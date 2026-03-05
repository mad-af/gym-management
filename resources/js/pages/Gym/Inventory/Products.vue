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
                <Button v-can="'create_products'" size="sm" variant="primary" :onClick="openDrawer" className="w-full sm:w-auto" :endIcon="PlusIcon">
                    Tambah Produk
                </Button>
            </template>
            <template #actions="{ row }">
                <div class="flex items-center gap-2">
                    <button v-can="'edit_products'" @click="editItem(row)" class="text-gray-500 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-500" title="Edit">
                        <PencilIcon class="w-4.5 h-4.5" />
                    </button>
                    <button v-can="'delete_products'" @click="deleteItem(row)" class="text-gray-500 hover:text-error-500 dark:text-gray-400 dark:hover:text-error-500" title="Hapus">
                        <TrashIcon class="w-4.5 h-4.5" />
                    </button>
                </div>
            </template>
        </DynamicTable>

        <Drawer :isOpen="isDrawerOpen" @close="closeDrawer" :title="form.id ? 'Edit Produk' : 'Tambah Produk'">
            <div class="space-y-6">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Nama Produk</label>
                    <input type="text" v-model="form.name" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-error-500">{{ form.errors.name }}</p>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Harga</label>
                    <input type="number" v-model.number="form.price" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
                    <p v-if="form.errors.price" class="mt-1 text-sm text-error-500">{{ form.errors.price }}</p>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Stok</label>
                    <input type="number" v-model.number="form.stock" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
                    <p v-if="form.errors.stock" class="mt-1 text-sm text-error-500">{{ form.errors.stock }}</p>
                </div>
            </div>
            <template #footer>
                <div class="flex w-full justify-end gap-3">
                    <Button variant="outline" :onClick="closeDrawer">Batal</Button>
                    <Button variant="primary" :onClick="saveItem" :disabled="form.processing">Simpan</Button>
                </div>
            </template>
        </Drawer>

        <Drawer :isOpen="isFilterDrawerOpen" @close="isFilterDrawerOpen = false" title="Filter Produk">
            <div class="space-y-6">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Min Stok</label>
                    <input type="number" v-model.number="filters.min_stock" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
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
import { PlusIcon, TrashIcon, PencilIcon, FilterIcon } from '@/icons';

const currentPageTitle = ref('Products');
const isDrawerOpen = ref(false);
const isFilterDrawerOpen = ref(false);
const filters = ref({ min_stock: 0 });

const items = ref<any[]>([]);
const totalItems = ref(0);
const currentPage = ref(1);
const perPage = ref(10);
const searchFilter = ref('');

const form = ref({
    id: null as string | null,
    name: '',
    price: 0,
    stock: 0,
    errors: {} as Record<string, string>,
    processing: false,
});

const tableData = computed(() =>
    items.value.map((p: any) => ({
        id: p.id,
        name: p.name,
        price: p.price,
        stock: p.stock ?? '-',
    })),
);

const columns = ref<Column[]>([
    { key: 'name', label: 'Nama Produk', sortable: true, class: 'min-w-[200px]' },
    { key: 'price', label: 'Harga', class: 'min-w-[120px]' },
    { key: 'stock', label: 'Stok', class: 'min-w-[100px]' },
    { key: 'actions', label: 'Aksi', type: 'action', class: 'w-[120px]' },
]);

const fetchItems = async () => {
    const { data } = await axios.get('/api/products', {
        params: {
            per_page: perPage.value,
            page: currentPage.value,
            search: searchFilter.value || undefined,
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
    form.value = { id: null, name: '', price: 0, stock: 0, errors: {}, processing: false };
    isDrawerOpen.value = true;
};
const closeDrawer = () => (isDrawerOpen.value = false);
const editItem = (row: any) => {
    form.value = { id: row.id, name: row.name, price: row.price, stock: row.stock === '-' ? 0 : row.stock, errors: {}, processing: false };
    isDrawerOpen.value = true;
};

const deleteItem = async (row: any) => {
    if (confirm('Hapus produk ini?')) {
        await axios.delete(`/api/products/${row.id}`);
        fetchItems();
    }
};

const saveItem = async () => {
    form.value.processing = true;
    form.value.errors = {};
    try {
        const payload: any = { name: form.value.name, price: form.value.price };
        if (form.value.stock !== null) payload.stock = form.value.stock;
        if (form.value.id) {
            await axios.put(`/api/products/${form.value.id}`, payload);
        } else {
            await axios.post('/api/products', payload);
        }
        closeDrawer();
        fetchItems();
    } catch (error: any) {
        if (error.response?.status === 422) {
            form.value.errors = error.response.data.errors || {};
        } else {
            console.error('Error saving product', error);
        }
    } finally {
        form.value.processing = false;
    }
};

const resetFilter = () => (filters.value = { min_stock: 0 });
const handleFilter = () => {
    currentPage.value = 1;
    fetchItems();
    isFilterDrawerOpen.value = false;
};

onMounted(fetchItems);
</script>
