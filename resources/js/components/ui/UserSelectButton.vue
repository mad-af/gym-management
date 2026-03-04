<template>
  <div class="space-y-2">
    <p v-if="label" class="text-xs font-medium text-gray-700 dark:text-gray-200">
      {{ label }}
    </p>
    <button type="button"
      class="flex w-full items-center gap-3 rounded-lg border border-gray-200 bg-white px-3 py-2 text-left hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900/40 dark:hover:bg-white/[0.04]"
      @click="$emit('click')">
      <div
        class="flex h-9 w-9 items-center justify-center overflow-hidden rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
        <AppImage v-if="avatarSrc" :src="avatarSrc" :placeholder="avatarPlaceholder" :alt="name || emptyNameLabel"
          containerClass="h-9 w-9 rounded-full" imgClass="rounded-full" />
        <UserCircleIcon v-else class="h-5 w-5" />
      </div>
      <div class="min-w-0">
        <p class="truncate text-sm font-medium text-gray-800 dark:text-white/90">
          {{ displayName }}
        </p>
        <p class="truncate text-xs text-gray-500 dark:text-gray-400">
          {{ displaySubLabel }}
        </p>
      </div>
    </button>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import AppImage from '@/components/common/AppImage.vue';
import { UserCircleIcon } from '@/icons';

const props = withDefaults(defineProps<{
  label?: string;
  name?: string;
  subLabel?: string;
  emptyNameLabel?: string;
  emptySubLabel?: string;
  avatarSrc?: string;
  avatarPlaceholder?: string;
}>(), {
  label: '',
  name: '',
  subLabel: '',
  emptyNameLabel: 'Belum dipilih',
  emptySubLabel: 'Klik untuk memilih',
  avatarSrc: '',
  avatarPlaceholder: '',
});

defineEmits<{
  (e: 'click'): void;
}>();

const displayName = computed(() => {
  if (props.name && props.name !== '') {
    return props.name;
  }

  return props.emptyNameLabel;
});

const displaySubLabel = computed(() => {
  if (props.subLabel && props.subLabel !== '') {
    return props.subLabel;
  }

  return props.emptySubLabel;
});
</script>
