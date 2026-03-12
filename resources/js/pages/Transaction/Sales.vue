<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="currentPageTitle" />

        <DynamicTable :columns="columns" :data="tableData" :items-per-page="perPage" :total-items="totalItems"
            :current-page="currentPage" :is-server-side="true" @update:page="handlePageChange"
            @update:search="handleSearch" @update:perPage="handlePerPageChange">
            <template #header-actions>
                <Button size="sm" variant="outline" :onClick="() => isFilterDrawerOpen = true"
                    className="w-full sm:w-auto" :startIcon="FilterIcon">
                    Filter
                </Button>
            </template>
            <template #cell-total_amount="{ row }">
                <span class="text-theme-sm font-medium text-gray-800 dark:text-white/90">
                    {{ formatCurrencyId(row.total_amount) }}
                </span>
            </template>
            <template #cell-created_at="{ row }">
                <span class="text-theme-sm text-gray-700 dark:text-gray-400">
                    {{ formatDateTimeId(row.created_at) }}
                </span>
            </template>
            <template #cell-item_quantity="{ row }">
                <span class="text-theme-sm font-medium text-gray-800 dark:text-white/90">
                    {{ formatNumberId(row.item_quantity) }}
                </span>
            </template>
            <template #cell-item_price="{ row }">
                <span class="text-theme-sm text-gray-700 dark:text-gray-400">
                    {{ formatCurrencyId(row.item_price) }}
                </span>
            </template>
            <template #cell-item_subtotal="{ row }">
                <span class="text-theme-sm font-medium text-gray-800 dark:text-white/90">
                    {{ formatCurrencyId(row.item_subtotal) }}
                </span>
            </template>
        </DynamicTable>

        <Drawer :isOpen="isFilterDrawerOpen" @close="isFilterDrawerOpen = false" title="Filter Penjualan">
            <div class="space-y-6">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Pelanggan</label>
                    <input type="text" v-model="filters.customer_id"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
                </div>
            </div>
            <template #footer>
                <div class="flex w-full justify-end gap-3">
                    <Button variant="outline" :onClick="resetFilter">Reset</Button>
                    <Button variant="primary" :onClick="handleFilter">Terapkan Filter</Button>
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
import { FilterIcon } from '@/icons';

const currentPageTitle = ref('Sales');
const isFilterDrawerOpen = ref(false);
const filters = ref({ customer_id: '' });

const items = ref<any[]>([]);
const totalItems = ref(0);
const currentPage = ref(1);
const perPage = ref(10);
const searchFilter = ref('');

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
                    total_amount: { rowspan: rowSpan },
                    created_at: { rowspan: rowSpan },
                };
            } else {
                row._cellAttributes = {
                    customer_name: { hidden: true },
                    total_amount: { hidden: true },
                    created_at: { hidden: true },
                };
            }

            return row;
        });
    })
);

const columns: Column[] = [
    { key: 'customer_name', label: 'Pelanggan', class: 'min-w-[200px]' },
    { key: 'created_at', label: 'Tanggal', type: 'custom', class: 'min-w-[170px]' },
    { key: 'item_product_name', label: 'Item', class: 'min-w-[260px]' },
    { key: 'item_quantity', label: 'Qty', type: 'custom', class: 'min-w-[80px] text-right' },
    { key: 'item_price', label: 'Harga', type: 'custom', class: 'min-w-[130px] text-right' },
    { key: 'item_subtotal', label: 'Subtotal', type: 'custom', class: 'min-w-[130px] text-right' },
    { key: 'total_amount', label: 'Total', type: 'custom', class: 'min-w-[140px] text-right' },
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

onMounted(() => {
    fetchItems();
});
</script>
