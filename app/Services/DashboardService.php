<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\MembershipTransaction;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\Visit;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class DashboardService
{
    public function getGymAnalytics(): array
    {
        $today = Carbon::today();
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();

        $dailyVisitRevenueToday = (float) Visit::query()
            ->where('visit_type', 'DAILY')
            ->whereDate('checkin_time', $today)
            ->sum('price');

        $salesRevenueToday = (float) Sale::query()
            ->notCancelled()
            ->whereDate('created_at', $today)
            ->sum('total_amount');

        $membershipRevenueToday = (float) MembershipTransaction::query()
            ->notCancelled()
            ->whereDate('created_at', $today)
            ->sum('price');

        $dailyVisitRevenueMonth = (float) Visit::query()
            ->where('visit_type', 'DAILY')
            ->whereDate('checkin_time', '>=', $monthStart)
            ->whereDate('checkin_time', '<=', $monthEnd)
            ->sum('price');

        $salesRevenueMonth = (float) Sale::query()
            ->notCancelled()
            ->whereDate('created_at', '>=', $monthStart)
            ->whereDate('created_at', '<=', $monthEnd)
            ->sum('total_amount');

        $membershipRevenueMonth = (float) MembershipTransaction::query()
            ->notCancelled()
            ->whereDate('created_at', '>=', $monthStart)
            ->whereDate('created_at', '<=', $monthEnd)
            ->sum('price');

        $salesProfitToday = (float) SaleItem::query()
            ->whereNotNull('capital_price')
            ->whereHas('sale', function ($query) use ($today) {
                $query->notCancelled()->whereDate('created_at', $today);
            })
            ->selectRaw('COALESCE(SUM((price - capital_price) * quantity), 0) as profit')
            ->value('profit');

        $salesProfitMonth = (float) SaleItem::query()
            ->whereNotNull('capital_price')
            ->whereHas('sale', function ($query) use ($monthStart, $monthEnd) {
                $query->notCancelled()
                    ->whereDate('created_at', '>=', $monthStart)
                    ->whereDate('created_at', '<=', $monthEnd);
            })
            ->selectRaw('COALESCE(SUM((price - capital_price) * quantity), 0) as profit')
            ->value('profit');

        return [
            'overview' => [
                'total_customers' => Customer::query()->count(),
                'active_members' => MembershipTransaction::query()
                    ->notCancelled()
                    ->where('status', 'active')
                    ->whereDate('start_date', '<=', $today)
                    ->whereDate('end_date', '>=', $today)
                    ->distinct('customer_id')
                    ->count('customer_id'),
                'visits_today' => Visit::query()
                    ->whereDate('checkin_time', $today)
                    ->count(),
                'daily_visits_today' => Visit::query()
                    ->where('visit_type', 'DAILY')
                    ->whereDate('checkin_time', $today)
                    ->count(),
                'membership_visits_today' => Visit::query()
                    ->where('visit_type', 'MEMBERSHIP')
                    ->whereDate('checkin_time', $today)
                    ->count(),
                'revenue_today' => $dailyVisitRevenueToday + $salesRevenueToday + $membershipRevenueToday,
                'revenue_this_month' => $dailyVisitRevenueMonth + $salesRevenueMonth + $membershipRevenueMonth,
                'profit_today' => $salesProfitToday,
                'profit_this_month' => $salesProfitMonth,
                'low_stock_products' => Product::query()
                    ->where('stock', '<=', 5)
                    ->count(),
                'expiring_memberships_7_days' => MembershipTransaction::query()
                    ->notCancelled()
                    ->where('status', 'active')
                    ->whereDate('end_date', '>=', $today)
                    ->whereDate('end_date', '<=', $today->copy()->addDays(7))
                    ->count(),
            ],
            'revenue_trend' => $this->getRevenueTrend(),
            'visit_distribution' => $this->getVisitDistribution(),
            'top_products' => $this->getTopProducts(),
            'expiring_memberships' => $this->getExpiringMemberships(),
            'recent_activities' => $this->getRecentActivities(),
        ];
    }

    public function getOperationsToday(): array
    {
        $today = Carbon::today();

        return [
            'visits' => [
                'count' => Visit::query()
                    ->whereDate('checkin_time', $today)
                    ->count(),
                'memberCount' => Visit::query()
                    ->where('visit_type', 'MEMBERSHIP')
                    ->whereDate('checkin_time', $today)
                    ->count(),
                'dailyRevenue' => (float) Visit::query()
                    ->where('visit_type', 'DAILY')
                    ->whereDate('checkin_time', $today)
                    ->sum('price'),
            ],
            'sales' => [
                'count' => Sale::query()
                    ->notCancelled()
                    ->whereDate('created_at', $today)
                    ->count(),
                'revenue' => (float) Sale::query()
                    ->notCancelled()
                    ->whereDate('created_at', $today)
                    ->sum('total_amount'),
                'profit' => (float) SaleItem::query()
                    ->whereNotNull('capital_price')
                    ->whereHas('sale', function ($query) use ($today) {
                        $query->notCancelled()->whereDate('created_at', $today);
                    })
                    ->selectRaw('COALESCE(SUM((price - capital_price) * quantity), 0) as profit')
                    ->value('profit'),
            ],
            'memberships' => [
                'count' => MembershipTransaction::query()
                    ->notCancelled()
                    ->whereDate('created_at', $today)
                    ->count(),
                'revenue' => (float) MembershipTransaction::query()
                    ->notCancelled()
                    ->whereDate('created_at', $today)
                    ->sum('price'),
            ],
            'stockMovements' => [
                'inQuantity' => (int) StockMovement::query()
                    ->where('type', 'IN')
                    ->whereDate('created_at', $today)
                    ->sum('quantity'),
                'outQuantity' => (int) StockMovement::query()
                    ->where('type', 'OUT')
                    ->whereDate('created_at', $today)
                    ->sum('quantity'),
            ],
        ];
    }

    private function getRevenueTrend(int $days = 7): array
    {
        $endDate = Carbon::today();
        $startDate = $endDate->copy()->subDays(max(1, $days) - 1);

        $salesByDate = Sale::query()
            ->notCancelled()
            ->selectRaw('DATE(created_at) as day, COALESCE(SUM(total_amount), 0) as total')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->groupBy('day')
            ->pluck('total', 'day');

        $membershipByDate = MembershipTransaction::query()
            ->notCancelled()
            ->selectRaw('DATE(created_at) as day, COALESCE(SUM(price), 0) as total')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->groupBy('day')
            ->pluck('total', 'day');

        $visitByDate = Visit::query()
            ->selectRaw('DATE(checkin_time) as day, COALESCE(SUM(price), 0) as total')
            ->where('visit_type', 'DAILY')
            ->whereDate('checkin_time', '>=', $startDate)
            ->whereDate('checkin_time', '<=', $endDate)
            ->groupBy('day')
            ->pluck('total', 'day');

        $labels = [];
        $salesSeries = [];
        $membershipSeries = [];
        $visitSeries = [];

        foreach (CarbonPeriod::create($startDate, $endDate) as $day) {
            $dayKey = $day->toDateString();
            $labels[] = $day->format('d M');
            $salesSeries[] = (float) ($salesByDate[$dayKey] ?? 0);
            $membershipSeries[] = (float) ($membershipByDate[$dayKey] ?? 0);
            $visitSeries[] = (float) ($visitByDate[$dayKey] ?? 0);
        }

        return [
            'labels' => $labels,
            'series' => [
                [
                    'name' => 'Penjualan Produk',
                    'data' => $salesSeries,
                ],
                [
                    'name' => 'Membership Baru',
                    'data' => $membershipSeries,
                ],
                [
                    'name' => 'Visit Harian',
                    'data' => $visitSeries,
                ],
            ],
        ];
    }

    private function getVisitDistribution(): array
    {
        $monthStart = Carbon::today()->startOfMonth();
        $monthEnd = Carbon::today()->endOfMonth();

        $dailyVisits = Visit::query()
            ->where('visit_type', 'DAILY')
            ->whereDate('checkin_time', '>=', $monthStart)
            ->whereDate('checkin_time', '<=', $monthEnd)
            ->count();

        $membershipVisits = Visit::query()
            ->where('visit_type', 'MEMBERSHIP')
            ->whereDate('checkin_time', '>=', $monthStart)
            ->whereDate('checkin_time', '<=', $monthEnd)
            ->count();

        return [
            'labels' => ['Visit Harian', 'Visit Membership'],
            'series' => [$dailyVisits, $membershipVisits],
            'total' => $dailyVisits + $membershipVisits,
        ];
    }

    private function getTopProducts(int $limit = 5): array
    {
        $startDate = Carbon::today()->subDays(30);

        return SaleItem::query()
            ->select('product_id')
            ->selectRaw('COALESCE(SUM(quantity), 0) as qty_sold')
            ->selectRaw('COALESCE(SUM(subtotal), 0) as revenue')
            ->whereHas('sale', function ($query) use ($startDate) {
                $query->notCancelled()->whereDate('created_at', '>=', $startDate);
            })
            ->with(['product:id,name,stock'])
            ->groupBy('product_id')
            ->orderByDesc('qty_sold')
            ->limit($limit)
            ->get()
            ->map(function (SaleItem $item): array {
                return [
                    'id' => $item->product_id,
                    'name' => $item->product?->name ?? '-',
                    'qty_sold' => (int) $item->qty_sold,
                    'revenue' => (float) $item->revenue,
                    'stock' => (int) ($item->product?->stock ?? 0),
                ];
            })
            ->all();
    }

    private function getExpiringMemberships(int $daysAhead = 7, int $limit = 5): array
    {
        $today = Carbon::today();
        $maxDate = $today->copy()->addDays(max(1, $daysAhead));

        return MembershipTransaction::query()
            ->notCancelled()
            ->with(['customer:id,name', 'package:id,name'])
            ->where('status', 'active')
            ->whereDate('end_date', '>=', $today)
            ->whereDate('end_date', '<=', $maxDate)
            ->orderBy('end_date')
            ->limit($limit)
            ->get()
            ->map(function (MembershipTransaction $transaction) use ($today): array {
                $endDate = Carbon::parse($transaction->end_date);

                return [
                    'id' => $transaction->id,
                    'customer_name' => $transaction->customer?->name ?? '-',
                    'package_name' => $transaction->package?->name ?? '-',
                    'end_date_label' => $endDate->format('d M Y'),
                    'days_left' => max(0, $today->diffInDays($endDate, false)),
                ];
            })
            ->all();
    }

    private function getRecentActivities(int $limit = 8): array
    {
        $visitActivities = Visit::query()
            ->with(['customer:id,name'])
            ->latest('checkin_time')
            ->limit($limit)
            ->get()
            ->map(function (Visit $visit): array {
                $checkedAt = Carbon::parse($visit->checkin_time);
                $isDaily = strtoupper((string) $visit->visit_type) === 'DAILY';

                return [
                    'id' => 'visit-'.$visit->id,
                    'title' => ($visit->customer?->name ?? 'Member').' check-in',
                    'description' => $isDaily ? 'Visit harian' : 'Visit membership',
                    'amount' => $isDaily ? (float) ($visit->price ?? 0) : null,
                    'type_label' => $isDaily ? 'DAILY' : 'MEMBER',
                    'type_class' => $isDaily
                        ? 'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300'
                        : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
                    'timestamp' => $checkedAt,
                    'time_label' => $checkedAt->diffForHumans(),
                ];
            });

        $saleActivities = Sale::query()
            ->notCancelled()
            ->with(['customer:id,name'])
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->map(function (Sale $sale): array {
                $createdAt = Carbon::parse($sale->created_at);

                return [
                    'id' => 'sale-'.$sale->id,
                    'title' => 'Penjualan #'.$this->shortId($sale->id),
                    'description' => 'Pelanggan: '.($sale->customer?->name ?? 'Umum'),
                    'amount' => (float) $sale->total_amount,
                    'type_label' => 'SALE',
                    'type_class' => 'bg-violet-50 text-violet-700 dark:bg-violet-500/15 dark:text-violet-300',
                    'timestamp' => $createdAt,
                    'time_label' => $createdAt->diffForHumans(),
                ];
            });

        $membershipActivities = MembershipTransaction::query()
            ->notCancelled()
            ->with(['customer:id,name', 'package:id,name'])
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->map(function (MembershipTransaction $transaction): array {
                $createdAt = Carbon::parse($transaction->created_at);

                return [
                    'id' => 'membership-'.$transaction->id,
                    'title' => 'Membership '.($transaction->customer?->name ?? '-'),
                    'description' => 'Paket: '.($transaction->package?->name ?? '-'),
                    'amount' => (float) $transaction->price,
                    'type_label' => 'MEMBER',
                    'type_class' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
                    'timestamp' => $createdAt,
                    'time_label' => $createdAt->diffForHumans(),
                ];
            });

        $stockActivities = StockMovement::query()
            ->with(['product:id,name'])
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->map(function (StockMovement $movement): array {
                $createdAt = Carbon::parse($movement->created_at);
                $type = strtoupper((string) $movement->type);

                return [
                    'id' => 'stock-'.$movement->id,
                    'title' => 'Stok '.$type.' '.($movement->product?->name ?? '-'),
                    'description' => 'Jumlah: '.(int) $movement->quantity,
                    'amount' => null,
                    'type_label' => $type,
                    'type_class' => $type === 'IN'
                        ? 'bg-cyan-50 text-cyan-700 dark:bg-cyan-500/15 dark:text-cyan-300'
                        : 'bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300',
                    'timestamp' => $createdAt,
                    'time_label' => $createdAt->diffForHumans(),
                ];
            });

        return collect()
            ->concat($visitActivities)
            ->concat($saleActivities)
            ->concat($membershipActivities)
            ->concat($stockActivities)
            ->sortByDesc('timestamp')
            ->take($limit)
            ->values()
            ->map(function (array $activity): array {
                unset($activity['timestamp']);

                return $activity;
            })
            ->all();
    }

    private function shortId(string $uuid): string
    {
        return strtoupper(substr(str_replace('-', '', $uuid), 0, 8));
    }
}
