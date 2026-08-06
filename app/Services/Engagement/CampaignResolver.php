<?php

namespace App\Services\Engagement;

use App\Models\User;
use Illuminate\Support\Facades\App;
use Exception;

class CampaignResolver
{
    /**
     * Resolves the extra dynamic data needed for a specific campaign type.
     */
    public function resolveData(string $campaignKey, User $user): array
    {
        return match ($campaignKey) {
            'PROFILE_COMPLETION' => App::make(ProfileCompletionService::class)->getStatus($user),
            'BIRTHDAY' => ['is_birthday' => true], // Placeholder for future service
            default => []
        };
    }

    /**
     * Checks if the user is actually eligible for the specific business logic of a campaign.
     */
    public function checkEligibility(string $campaignKey, User $user): bool
    {
        return match ($campaignKey) {
            'PROFILE_COMPLETION' => !App::make(ProfileCompletionService::class)->getStatus($user)['is_fully_completed'],
            default => true
        };
    }
}
