<?php

namespace Tests\Feature;

use App\Jobs\SendMembershipExpiryReminderJob;
use App\Models\Customer;
use App\Models\MembershipPackage;
use App\Models\MembershipTransaction;
use App\Services\WhatsappConfigService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class MembershipExpiryReminderTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Mockery::close();
        parent::tearDown();
    }

    public function test_command_dispatches_h_minus_3_and_h_day_jobs_once_per_target(): void
    {
        Carbon::setTestNow('2026-03-17 08:00:00');
        Cache::flush();
        Bus::fake();

        $package = MembershipPackage::create([
            'name' => 'Paket Bulanan',
            'duration_days' => 30,
            'price' => 150000,
        ]);

        $customerToday = Customer::create([
            'name' => 'Budi',
            'code' => 'MEM-101',
            'phone' => '081111111111',
        ]);

        $customerThreeDays = Customer::create([
            'name' => 'Siti',
            'code' => 'MEM-102',
            'phone' => '082222222222',
        ]);

        $inactiveCustomer = Customer::create([
            'name' => 'Tono',
            'code' => 'MEM-103',
            'phone' => '083333333333',
        ]);

        $todayTransaction = MembershipTransaction::create([
            'customer_id' => $customerToday->id,
            'package_id' => $package->id,
            'start_date' => '2026-03-01',
            'end_date' => '2026-03-17',
            'price' => 150000,
            'status' => 'active',
        ]);

        $h3Transaction = MembershipTransaction::create([
            'customer_id' => $customerThreeDays->id,
            'package_id' => $package->id,
            'start_date' => '2026-03-01',
            'end_date' => '2026-03-20',
            'price' => 150000,
            'status' => 'ACTIVE',
        ]);

        $inactiveTransaction = MembershipTransaction::create([
            'customer_id' => $inactiveCustomer->id,
            'package_id' => $package->id,
            'start_date' => '2026-03-01',
            'end_date' => '2026-03-20',
            'price' => 150000,
            'status' => 'inactive',
        ]);

        $this->artisan('membership:check-expiry-reminders')->assertExitCode(0);
        $this->artisan('membership:check-expiry-reminders')->assertExitCode(0);

        Bus::assertDispatchedTimes(SendMembershipExpiryReminderJob::class, 2);

        Bus::assertDispatched(SendMembershipExpiryReminderJob::class, function (SendMembershipExpiryReminderJob $job) use ($todayTransaction): bool {
            return $job->membershipTransactionId === $todayTransaction->id && $job->daysBefore === 0;
        });

        Bus::assertDispatched(SendMembershipExpiryReminderJob::class, function (SendMembershipExpiryReminderJob $job) use ($h3Transaction): bool {
            return $job->membershipTransactionId === $h3Transaction->id && $job->daysBefore === 3;
        });

        Bus::assertNotDispatched(SendMembershipExpiryReminderJob::class, function (SendMembershipExpiryReminderJob $job) use ($inactiveTransaction): bool {
            return $job->membershipTransactionId === $inactiveTransaction->id;
        });
    }

    public function test_job_sends_whatsapp_message_for_matching_h_minus_3_membership(): void
    {
        Carbon::setTestNow('2026-03-17 08:00:00');

        $package = MembershipPackage::create([
            'name' => 'Paket 1 Bulan',
            'duration_days' => 30,
            'price' => 100000,
        ]);

        $customer = Customer::create([
            'name' => 'Rina',
            'code' => 'MEM-201',
            'phone' => '081234567890',
        ]);

        $transaction = MembershipTransaction::create([
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'start_date' => '2026-03-01',
            'end_date' => '2026-03-20',
            'price' => 100000,
            'status' => 'active',
        ]);

        $whatsappService = Mockery::mock(WhatsappConfigService::class);
        $whatsappService
            ->shouldReceive('sendMessage')
            ->once()
            ->with(
                '081234567890',
                Mockery::on(fn (string $message): bool => str_contains($message, 'Rina') && str_contains($message, 'MEM-201') && str_contains($message, 'dalam 3 hari'))
            )
            ->andReturn(['status' => true]);

        $job = new SendMembershipExpiryReminderJob($transaction->id, 3);
        $job->handle($whatsappService);

        $this->assertTrue(true);
    }
}
