<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="currentPageTitle" />

        <Stats2 :items="statsItems" />

        <DynamicTable :columns="columns" :data="tableData" :items-per-page="perPage" :total-items="totalItems"
            :current-page="currentPage" :is-server-side="true" :selectable="false" @update:page="handlePageChange"
            @update:search="handleSearch" @update:perPage="handlePerPageChange" @download="handleExportData">
            <template #header-actions>
                <Button size="sm" variant="outline" :onClick="() => isFilterDrawerOpen = true"
                    className="w-full sm:w-auto" :startIcon="FilterIcon">
                    Filter
                </Button>
                <Button v-can="ASSET_DISPOSAL_PERMISSIONS.CREATE" size="sm" variant="primary"
                    :onClick="handleAddDisposal" className="w-full sm:w-auto" :endIcon="PlusIcon">
                    Buat Penghapusan
                </Button>
            </template>
            <template #actions="{ row }">
                <div class="flex items-center gap-2">
                    <Link v-can="ASSET_DISPOSAL_PERMISSIONS.VIEW" :href="`/asset-management/disposals/${row.id}`"
                        class="text-gray-500 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-500"
                        title="Detail">
                        <EyeIcon class="w-4.5 h-4.5" />
                    </Link>
                </div>
            </template>
        </DynamicTable>

        <Drawer :isOpen="isDrawerOpen" @close="closeDrawer" title="Buat Dokumen Penghapusan Aset">
            <div class="space-y-6">
                <div v-if="!currentOpdSet" class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        OPD <span class="text-error-500">*</span>
                    </label>
                    <Combobox v-model="form.opd_id" :options="opdOptions" labelKey="name" valueKey="id"
                        placeholder="Pilih OPD..." :loading="opdLoading" remote @search="onOpdSearch"
                        @load-more="onOpdLoadMore" />
                    <p v-if="form.errors.opd_id" class="mt-1 text-sm text-error-500">
                        {{ form.errors.opd_id }}
                    </p>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Tanggal Penghapusan <span class="text-error-500">*</span>
                    </label>
                    <input type="date" id="disposal_date" v-model="form.disposal_date"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
                    <p v-if="form.errors.disposal_date" class="mt-1 text-sm text-error-500">
                        {{ form.errors.disposal_date }}
                    </p>
                </div>

                <div class="space-y-2">
                    <SelectInput v-model="form.disposal_type" :options="disposalTypeOptions" label="Tipe Penghapusan"
                        placeholder="Pilih tipe penghapusan" :required="true" />
                    <p v-if="form.errors.disposal_type" class="mt-1 text-sm text-error-500">
                        {{ form.errors.disposal_type }}
                    </p>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Catatan
                    </label>
                    <textarea id="notes" v-model="form.notes" rows="3"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Masukkan catatan dokumen penghapusan (opsional)"></textarea>
                    <p v-if="form.errors.notes" class="mt-1 text-sm text-error-500">
                        {{ form.errors.notes }}
                    </p>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">
                            Daftar Aset yang Dihapus
                        </span>
                        <Button size="sm" variant="outline" :onClick="addItem">
                            Tambah Aset
                        </Button>
                    </div>
                    <div v-if="!form.items.length"
                        class="rounded-lg border border-dashed border-gray-300 p-4 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        Belum ada aset yang dipilih. Klik "Tambah Aset" untuk menambahkan.
                    </div>
                    <div v-else class="space-y-4">
                        <div v-for="(item, index) in form.items" :key="index"
                            class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1 space-y-4">
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                            Aset
                                        </label>
                                        <Combobox v-model="item.asset_id" :options="assetOptions" labelKey="display"
                                            valueKey="id" placeholder="Pilih aset..." :loading="assetLoading" remote
                                            @search="onAssetSearch" @load-more="onAssetLoadMore" />
                                        <p v-if="form.errors[`items.${index}.asset_id`]"
                                            class="mt-1 text-sm text-error-500">
                                            {{ form.errors[`items.${index}.asset_id`] }}
                                        </p>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                            Kondisi Saat Penghapusan
                                        </label>
                                        <input type="text" v-model="item.condition_at_disposal"
                                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                                            placeholder="Contoh: Rusak berat, hilang, dll." />
                                        <p v-if="form.errors[`items.${index}.condition_at_disposal`]"
                                            class="mt-1 text-sm text-error-500">
                                            {{ form.errors[`items.${index}.condition_at_disposal`] }}
                                        </p>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                            Alasan Penghapusan
                                        </label>
                                        <textarea v-model="item.reason" rows="2"
                                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                                            placeholder="Jelaskan alasan penghapusan aset"></textarea>
                                        <p v-if="form.errors[`items.${index}.reason`]"
                                            class="mt-1 text-sm text-error-500">
                                            {{ form.errors[`items.${index}.reason`] }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex-0">
                                    <Button size="sm" variant="outline" :onClick="() => removeItem(index)"
                                        className="p-2" title="Hapus aset dari daftar">
                                        <TrashIcon class="w-4 h-4" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p v-if="form.errors.items" class="mt-1 text-sm text-error-500">
                        {{ form.errors.items }}
                    </p>
                </div>
            </div>

            <template #footer>
                <div class="flex w-full justify-end gap-3">
                    <Button variant="outline" :onClick="closeDrawer">
                        Batal
                    </Button>
                    <Button variant="primary" :onClick="saveDisposal" :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Dokumen' }}
                    </Button>
                </div>
            </template>
        </Drawer>

        <Drawer :isOpen="isFilterDrawerOpen" @close="isFilterDrawerOpen = false" title="Filter Dokumen Penghapusan">
            <div class="space-y-6">
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        OPD
                    </label>
                    <Combobox v-model="filters.opd_id" :options="opdOptions" labelKey="name" valueKey="id"
                        placeholder="Pilih OPD..." :loading="opdLoading" remote @search="onOpdSearch"
                        @load-more="onOpdLoadMore" />
                </div>

                <div class="space-y-2">
                    <SelectInput v-model="filters.disposal_type" :options="disposalTypeOptions" label="Tipe Penghapusan"
                        placeholder="Semua Tipe" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Rentang Tanggal
                    </label>
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        <input type="date" v-model="filters.date_from"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
                        <input type="date" v-model="filters.date_to"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
                    </div>
                </div>
            </div>
            <template #footer>
                <div class="flex w-full justify-end gap-3">
                    <Button variant="outline" :onClick="resetFilter">
                        Reset
                    </Button>
                    <Button variant="primary" :onClick="handleFilter">
                        Terapkan Filter
                    </Button>
                </div>
            </template>
        </Drawer>
    </AdminLayout>
</template>

<script setup lang="ts">
import { useForm, Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { debounce } from 'lodash';
import { ref, computed, onMounted, watch } from 'vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import SelectInput from '@/components/forms/FormElements/SelectInput.vue';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import DynamicTable from '@/components/tables/data-tables/DynamicTable.vue';
import type { Column } from '@/components/tables/data-tables/DynamicTable.vue';
import Button from '@/components/ui/Button.vue';
import Combobox from '@/components/ui/Combobox.vue';
import Drawer from '@/components/ui/Drawer.vue';
import Stats2 from '@/components/ui/Stats2.vue';
import { ASSET_DISPOSAL_PERMISSIONS } from '@/directives/permissions';
import { PlusIcon, FilterIcon, EyeIcon, TrashIcon, FileTextIcon, CheckCircleIcon, WarningIcon, BarChartIcon } from '@/icons';

defineProps({});

const page = usePage();
const currentOpd = computed(() => (page.props.auth as any).current_opd);
const currentOpdSet = computed(() => !!currentOpd.value);

const currentPageTitle = ref('Dokumen Penghapusan Aset');
const isDrawerOpen = ref(false);
const isFilterDrawerOpen = ref(false);

const stats = ref({
    total_assets: 0,
    total_disposal_documents: 0,
    total_disposed_assets: 0,
    total_major_damage_not_disposed: 0,
    percentage_major_damage_disposed: 0,
});

const statsItems = computed(() => [
    {
        label: 'Dokumen Penghapusan',
        value: stats.value.total_disposal_documents,
        icon: FileTextIcon,
        iconBgClass: 'bg-blue-50 text-blue-500 dark:bg-blue-500/10',
    },
    {
        label: 'Aset Terhapus',
        value: stats.value.total_disposed_assets,
        icon: CheckCircleIcon,
        iconBgClass: 'bg-emerald-50 text-emerald-500 dark:bg-emerald-500/10',
    },
    {
        label: 'Belum Dihapus',
        value: stats.value.total_major_damage_not_disposed,
        icon: WarningIcon,
        iconBgClass: 'bg-amber-50 text-amber-500 dark:bg-amber-500/10',
    },
    {
        label: 'Terhapus',
        value: stats.value.percentage_major_damage_disposed,
        suffix: '%',
        icon: BarChartIcon,
        iconBgClass: 'bg-purple-50 text-purple-500 dark:bg-purple-500/10',
    },
]);

const documents = ref<any[]>([]);
const filters = ref({
    opd_id: null as string | null,
    disposal_type: '' as string,
    date_from: '',
    date_to: '',
});
const totalItems = ref(0);
const currentPage = ref(1);
const perPage = ref(10);
const searchFilter = ref('');
const loading = ref(false);

const opdOptions = ref<any[]>([]);
const opdLoading = ref(false);
const opdSearch = ref('');
const opdPage = ref(1);
const opdHasMore = ref(true);

const disposalTypeOptions = [
    { value: 'destruction', label: 'Pemusnahan' },
    { value: 'auction', label: 'Lelang' },
    { value: 'grant', label: 'Hibah' },
    { value: 'write_off', label: 'Penghapusan' },
];

const assetOptions = ref<any[]>([]);
const assetLoading = ref(false);
const assetSearch = ref('');
const assetPage = ref(1);
const assetHasMore = ref(true);

const form = useForm({
    opd_id: null as string | null,
    disposal_type: '' as string,
    disposal_date: '',
    notes: '',
    items: [] as {
        asset_id: string | null;
        reason: string;
        condition_at_disposal: string;
    }[],
});

const resetForm = () => {
    form.reset();
    form.clearErrors();
    form.opd_id = currentOpdSet.value ? (currentOpd.value as any).id : null;
    form.disposal_type = '';
    form.disposal_date = new Date().toISOString().split('T')[0];
    form.notes = '';
    form.items = [];
    assetOptions.value = [];
};

const fetchStats = async () => {
    try {
        const response = await axios.get('/api/disposals/stats');
        const data = response.data.data ?? {};
        stats.value.total_assets = data.total_assets ?? 0;
        stats.value.total_disposal_documents = data.total_disposal_documents ?? 0;
        stats.value.total_disposed_assets = data.total_disposed_assets ?? 0;
        stats.value.total_major_damage_not_disposed = data.total_major_damage_not_disposed ?? 0;
        stats.value.percentage_major_damage_disposed = data.percentage_major_damage_disposed ?? 0;
    } catch (error) {
        console.error('Error fetching disposal stats:', error);
    }
};

const fetchDocuments = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/disposals', {
            params: {
                page: currentPage.value,
                per_page: perPage.value,
                search: searchFilter.value || undefined,
                opd_id: filters.value.opd_id || undefined,
                disposal_type: filters.value.disposal_type || undefined,
                date_from: filters.value.date_from || undefined,
                date_to: filters.value.date_to || undefined,
            },
        });

        const paginator = response.data.data;

        documents.value = paginator.data ?? [];
        totalItems.value = paginator.total ?? 0;
        currentPage.value = paginator.current_page ?? 1;
        perPage.value = paginator.per_page ?? perPage.value;
    } catch (error) {
        console.error('Error fetching disposal documents:', error);
    } finally {
        loading.value = false;
    }
};

const fetchOpdOptions = async (reset = false) => {
    if (reset) {
        opdPage.value = 1;
        opdOptions.value = [];
        opdHasMore.value = true;
    }

    if (!opdHasMore.value && !reset) return;

    opdLoading.value = true;
    try {
        const response = await axios.get('/api/opds/selection', {
            params: {
                page: opdPage.value,
                per_page: 20,
                search: opdSearch.value,
            },
        });
        const data = response.data.data;
        if (reset) {
            opdOptions.value = data.data;
        } else {
            opdOptions.value = [...opdOptions.value, ...data.data];
        }
        opdHasMore.value = !!data.next_page_url;
        opdPage.value++;
    } catch (error) {
        console.error('Error fetching OPDs:', error);
    } finally {
        opdLoading.value = false;
    }
};

const onOpdSearch = (query: string) => {
    opdSearch.value = query;
    fetchOpdOptions(true);
};

const onOpdLoadMore = () => {
    fetchOpdOptions(false);
};

watch(isDrawerOpen, (newValue) => {
    if (newValue) {
        fetchOpdOptions(true);
    }
});

watch(isFilterDrawerOpen, (newValue) => {
    if (newValue) {
        fetchOpdOptions(true);
    }
});

const handleFilter = () => {
    currentPage.value = 1;
    fetchDocuments();
    isFilterDrawerOpen.value = false;
};

const resetFilter = () => {
    filters.value.opd_id = null;
    filters.value.disposal_type = '';
    filters.value.date_from = '';
    filters.value.date_to = '';
    handleFilter();
};

onMounted(() => {
    fetchDocuments();
    fetchStats();
});

const formatDate = (date: string) => {
    if (!date) return '-';
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(date));
};

const tableData = computed(() => {
    return documents.value.map((doc: any) => {
        return {
            ...doc,
            id: doc.id,
            disposal_number: doc.disposal_number,
            opd_name: doc.opd?.name ?? '-',
            disposal_type_label: getDisposalTypeLabel(doc.disposal_type),
            disposal_date: formatDate(doc.disposal_date),
            items_count: Array.isArray(doc.items) ? doc.items.length : doc.items_count ?? 0,
            created_by_name: doc.created_by?.name ?? '-',
        };
    });
});

const columns = ref<Column[]>([
    {
        key: 'disposal_number',
        label: 'Nomor Dokumen',
        class: 'min-w-[180px]',
    },
    {
        key: 'opd_name',
        label: 'OPD',
        class: 'min-w-[180px]',
    },
    {
        key: 'disposal_type_label',
        label: 'Tipe Penghapusan',
        class: 'min-w-[180px]',
    },
    {
        key: 'disposal_date',
        label: 'Tanggal',
        class: 'min-w-[140px]',
    },
    {
        key: 'items_count',
        label: 'Jumlah Aset',
        class: 'min-w-[120px]',
    },
    {
        key: 'created_by_name',
        label: 'Dibuat Oleh',
        class: 'min-w-[160px]',
    },
    {
        key: 'actions',
        label: 'Aksi',
        type: 'action',
        class: 'w-[100px]',
    },
]);

const getDisposalTypeLabel = (value: string) => {
    const option = disposalTypeOptions.find((opt) => opt.value === value);
    return option ? option.label : value;
};

const fetchAssetOptions = async (reset = false) => {
    if (!form.opd_id) {
        assetOptions.value = [];
        assetHasMore.value = true;
        return;
    }

    if (reset) {
        assetPage.value = 1;
        assetOptions.value = [];
        assetHasMore.value = true;
    }

    if (!assetHasMore.value && !reset) return;

    assetLoading.value = true;
    try {
        const response = await axios.get('/api/assets/selection', {
            params: {
                page: assetPage.value,
                per_page: 20,
                search: assetSearch.value,
                opd_id: form.opd_id ?? undefined,
                only_disposable: true,
            },
        });
        const data = response.data.data;
        const items = data.data.map((asset: any) => ({
            ...asset,
            display: `${asset.asset_code} - ${asset.name}`,
        }));
        const merged = reset ? items : [...assetOptions.value, ...items];
        assetOptions.value = merged;
        assetHasMore.value = !!data.next_page_url;
        assetPage.value++;
    } catch (error) {
        console.error('Error fetching assets for disposal:', error);
    } finally {
        assetLoading.value = false;
    }
};

const onAssetSearch = (query: string) => {
    assetSearch.value = query;
    fetchAssetOptions(true);
};

const onAssetLoadMore = () => {
    fetchAssetOptions(false);
};

watch(
    () => form.opd_id,
    () => {
        form.items = [];
        fetchAssetOptions(true);
    }
);

const addItem = () => {
    form.items.push({
        asset_id: null,
        reason: '',
        condition_at_disposal: '',
    });
};

const removeItem = (index: number) => {
    form.items.splice(index, 1);
};

const handlePageChange = (page: number) => {
    currentPage.value = page;
    fetchDocuments();
};

const handlePerPageChange = (newPerPage: number) => {
    perPage.value = newPerPage;
    currentPage.value = 1; // Reset to first page
    fetchDocuments();
};

const handleSearch = debounce((search: string) => {
    searchFilter.value = search;
    currentPage.value = 1; // Reset to first page
    fetchDocuments();
}, 800);

const handleAddDisposal = () => {
    resetForm();
    isDrawerOpen.value = true;
};

const saveDisposal = async () => {
    form.processing = true;

    const dataToSubmit = {
        ...form.data(),
    };

    try {
        await axios.post('/api/disposals', dataToSubmit);
        closeDrawer();
        fetchDocuments();
    } catch (error: any) {
        if (error.response && error.response.data && error.response.data.errors) {
            form.errors = error.response.data.errors;
        } else {
            console.error('Error saving disposal document:', error);
        }
    } finally {
        form.processing = false;
    }
};

const closeDrawer = () => {
    isDrawerOpen.value = false;
    resetForm();
};

const handleExportData = () => {
    // TODO: Implement export functionality
    alert('Fitur export belum tersedia.');
};
</script>
