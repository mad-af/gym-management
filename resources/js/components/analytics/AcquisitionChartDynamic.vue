<template>
  <div
    class="rounded-2xl border border-gray-200 bg-white px-5 pt-5 sm:px-6 sm:pt-6 dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="mb-4 flex items-center justify-between">
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

    <div class="custom-scrollbar max-w-full overflow-x-auto">
      <div id="acquisition-chart" class="-ml-5 min-w-[700px] pl-2 xl:min-w-full">
        <VueApexCharts type="bar" :height="height" :options="computedOptions" :series="series" />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import DropdownMenu from '../common/DropdownMenu.vue';

interface MenuItem {
  label: string;
  onClick?: () => void;
}

interface AcquisitionChartDynamicProps {
  title?: string;
  categories: string[];
  series: any[];
  menuItems?: MenuItem[];
  colors?: string[];
  height?: number;
  stacked?: boolean;
}

const props = withDefaults(defineProps<AcquisitionChartDynamicProps>(), {
  title: 'Acquisition Channels',
  colors: () => ['#2a31d8', '#465fff', '#7592ff', '#c2d6ff'],
  height: 315,
  stacked: true,
  menuItems: () => [],
});

const title = computed(() => props.title);
const menuItems = computed(() => props.menuItems);
const height = computed(() => props.height);
const series = computed(() => props.series);

const formatCurrencyCompact = (value: number): string => {
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
};

const formatCurrencyId = (value: unknown): string => {
  if (value === null || value === undefined || value === '') {
    return 'Rp 0';
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

const computedOptions = computed(() => {
  const options: any = {
    colors: props.colors,
    chart: {
      fontFamily: 'Outfit, sans-serif',
      type: 'bar',
      stacked: props.stacked,
      toolbar: {
        show: false,
      },
      zoom: {
        enabled: false,
      },
    },
    plotOptions: {
      bar: {
        horizontal: false,
        columnWidth: '39%',
        borderRadius: 10,
        borderRadiusApplication: 'end',
        borderRadiusWhenStacked: 'last',
      },
    },
    dataLabels: {
      enabled: false,
    },
    xaxis: {
      categories: props.categories,
      axisBorder: {
        show: false,
      },
      axisTicks: {
        show: false,
      },
    },
    legend: {
      show: true,
      position: 'top',
      horizontalAlign: 'left',
      fontFamily: 'Outfit',
      fontSize: '14px',
      fontWeight: 400,
      itemMargin: {
        horizontal: 10,
        vertical: 0,
      },
    },
    yaxis: {
      title: false,
      labels: {
        formatter: (val: number) => formatCurrencyCompact(val),
      },
    },
    grid: {
      yaxis: {
        lines: {
          show: true,
        },
      },
    },
    fill: {
      opacity: 1,
    },
    tooltip: {
      x: {
        show: false,
      },
      y: {
        formatter: (val: number) => formatCurrencyId(val),
      },
    },
  };

  return options;
});
</script>
