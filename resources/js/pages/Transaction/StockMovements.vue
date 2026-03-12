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
            <template #cell-type="{ row }">
                <Badge size="sm" :color="getMovementTypeBadgeColor(row.type)">
                    {{ getMovementTypeLabel(row.type) }}
                </Badge>
            </template>
            <template #cell-quantity="{ row }">
                <span class="text-theme-sm font-medium text-gray-800 dark:text-white/90">
                    {{ formatQuantity(row.quantity, row.type) }}
                </span>
            </template>
            <template #cell-created_at="{ row }">
                <span class="text-theme-sm text-gray-700 dark:text-gray-400">
                    {{ formatDateTimeId(row.created_at) }}
                </span>
            </template>
        </DynamicTable>

        <Drawer :isOpen="isFilterDrawerOpen" @close="isFilterDrawerOpen = false" title="Filter Pergerakan Stok">
            <div class="space-y-6">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Jenis</label>
                    <select v-model="filters.type"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                        <option value="">Semua</option>
                        <option value="in">IN</option>
                        <option value="adjustment">ADJUSTMENT</option>
                        <option value="out">OUT</option>
                    </select>
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
import Badge from '@/components/ui/Badge.vue';
import Button from '@/components/ui/Button.vue';
import Drawer from '@/components/ui/Drawer.vue';
import { FilterIcon } from '@/icons';

const currentPageTitle = ref('Stock Movements');
const isFilterDrawerOpen = ref(false);
const filters = ref({ type: '' });

const items = ref<any[]>([]);
const totalItems = ref(0);
const currentPage = ref(1);
const perPage = ref(10);
const searchFilter = ref('');

const tableData = computed(() =>
    items.value.map((m: any) => ({
        id: m.id,
        product_name: m.product?.name || '-',
        type: m.type || '-',
        quantity: m.quantity,
        created_at: m.created_at,
    })),
);

const columns = ref<Column[]>([
    { key: 'product_name', label: 'Produk', class: 'min-w-[200px]' },
    { key: 'type', label: 'Jenis', type: 'custom', class: 'min-w-[120px]' },
    { key: 'quantity', label: 'Jumlah', type: 'custom', class: 'min-w-[120px]' },
    { key: 'created_at', label: 'Tanggal', type: 'custom', class: 'min-w-[160px]' },
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
    const text = Number.isNaN(n) ? String(value ?? '-') : n.toLocaleString('id-ID');
    const t = String(type || '').toLowerCase();
    if (t === 'in') return `+${text}`;
    if (t === 'out') return `-${text}`;
    return text;
};

const fetchItems = async () => {
    const { data } = await axios.get('/api/stock-movements', {
        params: {
            per_page: perPage.value,
            page: currentPage.value,
            search: searchFilter.value || undefined,
            type: filters.value.type || undefined,
        },
    });
    items.value = data.data?.data || data.data || [];
    totalItems.value = data.data?.total || items.value.length;
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

const resetFilter = () => (filters.value = { type: '' });
const handleFilter = () => {
    currentPage.value = 1;
    fetchItems();
    isFilterDrawerOpen.value = false;
};

onMounted(fetchItems);
</script>
