<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\MembershipPackage;
use App\Models\MembershipPackageItem;
use App\Models\MembershipTransaction;
use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Visit;
use App\Services\CustomerService;
use App\Services\SaleService;
use App\Services\StockMovementService;
use App\Services\VisitService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestingMasterSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Roles & Users
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $staffRole = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);

        $adminUser = User::firstOrCreate(
            ['email' => 'admin@gym.com'],
            [
                'name' => 'Admin Gym',
                'password' => Hash::make('password'),
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
        $customerService = app(CustomerService::class);
        $customer1 = Customer::where('email', 'member1@example.com')->first();
        if (! $customer1) {
            $customer1 = $customerService->create([
                'name' => 'Budi Santoso',
                'phone' => '081234567890',
                'email' => 'member1@example.com',
                'qr_code' => 'MEMBER-001',
                'created_at' => now(),
            ]);
        }
        // Code & avatar handled by service on create

        $customer2 = Customer::where('email', 'member2@example.com')->first();
        if (! $customer2) {
            $customer2 = $customerService->create([
                'name' => 'Siti Aminah',
                'phone' => '081234567891',
                'email' => 'member2@example.com',
                'qr_code' => 'MEMBER-002',
                'created_at' => now(),
            ]);
        }
        // Code & avatar handled by service on create

        // 5. Simulate Transactions
        if (MembershipTransaction::count() === 0) {
            // Membership Transaction for Customer 1 (Monthly)
            $transaction = MembershipTransaction::create([
                'customer_id' => $customer1->id,
                'package_id' => $monthlyPackage->id,
                'start_date' => now(),
                'end_date' => now()->addDays($monthlyPackage->duration_days),
                'price' => $monthlyPackage->price,
                'status' => 'active',
                'created_by' => $staffUser->id,
                'created_at' => now(),
            ]);

            // Visit for Customer 1
            Visit::create([
                'customer_id' => $customer1->id,
                'membership_transaction_id' => $transaction->id,
                'visit_type' => 'MEMBERSHIP',
                'price' => 0, // Free for members
                'checkin_method' => 'QR_CODE',
                'created_by' => $staffUser->id,
                'checkin_time' => now(),
            ]);
        }

        if (Sale::count() === 0) {
            // Product Sale for Customer 2 (use service to auto-create OUT movements & deduct stock)
            $saleService = app(SaleService::class);
            $saleService->create([
                'customer_id' => $customer2->id,
                'items' => [
                    ['product_id' => $water->id, 'quantity' => 1, 'price' => $water->price],
                    ['product_id' => $proteinShake->id, 'quantity' => 1, 'price' => $proteinShake->price],
                ],
            ], $staffUser->id);
        }

        // 6. Initial Stock Movements for products (if none exists)
        foreach ([$water, $proteinShake, $towel] as $product) {
            if (! StockMovement::where('product_id', $product->id)->exists()) {
                // Record initial stock as IN movement
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'IN',
                    'quantity' => $product->stock,
                    'description' => 'Initial stock seeding',
                    'created_at' => now(),
                ]);
            }
        }

        // 7. Additional Stock Adjustment example (set towel stock to 25)
        $stockService = app(StockMovementService::class);
        $stockService->create([
            'product_id' => $towel->id,
            'type' => 'ADJUSTMENT',
            'quantity' => 25,
            'description' => 'Inventory adjustment (seeding)',
        ]);

        // 8. Daily Visit for Customer 2 (use service to enforce rules)
        $visitService = app(VisitService::class);
        $visitService->create([
            'customer_id' => $customer2->id,
            'visit_type' => 'DAILY',
            'price' => 50000,
            'checkin_method' => 'MANUAL',
        ], $staffUser->id);

    }
}
