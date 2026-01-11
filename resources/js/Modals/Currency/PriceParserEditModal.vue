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

const createSideState = () => ({
    amount: null,
    payment_methods: [],
    ad_quantity: null,
    min_recent_orders: null,
});

const form = ref({
    buy: createSideState(),
    sell: createSideState(),
});

const parserSides = [
    {
        key: 'buy',
        title: 'Покупка USDT',
        badgeClass: 'badge badge-success badge-sm',
        hint: 'Используется для зелёного стакана (получаем курс покупки).',
    },
    {
        key: 'sell',
        title: 'Продажа USDT',
        badgeClass: 'badge badge-error badge-sm',
        hint: 'Используется для красного стакана (получаем курс продажи).',
    },
];
const title = computed(() => {
    return currency.value ? ('Настройка парсера для валюты - ' + currency.value.toUpperCase()) : 'Настройка парсера';
});

const resetForm = () => {
    form.value = {
        buy: createSideState(),
        sell: createSideState(),
    };
    errors.value = {};
    settings.value = null;
    methods.value = [];
    currency.value = null;
};

const clearError = (field) => {
    if (!errors.value[field]) {
        return;
    }
    const copy = {...errors.value};
    delete copy[field];
    errors.value = copy;
};

const errorMessage = (field) => errors.value?.[field]?.[0] ?? null;

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

            const buySettings = settings.value.buy ?? settings.value ?? {};
            const sellSettings = settings.value.sell ?? settings.value ?? {};

            form.value.buy = {
                amount: buySettings.amount ?? null,
                payment_methods: (buySettings.payment_methods ?? []).map((value) => String(value)),
                ad_quantity: buySettings.ad_quantity ?? null,
                min_recent_orders: buySettings.min_recent_orders ?? null,
            };

            form.value.sell = {
                amount: sellSettings.amount ?? null,
                payment_methods: (sellSettings.payment_methods ?? []).map((value) => String(value)),
                ad_quantity: sellSettings.ad_quantity ?? null,
                min_recent_orders: sellSettings.min_recent_orders ?? null,
            };
            loading.value = false;
        })
        .catch(() => {
            loading.value = false;
        });
};

const normalizeSidePayload = (sideKey) => {
    const side = form.value[sideKey] ?? createSideState();
    return {
        amount: side.amount ?? null,
        payment_methods: Array.isArray(side.payment_methods)
            ? side.payment_methods
                .map((value) => Number(value))
                .filter((value) => !Number.isNaN(value))
            : [],
        ad_quantity: side.ad_quantity ?? null,
        min_recent_orders: side.min_recent_orders ?? null,
    };
};

const normalizePayload = () => ({
    buy: normalizeSidePayload('buy'),
    sell: normalizeSidePayload('sell'),
    _method: 'PATCH',
});

const handleAdQuantityInput = (sideKey) => {
    clearError(`${sideKey}.ad_quantity`);
    const side = form.value[sideKey];
    if (!side) return;

    if (side.ad_quantity === null || side.ad_quantity === '') {
        side.ad_quantity = null;
        return;
    }

    const numericValue = Number(side.ad_quantity);
    if (Number.isNaN(numericValue)) {
        side.ad_quantity = null;
        return;
    }

    side.ad_quantity = Math.min(200, Math.max(1, numericValue));
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
                    errors.value = { 'buy.amount': [error.response.data.message] };
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
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="alert alert-info mb-3" role="alert">
                        <svg class="shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                        </svg>
                        <div class="text-sm">
                            Данные настройки только для ByBit P2P парсера.
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div
                            v-for="side in parserSides"
                            :key="side.key"
                            class="rounded-lg border border-base-300 bg-base-100/50 p-4 space-y-4"
                        >
                            <div class="flex flex-col gap-1">
                                <div class="flex items-center gap-2">
                                    <span :class="side.badgeClass">{{ side.title }}</span>
                                    <span class="text-xs uppercase tracking-wide text-base-content/50">{{ currency }}</span>
                                </div>
                                <p class="text-sm text-base-content/70">{{ side.hint }}</p>
                            </div>

                            <div class="grid gap-4">
                                <div>
                                    <InputLabel
                                        :for="`${side.key}-amount`"
                                        :value="'Объем в ' + (currency || 'RUB')"
                                        :error="!!errorMessage(`${side.key}.amount`)"
                                    />

                                    <NumberInput
                                        :id="`${side.key}-amount`"
                                        v-model="form[side.key].amount"
                                        type="text"
                                        :class="['input input-bordered w-full mt-1', errorMessage(`${side.key}.amount`) ? 'input-error' : '']"
                                        :error="!!errorMessage(`${side.key}.amount`)"
                                        @input="clearError(`${side.key}.amount`)"
                                        placeholder="Введите объем"
                                    />

                                    <InputError :message="errorMessage(`${side.key}.amount`)" class="mt-2" />
                                    <InputHelper v-if="!errorMessage(`${side.key}.amount`)" model-value="Минимальный объем доступного лимита на обмен" />
                                </div>

                                <div>
                                    <InputLabel
                                        :for="`${side.key}-payment_methods`"
                                        value="Платежные методы"
                                        :error="!!errorMessage(`${side.key}.payment_methods`)"
                                        class="mb-1"
                                    />

                                    <Multiselect
                                        :id="`${side.key}-payment_methods`"
                                        v-model="form[side.key].payment_methods"
                                        :options="methods"
                                        label-key="name"
                                        value-key="id"
                                        placeholder="Выберите один или несколько методов"
                                        :class="errorMessage(`${side.key}.payment_methods`) ? 'input-error' : ''"
                                        @change="clearError(`${side.key}.payment_methods`)"
                                    />

                                    <InputError :message="errorMessage(`${side.key}.payment_methods`)" class="mt-2" />
                                    <InputHelper v-if="!errorMessage(`${side.key}.payment_methods`)" model-value="Если ничего не выбрать, берём объявления со всеми методами." />
                                </div>

                                <div>
                                    <InputLabel
                                        :for="`${side.key}-ad_quantity`"
                                        value="Количество объявлений"
                                        :error="!!errorMessage(`${side.key}.ad_quantity`)"
                                    />

                                    <NumberInput
                                        :id="`${side.key}-ad_quantity`"
                                        v-model="form[side.key].ad_quantity"
                                        type="text"
                                        :class="['input input-bordered w-full mt-1', errorMessage(`${side.key}.ad_quantity`) ? 'input-error' : '']"
                                        :error="!!errorMessage(`${side.key}.ad_quantity`)"
                                        @input="handleAdQuantityInput(side.key)"
                                        placeholder="Укажите количество объявлений"
                                    />

                                    <InputError :message="errorMessage(`${side.key}.ad_quantity`)" class="mt-2" />
                                    <InputHelper v-if="!errorMessage(`${side.key}.ad_quantity`)" model-value="Парсер возьмет первые N объявлений (не более 200) и рассчитает усредненную цену." />
                                </div>

                                <div>
                                    <InputLabel
                                        :for="`${side.key}-min_recent_orders`"
                                        value="Количество сделок"
                                        :error="!!errorMessage(`${side.key}.min_recent_orders`)"
                                    />

                                    <NumberInput
                                        :id="`${side.key}-min_recent_orders`"
                                        v-model="form[side.key].min_recent_orders"
                                        type="text"
                                        :class="['input input-bordered w-full mt-1', errorMessage(`${side.key}.min_recent_orders`) ? 'input-error' : '']"
                                        :error="!!errorMessage(`${side.key}.min_recent_orders`)"
                                        @input="clearError(`${side.key}.min_recent_orders`)"
                                        placeholder="Например, 100"
                                    />

                                    <InputError :message="errorMessage(`${side.key}.min_recent_orders`)" class="mt-2" />
                                    <InputHelper v-if="!errorMessage(`${side.key}.min_recent_orders`)" model-value="Отфильтруем объявления мерчантов с количеством сделок ниже указанного." />
                                </div>
                            </div>
                        </div>
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


