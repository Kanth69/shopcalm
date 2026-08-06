<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class EngagementEngineSeeder extends Seeder
{
    public function run(): void
    {
        // The Engagement Engine tables (including profile_completion_rules)
        // were permanently removed from the architecture in migration:
        // 2024_03_21_000001_remove_engagement_engine_and_add_prompt_flag.php
        // in favor of a simpler One-Time Profile Setup workflow.

        // This seeder is intentionally left empty to prevent SQLSTATE[42S02] errors
        // when running db:seed on a fresh database.
    }
}
