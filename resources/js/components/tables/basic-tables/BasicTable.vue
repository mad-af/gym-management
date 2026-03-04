<template>
  <div class="overflow-hidden rounded-xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-white/3">
    <div
      ref="scrollContainer"
      class="custom-scrollbar max-w-full overflow-x-auto overflow-y-auto"
      :style="maxHeight ? { maxHeight } : undefined"
    >
      <table class="min-w-full">
        <thead class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-900">
          <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900">
            <th v-for="column in columns" :key="column.key" :class="column.widthClass || 'px-5 py-3 text-left sm:px-6'">
              <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">
                {{ column.header }}
              </p>
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
          <tr v-for="(row, rowIndex) in rows" :key="rowKey && row[rowKey] !== undefined ? row[rowKey] : rowIndex"
            class="border-t border-gray-100 dark:border-gray-800">
            <td v-for="(column, colIndex) in columns" :key="column.key" class="px-5 py-4 sm:px-6">
              <slot :name="`cell-${column.key}`" :row="row" :column="column" :value="getCellValue(row, column)"
                :rowIndex="rowIndex" :colIndex="colIndex">
                <p class="text-theme-sm text-gray-500 dark:text-gray-400">
                  {{ getCellValue(row, column) }}
                </p>
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useInfiniteScroll } from '@vueuse/core';
import { computed, ref } from 'vue';

interface DataTableColumn {
  key: string;
  header: string;
  widthClass?: string;
}

const props = withDefaults(defineProps<{
  columns: DataTableColumn[];
  rows: any[];
  rowKey?: string;
  maxHeight?: string;
  remote?: boolean;
  loading?: boolean;
}>(), {
  columns: () => [],
  rows: () => [],
  rowKey: '',
  maxHeight: '',
  remote: false,
  loading: false,
});

const emit = defineEmits<{
  (e: 'load-more'): void;
}>();

const scrollContainer = ref<HTMLElement | null>(null);

const columns = computed(() => props.columns);
const rows = computed(() => props.rows);
const rowKey = computed(() => props.rowKey);
const maxHeight = computed(() => props.maxHeight);

const getCellValue = (row: any, column: DataTableColumn): unknown => {
  if (!row || !column.key) return '';
  return (row as any)[column.key];
};

useInfiniteScroll(
  scrollContainer,
  () => {
    if (props.remote && !props.loading) {
      emit('load-more');
    }
  },
  { distance: 10 },
);
</script>
