<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PurgeTestDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:purge {--force : Force the operation to run without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely delete all customer and non-super-admin users, orders, carts, wallets, and order histories while preserving Super Admin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $superAdminCount = User::where('role_id', User::ROLE_SUPER_ADMIN)->count();

        if ($superAdminCount === 0) {
            $this->error('Aborting: No Super Admin account was found in the database! Cannot proceed without preserving at least one Super Admin.');
            return 1;
        }

        if (!$this->option('force') && !$this->confirm("Are you sure you want to delete ALL customer data, non-super-admin users, orders, and wallets? This cannot be undone!")) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $this->warn("Starting database purge... (Preserving {$superAdminCount} Super Admin account(s))");

        Schema::disableForeignKeyConstraints();

        // 1. Order-related tables (Truncate completely)
        $orderTables = [
            'order_items',
            'order_fulfillments',
            'order_status_histories',
            'order_cancellations',
            'order_feedbacks',
            'payments',
            'cash_settlements',
            'orders',
        ];

        foreach ($orderTables as $table) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                DB::table($table)->truncate();
                $this->line("<comment>Truncated:</comment> {$table} ({$count} records)");
            }
        }

        // 2. Customer & User data tables (Truncate completely)
        $userDataTables = [
            'customer_profiles',
            'customer_interest_map',
            'addresses',
            'wallet_transactions',
            'wallets',
            'cart_items',
            'carts',
            'wishlist_items',
            'wishlists',
            'coupon_usages',
            'product_reviews',
            'contact_enquiries',
            'subscribers',
            'otp_verifications',
            'sessions',
        ];

        foreach ($userDataTables as $table) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                DB::table($table)->truncate();
                $this->line("<comment>Truncated:</comment> {$table} ({$count} records)");
            }
        }

        // 3. Clear order-related stock movement records
        if (Schema::hasTable('stock_movements')) {
            $deletedStockMovements = DB::table('stock_movements')
                ->where('reference_type', 'App\\Models\\Order')
                ->orWhere('source', 'ORDER')
                ->delete();
            $this->line("<comment>Cleaned stock movements:</comment> Removed {$deletedStockMovements} order movement records");

            // Point remaining stock movements created_by to the first Super Admin
            $firstSuperAdmin = User::where('role_id', User::ROLE_SUPER_ADMIN)->first();
            if ($firstSuperAdmin) {
                DB::table('stock_movements')
                    ->whereNotIn('created_by', User::where('role_id', User::ROLE_SUPER_ADMIN)->pluck('id'))
                    ->update(['created_by' => $firstSuperAdmin->id]);
            }
        }

        // 4. Clear Personal Access Tokens for non-super-admins
        if (Schema::hasTable('personal_access_tokens')) {
            $superAdminIds = User::where('role_id', User::ROLE_SUPER_ADMIN)->pluck('id');
            $deletedTokens = DB::table('personal_access_tokens')
                ->whereNotIn('tokenable_id', $superAdminIds)
                ->delete();
            $this->line("<comment>Cleaned tokens:</comment> Removed {$deletedTokens} customer/staff access tokens");
        }

        // 5. Delete all users EXCEPT Super Admins
        $deletedUsers = DB::table('users')
            ->where('role_id', '!=', User::ROLE_SUPER_ADMIN)
            ->delete();
        $this->line("<comment>Users pruned:</comment> Deleted {$deletedUsers} non-super-admin user accounts");

        Schema::enableForeignKeyConstraints();

        // 6. Flush cache
        \Illuminate\Support\Facades\Cache::flush();

        $this->newLine();
        $this->info("✅ Database purge complete! Preserved {$superAdminCount} Super Admin account(s).");
        $this->info("Product catalog, categories, brands, settings, and pincodes remain intact.");

        return 0;
    }
}
