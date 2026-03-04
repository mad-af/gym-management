<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="currentPageTitle" />

        <div class="mt-4">
            <div v-if="loading"
                class="flex h-40 items-center justify-center rounded-2xl border border-gray-200 bg-white px-5 py-6 xl:px-8 xl:py-8 dark:border-gray-800 dark:bg-white/3">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Memuat detail perawatan aset...
                </p>
            </div>

            <div v-else-if="maintenance" class="grid grid-cols-1 gap-4 xl:grid-cols-3 xl:gap-6">
                <div class="space-y-4 xl:col-span-2">
                    <DetailGrid :cardTitle="cardTitle" :items="detailItems" :columns="2" />

                    <ComponentCard v-if="logRows.length" title="Riwayat Perawatan">
                        <BasicTable :columns="logColumns" :rows="logRows" rowKey="id" maxHeight="320px" remote
                            :loading="logLoading" @load-more="onLogLoadMore">
                            <template #cell-notes="{ value }">
                                <p class="text-theme-sm text-gray-600 dark:text-gray-300 whitespace-pre-line">
                                    {{ value || '-' }}
                                </p>
                            </template>

                            <template #cell-performed_by_name="{ row, value }">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-8 w-8 items-center justify-center overflow-hidden rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                                        <AppImage v-if="row.performed_by && row.performed_by.avatar"
                                            :src="row.performed_by?.avatar?.url"
                                            :placeholder="typeof row.performed_by.avatar === 'object' ? row.performed_by.avatar.placeholder : ''"
                                            :alt="row.performed_by.name || value" containerClass="h-8 w-8 rounded-full"
                                            imgClass="h-8 w-8 rounded-full object-cover" />
                                        <span v-else class="text-xs font-semibold">
                                            {{ (value || '?').toString().charAt(0).toUpperCase() }}
                                        </span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate text-theme-sm font-medium text-gray-800 dark:text-white/90">
                                            {{ value }}
                                        </p>
                                    </div>
                                </div>
                            </template>
                        </BasicTable>
                    </ComponentCard>
                </div>

                <div class="xl:col-span-1">
                    <CardList v-if="canStart || canComplete || canCancel || canEdit"
                        v-can="ASSET_MAINTENANCE_PERMISSIONS.MANAGE" title="Aksi Perawatan">
                        <li v-if="canStart" class="py-2">
                            <Button v-can="ASSET_MAINTENANCE_PERMISSIONS.MANAGE" size="sm" variant="primary"
                                class="w-full" :startIcon="PlayIcon" :onClick="startMaintenance">
                                Mulai Perawatan
                            </Button>
                        </li>
                        <li v-if="canComplete" class="py-2">
                            <Button v-can="ASSET_MAINTENANCE_PERMISSIONS.MANAGE" size="sm" variant="primary"
                                class="w-full" :startIcon="CheckCircleIcon" :onClick="openCompleteDrawer">
                                Selesaikan Perawatan
                            </Button>
                        </li>
                        <li v-if="canEdit" class="py-2">
                            <MaintenanceForm v-can="ASSET_MAINTENANCE_PERMISSIONS.MANAGE" :maintenance="maintenance"
                                buttonText="Edit Perawatan" :startIcon="PencilIcon" buttonVariant="outline"
                                buttonSize="sm"
                                buttonClass="w-full border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                                @saved="fetchDetail" />
                        </li>
                        <li v-if="canCancel" class="py-2">
                            <Button v-can="ASSET_MAINTENANCE_PERMISSIONS.MANAGE" size="sm" variant="outline"
                                class="w-full border-error-500 text-error-600 hover:bg-error-50 dark:border-error-500 dark:text-error-400 dark:hover:bg-error-500/10"
                                :startIcon="CloseIcon" :onClick="cancelMaintenance">
                                Batalkan Perawatan
                            </Button>
                        </li>
                    </CardList>
                </div>
            </div>

            <div v-else
                class="flex h-40 items-center justify-center rounded-2xl border border-gray-200 bg-white px-5 py-6 xl:px-8 xl:py-8 dark:border-gray-800 dark:bg-white/3">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Data perawatan aset tidak ditemukan.
                </p>
            </div>
        </div>

        <Drawer :isOpen="isCompleteDrawerOpen" @close="closeCompleteDrawer" title="Selesaikan Perawatan Aset">
            <div class="space-y-6">
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Tanggal Selesai
                    </label>
                    <input v-model="completeForm.completed_date" type="date"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
                    <p v-if="completeForm.errors.completed_date" class="mt-1 text-sm text-error-500">
                        {{ completeForm.errors.completed_date }}
                    </p>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Catatan Log
                    </label>
                    <textarea v-model="completeForm.log_notes" rows="3"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Tambahkan catatan log untuk histori perawatan"></textarea>
                    <p v-if="completeForm.errors.log_notes" class="mt-1 text-sm text-error-500">
                        {{ completeForm.errors.log_notes }}
                    </p>
                </div>
            </div>

            <template #footer>
                <div class="flex w-full justify-end gap-3">
                    <Button variant="outline" :onClick="closeCompleteDrawer">
                        Batal
                    </Button>
                    <Button variant="primary" :loading="completeForm.processing" :onClick="saveComplete">
                        Simpan
                    </Button>
                </div>
            </template>
        </Drawer>
    </AdminLayout>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, ref } from 'vue';
import AppImage from '@/components/common/AppImage.vue';
import CardList from '@/components/common/CardList.vue';
import ComponentCard from '@/components/common/ComponentCard.vue';
import DetailGrid from '@/components/common/DetailGrid.vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import BasicTable from '@/components/tables/basic-tables/BasicTable.vue';
import Button from '@/components/ui/Button.vue';
import Drawer from '@/components/ui/Drawer.vue';
import { ASSET_MAINTENANCE_PERMISSIONS } from '@/directives/permissions';
import { CheckCircleIcon, CloseIcon, PlayIcon, PencilIcon } from '@/icons';
import MaintenanceForm from './components/MaintenanceForm.vue';

const props = defineProps<{
    maintenanceId: string | number;
}>();

const currentPageTitle = ref('Detail Perawatan Aset');

const loading = ref(false);
const maintenance = ref<any | null>(null);
const isCompleteDrawerOpen = ref(false);

const completeForm = useForm({
    completed_date: '',
    log_notes: '',
});

const statusOptions = ref<{ value: string; label: string; class?: string }[]>([]);
const conditionOptions = ref<{ value: string; label: string; class?: string }[]>([]);

const asset = computed(() => maintenance.value?.asset ?? null);

const logItems = ref<any[]>([]);
const logPage = ref(1);
const logHasMore = ref(true);
const logLoading = ref(false);

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

const formatDate = (date: string | null | undefined) => {
    if (!date) return '-';
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    }).format(new Date(date));
};

const findStatusOption = (status: string | null | undefined) => {
    if (!status) return null;
    return statusOptions.value.find((opt) => opt.value === status) ?? null;
};

const findConditionOption = (condition: string | null | undefined) => {
    if (!condition) return null;
    return conditionOptions.value.find((opt) => opt.value === condition) ?? null;
};

const statusLabel = computed(() => {
    if (!maintenance.value) return '-';
    const opt = findStatusOption(maintenance.value.status);
    if (opt) return opt.label;
    return maintenance.value.status || '-';
});

const statusBadgeClass = computed(() => {
    if (!maintenance.value) return '';
    const opt = findStatusOption(maintenance.value.status);
    return opt?.class ?? '';
});

const conditionBeforeLabel = computed(() => {
    if (!maintenance.value || !maintenance.value.condition_before) return '-';
    const opt = findConditionOption(maintenance.value.condition_before);
    if (opt) return opt.label;
    return maintenance.value.condition_before;
});

const conditionBeforeBadgeClass = computed(() => {
    if (!maintenance.value || !maintenance.value.condition_before) return '';
    const opt = findConditionOption(maintenance.value.condition_before);
    return opt?.class ?? '';
});

const conditionAfterLabel = computed(() => {
    if (!maintenance.value) return '-';
    if (maintenance.value.status !== 'completed') return '-';
    const raw = maintenance.value.condition_after ?? asset.value?.condition ?? null;
    if (!raw) return '-';
    const opt = findConditionOption(raw);
    if (opt) return opt.label;
    return raw;
});

const conditionAfterBadgeClass = computed(() => {
    if (!maintenance.value) return '';
    if (maintenance.value.status !== 'completed') return '';
    const raw = maintenance.value.condition_after ?? asset.value?.condition ?? null;
    if (!raw) return '';
    const opt = findConditionOption(raw);
    return opt?.class ?? '';
});

const detailItems = computed<any[]>(() => {
    if (!maintenance.value) {
        return [];
    }

    const items: any[] = [];

    items.push({
        key: 'header',
        type: 'header',
        title: asset.value?.name ?? 'Aset',
        subtitle: asset.value?.asset_code ? `Kode: ${asset.value.asset_code}` : '',
        image: asset.value?.photo ?? null,
    });

    items.push(
        {
            key: 'maintenance_type',
            label: 'Jenis Perawatan',
            value: maintenance.value.maintenance_type ?? '-',
        },
        {
            key: 'status',
            label: 'Status',
            type: 'badge',
            value: statusLabel.value,
            badgeClass: statusBadgeClass.value,
        },
        {
            key: 'condition_before',
            label: 'Kondisi Aset (Sebelum)',
            type: 'badge',
            value: conditionBeforeLabel.value,
            badgeClass: conditionBeforeBadgeClass.value,
        },
        {
            key: 'condition_after',
            label: 'Kondisi Aset (Sesudah)',
            type: 'badge',
            value: conditionAfterLabel.value,
            badgeClass: conditionAfterBadgeClass.value,
        },
        {
            key: 'scheduled_date',
            label: 'Tanggal Terjadwal',
            value: maintenance.value.scheduled_date ? formatDate(maintenance.value.scheduled_date) : '-',
        },
        {
            key: 'completed_date',
            label: 'Tanggal Selesai',
            value: maintenance.value.completed_date ? formatDate(maintenance.value.completed_date) : '-',
        },
        {
            key: 'cost',
            label: 'Biaya Perawatan',
            value: formatCurrencyId(maintenance.value.cost),
        },
        {
            key: 'vendor',
            label: 'Vendor',
            value: maintenance.value.vendor ?? '-',
        },
        {
            key: 'asset_opd',
            label: 'OPD',
            value: asset.value?.opd?.name ?? '-',
        },
        {
            key: 'asset_room',
            label: 'Ruangan',
            value: asset.value?.room?.name ?? '-',
        },
    );

    if (maintenance.value.description) {
        items.push({
            key: 'description',
            type: 'note',
            label: 'Deskripsi',
            value: maintenance.value.description,
        });
    }

    return items;
});

const cardTitle = computed(() => {
    if (!maintenance.value) {
        return 'Detail Perawatan Aset';
    }

    return maintenance.value.reference_number || 'Detail Perawatan Aset';
});

const canStart = computed(() => {
    if (!maintenance.value) return false;
    return maintenance.value.status === 'scheduled';
});

const canEdit = computed(() => {
    if (!maintenance.value) return false;
    return maintenance.value.status === 'scheduled';
});

const canComplete = computed(() => {
    if (!maintenance.value) return false;
    return maintenance.value.status === 'in_progress';
});

const canCancel = computed(() => {
    if (!maintenance.value) return false;
    const status = maintenance.value.status;
    return status === 'scheduled' || status === 'in_progress';
});

const startMaintenance = async () => {
    if (!maintenance.value) return;
    if (!confirm('Apakah Anda yakin ingin memulai perawatan ini?')) return;

    loading.value = true;
    try {
        await axios.post(`/api/asset-maintenances/${maintenance.value.id}/start`);
        await fetchDetail();
    } catch (error) {
        console.error('Error starting maintenance:', error);
        alert('Gagal memulai perawatan.');
    } finally {
        loading.value = false;
    }
};

const formatDateTime = (date: string | null | undefined) => {
    if (!date) return '-';
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(date));
};

const fetchDetail = async () => {
    loading.value = true;
    try {
        const response = await axios.get(`/api/asset-maintenances/${props.maintenanceId}`);
        const payload = response.data.data;
        if (payload && payload.item) {
            maintenance.value = payload.item;
            if (payload.status_options) {
                statusOptions.value = payload.status_options;
            }
            if (payload.condition_options) {
                conditionOptions.value = payload.condition_options;
            }
        } else {
            maintenance.value = payload;
        }

        await fetchLogs(true);
    } catch (error) {
        console.error('Error fetching maintenance detail:', error);
    } finally {
        loading.value = false;
    }
};

const openCompleteDrawer = () => {
    if (!maintenance.value) return;
    completeForm.reset();
    completeForm.clearErrors();
    completeForm.completed_date = '';
    completeForm.log_notes = '';
    isCompleteDrawerOpen.value = true;
};

const closeCompleteDrawer = () => {
    isCompleteDrawerOpen.value = false;
    completeForm.reset();
    completeForm.clearErrors();
};

const saveComplete = async () => {
    if (!maintenance.value) return;
    completeForm.processing = true;
    try {
        const response = await axios.post(
            `/api/asset-maintenances/${maintenance.value.id}/complete`,
            completeForm.data(),
        );
        maintenance.value = response.data.data;
        await fetchDetail();
        closeCompleteDrawer();
    } catch (error: any) {
        if (error.response && error.response.status === 422 && error.response.data.errors) {
            completeForm.errors = error.response.data.errors;
        } else {
            console.error('Error completing maintenance:', error);
        }
    } finally {
        completeForm.processing = false;
    }
};

const cancelMaintenance = async () => {
    if (!maintenance.value) return;
    const ok = confirm('Apakah Anda yakin ingin membatalkan perawatan ini?');
    if (!ok) return;
    try {
        const response = await axios.post(`/api/asset-maintenances/${maintenance.value.id}/cancel`, {
            reason: '',
        });
        maintenance.value = response.data.data;
        await fetchDetail();
    } catch (error) {
        console.error('Error cancelling maintenance:', error);
    }
};

const logRows = computed(() => {
    return logItems.value.map((log: any) => ({
        id: log.id,
        notes: log.notes ?? '',
        performed_by_name: log.performed_by?.name ?? log.performed_by_name ?? '-',
        performed_by: log.performed_by ?? null,
        created_at_label: formatDateTime(log.created_at),
    }));
});

const logColumns = [
    {
        key: 'created_at_label',
        header: 'Waktu',
        widthClass: 'px-5 py-3 text-left sm:px-6',
    },
    {
        key: 'notes',
        header: 'Catatan',
        widthClass: 'px-5 py-3 text-left sm:px-6',
    },
    {
        key: 'performed_by_name',
        header: 'Dilakukan Oleh',
        widthClass: 'w-3/12 px-5 py-3 text-left sm:px-6',
    },
];

const fetchLogs = async (reset = false) => {
    if (!maintenance.value) return;
    if (logLoading.value) return;

    logLoading.value = true;

    try {
        const page = reset ? 1 : logPage.value;
        const response = await axios.get(`/api/asset-maintenances/${maintenance.value.id}/logs`, {
            params: {
                page,
                per_page: 10,
            },
        });

        const payload = response.data.data;
        const paginator = payload.items;
        const data = paginator.data ?? [];

        if (reset) {
            logItems.value = data;
        } else {
            logItems.value = [...logItems.value, ...data];
        }

        logPage.value = paginator.current_page ?? page;
        const total = paginator.total ?? data.length;
        const perPage = paginator.per_page ?? 10;
        const loadedCount = logItems.value.length;

        logHasMore.value = loadedCount < total && data.length >= perPage;
    } catch (error) {
        console.error('Error fetching maintenance logs:', error);
    } finally {
        logLoading.value = false;
    }
};

const onLogLoadMore = () => {
    if (!logHasMore.value || logLoading.value) return;
    logPage.value += 1;
    fetchLogs();
};

onMounted(() => {
    fetchDetail();
});
</script>
