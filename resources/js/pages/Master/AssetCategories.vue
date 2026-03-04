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
                <Button v-can="ASSET_CATEGORY_PERMISSIONS.CREATE" size="sm" variant="primary"
                    :onClick="handleAddCategory" className="w-full sm:w-auto" :endIcon="PlusIcon">
                    Tambah Kategori
                </Button>
            </template>
            <template #actions="{ row }">
                <div class="flex items-center gap-2 justify-center">
                    <button v-can="ASSET_CATEGORY_PERMISSIONS.EDIT" @click="editItem(row)"
                        class="text-gray-500 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-500"
                        title="Edit">
                        <PencilIcon class="w-4.5 h-4.5" />
                    </button>
                    <button v-if="row.parent_id" v-can="ASSET_CATEGORY_PERMISSIONS.DELETE" @click="deleteCategory(row)"
                        class="text-gray-500 hover:text-error-500 dark:text-gray-400 dark:hover:text-error-500"
                        title="Hapus">
                        <TrashIcon class="w-4.5 h-4.5" />
                    </button>
                </div>
            </template>
        </DynamicTable>

        <div v-if="selectedItems.length > 0" class="mt-4 rounded-lg bg-gray-50 p-4 dark:bg-gray-900">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Selected items: {{selectedItems.map(item => item.name).join(', ')}}
            </p>
        </div>

        <Drawer :isOpen="isDrawerOpen" @close="closeDrawer"
            :title="form.id ? 'Edit Kategori Aset' : 'Tambah Kategori Aset'">
            <div class="space-y-6">
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <input id="is_parent" v-model="form.is_parent" type="checkbox" :disabled="!!form.id"
                            class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800" />
                        <label for="is_parent" class="text-sm font-medium text-gray-700 dark:text-gray-200">
                            Kategori Induk
                        </label>
                    </div>
                    <div>
                        <label for="parent_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                            Induk Kategori
                        </label>
                        <Combobox v-model="form.parent_id" :options="parentOptionsForForm" valueKey="id" labelKey="name"
                            placeholder="Pilih induk kategori" :loading="parentLoading" remote
                            :disabled="!!form.id || !!form.is_parent" @search="onParentSearch"
                            @load-more="onParentLoadMore" />
                        <p v-if="form.errors.parent_id" class="mt-1 text-sm text-error-500">
                            {{ form.errors.parent_id }}
                        </p>
                    </div>
                </div>

                <div>
                    <label for="code" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Kode Kategori <span class="text-error-500">*</span>
                    </label>
                    <input id="code" v-model="form.code" type="text"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Masukkan kode kategori" />
                    <p v-if="form.errors.code" class="mt-1 text-sm text-error-500">
                        {{ form.errors.code }}
                    </p>
                </div>

                <div>
                    <label for="name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Nama Kategori <span class="text-error-500">*</span>
                    </label>
                    <input id="name" v-model="form.name" type="text"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Masukkan nama kategori aset" />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-error-500">
                        {{ form.errors.name }}
                    </p>
                </div>

                <div>
                    <label for="useful_life_years"
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Umur Manfaat (tahun)
                    </label>
                    <input id="useful_life_years" v-model.number="form.useful_life_years" type="number" min="1"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Masukkan umur manfaat aset (opsional)" />
                    <p v-if="form.errors.useful_life_years" class="mt-1 text-sm text-error-500">
                        {{ form.errors.useful_life_years }}
                    </p>
                </div>
            </div>

            <template #footer>
                <div class="flex w-full justify-end gap-3">
                    <Button variant="outline" :onClick="closeDrawer">
                        Batal
                    </Button>
                    <Button variant="primary" :onClick="saveCategory" :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </Button>
                </div>
            </template>
        </Drawer>

        <Drawer :isOpen="isFilterDrawerOpen" @close="isFilterDrawerOpen = false" title="Filter Kategori Aset">
            <div class="space-y-6">
                <div>
                    <Combobox label="Induk Kategori" v-model="filters.parent_id" :options="parentOptions" valueKey="id"
                        labelKey="name" placeholder="Semua induk kategori" :loading="parentLoading" remote
                        @search="onParentSearch" @load-more="onParentLoadMore" />
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
import AdminLayout from '@/components/layout/AdminLayout.vue';
import DynamicTable from '@/components/tables/data-tables/DynamicTable.vue';
import type { Column } from '@/components/tables/data-tables/DynamicTable.vue';
import Button from '@/components/ui/Button.vue';
import Combobox from '@/components/ui/Combobox.vue';
import Drawer from '@/components/ui/Drawer.vue';
import { ASSET_CATEGORY_PERMISSIONS } from '@/directives/permissions';
import { PlusIcon, TrashIcon, PencilIcon, FilterIcon } from '@/icons';

const currentPageTitle = ref('Manajemen Kategori Aset');
const selectedItems = ref<any[]>([]);
const isDrawerOpen = ref(false);
const isFilterDrawerOpen = ref(false);

const filters = ref({
    parent_id: null as string | null,
});

const categories = ref<any[]>([]);
const totalItems = ref(0);
const currentPage = ref(1);
const perPage = ref(10);
const searchFilter = ref('');
const loading = ref(false);

const parentOptions = ref<any[]>([]);
const parentOptionsForForm = computed(() =>
    parentOptions.value.filter((option: any) => option.id !== form.id),
);
const parentLoading = ref(false);
const parentSearch = ref('');
const parentPage = ref(1);
const parentHasMore = ref(true);

const form = useForm({
    id: null as string | null,
    parent_id: null as string | null,
    is_parent: true as boolean,
    code: '',
    name: '',
    useful_life_years: null as number | null,
    level: null as number | null,
});

const tableData = computed(() => {
    return categories.value.map((category: any) => ({
        ...category,
        parent_name: category.parent?.name || '-',
        useful_life_years_display: category.useful_life_years ?? '-',
    }));
});

const columns = ref<Column[]>([
    {
        key: 'code',
        label: 'Kode',
        sortable: true,
        class: 'min-w-[100px]',
    },
    {
        key: 'name',
        label: 'Nama Kategori',
        sortable: true,
        class: 'min-w-[200px] font-medium',
    },
    {
        key: 'parent_name',
        label: 'Induk Kategori',
        sortable: false,
        class: 'min-w-[200px]',
    },
    {
        key: 'useful_life_years_display',
        label: 'Umur Manfaat (tahun)',
        sortable: false,
        class: 'min-w-[140px] text-center',
    },
    {
        key: 'actions',
        label: 'Aksi',
        type: 'action',
        sortable: false,
        class: 'text-center min-w-[120px]',
    },
]);

const fetchCategories = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/asset-categories', {
            params: {
                page: currentPage.value,
                per_page: perPage.value,
                search: searchFilter.value,
                parent_id: filters.value.parent_id,
            },
        });
        const data = response.data.data;
        categories.value = data.data;
        totalItems.value = data.total;
        currentPage.value = data.current_page;
        perPage.value = data.per_page;
    } catch (error) {
        console.error('Error fetching asset categories:', error);
    } finally {
        loading.value = false;
    }
};

const fetchParentOptions = async (reset = false) => {
    if (reset) {
        parentPage.value = 1;
        parentOptions.value = [];
        parentHasMore.value = true;
    }

    if (!parentHasMore.value && !reset) return;

    parentLoading.value = true;
    try {
        const response = await axios.get('/api/asset-categories/selection', {
            params: {
                page: parentPage.value,
                per_page: 20,
                search: parentSearch.value,
                only_parents: true,
            },
        });
        const data = response.data.data;
        if (reset) {
            parentOptions.value = data.data;
        } else {
            parentOptions.value = [...parentOptions.value, ...data.data];
        }
        parentHasMore.value = !!data.next_page_url;
        parentPage.value++;
    } catch (error) {
        console.error('Error fetching parent categories:', error);
    } finally {
        parentLoading.value = false;
    }
};

const onParentSearch = (query: string) => {
    parentSearch.value = query;
    fetchParentOptions(true);
};

const onParentLoadMore = () => {
    fetchParentOptions(false);
};

const handlePageChange = (page: number) => {
    currentPage.value = page;
    fetchCategories();
};

const handlePerPageChange = (newPerPage: number) => {
    perPage.value = newPerPage;
    currentPage.value = 1;
    fetchCategories();
};

const handleSearch = debounce((search: string) => {
    searchFilter.value = search;
    currentPage.value = 1;
    fetchCategories();
}, 500);

const handleFilter = () => {
    currentPage.value = 1;
    isFilterDrawerOpen.value = false;
    fetchCategories();
};

const resetFilter = () => {
    filters.value = {
        parent_id: null,
    };
    handleFilter();
};

const handleSelectionChange = (items: any[]) => {
    selectedItems.value = items;
};

const handleAddCategory = () => {
    form.reset();
    form.clearErrors();
    form.is_parent = true;
    form.parent_id = null;
    form.level = null;
    isDrawerOpen.value = true;
};

const editItem = (item: any) => {
    form.clearErrors();
    form.id = item.id;
    form.parent_id = item.parent_id;
    form.is_parent = !item.parent_id;
    form.code = item.code;
    form.name = item.name;
    form.useful_life_years = item.useful_life_years;
    form.level = item.level ?? null;

    if (item.parent) {
        if (!parentOptions.value.find((cat: any) => cat.id === item.parent.id)) {
            parentOptions.value.push(item.parent);
        }
    }

    isDrawerOpen.value = true;
};

watch(
    () => form.is_parent,
    (newValue) => {
        if (newValue) {
            form.parent_id = null;
        }
    },
);

const closeDrawer = () => {
    isDrawerOpen.value = false;
    form.reset();
};

const deleteCategory = async (item: any) => {
    if (confirm('Apakah Anda yakin ingin menghapus kategori aset ini?')) {
        try {
            await axios.delete(`/api/asset-categories/${item.id}`);
            fetchCategories();
        } catch (error) {
            console.error('Error deleting asset category:', error);
        }
    }
};

const handleExportData = () => {
    console.log('Export data');
};

const saveCategory = async () => {
    form.processing = true;

    try {
        if (form.id) {
            await axios.put(`/api/asset-categories/${form.id}`, form.data());
        } else {
            await axios.post('/api/asset-categories', form.data());
        }
        closeDrawer();
        fetchCategories();
    } catch (error: any) {
        if (error.response && error.response.status === 422) {
            form.errors = error.response.data.errors;
        } else {
            console.error('Error saving asset category:', error);
        }
    } finally {
        form.processing = false;
    }
};

onMounted(() => {
    fetchCategories();
    fetchParentOptions(true);
});
</script>
