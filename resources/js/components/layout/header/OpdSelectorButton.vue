<template>
  <div class="relative">
    <button type="button"
      class="hover:text-dark-900 relative flex h-11 w-11 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
      :title="selectedLabel" @click="isDrawerOpen = true">
      <span class="text-[11px] font-semibold uppercase tracking-wide">
        OPD
      </span>
      <span v-if="internalValue !== ''"
        class="pointer-events-none absolute inset-0.5 rounded-full border-2 border-brand-500/70 dark:border-brand-400/80" />
    </button>

    <Drawer :isOpen="isDrawerOpen" @close="isDrawerOpen = false" title="Pilih OPD" size="sm">
      <div class="space-y-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">
          Pilihan OPD / SKPD di sini akan menjadi filter global untuk data yang Anda lihat.
        </p>
        <button v-if="hasAllOpds" type="button"
          class="flex w-full items-center justify-between rounded-lg border px-3 py-2.5 text-sm transition-colors"
          :class="internalValue === '' ? 'border-brand-500 bg-brand-50 text-brand-700 dark:border-brand-500 dark:bg-brand-500/10 dark:text-brand-300' : 'border-gray-200 text-gray-700 hover:border-brand-300 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:border-brand-500 dark:hover:bg-gray-800'"
          @click="selectOpd('')">
          <span class="truncate">Semua OPD</span>
          <span v-if="internalValue === ''" class="h-2 w-2 rounded-full bg-brand-500 dark:bg-brand-400" />
        </button>

        <div class="max-h-[360px] space-y-1 overflow-y-auto pr-1">
          <button v-for="opd in opds" :key="opd.id" type="button"
            class="flex w-full items-center justify-between rounded-lg border px-3 py-2.5 text-sm transition-colors"
            :class="internalValue === opd.id ? 'border-brand-500 bg-brand-50 text-brand-700 dark:border-brand-500 dark:bg-brand-500/10 dark:text-brand-300' : 'border-gray-200 text-gray-700 hover:border-brand-300 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:border-brand-500 dark:hover:bg-gray-800'"
            @click="selectOpd(opd.id)">
            <span class="truncate">
              {{ opd.code ? `${opd.name} (${opd.code})` : opd.name }}
            </span>
            <span v-if="internalValue === opd.id" class="h-2 w-2 rounded-full bg-brand-500 dark:bg-brand-400" />
          </button>
        </div>
      </div>
    </Drawer>
  </div>
</template>

<script setup lang="ts">
import axios from 'axios';
import { computed, ref, watch } from 'vue';
import Drawer from '@/components/ui/Drawer.vue';

interface Opd {
  id: string;
  name: string;
  code?: string | null;
}

interface Props {
  modelValue: string | '';
  opds: Opd[];
  currentOpd?: Opd | null;
  hasAllOpds?: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
  (e: 'update:modelValue', value: string | ''): void;
  (e: 'change', value: string | ''): void;
}>();

const isDrawerOpen = ref(false);

const internalValue = ref<string | ''>(props.modelValue ?? '');

// Sync with currentOpd prop when it changes
// Tapi jangan override jika user sudah memilih Semua OPD
watch(
  () => props.currentOpd,
  (newCurrentOpd) => {
    // Hanya update jika ada perubahan dan user tidak sengaja memilih Semua OPD
    if (newCurrentOpd && internalValue.value !== '') {
      internalValue.value = newCurrentOpd.id;
    } else if (!newCurrentOpd && internalValue.value !== '') {
      internalValue.value = '';
    }
  },
  { immediate: true }
);

watch(
  () => props.modelValue,
  value => {
    internalValue.value = value ?? '';
  },
);

const selectedLabel = computed(() => {
  const current = props.opds.find(opd => opd.id === internalValue.value);
  if (!current) {
    return 'Semua OPD';
  }
  if (current.code) {
    return `${current.name} (${current.code})`;
  }
  return current.name;
});

const selectOpd = async (value: string | '') => {
  try {
    // Validasi frontend - hanya user dengan has_all_opds yang bisa pilih Semua OPD
    if (value === '' && !props.hasAllOpds) {
      console.error('User tidak memiliki izin untuk memilih Semua OPD');
      alert('Anda tidak memiliki izin untuk memilih Semua OPD');
      return;
    }

    console.log('Selecting OPD:', value === '' ? 'Semua OPD (null)' : value);

    // Update session via API - pastikan null untuk "Semua OPD"
    const opdId = value === '' ? null : value;
    const response = await axios.post('/api/current-opd', { opd_id: opdId });

    console.log('OPD update response:', response.data);

    internalValue.value = value;
    emit('update:modelValue', value);
    emit('change', value);
    isDrawerOpen.value = false;

    // Refresh halaman untuk update data
    window.location.reload();
  } catch (error: any) {
    console.error('Failed to update current OPD:', error);
    if (error.response?.data?.message) {
      alert(error.response.data.message);
    }
  }
};
</script>
