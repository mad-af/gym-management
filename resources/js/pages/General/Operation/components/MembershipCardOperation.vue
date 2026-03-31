<template>
    <div>
        <OperationActionButton
            :icon="UserCircleIcon"
            title="Member Card"
            description="Cetak dan kirim kartu member."
            iconBgClass="bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10"
            glowClass="bg-indigo-400"
            @click="isOpen = true"
        />

        <Drawer :isOpen="isOpen" title="Member Card" @close="closeDrawer">
            <div class="space-y-6">
                <nav
                    class="flex overflow-x-auto rounded-lg bg-gray-100 p-1 dark:bg-gray-900"
                >
                    <button
                        type="button"
                        @click="activeTab = 'print'"
                        :class="[
                            'inline-flex flex-1 items-center justify-center rounded-md px-3 py-2 text-sm font-medium transition-colors duration-200 ease-in-out',
                            activeTab === 'print'
                                ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-white/[0.03] dark:text-white'
                                : 'bg-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200',
                        ]"
                    >
                        Print PDF
                    </button>
                    <button
                        type="button"
                        @click="activeTab = 'whatsapp'"
                        :class="[
                            'inline-flex flex-1 items-center justify-center rounded-md px-3 py-2 text-sm font-medium transition-colors duration-200 ease-in-out',
                            activeTab === 'whatsapp'
                                ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-white/[0.03] dark:text-white'
                                : 'bg-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200',
                        ]"
                    >
                        Kirim WhatsApp
                    </button>
                </nav>

                <div class="space-y-2">
                    <label
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200"
                        >Customer</label
                    >
                    <Combobox
                        v-model="selectedCustomerId"
                        :options="customerOptions"
                        labelKey="name"
                        descriptionKey="phone"
                        valueKey="id"
                        placeholder="Pilih customer..."
                        :loading="customerLoading"
                        remote
                        @search="onCustomerSearch"
                        @load-more="onCustomerLoadMore"
                    />
                    <p
                        v-if="errors.customer_id"
                        class="mt-1 text-sm text-error-500"
                    >
                        {{ errors.customer_id }}
                    </p>
                </div>

                <div
                    v-if="selectedCustomer"
                    class="rounded-xl border border-gray-200 bg-white p-3 text-sm dark:border-gray-700 dark:bg-gray-900"
                >
                    <p class="font-medium text-gray-900 dark:text-white/90">
                        {{ selectedCustomer.name }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Kode:
                        {{ selectedCustomer.code || '-' }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Nomor: {{ selectedCustomer.phone || '-' }}
                    </p>
                </div>

                <div v-if="activeTab === 'print'" class="space-y-2">
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        Cetak kartu member dalam format PDF berdasarkan data
                        membership aktif customer.
                    </p>
                    <Button
                        variant="outline"
                        :onClick="printCard"
                        :disabled="!selectedCustomerId"
                    >
                        Buka PDF
                    </Button>
                </div>

                <div v-else class="space-y-3">
                    <div class="space-y-2">
                        <label
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200"
                            >Nomor Tujuan WhatsApp</label
                        >
                        <input
                            v-model="targetPhone"
                            type="text"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                            placeholder="Contoh: 081234567890"
                        />
                        <p
                            v-if="errors.target"
                            class="mt-1 text-sm text-error-500"
                        >
                            {{ errors.target }}
                        </p>
                    </div>

                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Jika dikosongkan, sistem akan memakai nomor customer.
                    </p>

                    <Button
                        variant="primary"
                        :onClick="sendWhatsapp"
                        :disabled="sendDisabled"
                    >
                        {{
                            sending ? 'Mengirim...' : 'Kirim Kartu via WhatsApp'
                        }}
                    </Button>
                    <p
                        v-if="successMessage"
                        class="text-sm text-success-600 dark:text-success-400"
                    >
                        {{ successMessage }}
                    </p>
                </div>
            </div>

            <template #footer>
                <div class="flex w-full justify-end">
                    <Button variant="outline" :onClick="closeDrawer"
                        >Tutup</Button
                    >
                </div>
            </template>
        </Drawer>
    </div>
</template>

<script setup lang="ts">
import axios from 'axios';
import { computed, ref, watch } from 'vue';
import Button from '@/components/ui/Button.vue';
import Combobox from '@/components/ui/Combobox.vue';
import Drawer from '@/components/ui/Drawer.vue';
import OperationActionButton from '@/components/ui/OperationActionButton.vue';
import { UserCircleIcon } from '@/icons';

interface CustomerOption {
    id: string;
    name: string;
    code?: string | null;
    phone?: string | null;
}

const isOpen = ref(false);
const activeTab = ref<'print' | 'whatsapp'>('print');

const selectedCustomerId = ref<string | null>(null);
const selectedCustomer = ref<CustomerOption | null>(null);
const customerOptions = ref<CustomerOption[]>([]);
const customerLoading = ref(false);
const customerSearch = ref('');
const customerPage = ref(1);
const customerHasMore = ref(true);

const targetPhone = ref('');
const sending = ref(false);
const successMessage = ref('');
const errors = ref<Record<string, string>>({});

const fetchCustomerOptions = async (reset = false) => {
    if (reset) {
        customerPage.value = 1;
        customerOptions.value = [];
        customerHasMore.value = true;
    }
    if (!customerHasMore.value && !reset) {
        return;
    }

    customerLoading.value = true;
    try {
        const { data } = await axios.get('/api/customers/selection', {
            params: {
                per_page: 20,
                page: customerPage.value,
                search: customerSearch.value || undefined,
                is_member: true,
            },
        });

        const payload = data?.data;
        const arr = Array.isArray(payload?.data)
            ? payload.data
            : Array.isArray(payload)
              ? payload
              : [];

        const mapped = arr.map((item: Record<string, unknown>) => ({
            id: String(item.id ?? ''),
            name: String(item.name ?? '-'),
            code: typeof item.code === 'string' ? item.code : null,
            phone: typeof item.phone === 'string' ? item.phone : null,
        }));

        customerOptions.value = reset
            ? mapped
            : [
                  ...customerOptions.value,
                  ...mapped.filter(
                      (item) =>
                          !customerOptions.value.some(
                              (existing) => existing.id === item.id,
                          ),
                  ),
              ];

        customerHasMore.value = Boolean(payload?.next_page_url);
        customerPage.value += 1;
    } finally {
        customerLoading.value = false;
    }
};

const fetchCustomerDetail = async (customerId: string) => {
    try {
        const { data } = await axios.get(`/api/customers/${customerId}`);
        const customer = data?.data ?? data;
        selectedCustomer.value = {
            id: String(customer?.id ?? customerId),
            name: String(customer?.name ?? '-'),
            code: typeof customer?.code === 'string' ? customer.code : null,
            phone: typeof customer?.phone === 'string' ? customer.phone : null,
        };
        if (!targetPhone.value) {
            targetPhone.value = selectedCustomer.value.phone || '';
        }
    } catch {
        selectedCustomer.value = null;
    }
};

watch(selectedCustomerId, (id) => {
    errors.value = {};
    successMessage.value = '';
    if (!id) {
        selectedCustomer.value = null;
        targetPhone.value = '';
        return;
    }

    fetchCustomerDetail(id);
});

const onCustomerSearch = (query: string) => {
    customerSearch.value = query;
    fetchCustomerOptions(true);
};

const onCustomerLoadMore = () => {
    fetchCustomerOptions(false);
};

const printCard = () => {
    errors.value = {};
    successMessage.value = '';
    if (!selectedCustomerId.value) {
        errors.value.customer_id = 'Customer wajib dipilih.';
        return;
    }

    const url = `/api/membership-cards/print?customer_id=${encodeURIComponent(selectedCustomerId.value)}`;
    window.open(url, '_blank');
};

const sendDisabled = computed(() => {
    if (sending.value) {
        return true;
    }
    return !selectedCustomerId.value;
});

const sendWhatsapp = async () => {
    errors.value = {};
    successMessage.value = '';
    if (!selectedCustomerId.value) {
        errors.value.customer_id = 'Customer wajib dipilih.';
        return;
    }

    sending.value = true;
    try {
        const { data } = await axios.post(
            '/api/membership-cards/send-whatsapp',
            {
                customer_id: selectedCustomerId.value,
                target: targetPhone.value || undefined,
            },
        );

        successMessage.value =
            data?.message || 'Kartu member berhasil dikirim.';
        emit('submitted');
    } catch (e: unknown) {
        const error = e as {
            response?: {
                status?: number;
                data?: {
                    message?: string;
                    errors?: Record<string, string[] | string>;
                };
            };
        };

        if (error.response?.status === 422 && error.response.data?.errors) {
            const validationErrors = error.response.data.errors;
            errors.value = Object.keys(validationErrors).reduce(
                (acc, key) => {
                    acc[key] = Array.isArray(validationErrors[key])
                        ? (validationErrors[key] as string[])[0]
                        : String(validationErrors[key]);
                    return acc;
                },
                {} as Record<string, string>,
            );
        } else {
            errors.value.target =
                error.response?.data?.message || 'Gagal mengirim kartu member.';
        }
    } finally {
        sending.value = false;
    }
};

const closeDrawer = () => {
    isOpen.value = false;
    activeTab.value = 'print';
    selectedCustomerId.value = null;
    selectedCustomer.value = null;
    customerSearch.value = '';
    targetPhone.value = '';
    successMessage.value = '';
    sending.value = false;
    errors.value = {};
};

watch(isOpen, (open) => {
    if (!open) {
        return;
    }

    customerSearch.value = '';
    fetchCustomerOptions(true);
});

const emit = defineEmits<{ (e: 'submitted'): void }>();
</script>
