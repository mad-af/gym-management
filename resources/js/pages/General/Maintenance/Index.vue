<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="currentPageTitle" />

        <Stats2 :items="statsItems" />

        <DynamicTable :columns="columns" :data="tableData" :items-per-page="perPage" :total-items="totalItems"
            :current-page="currentPage" :is-server-side="true" :selectable="false" @update:page="handlePageChange"
            @update:search="handleSearch" @update:perPage="handlePerPageChange">
            <template #header-actions>
                <Button size="sm" variant="outline" :onClick="() => isFilterDrawerOpen = true"
                    className="w-full sm:w-auto" :startIcon="FilterIcon">
                    Filter
                </Button>
                <MaintenanceForm v-can="ASSET_MAINTENANCE_PERMISSIONS.MANAGE" @saved="fetchMaintenances"
                    buttonText="Jadwalkan Perawatan" :endIcon="PlusIcon" buttonVariant="primary"
                    buttonClass="w-full sm:w-auto" />
            </template>
            <template #actions="{ row }">
                <div class="flex items-center gap-2 justify-center">
                    <Link v-can="ASSET_MAINTENANCE_PERMISSIONS.VIEW" :href="`/maintenance/${row.id}`"
                        class="text-gray-500 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-500"
                        title="Detail">
                        <EyeIcon class="w-4.5 h-4.5" />
                    </Link>
                </div>
            </template>
        </DynamicTable>

        <FilterDrawer :isOpen="isFilterDrawerOpen" :filters="filters" :statusOptions="statusFilterOptions"
            @close="isFilterDrawerOpen = false" @apply="handleFilterApply" />

    </AdminLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { debounce } from 'lodash';
import { computed, onMounted, ref } from 'vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import DynamicTable from '@/components/tables/data-tables/DynamicTable.vue';
import type { Column } from '@/components/tables/data-tables/DynamicTable.vue';
import Button from '@/components/ui/Button.vue';
import Stats2 from '@/components/ui/Stats2.vue';
import { ASSET_MAINTENANCE_PERMISSIONS } from '@/directives/permissions';
import { FilterIcon, PlusIcon, EyeIcon, WrenchIcon, Calendar2Line, RefreshIcon, WarningIcon } from '@/icons';
import FilterDrawer from './components/FilterDrawer.vue';
import MaintenanceForm from './components/MaintenanceForm.vue';

const currentPageTitle = ref('Perawatan Aset');
const maintenances = ref<any[]>([]);
const totalItems = ref(0);
const currentPage = ref(1);
const perPage = ref(10);
const searchFilter = ref('');
const loading = ref(false);

const stats = ref({
    total_maintenances: 0,
    scheduled_maintenances: 0,
    in_progress_maintenances: 0,
    overdue_maintenances: 0,
});

const statsItems = computed(() => [
    {
        label: 'Total Perawatan',
        value: stats.value.total_maintenances,
        icon: WrenchIcon,
        iconBgClass: 'bg-blue-50 text-blue-500 dark:bg-blue-500/10',
    },
    {
        label: 'Terjadwal',
        value: stats.value.scheduled_maintenances,
        icon: Calendar2Line,
        iconBgClass: 'bg-emerald-50 text-emerald-500 dark:bg-emerald-500/10',
    },
    {
        label: 'Sedang Berjalan',
        value: stats.value.in_progress_maintenances,
        icon: RefreshIcon,
        iconBgClass: 'bg-indigo-50 text-indigo-500 dark:bg-indigo-500/10',
    },
    {
        label: 'Terlambat',
        value: stats.value.overdue_maintenances,
        icon: WarningIcon,
        iconBgClass: 'bg-amber-50 text-amber-500 dark:bg-amber-500/10',
    },
]);

const isFilterDrawerOpen = ref(false);
const filters = ref({
    asset_id: null as string | null,
    status: '' as string,
    scheduled_from: '',
    scheduled_to: '',
});

const statusFilterOptions = ref<{ value: string; label: string; class: string }[]>([]);
const conditionOptions = ref<{ value: string; label: string; class: string }[]>([]);

const formatDate = (date: string | null | undefined) => {
    if (!date) return '-';
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(date));
};

const fetchMaintenances = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/asset-maintenances', {
            params: {
                page: currentPage.value,
                per_page: perPage.value,
                search: searchFilter.value || undefined,
                asset_id: filters.value.asset_id || undefined,
                status: filters.value.status || undefined,
                scheduled_from: filters.value.scheduled_from || undefined,
                scheduled_to: filters.value.scheduled_to || undefined,
            },
        });

        const payload = response.data.data;
        const paginator = payload.items;
        maintenances.value = paginator.data ?? [];
        totalItems.value = paginator.total ?? 0;
        currentPage.value = paginator.current_page ?? 1;
        perPage.value = paginator.per_page ?? perPage.value;

        if (payload.status_options) {
            statusFilterOptions.value = payload.status_options;
            const map: Record<string, string> = {};
            statusFilterOptions.value.forEach((opt) => {
                if (opt.class) {
                    map[opt.label] = opt.class;
                }
            });
            const cols = columns.value;
            const statusCol = cols.find((c) => c.key === 'status_label');
            if (statusCol) {
                statusCol.statusMap = map;
            }
        }

        if (payload.condition_options) {
            conditionOptions.value = payload.condition_options;
        }
    } catch (error) {
        console.error('Error fetching maintenances:', error);
    } finally {
        loading.value = false;
    }
};

const fetchStats = async () => {
    try {
        const response = await axios.get('/api/asset-maintenances/stats');
        const data = response.data.data ?? {};
        stats.value.total_maintenances = data.total_maintenances ?? 0;
        stats.value.scheduled_maintenances = data.scheduled_maintenances ?? 0;
        stats.value.in_progress_maintenances = data.in_progress_maintenances ?? 0;
        stats.value.overdue_maintenances = data.overdue_maintenances ?? 0;
    } catch (error) {
        console.error('Error fetching maintenance stats:', error);
    }
};

const tableData = computed(() => {
    return maintenances.value.map((maintenance: any) => {
        const asset = maintenance.asset;
        const statusOption = statusFilterOptions.value.find((option) => option.value === maintenance.status);
        const statusLabel = statusOption ? statusOption.label : maintenance.status;
        const isTerminal = maintenance.status === 'completed' || maintenance.status === 'canceled';

        return {
            ...maintenance,
            id: maintenance.id,
            asset_display: {
                name: asset ? asset.name : '-',
            },
            name: asset ? asset.name : '-',
            asset_code: asset ? asset.asset_code : '-',
            photo: asset?.photo ?? null,
            maintenance_type: maintenance.maintenance_type,
            scheduled_date_label: formatDate(maintenance.scheduled_date),
            completed_date_label: maintenance.completed_date ? formatDate(maintenance.completed_date) : '-',
            status_label: statusLabel,
            can_complete: !isTerminal,
            can_cancel: !isTerminal,
        };
    });
});

const columns = ref<Column[]>([
    {
        key: 'asset_display',
        label: 'Aset',
        type: 'cover',
        avatarField: 'photo',
        labelField: 'name',
        subLabelField: 'asset_code',
        class: 'min-w-[220px]',
    },
    {
        key: 'maintenance_type',
        label: 'Jenis Perawatan',
        class: 'min-w-[180px]',
    },
    {
        key: 'scheduled_date_label',
        label: 'Tgl Terjadwal',
        class: 'min-w-[140px]',
    },
    {
        key: 'completed_date_label',
        label: 'Tgl Selesai',
        class: 'min-w-[140px]',
    },
    {
        key: 'status_label',
        label: 'Status',
        type: 'status',
        statusMap: {},
        class: 'min-w-[140px]',
    },
    {
        key: 'actions',
        label: 'Aksi',
        type: 'action',
        class: 'w-[140px]',
    },
]);

const handlePageChange = (page: number) => {
    currentPage.value = page;
    fetchMaintenances();
};

const handlePerPageChange = (newPerPage: number) => {
    perPage.value = newPerPage;
    currentPage.value = 1;
    fetchMaintenances();
};

const handleSearch = debounce((search: string) => {
    searchFilter.value = search;
    currentPage.value = 1;
    fetchMaintenances();
}, 800);

const handleFilterApply = (newFilters: any) => {
    filters.value = { ...newFilters };
    currentPage.value = 1;
    fetchMaintenances();
    isFilterDrawerOpen.value = false;
};

onMounted(() => {
    fetchMaintenances();
    fetchStats();
});
</script>
