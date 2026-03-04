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
                <Button v-can="ROOM_PERMISSIONS.CREATE" size="sm" variant="primary" :onClick="handleAddRoom"
                    className="w-full sm:w-auto" :endIcon="PlusIcon">
                    Tambah Ruangan
                </Button>
            </template>
            <template #actions="{ row }">
                <div class="flex items-center gap-2">
                    <button v-can="ROOM_PERMISSIONS.EDIT" @click="editItem(row)"
                        class="text-gray-500 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-500"
                        title="Edit">
                        <PencilIcon class="w-4.5 h-4.5" />
                    </button>

                    <button v-can="ROOM_PERMISSIONS.DELETE" v-if="row.status === 'Active' || row.status === 'active'"
                        @click="deactivateRoom(row)"
                        class="text-gray-500 hover:text-error-500 dark:text-gray-400 dark:hover:text-error-500"
                        title="Nonaktifkan">
                        <TrashIcon class="w-4.5 h-4.5" />
                    </button>

                    <button v-can="ROOM_PERMISSIONS.ACTIVATE" v-else @click="activateRoom(row)"
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

        <!-- Add/Edit Room Drawer -->
        <Drawer :isOpen="isDrawerOpen" @close="closeDrawer" :title="form.id ? 'Edit Ruangan' : 'Tambah Ruangan Baru'">
            <div class="space-y-6">
                <!-- Name Input -->
                <div>
                    <label for="name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Nama Ruangan <span class="text-error-500">*</span>
                    </label>
                    <input type="text" id="name" v-model="form.name"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Masukkan nama ruangan" />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-error-500">{{ form.errors.name }}</p>
                </div>

                <!-- Code Input -->
                <div>
                    <label for="code" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Kode Ruangan <span class="text-error-500">*</span>
                    </label>
                    <input type="text" id="code" v-model="form.code"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Masukkan kode ruangan" />
                    <p v-if="form.errors.code" class="mt-1 text-sm text-error-500">{{ form.errors.code }}</p>
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
                    <Button variant="primary" :onClick="saveRoom" :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </Button>
                </div>
            </template>
        </Drawer>

        <!-- Filter Drawer -->
        <Drawer :isOpen="isFilterDrawerOpen" @close="isFilterDrawerOpen = false" title="Filter Ruangan">
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
import { useForm, usePage } from '@inertiajs/vue3';
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
import { ROOM_PERMISSIONS } from '@/directives/permissions';
import { PlusIcon, TrashIcon, CheckCircleIcon, PencilIcon, FilterIcon } from '@/icons';

const page = usePage();
const currentOpd = computed(() => (page.props.auth as any).current_opd);
const currentOpdSet = computed(() => !!currentOpd.value);

const currentPageTitle = ref('Manajemen Ruangan');
const selectedItems = ref<any[]>([]);
const isDrawerOpen = ref(false);
const isFilterDrawerOpen = ref(false);

// Local State
const rooms = ref<any[]>([]);
const totalItems = ref(0);
const currentPage = ref(1);
const perPage = ref(10);
const searchFilter = ref('');
const filters = ref({
    opd_id: null,
    status: ''
});
const loading = ref(false);

const statusOptions = [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
];

// Combobox State
const opdOptions = ref<any[]>([]);
const opdLoading = ref(false);
const opdSearch = ref('');
const opdPage = ref(1);
const opdHasMore = ref(true);

const form = useForm({
    id: null as number | null,
    opd_id: null as number | null,
    name: '',
    code: '',
});

// Fetch Rooms
const fetchRooms = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/rooms', {
            params: {
                page: currentPage.value,
                per_page: perPage.value,
                search: searchFilter.value,
                opd_id: filters.value.opd_id,
                status: filters.value.status
            }
        });
        const data = response.data.data;
        rooms.value = data.data;
        totalItems.value = data.total;
        currentPage.value = data.current_page;
        perPage.value = data.per_page;
    } catch (error) {
        console.error('Error fetching rooms:', error);
    } finally {
        loading.value = false;
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
    isFilterDrawerOpen.value = false;
    fetchRooms();
};

const resetFilter = () => {
    filters.value = {
        opd_id: null,
        status: ''
    };
    handleFilter();
};

onMounted(() => {
    fetchRooms();
});

const tableData = computed(() => {
    return rooms.value.map((room: any) => ({
        ...room,
        id: room.id,
        name: room.name,
        code: room.code,
        opd_name: room.opd?.name || '-',
        status: room.status === 'active' ? 'Active' : 'Inactive',
    }));
});

const columns = ref<Column[]>([
    {
        key: 'name',
        label: 'Nama Ruangan',
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
        key: 'opd_name',
        label: 'OPD',
        sortable: true,
        class: 'min-w-[200px]'
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
    fetchRooms();
};

const handlePerPageChange = (newPerPage: number) => {
    perPage.value = newPerPage;
    currentPage.value = 1;
    fetchRooms();
};

const handleSearch = debounce((search: string) => {
    searchFilter.value = search;
    currentPage.value = 1;
    fetchRooms();
}, 500);

const handleSelectionChange = (items: any[]) => {
    selectedItems.value = items;
};

const handleAddRoom = () => {
    form.reset();
    form.clearErrors();
    form.opd_id = currentOpdSet.value ? (currentOpd.value as any).id : null;
    isDrawerOpen.value = true;
};

const editItem = (item: any) => {
    form.clearErrors();
    form.id = item.id;
    form.name = item.name;
    form.code = item.code;
    form.opd_id = item.opd_id;

    // Pre-fill OPD option if it exists
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
};

const deactivateRoom = async (item: any) => {
    if (confirm('Apakah Anda yakin ingin menonaktifkan ruangan ini?')) {
        try {
            await axios.delete(`/api/rooms/${item.id}`);
            fetchRooms();
        } catch (error) {
            console.error('Error deactivating room:', error);
        }
    }
};

const activateRoom = async (item: any) => {
    if (confirm('Apakah Anda yakin ingin mengaktifkan kembali ruangan ini?')) {
        try {
            await axios.put(`/api/rooms/${item.id}/activate`);
            fetchRooms();
        } catch (error) {
            console.error('Error activating room:', error);
        }
    }
};

const saveRoom = async () => {
    form.processing = true;

    try {
        if (form.id) {
            await axios.put(`/api/rooms/${form.id}`, form.data());
        } else {
            await axios.post('/api/rooms', form.data());
        }
        closeDrawer();
        fetchRooms();
    } catch (error: any) {
        if (error.response && error.response.status === 422) {
            form.errors = error.response.data.errors;
        } else {
            console.error('Error saving room:', error);
        }
    } finally {
        form.processing = false;
    }
};

const handleExportData = () => {
    console.log('Export data clicked');
};
</script>
