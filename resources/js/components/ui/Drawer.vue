<template>
  <Teleport to="body">
    <div v-if="isOpen" class="relative z-99999" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
      <!-- Backdrop -->
      <transition enter-active-class="ease-in-out duration-500" enter-from-class="opacity-0"
        enter-to-class="opacity-100" leave-active-class="ease-in-out duration-500" leave-from-class="opacity-100"
        leave-to-class="opacity-0">
        <div v-if="isOpen" class="fixed inset-0 bg-gray-500/75 backdrop-blur-sm transition-opacity" @click="close">
        </div>
      </transition>

      <div class="fixed inset-0 overflow-hidden">
        <div class="absolute inset-0 overflow-hidden">
          <div class="pointer-events-none fixed inset-y-0 flex max-w-full" :class="placementClass">
            <transition :enter-active-class="`transform transition ease-in-out duration-500 sm:duration-700`"
              :enter-from-class="translateEnterClass" :enter-to-class="`translate-x-0`"
              :leave-active-class="`transform transition ease-in-out duration-500 sm:duration-700`"
              :leave-from-class="`translate-x-0`" :leave-to-class="translateLeaveClass">
              <div v-if="isOpen" class="pointer-events-auto relative w-screen" :class="sizeClass">

                <div class="flex h-full flex-col overflow-y-scroll bg-white shadow-xl dark:bg-gray-800">
                  <!-- Header -->
                  <div class="px-4 py-6 sm:px-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-start justify-between">
                      <h2 class="text-base font-semibold leading-6 text-gray-900 dark:text-white" id="slide-over-title">
                        <slot name="header">{{ title }}</slot>
                      </h2>
                      <div class="ml-3 flex h-7 items-center">
                        <button type="button"
                          class="relative rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 dark:bg-gray-800 dark:text-gray-400 dark:hover:text-gray-300"
                          @click="close">
                          <span class="absolute -inset-2.5"></span>
                          <span class="sr-only">Close panel</span>
                          <XIcon class="h-6 w-6" aria-hidden="true" />
                        </button>
                      </div>
                    </div>
                  </div>

                  <!-- Body -->
                  <div class="relative my-6 flex-1 px-4 sm:px-6">
                    <slot></slot>
                  </div>

                  <!-- Footer -->
                  <div v-if="$slots.footer"
                    class="flex shrink-0 justify-end px-4 py-4 border-t border-gray-200 dark:border-gray-700">
                    <slot name="footer"></slot>
                  </div>
                </div>
              </div>
            </transition>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { computed, watch } from 'vue'
import { X as XIcon } from 'lucide-vue-next'
import { useScrollLock } from '@vueuse/core'

interface Props {
  isOpen: boolean
  title?: string
  placement?: 'left' | 'right'
  size?: 'sm' | 'md' | 'lg' | 'xl' | '2xl' | 'full'
}

const props = withDefaults(defineProps<Props>(), {
  isOpen: false,
  title: 'Drawer',
  placement: 'right',
  size: 'md',
})

const emit = defineEmits(['close'])

const isLocked = useScrollLock(document.body)

watch(() => props.isOpen, (val) => {
  isLocked.value = val
})

const close = () => {
  emit('close')
}

const placementClass = computed(() => {
  return props.placement === 'left' ? 'left-0 pr-10' : 'right-0 pl-10'
})

const translateEnterClass = computed(() => {
  return props.placement === 'left' ? '-translate-x-full' : 'translate-x-full'
})

const translateLeaveClass = computed(() => {
  return props.placement === 'left' ? '-translate-x-full' : 'translate-x-full'
})

const sizeClass = computed(() => {
  switch (props.size) {
    case 'sm': return 'max-w-md'
    case 'md': return 'max-w-lg'
    case 'lg': return 'max-w-2xl'
    case 'xl': return 'max-w-4xl'
    case '2xl': return 'max-w-5xl'
    case 'full': return 'max-w-full'
    default: return 'max-w-md'
  }
})
</script>
