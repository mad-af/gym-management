<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="currentPageTitle" />
        <Stats2 :items="statsItems" />

        <DynamicTable :columns="columns" :data="tableData" :items-per-page="perPage" :total-items="totalItems"
            :current-page="currentPage" :is-server-side="true" @update:page="handlePageChange"
            @update:search="handleSearch" @update:perPage="handlePerPageChange">
            <template #header-actions>
                <Button size="sm" variant="outline" :onClick="() => isFilterDrawerOpen = true"
                    className="w-full sm:w-auto" :startIcon="FilterIcon">
                    Filter
                </Button>
                <Button v-can="'create_membership_packages'" size="sm" variant="primary" :onClick="openDrawer"
                    className="w-full sm:w-auto" :endIcon="PlusIcon">
                    Tambah Paket
                </Button>
            </template>
            <template #actions="{ row }">
                <div class="flex items-center gap-2">
                    <button v-can="'edit_membership_packages'" @click="editItem(row)"
                        class="text-gray-500 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-500"
                        title="Edit">
                        <PencilIcon class="w-4.5 h-4.5" />
                    </button>
                    <button v-can="'delete_membership_packages'" v-if="row.is_active === 'Active'"
                        @click="deactivatePackage(row)"
                        class="text-gray-500 hover:text-error-500 dark:text-gray-400 dark:hover:text-error-500"
                        title="Nonaktifkan">
                        <TrashIcon class="w-4.5 h-4.5" />
                    </button>
                    <button v-can="'edit_membership_packages'" v-else @click="activatePackage(row)"
                        class="text-gray-500 hover:text-success-500 dark:text-gray-400 dark:hover:text-success-500"
                        title="Aktifkan">
                        <CheckCircleIcon class="w-4.5 h-4.5" />
                    </button>
                </div>
            </template>
        </DynamicTable>

        <Drawer :isOpen="isDrawerOpen" @close="closeDrawer" :title="form.id ? 'Edit Paket' : 'Tambah Paket'">
            <div class="space-y-6">
                <div class="flex justify-start">
                    <AvatarInput v-model="coverFile" :src="currentCoverSrc" :placeholder="currentCoverPlaceholder"
                        size="large" variant="square" />
                </div>
                <div>
                    <label for="name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Nama Paket <span class="text-error-500">*</span>
                    </label>
                    <input type="text" id="name" v-model="form.name"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-error-500">{{ form.errors.name }}</p>
                </div>
                <div>
                    <label for="price" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Harga <span class="text-error-500">*</span>
                    </label>
                    <input type="text" id="price" v-model="priceText" inputmode="numeric" @focus="handlePriceFocus"
                        @input="handlePriceInput" @blur="handlePriceBlur"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
                    <p v-if="form.errors.price" class="mt-1 text-sm text-error-500">{{ form.errors.price }}</p>
                </div>
                <div>
                    <label for="duration_days" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Durasi (hari) <span class="text-error-500">*</span>
                    </label>
                    <input type="number" id="duration_days" v-model.number="form.duration_days"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
                    <p v-if="form.errors.duration_days" class="mt-1 text-sm text-error-500">{{ form.errors.duration_days
                        }}</p>
                </div>
                <div>
                    <label for="description" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Deskripsi
                    </label>
                    <textarea id="description" v-model="form.description" rows="3"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
                    <p v-if="form.errors.description" class="mt-1 text-sm text-error-500">{{ form.errors.description }}
                    </p>
                </div>
                <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">Items Paket</p>
                        <Button size="sm" variant="outline" :onClick="addItem" className="w-auto" :startIcon="PlusIcon">
                            Tambah Item
                        </Button>
                    </div>

                    <div class="space-y-4">
                        <div v-for="(it, index) in formItems" :key="it.id || index"
                            class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-12">
                                <div class="sm:col-span-6">
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                                        Item Name <span class="text-error-500">*</span>
                                    </label>
                                    <input type="text" v-model="it.item_name"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
                                    <p v-if="getError(`items.${index}.item_name`)" class="mt-1 text-sm text-error-500">
                                        {{ getError(`items.${index}.item_name`) }}
                                    </p>
                                </div>

                                <div class="sm:col-span-3">
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                                        Qty <span class="text-error-500">*</span>
                                    </label>
                                    <input type="number" v-model.number="it.quantity" min="1"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
                                    <p v-if="getError(`items.${index}.quantity`)" class="mt-1 text-sm text-error-500">
                                        {{ getError(`items.${index}.quantity`) }}
                                    </p>
                                </div>

                                <div class="sm:col-span-3">
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                                        Unit
                                    </label>
                                    <input type="text" v-model="it.unit"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
                                    <p v-if="getError(`items.${index}.unit`)" class="mt-1 text-sm text-error-500">
                                        {{ getError(`items.${index}.unit`) }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-4 flex justify-end">
                                <Button size="sm" variant="outline" :onClick="() => removeItem(index)"
                                    className="w-auto" :startIcon="TrashIcon">
                                    Hapus Item
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <template #footer>
                <div class="flex w-full justify-end gap-3">
                    <Button variant="outline" :onClick="closeDrawer">Batal</Button>
                    <Button variant="primary" :onClick="saveItem" :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </Button>
                </div>
            </template>
        </Drawer>

        <Drawer :isOpen="isFilterDrawerOpen" @close="isFilterDrawerOpen = false" title="Filter Paket">
            <div class="space-y-6">
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="only_active" v-model="filters.only_active" />
                    <label for="only_active" class="text-sm text-gray-700 dark:text-gray-200">Hanya aktif</label>
                </div>
            </div>
            <template #footer>
                <div class="flex w-full justify-end gap-3">
                    <Button variant="outline" :onClick="resetFilter">Reset</Button>
                    <Button variant="primary" :onClick="handleFilter">Terapkan Filter</Button>
                </div>
            </template>
        </Drawer>
    </AdminLayout>
</template>

<script setup lang="ts">
import axios from 'axios';
import { ref, computed, onMounted } from 'vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import AvatarInput from '@/components/forms/FormElements/AvatarInput.vue';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import DynamicTable from '@/components/tables/data-tables/DynamicTable.vue';
import type { Column } from '@/components/tables/data-tables/DynamicTable.vue';
import Button from '@/components/ui/Button.vue';
import Drawer from '@/components/ui/Drawer.vue';
import Stats2 from '@/components/ui/Stats2.vue';
import { PlusIcon, TrashIcon, CheckCircleIcon, PencilIcon, FilterIcon, PackageIcon, ErrorIcon } from '@/icons';

const currentPageTitle = ref('Membership Packages');
const isDrawerOpen = ref(false);
const isFilterDrawerOpen = ref(false);
const filters = ref({ only_active: true });

const stats = ref({
    total: 0,
    active: 0,
    inactive: 0,
});

const statsItems = computed(() => [
    {
        label: 'Total Paket',
        value: stats.value.total,
        icon: PackageIcon,
        iconBgClass: 'bg-brand-50 text-brand-500 dark:bg-brand-500/10',
    },
    {
        label: 'Paket Aktif',
        value: stats.value.active,
        icon: CheckCircleIcon,
        iconBgClass: 'bg-success-50 text-success-600 dark:bg-success-500/10',
    },
    {
        label: 'Paket Nonaktif',
        value: stats.value.inactive,
        icon: ErrorIcon,
        iconBgClass: 'bg-error-50 text-error-600 dark:bg-error-500/10',
    }
]);

const packages = ref<any[]>([]);
const totalItems = ref(0);
const currentPage = ref(1);
const perPage = ref(10);
const searchFilter = ref('');

const coverFile = ref<File | null>(null);
const currentCover = ref<any | null>(null);

const currentCoverSrc = computed(() => {
    if (currentCover.value && typeof currentCover.value === 'object') {
        return currentCover.value.url || '';
    }
    return '';
});

const currentCoverPlaceholder = computed(() => {
    if (currentCover.value && typeof currentCover.value === 'object') {
        return currentCover.value.placeholder || '';
    }
    return '';
});

const form = ref({
    id: null as string | null,
    name: '',
    price: 0,
    duration_days: 30,
    description: '',
    errors: {} as Record<string, string>,
    processing: false,
});

const priceText = ref('Rp 0');

const formItems = ref<any[]>([]);

const formatRupiah = (value: unknown): string => {
    if (value === null || value === undefined || value === '') {
        return 'Rp 0';
    }

    const numberValue = typeof value === 'number' ? value : Number(value);
    if (Number.isNaN(numberValue)) {
        return 'Rp 0';
    }

    return `Rp ${Math.trunc(numberValue).toLocaleString('id-ID')}`;
};

const handlePriceFocus = () => {
    priceText.value = form.value.price ? String(form.value.price) : '';
};

const handlePriceInput = () => {
    const digits = (priceText.value || '').replace(/\D/g, '');
    priceText.value = digits;
    form.value.price = digits ? Number(digits) : 0;
};

const handlePriceBlur = () => {
    priceText.value = formatRupiah(form.value.price);
};

const getError = (key: string) => {
    const err = (form.value.errors as any)?.[key];
    return Array.isArray(err) ? err[0] : err;
};

const addItem = () => {
    formItems.value.push({
        id: null as string | null,
        item_name: '',
        quantity: 1,
        unit: '',
    });
};

const removeItem = (index: number) => {
    formItems.value.splice(index, 1);
};

const tableData = computed(() => {
    const rows: any[] = [];
    packages.value.forEach((p: any) => {
        const items = p.items && p.items.length > 0 ? p.items : [{}];
        const rowSpan = items.length;

        items.forEach((item: any, index: number) => {
            const isFirst = index === 0;
            const row: any = {
                ...p,
                id: p.id,
                cover: p.cover || null,
                name: p.name,
                price: formatRupiah(p.price),
                duration_days: p.duration_days,
                is_active: p.is_active ? 'Active' : 'Inactive',
                item_name: item.item_name || '-',
                item_quantity: item.quantity || '-',
                item_unit: item.unit || '-',
                _key: `${p.id}_${index}`,
                _cellAttributes: {}
            };

            if (isFirst) {
                row._cellAttributes = {
                    cover: { rowspan: rowSpan },
                    price: { rowspan: rowSpan },
                    duration_days: { rowspan: rowSpan },
                    is_active: { rowspan: rowSpan },
                    actions: { rowspan: rowSpan },
                    checkbox: { rowspan: rowSpan },
                };
            } else {
                row._cellAttributes = {
                    cover: { hidden: true },
                    price: { hidden: true },
                    duration_days: { hidden: true },
                    is_active: { hidden: true },
                    actions: { hidden: true },
                    checkbox: { hidden: true },
                };
            }
            rows.push(row);
        });
    });
    return rows;
});

const columns = ref<Column[]>([
    { key: 'cover', label: 'Cover', sortable: false, type: 'cover', avatarField: 'cover', labelField: 'name', class: 'font-medium align-top' },
    { key: 'price', label: 'Harga', class: 'align-top' },
    { key: 'duration_days', label: 'Durasi (hari)', class: 'align-top' },
    { key: 'is_active', label: 'Status', type: 'status', class: 'align-top', statusMap: { Active: 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500', Inactive: 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-500' } },
    { key: 'item_name', label: 'Item Name', class: '' },
    { key: 'item_quantity', label: 'Qty', class: '' },
    { key: 'item_unit', label: 'Unit', class: '' },
    { key: 'actions', label: 'Aksi', type: 'action', class: 'w-[120px] align-top' },
]);

const fetchStats = async () => {
    try {
        const { data } = await axios.get('/api/membership-packages/stats');
        const s = data.data || data;
        stats.value = {
            total: s.total ?? 0,
            active: s.active ?? 0,
            inactive: s.inactive ?? 0,
        };
    } catch (e) {
        console.error('Error fetching membership package stats', e);
    }
};

const fetchPackages = async () => {
    const { data } = await axios.get('/api/membership-packages', {
        params: {
            per_page: perPage.value,
            page: currentPage.value,
            search: searchFilter.value || undefined,
        },
    });
    packages.value = data.data?.data || data.data || [];
    totalItems.value = data.data?.total || packages.value.length;
    fetchStats();
};

const handlePageChange = (p: number) => {
    currentPage.value = p;
    fetchPackages();
};
const handleSearch = (s: string) => {
    searchFilter.value = s;
    currentPage.value = 1;
    fetchPackages();
};
const handlePerPageChange = (n: number) => {
    perPage.value = n;
    currentPage.value = 1;
    fetchPackages();
};

const openDrawer = () => {
    form.value = { id: null, name: '', price: 0, duration_days: 30, description: '', errors: {}, processing: false };
    priceText.value = formatRupiah(0);
    formItems.value = [];
    coverFile.value = null;
    currentCover.value = null;
    addItem();
    isDrawerOpen.value = true;
};
const closeDrawer = () => (isDrawerOpen.value = false);
const editItem = async (row: any) => {
    form.value.errors = {};
    form.value.processing = true;
    try {
        const { data } = await axios.get(`/api/membership-packages/${row.id}`);
        const pkg = data.data;

        form.value = {
            id: pkg.id,
            name: pkg.name,
            price: Number(pkg.price),
            duration_days: pkg.duration_days,
            description: pkg.description || '',
            errors: {},
            processing: false,
        };
        priceText.value = formatRupiah(form.value.price);
        currentCover.value = pkg.cover || null;
        coverFile.value = null;

        formItems.value = (pkg.items || []).map((it: any) => ({
            id: it.id,
            item_name: it.item_name,
            quantity: it.quantity,
            unit: it.unit || '',
        }));

        if (formItems.value.length === 0) {
            addItem();
        }

        isDrawerOpen.value = true;
    } catch (error) {
        console.error('Error fetching package details', error);
    } finally {
        form.value.processing = false;
    }
};
const deactivatePackage = async (row: any) => {
    if (confirm('Nonaktifkan paket ini?')) {
        await axios.delete(`/api/membership-packages/${row.id}`);
        fetchPackages();
    }
};

const activatePackage = async (row: any) => {
    if (confirm('Aktifkan paket ini?')) {
        await axios.put(`/api/membership-packages/${row.id}/activate`);
        fetchPackages();
    }
};

const saveItem = async () => {
    form.value.processing = true;
    form.value.errors = {};
    try {
        const formData = new FormData();
        formData.append('name', form.value.name);
        formData.append('price', String(form.value.price));
        formData.append('duration_days', String(form.value.duration_days));
        formData.append('description', form.value.description || '');

        formItems.value.forEach((it: any, index: number) => {
            if (it.id) {
                formData.append(`items[${index}][id]`, String(it.id));
            }
            formData.append(`items[${index}][item_name]`, String(it.item_name || ''));
            formData.append(`items[${index}][quantity]`, String(it.quantity || 1));
            formData.append(`items[${index}][unit]`, String(it.unit || ''));
        });

        if (coverFile.value) {
            formData.append('cover', coverFile.value);
        }

        if (form.value.id) {
            await axios.post(`/api/membership-packages/${form.value.id}?_method=PUT`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
        } else {
            await axios.post('/api/membership-packages', formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
        }
        closeDrawer();
        fetchPackages();
    } catch (error: any) {
        if (error.response?.status === 422) {
            form.value.errors = error.response.data.errors || {};
        } else {
            console.error('Error saving package', error);
        }
    } finally {
        form.value.processing = false;
    }
};

const resetFilter = () => (filters.value = { only_active: true });
const handleFilter = () => {
    currentPage.value = 1;
    fetchPackages();
    isFilterDrawerOpen.value = false;
};

onMounted(() => {
    fetchPackages();
    fetchStats();
});
</script>
