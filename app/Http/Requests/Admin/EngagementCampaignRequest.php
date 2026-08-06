<?php

namespace App\Http\Requests\Admin;

use App\Enums\EngagementTrigger;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class EngagementCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        $id = $this->route('campaign')?->id;

        return [
            'campaign_key' => ['required', 'string', 'max:50', Rule::unique('engagement_campaigns')->ignore($id)],
            'name' => ['required', 'string', 'max:255'],
            'trigger_type' => ['required', new Enum(EngagementTrigger::class)],
            'target_role' => ['required', 'in:CUSTOMER,VENDOR,ADMIN'],
            'frequency_type' => ['required', 'in:ONCE,EVERY_LOGIN,AFTER_X_DAYS,UNTIL_COMPLETED'],
            'priority' => ['required', 'integer', 'min:1'],
            'cooldown_days' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date', 'after:start_at'],

            // Template Validation
            'template.title' => ['required', 'string', 'max:255'],
            'template.message' => ['required', 'string'],
            'template.button_text' => ['nullable', 'string', 'max:50'],
            'template.button_url' => ['nullable', 'string', 'max:255'],
            'template.theme_color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
        ];
    }
}
