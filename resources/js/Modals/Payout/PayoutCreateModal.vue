<script setup>
import ModalFooter from "@/Components/Modals/Components/ModalFooter.vue";
import ModalBody from "@/Components/Modals/Components/ModalBody.vue";
import Modal from "@/Components/Modals/Modal.vue";
import ModalHeader from "@/Components/Modals/Components/ModalHeader.vue";
import { storeToRefs } from 'pinia';
import { useModalStore } from "@/store/modal.js";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import Select from "@/Components/Select.vue";
import NumberInputBlock from "@/Components/Form/NumberInputBlock.vue";
import TextInputBlock from "@/Components/Form/TextInputBlock.vue";
import AlertInfo from "@/Components/Alerts/AlertInfo.vue";
import AlertError from "@/Components/Alerts/AlertError.vue";
import { ref, watch, computed } from "vue";
import { router } from "@inertiajs/vue3";

const modalStore = useModalStore();
const { payoutCreateModal } = storeToRefs(modalStore);

const loading = ref(false);
const processing = ref(false);
const errors = ref({});

const paymentGateways = ref([]);
const merchants = ref([]);

const payoutMethodTypes = [
    { id: 'sbp', name: 'СБП' },
    { id: 'card', name: 'Карта' },
];

const requisitesMeta = computed(() => {
    if (form.value.payout_method_type === 'card') {
        return {
            label: 'Номер карты',
            placeholder: '4890 XXXX XXXX XXXX',
            helper: 'Укажите номер банковской карты получателя.',
        };
    }
    return {
        label: 'Телефон (СБП)',
        placeholder: '+7XXXXXXXXXX',
        helper: 'Укажите российский номер телефона получателя.',
    };
});

const form = ref({
    external_id: '',
    amount: null,
    payout_method_type: payoutMethodTypes[0].id,
    payment_gateway: '',
    merchant_id: 0,
    requisites: '',
    initials: '',
    callback_url: '',
});

const resetForm = () => {
    form.value = {
        external_id: '',
        amount: null,
        payout_method_type: payoutMethodTypes[0].id,
        payment_gateway: '',
        merchant_id: 0,
        requisites: '',
        initials: '',
        callback_url: '',
    };
    errors.value = {};
};

const close = () => {
    modalStore.closeModal('payoutCreate');
};

const setDefaults = () => {
    form.value.merchant_id = merchants.value[0]?.id ?? 0;
    form.value.payment_gateway = paymentGateways.value[0]?.code ?? '';
    form.value.payout_method_type = payoutMethodTypes[0].id;
    const merchant = merchants.value.find(item => item.id === form.value.merchant_id);
    form.value.callback_url = merchant?.payout_callback_url ?? '';
};

const loadData = () => {
    loading.value = true;
    axios.get(route('merchant.payouts.create-data'))
        .then(response => {
            const data = response.data?.data || response.data || {};
            paymentGateways.value = data.paymentGateways || [];
            merchants.value = data.merchants || [];
            setDefaults();
            loading.value = false;
        })
        .catch(() => {
            loading.value = false;
        });
};

const transformedPayload = () => {
    const payload = {
        ...form.value,
        merchant_id: Number(form.value.merchant_id),
    };

    payload.callback_url = payload.callback_url ? payload.callback_url : null;

    return payload;
};

const submit = () => {
    processing.value = true;
    errors.value = {};

    axios.post(route('merchant.payouts.store'), transformedPayload(), {
        headers: { 'Accept': 'application/json' }
    })
        .then(response => {
            processing.value = false;
            if (response.data?.success || response.status === 200 || response.status === 201) {
                close();
                resetForm();
                router.reload({ only: ['payouts'] });
            }
        })
        .catch(error => {
            processing.value = false;
            if (error.response && error.response.data) {
                if (error.response.data.errors) {
                    errors.value = error.response.data.errors;
                } else if (error.response.data.message) {
                    errors.value = { message: [error.response.data.message] };
                }
            }
        });
};

watch(
    () => payoutCreateModal.value.showed,
    (state) => {
        if (state) {
            resetForm();
            loadData();
        } else {
            resetForm();
            paymentGateways.value = [];
            merchants.value = [];
        }
    }
);

watch(
    () => form.value.merchant_id,
    (merchantId) => {
        const merchant = merchants.value.find(item => item.id === Number(merchantId));
        if (merchant) {
            form.value.callback_url = merchant.payout_callback_url ?? '';
        }
    }
);
</script>

<template>
    <Modal :show="payoutCreateModal.showed" @close="close" maxWidth="3xl">
        <ModalHeader @close="close" title="Создание выплаты" />

        <ModalBody>
            <div v-if="loading" class="py-6 text-center">
                <span class="loading loading-spinner loading-md"></span>
            </div>

            <form v-else @submit.prevent="submit" class="mt-2 space-y-6">
                <AlertError v-if="errors.message?.[0]" :message="errors.message?.[0]" />

                <NumberInputBlock
                    v-model="form.amount"
                    :form="{ errors }"
                    field="amount"
                    label="Сумма выплаты"
                    placeholder="0"
                />

                <TextInputBlock
                    v-model="form.external_id"
                    :form="{ errors }"
                    field="external_id"
                    label="External ID (опционально)"
                    placeholder="ID выплаты на стороне внешнего сервиса"
                />

                <div class="grid md:grid-cols-2 grid-cols-1 gap-6">
                    <div>
                        <InputLabel
                            for="payout_method_type"
                            value="Способ выплаты"
                            :error="!!errors.payout_method_type"
                            class="mb-1"
                        />
                        <Select
                            id="payout_method_type"
                            v-model="form.payout_method_type"
                            :items="payoutMethodTypes"
                            value="id"
                            name="name"
                            default_title="Выберите способ"
                            @change="errors.payout_method_type = null"
                        />
                        <InputError :message="errors.payout_method_type" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel
                            for="payment_gateway"
                            value="Платёжный метод"
                            :error="!!errors.payment_gateway"
                            class="mb-1"
                        />
                        <Select
                            id="payment_gateway"
                            v-model="form.payment_gateway"
                            :error="!!errors.payment_gateway"
                            :items="paymentGateways"
                            value="code"
                            name="name"
                            default_title="Выберите метод"
                            default_value=""
                            @change="errors.payment_gateway = null"
                        />
                        <InputError :message="errors.payment_gateway" class="mt-1" />
                    </div>
                </div>

                <div class="grid md:grid-cols-2 grid-cols-1 gap-6">
                    <div>
                        <InputLabel
                            for="merchant_id"
                            value="Мерчант"
                            :error="!!errors.merchant_id"
                            class="mb-1"
                        />
                        <Select
                            id="merchant_id"
                            v-model="form.merchant_id"
                            :error="!!errors.merchant_id"
                            :items="merchants"
                            value="id"
                            name="name"
                            default_title="Выберите мерчанта"
                            default_value="0"
                            @change="errors.merchant_id = null"
                        />
                        <InputError :message="errors.merchant_id" class="mt-1" />
                    </div>

                    <div>
                        <TextInputBlock
                            v-model="form.initials"
                            :form="{ errors }"
                            field="initials"
                            label="Получатель"
                            placeholder="ФИО получателя"
                        />
                    </div>
                </div>

                <TextInputBlock
                    v-model="form.requisites"
                    :form="{ errors }"
                    field="requisites"
                    :label="requisitesMeta.label"
                    :helper="requisitesMeta.helper"
                    :placeholder="requisitesMeta.placeholder"
                />

                <TextInputBlock
                    v-model="form.callback_url"
                    :form="{ errors }"
                    field="callback_url"
                    label="Callback URL (опционально)"
                    placeholder="https://example.com/payout-callback"
                    helper="Если не указано — используется callback из настроек мерчанта."
                />

                <AlertInfo message="Средства будут зарезервированы с баланса мерчанта сразу после создания выплаты." />
            </form>
        </ModalBody>

        <ModalFooter>
            <button @click="close" type="button" class="btn btn-sm">
                Отмена
            </button>
            <button
                @click="submit"
                type="button"
                class="btn btn-sm btn-primary"
                :class="{ 'btn-disabled': processing }"
                :disabled="processing"
            >
                Сохранить
            </button>
        </ModalFooter>
    </Modal>
</template>

