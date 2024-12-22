<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputHelper from '@/Components/InputHelper.vue';
import {Head, router, useForm, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Select from "@/Components/Select.vue";
import SaveButton from "@/Components/Form/SaveButton.vue";
import SecondaryPageSection from "@/Wrappers/SecondaryPageSection.vue";
import {useViewStore} from "@/store/view.js";
import NumberInputBlock from "@/Components/Form/NumberInputBlock.vue";
import {computed, ref} from "vue";

const viewStore = useViewStore();

const payment_gateways = usePage().props.paymentGateways;
const currencies = usePage().props.currencies;
const merchants = usePage().props.merchants;

const detail_types = [
    {id: 'card', name: 'Карта'},
    {id: 'phone', name: 'Телефон'},
    {id: 'account_number', name: 'Номер счета'},
]

const form = useForm({
    amount: null,
    currency: 0,
    payment_gateway: 0,
    sub_payment_gateway: 0,
    payment_detail_type: 'card',
    merchant_id: 0,
    manually: null,
});
const submit = () => {
    form
        .transform((data) => {
            if (data.payment_gateway === 0) {
                if (data.currency) {
                    data.currency = data.currency.toLowerCase()
                }
                delete data.payment_gateway;
            }
            if (data.currency === 0) {
                delete data.currency;
            }
            if (data.merchant_id === 0) {
                delete data.merchant_id;
            }
            if (manually_mode.value === true) {
                data.manually = 1;
                delete data.payment_gateway;
                delete data.sub_payment_gateway;
                delete data.payment_detail_type;
            } else {
                delete data.manually;
            }

            return data;
        })
        .post(route('payments.store'), {
            preserveScroll: true,
        });
};

const manually_mode = ref(false);
const gateway_mode = ref('payment_gateway');
const detail_type_mode = ref('card');

const currentPaymentGateway = computed(() => {
    return payment_gateways.find((item) => {
        if (item.code === form.payment_gateway) {
            return item;
        } else {
            return null;
        }
    });
})

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Создание платежа" />

        <SecondaryPageSection
            :back-link="route('payments.index')"
            title="Создание платежа"
            description="Здесь вы можете вручную создать платеж для клиента."
        >
            <form @submit.prevent="submit" class="mt-6 space-y-6">
                <div v-show="$page.props.flash.message" class="flex items-center p-4 mb-4 text-sm text-red-800 border border-red-300 rounded-xl  bg-red-50 dark:bg-gray-800 dark:text-red-400 dark:border-red-800" role="alert">
                    <svg class="flex-shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                    </svg>
                    <div>
                        <span class="font-medium">Внимание</span> {{ $page.props.flash.message }}
                    </div>
                </div>

                <div class="mb-4">
                    <InputLabel
                        for="payment_detail_type"
                        value="Выберите способ выбора платежного метода"
                        class="mb-1"
                    />
                    <ul class="text-sm border border-gray-200 dark:border-gray-700 font-medium text-center text-gray-500 rounded-xl  flex dark:divide-gray-700 dark:text-gray-400">
                        <li class="w-full focus-within:z-10">
                            <a
                                @click.prevent="manually_mode = false; gateway_mode = 'payment_gateway';"
                                href="#"
                                class="inline-block w-full p-2 border-r-0 border-gray-200 dark:border-gray-700 rounded-l-lg hover:text-gray-700 hover:bg-gray-200 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:hover:text-white dark:hover:bg-gray-700"
                                :class="manually_mode === false ? 'text-gray-900 bg-gray-200 border-r rounded-s-lg dark:bg-gray-700 dark:text-white' : 'bg-white dark:bg-gray-800'"
                            >
                                Авто выбор
                            </a>
                        </li>
                        <li class="w-full focus-within:z-10">
                            <a @click.prevent="manually_mode = true; gateway_mode = 'currency'; form.payment_gateway = 0;"
                               href="#"
                               class="inline-block w-full p-2 border-s-0 border-gray-200 dark:border-gray-700 rounded-e-lg hover:text-gray-700 hover:bg-gray-200 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:hover:text-white dark:hover:bg-gray-700"
                               :class="manually_mode === true ? 'text-gray-900 bg-gray-200 border-r rounded-r-lg dark:bg-gray-700 dark:text-white' : 'bg-white dark:bg-gray-800'"
                            >
                                Ручной выбор
                            </a>
                        </li>
                    </ul>
                </div>

                <div v-if="manually_mode === true" class="flex items-center p-4 mb-4 text-sm text-blue-800 border border-blue-300 rounded-xl  bg-blue-50 dark:bg-gray-800 dark:text-blue-400 dark:border-blue-800" role="alert">
                    <svg class="flex-shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                    </svg>
                    <span class="sr-only">Информация</span>
                    <div>
                        Клиенту будет предложен список доступных методов на выбор, из которых он сам может выбрать наиболее удобный.
                    </div>
                </div>

                <div v-if="manually_mode === false" class="flex items-center p-4 mb-4 text-sm text-blue-800 border border-blue-300 rounded-xl  bg-blue-50 dark:bg-gray-800 dark:text-blue-400 dark:border-blue-800" role="alert">
                    <svg class="flex-shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                    </svg>
                    <span class="sr-only">Информация</span>
                    <div>
                        У клиента не будет возможности выбрать метод для оплаты. Вместо этого будут предложены реквизиты по выбранным ниже фильтрам.
                    </div>
                </div>

                <NumberInputBlock
                    v-model="form.amount"
                    :form="form"
                    field="amount"
                    label="Сумма платежа"
                    placeholder="0"
                />

                <div>
                    <div class="mb-4">
                        <div v-show="manually_mode === false" class="mb-4">
                            <InputLabel
                                for="payment_detail_type"
                                value="Выберите направление"
                                class="mb-1"
                            />
                            <ul class="text-sm border border-gray-200 dark:border-gray-700 font-medium text-center text-gray-500 rounded-xl  flex dark:divide-gray-700 dark:text-gray-400">
                                <li class="w-full focus-within:z-10">
                                    <a
                                        @click.prevent="gateway_mode = 'payment_gateway'; form.currency = 0"
                                        href="#"
                                        class="inline-block w-full p-2 border-r-0 border-gray-200 dark:border-gray-700 rounded-l-lg hover:text-gray-700 hover:bg-gray-200 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:hover:text-white dark:hover:bg-gray-700"
                                        :class="gateway_mode === 'payment_gateway' ? 'text-gray-900 bg-gray-200 border-r rounded-s-lg dark:bg-gray-700 dark:text-white' : 'bg-white dark:bg-gray-800'"
                                    >
                                        Метод
                                    </a>
                                </li>
                                <li class="w-full focus-within:z-10">
                                    <a @click.prevent="gateway_mode = 'currency'; form.payment_gateway = 0"
                                       href="#"
                                       class="inline-block w-full p-2 border-s-0 border-gray-200 dark:border-gray-700 rounded-e-lg hover:text-gray-700 hover:bg-gray-200 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:hover:text-white dark:hover:bg-gray-700"
                                       :class="gateway_mode === 'currency' ? 'text-gray-900 bg-gray-200 border-r rounded-r-lg dark:bg-gray-700 dark:text-white' : 'bg-white dark:bg-gray-800'"
                                    >
                                        Валюта
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div v-show="gateway_mode === 'payment_gateway'">
                            <InputLabel
                                for="payment_gateway"
                                value="Платежный метод"
                                :error="!!form.errors.payment_gateway"
                                class="mb-1"
                            />
                            <Select
                                id="payment_gateway"
                                v-model="form.payment_gateway"
                                :error="!!form.errors.payment_gateway"
                                :items="payment_gateways"
                                value="code"
                                name="name"
                                default_title="Выберите метод"
                                @change="form.clearErrors('payment_gateway')"
                            ></Select>

                            <InputError :message="form.errors.payment_gateway" class="mt-2" />
                            <InputHelper v-if="! form.errors.payment_gateway" model-value="Платеж будет создан только в рамках выбранного платежного метода."></InputHelper>
                        </div>

                        <div v-if="gateway_mode === 'payment_gateway' && currentPaymentGateway?.sub_payment_gateways" class="mt-4">
                            <InputLabel
                                for="sub_payment_gateway"
                                value="Выберите СБП метод"
                                :error="!!form.errors.sub_payment_gateway"
                                class="mb-1"
                            />
                            <Select
                                id="sub_payment_gateway"
                                v-model="form.sub_payment_gateway"
                                :error="!!form.errors.sub_payment_gateway"
                                :items="currentPaymentGateway?.sub_payment_gateways"
                                value="code"
                                name="name"
                                default_title="Выберите СБП метод"
                                @change="form.clearErrors('sub_payment_gateway')"
                            ></Select>

                            <InputError :message="form.errors.sub_payment_gateway" class="mt-2" />
                            <InputHelper v-if="! form.errors.sub_payment_gateway" model-value="Уточняет какой конкретно метод использовать для СБП (не обязателен)."></InputHelper>
                        </div>

                        <div v-show="gateway_mode === 'currency'">
                            <InputLabel
                                for="currency"
                                value="Валюта"
                                :error="!!form.errors.currency"
                                class="mb-1"
                            />
                            <Select
                                id="currency"
                                v-model="form.currency"
                                :error="!!form.errors.currency"
                                :items="currencies"
                                value="code"
                                name="name"
                                default_title="Выберите валюту"
                                @change="form.clearErrors('currency')"
                            ></Select>

                            <InputError :message="form.errors.currency" class="mt-2" />
                            <InputHelper v-if="! form.errors.currency && manually_mode === false" model-value="Будет использован любой платежный метод в рамках выбранной валюты."></InputHelper>
                        </div>
                    </div>

                    <div v-show="manually_mode === false" class="mb-4">
                        <InputLabel
                            for="payment_detail_type"
                            value="Выберите тип реквизитов"
                            :error="!!form.errors.payment_detail_type"
                            class="mb-1"
                        />
                        <ul class="hidden text-sm border border-gray-200 dark:border-gray-700 font-medium text-center text-gray-500 rounded-xl  sm:flex dark:divide-gray-700 dark:text-gray-400">
                            <li class="w-full focus-within:z-10">
                                <a
                                    @click.prevent="detail_type_mode = 'card'; form.payment_detail_type = 'card'"
                                    href="#"
                                    class="inline-block w-full p-2 border-r-0 border-gray-200 dark:border-gray-700 rounded-l-lg hover:text-gray-700 hover:bg-gray-200 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:hover:text-white dark:hover:bg-gray-700"
                                    :class="detail_type_mode === 'card' ? 'text-gray-900 bg-gray-200 border-r rounded-s-lg dark:bg-gray-700 dark:text-white' : 'bg-white dark:bg-gray-800'"
                                >
                                    Карта
                                </a>
                            </li>
                            <li class="w-full focus-within:z-10">
                                <a @click.prevent="detail_type_mode = 'phone'; form.payment_detail_type = 'phone'"
                                   href="#"
                                   class="inline-block w-full p-2 border-s-0 border-gray-200 dark:border-gray-700 hover:text-gray-700 hover:bg-gray-200 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:hover:text-white dark:hover:bg-gray-700"
                                   :class="detail_type_mode === 'phone' ? 'text-gray-900 bg-gray-200 border-r border-l dark:bg-gray-700 dark:text-white' : 'bg-white dark:bg-gray-800'"
                                >
                                    Телефон
                                </a>
                            </li>
                            <li class="w-full focus-within:z-10">
                                <a @click.prevent="detail_type_mode = 'account_number'; form.payment_detail_type = 'account_number'"
                                   href="#"
                                   class="inline-block w-full p-2 border-s-0 border-gray-200 dark:border-gray-700 rounded-e-lg hover:text-gray-700 hover:bg-gray-200 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:hover:text-white dark:hover:bg-gray-700"
                                   :class="detail_type_mode === 'account_number' ? 'text-gray-900 bg-gray-200 border-r rounded-r-lg dark:bg-gray-700 dark:text-white' : 'bg-white dark:bg-gray-800'"
                                >
                                    Номер счета
                                </a>
                            </li>
                        </ul>
                        <div class="block sm:hidden">
                            <Select
                                id="payment_detail_type"
                                v-model="form.payment_detail_type"
                                :items="detail_types"
                                value="id"
                                name="name"
                                default_title="Выберите тип реквизитов"
                                @change="detail_type_mode = $event.target.value"
                            ></Select>
                        </div>
                    </div>

                    <div>
                        <InputLabel
                            for="merchant_id"
                            value="Мерчант"
                            :error="!!form.errors.merchant_id"
                            class="mb-1"
                        />
                        <Select
                            id="merchant_id"
                            v-model="form.merchant_id"
                            :error="!!form.errors.merchant_id"
                            :items="merchants"
                            value="id"
                            name="name"
                            default_title="Выберите мерчант"
                            @change="form.clearErrors('merchant_id')"
                        ></Select>

                        <InputError :message="form.errors.merchant_id" class="mt-2" />
                    </div>
                </div>

                <div class="flex items-center p-3 mb-4 text-sm text-yellow-800 border border-yellow-300 rounded-xl bg-yellow-50 dark:bg-yellow-500/20 dark:text-yellow-300 dark:border-yellow-800">
                    <svg class="flex-shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                    </svg>
                    <div>
                        Не для всех вариантов выбранных параметров могут не быть подходящие реквизиты.
                    </div>
                </div>

                <SaveButton
                    :disabled="form.processing"
                    :saved="false"
                ></SaveButton>
            </form>
        </SecondaryPageSection>
    </div>
</template>
