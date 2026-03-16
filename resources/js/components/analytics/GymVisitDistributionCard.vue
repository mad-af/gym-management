<template>
    <div
        class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
    >
        <div class="mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ title }}
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Komposisi kunjungan bulan berjalan.
            </p>
        </div>

        <div class="mx-auto flex justify-center">
            <div class="relative h-[260px] w-[300px]">
                <VueApexCharts
                    type="donut"
                    :width="300"
                    :height="260"
                    :series="series"
                    :options="chartOptions"
                />

                <div
                    class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center"
                >
                    <span
                        class="text-2xl font-bold text-gray-900 dark:text-white"
                        >{{ total }}</span
                    >
                    <span class="text-xs text-gray-500 dark:text-gray-400"
                        >Total Kunjungan</span
                    >
                </div>
            </div>
        </div>

        <div class="mt-4 space-y-2">
            <div
                v-for="(item, index) in distributionItems"
                :key="item.label"
                class="flex items-center justify-between rounded-lg border border-gray-100 px-3 py-2 dark:border-gray-800"
            >
                <div class="flex items-center gap-2">
                    <span
                        class="h-2.5 w-2.5 rounded-full"
                        :style="{ backgroundColor: colorAt(index) }"
                    />
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{
                        item.label
                    }}</span>
                </div>
                <span class="text-sm font-medium text-gray-900 dark:text-white"
                    >{{ item.value }} ({{ item.percentage }}%)</span
                >
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

interface GymVisitDistributionCardProps {
    title?: string;
    labels: string[];
    series: number[];
    colors?: string[];
}

const props = withDefaults(defineProps<GymVisitDistributionCardProps>(), {
    title: 'Komposisi Kunjungan',
    colors: () => ['#3b82f6', '#14b8a6', '#f59e0b', '#8b5cf6'],
});

const total = computed(() =>
    props.series.reduce((sum, current) => sum + current, 0),
);

const colorAt = (index: number): string => {
    return props.colors[index] ?? '#94a3b8';
};

const distributionItems = computed(() => {
    return props.labels.map((label, index) => {
        const value = props.series[index] ?? 0;
        const percentage =
            total.value > 0 ? Math.round((value / total.value) * 100) : 0;

        return {
            label,
            value,
            percentage,
        };
    });
});

const chartOptions = computed(() => {
    return {
        chart: {
            fontFamily: 'Outfit, sans-serif',
        },
        labels: props.labels,
        colors: props.colors,
        stroke: {
            show: false,
        },
        dataLabels: {
            enabled: false,
        },
        legend: {
            show: false,
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '68%',
                    labels: {
                        show: false,
                    },
                },
            },
        },
        tooltip: {
            y: {
                formatter: (value: number) => `${value} kunjungan`,
            },
        },
    };
});
</script>
