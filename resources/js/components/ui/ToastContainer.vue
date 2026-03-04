<template>
  <div
    class="fixed bottom-4 left-1/2 -translate-x-1/2 z-999999 flex flex-col gap-3 w-full max-w-md pointer-events-none">
    <TransitionGroup enter-active-class="transition duration-300 ease-out" enter-from-class="translate-y-full opacity-0"
      enter-to-class="translate-y-0 opacity-100" leave-active-class="transition duration-200 ease-in"
      leave-from-class="translate-y-0 opacity-100" leave-to-class="translate-y-full opacity-0">
      <div v-for="toast in toasts" :key="toast.id" class="pointer-events-auto shadow-lg rounded-xl overflow-hidden">
        <div class="relative">
          <Alert :variant="toast.type" :title="getTitle(toast.type)" :message="toast.message" />
          <button @click="removeToast(toast.id)"
            class="absolute top-2 right-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1 rounded-full hover:bg-black/5 dark:hover:bg-white/10 transition-colors"
            aria-label="Close">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd"
                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                clip-rule="evenodd" />
            </svg>
          </button>
        </div>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup lang="ts">
import { useToast } from '@/composables/useToast';
import Alert from '@/components/ui/Alert.vue';

const { toasts, removeToast } = useToast();

const getTitle = (type: string) => {
  switch (type) {
    case 'success':
      return 'Success';
    case 'error':
      return 'Error';
    case 'warning':
      return 'Warning';
    case 'info':
      return 'Information';
    default:
      return 'Notification';
  }
};
</script>
