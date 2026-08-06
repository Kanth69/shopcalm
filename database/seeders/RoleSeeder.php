<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::updateOrCreate(['id' => 1], ['name' => 'Super Admin']);
        Role::updateOrCreate(['id' => 2], ['name' => 'Admin']);
        Role::updateOrCreate(['id' => 3], ['name' => 'Customer']);
    }
}
