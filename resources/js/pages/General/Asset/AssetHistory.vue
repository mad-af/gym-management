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
            </template>
            <template #actions="{ row }">
                <div class="flex items-center justify-center gap-2">
                    <button @click="openDetail(row)"
                        class="text-gray-500 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-500"
                        title="Detail Riwayat">
                        <EyeIcon class="w-4.5 h-4.5" />
                    </button>
                </div>
            </template>
        </DynamicTable>

        <Drawer :isOpen="isFilterDrawerOpen" @close="isFilterDrawerOpen = false" title="Filter Riwayat Aset">
            <div class="space-y-6">
                <div>
                    <SelectInput v-model="filters.action" :options="actionOptions" label="Aksi"
                        placeholder="Semua Aksi" />
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Tanggal Dari
                    </label>
                    <input type="date" v-model="filters.date_from"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Tanggal Sampai
                    </label>
                    <input type="date" v-model="filters.date_to"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
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

        <Modal v-if="isDetailModalOpen" :fullScreenBackdrop="true" @close="closeDetail">
            <template #body>
                <div
                    class="relative no-scrollbar w-full max-w-[720px] overflow-y-auto rounded-3xl bg-white p-4 lg:p-8 dark:bg-gray-900">
                    <button @click="closeDetail"
                        class="transition-color absolute top-5 right-5 z-999 flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 text-gray-400 hover:bg-gray-200 hover:text-gray-600 dark:bg-gray-700 dark:text-gray-400 dark:hover:bg-white/[0.07] dark:hover:text-gray-300">
                        <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M6.04289 16.5418C5.65237 16.9323 5.65237 17.5655 6.04289 17.956C6.43342 18.3465 7.06658 18.3465 7.45711 17.956L11.9987 13.4144L16.5408 17.9565C16.9313 18.347 17.5645 18.347 17.955 17.9565C18.3455 17.566 18.3455 16.9328 17.955 16.5423L13.4129 12.0002L17.955 7.45808C18.3455 7.06756 18.3455 6.43439 17.955 6.04387C17.5645 5.65335 16.9313 5.65335 16.5408 6.04387L11.9987 10.586L7.45711 6.04439C7.06658 5.65386 6.43342 5.65386 6.04289 6.04439C5.65237 6.43491 5.65237 7.06808 6.04289 7.4586L10.5845 12.0002L6.04289 16.5418Z"
                                fill="" />
                        </svg>
                    </button>

                    <div class="px-2 pr-10 lg:pr-14">
                        <h4 class="mb-1 text-lg font-semibold text-gray-800 lg:text-xl dark:text-white/90">
                            Detail Riwayat Aset
                        </h4>
                        <p class="mb-4 text-xs text-gray-500 lg:mb-6 dark:text-gray-400">
                            Perubahan data aset yang tercatat dalam sistem.
                        </p>
                    </div>

                    <div class="custom-scrollbar max-h-[520px] overflow-y-auto px-2 pb-2">
                        <div v-if="selectedHistory"
                            class="mb-6 grid grid-cols-1 gap-4 rounded-2xl border border-gray-100 bg-gray-50/60 p-4 lg:grid-cols-2 lg:gap-6 dark:border-gray-800 dark:bg-gray-900/40">
                            <div>
                                <p
                                    class="mb-1 text-xs font-medium capitalize tracking-wide text-gray-500 dark:text-gray-400">
                                    Aset
                                </p>
                                <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                                    {{ selectedHistory.asset_display?.name ?? '-' }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Kode: {{ selectedHistory.asset_display?.code ?? '-' }}
                                </p>
                            </div>
                            <div class="flex flex-col gap-3">
                                <div>
                                    <p
                                        class="mb-1 text-xs font-medium capitalize tracking-wide text-gray-500 dark:text-gray-400">
                                        Aksi
                                    </p>
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium"
                                        :class="actionStatusMap[selectedHistory.action_label] || 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'">
                                        {{ selectedHistory.action_label }}
                                    </span>
                                </div>
                                <div>
                                    <p
                                        class="mb-1 text-xs font-medium capitalize tracking-wide text-gray-500 dark:text-gray-400">
                                        Dilakukan Oleh
                                    </p>
                                    <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                                        {{ selectedHistory.performed_by_name }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="mb-1 text-xs font-medium capitalize tracking-wide text-gray-500 dark:text-gray-400">
                                        Waktu
                                    </p>
                                    <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                                        {{ selectedHistory.performed_at }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-6">
                            <div>
                                <p
                                    class="mb-2 text-xs font-medium capitalize tracking-wide text-gray-500 dark:text-gray-400">
                                    Data Sebelum
                                </p>
                                <pre
                                    class="max-h-72 overflow-auto rounded-2xl border border-gray-100 bg-gray-50 p-3 text-xs text-gray-700 dark:border-gray-800 dark:bg-gray-900/60 dark:text-gray-300">
{{ formatPayload(selectedHistory?.data_before) }}
                                </pre>
                            </div>
                            <div>
                                <p
                                    class="mb-2 text-xs font-medium capitalize tracking-wide text-gray-500 dark:text-gray-400">
                                    Data Sesudah
                                </p>
                                <pre
                                    class="max-h-72 overflow-auto rounded-2xl border border-gray-100 bg-gray-50 p-3 text-xs text-gray-700 dark:border-gray-800 dark:bg-gray-900/60 dark:text-gray-300">
{{ formatPayload(selectedHistory?.data_after) }}
                                </pre>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </Modal>
    </AdminLayout>
</template>

<script setup lang="ts">
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
import Modal from '@/components/ui/Modal.vue';
import { FilterIcon, EyeIcon } from '@/icons';

const currentPageTitle = ref('Riwayat Data Aset');
const isFilterDrawerOpen = ref(false);

const histories = ref<any[]>([]);
const isDetailModalOpen = ref(false);
const selectedHistory = ref<any | null>(null);
const filters = ref({
    action: '' as string,
    date_from: '',
    date_to: '',
});
const totalItems = ref(0);
const currentPage = ref(1);
const perPage = ref(10);
const searchFilter = ref('');
const loading = ref(false);
const actionOptions = ref<{ value: string; label: string; class?: string }[]>([]);

const actionStatusMap = ref<Record<string, string>>({});

const fetchHistories = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/asset-histories', {
            params: {
                page: currentPage.value,
                per_page: perPage.value,
                search: searchFilter.value || undefined,
                action: filters.value.action || undefined,
                date_from: filters.value.date_from || undefined,
                date_to: filters.value.date_to || undefined,
            },
        });

        const payload = response.data.data;
        const items = payload.items;

        histories.value = items.data;
        totalItems.value = items.total;
        currentPage.value = items.current_page;
        perPage.value = items.per_page;

        if (Array.isArray(payload.action_options) && !actionOptions.value.length) {
            actionOptions.value = payload.action_options;

            const map: Record<string, string> = {};
            payload.action_options.forEach((option: { label: string; class?: string }) => {
                if (option.class) {
                    map[option.label] = option.class;
                }
            });

            actionStatusMap.value = map;
        }
    } catch (error) {
        console.error('Error fetching asset histories:', error);
    } finally {
        loading.value = false;
    }
};

const tableData = computed(() => {
    return histories.value.map((history: any) => {
        const asset = history.asset;
        const actionOption = actionOptions.value.find((item) => item.value === history.action);

        return {
            id: history.id,
            asset_display: {
                name: asset ? asset.name : '-',
                code: asset ? asset.asset_code : '-',
                photo: asset?.photo ?? null,
            },
            name: asset ? asset.name : '-',
            asset_code: asset ? asset.asset_code : '-',
            photo: asset?.photo ?? null,
            action_label: actionOption ? actionOption.label : history.action,
            performed_by_name: history.performed_by ? history.performed_by.name : history.performed_by_name ?? '-',
            performed_at: history.created_at ?? '-',
            ...history,
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
        key: 'action_label',
        label: 'Aksi',
        type: 'status',
        get statusMap() {
            return actionStatusMap.value;
        },
        class: 'min-w-[140px]',
    },
    {
        key: 'performed_by_name',
        label: 'Dilakukan Oleh',
        class: 'min-w-[180px]',
    },
    {
        key: 'performed_at',
        label: 'Waktu',
        class: 'min-w-[180px]',
    },
    {
        key: 'actions',
        label: 'Aksi',
        type: 'action',
        class: 'w-[90px]',
    },
]);

const handlePageChange = (page: number) => {
    currentPage.value = page;
    fetchHistories();
};

const handlePerPageChange = (newPerPage: number) => {
    perPage.value = newPerPage;
    currentPage.value = 1;
    fetchHistories();
};

const handleSearch = debounce((search: string) => {
    searchFilter.value = search;
    currentPage.value = 1;
    fetchHistories();
}, 800);

const handleFilter = () => {
    currentPage.value = 1;
    fetchHistories();
    isFilterDrawerOpen.value = false;
};

const resetFilter = () => {
    filters.value.action = '';
    filters.value.date_from = '';
    filters.value.date_to = '';
    handleFilter();
};

const handleExportData = () => {
    alert('Fitur export belum tersedia.');
};

const openDetail = (row: any) => {
    selectedHistory.value = row;
    isDetailModalOpen.value = true;
};

const closeDetail = () => {
    isDetailModalOpen.value = false;
    selectedHistory.value = null;
};

const formatPayload = (value: unknown) => {
    if (value === null || value === undefined) {
        return '-';
    }

    if (typeof value === 'string') {
        return value;
    }

    try {
        return JSON.stringify(value, null, 2);
    } catch {
        return '-';
    }
};

onMounted(() => {
    fetchHistories();
});
</script>
