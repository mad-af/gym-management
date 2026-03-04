<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="currentPageTitle" />

        <Stats2 :items="statsItems" />

        <DynamicTable :columns="columns" :data="tableData" :items-per-page="perPage" :total-items="totalItems"
            :current-page="currentPage" :is-server-side="true" :selectable="false" @update:page="handlePageChange"
            @update:search="handleSearch" @update:perPage="handlePerPageChange" @download="handleExportData">
            <template #header-actions>
                <Button size="sm" variant="outline" :onClick="() => isFilterDrawerOpen = true"
                    className="w-full sm:w-auto" :startIcon="FilterIcon">
                    Filter
                </Button>
                <Button v-can="ASSET_TRANSFER_PERMISSIONS.CREATE" size="sm" variant="primary"
                    :onClick="handleAddTransfer" className="w-full sm:w-auto" :endIcon="PlusIcon">
                    Buat Mutasi
                </Button>
            </template>
            <template #cell-direction_label="{ row }">
                <span :class="row.direction_class">
                    {{ row.direction_label }}
                </span>
            </template>
            <template #actions="{ row }">
                <div class="flex items-center gap-2">
                    <Link :href="`/asset-management/transfers/${row.id}`"
                        class="text-gray-500 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-500"
                        title="Detail Mutasi">
                        <EyeIcon class="w-4.5 h-4.5" />
                    </Link>
                </div>
            </template>
        </DynamicTable>

        <Drawer :isOpen="isDrawerOpen" @close="closeDrawer" title="Buat Mutasi Aset">
            <div class="space-y-6">
                <div v-if="!currentOpdSet" class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Dari OPD <span class="text-error-500">*</span>
                    </label>
                    <Combobox v-model="form.from_opd_id" :options="opdOptions" labelKey="name" valueKey="id"
                        placeholder="Pilih OPD asal..." :loading="opdLoading" remote @search="onOpdSearch"
                        @load-more="onOpdLoadMore" />
                    <p v-if="form.errors.from_opd_id" class="mt-1 text-sm text-error-500">
                        {{ form.errors.from_opd_id }}
                    </p>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Ke OPD Tujuan <span class="text-error-500">*</span>
                    </label>
                    <Combobox v-model="form.to_opd_id" :options="opdOptions" labelKey="name" valueKey="id"
                        placeholder="Pilih OPD tujuan..." :loading="opdLoading" remote @search="onOpdSearch"
                        @load-more="onOpdLoadMore" />
                    <p v-if="form.errors.to_opd_id" class="mt-1 text-sm text-error-500">
                        {{ form.errors.to_opd_id }}
                    </p>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">
                            Daftar Aset
                        </span>
                        <Button size="sm" variant="outline" :onClick="addItem">
                            Tambah Aset
                        </Button>
                    </div>
                    <div v-if="!form.items.length"
                        class="rounded-lg border border-dashed border-gray-300 p-4 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        Belum ada aset yang dipilih. Klik "Tambah Aset" untuk menambahkan.
                    </div>
                    <div v-else class="space-y-4">
                        <div v-for="(item, index) in form.items" :key="index"
                            class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1 space-y-3">
                                    <div class="space-y-2">
                                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">
                                            Aset
                                        </label>
                                        <Combobox v-model="item.asset_id" :options="assetOptions" labelKey="display"
                                            valueKey="id" placeholder="Pilih aset..." :loading="assetLoading" remote
                                            @search="onAssetSearch" @load-more="onAssetLoadMore" />
                                    </div>
                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                        <div class="space-y-2">
                                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">
                                                Ruangan Asal
                                            </label>
                                            <Combobox :modelValue="getFromRoom(item.asset_id)?.id ?? null"
                                                :options="getFromRoom(item.asset_id) ? [getFromRoom(item.asset_id)] : []"
                                                labelKey="name" valueKey="id" placeholder="Tidak ada ruangan"
                                                :disabled="true" />
                                        </div>
                                        <div class="space-y-2">
                                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">
                                                Ruangan Tujuan
                                            </label>
                                            <Combobox v-model="item.to_room_id"
                                                :options="getFilteredRoomOptions(item.asset_id)" labelKey="name"
                                                valueKey="id" placeholder="Pilih ruangan tujuan (opsional)..."
                                                :loading="roomLoading" remote @search="onRoomSearch"
                                                @load-more="onRoomLoadMore" />
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-0">
                                    <Button size="sm" variant="outline" :onClick="() => removeItem(index)"
                                        className="p-2" title="Hapus aset dari daftar">
                                        <TrashIcon class="w-4 h-4" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p v-if="form.errors.items" class="mt-1 text-sm text-error-500">
                        {{ form.errors.items }}
                    </p>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Catatan
                    </label>
                    <textarea id="notes" v-model="form.notes" rows="3"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Masukkan catatan tambahan (opsional)"></textarea>
                    <p v-if="form.errors.notes" class="mt-1 text-sm text-error-500">
                        {{ form.errors.notes }}
                    </p>
                </div>
            </div>

            <template #footer>
                <div class="flex w-full justify-end gap-3">
                    <Button variant="outline" :onClick="closeDrawer">
                        Batal
                    </Button>
                    <Button variant="primary" :onClick="saveTransfer" :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </Button>
                </div>
            </template>
        </Drawer>

        <Drawer :isOpen="isFilterDrawerOpen" @close="isFilterDrawerOpen = false" title="Filter Mutasi Aset">
            <div class="space-y-6">
                <div class="space-y-2">
                    <SelectInput v-model="filters.status" :options="statusOptions" label="Status"
                        placeholder="Semua Status" />
                </div>

                <div class="space-y-2">
                    <SelectInput v-model="filters.type" :options="typeOptions" label="Tipe Mutasi"
                        placeholder="Semua Tipe" />
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Dari OPD
                    </label>
                    <Combobox v-model="filters.from_opd_id" :options="opdOptions" labelKey="name" valueKey="id"
                        placeholder="Pilih OPD asal..." :loading="opdLoading" remote @search="onOpdSearch"
                        @load-more="onOpdLoadMore" />
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Ke OPD
                    </label>
                    <Combobox v-model="filters.to_opd_id" :options="opdOptions" labelKey="name" valueKey="id"
                        placeholder="Pilih OPD tujuan..." :loading="opdLoading" remote @search="onOpdSearch"
                        @load-more="onOpdLoadMore" />
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
import { Link, useForm, usePage } from '@inertiajs/vue3';
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
import Stats2 from '@/components/ui/Stats2.vue';
import { ASSET_TRANSFER_PERMISSIONS } from '@/directives/permissions';
import { PlusIcon, FilterIcon, TrashIcon, EyeIcon, LayersIcon, ArrowRightIcon, WarningIcon } from '@/icons';

const page = usePage();
const currentOpd = computed(() => (page.props.auth as any).current_opd);
const currentOpdSet = computed(() => !!currentOpd.value);

const parseInitialFilters = () => {
    try {
        const url = new URL(page.url, window.location.origin);
        return {
            status: url.searchParams.get('status') || '',
            type: url.searchParams.get('type') || '',
        };
    } catch {
        return {
            status: '',
            type: '',
        };
    }
};

const initialFilters = parseInitialFilters();

const currentPageTitle = ref('Mutasi Aset');
const isDrawerOpen = ref(false);
const isFilterDrawerOpen = ref(false);

const stats = ref({
    total_transfers: 0,
    internal_transfers: 0,
    external_transfers: 0,
    pending_external_transfers: 0,
});

const statsItems = computed(() => [
    {
        label: 'Total Mutasi',
        value: stats.value.total_transfers,
        icon: LayersIcon,
        iconBgClass: 'bg-blue-50 text-blue-500 dark:bg-blue-500/10',
    },
    {
        label: 'Mutasi Internal',
        value: stats.value.internal_transfers,
        icon: ArrowRightIcon,
        iconBgClass: 'bg-emerald-50 text-emerald-500 dark:bg-emerald-500/10',
    },
    {
        label: 'Mutasi Eksternal',
        value: stats.value.external_transfers,
        icon: ArrowRightIcon,
        iconBgClass: 'bg-purple-50 text-purple-500 dark:bg-purple-500/10',
    },
    {
        label: 'Eksternal Pending',
        value: stats.value.pending_external_transfers,
        icon: WarningIcon,
        iconBgClass: 'bg-amber-50 text-amber-500 dark:bg-amber-500/10',
    },
]);

const transfers = ref<any[]>([]);
const filters = ref({
    status: initialFilters.status as string,
    type: initialFilters.type as string,
    from_opd_id: null as string | null,
    to_opd_id: null as string | null,
});
const totalItems = ref(0);
const currentPage = ref(1);
const perPage = ref(10);
const searchFilter = ref('');
const loading = ref(false);

const opdOptions = ref<any[]>([]);
const opdLoading = ref(false);
const opdSearch = ref('');
const opdPage = ref(1);
const opdHasMore = ref(true);

const roomOptions = ref<any[]>([]);
const roomLoading = ref(false);
const roomSearch = ref('');
const roomPage = ref(1);
const roomHasMore = ref(true);

const assetOptions = ref<any[]>([]);
const assetLoading = ref(false);
const assetSearch = ref('');
const assetPage = ref(1);
const assetHasMore = ref(true);

const directionStatusMap: Record<string, string> = {
    'Masuk ke OPD Saya':
        'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400',
    'Keluar dari OPD Saya':
        'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400',
    'Internal OPD Saya':
        'bg-purple-50 text-purple-700 dark:bg-purple-500/15 dark:text-purple-400',
};

const statusOptions = ref<{ value: string; label: string; class: string }[]>([]);

const typeOptions = ref<{ value: string; label: string; class: string }[]>([]);

const statusLabelMap: Record<string, string> = {};
const statusClassMap: Record<string, string> = {};

const syncStatusMaps = () => {
    Object.keys(statusLabelMap).forEach((key) => delete statusLabelMap[key]);
    Object.keys(statusClassMap).forEach((key) => delete statusClassMap[key]);

    statusOptions.value.forEach((opt) => {
        statusLabelMap[opt.value] = opt.label;
        statusClassMap[opt.label] = opt.class;
    });
};

const form = useForm({
    from_opd_id: null as string | null,
    to_opd_id: null as string | null,
    items: [] as { asset_id: string | null; from_room_id: string | null; to_room_id: string | null }[],
    notes: '',
});

const columns = ref<Column[]>([
    {
        key: 'transfer_number',
        label: 'No. Mutasi',
        class: 'min-w-[160px]',
    },
    {
        key: 'from_opd_name',
        label: 'Dari OPD',
        class: 'min-w-[180px]',
    },
    {
        key: 'to_opd_name',
        label: 'Ke OPD',
        class: 'min-w-[180px]',
    },
    {
        key: 'type_label',
        label: 'Tipe',
        class: 'min-w-[140px]',
    },
    {
        key: 'direction_label',
        label: 'Arah',
        type: 'status',
        statusMap: directionStatusMap,
        class: 'min-w-[180px]',
    },
    {
        key: 'status_label',
        label: 'Status',
        type: 'status',
        statusMap: statusClassMap,
        class: 'min-w-[140px]',
    },
    {
        key: 'actions',
        label: 'Aksi',
        type: 'action',
        class: 'w-[160px]',
    },
]);

const fetchTransfers = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/transfers', {
            params: {
                page: currentPage.value,
                per_page: perPage.value,
                search: searchFilter.value,
                status: filters.value.status || undefined,
                type: filters.value.type || undefined,
                from_opd_id: filters.value.from_opd_id || undefined,
                to_opd_id: filters.value.to_opd_id || undefined,
            },
        });

        const data = response.data.data;
        const paginator = data.items;

        transfers.value = paginator.data;
        totalItems.value = paginator.total;
        currentPage.value = paginator.current_page;
        perPage.value = paginator.per_page;

        statusOptions.value = data.status_options ?? [];
        typeOptions.value = data.type_options ?? [];
        syncStatusMaps();
    } catch (error) {
        console.error('Error fetching transfers:', error);
    } finally {
        loading.value = false;
    }
};

const fetchStats = async () => {
    try {
        const response = await axios.get('/api/transfers/stats');
        const data = response.data.data ?? {};
        stats.value.total_transfers = data.total_transfers ?? 0;
        stats.value.internal_transfers = data.internal_transfers ?? 0;
        stats.value.external_transfers = data.external_transfers ?? 0;
        stats.value.pending_external_transfers = data.pending_external_transfers ?? 0;
    } catch (error) {
        console.error('Error fetching transfer stats:', error);
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

const fetchRoomOptions = async (reset = false) => {
    if (!form.to_opd_id) {
        roomOptions.value = [];
        return;
    }

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
                opd_id: form.to_opd_id ?? undefined,
            },
        });
        const data = response.data.data;
        const items = data.data;
        const merged = reset ? items : [...roomOptions.value, ...items];
        roomOptions.value = merged;
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

const fetchAssetOptions = async (reset = false) => {
    if (!form.from_opd_id) {
        assetOptions.value = [];
        return;
    }

    if (reset) {
        assetPage.value = 1;
        assetOptions.value = [];
        assetHasMore.value = true;
    }

    if (!assetHasMore.value && !reset) return;

    assetLoading.value = true;
    try {
        const response = await axios.get('/api/assets/selection', {
            params: {
                page: assetPage.value,
                per_page: 20,
                search: assetSearch.value,
                opd_id: form.from_opd_id ?? undefined,
            },
        });
        const data = response.data.data;
        const items = data.data.map((asset: any) => ({
            ...asset,
            display: `${asset.asset_code} - ${asset.name}`,
        }));
        const merged = reset ? items : [...assetOptions.value, ...items];
        assetOptions.value = merged;
        assetHasMore.value = !!data.next_page_url;
        assetPage.value++;
    } catch (error) {
        console.error('Error fetching assets:', error);
    } finally {
        assetLoading.value = false;
    }
};

const onAssetSearch = (query: string) => {
    assetSearch.value = query;
    fetchAssetOptions(true);
};

const onAssetLoadMore = () => {
    fetchAssetOptions(false);
};

watch(
    () => form.from_opd_id,
    () => {
        form.items = [];
        fetchAssetOptions(true);
    }
);

watch(
    () => form.to_opd_id,
    () => {
        fetchRoomOptions(true);
        form.items = form.items.map((item) => ({
            ...item,
            to_room_id: null,
        }));
    }
);

watch(isDrawerOpen, (newValue) => {
    if (newValue) {
        fetchOpdOptions(true);
        if (form.from_opd_id) {
            fetchAssetOptions(true);
        }
        if (form.to_opd_id) {
            fetchRoomOptions(true);
        }
    }
});

watch(isFilterDrawerOpen, (newValue) => {
    if (newValue) {
        fetchOpdOptions(true);
    }
});

const handleFilter = () => {
    currentPage.value = 1;
    fetchTransfers();
    isFilterDrawerOpen.value = false;
};

const resetFilter = () => {
    filters.value.status = '';
    filters.value.type = '';
    filters.value.from_opd_id = null;
    filters.value.to_opd_id = null;
    handleFilter();
};

onMounted(() => {
    fetchTransfers();
    fetchStats();
});

const tableData = computed(() => {
    return transfers.value.map((transfer: any) => {
        const statusLabel =
            transfer.status_label || statusLabelMap[transfer.status] || transfer.status;

        const direction = transfer.direction as 'incoming' | 'outgoing' | 'internal' | null;

        const directionLabel =
            transfer.direction_label ||
            (direction === 'incoming'
                ? 'Masuk ke OPD Saya'
                : direction === 'outgoing'
                    ? 'Keluar dari OPD Saya'
                    : direction === 'internal'
                        ? 'Internal OPD Saya'
                        : '-');

        return {
            id: transfer.id,
            transfer_number: transfer.transfer_number,
            from_opd_name: transfer.from_opd ? transfer.from_opd.name : '-',
            to_opd_name: transfer.to_opd ? transfer.to_opd.name : '-',
            type_label:
                transfer.type_label ||
                (transfer.type === 'internal'
                    ? 'Internal'
                    : transfer.type === 'external'
                        ? 'Eksternal'
                        : transfer.type),
            status_label: statusLabel,
            direction_label: directionLabel,
            can_approve: transfer.type === 'external' && transfer.status === 'pending' && transfer.is_incoming,
            can_reject: transfer.type === 'external' && transfer.status === 'pending' && transfer.is_incoming,
        };
    });
});

const handlePageChange = (page: number) => {
    currentPage.value = page;
    fetchTransfers();
};

const handlePerPageChange = (newPerPage: number) => {
    perPage.value = newPerPage;
    currentPage.value = 1;
    fetchTransfers();
};

const handleSearch = debounce((search: string) => {
    searchFilter.value = search;
    currentPage.value = 1;
    fetchTransfers();
}, 800);

const handleAddTransfer = () => {
    resetForm();
    isDrawerOpen.value = true;
};

const addItem = () => {
    form.items.push({
        asset_id: null,
        from_room_id: null,
        to_room_id: null,
    });
};

const removeItem = (index: number) => {
    form.items.splice(index, 1);
};

const getFromRoom = (assetId: string | null) => {
    if (!assetId) return null;
    const asset = assetOptions.value.find((a) => a.id === assetId);
    if (!asset || !asset.room) return null;
    return asset.room;
};

const getFilteredRoomOptions = (assetId: string | null) => {
    const fromRoom = getFromRoom(assetId);
    if (!fromRoom) return roomOptions.value;

    return roomOptions.value.filter((room: any) => room.id !== fromRoom.id);
};

const resetForm = () => {
    form.reset();
    form.clearErrors();
    form.from_opd_id = currentOpdSet.value ? (currentOpd.value as any).id : null;
    form.to_opd_id = null;
    form.items = [];
    form.notes = '';
    assetOptions.value = [];
    roomOptions.value = [];
};

const saveTransfer = async () => {
    form.processing = true;

    const payload = {
        ...form.data(),
    };

    try {
        await axios.post('/api/transfers', payload);
        closeDrawer();
        fetchTransfers();
    } catch (error: any) {
        if (error.response && error.response.data && error.response.data.errors) {
            form.errors = error.response.data.errors;
        } else {
            console.error('Error saving transfer:', error);
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
    alert('Fitur export belum tersedia.');
};
</script>
