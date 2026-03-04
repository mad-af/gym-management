<template>
  <div class="flex flex-col items-center gap-3">
    <div class="relative inline-block" :class="sizeClasses[size]">
      <div v-if="hasImage" class="h-full w-full">
        <AppImage :src="currentSrc" :placeholder="currentPlaceholder" :alt="alt" :containerClass="avatarContainerClass"
          :imgClass="avatarImgClass" />
      </div>
      <div v-else
        class="flex h-full w-full items-center justify-center bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500"
        :class="shapeClass">
        <UserCircleIcon v-if="variant === 'circle'" class="h-1/2 w-1/2" />
        <ImageIcon v-else class="h-1/2 w-1/2" />
      </div>
      <button type="button"
        class="absolute bottom-0 right-0 flex h-8 w-8 items-center justify-center rounded-full bg-brand-500 text-white shadow-theme-md hover:bg-brand-600 focus:outline-hidden focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900"
        @click="triggerFileInput">
        <PencilIcon class="h-4 w-4" />
      </button>
    </div>
    <input ref="fileInputRef" type="file" accept="image/*" class="hidden" @change="onFileChange" />
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import AppImage from '@/components/common/AppImage.vue';
import { PencilIcon, UserCircleIcon, ImageIcon } from '@/icons';

type AvatarFile = File | null;

const props = withDefaults(defineProps<{
  modelValue?: AvatarFile;
  src?: string | null;
  placeholder?: string;
  size?: 'small' | 'medium' | 'large';
  alt?: string;
  variant?: 'circle' | 'square';
}>(), {
  modelValue: null,
  src: null,
  placeholder: '',
  size: 'large',
  alt: 'Avatar',
  variant: 'circle',
});

const emit = defineEmits<{
  (e: 'update:modelValue', value: AvatarFile): void;
  (e: 'change', file: AvatarFile): void;
}>();

const fileInputRef = ref<HTMLInputElement | null>(null);
const previewUrl = ref<string | null>(null);

const hasImage = computed(() => {
  if (previewUrl.value) {
    return true;
  }

  return !!(props.src && props.src !== '');
});

const currentSrc = computed(() => {
  if (previewUrl.value) {
    return previewUrl.value;
  }

  if (props.src && props.src !== '') {
    return props.src;
  }

  return '';
});

const currentPlaceholder = computed(() => {
  if (previewUrl.value) {
    return '';
  }

  return props.placeholder || '';
});

const sizeClasses: Record<'small' | 'medium' | 'large', string> = {
  small: 'h-16 w-16',
  medium: 'h-20 w-20',
  large: 'h-24 w-24',
};

const shapeClasses: Record<'circle' | 'square', string> = {
  circle: 'rounded-full',
  square: 'rounded-lg',
};

const avatarContainerClass = computed(() => `h-full w-full ${shapeClasses[props.variant]}`);
const avatarImgClass = computed(() => shapeClasses[props.variant]);
const shapeClass = computed(() => shapeClasses[props.variant]);

const triggerFileInput = () => {
  if (fileInputRef.value) {
    fileInputRef.value.click();
  }
};

const onFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const file = target.files && target.files[0] ? target.files[0] : null;

  if (previewUrl.value) {
    URL.revokeObjectURL(previewUrl.value);
    previewUrl.value = null;
  }

  if (file) {
    previewUrl.value = URL.createObjectURL(file);
  }

  emit('update:modelValue', file);
  emit('change', file);
};
</script>
