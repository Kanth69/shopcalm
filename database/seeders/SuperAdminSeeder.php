<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'kanthcomic@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('123456'),
                'role_id' => User::ROLE_SUPER_ADMIN,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'productmanager@shopcalm.com'],
            [
                'name' => 'Product Manager Staff',
                'password' => Hash::make('password123'),
                'role_id' => User::ROLE_PRODUCT_MANAGER,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'ordermanager@shopcalm.com'],
            [
                'name' => 'Order Manager Staff',
                'password' => Hash::make('password123'),
                'role_id' => User::ROLE_ORDER_MANAGER,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'support@shopcalm.com'],
            [
                'name' => 'Customer Support Staff',
                'password' => Hash::make('password123'),
                'role_id' => User::ROLE_SUPPORT,
                'email_verified_at' => now(),
            ]
        );
    }
}
