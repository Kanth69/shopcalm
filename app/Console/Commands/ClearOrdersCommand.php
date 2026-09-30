<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClearOrdersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:clear {--force : Force the operation to run without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely delete all orders and associated items, payments, fulfillments, and histories';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force') && !$this->confirm('Are you sure you want to delete ALL orders and related data from the database?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $this->info('Clearing order tables...');

        Schema::disableForeignKeyConstraints();

        $tables = [
            'order_items',
            'order_fulfillments',
            'order_status_histories',
            'order_cancellations',
            'order_feedbacks',
            'payments',
            'orders',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
                $this->line("<comment>Truncated table:</comment> {$table}");
            }
        }

        Schema::enableForeignKeyConstraints();

        $this->info('✅ All order data deleted successfully!');
        return 0;
    }
}
