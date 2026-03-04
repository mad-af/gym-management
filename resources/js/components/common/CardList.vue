<template>
  <ComponentCard :title="title" :desc="desc" :className="className">
    <template v-if="$slots.actions" #actions>
      <slot name="actions" />
    </template>
    <ul class="divide-y divide-gray-200 dark:divide-gray-800" :class="listClass">
      <slot />
    </ul>
  </ComponentCard>
</template>

<script setup lang="ts">
import ComponentCard from '@/components/common/ComponentCard.vue';

interface Props {
  title: string;
  desc?: string;
  className?: string;
  listClass?: string;
}

defineProps<Props>();
</script>
