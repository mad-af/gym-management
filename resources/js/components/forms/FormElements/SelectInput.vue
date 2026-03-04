<template>
    <div class="space-y-1.5">
        <label v-if="label" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            {{ label }}
            <span v-if="required" class="text-error-500">*</span>
        </label>
        <div class="relative z-20 bg-transparent">
            <select :value="modelValue ?? ''" :disabled="disabled"
                class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                :class="{
                    'text-gray-800 dark:text-white/90': modelValue !== null && modelValue !== '',
                }" @change="handleChange">
                <option v-if="placeholder" value="" disabled>
                    {{ placeholder }}
                </option>
                <option v-for="option in options" :key="getOptionValue(option)" :value="String(getOptionValue(option))"
                    class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                    {{ getOptionLabel(option) }}
                </option>
            </select>
            <span
                class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-700 dark:text-gray-400">
                <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5"
                        stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </span>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

type Primitive = string | number;

interface Option {
    value: Primitive;
    label: string;
}

const props = defineProps<{
    modelValue: Primitive | '' | null;
    options: Option[] | any[];
    label?: string;
    placeholder?: string;
    disabled?: boolean;
    valueKey?: string;
    labelKey?: string;
    required?: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: Primitive | '' | null): void;
    (e: 'change', value: Primitive | '' | null): void;
}>();

const valueKey = computed(() => props.valueKey ?? 'value');
const labelKey = computed(() => props.labelKey ?? 'label');

const getOptionValue = (option: any): Primitive => {
    if (option && typeof option === 'object') {
        return option[valueKey.value] as Primitive;
    }
    return option as Primitive;
};

const getOptionLabel = (option: any): string => {
    if (option && typeof option === 'object') {
        return String(option[labelKey.value] ?? '');
    }
    return String(option ?? '');
};

const handleChange = (event: Event) => {
    const target = event.target as HTMLSelectElement;
    const raw = target.value;

    if (raw === '') {
        emit('update:modelValue', '');
        emit('change', '');
        return;
    }

    const matched = (props.options || []).find((option: any) => {
        return String(getOptionValue(option)) === raw;
    });

    const value = matched ? getOptionValue(matched) : (raw as Primitive);

    emit('update:modelValue', value);
    emit('change', value);
};
</script>
