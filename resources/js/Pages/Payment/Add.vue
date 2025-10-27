<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputHelper from '@/Components/InputHelper.vue';
import {Head, useForm, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Select from "@/Components/Select.vue";
import SaveButton from "@/Components/Form/SaveButton.vue";
import SecondaryPageSection from "@/Wrappers/SecondaryPageSection.vue";
import NumberInputBlock from "@/Components/Form/NumberInputBlock.vue";
import {ref} from "vue";
import AlertWarning from "@/Components/Alerts/AlertWarning.vue";
import AlertInfo from "@/Components/Alerts/AlertInfo.vue";

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
                    <ul class="flex flex-wrap text-sm font-medium text-center">
                        <li class="me-2">
                            <a @click.prevent="manually_mode = false; gateway_mode = 'payment_gateway';" href="#" :class="manually_mode === false ? 'btn btn-primary' : 'btn btn-outline'" class="inline-flex items-center px-4 py-2 rounded-xl" aria-current="page">
                                <span class="sm:block hidden">Авто выбор</span>
                            </a>
                        </li>
                        <li class="me-2">
                            <a @click.prevent="manually_mode = true; gateway_mode = 'currency'; form.payment_gateway = 0;" href="#" :class="manually_mode === true ? 'btn btn-primary' : 'btn btn-outline'" class="inline-flex items-center px-4 py-2 rounded-xl" aria-current="page">
                                <span class="sm:block hidden">Ручной выбор</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <AlertInfo v-if="manually_mode === true" message="Клиенту будет предложен список доступных методов на выбор, из которых он сам может выбрать наиболее удобный."/>
                <AlertInfo v-if="manually_mode === false" message="У клиента не будет возможности выбрать метод для оплаты. Вместо этого будут предложены реквизиты по выбранным ниже фильтрам."/>

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
                            <ul class="flex flex-wrap text-sm font-medium text-center">
                                <li class="me-2">
                                    <a @click.prevent="gateway_mode = 'payment_gateway'; form.currency = 0" href="#" :class="gateway_mode === 'payment_gateway' ? 'btn btn-primary' : 'btn btn-outline'" class="inline-flex items-center px-4 py-2 rounded-xl" aria-current="page">
                                        <span class="sm:block hidden">Метод</span>
                                    </a>
                                </li>
                                <li class="me-2">
                                    <a @click.prevent="gateway_mode = 'currency'; form.payment_gateway = 0" href="#" :class="gateway_mode === 'currency' ? 'btn btn-primary' : 'btn btn-outline'" class="inline-flex items-center px-4 py-2 rounded-xl" aria-current="page">
                                        <span class="sm:block hidden">Валюта</span>
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
                        <ul class="hidden sm:flex flex-wrap text-sm font-medium text-center">
                            <li class="me-2">
                                <a @click.prevent="detail_type_mode = 'card'; form.payment_detail_type = 'card'" href="#" :class="detail_type_mode === 'card' ? 'btn btn-primary' : 'btn btn-outline'" class="inline-flex items-center px-4 py-2 rounded-xl" aria-current="page">
                                    <span class="sm:block hidden">Карта</span>
                                </a>
                            </li>
                            <li class="me-2">
                                <a @click.prevent="detail_type_mode = 'phone'; form.payment_detail_type = 'phone'" href="#" :class="detail_type_mode === 'phone' ? 'btn btn-primary' : 'btn btn-outline'" class="inline-flex items-center px-4 py-2 rounded-xl" aria-current="page">
                                    <span class="sm:block hidden">Телефон</span>
                                </a>
                            </li>
                            <li class="me-2">
                                <a @click.prevent="detail_type_mode = 'account_number'; form.payment_detail_type = 'account_number'" href="#" :class="detail_type_mode === 'account_number' ? 'btn btn-primary' : 'btn btn-outline'" class="inline-flex items-center px-4 py-2 rounded-xl" aria-current="page">
                                    <span class="sm:block hidden">Номер счета</span>
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

                <AlertWarning message="Не для всех вариантов выбранных параметров могут не быть подходящие реквизиты."/>

                <SaveButton
                    :disabled="form.processing"
                    :saved="false"
                ></SaveButton>
            </form>
        </SecondaryPageSection>
    </div>
</template>
