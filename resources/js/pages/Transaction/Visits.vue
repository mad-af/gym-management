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
            <template #cell-visit_type="{ row }">
                <Badge
                    size="sm"
                    :color="getVisitTypeBadgeColor(row.visit_type)"
                >
                    {{ getVisitTypeLabel(row.visit_type) }}
                </Badge>
            </template>
            <template #cell-price="{ row }">
                <span
                    v-if="
                        String(row.visit_type || '').toUpperCase() ===
                        'MEMBERSHIP'
                    "
                    class="text-theme-sm font-medium text-gray-800 dark:text-white/90"
                >
                    -
                </span>
                <span
                    v-else
                    class="text-theme-sm font-medium text-gray-800 dark:text-white/90"
                >
                    {{ formatCurrencyId(row.price) }}
                </span>
            </template>
            <template #cell-checkin_time="{ row }">
                <span class="text-theme-sm text-gray-700 dark:text-gray-400">
                    {{ formatDateTimeId(row.checkin_time) }}
                </span>
            </template>
        </DynamicTable>

        <Drawer
            :isOpen="isFilterDrawerOpen"
            @close="isFilterDrawerOpen = false"
            title="Filter Kunjungan"
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
                        >Jenis</label
                    >
                    <select
                        v-model="filters.visit_type"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                    >
                        <option value="">Semua</option>
                        <option value="MEMBERSHIP">Member</option>
                        <option value="DAILY">Harian</option>
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
            title="Export Kunjungan"
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
import {
    BanknoteIcon,
    DoorOpenIcon,
    FileTextIcon,
    FilterIcon,
    ShieldCheckIcon,
} from '@/icons';

const currentPageTitle = ref('Visits / Check In');
const isFilterDrawerOpen = ref(false);
const isExportDrawerOpen = ref(false);
const filters = ref({
    customer_id: '',
    visit_type: '',
    created_by: '',
    start_date: '',
    end_date: '',
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
    visitsToday: 0,
    memberVisitsToday: 0,
    dailyRevenueToday: 0,
});

const items = ref<any[]>([]);
const totalItems = ref(0);
const currentPage = ref(1);
const perPage = ref(10);
const searchFilter = ref('');

const statsItems = computed(() => [
    {
        label: 'Kunjungan Hari Ini',
        value: stats.value.visitsToday,
        icon: DoorOpenIcon,
        iconBgClass: 'bg-brand-50 text-brand-500 dark:bg-brand-500/10',
    },
    {
        label: 'Customer Hari Ini',
        value: stats.value.memberVisitsToday,
        icon: ShieldCheckIcon,
        iconBgClass: 'bg-success-50 text-success-600 dark:bg-success-500/10',
    },
    {
        label: 'Pendapatan Harian',
        value: formatCurrencyCompactId(stats.value.dailyRevenueToday),
        icon: BanknoteIcon,
        iconBgClass: 'bg-blue-50 text-blue-500 dark:bg-blue-500/10',
    },
]);

const tableData = computed(() =>
    items.value.map((v: any) => ({
        id: v.id,
        customer_name: v.customer?.name || '-',
        visit_type: v.visit_type || '-',
        price: v.price ?? null,
        checkin_time: v.checkin_time || '-',
        created_by: v.creator?.name || '-',
    })),
);

const columns = ref<Column[]>([
    { key: 'customer_name', label: 'Pelanggan', class: 'min-w-[200px]' },
    {
        key: 'visit_type',
        label: 'Jenis',
        type: 'custom',
        class: 'min-w-[120px]',
    },
    {
        key: 'price',
        label: 'Harga',
        type: 'custom',
        class: 'min-w-[140px] text-right',
    },
    {
        key: 'checkin_time',
        label: 'Check-In',
        type: 'custom',
        class: 'min-w-[180px]',
    },
    { key: 'created_by', label: 'Petugas', class: 'min-w-[180px]' },
]);

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

const getVisitTypeLabel = (value: unknown): string => {
    const v = String(value || '').toUpperCase();
    if (v === 'MEMBERSHIP') return 'Member';
    if (v === 'DAILY') return 'Harian';
    return value ? String(value) : '-';
};

const getVisitTypeBadgeColor = (value: unknown) => {
    const v = String(value || '').toUpperCase();
    if (v === 'MEMBERSHIP') return 'primary';
    if (v === 'DAILY') return 'info';
    return 'light';
};

const fetchStats = async () => {
    try {
        const { data } = await axios.get('/api/visits/stats');
        const s = data.data || data;
        stats.value = {
            visitsToday: s.visitsToday ?? 0,
            memberVisitsToday: s.memberVisitsToday ?? 0,
            dailyRevenueToday: s.dailyRevenueToday ?? 0,
        };
    } catch (e) {
        console.error('Error fetching visit stats', e);
    }
};

const fetchItems = async () => {
    const { data } = await axios.get('/api/visits', {
        params: {
            per_page: perPage.value,
            page: currentPage.value,
            search: searchFilter.value || undefined,
            customer_id: filters.value.customer_id || undefined,
            visit_type: filters.value.visit_type || undefined,
            created_by: filters.value.created_by || undefined,
            start_date: filters.value.start_date || undefined,
            end_date: filters.value.end_date || undefined,
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
        visit_type: '',
        created_by: '',
        start_date: '',
        end_date: '',
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
        const response = await axios.get('/api/visits/export', {
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
            `visits_${exportForm.value.start_date}_to_${exportForm.value.end_date}.csv`,
        );
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
        isExportDrawerOpen.value = false;
    } catch (e) {
        console.error('Error exporting visits csv', e);
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
        const response = await axios.get('/api/visits/export/pdf', {
            params: exportForm.value,
            responseType: 'blob',
        });

        const blob = new Blob([response.data], {
            type: 'application/pdf',
        });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute(
            'download',
            `visits_${exportForm.value.start_date}_to_${exportForm.value.end_date}.pdf`,
        );
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
        isExportDrawerOpen.value = false;
    } catch (e) {
        console.error('Error exporting visits pdf', e);
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
    if (customerLoading.value || !customerHasMore.value) return;
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
    if (staffLoading.value || !staffHasMore.value) return;
    fetchStaffOptions(false);
};
</script>
