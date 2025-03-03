<script setup>
import ModalFooter from "@/Components/Modals/Components/ModalFooter.vue";
import Modal from "@/Components/Modals/Modal.vue";
import PaymentDetail from "@/Components/PaymentDetail.vue";
import {Link, router, useForm, usePage} from "@inertiajs/vue3";
import ModalHeader from "@/Components/Modals/Components/ModalHeader.vue";
import ModalBody from "@/Components/Modals/Components/ModalBody.vue";
import {useModalStore} from "@/store/modal.js";
import {storeToRefs} from "pinia";
import {useViewStore} from "@/store/view.js";

const viewStore = useViewStore();
const modalStore = useModalStore();
const { orderModal } = storeToRefs(modalStore);
const user = usePage().props.auth.user;

const showUserSmsLogs = () => {
    modalStore.openUserSmsLogsModal({user: user});
};

const closeModal = () => {
    modalStore.closeModal('order');
};

const confirmAcceptOrder = (order) => {
    modalStore.openConfirmModal({
        title: 'Вы уверены что хотите  закрыть сделку как оплаченную?',
        confirm_button_name: 'Платеж поступил',
        confirm: () => {
            useForm({}).patch(route('orders.accept', order.id), {
                preserveScroll: true,
                onSuccess: () => {
                    modalStore.closeAll()
                    router.visit(route(viewStore.adminPrefix + 'orders.index'), {
                        only: ['orders'],
                    })
                },
            })
        }
    });
}

const orderPaymentLink = (payment_link) => {
    window.open(payment_link, '_blank')
}
</script>

<template>
    <Modal :show="!! orderModal.showed" @close="closeModal" maxWidth="md">
        <ModalHeader
            :title="'Данные сделки #' + orderModal.params.order.uuid_short"
            @close="closeModal"
        />
        <ModalBody>
            <form action="#" class="mx-auto max-w-screen-xl px-2 2xl:px-0">
                <div class="mx-auto max-w-3xl">
                    <div>
                        <div>
                            <div class="mb-3">
                                <div v-if="orderModal.params.order.status === 'success'">
                                    <div class="flex items-center justify-center mb-2">
                                        <svg class="w-16 h-16 text-green-400 dark:text-green-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.5 11.5 11 14l4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                        </svg>
                                    </div>
                                    <p class="mb-1 text-lg font-semibold text-gray-900 dark:text-gray-300 text-center">Платеж зачислен</p>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-400 text-center">{{ orderModal.params.order.finished_at }}</p>
                                </div>
                                <div v-else-if="orderModal.params.order.status === 'fail'">
                                    <div class="flex items-center justify-center mb-2">
                                        <svg class="w-16 h-16 text-red-500 dark:text-red-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 9-6 6m0-6 6 6m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                        </svg>
                                    </div>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-300 text-center">Платеж отменен</p>
                                </div>
                                <div v-else-if="orderModal.params.order.status === 'pending'">
                                    <div class="flex items-center justify-center mb-2">
                                        <svg class="w-16 h-16 text-yellow-300 dark:text-yellow-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                        </svg>
                                    </div>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-300 text-center">Платеж еще не поступил</p>
                                </div>
                            </div>
<!--                            <div class="flex justify-end space-x-1.5 mb-3"> // TODO вернуть, а для этого надо сделать загрузку sms logs
                                <button
                                    @click.prevent="showUserSmsLogs(orderModal.params.order)"
                                    type="button"
                                    class="p-1 text-xs font-medium text-center inline-flex items-center text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 rounded-xl  dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700"
                                >
                                    <svg class="w-4 h-4 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.556 8.5h8m-8 3.5H12m7.111-7H4.89a.896.896 0 0 0-.629.256.868.868 0 0 0-.26.619v9.25c0 .232.094.455.26.619A.896.896 0 0 0 4.89 16H9l3 4 3-4h4.111a.896.896 0 0 0 .629-.256.868.868 0 0 0 .26-.619v-9.25a.868.868 0 0 0-.26-.619.896.896 0 0 0-.63-.256Z"/>
                                    </svg>
                                </button>
                            </div>-->
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <dl v-if="viewStore.isAdminViewMode" class="flex items-center justify-between gap-4">
                                        <dt class="text-gray-500 dark:text-gray-400">Мерчант</dt>
                                        <dd class="text-base font-medium text-gray-900 dark:text-gray-300"><span class="truncate">{{ orderModal.params.order.merchant.name }}</span> (id:{{ orderModal.params.order.merchant.id }})</dd>
                                    </dl>
                                    <dl class="flex items-center justify-between gap-4">
                                        <dt class="text-gray-500 dark:text-gray-400">UUID</dt>
                                        <dd class="text-base font-medium text-gray-900 dark:text-gray-300">{{ orderModal.params.order.uuid }}</dd>
                                    </dl>
                                    <dl v-if="viewStore.isAdminViewMode" class="flex items-center justify-between gap-4">
                                        <dt class="text-gray-500 dark:text-gray-400">Внешний ID</dt>
                                        <dd class="text-base font-medium text-gray-900 dark:text-gray-300">{{ orderModal.params.order.external_id }}</dd>
                                    </dl>
                                    <dl class="flex items-center justify-between gap-4">
                                        <dt class="text-gray-500 dark:text-gray-400">Сумма</dt>
                                        <dd class="text-base font-medium text-gray-900 dark:text-gray-300">
                                            <div class="flex gap-2">
                                                <a
                                                    v-if="orderModal.params.order.canEditAmount"
                                                    href="#"
                                                    class="px-0 py-0 text-blue-500 hover:text-blue-600 inline-flex items-center hover:underline"
                                                    @click.prevent="modalStore.openEditOrderAmountModal({order: orderModal.params.order})"
                                                >
                                                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.779 17.779 4.36 19.918 6.5 13.5m4.279 4.279 8.364-8.643a3.027 3.027 0 0 0-2.14-5.165 3.03 3.03 0 0 0-2.14.886L6.5 13.5m4.279 4.279L6.499 13.5m2.14 2.14 6.213-6.504M12.75 7.04 17 11.28"/>
                                                    </svg>
                                                </a>
                                                <div>
                                                    {{ orderModal.params.order.amount }} {{orderModal.params.order.currency.toUpperCase()}}
                                                </div>
                                            </div>
                                        </dd>
                                    </dl>
                                    <dl v-if="viewStore.isAdminViewMode && orderModal.params.order.amount_updates_history">
                                        <div class="overflow-x-auto mx-3 rounded-lg">
                                            <table class="w-full text-xs text-left rtl:text-right text-gray-500 dark:text-gray-400">
                                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                                    <tr>
                                                        <th scope="col" class="px-2 py-1">
                                                            Старая сумма
                                                        </th>
                                                        <th scope="col" class="px-2 py-1">
                                                            Новая сумма
                                                        </th>
                                                        <th scope="col" class="px-2 py-1">
                                                            Дата изменения
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr
                                                        v-for="item in orderModal.params.order.amount_updates_history"
                                                        class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200"
                                                    >
                                                        <th scope="row" class="px-2 py-1 font-normal">
                                                            {{ item.old_amount }} {{ orderModal.params.order.currency.toUpperCase() }}
                                                        </th>
                                                        <td class="px-2 py-1">
                                                            {{ item.new_amount }} {{ orderModal.params.order.currency.toUpperCase() }}
                                                        </td>
                                                        <td class="px-2 py-1">
                                                            {{ item.updated_at }}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </dl>
                                    <dl class="flex items-center justify-between gap-4">
                                        <dt class="text-gray-500 dark:text-gray-400">Сумма в USDT</dt>
                                        <dd class="text-base font-medium text-gray-900 dark:text-gray-300">{{ orderModal.params.order.total_profit }} {{orderModal.params.order.base_currency.toUpperCase()}}</dd>
                                    </dl>
                                    <dl class="flex items-center justify-between gap-4">
                                        <dt class="text-gray-500 dark:text-gray-400">Курс</dt>
                                        <dd class="text-base font-medium text-gray-900 dark:text-gray-300">{{ orderModal.params.order.conversion_price }} {{orderModal.params.order.currency.toUpperCase()}}</dd>
                                    </dl>
                                    <template v-if="viewStore.isAdminViewMode">
                                        <dl class="flex items-center justify-between gap-4">
                                            <dt class="text-gray-500 dark:text-gray-400">Прибыль мерчанта</dt>
                                            <dd class="text-base font-medium text-gray-900 dark:text-gray-300">{{ orderModal.params.order.merchant_profit }} {{orderModal.params.order.base_currency.toUpperCase()}}</dd>
                                        </dl>
                                        <dl class="flex items-center justify-between gap-4">
                                            <dt class="text-gray-500 dark:text-gray-400">Прибыль трейдера</dt>
                                            <dd class="text-base font-medium text-gray-900 dark:text-gray-300">{{ orderModal.params.order.trader_profit }} {{orderModal.params.order.base_currency.toUpperCase()}}</dd>
                                        </dl>
                                        <dl class="flex items-center justify-between gap-4">
                                            <dt class="text-gray-500 dark:text-gray-400">Прибыль сервиса</dt>
                                            <dd class="text-base font-medium text-gray-900 dark:text-gray-300">{{ orderModal.params.order.service_profit }} {{orderModal.params.order.base_currency.toUpperCase()}}</dd>
                                        </dl>
                                    </template>
                                    <template v-else>
                                        <dl class="flex items-center justify-between gap-4">
                                            <dt class="text-gray-500 dark:text-gray-400">Списано со счета</dt>
                                            <dd class="text-base font-medium text-gray-900 dark:text-gray-300">{{ orderModal.params.order.trader_paid_for_order }} {{orderModal.params.order.base_currency.toUpperCase()}}</dd>
                                        </dl>
                                        <dl class="flex items-center justify-between gap-4">
                                            <dt class="text-gray-500 dark:text-gray-400">Прибыль</dt>
                                            <dd class="text-base font-medium text-gray-900 dark:text-gray-300">{{ orderModal.params.order.trader_profit }} {{orderModal.params.order.base_currency.toUpperCase()}}</dd>
                                        </dl>
                                    </template>
                                    <dl v-if="viewStore.isAdminViewMode" class="flex items-center justify-between gap-4">
                                        <dt class="text-gray-500 dark:text-gray-400">Трейдер заплатил</dt>
                                        <dd class="text-base font-medium text-gray-900 dark:text-gray-300">{{ orderModal.params.order.trader_paid_for_order }} {{orderModal.params.order.base_currency.toUpperCase()}}</dd>
                                    </dl>
                                    <dl v-if="viewStore.isAdminViewMode" class="flex items-center justify-between gap-4">
                                        <dt class="text-gray-500 dark:text-gray-400">Комиссия трейдера</dt>
                                        <dd class="text-base font-medium text-gray-900 dark:text-gray-300">{{ orderModal.params.order.trader_commission_rate }} %</dd>
                                    </dl>
                                    <dl v-if="viewStore.isAdminViewMode" class="flex items-center justify-between gap-4">
                                        <dt class="text-gray-500 dark:text-gray-400">Полная комиссия сервиса</dt>
                                        <dd class="text-base font-medium text-gray-900 dark:text-gray-300 flex items-center">
                                            {{ orderModal.params.order.service_commission_rate_total }}%
<!--                                            <span class="text-xs text-gray-500 dark:text-gray-400 flex items-center">
                                                <svg class="ml-2 w-4 h-4 mr-0.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12c.263 0 .524-.06.767-.175a2 2 0 0 0 .65-.491c.186-.21.333-.46.433-.734.1-.274.15-.568.15-.864a2.4 2.4 0 0 0 .586 1.591c.375.422.884.659 1.414.659.53 0 1.04-.237 1.414-.659A2.4 2.4 0 0 0 12 9.736a2.4 2.4 0 0 0 .586 1.591c.375.422.884.659 1.414.659.53 0 1.04-.237 1.414-.659A2.4 2.4 0 0 0 16 9.736c0 .295.052.588.152.861s.248.521.434.73a2 2 0 0 0 .649.488 1.809 1.809 0 0 0 1.53 0 2.03 2.03 0 0 0 .65-.488c.185-.209.332-.457.433-.73.1-.273.152-.566.152-.861 0-.974-1.108-3.85-1.618-5.121A.983.983 0 0 0 17.466 4H6.456a.986.986 0 0 0-.93.645C5.045 5.962 4 8.905 4 9.736c.023.59.241 1.148.611 1.567.37.418.865.667 1.389.697Zm0 0c.328 0 .651-.091.94-.266A2.1 2.1 0 0 0 7.66 11h.681a2.1 2.1 0 0 0 .718.734c.29.175.613.266.942.266.328 0 .651-.091.94-.266.29-.174.537-.427.719-.734h.681a2.1 2.1 0 0 0 .719.734c.289.175.612.266.94.266.329 0 .652-.091.942-.266.29-.174.536-.427.718-.734h.681c.183.307.43.56.719.734.29.174.613.266.941.266a1.819 1.819 0 0 0 1.06-.351M6 12a1.766 1.766 0 0 1-1.163-.476M5 12v7a1 1 0 0 0 1 1h2v-5h3v5h7a1 1 0 0 0 1-1v-7m-5 3v2h2v-2h-2Z"/>
                                                </svg>
                                                {{ orderModal.params.order.service_commission_rate_merchant }}%
                                            </span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400 flex items-center">
                                                <svg class="w-4 h-4 ml-1 mr-0.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0 0a8.949 8.949 0 0 0 4.951-1.488A3.987 3.987 0 0 0 13 16h-2a3.987 3.987 0 0 0-3.951 3.512A8.948 8.948 0 0 0 12 21Zm3-11a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                                </svg>
                                                {{ orderModal.params.order.service_commission_rate_client }}%
                                            </span>-->
                                        </dd>
                                    </dl>
                                    <dl class="flex items-center justify-between gap-4">
                                        <dt class="text-gray-500 dark:text-gray-400">Метод</dt>
                                        <dd class="text-base font-medium text-gray-900 dark:text-gray-300">{{ orderModal.params.order.payment_gateway_name }}
                                            <template v-if="orderModal.params.order.sub_payment_gateway_name">({{orderModal.params.order.sub_payment_gateway_name}})</template>
                                        </dd>                                                </dl>
                                    <dl class="flex items-center justify-between gap-4">
                                        <dt class="text-gray-500 dark:text-gray-400">Реквизиты</dt>
                                        <dd class="text-base font-medium text-gray-900 dark:text-gray-300">
                                            <PaymentDetail :detail="orderModal.params.order.payment_detail" :copyable="false" :type="orderModal.params.order.payment_detail_type"></PaymentDetail>
                                        </dd>
                                    </dl>
                                    <dl v-if="viewStore.isAdminViewMode" class="flex items-center justify-between gap-4">
                                        <dt class="text-gray-500 dark:text-gray-400">Коллбек URL</dt>
                                        <dd class="text-base font-medium text-gray-900 dark:text-gray-300">{{ orderModal.params.order.callback_url }}</dd>
                                    </dl>
                                    <dl v-if="viewStore.isAdminViewMode && ! orderModal.params.order.is_h2h" class="flex items-center justify-between gap-4">
                                        <dt class="text-gray-500 dark:text-gray-400">Страница оплаты</dt>
                                        <dd class="text-base font-medium text-gray-900 dark:text-gray-300">
                                            <button
                                                @click="orderPaymentLink(orderModal.params.order.payment_link)"
                                                type="button"
                                                class="px-0 py-0 justify-items-center text-blue-500 hover:text-blue-600 inline-flex items-center hover:underline"
                                            >
                                                <svg class="w-[22px] h-[22px]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 3v4a1 1 0 0 1-1 1H5m8-2h3m-3 3h3m-4 3v6m4-3H8M19 4v16a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7.914a1 1 0 0 1 .293-.707l3.914-3.914A1 1 0 0 1 9.914 3H18a1 1 0 0 1 1 1ZM8 12v6h8v-6H8Z"/>
                                                </svg>
                                            </button>
                                        </dd>
                                    </dl>
                                    <dl class="flex items-center justify-between gap-4">
                                        <dt class="text-gray-500 dark:text-gray-400">Создан</dt>
                                        <dd class="text-base font-medium text-gray-900 dark:text-gray-300">{{ orderModal.params.order.created_at }}</dd>
                                    </dl>
                                    <dl class="flex items-center justify-between gap-4">
                                        <dt class="text-gray-500 dark:text-gray-400">Истекает</dt>
                                        <dd class="text-base font-medium text-gray-900 dark:text-gray-300">{{ orderModal.params.order.expires_at }}</dd>
                                    </dl>
                                    <dl v-if="orderModal.params.order.finished_at" class="flex items-center justify-between gap-4">
                                        <dt class="text-gray-500 dark:text-gray-400">Завершен</dt>
                                        <dd class="text-base font-medium text-gray-900 dark:text-gray-300">{{ orderModal.params.order.finished_at }}</dd>
                                    </dl>
                                </div>
                                <div v-if="orderModal.params.order.sms_log" class="p-6 bg-white border border-gray-200 rounded-xl  dark:bg-gray-700/50 dark:border-gray-700">
                                    <footer class="flex justify-between items-center mb-2">
                                        <div class="flex items-center">
                                            <p class="inline-flex items-center mr-3 text-sm text-gray-900 dark:text-gray-200 font-semibold">
                                                <svg class="w-4 h-4 text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0 0a8.949 8.949 0 0 0 4.951-1.488A3.987 3.987 0 0 0 13 16h-2a3.987 3.987 0 0 0-3.951 3.512A8.948 8.948 0 0 0 12 21Zm3-11a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                                </svg>
                                                <span class="pl-1">{{ orderModal.params.order.sms_log.sender }}</span>
                                            </p>
                                        </div>
                                        <div>
                                            <p class="flex text-sm text-gray-600 dark:text-gray-200">
                                                <svg class="h-4 w-4 text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 10h16m-8-3V4M7 7V4m10 3V4M5 20h14a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Zm3-7h.01v.01H8V13Zm4 0h.01v.01H12V13Zm4 0h.01v.01H16V13Zm-8 4h.01v.01H8V17Zm4 0h.01v.01H12V17Zm4 0h.01v.01H16V17Z" />
                                                </svg>
                                                <span class="pl-1">{{ orderModal.params.order.sms_log.created_at }}</span>
                                            </p>
                                        </div>
                                    </footer>
                                    <p class="text-gray-500 dark:text-gray-300">
                                        {{ orderModal.params.order.sms_log.message }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </ModalBody>
        <ModalFooter v-if="orderModal.params.order.status === 'pending' || orderModal.params.order.status === 'fail'">
            <div class="flex justify-center">
                <template v-if="! orderModal.params.order.has_dispute">
                    <button
                        @click.prevent="confirmAcceptOrder(orderModal.params.order)"
                        type="button"
                        class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-xl  text-sm px-5 py-2.5 text-center inline-flex items-center me-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                    >
                        <svg class="w-3.5 h-3.5 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 11.917 9.724 16.5 19 7.5"/>
                        </svg>
                        Оплачен
                    </button>
                </template>
                <template v-else>
                    <div>
                        <h2 class="text-gray-900 dark:text-gray-300">По этой сделке был открыт спор</h2>
                        <div class="flex justify-center">
                            <Link
                                @click="modalStore.closeAll()"
                                :href="route(viewStore.adminPrefix + 'disputes.index')"
                                class="inline-flex items-center font-medium text-blue-600 dark:text-blue-500 hover:underline"
                            >
                                Перейти
                                <svg class="w-4 h-4 ms-2 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9"/>
                                </svg>
                            </Link>
                        </div>
                    </div>
                </template>
            </div>
        </ModalFooter>
    </Modal>
</template>

<style scoped>

</style>
