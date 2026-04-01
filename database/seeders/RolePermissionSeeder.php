<?php

namespace Database\Seeders;

use App\Enums\Permission;
use App\Enums\Role;
use App\Models\Permission as SpatiePermission;
use App\Models\Role as SpatieRole;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        foreach (Permission::cases() as $permission) {
            SpatiePermission::firstOrCreate(['name' => $permission->value]);
        }

        // Create Roles and Assign Permissions

        // Admin (Static System Role) - Gets all permissions
        $adminRole = SpatieRole::firstOrCreate(['name' => Role::ADMIN->value]);
        $adminRole->syncPermissions(Permission::values());

        $staffPermissions = [
            Permission::VIEW_DASHBOARD->value,
            Permission::VIEW_OPERATIONS->value,
            Permission::CREATE_CUSTOMERS->value,
            Permission::CREATE_MEMBERSHIP_TRANSACTIONS->value,
            Permission::CANCEL_MEMBERSHIP_TRANSACTIONS->value,
            Permission::CREATE_VISITS->value,
            Permission::CANCEL_VISITS->value,
            Permission::CREATE_STOCK_MOVEMENTS->value,
            Permission::CREATE_SALES->value,
            Permission::CANCEL_SALES->value,
        ];

        $staffRole = SpatieRole::firstOrCreate(['name' => Role::STAFF->value]);
        $staffRole->syncPermissions($staffPermissions);
    }
}
