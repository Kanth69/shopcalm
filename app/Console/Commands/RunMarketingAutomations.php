<?php

namespace App\Console\Commands;

use App\Services\MarketingAutomationService;
use Illuminate\Console\Command;

class RunMarketingAutomations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'marketing:run-automations {--type=all : The type of automation to execute: all, birthday, cart, winback}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute active marketing automated playbooks (Birthday treats, Cart recoveries, Win-backs)';

    /**
     * Execute the console command.
     */
    public function handle(MarketingAutomationService $service): int
    {
        $type = $this->option('type') ?: 'all';
        $this->info("Starting Marketing Automation Runner [Type: {$type}]...");

        if ($type === 'all' || $type === 'birthday') {
            $this->line("Evaluating Birthday treats...");
            $bCount = $service->runBirthdayAutomations();
            $this->info("✓ Birthday Treats: {$bCount} user(s) rewarded and notified.");
        }

        if ($type === 'all' || $type === 'cart') {
            $this->line("Evaluating Abandoned Cart recoveries...");
            $cCount = $service->runAbandonedCartAutomations();
            $this->info("✓ Abandoned Carts: {$cCount} nudge(s) dispatched.");
        }

        if ($type === 'all' || $type === 'winback') {
            $this->line("Evaluating Inactive Customer Win-backs...");
            $wCount = $service->runCustomerWinbackAutomations();
            $this->info("✓ Customer Win-backs: {$wCount} notification(s) dispatched.");
        }

        $this->info("Marketing automation run completed.");
        return Command::SUCCESS;
    }
}
