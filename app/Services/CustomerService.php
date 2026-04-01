<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\MembershipTransaction;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    private function generateNumericCode(int $sequenceLength = 4): string
    {
        $prefix = now()->format('ym'); // tahun + bulan (2603)

        $lastCode = Customer::query()
            ->where('code', 'like', $prefix.'%')
            ->orderByDesc('code')
            ->value('code');

        if ($lastCode) {
            $lastSequence = (int) substr($lastCode, -$sequenceLength);
            $nextSequence = $lastSequence + 1;
        } else {
            $nextSequence = 1;
        }

        $sequence = str_pad((string) $nextSequence, $sequenceLength, '0', STR_PAD_LEFT);

        return $prefix.$sequence;
    }

    public function getAll(
        int $perPage = 10,
        ?string $search = null,
        int $page = 1,
        ?bool $isMember = null
    ): LengthAwarePaginator {
        $now = Carbon::now()->startOfDay();
        $query = Customer::query()
            ->with([
                'membershipTransactions' => function ($q) use ($now) {
                    $q->whereDate('start_date', '<=', $now)
                        ->whereDate('end_date', '>=', $now)
                        ->with('package');
                },
                'media' => function ($q) {
                    $q->where('collection', 'avatar');
                },
            ])
            ->latest('created_at');

        if ($isMember === true) {
            $query->whereHas('membershipTransactions', function ($q) use ($now) {
                $q->where('status', 'active')
                    ->whereDate('start_date', '<=', $now)
                    ->whereDate('end_date', '>=', $now);
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function getSelection(
        int $perPage = 20,
        ?string $search = null,
        int $page = 1,
        ?bool $isMember = null
    ): LengthAwarePaginator {
        $now = Carbon::now()->startOfDay();
        $query = Customer::query()->latest('created_at');

        $activeMembershipConstraint = function ($membershipQuery) use ($now) {
            $membershipQuery
                ->where(function ($statusQuery) {
                    $statusQuery
                        ->where('status', 'active')
                        ->orWhere('status', 'ACTIVE');
                })
                ->whereDate('start_date', '<=', $now)
                ->whereDate('end_date', '>=', $now);
        };

        if ($isMember === true) {
            $query->whereHas('membershipTransactions', $activeMembershipConstraint);
        }

        if ($isMember === false) {
            $query->whereDoesntHave('membershipTransactions', $activeMembershipConstraint);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return $query->select(['id', 'name', 'code', 'phone'])->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $data): Customer
    {
        return DB::transaction(function () use ($data) {
            if (! isset($data['code']) || $data['code'] === '') {
                $data['code'] = $this->generateNumericCode();
            }

            // $shouldGenerateAvatar = ! (isset($data['avatar']) && $data['avatar'] instanceof \Illuminate\Http\UploadedFile);
            // unset($data['avatar']);

            $customer = Customer::create($data);

            // if ($shouldGenerateAvatar) {
            try {
                $generator = new AvatarGenerator;
                $generator->generateAndSave($customer->name, 'svg', $customer);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Customer avatar generation failed: '.$e->getMessage());
            }
            // }

            return $customer->load(['media' => function ($q) {
                $q->where('collection', 'avatar');
            }]);
        });
    }

    public function update(Customer $customer, array $data): Customer
    {
        unset($data['avatar']);
        $customer->update($data);

        return $customer->refresh()->load(['media' => function ($q) {
            $q->where('collection', 'avatar');
        }]);
    }

    public function delete(Customer $customer): bool
    {
        return (bool) $customer->delete();
    }

    public function getStats(): array
    {
        $today = Carbon::today();
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth = $today->copy()->endOfMonth();

        $totalCustomers = Customer::query()->count();

        $activeMembers = Customer::query()
            ->whereHas('membershipTransactions', function ($q) use ($today) {
                $q->whereDate('start_date', '<=', $today)
                    ->whereDate('end_date', '>=', $today)
                    ->where('status', 'active');
            })
            ->count();

        $activeThisMonth = MembershipTransaction::query()
            ->where('status', 'active')
            ->whereDate('start_date', '>=', $startOfMonth)
            ->whereDate('start_date', '<=', $endOfMonth)
            ->distinct('customer_id')
            ->count('customer_id');

        $visitsToday = Visit::query()
            ->notCancelled()
            ->whereDate('checkin_time', $today)
            ->count();

        return [
            'total' => $totalCustomers,
            'activeThisMonth' => $activeThisMonth,
            'activeMembers' => $activeMembers,
            'visitsToday' => $visitsToday,
        ];
    }
}
