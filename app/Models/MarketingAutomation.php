<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketingAutomation extends Model
{
    use HasFactory;

    protected $table = 'marketing_automations';

    protected $fillable = [
        'key',
        'name',
        'category',
        'trigger_event',
        'is_active',
        'delay_minutes',
        'reward_type',
        'reward_amount',
        'reward_expiry_days',
        'min_order_value',
        'title_template',
        'body_template',
        'supported_tags',
        'total_sent',
        'last_triggered_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'delay_minutes' => 'integer',
        'reward_amount' => 'float',
        'reward_expiry_days' => 'integer',
        'min_order_value' => 'float',
        'title_template' => 'array',
        'body_template' => 'array',
        'supported_tags' => 'array',
        'total_sent' => 'integer',
        'last_triggered_at' => 'datetime',
    ];

    /**
     * Format title & body in the target language with replaced tags.
     */
    public function formatMessage(string $lang = 'en', array $variables = []): array
    {
        $lang = strtolower($lang);
        $titles = $this->title_template ?? [];
        $bodies = $this->body_template ?? [];

        $title = $titles[$lang] ?? ($titles['default'] ?? ($titles['en'] ?? (is_array($titles) ? (reset($titles) ?: '') : (string) $titles)));
        $body  = $bodies[$lang]  ?? ($bodies['default']  ?? ($bodies['en']  ?? (is_array($bodies)  ? (reset($bodies) ?: '') : (string) $bodies)));

        foreach ($variables as $key => $val) {
            $tag = str_starts_with($key, '{') ? $key : ('{' . $key . '}');
            $strVal = (string) $val;
            $title = str_replace($tag, $strVal, $title);
            $body = str_replace($tag, $strVal, $body);
        }

        return [
            'title' => $title,
            'body'  => $body,
        ];
    }
}
