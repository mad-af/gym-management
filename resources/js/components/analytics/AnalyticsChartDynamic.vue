<template>
  <div
    class="rounded-2xl border border-gray-200 bg-white px-5 pt-5 sm:px-6 sm:pt-6 dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="flex flex-wrap items-start justify-between gap-5">
      <div>
        <h3 class="mb-1 text-lg font-semibold text-gray-800 dark:text-white/90">
          {{ title }}
        </h3>
        <span class="block text-theme-sm text-gray-500 dark:text-gray-400">
          {{ subtitle }}
        </span>
      </div>

      <div v-if="timeOptions.length" class="flex items-center gap-0.5 rounded-lg bg-gray-100 p-0.5 dark:bg-gray-900">
        <button v-for="option in timeOptions" :key="option.value" @click="onSelectTime(option.value)" :class="[
          'rounded-md px-3 py-2 text-theme-sm font-medium',
          selectedTime === option.value
            ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-gray-800 dark:text-white'
            : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white',
        ]">
          {{ option.label }}
        </button>
      </div>
    </div>

    <div class="mt-4">
      <VueApexCharts width="100%" :height="height" :type="type" :options="computedOptions" :series="series" />
    </div>
  </div>
</template>

<script setup lang="ts">
import type { ApexOptions } from 'apexcharts';
import { computed, ref, watch } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

interface TimeOption {
  value: string;
  label: string;
}

interface AnalyticsChartDynamicProps {
  title?: string;
  subtitle?: string;
  series: any[];
  categories: string[];
  colors?: string[];
  height?: number;
  type?: 'bar' | 'line' | 'area';
  timeOptions?: TimeOption[];
  modelValue?: string | null;
}

const props = withDefaults(defineProps<AnalyticsChartDynamicProps>(), {
  title: 'Analytics',
  subtitle: 'Visitor analytics of last 30 days',
  colors: () => ['#465fff'],
  height: 280,
  type: 'bar',
  timeOptions: () => [],
  modelValue: null,
});

const emit = defineEmits<{
  (e: 'update:modelValue', value: string | null): void;
  (e: 'time-change', value: string | null): void;
}>();

const selectedTime = ref<string | null>(
  props.modelValue ?? (props.timeOptions[0]?.value ?? null),
);

watch(
  () => props.modelValue,
  (val) => {
    if (val !== null && val !== selectedTime.value) {
      selectedTime.value = val;
    }
  },
);

const timeOptions = computed(() => props.timeOptions);

const title = computed(() => props.title);
const subtitle = computed(() => props.subtitle);
const height = computed(() => props.height);
const type = computed(() => props.type);
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

const computedOptions = computed<ApexOptions>(() => {
  const options: ApexOptions = {
    colors: props.colors,
    chart: {
      fontFamily: 'Outfit, sans-serif',
      toolbar: {
        show: false,
      },
    },
    plotOptions: {
      bar: {
        horizontal: false,
        columnWidth: '45%',
        borderRadius: 5,
        borderRadiusApplication: 'end',
      },
    },
    dataLabels: {
      enabled: false,
    },
    stroke: {
      show: true,
      width: 4,
      colors: ['transparent'],
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
      markers: {},
    },
    yaxis: {
      title: {
        text: undefined,
      },
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

const onSelectTime = (value: string) => {
  if (selectedTime.value === value) {
    return;
  }

  selectedTime.value = value;
  emit('update:modelValue', value);
  emit('time-change', value);
};
</script>
