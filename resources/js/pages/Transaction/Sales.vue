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
            <template #cell-total_amount="{ row }">
                <span
                    class="text-theme-sm font-medium text-gray-800 dark:text-white/90"
                >
                    {{ formatCurrencyId(row.total_amount) }}
                </span>
            </template>
            <template #cell-created_at="{ row }">
                <span class="text-theme-sm text-gray-700 dark:text-gray-400">
                    {{ formatDateTimeId(row.created_at) }}
                </span>
            </template>
            <template #cell-item_quantity="{ row }">
                <span
                    class="text-theme-sm font-medium text-gray-800 dark:text-white/90"
                >
                    {{ formatNumberId(row.item_quantity) }}
                </span>
            </template>
            <template #cell-customer_name="{ row }">
                <div class="flex flex-col gap-1">
                    <span
                        class="font-medium text-gray-800 dark:text-white/90"
                        >{{ row.customer_name }}</span
                    >
                    <span
                        v-if="row.customer_is_member"
                        class="w-fit rounded-full bg-success-50 px-2 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/15 dark:text-success-500"
                    >
                        Member
                    </span>
                </div>
            </template>
            <template #cell-item_price="{ row }">
                <span class="text-theme-sm text-gray-700 dark:text-gray-400">
                    {{ formatCurrencyId(row.item_price) }}
                </span>
            </template>
            <template #cell-item_subtotal="{ row }">
                <span
                    class="text-theme-sm font-medium text-gray-800 dark:text-white/90"
                >
                    {{ formatCurrencyId(row.item_subtotal) }}
                </span>
            </template>
        </DynamicTable>

        <Drawer
            :isOpen="isFilterDrawerOpen"
            @close="isFilterDrawerOpen = false"
            title="Filter Penjualan"
        >
            <div class="space-y-6">
                <div>
                    <label
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200"
                        >Pelanggan</label
                    >
                    <input
                        type="text"
                        v-model="filters.customer_id"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                    />
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
            title="Export Penjualan (CSV)"
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
import Button from '@/components/ui/Button.vue';
import Drawer from '@/components/ui/Drawer.vue';
import Stats2 from '@/components/ui/Stats2.vue';
import {
    BanknoteIcon,
    BarChartIcon,
    ClipboardCheckIcon,
    FileTextIcon,
    FilterIcon,
} from '@/icons';

const currentPageTitle = ref('Sales');
const isFilterDrawerOpen = ref(false);
const isExportDrawerOpen = ref(false);
const filters = ref({ customer_id: '' });
const exportForm = ref({ start_date: '', end_date: '' });
const exportErrors = ref<{ start_date?: string; end_date?: string }>({});
const exportProcessing = ref(false);

const stats = ref({
    totalSales: 0,
    revenueThisMonth: 0,
    revenueToday: 0,
});

const items = ref<any[]>([]);
const totalItems = ref(0);
const currentPage = ref(1);
const perPage = ref(10);
const searchFilter = ref('');

const statsItems = computed(() => [
    {
        label: 'Total Penjualan',
        value: stats.value.totalSales,
        icon: ClipboardCheckIcon,
        iconBgClass: 'bg-brand-50 text-brand-500 dark:bg-brand-500/10',
    },
    {
        label: 'Omzet Bulan Ini',
        value: formatCurrencyCompactId(stats.value.revenueThisMonth),
        icon: BanknoteIcon,
        iconBgClass: 'bg-success-50 text-success-600 dark:bg-success-500/10',
    },
    {
        label: 'Omzet Hari Ini',
        value: formatCurrencyCompactId(stats.value.revenueToday),
        icon: BarChartIcon,
        iconBgClass: 'bg-blue-50 text-blue-500 dark:bg-blue-500/10',
    },
]);

const tableData = computed(() =>
    items.value.flatMap((s: any) => {
        const saleItems = Array.isArray(s.items) ? s.items : [];
        const normalizedItems = saleItems.length > 0 ? saleItems : [null];
        const rowSpan = normalizedItems.length;

        return normalizedItems.map((it: any, idx: number) => {
            const row: any = {
                _key: `${s.id}-${idx}`,
                id: s.id,
                customer_name: s.customer?.name || '-',
                customer_is_member: s.customer?.is_active_member ?? false,
                staff_name: s.creator?.name || '-',
                total_amount: s.total_amount,
                created_at: s.created_at,
                item_product_name: it?.product?.name || '-',
                item_quantity: it?.quantity ?? null,
                item_price: it?.price ?? null,
                item_subtotal: it?.subtotal ?? null,
            };

            if (idx === 0) {
                row._cellAttributes = {
                    customer_name: { rowspan: rowSpan },
                    staff_name: { rowspan: rowSpan },
                    total_amount: { rowspan: rowSpan },
                    created_at: { rowspan: rowSpan },
                };
            } else {
                row._cellAttributes = {
                    customer_name: { hidden: true },
                    staff_name: { hidden: true },
                    total_amount: { hidden: true },
                    created_at: { hidden: true },
                };
            }

            return row;
        });
    }),
);

const columns: Column[] = [
    {
        key: 'customer_name',
        label: 'Pelanggan',
        type: 'custom',
        class: 'min-w-[200px]',
    },
    {
        key: 'created_at',
        label: 'Tanggal',
        type: 'custom',
        class: 'min-w-[170px]',
    },
    { key: 'item_product_name', label: 'Item', class: 'min-w-[260px]' },
    {
        key: 'item_quantity',
        label: 'Qty',
        type: 'custom',
        class: 'min-w-[80px] text-right',
    },
    {
        key: 'item_price',
        label: 'Harga',
        type: 'custom',
        class: 'min-w-[130px] text-right',
    },
    {
        key: 'item_subtotal',
        label: 'Subtotal',
        type: 'custom',
        class: 'min-w-[130px] text-right',
    },
    {
        key: 'total_amount',
        label: 'Total',
        type: 'custom',
        class: 'min-w-[140px] text-right',
    },
    { key: 'staff_name', label: 'Petugas', class: 'min-w-[180px]' },
];

const formatNumberId = (value: unknown): string => {
    if (value === null || value === undefined || value === '') {
        return '-';
    }

    const numberValue = typeof value === 'number' ? value : Number(value);
    if (Number.isNaN(numberValue)) {
        return String(value);
    }

    return numberValue.toLocaleString('id-ID');
};

const formatCurrencyId = (value: unknown): string => {
    if (value === null || value === undefined || value === '') {
        return 'Rp 0';
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

const formatDateTimeId = (value: unknown): string => {
    if (!value) return '-';

    const d = value instanceof Date ? value : new Date(String(value));
    if (Number.isNaN(d.getTime())) return String(value);

    return new Intl.DateTimeFormat('id-ID', {
        year: 'numeric',
        month: 'short',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    }).format(d);
};

const fetchStats = async () => {
    try {
        const { data } = await axios.get('/api/sales/stats');
        const s = data.data || data;
        stats.value = {
            totalSales: s.totalSales ?? 0,
            revenueThisMonth: s.revenueThisMonth ?? 0,
            revenueToday: s.revenueToday ?? 0,
        };
    } catch (e) {
        console.error('Error fetching sales stats', e);
    }
};

const fetchItems = async () => {
    try {
        const { data } = await axios.get('/api/sales', {
            params: {
                page: currentPage.value,
                per_page: perPage.value,
                search: searchFilter.value,
                customer_id: filters.value.customer_id,
            },
        });
        items.value = data.data?.data || [];
        totalItems.value = data.data?.total || 0;
        fetchStats();
    } catch (e) {
        console.error(e);
    }
};

const handlePageChange = (page: number) => {
    currentPage.value = page;
    fetchItems();
};

const handleSearch = (search: string) => {
    searchFilter.value = search;
    currentPage.value = 1;
    fetchItems();
};

const handlePerPageChange = (newPerPage: number) => {
    perPage.value = newPerPage;
    currentPage.value = 1;
    fetchItems();
};

const handleFilter = () => {
    isFilterDrawerOpen.value = false;
    currentPage.value = 1;
    fetchItems();
};

const resetFilter = () => {
    filters.value.customer_id = '';
    handleFilter();
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
        const response = await axios.get('/api/sales/export', {
            params: exportForm.value,
            responseType: 'blob',
        });

        const blob = new Blob([response.data], {
            type: 'text/csv;charset=utf-8;',
        });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute(
            'download',
            `sales_${exportForm.value.start_date}_to_${exportForm.value.end_date}.csv`,
        );
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
        isExportDrawerOpen.value = false;
    } catch (e) {
        console.error('Error exporting sales csv', e);
    } finally {
        exportProcessing.value = false;
    }
};

onMounted(() => {
    fetchItems();
});
</script>
