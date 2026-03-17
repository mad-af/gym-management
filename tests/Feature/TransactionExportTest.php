<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\MembershipPackage;
use App\Models\MembershipTransaction;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    public function test_visits_export_returns_csv_in_selected_date_range(): void
    {
        $customer = Customer::create([
            'name' => 'Budi Santoso',
            'code' => 'MEM-001',
        ]);

        Visit::create([
            'customer_id' => $customer->id,
            'visit_type' => 'MEMBERSHIP',
            'price' => 0,
            'checkin_method' => 'QR_CODE',
            'checkin_time' => '2026-03-10 10:00:00',
        ]);

        $response = $this->get('/api/visits/export?start_date=2026-03-01&end_date=2026-03-31');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('Tanggal Check-In', $response->streamedContent());
        $this->assertStringContainsString('Budi Santoso', $response->streamedContent());
    }

    public function test_membership_transactions_export_returns_csv_in_selected_date_range(): void
    {
        $customer = Customer::create([
            'name' => 'Siti Aminah',
            'code' => 'MEM-002',
        ]);

        $package = MembershipPackage::create([
            'name' => 'Paket Bulanan',
            'duration_days' => 30,
            'price' => 150000,
        ]);

        MembershipTransaction::create([
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'start_date' => '2026-03-01',
            'end_date' => '2026-03-31',
            'price' => 150000,
            'status' => 'ACTIVE',
            'created_at' => '2026-03-05 09:00:00',
        ]);

        $response = $this->get('/api/membership-transactions/export?start_date=2026-03-01&end_date=2026-03-31');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('Tanggal Transaksi', $response->streamedContent());
        $this->assertStringContainsString('Siti Aminah', $response->streamedContent());
        $this->assertStringContainsString('Paket Bulanan', $response->streamedContent());
    }

    public function test_sales_export_returns_csv_in_selected_date_range(): void
    {
        $customer = Customer::create([
            'name' => 'Andi Wijaya',
            'code' => 'MEM-003',
        ]);

        $product = Product::create([
            'name' => 'Protein Bar',
            'price' => 25000,
            'stock' => 100,
        ]);

        $sale = Sale::create([
            'customer_id' => $customer->id,
            'total_amount' => 50000,
            'created_at' => '2026-03-11 13:00:00',
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 25000,
            'subtotal' => 50000,
        ]);

        $response = $this->get('/api/sales/export?start_date=2026-03-01&end_date=2026-03-31');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('Total Penjualan', $response->streamedContent());
        $this->assertStringContainsString('Andi Wijaya', $response->streamedContent());
        $this->assertStringContainsString('Protein Bar', $response->streamedContent());
    }

    public function test_stock_movements_export_returns_csv_in_selected_date_range(): void
    {
        $product = Product::create([
            'name' => 'Dumbbell 5kg',
            'price' => 300000,
            'stock' => 10,
        ]);

        StockMovement::create([
            'product_id' => $product->id,
            'type' => 'IN',
            'quantity' => 5,
            'description' => 'Restock gudang',
            'created_at' => '2026-03-12 08:00:00',
        ]);

        $response = $this->get('/api/stock-movements/export?start_date=2026-03-01&end_date=2026-03-31');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('Tanggal', $response->streamedContent());
        $this->assertStringContainsString('Dumbbell 5kg', $response->streamedContent());
        $this->assertStringContainsString('IN', $response->streamedContent());
    }
}
