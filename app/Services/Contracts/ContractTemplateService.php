<?php

namespace App\Services\Contracts;

use App\Models\Contract;
use DOMDocument;
use DOMElement;
use DOMNode;
use Illuminate\Support\Facades\View;
use InvalidArgumentException;

/**
 * Fills Jeota Media's contract templates with client-specific details and keeps
 * agreement HTML (template output, AI output or manual edits) safe to display.
 */
class ContractTemplateService
{
    /**
     * Services offered on the Order Form.
     *
     * @var array<string, string>
     */
    public const SERVICES = [
        'photography_project' => 'Photography (Project)',
        'photography_event' => 'Photography (Event)',
        'videography_project' => 'Videography (Project)',
        'videography_event' => 'Videography (Event)',
        'social_content' => 'Social Media Content Ideation & Creation',
        'social_management' => 'Social Media Accounts Management',
        'other' => 'Other',
    ];

    /**
     * Tags allowed in an agreement body. Everything else is unwrapped or removed.
     *
     * @var list<string>
     */
    private const ALLOWED_TAGS = ['h2', 'h3', 'h4', 'p', 'br', 'ol', 'ul', 'li', 'strong', 'b', 'em', 'i', 'u', 'span', 'table', 'thead', 'tbody', 'tr', 'th', 'td', 'blockquote', 'hr'];

    /**
     * Tags removed together with their content.
     *
     * @var list<string>
     */
    private const DROPPED_TAGS = ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'textarea', 'select', 'link', 'meta', 'svg', 'math', 'img', 'video', 'audio', 'head', 'title'];

    /**
     * Default values for template variables.
     *
     * @return array<string, mixed>
     */
    public function defaults(): array
    {
        return [
            'services' => [],
            'other_description' => '',
            'fee' => 0,
            'deposit_percent' => 50,
            'payment_days' => 7,
            'late_interest' => 5,
            'feedback_days' => 2,
            'revision_rounds' => 2,
            'reschedule_days' => 7,
            'cancellation_hours' => 48,
            'termination_days' => 30,
            'agreement_date' => '',
            'start_date' => '',
            'end_date' => '',
            'event_date' => '',
            'photo_count' => '',
            'reel_count' => '',
            'delivery_period' => 'month',
            'delivery_method' => 'a Digital Gallery / Cloud Link',
            'photo_delivery_days' => 7,
            'filming_sessions' => '',
            'session_hours' => '',
            'location' => '',
            'video_count' => '',
            'video_resolution' => '4K',
            'video_format' => 'MP4',
            'video_platform' => 'Google Drive',
            'video_delivery_days' => 14,
            'platforms' => '',
            'posts_per_week' => '',
            'engagement_hours' => '',
            'special_terms' => '',
            // Free-text overrides typed directly into the document editor
            'services_label' => '',
            'fee_words' => '',
            'initial_term' => '',
            'dates_photography_project' => '',
            'dates_photography_event' => '',
            'dates_videography_project' => '',
            'dates_videography_event' => '',
            'dates_social_content' => '',
            'dates_social_management' => '',
            'dates_other' => '',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function templates(): array
    {
        return config('jeota.contracts.templates', []);
    }

    /**
     * Keep only known template variables from user input (stored on the contract).
     *
     * @param  array<string, mixed>  $fields
     * @return array<string, mixed>
     */
    public function normalizeInput(array $fields): array
    {
        $clean = array_intersect_key($fields, $this->defaults());

        foreach ($clean as $key => $value) {
            if ($key === 'services') {
                $clean[$key] = array_values(array_intersect(array_keys(self::SERVICES), (array) $value));
            } elseif (is_array($value) || is_object($value)) {
                unset($clean[$key]);
            } elseif (is_string($value)) {
                $clean[$key] = mb_substr(trim($value), 0, $key === 'special_terms' ? 4000 : 255);
            }
        }

        return $clean;
    }

    /**
     * Merge user-provided fields with the defaults and derive display values.
     *
     * @param  array<string, mixed>  $fields
     * @return array<string, mixed>
     */
    public function normalize(array $fields): array
    {
        $defaults = $this->defaults();
        $provided = array_filter(
            $fields,
            fn ($value, $key) => $value !== null && ! ($value === '' && ($defaults[$key] ?? '') !== ''),
            ARRAY_FILTER_USE_BOTH
        );
        $f = array_merge($defaults, $provided);

        $f['services'] = array_values(array_intersect(array_keys(self::SERVICES), (array) $f['services']));
        if (trim((string) $f['services_label']) === '') {
            $f['services_label'] = $this->servicesSentence($f['services'], (string) $f['other_description']);
        }

        $f['fee'] = round((float) $f['fee'], 2);
        $f['fee_formatted'] = $f['fee'] > 0 ? number_format($f['fee'], 2) : '';
        if (trim((string) $f['fee_words']) === '') {
            $f['fee_words'] = $f['fee'] > 0 ? $this->amountInWords($f['fee']) : '';
        }

        foreach (['deposit_percent', 'payment_days', 'late_interest', 'feedback_days', 'revision_rounds', 'reschedule_days', 'termination_days'] as $key) {
            $f[$key] = max(0, (int) $f[$key]);
        }

        foreach (['payment_days', 'feedback_days', 'revision_rounds', 'reschedule_days', 'termination_days'] as $key) {
            $f[$key.'_words'] = strtolower($this->numberInWords($f[$key]));
        }

        return $f;
    }

    /**
     * Render the agreement body for a contract from its template.
     */
    public function render(Contract $contract): string
    {
        $template = $contract->template ?: 'photo-video-social';

        if (! array_key_exists($template, $this->templates()) || ! View::exists("contracts.templates.{$template}")) {
            throw new InvalidArgumentException("Unknown contract template [{$template}].");
        }

        $raw = $contract->fields ?? [];
        if (empty($raw['services'])) {
            // A fresh contract starts with every standard service; unwanted sections are deleted in the editor.
            $raw['services'] = ['photography_project', 'videography_project', 'social_content', 'social_management'];
        }
        $fields = $this->normalize($raw);
        $party = [
            'client_name' => $contract->client_name,
            'signatory_name' => $contract->signatory_name,
            'signatory_position' => $contract->signatory_position,
            'client_email' => $contract->client_email,
            'client_phone' => $contract->client_phone,
            'client_address' => $contract->client_address,
        ];

        $html = view("contracts.templates.{$template}", [
            'f' => $fields,
            'v' => fn (string $key): string => $this->field($key, $fields[$key] ?? ''),
            'p' => fn (string $key): string => $this->field($key, $party[$key] ?? ''),
            'has' => fn (string ...$services): bool => count(array_intersect($services, $fields['services'])) > 0,
            'party' => $party,
            'b' => config('jeota'),
            'quote' => $contract->quote,
        ])->render();

        return $this->sanitize($html);
    }

    /**
     * Whitelist-sanitise agreement HTML (drops scripts, event handlers, links, styles and unknown tags).
     */
    public function sanitize(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        $doc = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8"><div id="contract-root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $doc->getElementById('contract-root');
        if (! $root) {
            return e(strip_tags($html));
        }

        $this->cleanNode($root);

        $out = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            $out .= $doc->saveHTML($child);
        }

        return trim(preg_replace("/\n{3,}/", "\n\n", $out));
    }

    /**
     * Number of unfilled template blanks left in an agreement body.
     */
    public function countBlanks(?string $html): int
    {
        return preg_match_all('/class="[^"]*\bblank\b[^"]*"/', (string) $html);
    }

    /**
     * @param  list<string>  $services
     */
    public function servicesSentence(array $services, string $otherDescription = ''): string
    {
        $labels = [];
        foreach ($services as $key) {
            if ($key === 'other') {
                if (trim($otherDescription) !== '') {
                    $labels[] = trim($otherDescription);
                }

                continue;
            }
            $labels[] = self::SERVICES[$key] ?? $key;
        }

        $labels = array_values(array_unique($labels));
        if (count($labels) <= 1) {
            return $labels[0] ?? '';
        }

        $last = array_pop($labels);

        return implode(', ', $labels).' and '.$last;
    }

    /**
     * Amount in words to follow "Kenya Shillings", e.g. 150000.50 => "One Hundred and Fifty Thousand and Fifty Cents Only".
     */
    public function amountInWords(float $amount): string
    {
        $shillings = (int) floor($amount);
        $cents = (int) round(($amount - $shillings) * 100);

        $words = $this->numberInWords($shillings);
        if ($cents > 0) {
            $words .= ' and '.$this->numberInWords($cents).' Cents';
        }

        return $words.' Only';
    }

    public function numberInWords(int $number): string
    {
        if ($number === 0) {
            return 'Zero';
        }

        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        $belowThousand = function (int $n) use ($ones, $tens): string {
            $parts = [];
            if ($n >= 100) {
                $parts[] = $ones[intdiv($n, 100)].' Hundred';
                $n %= 100;
                if ($n > 0) {
                    $parts[] = 'and';
                }
            }
            if ($n >= 20) {
                $parts[] = $tens[intdiv($n, 10)].($n % 10 ? '-'.$ones[$n % 10] : '');
            } elseif ($n > 0) {
                $parts[] = $ones[$n];
            }

            return implode(' ', $parts);
        };

        $scales = [1_000_000_000 => 'Billion', 1_000_000 => 'Million', 1_000 => 'Thousand'];
        $parts = [];
        foreach ($scales as $value => $label) {
            if ($number >= $value) {
                $parts[] = $belowThousand(intdiv($number, $value)).' '.$label;
                $number %= $value;
            }
        }

        if ($number > 0) {
            $parts[] = ($parts && $number < 100 ? 'and ' : '').$belowThousand($number);
        }

        return implode(' ', $parts);
    }

    /**
     * An inline, editable template field. The editor syncs every span with the same data-f key
     * and reads them back on save; unfilled values show as highlighted blanks.
     */
    public function field(string $key, mixed $value): string
    {
        $value = is_scalar($value) ? trim((string) $value) : '';

        return $value === ''
            ? '<span class="cf blank" data-f="'.e($key).'">________</span>'
            : '<span class="cf" data-f="'.e($key).'">'.e($value).'</span>';
    }

    private function allowedClasses(string $value): string
    {
        $tokens = preg_split('/\s+/', trim($value)) ?: [];

        return implode(' ', array_values(array_intersect($tokens, ['cf', 'blank', 'appendix', 'tick', 'order-form'])));
    }

    private function cleanNode(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child->nodeType === XML_COMMENT_NODE || $child->nodeType === XML_PI_NODE) {
                $node->removeChild($child);

                continue;
            }

            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::DROPPED_TAGS, true)) {
                $node->removeChild($child);

                continue;
            }

            $this->cleanNode($child);

            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                // Unwrap unknown tags (div, a, font, …) but keep their text.
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);

                continue;
            }

            foreach (iterator_to_array($child->attributes) as $attribute) {
                $name = strtolower($attribute->name);
                $keep = ($name === 'type' && $tag === 'ol' && in_array($attribute->value, ['1', 'a', 'A', 'i', 'I'], true))
                    || (in_array($name, ['colspan', 'rowspan'], true) && ctype_digit($attribute->value))
                    || ($name === 'data-f' && $tag === 'span' && preg_match('/^[a-z_]{1,40}$/', $attribute->value))
                    || ($name === 'class' && $this->allowedClasses($attribute->value) !== '');

                if ($name === 'class' && $keep) {
                    $child->setAttribute('class', $this->allowedClasses($attribute->value));

                    continue;
                }

                if (! $keep) {
                    $child->removeAttribute($attribute->name);
                }
            }
        }
    }
}
