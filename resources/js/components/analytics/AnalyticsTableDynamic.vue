<template>
  <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="mb-4 flex flex-col gap-4 px-6 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
          {{ title }}
        </h3>
        <p v-if="subtitle" class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">
          {{ subtitle }}
        </p>
      </div>

      <div v-if="showSeeAll" class="flex items-center gap-3">
        <button
          type="button"
          class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
          @click="handleSeeAll"
        >
          <span>Lihat semua</span>
        </button>
      </div>
    </div>

    <div class="max-w-full overflow-x-auto">
      <table class="min-w-full">
        <thead>
          <tr class="border-t border-gray-100 dark:border-gray-800">
            <th
              v-for="column in columns"
              :key="column.key"
              class="px-6 py-3"
            >
              <div class="flex items-center">
                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">
                  {{ column.label }}
                </p>
              </div>
            </th>
          </tr>
        </thead>
        <tbody v-if="rows.length" class="divide-y divide-gray-100 dark:divide-gray-800">
          <tr
            v-for="row in rows"
            :key="rowKey ? row[rowKey] : rowIndex(row)"
            class="cursor-pointer hover:bg-gray-50 dark:hover:bg-white/[0.02]"
            @click="handleRowClick(row)"
          >
            <td
              v-for="column in columns"
              :key="column.key"
              class="px-6 py-3.5"
            >
              <slot
                :name="`cell-${column.key}`"
                :row="row"
                :value="row[column.key]"
              >
                <div class="flex items-center">
                  <p
                    :class="[
                      'text-theme-sm',
                      column.primary ? 'font-medium text-gray-800 dark:text-white/90' : 'text-gray-500 dark:text-gray-400',
                    ]"
                  >
                    {{ row[column.key] }}
                  </p>
                </div>
              </slot>
            </td>
          </tr>
        </tbody>
        <tbody v-else>
          <tr>
            <td class="px-6 py-6 text-center text-theme-sm text-gray-500 dark:text-gray-400" :colspan="columns.length">
              Tidak ada data
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Column {
  key: string;
  label: string;
  primary?: boolean;
}

interface AnalyticsTableDynamicProps {
  title: string;
  subtitle?: string;
  columns: Column[];
  rows: any[];
  rowKey?: string;
  showSeeAll?: boolean;
  seeAllUrl?: string;
}

const props = withDefaults(defineProps<AnalyticsTableDynamicProps>(), {
  subtitle: '',
  rowKey: '',
  showSeeAll: true,
  seeAllUrl: '',
});

const emit = defineEmits<{
  (e: 'seeAll'): void;
  (e: 'rowClick', row: any): void;
}>();

const rowIndex = (row: any) => {
  return JSON.stringify(row);
};

const handleSeeAll = () => {
  if (props.seeAllUrl) {
    window.location.href = props.seeAllUrl;
    return;
  }

  emit('seeAll');
};

const handleRowClick = (row: any) => {
  emit('rowClick', row);
};
</script>

