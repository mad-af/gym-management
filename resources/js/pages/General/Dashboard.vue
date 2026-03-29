<template>
    <AdminLayout>
        <div class="space-y-6">
            <div
                class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"
            >
                <div
                    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <h1
                            class="text-xl font-semibold text-gray-900 dark:text-white"
                        >
                            Dashboard Analytics Gym
                        </h1>
                        <p
                            class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                        >
                            Ringkasan performa operasional, revenue, dan
                            aktivitas terbaru.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-70 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800"
                        :disabled="loading"
                        @click="fetchDashboard"
                    >
                        <RefreshIcon
                            class="h-4 w-4"
                            :class="loading ? 'animate-spin' : ''"
                        />
                        <span>{{
                            loading ? 'Memuat...' : 'Refresh Data'
                        }}</span>
                    </button>
                </div>

                <p
                    v-if="lastUpdatedLabel"
                    class="mt-3 text-xs text-gray-400 dark:text-gray-500"
                >
                    Diperbarui: {{ lastUpdatedLabel }}
                </p>
            </div>

            <div
                v-if="errorMessage"
                class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-900/70 dark:bg-rose-900/20 dark:text-rose-300"
            >
                {{ errorMessage }}
            </div>

            <Stats2 :items="statsItems" />

            <div class="grid grid-cols-12 gap-4 md:gap-6">
                <div class="col-span-12 xl:col-span-8">
                    <GymRevenueTrendChart
                        title="Tren Pendapatan 7 Hari"
                        :labels="dashboardData.revenueTrend.labels"
                        :series="dashboardData.revenueTrend.series"
                    />
                </div>

                <div class="col-span-12 xl:col-span-4">
                    <GymVisitDistributionCard
                        title="Komposisi Kunjungan Bulan Ini"
                        :labels="dashboardData.visitDistribution.labels"
                        :series="dashboardData.visitDistribution.series"
                    />
                </div>

                <div class="col-span-12 md:col-span-6 xl:col-span-4">
                    <GymInsightListCard
                        title="Produk Terlaris (30 Hari)"
                        subtitle="Berdasarkan jumlah item terjual"
                        :items="topProductItems"
                        empty-text="Belum ada transaksi penjualan."
                    />
                </div>

                <div class="col-span-12 md:col-span-6 xl:col-span-4">
                    <GymInsightListCard
                        title="Membership Akan Berakhir"
                        subtitle="Monitoring masa aktif member"
                        :items="expiringMembershipItems"
                        empty-text="Belum ada membership yang mendekati masa berakhir."
                    />
                </div>

                <div class="col-span-12 xl:col-span-4">
                    <GymActivityFeed
                        title="Aktivitas Terkini"
                        :items="activityItems"
                    />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import axios from 'axios';
import { computed, onMounted, ref } from 'vue';
import GymActivityFeed from '@/components/analytics/GymActivityFeed.vue';
import GymInsightListCard from '@/components/analytics/GymInsightListCard.vue';
import GymRevenueTrendChart from '@/components/analytics/GymRevenueTrendChart.vue';
import GymVisitDistributionCard from '@/components/analytics/GymVisitDistributionCard.vue';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import Stats2 from '@/components/ui/Stats2.vue';
import {
    BanknoteIcon,
    DoorOpenIcon,
    RefreshIcon,
    UserCircleIcon,
    UserGroupIcon,
    WarningIcon,
} from '@/icons';

interface DashboardOverview {
    total_customers: number;
    active_members: number;
    visits_today: number;
    daily_visits_today: number;
    membership_visits_today: number;
    revenue_today: number;
    revenue_this_month: number;
    low_stock_products: number;
}

interface RevenueTrendSeries {
    name: string;
    data: number[];
}

interface RevenueTrend {
    labels: string[];
    series: RevenueTrendSeries[];
}

interface VisitDistribution {
    labels: string[];
    series: number[];
    total: number;
}

interface TopProduct {
    id: string;
    name: string;
    qty_sold: number;
    revenue: number;
    stock: number;
}

interface ExpiringMembership {
    id: string;
    customer_name: string;
    package_name: string;
    end_date_label: string;
    days_left: number;
}

interface RecentActivity {
    id: string;
    title: string;
    description: string;
    amount: number | null;
    type_label: string;
    type_class: string;
    time_label: string;
}

interface DashboardApiResponse {
    overview: DashboardOverview;
    revenue_trend: RevenueTrend;
    visit_distribution: VisitDistribution;
    top_products: TopProduct[];
    expiring_memberships: ExpiringMembership[];
    recent_activities: RecentActivity[];
}

interface DashboardState {
    overview: DashboardOverview;
    revenueTrend: RevenueTrend;
    visitDistribution: VisitDistribution;
    topProducts: TopProduct[];
    expiringMemberships: ExpiringMembership[];
    recentActivities: RecentActivity[];
}

const defaultDashboardState = (): DashboardState => ({
    overview: {
        total_customers: 0,
        active_members: 0,
        visits_today: 0,
        daily_visits_today: 0,
        membership_visits_today: 0,
        revenue_today: 0,
        revenue_this_month: 0,
        low_stock_products: 0,
    },
    revenueTrend: {
        labels: [],
        series: [],
    },
    visitDistribution: {
        labels: [],
        series: [],
        total: 0,
    },
    topProducts: [],
    expiringMemberships: [],
    recentActivities: [],
});

const dashboardData = ref<DashboardState>(defaultDashboardState());
const loading = ref(false);
const errorMessage = ref('');
const lastUpdatedAt = ref<Date | null>(null);

const formatCurrencyId = (value: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    })
        .format(value)
        .replace('Rp', 'Rp ');
};

const formatCurrencyCompactId = (value: number): string => {
    const abs = Math.abs(value);
    const formatter = new Intl.NumberFormat('id-ID', {
        maximumFractionDigits: 1,
        minimumFractionDigits: 0,
    });

    if (abs >= 1_000_000_000_000)
        return `Rp ${formatter.format(value / 1_000_000_000_000)}T`;
    if (abs >= 1_000_000_000)
        return `Rp ${formatter.format(value / 1_000_000_000)}M`;
    if (abs >= 1_000_000) return `Rp ${formatter.format(value / 1_000_000)}Jt`;
    if (abs >= 1_000) return `Rp ${formatter.format(value / 1_000)}Rb`;

    return formatCurrencyId(value);
};

const lastUpdatedLabel = computed(() => {
    if (!lastUpdatedAt.value) {
        return '';
    }

    return lastUpdatedAt.value.toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
});

const statsItems = computed(() => {
    const overview = dashboardData.value.overview;

    return [
        {
            label: 'Total Pelanggan',
            value: overview.total_customers,
            icon: UserCircleIcon,
            iconBgClass:
                'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
        },
        {
            label: 'Member Aktif',
            value: overview.active_members,
            icon: UserGroupIcon,
            iconBgClass:
                'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10',
        },
        {
            label: 'Kunjungan Hari Ini',
            value: overview.visits_today,
            icon: DoorOpenIcon,
            iconBgClass: 'bg-blue-50 text-blue-600 dark:bg-blue-500/10',
        },
        {
            label: 'Omzet Hari Ini',
            value: formatCurrencyCompactId(overview.revenue_today),
            icon: BanknoteIcon,
            iconBgClass: 'bg-violet-50 text-violet-600 dark:bg-violet-500/10',
            detail: formatCurrencyId(overview.revenue_today),
        },
        {
            label: 'Omzet Bulan Ini',
            value: formatCurrencyCompactId(overview.revenue_this_month),
            icon: BanknoteIcon,
            iconBgClass: 'bg-amber-50 text-amber-600 dark:bg-amber-500/10',
            detail: formatCurrencyId(overview.revenue_this_month),
        },
        {
            label: 'Produk Stok Rendah',
            value: overview.low_stock_products,
            icon: WarningIcon,
            iconBgClass: 'bg-rose-50 text-rose-600 dark:bg-rose-500/10',
        },
    ];
});

const topProductItems = computed(() => {
    return dashboardData.value.topProducts.map((product) => {
        const isLowStock = product.stock <= 5;

        return {
            id: product.id,
            title: product.name,
            description: `${product.qty_sold} item terjual`,
            valueLabel: formatCurrencyId(product.revenue),
            badgeLabel: `Stok ${product.stock}`,
            badgeClass: isLowStock
                ? 'bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300'
                : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
        };
    });
});

const expiringMembershipItems = computed(() => {
    return dashboardData.value.expiringMemberships.map((membership) => {
        const isUrgent = membership.days_left <= 2;

        return {
            id: membership.id,
            title: membership.customer_name,
            description: `${membership.package_name} • Berakhir ${membership.end_date_label}`,
            badgeLabel: `${membership.days_left} hari lagi`,
            badgeClass: isUrgent
                ? 'bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300'
                : 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
        };
    });
});

const activityItems = computed(() => {
    return dashboardData.value.recentActivities.map((activity) => {
        return {
            id: activity.id,
            title: activity.title,
            description: activity.description,
            typeLabel: activity.type_label,
            typeClass: activity.type_class,
            timeLabel: activity.time_label,
            amountLabel:
                activity.amount !== null
                    ? formatCurrencyId(activity.amount)
                    : undefined,
        };
    });
});

const fetchDashboard = async () => {
    loading.value = true;
    errorMessage.value = '';

    try {
        const response = await axios.get('/api/dashboard/stats');
        const data = (response.data?.data ??
            {}) as Partial<DashboardApiResponse>;

        dashboardData.value = {
            overview: data.overview ?? defaultDashboardState().overview,
            revenueTrend:
                data.revenue_trend ?? defaultDashboardState().revenueTrend,
            visitDistribution:
                data.visit_distribution ??
                defaultDashboardState().visitDistribution,
            topProducts: data.top_products ?? [],
            expiringMemberships: data.expiring_memberships ?? [],
            recentActivities: data.recent_activities ?? [],
        };

        lastUpdatedAt.value = new Date();
    } catch (error) {
        dashboardData.value = defaultDashboardState();
        errorMessage.value =
            'Gagal memuat data dashboard. Silakan coba lagi beberapa saat.';
        console.error('Error fetching dashboard analytics:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchDashboard();
});
</script>
