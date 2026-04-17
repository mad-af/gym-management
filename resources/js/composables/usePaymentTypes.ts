import axios from 'axios';
import { ref } from 'vue';

export type PaymentTypeOption = {
    value: string;
    label: string;
};

const paymentTypeOptions = ref<PaymentTypeOption[]>([]);
let loaded = false;

export function usePaymentTypes() {
    const fetchPaymentTypes = async () => {
        if (loaded) return;

        try {
            const { data } = await axios.get('/api/payment-types/selection');
            paymentTypeOptions.value = data.data || [];
            loaded = true;
        } catch (e) {
            console.error('Error fetching payment types', e);
        }
    };

    return {
        paymentTypeOptions,
        fetchPaymentTypes,
    };
}
