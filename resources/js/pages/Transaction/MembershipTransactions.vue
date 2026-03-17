<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="currentPageTitle" />
        <Stats2 :items="statsItems" />

        <DynamicTable
            :columns="columns"
            :data="tableData"
            :items-per-page="perPage"
            :total-items="totalItems"
            :current-page="currentPage"
            :is-server-side="true"
            @update:page="handlePageChange"
            @update:search="handleSearch"
            @update:perPage="handlePerPageChange"
        >
            <template #header-actions>
                <Button
                    size="sm"
                    variant="outline"
                    :onClick="() => (isFilterDrawerOpen = true)"
                    className="w-full sm:w-auto"
                    :startIcon="FilterIcon"
                >
                    Filter
                </Button>
                <Button
                    size="sm"
                    variant="outline"
                    :onClick="() => (isExportDrawerOpen = true)"
                    className="w-full sm:w-auto"
                    :startIcon="FileTextIcon"
                >
                    Export CSV
                </Button>
            </template>
            <template #cell-price="{ row }">
                <span
                    class="text-theme-sm font-medium text-gray-800 dark:text-white/90"
                >
                    {{ formatCurrencyId(row.price) }}
                </span>
            </template>
            <template #cell-start_date="{ row }">
                <span class="text-theme-sm text-gray-700 dark:text-gray-400">
                    {{ formatDateId(row.start_date) }}
                </span>
            </template>
            <template #cell-end_date="{ row }">
                <span class="text-theme-sm text-gray-700 dark:text-gray-400">
                    {{ formatDateId(row.end_date) }}
                </span>
            </template>
            <template #cell-status="{ row }">
                <Badge size="sm" :color="getStatusBadgeColor(row.status)">
                    {{ getStatusLabel(row.status) }}
                </Badge>
            </template>
        </DynamicTable>

        <Drawer
            :isOpen="isFilterDrawerOpen"
            @close="isFilterDrawerOpen = false"
            title="Filter Membership"
        >
            <div class="space-y-6">
                <div>
                    <label
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200"
                        >Status</label
                    >
                    <select
                        v-model="filters.status"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                    >
                        <option value="">Semua</option>
                        <option value="active">Aktif</option>
                        <option value="expired">Expired</option>
                    </select>
                </div>
            </div>
            <template #footer>
                <div class="flex w-full justify-end gap-3">
                    <Button variant="outline" :onClick="resetFilter"
                        >Reset</Button
                    >
                    <Button variant="primary" :onClick="handleFilter"
                        >Terapkan Filter</Button
                    >
                </div>
            </template>
        </Drawer>

        <Drawer
            :isOpen="isExportDrawerOpen"
            @close="isExportDrawerOpen = false"
            title="Export Membership (CSV)"
        >
            <div class="space-y-6">
                <div>
                    <label
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200"
                        >Tanggal Mulai</label
                    >
                    <input
                        v-model="exportForm.start_date"
                        type="date"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                    />
                    <p
                        v-if="exportErrors.start_date"
                        class="mt-1 text-sm text-error-500"
                    >
                        {{ exportErrors.start_date }}
                    </p>
                </div>
                <div>
                    <label
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200"
                        >Tanggal Sampai</label
                    >
                    <input
                        v-model="exportForm.end_date"
                        type="date"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                    />
                    <p
                        v-if="exportErrors.end_date"
                        class="mt-1 text-sm text-error-500"
                    >
                        {{ exportErrors.end_date }}
                    </p>
                </div>
            </div>
            <template #footer>
                <div class="flex w-full justify-end gap-3">
                    <Button variant="outline" :onClick="resetExport"
                        >Reset</Button
                    >
                    <Button
                        variant="primary"
                        :onClick="exportCsv"
                        :disabled="exportProcessing"
                    >
                        {{ exportProcessing ? 'Memproses...' : 'Download CSV' }}
                    </Button>
                </div>
            </template>
        </Drawer>
    </AdminLayout>
</template>

<script setup lang="ts">
import axios from 'axios';
import { ref, computed, onMounted } from 'vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import DynamicTable from '@/components/tables/data-tables/DynamicTable.vue';
import type { Column } from '@/components/tables/data-tables/DynamicTable.vue';
import Badge from '@/components/ui/Badge.vue';
import Button from '@/components/ui/Button.vue';
import Drawer from '@/components/ui/Drawer.vue';
import Stats2 from '@/components/ui/Stats2.vue';
import {
    BanknoteIcon,
    CalenderIcon,
    FileTextIcon,
    FilterIcon,
    ShieldCheckIcon,
} from '@/icons';

const currentPageTitle = ref('Membership Transactions');
const isFilterDrawerOpen = ref(false);
const isExportDrawerOpen = ref(false);
const filters = ref({ status: '' });
const exportForm = ref({ start_date: '', end_date: '' });
const exportErrors = ref<{ start_date?: string; end_date?: string }>({});
const exportProcessing = ref(false);

const stats = ref({
    activeMembers: 0,
    transactionsThisMonth: 0,
    revenueThisMonth: 0,
});

const items = ref<any[]>([]);
const totalItems = ref(0);
const currentPage = ref(1);
const perPage = ref(10);
const searchFilter = ref('');

const statsItems = computed(() => [
    {
        label: 'Member Aktif',
        value: stats.value.activeMembers,
        icon: ShieldCheckIcon,
        iconBgClass: 'bg-success-50 text-success-600 dark:bg-success-500/10',
    },
    {
        label: 'Transaksi Bulan Ini',
        value: stats.value.transactionsThisMonth,
        icon: CalenderIcon,
        iconBgClass: 'bg-blue-50 text-blue-500 dark:bg-blue-500/10',
    },
    {
        label: 'Omzet Bulan Ini',
        value: formatCurrencyCompactId(stats.value.revenueThisMonth),
        icon: BanknoteIcon,
        iconBgClass: 'bg-brand-50 text-brand-500 dark:bg-brand-500/10',
    },
]);

const tableData = computed(() =>
    items.value.map((t: any) => ({
        id: t.id,
        customer_name: t.customer?.name || '-',
        staff_name: t.creator?.name || '-',
        package_name: t.package?.name || '-',
        price: t.price ?? null,
        start_date: t.start_date,
        end_date: t.end_date,
        status: t.status || '-',
    })),
);

const columns = ref<Column[]>([
    { key: 'customer_name', label: 'Pelanggan', class: 'min-w-[200px]' },
    { key: 'package_name', label: 'Paket', class: 'min-w-[200px]' },
    {
        key: 'price',
        label: 'Harga',
        type: 'custom',
        class: 'min-w-[140px] text-right',
    },
    {
        key: 'start_date',
        label: 'Mulai',
        type: 'custom',
        class: 'min-w-[140px]',
    },
    {
        key: 'end_date',
        label: 'Selesai',
        type: 'custom',
        class: 'min-w-[140px]',
    },
    { key: 'status', label: 'Status', type: 'custom', class: 'min-w-[120px]' },
    { key: 'staff_name', label: 'Petugas', class: 'min-w-[180px]' },
]);

const formatCurrencyId = (value: unknown): string => {
    if (value === null || value === undefined || value === '') {
        return '-';
    }

    const numberValue = typeof value === 'number' ? value : Number(value);
    if (Number.isNaN(numberValue)) {
        return String(value);
    }

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    })
        .format(numberValue)
        .replace('Rp', 'Rp ');
};

const formatCompactNumberId = (value: number): string => {
    const abs = Math.abs(value);

    const formatWithSuffix = (divisor: number, suffix: string) => {
        const scaled = value / divisor;
        const maximumFractionDigits = Math.abs(scaled) < 10 ? 1 : 0;

        const formatted = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits,
        }).format(scaled);

        return `${formatted.replace(/,0$/, '')} ${suffix}`;
    };

    if (abs < 1_000) return value.toLocaleString('id-ID');
    if (abs < 1_000_000) return formatWithSuffix(1_000, 'rb');
    if (abs < 1_000_000_000) return formatWithSuffix(1_000_000, 'jt');
    if (abs < 1_000_000_000_000) return formatWithSuffix(1_000_000_000, 'M');
    return formatWithSuffix(1_000_000_000_000, 'T');
};

const formatCurrencyCompactId = (value: unknown): string => {
    if (value === null || value === undefined || value === '') {
        return 'Rp 0';
    }

    const numberValue = typeof value === 'number' ? value : Number(value);
    if (Number.isNaN(numberValue)) {
        return String(value);
    }

    return `Rp ${formatCompactNumberId(numberValue)}`;
};

const fetchStats = async () => {
    try {
        const { data } = await axios.get('/api/membership-transactions/stats');
        const s = data.data || data;
        stats.value = {
            activeMembers: s.activeMembers ?? 0,
            transactionsThisMonth: s.transactionsThisMonth ?? 0,
            revenueThisMonth: s.revenueThisMonth ?? 0,
        };
    } catch (e) {
        console.error('Error fetching membership transaction stats', e);
    }
};

const formatDateId = (value: unknown): string => {
    if (!value) return '-';

    const raw = String(value);

    if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) {
        const [y, m, d] = raw.split('-').map(Number);
        const dt = new Date(y, (m || 1) - 1, d || 1);
        return new Intl.DateTimeFormat('id-ID', {
            year: 'numeric',
            month: 'short',
            day: '2-digit',
        }).format(dt);
    }

    const dt = new Date(raw);
    if (Number.isNaN(dt.getTime())) return raw;

    return new Intl.DateTimeFormat('id-ID', {
        year: 'numeric',
        month: 'short',
        day: '2-digit',
    }).format(dt);
};

const getStatusLabel = (value: unknown): string => {
    const v = String(value || '').toLowerCase();
    if (v === 'active') return 'Aktif';
    if (v === 'expired') return 'Expired';
    return value ? String(value) : '-';
};

const getStatusBadgeColor = (value: unknown) => {
    const v = String(value || '').toLowerCase();
    if (v === 'active') return 'success';
    if (v === 'expired') return 'warning';
    return 'light';
};

const fetchItems = async () => {
    const { data } = await axios.get('/api/membership-transactions', {
        params: {
            per_page: perPage.value,
            page: currentPage.value,
            search: searchFilter.value || undefined,
            status: filters.value.status || undefined,
        },
    });
    items.value = data.data?.data || data.data || [];
    totalItems.value = data.data?.total || items.value.length;
    fetchStats();
};

const handlePageChange = (p: number) => {
    currentPage.value = p;
    fetchItems();
};
const handleSearch = (s: string) => {
    searchFilter.value = s;
    currentPage.value = 1;
    fetchItems();
};
const handlePerPageChange = (n: number) => {
    perPage.value = n;
    currentPage.value = 1;
    fetchItems();
};

const resetFilter = () => (filters.value = { status: '' });
const handleFilter = () => {
    currentPage.value = 1;
    fetchItems();
    isFilterDrawerOpen.value = false;
};

const resetExport = () => {
    exportForm.value = { start_date: '', end_date: '' };
    exportErrors.value = {};
};

const exportCsv = async () => {
    exportErrors.value = {};

    if (!exportForm.value.start_date) {
        exportErrors.value.start_date = 'Tanggal mulai wajib diisi.';
    }

    if (!exportForm.value.end_date) {
        exportErrors.value.end_date = 'Tanggal sampai wajib diisi.';
    }

    if (Object.keys(exportErrors.value).length > 0) {
        return;
    }

    exportProcessing.value = true;
    try {
        const response = await axios.get(
            '/api/membership-transactions/export',
            {
                params: exportForm.value,
                responseType: 'blob',
            },
        );

        const blob = new Blob([response.data], {
            type: 'text/csv;charset=utf-8;',
        });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute(
            'download',
            `membership_transactions_${exportForm.value.start_date}_to_${exportForm.value.end_date}.csv`,
        );
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
        isExportDrawerOpen.value = false;
    } catch (e) {
        console.error('Error exporting membership csv', e);
    } finally {
        exportProcessing.value = false;
    }
};

onMounted(() => {
    fetchItems();
});
</script>
