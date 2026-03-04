<template>
  <div class="relative" ref="target">
    <label v-if="label" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
      {{ label }}
    </label>

    <div class="relative">
      <input
        type="text"
        :value="displayValue"
        @input="handleInput"
        @focus="open"
        @click="open"
        :placeholder="placeholder"
        :disabled="disabled"
        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 pr-10 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 disabled:cursor-not-allowed disabled:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:focus:border-brand-500"
        :class="{ 'cursor-pointer': !searchable }"
        :readonly="!searchable"
      />

      <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
        <ChevronDownIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
      </div>
    </div>

    <transition
      enter-active-class="transition duration-100 ease-out"
      enter-from-class="transform scale-95 opacity-0"
      enter-to-class="transform scale-100 opacity-100"
      leave-active-class="transition duration-75 ease-in"
      leave-from-class="transform scale-100 opacity-100"
      leave-to-class="transform scale-95 opacity-0"
    >
      <ul
        v-if="isOpen && filteredOptions.length > 0"
        ref="listRef"
        class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-lg bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm dark:bg-gray-800 dark:ring-gray-700"
      >
        <li
          v-for="(option, index) in filteredOptions"
          :key="index"
          @click="toggleOption(option)"
          class="relative cursor-default select-none py-2 pl-10 pr-4 text-gray-900 hover:bg-brand-50 hover:text-brand-600 dark:text-gray-200 dark:hover:bg-gray-700/50 dark:hover:text-brand-400"
          :class="{ 'bg-brand-50 text-brand-600 dark:bg-gray-700/50 dark:text-brand-400': isSelected(option) }"
        >
          <span
            class="block truncate"
            :class="{ 'font-medium': isSelected(option), 'font-normal': !isSelected(option) }"
          >
            {{ getOptionLabel(option) }}
          </span>
          <span
            v-if="isSelected(option)"
            class="absolute inset-y-0 left-0 flex items-center pl-3 text-brand-600 dark:text-brand-400"
          >
            <CheckIcon class="h-5 w-5" aria-hidden="true" />
          </span>
        </li>
        <li
          v-if="loading"
          class="relative cursor-default select-none py-2 text-center text-gray-500 dark:text-gray-400"
        >
          Loading...
        </li>
      </ul>
      <ul
        v-else-if="isOpen && filteredOptions.length === 0"
        class="absolute z-50 mt-1 w-full overflow-auto rounded-lg bg-white py-2 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm dark:bg-gray-800 dark:ring-gray-700"
      >
        <li
          v-if="loading"
          class="relative cursor-default select-none px-4 py-2 text-gray-500 dark:text-gray-400"
        >
          Loading...
        </li>
        <li
          v-else
          class="relative cursor-default select-none px-4 py-2 text-gray-500 dark:text-gray-400"
        >
          Tidak ada hasil ditemukan.
        </li>
      </ul>
    </transition>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { onClickOutside, useDebounceFn, useInfiniteScroll } from '@vueuse/core'
import { ChevronDown as ChevronDownIcon, Check as CheckIcon } from 'lucide-vue-next'

interface Option {
  [key: string]: any
}

interface Props {
  modelValue?: Array<string | number | boolean | null>
  options?: (string | Option)[]
  label?: string
  placeholder?: string
  disabled?: boolean
  searchable?: boolean
  valueKey?: string
  labelKey?: string
  remote?: boolean
  loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: () => [],
  options: () => [],
  placeholder: 'Pilih opsi...',
  disabled: false,
  searchable: true,
  valueKey: 'value',
  labelKey: 'label',
  remote: false,
  loading: false,
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: Array<string | number | boolean | null>): void
  (e: 'change', value: Array<string | number | boolean | null>): void
  (e: 'search', query: string): void
  (e: 'load-more'): void
}>()

const isOpen = ref(false)
const searchQuery = ref('')
const target = ref<HTMLElement | null>(null)
const listRef = ref<HTMLElement | null>(null)

const emitSearch = useDebounceFn((query: string) => {
  emit('search', query)
}, 300)

const getOptionLabel = (option: string | Option | null | undefined): string => {
  if (option === null || option === undefined) return ''
  if (typeof option === 'string') return option
  return option[props.labelKey]
}

const getOptionValue = (option: string | Option | null | undefined): any => {
  if (option === null || option === undefined) return null
  if (typeof option === 'string') return option
  return option[props.valueKey]
}

const selectedValues = computed(() => {
  if (!Array.isArray(props.modelValue)) return []
  return props.modelValue
})

const displayValue = computed(() => {
  if (isOpen.value && props.searchable) return searchQuery.value
  if (!selectedValues.value.length) return ''

  const labels: string[] = []
  for (const opt of props.options) {
    const val = getOptionValue(opt)
    if (selectedValues.value.includes(val)) {
      labels.push(getOptionLabel(opt))
    }
  }

  return labels.join(', ')
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
  return selectedValues.value.includes(val)
}

const open = () => {
  if (props.disabled) return
  isOpen.value = true
  if (props.searchable && displayValue.value) {
    searchQuery.value = ''
  }
}

const close = () => {
  isOpen.value = false
  searchQuery.value = ''
}

const toggleOption = (option: string | Option) => {
  const val = getOptionValue(option)
  const current = [...selectedValues.value]
  const index = current.indexOf(val)

  if (index === -1) {
    current.push(val)
  } else {
    current.splice(index, 1)
  }

  emit('update:modelValue', current)
  emit('change', current)
}

const handleInput = (event: Event) => {
  if (!props.searchable) return
  searchQuery.value = (event.target as HTMLInputElement).value
  if (!isOpen.value) isOpen.value = true

  if (props.remote) {
    emitSearch(searchQuery.value)
  }
}

onClickOutside(target, close)

useInfiniteScroll(
  listRef,
  () => {
    if (props.remote && !props.loading) {
      emit('load-more')
    }
  },
  { distance: 10 },
)
</script>

