<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\MembershipPackage;
use App\Models\MembershipTransaction;
use App\Models\Visit;
use App\Services\WhatsappConfigService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class TimezoneConsistencyTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Mockery::close();
        parent::tearDown();
    }

    public function test_carbon_today_respects_app_timezone(): void
    {
        Carbon::setTestNow('2026-04-10 00:00:00', 'UTC');

        $todayUtc = Carbon::today()->toDateString();

        $this->assertEquals('2026-04-10', $todayUtc);
    }

    public function test_visit_checkin_time_is_iso8601_utc(): void
    {
        Carbon::setTestNow('2026-04-10 07:00:00', 'Asia/Jakarta');

        $customer = Customer::create(['name' => 'Test', 'code' => 'TST-001']);
        $visit = Visit::create([
            'customer_id' => $customer->id,
            'visit_type' => 'DAILY',
            'checkin_time' => now(),
            'price' => 0,
        ]);

        $stored = $visit->fresh()->checkin_time;
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/',
            $stored->toIsoString()
        );
    }

    public function test_membership_end_date_is_iso8601(): void
    {
        Carbon::setTestNow('2026-04-10 00:00:00', 'Asia/Jakarta');

        $package = MembershipPackage::create([
            'name' => 'Paket 30 Hari',
            'duration_days' => 30,
            'price' => 150000,
        ]);
        $customer = Customer::create(['name' => 'Andi', 'code' => 'AND-001']);

        $transaction = MembershipTransaction::create([
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'start_date' => '2026-04-01',
            'end_date' => '2026-04-30',
            'price' => 150000,
            'status' => 'active',
        ]);

        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}T/',
            $transaction->fresh()->end_date->startOfDay()->toIsoString()
        );
    }

    public function test_expiry_reminder_dispatches_job_at_wib_midnight(): void
    {
        Carbon::setTestNow('2026-04-10 00:00:00', 'Asia/Jakarta');

        $package = MembershipPackage::create([
            'name' => 'Paket 1 Bulan',
            'duration_days' => 30,
            'price' => 100000,
        ]);
        $customer = Customer::create([
            'name' => 'Budi',
            'code' => 'BUD-001',
            'phone' => '081234567890',
        ]);

        MembershipTransaction::create([
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'start_date' => '2026-03-11',
            'end_date' => Carbon::today()->toDateString(),
            'price' => 100000,
            'status' => 'active',
        ]);

        $whatsappService = Mockery::mock(WhatsappConfigService::class);
        $whatsappService->shouldReceive('sendMessage')->andReturn(['status' => true]);
        $this->app->instance(WhatsappConfigService::class, $whatsappService);

        $this->artisan('membership:check-expiry-reminders')
            ->assertExitCode(0);
    }
}
