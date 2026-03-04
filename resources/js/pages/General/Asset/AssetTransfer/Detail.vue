<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="currentPageTitle" />

        <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-3 xl:gap-6">
            <div class="space-y-4 xl:col-span-2">
                <DetailGrid :cardTitle="cardTitle" :cardDesc="cardDesc" :items="detailItems" :columns="2" />

                <ComponentCard title="Daftar Aset yang Dimutasi">
                    <BasicTable :columns="itemColumns" :rows="itemRows" rowKey="id">
                        <template #cell-actions="{ row }">
                            <div class="flex items-center justify-center">
                                <AssetDetailModal v-if="row.asset_id" :assetId="String(row.asset_id)" />
                            </div>
                        </template>
                    </BasicTable>
                </ComponentCard>
            </div>

            <div class="xl:col-span-1">
                <CardList v-if="showActionCard" title="Aksi Mutasi">
                    <li v-if="canCancel" v-can="ASSET_TRANSFER_PERMISSIONS.CREATE" class="py-2">
                        <Button size="sm" variant="outline"
                            class="w-full border-error-500 text-error-600 hover:bg-error-50 dark:border-error-500 dark:text-error-400 dark:hover:bg-error-500/10"
                            :startIcon="CloseIcon" :onClick="cancelTransfer">
                            Batalkan Mutasi
                        </Button>
                    </li>
                    <li v-if="canApprove" v-can="ASSET_TRANSFER_PERMISSIONS.APPROVE" class="py-2">
                        <Button size="sm" variant="primary" class="w-full" :startIcon="CheckCircleIcon"
                            :onClick="approveTransfer">
                            Setujui Mutasi
                        </Button>
                    </li>
                    <li v-if="canReject" v-can="ASSET_TRANSFER_PERMISSIONS.APPROVE" class="py-2">
                        <Button size="sm" variant="outline" class="w-full" :startIcon="CloseIcon"
                            :onClick="rejectTransfer">
                            Tolak Mutasi
                        </Button>
                    </li>
                </CardList>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import axios from 'axios';
import { computed, onMounted, ref } from 'vue';
import CardList from '@/components/common/CardList.vue';
import ComponentCard from '@/components/common/ComponentCard.vue';
import DetailGrid from '@/components/common/DetailGrid.vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import BasicTable from '@/components/tables/basic-tables/BasicTable.vue';
import Button from '@/components/ui/Button.vue';
import { ASSET_TRANSFER_PERMISSIONS } from '@/directives/permissions';
import { CheckCircleIcon, CloseIcon } from '@/icons';
import AssetDetailModal from './components/AssetDetailModal.vue';

const props = defineProps<{
    transferId: string;
}>();

const currentPageTitle = ref('Detail Mutasi Aset');
const transfer = ref<any | null>(null);
const loading = ref(true);

const formatDateTime = (value: string | null | undefined) => {
    if (!value) return '-';
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(value));
};

const statusLabel = computed(() => {
    if (!transfer.value) return '-';
    if (transfer.value.status_label) return transfer.value.status_label;
    const status = transfer.value.status;
    if (status === 'pending') return 'Pending';
    if (status === 'cancel') return 'Dibatalkan';
    if (status === 'rejected') return 'Ditolak';
    if (status === 'completed') return 'Selesai';
    return status;
});

const statusBadgeClass = computed(() => {
    if (!transfer.value) {
        return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300';
    }
    if (transfer.value.status_badge_class) {
        return transfer.value.status_badge_class;
    }
    switch (transfer.value.status) {
        case 'pending':
            return 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-500';
        case 'cancel':
            return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300';
        case 'rejected':
            return 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-500';
        case 'completed':
            return 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500';
        default:
            return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300';
    }
});

const typeLabel = computed(() => {
    if (!transfer.value) return '-';
    if (transfer.value.type_label) return transfer.value.type_label;
    const type = transfer.value.type;
    if (type === 'internal') return 'Mutasi Internal';
    if (type === 'external') return 'Mutasi Eksternal';
    return type;
});

const directionLabel = computed(() => {
    if (!transfer.value) return '-';
    if (transfer.value.direction_label) return transfer.value.direction_label;
    const direction = transfer.value.direction as 'incoming' | 'outgoing' | 'internal' | null;

    if (direction === 'incoming') {
        return 'Masuk ke OPD Saya';
    }

    if (direction === 'outgoing') {
        return 'Keluar dari OPD Saya';
    }

    if (direction === 'internal') {
        return 'Internal OPD Saya';
    }

    return '-';
});

const directionBadgeClass = computed(() => {
    if (!transfer.value || !transfer.value.direction) {
        return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300';
    }

    const direction = transfer.value.direction as 'incoming' | 'outgoing' | 'internal';

    if (direction === 'incoming') {
        return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400';
    }

    if (direction === 'outgoing') {
        return 'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400';
    }

    if (direction === 'internal') {
        return 'bg-purple-50 text-purple-700 dark:bg-purple-500/15 dark:text-purple-400';
    }

    return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300';
});

const cardTitle = computed(() => {
    if (!transfer.value) {
        return 'Detail Mutasi Aset';
    }

    return transfer.value.transfer_number || 'Detail Mutasi Aset';
});

const cardDesc = computed(() => {
    if (!transfer.value) {
        return '';
    }

    const fromName = transfer.value.from_opd?.name ?? '-';
    const toName = transfer.value.to_opd?.name ?? '-';

    return `${fromName} → ${toName}`;
});

const isExternal = computed(() => {
    if (!transfer.value) {
        return false;
    }
    return transfer.value.type === 'external';
});

const showActionCard = computed(() => {
    if (!transfer.value) {
        return false;
    }
    if (transfer.value.status === 'completed') {
        return false;
    }
    return isExternal.value;
});

const canCancel = computed(() => {
    if (!transfer.value) {
        return false;
    }
    return isExternal.value && transfer.value.status === 'pending' && transfer.value.is_outgoing;
});

const canApprove = computed(() => {
    if (!transfer.value) {
        return false;
    }
    return isExternal.value && transfer.value.status === 'pending' && transfer.value.is_incoming;
});

const canReject = computed(() => {
    if (!transfer.value) {
        return false;
    }
    return isExternal.value && transfer.value.status === 'pending' && transfer.value.is_incoming;
});

const detailItems = computed<any[]>(() => {
    if (!transfer.value) {
        return [];
    }

    const items: any[] = [
        {
            key: 'type',
            label: 'Tipe Mutasi',
            value: typeLabel.value,
        },
        {
            key: 'direction',
            label: 'Arah',
            type: 'badge',
            value: directionLabel.value,
            badgeClass: directionBadgeClass.value,
        },
        {
            key: 'status',
            label: 'Status',
            type: 'badge',
            value: statusLabel.value,
            badgeClass: statusBadgeClass.value,
        },
        {
            key: 'requested_at',
            label: 'Tanggal Permintaan',
            value: formatDateTime(transfer.value.requested_at),
        },
    ];

    if (transfer.value.approved_at) {
        items.push({
            key: 'approved_at',
            label: 'Tanggal Persetujuan',
            value: formatDateTime(transfer.value.approved_at),
        });
    }

    if (transfer.value.completed_at) {
        items.push({
            key: 'completed_at',
            label: 'Tanggal Selesai',
            value: formatDateTime(transfer.value.completed_at),
        });
    }

    if (transfer.value.requester) {
        items.push({
            key: 'requester',
            label: 'Pemohon',
            type: 'user',
            value: transfer.value.requester,
        });
    }

    if (transfer.value.approver) {
        items.push({
            key: 'approver',
            label: 'Disetujui Oleh',
            type: 'user',
            value: transfer.value.approver,
        });
    }

    if (transfer.value.notes) {
        items.push({
            key: 'notes',
            type: 'note',
            label: 'Catatan',
            value: transfer.value.notes,
        });
    }

    return items;
});

const itemColumns = [
    { key: 'asset_display', header: 'Aset', widthClass: 'w-4/12 px-5 py-3 text-left sm:px-6' },
    { key: 'from_room_name', header: 'Dari Ruangan', widthClass: 'w-3/12 px-5 py-3 text-left sm:px-6' },
    { key: 'to_room_name', header: 'Ke Ruangan', widthClass: 'w-3/12 px-5 py-3 text-left sm:px-6' },
    { key: 'actions', header: 'Aksi', widthClass: 'w-2/12 px-5 py-3 text-center sm:px-6' },
];

const itemRows = computed(() => {
    if (!transfer.value || !Array.isArray(transfer.value.items)) {
        return [];
    }

    return transfer.value.items.map((item: any) => {
        const asset = item.asset || null;
        const fromRoom = item.from_room || item.fromRoom || null;
        const toRoom = item.to_room || item.toRoom || null;

        return {
            id: item.id,
            asset_id: asset?.id ?? null,
            asset_display: asset
                ? `${asset.asset_code ?? '-'} - ${asset.name ?? '-'}`
                : '-',
            from_room_name: fromRoom?.name ?? '-',
            to_room_name: toRoom?.name ?? '-',
        };
    });
});

const refreshDetail = async () => {
    try {
        const response = await axios.get(`/api/transfers/${props.transferId}`);
        transfer.value = response.data.data;
    } catch (error) {
        console.error('Error refreshing transfer detail:', error);
    }
};

const cancelTransfer = async () => {
    if (!transfer.value) {
        return;
    }
    const ok = confirm('Apakah Anda yakin ingin membatalkan mutasi ini?');
    if (!ok) {
        return;
    }
    try {
        const response = await axios.post(`/api/transfers/${transfer.value.id}/cancel`);
        transfer.value = response.data.data;
        await refreshDetail();
    } catch (error) {
        console.error('Error cancelling transfer:', error);
    }
};

const approveTransfer = async () => {
    if (!transfer.value) {
        return;
    }
    const ok = confirm('Apakah Anda yakin ingin menyetujui mutasi ini?');
    if (!ok) {
        return;
    }
    try {
        const response = await axios.post(`/api/transfers/${transfer.value.id}/approve`);
        transfer.value = response.data.data;
        await refreshDetail();
    } catch (error) {
        console.error('Error approving transfer:', error);
    }
};

const rejectTransfer = async () => {
    if (!transfer.value) {
        return;
    }
    const ok = confirm('Apakah Anda yakin ingin menolak mutasi ini?');
    if (!ok) {
        return;
    }
    try {
        const response = await axios.post(`/api/transfers/${transfer.value.id}/reject`, {
            reason: null,
        });
        transfer.value = response.data.data;
        await refreshDetail();
    } catch (error) {
        console.error('Error rejecting transfer:', error);
    }
};

onMounted(async () => {
    try {
        const response = await axios.get(`/api/transfers/${props.transferId}`);
        transfer.value = response.data.data;
    } catch (error) {
        console.error('Error fetching transfer detail:', error);
    } finally {
        loading.value = false;
    }
});
</script>
