<template>
    <div>
        <Button v-can="ASSET_PERMISSIONS.CREATE" size="sm" variant="primary" className="w-full sm:w-auto"
            :endIcon="PlusIcon" :onClick="openDrawer">
            Tambah Aset
        </Button>

        <Drawer :isOpen="isOpen" @close="closeDrawer" title="Tambah Aset Baru">
            <div class="space-y-6">
                <!-- Foto Aset -->
                <div class="flex justify-start">
                    <AvatarInput v-model="photoFile" :src="''" :placeholder="''" size="large" variant="square"
                        alt="Foto aset" @change="validatePhoto" />
                </div>
                <p v-if="(form.errors as any).photo" class="mt-1 text-sm text-error-500">
                    {{ (form.errors as any).photo }}
                </p>

                <!-- Informasi Utama -->
                <div class="space-y-4">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white border-b pb-2">
                        Informasi Utama
                    </h3>

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
                            Kategori Aset <span class="text-error-500">*</span>
                        </label>
                        <Combobox v-model="form.category_id" :options="categoryOptions" labelKey="name" valueKey="id"
                            placeholder="Pilih kategori..." :loading="categoryLoading" remote @search="onCategorySearch"
                            @load-more="onCategoryLoadMore" />
                        <p v-if="form.errors.category_id" class="mt-1 text-sm text-error-500">
                            {{ form.errors.category_id }}
                        </p>
                    </div>

                    <div v-if="!currentOpdSet" class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                            OPD <span class="text-error-500">*</span>
                        </label>
                        <Combobox v-model="form.opd_id" :options="opdOptions" labelKey="name" valueKey="id"
                            placeholder="Pilih OPD..." :loading="opdLoading" remote @search="onOpdSearch"
                            @load-more="onOpdLoadMore" />
                        <p v-if="form.errors.opd_id" class="mt-1 text-sm text-error-500">
                            {{ form.errors.opd_id }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                            Ruangan
                        </label>
                        <Combobox v-model="form.room_id" :options="roomOptions" labelKey="name" valueKey="id"
                            placeholder="Pilih ruangan (opsional)..." :loading="roomLoading" remote
                            @search="onRoomSearch" @load-more="onRoomLoadMore" />
                        <p v-if="form.errors.room_id" class="mt-1 text-sm text-error-500">
                            {{ form.errors.room_id }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <SelectInput v-model="form.condition" :options="conditionOptions" label="Kondisi Aset"
                            placeholder="Pilih kondisi aset" :required="true" />
                        <p v-if="form.errors.condition" class="mt-1 text-sm text-error-500">
                            {{ form.errors.condition }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
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
                            <InputWithAffix v-model="form.purchase_price" type="number" label="Nilai Perolehan"
                                prefix="Rp" placeholder="0" min="0" step="0.01" />
                            <p v-if="form.errors.purchase_price" class="mt-1 text-sm text-error-500">
                                {{ form.errors.purchase_price }}
                            </p>
                        </div>
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
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                            Catatan Umum
                        </label>
                        <textarea id="notes" v-model="form.notes" rows="2"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                            placeholder="Masukkan catatan umum aset"></textarea>
                        <p v-if="form.errors.notes" class="mt-1 text-sm text-error-500">
                            {{ form.errors.notes }}
                        </p>
                    </div>
                </div>

                <!-- Informasi Tambahan -->
                <div class="space-y-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                        Informasi Tambahan (Detail Fisik)
                    </h3>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <label for="manufacturer"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                Merk / Pabrikan <span class="text-error-500">*</span>
                            </label>
                            <input id="manufacturer" v-model="additionalForm.manufacturer" type="text"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                                placeholder="Contoh: Toyota, Dell, Ikea" />
                            <p v-if="additionalForm.errors.manufacturer" class="mt-1 text-sm text-error-500">
                                {{ additionalForm.errors.manufacturer }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <label for="model" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                Model / Tipe <span class="text-error-500">*</span>
                            </label>
                            <input id="model" v-model="additionalForm.model" type="text"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                                placeholder="Contoh: Avanza G, Latitude 7420" />
                            <p v-if="additionalForm.errors.model" class="mt-1 text-sm text-error-500">
                                {{ additionalForm.errors.model }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="serial_number" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                            Nomor Seri (S/N)
                        </label>
                        <input id="serial_number" v-model="additionalForm.serial_number" type="text"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                            placeholder="Masukkan nomor seri jika ada" />
                        <p v-if="additionalForm.errors.serial_number" class="mt-1 text-sm text-error-500">
                            {{ additionalForm.errors.serial_number }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                            Spesifikasi / Detail Tambahan
                        </label>
                        <textarea id="extra_notes" v-model="additionalForm.extra_notes" rows="3"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                            placeholder="Masukkan detail spesifikasi, warna, bahan, dsb."></textarea>
                        <p v-if="additionalForm.errors.extra_notes" class="mt-1 text-sm text-error-500">
                            {{ additionalForm.errors.extra_notes }}
                        </p>
                    </div>
                </div>
            </div>

            <template #footer>
                <div class="flex w-full justify-end gap-3">
                    <Button variant="outline" :onClick="closeDrawer">
                        Batal
                    </Button>
                    <Button variant="primary" :onClick="save" :disabled="form.processing || additionalForm.processing">
                        {{ (form.processing || additionalForm.processing) ? 'Menyimpan...' : 'Simpan Aset' }}
                    </Button>
                </div>
            </template>
        </Drawer>
    </div>
</template>

<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref } from 'vue';
import AvatarInput from '@/components/forms/FormElements/AvatarInput.vue';
import InputWithAffix from '@/components/forms/FormElements/InputWithAffix.vue';
import SelectInput from '@/components/forms/FormElements/SelectInput.vue';
import Button from '@/components/ui/Button.vue';
import Combobox from '@/components/ui/Combobox.vue';
import Drawer from '@/components/ui/Drawer.vue';
import { ASSET_PERMISSIONS } from '@/directives/permissions';
import { PlusIcon } from '@/icons';

const emit = defineEmits<{
    (e: 'saved', asset: any): void;
}>();

const page = usePage();
const currentOpd = computed(() => (page.props.auth as any).current_opd);
const currentOpdSet = computed(() => !!currentOpd.value);

const isOpen = ref(false);
const photoFile = ref<File | null>(null);
const createdAssetId = ref<string | null>(null);

const form = useForm({
    asset_code: '',
    name: '',
    category_id: null as string | null,
    opd_id: null as string | null,
    room_id: null as string | null,
    funding_source_id: null as string | null,
    condition: '' as string,
    purchase_date: '',
    purchase_price: null as number | null,
    status: 'active', // Default active for new assets
    notes: '',
});

// Separate form for additional info to handle errors separately if needed,
// but we'll manage validation manually for required fields before submission.
const additionalForm = useForm({
    manufacturer: '',
    model: '',
    serial_number: '',
    extra_notes: '',
});

const conditionOptions = [
    { value: 'good', label: 'Baik' },
    { value: 'minor_damage', label: 'Rusak Ringan' },
    { value: 'major_damage', label: 'Rusak Berat' },
    { value: 'lost', label: 'Hilang' },
];

// Options State
const categoryOptions = ref<any[]>([]);
const categoryLoading = ref(false);
const categorySearch = ref('');
const categoryPage = ref(1);
const categoryHasMore = ref(true);

const opdOptions = ref<any[]>([]);
const opdLoading = ref(false);
const opdSearch = ref('');
const opdPage = ref(1);
const opdHasMore = ref(true);

const roomOptions = ref<any[]>([]);
const roomLoading = ref(false);
const roomSearch = ref('');
const roomPage = ref(1);
const roomHasMore = ref(true);

const fundingSourceOptions = ref<any[]>([]);
const fundingSourceLoading = ref(false);
const fundingSourceSearch = ref('');
const fundingSourcePage = ref(1);
const fundingSourceHasMore = ref(true);

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

const validateAdditionalInfo = () => {
    let isValid = true;
    additionalForm.clearErrors();

    if (!additionalForm.manufacturer) {
        additionalForm.setError('manufacturer', 'Merk / Pabrikan wajib diisi.');
        isValid = false;
    }

    if (!additionalForm.model) {
        additionalForm.setError('model', 'Model / Tipe wajib diisi.');
        isValid = false;
    }

    return isValid;
};

const openDrawer = () => {
    resetForm();
    fetchInitialOptions();
    isOpen.value = true;
};

const closeDrawer = () => {
    isOpen.value = false;
    form.clearErrors();
    additionalForm.clearErrors();
    photoFile.value = null;
};

const resetForm = () => {
    form.reset();
    form.clearErrors();
    additionalForm.reset();
    additionalForm.clearErrors();

    form.opd_id = currentOpdSet.value ? (currentOpd.value as any).id : null;
    photoFile.value = null;
};

const fetchInitialOptions = () => {
    fetchCategoryOptions(true);
    fetchOpdOptions(true);
    fetchRoomOptions(true);
    fetchFundingSourceOptions(true);
};

// Fetch Options Functions (Copied from AssetForm)
const fetchCategoryOptions = async (reset = false) => {
    if (reset) {
        categoryPage.value = 1;
        categoryOptions.value = [];
        categoryHasMore.value = true;
    }
    if (!categoryHasMore.value && !reset) return;
    categoryLoading.value = true;
    try {
        const response = await axios.get('/api/asset-categories/selection', {
            params: { page: categoryPage.value, per_page: 20, search: categorySearch.value, level: 2 },
        });
        const data = response.data.data;
        if (reset) categoryOptions.value = data.data;
        else categoryOptions.value = [...categoryOptions.value, ...data.data];
        categoryHasMore.value = !!data.next_page_url;
        categoryPage.value++;
    } catch (error) {
        console.error('Error fetching asset categories:', error);
    } finally {
        categoryLoading.value = false;
    }
};

const onCategorySearch = (query: string) => {
    categorySearch.value = query;
    fetchCategoryOptions(true);
};

const onCategoryLoadMore = () => {
    fetchCategoryOptions(false);
};

const fetchOpdOptions = async (reset = false) => {
    if (reset) {
        opdPage.value = 1;
        opdOptions.value = [];
        opdHasMore.value = true;
    }
    if (!opdHasMore.value && !reset) return;
    opdLoading.value = true;
    try {
        const response = await axios.get('/api/opds/selection', {
            params: { page: opdPage.value, per_page: 20, search: opdSearch.value },
        });
        const data = response.data.data;
        if (reset) opdOptions.value = data.data;
        else opdOptions.value = [...opdOptions.value, ...data.data];
        opdHasMore.value = !!data.next_page_url;
        opdPage.value++;
    } catch (error) {
        console.error('Error fetching OPDs:', error);
    } finally {
        opdLoading.value = false;
    }
};

const onOpdSearch = (query: string) => {
    opdSearch.value = query;
    fetchOpdOptions(true);
};

const onOpdLoadMore = () => {
    fetchOpdOptions(false);
};

const fetchRoomOptions = async (reset = false) => {
    if (reset) {
        roomPage.value = 1;
        roomOptions.value = [];
        roomHasMore.value = true;
    }
    if (!roomHasMore.value && !reset) return;
    roomLoading.value = true;
    try {
        const response = await axios.get('/api/rooms/selection', {
            params: { page: roomPage.value, per_page: 20, search: roomSearch.value, opd_id: form.opd_id ?? undefined },
        });
        const data = response.data.data;
        if (reset) roomOptions.value = data.data;
        else roomOptions.value = [...roomOptions.value, ...data.data];
        roomHasMore.value = !!data.next_page_url;
        roomPage.value++;
    } catch (error) {
        console.error('Error fetching rooms:', error);
    } finally {
        roomLoading.value = false;
    }
};

const onRoomSearch = (query: string) => {
    roomSearch.value = query;
    fetchRoomOptions(true);
};

const onRoomLoadMore = () => {
    fetchRoomOptions(false);
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
            params: { page: fundingSourcePage.value, per_page: 20, search: fundingSourceSearch.value },
        });
        const data = response.data.data;
        if (reset) fundingSourceOptions.value = data.data;
        else fundingSourceOptions.value = [...fundingSourceOptions.value, ...data.data];
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

const save = async () => {
    // Validate Photo
    if (!validatePhoto(photoFile.value)) {
        return;
    }

    // Validate Additional Info
    if (!validateAdditionalInfo()) {
        return;
    }

    form.processing = true;
    additionalForm.processing = true;

    // 1. Create Asset
    const formData = new FormData();
    const dataToSubmit = { ...form.data() };

    Object.entries(dataToSubmit).forEach(([key, value]) => {
        if (value === null || typeof value === 'undefined') return;
        if (key === 'status' && value === '') return;
        formData.append(key, value as any);
    });

    if (photoFile.value) {
        formData.append('photo', photoFile.value);
    }

    try {
        // Step 1: Create Asset (if not already created in a previous failed attempt)
        if (!createdAssetId.value) {
            const response = await axios.post('/api/assets', formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
            const createdAsset = response.data.data;
            createdAssetId.value = createdAsset.id;
        }

        // Step 2: Save Additional Info
        if (createdAssetId.value) {
            await axios.post(`/api/assets/${createdAssetId.value}/additional-info`, {
                manufacturer: additionalForm.manufacturer,
                model: additionalForm.model,
                serial_number: additionalForm.serial_number,
                extra_notes: additionalForm.extra_notes,
            });
        }

        emit('saved', { id: createdAssetId.value }); // We might not have the full object here if we skipped step 1, but id is enough for refresh
        closeDrawer();

    } catch (error: any) {
        if (error.response && error.response.data && error.response.data.errors) {
            if (!createdAssetId.value) {
                form.errors = error.response.data.errors;
            } else {
                additionalForm.errors = error.response.data.errors;
            }
        } else {
            console.error('Error saving asset:', error);
        }
    } finally {
        form.processing = false;
        additionalForm.processing = false;
    }
};
</script>
