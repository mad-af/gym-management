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
    }
}
