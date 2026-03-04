<template>
    <Drawer :isOpen="isOpen" @close="$emit('close')" title="Filter Perawatan Aset">
        <div class="space-y-6">
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                    Aset
                </label>
                <Combobox v-model="localFilters.asset_id" :options="assetOptions" labelKey="display" valueKey="id"
                    placeholder="Pilih aset..." :loading="assetLoading" remote @search="onAssetSearch"
                    @load-more="onAssetLoadMore" />
            </div>
            <div>
                <SelectInput v-model="localFilters.status" :options="statusOptions" label="Status"
                    placeholder="Semua Status" />
            </div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Tanggal Terjadwal Dari
                    </label>
                    <input type="date" v-model="localFilters.scheduled_from"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
                </div>
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Tanggal Terjadwal Sampai
                    </label>
                    <input type="date" v-model="localFilters.scheduled_to"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <Button variant="outline" :onClick="resetFilter">
                    Reset
                </Button>
                <Button variant="primary" :onClick="applyFilter">
                    Terapkan Filter
                </Button>
            </div>
        </div>
    </Drawer>
</template>

<script setup lang="ts">
import axios from 'axios';
import { ref, watch } from 'vue';
import SelectInput from '@/components/forms/FormElements/SelectInput.vue';
import Button from '@/components/ui/Button.vue';
import Combobox from '@/components/ui/Combobox.vue';
import Drawer from '@/components/ui/Drawer.vue';

const props = defineProps<{
    isOpen: boolean;
    filters: {
        asset_id: string | null;
        status: string;
        scheduled_from: string;
        scheduled_to: string;
    };
    statusOptions: { value: string; label: string; class: string }[];
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'apply', filters: any): void;
}>();

const localFilters = ref({ ...props.filters });

const assetOptions = ref<any[]>([]);
const assetLoading = ref(false);
const assetSearch = ref('');
const assetPage = ref(1);
const assetHasMore = ref(true);

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

const applyFilter = () => {
    emit('apply', localFilters.value);
    emit('close');
};

const resetFilter = () => {
    localFilters.value = {
        asset_id: null,
        status: '',
        scheduled_from: '',
        scheduled_to: '',
    };
    emit('apply', localFilters.value);
    emit('close');
};

watch(() => props.isOpen, (newVal) => {
    if (newVal) {
        localFilters.value = { ...props.filters };
        if (assetOptions.value.length === 0) {
            fetchAssetOptions(true);
        }
    }
});
</script>
