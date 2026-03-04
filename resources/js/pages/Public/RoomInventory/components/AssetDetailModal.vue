<template>
    <div class="w-full h-full flex justify-center items-center">
        <button type="button" @click="open"
            class="inline-flex items-center justify-center text-gray-500 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-500"
            title="Detail Aset">
            <EyeIcon class="h-4.5 w-4.5" />
        </button>

        <Modal v-if="isOpen" :fullScreenBackdrop="true" @close="close">
            <template #body>
                <div
                    class="relative no-scrollbar w-full max-w-[720px] overflow-y-auto rounded-3xl bg-white p-4 lg:p-8 dark:bg-gray-900 max-h-[90vh] overflow-y-auto">
                    <button type="button" @click="close"
                        class="transition-color absolute right-5 top-5 z-999 flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 text-gray-400 hover:bg-gray-200 hover:text-gray-600 dark:bg-gray-700 dark:text-gray-400 dark:hover:bg-white/[0.07] dark:hover:text-gray-300">
                        <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M6.04289 16.5418C5.65237 16.9323 5.65237 17.5655 6.04289 17.956C6.43342 18.3465 7.06658 18.3465 7.45711 17.956L11.9987 13.4144L16.5408 17.9565C16.9313 18.347 17.5645 18.347 17.955 17.9565C18.3455 17.566 18.3455 16.9328 17.955 16.5423L13.4129 12.0002L17.955 7.45808C18.3455 7.06756 18.3455 6.43439 17.955 6.04387C17.5645 5.65335 16.9313 5.65335 16.5408 6.04387L11.9987 10.586L7.45711 6.04439C7.06658 5.65386 6.43342 5.65386 6.04289 6.04439C5.65237 6.43491 5.65237 7.06808 6.04289 7.4586L10.5845 12.0002L6.04289 16.5418Z" />
                        </svg>
                    </button>

                    <div class="px-2 pr-10 lg:pr-14">
                        <h4 class="mb-1 text-lg font-semibold text-gray-800 lg:text-xl dark:text-white/90">
                            Detail Aset
                        </h4>
                        <p class="mb-4 text-xs text-gray-500 lg:mb-6 dark:text-gray-400">
                            Informasi lengkap aset yang dimutasi.
                        </p>
                    </div>

                    <div class="px-2 pb-2">
                        <div v-if="loading" class="flex h-32 items-center justify-center">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Memuat detail aset...
                            </p>
                        </div>
                        <div v-else class="space-y-4">
                            <DetailGrid cardTitle="Detail Aset" :items="detailItems" :columns="2" />
                            <DetailGrid cardTitle="Informasi Tambahan Aset" :items="additionalInfoItems" :columns="2" />
                        </div>
                    </div>
                </div>
            </template>
        </Modal>
    </div>
</template>

<script setup lang="ts">
import axios from 'axios';
import { computed, onMounted, ref, watch } from 'vue';
import DetailGrid from '@/components/common/DetailGrid.vue';
import Modal from '@/components/ui/Modal.vue';
import { EyeIcon } from '@/icons';

const props = defineProps<{
    assetId: string;
    statusOptions?: { value: string; label: string; class?: string }[];
    conditionOptions?: { value: string; label: string; class?: string }[];
}>();

const isOpen = ref(false);
const asset = ref<any | null>(null);
const additionalInfo = ref<any | null>(null);
const loading = ref(false);

const statusOptions = ref<{ value: string; label: string; class?: string }[]>(props.statusOptions ?? []);
const conditionOptions = ref<{ value: string; label: string; class?: string }[]>(props.conditionOptions ?? []);

watch(() => props.statusOptions, (newVal) => {
    if (newVal) statusOptions.value = newVal;
});

watch(() => props.conditionOptions, (newVal) => {
    if (newVal) conditionOptions.value = newVal;
});

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

const formatDateId = (value: unknown): string => {
    if (!value) {
        return '-';
    }

    const date = new Date(String(value));
    if (Number.isNaN(date.getTime())) {
        return String(value);
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    }).format(date);
};

const detailItems = computed<any[]>(() => {
    if (!asset.value) {
        return [];
    }

    const statusOpt = statusOptions.value.find((opt) => opt.value === asset.value.status);
    const conditionOpt = conditionOptions.value.find((opt) => opt.value === asset.value.condition);

    const items: any[] = [
        {
            key: 'header',
            type: 'header',
            title: asset.value.name,
            subtitle: `Kode: ${asset.value.asset_code}`,
            image: asset.value.photo,
        },
        { key: 'category', label: 'Kategori', value: asset.value.category?.name },
        { key: 'opd', label: 'OPD', value: asset.value.opd?.name },
        { key: 'room', label: 'Ruangan', value: asset.value.room?.name },
        {
            key: 'status',
            label: 'Status',
            value: statusOpt?.label ?? asset.value.status,
            type: 'badge',
            badgeClass: statusOpt?.class,
        },
        {
            key: 'condition',
            label: 'Kondisi',
            value: conditionOpt?.label ?? asset.value.condition,
            type: 'badge',
            badgeClass: conditionOpt?.class,
        },
        {
            key: 'purchase_date',
            label: 'Tanggal Perolehan',
            value: formatDateId(asset.value.purchase_date),
        },
        {
            key: 'purchase_price',
            label: 'Harga Perolehan',
            value: formatCurrencyId(asset.value.purchase_price),
        },
        {
            key: 'funding_source',
            label: 'Sumber Dana',
            value: asset.value.funding_source?.name,
        },
    ];

    if (asset.value.creator) {
        items.push({
            key: 'created_by',
            label: 'Dibuat Oleh',
            value: asset.value.creator,
            type: 'user',
        });
    }

    if (asset.value.notes) {
        items.push({
            key: 'notes',
            type: 'note',
            label: 'Catatan',
            value: asset.value.notes,
        });
    }

    return items;
});

const additionalInfoItems = computed<any[]>(() => {
    if (!additionalInfo.value) {
        return [];
    }

    const items: any[] = [
        {
            key: 'manufacturer',
            label: 'Merk',
            value: additionalInfo.value.manufacturer,
        },
        {
            key: 'model',
            label: 'Model',
            value: additionalInfo.value.model,
        },
        {
            key: 'serial_number',
            label: 'Nomor Seri',
            value: additionalInfo.value.serial_number,
        },
    ];

    if (additionalInfo.value.extra_notes) {
        items.push({
            key: 'extra_notes',
            type: 'note',
            label: 'Catatan Tambahan',
            value: additionalInfo.value.extra_notes,
        });
    }

    return items;
});

const fetchData = async () => {
    if (!props.assetId) {
        return;
    }

    loading.value = true;
    try {
        const response = await axios.get(`/api/public/assets/${props.assetId}`);

        asset.value = response.data.data.asset;
        additionalInfo.value = response.data.data.additional_info;
    } catch (error) {
        console.error('Error fetching asset detail for modal:', error);
    } finally {
        loading.value = false;
    }
};

const open = () => {
    if (!props.assetId) {
        return;
    }
    isOpen.value = true;
    fetchData();
};

const close = () => {
    isOpen.value = false;
};

watch(
    () => props.assetId,
    (newValue, oldValue) => {
        if (newValue && newValue !== oldValue && isOpen.value) {
            fetchData();
        }
    },
);

onMounted(() => {
    if (props.assetId && isOpen.value) {
        fetchData();
    }
});
</script>
