<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="currentPageTitle" />

        <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-3 xl:gap-6">
            <div class="space-y-4 xl:col-span-2">
                <DetailGrid cardTitle="Detail Aset" :items="detailItems" :columns="2">
                    <template #actions>
                        <AssetForm v-if="asset && asset.status !== 'disposed'" v-can="ASSET_PERMISSIONS.EDIT"
                            :assetId="props.assetId" :asset="asset" @saved="handleAssetUpdated" />
                    </template>
                </DetailGrid>

                <DetailGrid cardTitle="Informasi Tambahan Aset" :items="additionalInfoItems" :columns="2">
                    <template #actions>
                        <AssetAdditionalInfoForm v-if="asset && asset.status !== 'disposed'"
                            v-can="ASSET_PERMISSIONS.EDIT" :assetId="props.assetId" :info="additionalInfo"
                            @saved="handleAdditionalInfoUpdated" />
                    </template>
                </DetailGrid>

                <ComponentCard title="Riwayat Aset">
                    <BasicTable :columns="assetHistoryColumns" :rows="assetHistoryRows" rowKey="id" maxHeight="400px"
                        remote :loading="assetHistoryLoading" @load-more="onAssetHistoryLoadMore">
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

            <div class="xl:col-span-1" v-if="asset && asset.status !== 'disposed'">
                <CardList title="Aksi Aset">
                    <li v-can="ASSET_PERMISSIONS.EDIT" class="pb-3">
                        <UserSelectButton label="Pegawai Penanggung Jawab" :name="currentEmployeeName"
                            :subLabel="currentEmployeeNip" emptyNameLabel="Belum ada penanggung jawab"
                            emptySubLabel="Klik untuk memilih pegawai" :avatarSrc="currentEmployeeAvatarSrc"
                            :avatarPlaceholder="currentEmployeeAvatarPlaceholder" @click="openEmployeeDrawer" />
                    </li>
                    <li class="pt-3">
                        <Button v-if="asset && asset.status !== 'Disposed'" v-can="ASSET_PERMISSIONS.VIEW" size="sm"
                            variant="outline" class="w-full" :onClick="downloadLabel" :disabled="!asset"
                            :startIcon="PrinterIcon">
                            Print Label Aset
                        </Button>
                    </li>
                </CardList>
            </div>
        </div>

        <Drawer :isOpen="isEmployeeDrawerOpen" @close="isEmployeeDrawerOpen = false"
            title="Pilih Pegawai Penanggung Jawab">
            <div class="space-y-4">
                <div class="space-y-2">
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">
                        Pegawai
                    </label>
                    <Combobox v-model="draftEmployeeId" :options="orderedEmployeeOptions" labelKey="name" valueKey="id"
                        placeholder="Cari dan pilih pegawai..." :loading="employeeLoading || updatingEmployeeEmployee"
                        remote @search="onEmployeeSearch" @load-more="onEmployeeLoadMore" />
                </div>
            </div>

            <template #footer>
                <div class="flex w-full justify-end gap-3">
                    <Button size="sm" variant="outline" :onClick="() => (isEmployeeDrawerOpen = false)">
                        Batal
                    </Button>
                    <Button size="sm" :onClick="handleSaveEmployee" :disabled="updatingEmployeeEmployee">
                        {{ updatingEmployeeEmployee ? 'Menyimpan...' : 'Simpan' }}
                    </Button>
                </div>
            </template>
        </Drawer>

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
import Combobox from '@/components/ui/Combobox.vue';
import Drawer from '@/components/ui/Drawer.vue';
import Modal from '@/components/ui/Modal.vue';
import UserSelectButton from '@/components/ui/UserSelectButton.vue';
import { ASSET_PERMISSIONS } from '@/directives/permissions';
import { EyeIcon, PrinterIcon } from '@/icons';
import AssetAdditionalInfoForm from './components/AssetAdditionalInfoForm.vue';
import AssetForm from './components/AssetForm.vue';

const props = defineProps<{
    assetId: string;
}>();

const currentPageTitle = ref('Detail Aset');
const asset = ref<any | null>(null);
const additionalInfo = ref<any | null>(null);
const loading = ref(true);

const isEmployeeDrawerOpen = ref(false);

const selectedEmployeeId = ref<string | null>(null);
const draftEmployeeId = ref<string | null>(null);
const employeeOptions = ref<any[]>([]);
const employeeLoading = ref(false);
const employeeSearch = ref('');
const employeePage = ref(1);
const employeeHasMore = ref(true);
const updatingEmployeeEmployee = ref(false);

const currentEmployeeName = computed(() => {
    if (!asset.value || !asset.value.employee) {
        return '';
    }

    return asset.value.employee.name || '';
});

const currentEmployeeNip = computed(() => {
    if (!asset.value || !asset.value.employee) {
        return '';
    }

    return asset.value.employee.nip || '';
});

const currentEmployeeAvatarSrc = computed(() => {
    if (!asset.value || !asset.value.employee || !asset.value.employee.avatar) {
        return '';
    }

    const avatar = asset.value.employee.avatar;

    if (typeof avatar === 'object') {
        return avatar.url || '';
    }

    if (typeof avatar === 'string') {
        return avatar;
    }

    return '';
});

const currentEmployeeAvatarPlaceholder = computed(() => {
    if (!asset.value || !asset.value.employee || !asset.value.employee.avatar) {
        return '';
    }

    const avatar = asset.value.employee.avatar;

    if (typeof avatar === 'object') {
        return avatar.placeholder || '';
    }

    return '';
});

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

const assetHistoryLoading = ref(false);
const assetHistoryItems = ref<any[]>([]);
const assetHistoryActionOptions = ref<{ value: string; label: string; class?: string }[]>([]);
const assetHistoryPage = ref(1);
const assetHistoryHasMore = ref(true);
const assetHistoryPerPage = 8;

const assetHistoryColumns = [
    { key: 'performed_at', header: 'Waktu', widthClass: 'w-3/12 px-5 py-3 text-left sm:px-6' },
    { key: 'action_label', header: 'Aksi', widthClass: 'w-3/12 px-5 py-3 text-left sm:px-6' },
    { key: 'performed_by_name', header: 'Dilakukan Oleh', widthClass: 'w-5/12 px-5 py-3 text-left sm:px-6' },
    { key: 'actions', header: 'Aksi', widthClass: 'w-1/12 px-5 py-3 text-center sm:px-6' },
];

const assetHistoryRows = computed(() => {
    return assetHistoryItems.value.map((history: any) => {
        const actionOption = assetHistoryActionOptions.value.find((item) => item.value === history.action);

        return {
            id: history.id,
            action_label: actionOption ? actionOption.label : history.action,
            performed_by_name: history.performed_by ? history.performed_by.name : history.performed_by_name ?? '-',
            performed_at: history.created_at ?? '-',
            ...history,
        };
    });
});

const assetHistoryStatusMap = computed<Record<string, string>>(() => {
    const map: Record<string, string> = {};

    assetHistoryActionOptions.value.forEach((option) => {
        if (option.label && option.class) {
            map[option.label] = option.class;
        }
    });

    return map;
});

const isHistoryModalOpen = ref(false);
const selectedHistory = ref<any | null>(null);

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
        .replace('Rp', 'Rp '); // tambahkan spasi setelah simbol Rp
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

    const statusOpt = statusOptions.find((opt) => opt.value === asset.value.status);
    const conditionOpt = conditionOptions.find((opt) => opt.value === asset.value.condition);

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

const handleAssetUpdated = (updated: any) => {
    asset.value = updated ?? asset.value;
};

const handleAdditionalInfoUpdated = (updated: any) => {
    additionalInfo.value = updated ?? additionalInfo.value;
};

const fetchAssetHistory = async (reset = false) => {
    if (reset) {
        assetHistoryPage.value = 1;
        assetHistoryItems.value = [];
        assetHistoryHasMore.value = true;
    }

    if (!assetHistoryHasMore.value && !reset) {
        return;
    }

    assetHistoryLoading.value = true;
    try {
        const response = await axios.get('/api/asset-histories', {
            params: {
                asset_id: props.assetId,
                per_page: assetHistoryPerPage,
                page: assetHistoryPage.value,
            },
        });

        const payload = response.data.data;
        const items = payload.items;

        const pageData = items.data ?? [];

        if (reset) {
            assetHistoryItems.value = pageData;
        } else {
            const existingIds = new Set(assetHistoryItems.value.map((item: any) => item.id));
            const incoming = pageData.filter((item: any) => !existingIds.has(item.id));
            assetHistoryItems.value = [...assetHistoryItems.value, ...incoming];
        }

        assetHistoryHasMore.value = !!items.next_page_url;
        assetHistoryPage.value += 1;

        if (Array.isArray(payload.action_options) && !assetHistoryActionOptions.value.length) {
            assetHistoryActionOptions.value = payload.action_options;
        }
    } catch (error) {
        console.error('Error fetching asset history:', error);
    } finally {
        assetHistoryLoading.value = false;
    }
};

const openHistoryDetail = (row: any) => {
    const source = assetHistoryItems.value.find((item: any) => item.id === row.id) ?? row;

    selectedHistory.value = {
        ...source,
        action_label: row.action_label,
        performed_by_name: row.performed_by_name,
        performed_at: row.performed_at,
    };

    isHistoryModalOpen.value = true;
};

const closeHistoryDetail = () => {
    isHistoryModalOpen.value = false;
    selectedHistory.value = null;
};

const formatPayload = (value: unknown) => {
    if (value === null || value === undefined) {
        return '-';
    }

    if (typeof value === 'string') {
        return value;
    }

    try {
        return JSON.stringify(value, null, 2);
    } catch {
        return '-';
    }
};

const onAssetHistoryLoadMore = () => {
    fetchAssetHistory(false);
};

const openEmployeeDrawer = () => {
    draftEmployeeId.value = selectedEmployeeId.value;
    isEmployeeDrawerOpen.value = true;
};

const orderedEmployeeOptions = computed(() => {
    if (!draftEmployeeId.value) {
        return employeeOptions.value;
    }

    const index = employeeOptions.value.findIndex((emp: any) => emp.id === draftEmployeeId.value);
    if (index === -1) {
        return employeeOptions.value;
    }

    const selected = employeeOptions.value[index];
    const rest = employeeOptions.value.filter((_: any, i: number) => i !== index);

    return [selected, ...rest];
});

const fetchEmployeeOptions = async (reset = false) => {
    if (reset) {
        employeePage.value = 1;
        employeeOptions.value = [];
        employeeHasMore.value = true;
    }

    if (!employeeHasMore.value && !reset) return;

    employeeLoading.value = true;
    try {
        const response = await axios.get('/api/employees/selection', {
            params: {
                page: employeePage.value,
                per_page: 20,
                search: employeeSearch.value,
            },
        });
        const data = response.data.data;
        if (reset) {
            employeeOptions.value = data.data ?? [];
        } else {
            const existingIds = new Set(employeeOptions.value.map((emp: any) => emp.id));
            const incoming = (data.data ?? []).filter((emp: any) => !existingIds.has(emp.id));
            employeeOptions.value = [...employeeOptions.value, ...incoming];
        }
        employeeHasMore.value = !!data.next_page_url;
        employeePage.value++;
    } catch (error) {
        console.error('Error fetching employees:', error);
    } finally {
        employeeLoading.value = false;
    }
};

const onEmployeeSearch = (query: string) => {
    employeeSearch.value = query;
    fetchEmployeeOptions(true);
};

const onEmployeeLoadMore = () => {
    fetchEmployeeOptions(false);
};

const updateAssetEmployee = async (employeeId: string | null) => {
    if (!asset.value) return;
    updatingEmployeeEmployee.value = true;
    try {
        const response = await axios.put(`/api/assets/${props.assetId}/employee`, {
            employee_id: employeeId,
        });
        const updatedEmployee = response.data.data?.employee ?? null;
        asset.value.employee = updatedEmployee;
        asset.value.employee_id = updatedEmployee ? updatedEmployee.id : null;
    } catch (error) {
        console.error('Error updating asset employee:', error);
    } finally {
        updatingEmployeeEmployee.value = false;
    }
};

const downloadLabel = () => {
    if (!asset.value) return;
    const url = `/api/assets/${asset.value.id}/label-pdf`;
    window.open(url, '_blank');
};

const handleSaveEmployee = async () => {
    await updateAssetEmployee(draftEmployeeId.value);
    selectedEmployeeId.value = draftEmployeeId.value;
    isEmployeeDrawerOpen.value = false;
};

onMounted(async () => {
    try {
        const response = await axios.get(`/api/assets/${props.assetId}`);
        asset.value = response.data.data;

        if (asset.value && asset.value.employee) {
            selectedEmployeeId.value = asset.value.employee.id;
            if (!employeeOptions.value.find((emp) => emp.id === asset.value.employee.id)) {
                employeeOptions.value.push(asset.value.employee);
            }
        }

        const extraResponse = await axios.get(`/api/assets/${props.assetId}/additional-info`);
        additionalInfo.value = extraResponse.data.data;

        await Promise.all([
            fetchEmployeeOptions(false),
            fetchAssetHistory(true),
        ]);
    } catch (error) {
        console.error('Error fetching asset detail:', error);
    } finally {
        loading.value = false;
    }
});
</script>
