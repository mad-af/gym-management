<template>
    <div>
        <Button v-if="showButton" :size="buttonSize" :variant="buttonVariant" :className="buttonClass"
            :startIcon="startIcon" :endIcon="endIcon" :onClick="openDrawer">
            {{ buttonText }}
        </Button>

        <Drawer :isOpen="isOpen" @close="closeDrawer" :title="drawerTitle">
            <div class="space-y-6">
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Aset <span class="text-error-500">*</span>
                    </label>
                    <Combobox v-model="form.asset_id" :options="assetOptions" labelKey="display" valueKey="id"
                        placeholder="Pilih aset..." :loading="assetLoading" remote @search="onAssetSearch"
                        @load-more="onAssetLoadMore" />
                    <p v-if="form.errors.asset_id" class="mt-1 text-sm text-error-500">
                        {{ form.errors.asset_id }}
                    </p>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Jenis Perawatan <span class="text-error-500">*</span>
                    </label>
                    <input type="text" v-model="form.maintenance_type"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Contoh: Servis rutin, pengecekan tahunan, dll." />
                    <p v-if="form.errors.maintenance_type" class="mt-1 text-sm text-error-500">
                        {{ form.errors.maintenance_type }}
                    </p>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Tanggal Terjadwal <span class="text-error-500">*</span>
                    </label>
                    <input type="date" v-model="form.scheduled_date"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
                    <p v-if="form.errors.scheduled_date" class="mt-1 text-sm text-error-500">
                        {{ form.errors.scheduled_date }}
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                            Vendor
                        </label>
                        <input type="text" v-model="form.vendor"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                            placeholder="Nama vendor (opsional)" />
                        <p v-if="form.errors.vendor" class="mt-1 text-sm text-error-500">
                            {{ form.errors.vendor }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                            Perkiraan Biaya
                        </label>
                        <input type="number" min="0" step="0.01" v-model.number="form.cost"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                            placeholder="Masukkan biaya (opsional)" />
                        <p v-if="form.errors.cost" class="mt-1 text-sm text-error-500">
                            {{ form.errors.cost }}
                        </p>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Deskripsi
                    </label>
                    <textarea v-model="form.description" rows="3"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Catatan tambahan terkait perawatan (opsional)"></textarea>
                    <p v-if="form.errors.description" class="mt-1 text-sm text-error-500">
                        {{ form.errors.description }}
                    </p>
                </div>

                <div class="flex justify-end gap-2">
                    <Button variant="outline" :onClick="closeDrawer">
                        Batal
                    </Button>
                    <Button variant="primary" :onClick="save" :disabled="form.processing">
                        {{ isEditMode ? 'Simpan Perubahan' : 'Simpan' }}
                    </Button>
                </div>
            </div>
        </Drawer>
    </div>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref, watch } from 'vue';
import Button from '@/components/ui/Button.vue';
import Combobox from '@/components/ui/Combobox.vue';
import Drawer from '@/components/ui/Drawer.vue';

const props = withDefaults(
    defineProps<{
        maintenance?: any | null;
        buttonText?: string;
        startIcon?: any;
        endIcon?: any;
        buttonVariant?: 'primary' | 'outline';
        buttonSize?: 'xs' | 'sm' | 'md';
        buttonClass?: string;
        showButton?: boolean;
    }>(),
    {
        maintenance: null,
        buttonText: 'Jadwalkan Perawatan',
        startIcon: null,
        endIcon: null,
        buttonVariant: 'primary',
        buttonSize: 'sm',
        buttonClass: 'w-full sm:w-auto',
        showButton: true,
    }
);

const emit = defineEmits<{
    (e: 'saved', maintenance: any): void;
}>();

const isOpen = ref(false);
const assetOptions = ref<any[]>([]);
const assetLoading = ref(false);
const assetSearch = ref('');
const assetPage = ref(1);
const assetHasMore = ref(true);

const form = useForm({
    asset_id: null as string | null,
    maintenance_type: '',
    scheduled_date: '',
    cost: null as number | null,
    vendor: '',
    description: '',
});

const isEditMode = computed(() => !!props.maintenance);
const drawerTitle = computed(() => isEditMode.value ? 'Edit Perawatan Aset' : 'Jadwalkan Perawatan Aset');

const openDrawer = () => {
    form.clearErrors();

    if (isEditMode.value && props.maintenance) {
        form.asset_id = props.maintenance.asset_id;
        form.maintenance_type = props.maintenance.maintenance_type;
        // Format date to YYYY-MM-DD
        form.scheduled_date = props.maintenance.scheduled_date
            ? new Date(props.maintenance.scheduled_date).toISOString().split('T')[0]
            : '';
        form.cost = props.maintenance.cost;
        form.vendor = props.maintenance.vendor;
        form.description = props.maintenance.description;

        // Pre-fill asset options with current asset
        if (props.maintenance.asset) {
            const asset = props.maintenance.asset;
            const display = `${asset.asset_code} - ${asset.name}`;
            assetOptions.value = [{ ...asset, display }];
        }
    } else {
        form.reset();
        assetOptions.value = [];
        fetchAssetOptions(true);
    }

    isOpen.value = true;
};

const closeDrawer = () => {
    isOpen.value = false;
    form.reset();
    form.clearErrors();
};

const fetchAssetOptions = async (reset = false) => {
    if (reset) {
        assetPage.value = 1;
        assetOptions.value = [];
        assetHasMore.value = true;
    }

    if (!assetHasMore.value && !reset) return;

    assetLoading.value = true;
    try {
        const response = await axios.get('/api/assets/selection', {
            params: {
                page: assetPage.value,
                per_page: 20,
                search: assetSearch.value,
                only_maintainable: true,
            },
        });
        const data = response.data.data;
        const items = data.data.map((asset: any) => ({
            ...asset,
            display: `${asset.asset_code} - ${asset.name}`,
        }));

        // If in edit mode and resetting (searching), keep the selected asset in the list if it matches search or just append results
        // But simplified: just standard pagination.

        const merged = reset ? items : [...assetOptions.value, ...items];
        assetOptions.value = merged;
        assetHasMore.value = !!data.next_page_url;
        assetPage.value++;
    } catch (error) {
        console.error('Error fetching assets for maintenance:', error);
    } finally {
        assetLoading.value = false;
    }
};

const onAssetSearch = (query: string) => {
    assetSearch.value = query;
    fetchAssetOptions(true);
};

const onAssetLoadMore = () => {
    fetchAssetOptions(false);
};

const save = async () => {
    form.processing = true;
    try {
        let response;
        if (isEditMode.value && props.maintenance) {
            response = await axios.put(`/api/asset-maintenances/${props.maintenance.id}`, form.data());
        } else {
            response = await axios.post('/api/asset-maintenances', form.data());
        }

        closeDrawer();
        emit('saved', response.data.data);
    } catch (error: any) {
        if (error.response && error.response.status === 422 && error.response.data.errors) {
            form.errors = error.response.data.errors;
        } else {
            console.error('Error saving maintenance:', error);
        }
    } finally {
        form.processing = false;
    }
};

watch(() => props.maintenance, (newVal) => {
    if (isOpen.value && newVal) {
        // If drawer is open and maintenance prop updates (maybe unlikely but possible), update form
        // But usually we close drawer on save.
    }
});
</script>
