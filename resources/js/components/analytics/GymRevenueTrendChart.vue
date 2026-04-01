<template>
    <div
        class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
    >
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ title }}
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Perbandingan omzet 7 hari terakhir.
                </p>
            </div>
        </div>

        <div class="max-w-full overflow-x-auto">
            <div class="min-w-[680px] xl:min-w-full">
                <VueApexCharts
                    type="line"
                    :height="320"
                    :series="series"
                    :options="chartOptions"
                />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

interface RevenueSeries {
    name: string;
    data: number[];
}

interface GymRevenueTrendChartProps {
    title?: string;
    labels: string[];
    series: RevenueSeries[];
}

const props = withDefaults(defineProps<GymRevenueTrendChartProps>(), {
    title: 'Tren Pendapatan',
});

const formatCurrencyCompactId = (value: number): string => {
    const abs = Math.abs(value);
    const formatter = new Intl.NumberFormat('id-ID', {
        maximumFractionDigits: 1,
        minimumFractionDigits: 0,
    });

    if (abs >= 1_000_000_000_000)
        return `Rp ${formatter.format(value / 1_000_000_000_000)}T`;
    if (abs >= 1_000_000_000)
        return `Rp ${formatter.format(value / 1_000_000_000)}M`;
    if (abs >= 1_000_000) return `Rp ${formatter.format(value / 1_000_000)}Jt`;
    if (abs >= 1_000) return `Rp ${formatter.format(value / 1_000)}Rb`;

    return `Rp ${formatter.format(value)}`;
};

const formatCurrencyId = (value: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    })
        .format(value)
        .replace('Rp', 'Rp ');
};

const chartOptions = computed(() => {
    return {
        chart: {
            fontFamily: 'Outfit, sans-serif',
            toolbar: { show: false },
            zoom: { enabled: false },
        },
        stroke: {
            curve: 'smooth',
            width: 3,
        },
        colors: ['#3b82f6', '#10b981', '#f59e0b'],
        dataLabels: { enabled: false },
        xaxis: {
            categories: props.labels,
            axisBorder: { show: false },
            axisTicks: { show: false },
        },
        yaxis: {
            labels: {
                formatter: (val: number) => formatCurrencyCompactId(val),
            },
        },
        legend: {
            show: true,
            position: 'top',
            horizontalAlign: 'left',
            fontFamily: 'Outfit, sans-serif',
            fontSize: '13px',
        },
        grid: {
            borderColor: '#e5e7eb',
            strokeDashArray: 4,
        },
        tooltip: {
            y: {
                formatter: (val: number) => formatCurrencyId(val),
            },
        },
    };
});
</script>
