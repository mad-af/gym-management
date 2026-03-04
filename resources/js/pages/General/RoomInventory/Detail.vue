<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="currentPageTitle" />

        <div class="mt-4">
            <div v-if="loadingRoom"
                class="flex h-40 items-center justify-center rounded-2xl border border-gray-200 bg-white px-5 py-6 xl:px-8 xl:py-8 dark:border-gray-800 dark:bg-white/3">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Memuat detail KIR ruangan...
                </p>
            </div>

            <div v-else-if="room" class="grid grid-cols-1 gap-4 xl:grid-cols-3 xl:gap-6">
                <div class="space-y-4 xl:col-span-2 order-2 xl:order-1">
                    <ComponentCard title="Daftar Aset di Ruangan">
                        <template #actions>
                            <RoomInventoryFilterDrawer v-model:status="assetFilters.status"
                                v-model:search="assetsSearch" @apply="handleAssetFilter" @reset="handleAssetReset" />
                        </template>

                        <BasicTable :columns="itemColumns" :rows="itemRows" rowKey="id">
                            <template #cell-asset_display="{ row }">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800">
                                        <AppImage v-if="row.photo" :src="assetPhotoSrc(row.photo)"
                                            :placeholder="assetPhotoPlaceholder(row.photo)" :alt="row.name || 'Aset'"
                                            containerClass="h-10 w-10 rounded-lg"
                                            imgClass="h-10 w-10 object-cover rounded-lg" />
                                        <ImageIcon v-else class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium text-gray-900 dark:text-white">
                                            {{ row.name || '-' }}
                                        </p>
                                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                                            {{ row.asset_code || '-' }}
                                        </p>
                                    </div>
                                </div>
                            </template>

                            <template #cell-actions="{ row }">
                                <div class="flex items-center justify-center">
                                    <Link v-if="row.asset_id" v-can="ASSET_PERMISSIONS.VIEW"
                                        :href="`/asset-management/assets/${row.asset_id}`"
                                        class="text-gray-500 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-500"
                                        title="Detail Aset">
                                        <EyeIcon class="h-4.5 w-4.5" />
                                    </Link>
                                </div>
                            </template>

                            <template #cell-status_label="{ row }">
                                <span
                                    class="inline-flex rounded-full px-3 py-1 text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300"
                                    :class="row.status_class">
                                    {{ row.status_label || '-' }}
                                </span>
                            </template>
                        </BasicTable>
                    </ComponentCard>
                </div>

                <div class="xl:col-span-1 order-1 xl:order-2">
                    <CardList title="Informasi Ruangan" listClass="text-sm text-gray-700 dark:text-gray-300">
                        <li class="flex items-center justify-between py-2">
                            <span class="text-gray-500 dark:text-gray-400 text-xs">
                                Kode Ruangan
                            </span>
                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ room.code || '-' }}
                            </span>
                        </li>
                        <li class="flex items-center justify-between py-2">
                            <span class="text-gray-500 dark:text-gray-400 text-xs">
                                Nama Ruangan
                            </span>
                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ room.name || '-' }}
                            </span>
                        </li>
                        <li class="flex items-center justify-between py-2">
                            <span class="text-gray-500 dark:text-gray-400 text-xs">
                                OPD
                            </span>
                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ room.opd?.name ?? '-' }}
                            </span>
                        </li>
                        <li class="flex items-center justify-between py-2">
                            <span class="text-gray-500 dark:text-gray-400 text-xs">
                                Jumlah Aset
                            </span>
                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ stats.total_assets }}
                            </span>
                        </li>
                        <li v-for="(count, statusKey) in stats.by_status" :key="statusKey"
                            class="flex items-center justify-between py-2">
                            <span class="text-gray-500 dark:text-gray-400 text-xs">
                                Aset {{ formatStatusLabel(statusKey) }}
                            </span>
                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ count }}
                            </span>
                        </li>
                        <li class="pt-3">
                            <Button v-can="ROOM_INVENTORY_PERMISSIONS.VIEW" size="sm" variant="outline" class="w-full"
                                :onClick="downloadPdf" :disabled="!room" :startIcon="FileTextIcon">
                                Download Dokumen KIR
                            </Button>
                        </li>
                    </CardList>
                </div>
            </div>

            <div v-else
                class="flex h-40 items-center justify-center rounded-2xl border border-gray-200 bg-white px-5 py-6 xl:px-8 xl:py-8 dark:border-gray-800 dark:bg-white/3">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Data KIR ruangan tidak ditemukan.
                </p>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, ref } from 'vue';
import AppImage from '@/components/common/AppImage.vue';
import CardList from '@/components/common/CardList.vue';
import ComponentCard from '@/components/common/ComponentCard.vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import BasicTable from '@/components/tables/basic-tables/BasicTable.vue';
import Button from '@/components/ui/Button.vue';
import { ASSET_PERMISSIONS, ROOM_INVENTORY_PERMISSIONS } from '@/directives/permissions';
import { FileTextIcon, ImageIcon, EyeIcon } from '@/icons';
import RoomInventoryFilterDrawer from './components/RoomInventoryFilterDrawer.vue';

const props = defineProps<{
    roomId: string;
}>();

const currentPageTitle = ref('Detail KIR Ruangan');
const room = ref<any | null>(null);
const stats = ref<{ total_assets: number; by_status: Record<string, number> }>({
    total_assets: 0,
    by_status: {},
});

const loadingRoom = ref(true);

const assets = ref<any[]>([]);
const statusOptions = ref<{ value: string; label: string; class: string }[]>([]);
const totalAssets = ref(0);
const assetsCurrentPage = ref(1);
const assetsPerPage = ref(10);
const assetsSearch = ref('');

const assetFilters = ref({
    status: '',
});

const fetchRoomDetail = async () => {
    loadingRoom.value = true;
    try {
        const response = await axios.get(`/api/room-inventory/rooms/${props.roomId}`);
        const data = response.data.data;
        room.value = data.room;
        stats.value = data.stats;
    } catch (error) {
        console.error('Error fetching room inventory detail:', error);
    } finally {
        loadingRoom.value = false;
    }
};

const fetchRoomAssets = async () => {
    try {
        const response = await axios.get(`/api/room-inventory/rooms/${props.roomId}/assets`, {
            params: {
                page: assetsCurrentPage.value,
                per_page: assetsPerPage.value,
                search: assetsSearch.value || undefined,
                status: assetFilters.value.status || undefined,
            },
        });
        const data = response.data.data;
        const paginator = data.items;

        assets.value = paginator.data ?? [];
        totalAssets.value = paginator.total ?? 0;
        assetsCurrentPage.value = paginator.current_page ?? 1;
        assetsPerPage.value = paginator.per_page ?? assetsPerPage.value;

        statusOptions.value = data.status_options ?? [];
    } catch (error) {
        console.error('Error fetching room inventory assets:', error);
    }
};

const handleAssetFilter = () => {
    assetsCurrentPage.value = 1;
    fetchRoomAssets();
};

const handleAssetReset = () => {
    assetFilters.value.status = '';
    assetsSearch.value = '';
    assetsCurrentPage.value = 1;
    fetchRoomAssets();
};

const downloadPdf = () => {
    if (!room.value) return;
    const url = `/api/room-inventory/rooms/${props.roomId}/pdf`;
    window.open(url, '_blank');
};

onMounted(async () => {
    await Promise.all([fetchRoomDetail(), fetchRoomAssets()]);
});

const itemRows = computed(() => {
    return assets.value.map((asset: any) => ({
        id: asset.id,
        asset_id: asset.id,
        name: asset.name,
        asset_code: asset.asset_code,
        photo: asset.photo ?? null,
        category_name: asset.category?.name ?? '-',
        ...buildStatusFields(asset),
    }));
});

const buildStatusFields = (asset: any) => {
    const statusOption = statusOptions.value.find((opt) => opt.value === asset.status);

    if (!statusOption) {
        return {
            status_label: asset.status ?? '-',
            status_class: '',
        };
    }

    return {
        status_label: statusOption.label,
        status_class: statusOption.class ?? '',
    };
};

const itemColumns = [
    {
        key: 'asset_display',
        header: 'Aset',
        widthClass: 'w-5/12 px-5 py-3 text-left sm:px-6',
    },
    {
        key: 'category_name',
        header: 'Kategori',
        widthClass: 'w-3/12 px-5 py-3 text-left sm:px-6',
    },
    {
        key: 'status_label',
        header: 'Status',
        widthClass: 'w-2/12 px-5 py-3 text-left sm:px-6',
    },
    {
        key: 'actions',
        header: 'Aksi',
        widthClass: 'w-2/12 px-5 py-3 text-center sm:px-6',
    },
];

const formatStatusLabel = (statusKey: string) => {
    if (!statusKey) return '-';
    if (statusKey === 'good') return 'Baik';
    if (statusKey === 'minor_damage') return 'Rusak Ringan';
    if (statusKey === 'major_damage') return 'Rusak Berat';
    return statusKey;
};

const assetPhotoSrc = (value: unknown): string => {
    if (!value) {
        return '';
    }

    if (typeof value === 'string') {
        return value;
    }

    if (typeof value === 'object' && value !== null && 'url' in value) {
        return (value as any).url || '';
    }

    return '';
};

const assetPhotoPlaceholder = (value: unknown): string => {
    if (!value || typeof value !== 'object' || value === null || !('placeholder' in value)) {
        return '';
    }

    return (value as any).placeholder || '';
};
</script>
