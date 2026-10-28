<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::updateOrCreate(
            ['name' => 'Admin'],
            ['description' => 'Full system access']
        );

        Role::updateOrCreate(
            ['name' => 'Manager'],
            ['description' => 'Manage products, inventory, purchases and reports']
        );

        Role::updateOrCreate(
            ['name' => 'Staff'],
            ['description' => 'Manage sales and transactions']
        );
    }
}