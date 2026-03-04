<template>
  <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-6 xl:grid-cols-4">
    <div
      v-for="(item, index) in items"
      :key="index"
      class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-white/3"
    >
      <p class="text-theme-sm text-gray-500 dark:text-gray-400">
        {{ item.label }}
      </p>

      <div class="mt-3 flex items-end justify-between">
        <div>
          <h4 class="text-2xl font-bold text-gray-800 dark:text-white/90">
            {{ item.value }}<span v-if="item.suffix">{{ item.suffix }}</span>
          </h4>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface StatItem {
  label: string;
  value: string | number;
  suffix?: string;
}

interface StatsProps {
  items: StatItem[];
}

defineProps<StatsProps>();
</script>

