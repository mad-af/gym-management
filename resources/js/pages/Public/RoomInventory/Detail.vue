<template>
    <ThemeProvider>

        <Head :title="pageTitle" />
        <div class="min-h-screen bg-gray-50/50 p-4 md:p-6 lg:p-8 dark:bg-gray-900/50">
            <div class="mx-auto max-w-7xl transition-all duration-300 ease-in-out">
                <!-- Header -->
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Detail KIR Ruangan</h1>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Kartu Inventaris Ruangan (KIR)
                        </p>
                    </div>
                    <Link href="/" v-if="!user"
                        class="rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                        &larr; Beranda
                    </Link>
                    <Link href="/dashboard" v-else
                        class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                        Dashboard
                    </Link>
                </div>

                <div v-if="loadingRoom"
                    class="flex h-60 items-center justify-center rounded-2xl border border-gray-200 bg-white px-5 py-6 xl:px-8 xl:py-8 dark:border-gray-800 dark:bg-white/3">
                    <div class="text-center">
                        <svg class="mx-auto h-8 w-8 animate-spin text-brand-500" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            Memuat detail ruangan...
                        </p>
                    </div>
                </div>

                <div v-else-if="room" class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-3 xl:gap-6">
                    <!-- Main Content -->
                    <div class="space-y-4 order-last xl:order-first xl:col-span-2">
                        <!-- Asset List -->
                        <ComponentCard title="Daftar Aset di Ruangan">
                            <template #actions>
                                <RoomInventoryFilterDrawer v-model:status="assetFilters.status"
                                    v-model:search="assetsSearch" @apply="handleAssetFilter"
                                    @reset="handleAssetReset" />
                            </template>

                            <div v-if="loadingAssets" class="flex h-40 items-center justify-center">
                                <svg class="h-6 w-6 animate-spin text-brand-500" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4">
                                    </circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </div>

                            <BasicTable v-else :columns="assetListColumns" :rows="assetListRows" rowKey="id"
                                :pagination="{
                                    current_page: currentPage,
                                    last_page: totalPages,
                                    total: totalAssets,
                                    per_page: 10
                                }" @page-change="changePage">
                                <template #cell-asset_display="{ row }">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 flex-shrink-0 items-center justify-center overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800">
                                            <AppImage v-if="row.photo" :src="row.photo" :alt="row.name"
                                                class="h-10 w-10 rounded-lg object-cover" />
                                            <ImageIcon v-else class="h-5 w-5 text-gray-400" />
                                        </div>
                                        <div class="min-w-0">
                                            <div class="truncate font-medium text-gray-900 dark:text-white">
                                                {{ row.name }}
                                            </div>
                                            <div class="truncate text-xs text-gray-500">
                                                {{ row.asset_code }}
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <template #cell-status_label="{ row }">
                                    <div class="flex justify-center">
                                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium"
                                            :class="getStatusClass(row.status)">
                                            {{ formatStatusLabel(row.status) }}
                                        </span>
                                    </div>
                                </template>

                                <template #cell-actions="{ row }">
                                    <div class="flex items-center justify-center">
                                        <AssetDetailModal :asset-id="row.id" :status-options="statusOptions"
                                            :condition-options="conditionOptions" />
                                    </div>
                                </template>
                            </BasicTable>
                        </ComponentCard>
                    </div>

                    <!-- Sidebar -->
                    <div class="xl:col-span-1 order-first xl:order-last">
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
                                    Aset {{ formatStatusLabel(String(statusKey)) }}
                                </span>
                                <span class="font-medium text-gray-900 dark:text-white">
                                    {{ count }}
                                </span>
                            </li>
                            <li class="pt-3 space-y-3">
                                <a :href="`/api/public/room-inventory/${props.roomId}/pdf`" target="_blank"
                                    class="flex w-full items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                                    <FileTextIcon class="h-4 w-4" />
                                    Download Dokumen KIR
                                </a>
                                <Link v-if="user" :href="`/room-inventory/${props.roomId}`"
                                    class="flex w-full items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                                    <EyeIcon class="h-4 w-4" />
                                    Lihat di Dashboard
                                </Link>
                            </li>
                        </CardList>
                    </div>
                </div>

                <div v-else
                    class="flex h-60 items-center justify-center rounded-2xl border border-gray-200 bg-white px-5 py-6 xl:px-8 xl:py-8 dark:border-gray-800 dark:bg-white/3">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Data ruangan tidak ditemukan.
                    </p>
                </div>
            </div>
        </div>
    </ThemeProvider>
</template>

<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, ref } from 'vue';
import AppImage from '@/components/common/AppImage.vue';
import CardList from '@/components/common/CardList.vue';
import ComponentCard from '@/components/common/ComponentCard.vue';
import ThemeProvider from '@/components/layout/ThemeProvider.vue';
import BasicTable from '@/components/tables/basic-tables/BasicTable.vue';
import { FileTextIcon, ImageIcon, EyeIcon } from '@/icons';
import AssetDetailModal from './components/AssetDetailModal.vue';
import RoomInventoryFilterDrawer from './components/RoomInventoryFilterDrawer.vue';

const props = defineProps<{
    roomId: string;
}>();

const page = usePage();
const user = page.props.auth.user;

const pageTitle = ref('Detail KIR Ruangan');
const room = ref<any>(null);
const stats = ref<any>({ total_assets: 0, by_status: {} });
const loadingRoom = ref(true);
const loadingAssets = ref(false);
const assets = ref<any[]>([]);
const totalPages = ref(1);
const currentPage = ref(1);
const totalAssets = ref(0);
const assetsSearch = ref('');
const assetFilters = ref({
    status: '',
});

const statusOptions = ref<any[]>([]);
const conditionOptions = ref<any[]>([]);

const fetchRoomData = async () => {
    loadingRoom.value = true;
    try {
        const response = await axios.get(`/api/public/room-inventory/${props.roomId}`);
        const data = response.data.data;
        room.value = data.room;
        stats.value = data.stats;
        pageTitle.value = `KIR - ${data.room.name}`;
    } catch (error) {
        console.error('Error fetching room data:', error);
    } finally {
        loadingRoom.value = false;
    }
};

const fetchAssets = async () => {
    loadingAssets.value = true;
    try {
        const response = await axios.get(`/api/public/room-inventory/${props.roomId}/assets`, {
            params: {
                page: currentPage.value,
                search: assetsSearch.value,
                status: assetFilters.value.status,
            },
        });
        const data = response.data.data;
        assets.value = data.items.data;
        totalPages.value = data.items.last_page;
        currentPage.value = data.items.current_page;
        totalAssets.value = data.items.total;
        statusOptions.value = data.status_options;
        conditionOptions.value = data.condition_options;
    } catch (error) {
        console.error('Error fetching assets:', error);
    } finally {
        loadingAssets.value = false;
    }
};

const handleAssetFilter = () => {
    currentPage.value = 1;
    fetchAssets();
};

const handleAssetReset = () => {
    assetsSearch.value = '';
    assetFilters.value.status = '';
    currentPage.value = 1;
    fetchAssets();
};

const changePage = (page: number) => {
    currentPage.value = page;
    fetchAssets();
};

onMounted(() => {
    fetchRoomData();
    fetchAssets();
});

// Format helpers
const formatStatusLabel = (key: string) => {
    const map: Record<string, string> = {
        'active': 'Aktif',
        'inactive': 'Tidak Aktif',
        'disposed': 'Dibuang',
        'under_maintenance': 'Dalam Perawatan',
        'good': 'Baik',
        'minor_damage': 'Rusak Ringan',
        'major_damage': 'Rusak Berat',
        'lost': 'Hilang',
        'borrowed': 'Dipinjam',
    };
    return map[key] || key;
};

const getStatusClass = (status: string) => {
    switch (status) {
        case 'active':
        case 'good':
            return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400';
        case 'inactive':
            return 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300';
        case 'under_maintenance':
        case 'minor_damage':
            return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400';
        case 'major_damage':
        case 'lost':
        case 'disposed':
            return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400';
        default:
            return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300';
    }
};

// BasicTable Configuration
const assetListColumns = [
    {
        key: 'asset_display',
        header: 'Aset',
        widthClass: 'w-5/12 px-4 py-3 text-left',
    },
    {
        key: 'category_name',
        header: 'Kategori',
        widthClass: 'w-3/12 px-4 py-3 text-left',
    },
    {
        key: 'status_label',
        header: 'Status',
        widthClass: 'w-2/12 px-4 py-3 text-center',
    },
    {
        key: 'actions',
        header: 'Aksi',
        widthClass: 'w-2/12 px-4 py-3 text-center',
    },
];

const assetListRows = computed(() => {
    return assets.value.map(asset => {
        let photoUrl = null;
        if (asset.photo) {
            if (typeof asset.photo === 'string') {
                photoUrl = asset.photo;
            } else if (typeof asset.photo === 'object' && asset.photo.url) {
                photoUrl = asset.photo.url;
            }
        }

        return {
            id: asset.id,
            name: asset.name,
            asset_code: asset.asset_code,
            photo: photoUrl,
            category_name: asset.category?.name || '-',
            status: asset.status, // Pass raw status for custom formatting in slot
        };
    });
});
</script>