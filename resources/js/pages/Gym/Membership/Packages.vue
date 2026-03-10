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
                <Button v-can="'create_membership_packages'" size="sm" variant="primary" :onClick="openDrawer"
                    className="w-full sm:w-auto" :endIcon="PlusIcon">
                    Tambah Paket
                </Button>
            </template>
            <template #actions="{ row }">
                <div class="flex items-center gap-2">
                    <button v-can="'edit_membership_packages'" @click="editItem(row)"
                        class="text-gray-500 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-500"
                        title="Edit">
                        <PencilIcon class="w-4.5 h-4.5" />
                    </button>
                    <button v-can="'delete_membership_packages'" @click="deleteItem(row)"
                        class="text-gray-500 hover:text-error-500 dark:text-gray-400 dark:hover:text-error-500"
                        title="Hapus">
                        <TrashIcon class="w-4.5 h-4.5" />
                    </button>
                </div>
            </template>
        </DynamicTable>

        <Drawer :isOpen="isDrawerOpen" @close="closeDrawer" :title="form.id ? 'Edit Paket' : 'Tambah Paket'">
            <div class="space-y-6">
                <div>
                    <label for="name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Nama Paket <span class="text-error-500">*</span>
                    </label>
                    <input type="text" id="name" v-model="form.name"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-error-500">{{ form.errors.name }}</p>
                </div>
                <div>
                    <label for="price" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Harga <span class="text-error-500">*</span>
                    </label>
                    <input type="number" id="price" v-model.number="form.price"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
                    <p v-if="form.errors.price" class="mt-1 text-sm text-error-500">{{ form.errors.price }}</p>
                </div>
                <div>
                    <label for="duration_days" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Durasi (hari) <span class="text-error-500">*</span>
                    </label>
                    <input type="number" id="duration_days" v-model.number="form.duration_days"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
                    <p v-if="form.errors.duration_days" class="mt-1 text-sm text-error-500">{{ form.errors.duration_days
                        }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="is_active" v-model="form.is_active" />
                    <label for="is_active" class="text-sm text-gray-700 dark:text-gray-200">Aktif</label>
                </div>
            </div>
            <template #footer>
                <div class="flex w-full justify-end gap-3">
                    <Button variant="outline" :onClick="closeDrawer">Batal</Button>
                    <Button variant="primary" :onClick="saveItem" :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </Button>
                </div>
            </template>
        </Drawer>

        <Drawer :isOpen="isFilterDrawerOpen" @close="isFilterDrawerOpen = false" title="Filter Paket">
            <div class="space-y-6">
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="only_active" v-model="filters.only_active" />
                    <label for="only_active" class="text-sm text-gray-700 dark:text-gray-200">Hanya aktif</label>
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

const currentPageTitle = ref('Membership Packages');
const isDrawerOpen = ref(false);
const isFilterDrawerOpen = ref(false);
const filters = ref({ only_active: true });

const packages = ref<any[]>([]);
const totalItems = ref(0);
const currentPage = ref(1);
const perPage = ref(10);
const searchFilter = ref('');

const form = ref({
    id: null as string | null,
    name: '',
    price: 0,
    duration_days: 30,
    is_active: true,
    errors: {} as Record<string, string>,
    processing: false,
});

const tableData = computed(() => {
    const rows: any[] = [];
    packages.value.forEach((p: any) => {
        const items = p.items && p.items.length > 0 ? p.items : [{}];
        const rowSpan = items.length;

        items.forEach((item: any, index: number) => {
            const isFirst = index === 0;
            const row: any = {
                ...p,
                id: p.id,
                name: p.name,
                price: p.price,
                duration_days: p.duration_days,
                is_active: p.is_active ? 'Active' : 'Inactive',
                item_name: item.item_name || '-',
                item_quantity: item.quantity || '-',
                item_unit: item.unit || '-',
                _key: `${p.id}_${index}`,
                _cellAttributes: {}
            };

            if (isFirst) {
                row._cellAttributes = {
                    name: { rowspan: rowSpan },
                    price: { rowspan: rowSpan },
                    duration_days: { rowspan: rowSpan },
                    is_active: { rowspan: rowSpan },
                    actions: { rowspan: rowSpan },
                    checkbox: { rowspan: rowSpan },
                };
            } else {
                row._cellAttributes = {
                    name: { hidden: true },
                    price: { hidden: true },
                    duration_days: { hidden: true },
                    is_active: { hidden: true },
                    actions: { hidden: true },
                    checkbox: { hidden: true },
                };
            }
            rows.push(row);
        });
    });
    return rows;
});

const columns = ref<Column[]>([
    { key: 'name', label: 'Nama Paket', sortable: true, class: 'min-w-[200px] font-medium align-top' },
    { key: 'price', label: 'Harga', class: 'min-w-[120px] align-top' },
    { key: 'duration_days', label: 'Durasi (hari)', class: 'min-w-[140px] align-top' },
    { key: 'is_active', label: 'Status', type: 'status', class: 'min-w-[120px] align-top', statusMap: { Active: 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500', Inactive: 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-500' } },
    { key: 'item_name', label: 'Item Name', class: 'min-w-[150px]' },
    { key: 'item_quantity', label: 'Qty', class: 'min-w-[80px]' },
    { key: 'item_unit', label: 'Unit', class: 'min-w-[80px]' },
    { key: 'actions', label: 'Aksi', type: 'action', class: 'w-[120px] align-top' },
]);

const fetchPackages = async () => {
    const { data } = await axios.get('/api/membership-packages', {
        params: {
            per_page: perPage.value,
            page: currentPage.value,
            search: searchFilter.value || undefined,
            is_active: filters.value.only_active ? true : undefined,
        },
    });
    packages.value = data.data?.data || data.data || [];
    totalItems.value = data.data?.total || packages.value.length;
};

const handlePageChange = (p: number) => {
    currentPage.value = p;
    fetchPackages();
};
const handleSearch = (s: string) => {
    searchFilter.value = s;
    currentPage.value = 1;
    fetchPackages();
};
const handlePerPageChange = (n: number) => {
    perPage.value = n;
    currentPage.value = 1;
    fetchPackages();
};

const openDrawer = () => {
    form.value = { id: null, name: '', price: 0, duration_days: 30, is_active: true, errors: {}, processing: false };
    isDrawerOpen.value = true;
};
const closeDrawer = () => (isDrawerOpen.value = false);
const editItem = (row: any) => {
    form.value = { id: row.id, name: row.name, price: row.price, duration_days: row.duration_days, is_active: row.is_active === 'Active', errors: {}, processing: false };
    isDrawerOpen.value = true;
};
const deleteItem = async (row: any) => {
    if (confirm('Hapus paket ini?')) {
        await axios.delete(`/api/membership-packages/${row.id}`);
        fetchPackages();
    }
};

const saveItem = async () => {
    form.value.processing = true;
    form.value.errors = {};
    try {
        const payload = { name: form.value.name, price: form.value.price, duration_days: form.value.duration_days, is_active: form.value.is_active };
        if (form.value.id) {
            await axios.put(`/api/membership-packages/${form.value.id}`, payload);
        } else {
            await axios.post('/api/membership-packages', payload);
        }
        closeDrawer();
        fetchPackages();
    } catch (error: any) {
        if (error.response?.status === 422) {
            form.value.errors = error.response.data.errors || {};
        } else {
            console.error('Error saving package', error);
        }
    } finally {
        form.value.processing = false;
    }
};

const resetFilter = () => (filters.value = { only_active: true });
const handleFilter = () => {
    currentPage.value = 1;
    fetchPackages();
    isFilterDrawerOpen.value = false;
};

onMounted(fetchPackages);
</script>
