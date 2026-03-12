<template>
  <div class="custom-scrollbar mb-6 max-w-full overflow-x-auto pb-1">
    <div class="flex gap-4 md:gap-6">
      <div v-for="(item, index) in items" :key="index" :class="[
        cardClass,
        'rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-white/3',
      ]">
        <div class="flex items-center gap-4">
          <div :class="[
            'flex h-12 w-12 items-center justify-center rounded-xl',
            item.iconBgClass || 'bg-brand-50 text-brand-500 dark:bg-brand-500/10',
          ]">
            <component :is="item.icon" class="h-6 w-6" />
          </div>

          <div class="flex-1">
            <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">
              {{ item.value }}<span v-if="item.suffix">{{ item.suffix }}</span>
            </p>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
              {{ item.label }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { Component } from 'vue';

interface Stats2Item {
  label: string;
  value: string | number;
  suffix?: string;
  icon: Component;
  iconBgClass?: string;
}

interface Stats2Props {
  items: Stats2Item[];
}

const props = defineProps<Stats2Props>();

const cardClass = computed(() => {
  const count = props.items.length;

  if (count <= 1) {
    return 'w-full';
  }

  if (count <= 3) {
    return 'min-w-[12rem] flex-1';
  }

  return 'w-64 flex-shrink-0';
});
</script>
