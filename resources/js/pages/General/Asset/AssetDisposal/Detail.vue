<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="currentPageTitle" />

        <div class="mt-4">
            <div v-if="loading"
                class="flex h-40 items-center justify-center rounded-2xl border border-gray-200 bg-white px-5 py-6 xl:px-8 xl:py-8 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Memuat detail penghapusan aset...
                </p>
            </div>

            <div v-else-if="disposal" class="grid grid-cols-1 gap-4 xl:grid-cols-3 xl:gap-6">
                <div class="space-y-4 xl:col-span-2 order-2 xl:order-1">
                    <ComponentCard title="Daftar Aset yang Dihapus">
                        <BasicTable :columns="itemColumns" :rows="itemRows" rowKey="id">
                            <template #cell-actions="{ row }">
                                <div class="flex items-center justify-center">
                                    <AssetDetailModal v-if="row.asset_id" :assetId="String(row.asset_id)" />
                                </div>
                            </template>
                        </BasicTable>
                    </ComponentCard>
                </div>

                <div class="xl:col-span-1 order-1 xl:order-2">
                    <CardList title="Informasi Dokumen" listClass="text-sm text-gray-700 dark:text-gray-300">
                        <li class="flex items-center justify-between py-2">
                            <span class="text-gray-500 dark:text-gray-400 text-xs">
                                Jenis Penghapusan
                            </span>
                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ disposalTypeLabel }}
                            </span>
                        </li>
                        <li class="flex items-center justify-between py-2">
                            <span class="text-gray-500 dark:text-gray-400 text-xs">
                                Nomor Dokumen
                            </span>
                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ disposal.disposal_number }}
                            </span>
                        </li>
                        <li class="flex items-center justify-between py-2">
                            <span class="text-gray-500 dark:text-gray-400 text-xs">
                                Tanggal Penghapusan
                            </span>
                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ formattedDisposalDate }}
                            </span>
                        </li>
                        <li class="flex items-center justify-between py-2">
                            <span class="text-gray-500 dark:text-gray-400 text-xs">
                                OPD
                            </span>
                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ disposal.opd?.name ?? '-' }}
                            </span>
                        </li>
                        <li class="flex items-center justify-between py-2">
                            <span class="text-gray-500 dark:text-gray-400 text-xs">
                                Jumlah Aset
                            </span>
                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ items.length }}
                            </span>
                        </li>
                        <li class="flex items-center justify-between py-2">
                            <span class="text-gray-500 dark:text-gray-400 text-xs">
                                Dibuat Oleh
                            </span>
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-8 w-8 items-center justify-center overflow-hidden rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                                    <AppImage v-if="creatorAvatarSrc" :src="creatorAvatarSrc"
                                        :placeholder="creatorAvatarPlaceholder" :alt="creatorName || 'Dibuat oleh'"
                                        containerClass="h-8 w-8 rounded-full" imgClass="rounded-full" />
                                    <span v-else class="text-xs font-medium">
                                        {{ creatorInitials }}
                                    </span>
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-xs font-medium text-gray-900 dark:text-white">
                                        {{ creatorName || '-' }}
                                    </p>
                                </div>
                            </div>
                        </li>
                        <li class="py-2">
                            <div class="flex flex-col gap-1">
                                <span class="text-gray-500 dark:text-gray-400 text-xs">
                                    Catatan
                                </span>
                                <span class="text-gray-700 dark:text-gray-300">
                                    {{ disposal.notes || '-' }}
                                </span>
                            </div>
                        </li>
                        <li v-if="disposal.document_path" class="py-2">
                            <div class="flex flex-col gap-1">
                                <span class="text-gray-500 dark:text-gray-400 text-xs">
                                    Dokumen
                                </span>
                                <a :href="disposal.document_path" target="_blank" rel="noopener"
                                    class="text-sm font-medium text-primary-600 hover:underline dark:text-primary-400">
                                    Lihat Dokumen
                                </a>
                            </div>
                        </li>
                    </CardList>
                </div>
            </div>

            <div v-else
                class="flex h-40 items-center justify-center rounded-2xl border border-gray-200 bg-white px-5 py-6 xl:px-8 xl:py-8 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Data penghapusan aset tidak ditemukan.
                </p>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import axios from 'axios';
import { computed, onMounted, ref } from 'vue';
import AppImage from '@/components/common/AppImage.vue';
import CardList from '@/components/common/CardList.vue';
import ComponentCard from '@/components/common/ComponentCard.vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import BasicTable from '@/components/tables/basic-tables/BasicTable.vue';
import AssetDetailModal from './components/AssetDetailModal.vue';
const props = defineProps<{
    disposalId: string;
}>();

const currentPageTitle = ref('Detail Penghapusan Aset');
const disposal = ref<any | null>(null);
const loading = ref(true);

const formatDate = (date: string | null | undefined) => {
    if (!date) return '-';
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(date));
};

const items = computed(() => {
    if (!disposal.value || !Array.isArray(disposal.value.items)) return [];
    return disposal.value.items;
});

const creator = computed(() => {
    if (!disposal.value) return null;
    return disposal.value.created_by || null;
});

const creatorName = computed(() => {
    if (!creator.value) return '';
    return creator.value.name || '';
});

const creatorAvatarSrc = computed(() => {
    if (!creator.value) return '';
    const avatar = creator.value.avatar;
    const defaultAvatar = '/images/user/owner.jpg';

    if (!avatar) {
        return '';
    }

    if (typeof avatar === 'object') {
        return avatar.url || defaultAvatar;
    }

    if (typeof avatar === 'string') {
        return avatar || defaultAvatar;
    }

    return '';
});

const creatorAvatarPlaceholder = computed(() => {
    if (!creator.value) return '';
    const avatar = creator.value.avatar;

    if (avatar && typeof avatar === 'object') {
        return avatar.placeholder || '';
    }

    return '';
});

const creatorInitials = computed(() => {
    if (!creatorName.value) return '';
    const parts = creatorName.value.trim().split(' ');
    const first = parts[0]?.[0] || '';
    const last = parts.length > 1 ? parts[parts.length - 1]?.[0] || '' : '';

    return (first + last).toUpperCase();
});

const itemColumns = [
    {
        key: 'asset_display',
        header: 'Aset',
        widthClass: 'w-4/12 px-5 py-3 text-left sm:px-6',
    },
    {
        key: 'opd_name',
        header: 'OPD',
        widthClass: 'w-3/12 px-5 py-3 text-left sm:px-6',
    },
    {
        key: 'room_name',
        header: 'Ruangan',
        widthClass: 'w-3/12 px-5 py-3 text-left sm:px-6',
    },
    {
        key: 'condition_at_disposal',
        header: 'Kondisi Saat Penghapusan',
        widthClass: 'w-3/12 px-5 py-3 text-left sm:px-6',
    },
    {
        key: 'reason',
        header: 'Alasan Penghapusan',
        widthClass: 'w-3/12 px-5 py-3 text-left sm:px-6',
    },
    {
        key: 'actions',
        header: 'Aksi',
        widthClass: 'w-2/12 px-5 py-3 text-center sm:px-6',
    },
];

const itemRows = computed(() => {
    if (!Array.isArray(items.value)) return [];

    return items.value.map((item: any) => {
        const asset = item.asset || null;
        const opd = asset?.opd || null;
        const room = asset?.room || null;

        return {
            id: item.id,
            asset_id: asset?.id ?? null,
            asset_display: asset
                ? `${asset.name ?? '-'} (${asset.asset_code ?? '-'})`
                : '-',
            opd_name: opd?.name ?? '-',
            room_name: room?.name ?? '-',
            condition_at_disposal: item.condition_at_disposal ?? '-',
            reason: item.reason ?? '-',
        };
    });
});

const disposalTypeLabel = computed(() => {
    if (!disposal.value) return '-';
    const type = disposal.value.disposal_type;
    if (type === 'destruction') return 'Pemusnahan';
    if (type === 'auction') return 'Lelang';
    if (type === 'grant') return 'Hibah';
    if (type === 'write_off') return 'Penghapusan';
    return type;
});

const formattedDisposalDate = computed(() => {
    if (!disposal.value) return '-';
    return formatDate(disposal.value.disposal_date);
});

onMounted(async () => {
    try {
        const response = await axios.get(`/api/disposals/${props.disposalId}`);
        disposal.value = response.data.data;
    } catch (error) {
        console.error('Error fetching disposal detail:', error);
    } finally {
        loading.value = false;
    }
});
</script>
