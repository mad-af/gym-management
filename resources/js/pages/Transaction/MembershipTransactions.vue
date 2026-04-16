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
                    Export
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
                <Badge
                    size="sm"
                    :color="getStatusBadgeColor(row.status, row.days_remaining)"
                >
                    {{ getStatusLabel(row.status, row.days_remaining) }}
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
                        >Pelanggan</label
                    >
                    <Combobox
                        v-model="filters.customer_id"
                        :options="customerOptions"
                        labelKey="name"
                        valueKey="id"
                        placeholder="Pilih pelanggan..."
                        :loading="customerLoading"
                        remote
                        @search="onCustomerSearch"
                        @load-more="onCustomerLoadMore"
                    />
                </div>
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
                <div>
                    <label
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200"
                        >Akan Berakhir</label
                    >
                    <select
                        v-model="filters.expiring_within_days"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                    >
                        <option value="">Semua</option>
                        <option value="3">3 Hari</option>
                        <option value="7">7 Hari</option>
                        <option value="14">14 Hari</option>
                        <option value="30">30 Hari</option>
                    </select>
                </div>
                <div>
                    <label
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200"
                        >Petugas</label
                    >
                    <Combobox
                        v-model="filters.created_by"
                        :options="staffOptions"
                        labelKey="name"
                        valueKey="id"
                        placeholder="Pilih petugas..."
                        :loading="staffLoading"
                        remote
                        @search="onStaffSearch"
                        @load-more="onStaffLoadMore"
                    />
                </div>
                <div>
                    <label
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200"
                        >Tanggal Mulai</label
                    >
                    <input
                        v-model="filters.start_date"
                        type="date"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                    />
                </div>
                <div>
                    <label
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200"
                        >Tanggal Sampai</label
                    >
                    <input
                        v-model="filters.end_date"
                        type="date"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                    />
                </div>
                <div>
                    <label
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200"
                        >Metode Pembayaran</label
                    >
                    <select
                        v-model="filters.payment_type"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                    >
                        <option value="">Semua</option>
                        <option value="CASH">Cash</option>
                        <option value="DEBIT_CARD">Debit Card</option>
                        <option value="CREDIT_CARD">Credit Card</option>
                        <option value="E_WALLET">E-Wallet</option>
                        <option value="QRIS">QRIS</option>
                        <option value="TRANSFER">Transfer</option>
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
            title="Export Membership"
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
                        variant="outline"
                        :onClick="exportPdf"
                        :disabled="exportProcessing"
                    >
                        {{ exportProcessing ? 'Memproses...' : 'Download PDF' }}
                    </Button>
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
import { ref, computed, onMounted, watch } from 'vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import DynamicTable from '@/components/tables/data-tables/DynamicTable.vue';
import type { Column } from '@/components/tables/data-tables/DynamicTable.vue';
import Badge from '@/components/ui/Badge.vue';
import Button from '@/components/ui/Button.vue';
import Combobox from '@/components/ui/Combobox.vue';
import Drawer from '@/components/ui/Drawer.vue';
import Stats2 from '@/components/ui/Stats2.vue';
import { useTimezone } from '@/helpers/timezone';
import {
    BanknoteIcon,
    CalenderIcon,
    FileTextIcon,
    FilterIcon,
    ShieldCheckIcon,
    WarningIcon,
} from '@/icons';

const { formatDateId } = useTimezone();

const currentPageTitle = ref('Membership Transactions');
const isFilterDrawerOpen = ref(false);
const isExportDrawerOpen = ref(false);
const filters = ref({
    customer_id: '',
    status: '',
    created_by: '',
    start_date: '',
    end_date: '',
    expiring_within_days: '',
    payment_type: '',
});
const exportForm = ref({ start_date: '', end_date: '' });
const exportErrors = ref<{ start_date?: string; end_date?: string }>({});
const exportProcessing = ref(false);

const customerOptions = ref<any[]>([]);
const customerLoading = ref(false);
const customerPage = ref(1);
const customerHasMore = ref(true);
const customerSearchQuery = ref('');

const staffOptions = ref<any[]>([]);
const staffLoading = ref(false);
const staffPage = ref(1);
const staffHasMore = ref(true);
const staffSearchQuery = ref('');

const stats = ref({
    activeMembers: 0,
    transactionsThisMonth: 0,
    revenueThisMonth: 0,
    expiringCount: {
        '3_days': 0,
        '7_days': 0,
        '14_days': 0,
        '30_days': 0,
    },
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
        detail: formatCurrencyId(stats.value.revenueThisMonth),
    },
    {
        label: 'Akan Berakhir (7 Hr)',
        value: stats.value.expiringCount['7_days'],
        icon: WarningIcon,
        iconBgClass: 'bg-yellow-50 text-yellow-600 dark:bg-yellow-500/10',
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
        days_remaining: t.days_remaining ?? null,
        is_expiring_soon: t.is_expiring_soon ?? false,
        payment_type: t.payment_type?.label ?? t.payment_type ?? '-',
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
    { key: 'payment_type', label: 'Metode Bayar', class: 'min-w-[140px]' },
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
            expiringCount: s.expiringCount ?? {
                '3_days': 0,
                '7_days': 0,
                '14_days': 0,
                '30_days': 0,
            },
        };
    } catch (e) {
        console.error('Error fetching membership transaction stats', e);
    }
};

const getStatusLabel = (value: unknown, daysRemaining: unknown): string => {
    const v = String(value || '').toLowerCase();
    const days = typeof daysRemaining === 'number' ? daysRemaining : null;
    if (v === 'active' && days !== null && days <= 7 && days >= 0) {
        return `Aktif (Tinggal ${days} Hari)`;
    }
    if (v === 'active') return 'Aktif';
    if (v === 'expired') return 'Expired';
    return value ? String(value) : '-';
};

const getStatusBadgeColor = (value: unknown, daysRemaining: unknown) => {
    const v = String(value || '').toLowerCase();
    const days = typeof daysRemaining === 'number' ? daysRemaining : null;
    if (v === 'active' && days !== null && days <= 3 && days >= 0) {
        return 'error';
    }
    if (v === 'active' && days !== null && days <= 7 && days >= 0) {
        return 'warning';
    }
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
            customer_id: filters.value.customer_id || undefined,
            status: filters.value.status || undefined,
            created_by: filters.value.created_by || undefined,
            start_date: filters.value.start_date || undefined,
            end_date: filters.value.end_date || undefined,
            expiring_within_days:
                filters.value.expiring_within_days || undefined,
            payment_type: filters.value.payment_type || undefined,
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

const resetFilter = () => {
    filters.value = {
        customer_id: '',
        status: '',
        created_by: '',
        start_date: '',
        end_date: '',
        expiring_within_days: '',
    };
    customerOptions.value = [];
    customerPage.value = 1;
    customerHasMore.value = true;
    staffOptions.value = [];
    staffPage.value = 1;
    staffHasMore.value = true;
    handleFilter();
};
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

const exportPdf = async () => {
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
            '/api/membership-transactions/export/pdf',
            {
                params: exportForm.value,
                responseType: 'blob',
            },
        );

        const blob = new Blob([response.data], {
            type: 'application/pdf',
        });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute(
            'download',
            `membership_transactions_${exportForm.value.start_date}_to_${exportForm.value.end_date}.pdf`,
        );
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
        isExportDrawerOpen.value = false;
    } catch (e) {
        console.error('Error exporting membership pdf', e);
    } finally {
        exportProcessing.value = false;
    }
};

onMounted(() => {
    fetchItems();
});

watch(isFilterDrawerOpen, (open) => {
    if (!open) return;
    customerSearchQuery.value = '';
    staffSearchQuery.value = '';
    fetchCustomerOptions(true);
    fetchStaffOptions(true);
});

const fetchCustomerOptions = async (reset = false) => {
    if (reset) {
        customerPage.value = 1;
        customerOptions.value = [];
        customerHasMore.value = true;
    }
    if (!customerHasMore.value && !reset) return;

    customerLoading.value = true;
    try {
        const { data } = await axios.get('/api/customers/selection', {
            params: {
                search: customerSearchQuery.value || undefined,
                page: customerPage.value,
            },
        });
        const newOptions = data.data?.data || [];
        customerOptions.value = reset
            ? newOptions
            : [
                  ...customerOptions.value,
                  ...newOptions.filter(
                      (item: any) =>
                          !customerOptions.value.some(
                              (existing) => existing.id === item.id,
                          ),
                  ),
              ];
        customerHasMore.value =
            data.data?.current_page < data.data?.last_page ?? false;
        customerPage.value += 1;
    } catch (e) {
        console.error(e);
    } finally {
        customerLoading.value = false;
    }
};

const onCustomerSearch = (search: string) => {
    customerSearchQuery.value = search;
    fetchCustomerOptions(true);
};

const onCustomerLoadMore = () => {
    fetchCustomerOptions(false);
};

const fetchStaffOptions = async (reset = false) => {
    if (reset) {
        staffPage.value = 1;
        staffOptions.value = [];
        staffHasMore.value = true;
    }
    if (!staffHasMore.value && !reset) return;

    staffLoading.value = true;
    try {
        const { data } = await axios.get('/api/users/selection', {
            params: {
                search: staffSearchQuery.value || undefined,
                page: staffPage.value,
            },
        });
        const newOptions = data.data?.data || [];
        staffOptions.value = reset
            ? newOptions
            : [
                  ...staffOptions.value,
                  ...newOptions.filter(
                      (item: any) =>
                          !staffOptions.value.some(
                              (existing) => existing.id === item.id,
                          ),
                  ),
              ];
        staffHasMore.value =
            data.data?.current_page < data.data?.last_page ?? false;
        staffPage.value += 1;
    } catch (e) {
        console.error(e);
    } finally {
        staffLoading.value = false;
    }
};

const onStaffSearch = (search: string) => {
    staffSearchQuery.value = search;
    fetchStaffOptions(true);
};

const onStaffLoadMore = () => {
    fetchStaffOptions(false);
};
</script>
