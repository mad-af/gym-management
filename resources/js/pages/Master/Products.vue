<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="currentPageTitle" />
        <Stats2 :items="statsItems" />

        <DynamicTable
            :columns="columns"
            :data="tableData"
            :items-per-page="perPage"
            :total-items="totalItems"
            :current-page="currentPage"
            :is-server-side="true"
            @update:page="handlePageChange"
            @update:search="handleSearch"
            @update:perPage="handlePerPageChange"
        >
            <template #header-actions>
                <Button
                    size="sm"
                    variant="outline"
                    :onClick="() => (isFilterDrawerOpen = true)"
                    className="w-full sm:w-auto"
                    :startIcon="FilterIcon"
                >
                    Filter
                </Button>
                <Button
                    v-can="'create_products'"
                    size="sm"
                    variant="primary"
                    :onClick="openDrawer"
                    className="w-full sm:w-auto"
                    :endIcon="PlusIcon"
                >
                    Tambah Produk
                </Button>
            </template>
            <template #actions="{ row }">
                <div class="flex items-center gap-2">
                    <button
                        v-can="'edit_products'"
                        @click="editItem(row)"
                        class="text-gray-500 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-500"
                        title="Edit"
                    >
                        <PencilIcon class="h-4.5 w-4.5" />
                    </button>
                    <button
                        v-can="'delete_products'"
                        v-if="row.is_active === 'Active'"
                        @click="deactivateItem(row)"
                        class="text-gray-500 hover:text-error-500 dark:text-gray-400 dark:hover:text-error-500"
                        title="Nonaktifkan"
                    >
                        <TrashIcon class="h-4.5 w-4.5" />
                    </button>
                    <button
                        v-can="'edit_products'"
                        v-else
                        @click="activateItem(row)"
                        class="text-gray-500 hover:text-success-500 dark:text-gray-400 dark:hover:text-success-500"
                        title="Aktifkan"
                    >
                        <CheckCircleIcon class="h-4.5 w-4.5" />
                    </button>
                </div>
            </template>
        </DynamicTable>

        <Drawer
            :isOpen="isDrawerOpen"
            @close="closeDrawer"
            :title="form.id ? 'Edit Produk' : 'Tambah Produk'"
        >
            <div class="space-y-6">
                <div class="flex justify-start">
                    <AvatarInput
                        v-model="coverFile"
                        :src="currentCoverSrc"
                        :placeholder="currentCoverPlaceholder"
                        size="large"
                        variant="square"
                    />
                </div>
                <div>
                    <label
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200"
                        >Nama Produk</label
                    >
                    <input
                        type="text"
                        v-model="form.name"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                    />
                    <p
                        v-if="form.errors.name"
                        class="mt-1 text-sm text-error-500"
                    >
                        {{ form.errors.name }}
                    </p>
                </div>
                <div>
                    <label
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200"
                        >Harga</label
                    >
                    <input
                        type="text"
                        v-model="priceText"
                        inputmode="numeric"
                        @focus="handlePriceFocus"
                        @input="handlePriceInput"
                        @blur="handlePriceBlur"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                    />
                    <p
                        v-if="form.errors.price"
                        class="mt-1 text-sm text-error-500"
                    >
                        {{ form.errors.price }}
                    </p>
                </div>
                <div>
                    <label
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200"
                        >Harga Modal</label
                    >
                    <input
                        type="text"
                        v-model="capitalPriceText"
                        inputmode="numeric"
                        @focus="handleCapitalPriceFocus"
                        @input="handleCapitalPriceInput"
                        @blur="handleCapitalPriceBlur"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                    />
                    <p
                        v-if="form.errors.capital_price"
                        class="mt-1 text-sm text-error-500"
                    >
                        {{ form.errors.capital_price }}
                    </p>
                </div>
                <div v-if="!form.id">
                    <label
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200"
                        >Stok</label
                    >
                    <input
                        type="number"
                        v-model.number="form.stock"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                    />
                    <p
                        v-if="form.errors.stock"
                        class="mt-1 text-sm text-error-500"
                    >
                        {{ form.errors.stock }}
                    </p>
                </div>
            </div>
            <template #footer>
                <div class="flex w-full justify-end gap-3">
                    <Button variant="outline" :onClick="closeDrawer"
                        >Batal</Button
                    >
                    <Button
                        variant="primary"
                        :onClick="saveItem"
                        :disabled="form.processing"
                        >Simpan</Button
                    >
                </div>
            </template>
        </Drawer>

        <Drawer
            :isOpen="isFilterDrawerOpen"
            @close="isFilterDrawerOpen = false"
            title="Filter Produk"
        >
            <div class="space-y-6">
                <div>
                    <label
                        class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-200"
                    >
                        <input
                            type="checkbox"
                            v-model="filters.only_active"
                            class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800"
                        />
                        Hanya aktif
                    </label>
                </div>
            </div>
            <template #footer>
                <div class="flex w-full justify-end gap-3">
                    <Button variant="outline" :onClick="resetFilter"
                        >Reset</Button
                    >
                    <Button variant="primary" :onClick="handleFilter"
                        >Terapkan Filter</Button
                    >
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
import {
    PlusIcon,
    TrashIcon,
    CheckCircleIcon,
    PencilIcon,
    FilterIcon,
    PackageIcon,
    WarningIcon,
    ErrorIcon,
} from '@/icons';

const currentPageTitle = ref('Products');
const isDrawerOpen = ref(false);
const isFilterDrawerOpen = ref(false);
const filters = ref({ only_active: false });

const items = ref<any[]>([]);
const totalItems = ref(0);
const currentPage = ref(1);
const perPage = ref(10);
const searchFilter = ref('');

const stats = ref({
    total: 0,
    active: 0,
    outOfStock: 0,
    lowStock: 0,
});

const statsItems = computed(() => [
    {
        label: 'Total Produk',
        value: stats.value.total,
        icon: PackageIcon,
        iconBgClass: 'bg-brand-50 text-brand-500 dark:bg-brand-500/10',
    },
    {
        label: 'Produk Aktif',
        value: stats.value.active,
        icon: CheckCircleIcon,
        iconBgClass: 'bg-success-50 text-success-600 dark:bg-success-500/10',
    },
    {
        label: 'Stok Habis',
        value: stats.value.outOfStock,
        icon: ErrorIcon,
        iconBgClass: 'bg-error-50 text-error-600 dark:bg-error-500/10',
    },
    {
        label: 'Stok Rendah',
        value: stats.value.lowStock,
        icon: WarningIcon,
        iconBgClass: 'bg-yellow-50 text-yellow-600 dark:bg-yellow-500/10',
    },
]);

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
    capital_price: 0,
    stock: 0,
    errors: {} as Record<string, string>,
    processing: false,
});

const priceText = ref('Rp 0');
const capitalPriceText = ref('Rp 0');

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

const handleCapitalPriceFocus = () => {
    capitalPriceText.value = form.value.capital_price
        ? String(form.value.capital_price)
        : '';
};

const handleCapitalPriceInput = () => {
    const digits = (capitalPriceText.value || '').replace(/\D/g, '');
    capitalPriceText.value = digits;
    form.value.capital_price = digits ? Number(digits) : 0;
};

const handleCapitalPriceBlur = () => {
    capitalPriceText.value = formatRupiah(form.value.capital_price);
};

const tableData = computed(() =>
    items.value.map((p: any) => ({
        id: p.id,
        cover: p.cover || null,
        name: p.name,
        price: formatRupiah(p.price),
        capital_price: p.capital_price ? formatRupiah(p.capital_price) : '-',
        stock: p.stock ?? '-',
        is_active: p.is_active ? 'Active' : 'Inactive',
    })),
);

const columns = ref<Column[]>([
    {
        key: 'name',
        label: 'Produk',
        sortable: true,
        type: 'cover',
        avatarField: 'cover',
        labelField: 'name',
        class: 'min-w-[240px]',
    },
    { key: 'price', label: 'Harga', class: 'min-w-[140px]' },
    { key: 'capital_price', label: 'Harga Modal', class: 'min-w-[140px]' },
    { key: 'stock', label: 'Stok', class: 'min-w-[100px]' },
    {
        key: 'is_active',
        label: 'Status',
        type: 'status',
        class: 'min-w-[120px]',
        statusMap: {
            Active: 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500',
            Inactive:
                'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-500',
        },
    },
    { key: 'actions', label: 'Aksi', type: 'action', class: 'w-[120px]' },
]);

const fetchStats = async () => {
    try {
        const { data } = await axios.get('/api/products/stats');
        const s = data.data || data;
        stats.value = {
            total: s.total ?? 0,
            active: s.active ?? 0,
            outOfStock: s.outOfStock ?? 0,
            lowStock: s.lowStock ?? 0,
        };
    } catch (e) {
        console.error('Error fetching product stats', e);
    }
};

const fetchItems = async () => {
    const { data } = await axios.get('/api/products', {
        params: {
            per_page: perPage.value,
            page: currentPage.value,
            search: searchFilter.value || undefined,
            is_active: filters.value.only_active ? true : undefined,
        },
    });
    items.value = data.data?.data || data.data || [];
    totalItems.value = data.data?.total || items.value.length;
    fetchStats();
};

const handlePageChange = (p: number) => {
    currentPage.value = p;
    fetchItems();
};
const handleSearch = (s: string) => {
    searchFilter.value = s;
    currentPage.value = 1;
    fetchItems();
};
const handlePerPageChange = (n: number) => {
    perPage.value = n;
    currentPage.value = 1;
    fetchItems();
};

const openDrawer = () => {
    form.value = {
        id: null,
        name: '',
        price: 0,
        capital_price: 0,
        stock: 0,
        errors: {},
        processing: false,
    };
    priceText.value = formatRupiah(0);
    capitalPriceText.value = formatRupiah(0);
    coverFile.value = null;
    currentCover.value = null;
    isDrawerOpen.value = true;
};
const closeDrawer = () => (isDrawerOpen.value = false);
const editItem = (row: any) => {
    const rowCapitalPrice =
        row.capital_price && row.capital_price !== '-'
            ? Number(String(row.capital_price).replace(/\D/g, '')) || 0
            : 0;
    form.value = {
        id: row.id,
        name: row.name,
        price: Number(String(row.price).replace(/\D/g, '')) || 0,
        capital_price: rowCapitalPrice,
        stock: row.stock === '-' ? 0 : row.stock,
        errors: {},
        processing: false,
    };
    priceText.value = formatRupiah(form.value.price);
    capitalPriceText.value = formatRupiah(form.value.capital_price);
    coverFile.value = null;
    currentCover.value = row.cover || null;
    isDrawerOpen.value = true;
};

const deactivateItem = async (row: any) => {
    if (confirm('Nonaktifkan produk ini?')) {
        await axios.delete(`/api/products/${row.id}`);
        fetchItems();
    }
};

const activateItem = async (row: any) => {
    await axios.put(`/api/products/${row.id}/activate`);
    fetchItems();
};

const saveItem = async () => {
    form.value.processing = true;
    form.value.errors = {};
    try {
        const payload = new FormData();
        payload.append('name', form.value.name);
        payload.append('price', String(form.value.price));
        if (form.value.capital_price > 0) {
            payload.append('capital_price', String(form.value.capital_price));
        }

        if (!form.value.id && form.value.stock !== null) {
            payload.append('stock', String(form.value.stock));
        }

        if (coverFile.value) {
            payload.append('cover', coverFile.value);
        }

        if (form.value.id) {
            await axios.post(
                `/api/products/${form.value.id}?_method=PUT`,
                payload,
                {
                    headers: { 'Content-Type': 'multipart/form-data' },
                },
            );
        } else {
            await axios.post('/api/products', payload, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
        }
        closeDrawer();
        fetchItems();
    } catch (error: any) {
        if (error.response?.status === 422) {
            form.value.errors = error.response.data.errors || {};
        } else {
            console.error('Error saving product', error);
        }
    } finally {
        form.value.processing = false;
    }
};

const resetFilter = () => (filters.value = { only_active: false });
const handleFilter = () => {
    currentPage.value = 1;
    fetchItems();
    isFilterDrawerOpen.value = false;
};

onMounted(() => {
    fetchItems();
    fetchStats();
});
</script>
