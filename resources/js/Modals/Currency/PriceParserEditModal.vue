<script setup>
import ModalFooter from "@/Components/Modals/Components/ModalFooter.vue";
import ModalBody from "@/Components/Modals/Components/ModalBody.vue";
import Modal from "@/Components/Modals/Modal.vue";
import ModalHeader from "@/Components/Modals/Components/ModalHeader.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import NumberInput from "@/Components/NumberInput.vue";
import InputHelper from "@/Components/InputHelper.vue";
import Select from "@/Components/Select.vue";
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
    payment_method: 0,
    ad_quantity: null,
});

const title = computed(() => {
    return currency.value ? ('Настройка парсера для валюты - ' + currency.value.toUpperCase()) : 'Настройка парсера';
});

const resetForm = () => {
    form.value = {
        amount: null,
        payment_method: 0,
        ad_quantity: null,
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
            methods.value = data.methods || [];
            settings.value = data.settings || {};
            form.value.amount = settings.value.amount ?? null;
            form.value.payment_method = settings.value.payment_method ?? 0;
            form.value.ad_quantity = settings.value.ad_quantity ?? null;
            loading.value = false;
        })
        .catch(() => {
            loading.value = false;
        });
};

const normalizePayload = () => {
    const payload = {
        amount: form.value.amount ?? null,
        payment_method: form.value.payment_method === 0 ? null : form.value.payment_method,
        ad_quantity: form.value.ad_quantity ?? null,
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
                            :value="'Сумма в ' + (currency || 'RUB')"
                            :error="!!errors.amount?.[0]"
                        />

                        <NumberInput
                            id="amount"
                            v-model="form.amount"
                            type="text"
                            :class="['input input-bordered w-full mt-1', errors.amount?.[0] ? 'input-error' : '']"
                            :error="!!errors.amount?.[0]"
                            @input="errors.amount = null"
                            placeholder="Введите сумму"
                        />

                        <InputError :message="errors.amount?.[0]" class="mt-2" />
                        <InputHelper v-if="!errors.amount" model-value="Минимальный сумма доступного лимита на обмен" />
                    </div>

                    <div>
                        <InputLabel
                            for="payment_method"
                            value="Платежный метод"
                            :error="!!errors.payment_method?.[0]"
                            class="mb-1"
                        />
                        <Select
                            id="payment_method"
                            v-model="form.payment_method"
                            :class="['select select-bordered w-full', errors.payment_method?.[0] ? 'select-error' : '']"
                            :error="!!errors.payment_method?.[0]"
                            :items="methods"
                            value="id"
                            name="name"
                            default_title="Выберите платежный метод"
                            @change="errors.payment_method = null"
                        />

                        <InputError :message="errors.payment_method?.[0]" class="mt-2" />
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


