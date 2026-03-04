<template>
    <div>
        <Button v-can="ASSET_PERMISSIONS.EDIT" size="xs" variant="outline" :startIcon="PencilIcon" :onClick="openDrawer"
            className="w-full sm:w-auto">
            Edit
        </Button>

        <Drawer :isOpen="isOpen" @close="closeDrawer" title="Informasi Tambahan Aset">
            <div class="space-y-4">
                <div>
                    <label for="manufacturer" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Merk
                    </label>
                    <input id="manufacturer" v-model="form.manufacturer" type="text"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Masukkan merk aset" />
                    <p v-if="errors.manufacturer" class="mt-1 text-sm text-error-500">
                        {{ errors.manufacturer }}
                    </p>
                </div>

                <div>
                    <label for="model" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Model
                    </label>
                    <input id="model" v-model="form.model" type="text"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Masukkan model aset" />
                    <p v-if="errors.model" class="mt-1 text-sm text-error-500">
                        {{ errors.model }}
                    </p>
                </div>

                <div>
                    <label for="serial_number" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Nomor Seri
                    </label>
                    <input id="serial_number" v-model="form.serial_number" type="text"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Masukkan nomor seri aset" />
                    <p v-if="errors.serial_number" class="mt-1 text-sm text-error-500">
                        {{ errors.serial_number }}
                    </p>
                </div>

                <div>
                    <label for="extra_notes" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Catatan Tambahan
                    </label>
                    <textarea id="extra_notes" v-model="form.extra_notes" rows="3"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Masukkan catatan tambahan" />
                    <p v-if="errors.extra_notes" class="mt-1 text-sm text-error-500">
                        {{ errors.extra_notes }}
                    </p>
                </div>
            </div>

            <template #footer>
                <div class="flex w-full justify-end gap-3">
                    <Button variant="outline" :onClick="closeDrawer">
                        Batal
                    </Button>
                    <Button variant="primary" :onClick="save" :disabled="processing">
                        {{ processing ? 'Menyimpan...' : 'Simpan' }}
                    </Button>
                </div>
            </template>
        </Drawer>
    </div>
</template>

<script setup lang="ts">
import axios from 'axios';
import { ref, watch } from 'vue';
import Button from '@/components/ui/Button.vue';
import Drawer from '@/components/ui/Drawer.vue';
import { ASSET_PERMISSIONS } from '@/directives/permissions';
import { PencilIcon } from '@/icons';

const props = defineProps<{
    assetId: string;
    info?: any | null;
}>();

const emit = defineEmits<{
    (e: 'saved', info: any): void;
}>();

const isOpen = ref(false);
const processing = ref(false);
const form = ref({
    manufacturer: '',
    model: '',
    serial_number: '',
    extra_notes: '',
});
const errors = ref<Record<string, string>>({});

const fillFormFromInfo = (info: any | null | undefined) => {
    form.value.manufacturer = info?.manufacturer ?? '';
    form.value.model = info?.model ?? '';
    form.value.serial_number = info?.serial_number ?? '';
    form.value.extra_notes = info?.extra_notes ?? '';
};

watch(
    () => props.info,
    (info) => {
        if (isOpen.value) {
            fillFormFromInfo(info);
        }
    },
);

const openDrawer = () => {
    fillFormFromInfo(props.info ?? null);
    errors.value = {};
    isOpen.value = true;
};

const closeDrawer = () => {
    isOpen.value = false;
    processing.value = false;
    errors.value = {};
};

const save = async () => {
    processing.value = true;
    errors.value = {};

    try {
        const response = await axios.post(`/api/assets/${props.assetId}/additional-info`, {
            ...form.value,
        });

        emit('saved', response.data.data);
        closeDrawer();
    } catch (error: any) {
        if (error.response && error.response.status === 422 && error.response.data?.errors) {
            const e: Record<string, string> = {};
            Object.keys(error.response.data.errors).forEach((key) => {
                const messages = error.response.data.errors[key];
                e[key] = Array.isArray(messages) ? messages[0] : String(messages);
            });
            errors.value = e;
        } else {
            console.error('Error saving asset additional info:', error);
        }
        processing.value = false;
    }
};
</script>
