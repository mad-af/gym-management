<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="currentPageTitle" />

        <DynamicTable :columns="columns" :data="tableData" :items-per-page="perPage" :total-items="totalItems"
            :current-page="currentPage" :is-server-side="true" :selectable="false" @update:page="handlePageChange"
            @update:search="handleSearch" @update:perPage="handlePerPageChange">
            <template #header-actions>
                <Button size="sm" variant="outline" :onClick="() => isFilterDrawerOpen = true"
                    className="w-full sm:w-auto" :startIcon="FilterIcon">
                    Filter
                </Button>
            </template>
            <template #actions="{ row }">
                <div class="flex items-center gap-2">
                    <Link :href="`/room-inventory/${row.id}`"
                        class="text-gray-500 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-500"
                        title="Detail KIR">
                        <EyeIcon class="w-4.5 h-4.5" />
                    </Link>
                </div>
            </template>
        </DynamicTable>

        <Drawer :isOpen="isFilterDrawerOpen" @close="isFilterDrawerOpen = false" title="Filter Ruangan">
            <div class="space-y-6">
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        OPD
                    </label>
                    <Combobox v-model="filters.opd_id" :options="opdOptions" labelKey="name" valueKey="id"
                        placeholder="Pilih OPD..." :loading="opdLoading" remote @search="onOpdSearch"
                        @load-more="onOpdLoadMore" />
                </div>
                <div class="flex justify-end gap-2">
                    <Button variant="outline" :onClick="resetFilter">
                        Reset
                    </Button>
                    <Button variant="primary" :onClick="handleFilter">
                        Terapkan Filter
                    </Button>
                </div>
            </div>
        </Drawer>
    </AdminLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { debounce } from 'lodash';
import { computed, onMounted, ref, watch } from 'vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import DynamicTable from '@/components/tables/data-tables/DynamicTable.vue';
import type { Column } from '@/components/tables/data-tables/DynamicTable.vue';
import Button from '@/components/ui/Button.vue';
import Combobox from '@/components/ui/Combobox.vue';
import Drawer from '@/components/ui/Drawer.vue';
import { FilterIcon, EyeIcon } from '@/icons';

const currentPageTitle = ref('KIR');

const rooms = ref<any[]>([]);
const totalItems = ref(0);
const currentPage = ref(1);
const perPage = ref(10);
const searchFilter = ref('');
const loading = ref(false);

const isFilterDrawerOpen = ref(false);
const filters = ref({
    opd_id: null as string | null,
});

const opdOptions = ref<any[]>([]);
const opdLoading = ref(false);
const opdSearch = ref('');
const opdPage = ref(1);
const opdHasMore = ref(true);

const fetchRooms = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/room-inventory/rooms', {
            params: {
                page: currentPage.value,
                per_page: perPage.value,
                search: searchFilter.value || undefined,
                opd_id: filters.value.opd_id || undefined,
            },
        });

        const paginator = response.data.data;
        rooms.value = paginator.data ?? [];
        totalItems.value = paginator.total ?? 0;
        currentPage.value = paginator.current_page ?? 1;
        perPage.value = paginator.per_page ?? perPage.value;
    } catch (error) {
        console.error('Error fetching room inventory list:', error);
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
                search: opdSearch.value,
            },
        });
        const data = response.data.data;
        const items = data.data;
        const merged = reset ? items : [...opdOptions.value, ...items];
        opdOptions.value = merged;
        opdHasMore.value = !!data.next_page_url;
        opdPage.value++;
    } catch (error) {
        console.error('Error fetching OPDs for room inventory:', error);
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
}, 800);

const handleFilter = () => {
    currentPage.value = 1;
    isFilterDrawerOpen.value = false;
    fetchRooms();
};

const resetFilter = () => {
    filters.value.opd_id = null;
    handleFilter();
};

onMounted(() => {
    fetchRooms();
});

watch(isFilterDrawerOpen, (newValue) => {
    if (newValue) {
        fetchOpdOptions(true);
    }
});

const tableData = computed(() => {
    return rooms.value.map((room: any) => ({
        ...room,
        id: room.id,
        opd_name: room.opd?.name ?? '-',
        assets_count: room.assets_count ?? 0,
    }));
});

const columns = ref<Column[]>([
    {
        key: 'name',
        label: 'Nama Ruangan',
        class: 'min-w-[200px]',
    },
    {
        key: 'code',
        label: 'Kode',
        class: 'min-w-[120px]',
    },
    {
        key: 'opd_name',
        label: 'OPD',
        class: 'min-w-[200px]',
    },
    {
        key: 'assets_count',
        label: 'Jumlah Aset',
        class: 'min-w-[140px]',
    },
    {
        key: 'actions',
        label: 'Aksi',
        type: 'action',
        class: 'w-[120px]',
    },
]);
</script>
