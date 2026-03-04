<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="currentPageTitle" />

        <DynamicTable :columns="columns" :data="tableData" :items-per-page="perPage" :total-items="totalItems"
            :current-page="currentPage" :is-server-side="true" @update:page="handlePageChange"
            @update:search="handleSearch" @update:perPage="handlePerPageChange"
            @update:selection="handleSelectionChange" @edit="editItem" @download="handleExportData">
            <template #actions="{ row }">
                <div class="flex items-center gap-2">
                    <Link v-if="row.has_user"
                        :href="`/users?search=${encodeURIComponent(row.user_name || row.user_email || '')}`"
                        class="text-gray-500 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-500"
                        title="Lihat User">
                        <UserCircleIcon class="w-4.5 h-4.5" />
                    </Link>
                    <button v-can="EMPLOYEE_PERMISSIONS.EDIT" @click="editItem(row)"
                        class="text-gray-500 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-500"
                        title="Edit">
                        <PencilIcon class="w-4.5 h-4.5" />
                    </button>
                    <button v-can="EMPLOYEE_PERMISSIONS.DELETE"
                        v-if="row.status === 'Active' || row.status === 'active'" @click="deactivateEmployee(row)"
                        class="text-gray-500 hover:text-error-500 dark:text-gray-400 dark:hover:text-error-500"
                        title="Nonaktifkan">
                        <TrashIcon class="w-4.5 h-4.5" />
                    </button>
                    <button v-can="EMPLOYEE_PERMISSIONS.ACTIVATE" v-else @click="activateEmployee(row)"
                        class="text-gray-500 hover:text-success-500 dark:text-gray-400 dark:hover:text-success-500"
                        title="Aktifkan">
                        <CheckCircleIcon class="w-4.5 h-4.5" />
                    </button>
                </div>
            </template>
            <template #header-actions>
                <Button size="sm" variant="outline" :onClick="() => isFilterDrawerOpen = true"
                    className="w-full sm:w-auto" :startIcon="FilterIcon">
                    Filter
                </Button>
                <Button v-can="EMPLOYEE_PERMISSIONS.CREATE" size="sm" variant="primary" :onClick="handleAddEmployee"
                    className="w-full sm:w-auto" :endIcon="PlusIcon">
                    Tambah Pegawai
                </Button>
            </template>
        </DynamicTable>

        <!-- Selected Items Display -->
        <div v-if="selectedItems.length > 0" class="mt-4 rounded-lg bg-gray-50 p-4 dark:bg-gray-900">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Selected items: {{selectedItems.map(item => item.name).join(', ')}}
            </p>
        </div>

        <!-- Add/Edit Employee Drawer -->
        <Drawer :isOpen="isDrawerOpen" @close="closeDrawer" :title="form.id ? 'Edit Pegawai' : 'Tambah Pegawai Baru'">
            <div class="space-y-6">
                <div class="flex justify-start">
                    <AvatarInput v-model="avatarFile" :src="currentAvatarSrc" :placeholder="currentAvatarPlaceholder"
                        size="large" />
                </div>
                <!-- NIP Input -->
                <div>
                    <label for="nip" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                        NIP <span class="text-error-500">*</span>
                    </label>
                    <input type="text" id="nip" v-model="form.nip"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Masukkan NIP" />
                    <p v-if="form.errors.nip" class="mt-1 text-sm text-error-500">{{ form.errors.nip }}</p>
                </div>

                <!-- Name Input -->
                <div>
                    <label for="name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Nama Pegawai <span class="text-error-500">*</span>
                    </label>
                    <input type="text" id="name" v-model="form.name"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Masukkan nama pegawai" />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-error-500">{{ form.errors.name }}</p>
                </div>

                <!-- Position Input -->
                <div>
                    <label for="position" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Jabatan <span class="text-error-500">*</span>
                    </label>
                    <input type="text" id="position" v-model="form.position"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Masukkan jabatan" />
                    <p v-if="form.errors.position" class="mt-1 text-sm text-error-500">{{ form.errors.position }}</p>
                </div>

                <!-- OPD Selection -->
                <div v-if="!currentOpdSet" class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        OPD <span class="text-error-500">*</span>
                    </label>
                    <Combobox v-model="form.opd_id" :options="opdOptions" labelKey="name" valueKey="id"
                        placeholder="Pilih OPD..." :loading="opdLoading" remote @search="onOpdSearch"
                        @load-more="onOpdLoadMore" />
                    <p v-if="form.errors.opd_id" class="mt-1 text-sm text-error-500">{{ form.errors.opd_id }}</p>
                </div>
            </div>

            <template #footer>
                <div class="flex w-full justify-end gap-3">
                    <Button variant="outline" :onClick="closeDrawer">
                        Batal
                    </Button>
                    <Button variant="primary" :onClick="saveEmployee" :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </Button>
                </div>
            </template>
        </Drawer>

        <!-- Filter Drawer -->
        <Drawer :isOpen="isFilterDrawerOpen" @close="isFilterDrawerOpen = false" title="Filter Pegawai">
            <div class="space-y-6">
                <!-- OPD Filter -->
                <div>
                    <Combobox label="OPD" v-model="filters.opd_id" :options="opdOptions" valueKey="id" labelKey="name"
                        placeholder="Pilih OPD" :loading="opdLoading" remote @search="onOpdSearch"
                        @load-more="onOpdLoadMore" />
                </div>

                <!-- Status Filter -->
                <div>
                    <SelectInput v-model="filters.status" :options="statusOptions" label="Status"
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
import { useForm, Link, usePage } from '@inertiajs/vue3';
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
import Drawer from '@/components/ui/Drawer.vue';
import { EMPLOYEE_PERMISSIONS } from '@/directives/permissions';
import { PlusIcon, TrashIcon, CheckCircleIcon, PencilIcon, FilterIcon, UserCircleIcon } from '@/icons';

const page = usePage();
const currentOpd = computed(() => (page.props.auth as any).current_opd);
const currentOpdSet = computed(() => !!currentOpd.value);

const currentPageTitle = ref('Manajemen Pegawai');
const selectedItems = ref<any[]>([]);
const isDrawerOpen = ref(false);
const isFilterDrawerOpen = ref(false);
const filters = ref({
    status: '',
    opd_id: null as string | null
});

const statusOptions = [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
];

// Local State
const employees = ref<any[]>([]);
const opdOptions = ref<any[]>([]);
const opdLoading = ref(false);
const opdSearch = ref('');
const opdPage = ref(1);
const opdHasMore = ref(true);

const totalItems = ref(0);
const currentPage = ref(1);
const perPage = ref(10);
const searchFilter = ref('');
const loading = ref(false);

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

const form = useForm({
    id: null as number | null,
    nip: '',
    name: '',
    opd_id: null as number | null,
    position: '',
});

// Fetch Employees
const fetchEmployees = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/employees', {
            params: {
                page: currentPage.value,
                per_page: perPage.value,
                search: searchFilter.value,
                status: filters.value.status,
                opd_id: filters.value.opd_id
            }
        });
        const data = response.data.data;
        employees.value = data.data;
        totalItems.value = data.total;
        currentPage.value = data.current_page;
        perPage.value = data.per_page;
    } catch (error) {
        console.error('Error fetching employees:', error);
    } finally {
        loading.value = false;
    }
};

// Fetch OPD Options
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
                search: opdSearch.value
            }
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

watch(isDrawerOpen, (newValue) => {
    if (newValue) {
        fetchOpdOptions(true);
    }
});

watch(isFilterDrawerOpen, (newValue) => {
    if (newValue) {
        fetchOpdOptions(true);
    }
});

const handleFilter = () => {
    currentPage.value = 1;
    fetchEmployees();
    isFilterDrawerOpen.value = false;
};

const resetFilter = () => {
    filters.value.status = '';
    filters.value.opd_id = null;
    handleFilter();
};

onMounted(() => {
    fetchEmployees();
});

const tableData = computed(() => {
    return employees.value.map((employee: any) => ({
        ...employee,
        id: employee.id,
        name: employee.name,
        nip: employee.nip,
        position: employee.position,
        opd_name: employee.opd?.name || '-',
        status: employee.status === 'active' ? 'Active' : 'Inactive',
        avatar: employee.avatar,
        has_user: !!employee.user,
        user_email: employee.user?.email || '',
        user_name: employee.user?.name || '',
    }));
});

const columns = ref<Column[]>([
    {
        key: 'name',
        label: 'Nama Pegawai',
        sortable: true,
        type: 'avatar',
        avatarField: 'avatar',
        labelField: 'name',
        subLabelField: 'nip',
        class: 'min-w-[250px] font-medium',
    },
    {
        key: 'nip',
        label: 'NIP',
        sortable: true,
        class: 'min-w-[150px]'
    },
    {
        key: 'position',
        label: 'Jabatan',
        sortable: true,
        class: 'min-w-[150px]'
    },
    {
        key: 'opd_name',
        label: 'OPD',
        sortable: true,
        class: 'min-w-[150px]'
    },
    {
        key: 'status',
        label: 'Status',
        type: 'status',
        sortable: true,
        class: 'text-center',
        statusMap: {
            'Active': 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500',
            'Inactive': 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-500'
        }
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
    fetchEmployees();
};

const handlePerPageChange = (newPerPage: number) => {
    perPage.value = newPerPage;
    currentPage.value = 1;
    fetchEmployees();
};

const handleSearch = debounce((search: string) => {
    searchFilter.value = search;
    currentPage.value = 1;
    fetchEmployees();
}, 500);

const handleSelectionChange = (items: any[]) => {
    selectedItems.value = items;
};

const handleAddEmployee = () => {
    form.reset();
    form.clearErrors();
    form.id = null;
    form.nip = '';
    form.name = '';
    form.opd_id = currentOpdSet.value ? (currentOpd.value as any).id : null;
    form.position = '';
    avatarFile.value = null;
    currentAvatar.value = null;
    isDrawerOpen.value = true;
};

const editItem = (item: any) => {
    form.clearErrors();
    form.id = item.id;
    form.nip = item.nip;
    form.name = item.name;
    form.opd_id = item.opd_id;
    form.position = item.position;

    currentAvatar.value = item.avatar || null;
    avatarFile.value = null;

    if (item.opd) {
        if (!opdOptions.value.find(opd => opd.id === item.opd.id)) {
            opdOptions.value.push(item.opd);
        }
    }

    isDrawerOpen.value = true;
};

const closeDrawer = () => {
    isDrawerOpen.value = false;
    form.reset();
    avatarFile.value = null;
    currentAvatar.value = null;
};

const deactivateEmployee = async (item: any) => {
    if (confirm('Apakah Anda yakin ingin menonaktifkan pegawai ini?')) {
        try {
            await axios.delete(`/api/employees/${item.id}`);
            fetchEmployees();
        } catch (error) {
            console.error('Error deactivating employee:', error);
        }
    }
};

const activateEmployee = async (item: any) => {
    if (confirm('Apakah Anda yakin ingin mengaktifkan kembali pegawai ini?')) {
        try {
            await axios.put(`/api/employees/${item.id}/activate`);
            fetchEmployees();
        } catch (error) {
            console.error('Error activating employee:', error);
        }
    }
};

const saveEmployee = async () => {
    form.processing = true;

    const formData = new FormData();
    const data = form.data();

    Object.entries(data).forEach(([key, value]) => {
        if (value === null || typeof value === 'undefined') {
            return;
        }

        formData.append(key, value as any);
    });

    if (avatarFile.value) {
        formData.append('avatar', avatarFile.value);
    }

    try {
        if (form.id) {
            await axios.post(`/api/employees/${form.id}?_method=PUT`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
        } else {
            await axios.post('/api/employees', formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
        }
        closeDrawer();
        fetchEmployees();
    } catch (error: any) {
        if (error.response && error.response.status === 422) {
            form.errors = error.response.data.errors;
        } else {
            console.error('Error saving employee:', error);
        }
    } finally {
        form.processing = false;
    }
};

const handleExportData = () => {
    console.log('Export data clicked');
};
</script>
