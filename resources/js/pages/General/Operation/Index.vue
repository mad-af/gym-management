<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="currentPageTitle" />
        <div class="space-y-6">
            <Stats2 :items="statsItems" />
            <div
                v-if="
                    canView(VISIT_PERMISSIONS.CREATE) ||
                    canView(SALE_PERMISSIONS.CREATE) ||
                    canView(MEMBERSHIP_TRANSACTION_PERMISSIONS.CREATE) ||
                    canView(STOCK_MOVEMENT_PERMISSIONS.CREATE)
                "
                class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900"
            >
                <div
                    class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between"
                >
                    <div>
                        <h2
                            class="text-lg font-semibold text-gray-900 dark:text-white"
                        >
                            Input Data Utama
                        </h2>
                        <p
                            class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                        >
                            Akses cepat untuk fitur operasional harian.
                        </p>
                    </div>
                </div>
                <div
                    class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-2"
                >
                    <VisitsOperation
                        v-if="canView(VISIT_PERMISSIONS.CREATE)"
                        @submitted="fetchOpsStats"
                    />
                    <SalesOperation
                        v-if="canView(SALE_PERMISSIONS.CREATE)"
                        @submitted="fetchOpsStats"
                    />
                    <MembershipOperation
                        v-if="
                            canView(MEMBERSHIP_TRANSACTION_PERMISSIONS.CREATE)
                        "
                        @submitted="fetchOpsStats"
                    />
                    <MembershipCardOperation
                        v-if="
                            canView(MEMBERSHIP_TRANSACTION_PERMISSIONS.CREATE)
                        "
                        @submitted="fetchOpsStats"
                    />
                    <StockMovementOperation
                        v-if="canView(STOCK_MOVEMENT_PERMISSIONS.CREATE)"
                        @submitted="fetchOpsStats"
                    />
                </div>
            </div>

            <div
                v-if="
                    canView(VISIT_PERMISSIONS.CANCEL) ||
                    canView(MEMBERSHIP_TRANSACTION_PERMISSIONS.CANCEL) ||
                    canView(SALE_PERMISSIONS.CANCEL)
                "
                class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900"
            >
                <div
                    class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between"
                >
                    <div>
                        <h2
                            class="text-lg font-semibold text-gray-900 dark:text-white"
                        >
                            Pembatalan Transaksi
                        </h2>
                        <p
                            class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                        >
                            Batalkan transaksi yang sudah dibuat.
                        </p>
                    </div>
                </div>
                <div
                    class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-2"
                >
                    <VisitCancellationOperation
                        v-if="canView(VISIT_PERMISSIONS.CANCEL)"
                        @submitted="fetchOpsStats"
                    />
                    <MembershipCancellationOperation
                        v-if="
                            canView(MEMBERSHIP_TRANSACTION_PERMISSIONS.CANCEL)
                        "
                        @submitted="fetchOpsStats"
                    />
                    <SalesCancellationOperation
                        v-if="canView(SALE_PERMISSIONS.CANCEL)"
                        @submitted="fetchOpsStats"
                    />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import Stats2 from '@/components/ui/Stats2.vue';
import {
    MEMBERSHIP_TRANSACTION_PERMISSIONS,
    SALE_PERMISSIONS,
    STOCK_MOVEMENT_PERMISSIONS,
    VISIT_PERMISSIONS,
} from '@/directives/permissions';
import { CalenderIcon } from '@/icons';
import MembershipCancellationOperation from './components/MembershipCancellationOperation.vue';
import MembershipCardOperation from './components/MembershipCardOperation.vue';
import MembershipOperation from './components/MembershipOperation.vue';
import SalesCancellationOperation from './components/SalesCancellationOperation.vue';
import SalesOperation from './components/SalesOperation.vue';
import StockMovementOperation from './components/StockMovementOperation.vue';
import VisitCancellationOperation from './components/VisitCancellationOperation.vue';
import VisitsOperation from './components/VisitsOperation.vue';

const currentPageTitle = ref('Operasional');
const page = usePage();

const now = ref(new Date());
let timer: number | null = null;

const dateText = computed(() =>
    now.value.toLocaleDateString('id-ID', {
        weekday: 'long',
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    }),
);

const timeText = computed(
    () =>
        `${now.value.toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
        })} WIB`,
);

const opsStats = ref({
    visits: { count: 0, memberCount: 0, dailyRevenue: 0 },
    sales: { count: 0, revenue: 0 },
    memberships: { count: 0, revenue: 0 },
    stockMovements: { inQuantity: 0, outQuantity: 0 },
});

const formatCompactNumberId = (value: number): string => {
    const n = Number.isFinite(value) ? value : 0;
    if (n < 1000) return n.toString();
    const units = [
        { v: 1e12, s: 'T' },
        { v: 1e9, s: 'M' },
        { v: 1e6, s: 'jt' },
        { v: 1e3, s: 'rb' },
    ];
    for (const u of units) {
        if (n >= u.v) {
            const num = n / u.v;
            const formatted = num % 1 === 0 ? num.toString() : num.toFixed(1);
            return `${formatted}${u.s}`;
        }
    }
    return n.toString();
};

const formatCurrencyCompactId = (value: unknown): string => {
    const n = typeof value === 'number' ? value : Number(value) || 0;
    return `Rp ${formatCompactNumberId(n)}`;
};

const formatCurrencyId = (value: unknown): string => {
    const n = typeof value === 'number' ? value : Number(value) || 0;
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    })
        .format(n)
        .replace('Rp', 'Rp ');
};

const statsItems = computed(() => [
    {
        label: dateText.value,
        value: timeText.value,
        icon: CalenderIcon,
        iconBgClass: 'bg-brand-50 text-brand-500 dark:bg-brand-500/10',
    },
    {
        label: 'Kunjungan Hari Ini',
        value: opsStats.value.visits.count,
        icon: CalenderIcon,
        iconBgClass: 'bg-brand-50 text-brand-600 dark:bg-brand-500/10',
    },
    {
        label: 'Omzet Penjualan',
        value: formatCurrencyCompactId(opsStats.value.sales.revenue),
        icon: CalenderIcon,
        iconBgClass: 'bg-blue-50 text-blue-600 dark:bg-blue-500/10',
        detail: formatCurrencyId(opsStats.value.sales.revenue),
    },
    {
        label: 'Omzet Membership',
        value: formatCurrencyCompactId(opsStats.value.memberships.revenue),
        icon: CalenderIcon,
        iconBgClass: 'bg-success-50 text-success-700 dark:bg-success-500/10',
        detail: formatCurrencyId(opsStats.value.memberships.revenue),
    },
    {
        label: 'Stok IN/OUT',
        value: `IN ${opsStats.value.stockMovements.inQuantity} / OUT ${opsStats.value.stockMovements.outQuantity}`,
        icon: CalenderIcon,
        iconBgClass: 'bg-purple-50 text-purple-600 dark:bg-purple-500/10',
    },
]);

const userPermissions = computed<string[]>(() => {
    const props: any = page.props;
    return props?.auth?.permissions ?? [];
});

const canView = (permission?: string | string[]) => {
    if (!permission) return true;
    const permissions = userPermissions.value;
    if (!permissions || permissions.length === 0) return false;
    if (Array.isArray(permission))
        return permission.some((p) => permissions.includes(p));
    return permissions.includes(permission);
};

onMounted(() => {
    timer = window.setInterval(() => {
        now.value = new Date();
    }, 1000);
    fetchOpsStats();
});

onUnmounted(() => {
    if (timer) {
        window.clearInterval(timer);
        timer = null;
    }
});

const fetchOpsStats = async () => {
    const { data } = await axios.get('/api/operations/stats-today');
    const s = data?.data || data;
    opsStats.value = {
        visits: {
            count: s?.visits?.count ?? 0,
            memberCount: s?.visits?.memberCount ?? 0,
            dailyRevenue: s?.visits?.dailyRevenue ?? 0,
        },
        sales: {
            count: s?.sales?.count ?? 0,
            revenue: s?.sales?.revenue ?? 0,
        },
        memberships: {
            count: s?.memberships?.count ?? 0,
            revenue: s?.memberships?.revenue ?? 0,
        },
        stockMovements: {
            inQuantity: s?.stockMovements?.inQuantity ?? 0,
            outQuantity: s?.stockMovements?.outQuantity ?? 0,
        },
    };
};
</script>
