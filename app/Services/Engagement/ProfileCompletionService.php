<?php

namespace App\Services\Engagement;

use App\Models\User;
use App\Models\ProfileCompletionRule;
use Illuminate\Support\Facades\Log;

class ProfileCompletionService
{
    public function getStatus(User $user): array
    {
        $user->loadMissing(['profile', 'interests', 'addresses']);

        $rules = ProfileCompletionRule::where('status', true)->get();

        $totalWeight = $rules->sum('weight');
        $achievedWeight = 0;
        $completedFields = [];
        $missingFields = [];

        foreach ($rules as $rule) {
            $isComplete = $this->checkField($user, $rule->field_key);

            if ($isComplete) {
                $achievedWeight += $rule->weight;
                $completedFields[] = $rule->display_name;
            } else {
                $missingFields[] = [
                    'key' => $rule->field_key,
                    'name' => $rule->display_name,
                    'is_required' => $rule->is_required
                ];
            }
        }

        $percentage = ($totalWeight > 0) ? round(($achievedWeight / $totalWeight) * 100) : 0;

        // Find the next field that the user HAS NOT yet provided
        // We only recommend fields that are NOT already completed
        $nextField = null;
        foreach ($missingFields as $field) {
            $nextField = $field['name'];
            break;
        }

        return [
            'percentage' => (int) $percentage,
            'completed_fields' => $completedFields,
            'missing_fields' => $missingFields,
            'next_recommended_field' => $nextField,
            'is_fully_completed' => $percentage >= 100
        ];
    }

    private function checkField(User $user, string $key): bool
    {
        return match ($key) {
            'name' => !empty($user->name),
            'email' => !empty($user->email),
            'mobile_number' => !empty($user->mobile_number),
            'gender' => $user->profile && !empty($user->profile->gender),
            'date_of_birth' => $user->profile && !empty($user->profile->date_of_birth),
            'interests' => $user->interests->count() >= 3,
            'address' => $user->addresses->where('is_default', true)->isNotEmpty(),
            default => false
        };
    }
}
