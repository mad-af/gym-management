<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="currentPageTitle" />

        <Stats2 :items="statsItems" />

        <DynamicTable :columns="columns" :data="tableData" :items-per-page="perPage" :total-items="totalItems"
            :current-page="currentPage" :is-server-side="true" :selectable="true" @update:page="handlePageChange"
            @update:search="handleSearch" @update:perPage="handlePerPageChange" @download="handleExportData"
            @update:selection="handleSelection">
            <template #header-actions>
                <div class="relative" ref="printDropdownRef">
                    <Button size="sm" variant="outline" :onClick="togglePrintDropdown" className="w-full sm:w-auto"
                        :startIcon="PrinterIcon" :endIcon="ChevronDownIcon" :disabled="selectedItems.length === 0">
                        Print Label
                    </Button>
                    <div v-if="isPrintDropdownOpen"
                        class="absolute right-0 top-full z-40 mt-2 w-[200px] rounded-lg border border-gray-200 bg-white p-1 shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark">
                        <button @click="() => { handleBulkPrintLabel('label'); closePrintDropdown(); }"
                            class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">
                            Label (3x5cm)
                        </button>
                        <button @click="() => { handleBulkPrintLabel('a4'); closePrintDropdown(); }"
                            class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">
                            Label (A4)
                        </button>
                    </div>
                </div>
                <Button size="sm" variant="outline" :onClick="() => isFilterDrawerOpen = true"
                    className="w-full sm:w-auto" :startIcon="FilterIcon">
                    Filter
                </Button>
                <AssetCreateForm @saved="handleAssetSaved" />
            </template>
            <template #actions="{ row }">
                <div class="flex items-center gap-2">
                    <Link v-can="ASSET_PERMISSIONS.VIEW" :href="`/asset-management/assets/${row.id}`"
                        class="text-gray-500 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-500"
                        title="Detail">
                        <EyeIcon class="w-4.5 h-4.5" />
                    </Link>
                </div>
            </template>
        </DynamicTable>

        <!-- Filter Drawer -->
        <Drawer :isOpen="isFilterDrawerOpen" @close="isFilterDrawerOpen = false" title="Filter Aset">
            <div class="space-y-6">
                <!-- Category Filter -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Kategori
                    </label>
                    <Combobox v-model="filters.category_id" :options="categoryOptions" labelKey="name" valueKey="id"
                        placeholder="Pilih kategori..." :loading="categoryLoading" remote @search="onCategorySearch"
                        @load-more="onCategoryLoadMore" />
                </div>

                <!-- Room Filter -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Ruangan
                    </label>
                    <Combobox v-model="filters.room_id" :options="roomOptions" labelKey="name" valueKey="id"
                        placeholder="Pilih ruangan..." :loading="roomLoading" remote @search="onRoomSearch"
                        @load-more="onRoomLoadMore" />
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
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { debounce } from 'lodash';
import { ref, computed, onMounted, watch, onUnmounted } from 'vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import SelectInput from '@/components/forms/FormElements/SelectInput.vue';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import DynamicTable from '@/components/tables/data-tables/DynamicTable.vue';
import type { Column } from '@/components/tables/data-tables/DynamicTable.vue';
import Button from '@/components/ui/Button.vue';
import Combobox from '@/components/ui/Combobox.vue';
import Drawer from '@/components/ui/Drawer.vue';
import Stats2 from '@/components/ui/Stats2.vue';
import { ASSET_PERMISSIONS } from '@/directives/permissions';
import { FilterIcon, EyeIcon, BoxIcon, BanknoteIcon, CheckCircleIcon, PrinterIcon, ChevronDownIcon } from '@/icons';
import AssetCreateForm from './components/AssetCreateForm.vue';


defineProps({});

const currentPageTitle = ref('Data Aset');
const isFilterDrawerOpen = ref(false);
const selectedItems = ref<any[]>([]);

const stats = ref({
    total_assets: 0,
    total_valued_assets: 0,
    assets_with_price: 0,
    assets_without_price: 0,
    active_assets: 0,
});

const formatCurrency = (value: number) => {
    if (!value) return 'Rp 0';

    const abs = Math.abs(value);
    const formatter = new Intl.NumberFormat('id-ID', {
        maximumFractionDigits: 1,
        minimumFractionDigits: 0,
    });

    if (abs >= 1_000_000_000_000) {
        return `Rp ${formatter.format(value / 1_000_000_000_000)}T`;
    }

    if (abs >= 1_000_000_000) {
        return `Rp ${formatter.format(value / 1_000_000_000)}M`;
    }

    if (abs >= 1_000_000) {
        return `Rp ${formatter.format(value / 1_000_000)}Jt`;
    }

    if (abs >= 1_000) {
        return `Rp ${formatter.format(value / 1_000)}Rb`;
    }

    return `Rp ${formatter.format(value)}`;
};

const priceCoverage = computed(() => {
    if (!stats.value.total_assets) return 0;
    return Math.round((stats.value.assets_with_price / stats.value.total_assets) * 100);
});

const statsItems = computed(() => [
    {
        label: 'Total Aset',
        value: stats.value.total_assets,
        icon: BoxIcon,
        iconBgClass: 'bg-blue-50 text-blue-500 dark:bg-blue-500/10',
    },
    {
        label: 'Aset Aktif',
        value: stats.value.active_assets,
        icon: CheckCircleIcon,
        iconBgClass: 'bg-indigo-50 text-indigo-500 dark:bg-indigo-500/10',
    },
    {
        label:
            stats.value.total_assets > 0
                ? `Total Nilai Aset (${priceCoverage.value}%)`
                : 'Total Nilai Aset (belum ada harga)',
        value: formatCurrency(stats.value.total_valued_assets),
        icon: BanknoteIcon,
        iconBgClass: 'bg-emerald-50 text-emerald-500 dark:bg-emerald-500/10',
    },
]);

const assets = ref<any[]>([]);
const filters = ref({
    category_id: null as string | null,
    room_id: null as string | null,
    status: '' as string,
});
const totalItems = ref(0);
const currentPage = ref(1);
const perPage = ref(10);
const searchFilter = ref('');
const loading = ref(false);

const categoryOptions = ref<any[]>([]);
const categoryLoading = ref(false);
const categorySearch = ref('');
const categoryPage = ref(1);
const categoryHasMore = ref(true);

const roomOptions = ref<any[]>([]);
const roomLoading = ref(false);
const roomSearch = ref('');
const roomPage = ref(1);
const roomHasMore = ref(true);

const statusOptions = ref<{ value: string; label: string; class: string }[]>([]);
const conditionOptions = ref<{ value: string; label: string; class: string }[]>([]);

function syncStatusMaps() {
    const statusMap: Record<string, string> = {};
    statusOptions.value.forEach((opt) => {
        statusMap[opt.label] = opt.class;
    });

    const conditionMap: Record<string, string> = {};
    conditionOptions.value.forEach((opt) => {
        conditionMap[opt.label] = opt.class;
    });

    const cols = columns.value;
    const statusCol = cols.find((c) => c.key === 'status_label');
    if (statusCol) {
        statusCol.statusMap = statusMap;
    }

    const conditionCol = cols.find((c) => c.key === 'condition_label');
    if (conditionCol) {
        conditionCol.statusMap = conditionMap;
    }
}

const fetchAssets = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/assets', {
            params: {
                page: currentPage.value,
                per_page: perPage.value,
                search: searchFilter.value,
                status: filters.value.status || undefined,
                category_id: filters.value.category_id,
                room_id: filters.value.room_id,
            },
        });

        const data = response.data.data;
        const items = data.items;

        assets.value = items.data;
        totalItems.value = items.total;
        currentPage.value = items.current_page;
        perPage.value = items.per_page;

        statusOptions.value = data.status_options ?? [];
        conditionOptions.value = data.condition_options ?? [];
        syncStatusMaps();
    } catch (error) {
        console.error('Error fetching assets:', error);
    } finally {
        loading.value = false;
    }
};

const fetchStats = async () => {
    try {
        const response = await axios.get('/api/assets/stats');
        const data = response.data.data ?? {};
        stats.value.total_assets = data.total_assets ?? 0;
        stats.value.total_valued_assets = data.total_valued_assets ?? 0;
        stats.value.assets_with_price = data.assets_with_price ?? 0;
        stats.value.assets_without_price = data.assets_without_price ?? 0;
        stats.value.active_assets = data.active_assets ?? 0;
    } catch (error) {
        console.error('Error fetching asset stats:', error);
    }
};

const fetchCategoryOptions = async (reset = false) => {
    if (reset) {
        categoryPage.value = 1;
        categoryOptions.value = [];
        categoryHasMore.value = true;
    }

    if (!categoryHasMore.value && !reset) return;

    categoryLoading.value = true;
    try {
        const response = await axios.get('/api/asset-categories/selection', {
            params: {
                page: categoryPage.value,
                per_page: 20,
                search: categorySearch.value,
            },
        });
        const data = response.data.data;
        if (reset) {
            categoryOptions.value = data.data;
        } else {
            categoryOptions.value = [...categoryOptions.value, ...data.data];
        }
        categoryHasMore.value = !!data.next_page_url;
        categoryPage.value++;
    } catch (error) {
        console.error('Error fetching asset categories:', error);
    } finally {
        categoryLoading.value = false;
    }
};

const onCategorySearch = (query: string) => {
    categorySearch.value = query;
    fetchCategoryOptions(true);
};

const onCategoryLoadMore = () => {
    fetchCategoryOptions(false);
};

const fetchRoomOptions = async (reset = false) => {
    if (reset) {
        roomPage.value = 1;
        roomOptions.value = [];
        roomHasMore.value = true;
    }

    if (!roomHasMore.value && !reset) return;

    roomLoading.value = true;
    try {
        const response = await axios.get('/api/rooms/selection', {
            params: {
                page: roomPage.value,
                per_page: 20,
                search: roomSearch.value,
            },
        });
        const data = response.data.data;
        if (reset) {
            roomOptions.value = data.data;
        } else {
            roomOptions.value = [...roomOptions.value, ...data.data];
        }
        roomHasMore.value = !!data.next_page_url;
        roomPage.value++;
    } catch (error) {
        console.error('Error fetching rooms:', error);
    } finally {
        roomLoading.value = false;
    }
};

const onRoomSearch = (query: string) => {
    roomSearch.value = query;
    fetchRoomOptions(true);
};

const onRoomLoadMore = () => {
    fetchRoomOptions(false);
};

watch(isFilterDrawerOpen, (newValue) => {
    if (newValue) {
        fetchCategoryOptions(true);
        fetchRoomOptions(true);
    }
});

const handleFilter = () => {
    currentPage.value = 1;
    fetchAssets();
    isFilterDrawerOpen.value = false;
};

const resetFilter = () => {
    filters.value.category_id = null;
    filters.value.room_id = null;
    filters.value.status = '';
    handleFilter();
};

const isPrintDropdownOpen = ref(false);
const printDropdownRef = ref<HTMLElement | null>(null);

const togglePrintDropdown = () => {
    isPrintDropdownOpen.value = !isPrintDropdownOpen.value;
};

const closePrintDropdown = () => {
    isPrintDropdownOpen.value = false;
};

const handleClickOutside = (event: MouseEvent) => {
    if (printDropdownRef.value && !printDropdownRef.value.contains(event.target as Node)) {
        closePrintDropdown();
    }
};

onMounted(() => {
    fetchAssets();
    fetchStats();
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

const tableData = computed(() => {
    return assets.value.map((asset: any) => {
        const statusOption = statusOptions.value.find((s) => s.value === asset.status);
        const conditionOption = conditionOptions.value.find((c) => c.value === asset.condition);

        return {
            id: asset.id,
            asset_display: {
                name: asset.name,
                code: asset.asset_code,
                photo: asset.photo ?? null,
            },
            category_name: asset.category ? asset.category.name : '-',
            room_name: asset.room ? asset.room.name : '-',
            condition_label: conditionOption ? conditionOption.label : '-',
            status_label: statusOption ? statusOption.label : '-',
            ...asset,
        };
    });
});

const columns = ref<Column[]>([
    {
        key: 'asset_display',
        label: 'Aset',
        type: 'cover',
        avatarField: 'photo',
        labelField: 'name',
        subLabelField: 'asset_code',
        class: 'min-w-[220px]',
    },
    {
        key: 'category_name',
        label: 'Kategori',
        class: 'min-w-[160px]',
    },
    {
        key: 'condition_label',
        label: 'Kondisi',
        type: 'status',
        statusMap: {},
        class: 'min-w-[160px]',
    },
    {
        key: 'room_name',
        label: 'Ruangan',
        class: 'min-w-[160px]',
    },
    {
        key: 'status_label',
        label: 'Status',
        type: 'status',
        statusMap: {},
        class: 'min-w-[140px]',
    },
    {
        key: 'actions',
        label: 'Aksi',
        type: 'action',
        class: 'w-[100px]',
    },
]);

const handlePageChange = (page: number) => {
    currentPage.value = page;
    fetchAssets();
};

const handlePerPageChange = (newPerPage: number) => {
    perPage.value = newPerPage;
    currentPage.value = 1; // Reset to first page
    fetchAssets();
};

const handleSearch = debounce((search: string) => {
    searchFilter.value = search;
    currentPage.value = 1; // Reset to first page
    fetchAssets();
}, 800);

const handleSelection = (items: any[]) => {
    selectedItems.value = items;
};

const handleBulkPrintLabel = (paperSize: 'label' | 'a4') => {
    if (selectedItems.value.length === 0) return;

    const ids = selectedItems.value.map((item) => item.id);

    // Create a hidden form to submit POST request and open in new tab
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/api/assets/bulk-label-pdf';
    form.target = '_blank';

    // Add CSRF token if available (though API routes might not need it if not in web middleware group)
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
    }

    // Add asset IDs
    ids.forEach((id) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'asset_ids[]';
        input.value = id;
        form.appendChild(input);
    });

    // Add paper size
    const sizeInput = document.createElement('input');
    sizeInput.type = 'hidden';
    sizeInput.name = 'paper_size';
    sizeInput.value = paperSize;
    form.appendChild(sizeInput);

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
};

const handleAssetSaved = () => {
    fetchAssets();
    fetchStats();
};

const handleExportData = () => {
    // TODO: Implement export functionality
    alert('Fitur export belum tersedia.');
};
</script>
