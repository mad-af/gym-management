<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="currentPageTitle" />

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
                <Button size="sm" variant="outline" :onClick="() => isFilterDrawerOpen = true" className="w-full sm:w-auto" :startIcon="FilterIcon">
                    Filter
                </Button>
                <Button v-can="'create_sales'" size="sm" variant="primary" :onClick="openDrawer" className="w-full sm:w-auto" :endIcon="PlusIcon">
                    Buat Penjualan
                </Button>
            </template>
            <template #actions="{ row }">
                <a :href="`/gym/sales/${row.id}`" class="text-brand-600 hover:underline">Detail</a>
            </template>
        </DynamicTable>

        <Drawer :isOpen="isDrawerOpen" @close="closeDrawer" title="Buat Penjualan">
            <div class="space-y-6">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Pelanggan</label>
                    <input type="text" v-model="form.customer_id" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" placeholder="ID pelanggan" />
                    <p v-if="form.errors.customer_id" class="mt-1 text-sm text-error-500">{{ form.errors.customer_id }}</p>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Items (JSON)</label>
                    <textarea v-model="form.items_json" rows="4" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" placeholder='[{"product_id":"","quantity":1,"price":0,"subtotal":0}]'></textarea>
                    <p v-if="form.errors.items" class="mt-1 text-sm text-error-500">{{ form.errors.items }}</p>
                </div>
            </div>
            <template #footer>
                <div class="flex w-full justify-end gap-3">
                    <Button variant="outline" :onClick="closeDrawer">Batal</Button>
                    <Button variant="primary" :onClick="saveItem" :disabled="form.processing">Simpan</Button>
                </div>
            </template>
        </Drawer>

        <Drawer :isOpen="isFilterDrawerOpen" @close="isFilterDrawerOpen = false" title="Filter Penjualan">
            <div class="space-y-6">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Pelanggan</label>
                    <input type="text" v-model="filters.customer_id" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
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
import { PlusIcon, FilterIcon } from '@/icons';

const currentPageTitle = ref('Sales');
const isDrawerOpen = ref(false);
const isFilterDrawerOpen = ref(false);
const filters = ref({ customer_id: '' });

const items = ref<any[]>([]);
const totalItems = ref(0);
const currentPage = ref(1);
const perPage = ref(10);
const searchFilter = ref('');

const form = ref({
    customer_id: '',
    items_json: '[]',
    errors: {} as Record<string, string>,
    processing: false,
});

const tableData = computed(() =>
    items.value.map((s: any) => ({
        id: s.id,
        customer_name: s.customer?.name || '-',
        total_amount: s.total_amount,
        created_at: s.created_at,
    })),
);

const columns = ref<Column[]>([
    { key: 'customer_name', label: 'Pelanggan', class: 'min-w-[200px]' },
    { key: 'total_amount', label: 'Total', class: 'min-w-[140px]' },
    { key: 'created_at', label: 'Tanggal', class: 'min-w-[160px]' },
    { key: 'actions', label: 'Aksi', type: 'action', class: 'w-[120px]' },
]);

const fetchItems = async () => {
    const { data } = await axios.get('/api/sales', {
        params: {
            per_page: perPage.value,
            page: currentPage.value,
            search: searchFilter.value || undefined,
            customer_id: filters.value.customer_id || undefined,
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

const openDrawer = () => {
    form.value = { customer_id: '', items_json: '[]', errors: {}, processing: false };
    isDrawerOpen.value = true;
};
const closeDrawer = () => (isDrawerOpen.value = false);

const saveItem = async () => {
    form.value.processing = true;
    form.value.errors = {};
    try {
        const items = JSON.parse(form.value.items_json);
        const total_amount = items.reduce((sum: number, it: any) => sum + (it.subtotal || 0), 0);
        await axios.post('/api/sales', { customer_id: form.value.customer_id, items, total_amount });
        closeDrawer();
        fetchItems();
    } catch (error: any) {
        if (error.response?.status === 422) {
            form.value.errors = error.response.data.errors || {};
        } else {
            console.error('Error saving sale', error);
        }
    } finally {
        form.value.processing = false;
    }
};

const resetFilter = () => (filters.value = { customer_id: '' });
const handleFilter = () => {
    currentPage.value = 1;
    fetchItems();
    isFilterDrawerOpen.value = false;
};

onMounted(fetchItems);
</script>
