<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\MembershipPackage;
use App\Models\MembershipPackageItem;
use App\Models\MembershipTransaction;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role as SpatieRole;

class TestingMasterSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Roles & Users
        $adminRole = SpatieRole::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $staffRole = SpatieRole::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);

        $adminUser = User::firstOrCreate(
            ['email' => 'admin@gym.com'],
            [
                'name' => 'Admin Gym',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        if (! $adminUser->hasRole('admin')) {
            $adminUser->assignRole($adminRole);
        }

        $staffUser = User::firstOrCreate(
            ['email' => 'staff@gym.com'],
            [
                'name' => 'Staff Gym',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        if (! $staffUser->hasRole('staff')) {
            $staffUser->assignRole($staffRole);
        }

        // 2. Create Membership Packages
        $dailyPackage = MembershipPackage::firstOrCreate(
            ['name' => 'Daily Pass'],
            [
                'price' => 50000,
                'duration_days' => 1,
                'description' => 'Akses gym harian',
                'is_active' => true,
                'created_at' => now(),
            ]
        );

        $monthlyPackage = MembershipPackage::firstOrCreate(
            ['name' => 'Monthly Membership'],
            [
                'price' => 450000,
                'duration_days' => 30,
                'description' => 'Akses gym bulanan',
                'is_active' => true,
                'created_at' => now(),
            ]
        );

        $annualPackage = MembershipPackage::firstOrCreate(
            ['name' => 'Annual Membership'],
            [
                'price' => 4500000,
                'duration_days' => 365,
                'description' => 'Akses gym tahunan (Hemat 2 bulan)',
                'is_active' => true,
                'created_at' => now(),
            ]
        );

        // Add items to packages
        MembershipPackageItem::firstOrCreate([
            'package_id' => $dailyPackage->id,
            'item_name' => 'Access to Gym Floor',
            'quantity' => 1,
            'unit' => 'Day',
        ]);

        MembershipPackageItem::firstOrCreate([
            'package_id' => $monthlyPackage->id,
            'item_name' => 'Access to Gym Floor',
            'quantity' => 30,
            'unit' => 'Days',
        ]);

        MembershipPackageItem::firstOrCreate([
            'package_id' => $annualPackage->id,
            'item_name' => 'Access to Gym Floor',
            'quantity' => 365,
            'unit' => 'Days',
        ]);

        MembershipPackageItem::firstOrCreate([
            'package_id' => $annualPackage->id,
            'item_name' => 'Free PT Session',
            'quantity' => 2,
            'unit' => 'Sessions',
        ]);

        // 3. Create Products
        $water = Product::firstOrCreate(
            ['name' => 'Mineral Water 600ml'],
            [
                'price' => 5000,
                'stock' => 100,
                'created_at' => now(),
            ]
        );

        $proteinShake = Product::firstOrCreate(
            ['name' => 'Protein Shake'],
            [
                'price' => 35000,
                'stock' => 50,
                'created_at' => now(),
            ]
        );

        $towel = Product::firstOrCreate(
            ['name' => 'Gym Towel'],
            [
                'price' => 75000,
                'stock' => 20,
                'created_at' => now(),
            ]
        );

        // 4. Create Customers
        $customer1 = Customer::firstOrCreate(
            ['email' => 'member1@example.com'],
            [
                'name' => 'Budi Santoso',
                'phone' => '081234567890',
                'qr_code' => 'MEMBER-001',
                'created_at' => now(),
            ]
        );

        $customer2 = Customer::firstOrCreate(
            ['email' => 'member2@example.com'],
            [
                'name' => 'Siti Aminah',
                'phone' => '081234567891',
                'qr_code' => 'MEMBER-002',
                'created_at' => now(),
            ]
        );

        // 5. Simulate Transactions
        if (MembershipTransaction::count() === 0) {
            // Membership Transaction for Customer 1 (Monthly)
            $transaction = MembershipTransaction::create([
                'customer_id' => $customer1->id,
                'package_id' => $monthlyPackage->id,
                'start_date' => now(),
                'end_date' => now()->addDays($monthlyPackage->duration_days),
                'price' => $monthlyPackage->price,
                'status' => 'paid',
                'created_by' => $staffUser->id,
                'created_at' => now(),
            ]);

            // Visit for Customer 1
            Visit::create([
                'customer_id' => $customer1->id,
                'membership_transaction_id' => $transaction->id,
                'visit_type' => 'member',
                'price' => 0, // Free for members
                'checkin_method' => 'qr_code',
                'created_by' => $staffUser->id,
                'checkin_time' => now(),
            ]);
        }

        if (Sale::count() === 0) {
            // Product Sale for Customer 2
            $sale = Sale::create([
                'customer_id' => $customer2->id,
                'total_amount' => $water->price + $proteinShake->price,
                'created_by' => $staffUser->id,
                'created_at' => now(),
            ]);

            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $water->id,
                'quantity' => 1,
                'price' => $water->price,
                'subtotal' => $water->price,
            ]);

            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $proteinShake->id,
                'quantity' => 1,
                'price' => $proteinShake->price,
                'subtotal' => $proteinShake->price,
            ]);
        }
    }
}
