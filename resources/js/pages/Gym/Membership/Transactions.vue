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
                <Button v-can="'create_membership_transactions'" size="sm" variant="primary" :onClick="openDrawer"
                    className="w-full sm:w-auto" :endIcon="PlusIcon">
                    Buat Membership
                </Button>
            </template>
        </DynamicTable>

        <Drawer :isOpen="isDrawerOpen" @close="closeDrawer" title="Buat Membership">
            <div class="space-y-6">
                <div>
                    <Combobox label="Pelanggan" v-model="form.customer_id" :options="customerOptions"
                        placeholder="Cari Pelanggan..." :searchable="true" :remote="true" @search="fetchCustomers"
                        :loading="loadingCustomers" value-key="id" label-key="name" />
                    <p v-if="form.errors.customer_id" class="mt-1 text-sm text-error-500">{{ form.errors.customer_id }}
                    </p>
                </div>
                <div>
                    <Combobox label="Paket" v-model="form.package_id" :options="packageOptions"
                        placeholder="Pilih Paket" value-key="id" label-key="name" />
                    <p v-if="form.errors.package_id" class="mt-1 text-sm text-error-500">{{ form.errors.package_id }}
                    </p>
                    <div v-if="selectedPackage" class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        <p>Harga: Rp {{ Number(selectedPackage.price).toLocaleString('id-ID') }}</p>
                        <p>Durasi: {{ selectedPackage.duration_days }} hari</p>
                    </div>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Tanggal Mulai</label>
                    <input type="date" v-model="form.start_date"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
                    <p v-if="form.errors.start_date" class="mt-1 text-sm text-error-500">{{ form.errors.start_date }}
                    </p>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Tanggal Selesai
                        (Estimasi)</label>
                    <input type="date" :value="estimatedEndDate" disabled
                        class="w-full rounded-lg border border-gray-300 bg-gray-100 px-4 py-3 text-sm text-gray-500 cursor-not-allowed dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400" />
                </div>
            </div>
            <template #footer>
                <div class="flex w-full justify-end gap-3">
                    <Button variant="outline" :onClick="closeDrawer">Batal</Button>
                    <Button variant="primary" :onClick="saveItem" :disabled="form.processing">Simpan</Button>
                </div>
            </template>
        </Drawer>

        <Drawer :isOpen="isFilterDrawerOpen" @close="isFilterDrawerOpen = false" title="Filter Membership">
            <div class="space-y-6">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Status</label>
                    <select v-model="filters.status"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                        <option value="">Semua</option>
                        <option value="active">Aktif</option>
                        <option value="expired">Expired</option>
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
import Combobox from '@/components/ui/Combobox.vue';
import Drawer from '@/components/ui/Drawer.vue';
import { PlusIcon, FilterIcon } from '@/icons';

const currentPageTitle = ref('Membership Transactions');
const isDrawerOpen = ref(false);
const isFilterDrawerOpen = ref(false);
const filters = ref({ status: '' });

const items = ref<any[]>([]);
const totalItems = ref(0);
const currentPage = ref(1);
const perPage = ref(10);
const searchFilter = ref('');

const customerOptions = ref<any[]>([]);
const packageOptions = ref<any[]>([]);
const loadingCustomers = ref(false);

const form = ref({
    customer_id: '',
    package_id: '',
    start_date: new Date().toISOString().split('T')[0],
    errors: {} as Record<string, string>,
    processing: false,
});

const selectedPackage = computed(() => {
    return packageOptions.value.find((p: any) => p.id === form.value.package_id);
});

const estimatedEndDate = computed(() => {
    if (!form.value.start_date || !selectedPackage.value) return '';
    const start = new Date(form.value.start_date);
    start.setDate(start.getDate() + selectedPackage.value.duration_days);
    return start.toISOString().split('T')[0];
});

const tableData = computed(() =>
    items.value.map((t: any) => ({
        id: t.id,
        customer_name: t.customer?.name || '-',
        package_name: t.package?.name || '-',
        start_date: t.start_date,
        end_date: t.end_date,
        status: t.status || '-',
    })),
);

const columns = ref<Column[]>([
    { key: 'customer_name', label: 'Pelanggan', class: 'min-w-[200px]' },
    { key: 'package_name', label: 'Paket', class: 'min-w-[200px]' },
    { key: 'start_date', label: 'Mulai', class: 'min-w-[140px]' },
    { key: 'end_date', label: 'Selesai', class: 'min-w-[140px]' },
    { key: 'status', label: 'Status', class: 'min-w-[120px]' },
]);

const fetchItems = async () => {
    const { data } = await axios.get('/api/membership-transactions', {
        params: {
            per_page: perPage.value,
            page: currentPage.value,
            search: searchFilter.value || undefined,
            status: filters.value.status || undefined,
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
    form.value = { customer_id: '', package_id: '', start_date: new Date().toISOString().split('T')[0], errors: {}, processing: false };
    isDrawerOpen.value = true;
};
const closeDrawer = () => (isDrawerOpen.value = false);

const saveItem = async () => {
    form.value.processing = true;
    form.value.errors = {};
    try {
        await axios.post('/api/membership-transactions', {
            customer_id: form.value.customer_id,
            package_id: form.value.package_id,
            start_date: form.value.start_date,
        });
        closeDrawer();
        fetchItems();
    } catch (error: any) {
        if (error.response?.status === 422) {
            form.value.errors = error.response.data.errors || {};
        } else {
            console.error('Error saving membership', error);
        }
    } finally {
        form.value.processing = false;
    }
};

const resetFilter = () => (filters.value = { status: '' });
const handleFilter = () => {
    currentPage.value = 1;
    fetchItems();
    isFilterDrawerOpen.value = false;
};

const fetchCustomers = async (query: string = '') => {
    loadingCustomers.value = true;
    try {
        const { data } = await axios.get('/api/customers', {
            params: { search: query, per_page: 20 },
        });
        customerOptions.value = data.data?.data || [];
    } catch (e) {
        console.error(e);
    } finally {
        loadingCustomers.value = false;
    }
};

const fetchPackages = async () => {
    try {
        const { data } = await axios.get('/api/membership-packages', {
            params: { is_active: true, per_page: 100 }
        });
        packageOptions.value = data.data?.data || [];
    } catch (e) {
        console.error(e);
    }
};

onMounted(() => {
    fetchItems();
    fetchPackages();
    fetchCustomers();
});
</script>
