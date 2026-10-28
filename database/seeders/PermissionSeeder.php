<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            [
                'name' => 'dashboard.view',
                'description' => 'View dashboard',
            ],

            [
                'name' => 'products.view',
                'description' => 'View products',
            ],
            [
                'name' => 'products.create',
                'description' => 'Create products',
            ],
            [
                'name' => 'products.update',
                'description' => 'Update products',
            ],
            [
                'name' => 'products.delete',
                'description' => 'Delete products',
            ],

            [
                'name' => 'categories.view',
                'description' => 'View categories',
            ],
            [
                'name' => 'categories.create',
                'description' => 'Create categories',
            ],
            [
                'name' => 'categories.update',
                'description' => 'Update categories',
            ],
            [
                'name' => 'categories.delete',
                'description' => 'Delete categories',
            ],

            [
                'name' => 'inventory.view',
                'description' => 'View inventory',
            ],
            [
                'name' => 'inventory.adjust',
                'description' => 'Adjust inventory',
            ],

            [
                'name' => 'purchases.view',
                'description' => 'View purchases',
            ],
            [
                'name' => 'purchases.create',
                'description' => 'Create purchases',
            ],

            [
                'name' => 'sales.view',
                'description' => 'View sales',
            ],

            [
                'name' => 'reports.view',
                'description' => 'View reports',
            ],

            [
                'name' => 'users.view',
                'description' => 'View users',
            ],
            [
                'name' => 'users.create',
                'description' => 'Create users',
            ],
            [
                'name' => 'users.update',
                'description' => 'Update users',
            ],
            [
                'name' => 'users.delete',
                'description' => 'Delete users',
            ],

            [
                'name' => 'settings.manage',
                'description' => 'Manage settings',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                ['description' => $permission['description']]
            );
        }

        // Admin gets ALL permissions
        $admin = Role::where('name', 'Admin')->first();

        if ($admin) {
            $admin->permissions()->sync(
                Permission::pluck('id')
            );
        }
    }
}