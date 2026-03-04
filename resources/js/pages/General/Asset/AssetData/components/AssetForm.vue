<template>
    <div>
        <Button size="xs" variant="outline" :startIcon="PencilIcon" :onClick="openDrawer" className="w-full sm:w-auto">
            Edit
        </Button>

        <Drawer :isOpen="isOpen" @close="closeDrawer" title="Edit Aset">
            <div class="space-y-6">
                <div class="flex justify-start">
                    <AvatarInput v-model="photoFile" :src="currentPhotoSrc" :placeholder="currentPhotoPlaceholder"
                        size="large" variant="square" alt="Foto aset" @change="validatePhoto" />
                </div>
                <p v-if="(form.errors as any).photo" class="mt-1 text-sm text-error-500">
                    {{ (form.errors as any).photo }}
                </p>

                <div>
                    <label for="asset_code" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Kode Aset <span class="text-error-500">*</span>
                    </label>
                    <input id="asset_code" v-model="form.asset_code" type="text"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Masukkan kode aset" />
                    <p v-if="form.errors.asset_code" class="mt-1 text-sm text-error-500">
                        {{ form.errors.asset_code }}
                    </p>
                </div>

                <div>
                    <label for="name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Nama Aset <span class="text-error-500">*</span>
                    </label>
                    <input id="name" v-model="form.name" type="text"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Masukkan nama aset" />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-error-500">
                        {{ form.errors.name }}
                    </p>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Kategori Aset
                    </label>
                    <Combobox v-model="form.category_id" :options="categoryOptions" labelKey="name" valueKey="id"
                        placeholder="Pilih kategori aset..." :loading="categoryLoading" remote
                        @search="onCategorySearch" />
                    <p v-if="form.errors.category_id" class="mt-1 text-sm text-error-500">
                        {{ form.errors.category_id }}
                    </p>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        OPD (Perangkat Daerah)
                    </label>
                    <div
                        class="w-full rounded-lg border border-gray-300 bg-gray-100 px-4 py-3 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
                        {{ asset?.opd?.name || '-' }}
                    </div>
                </div>

                <div class="space-y-2">
                    <SelectInput v-model="form.condition" :options="conditionOptions" label="Kondisi Aset"
                        placeholder="Pilih kondisi aset" :required="true" />
                    <p v-if="form.errors.condition" class="mt-1 text-sm text-error-500">
                        {{ form.errors.condition }}
                    </p>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Tanggal Perolehan
                    </label>
                    <input id="purchase_date" v-model="form.purchase_date" type="date"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
                    <p v-if="form.errors.purchase_date" class="mt-1 text-sm text-error-500">
                        {{ form.errors.purchase_date }}
                    </p>
                </div>

                <div class="space-y-2">
                    <InputWithAffix v-model="form.purchase_price" type="number" label="Nilai Perolehan" prefix="Rp"
                        placeholder="Masukkan nilai perolehan" min="0" step="0.01" />
                    <p v-if="form.errors.purchase_price" class="mt-1 text-sm text-error-500">
                        {{ form.errors.purchase_price }}
                    </p>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Sumber Perolehan
                    </label>
                    <Combobox v-model="form.funding_source_id" :options="fundingSourceOptions" labelKey="name"
                        valueKey="id" placeholder="Pilih sumber perolehan..." :loading="fundingSourceLoading" remote
                        @search="onFundingSourceSearch" @load-more="onFundingSourceLoadMore" />
                    <p v-if="form.errors.funding_source_id" class="mt-1 text-sm text-error-500">
                        {{ form.errors.funding_source_id }}
                    </p>
                </div>

                <div class="space-y-2">
                    <SelectInput v-model="form.status" :options="statusOptions" label="Status"
                        placeholder="Pilih status aset" />
                    <p v-if="form.errors.status" class="mt-1 text-sm text-error-500">
                        {{ form.errors.status }}
                    </p>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Catatan
                    </label>
                    <textarea id="notes" v-model="form.notes" rows="3"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Masukkan catatan tambahan"></textarea>
                    <p v-if="form.errors.notes" class="mt-1 text-sm text-error-500">
                        {{ form.errors.notes }}
                    </p>
                </div>
            </div>

            <template #footer>
                <div class="flex w-full justify-end gap-3">
                    <Button variant="outline" :onClick="closeDrawer">
                        Batal
                    </Button>
                    <Button variant="primary" :onClick="save" :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </Button>
                </div>
            </template>
        </Drawer>
    </div>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref } from 'vue';
import AvatarInput from '@/components/forms/FormElements/AvatarInput.vue';
import InputWithAffix from '@/components/forms/FormElements/InputWithAffix.vue';
import SelectInput from '@/components/forms/FormElements/SelectInput.vue';
import Button from '@/components/ui/Button.vue';
import Combobox from '@/components/ui/Combobox.vue';
import Drawer from '@/components/ui/Drawer.vue';
import { PencilIcon } from '@/icons';

const props = defineProps<{
    assetId?: string;
    asset?: any | null;
}>();

const emit = defineEmits<{
    (e: 'saved', asset: any): void;
}>();

const isOpen = ref(false);
const photoFile = ref<File | null>(null);

const form = useForm({
    asset_code: '',
    name: '',
    category_id: null as string | null,
    opd_id: null as string | null,
    funding_source_id: null as string | null,
    condition: '' as string,
    purchase_date: '',
    purchase_price: null as number | null,
    status: '' as string,
    notes: '',
});

const conditionOptions = [
    { value: 'good', label: 'Baik' },
    { value: 'minor_damage', label: 'Rusak Ringan' },
    { value: 'major_damage', label: 'Rusak Berat' },
    { value: 'lost', label: 'Hilang' },
];

const fundingSourceOptions = ref<any[]>([]);
const fundingSourceLoading = ref(false);
const fundingSourceSearch = ref('');
const fundingSourcePage = ref(1);
const fundingSourceHasMore = ref(true);

const categoryOptions = ref<any[]>([]);
const categoryLoading = ref(false);
const categorySearch = ref('');

const statusOptions = [
    { value: 'active', label: 'Aktif' },
    { value: 'inactive', label: 'Tidak Aktif' },
];

const validatePhoto = (file: File | null) => {
    (form.errors as any).photo = '';

    if (!file) {
        return true;
    }

    if (!file.type.startsWith('image/')) {
        (form.errors as any).photo = 'File harus berupa gambar.';
        return false;
    }

    const maxSize = 2 * 1024 * 1024;
    if (file.size > maxSize) {
        (form.errors as any).photo = 'Ukuran foto maksimal 2 MB.';
        return false;
    }

    return true;
};

const currentPhoto = computed(() => props.asset?.photo ?? null);

const currentPhotoSrc = computed(() => {
    const photo = currentPhoto.value;

    if (!photo) {
        return '';
    }

    if (typeof photo === 'string') {
        return photo;
    }

    if (typeof photo === 'object' && photo !== null) {
        return photo.url || '';
    }

    return '';
});

const currentPhotoPlaceholder = computed(() => {
    const photo = currentPhoto.value;

    if (!photo || typeof photo !== 'object') {
        return '';
    }

    return photo.placeholder || '';
});

const openDrawer = () => {
    if (props.asset) {
        fillFormFromAsset(props.asset);
    }
    fetchFundingSourceOptions(true);
    fetchCategoryOptions();
    isOpen.value = true;
};

const closeDrawer = () => {
    isOpen.value = false;
    form.clearErrors();
    photoFile.value = null;
};

const fillFormFromAsset = (asset: any) => {
    form.asset_code = asset.asset_code ?? '';
    form.name = asset.name ?? '';
    form.category_id = asset.category_id ?? null;
    form.opd_id = asset.opd_id ?? null;
    form.funding_source_id = asset.funding_source_id ?? null;
    form.condition = asset.condition ?? '';
    // Format date to YYYY-MM-DD for input[type="date"]
    if (asset.purchase_date) {
        const date = new Date(asset.purchase_date);
        if (!isNaN(date.getTime())) {
            form.purchase_date = date.toISOString().split('T')[0];
        } else {
            form.purchase_date = asset.purchase_date;
        }
    } else {
        form.purchase_date = '';
    }
    form.purchase_price = asset.purchase_price ?? null;
    form.status = asset.status ?? 'active';
    form.notes = asset.notes ?? '';
};

const fetchFundingSourceOptions = async (reset = false) => {
    if (reset) {
        fundingSourcePage.value = 1;
        fundingSourceOptions.value = [];
        fundingSourceHasMore.value = true;
    }

    if (!fundingSourceHasMore.value && !reset) return;

    fundingSourceLoading.value = true;
    try {
        const response = await axios.get('/api/funding-sources/selection', {
            params: {
                page: fundingSourcePage.value,
                per_page: 20,
                search: fundingSourceSearch.value,
            },
        });
        const data = response.data.data;
        if (reset) {
            fundingSourceOptions.value = data.data;
        } else {
            fundingSourceOptions.value = [...fundingSourceOptions.value, ...data.data];
        }
        fundingSourceHasMore.value = !!data.next_page_url;
        fundingSourcePage.value++;
    } catch (error) {
        console.error('Error fetching funding sources:', error);
    } finally {
        fundingSourceLoading.value = false;
    }
};

const onFundingSourceSearch = (query: string) => {
    fundingSourceSearch.value = query;
    fetchFundingSourceOptions(true);
};

const onFundingSourceLoadMore = () => {
    fetchFundingSourceOptions(false);
};

const fetchCategoryOptions = async () => {
    categoryLoading.value = true;
    try {
        const response = await axios.get('/api/asset-categories/selection', {
            params: { search: categorySearch.value },
        });

        const options = response.data.data.data || response.data.data;
        // Ensure the current asset's category is in the options list if it exists
        if (props.asset && props.asset.category && props.asset.category.id === form.category_id) {
            const exists = options.find((o: any) => o.id === props.asset.category.id);
            if (!exists) {
                options.unshift(props.asset.category);
            }
        }

        categoryOptions.value = options;
    } catch (error) {
        console.error('Error fetching categories:', error);
    } finally {
        categoryLoading.value = false;
    }
};

const onCategorySearch = (query: string) => {
    categorySearch.value = query;
    fetchCategoryOptions();
};

const save = async () => {
    form.processing = true;

    if (!validatePhoto(photoFile.value)) {
        form.processing = false;
        return;
    }

    if (!props.assetId) {
        form.processing = false;
        return;
    }

    const formData = new FormData();
    const dataToSubmit = {
        ...form.data(),
    };

    Object.entries(dataToSubmit).forEach(([key, value]) => {
        if (value === null || typeof value === 'undefined') {
            return;
        }

        formData.append(key, value as any);
    });

    if (photoFile.value) {
        formData.append('photo', photoFile.value);
    }

    // Method spoofing for PUT request with FormData
    formData.append('_method', 'PUT');

    try {
        const response = await axios.post(`/api/assets/${props.assetId}`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        emit('saved', response.data.data);
        closeDrawer();
    } catch (error: any) {
        if (error.response && error.response.data && error.response.data.errors) {
            form.errors = error.response.data.errors;
        } else {
            console.error('Error saving asset:', error);
        }
    } finally {
        form.processing = false;
    }
};
</script>
