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
            <template #cell-visit_type="{ row }">
                <Badge size="sm" :color="getVisitTypeBadgeColor(row.visit_type)">
                    {{ getVisitTypeLabel(row.visit_type) }}
                </Badge>
            </template>
            <template #cell-price="{ row }">
                <span v-if="String(row.visit_type || '').toUpperCase() === 'MEMBERSHIP'" class="text-theme-sm font-medium text-gray-800 dark:text-white/90">
                    -
                </span>
                <span v-else class="text-theme-sm font-medium text-gray-800 dark:text-white/90">
                    {{ formatCurrencyId(row.price) }}
                </span>
            </template>
            <template #cell-checkin_time="{ row }">
                <span class="text-theme-sm text-gray-700 dark:text-gray-400">
                    {{ formatDateTimeId(row.checkin_time) }}
                </span>
            </template>
        </DynamicTable>

        <Drawer :isOpen="isFilterDrawerOpen" @close="isFilterDrawerOpen = false" title="Filter Kunjungan">
            <div class="space-y-6">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Jenis</label>
                    <select v-model="filters.visit_type"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                        <option value="">Semua</option>
                        <option value="MEMBERSHIP">Member</option>
                        <option value="DAILY">Harian</option>
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

const currentPageTitle = ref('Visits / Check In');
const isFilterDrawerOpen = ref(false);
const filters = ref({ visit_type: '' });

const items = ref<any[]>([]);
const totalItems = ref(0);
const currentPage = ref(1);
const perPage = ref(10);
const searchFilter = ref('');

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
    { key: 'visit_type', label: 'Jenis', type: 'custom', class: 'min-w-[120px]' },
    { key: 'price', label: 'Harga', type: 'custom', class: 'min-w-[140px] text-right' },
    { key: 'checkin_time', label: 'Check-In', type: 'custom', class: 'min-w-[180px]' },
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

const fetchItems = async () => {
    const { data } = await axios.get('/api/visits', {
        params: {
            per_page: perPage.value,
            page: currentPage.value,
            search: searchFilter.value || undefined,
            visit_type: filters.value.visit_type || undefined,
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

const resetFilter = () => (filters.value = { visit_type: '' });
const handleFilter = () => {
    currentPage.value = 1;
    fetchItems();
    isFilterDrawerOpen.value = false;
};

onMounted(() => {
    fetchItems();
});
</script>
