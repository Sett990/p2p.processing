<script setup>
import { computed } from "vue";
import { useFormatPaymentDetail } from "@/Utils/paymentDetail.js";

const props = defineProps({
    data: {
        type: Object,
        default: {}
    }
});

const formatedPaymentDetail = computed(() => {
    return useFormatPaymentDetail(props.data.detail, props.data.detail_type);
})
</script>

<template>
    <div id="helper-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
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
                            <span v-if="data.detail_type === 'card'">Скопируйте номер карты для перевода <b class="text-nowrap">{{ formatedPaymentDetail }}</b></span>
                            <span v-if="data.detail_type === 'phone'">Скопируйте номер телефона для перевода <b class="text-nowrap">{{ formatedPaymentDetail }}</b></span>
                            <span v-if="data.detail_type === 'account_number'">Скопируйте номер счета для перевода <b class="text-nowrap">{{ formatedPaymentDetail }}</b></span>
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
                    <button data-modal-hide="helper-modal" type="button" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-xl text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Закрыть</button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
