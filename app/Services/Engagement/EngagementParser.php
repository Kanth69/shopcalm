<?php

namespace App\Services\Engagement;

use App\Models\User;

class EngagementParser
{
    /**
     * Parse template message and replace placeholders with real data.
     */
    public function parse(string $message, User $user, array $data): string
    {
        $placeholders = [
            '{name}' => $user->name,
            '{progress}' => $data['percentage'] ?? 0,
            '{first_name}' => explode(' ', $user->name)[0],
            '{city}' => $user->addresses()->where('is_default', true)->first()?->city ?? 'your city',
        ];

        return str_replace(array_keys($placeholders), array_values($placeholders), $message);
    }
}
