<template>
    <admin-layout>
        <div class="grid grid-cols-12 gap-4 md:gap-6">
            <div class="col-span-12 -mb-6">
                <Stats2 :items="statsItems" />
            </div>

            <div class="col-span-12 xl:col-span-7">
                <AcquisitionChartDynamic title="Serapan Anggaran per Kategori Aset"
                    :categories="categoryBudgetCategories" :series="categoryBudgetSeries" />
            </div>

            <div class="col-span-12 xl:col-span-5">
                <SessionChartDynamic title="Kondisi Kesehatan Aset" :labels="assetConditionLabels"
                    :series="assetConditionSeries" :colors="assetConditionColors" />
            </div>

            <div class="col-span-12 xl:col-span-4">
                <TopPagesCard :title="'Mutasi Aset Eksternal Pending'" :loading="transferSummaryLoading"
                    :items="transferSummaryItems" :empty-text="'Belum ada mutasi aset eksternal pending.'" />
            </div>

            <div class="col-span-12 xl:col-span-8">
                <AnalyticsTableDynamic title="Perawatan Aset Terbaru"
                    subtitle="Jadwal dan status perawatan aset terbaru di OPD ini" :columns="maintenanceColumns"
                    :rows="maintenanceRows" :row-key="'id'" @rowClick="goToMaintenanceDetail">
                    <template #cell-asset_display="{ row }">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-lg bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                                <img v-if="row.raw.photo && (typeof row.raw.photo === 'string' || row.raw.photo.url)"
                                    :src="typeof row.raw.photo === 'string' ? row.raw.photo : row.raw.photo.url" alt=""
                                    class="h-full w-full object-cover" />
                                <ImageIcon v-else class="h-5 w-5" />
                            </div>
                            <div class="space-y-0.5">
                                <p class="text-theme-sm font-medium text-gray-800 dark:text-white/90">
                                    {{ row.asset_display?.name ?? '-' }}
                                </p>
                                <p class="text-theme-xs text-gray-500 dark:text-gray-400">
                                    {{ row.asset_display?.code ?? '-' }}
                                </p>
                                <p class="text-theme-xs text-gray-400 dark:text-gray-500">
                                    Terjadwal:
                                    {{ row.scheduled_date_label ?? '-' }}
                                </p>
                            </div>
                        </div>
                    </template>

                    <template #cell-status_label="{ row, value }">
                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-theme-xs font-medium"
                            :class="maintenanceStatusClassMap[row.raw.status] || ''">
                            {{ value }}
                        </span>
                    </template>
                </AnalyticsTableDynamic>
            </div>

            <div class="col-span-12 xl:col-span-8">
                <AnalyticsTableDynamic title="Usulan Aset Terbaru" subtitle="Ringkasan usulan aset terbaru di OPD ini"
                    :columns="proposalColumns" :rows="proposalRows" :row-key="'id'" @rowClick="goToProposalDetail">
                    <template #cell-status_label="{ row, value }">
                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-theme-xs font-medium"
                            :class="proposalStatusClassMap[row.raw.status] || ''">
                            {{ value }}
                        </span>
                    </template>
                </AnalyticsTableDynamic>
            </div>
        </div>
    </admin-layout>
</template>

<script lang="ts">
import axios from 'axios';
import { BoxIcon, BanknoteIcon, TrashIcon, ImageIcon, WarningIcon, ArrowRightIcon } from '@/icons';
import AcquisitionChartDynamic from '../../components/analytics/AcquisitionChartDynamic.vue';
import AnalyticsTableDynamic from '../../components/analytics/AnalyticsTableDynamic.vue';
import SessionChartDynamic from '../../components/analytics/SessionChartDynamic.vue';
import TopPagesCard from '../../components/analytics/TopPagesCard.vue';
import AdminLayout from '../../components/layout/AdminLayout.vue';
import Stats2 from '../../components/ui/Stats2.vue';

export default {
    name: 'Dashboard',
    components: {
        AdminLayout,
        Stats2,
        AcquisitionChartDynamic,
        TopPagesCard,
        AnalyticsTableDynamic,
        SessionChartDynamic,
        ImageIcon,
    },
    data() {
        return {
            statsItems: [] as any[],
            categoryBudgetCategories: [] as string[],
            categoryBudgetSeries: [] as any[],
            assetConditionLabels: [] as string[],
            assetConditionSeries: [] as number[],
            assetConditionColors: ['#3641f5', '#4f46e5', '#7592ff', '#dde9ff'] as string[],
            maintenanceColumns: [
                { key: 'asset_display', label: 'Aset', primary: true },
                { key: 'maintenance_type', label: 'Jenis Perawatan' },
                { key: 'scheduled_date_label', label: 'Tgl Terjadwal' },
                { key: 'status_label', label: 'Status' },
            ] as any[],
            maintenanceRows: [] as any[],
            maintenanceStatusClassMap: {} as Record<string, string>,
            proposalColumns: [
                { key: 'proposal_number', label: 'No. Usulan', primary: true },
                { key: 'item_name', label: 'Nama Barang' },
                { key: 'category_name', label: 'Kategori' },
                { key: 'proposal_date_formatted', label: 'Tgl Usulan' },
                { key: 'status_label', label: 'Status' },
            ] as any[],
            proposalRows: [] as any[],
            proposalStatusClassMap: {} as Record<string, string>,
            transferSummaryItems: [] as {
                id: string | number;
                primary: string;
                secondary: string;
                statusLabel: string;
                statusClass?: string;
            }[],
            transferSummaryLoading: false as boolean,
        };
    },
    methods: {
        formatCurrency(value: number): string {
            if (!value) return 'Rp 0';

            const abs = Math.abs(value);
            const formatter = new Intl.NumberFormat('id-ID', {
                maximumFractionDigits: 1,
                minimumFractionDigits: 0,
            });

            if (abs >= 1_000_000_000_000) {
                return `Rp ${formatter.format(value / 1_000_000_000_000)}T`;
            }

            if (abs >= 1_000_000_000) {
                return `Rp ${formatter.format(value / 1_000_000_000)}M`;
            }

            if (abs >= 1_000_000) {
                return `Rp ${formatter.format(value / 1_000_000)}Jt`;
            }

            if (abs >= 1_000) {
                return `Rp ${formatter.format(value / 1_000)}Rb`;
            }

            return `Rp ${formatter.format(value)}`;
        },
        async fetchStats() {
            try {
                const response = await axios.get('/api/dashboard/stats');
                const data = response.data?.data || {};

                let pendingExternalTransfers = 0;

                try {
                    const transferStatsResponse = await axios.get('/api/transfers/stats');
                    const transferStats = transferStatsResponse.data?.data || {};
                    pendingExternalTransfers = transferStats.pending_external_transfers ?? 0;
                } catch (error) {
                    console.error('Error fetching transfer stats for dashboard:', error);
                }

                const totalAssets = data.total_assets || 0;
                const assetsWithPrice = data.assets_with_price || 0;

                const priceCoverage =
                    totalAssets > 0 ? Math.round((assetsWithPrice / totalAssets) * 100) : 0;

                this.statsItems = [
                    {
                        label: 'Total Aset',
                        value: totalAssets,
                        icon: BoxIcon,
                        iconBgClass: 'bg-blue-50 text-blue-500 dark:bg-blue-500/10',
                    },
                    {
                        label:
                            totalAssets > 0
                                ? `Total Nilai Aset (${priceCoverage}%)`
                                : 'Total Nilai Aset (belum ada harga)',
                        value: this.formatCurrency(data.total_valued_assets || 0),
                        icon: BanknoteIcon,
                        iconBgClass: 'bg-emerald-50 text-emerald-500 dark:bg-emerald-500/10',
                    },
                    {
                        label: 'Mutasi Pending',
                        value: pendingExternalTransfers,
                        icon: ArrowRightIcon,
                        iconBgClass: 'bg-amber-50 text-amber-500 dark:bg-amber-500/10',
                    },
                    {
                        label: 'Aset Rusak Berat',
                        value: data.major_damage_assets || 0,
                        icon: WarningIcon,
                        iconBgClass:
                            'bg-rose-50 text-rose-500 dark:bg-rose-500/10',
                    },
                    {
                        label: 'Aset Dihapus',
                        value: data.disposed_assets || 0,
                        icon: TrashIcon,
                        iconBgClass: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
                    },
                ];
            } catch (error) {
                console.error('Error fetching dashboard stats:', error);
            }
        },
        async fetchCategoryBudget() {
            try {
                const response = await axios.get('/api/dashboard/category-budget');
                const data = response.data?.data || {};

                const parents = Array.isArray(data.parents) ? data.parents : [];
                const children = Array.isArray(data.children) ? data.children : [];
                const values = Array.isArray(data.values) ? data.values : [];

                this.categoryBudgetCategories = parents.map((parent: any) => parent.name);

                this.categoryBudgetSeries = children.map((child: any, childIndex: number) => {
                    const seriesData = parents.map((_: any, parentIndex: number) => {
                        const row = values[parentIndex] ?? [];
                        const value = row[childIndex];

                        return typeof value === 'number' ? value : 0;
                    });

                    return {
                        name: child.name,
                        data: seriesData,
                    };
                });
            } catch (error) {
                console.error('Error fetching dashboard category budget stats:', error);
            }
        },
        async fetchAssetCondition() {
            try {
                const response = await axios.get('/api/dashboard/asset-condition');
                const data = response.data?.data || {};

                const labels = Array.isArray(data.labels) ? data.labels : [];
                const series = Array.isArray(data.series) ? data.series : [];

                this.assetConditionLabels = labels;
                this.assetConditionSeries = series;
            } catch (error) {
                console.error('Error fetching dashboard asset condition stats:', error);
            }
        },
        async fetchMaintenances() {
            try {
                const response = await axios.get('/api/dashboard/maintenances');
                const data = response.data?.data || {};
                const items = Array.isArray(data.items) ? data.items : [];

                const formatDate = (date: string | null | undefined) => {
                    if (!date) return '-';

                    return new Intl.DateTimeFormat('id-ID', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric',
                    }).format(new Date(date));
                };

                const statusOptions = Array.isArray(data.status_options) ? data.status_options : [];
                const statusClassMap: Record<string, string> = {};

                statusOptions.forEach((opt: any) => {
                    if (opt && typeof opt.value === 'string' && typeof opt.class === 'string') {
                        statusClassMap[opt.value] = opt.class;
                    }
                });

                this.maintenanceStatusClassMap = statusClassMap;

                this.maintenanceRows = items.map((item: any) => ({
                    id: item.id,
                    asset_display: {
                        name: item.asset_name ?? '-',
                        code: item.asset_code ?? '-',
                    },
                    scheduled_date_label: formatDate(item.scheduled_date),
                    maintenance_type: item.maintenance_type,
                    status_label: item.status_label || item.status || '-',
                    raw: item,
                }));
            } catch (error) {
                console.error('Error fetching dashboard maintenances summary:', error);
            }
        },
        async fetchProposals() {
            try {
                const response = await axios.get('/api/dashboard/proposals');
                const data = response.data?.data || {};
                const items = Array.isArray(data.items) ? data.items : [];

                const statusOptions = Array.isArray(data.status_options) ? data.status_options : [];
                const statusClassMap: Record<string, string> = {};

                statusOptions.forEach((opt: any) => {
                    if (opt && typeof opt.value === 'string' && typeof opt.class === 'string') {
                        statusClassMap[opt.value] = opt.class;
                    }
                });

                this.proposalStatusClassMap = statusClassMap;

                const formatDate = (date: string | null | undefined) => {
                    if (!date) return '-';

                    return new Intl.DateTimeFormat('id-ID', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric',
                    }).format(new Date(date));
                };

                this.proposalRows = items.map((item: any) => ({
                    id: item.id,
                    proposal_number: item.proposal_number,
                    item_name: item.item_name,
                    category_name: item.category_name ?? '-',
                    proposal_date_formatted: formatDate(item.proposal_date),
                    status_label: item.status_label || item.status || '-',
                    raw: item,
                }));
            } catch (error) {
                console.error('Error fetching dashboard proposals summary:', error);
            }
        },
        async fetchTransferSummary() {
            this.transferSummaryLoading = true;
            try {
                const response = await axios.get('/api/transfers', {
                    params: {
                        page: 1,
                        per_page: 4,
                        status: 'pending',
                        type: 'external',
                    },
                });

                const data = response.data?.data || {};
                const paginator = data.items || {};
                const items = Array.isArray(paginator.data) ? paginator.data : [];

                const statusOptions = Array.isArray(data.status_options) ? data.status_options : [];
                const statusLabelMap: Record<string, string> = {};
                const statusClassMap: Record<string, string> = {};

                statusOptions.forEach((opt: any) => {
                    if (opt && typeof opt.value === 'string') {
                        if (typeof opt.label === 'string') {
                            statusLabelMap[opt.value] = opt.label;
                        }
                        if (typeof opt.class === 'string') {
                            statusClassMap[opt.value] = opt.class;
                        }
                    }
                });

                this.transferSummaryItems = items.map((item: any) => {
                    const from = item.from_opd?.name ?? '-';
                    const to = item.to_opd?.name ?? '-';

                    return {
                        id: item.id,
                        primary: item.transfer_number,
                        secondary: `${from} → ${to}`,
                        statusLabel: statusLabelMap[item.status] || item.status || 'Pending',
                        statusClass:
                            statusClassMap[item.status] ||
                            'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400',
                    };
                });
            } catch (error) {
                console.error('Error fetching dashboard transfer summary:', error);
                this.transferSummaryItems = [];
            } finally {
                this.transferSummaryLoading = false;
            }
        },
        goToProposalDetail() {
            // Navigation disabled
        },
        goToMaintenanceDetail() {
            // Navigation disabled
        },
    },
    mounted() {
        this.fetchStats();
        this.fetchCategoryBudget();
        this.fetchAssetCondition();
        this.fetchMaintenances();
        this.fetchProposals();
        this.fetchTransferSummary();
    },
};
</script>
