<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="currentPageTitle" />

        <div class="mt-4">
            <div v-if="loading"
                class="flex h-40 items-center justify-center rounded-2xl border border-gray-200 bg-white px-5 py-6 xl:px-8 xl:py-8 dark:border-gray-800 dark:bg-white/3">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Memuat detail usulan aset...
                </p>
            </div>

            <div v-else-if="proposal" class="grid grid-cols-1 gap-4 xl:grid-cols-3 xl:gap-6">
                <div class="space-y-4 xl:col-span-2">
                    <DetailGrid :cardTitle="cardTitle" :cardDesc="cardDesc" :items="detailItems" :columns="2" />
                </div>

                <div class="xl:col-span-1">
                    <CardList v-if="hasActions" title="Aksi Usulan">
                        <li v-if="canCancel" v-can="ASSET_PROPOSAL_PERMISSIONS.DELETE" class="py-2">
                            <Button size="sm" variant="outline"
                                class="w-full border-error-500 text-error-600 hover:bg-error-50 dark:border-error-500 dark:text-error-400 dark:hover:bg-error-500/10"
                                :startIcon="CloseIcon" :onClick="cancelProposal">
                                Batalkan Usulan
                            </Button>
                        </li>
                        <li v-if="canApprove" v-can="ASSET_PROPOSAL_PERMISSIONS.EDIT" class="py-2">
                            <Button size="sm" variant="primary" class="w-full" :startIcon="CheckCircleIcon"
                                :onClick="approveProposal">
                                Setujui Usulan
                            </Button>
                        </li>
                        <li v-if="canReject" v-can="ASSET_PROPOSAL_PERMISSIONS.EDIT" class="py-2">
                            <Button size="sm" variant="outline"
                                class="w-full border-error-500 text-error-600 hover:bg-error-50 dark:border-error-500 dark:text-error-400 dark:hover:bg-error-500/10"
                                :startIcon="CloseIcon" :onClick="rejectProposal">
                                Tolak Usulan
                            </Button>
                        </li>
                        <li v-if="canComplete" v-can="ASSET_PROPOSAL_PERMISSIONS.EDIT" class="py-2">
                            <Button size="sm" variant="primary" class="w-full" :startIcon="SuccessIcon"
                                :onClick="completeProposal">
                                Selesaikan &amp; Buat Aset
                            </Button>
                        </li>
                    </CardList>
                </div>
            </div>

            <div v-else
                class="flex h-40 items-center justify-center rounded-2xl border border-gray-200 bg-white px-5 py-6 xl:px-8 xl:py-8 dark:border-gray-800 dark:bg-white/3">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Data usulan aset tidak ditemukan.
                </p>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, ref } from 'vue';
import CardList from '@/components/common/CardList.vue';
import DetailGrid from '@/components/common/DetailGrid.vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import Button from '@/components/ui/Button.vue';
import { ASSET_PROPOSAL_PERMISSIONS } from '@/directives/permissions';
import { CheckCircleIcon, CloseIcon, SuccessIcon } from '@/icons';

const props = defineProps<{
    proposalId: string;
}>();

const currentPageTitle = ref('Detail Usulan Aset');
const proposal = ref<any | null>(null);
const loading = ref(false);

const page = usePage();
const currentUser = computed(() => (page.props as any).auth?.user ?? null);
const userPermissions = computed<string[]>(() => (page.props as any).auth?.permissions ?? []);

const formatDate = (date: string | null | undefined) => {
    if (!date) return '-';
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    }).format(new Date(date));
};

const formatCurrencyId = (value: unknown): string => {
    if (value === null || value === undefined || value === '') {
        return '-';
    }

    const numberValue = typeof value === 'number' ? value : Number(value);
    if (Number.isNaN(numberValue)) {
        return String(value);
    }

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    })
        .format(numberValue)
        .replace('Rp', 'Rp ');
};

const proposalStatus = computed(() => proposal.value?.status ?? null);

const hasEditPermission = computed(() =>
    userPermissions.value.includes(ASSET_PROPOSAL_PERMISSIONS.EDIT),
);

const hasDeletePermission = computed(() =>
    userPermissions.value.includes(ASSET_PROPOSAL_PERMISSIONS.DELETE),
);

const isProposer = computed(() => {
    if (!proposal.value || !currentUser.value) return false;
    const userId = (currentUser.value as any).id;
    const proposerId = proposal.value.proposed_by ?? proposal.value.proposer?.id ?? null;
    return !!userId && !!proposerId && userId === proposerId;
});

const canCancel = computed(() => {
    if (!proposal.value) return false;
    return proposalStatus.value === 'submitted' && isProposer.value && hasDeletePermission.value;
});

const canApprove = computed(() => {
    if (!proposal.value) return false;
    return proposalStatus.value === 'submitted' && hasEditPermission.value;
});

const canReject = computed(() => {
    if (!proposal.value) return false;
    return proposalStatus.value === 'submitted' && hasEditPermission.value;
});

const canComplete = computed(() => {
    if (!proposal.value) return false;
    return proposalStatus.value === 'approved' && hasEditPermission.value;
});

const hasActions = computed(() => canCancel.value || canApprove.value || canReject.value || canComplete.value);

const detailItems = computed<any[]>(() => {
    if (!proposal.value) {
        return [];
    }

    const items: any[] = [];

    items.push(
        {
            key: 'proposal_date',
            label: 'Tanggal Usulan',
            value: proposal.value.proposal_date ? formatDate(proposal.value.proposal_date) : '-',
        },
        {
            key: 'category',
            label: 'Kategori',
            value: proposal.value.category?.name ?? '-',
        },
        {
            key: 'opd',
            label: 'OPD',
            value: proposal.value.opd?.name ?? '-',
        },
        {
            key: 'proposer',
            type: 'user',
            label: 'Pengusul',
            value: proposal.value.proposer ?? null,
        },
        {
            key: 'qty',
            label: 'Jumlah',
            value: proposal.value.qty ?? '-',
        },
        {
            key: 'estimated_price',
            label: 'Perkiraan Harga Satuan',
            value: formatCurrencyId(proposal.value.estimated_price),
        },
        {
            key: 'total_estimation',
            label: 'Total Perkiraan',
            value: formatCurrencyId(proposal.value.total_estimation),
        },
    );

    if (proposal.value.specification) {
        items.push({
            key: 'specification',
            type: 'note',
            label: 'Spesifikasi / Detail Barang',
            value: proposal.value.specification,
        });
    }

    if (proposal.value.notes) {
        items.push({
            key: 'notes',
            type: 'note',
            label: 'Catatan Tambahan',
            value: proposal.value.notes,
        });
    }

    return items;
});

const cardTitle = computed(() => {
    if (!proposal.value) {
        return 'Detail Usulan Aset';
    }

    return proposal.value.item_name || 'Usulan Aset';
});

const cardDesc = computed(() => {
    if (!proposal.value || !proposal.value.proposal_number) {
        return '';
    }

    return `No. Usulan: ${proposal.value.proposal_number}`;
});

const updateProposalFromPayload = (payload: any) => {
    if (!payload) return;
    if (payload.item) {
        proposal.value = payload.item;
    } else {
        proposal.value = payload;
    }
};

const fetchDetail = async () => {
    loading.value = true;
    try {
        const response = await axios.get(`/api/asset-proposals/${props.proposalId}`);
        const payload = response.data.data;
        updateProposalFromPayload(payload);
    } catch (error) {
        console.error('Error fetching proposal detail:', error);
    } finally {
        loading.value = false;
    }
};

const approveProposal = async () => {
    if (!proposal.value) return;
    try {
        const response = await axios.post(`/api/asset-proposals/${proposal.value.id}/approve`);
        const payload = response.data.data;
        updateProposalFromPayload(payload);
    } catch (error) {
        console.error('Error approving proposal:', error);
    }
};

const rejectProposal = async () => {
    if (!proposal.value) return;
    try {
        const response = await axios.post(`/api/asset-proposals/${proposal.value.id}/reject`);
        const payload = response.data.data;
        updateProposalFromPayload(payload);
    } catch (error) {
        console.error('Error rejecting proposal:', error);
    }
};

const completeProposal = async () => {
    if (!proposal.value) return;
    try {
        const response = await axios.post(`/api/asset-proposals/${proposal.value.id}/complete`);
        const payload = response.data.data;
        updateProposalFromPayload(payload);
    } catch (error) {
        console.error('Error completing proposal:', error);
    }
};

const cancelProposal = async () => {
    if (!proposal.value) return;
    const ok = confirm('Apakah Anda yakin ingin membatalkan usulan ini?');
    if (!ok) return;
    try {
        await axios.delete(`/api/asset-proposals/${proposal.value.id}`);
        window.location.href = '/proposals';
    } catch (error) {
        console.error('Error cancelling proposal:', error);
    }
};

onMounted(() => {
    fetchDetail();
});
</script>
