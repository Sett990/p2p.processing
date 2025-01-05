<script setup>
import Modal from "@/Components/Modals/Modal.vue";
import PaymentDetail from "@/Components/PaymentDetail.vue";
import {usePage} from "@inertiajs/vue3";
import ModalHeader from "@/Components/Modals/Components/ModalHeader.vue";
import ModalBody from "@/Components/Modals/Components/ModalBody.vue";
import {useModalStore} from "@/store/modal.js";
import {storeToRefs} from "pinia";
import {useViewStore} from "@/store/view.js";
import {computed, nextTick, onMounted, ref, watch} from "vue";

const viewStore = useViewStore();
const modalStore = useModalStore();
const { payoutModal } = storeToRefs(modalStore);
const user = usePage().props.auth.user;

const closeModal = () => {
    modalStore.closeModal('payout');
};

const payout = computed(() => {
    return payoutModal.value.params.payout;
})

const showReceipt = () => {
    window.open(payoutModal.value.params.payout.receipt_url, '_blank').focus();
};
</script>

<template>
    <Modal :show="!! payoutModal.showed" @close="closeModal" maxWidth="md">
        <ModalHeader
            :title="'Выплата #' + payout.id"
            @close="closeModal"
        />
        <ModalBody>
            <form v-if="payoutModal.showed" action="#" class="mx-auto max-w-screen-xl px-2 2xl:px-0">
                <div class="mx-auto max-w-3xl">
                    <div>
                        <div class="space-y-5">
                            <div>
                                <div v-if="payout.status === 'success'">
                                    <div class="flex items-center justify-center mb-2">
                                        <svg class="w-24 h-24 text-green-400 dark:text-green-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.5 11.5 11 14l4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                        </svg>
                                    </div>
                                    <p class="mb-1 text-lg font-semibold text-gray-900 dark:text-gray-300 text-center">Выплата завершена</p>
                                    <p class="text-sm font-semibold text-gray-500 dark:text-gray-400 text-center">{{ payout.finished_at }}</p>
                                </div>
                                <div v-else-if="payout.status === 'fail'">
                                    <div class="flex items-center justify-center mb-2">
                                        <svg class="w-24 h-24 text-red-500 dark:text-red-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 9-6 6m0-6 6 6m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                        </svg>
                                    </div>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-300 text-center">Выплата отменена</p>
                                    <p class="text-sm font-semibold text-gray-500 dark:text-gray-400 text-center">{{ payout.finished_at }}</p>
                                </div>
                                <div v-else-if="payout.status === 'pending'">
                                    <div class="flex items-center justify-center mb-2">
                                        <svg class="w-24 h-24 text-yellow-300 dark:text-yellow-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                        </svg>
                                    </div>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-300 text-center">Выплата еще не произведена</p>
                                </div>
                            </div>
                            <div v-if="payout.funds_on_hold?.is_on_hold && (viewStore.isTraderViewMode || viewStore.isAdminViewMode)" class="flex justify-center items-center gap-2 border-0 border-b border-t border-dashed p-2">
                                <div>
                                    <svg class="w-9 h-9 text-yellow-300 dark:text-yellow-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                    </svg>
                                </div>
                                <div class="text-center">
                                    <div class="text-sm text-gray-900 dark:text-gray-300 font-semibold">
                                        {{ payout.funds_on_hold.hold_until }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        Средства на удержании
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <dl class="flex items-center justify-between gap-4">
                                        <dt class="text-gray-500 dark:text-gray-400">UUID</dt>
                                        <dd class="text-base font-medium text-gray-900 dark:text-gray-300">{{ payout.uuid }}</dd>
                                    </dl>
                                    <template v-if="viewStore.isMerchantViewMode">
                                        <dl class="flex items-center justify-between gap-4">
                                            <dt class="text-gray-500 dark:text-gray-400">Внешний ID</dt>
                                            <dd class="text-base font-medium text-gray-900 dark:text-gray-300">{{ payout.external_id }}</dd>
                                        </dl>
                                        <dl class="flex items-center justify-between gap-4">
                                            <dt class="text-gray-500 dark:text-gray-400">Выплата клиенту</dt>
                                            <dd class="text-base font-medium text-gray-900 dark:text-gray-300">
                                                {{ payout.payout_amount }} {{ payout.currency.toUpperCase() }}
                                            </dd>
                                        </dl>
                                        <dl class="flex items-center justify-between gap-4">
                                            <dt class="text-gray-500 dark:text-gray-400">Списание со счета</dt>
                                            <dd class="text-base font-medium text-gray-900 dark:text-gray-300">
                                                {{ payout.liquidity_amount }} {{ payout.liquidity_currency.toUpperCase() }}
                                            </dd>
                                        </dl>
                                        <dl class="flex items-center justify-between gap-4">
                                            <dt class="text-gray-500 dark:text-gray-400">Курс обмена</dt>
                                            <dd class="text-base font-medium text-gray-900 dark:text-gray-300">
                                                <div>{{ payout.exchange_price }} {{ payout.currency.toUpperCase() }}</div>
                                            </dd>
                                        </dl>
                                        <dl class="flex items-center justify-between gap-4">
                                            <dt class="text-gray-500 dark:text-gray-400">Комиссия сервиса</dt>
                                            <dd class="text-base font-medium text-gray-900 dark:text-gray-300">
                                                {{ payout.service_commission_amount }} {{ payout.liquidity_currency.toUpperCase() }} ({{ payout.service_commission_rate }}%)
                                            </dd>
                                        </dl>
                                        <dl class="flex items-center justify-between gap-4">
                                            <dt class="text-gray-500 dark:text-gray-400">Реквизит</dt>
                                            <dd class="text-base font-medium text-gray-900 dark:text-gray-300">
                                                <PaymentDetail :detail="payout.detail" :copyable="false" :type="payout.detail_type.code"></PaymentDetail>
                                            </dd>
                                        </dl>
                                        <dl class="flex items-center justify-between gap-4">
                                            <dt class="text-gray-500 dark:text-gray-400">Держатель реквизита</dt>
                                            <dd class="text-base font-medium text-gray-900 dark:text-gray-300">
                                                {{ payout.detail_initials }}
                                            </dd>
                                        </dl>
                                        <dl class="flex items-center justify-between gap-4">
                                            <dt class="text-gray-500 dark:text-gray-400">Платежный метод</dt>
                                            <dd class="text-base font-medium text-gray-900 dark:text-gray-300">
                                                {{ payout.payment_gateway.name }} <span v-if="payout.sub_payment_gateway">({{ payout.sub_payment_gateway.name }})</span>
                                            </dd>
                                        </dl>
                                    </template>
                                    <template v-if="viewStore.isTraderViewMode">
                                        <dl class="flex items-center justify-between gap-4">
                                            <dt class="text-gray-500 dark:text-gray-400">Сумма выплаты</dt>
                                            <dd class="text-base font-medium text-gray-900 dark:text-gray-300">
                                                {{ payout.payout_amount }} {{ payout.currency.toUpperCase() }}
                                            </dd>
                                        </dl>
                                        <dl class="flex items-center justify-between gap-4">
                                            <dt class="text-gray-500 dark:text-gray-400">Реквизит</dt>
                                            <dd class="text-base font-medium text-gray-900 dark:text-gray-300">
                                                <PaymentDetail :detail="payout.detail" :copyable="false" :type="payout.detail_type.code"></PaymentDetail>
                                            </dd>
                                        </dl>
                                        <dl class="flex items-center justify-between gap-4">
                                            <dt class="text-gray-500 dark:text-gray-400">Держатель реквизита</dt>
                                            <dd class="text-base font-medium text-gray-900 dark:text-gray-300">
                                                {{ payout.detail_initials }}
                                            </dd>
                                        </dl>
                                        <dl class="flex items-center justify-between gap-4">
                                            <dt class="text-gray-500 dark:text-gray-400">Платежный метод</dt>
                                            <dd class="text-base font-medium text-gray-900 dark:text-gray-300">
                                                {{ payout.payment_gateway.name }} <span v-if="payout.sub_payment_gateway">({{ payout.sub_payment_gateway.name }})</span>
                                            </dd>
                                        </dl>
                                        <dl class="flex items-center justify-between gap-4">
                                            <dt class="text-gray-500 dark:text-gray-400">Зачисление на баланс</dt>
                                            <dd class="text-base font-medium text-gray-900 dark:text-gray-300">
                                                {{ payout.trader_profit_amount }} {{ payout.liquidity_currency.toUpperCase() }}
                                            </dd>
                                        </dl>
                                        <dl class="flex items-center justify-between gap-4">
                                            <dt class="text-gray-500 dark:text-gray-400">Курс обмена</dt>
                                            <dd class="text-base font-medium text-gray-900 dark:text-gray-300">
                                                <div>{{ payout.exchange_price }} {{ payout.currency.toUpperCase() }}</div>
                                            </dd>
                                        </dl>
                                    </template>
                                    <template v-if="viewStore.isAdminViewMode">
                                        <dl class="flex items-center justify-between gap-4">
                                            <dt class="text-gray-500 dark:text-gray-400">Сумма выплаты</dt>
                                            <dd class="text-base font-medium text-gray-900 dark:text-gray-300">
                                                {{ payout.payout_amount }} {{ payout.currency.toUpperCase() }}
                                            </dd>
                                        </dl>
                                        <dl class="flex items-center justify-between gap-4">
                                            <dt class="text-gray-500 dark:text-gray-400">Реквизит</dt>
                                            <dd class="text-base font-medium text-gray-900 dark:text-gray-300">
                                                <PaymentDetail :detail="payout.detail" :copyable="false" :type="payout.detail_type.code"></PaymentDetail>
                                            </dd>
                                        </dl>
                                        <dl class="flex items-center justify-between gap-4">
                                            <dt class="text-gray-500 dark:text-gray-400">Держатель реквизита</dt>
                                            <dd class="text-base font-medium text-gray-900 dark:text-gray-300">
                                                {{ payout.detail_initials }}
                                            </dd>
                                        </dl>
                                        <dl class="flex items-center justify-between gap-4">
                                            <dt class="text-gray-500 dark:text-gray-400">Платежный метод</dt>
                                            <dd class="text-base font-medium text-gray-900 dark:text-gray-300">
                                                {{ payout.payment_gateway.name }} <span v-if="payout.sub_payment_gateway">({{ payout.sub_payment_gateway.name }})</span>
                                            </dd>
                                        </dl>
                                        <dl class="flex items-center justify-between gap-4">
                                            <dt class="text-gray-500 dark:text-gray-400">Списание у мерчанта</dt>
                                            <dd class="text-base font-medium text-gray-900 dark:text-gray-300">
                                                {{ payout.liquidity_amount }} {{ payout.liquidity_currency.toUpperCase() }}
                                            </dd>
                                        </dl>
                                        <dl class="flex items-center justify-between gap-4">
                                            <dt class="text-gray-500 dark:text-gray-400">Зачисление трейдеру</dt>
                                            <dd class="text-base font-medium text-gray-900 dark:text-gray-300">
                                                {{ payout.trader_profit_amount }} {{ payout.liquidity_currency.toUpperCase() }}
                                            </dd>
                                        </dl>
                                        <dl class="flex items-center justify-between gap-4">
                                            <dt class="text-gray-500 dark:text-gray-400">Курс обмена</dt>
                                            <dd class="text-base font-medium text-gray-900 dark:text-gray-300">
                                                <div>{{ payout.exchange_price }} {{ payout.currency.toUpperCase() }}</div>
                                            </dd>
                                        </dl>
                                        <dl class="flex items-center justify-between gap-4">
                                            <dt class="text-gray-500 dark:text-gray-400">Комиссия сервиса</dt>
                                            <dd class="text-base font-medium text-gray-900 dark:text-gray-300 flex items-center gap-2">
                                                <div>{{ payout.service_commission_rate }}%</div>
                                                <div class="border-r border-gray-900 h-3"></div>
                                                <div>{{ payout.service_commission_amount }} {{ payout.liquidity_currency.toUpperCase() }}</div>
                                            </dd>
                                        </dl>
                                        <dl class="flex items-center justify-between gap-4">
                                            <dt class="text-gray-500 dark:text-gray-400">Наценка трейдера</dt>
                                            <dd class="text-base font-medium text-gray-900 dark:text-gray-300 flex items-center gap-2">
                                                <div>{{ payout.trader_exchange_markup_rate }}%</div>
                                                <div class="border-r border-gray-900 h-3"></div>
                                                <div>{{ payout.trader_exchange_markup_amount }} {{ payout.liquidity_currency.toUpperCase() }}</div>
                                            </dd>
                                        </dl>
                                        <dl class="flex items-center justify-between gap-4">
                                            <dt class="text-gray-500 dark:text-gray-400">Трейдер</dt>
                                            <dd class="text-base font-medium text-gray-900 dark:text-gray-300">
                                                <div>{{ payout.trader.email }}</div>
                                            </dd>
                                        </dl>
                                        <dl class="flex items-center justify-between gap-4">
                                            <dt class="text-gray-500 dark:text-gray-400">Владелец</dt>
                                            <dd class="text-base font-medium text-gray-900 dark:text-gray-300">
                                                <div>{{ payout.owner.email }}</div>
                                            </dd>
                                        </dl>
                                        <dl class="flex items-center justify-between gap-4">
                                            <dt class="text-gray-500 dark:text-gray-400">Направление</dt>
                                            <dd class="text-base font-medium text-gray-900 dark:text-gray-300">
                                                <div>{{ payout.payout_gateway.name }}</div>
                                            </dd>
                                        </dl>
                                        <dl class="flex items-center justify-between gap-4">
                                            <dt class="text-gray-500 dark:text-gray-400">Callback URL</dt>
                                            <dd class="text-base font-medium text-gray-900 dark:text-gray-300">
                                                <div class="w-48 break-all">{{ payout.callback_url }}</div>
                                            </dd>
                                        </dl>
                                        <dl class="flex items-center justify-between gap-4">
                                            <dt class="text-gray-500 dark:text-gray-400">Квитанция</dt>
                                            <dd class="text-base font-medium text-gray-900 dark:text-gray-300">
                                                <button
                                                    @click.prevent="showReceipt"
                                                    type="button"
                                                    class="text-sm text-center inline-flex items-center text-blue-500 hover:text-blue-600"
                                                >
                                                    Посмотреть
                                                    <svg class="w-4 h-4 ml-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m14 0-4 4m4-4-4-4"/>
                                                    </svg>
                                                </button>
                                            </dd>
                                        </dl>
                                    </template>
                                    <dl class="flex items-center justify-between gap-4">
                                        <dt class="text-gray-500 dark:text-gray-400">Создан</dt>
                                        <dd class="text-base font-medium text-gray-900 dark:text-gray-300">{{ payout.created_at }}</dd>
                                    </dl>
                                    <template v-if="viewStore.isAdminViewMode">
                                        <dl class="flex items-center justify-between gap-4">
                                            <dt class="text-gray-500 dark:text-gray-400">Истекает</dt>
                                            <dd class="text-base font-medium text-gray-900 dark:text-gray-300">{{ payout.expires_at }}</dd>
                                        </dl>
                                        <dl class="flex items-center justify-between gap-4">
                                            <dt class="text-gray-500 dark:text-gray-400">Завершен</dt>
                                            <dd class="text-base font-medium text-gray-900 dark:text-gray-300">{{ payout.finished_at }}</dd>
                                        </dl>
                                    </template>
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
