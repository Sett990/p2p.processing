<script setup>
import ModalFooter from "@/Components/Modals/Components/ModalFooter.vue";
import ModalBody from "@/Components/Modals/Components/ModalBody.vue";
import Modal from "@/Components/Modals/Modal.vue";
import ModalHeader from "@/Components/Modals/Components/ModalHeader.vue";
import { storeToRefs } from 'pinia';
import { useModalStore } from "@/store/modal.js";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import NumberInput from "@/Components/NumberInput.vue";
import InputHelper from "@/Components/InputHelper.vue";
import DropDownWithCheckbox from "@/Components/Form/DropDownWithCheckbox.vue";
import DropDownWithRadio from "@/Components/Form/DropDownWithRadio.vue";
import TextInputBlock from "@/Components/Form/TextInputBlock.vue";
import Dropzone from "@/Components/Form/Dropzone.vue";
import { ref, watch } from "vue";
import { router } from '@inertiajs/vue3';

const modalStore = useModalStore();
const { paymentGatewayEditModal } = storeToRefs(modalStore);

const loading = ref(false);
const processing = ref(false);
const errors = ref({});

const currencies = ref([]);
const detail_types = ref([]);

const paymentGateway = ref(null);
const sms_sender = ref(null);

const form = ref({
    name: null,
    code: null,
    nspk_schema: null,
    min_limit: null,
    max_limit: null,
    trader_commission_rate_for_orders: null,
    trader_commission_rate_for_payouts: null,
    total_service_commission_rate_for_orders: null,
    total_service_commission_rate_for_payouts: null,
    is_active: true,
    is_intrabank: false,
    reservation_time_for_orders: null,
    reservation_time_for_payouts: null,
    currency: 'RUB',
    detail_types: [],
    sms_senders: [],
    logo: null
});

const resetForm = () => {
    form.value = {
        name: null,
        code: null,
        nspk_schema: null,
        min_limit: null,
        max_limit: null,
        trader_commission_rate_for_orders: null,
        trader_commission_rate_for_payouts: null,
        total_service_commission_rate_for_orders: null,
        total_service_commission_rate_for_payouts: null,
        is_active: true,
        is_intrabank: false,
        reservation_time_for_orders: null,
        reservation_time_for_payouts: null,
        currency: 'RUB',
        detail_types: [],
        sms_senders: [],
        logo: null
    };
    sms_sender.value = null;
    errors.value = {};
    paymentGateway.value = null;
};

const close = () => {
    modalStore.closeModal('paymentGatewayEdit');
};

const loadData = () => {
    if (!paymentGatewayEditModal.value.params?.paymentGatewayId) return;
    loading.value = true;
    axios.get(route('admin.payment-gateways.edit-data', paymentGatewayEditModal.value.params.paymentGatewayId))
        .then(response => {
            const data = response.data?.data || response.data || {};
            currencies.value = data.currencies || [];
            detail_types.value = data.detailTypes || [];
            paymentGateway.value = data.paymentGateway;
            // init form
            form.value.name = paymentGateway.value.original_name;
            form.value.code = paymentGateway.value.code;
            form.value.nspk_schema = paymentGateway.value.nspk_schema;
            form.value.min_limit = paymentGateway.value.min_limit;
            form.value.max_limit = paymentGateway.value.max_limit;
            form.value.trader_commission_rate_for_orders = paymentGateway.value.trader_commission_rate_for_orders;
            form.value.trader_commission_rate_for_payouts = paymentGateway.value.trader_commission_rate_for_payouts;
            form.value.total_service_commission_rate_for_orders = paymentGateway.value.total_service_commission_rate_for_orders;
            form.value.total_service_commission_rate_for_payouts = paymentGateway.value.total_service_commission_rate_for_payouts;
            form.value.is_active = !!paymentGateway.value.is_active;
            form.value.is_intrabank = !!paymentGateway.value.is_intrabank;
            form.value.reservation_time_for_orders = paymentGateway.value.reservation_time_for_orders;
            form.value.reservation_time_for_payouts = paymentGateway.value.reservation_time_for_payouts;
            form.value.currency = (paymentGateway.value.currency || 'RUB').toUpperCase();
            form.value.detail_types = paymentGateway.value.detail_types ?? [];
            form.value.sms_senders = paymentGateway.value.sms_senders ?? [];
            loading.value = false;
        })
        .catch(() => {
            loading.value = false;
        });
};

const addSender = () => {
    if (!sms_sender.value) {
        return;
    }
    form.value.sms_senders.push(sms_sender.value);
    form.value.sms_senders = form.value.sms_senders.filter((value, index, array) => array.indexOf(value) === index);
    sms_sender.value = null;
};

const removeSender = (sender) => {
    form.value.sms_senders = form.value.sms_senders.filter((item) => item !== sender);
};

watch(() => form.value.is_intrabank, (newValue) => {
    if (newValue) {
        form.value.detail_types = form.value.detail_types.filter(type => type !== 'phone');
    }
});

const toFormData = () => {
    const fd = new FormData();
    fd.append('name', form.value.name ?? '');
    fd.append('code', form.value.code ?? '');
    fd.append('nspk_schema', form.value.nspk_schema ?? '');
    fd.append('min_limit', form.value.min_limit ?? '');
    fd.append('max_limit', form.value.max_limit ?? '');
    fd.append('trader_commission_rate_for_orders', form.value.trader_commission_rate_for_orders ?? '');
    fd.append('trader_commission_rate_for_payouts', form.value.trader_commission_rate_for_payouts ?? '');
    fd.append('total_service_commission_rate_for_orders', form.value.total_service_commission_rate_for_orders ?? '');
    fd.append('total_service_commission_rate_for_payouts', form.value.total_service_commission_rate_for_payouts ?? '');
    fd.append('is_active', form.value.is_active ? '1' : '0');
    fd.append('is_intrabank', form.value.is_intrabank ? '1' : '0');
    fd.append('reservation_time_for_orders', form.value.reservation_time_for_orders ?? '');
    fd.append('reservation_time_for_payouts', form.value.reservation_time_for_payouts ?? '');
    fd.append('currency', (form.value.currency || 'RUB').toString().toUpperCase());
    (form.value.detail_types || []).forEach(v => fd.append('detail_types[]', v));
    (form.value.sms_senders || []).forEach(v => fd.append('sms_senders[]', v));
    if (form.value.logo) {
        fd.append('logo', form.value.logo);
    }
    fd.append('_method', 'PATCH');
    return fd;
};

const submit = () => {
    if (!paymentGateway.value) return;
    processing.value = true;
    errors.value = {};
    axios.post(route('admin.payment-gateways.update', paymentGateway.value.id), toFormData(), {
        headers: { 'Accept': 'application/json' }
    })
        .then(response => {
            processing.value = false;
            if (response.data?.success || response.status === 200 || response.status === 204) {
                close();
                resetForm();
                router.reload({ only: ['paymentGateways'] });
            }
        })
        .catch(error => {
            processing.value = false;
            if (error.response && error.response.data) {
                if (error.response.data.errors) {
                    errors.value = error.response.data.errors;
                } else if (error.response.data.message) {
                    errors.value = { name: [error.response.data.message] };
                }
            }
        });
};

watch(
    () => paymentGatewayEditModal.value.showed,
    (state) => {
        if (state) {
            resetForm();
            loadData();
        } else {
            resetForm();
        }
    }
);
</script>

<template>
    <Modal :show="paymentGatewayEditModal.showed" @close="close" maxWidth="4xl">
        <ModalHeader @close="close" :title="paymentGateway ? ('Редактирование платежного метода - ' + paymentGateway.name) : 'Редактирование платежного метода'" />

        <ModalBody>
            <div v-if="loading" class="py-6 text-center">
                <span class="loading loading-spinner loading-md"></span>
            </div>
            <div v-else>
                <form @submit.prevent="submit" class="mt-2 space-y-6" v-if="paymentGateway">
                    <TextInputBlock
                        v-model="form.name"
                        :form="{ errors: errors }"
                        field="name"
                        label="Название"
                        placeholder="Сбербанк"
                    />

                    <div class="grid md:grid-cols-2 grid-cols-1 gap-6">
                        <TextInputBlock
                            v-model="form.code"
                            :form="{ errors: errors }"
                            field="code"
                            label="Код метода"
                            helper="Например: sberbank"
                        />

                        <TextInputBlock
                            v-model="form.nspk_schema"
                            :form="{ errors: errors }"
                            field="nspk_schema"
                            label="Scheme"
                            helper="Например: 100000000111"
                        />
                    </div>

                    <div>
                        <DropDownWithCheckbox
                            v-model="form.detail_types"
                            :items="detail_types.filter(type => !form.is_intrabank || type.code !== 'phone')"
                            value="code"
                            name="name"
                            label="Тип реквизитов"
                        />
                        <InputError :message="errors.detail_types?.[0]" class="mt-2" />
                        <InputHelper v-if="form.is_intrabank" model-value="Тип 'СБП' недоступен для внутрибанковского перевода"></InputHelper>
                    </div>

                    <div>
                        <DropDownWithRadio
                            v-model="form.currency"
                            :items="currencies"
                            value="code"
                            name="code"
                            label="Валюта"
                        />
                        <InputError :message="errors.currency?.[0]" class="mt-2" />
                    </div>

                    <div class="grid md:grid-cols-2 grid-cols-1 gap-6">
                        <div>
                            <InputLabel
                                for="min_limit"
                                :value="'Минимальная сумма в ' + (form.currency || 'RUB')?.toUpperCase()"
                                :error="!!errors.min_limit?.[0]"
                            />

                            <NumberInput
                                id="min_limit"
                                v-model="form.min_limit"
                                class="mt-1 block w-full"
                                placeholder="0"
                                :error="!!errors.min_limit?.[0]"
                                @input="errors.min_limit = null"
                            />

                            <InputError :message="errors.min_limit?.[0]" class="mt-2" />
                            <InputHelper v-if="! errors.min_limit" model-value="Минимальный лимит на одну операцию"></InputHelper>
                        </div>

                        <div>
                            <InputLabel
                                for="max_limit"
                                :value="'Максимальная сумма в ' + (form.currency || 'RUB')?.toUpperCase()"
                                :error="!!errors.max_limit?.[0]"
                            />

                            <NumberInput
                                id="max_limit"
                                v-model="form.max_limit"
                                class="mt-1 block w-full"
                                placeholder="0"
                                :error="!!errors.max_limit?.[0]"
                                @input="errors.max_limit = null"
                            />

                            <InputError :message="errors.max_limit?.[0]" class="mt-2" />
                            <InputHelper v-if="! errors.max_limit" model-value="Максимальный лимит на одну операцию"></InputHelper>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-1 grid-cols-1 gap-6">
                        <div>
                            <InputLabel
                                for="trader_commission_rate_for_orders"
                                value="Комиссия трейдера %"
                                :error="!!errors.trader_commission_rate_for_orders?.[0]"
                            />

                            <NumberInput
                                id="trader_commission_rate_for_orders"
                                v-model="form.trader_commission_rate_for_orders"
                                class="mt-1 block w-full"
                                step="0.1"
                                placeholder="0.0"
                                :error="!!errors.trader_commission_rate_for_orders?.[0]"
                                @input="errors.trader_commission_rate_for_orders = null"
                            />

                            <InputError :message="errors.trader_commission_rate_for_orders?.[0]" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid md:grid-cols-1 grid-cols-1 gap-6">
                        <div>
                            <InputLabel
                                for="total_service_commission_rate_for_orders"
                                value="Комиссия сервиса в %"
                                :error="!!errors.total_service_commission_rate_for_orders?.[0]"
                            />

                            <NumberInput
                                id="total_service_commission_rate_for_orders"
                                v-model="form.total_service_commission_rate_for_orders"
                                class="mt-1 block w-full"
                                step="0.1"
                                placeholder="0.0"
                                :error="!!errors.total_service_commission_rate_for_orders?.[0]"
                                @input="errors.total_service_commission_rate_for_orders = null"
                            />

                            <InputError :message="errors.total_service_commission_rate_for_orders?.[0]" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid md:grid-cols-1 grid-cols-1 gap-6">
                        <div>
                            <InputLabel
                                for="reservation_time_for_orders"
                                value="Время удержания реквизитов"
                                :error="!!errors.reservation_time_for_orders?.[0]"
                            />

                            <NumberInput
                                id="reservation_time_for_orders"
                                v-model="form.reservation_time_for_orders"
                                class="mt-1 block w-full"
                                placeholder="0"
                                :error="!!errors.reservation_time_for_orders?.[0]"
                                @input="errors.reservation_time_for_orders = null"
                            />

                            <InputError :message="errors.reservation_time_for_orders?.[0]" class="mt-2" />
                            <InputHelper v-if="! errors.reservation_time_for_orders" model-value="Время на одну операцию обмена в минутах"></InputHelper>
                        </div>
                    </div>

                    <div>
                        <InputLabel
                            for="sms_senders"
                            value="Отправители смс/push"
                            :error="!!errors.sms_senders?.[0]"
                            class="mb-1"
                        />

                        <div class="relative">
                            <TextInput
                                id="sms_senders"
                                v-model="sms_sender"
                                class="block w-full"
                                :error="!!errors.sms_senders?.[0]"
                                @input="errors.sms_senders = null"
                            />

                            <button @click.prevent="addSender" type="button" class="btn btn-primary btn-sm absolute end-1.5 sm:bottom-1 bottom-1.5">Добавить</button>
                        </div>

                        <InputError :message="errors.sms_senders?.[0]" class="mt-2" />
                        <InputHelper v-if="! errors.sms_senders" model-value="Например: 900, Alfabank"></InputHelper>

                        <div class="flex flex-wrap gap-0.5 mt-2">
                            <div v-for="sender in form.sms_senders" :key="sender">
                                <span class="badge badge-ghost inline-flex items-center me-2">
                                    {{ sender }}
                                    <svg @click="removeSender(sender)" class="w-3 h-3 ml-1.5 cursor-pointer" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <InputLabel
                            for="logo"
                            value="Загрузите логотип метода"
                            class="mb-1"
                            :error="!!errors.logo?.[0]"
                        />
                        <Dropzone
                            v-model="form.logo"
                            title="Нажмите, чтобы загрузить изображение"
                            description="PNG (квадрат 1x1)"
                        />
                        <InputError :message="errors.logo?.[0]" class="mt-2" />
                    </div>

                    <div v-if="paymentGateway.logo_path">
                        <img :src="paymentGateway.logo_path" class="w-20 card">
                    </div>

                    <div>
                        <label class="label cursor-pointer mb-3 mt-3 justify-start gap-3" :class="{'opacity-75': paymentGateway.is_intrabank}">
                            <input type="checkbox" class="toggle toggle-primary" v-model="form.is_intrabank" :disabled="paymentGateway.is_intrabank">
                            <span class="label-text text-sm">Внутри банковский перевод</span>
                        </label>
                        <div v-if="paymentGateway.is_intrabank" class="ms-2 text-xs text-error">(нельзя отключить после активации)</div>
                    </div>

                    <div>
                        <label class="label cursor-pointer mb-3 mt-3 justify-start gap-3">
                            <input type="checkbox" class="toggle toggle-primary" v-model="form.is_active">
                            <span class="label-text text-sm">Метод активен</span>
                        </label>
                    </div>
                </form>
            </div>
        </ModalBody>

        <ModalFooter>
            <button @click="close" type="button" class="btn btn-sm">
                Отмена
            </button>
            <button @click="submit" type="button" class="btn btn-sm btn-primary" :class="{ 'btn-disabled': processing }" :disabled="processing">
                Сохранить
            </button>
        </ModalFooter>
    </Modal>
</template>


