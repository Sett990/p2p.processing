<script setup>
import Modal from "@/Components/Modals/Modal.vue";
import PaymentDetail from "@/Components/PaymentDetail.vue";
import {router, useForm, usePage} from "@inertiajs/vue3";
import ModalHeader from "@/Components/Modals/Components/ModalHeader.vue";
import ModalBody from "@/Components/Modals/Components/ModalBody.vue";
import {useModalStore} from "@/store/modal.js";
import {storeToRefs} from "pinia";
import {useViewStore} from "@/store/view.js";

const viewStore = useViewStore();
const modalStore = useModalStore();
const { payoutModal } = storeToRefs(modalStore);
const user = usePage().props.auth.user;

const closeModal = () => {
    modalStore.closeModal('payout');
};
</script>

<template>
    <Modal :show="!! payoutModal.showed" @close="closeModal" maxWidth="md">
        <ModalHeader
            :title="'Выплата #' + payoutModal.params.payout.id"
            @close="closeModal"
        />
        <ModalBody>
            <form action="#" class="mx-auto max-w-screen-xl px-2 2xl:px-0">
                <div class="mx-auto max-w-3xl">
                    <div>
                        <div>
                            <div class="mb-5">
                                <div v-if="payoutModal.params.payout.status === 'success'">
                                    <div class="flex items-center justify-center mb-2">
                                        <svg class="w-24 h-24 text-green-400 dark:text-green-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.5 11.5 11 14l4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                        </svg>
                                    </div>
                                    <p class="mb-1 text-lg font-semibold text-gray-900 dark:text-gray-300 text-center">Выплата завершена</p>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-400 text-center">{{ payoutModal.params.payout.finished_at }}</p>
                                </div>
                                <div v-else-if="payoutModal.params.payout.status === 'fail'">
                                    <div class="flex items-center justify-center mb-2">
                                        <svg class="w-24 h-24 text-red-500 dark:text-red-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 9-6 6m0-6 6 6m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                        </svg>
                                    </div>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-300 text-center">Выплата отменена</p>
                                </div>
                                <div v-else-if="payoutModal.params.payout.status === 'pending'">
                                    <div class="flex items-center justify-center mb-2">
                                        <svg class="w-24 h-24 text-yellow-300 dark:text-yellow-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                        </svg>
                                    </div>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-300 text-center">Выплата еще не произведена</p>
                                </div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-400 text-center">{{ payoutModal.params.payout.uuid }}</p>
                            </div>
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <dl class="flex items-center justify-between gap-4">
                                        <dt class="text-gray-500 dark:text-gray-400">ID</dt>
                                        <dd class="text-base font-medium text-gray-900 dark:text-gray-300">{{ payoutModal.params.payout.id }}</dd>
                                    </dl>
                                    <dl class="flex items-center justify-between gap-4">
                                        <dt class="text-gray-500 dark:text-gray-400">Внешний ID</dt>
                                        <dd class="text-base font-medium text-gray-900 dark:text-gray-300">{{ payoutModal.params.payout.external_id }}</dd>
                                    </dl>
                                    <dl class="flex items-center justify-between gap-4">
                                        <dt class="text-gray-500 dark:text-gray-400">Полная сумма выплаты</dt>
                                        <dd class="text-base font-medium text-gray-900 dark:text-gray-300">
                                            {{ payoutModal.params.payout.liquidity_amount }} {{ payoutModal.params.payout.liquidity_currency.toUpperCase() }}
                                        </dd>
                                    </dl>
                                    <dl class="flex items-center justify-between gap-4">
                                        <dt class="text-gray-500 dark:text-gray-400">Выплата клиенту</dt>
                                        <dd class="text-base font-medium text-gray-900 dark:text-gray-300">
                                            {{ payoutModal.params.payout.payout_amount }} {{ payoutModal.params.payout.currency.toUpperCase() }}
                                        </dd>
                                    </dl>
                                    <dl class="flex items-center justify-between gap-4">
                                        <dt class="text-gray-500 dark:text-gray-400">Курс обмена</dt>
                                        <dd class="text-base font-medium text-gray-900 dark:text-gray-300">
                                            <div>{{ payoutModal.params.payout.exchange_price }} {{ payoutModal.params.payout.currency.toUpperCase() }}</div>
                                        </dd>
                                    </dl>
                                    <dl class="flex items-center justify-between gap-4">
                                        <dt class="text-gray-500 dark:text-gray-400">Оплата трейдеру</dt>
                                        <dd class="text-base font-medium text-gray-900 dark:text-gray-300">
                                            <div>{{ payoutModal.params.payout.trader_profit_amount }} {{ payoutModal.params.payout.liquidity_currency.toUpperCase() }}</div>
                                        </dd>
                                    </dl>
                                    <dl class="flex items-center justify-between gap-4">
                                        <dt class="text-gray-500 dark:text-gray-400">Комиссия сервиса</dt>
                                        <dd class="text-base font-medium text-gray-900 dark:text-gray-300">
                                            {{ payoutModal.params.payout.service_commission_amount }} {{ payoutModal.params.payout.liquidity_currency.toUpperCase() }}
                                        </dd>
                                    </dl>
                                    <dl class="flex items-center justify-between gap-4">
                                        <dt class="text-gray-500 dark:text-gray-400">Комиссия сервиса в %</dt>
                                        <dd class="text-base font-medium text-gray-900 dark:text-gray-300">
                                            {{ payoutModal.params.payout.service_commission_rate }} %
                                        </dd>
                                    </dl>
                                    <dl class="flex items-center justify-between gap-4">
                                        <dt class="text-gray-500 dark:text-gray-400">Реквизит</dt>
                                        <dd class="text-base font-medium text-gray-900 dark:text-gray-300">
                                            <PaymentDetail :detail="payoutModal.params.payout.detail" :copyable="false" :type="payoutModal.params.payout.detail_type"></PaymentDetail>
                                        </dd>
                                    </dl>
                                    <dl class="flex items-center justify-between gap-4">
                                        <dt class="text-gray-500 dark:text-gray-400">Держатель реквизитов</dt>
                                        <dd class="text-base font-medium text-gray-900 dark:text-gray-300">
                                            {{ payoutModal.params.payout.detail_initials }}
                                        </dd>
                                    </dl>
                                    <dl class="flex items-center justify-between gap-4">
                                        <dt class="text-gray-500 dark:text-gray-400">Платежный метод</dt>
                                        <dd class="text-base font-medium text-gray-900 dark:text-gray-300">
                                            {{ payoutModal.params.payout.payment_gateway_name }}
                                        </dd>
                                    </dl>
                                    <dl class="flex items-center justify-between gap-4">
                                        <dt class="text-gray-500 dark:text-gray-400">Создан</dt>
                                        <dd class="text-base font-medium text-gray-900 dark:text-gray-300">{{ payoutModal.params.payout.created_at }}</dd>
                                    </dl>
                                    <dl class="flex items-center justify-between gap-4">
                                        <dt class="text-gray-500 dark:text-gray-400">Истекает</dt>
                                        <dd class="text-base font-medium text-gray-900 dark:text-gray-300">{{ payoutModal.params.payout.expires_at }}</dd>
                                    </dl>
                                    <dl v-if="payoutModal.params.payout.finished_at" class="flex items-center justify-between gap-4">
                                        <dt class="text-gray-500 dark:text-gray-400">Завершен</dt>
                                        <dd class="text-base font-medium text-gray-900 dark:text-gray-300">{{ payoutModal.params.payout.finished_at }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </ModalBody>
    </Modal>
</template>

<style scoped>

</style>
