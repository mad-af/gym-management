<template>
    <div>
        <OperationActionButton
            :icon="BanknoteIcon"
            title="Pembatalan Sales"
            description="Batalkan transaksi penjualan."
            iconBgClass="bg-red-50 text-red-600 dark:bg-red-500/10"
            glowClass="bg-red-400"
            @click="openModal"
        />

        <Teleport to="body">
            <Modal
                v-if="isModalOpen"
                :fullScreenBackdrop="true"
                @close="closeModal"
            >
                <template #body>
                    <div
                        class="relative mx-auto w-full max-w-[900px] rounded-3xl bg-white p-6 lg:p-10 dark:bg-gray-900"
                    >
                        <button
                            @click="closeModal"
                            class="absolute top-3 right-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-100 text-gray-400 transition-colors hover:bg-gray-200 hover:text-gray-700 sm:top-6 sm:right-6 sm:h-11 sm:w-11 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200"
                            type="button"
                        >
                            <svg
                                class="h-6 w-6"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>

                        <h4
                            class="mb-2 text-title-sm font-semibold text-gray-800 dark:text-white/90"
                        >
                            Pembatalan Sales
                        </h4>
                        <p
                            class="mb-5 text-sm text-gray-500 dark:text-gray-400"
                        >
                            Menampilkan data transaksi 24 jam terakhir.
                        </p>

                        <DynamicTable
                            :columns="columns"
                            :data="tableData"
                            :items-per-page="10"
                            :total-items="totalItems"
                            :current-page="currentPage"
                            :is-server-side="true"
                            @update:page="handlePageChange"
                        >
                            <template #cell-total_amount="{ row }">
                                <span
                                    class="text-theme-sm font-medium text-gray-800 dark:text-white/90"
                                >
                                    {{ formatCurrencyId(row.total_amount) }}
                                </span>
                            </template>
                            <template #cell-created_at="{ row }">
                                <span
                                    class="text-theme-sm text-gray-700 dark:text-gray-400"
                                >
                                    {{ formatDateTimeId(row.created_at) }}
                                </span>
                            </template>
                            <template #cell-actions="{ row }">
                                <Button
                                    size="sm"
                                    variant="outline"
                                    className="!text-error-500 !border-error-500 hover:!bg-error-50"
                                    :onClick="() => openCancelDialog(row)"
                                >
                                    Batalkan Transaksi
                                </Button>
                            </template>
                        </DynamicTable>
                    </div>
                </template>
            </Modal>

            <Modal
                v-if="isCancelDialogOpen"
                :fullScreenBackdrop="true"
                @close="closeCancelDialog"
            >
                <template #body>
                    <div
                        class="relative mx-auto w-full max-w-[500px] rounded-3xl bg-white p-6 dark:bg-gray-900"
                    >
                        <button
                            @click="closeCancelDialog"
                            class="absolute top-3 right-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-100 text-gray-400 transition-colors hover:bg-gray-200 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200"
                            type="button"
                        >
                            <svg
                                class="h-6 w-6"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>

                        <h4
                            class="mb-7 text-title-sm font-semibold text-gray-800 dark:text-white/90"
                        >
                            Konfirmasi Pembatalan
                        </h4>

                        <div class="space-y-4">
                            <div
                                class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800"
                            >
                                <p
                                    class="text-sm text-gray-600 dark:text-gray-400"
                                >
                                    Sale #{{ selectedItem?.id?.slice(0, 8) }}...
                                </p>
                                <p
                                    class="mt-1 font-medium text-gray-800 dark:text-white/90"
                                >
                                    {{ selectedItem?.customer_name }}
                                </p>
                                <p
                                    class="text-sm text-gray-600 dark:text-gray-400"
                                >
                                    Total:
                                    {{
                                        formatCurrencyId(
                                            selectedItem?.total_amount,
                                        )
                                    }}
                                </p>
                            </div>

                            <div
                                class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-500/10"
                            >
                                <p
                                    class="text-sm font-medium text-yellow-700 dark:text-yellow-400"
                                >
                                    Ketik phrase berikut untuk konfirmasi:
                                </p>
                                <p
                                    class="font-mono mt-2 text-lg font-bold text-yellow-800 dark:text-yellow-300"
                                >
                                    Saya ingin menghapus transaksi ini
                                </p>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200"
                                >
                                    Ketik phrase konfirmasi
                                    <span class="text-error-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    v-model="confirmInput"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                                    placeholder="Ketik phrase di atas..."
                                />
                                <p
                                    v-if="cancelErrors.confirmInput"
                                    class="mt-1 text-sm text-error-500"
                                >
                                    {{ cancelErrors.confirmInput }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200"
                                >
                                    Alasan Pembatalan
                                    <span class="text-error-500">*</span>
                                </label>
                                <textarea
                                    v-model="cancellationReason"
                                    rows="3"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                                    placeholder="Masukkan alasan pembatalan..."
                                ></textarea>
                                <p
                                    v-if="cancelErrors.reason"
                                    class="mt-1 text-sm text-error-500"
                                >
                                    {{ cancelErrors.reason }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="mt-8 flex w-full items-center justify-end gap-3"
                        >
                            <Button
                                size="sm"
                                variant="outline"
                                :onClick="closeCancelDialog"
                            >
                                Batal
                            </Button>
                            <Button
                                size="sm"
                                variant="primary"
                                className="!bg-error-500 hover:!bg-error-600"
                                :onClick="submitCancellation"
                                :disabled="
                                    confirmInput !==
                                        'Saya ingin menghapus transaksi ini' ||
                                    !cancellationReason.trim() ||
                                    cancelProcessing
                                "
                            >
                                {{
                                    cancelProcessing
                                        ? 'Membatalkan...'
                                        : 'Batalkan'
                                }}
                            </Button>
                        </div>
                    </div>
                </template>
            </Modal>
        </Teleport>
    </div>
</template>

<script setup lang="ts">
import axios from 'axios';
import { ref, computed } from 'vue';
import DynamicTable from '@/components/tables/data-tables/DynamicTable.vue';
import type { Column } from '@/components/tables/data-tables/DynamicTable.vue';
import Button from '@/components/ui/Button.vue';
import Modal from '@/components/ui/Modal.vue';
import OperationActionButton from '@/components/ui/OperationActionButton.vue';
import { BanknoteIcon } from '@/icons';

const emit = defineEmits<{ (e: 'submitted'): void }>();

const isModalOpen = ref(false);
const isCancelDialogOpen = ref(false);
const items = ref<any[]>([]);
const totalItems = ref(0);
const currentPage = ref(1);
const selectedItem = ref<any | null>(null);

const cancellationReason = ref('');
const confirmInput = ref('');
const cancelProcessing = ref(false);
const cancelErrors = ref<{ reason?: string; confirmInput?: string }>({});

const columns: Column[] = [
    {
        key: 'created_at',
        label: 'Tanggal',
        type: 'custom',
        class: 'min-w-[160px]',
    },
    { key: 'customer_name', label: 'Pelanggan', class: 'min-w-[180px]' },
    {
        key: 'total_amount',
        label: 'Total',
        type: 'custom',
        class: 'min-w-[140px]',
    },
    {
        key: 'actions',
        label: 'Aksi',
        type: 'custom',
        class: 'min-w-[100px]',
        sortable: false,
    },
];

const tableData = computed(() =>
    items.value.map((s: any) => ({
        id: s.id,
        customer_name: s.customer?.name || '-',
        total_amount: s.total_amount,
        created_at: s.created_at,
    })),
);

const formatCurrencyId = (value: unknown): string => {
    if (value === null || value === undefined || value === '') {
        return 'Rp 0';
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

const formatDateTimeId = (value: unknown): string => {
    if (!value) return '-';
    const d = value instanceof Date ? value : new Date(String(value));
    if (Number.isNaN(d.getTime())) return String(value);
    return new Intl.DateTimeFormat('id-ID', {
        year: 'numeric',
        month: 'short',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    }).format(d);
};

const fetchItems = async (page = 1) => {
    try {
        const { data } = await axios.get('/api/sales', {
            params: {
                per_page: 10,
                page,
                last_24_hours: true,
            },
        });
        items.value = data.data?.data || [];
        totalItems.value = data.data?.total || 0;
    } catch (e) {
        console.error('Error fetching sales', e);
    }
};

const openModal = () => {
    isModalOpen.value = true;
    currentPage.value = 1;
    fetchItems(1);
};

const closeModal = () => {
    isModalOpen.value = false;
};

const handlePageChange = (page: number) => {
    currentPage.value = page;
    fetchItems(page);
};

const openCancelDialog = (row: any) => {
    selectedItem.value = row;
    cancellationReason.value = '';
    confirmInput.value = '';
    cancelErrors.value = {};
    isCancelDialogOpen.value = true;
};

const closeCancelDialog = () => {
    isCancelDialogOpen.value = false;
    selectedItem.value = null;
};

const submitCancellation = async () => {
    if (
        confirmInput.value !== 'Saya ingin menghapus transaksi ini' ||
        !cancellationReason.value.trim()
    ) {
        return;
    }

    if (confirmInput.value !== 'Saya ingin menghapus transaksi ini') {
        cancelErrors.value = {
            confirmInput: 'Phrase konfirmasi tidak sesuai.',
        };
        return;
    }

    if (cancellationReason.value.trim().length < 3) {
        cancelErrors.value = { reason: 'Alasan minimal 3 karakter.' };
        return;
    }

    cancelProcessing.value = true;
    cancelErrors.value = {};

    try {
        await axios.post(`/api/sales/${selectedItem.value.id}/cancel`, {
            cancellation_reason: cancellationReason.value.trim(),
        });

        closeCancelDialog();
        fetchItems(currentPage.value);
        emit('submitted');
    } catch (e: any) {
        console.error('Error cancelling transaction', e);
        if (e.response?.status === 422 && e.response.data?.errors) {
            const ers = e.response.data.errors;
            cancelErrors.value = {
                reason: Array.isArray(ers.cancellation_reason)
                    ? ers.cancellation_reason[0]
                    : String(ers.cancellation_reason || 'Validation error'),
            };
        } else {
            alert('Gagal membatalkan transaksi.');
        }
    } finally {
        cancelProcessing.value = false;
    }
};
</script>
