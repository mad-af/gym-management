<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="currentPageTitle" />

        <div class="space-y-6">
            <Stats2 :items="statsItems" />

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Input Data Utama
                        </h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Akses cepat untuk fitur operasional harian.
                        </p>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <Link v-for="action in visibleActions" :key="action.name" :href="action.href"
                        class="group relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 transition hover:border-gray-300 hover:shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:hover:border-gray-700">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl"
                                :class="action.iconBgClass">
                                <component :is="action.icon" class="h-6 w-6" />
                            </div>
                            <ArrowRightIcon
                                class="h-5 w-5 text-gray-300 transition group-hover:text-gray-400 dark:text-gray-600 dark:group-hover:text-gray-500" />
                        </div>

                        <div class="mt-4">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                                {{ action.name }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ action.description }}
                            </p>
                        </div>

                        <div class="pointer-events-none absolute -right-10 -top-10 h-28 w-28 rounded-full opacity-25 blur-2xl"
                            :class="action.glowClass" />
                    </Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
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
import {
    ArrowRightIcon,
    BanknoteIcon,
    CalenderIcon,
    DoorOpenIcon,
    GridIcon,
    PackageIcon,
} from '@/icons';

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

const timeText = computed(() =>
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

const statsItems = computed(() => {
    const items = [
        {
            label: 'Kunjungan Hari Ini',
            value: opsStats.value.visits.count,
            icon: DoorOpenIcon,
            iconBgClass: 'bg-brand-50 text-brand-600 dark:bg-brand-500/10',
        },
        {
            label: 'Omzet Penjualan',
            value: formatCurrencyCompactId(opsStats.value.sales.revenue),
            icon: BanknoteIcon,
            iconBgClass: 'bg-blue-50 text-blue-600 dark:bg-blue-500/10',
        },
        {
            label: 'Omzet Membership',
            value: formatCurrencyCompactId(opsStats.value.memberships.revenue),
            icon: PackageIcon,
            iconBgClass: 'bg-success-50 text-success-700 dark:bg-success-500/10',
        },
        {
            label: dateText.value,
            value: timeText.value,
            icon: CalenderIcon,
            iconBgClass: 'bg-brand-50 text-brand-500 dark:bg-brand-500/10',
        },
    ];
    return items;
});

const userPermissions = computed<string[]>(() => {
    const props: any = page.props;
    return props?.auth?.permissions ?? [];
});

const hasPermission = (permission?: string | string[]) => {
    if (!permission) {
        return true;
    }

    const permissions = userPermissions.value;
    if (!permissions || permissions.length === 0) {
        return false;
    }

    if (Array.isArray(permission)) {
        return permission.some((p) => permissions.includes(p));
    }

    return permissions.includes(permission);
};

type OperationAction = {
    name: string;
    description: string;
    href: string;
    icon: any;
    iconBgClass: string;
    glowClass: string;
    permission?: string | string[];
};

const actions = computed<OperationAction[]>(() => [
    {
        name: 'Visits / Check In',
        description: 'Input kunjungan harian dan check-in member.',
        href: '/visits',
        icon: DoorOpenIcon,
        iconBgClass: 'bg-brand-50 text-brand-600 dark:bg-brand-500/10',
        glowClass: 'bg-brand-500',
        permission: VISIT_PERMISSIONS.VIEW,
    },
    {
        name: 'Sales',
        description: 'Input penjualan produk dan lihat transaksi.',
        href: '/sales',
        icon: BanknoteIcon,
        iconBgClass: 'bg-blue-50 text-blue-600 dark:bg-blue-500/10',
        glowClass: 'bg-blue-500',
        permission: SALE_PERMISSIONS.VIEW,
    },
    {
        name: 'Membership',
        description: 'Input transaksi membership dan perpanjangan.',
        href: '/membership/transactions',
        icon: PackageIcon,
        iconBgClass: 'bg-success-50 text-success-700 dark:bg-success-500/10',
        glowClass: 'bg-success-500',
        permission: MEMBERSHIP_TRANSACTION_PERMISSIONS.VIEW,
    },
    {
        name: 'Stock Movement',
        description: 'Catat stok masuk/keluar untuk inventory.',
        href: '/inventory/stock-movements',
        icon: GridIcon,
        iconBgClass: 'bg-purple-50 text-purple-600 dark:bg-purple-500/10',
        glowClass: 'bg-purple-500',
        permission: STOCK_MOVEMENT_PERMISSIONS.VIEW,
    },
]);

const visibleActions = computed(() =>
    actions.value.filter((action) => hasPermission(action.permission)),
);

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
