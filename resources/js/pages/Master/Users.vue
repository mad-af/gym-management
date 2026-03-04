<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="currentPageTitle" />

        <DynamicTable :columns="columns" :data="tableData" :items-per-page="perPage" :total-items="totalItems"
            :current-page="currentPage" :is-server-side="true" :selectable="false" @update:page="handlePageChange"
            @update:search="handleSearch" @update:perPage="handlePerPageChange" @download="handleExportData">
            <template #header-actions>
                <Button size="sm" variant="outline" :onClick="() => isFilterDrawerOpen = true"
                    className="w-full sm:w-auto" :startIcon="FilterIcon">
                    Filter
                </Button>
                <Button v-can="USER_PERMISSIONS.CREATE" size="sm" variant="primary" :onClick="handleAddUser"
                    className="w-full sm:w-auto" :endIcon="PlusIcon">
                    Tambah Pengguna
                </Button>
            </template>
            <template #cell-opds="{ row }">
                <div class="flex flex-wrap items-center gap-1">
                    <span v-if="row.has_all_opds"
                        class="rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-medium text-brand-700 dark:bg-brand-500/15 dark:text-brand-400">
                        Semua OPD
                    </span>
                    <template v-else>
                        <span v-for="opd in row.opds.slice(0, 2)" :key="opd.id"
                            class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                            {{ opd.name }}
                        </span>
                        <Tooltip v-if="row.opds.length > 2" :content="row.opds.map((opd: any) => opd.name).join(', ')"
                            position="top">
                            <span
                                class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs text-gray-600 dark:bg-gray-800 dark:text-gray-300 cursor-default">
                                +{{ row.opds.length - 2 }}
                            </span>
                        </Tooltip>
                    </template>
                </div>
            </template>
            <template #actions="{ row }">
                <div class="flex items-center gap-2">
                    <button v-can="USER_PERMISSIONS.EDIT" @click="editItem(row)"
                        class="text-gray-500 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-500"
                        title="Edit">
                        <PencilIcon class="w-4.5 h-4.5" />
                    </button>

                    <button v-can="USER_PERMISSIONS.DELETE" v-if="row.is_active" @click="deactivateUser(row)"
                        class="text-gray-500 hover:text-error-500 dark:text-gray-400 dark:hover:text-error-500"
                        title="Nonaktifkan">
                        <TrashIcon class="w-4.5 h-4.5" />
                    </button>

                    <button v-can="USER_PERMISSIONS.ACTIVATE" v-else @click="activateUser(row)"
                        class="text-gray-500 hover:text-success-500 dark:text-gray-400 dark:hover:text-success-500"
                        title="Aktifkan">
                        <CheckCircleIcon class="w-4.5 h-4.5" />
                    </button>
                </div>
            </template>
        </DynamicTable>

        <!-- Add User Drawer -->
        <Drawer :isOpen="isDrawerOpen" @close="closeDrawer" :title="form.id ? 'Edit Pengguna' : 'Tambah Pengguna Baru'">
            <div class="space-y-6">
                <div class="flex justify-start">
                    <AvatarInput v-model="avatarFile" :src="currentAvatarSrc" :placeholder="currentAvatarPlaceholder"
                        size="large" />
                </div>
                <div>
                    <label for="name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Nama Lengkap <span class="text-error-500">*</span>
                    </label>
                    <input type="text" id="name" v-model="form.name"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Masukkan nama lengkap" />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-error-500">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Email <span class="text-error-500">*</span>
                    </label>
                    <input type="email" id="email" v-model="form.email"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Masukkan email" />
                    <p v-if="form.errors.email" class="mt-1 text-sm text-error-500">{{ form.errors.email }}</p>
                </div>

                <div>
                    <label for="phone" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                        No. Telepon (WhatsApp)
                    </label>
                    <input type="text" id="phone" v-model="form.phone"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Masukkan nomor telepon" />
                    <p v-if="form.errors.phone" class="mt-1 text-sm text-error-500">{{ form.errors.phone }}</p>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Role Pengguna <span class="text-error-500">*</span>
                    </label>
                    <Combobox v-model="form.roles" :options="roleOptions" labelKey="name" valueKey="name"
                        placeholder="Pilih role..." :loading="roleLoading" remote @search="onRoleSearch"
                        @load-more="onRoleLoadMore" />
                    <p v-if="form.errors.roles" class="mt-1 text-sm text-error-500">{{ form.errors.roles }}</p>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Hubungkan Pegawai
                    </label>
                    <Combobox v-model="form.employee_id" :options="employeeOptions" labelKey="name" valueKey="id"
                        placeholder="Pilih pegawai..." :loading="employeeLoading" :disabled="isEmployeeLocked" remote
                        @search="onEmployeeSearch" @load-more="onEmployeeLoadMore" />
                    <p v-if="form.errors.employee_id" class="mt-1 text-sm text-error-500">
                        {{ form.errors.employee_id }}
                    </p>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Akses OPD <span class="text-error-500">*</span>
                    </label>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Wajib pilih akses semua OPD atau pilih minimal satu OPD.
                    </p>
                    <div class="flex items-center gap-2">
                        <input id="has_all_opds" type="checkbox" v-model="form.has_all_opds"
                            class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800" />
                        <label for="has_all_opds" class="text-sm text-gray-700 dark:text-gray-200">
                            Akses semua OPD
                        </label>
                    </div>
                    <div v-if="!form.has_all_opds" class="mt-3">
                        <ComboboxMulti v-model="form.opd_ids" :options="opdOptions" labelKey="name" valueKey="id"
                            placeholder="Pilih OPD..." :loading="opdLoading" remote @search="onOpdSearch"
                            @load-more="onOpdLoadMore" />
                    </div>
                    <p v-if="form.errors.opd_ids" class="mt-1 text-sm text-error-500">
                        {{ form.errors.opd_ids }}
                    </p>
                </div>
            </div>

            <template #footer>
                <div class="flex w-full justify-end gap-3">
                    <Button variant="outline" :onClick="closeDrawer">
                        Batal
                    </Button>
                    <Button variant="primary" :onClick="saveUser" :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </Button>
                </div>
            </template>
        </Drawer>

        <!-- Filter Drawer -->
        <Drawer :isOpen="isFilterDrawerOpen" @close="isFilterDrawerOpen = false" title="Filter Pengguna">
            <div class="space-y-6">
                <!-- Role Filter -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Role
                    </label>
                    <Combobox v-model="filters.role_name" :options="roleOptions" labelKey="name" valueKey="name"
                        placeholder="Pilih role..." :loading="roleLoading" remote @search="onRoleSearch"
                        @load-more="onRoleLoadMore" />
                </div>

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
import { ref, computed, onMounted, watch } from 'vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import AvatarInput from '@/components/forms/FormElements/AvatarInput.vue';
import SelectInput from '@/components/forms/FormElements/SelectInput.vue';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import DynamicTable from '@/components/tables/data-tables/DynamicTable.vue';
import type { Column } from '@/components/tables/data-tables/DynamicTable.vue';
import Button from '@/components/ui/Button.vue';
import Combobox from '@/components/ui/Combobox.vue';
import ComboboxMulti from '@/components/ui/ComboboxMulti.vue';
import Drawer from '@/components/ui/Drawer.vue';
import Tooltip from '@/components/ui/Tooltip.vue';
import { USER_PERMISSIONS } from '@/directives/permissions';
import { PlusIcon, TrashIcon, CheckCircleIcon, PencilIcon, FilterIcon } from '@/icons';

defineProps({});

const currentPageTitle = ref('Manajemen Pengguna');
const isDrawerOpen = ref(false);
const isFilterDrawerOpen = ref(false);

// Local State for Data
const users = ref<any[]>([]);
const filters = ref({
    role_name: '',
    is_active: '' as string | number
});
const totalItems = ref(0);
const currentPage = ref(1);
const perPage = ref(10);
const searchFilter = ref('');

// Combobox State
const roleOptions = ref<any[]>([]);
const roleLoading = ref(false);
const roleSearch = ref('');
const rolePage = ref(1);
const roleHasMore = ref(true);

const employeeOptions = ref<any[]>([]);
const employeeLoading = ref(false);
const employeeSearch = ref('');
const employeePage = ref(1);
const employeeHasMore = ref(true);

const opdOptions = ref<any[]>([]);
const opdLoading = ref(false);
const opdSearch = ref('');
const opdPage = ref(1);
const opdHasMore = ref(true);

const statusOptions = [
    { value: 1, label: 'Active' },
    { value: 0, label: 'Inactive' },
];

const avatarFile = ref<File | null>(null);
const currentAvatar = ref<any | null>(null);

const currentAvatarSrc = computed(() => {
    if (currentAvatar.value && typeof currentAvatar.value === 'object') {
        return currentAvatar.value.url || '';
    }

    return '';
});

const currentAvatarPlaceholder = computed(() => {
    if (currentAvatar.value && typeof currentAvatar.value === 'object') {
        return currentAvatar.value.placeholder || '';
    }

    return '';
});

// Form handling
const form = useForm({
    id: null as number | null,
    name: '',
    email: '',
    phone: '',
    roles: '',
    employee_id: null as number | null,
    has_all_opds: false,
    opd_ids: [] as string[],
});

const isEmployeeLocked = computed(() => !!form.id && !!form.employee_id);

const resetForm = () => {
    form.reset();
    form.clearErrors();
    form.id = null;
    form.name = '';
    form.email = '';
    form.phone = '';
    form.roles = '';
    form.employee_id = null;
    form.has_all_opds = false;
    form.opd_ids = [];
    avatarFile.value = null;
    currentAvatar.value = null;
};

// Fetch Data Function
const fetchUsers = async () => {
    try {
        const response = await axios.get('/api/users', {
            params: {
                page: currentPage.value,
                per_page: perPage.value,
                search: searchFilter.value,
                role_name: filters.value.role_name,
                is_active: filters.value.is_active
            }
        });

        const data = response.data.data;
        users.value = data.data; // Paginator data
        totalItems.value = data.total;
        currentPage.value = data.current_page;
        perPage.value = data.per_page;

    } catch (error) {
        console.error('Error fetching users:', error);
    }
};

const fetchOpdOptions = async (reset = false) => {
    if (reset) {
        opdPage.value = 1;
        opdOptions.value = [];
        opdHasMore.value = true;
    }

    if (!opdHasMore.value && !reset) return;

    opdLoading.value = true;
    try {
        const response = await axios.get('/api/opds/selection', {
            params: {
                page: opdPage.value,
                per_page: 20,
                search: opdSearch.value,
            },
        });
        const data = response.data.data;
        if (reset) {
            opdOptions.value = data.data;
        } else {
            opdOptions.value = [...opdOptions.value, ...data.data];
        }
        opdHasMore.value = !!data.next_page_url;
        opdPage.value++;
    } catch (error) {
        console.error('Error fetching OPDs:', error);
    } finally {
        opdLoading.value = false;
    }
};

const onOpdSearch = (query: string) => {
    opdSearch.value = query;
    fetchOpdOptions(true);
};

const onOpdLoadMore = () => {
    fetchOpdOptions(false);
};

const fetchRoleOptions = async (reset = false) => {
    if (reset) {
        rolePage.value = 1;
        roleOptions.value = [];
        roleHasMore.value = true;
    }

    if (!roleHasMore.value && !reset) return;

    roleLoading.value = true;
    try {
        const response = await axios.get('/api/roles/selection', {
            params: {
                page: rolePage.value,
                per_page: 20,
                search: roleSearch.value
            }
        });
        const data = response.data.data;
        if (reset) {
            roleOptions.value = data.data;
        } else {
            roleOptions.value = [...roleOptions.value, ...data.data];
        }
        roleHasMore.value = !!data.next_page_url;
        rolePage.value++;
    } catch (error) {
        console.error('Error fetching roles:', error);
    } finally {
        roleLoading.value = false;
    }
};

const onRoleSearch = (query: string) => {
    roleSearch.value = query;
    fetchRoleOptions(true);
};

const onRoleLoadMore = () => {
    fetchRoleOptions(false);
};

const fetchEmployeeOptions = async (reset = false) => {
    if (reset) {
        employeePage.value = 1;
        employeeOptions.value = [];
        employeeHasMore.value = true;
    }

    if (!employeeHasMore.value && !reset) return;

    employeeLoading.value = true;
    try {
        const response = await axios.get('/api/employees/selection', {
            params: {
                page: employeePage.value,
                per_page: 20,
                search: employeeSearch.value,
                only_without_user: true,
            }
        });
        const data = response.data.data;
        if (reset) {
            employeeOptions.value = data.data;
        } else {
            employeeOptions.value = [...employeeOptions.value, ...data.data];
        }
        employeeHasMore.value = !!data.next_page_url;
        employeePage.value++;
    } catch (error) {
        console.error('Error fetching employees:', error);
    } finally {
        employeeLoading.value = false;
    }
};

const onEmployeeSearch = (query: string) => {
    employeeSearch.value = query;
    fetchEmployeeOptions(true);
};

const onEmployeeLoadMore = () => {
    fetchEmployeeOptions(false);
};

watch(isDrawerOpen, (newValue) => {
    if (newValue) {
        fetchRoleOptions(true);

        if (!isEmployeeLocked.value) {
            fetchEmployeeOptions(true);
        }

        fetchOpdOptions(true);
    }
});

watch(isFilterDrawerOpen, (newValue) => {
    if (newValue) {
        fetchRoleOptions(true);
    }
});

const handleFilter = () => {
    currentPage.value = 1;
    fetchUsers();
    isFilterDrawerOpen.value = false;
};

const resetFilter = () => {
    filters.value.role_name = '';
    filters.value.is_active = '';
    handleFilter();
};

// Initial Fetch
onMounted(() => {
    // Get search parameter from URL
    const urlSearch = new URLSearchParams(window.location.search).get('search');
    if (urlSearch) {
        searchFilter.value = urlSearch;
    }
    fetchUsers();
});

// Convert props data to table format
const tableData = computed(() => {
    return users.value.map((user: any) => ({
        id: user.id,
        name: user.name,
        email: user.email,
        phone: user.phone || '-',
        avatar: user.avatar,
        role: user.roles && user.roles.length > 0 ? user.roles[0].name : '-',
        status: user.is_active ? 'Active' : 'Inactive',
        ...user // Keep original data
    }));
});

const columns = ref<Column[]>([
    {
        key: 'name',
        label: 'Nama Lengkap',
        type: 'avatar',
        avatarField: 'avatar',
        labelField: 'name',
        subLabelField: 'email',
        class: 'min-w-[250px]'
    },
    {
        key: 'phone',
        label: 'No. Telepon',
        class: 'min-w-[150px]'
    },
    {
        key: 'opds',
        label: 'OPD',
        type: 'custom',
        class: 'min-w-[220px]'
    },
    {
        key: 'role',
        label: 'Role',
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
        key: 'actions',
        label: 'Aksi',
        type: 'action',
        class: 'w-[100px]'
    }
]);

const handlePageChange = (page: number) => {
    currentPage.value = page;
    fetchUsers();
};

const handlePerPageChange = (newPerPage: number) => {
    perPage.value = newPerPage;
    currentPage.value = 1; // Reset to first page
    fetchUsers();
};

const handleSearch = debounce((search: string) => {
    searchFilter.value = search;
    currentPage.value = 1; // Reset to first page
    fetchUsers();
}, 800);

const handleAddUser = () => {
    resetForm();
    isDrawerOpen.value = true;
};

const editItem = (item: any) => {
    resetForm();
    form.id = item.id;
    form.name = item.name;
    form.email = item.email;
    form.phone = item.phone || '';
    form.roles = item.roles && item.roles.length > 0 ? item.roles[0].name : '';
    form.employee_id = item.employee_id;

    form.has_all_opds = !!item.has_all_opds;
    form.opd_ids = item.has_all_opds
        ? []
        : (item.opds ? item.opds.map((opd: any) => opd.id) : []);

    currentAvatar.value = item.avatar || null;
    avatarFile.value = null;

    // Pre-fill options to ensure current value is displayed
    if (item.roles && item.roles.length > 0) {
        const role = item.roles[0];
        if (!roleOptions.value.find(r => r.name === role.name)) {
            roleOptions.value.push(role);
        }
    }

    if (item.employee) {
        if (!employeeOptions.value.find(e => e.id === item.employee.id)) {
            employeeOptions.value.push(item.employee);
        }
    }

    isDrawerOpen.value = true;
};

const deactivateUser = async (item: any) => {
    if (confirm('Apakah Anda yakin ingin menonaktifkan pengguna ini?')) {
        try {
            await axios.delete(`/api/users/${item.id}`);
            fetchUsers();
        } catch (error) {
            console.error('Error deactivating user:', error);
        }
    }
};

const activateUser = async (item: any) => {
    if (confirm('Apakah Anda yakin ingin mengaktifkan kembali pengguna ini?')) {
        try {
            await axios.put(`/api/users/${item.id}/activate`);
            fetchUsers();
        } catch (error) {
            console.error('Error activating user:', error);
        }
    }
};

const saveUser = async () => {
    form.processing = true;

    // Helper to extract value from object or return raw value
    const getValue = (val: any, key: string) => {
        if (val && typeof val === 'object') {
            return val[key];
        }
        return val;
    };

    // Transform data manually
    const roleName = getValue(form.roles, 'name');
    const employeeId = getValue(form.employee_id, 'id');

    const dataToSubmit: Record<string, any> = {
        ...form.data(),
        roles: roleName ? [roleName] : [],
        employee_id: employeeId,
    };

    const formData = new FormData();

    Object.entries(dataToSubmit).forEach(([key, value]) => {
        if (value === null || typeof value === 'undefined') {
            return;
        }

        if (Array.isArray(value)) {
            value.forEach((v) => {
                formData.append(`${key}[]`, String(v));
            });
        } else if (typeof value === 'boolean') {
            formData.append(key, value ? '1' : '0');
        } else if (typeof value === 'number') {
            formData.append(key, String(value));
        } else {
            formData.append(key, value as any);
        }
    });

    if (avatarFile.value) {
        formData.append('avatar', avatarFile.value);
    }

    try {
        if (form.id) {
            await axios.post(`/api/users/${form.id}?_method=PUT`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
        } else {
            await axios.post('/api/users', formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
        }
        closeDrawer();
        fetchUsers(); // Refresh data
    } catch (error: any) {
        if (error.response && error.response.data && error.response.data.errors) {
            form.errors = error.response.data.errors;
        } else {
            console.error('Error saving user:', error);
        }
    } finally {
        form.processing = false;
    }
};

const closeDrawer = () => {
    isDrawerOpen.value = false;
    resetForm();
};

const handleExportData = () => {
    // TODO: Implement export functionality
    alert('Fitur export belum tersedia.');
};
</script>
