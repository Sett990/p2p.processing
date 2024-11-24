<script setup>
import {Head, router, useForm, usePage} from '@inertiajs/vue3';
import PaymentLayout from "@/Layouts/PaymentLayout.vue";
import CopyPaymentText from "@/Components/CopyPaymentText.vue";
import {computed, onMounted, ref} from "vue";
import {initFlowbite} from "flowbite";
import InputError from "@/Components/InputError.vue";

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const stage = ref('payment');

const isDarkColorTheme = ref(false);

const switchThemeColorMode = () => {
    // if set via local storage previously
    if (localStorage.getItem('color-theme-payment')) {
        if (localStorage.getItem('color-theme-payment') === 'light') {
            document.documentElement.classList.add('dark');
            localStorage.setItem('color-theme-payment', 'dark');
            isDarkColorTheme.value = true;
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('color-theme-payment', 'light');
            isDarkColorTheme.value = false;
        }

        // if NOT set via local storage previously
    } else {
        if (document.documentElement.classList.contains('dark')) {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('color-theme-payment', 'light');
            isDarkColorTheme.value = false;
        } else {
            document.documentElement.classList.add('dark');
            localStorage.setItem('color-theme-payment', 'dark');
            isDarkColorTheme.value = true;
        }
    }
}

onMounted(() => {
    initFlowbite();

    if (localStorage.getItem('color-theme-payment') === 'dark' || (!('color-theme-payment' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('color-theme-payment', 'dark');
        isDarkColorTheme.value = true;
    } else {
        document.documentElement.classList.remove('dark')
        localStorage.setItem('color-theme-payment', 'light');
        isDarkColorTheme.value = false;
    }

    setTimeout(() => {
        checkPaid();
    }, 5000)
})

const data = ref({});
const clock = ref( {
    days: "0",
    hours: "0",
    minutes: "0",
    seconds: "0",
    now: null,
});

const setData = () => {
    data.value = {
        order_status: usePage().props.data.order_status,
        uuid: usePage().props.data.uuid,
        name: usePage().props.data.name,
        amount: usePage().props.data.amount,
        amount_formated: usePage().props.data.amount_formated,
        currency_symbol: usePage().props.data.currency_symbol,
        payment_link: usePage().props.data.payment_link,
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
    }
}

setData();

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

const openSuccess = () => {
    window.location = data.value.success_url;
};

const openFail = () => {
    window.location = data.value.fail_url;
};

const openSupport = () => {
    window.open(data.value.payment_link, '_blank');
};

const getTimeRemaining = (endtime, now) => {
    var t = Date.parse(endtime) - Date.parse(now);
    var seconds = Math.floor((t / 1000) % 60);
    var minutes = Math.floor((t / 1000 / 60) % 60);
    var hours = Math.floor((t / (1000 * 60 * 60)) % 24);
    var days = Math.floor(t / (1000 * 60 * 60 * 24));

    return {
        'total': t,
        'days': days,
        'hours': hours,
        'minutes': minutes,
        'seconds': seconds
    };
}

const initializeClock = () => {
    let endtime = new Date(data.value.expires_at);
    clock.value.now = new Date(data.value.now);

    function updateClock() {
        clock.value.now = new Date(Date.parse(clock.value.now) + 1000);
        var t = getTimeRemaining(endtime, clock.value.now);

        clock.value.days = t.days;
        clock.value.hours = ('0' + t.hours).slice(-2);
        clock.value.minutes = ('0' + t.minutes).slice(-2);
        clock.value.seconds = ('0' + t.seconds).slice(-2);

        if (t.total <= 0) {
            clearInterval(timeinterval);
            clock.value.days = '00';
            clock.value.hours = '00';
            clock.value.minutes = '00';
            clock.value.seconds = '00';
        } else {
            clock.value.days = t.days;
            clock.value.hours = ('0' + t.hours).slice(-2);
            clock.value.minutes = ('0' + t.minutes).slice(-2);
            clock.value.seconds = ('0' + t.seconds).slice(-2);
        }
    }

    updateClock();
    var timeinterval = setInterval(updateClock, 1000);
}

initializeClock();

const checkPaid = () => {
    setInterval(async () => {
        router.reload({ only: ['data'] })
    }, 5000);
}

router.on('success', (event) => {
    setData();

    clock.value.now = new Date(data.value.now);

    setStage();
})

const setStage = () => {
    if (data.value.order_status === 'pending' && ! data.value.has_dispute) {
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

setStage();

const formReceipt = useForm({
    receipt: null,
})

const receiptName = computed(() => {
    if (! formReceipt?.receipt?.name) {
        return null;
    }

    var split = formReceipt.receipt.name.split('.');
    var filename = split[0];
    var extension = split[1];

    if (filename.length > 10) {
        filename = filename.substring(0, 5) + '...' + filename.substring(5, 10);
    }

    return filename + '.' + extension;
})

function submitReceipt() {
    formReceipt.post(route('payment.dispute.store', data.value.uuid))
}

defineOptions({ layout: PaymentLayout });
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900">
        <Head title="Платеж" />

        <div
            class="w-full sm:max-w-md m-8"
        >
            <div class="flex justify-between items-center px-2 sm:px-0">
                <h2 class="text-xl font-medium text-gray-900 dark:text-white sm:text-2xl">{{ data.name }}</h2>
                <button
                    v-show="data.payment_link"
                    @click.prevent="openSupport"
                    type="button"
                    class="text-center flex items-center justify-center text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-xl text-xs px-2 py-1.5 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700"
                >
                    <span class="[&>svg]:h-4 [&>svg]:w-4 me-2">
                      <svg
                          xmlns="http://www.w3.org/2000/svg"
                          fill="currentColor"
                          viewBox="0 0 496 512">
                        <!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc. -->
                        <path
                            d="M248 8C111 8 0 119 0 256S111 504 248 504 496 393 496 256 385 8 248 8zM363 176.7c-3.7 39.2-19.9 134.4-28.1 178.3-3.5 18.6-10.3 24.8-16.9 25.4-14.4 1.3-25.3-9.5-39.3-18.7-21.8-14.3-34.2-23.2-55.3-37.2-24.5-16.1-8.6-25 5.3-39.5 3.7-3.8 67.1-61.5 68.3-66.7 .2-.7 .3-3.1-1.2-4.4s-3.6-.8-5.1-.5q-3.3 .7-104.6 69.1-14.8 10.2-26.9 9.9c-8.9-.2-25.9-5-38.6-9.1-15.5-5-27.9-7.7-26.8-16.3q.8-6.7 18.5-13.7 108.4-47.2 144.6-62.3c68.9-28.6 83.2-33.6 92.5-33.8 2.1 0 6.6 .5 9.6 2.9a10.5 10.5 0 0 1 3.5 6.7A43.8 43.8 0 0 1 363 176.7z" />
                      </svg>
                    </span>
                    Поддержка
                </button>
            </div>

            <div class="sm:mx-0 mx-2 bg-gray-200 dark:bg-gray-700 rounded-xl">
                <div class="flex justify-between mt-3 w-full px-6 py-5 text-sm text-gray-800 bg-white dark:bg-gray-800 rounded-xl dark:text-gray-300">
                    <div>
                        <div class="text-gray-900 dark:text-gray-200 text-2xl">{{ data.amount_formated }}{{ data.currency_symbol }}</div>
                        <div class="text-gray-400 dark:text-gray-500">Сумма для оплаты</div>
                    </div>
                    <div v-show="stage === 'payment'">
                        <div class="text-gray-900 dark:text-gray-200 text-2xl">{{ clock.minutes }}:{{ clock.seconds }}</div>
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
                    <div v-if="stage === 'payment'" class="sm:pb-3">
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
                            <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-16 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600">
                                <div class="flex items-center justify-center pt-5 pb-6">
                                    <svg class="w-8 h-8 mr-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                                    </svg>
                                    <div>
                                        <p v-if="!formReceipt.receipt" class="text-sm text-gray-500 dark:text-gray-400">Нажмите, чтобы загрузить файл</p>
                                        <p v-else class="text-sm text-gray-500 dark:text-gray-400">{{ receiptName }}</p>
                                    </div>
                                </div>
                                <input id="dropzone-file" type="file" @input="formReceipt.receipt = $event.target.files[0]" class="hidden" />
                            </label>
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
                <button @click.prevent="switchThemeColorMode" type="button" class="text-gray-800 dark:text-white border-none hover:text-gray-500 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-full text-sm p-2.5 text-center inline-flex items-center dark:hover:text-gray-500 dark:focus:ring-blue-800">
                    <svg v-if="isDarkColorTheme" class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                        <path fill-rule="evenodd" d="M13 3a1 1 0 1 0-2 0v2a1 1 0 1 0 2 0V3ZM6.343 4.929A1 1 0 0 0 4.93 6.343l1.414 1.414a1 1 0 0 0 1.414-1.414L6.343 4.929Zm12.728 1.414a1 1 0 0 0-1.414-1.414l-1.414 1.414a1 1 0 0 0 1.414 1.414l1.414-1.414ZM12 7a5 5 0 1 0 0 10 5 5 0 0 0 0-10Zm-9 4a1 1 0 1 0 0 2h2a1 1 0 1 0 0-2H3Zm16 0a1 1 0 1 0 0 2h2a1 1 0 1 0 0-2h-2ZM7.757 17.657a1 1 0 1 0-1.414-1.414l-1.414 1.414a1 1 0 1 0 1.414 1.414l1.414-1.414Zm9.9-1.414a1 1 0 0 0-1.414 1.414l1.414 1.414a1 1 0 0 0 1.414-1.414l-1.414-1.414ZM13 19a1 1 0 1 0-2 0v2a1 1 0 1 0 2 0v-2Z" clip-rule="evenodd"/>
                    </svg>
                    <svg v-else class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5V3m0 18v-2M7.05 7.05 5.636 5.636m12.728 12.728L16.95 16.95M5 12H3m18 0h-2M7.05 16.95l-1.414 1.414M18.364 5.636 16.95 7.05M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z"/>
                    </svg>
                </button>
            </div>

<!--            <div class="text-xs text-black dark:text-white flex gap-2 cursor-pointer">
                <div @click="stage = 'payment'" :class="{'text-blue-500' : stage === 'payment'}">payment</div>
                <div @click="stage = 'success'" :class="{'text-blue-500' : stage === 'success'}">success</div>
                <div @click="stage = 'fail'" :class="{'text-blue-500' : stage === 'fail'}">fail</div>
                <div @click="stage = 'dispute_review'" :class="{'text-blue-500' : stage === 'dispute_review'}">dispute_review</div>
                <div @click="stage = 'dispute_canceled'" :class="{'text-blue-500' : stage === 'dispute_canceled'}">dispute_canceled</div>
            </div>-->
        </div>
    </div>
</template>
