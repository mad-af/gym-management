<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="currentPageTitle" />

        <DynamicTable :columns="columns" :data="tableData" :items-per-page="perPage" :total-items="totalItems"
            :current-page="currentPage" :is-server-side="true" @update:page="handlePageChange"
            @update:search="handleSearch" @update:perPage="handlePerPageChange"
            @update:selection="handleSelectionChange" @download="handleExportData">
            <template #header-actions>
                <Button size="sm" variant="outline" :onClick="() => isFilterDrawerOpen = true"
                    className="w-full sm:w-auto" :startIcon="FilterIcon">
                    Filter
                </Button>
                <Button v-can="ROLE_PERMISSIONS.CREATE" size="sm" variant="primary" :onClick="handleAddRole"
                    className="w-full sm:w-auto" :endIcon="PlusIcon">
                    Tambah Role
                </Button>
            </template>
            <template #cell-created_at="{ row }">
                <span class="text-sm text-gray-700 dark:text-gray-300">
                    {{ formatDate(row.created_at) }}
                </span>
            </template>
            <template #actions="{ row }">
                <div class="flex items-center gap-2">
                    <button v-can="ROLE_PERMISSIONS.EDIT" @click="editItem(row)"
                        class="text-gray-500 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-500"
                        title="Edit">
                        <PencilIcon class="w-4.5 h-4.5" />
                    </button>

                    <button v-can="ROLE_PERMISSIONS.DELETE" v-if="row.is_active" @click="deactivateRole(row)"
                        class="text-gray-500 hover:text-error-500 dark:text-gray-400 dark:hover:text-error-500"
                        title="Nonaktifkan">
                        <TrashIcon class="w-4.5 h-4.5" />
                    </button>

                    <button v-can="ROLE_PERMISSIONS.ACTIVATE" v-else @click="activateRole(row)"
                        class="text-gray-500 hover:text-success-500 dark:text-gray-400 dark:hover:text-success-500"
                        title="Aktifkan">
                        <CheckCircleIcon class="w-4.5 h-4.5" />
                    </button>
                </div>
            </template>
        </DynamicTable>

        <!-- Selected Items Display -->
        <div v-if="selectedItems.length > 0" class="mt-4 rounded-lg bg-gray-50 p-4 dark:bg-gray-900">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Selected items: {{selectedItems.map(item => item.name).join(', ')}}
            </p>
        </div>

        <!-- Add/Edit Role Drawer -->
        <Drawer :isOpen="isDrawerOpen" @close="closeDrawer" :title="form.id ? 'Edit Role' : 'Tambah Role Baru'">
            <div class="space-y-6">
                <!-- Name Input -->
                <div>
                    <label for="name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Nama Role <span class="text-error-500">*</span>
                    </label>
                    <input type="text" id="name" v-model="form.name"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Masukkan nama role" />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-error-500">{{ form.errors.name }}</p>
                </div>

                <!-- Permissions Selection -->
                <div>
                    <label class="mb-4 block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Hak Akses (Permissions)
                    </label>

                    <div class="space-y-6">
                        <div v-for="group in permissionsList" :key="group.group"
                            class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                            <h4 class="mb-3 text-sm font-semibold text-gray-800 dark:text-white">
                                {{ group.group }}
                            </h4>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <label v-for="permission in group.permissions" :key="permission.id"
                                    class="flex cursor-pointer items-start gap-3">
                                    <div class="flex items-center h-5">
                                        <input type="checkbox" :value="permission.id" v-model="form.permissions"
                                            class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800" />
                                    </div>
                                    <span class="text-sm text-gray-700 dark:text-gray-300 select-none">
                                        {{ permission.label }}
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <p v-if="form.errors.permissions" class="mt-1 text-sm text-error-500">{{ form.errors.permissions }}
                    </p>
                </div>
            </div>

            <template #footer>
                <div class="flex w-full justify-end gap-3">
                    <Button variant="outline" :onClick="closeDrawer">
                        Batal
                    </Button>
                    <Button variant="primary" :onClick="saveRole" :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </Button>
                </div>
            </template>
        </Drawer>

        <!-- Filter Drawer -->
        <Drawer :isOpen="isFilterDrawerOpen" @close="isFilterDrawerOpen = false" title="Filter Role">
            <div class="space-y-6">
                <!-- Status Filter -->
                <div>
                    <SelectInput v-model="filters.is_active" :options="statusOptions" label="Status"
                        placeholder="Semua Status" />
                </div>
            </div>
            <template #footer>
                <div class="flex w-full justify-end gap-3">
                    <Button variant="outline" :onClick="resetFilter">
                        Reset
                    </Button>
                    <Button variant="primary" :onClick="handleFilter">
                        Terapkan Filter
                    </Button>
                </div>
            </template>
        </Drawer>
    </AdminLayout>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { debounce } from 'lodash';
import { ref, computed, onMounted } from 'vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import SelectInput from '@/components/forms/FormElements/SelectInput.vue';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import DynamicTable from '@/components/tables/data-tables/DynamicTable.vue';
import type { Column } from '@/components/tables/data-tables/DynamicTable.vue';
import Button from '@/components/ui/Button.vue';
import Drawer from '@/components/ui/Drawer.vue';
import { ROLE_PERMISSIONS } from '@/directives/permissions';
import { PlusIcon, TrashIcon, CheckCircleIcon, PencilIcon, FilterIcon } from '@/icons';

defineProps({});

const formatDate = (date: string) => {
    if (!date) return '-';
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(date));
};

const currentPageTitle = ref('Role & Hak Akses');
const selectedItems = ref<any[]>([]);
const isDrawerOpen = ref(false);
const isFilterDrawerOpen = ref(false);

// Local State
const roles = ref<any[]>([]);
const permissionsList = ref<any[]>([]);
const totalItems = ref(0);
const currentPage = ref(1);
const perPage = ref(10);
const searchFilter = ref('');
const filters = ref({
    is_active: '' as string | number
});

const statusOptions = [
    { value: 1, label: 'Active' },
    { value: 0, label: 'Inactive' },
];

const form = useForm({
    id: null as number | null,
    name: '',
    permissions: [] as string[],
});

// Fetch Roles
const fetchRoles = async () => {
    try {
        const response = await axios.get('/api/roles', {
            params: {
                page: currentPage.value,
                per_page: perPage.value,
                search: searchFilter.value,
                is_active: filters.value.is_active
            }
        });

        const data = response.data.data;
        roles.value = data.data;
        totalItems.value = data.total;
        currentPage.value = data.current_page;
        perPage.value = data.per_page;
    } catch (error) {
        console.error('Error fetching roles:', error);
    }
};

// Fetch Permissions
const fetchPermissions = async () => {
    try {
        const response = await axios.get('/api/permissions');
        permissionsList.value = response.data.data;
    } catch (error) {
        console.error('Error fetching permissions:', error);
    }
};

const handleFilter = () => {
    currentPage.value = 1;
    isFilterDrawerOpen.value = false;
    fetchRoles();
};

const resetFilter = () => {
    filters.value = {
        is_active: ''
    };
    handleFilter();
};

// Initial Fetch
onMounted(() => {
    fetchRoles();
    fetchPermissions();
});

// Convert data to table format
const tableData = computed(() => {
    return roles.value.map((role: any) => ({
        id: role.id,
        name: role.name,
        description: `${role.permissions ? role.permissions.length : 0} Permissions`,
        users_count: role.users_count || 0,
        permissions_count: role.permissions ? role.permissions.length : 0,
        created_at: role.created_at,
        status: role.is_active ? 'Active' : 'Inactive',
        ...role
    }));
});

const columns = ref<Column[]>([
    {
        key: 'name',
        label: 'Nama Role',
        sortable: true,
        class: 'min-w-[150px] font-medium'
    },
    {
        key: 'permissions_count',
        label: 'Jumlah Hak Akses',
        sortable: false,
        class: 'min-w-[150px]'
    },
    {
        key: 'users_count',
        label: 'Jumlah Pengguna',
        sortable: false,
        class: 'min-w-[150px]'
    },
    {
        key: 'status',
        label: 'Status',
        type: 'status',
        statusMap: {
            'Active': 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500',
            'Inactive': 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-500'
        },
        class: 'min-w-[120px]'
    },
    {
        key: 'created_at',
        label: 'Dibuat Pada',
        type: 'custom',
        sortable: true,
        class: 'min-w-[150px]'
    },
    {
        key: 'actions',
        label: 'Aksi',
        type: 'action',
        sortable: false,
        class: 'text-center min-w-[120px]'
    }
]);

const handlePageChange = (page: number) => {
    currentPage.value = page;
    fetchRoles();
};

const handlePerPageChange = (newPerPage: number) => {
    perPage.value = newPerPage;
    currentPage.value = 1;
    fetchRoles();
};

const handleSearch = debounce((search: string) => {
    searchFilter.value = search;
    currentPage.value = 1;
    fetchRoles();
}, 500);

const handleSelectionChange = (items: any[]) => {
    selectedItems.value = items;
};

const handleAddRole = () => {
    form.reset();
    form.clearErrors();
    isDrawerOpen.value = true;
};

const editItem = (item: any) => {
    form.clearErrors();
    form.id = item.id;
    form.name = item.name;
    // Map existing permissions to array of strings
    form.permissions = item.permissions ? item.permissions.map((p: any) => p.name) : [];

    isDrawerOpen.value = true;
};

const deactivateRole = async (item: any) => {
    if (confirm('Apakah Anda yakin ingin menonaktifkan role ini?')) {
        try {
            await axios.delete(`/api/roles/${item.id}`);
            fetchRoles();
        } catch (error) {
            console.error('Error deactivating role:', error);
        }
    }
};

const activateRole = async (item: any) => {
    if (confirm('Apakah Anda yakin ingin mengaktifkan kembali role ini?')) {
        try {
            await axios.put(`/api/roles/${item.id}/activate`);
            fetchRoles();
        } catch (error) {
            console.error('Error activating role:', error);
        }
    }
};

const saveRole = async () => {
    form.processing = true;
    try {
        if (form.id) {
            await axios.put(`/api/roles/${form.id}`, form.data());
        } else {
            await axios.post('/api/roles', form.data());
        }
        closeDrawer();
        fetchRoles();
    } catch (error: any) {
        if (error.response && error.response.data && error.response.data.errors) {
            form.errors = error.response.data.errors;
        } else {
            console.error('Error saving role:', error);
        }
    } finally {
        form.processing = false;
    }
};

const closeDrawer = () => {
    isDrawerOpen.value = false;
    form.reset();
};

const handleExportData = () => {
    console.log('Export data');
};
</script>
