<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SuperAdminSeeder::class,
            PageSeeder::class,
            CategorySeeder::class,
            BrandSeeder::class,
            ProductSeeder::class,
            BannerSeeder::class,
            CouponSeeder::class,
            PincodeSeeder::class,
        ]);

        \Illuminate\Support\Facades\Cache::flush();
    }
}
