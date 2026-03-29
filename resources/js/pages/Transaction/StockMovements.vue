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
            <template #cell-type="{ row }">
                <Badge size="sm" :color="getMovementTypeBadgeColor(row.type)">
                    {{ getMovementTypeLabel(row.type) }}
                </Badge>
            </template>
            <template #cell-quantity="{ row }">
                <span
                    class="text-theme-sm font-medium text-gray-800 dark:text-white/90"
                >
                    {{ formatQuantity(row.quantity, row.type) }}
                </span>
            </template>
            <template #cell-created_at="{ row }">
                <span class="text-theme-sm text-gray-700 dark:text-gray-400">
                    {{ formatDateTimeId(row.created_at) }}
                </span>
            </template>
        </DynamicTable>

        <Drawer
            :isOpen="isFilterDrawerOpen"
            @close="isFilterDrawerOpen = false"
            title="Filter Pergerakan Stok"
        >
            <div class="space-y-6">
                <div>
                    <label
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200"
                        >Produk</label
                    >
                    <Combobox
                        v-model="filters.product_id"
                        :options="productOptions"
                        labelKey="name"
                        valueKey="id"
                        placeholder="Pilih produk..."
                        :loading="productLoading"
                        remote
                        @search="onProductSearch"
                        @load-more="onProductLoadMore"
                    />
                </div>
                <div>
                    <label
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200"
                        >Jenis</label
                    >
                    <select
                        v-model="filters.type"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                    >
                        <option value="">Semua</option>
                        <option value="in">IN</option>
                        <option value="out">OUT</option>
                        <option value="adjustment">ADJUSTMENT</option>
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
            title="Export Pergerakan Stok"
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
    CheckCircleIcon,
    ErrorIcon,
    FileTextIcon,
    FilterIcon,
    GridIcon,
} from '@/icons';

const currentPageTitle = ref('Stock Movements');
const isFilterDrawerOpen = ref(false);
const isExportDrawerOpen = ref(false);
const filters = ref({
    product_id: '',
    type: '',
    created_by: '',
    start_date: '',
    end_date: '',
});
const exportForm = ref({ start_date: '', end_date: '' });
const exportErrors = ref<{ start_date?: string; end_date?: string }>({});
const exportProcessing = ref(false);

const productOptions = ref<any[]>([]);
const productLoading = ref(false);
const productPage = ref(1);
const productHasMore = ref(true);
const productSearchQuery = ref('');

const staffOptions = ref<any[]>([]);
const staffLoading = ref(false);
const staffPage = ref(1);
const staffHasMore = ref(true);
const staffSearchQuery = ref('');

const stats = ref({
    movementsThisMonth: 0,
    inThisMonth: 0,
    outThisMonth: 0,
});

const items = ref<any[]>([]);
const totalItems = ref(0);
const currentPage = ref(1);
const perPage = ref(10);
const searchFilter = ref('');

const statsItems = computed(() => [
    {
        label: 'Pergerakan Bulan Ini',
        value: stats.value.movementsThisMonth,
        icon: GridIcon,
        iconBgClass: 'bg-brand-50 text-brand-500 dark:bg-brand-500/10',
    },
    {
        label: 'Stok Masuk',
        value: stats.value.inThisMonth,
        icon: CheckCircleIcon,
        iconBgClass: 'bg-success-50 text-success-600 dark:bg-success-500/10',
    },
    {
        label: 'Stok Keluar',
        value: stats.value.outThisMonth,
        icon: ErrorIcon,
        iconBgClass: 'bg-error-50 text-error-600 dark:bg-error-500/10',
    },
]);

const tableData = computed(() =>
    items.value.map((m: any) => ({
        id: m.id,
        product_name: m.product?.name || '-',
        type: m.type || '-',
        quantity: m.quantity,
        created_at: m.created_at,
        created_by: m.creator?.name || '-',
    })),
);

const columns = ref<Column[]>([
    { key: 'product_name', label: 'Produk', class: 'min-w-[200px]' },
    { key: 'type', label: 'Jenis', type: 'custom', class: 'min-w-[120px]' },
    {
        key: 'quantity',
        label: 'Jumlah',
        type: 'custom',
        class: 'min-w-[120px]',
    },
    {
        key: 'created_at',
        label: 'Tanggal',
        type: 'custom',
        class: 'min-w-[160px]',
    },
    { key: 'created_by', label: 'Petugas', class: 'min-w-[180px]' },
]);

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

const getMovementTypeLabel = (value: unknown): string => {
    const v = String(value || '').toLowerCase();
    if (v === 'in') return 'IN';
    if (v === 'out') return 'OUT';
    if (v === 'adjustment') return 'Adjustment';
    return value ? String(value) : '-';
};

const getMovementTypeBadgeColor = (value: unknown) => {
    const v = String(value || '').toLowerCase();
    if (v === 'in') return 'success';
    if (v === 'out') return 'error';
    if (v === 'adjustment') return 'warning';
    return 'light';
};

const formatQuantity = (value: unknown, type: unknown): string => {
    const n = typeof value === 'number' ? value : Number(value);
    const text = Number.isNaN(n)
        ? String(value ?? '-')
        : n.toLocaleString('id-ID');
    const t = String(type || '').toLowerCase();
    if (t === 'in') return `+${text}`;
    if (t === 'out') return `-${text}`;
    return text;
};

const fetchStats = async () => {
    try {
        const { data } = await axios.get('/api/stock-movements/stats');
        const s = data.data || data;
        stats.value = {
            movementsThisMonth: s.movementsThisMonth ?? 0,
            inThisMonth: s.inThisMonth ?? 0,
            outThisMonth: s.outThisMonth ?? 0,
        };
    } catch (e) {
        console.error('Error fetching stock movement stats', e);
    }
};

const fetchItems = async () => {
    const { data } = await axios.get('/api/stock-movements', {
        params: {
            per_page: perPage.value,
            page: currentPage.value,
            search: searchFilter.value || undefined,
            product_id: filters.value.product_id || undefined,
            type: filters.value.type || undefined,
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
        product_id: '',
        type: '',
        created_by: '',
        start_date: '',
        end_date: '',
    };
    productOptions.value = [];
    productPage.value = 1;
    productHasMore.value = true;
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
        const response = await axios.get('/api/stock-movements/export', {
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
            `stock_movements_${exportForm.value.start_date}_to_${exportForm.value.end_date}.csv`,
        );
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
        isExportDrawerOpen.value = false;
    } catch (e) {
        console.error('Error exporting stock movements csv', e);
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
        const response = await axios.get('/api/stock-movements/export/pdf', {
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
            `stock_movements_${exportForm.value.start_date}_to_${exportForm.value.end_date}.pdf`,
        );
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
        isExportDrawerOpen.value = false;
    } catch (e) {
        console.error('Error exporting stock movements pdf', e);
    } finally {
        exportProcessing.value = false;
    }
};

onMounted(fetchItems);

watch(isFilterDrawerOpen, (open) => {
    if (!open) return;
    productSearchQuery.value = '';
    staffSearchQuery.value = '';
    fetchProductOptions(true);
    fetchStaffOptions(true);
});

const fetchProductOptions = async (reset = false) => {
    if (reset) {
        productPage.value = 1;
        productOptions.value = [];
        productHasMore.value = true;
    }
    if (!productHasMore.value && !reset) return;

    productLoading.value = true;
    try {
        const { data } = await axios.get('/api/products/selection', {
            params: {
                search: productSearchQuery.value || undefined,
                page: productPage.value,
            },
        });
        const newOptions = data.data?.data || [];
        productOptions.value = reset
            ? newOptions
            : [
                  ...productOptions.value,
                  ...newOptions.filter(
                      (item: any) =>
                          !productOptions.value.some(
                              (existing) => existing.id === item.id,
                          ),
                  ),
              ];
        productHasMore.value =
            data.data?.current_page < data.data?.last_page ?? false;
        productPage.value += 1;
    } catch (e) {
        console.error(e);
    } finally {
        productLoading.value = false;
    }
};

const onProductSearch = (search: string) => {
    productSearchQuery.value = search;
    fetchProductOptions(true);
};

const onProductLoadMore = () => {
    if (productLoading.value || !productHasMore.value) return;
    fetchProductOptions(false);
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
