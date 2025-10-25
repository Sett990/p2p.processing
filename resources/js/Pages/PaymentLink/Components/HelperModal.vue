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
    <div id="helper-modal" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-50 overflow-y-auto overflow-x-hidden flex items-center justify-center w-full h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-xl max-h-full">
            <div class="relative bg-base-100 rounded-2xl shadow">
                <div class="flex items-center justify-between p-5 rounded-t">
                    <h3 class="text-lg font-semibold text-base-content">
                        Инструкция к оплате
                    </h3>
                </div>
                <div class="p-6 pt-0">
                    <ul class="w-full space-y-1 text-base-content list-inside">
                        <li class="flex items-center space-x-3 rtl:space-x-reverse">
                            <svg class="flex-shrink-0 w-3.5 h-3.5 text-success" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 16 12">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5.917 5.724 10.5 15 1.5"/>
                            </svg>
                            <span>Зайдите в свое банковское приложение</span>
                        </li>
                        <li class="flex items-center space-x-3 rtl:space-x-reverse">
                            <svg class="flex-shrink-0 w-3.5 h-3.5 text-success" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 16 12">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5.917 5.724 10.5 15 1.5"/>
                            </svg>
                            <span v-if="data.detail_type === 'card'">Скопируйте номер карты для перевода <b class="text-nowrap">{{ formatedPaymentDetail }}</b></span>
                            <span v-if="data.detail_type === 'phone'">Скопируйте номер телефона для перевода <b class="text-nowrap">{{ formatedPaymentDetail }}</b></span>
                            <span v-if="data.detail_type === 'account_number'">Скопируйте номер счета для перевода <b class="text-nowrap">{{ formatedPaymentDetail }}</b></span>
                        </li>
                        <li class="flex items-center space-x-3 rtl:space-x-reverse">
                            <svg class="flex-shrink-0 w-3.5 h-3.5 text-success" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 16 12">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5.917 5.724 10.5 15 1.5"/>
                            </svg>
                            <span v-if="data.detail_type === 'card'">В банковском приложении выберите перевод по карте</span>
                            <span v-if="data.detail_type === 'phone'">В банковском приложении выберите перевод по СБП</span>
                            <span v-if="data.detail_type === 'account_number'">В банковском приложении выберите перевод по номеру счета</span>
                        </li>
                        <li class="flex items-center space-x-3 rtl:space-x-reverse">
                            <svg class="flex-shrink-0 w-3.5 h-3.5 text-success" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 16 12">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5.917 5.724 10.5 15 1.5"/>
                            </svg>
                            <span>Сделайте перевод точной суммы <b class="text-nowrap">{{ data.amount_formated }}{{ data.currency_symbol }}</b></span>
                        </li>
                        <li class="flex items-center space-x-3 rtl:space-x-reverse">
                            <svg class="flex-shrink-0 w-3.5 h-3.5 text-success" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 16 12">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5.917 5.724 10.5 15 1.5"/>
                            </svg>
                            <span>Дождитесь зачисления средств. Не закрывайте страницу до подтверждения успешной оплаты.</span>
                        </li>
                    </ul>
                    <div class="alert alert-warning mt-5 text-sm">
                        <span>
                            <span class="font-semibold">Запрещено:</span> Оплачивать заявку несколькими переводами. В случае несоблюдений рекомендаций заявка будет отменена, а средства будут утеряны
                        </span>
                    </div>
                </div>
                <div class="flex items-center p-6 pt-0 rounded-b">
                    <button data-modal-hide="helper-modal" type="button" class="btn btn-primary w-full">Закрыть</button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
