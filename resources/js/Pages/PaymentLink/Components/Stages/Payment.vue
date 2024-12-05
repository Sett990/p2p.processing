<script setup>
import CopyPaymentText from "@/Components/CopyPaymentText.vue";
import MainButton from "@/Pages/PaymentLink/Components/MainButton.vue";
import {computed} from "vue";
import {useFormatPaymentDetail} from "@/Utils/paymentDetail.js";

const props = defineProps({
    data: {
        type: Object,
        default: {}
    },
});

const formatedPaymentDetail = computed(() => {
    return useFormatPaymentDetail(props.data.detail, props.data.detail_type);
})
</script>

<template>
    <div class="sm:pb-3">
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
                    <CopyPaymentText :text="formatedPaymentDetail" :copy_text="data.detail"></CopyPaymentText>
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
            <MainButton
                text="Инструкция к оплате"
                data-modal-target="helper-modal"
                data-modal-toggle="helper-modal"
            />
        </div>
    </div>

</template>

<style scoped>

</style>
