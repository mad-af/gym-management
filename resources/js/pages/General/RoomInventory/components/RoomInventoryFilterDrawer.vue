<template>
    <div class="flex items-center gap-2">
        <Button size="xs" variant="outline" className="w-full sm:w-auto" :startIcon="FilterIcon" :onClick="open">
            Filter
        </Button>

        <Drawer :isOpen="isOpen" @close="close" title="Filter Aset di Ruangan">
            <div class="space-y-6">
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Cari Aset
                    </label>
                    <input v-model="searchModel" type="text"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Cari berdasarkan nama atau kode aset..." />
                </div>

                <div>
                    <SelectInput v-model="statusModel" :options="statusOptions" label="Status Aset"
                        placeholder="Semua Status" />
                </div>
            </div>
            <template #footer>
                <div class="flex w-full justify-end gap-3">
                    <Button variant="outline" :onClick="handleResetClick">
                        Reset
                    </Button>
                    <Button variant="primary" :onClick="handleApplyClick">
                        Terapkan Filter
                    </Button>
                </div>
            </template>
        </Drawer>
    </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import SelectInput from '@/components/forms/FormElements/SelectInput.vue';
import Button from '@/components/ui/Button.vue';
import Drawer from '@/components/ui/Drawer.vue';
import { FilterIcon } from '@/icons';

const props = defineProps<{
    status: string;
    search: string;
}>();

const emit = defineEmits<{
    (e: 'update:status', value: string): void;
    (e: 'update:search', value: string): void;
    (e: 'apply'): void;
    (e: 'reset'): void;
}>();

const isOpen = ref(false);

const statusModel = computed({
    get: () => props.status,
    set: (value: string) => emit('update:status', value),
});

const searchModel = computed({
    get: () => props.search,
    set: (value: string) => emit('update:search', value),
});

const statusOptions = [
    { label: 'Semua Status', value: '' },
    { label: 'Aktif', value: 'active' },
    { label: 'Tidak Aktif', value: 'inactive' },
    { label: 'Dalam Perawatan', value: 'under_maintenance' },
    { label: 'Dibuang', value: 'disposed' },
];

const open = () => {
    isOpen.value = true;
};

const close = () => {
    isOpen.value = false;
};

const handleResetClick = () => {
    emit('update:status', '');
    emit('update:search', '');
    emit('reset');
    isOpen.value = false;
};

const handleApplyClick = () => {
    emit('apply');
    isOpen.value = false;
};
</script>
