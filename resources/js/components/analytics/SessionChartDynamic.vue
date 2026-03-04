<template>
  <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6 dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="mb-9 flex items-center justify-between">
      <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
        {{ title }}
      </h3>

      <div v-if="menuItems && menuItems.length" class="relative">
        <DropdownMenu :menu-items="menuItems">
          <template #icon>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd" clip-rule="evenodd"
                d="M10.2441 6C10.2441 5.0335 11.0276 4.25 11.9941 4.25H12.0041C12.9706 4.25 13.7541 5.0335 13.7541 6C13.7541 6.9665 12.9706 7.75 12.0041 7.75H11.9941C11.0276 7.75 10.2441 6.9665 10.2441 6ZM10.2441 18C10.2441 17.0335 11.0276 16.25 11.9941 16.25H12.0041C12.9706 16.25 13.7541 17.0335 13.7541 18C13.7541 18.9665 12.9706 19.75 12.0041 19.75H11.9941C11.0276 19.75 10.2441 18.9665 10.2441 18ZM11.9941 10.25C11.0276 10.25 10.2441 11.0335 10.2441 12C10.2441 12.9665 11.0276 13.75 11.9941 13.75H12.0041C12.9706 13.75 13.7541 12.9665 13.7541 12C13.7541 11.0335 12.9706 10.25 12.0041 10.25H11.9941Z"
                fill="currentColor" />
            </svg>
          </template>
        </DropdownMenu>
      </div>
    </div>

    <div>
      <div class="mx-auto flex justify-center">
        <VueApexCharts :type="type" :width="chartWidth" :height="height" :options="chartOptions" :series="series" />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import DropdownMenu from '../common/DropdownMenu.vue';

interface MenuItem {
  label: string;
  onClick?: () => void;
}

interface SessionChartDynamicProps {
  title?: string;
  labels: string[];
  series: number[];
  colors?: string[];
  height?: number;
  width?: number;
  type?: 'pie' | 'donut';
  autoResize?: boolean;
  menuItems?: MenuItem[];
}

const props = withDefaults(defineProps<SessionChartDynamicProps>(), {
  title: 'Sessions By Device',
  colors: () => ['#3641f5', '#7592ff', '#dde9ff'],
  height: 290,
  width: 445,
  type: 'donut',
  autoResize: true,
  menuItems: () => [
    { label: 'View More' },
    { label: 'Delete' },
  ],
});

const title = computed(() => props.title);
const series = computed(() => props.series);
const type = computed(() => props.type);
const height = computed(() => props.height);
const menuItems = computed(() => props.menuItems);

const chartWidth = ref<number | string>(props.width);

const updateChartSize = () => {
  if (!props.autoResize) {
    chartWidth.value = props.width;
    return;
  }

  if (window.innerWidth <= 640) {
    chartWidth.value = 370;
  } else {
    chartWidth.value = props.width;
  }
};

const chartOptions = computed(() => {
  const options: any = {
    colors: props.colors,
    labels: props.labels,
    chart: {
      fontFamily: 'Outfit, sans-serif',
      type: props.type,
    },
    plotOptions: {
      pie: {
        donut: {
          size: props.type === 'donut' ? '65%' : undefined,
          background: 'transparent',
          labels: {
            show: true,
            value: {
              show: true,
              offsetY: 0,
            },
          },
        },
      },
    },
    dataLabels: {
      enabled: false,
    },
    tooltip: {
      enabled: false,
    },
    stroke: {
      show: false,
      width: 4,
      colors: 'transparent',
    },
    legend: {
      show: true,
      position: 'bottom',
      horizontalAlign: 'center',
      fontFamily: 'Outfit',
      fontSize: '14px',
      fontWeight: 400,
      markers: {
        size: 5,
        shape: 'circle',
        radius: 999,
        strokeWidth: 0,
      },
      itemMargin: {
        horizontal: 10,
        vertical: 0,
      },
    },
  };

  return options;
});

onMounted(() => {
  updateChartSize();
  if (props.autoResize) {
    window.addEventListener('resize', updateChartSize);
  }
});

onUnmounted(() => {
  if (props.autoResize) {
    window.removeEventListener('resize', updateChartSize);
  }
});
</script>
