<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use App\Services\AvatarGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@asset-management.com'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'has_all_opds' => true,
            ]
        );

        $admin->assignRole(Role::ADMIN->value);

        // Generate Avatar for Admin
        if ($admin->media()->where('collection', 'avatar')->doesntExist()) {
            $generator = new AvatarGenerator;
            // Generate SVG avatar
            $generator->generateAndSave($admin->name, 'svg', $admin);
        }
    }
}
