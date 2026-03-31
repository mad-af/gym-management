<template>
  <div class="relative" ref="target">
    <label v-if="label" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
      {{ label }}
    </label>

    <div class="relative">
      <input type="text" :value="displayValue" @input="handleInput" @focus="open" @click="open"
        :placeholder="placeholder" :disabled="disabled"
        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 pr-10 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 disabled:cursor-not-allowed disabled:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:focus:border-brand-500"
        :class="{ 'cursor-pointer': !searchable }" :readonly="!searchable" />

      <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
        <ChevronDownIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
      </div>
    </div>

    <transition enter-active-class="transition duration-100 ease-out" enter-from-class="transform scale-95 opacity-0"
      enter-to-class="transform scale-100 opacity-100" leave-active-class="transition duration-75 ease-in"
      leave-from-class="transform scale-100 opacity-100" leave-to-class="transform scale-95 opacity-0">
      <div v-if="isOpen"
        class="absolute z-[9999] mt-1 w-full overflow-hidden rounded-lg bg-white text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm dark:bg-gray-800 dark:ring-gray-700">
        <div ref="listRef" class="max-h-60 overflow-auto py-1">
          <ul v-if="filteredOptions.length > 0">
            <li v-for="(option, index) in filteredOptions" :key="index" @click="selectOption(option)"
              class="relative cursor-default select-none py-2 pl-10 pr-4 text-gray-900 hover:bg-brand-50 hover:text-brand-600 dark:text-gray-200 dark:hover:bg-gray-700/50 dark:hover:text-brand-400"
              :class="{ 'bg-brand-50 text-brand-600 dark:bg-gray-700/50 dark:text-brand-400': isSelected(option) }">
              <span class="block truncate"
                :class="{ 'font-medium': isSelected(option), 'font-normal': !isSelected(option) }">
                {{ getOptionLabel(option) }}
              </span>
              <span v-if="getOptionDescription(option)"
                class="block truncate text-xs text-gray-500 dark:text-gray-400"
                :class="{ 'text-brand-500 dark:text-brand-400': isSelected(option) }">
                {{ getOptionDescription(option) }}
              </span>
              <span v-if="isSelected(option)"
                class="absolute inset-y-0 left-0 flex items-center pl-3 text-brand-600 dark:text-brand-400">
                <CheckIcon class="h-5 w-5" aria-hidden="true" />
              </span>
            </li>
          </ul>
          <div v-else class="px-4 py-2 text-gray-500 dark:text-gray-400">
            <span v-if="loading">Loading...</span>
            <span v-else>Tidak ada hasil ditemukan.</span>
          </div>
          <div v-if="filteredOptions.length > 0 && loading" class="py-2 text-center text-gray-500 dark:text-gray-400">
            Loading...
          </div>
        </div>

        <div v-if="actionText" class="border-t border-gray-200 p-2 dark:border-gray-700">
          <button type="button"
            class="mx-auto block text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300 disabled:cursor-not-allowed disabled:opacity-50"
            :disabled="actionDisabled" @click="handleAction">
            {{ actionText }}
          </button>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { onClickOutside } from '@vueuse/core'
import { ChevronDown as ChevronDownIcon, Check as CheckIcon } from 'lucide-vue-next'

interface Option {
  [key: string]: any
}

interface Props {
  modelValue?: string | number | object | boolean | null
  options?: (string | Option)[]
  label?: string
  placeholder?: string
  disabled?: boolean
  searchable?: boolean
  valueKey?: string
  labelKey?: string
  descriptionKey?: string
  remote?: boolean
  loading?: boolean
  actionText?: string
  actionDisabled?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  options: () => [],
  placeholder: 'Pilih opsi...',
  disabled: false,
  searchable: true,
  valueKey: 'value',
  labelKey: 'label',
  descriptionKey: '',
  remote: false,
  loading: false,
  actionText: '',
  actionDisabled: false,
})

const emit = defineEmits(['update:modelValue', 'change', 'search', 'load-more', 'action'])

const isOpen = ref(false)
const searchQuery = ref('')
const target = ref(null)
const listRef = ref<HTMLElement | null>(null)

// Debounce search emit
import { useDebounceFn } from '@vueuse/core'
const emitSearch = useDebounceFn((query: string) => {
  emit('search', query)
}, 300)

const getOptionLabel = (option: string | Option | null | undefined): string => {
  if (option === null || option === undefined) return ''
  if (typeof option === 'string') return option
  return option[props.labelKey]
}

const getOptionDescription = (option: string | Option | null | undefined): string => {
  if (option === null || option === undefined) return ''
  if (typeof option === 'string') return ''
  if (!props.descriptionKey) return ''
  return option[props.descriptionKey] || ''
}

const getOptionValue = (option: string | Option | null | undefined): any => {
  if (option === null || option === undefined) return null
  if (typeof option === 'string') return option
  return option[props.valueKey]
}

const displayValue = computed(() => {
  if (isOpen.value && props.searchable) return searchQuery.value
  if (props.modelValue === null || props.modelValue === undefined || props.modelValue === '') return ''

  // If remote, try to find in options first, but it might not be there if it's paginated.
  // However, usually we should have the selected option in the list or passed separately.
  // For now, let's assume it's in the list or we just show the modelValue if not found? 
  // Actually, if it's remote, we might need a way to display the label even if it's not in options.
  // But let's stick to current logic: find in options.
  const selected = props.options.find(opt => {
    const val = getOptionValue(opt)
    const modelVal = typeof props.modelValue === 'object' ? getOptionValue(props.modelValue as Option) : props.modelValue
    return val === modelVal
  })

  return selected ? getOptionLabel(selected) : ''
})

const filteredOptions = computed(() => {
  if (props.remote) return props.options
  if (!props.searchable || !searchQuery.value) return props.options

  const query = searchQuery.value.toLowerCase()
  return props.options.filter(option =>
    getOptionLabel(option).toLowerCase().includes(query)
  )
})

const isSelected = (option: string | Option) => {
  const val = getOptionValue(option)
  const modelVal = typeof props.modelValue === 'object' ? getOptionValue(props.modelValue as Option) : props.modelValue
  return val === modelVal
}

const open = () => {
  if (props.disabled) return
  isOpen.value = true
  if (props.searchable && displayValue.value) {
    // If remote, we might want to trigger a search or load initial options if empty?
    // But usually options are passed from parent.
    searchQuery.value = ''
  }
}

const close = () => {
  isOpen.value = false
  searchQuery.value = ''
}

const selectOption = (option: string | Option) => {
  const value = getOptionValue(option)
  const modelVal = typeof props.modelValue === 'object' ? getOptionValue(props.modelValue as Option) : props.modelValue
  const nextValue = value === modelVal ? null : value
  emit('update:modelValue', nextValue)
  emit('change', nextValue)
  close()
}

const handleInput = (event: Event) => {
  if (!props.searchable) return
  searchQuery.value = (event.target as HTMLInputElement).value
  if (!isOpen.value) isOpen.value = true

  if (props.remote) {
    emitSearch(searchQuery.value)
  }
}

const handleAction = () => {
  if (props.actionDisabled) return
  emit('action')
  close()
}

onClickOutside(target, close)

import { useInfiniteScroll } from '@vueuse/core'

useInfiniteScroll(listRef, () => {
  if (props.remote && !props.loading) {
    emit('load-more')
  }
}, { distance: 10 })

watch(() => props.modelValue, () => {
  // Reset search query when value changes externally
  // searchQuery.value = ''
})
</script>
