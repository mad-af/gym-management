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
                <Button v-can="'create_stock_movements'" size="sm" variant="primary" :onClick="openDrawer" className="w-full sm:w-auto" :endIcon="PlusIcon">
                    Tambah Stok
                </Button>
            </template>
        </DynamicTable>

        <Drawer :isOpen="isDrawerOpen" @close="closeDrawer" title="Tambah/Adjust Stok">
            <div class="space-y-6">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Produk</label>
                    <input type="text" v-model="form.product_id" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" placeholder="ID Produk" />
                    <p v-if="form.errors.product_id" class="mt-1 text-sm text-error-500">{{ form.errors.product_id }}</p>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Jenis</label>
                    <select v-model="form.type" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                        <option value="in">IN</option>
                        <option value="adjustment">ADJUSTMENT</option>
                    </select>
                    <p v-if="form.errors.type" class="mt-1 text-sm text-error-500">{{ form.errors.type }}</p>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Jumlah</label>
                    <input type="number" v-model.number="form.quantity" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
                    <p v-if="form.errors.quantity" class="mt-1 text-sm text-error-500">{{ form.errors.quantity }}</p>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Catatan</label>
                    <input type="text" v-model="form.note" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
                </div>
            </div>
            <template #footer>
                <div class="flex w-full justify-end gap-3">
                    <Button variant="outline" :onClick="closeDrawer">Batal</Button>
                    <Button variant="primary" :onClick="saveItem" :disabled="form.processing">Simpan</Button>
                </div>
            </template>
        </Drawer>

        <Drawer :isOpen="isFilterDrawerOpen" @close="isFilterDrawerOpen = false" title="Filter Pergerakan Stok">
            <div class="space-y-6">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Jenis</label>
                    <select v-model="filters.type" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
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
import Button from '@/components/ui/Button.vue';
import Drawer from '@/components/ui/Drawer.vue';
import { PlusIcon, FilterIcon } from '@/icons';

const currentPageTitle = ref('Stock Movements');
const isDrawerOpen = ref(false);
const isFilterDrawerOpen = ref(false);
const filters = ref({ type: '' });

const items = ref<any[]>([]);
const totalItems = ref(0);
const currentPage = ref(1);
const perPage = ref(10);
const searchFilter = ref('');

const form = ref({
    product_id: '',
    type: 'in',
    quantity: 0,
    note: '',
    errors: {} as Record<string, string>,
    processing: false,
});

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
    { key: 'type', label: 'Jenis', class: 'min-w-[120px]' },
    { key: 'quantity', label: 'Jumlah', class: 'min-w-[120px]' },
    { key: 'created_at', label: 'Tanggal', class: 'min-w-[160px]' },
]);

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

const openDrawer = () => {
    form.value = { product_id: '', type: 'in', quantity: 0, note: '', errors: {}, processing: false };
    isDrawerOpen.value = true;
};
const closeDrawer = () => (isDrawerOpen.value = false);

const saveItem = async () => {
    form.value.processing = true;
    form.value.errors = {};
    try {
        await axios.post('/api/stock-movements', {
            product_id: form.value.product_id,
            type: form.value.type,
            quantity: form.value.quantity,
            note: form.value.note,
        });
        closeDrawer();
        fetchItems();
    } catch (error: any) {
        if (error.response?.status === 422) {
            form.value.errors = error.response.data.errors || {};
        } else {
            console.error('Error saving stock movement', error);
        }
    } finally {
        form.value.processing = false;
    }
};

const resetFilter = () => (filters.value = { type: '' });
const handleFilter = () => {
    currentPage.value = 1;
    fetchItems();
    isFilterDrawerOpen.value = false;
};

onMounted(fetchItems);
</script>
