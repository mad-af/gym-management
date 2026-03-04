<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="currentPageTitle" />

        <Stats2 :items="statsItems" />

        <DynamicTable :columns="columns" :data="tableData" :items-per-page="perPage" :total-items="totalItems"
            :current-page="currentPage" :is-server-side="true" :selectable="false" @edit="goToDetail"
            @update:page="handlePageChange" @update:search="handleSearch" @update:perPage="handlePerPageChange">
            <template #header-actions>
                <Button size="sm" variant="outline" :onClick="() => isFilterDrawerOpen = true"
                    className="w-full sm:w-auto" :startIcon="FilterIcon">
                    Filter
                </Button>
                <Button v-can="ASSET_PROPOSAL_PERMISSIONS.CREATE" size="sm" variant="primary"
                    :onClick="openCreateDrawer" className="w-full sm:w-auto" :endIcon="PlusIcon">
                    Buat Usulan
                </Button>
            </template>
            <template #actions="{ row }">
                <div class="flex items-center justify-center gap-2">
                    <button @click="goToDetail(row)"
                        class="text-gray-500 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-500"
                        title="Detail Usulan">
                        <EyeIcon class="w-4.5 h-4.5" />
                    </button>
                </div>
            </template>
        </DynamicTable>

        <Drawer :isOpen="isFilterDrawerOpen" @close="isFilterDrawerOpen = false" title="Filter Usulan Aset">
            <div class="space-y-6">
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        OPD
                    </label>
                    <Combobox v-model="filters.opd_id" :options="opdOptions" labelKey="name" valueKey="id"
                        placeholder="Semua OPD" :loading="opdLoading" remote @search="onOpdSearch"
                        @load-more="onOpdLoadMore" />
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Kategori
                    </label>
                    <Combobox v-model="filters.category_id" :options="categoryOptions" labelKey="name" valueKey="id"
                        placeholder="Semua Kategori" :loading="categoryLoading" remote @search="onCategorySearch"
                        @load-more="onCategoryLoadMore" />
                </div>

                <div class="space-y-2">
                    <SelectInput v-model="filters.status" :options="statusOptions" label="Status Usulan"
                        placeholder="Semua Status" valueKey="value" labelKey="label" />
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Periode Tanggal Usulan
                    </label>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <input type="date" v-model="filters.date_from"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
                        <input type="date" v-model="filters.date_to"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
                    </div>
                </div>
            </div>
            <template #footer>
                <div class="flex w-full justify-end gap-3">
                    <Button variant="outline" :onClick="resetFilter">
                        Reset
                    </Button>
                    <Button variant="primary" :onClick="applyFilter">
                        Terapkan Filter
                    </Button>
                </div>
            </template>
        </Drawer>

        <Drawer :isOpen="isFormDrawerOpen" @close="closeFormDrawer"
            :title="isEditing ? 'Ubah Usulan Aset' : 'Buat Usulan Aset'">
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
                        Kategori Aset <span class="text-error-500">*</span>
                    </label>
                    <Combobox v-model="form.category_id" :options="categoryOptions" labelKey="name" valueKey="id"
                        placeholder="Pilih kategori..." :loading="categoryLoading" remote @search="onCategorySearch"
                        @load-more="onCategoryLoadMore" />
                    <p v-if="form.errors.category_id" class="mt-1 text-sm text-error-500">
                        {{ form.errors.category_id }}
                    </p>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Nama Barang <span class="text-error-500">*</span>
                    </label>
                    <input type="text" v-model="form.item_name"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Masukkan nama barang" />
                    <p v-if="form.errors.item_name" class="mt-1 text-sm text-error-500">
                        {{ form.errors.item_name }}
                    </p>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Spesifikasi
                    </label>
                    <textarea v-model="form.specification" rows="3"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Masukkan spesifikasi barang (opsional)"></textarea>
                    <p v-if="form.errors.specification" class="mt-1 text-sm text-error-500">
                        {{ form.errors.specification }}
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                            Jumlah <span class="text-error-500">*</span>
                        </label>
                        <input type="number" v-model.number="form.qty" min="1"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                            placeholder="Masukkan jumlah" />
                        <p v-if="form.errors.qty" class="mt-1 text-sm text-error-500">
                            {{ form.errors.qty }}
                        </p>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                            Perkiraan Harga Satuan <span class="text-error-500">*</span>
                        </label>
                        <input type="number" v-model.number="form.estimated_price" min="0" step="0.01"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                            placeholder="Masukkan harga satuan" />
                        <p v-if="form.errors.estimated_price" class="mt-1 text-sm text-error-500">
                            {{ form.errors.estimated_price }}
                        </p>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Catatan Tambahan
                    </label>
                    <textarea v-model="form.notes" rows="3"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Masukkan catatan tambahan (opsional)"></textarea>
                    <p v-if="form.errors.notes" class="mt-1 text-sm text-error-500">
                        {{ form.errors.notes }}
                    </p>
                </div>

                <div class="rounded-lg bg-gray-50 p-3 text-xs text-gray-600 dark:bg-gray-800/60 dark:text-gray-300">
                    <p class="font-medium">
                        Ringkasan Perkiraan Total:
                    </p>
                    <p class="mt-1">
                        {{ form.qty || 0 }} x Rp {{ formatCurrency(form.estimated_price || 0) }} =
                        <span class="font-semibold">Rp {{ formatCurrency((form.qty || 0) * (form.estimated_price || 0))
                            }}</span>
                    </p>
                </div>
            </div>
            <template #footer>
                <div class="flex w-full justify-end gap-3">
                    <Button variant="outline" :onClick="closeFormDrawer">
                        Batal
                    </Button>
                    <Button variant="primary" :onClick="submitForm" :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : isEditing ? 'Simpan Perubahan' : 'Simpan Usulan' }}
                    </Button>
                </div>
            </template>
        </Drawer>
    </AdminLayout>
</template>

<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { debounce } from 'lodash';
import { onMounted, ref, computed } from 'vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import SelectInput from '@/components/forms/FormElements/SelectInput.vue';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import DynamicTable from '@/components/tables/data-tables/DynamicTable.vue';
import type { Column } from '@/components/tables/data-tables/DynamicTable.vue';
import Button from '@/components/ui/Button.vue';
import Combobox from '@/components/ui/Combobox.vue';
import Drawer from '@/components/ui/Drawer.vue';
import Stats2 from '@/components/ui/Stats2.vue';
import { ASSET_PROPOSAL_PERMISSIONS } from '@/directives/permissions';
import { PlusIcon, FilterIcon, EyeIcon, FileTextIcon, CheckCircleIcon, SuccessIcon } from '@/icons';

const page = usePage();
const currentOpd = computed(() => (page.props.auth as any).current_opd);
const currentOpdSet = computed(() => !!currentOpd.value);

const currentPageTitle = ref('Usulan Aset');

const columns = ref<Column[]>([
    {
        key: 'proposal_number',
        label: 'No. Usulan',
        sortable: false,
    },
    {
        key: 'item_name',
        label: 'Nama Barang',
        sortable: false,
        class: 'min-w-[180px]',
    },
    {
        key: 'category_name',
        label: 'Kategori',
        sortable: false,
    },
    {
        key: 'proposal_date',
        label: 'Tgl Usulan',
        sortable: false,
    },
    {
        key: 'status_label',
        label: 'Status',
        type: 'status',
        statusMap: {},
    },
    {
        key: 'actions',
        label: 'Aksi',
        type: 'action',
        class: 'text-center',
    },
]);

const tableData = ref<any[]>([]);
const currentPage = ref(1);
const perPage = ref(10);
const totalItems = ref(0);

const stats = ref({
    total_proposals: 0,
    submitted_proposals: 0,
    approved_proposals: 0,
    completed_proposals: 0,
});

const statsItems = computed(() => [
    {
        label: 'Total Usulan',
        value: stats.value.total_proposals,
        icon: FileTextIcon,
        iconBgClass: 'bg-blue-50 text-blue-500 dark:bg-blue-500/10',
    },
    {
        label: 'Diajukan',
        value: stats.value.submitted_proposals,
        icon: PlusIcon,
        iconBgClass: 'bg-emerald-50 text-emerald-500 dark:bg-emerald-500/10',
    },
    {
        label: 'Disetujui',
        value: stats.value.approved_proposals,
        icon: CheckCircleIcon,
        iconBgClass: 'bg-purple-50 text-purple-500 dark:bg-purple-500/10',
    },
    {
        label: 'Selesai',
        value: stats.value.completed_proposals,
        icon: SuccessIcon,
        iconBgClass: 'bg-indigo-50 text-indigo-500 dark:bg-indigo-500/10',
    },
]);

const isFilterDrawerOpen = ref(false);
const isFormDrawerOpen = ref(false);
const isEditing = ref(false);

const statusOptions = ref<{ value: string; label: string; class: string }[]>([]);
const opdOptions = ref<any[]>([]);
const opdLoading = ref(false);
const categoryOptions = ref<any[]>([]);
const categoryLoading = ref(false);

const filters = ref<{
    search: string;
    opd_id: string | null;
    category_id: string | null;
    status: string | null;
    date_from: string | null;
    date_to: string | null;
}>({
    search: '',
    opd_id: null,
    category_id: null,
    status: '',
    date_from: null,
    date_to: null,
});

const form = useForm({
    id: null as string | null,
    opd_id: null as string | null,
    category_id: null as string | null,
    item_name: '',
    specification: '',
    qty: 1,
    estimated_price: 0,
    notes: '',
});

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
};

const formatDate = (date: string | null | undefined) => {
    if (!date) return '-';

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(date));
};

const goToDetail = (row: any) => {
    window.location.href = `/proposals/${row.id}`;
};

const mapProposalRow = (proposal: any, options: { value: string; label: string; class: string }[]) => {
    const statusOption = options.find((option) => option.value === proposal.status);
    const statusLabel = statusOption?.label ?? proposal.status;

    return {
        id: proposal.id,
        proposal_number: proposal.proposal_number,
        proposal_date: formatDate(proposal.proposal_date),
        opd_name: proposal.opd?.name ?? '-',
        item_name: proposal.item_name,
        category_name: proposal.category?.name ?? '-',
        summary: `${proposal.qty} x Rp ${formatCurrency(proposal.estimated_price)} (Rp ${formatCurrency(proposal.total_estimation)})`,
        status: proposal.status,
        status_label: statusLabel,
        raw: proposal,
    };
};

const fetchProposals = async () => {
    try {
        const response = await axios.get('/api/asset-proposals', {
            params: {
                per_page: perPage.value,
                page: currentPage.value,
                search: filters.value.search || undefined,
                opd_id: filters.value.opd_id || undefined,
                category_id: filters.value.category_id || undefined,
                status: filters.value.status || undefined,
                date_from: filters.value.date_from || undefined,
                date_to: filters.value.date_to || undefined,
            },
        });
        const data = response.data?.data;
        const paginator = data?.items;

        const options: { value: string; label: string; class: string }[] = data?.status_options ?? [];
        statusOptions.value = options;

        const statusMap: Record<string, string> = {};
        options.forEach((option: { value: string; label: string; class: string }) => {
            if (option.class) {
                statusMap[option.label] = option.class;
            }
        });

        const cols = columns.value;
        const statusCol = cols.find((c) => c.key === 'status_label');
        if (statusCol) {
            statusCol.statusMap = statusMap;
        }

        tableData.value = (paginator?.data ?? []).map((proposal: any) => mapProposalRow(proposal, options));
        totalItems.value = paginator?.total ?? 0;
        currentPage.value = paginator?.current_page ?? 1;
        perPage.value = paginator?.per_page ?? perPage.value;
    } catch (error) {
        console.error('Gagal mengambil daftar usulan aset:', error);
    }
};

const fetchStats = async () => {
    try {
        const response = await axios.get('/api/asset-proposals/stats');
        const data = response.data?.data ?? {};
        stats.value.total_proposals = data.total_proposals ?? 0;
        stats.value.submitted_proposals = data.submitted_proposals ?? 0;
        stats.value.approved_proposals = data.approved_proposals ?? 0;
        stats.value.completed_proposals = data.completed_proposals ?? 0;
    } catch (error) {
        console.error('Gagal mengambil statistik usulan aset:', error);
    }
};

const handlePageChange = (page: number) => {
    currentPage.value = page;
    fetchProposals();
};

const handlePerPageChange = (value: number) => {
    perPage.value = value;
    currentPage.value = 1;
    fetchProposals();
};

const handleSearch = debounce((value: string) => {
    filters.value.search = value;
    currentPage.value = 1;
    fetchProposals();
}, 400);

const resetFilter = () => {
    filters.value = {
        search: '',
        opd_id: null,
        category_id: null,
        status: '',
        date_from: null,
        date_to: null,
    };

    applyFilter();
};

const applyFilter = () => {
    currentPage.value = 1;
    fetchProposals();
    isFilterDrawerOpen.value = false;
};

const openCreateDrawer = () => {
    isEditing.value = false;
    form.reset();
    form.clearErrors();
    form.opd_id = currentOpdSet.value ? (currentOpd.value as any).id : null;
    form.qty = 1;
    form.estimated_price = 0;
    isFormDrawerOpen.value = true;
};

const closeFormDrawer = () => {
    isFormDrawerOpen.value = false;
};

const submitForm = async () => {
    form.processing = true;
    form.clearErrors();

    const payload = {
        opd_id: form.opd_id,
        category_id: form.category_id,
        item_name: form.item_name,
        specification: form.specification,
        qty: form.qty,
        estimated_price: form.estimated_price,
        notes: form.notes,
    };

    try {
        if (isEditing.value && form.id) {
            await axios.put(`/api/asset-proposals/${form.id}`, payload);
        } else {
            await axios.post('/api/asset-proposals', payload);
        }

        closeFormDrawer();
        fetchProposals();
    } catch (error: any) {
        if (error.response?.status === 422 && error.response.data?.errors) {
            const errors = error.response.data.errors as Record<string, string[] | string>;

            Object.entries(errors).forEach(([key, value]) => {
                const message = Array.isArray(value) ? value[0] : value;
                form.setError(key as any, message);
            });
        }

        console.error('Gagal menyimpan usulan aset:', error);
    } finally {
        form.processing = false;
    }
};

const onOpdSearch = async (query: string) => {
    opdLoading.value = true;
    try {
        const response = await axios.get('/api/opds', {
            params: {
                search: query || undefined,
                per_page: 10,
            },
        });
        const data = response.data?.data;
        const paginator = data?.items ?? data;
        opdOptions.value = paginator?.data ?? paginator ?? [];
    } catch (error) {
        console.error('Gagal mengambil data OPD:', error);
    } finally {
        opdLoading.value = false;
    }
};

const onOpdLoadMore = async () => {
    if (opdLoading.value) return;
};

const onCategorySearch = async (query: string) => {
    categoryLoading.value = true;
    try {
        const response = await axios.get('/api/asset-categories', {
            params: {
                search: query || undefined,
                per_page: 10,
            },
        });
        const data = response.data?.data;
        const paginator = data?.items ?? data;
        categoryOptions.value = paginator?.data ?? paginator ?? [];
    } catch (error) {
        console.error('Gagal mengambil data kategori aset:', error);
    } finally {
        categoryLoading.value = false;
    }
};

const onCategoryLoadMore = async () => {
    if (categoryLoading.value) return;
};

onMounted(() => {
    fetchProposals();
    fetchStats();
    onOpdSearch('');
    onCategorySearch('');
});
</script>
