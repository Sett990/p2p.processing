<script setup>
import ModalFooter from "@/Components/Modals/Components/ModalFooter.vue";
import ModalBody from "@/Components/Modals/Components/ModalBody.vue";
import Modal from "@/Components/Modals/Modal.vue";
import ModalHeader from "@/Components/Modals/Components/ModalHeader.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import NumberInput from "@/Components/NumberInput.vue";
import InputHelper from "@/Components/InputHelper.vue";
import Multiselect from "@/Components/Form/Multiselect.vue";
import { ref, watch, computed } from "vue";
import { storeToRefs } from "pinia";
import { useModalStore } from "@/store/modal.js";
import { router } from "@inertiajs/vue3";

const modalStore = useModalStore();
const { priceParserEditModal } = storeToRefs(modalStore);

const loading = ref(false);
const processing = ref(false);
const errors = ref({});

const currency = ref(null);
const methods = ref([]);
const settings = ref(null);

const form = ref({
    amount: null,
    payment_methods: [],
    ad_quantity: null,
    min_recent_orders: null,
});

const title = computed(() => {
    return currency.value ? ('Настройка парсера для валюты - ' + currency.value.toUpperCase()) : 'Настройка парсера';
});

const resetForm = () => {
    form.value = {
        amount: null,
        payment_methods: [],
        ad_quantity: null,
        min_recent_orders: null,
    };
    errors.value = {};
    settings.value = null;
    methods.value = [];
    currency.value = null;
};

const close = () => {
    modalStore.closeModal('priceParserEdit');
};

const loadData = () => {
    const code = priceParserEditModal.value.params?.currency;
    if (!code) return;
    loading.value = true;
    axios.get(route('admin.currencies.price-parsers.edit-data', code))
        .then(response => {
            const data = response.data?.data || response.data || {};
            currency.value = (data.currency || code || 'RUB').toUpperCase();
            methods.value = (data.methods || []).map((method) => ({
                ...method,
                id: String(method.id),
            }));
            settings.value = data.settings || {};
            form.value.amount = settings.value.amount ?? null;
            form.value.payment_methods = (settings.value.payment_methods ?? []).map((value) => String(value));
            form.value.ad_quantity = settings.value.ad_quantity ?? null;
            form.value.min_recent_orders = settings.value.min_recent_orders ?? null;
            loading.value = false;
        })
        .catch(() => {
            loading.value = false;
        });
};

const normalizePayload = () => {
    const payload = {
        amount: form.value.amount ?? null,
        payment_methods: Array.isArray(form.value.payment_methods)
            ? form.value.payment_methods
                .map((value) => Number(value))
                .filter((value) => !Number.isNaN(value))
            : [],
        ad_quantity: form.value.ad_quantity ?? null,
        min_recent_orders: form.value.min_recent_orders ?? null,
        _method: 'PATCH',
    };
    return payload;
};

const submit = () => {
    const code = priceParserEditModal.value.params?.currency;
    if (!code) return;
    processing.value = true;
    errors.value = {};
    axios.post(route('admin.currencies.price-parsers.update', code.toLowerCase()), normalizePayload(), {
        headers: { 'Accept': 'application/json' }
    })
        .then(response => {
            processing.value = false;
            if (response.data?.success || response.status === 200 || response.status === 204) {
                close();
                resetForm();
                router.reload({ only: ['markets'] });
            }
        })
        .catch(error => {
            processing.value = false;
            if (error.response && error.response.data) {
                if (error.response.data.errors) {
                    errors.value = error.response.data.errors;
                } else if (error.response.data.message) {
                    errors.value = { amount: [error.response.data.message] };
                }
            }
        });
};

watch(
    () => priceParserEditModal.value.showed,
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
    <Modal :show="priceParserEditModal.showed" @close="close" maxWidth="3xl">
        <ModalHeader @close="close" :title="title" />

        <ModalBody>
            <div v-if="loading" class="py-6 text-center">
                <span class="loading loading-spinner loading-md"></span>
            </div>
            <div v-else>
                <form @submit.prevent="submit" class="mt-2 space-y-6">
                    <div class="alert alert-info mb-1" role="alert">
                        <svg class="shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                        </svg>
                        <div class="text-sm">
                            Данные настройки только для ByBit P2P парсера.
                        </div>
                    </div>

                    <div>
                        <InputLabel
                            for="amount"
                            :value="'Объем в ' + (currency || 'RUB')"
                            :error="!!errors.amount?.[0]"
                        />

                        <NumberInput
                            id="amount"
                            v-model="form.amount"
                            type="text"
                            :class="['input input-bordered w-full mt-1', errors.amount?.[0] ? 'input-error' : '']"
                            :error="!!errors.amount?.[0]"
                            @input="errors.amount = null"
                            placeholder="Введите объем"
                        />

                        <InputError :message="errors.amount?.[0]" class="mt-2" />
                        <InputHelper v-if="!errors.amount" model-value="Минимальный объем доступного лимита на обмен" />
                    </div>

                    <div>
                        <InputLabel
                            for="payment_methods"
                            value="Платежные методы"
                            :error="!!errors.payment_methods?.[0]"
                            class="mb-1"
                        />

                        <Multiselect
                            id="payment_methods"
                            v-model="form.payment_methods"
                            :options="methods"
                            label-key="name"
                            value-key="id"
                            placeholder="Выберите один или несколько методов"
                            :class="errors.payment_methods?.[0] ? 'input-error' : ''"
                            @change="errors.payment_methods = null"
                        />

                        <InputError :message="errors.payment_methods?.[0]" class="mt-2" />
                        <InputHelper v-if="!errors.payment_methods" model-value="Если ничего не выбрать, берём объявления со всеми методами." />
                    </div>

                    <div>
                        <InputLabel
                            for="ad_quantity"
                            value="Количество объявлений"
                            :error="!!errors.ad_quantity?.[0]"
                        />

                        <NumberInput
                            id="ad_quantity"
                            v-model="form.ad_quantity"
                            type="text"
                            :class="['input input-bordered w-full mt-1', errors.ad_quantity?.[0] ? 'input-error' : '']"
                            :error="!!errors.ad_quantity?.[0]"
                            @input="errors.ad_quantity = null"
                            placeholder="Укажите количество объявлений"
                        />

                        <InputError :message="errors.ad_quantity?.[0]" class="mt-2" />
                        <InputHelper v-if="!errors.ad_quantity" model-value="Парсер возьмет первые N количество объявлений, и рассчитает усредненную цену." />
                    </div>

                    <div>
                        <InputLabel
                            for="min_recent_orders"
                            value="Минимум успешных сделок у мерчанта"
                            :error="!!errors.min_recent_orders?.[0]"
                        />

                        <NumberInput
                            id="min_recent_orders"
                            v-model="form.min_recent_orders"
                            type="text"
                            :class="['input input-bordered w-full mt-1', errors.min_recent_orders?.[0] ? 'input-error' : '']"
                            :error="!!errors.min_recent_orders?.[0]"
                            @input="errors.min_recent_orders = null"
                            placeholder="Например, 100"
                        />

                        <InputError :message="errors.min_recent_orders?.[0]" class="mt-2" />
                        <InputHelper v-if="!errors.min_recent_orders" model-value="Отфильтруем объявления мерчантов, у которых recentOrderNum ниже указанного значения." />
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


