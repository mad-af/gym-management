<template>
  <button
    type="button"
    :class="[
      'group relative w-full overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 text-left transition hover:border-gray-300 hover:shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:hover:border-gray-700',
    ]"
    @click="onClick"
  >
    <div class="flex items-start justify-between gap-3">
      <div :class="['flex h-12 w-12 items-center justify-center rounded-xl', iconBgClass]">
        <component :is="icon" class="h-6 w-6" />
      </div>
      <ArrowRightIcon
        class="h-5 w-5 text-gray-300 transition group-hover:text-gray-400 dark:text-gray-600 dark:group-hover:text-gray-500"
      />
    </div>
    <div class="mt-4">
      <h3 class="text-base font-semibold text-gray-900 dark:text-white">
        {{ title }}
      </h3>
      <p v-if="description" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        {{ description }}
      </p>
    </div>
    <div class="pointer-events-none absolute -right-10 -top-10 h-28 w-28 rounded-full opacity-25 blur-2xl" :class="glowClass" />
  </button>
</template>

<script setup lang="ts">
import { ArrowRightIcon } from '@/icons';
import type { Component } from 'vue';

interface Props {
  icon: Component;
  title: string;
  description?: string;
  iconBgClass?: string;
  glowClass?: string;
}

const props = defineProps<Props>();
const emit = defineEmits<{ (e: 'click'): void }>();
const onClick = () => emit('click');
</script>
