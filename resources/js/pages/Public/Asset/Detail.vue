<template>
    <ThemeProvider>
        <div class="min-h-screen bg-gray-50/50 p-4 md:p-6 lg:p-8 dark:bg-gray-900/50">
            <div class="mx-auto transition-all duration-300 ease-in-out" :class="[user ? 'max-w-7xl' : 'max-w-3xl']">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Detail Aset</h1>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Informasi detail mengenai aset ini.
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

                <div class="mt-4" :class="[user ? 'grid grid-cols-1 gap-4 xl:grid-cols-3 xl:gap-6' : 'space-y-6']">
                    <div class="space-y-4 order-last xl:order-first" :class="{ 'xl:col-span-2': user }">
                        <DetailGrid cardTitle="Detail Aset" :items="detailItems" :columns="2" />

                        <DetailGrid cardTitle="Informasi Tambahan Aset" :items="additionalInfoItems" :columns="2" />

                        <ComponentCard title="Riwayat Aset">
                            <BasicTable :columns="assetHistoryColumns" :rows="assetHistoryRows" rowKey="id"
                                maxHeight="400px">
                                <template #cell-action_label="{ value }">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium"
                                        :class="assetHistoryStatusMap[String(value)] || 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'">
                                        {{ value }}
                                    </span>
                                </template>
                                <template #cell-actions="{ row }">
                                    <div class="flex items-center justify-center">
                                        <button @click="openHistoryDetail(row)"
                                            class="text-gray-500 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-500"
                                            title="Detail Riwayat">
                                            <EyeIcon class="h-4.5 w-4.5" />
                                        </button>
                                    </div>
                                </template>
                            </BasicTable>
                        </ComponentCard>
                    </div>

                    <div class="xl:col-span-1 order-first xl:order-last" v-if="user">
                        <CardList title="Aksi Aset">
                            <li class="pb-3">
                                <button @click="openConditionDrawer"
                                    class="flex w-full items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                                    <PencilIcon class="h-4 w-4" />
                                    Ubah Kondisi
                                </button>
                            </li>
                            <li class="pt-3 pb-3">
                                <button @click="openStatusDrawer" :disabled="asset.condition === 'lost'"
                                    class="flex w-full items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-500/20 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                                    <PencilIcon class="h-4 w-4" />
                                    Ubah Status
                                </button>
                            </li>
                            <li class="pt-3">
                                <Link :href="`/asset-management/assets/${asset.id}`"
                                    class="flex w-full items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                                    <EyeIcon class="h-4 w-4" />
                                    Lihat di Dashboard
                                </Link>
                            </li>
                        </CardList>
                    </div>
                </div>
            </div>

            <Modal v-if="isHistoryModalOpen" :fullScreenBackdrop="true" @close="closeHistoryDetail">
                <template #body>
                    <div
                        class="relative no-scrollbar w-full max-w-[720px] overflow-y-auto rounded-3xl bg-white p-4 lg:p-8 dark:bg-gray-900">
                        <button @click="closeHistoryDetail"
                            class="transition-color absolute right-5 top-5 z-999 flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 text-gray-400 hover:bg-gray-200 hover:text-gray-600 dark:bg-gray-700 dark:text-gray-400 dark:hover:bg-white/[0.07] dark:hover:text-gray-300">
                            <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M6.04289 16.5418C5.65237 16.9323 5.65237 17.5655 6.04289 17.956C6.43342 18.3465 7.06658 18.3465 7.45711 17.956L11.9987 13.4144L16.5408 17.9565C16.9313 18.347 17.5645 18.347 17.955 17.9565C18.3455 17.566 18.3455 16.9328 17.955 16.5423L13.4129 12.0002L17.955 7.45808C18.3455 7.06756 18.3455 6.43439 17.955 6.04387C17.5645 5.65335 16.9313 5.65335 16.5408 6.04387L11.9987 10.586L7.45711 6.04439C7.06658 5.65386 6.43342 5.65386 6.04289 6.04439C5.65237 6.43491 5.65237 7.06808 6.04289 7.4586L10.5845 12.0002L6.04289 16.5418Z" />
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
                                        Aksi
                                    </p>
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium"
                                        :class="assetHistoryStatusMap[selectedHistory.action_label] || 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'">
                                        {{ selectedHistory.action_label }}
                                    </span>
                                </div>
                                <div class="flex flex-col gap-3">
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
                                        class="max-h-72 overflow-auto rounded-2xl border border-gray-100 bg-gray-50 p-3 text-xs text-gray-700 dark:border-gray-800 dark:bg-gray-900/60 dark:text-gray-300">{{ formatPayload(selectedHistory?.data_before) }}</pre>
                                </div>
                                <div>
                                    <p
                                        class="mb-2 text-xs font-medium capitalize tracking-wide text-gray-500 dark:text-gray-400">
                                        Data Sesudah
                                    </p>
                                    <pre
                                        class="max-h-72 overflow-auto rounded-2xl border border-gray-100 bg-gray-50 p-3 text-xs text-gray-700 dark:border-gray-800 dark:bg-gray-900/60 dark:text-gray-300">{{ formatPayload(selectedHistory?.data_after) }}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </Modal>

            <Drawer :isOpen="isStatusDrawerOpen" title="Ubah Status Aset" @close="isStatusDrawerOpen = false">
                <div class="space-y-4">
                    <div v-for="option in statusOptions" :key="option.value" @click="selectedStatus = option.value"
                        class="flex cursor-pointer items-center justify-between rounded-lg border p-3 transition-all"
                        :class="[selectedStatus === option.value ? 'border-brand-500 bg-brand-50 ring-1 ring-brand-500 dark:bg-brand-500/10' : 'border-gray-200 hover:border-brand-200 dark:border-gray-700 dark:hover:border-gray-600']">
                        <div class="flex items-center gap-3">
                            <div class="flex h-5 w-5 items-center justify-center rounded-full border transition-colors"
                                :class="[selectedStatus === option.value ? 'border-brand-500 bg-brand-500' : 'border-gray-300 bg-transparent dark:border-gray-600']">
                                <div v-if="selectedStatus === option.value" class="h-2 w-2 rounded-full bg-white"></div>
                            </div>
                            <span
                                class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
                                :class="option.class">
                                {{ option.label }}
                            </span>
                        </div>
                    </div>
                </div>
                <template #footer>
                    <div class="flex w-full gap-3">
                        <button @click="isStatusDrawerOpen = false"
                            class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                            Batal
                        </button>
                        <button @click="updateStatus" :disabled="isSubmitting"
                            class="flex-1 rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/20 disabled:cursor-not-allowed disabled:opacity-50">
                            <span v-if="isSubmitting">Menyimpan...</span>
                            <span v-else>Simpan Perubahan</span>
                        </button>
                    </div>
                </template>
            </Drawer>

            <Drawer :isOpen="isConditionDrawerOpen" title="Ubah Kondisi Aset" @close="isConditionDrawerOpen = false">
                <div class="space-y-4">
                    <div v-for="option in conditionOptions" :key="option.value"
                        @click="selectedCondition = option.value"
                        class="flex cursor-pointer items-center justify-between rounded-lg border p-3 transition-all"
                        :class="[selectedCondition === option.value ? 'border-brand-500 bg-brand-50 ring-1 ring-brand-500 dark:bg-brand-500/10' : 'border-gray-200 hover:border-brand-200 dark:border-gray-700 dark:hover:border-gray-600']">
                        <div class="flex items-center gap-3">
                            <div class="flex h-5 w-5 items-center justify-center rounded-full border transition-colors"
                                :class="[selectedCondition === option.value ? 'border-brand-500 bg-brand-500' : 'border-gray-300 bg-transparent dark:border-gray-600']">
                                <div v-if="selectedCondition === option.value" class="h-2 w-2 rounded-full bg-white">
                                </div>
                            </div>
                            <span
                                class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
                                :class="option.class">
                                {{ option.label }}
                            </span>
                        </div>
                    </div>
                </div>
                <template #footer>
                    <div class="flex w-full gap-3">
                        <button @click="isConditionDrawerOpen = false"
                            class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                            Batal
                        </button>
                        <button @click="updateCondition" :disabled="isSubmitting"
                            class="flex-1 rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/20 disabled:cursor-not-allowed disabled:opacity-50">
                            <span v-if="isSubmitting">Menyimpan...</span>
                            <span v-else>Simpan Perubahan</span>
                        </button>
                    </div>
                </template>
            </Drawer>
        </div>
    </ThemeProvider>
</template>

<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref } from 'vue';
import CardList from '@/components/common/CardList.vue';
import ComponentCard from '@/components/common/ComponentCard.vue';
import DetailGrid from '@/components/common/DetailGrid.vue';
import ThemeProvider from '@/components/layout/ThemeProvider.vue';
import BasicTable from '@/components/tables/basic-tables/BasicTable.vue';
import Drawer from '@/components/ui/Drawer.vue';
import Modal from '@/components/ui/Modal.vue';
import { useToast } from '@/composables/useToast';
import { EyeIcon, PencilIcon } from '@/icons';

const props = defineProps<{
    asset: any;
    additionalInfo: any;
    history: any[];
}>();

const page = usePage();
const user = computed(() => page.props.auth.user);
const { addToast } = useToast();

const asset = ref<any>(props.asset);
const additionalInfo = ref<any>(props.additionalInfo);

// Initialize history from props
const assetHistoryItems = ref<any[]>(props.history || []);

// Drawer State
const isStatusDrawerOpen = ref(false);
const isConditionDrawerOpen = ref(false);
const selectedStatus = ref(asset.value.status);
const selectedCondition = ref(asset.value.condition);
const isSubmitting = ref(false);

// Display Logic (Status, Condition, Formatters)
const statusOptions = [
    { value: 'active', label: 'Aktif', class: 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500' },
    { value: 'inactive', label: 'Tidak Aktif', class: 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-500' },
    { value: 'under_maintenance', label: 'Dalam Perawatan', class: 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-500' },
    { value: 'disposed', label: 'Dibuang', class: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' },
];

const conditionOptions = [
    { value: 'good', label: 'Baik', class: 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500' },
    { value: 'minor_damage', label: 'Rusak Ringan', class: 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-500' },
    { value: 'major_damage', label: 'Rusak Berat', class: 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-500' },
    { value: 'lost', label: 'Hilang', class: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' },
];

const openStatusDrawer = () => {
    selectedStatus.value = asset.value.status;
    isStatusDrawerOpen.value = true;
};

const openConditionDrawer = () => {
    selectedCondition.value = asset.value.condition;
    isConditionDrawerOpen.value = true;
};

const updateStatus = async () => {
    if (!selectedStatus.value) return;

    isSubmitting.value = true;
    try {
        const response = await axios.put(`/api/assets/${asset.value.id}/status`, {
            status: selectedStatus.value
        });

        asset.value.status = selectedStatus.value;

        // Update history if returned in response or just rely on local update
        // If the API returns the updated asset, we can use that
        if (response.data?.data) {
            // Optionally update other fields if changed
        }

        addToast('Status aset berhasil diperbarui', 'success');
        isStatusDrawerOpen.value = false;

        // Refresh page to get latest history
        window.location.reload();
    } catch (error: any) {
        console.error('Error updating status:', error);
        addToast(error.response?.data?.message || 'Gagal memperbarui status aset', 'error');
    } finally {
        isSubmitting.value = false;
    }
};

const updateCondition = async () => {
    if (!selectedCondition.value) return;

    isSubmitting.value = true;
    try {
        await axios.put(`/api/assets/${asset.value.id}/condition`, {
            condition: selectedCondition.value
        });

        asset.value.condition = selectedCondition.value;

        addToast('Kondisi aset berhasil diperbarui', 'success');
        isConditionDrawerOpen.value = false;

        // Refresh page to get latest history
        window.location.reload();
    } catch (error: any) {
        console.error('Error updating condition:', error);
        addToast(error.response?.data?.message || 'Gagal memperbarui kondisi aset', 'error');
    } finally {
        isSubmitting.value = false;
    }
};

const formatCurrencyId = (value: unknown): string => {
    if (value === null || value === undefined || value === '') return '-';
    const numberValue = typeof value === 'number' ? value : Number(value);
    if (Number.isNaN(numberValue)) return String(value);
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(numberValue).replace('Rp', 'Rp ');
};

const formatDateId = (value: unknown): string => {
    if (!value) return '-';
    const date = new Date(String(value));
    if (Number.isNaN(date.getTime())) return String(value);
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit', month: 'long', year: 'numeric',
    }).format(date);
};

const detailItems = computed<any[]>(() => {
    if (!asset.value) return [];
    const statusOpt = statusOptions.find((opt) => opt.value === asset.value.status);
    const conditionOpt = conditionOptions.find((opt) => opt.value === asset.value.condition);

    const items: any[] = [
        {
            key: 'header', type: 'header',
            title: asset.value.name,
            subtitle: `Kode: ${asset.value.asset_code}`,
            image: asset.value.photo,
        },
        { key: 'category', label: 'Kategori', value: asset.value.category?.name },
        { key: 'opd', label: 'OPD', value: asset.value.opd?.name },
        { key: 'room', label: 'Ruangan', value: asset.value.room?.name },
        {
            key: 'status', label: 'Status',
            value: statusOpt?.label ?? asset.value.status,
            type: 'badge', badgeClass: statusOpt?.class,
        },
        {
            key: 'condition', label: 'Kondisi',
            value: conditionOpt?.label ?? asset.value.condition,
            type: 'badge', badgeClass: conditionOpt?.class,
        },
        { key: 'purchase_date', label: 'Tanggal Perolehan', value: formatDateId(asset.value.purchase_date) },
        { key: 'purchase_price', label: 'Harga Perolehan', value: formatCurrencyId(asset.value.purchase_price) },
        { key: 'funding_source', label: 'Sumber Dana', value: asset.value.funding_source?.name },
    ];

    if (asset.value.creator) {
        items.push({ key: 'created_by', label: 'Dibuat Oleh', value: asset.value.creator, type: 'user' });
    }
    if (asset.value.notes) {
        items.push({ key: 'notes', type: 'note', label: 'Catatan', value: asset.value.notes });
    }
    return items;
});

const additionalInfoItems = computed<any[]>(() => {
    if (!additionalInfo.value) return [];
    const items: any[] = [
        { key: 'manufacturer', label: 'Merk', value: additionalInfo.value.manufacturer },
        { key: 'model', label: 'Model', value: additionalInfo.value.model },
        { key: 'serial_number', label: 'Nomor Seri', value: additionalInfo.value.serial_number },
    ];
    if (additionalInfo.value.extra_notes) {
        items.push({ key: 'extra_notes', type: 'note', label: 'Catatan Tambahan', value: additionalInfo.value.extra_notes });
    }
    return items;
});

// History Logic
const assetHistoryActionOptions = [
    { value: 'created', label: 'Dibuat', class: 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500' },
    { value: 'updated', label: 'Diperbarui', class: 'bg-info-50 text-info-700 dark:bg-info-500/15 dark:text-info-500' },
    { value: 'status_updated', label: 'Status Berubah', class: 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-500' },
    { value: 'maintenance_scheduled', label: 'Jadwal Pemeliharaan', class: 'bg-purple-50 text-purple-700 dark:bg-purple-500/15 dark:text-purple-500' },
    { value: 'maintenance_completed', label: 'Pemeliharaan Selesai', class: 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500' },
    { value: 'transfer_requested', label: 'Mutasi Diajukan', class: 'bg-orange-50 text-orange-700 dark:bg-orange-500/15 dark:text-orange-500' },
    { value: 'transfer_approved', label: 'Mutasi Disetujui', class: 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500' },
    { value: 'transfer_rejected', label: 'Mutasi Ditolak', class: 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-500' },
    { value: 'disposed', label: 'Penghapusan', class: 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-500' },
];

const assetHistoryColumns = [
    { key: 'performed_at', header: 'Waktu', widthClass: 'w-3/12 px-5 py-3 text-left sm:px-6' },
    { key: 'action_label', header: 'Aksi', widthClass: 'w-3/12 px-5 py-3 text-left sm:px-6' },
    { key: 'performed_by_name', header: 'Dilakukan Oleh', widthClass: 'w-5/12 px-5 py-3 text-left sm:px-6' },
    { key: 'actions', header: 'Aksi', widthClass: 'w-1/12 px-5 py-3 text-center sm:px-6' },
];

const assetHistoryRows = computed(() => {
    return assetHistoryItems.value.map((history: any) => {
        const actionOption = assetHistoryActionOptions.find((item) => item.value === history.action);
        return {
            id: history.id,
            action_label: actionOption ? actionOption.label : history.action,
            performed_by_name: history.performed_by ? history.performed_by.name : (history.performed_by_name ?? '-'),
            performed_at: formatDateId(history.created_at) + ' ' + new Date(history.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
            ...history,
        };
    });
});

const assetHistoryStatusMap = computed<Record<string, string>>(() => {
    const map: Record<string, string> = {};
    assetHistoryActionOptions.forEach((option) => {
        map[option.label] = option.class;
    });
    return map;
});

const isHistoryModalOpen = ref(false);
const selectedHistory = ref<any | null>(null);

const openHistoryDetail = (row: any) => {
    selectedHistory.value = row;
    isHistoryModalOpen.value = true;
};

const closeHistoryDetail = () => {
    isHistoryModalOpen.value = false;
    selectedHistory.value = null;
};

const formatPayload = (data: any) => {
    if (!data) return '-';
    try {
        return JSON.stringify(data, null, 2);
    } catch {
        return data;
    }
};

// Check for user login to enable/disable specific interactions
// Already done via `user` computed property

</script>
