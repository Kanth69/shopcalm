<?php

namespace App\Events\Engagement;

use App\Models\EngagementCampaign;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CampaignDismissed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public EngagementCampaign $campaign,
        public User $user
    ) {}
}
