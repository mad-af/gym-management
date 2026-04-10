<template>
  <div class="custom-scrollbar mb-6 max-w-full overflow-x-auto pb-1">
    <div class="flex gap-4 md:gap-6">
      <div
        v-for="(item, index) in items"
        :key="index"
        :ref="(el) => setTriggerRef(index, el)"
        :class="[
          cardClass,
          'rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-white/3',
          'cursor-pointer'
        ]"
        @mouseenter="showTooltip(index)"
        @mouseleave="hideTooltip"
      >
        <div class="group relative flex items-center gap-4">
          <div :class="[
            'flex h-12 w-12 items-center justify-center rounded-xl',
            item.iconBgClass || 'bg-brand-50 text-brand-500 dark:bg-brand-500/10',
          ]">
            <component :is="item.icon" class="h-6 w-6" />
          </div>

          <div class="flex-1">
            <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90 whitespace-nowrap">
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

  <Teleport to="body">
    <div
      v-if="activeTooltip !== null"
      class="fixed z-[9999] bg-gray-800 p-2 text-sm text-white rounded-md pointer-events-none"
      :style="tooltipStyle"
    >
      {{ tooltipContent }}
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import type { Component, ComponentPublicInstance } from 'vue';

interface Stats2Item {
  label: string;
  value: string | number;
  suffix?: string;
  icon: Component;
  iconBgClass?: string;
  detail?: string;
}

interface Stats2Props {
  items: Stats2Item[];
}

const props = defineProps<Stats2Props>();

const activeTooltip = ref<number | null>(null);
const triggerMap = ref<Map<number, HTMLElement>>(new Map());

const setTriggerRef = (index: number, el: Element | ComponentPublicInstance | null) => {
  if (el) {
    const element = (el as ComponentPublicInstance).$el ?? el as HTMLElement;
    triggerMap.value.set(index, element);
  }
};

const tooltipStyle = computed(() => {
  if (activeTooltip.value === null) return {};

  const trigger = triggerMap.value.get(activeTooltip.value);
  if (!trigger) return {};

  const rect = trigger.getBoundingClientRect();

  return {
    left: `${rect.left + rect.width / 2}px`,
    top: `${rect.top - 8}px`,
    transform: 'translate(-50%, -100%)',
  };
});

const tooltipContent = computed(() => {
  if (activeTooltip.value === null) return '';
  const item = props.items[activeTooltip.value];
  return item.detail ?? item.label;
});

const showTooltip = (index: number) => {
  activeTooltip.value = index;
};

const hideTooltip = () => {
  activeTooltip.value = null;
};

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
