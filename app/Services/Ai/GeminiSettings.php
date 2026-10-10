<?php

namespace App\Services\Ai;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;

/**
 * Google Gemini connection saved from Settings → AI Assistant.
 * Values saved in the dashboard take priority over GEMINI_* values in .env.
 */
class GeminiSettings
{
    public const KEY_SETTING = 'gemini_api_key';

    public const MODEL_SETTING = 'gemini_model';

    /**
     * Copy the saved dashboard values into runtime config (used by reports and contract drafting).
     */
    public static function apply(): void
    {
        try {
            $saved = SystemSetting::whereIn('key', [self::KEY_SETTING, self::MODEL_SETTING])->pluck('value', 'key');
        } catch (\Throwable) {
            return; // settings table not migrated yet
        }

        if (filled($saved[self::KEY_SETTING] ?? null)) {
            config(['ai.gemini.api_key' => trim($saved[self::KEY_SETTING])]);
        }
        if (filled($saved[self::MODEL_SETTING] ?? null)) {
            config(['ai.gemini.model' => trim($saved[self::MODEL_SETTING])]);
        }
    }

    /**
     * Check a key/model against Google: lists the models the key can use, then runs a tiny test prompt.
     *
     * @return array{ok: bool, message: string, models: list<string>, model?: string, reply?: string}
     */
    public function test(?string $apiKey = null, ?string $model = null): array
    {
        $apiKey = trim((string) ($apiKey ?: config('ai.gemini.api_key')));
        $model = trim((string) ($model ?: config('ai.gemini.model')));
        $base = rtrim((string) config('ai.gemini.endpoint', 'https://generativelanguage.googleapis.com/v1beta/models'), '/');

        if ($apiKey === '') {
            return ['ok' => false, 'message' => 'Paste your Gemini API key first.', 'models' => []];
        }

        try {
            $list = Http::timeout(15)->withHeaders(['x-goog-api-key' => $apiKey])->get($base, ['pageSize' => 200]);
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Could not reach Google from the server: '.$e->getMessage(), 'models' => []];
        }

        if (! $list->successful()) {
            return ['ok' => false, 'message' => $this->explain($list->status(), (string) $list->json('error.message')), 'models' => []];
        }

        $models = collect($list->json('models', []))
            ->filter(fn ($m) => in_array('generateContent', $m['supportedGenerationMethods'] ?? [], true))
            ->map(fn ($m) => str_replace('models/', '', $m['name']))
            ->filter(fn ($name) => str_starts_with($name, 'gemini'))
            ->values()
            ->all();

        if ($model === '' || ! in_array($model, $models, true)) {
            $suggested = collect($models)->first(fn ($m) => str_contains($m, 'flash') && ! str_contains($m, 'lite') && ! str_contains($m, 'preview') && ! str_contains($m, 'exp'))
                ?? ($models[0] ?? null);

            return [
                'ok' => false,
                'message' => ($model === '' ? 'The key works. Now choose a model.' : "The key works, but the model \"{$model}\" is not available to it.")
                    .($suggested ? " Suggested: {$suggested}." : ''),
                'models' => $models,
                'model' => $suggested,
            ];
        }

        try {
            $reply = Http::timeout(30)->withHeaders(['x-goog-api-key' => $apiKey])->post("{$base}/{$model}:generateContent", [
                'contents' => [['role' => 'user', 'parts' => [['text' => 'Reply with exactly: JMOS connected']]]],
                'generationConfig' => ['maxOutputTokens' => 20, 'temperature' => 0],
            ]);
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'The test request failed: '.$e->getMessage(), 'models' => $models, 'model' => $model];
        }

        if (! $reply->successful()) {
            return ['ok' => false, 'message' => $this->explain($reply->status(), (string) $reply->json('error.message')), 'models' => $models, 'model' => $model];
        }

        return [
            'ok' => true,
            'message' => "Connected to Google Gemini ({$model}).",
            'models' => $models,
            'model' => $model,
            'reply' => trim((string) $reply->json('candidates.0.content.parts.0.text')),
        ];
    }

    private function explain(int $status, string $googleMessage): string
    {
        $hint = match ($status) {
            400 => 'Google rejected the request — usually an invalid key or a model name that does not exist.',
            401, 403 => 'The key was refused. Check it was copied fully, and that the Generative Language API is allowed for this key.',
            404 => 'That model was not found for this key. Pick one from the list.',
            429 => 'Quota reached. Free-tier keys have small daily limits — wait, or enable billing in Google AI Studio.',
            default => "Google returned an error ({$status}).",
        };

        return trim($hint.($googleMessage !== '' ? ' Google says: '.$googleMessage : ''));
    }
}
