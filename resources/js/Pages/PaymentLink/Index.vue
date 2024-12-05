<script setup>
import {Head, router, useForm, usePage} from '@inertiajs/vue3';
import PaymentLayout from "@/Layouts/PaymentLayout.vue";
import CopyPaymentText from "@/Components/CopyPaymentText.vue";
import {computed, onMounted, ref} from "vue";
import {initFlowbite} from "flowbite";
import InputError from "@/Components/InputError.vue";
import GatewayLogo from "@/Components/GatewayLogo.vue";
import Dropzone from "@/Components/Form/Dropzone.vue";
import SupportButton from "@/Pages/PaymentLink/Components/SupportButton.vue";
import Clock from "@/Pages/PaymentLink/Components/Clock.vue";
import ColorThemeSwitcher from "@/Pages/PaymentLink/Components/ColorThemeSwitcher.vue";
import StageSwitcher from "@/Pages/PaymentLink/Components/StageSwitcher.vue";

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const stage = ref('payment');
const selectedGateway = ref(null);
const clockRef = ref(null);
const data = ref({});
const formReceipt = useForm({
    receipt: null,
})
const formGatewaySelect = useForm({});

const formatedDetail = computed(() => {
    if (data.value.detail_type === 'card') {
        return data.value.detail.match(/.{1,4}/g).join(' ');
    }
    if (data.value.detail_type === 'phone') {
        let x = data.value.detail.replace(/\D/g, '').match(/(\d{1})(\d{0,3})(\d{0,3})(\d{0,2})(\d{0,2})/);

        return  !x[2] ? x[1] : '+' + x[1] + ' (' + x[2] + ') ' + x[3] + '-' + x[4] + '-' + x[5];
    }
    if (data.value.detail_type === 'account_number') {
        return data.value.detail.match(/.{1,4}/g).join(' ');
    }
})

const initializeClock = () => {
    clockRef.value.initializeClock();
}

const setData = () => {
    data.value = {
        order_status: usePage().props.data.order_status,
        uuid: usePage().props.data.uuid,
        name: usePage().props.data.name,
        amount: usePage().props.data.amount,
        amount_formated: usePage().props.data.amount_formated,
        currency_symbol: usePage().props.data.currency_symbol,
        support_link: usePage().props.data.support_link,
        detail: usePage().props.data.detail,
        detail_type: usePage().props.data.detail_type,
        initials: usePage().props.data.initials,
        sub_payment_gateway: usePage().props.data.sub_payment_gateway,
        success_url: usePage().props.data.success_url,
        fail_url: usePage().props.data.fail_url,
        created_at: usePage().props.data.created_at,
        expires_at: usePage().props.data.expires_at,
        now: usePage().props.data.now,
        has_dispute: usePage().props.data.has_dispute,
        dispute_status: usePage().props.data.dispute_status,
        dispute_cancel_reason: usePage().props.data.dispute_cancel_reason,
        manually: usePage().props.data.manually,
        gateway_selected: usePage().props.data.gateway_selected,
        available_gateways: usePage().props.data.available_gateways,
    }
}

const openSuccess = () => {
    window.location = data.value.success_url;
};

const openFail = () => {
    window.location = data.value.fail_url;
};

const checkPaid = () => {
    setInterval(async () => {
        router.reload({ only: ['data'] })
    }, 5000);
}

const setStage = () => {
    if (! data.value.gateway_selected) {
        stage.value = 'select_gateway';
    } else  if (data.value.order_status === 'pending' && ! data.value.has_dispute) {
        stage.value = 'payment';
    } else if (data.value.order_status === 'success') {
        stage.value = 'success';
    } else if (data.value.order_status === 'fail' && ! data.value.has_dispute) {
        stage.value = 'fail';
    } else if (data.value.has_dispute && data.value.dispute_status === 'pending') {
        stage.value = 'dispute_review';
    } else if (data.value.has_dispute  && data.value.dispute_status === 'canceled') {
        stage.value = 'dispute_canceled';
    }
}

const submitReceipt = () => {
    formReceipt.post(route('payment.dispute.store', data.value.uuid))
}

const submitGatewaySelect = () => {
    formGatewaySelect.post(route('payment.payment-detail.store', {
        'order': data.value.uuid,
        'paymentGateway': selectedGateway.value,
    }), {
        onSuccess: result => {
            initializeClock();
        },
    })
}

setData();
setStage();

router.on('success', (event) => {
    setData();

    setStage();
})

onMounted(() => {
    initFlowbite();

    setTimeout(() => {
        checkPaid();
    }, 5000)

    if (data.value.gateway_selected) {
        initializeClock();
    }
})

defineOptions({ layout: PaymentLayout });
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900">
        <Head title="Платеж" />

        <div
            class="w-full m-8"
            :class="stage === 'select_gateway' ? 'sm:max-w-lg' : 'sm:max-w-md'"
        >
            <div class="flex justify-between items-center px-2 sm:px-0">
                <h2 class="text-xl font-medium text-gray-900 dark:text-white sm:text-2xl">{{ data.name }}</h2>
                <SupportButton :support_link="data.support_link"/>
            </div>

            <div v-if="stage !== 'select_gateway'" class="sm:mx-0 mx-2 bg-gray-200 dark:bg-gray-700 rounded-xl">
                <div class="flex justify-between mt-3 w-full px-6 py-5 text-sm text-gray-800 bg-white dark:bg-gray-800 rounded-xl dark:text-gray-300">
                    <div>
                        <div class="text-gray-900 dark:text-gray-200 text-2xl">{{ data.amount_formated }}{{ data.currency_symbol }}</div>
                        <div class="text-gray-400 dark:text-gray-500">Сумма для оплаты</div>
                    </div>
                    <div v-show="stage === 'payment'">
                        <div class="text-gray-900 dark:text-gray-200 text-2xl">
                            <Clock :expires_at="data.expires_at" :now="data.now" ref="clockRef"/>
                        </div>
                        <div class="text-gray-400 dark:text-gray-500">Время на оплату</div>
                    </div>
                </div>

                <div>
                    <div class="w-full p-2 text-center text-sm text-gray-800 dark:text-gray-300">
                        <span class="text-blue-500 dark:text-blue-500">ID:</span> {{ data.uuid }}
                    </div>
                </div>
            </div>

            <div class="sm:mx-0 mx-2 mt-4 sm:px-6 px-3 py-4 bg-white dark:bg-gray-800 overflow-hidden rounded-xl">
                <div>
                    <div v-if="stage === 'select_gateway'">
                        <div
                            v-if="! data.available_gateways.length"
                            class="py-5 flex items-center justify-center sm:text-xl text-xl text-gray-900 dark:text-gray-200 sm:mb-0 mb-3"
                        >
                            Доступные методы оплаты не найдены.
                        </div>

                        <template v-else>
                            <div v-show="$page.props.flash.message && ! formGatewaySelect.processing" class="flex items-center p-4 mb-4 text-sm text-red-800 border border-red-300 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400 dark:border-red-800" role="alert">
                                <svg class="flex-shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                                </svg>
                                <div>
                                    {{ $page.props.flash.message }}
                                </div>
                            </div>
                            <div class="relative sm:my-5 sm:text-base text-sm grid sm:grid-cols-3 grid-cols-2 gap-4 text-center">
                                <div v-show="formGatewaySelect.processing" role="status" class="absolute -translate-x-1/2 -translate-y-1/2 top-2/4 left-1/2 z-20">
                                    <svg aria-hidden="true" class="w-10 h-10 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/><path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/></svg>
                                    <span class="sr-only">Loading...</span>
                                </div>
                                <div v-show="formGatewaySelect.processing" class="absolute w-full h-full bg-gray-800/90 rounded-xl z-10"></div>
                                <div
                                    v-for="gateway in data.available_gateways"
                                    class="relative text-gray-900 dark:text-gray-200 border border-gray-200 dark:border-gray-600 rounded-xl cursor-pointer hover:border-blue-500/70 hover:dark:border-blue-400/70"
                                    @click="selectedGateway = gateway.id"
                                    :class="selectedGateway === gateway.id ? 'border border-blue-500/70 dark:border-blue-400/70' : ''"
                                >
                                    <div v-if="selectedGateway === gateway.id" class="absolute top-1 right-1">
                                        <svg class="w-6 h-6 text-blue-500 dark:text-blue-400/70" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 11.917 9.724 16.5 19 7.5"/>
                                        </svg>
                                    </div>
                                    <div class="m-3">
                                        <div class="flex justify-center">
                                            <GatewayLogo
                                                :img_path="gateway.logo_path"
                                                class="w-14 h-14 text-gray-400 dark:text-gray-500"
                                            />
                                        </div>
                                        <div class="text-sm truncate mt-2">
                                            {{gateway.name}}
                                        </div>
                                        <div class="text-gray-400 dark:text-gray-500 text-xs">
                                            Комиссия: {{ gateway.commission }}%
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-5 sm:pb-3">
                                <button
                                    type="button"
                                    class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-xl text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800"
                                    :disabled="! selectedGateway || formGatewaySelect.processing"
                                    @click.prevent="submitGatewaySelect"
                                >
                                    Выбрать
                                </button>
                            </div>
                        </template>
                    </div>

                    <div v-show="stage === 'payment'" class="sm:pb-3">
                        <div
                            v-if="data.sub_payment_gateway"
                            class="flex items-center sm:text-2xl text-xl text-gray-900 dark:text-gray-200 sm:mb-0 mb-3"
                        >
                            <img src="/images/sbp.svg" class="mr-2 w-8 h-8">
                            Быстрая оплата или СБП
                        </div>
                        <div v-if="data.detail_type === 'account_number'" class="flex items-center p-3 mb-4 sm:text-sm text-xs text-yellow-800 border border-yellow-300 rounded-xl bg-yellow-50 dark:bg-yellow-500/20 dark:text-yellow-300 dark:border-yellow-800">
                            <svg class="flex-shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                            </svg>
                            <div>
                                Внимание это перевод по счету, а не на карту!
                            </div>
                        </div>

                        <div class="sm:my-5 sm:text-base text-sm space-y-2">
                            <div class="flex justify-between items-center border border-gray-200 dark:border-gray-600 rounded-xl p-3">
                                <div class="flex items-center text-gray-900 dark:text-gray-200 sm:text-base text-xs">
                                    <template v-if="data.detail_type === 'card'">
                                        <svg class="mr-2 text-blue-500 sm:w-6 sm:h-6 w-5 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M6 14h2m3 0h5M3 7v10a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1Z"/>
                                        </svg>
                                        Номер карты
                                    </template>
                                    <template v-else-if="data.detail_type === 'phone'">
                                        <svg class="mr-2 text-blue-500 sm:w-6 sm:h-6 w-5 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 15h12M6 6h12m-6 12h.01M7 21h10a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1v16a1 1 0 0 0 1 1Z"/>
                                        </svg>
                                        Номер телефона
                                    </template>
                                    <template v-else-if="data.detail_type === 'account_number'">
                                        <svg class="mr-2 text-blue-500 sm:w-6 sm:h-6 w-5 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 9h3m-3 3h3m-3 3h3m-6 1c-.306-.613-.933-1-1.618-1H7.618c-.685 0-1.312.387-1.618 1M4 5h16a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Zm7 5a2 2 0 1 1-4 0 2 2 0 0 1 4 0Z"/>
                                        </svg>
                                        Номер счета
                                    </template>
                                </div>
                                <div class="text-gray-900 dark:text-gray-200">
                                    <CopyPaymentText :text="formatedDetail" :copy_text="data.detail"></CopyPaymentText>
                                </div>
                            </div>
                            <div class="flex justify-between items-center border border-gray-200 dark:border-gray-600 rounded-xl p-3">
                                <div class="flex items-center text-gray-900 dark:text-gray-200 sm:text-base text-xs">
                                    <svg class="mr-2 text-blue-500 sm:w-6 sm:h-6 w-5 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0 0a8.949 8.949 0 0 0 4.951-1.488A3.987 3.987 0 0 0 13 16h-2a3.987 3.987 0 0 0-3.951 3.512A8.948 8.948 0 0 0 12 21Zm3-11a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                    </svg>
                                    <template v-if="data.detail_type === 'card'">
                                        Держатель карты
                                    </template>
                                    <template v-else>
                                        Получатель
                                    </template>
                                </div>
                                <div class="text-gray-900 dark:text-gray-200">
                                    <CopyPaymentText :text="data.initials" :copy_text="data.initials"></CopyPaymentText>
                                </div>
                            </div>
                            <div v-if="data.sub_payment_gateway" class="flex justify-between items-center border border-gray-200 dark:border-gray-600 rounded-xl p-3">
                                <div class="flex items-center text-gray-900 dark:text-gray-200 sm:text-base text-xs">
                                    <svg class="mr-2 text-blue-500 sm:w-6 sm:h-6 w-5 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M3 21h18M4 18h16M6 10v8m4-8v8m4-8v8m4-8v8M4 9.5v-.955a1 1 0 0 1 .458-.84l7-4.52a1 1 0 0 1 1.084 0l7 4.52a1 1 0 0 1 .458.84V9.5a.5.5 0 0 1-.5.5h-15a.5.5 0 0 1-.5-.5Z"/>
                                    </svg>
                                    Банк
                                </div>
                                <div class="text-gray-900 dark:text-gray-200">
                                    {{ data.sub_payment_gateway }}
                                </div>
                            </div>
                            <div class="flex justify-between items-center border border-gray-200 dark:border-gray-600 rounded-xl p-3">
                                <div class="flex items-center text-gray-900 dark:text-gray-200 sm:text-base text-xs">
                                    <svg class="mr-2 text-blue-500 sm:w-6 sm:h-6 w-5 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M8 7V6a1 1 0 0 1 1-1h11a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1h-1M3 18v-7a1 1 0 0 1 1-1h11a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1Zm8-3.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z"/>
                                    </svg>
                                    Сумма перевода
                                </div>
                                <div class="text-gray-900 dark:text-gray-200">
                                    <CopyPaymentText :text="data.amount_formated+data.currency_symbol" :copy_text="data.amount"></CopyPaymentText>
                                </div>
                            </div>
                        </div>

                        <div v-if="data.detail_type !== 'account_number'" class="mt-3 sm:mt-0 flex items-center p-3 mb-4 sm:text-sm text-xs text-yellow-800 border border-yellow-300 rounded-xl bg-yellow-50 dark:bg-yellow-500/20 dark:text-yellow-300 dark:border-yellow-800">
                            <svg class="flex-shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                            </svg>
                            <div>
                                Переводите точную сумму, иначе средства не поступят!
                            </div>
                        </div>

                        <div class="mt-5">
                            <button
                                type="button"
                                class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-xl text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800"
                                data-modal-target="default-modal"
                                data-modal-toggle="default-modal"
                            >
                                Инструкция к оплате
                            </button>
                        </div>
                    </div>

                    <div v-if="stage === 'success' || stage === 'fail'" class="py-1">
                        <div class="mt-5 mb-5 text-base flex justify-center">
                            <div class="w-2/3">
                                <template v-if="stage === 'success'">
                                    <div class="flex items-center justify-center mb-2">
                                        <svg class="w-24 h-24 text-green-400 dark:text-green-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.5 11.5 11 14l4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                        </svg>
                                    </div>
                                    <p class="mb-1 text-2xl font-semibold text-gray-900 dark:text-gray-200 text-center">
                                        Платеж зачислен
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center">
                                        Счет на сумму {{ data.amount_formated }}{{ data.currency_symbol }} оплачен. Спасибо за оплату.
                                    </p>
                                </template>
                                <template v-else>
                                    <div class="flex items-center justify-center mb-2">
                                        <svg class="w-24 h-24 text-red-500 dark:text-red-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 9-6 6m0-6 6 6m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                        </svg>
                                    </div>
                                    <p class="mb-1 text-2xl font-semibold text-gray-900 dark:text-gray-200 text-center">
                                        Время истекло
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center">
                                        Счет на сумму {{ data.amount_formated }}{{ data.currency_symbol }} не оплачен. Оплата не поступила.
                                    </p>
                                </template>
                            </div>
                        </div>

                        <div class="mt-5" v-show="stage === 'success' && data.success_url">
                            <button
                                @click.prevent="openSuccess"
                                type="button"
                                class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-xl text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800"
                            >
                                Вернуться на сайт
                            </button>
                        </div>

                        <form @submit.prevent="submitReceipt" v-show="stage === 'fail'" class="w-full">
                            <div class="text-gray-500 dark:text-gray-400 text-sm mb-3 text-center">
                                Загрузите чек вашей транзакции, что бы мы могли найти ваш платеж
                            </div>
                            <Dropzone v-model="formReceipt.receipt" description="Расширение: jpeg, jpg, png, pdf"/>
                            <InputError :message="formReceipt.errors.receipt" class="mt-2" />

                            <div class="mt-4">
                                <button
                                    type="submit"
                                    class="w-full text-white bg-gray-800 hover:bg-gray-900 focus:outline-none focus:ring-4 focus:ring-gray-300 font-medium rounded-xl text-sm px-5 py-2.5 dark:border dark:bg-gray-950/20 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700"
                                >
                                    Отправить
                                </button>
                            </div>

                            <div class="mt-5">
                                <button
                                    v-show="data.fail_url"
                                    @click.prevent="openFail"
                                    type="button"
                                    class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-xl text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800"
                                >
                                    Вернуться на сайт
                                </button>
                            </div>
                        </form>
                    </div>

                    <div v-if="stage === 'dispute_review'" class="py-1">
                        <div class="mt-5 mb-5 text-base flex justify-center">
                            <div class="w-2/3">
                                <div class="flex items-center justify-center mb-2">
                                    <svg class="w-24 h-24 text-green-400 dark:text-green-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8.032 12 1.984 1.984 4.96-4.96m4.55 5.272.893-.893a1.984 1.984 0 0 0 0-2.806l-.893-.893a1.984 1.984 0 0 1-.581-1.403V7.04a1.984 1.984 0 0 0-1.984-1.984h-1.262a1.983 1.983 0 0 1-1.403-.581l-.893-.893a1.984 1.984 0 0 0-2.806 0l-.893.893a1.984 1.984 0 0 1-1.403.581H7.04A1.984 1.984 0 0 0 5.055 7.04v1.262c0 .527-.209 1.031-.581 1.403l-.893.893a1.984 1.984 0 0 0 0 2.806l.893.893c.372.372.581.876.581 1.403v1.262a1.984 1.984 0 0 0 1.984 1.984h1.262c.527 0 1.031.209 1.403.581l.893.893a1.984 1.984 0 0 0 2.806 0l.893-.893a1.985 1.985 0 0 1 1.403-.581h1.262a1.984 1.984 0 0 0 1.984-1.984V15.7c0-.527.209-1.031.581-1.403Z"/>
                                    </svg>
                                </div>
                                <p class="mb-1 text-2xl font-semibold text-gray-900 dark:text-gray-200 text-center">
                                    Чек загружен
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 text-center">
                                    Ожидает рассмотрения. Страница обновится автоматически.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div v-if="stage === 'dispute_canceled'">
                        <div class="mt-5 mb-5 text-base flex justify-center">
                            <div class="w-2/3">
                                <div class="flex items-center justify-center mb-2">
                                    <svg class="w-24 h-24 text-red-500 dark:text-red-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="m6 6 12 12m3-6a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                    </svg>
                                </div>
                                <p class="mb-1 text-2xl font-semibold text-gray-900 dark:text-gray-200 text-center">
                                    Заявка отклонена
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 text-center">
                                    Причина: {{ data.dispute_cancel_reason }}.
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 text-center mt-3">
                                    Если вы не согласны с решением, свяжитесь с поддержкой, кнопка вверху.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Main modal -->
                    <div id="default-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
                        <div class="relative p-4 w-full max-w-xl max-h-full">
                            <!-- Modal content -->
                            <div class="relative bg-white rounded-xl shadow dark:bg-gray-700">
                                <!-- Modal header -->
                                <div class="flex items-center justify-between p-4 md:p-5 rounded-t">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                        Инструкция к оплате
                                    </h3>
                                </div>
                                <!-- Modal body -->
                                <div class="p-6 pt-0">
                                    <ul class="w-full space-y-1 text-gray-900 list-inside dark:text-gray-200">
                                        <li class="flex items-center space-x-3 rtl:space-x-reverse">
                                            <svg class="flex-shrink-0 w-3.5 h-3.5 text-green-500 dark:text-green-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 16 12">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5.917 5.724 10.5 15 1.5"/>
                                            </svg>
                                            <span>Зайдите в свое банковское приложение</span>
                                        </li>
                                        <li class="flex items-center space-x-3 rtl:space-x-reverse">
                                            <svg class="flex-shrink-0 w-3.5 h-3.5 text-green-500 dark:text-green-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 16 12">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5.917 5.724 10.5 15 1.5"/>
                                            </svg>
                                            <span v-if="data.detail_type === 'card'">Скопируйте номер карты для перевода <b class="text-nowrap">{{ formatedDetail }}</b></span>
                                            <span v-if="data.detail_type === 'phone'">Скопируйте номер телефона для перевода <b class="text-nowrap">{{ formatedDetail }}</b></span>
                                            <span v-if="data.detail_type === 'account_number'">Скопируйте номер счета для перевода <b class="text-nowrap">{{ formatedDetail }}</b></span>
                                        </li>
                                        <li class="flex items-center space-x-3 rtl:space-x-reverse">
                                            <svg class="flex-shrink-0 w-3.5 h-3.5 text-green-500 dark:text-green-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 16 12">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5.917 5.724 10.5 15 1.5"/>
                                            </svg>
                                            <span v-if="data.detail_type === 'card'">В банковском приложении выберите перевод по карте</span>
                                            <span v-if="data.detail_type === 'phone'">В банковском приложении выберите перевод по СБП</span>
                                            <span v-if="data.detail_type === 'account_number'">В банковском приложении выберите перевод по номеру счета</span>
                                        </li>
                                        <li class="flex items-center space-x-3 rtl:space-x-reverse">
                                            <svg class="flex-shrink-0 w-3.5 h-3.5 text-green-500 dark:text-green-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 16 12">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5.917 5.724 10.5 15 1.5"/>
                                            </svg>
                                            <span>Сделайте перевод точной суммы <b class="text-nowrap">{{ data.amount_formated }}{{ data.currency_symbol }}</b></span>
                                        </li>
                                        <li class="flex items-center space-x-3 rtl:space-x-reverse">
                                            <svg class="flex-shrink-0 w-3.5 h-3.5 text-green-500 dark:text-green-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 16 12">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5.917 5.724 10.5 15 1.5"/>
                                            </svg>
                                            <span>Дождитесь зачисления средств. Не закрывайте страницу до подтверждения успешной оплаты.</span>
                                        </li>
                                    </ul>
                                    <div class="p-4 mt-5 text-sm text-gray-900 dark:text-gray-400 rounded-xl bg-red-50 dark:bg-gray-800" role="alert">
                                        <span class="font-medium text-red-800 dark:text-red-400">Запрещено:</span> Оплачивать заявку несколькими переводами. В случае
                                        несоблюдений рекомендаций заявка будет отменена, а средства будут утеряны
                                    </div>
                                </div>
                                <!-- Modal footer -->
                                <div class="flex items-center p-6 pt-0 rounded-b dark:border-gray-600">
                                    <button data-modal-hide="default-modal" type="button" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-xl text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Закрыть</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex justify-center mt-3">
                <ColorThemeSwitcher/>
            </div>

            <StageSwitcher :stage="stage"/>
        </div>
    </div>
</template>
