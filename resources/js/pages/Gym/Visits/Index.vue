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
                <Button v-can="'create_visits'" size="sm" variant="primary" :onClick="openDrawer" className="w-full sm:w-auto" :endIcon="PlusIcon">
                    Check In
                </Button>
            </template>
        </DynamicTable>

        <Drawer :isOpen="isDrawerOpen" @close="closeDrawer" title="Check In">
            <div class="space-y-6">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Pelanggan</label>
                    <input type="text" v-model="form.customer_id" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" placeholder="ID pelanggan" />
                    <p v-if="form.errors.customer_id" class="mt-1 text-sm text-error-500">{{ form.errors.customer_id }}</p>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Jenis Kunjungan</label>
                    <select v-model="form.visit_type" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                        <option value="member">Member</option>
                        <option value="daily">Harian</option>
                    </select>
                    <p v-if="form.errors.visit_type" class="mt-1 text-sm text-error-500">{{ form.errors.visit_type }}</p>
                </div>
            </div>
            <template #footer>
                <div class="flex w-full justify-end gap-3">
                    <Button variant="outline" :onClick="closeDrawer">Batal</Button>
                    <Button variant="primary" :onClick="saveItem" :disabled="form.processing">Simpan</Button>
                </div>
            </template>
        </Drawer>

        <Drawer :isOpen="isFilterDrawerOpen" @close="isFilterDrawerOpen = false" title="Filter Kunjungan">
            <div class="space-y-6">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Jenis</label>
                    <select v-model="filters.visit_type" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                        <option value="">Semua</option>
                        <option value="member">Member</option>
                        <option value="daily">Harian</option>
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

const currentPageTitle = ref('Visits / Check In');
const isDrawerOpen = ref(false);
const isFilterDrawerOpen = ref(false);
const filters = ref({ visit_type: '' });

const items = ref<any[]>([]);
const totalItems = ref(0);
const currentPage = ref(1);
const perPage = ref(10);
const searchFilter = ref('');

const form = ref({
    customer_id: '',
    visit_type: 'member',
    errors: {} as Record<string, string>,
    processing: false,
});

const tableData = computed(() =>
    items.value.map((v: any) => ({
        id: v.id,
        customer_name: v.customer?.name || '-',
        visit_type: v.visit_type || '-',
        checkin_time: v.checkin_time || '-',
        created_by: v.creator?.name || '-',
    })),
);

const columns = ref<Column[]>([
    { key: 'customer_name', label: 'Pelanggan', class: 'min-w-[200px]' },
    { key: 'visit_type', label: 'Jenis', class: 'min-w-[120px]' },
    { key: 'checkin_time', label: 'Check-In', class: 'min-w-[180px]' },
    { key: 'created_by', label: 'Petugas', class: 'min-w-[180px]' },
]);

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

const openDrawer = () => {
    form.value = { customer_id: '', visit_type: 'member', errors: {}, processing: false };
    isDrawerOpen.value = true;
};
const closeDrawer = () => (isDrawerOpen.value = false);

const saveItem = async () => {
    form.value.processing = true;
    form.value.errors = {};
    try {
        await axios.post('/api/visits', {
            customer_id: form.value.customer_id,
            visit_type: form.value.visit_type,
        });
        closeDrawer();
        fetchItems();
    } catch (error: any) {
        if (error.response?.status === 422) {
            form.value.errors = error.response.data.errors || {};
        } else {
            console.error('Error saving visit', error);
        }
    } finally {
        form.value.processing = false;
    }
};

const resetFilter = () => (filters.value = { visit_type: '' });
const handleFilter = () => {
    currentPage.value = 1;
    fetchItems();
    isFilterDrawerOpen.value = false;
};

onMounted(fetchItems);
</script>
