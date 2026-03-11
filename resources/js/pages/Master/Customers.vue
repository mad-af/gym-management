<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="currentPageTitle" />
        <Stats2 :items="statsItems" />

        <DynamicTable :columns="columns" :data="tableData" :items-per-page="perPage" :total-items="totalItems"
            :current-page="currentPage" :is-server-side="true" @update:page="handlePageChange"
            @update:search="handleSearch" @update:perPage="handlePerPageChange">
            <template #header-actions>
                <Button size="sm" variant="outline" :onClick="() => isFilterDrawerOpen = true"
                    className="w-full sm:w-auto" :startIcon="FilterIcon">
                    Filter
                </Button>
                <Button v-can="'create_customers'" size="sm" variant="primary" :onClick="() => openDrawer()"
                    className="w-full sm:w-auto" :endIcon="PlusIcon">
                    Tambah Pelanggan
                </Button>
            </template>
            <template #actions="{ row }">
                <div class="flex items-center gap-2">
                    <button v-can="'edit_customers'" @click="editItem(row)"
                        class="text-gray-500 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-500"
                        title="Edit">
                        <PencilIcon class="w-4.5 h-4.5" />
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
                    <input type="text" id="name" v-model="form.name"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Nama pelanggan" />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-error-500">{{ form.errors.name }}</p>
                </div>
                <div>
                    <label for="phone" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Telepon
                    </label>
                    <input type="text" id="phone" v-model="form.phone"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Nomor telepon" />
                    <p v-if="form.errors.phone" class="mt-1 text-sm text-error-500">{{ form.errors.phone }}</p>
                </div>
                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Email
                    </label>
                    <input type="email" id="email" v-model="form.email"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Alamat email" />
                    <p v-if="form.errors.email" class="mt-1 text-sm text-error-500">{{ form.errors.email }}</p>
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
                    <input type="text" v-model="filters.name"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Masukkan nama" />
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
import Stats2 from '@/components/ui/Stats2.vue';
import { PlusIcon, PencilIcon, FilterIcon, UserGroupIcon, CalenderIcon, ShieldCheckIcon, DoorOpenIcon } from '@/icons';

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

const statsItems = computed(() => [
    {
        label: 'Total Pelanggan',
        value: stats.value.total,
        icon: UserGroupIcon,
        iconBgClass: 'bg-brand-50 text-brand-500 dark:bg-brand-500/10',
    },
    {
        label: 'Aktif Bulan Ini',
        value: stats.value.activeThisMonth,
        icon: CalenderIcon,
        iconBgClass: 'bg-blue-50 text-blue-500 dark:bg-blue-500/10',
    },
    {
        label: 'Member Aktif',
        value: stats.value.activeMembers,
        icon: ShieldCheckIcon,
        iconBgClass: 'bg-success-50 text-success-600 dark:bg-success-500/10',
    },
    {
        label: 'Kunjungan Hari Ini',
        value: stats.value.visitsToday,
        icon: DoorOpenIcon,
        iconBgClass: 'bg-purple-50 text-purple-500 dark:bg-purple-500/10',
    },
]);

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
    errors: {} as Record<string, string>,
    processing: false,
});

const tableData = computed(() => {
    return customers.value.map((c: any) => ({
        id: c.id,
        name: c.name,
        code: c.code || '-',
        avatar: c.avatar || null,
        phone: c.phone || '-',
        email: c.email || '-',
        membership_status: c.is_active_member ? 'Aktif' : 'Tidak Aktif',
        active_until: c.active_membership_until || null,
        active_package: c.active_membership_package_name || '-',
    }));
});

const columns = ref<Column[]>([
    { key: 'customer', label: 'Customer', sortable: true, type: 'avatar', labelField: 'name', subLabelField: 'code', class: 'min-w-[220px]' },
    { key: 'phone', label: 'Telepon', class: 'min-w-[120px] whitespace-nowrap' },
    { key: 'email', label: 'Email', class: 'min-w-[160px] whitespace-nowrap' },
    {
        key: 'membership_status',
        label: 'Status Member',
        type: 'status',
        class: 'min-w-[140px]',
        statusMap: {
            'Aktif': 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500',
            'Tidak Aktif': 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-500',
        },
    },
    { key: 'active_package', label: 'Paket Aktif', class: 'min-w-[140px] max-w-[200px] break-words' },
    { key: 'actions', label: 'Aksi', type: 'action', class: 'w-[120px]' },
]);

const fetchStats = async () => {
    try {
        const { data } = await axios.get('/api/customers/stats');
        const s = data.data || data;
        stats.value = {
            total: s.total ?? 0,
            activeThisMonth: s.activeThisMonth ?? 0,
            activeMembers: s.activeMembers ?? 0,
            visitsToday: s.visitsToday ?? 0,
        };
    } catch (e) {
        console.error('Error fetching stats', e);
    }
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
        fetchStats();
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
    form.value = { id: null, name: '', phone: '', email: '', errors: {}, processing: false };
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
        errors: {},
        processing: false,
    };
    isDrawerOpen.value = true;
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
            });
        } else {
            await axios.post('/api/customers', {
                name: form.value.name,
                phone: form.value.phone,
                email: form.value.email,
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
