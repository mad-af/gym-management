<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="currentPageTitle" />

        <DynamicTable :columns="columns" :data="tableData" :items-per-page="perPage" :total-items="totalItems"
            :current-page="currentPage" :is-server-side="true" @update:page="handlePageChange"
            @update:search="handleSearch" @update:perPage="handlePerPageChange" @download="handleExportData">
            <template #actions="{ row }">
                <div class="flex items-center gap-2">
                    <button v-can="FUNDING_SOURCE_PERMISSIONS.EDIT" @click="editItem(row)"
                        class="text-gray-500 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-500"
                        title="Edit">
                        <PencilIcon class="w-4.5 h-4.5" />
                    </button>
                    <button v-can="FUNDING_SOURCE_PERMISSIONS.DELETE"
                        v-if="row.status === 'Active' || row.status === 'active'" @click="deactivateItem(row)"
                        class="text-gray-500 hover:text-error-500 dark:text-gray-400 dark:hover:text-error-500"
                        title="Nonaktifkan">
                        <TrashIcon class="w-4.5 h-4.5" />
                    </button>
                    <button v-can="FUNDING_SOURCE_PERMISSIONS.ACTIVATE" v-else @click="activateItem(row)"
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
                <Button v-can="FUNDING_SOURCE_PERMISSIONS.CREATE" size="sm" variant="primary"
                    :onClick="handleAddFundingSource" className="w-full sm:w-auto" :endIcon="PlusIcon">
                    Tambah Sumber Pendanaan
                </Button>
            </template>
        </DynamicTable>

        <!-- Add/Edit Funding Source Drawer -->
        <Drawer :isOpen="isDrawerOpen" @close="closeDrawer"
            :title="form.id ? 'Edit Sumber Pendanaan' : 'Tambah Sumber Pendanaan'">
            <div class="space-y-6">
                <div>
                    <label for="name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Nama Sumber Pendanaan <span class="text-error-500">*</span>
                    </label>
                    <input type="text" id="name" v-model="form.name"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Masukkan nama sumber pendanaan" />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-error-500">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label for="description" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Deskripsi
                    </label>
                    <textarea id="description" v-model="form.description" rows="3"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Masukkan deskripsi (opsional)" />
                    <p v-if="form.errors.description" class="mt-1 text-sm text-error-500">
                        {{ form.errors.description }}
                    </p>
                </div>
            </div>

            <template #footer>
                <div class="flex w-full justify-end gap-3">
                    <Button variant="outline" :onClick="closeDrawer">
                        Batal
                    </Button>
                    <Button variant="primary" :onClick="saveFundingSource" :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </Button>
                </div>
            </template>
        </Drawer>

        <Drawer :isOpen="isFilterDrawerOpen" @close="isFilterDrawerOpen = false" title="Filter Sumber Pendanaan">
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
import { ref, computed, onMounted } from 'vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import SelectInput from '@/components/forms/FormElements/SelectInput.vue';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import DynamicTable from '@/components/tables/data-tables/DynamicTable.vue';
import type { Column } from '@/components/tables/data-tables/DynamicTable.vue';
import Button from '@/components/ui/Button.vue';
import Drawer from '@/components/ui/Drawer.vue';
import { FUNDING_SOURCE_PERMISSIONS } from '@/directives/permissions';
import { PlusIcon, TrashIcon, PencilIcon, CheckCircleIcon, FilterIcon } from '@/icons';

const currentPageTitle = ref('Master Sumber Pendanaan');
const isDrawerOpen = ref(false);
const isFilterDrawerOpen = ref(false);
const fundingSources = ref<any[]>([]);

const totalItems = ref(0);
const currentPage = ref(1);
const perPage = ref(10);
const searchFilter = ref('');
const loading = ref(false);
const filters = ref({
    status: '',
});

const statusOptions = [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
];

const form = useForm({
    id: null as number | null,
    name: '',
    description: '',
});

// Fetch funding sources
const fetchFundingSources = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/funding-sources', {
            params: {
                page: currentPage.value,
                per_page: perPage.value,
                search: searchFilter.value,
                status: filters.value.status || undefined,
            },
        });
        const data = response.data.data;
        fundingSources.value = data.data;
        totalItems.value = data.total;
        currentPage.value = data.current_page;
        perPage.value = data.per_page;
    } catch (error) {
        console.error('Error fetching funding sources:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchFundingSources();
});

const tableData = computed(() => {
    return fundingSources.value.map((source: any) => ({
        ...source,
        id: source.id,
        name: source.name,
        description: source.description ?? '-',
        status: source.status === 'active' ? 'Active' : 'Inactive',
    }));
});

const columns = ref<Column[]>([
    {
        key: 'name',
        label: 'Nama Sumber Pendanaan',
        sortable: true,
        class: 'min-w-[200px] font-medium'
    },
    {
        key: 'description',
        label: 'Deskripsi',
        sortable: true,
        class: 'min-w-[250px]'
    },
    {
        key: 'status',
        label: 'Status',
        type: 'status',
        statusMap: {
            Active: 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500',
            Inactive: 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-500',
        },
        class: 'min-w-[120px]',
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
    fetchFundingSources();
};

const handlePerPageChange = (newPerPage: number) => {
    perPage.value = newPerPage;
    currentPage.value = 1;
    fetchFundingSources();
};

const handleSearch = debounce((search: string) => {
    searchFilter.value = search;
    currentPage.value = 1;
    fetchFundingSources();
}, 500);

const handleFilter = () => {
    currentPage.value = 1;
    isFilterDrawerOpen.value = false;
    fetchFundingSources();
};

const resetFilter = () => {
    filters.value = {
        status: '',
    };
    handleFilter();
};

const handleAddFundingSource = () => {
    form.reset();
    form.clearErrors();
    isDrawerOpen.value = true;
};

const editItem = (item: any) => {
    form.clearErrors();
    form.id = item.id;
    form.name = item.name;
    form.description = item.description;

    isDrawerOpen.value = true;
};

const closeDrawer = () => {
    isDrawerOpen.value = false;
    form.reset();
};

const deactivateItem = async (item: any) => {
    if (confirm('Apakah Anda yakin ingin menonaktifkan sumber pendanaan ini?')) {
        try {
            await axios.delete(`/api/funding-sources/${item.id}`);
            fetchFundingSources();
        } catch (error) {
            console.error('Error deactivating funding source:', error);
        }
    }
};

const activateItem = async (item: any) => {
    if (confirm('Apakah Anda yakin ingin mengaktifkan kembali sumber pendanaan ini?')) {
        try {
            await axios.put(`/api/funding-sources/${item.id}/activate`);
            fetchFundingSources();
        } catch (error) {
            console.error('Error activating funding source:', error);
        }
    }
};

const saveFundingSource = async () => {
    form.processing = true;

    try {
        if (form.id) {
            await axios.put(`/api/funding-sources/${form.id}`, form.data());
        } else {
            await axios.post('/api/funding-sources', form.data());
        }
        closeDrawer();
        fetchFundingSources();
    } catch (error: any) {
        if (error.response && error.response.status === 422) {
            form.errors = error.response.data.errors;
        } else {
            console.error('Error saving funding source:', error);
        }
    } finally {
        form.processing = false;
    }
};

const handleExportData = () => {
    console.log('Export data clicked');
};
</script>
