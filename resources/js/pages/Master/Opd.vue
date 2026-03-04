<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="currentPageTitle" />

        <!-- Enhanced DynamicTable -->
        <DynamicTable :columns="columns" :data="tableData" :items-per-page="perPage" :total-items="totalItems"
            :current-page="currentPage" :is-server-side="true" @update:page="handlePageChange"
            @update:search="handleSearch" @update:perPage="handlePerPageChange"
            @update:selection="handleSelectionChange" @download="handleExportData">
            <template #header-actions>
                <Button size="sm" variant="outline" :onClick="() => isFilterDrawerOpen = true"
                    className="w-full sm:w-auto" :startIcon="FilterIcon">
                    Filter
                </Button>
                <Button v-can="OPD_PERMISSIONS.CREATE" size="sm" variant="primary" :onClick="handleAddOPD"
                    className="w-full sm:w-auto" :endIcon="PlusIcon">
                    Tambah OPD
                </Button>
            </template>
            <template #actions="{ row }">
                <div class="flex items-center gap-2">
                    <button v-can="OPD_PERMISSIONS.EDIT" @click="editItem(row)"
                        class="text-gray-500 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-500"
                        title="Edit">
                        <PencilIcon class="w-4.5 h-4.5" />
                    </button>

                    <button v-can="OPD_PERMISSIONS.DELETE" v-if="row.status === 'Active' || row.status === 'active'"
                        @click="deactivateOpd(row)"
                        class="text-gray-500 hover:text-error-500 dark:text-gray-400 dark:hover:text-error-500"
                        title="Nonaktifkan">
                        <TrashIcon class="w-4.5 h-4.5" />
                    </button>

                    <button v-can="OPD_PERMISSIONS.ACTIVATE" v-else @click="activateOpd(row)"
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

        <!-- Add/Edit OPD Drawer -->
        <Drawer :isOpen="isDrawerOpen" @close="closeDrawer" :title="form.id ? 'Edit OPD' : 'Tambah OPD Baru'">
            <div class="space-y-6">
                <!-- Name Input -->
                <div>
                    <label for="name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Nama OPD <span class="text-error-500">*</span>
                    </label>
                    <input type="text" id="name" v-model="form.name"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Masukkan nama OPD" />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-error-500">{{ form.errors.name }}</p>
                </div>

                <!-- Code Input -->
                <div>
                    <label for="code" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Kode OPD <span class="text-error-500">*</span>
                    </label>
                    <input type="text" id="code" v-model="form.code"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Masukkan kode OPD" />
                    <p v-if="form.errors.code" class="mt-1 text-sm text-error-500">{{ form.errors.code }}</p>
                </div>

                <!-- Email Input -->
                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Email
                    </label>
                    <input type="email" id="email" v-model="form.email"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Masukkan email" />
                    <p v-if="form.errors.email" class="mt-1 text-sm text-error-500">{{ form.errors.email }}</p>
                </div>

                <!-- Phone Input -->
                <div>
                    <label for="phone" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Telepon
                    </label>
                    <input type="text" id="phone" v-model="form.phone"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Masukkan nomor telepon" />
                    <p v-if="form.errors.phone" class="mt-1 text-sm text-error-500">{{ form.errors.phone }}</p>
                </div>

                <!-- Head Selection -->
                <div>
                    <Combobox label="Kepala OPD" v-model="form.head_id" :options="employeeOptions" valueKey="id"
                        labelKey="name" placeholder="Pilih Kepala OPD" :loading="employeeLoading" remote
                        @search="onEmployeeSearch" @load-more="onEmployeeLoadMore" />
                    <p v-if="form.errors.head_id" class="mt-1 text-sm text-error-500">{{ form.errors.head_id }}</p>
                </div>

                <!-- Address Input -->
                <div>
                    <label for="address" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Alamat
                    </label>
                    <textarea id="address" v-model="form.address" rows="3"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Masukkan alamat"></textarea>
                    <p v-if="form.errors.address" class="mt-1 text-sm text-error-500">{{ form.errors.address }}</p>
                </div>
            </div>

            <template #footer>
                <div class="flex w-full justify-end gap-3">
                    <Button variant="outline" :onClick="closeDrawer">
                        Batal
                    </Button>
                    <Button variant="primary" :onClick="saveOpd" :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </Button>
                </div>
            </template>
        </Drawer>

        <!-- Filter Drawer -->
        <Drawer :isOpen="isFilterDrawerOpen" @close="isFilterDrawerOpen = false" title="Filter OPD">
            <div class="space-y-6">
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
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { debounce } from 'lodash';
import { ref, computed, onMounted, watch } from 'vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import SelectInput from '@/components/forms/FormElements/SelectInput.vue';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import DynamicTable from '@/components/tables/data-tables/DynamicTable.vue';
import type { Column } from '@/components/tables/data-tables/DynamicTable.vue';
import Button from '@/components/ui/Button.vue';
import Combobox from '@/components/ui/Combobox.vue';
import Drawer from '@/components/ui/Drawer.vue';
import { OPD_PERMISSIONS } from '@/directives/permissions';
import { PlusIcon, TrashIcon, CheckCircleIcon, PencilIcon, FilterIcon } from '@/icons';

const currentPageTitle = ref('Manajemen OPD (Organisasi Perangkat Daerah)');
const selectedItems = ref<any[]>([]);
const isDrawerOpen = ref(false);
const isFilterDrawerOpen = ref(false);
const filters = ref({
    status: ''
});

const statusOptions = [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
];

// Local State
const opds = ref<any[]>([]);
// Combobox State
const employeeOptions = ref<any[]>([]);
const employeeLoading = ref(false);
const employeeSearch = ref('');
const employeePage = ref(1);
const employeeHasMore = ref(true);

const totalItems = ref(0);
const currentPage = ref(1);
const perPage = ref(10);
const searchFilter = ref('');
const loading = ref(false);

// Form handling
const form = useForm({
    id: null as number | null,
    name: '',
    code: '',
    email: '',
    address: '',
    phone: '',
    head_id: null as string | null,
});

// Convert props data to table format
const tableData = computed(() => {
    return opds.value.map((opd: any) => ({
        ...opd,
        id: opd.id,
        name: opd.name,
        code: opd.code,
        email: opd.email || '-',
        address: opd.address || '-',
        phone: opd.phone || '-',
        head_name: opd.head?.name || '-',
        status: opd.status === 'active' ? 'Active' : 'Inactive',
    }));
});

const columns = ref<Column[]>([
    {
        key: 'name',
        label: 'Nama OPD',
        sortable: true,
        class: 'min-w-[200px] font-medium'
    },
    {
        key: 'code',
        label: 'Kode',
        sortable: true,
        class: 'min-w-[100px]'
    },
    {
        key: 'head_name',
        label: 'Kepala OPD',
        class: 'min-w-[150px]'
    },
    {
        key: 'phone',
        label: 'Telepon',
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

const fetchOpds = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/opds', {
            params: {
                page: currentPage.value,
                per_page: perPage.value,
                search: searchFilter.value,
                status: filters.value.status
            }
        });
        const data = response.data.data;
        opds.value = data.data;
        totalItems.value = data.total;
        currentPage.value = data.current_page;
        perPage.value = data.per_page;
    } catch (error) {
        console.error('Error fetching OPDs:', error);
    } finally {
        loading.value = false;
    }
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
                search: employeeSearch.value
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
        // Initial fetch when drawer opens
        fetchEmployeeOptions(true);
    }
});

const handlePageChange = (page: number) => {
    currentPage.value = page;
    fetchOpds();
};

const handlePerPageChange = (newPerPage: number) => {
    perPage.value = newPerPage;
    currentPage.value = 1;
    fetchOpds();
};

const handleSearch = debounce((search: string) => {
    searchFilter.value = search;
    currentPage.value = 1;
    fetchOpds();
}, 500);

const handleFilter = () => {
    currentPage.value = 1;
    isFilterDrawerOpen.value = false;
    fetchOpds();
};

const resetFilter = () => {
    filters.value = {
        status: ''
    };
    handleFilter();
};

const handleSelectionChange = (items: any[]) => {
    selectedItems.value = items;
};

const handleAddOPD = () => {
    form.reset();
    form.clearErrors();
    isDrawerOpen.value = true;
};

const editItem = (item: any) => {
    form.clearErrors();
    form.id = item.id;
    form.name = item.name;
    form.code = item.code;
    form.email = item.email;
    form.address = item.address;
    form.phone = item.phone;
    form.head_id = item.head_id;

    // Pre-fill head (employee) option if it exists
    if (item.head) {
        if (!employeeOptions.value.find(emp => emp.id === item.head.id)) {
            employeeOptions.value.push(item.head);
        }
    }

    isDrawerOpen.value = true;
};

const closeDrawer = () => {
    isDrawerOpen.value = false;
    form.reset();
};

const deactivateOpd = async (item: any) => {
    if (confirm('Apakah Anda yakin ingin menonaktifkan OPD ini?')) {
        try {
            await axios.delete(`/api/opds/${item.id}`);
            fetchOpds();
        } catch (error) {
            console.error('Error deactivating OPD:', error);
        }
    }
};

const activateOpd = async (item: any) => {
    if (confirm('Apakah Anda yakin ingin mengaktifkan kembali OPD ini?')) {
        try {
            await axios.put(`/api/opds/${item.id}/activate`);
            fetchOpds();
        } catch (error) {
            console.error('Error activating OPD:', error);
        }
    }
};

const handleExportData = () => {
    console.log('Export data');
};

const saveOpd = async () => {
    form.processing = true;

    try {
        if (form.id) {
            await axios.put(`/api/opds/${form.id}`, form.data());
        } else {
            await axios.post('/api/opds', form.data());
        }
        closeDrawer();
        fetchOpds();
    } catch (error: any) {
        if (error.response && error.response.status === 422) {
            form.errors = error.response.data.errors;
        } else {
            console.error('Error saving OPD:', error);
        }
    } finally {
        form.processing = false;
    }
};

onMounted(() => {
    fetchOpds();
});
</script>
