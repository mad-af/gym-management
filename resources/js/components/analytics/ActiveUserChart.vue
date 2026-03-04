<template>
    <div class="rounded-2xl border border-gray-200 bg-white p-5 md:p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-start justify-between">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                Active Users
            </h3>

            <div class="relative">
                <DropdownMenu :menu-items="menuItems">
                    <template #icon>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fillRule="evenodd" clipRule="evenodd"
                                d="M10.2441 6C10.2441 5.0335 11.0276 4.25 11.9941 4.25H12.0041C12.9706 4.25 13.7541 5.0335 13.7541 6C13.7541 6.9665 12.9706 7.75 12.0041 7.75H11.9941C11.0276 7.75 10.2441 6.9665 10.2441 6ZM10.2441 18C10.2441 17.0335 11.0276 16.25 11.9941 16.25H12.0041C12.9706 16.25 13.7541 17.0335 13.7541 18C13.7541 18.9665 12.9706 19.75 12.0041 19.75H11.9941C11.0276 19.75 10.2441 18.9665 10.2441 18ZM11.9941 10.25C11.0276 10.25 10.2441 11.0335 10.2441 12C10.2441 12.9665 11.0276 13.75 11.9941 13.75H12.0041C12.9706 13.75 13.7541 12.9665 13.7541 12C13.7541 11.0335 12.9706 10.25 12.0041 10.25H11.9941Z"
                                fill="currentColor" />
                        </svg>
                    </template>
                </DropdownMenu>
            </div>
        </div>

        <div class="mt-6 flex items-end gap-1.5">
            <div class="relative flex items-center gap-2.5">
                <span class="relative inline-block h-5 w-5">
                    <span
                        class="absolute top-1/2 left-1/2 h-2 w-2 -translate-x-1/2 -translate-y-1/2 transform rounded-full bg-error-500">
                        <span
                            class="absolute -top-1 -left-1 inline-flex h-4 w-4 animate-ping rounded-full bg-error-400 opacity-75"></span>
                    </span>
                </span>

                <span class="activeUsers text-title-sm font-semibold text-gray-800 dark:text-white/90">
                    364
                </span>
            </div>
            <span class="mb-1 block text-theme-sm text-gray-500 dark:text-gray-400">
                Live visitors
            </span>
        </div>

        <div class="my-5 min-h-[155px] rounded-xl bg-gray-50 dark:bg-gray-900">
            <div id="chartFive" class="-mr-2.5 -ml-[22px] h-full">
                <VueApexCharts type="area" height="140" :options="chartOptions" :series="series"></VueApexCharts>
            </div>
        </div>

        <div class="flex items-center justify-center gap-6">
            <div>
                <p class="text-center text-lg font-semibold text-gray-800 dark:text-white/90">
                    224
                </p>
                <p class="mt-0.5 text-center text-theme-xs text-gray-500 dark:text-gray-400">
                    Avg, Daily
                </p>
            </div>

            <div class="h-11 w-px bg-gray-200 dark:bg-gray-800"></div>

            <div>
                <p class="text-center text-lg font-semibold text-gray-800 dark:text-white/90">
                    1.4K
                </p>
                <p class="mt-0.5 text-center text-theme-xs text-gray-500 dark:text-gray-400">
                    Avg, Weekly
                </p>
            </div>

            <div class="h-11 w-px bg-gray-200 dark:bg-gray-800"></div>

            <div>
                <p class="text-center text-lg font-semibold text-gray-800 dark:text-white/90">
                    22.1K
                </p>
                <p class="mt-0.5 text-center text-theme-xs text-gray-500 dark:text-gray-400">
                    Avg, Monthly
                </p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import DropdownMenu from '../common/DropdownMenu.vue';
const menuItems = [
    { label: 'View More', onClick: () => console.log('View More clicked') },
    { label: 'Delete', onClick: () => console.log('Delete clicked') },
];

const activeUsers = ref(0);
const series = ref([
    {
        name: 'Sales',
        data: [180, 181, 182, 184, 183, 182, 181, 182, 183, 185, 186, 183],
    },
]);

const chartOptions = ref({
    chart: {
        fontFamily: 'Outfit, sans-serif',
        type: 'area',
        height: 140,
        toolbar: {
            show: false,
        },
    },
    colors: ['#465FFF'],
    fill: {
        gradient: {
            enabled: true,
            opacityFrom: 0.55,
            opacityTo: 0,
        },
    },
    stroke: {
        curve: 'smooth',
        width: 2,
    },
    markers: {
        size: 0,
    },
    grid: {
        xaxis: {
            lines: {
                show: false,
            },
        },
        yaxis: {
            lines: {
                show: false,
            },
        },
    },
    dataLabels: {
        enabled: false,
    },
    tooltip: {
        x: {
            format: 'dd MMM yyyy',
        },
    },
    xaxis: {
        type: 'category',
        categories: [
            'Jan',
            'Feb',
            'Mar',
            'Apr',
            'May',
            'Jun',
            'Jul',
            'Aug',
            'Sep',
            'Oct',
            'Nov',
            'Dec',
        ],
        axisBorder: {
            show: false,
        },
        axisTicks: {
            show: false,
        },
        labels: {
            show: false,
        },
        tooltip: {
            enabled: false,
        },
    },
    yaxis: {
        labels: {
            show: false,
        },
    },
    legend: {
        show: false,
    },
    responsive: [
        {
            breakpoint: 1024,
            options: {
                chart: {
                    height: 300,
                },
            },
        },
        {
            breakpoint: 1366,
            options: {
                chart: {
                    height: 320,
                },
            },
        },
    ],
});

let updateInterval;

onMounted(() => {
    // Start the interval for real-time updates
    updateInterval = setInterval(updateChartData, 2000);
});

onUnmounted(() => {
    // Clear the interval when the component is unmounted
    clearInterval(updateInterval);
});

function updateChartData() {
    // Generate new random data
    const newData = Array.from(
        { length: 12 },
        () => Math.floor(Math.random() * 50) + 150,
    );

    // Update the chart data
    series.value = [
        {
            name: 'Sales',
            data: newData,
        },
    ];

    // Update the active users count (random value for demo purposes)
    activeUsers.value = Math.floor(Math.random() * 500) + 100;
}
</script>

<style></style>
