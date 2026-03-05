<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="currentPageTitle" />
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-4">
            <div class="rounded-xl border border-gray-100 bg-white p-4 dark:border-gray-800 dark:bg-white/3">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Pelanggan</p>
                <p class="mt-1 text-2xl font-semibold">{{ stats.total }}</p>
            </div>
            <div class="rounded-xl border border-gray-100 bg-white p-4 dark:border-gray-800 dark:bg-white/3">
                <p class="text-sm text-gray-500 dark:text-gray-400">Aktif Bulan Ini</p>
                <p class="mt-1 text-2xl font-semibold">{{ stats.activeThisMonth }}</p>
            </div>
            <div class="rounded-xl border border-gray-100 bg-white p-4 dark:border-gray-800 dark:bg-white/3">
                <p class="text-sm text-gray-500 dark:text-gray-400">Member Aktif</p>
                <p class="mt-1 text-2xl font-semibold">{{ stats.activeMembers }}</p>
            </div>
            <div class="rounded-xl border border-gray-100 bg-white p-4 dark:border-gray-800 dark:bg-white/3">
                <p class="text-sm text-gray-500 dark:text-gray-400">Kunjungan Hari Ini</p>
                <p class="mt-1 text-2xl font-semibold">{{ stats.visitsToday }}</p>
            </div>
        </div>

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
                <Button v-can="'create_customers'" size="sm" variant="primary" :onClick="() => openDrawer()" className="w-full sm:w-auto" :endIcon="PlusIcon">
                    Tambah Pelanggan
                </Button>
            </template>
            <template #actions="{ row }">
                <div class="flex items-center gap-2">
                    <button v-can="'edit_customers'" @click="editItem(row)" class="text-gray-500 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-500" title="Edit">
                        <PencilIcon class="w-4.5 h-4.5" />
                    </button>
                    <button v-can="'delete_customers'" @click="deleteItem(row)" class="text-gray-500 hover:text-error-500 dark:text-gray-400 dark:hover:text-error-500" title="Hapus">
                        <TrashIcon class="w-4.5 h-4.5" />
                    </button>
                </div>
            </template>
        </DynamicTable>

        <Drawer :isOpen="isDrawerOpen" @close="closeDrawer" :title="form.id ? 'Edit Pelanggan' : 'Tambah Pelanggan'">
            <div class="space-y-6">
                <div>
                    <label for="name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Nama <span class="text-error-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        v-model="form.name"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Nama pelanggan"
                    />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-error-500">{{ form.errors.name }}</p>
                </div>
                <div>
                    <label for="phone" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Telepon
                    </label>
                    <input
                        type="text"
                        id="phone"
                        v-model="form.phone"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Nomor telepon"
                    />
                    <p v-if="form.errors.phone" class="mt-1 text-sm text-error-500">{{ form.errors.phone }}</p>
                </div>
                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Email
                    </label>
                    <input
                        type="email"
                        id="email"
                        v-model="form.email"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Alamat email"
                    />
                    <p v-if="form.errors.email" class="mt-1 text-sm text-error-500">{{ form.errors.email }}</p>
                </div>
                <div>
                    <label for="address" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Alamat
                    </label>
                    <textarea
                        id="address"
                        v-model="form.address"
                        rows="3"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Alamat pelanggan"
                    ></textarea>
                    <p v-if="form.errors.address" class="mt-1 text-sm text-error-500">{{ form.errors.address }}</p>
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

        <Drawer :isOpen="isFilterDrawerOpen" @close="isFilterDrawerOpen = false" title="Filter Pelanggan">
            <div class="space-y-6">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Cari Nama</label>
                    <input
                        type="text"
                        v-model="filters.name"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Masukkan nama"
                    />
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

const currentPageTitle = ref('Customers');
const isDrawerOpen = ref(false);
const isFilterDrawerOpen = ref(false);
const filters = ref({
    name: '',
});

const stats = ref({
    total: 0,
    activeThisMonth: 0,
    activeMembers: 0,
    visitsToday: 0,
});

const customers = ref<any[]>([]);
const totalItems = ref(0);
const currentPage = ref(1);
const perPage = ref(10);
const searchFilter = ref('');
const loading = ref(false);

const form = ref({
    id: null as string | null,
    name: '',
    phone: '',
    email: '',
    address: '',
    errors: {} as Record<string, string>,
    processing: false,
});

const tableData = computed(() => {
    return customers.value.map((c: any) => ({
        ...c,
        id: c.id,
        name: c.name,
        phone: c.phone || '-',
        email: c.email || '-',
        address: c.address || '-',
    }));
});

const columns = ref<Column[]>([
    { key: 'name', label: 'Nama', sortable: true, class: 'min-w-[200px] font-medium' },
    { key: 'phone', label: 'Telepon', class: 'min-w-[140px]' },
    { key: 'email', label: 'Email', class: 'min-w-[200px]' },
    { key: 'address', label: 'Alamat', class: 'min-w-[240px]' },
    { key: 'actions', label: 'Aksi', type: 'action', class: 'w-[120px]' },
]);

const fetchStats = async () => {
    // Placeholder: implement when API ready
    stats.value = { total: totalItems.value, activeThisMonth: 0, activeMembers: 0, visitsToday: 0 };
};

const fetchCustomers = async () => {
    loading.value = true;
    try {
        const { data } = await axios.get('/api/customers', {
            params: {
                per_page: perPage.value,
                page: currentPage.value,
                search: searchFilter.value || filters.value.name || undefined,
            },
        });
        customers.value = data.data?.data || data.data || [];
        totalItems.value = data.data?.total || customers.value.length;
        await fetchStats();
    } catch (e) {
        console.error('Error fetching customers', e);
    } finally {
        loading.value = false;
    }
};

const handlePageChange = (p: number) => {
    currentPage.value = p;
    fetchCustomers();
};
const handleSearch = (s: string) => {
    searchFilter.value = s;
    currentPage.value = 1;
    fetchCustomers();
};
const handlePerPageChange = (n: number) => {
    perPage.value = n;
    currentPage.value = 1;
    fetchCustomers();
};

const openDrawer = () => {
    form.value = { id: null, name: '', phone: '', email: '', address: '', errors: {}, processing: false };
    isDrawerOpen.value = true;
};
const closeDrawer = () => {
    isDrawerOpen.value = false;
};
const editItem = (row: any) => {
    form.value = {
        id: row.id,
        name: row.name,
        phone: row.phone,
        email: row.email,
        address: row.address,
        errors: {},
        processing: false,
    };
    isDrawerOpen.value = true;
};
const deleteItem = async (row: any) => {
    if (confirm('Hapus pelanggan ini?')) {
        try {
            await axios.delete(`/api/customers/${row.id}`);
            fetchCustomers();
        } catch (e) {
            console.error('Error deleting', e);
        }
    }
};

const saveItem = async () => {
    form.value.processing = true;
    form.value.errors = {};
    try {
        if (form.value.id) {
            await axios.put(`/api/customers/${form.value.id}`, {
                name: form.value.name,
                phone: form.value.phone,
                email: form.value.email,
                address: form.value.address,
            });
        } else {
            await axios.post('/api/customers', {
                name: form.value.name,
                phone: form.value.phone,
                email: form.value.email,
                address: form.value.address,
            });
        }
        closeDrawer();
        fetchCustomers();
    } catch (error: any) {
        if (error.response?.status === 422) {
            form.value.errors = error.response.data.errors || {};
        } else {
            console.error('Error saving', error);
        }
    } finally {
        form.value.processing = false;
    }
};

const resetFilter = () => {
    filters.value = { name: '' };
};
const handleFilter = () => {
    currentPage.value = 1;
    fetchCustomers();
    isFilterDrawerOpen.value = false;
};

onMounted(() => {
    fetchCustomers();
});
</script>
