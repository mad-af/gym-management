<template>
    <div class="space-y-1.5">
        <label v-if="label" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            {{ label }}
            <span v-if="required" class="text-error-500">*</span>
        </label>
        <div class="relative">
            <span
                v-if="prefix"
                class="absolute inset-y-0 left-0 flex items-center pl-4 text-sm text-gray-500 dark:text-gray-400"
            >
                {{ prefix }}
            </span>
            <input
                :type="type || 'text'"
                :value="inputValue"
                :min="min"
                :step="step"
                :placeholder="placeholder"
                :disabled="disabled"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                :class="{
                    'pl-11': !!prefix,
                    'pr-11': !!suffix,
                }"
                @input="handleInput"
                @change="handleChange"
            />
            <span
                v-if="suffix"
                class="absolute inset-y-0 right-0 flex items-center pr-4 text-sm text-gray-500 dark:text-gray-400"
            >
                {{ suffix }}
            </span>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

type Primitive = string | number | null;

const props = defineProps<{
    modelValue: Primitive;
    type?: string;
    label?: string;
    placeholder?: string;
    required?: boolean;
    prefix?: string;
    suffix?: string;
    min?: number | string;
    step?: number | string;
    disabled?: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: Primitive): void;
    (e: 'change', value: Primitive): void;
}>();

const inputValue = computed(() => {
    if (props.modelValue === null || typeof props.modelValue === 'undefined') {
        return '';
    }

    return String(props.modelValue);
});

const handleInput = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const raw = target.value;

    if (props.type === 'number') {
        const value = raw === '' ? null : Number(raw);
        emit('update:modelValue', value);
        return;
    }

    emit('update:modelValue', raw);
};

const handleChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const raw = target.value;

    if (props.type === 'number') {
        const value = raw === '' ? null : Number(raw);
        emit('change', value);
        return;
    }

    emit('change', raw);
};
</script>

