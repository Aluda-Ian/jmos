<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Models\User;
use App\Services\Ai\GeminiSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeminiConnectionTest extends TestCase
{
    use RefreshDatabase;

    private function fakeGoogle(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/v1beta/models?*' => Http::response(['models' => [
                ['name' => 'models/gemini-flash-test', 'supportedGenerationMethods' => ['generateContent']],
                ['name' => 'models/text-embedding-x', 'supportedGenerationMethods' => ['embedContent']],
            ]]),
            'generativelanguage.googleapis.com/v1beta/models/gemini-flash-test:generateContent' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => 'JMOS connected']]]]],
            ]),
        ]);
    }

    public function test_owner_can_test_a_gemini_key_and_is_offered_a_model(): void
    {
        $this->fakeGoogle();
        $owner = User::factory()->create(['role' => 'owner']);

        $this->actingAs($owner)->postJson('/api/settings/test-ai', ['api_key' => 'AIza-test'])
            ->assertOk()
            ->assertJsonPath('ok', false)
            ->assertJsonPath('model', 'gemini-flash-test')
            ->assertJsonPath('models', ['gemini-flash-test']);

        $this->actingAs($owner)->postJson('/api/settings/test-ai', ['api_key' => 'AIza-test', 'model' => 'gemini-flash-test'])
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('reply', 'JMOS connected');
    }

    public function test_only_owner_and_managers_can_test_ai_settings(): void
    {
        $this->postJson('/api/settings/test-ai')->assertUnauthorized();

        $sales = User::factory()->create(['role' => 'sales']);
        $this->actingAs($sales)->postJson('/api/settings/test-ai', ['api_key' => 'x'])->assertForbidden();
    }

    public function test_key_saved_in_settings_overrides_the_env_configuration(): void
    {
        config(['ai.gemini.api_key' => 'from-env', 'ai.gemini.model' => 'env-model']);
        SystemSetting::setVal('gemini_api_key', 'from-dashboard', 'ai', true);
        SystemSetting::setVal('gemini_model', 'dashboard-model', 'ai');

        GeminiSettings::apply();

        $this->assertSame('from-dashboard', config('ai.gemini.api_key'));
        $this->assertSame('dashboard-model', config('ai.gemini.model'));
    }
}
