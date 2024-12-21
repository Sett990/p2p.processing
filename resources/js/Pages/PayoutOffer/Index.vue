<script setup>
import {Head, router, useForm} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { usePage } from '@inertiajs/vue3';
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import {useViewStore} from "@/store/view.js";
import Pagination from "@/Components/Pagination/Pagination.vue";
import PrimeTimeBonus from "@/Pages/Settings/Partials/PrimeTimeBonus.vue";
import TextInputBlock from "@/Components/Form/TextInputBlock.vue";
import InputLabel from "@/Components/InputLabel.vue";
import NumberInput from "@/Components/NumberInput.vue";
import InputHelper from "@/Components/InputHelper.vue";
import InputError from "@/Components/InputError.vue";
import Select from "@/Components/Select.vue";
import {computed, ref} from "vue";

const viewStore = useViewStore();
const items = {
    data: [1,2,3]
};

const currencies = usePage().props.currencies;
const detailTypes = usePage().props.detailTypes;
const paymentGateways = usePage().props.paymentGateways;

const form = useForm({
    max_amount: null,
    min_amount: null,
    detail_types: [],
    active: false,
    payment_gateway_id: 0,
});

const selectedDetailType = ref(0);

const selectedPaymentGateway = computed(() => {
    if (form.payment_gateway_id === 0) {
        return null;
    }

    return paymentGateways.filter(p => {
        return p.id === form.payment_gateway_id;
    })[0];
});

const currentCurrency = computed(() => {
    if (! selectedPaymentGateway.value) {
        return null;
    }

    return selectedPaymentGateway.value.currency.toUpperCase();
});

const addSelectedDetailType = () => {
    if (selectedDetailType.value === 0 || selectedDetailType.value === '0') {
        return;
    }

    form.detail_types = form.detail_types.filter(d => {
        return d !== selectedDetailType.value;
    });
    form.detail_types.push(selectedDetailType.value);
}

const removeDetailType = (detailType) => {
    form.detail_types = form.detail_types.filter(d => {
        return d !== detailType.code;
    });
}

const detailTypesAvailableForGateway = computed(() => {
    if (! selectedPaymentGateway.value) {
        return null;
    }
    
    return detailTypes.filter(d => {
        return selectedPaymentGateway.value.detail_types.includes(d.code);
    })
});

const selectedDetailTypes = computed(() => {
    return detailTypes.filter(d => {
        return form.detail_types.includes(d.code);
    })
});

const submit = () => {

}

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Выплаты" />

        <div>
            <div>
                <div class="mx-auto space-y-6">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl text-gray-900 dark:text-white sm:text-4xl">Выплаты</h2>
                    </div>
                    <div>
                        <div class="p-5 sm:p-8 bg-white dark:bg-gray-800 shadow rounded-plate ">
                            <form @submit.prevent="submit" class="space-y-6">
                                <div>
                                    <InputLabel
                                        for="payment_gateway_id"
                                        value="Платежный метод"
                                        :error="!!form.errors.payment_gateway_id"
                                        class="mb-1"
                                    />
                                    <Select
                                        id="payment_gateway_id"
                                        v-model="form.payment_gateway_id"
                                        :error="!!form.errors.payment_gateway_id"
                                        :items="paymentGateways"
                                        key="id"
                                        value="id"
                                        name="name"
                                        default_title="Выберите платежный метод"
                                        @change="form.clearErrors('payment_gateway_id');"
                                    ></Select>

                                    <InputError :message="form.errors.payment_gateway_id" class="mt-2" />
                                </div>
                                <div>
                                    <InputLabel
                                        for="detail_types"
                                        value="Тип реквизитов"
                                        :error="!!form.errors.detail_types"
                                        class="mb-1"
                                    />
                                    <div class="flex justify-between gap-2">
                                        <Select
                                            id="detail_types"
                                            v-model="selectedDetailType"
                                            :error="!!form.errors.detail_types"
                                            :items="detailTypesAvailableForGateway"
                                            key="code"
                                            value="code"
                                            name="name"
                                            default_title="Выберите тип реквизитов"
                                            @change="form.clearErrors('detail_types');"
                                        ></Select>
                                        <button
                                            @click.prevent="addSelectedDetailType"
                                            type="button"
                                            class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800"
                                        >
                                            Добавить
                                        </button>
                                    </div>
                                    <div class="flex gap-3 mt-3">
                                        <span
                                            v-for="detailType in selectedDetailTypes"
                                            class="inline-flex items-center bg-indigo-100 text-indigo-800 text-sm font-medium me-2 px-2.5 py-0.5 rounded-lg dark:bg-indigo-900 dark:text-indigo-300"
                                        >
                                            {{ detailType.name }}
                                            <svg @click="removeDetailType(detailType)" class="w-2.5 h-2.5 ml-1.5 cursor-pointer" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/>
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex justify-between gap-6">
                                    <div>
                                        <InputLabel
                                            for="max_amount"
                                            :value="`Максимальная сумма ${currentCurrency ?? ''}`"
                                            :error="!!form.errors.max_amount"
                                        />

                                        <NumberInput
                                            id="max_amount"
                                            v-model="form.max_amount"
                                            class="mt-1 block w-full"
                                            placeholder="0"
                                            :error="!!form.errors.max_amount"
                                            @input="form.clearErrors('max_amount')"
                                        />

                                        <InputError :message="form.errors.max_amount" class="mt-2" />
                                        <InputHelper v-if="! form.errors.max_amount" model-value="Максимальная сумма на одну операцию которую вы готовы обработать одним переводом."></InputHelper>
                                    </div>
                                    <div>
                                        <InputLabel
                                            for="max_amount"
                                            :value="`Минимальная сумма ${currentCurrency ?? ''}`"
                                            :error="!!form.errors.max_amount"
                                        />

                                        <NumberInput
                                            id="max_amount"
                                            v-model="form.max_amount"
                                            class="mt-1 block w-full"
                                            placeholder="0"
                                            :error="!!form.errors.max_amount"
                                            @input="form.clearErrors('max_amount')"
                                        />

                                        <InputError :message="form.errors.max_amount" class="mt-2" />
                                        <InputHelper v-if="! form.errors.max_amount" model-value="Минимальная сумма на одну операцию которую вы готовы обработать одним переводом."></InputHelper>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
