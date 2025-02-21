<script setup>
import OrderStatus from "@/Components/OrderStatus.vue";
import ModalFooter from "@/Components/Modals/Components/ModalFooter.vue";
import ModalBody from "@/Components/Modals/Components/ModalBody.vue";
import PaymentDetail from "@/Components/PaymentDetail.vue";
import Modal from "@/Components/Modals/Modal.vue";
import ModalHeader from "@/Components/Modals/Components/ModalHeader.vue";
import { storeToRefs } from 'pinia'
import { useModalStore } from "@/store/modal.js";
import {useViewStore} from "@/store/view.js";
import DisplayUUID from "@/Components/DisplayUUID.vue";

const viewStore = useViewStore();
const modalStore = useModalStore();
const { disputeModal } = storeToRefs(modalStore);

const emit = defineEmits(['accept', 'cancel', 'rollback']);

const close = () => {
    modalStore.closeModal('dispute')
};

const accept = (dispute) => {
    emit('accept', dispute);
};

const cancel = (dispute) => {
    emit('cancel', dispute);
};

const rollback = (dispute) => {
    emit('rollback', dispute);
};

const showReceipt = () => {
    window.open(disputeModal.value.params.dispute.receipt_url, '_blank').focus();
};

const showUserSmsLogs = (dispute) => {
    modalStore.openUserSmsLogsModal({user: dispute.user});
};
</script>

<template>
    <Modal :show="disputeModal.showed" @close="close" maxWidth="md">
        <ModalHeader
            :title="'Спор #' + disputeModal.params.dispute.id"
            @close="close"
        />
        <ModalBody>
            <form action="#" class="mx-auto max-w-screen-xl px-2 2xl:px-0">
                <div class="mx-auto max-w-3xl">
                    <div>
                        <div>
                            <div v-if="disputeModal.params.dispute.status === 'accepted'">
                                <div class="flex items-center justify-center mb-2">
                                    <svg class="w-16 h-16 text-green-400 dark:text-green-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.5 11.5 11 14l4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                    </svg>
                                </div>
                                <p class="mb-1 text-lg font-semibold text-gray-900 dark:text-gray-300 text-center">Спор принят</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-400 text-center">{{ disputeModal.params.dispute.created_at }}</p>
                            </div>
                            <div v-else-if="disputeModal.params.dispute.status === 'canceled'">
                                <div class="flex items-center justify-center mb-2">
                                    <svg class="w-16 h-16 text-red-500 dark:text-red-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 9-6 6m0-6 6 6m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                    </svg>
                                </div>
                                <p class="mb-1 text-lg font-semibold text-gray-900 dark:text-gray-300 text-center">Спор отклонен</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-400 text-center">{{ disputeModal.params.dispute.created_at }}</p>
                            </div>
                            <div v-else-if="disputeModal.params.dispute.status === 'pending'">
                                <div class="flex items-center justify-center mb-2">
                                    <svg class="w-16 h-16 text-yellow-300 dark:text-yellow-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                    </svg>
                                </div>
                                <p class="mb-1 text-lg font-semibold text-gray-900 dark:text-gray-300 text-center">Спор ожидает проверки</p>
                                <p class="text-sm font-semibold text-gray-500 dark:text-gray-400 text-center">{{ disputeModal.params.dispute.created_at }}</p>
                            </div>
                            <div class="flex justify-end py-3 space-x-1.5">
                                <button
                                    @click.prevent="showUserSmsLogs(disputeModal.params.dispute)"
                                    type="button"
                                    class="p-1 text-xs font-medium text-center inline-flex items-center text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 rounded-xl  dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700"
                                >
                                    <svg class="w-4 h-4 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.556 8.5h8m-8 3.5H12m7.111-7H4.89a.896.896 0 0 0-.629.256.868.868 0 0 0-.26.619v9.25c0 .232.094.455.26.619A.896.896 0 0 0 4.89 16H9l3 4 3-4h4.111a.896.896 0 0 0 .629-.256.868.868 0 0 0 .26-.619v-9.25a.868.868 0 0 0-.26-.619.896.896 0 0 0-.63-.256Z"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="space-y-3">
                                <div class="py-2 px-4 bg-white border border-gray-200 dark:bg-gray-700/50 dark:border-gray-700 rounded-plate dark:shadow">
                                    <div class="flex justify-between items-center">
                                        <div class="items-center">
                                            <div class="mr-3 text-sm text-nowrap text-gray-900 dark:text-gray-300">
                                                Сумма спора
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                                <div class="text-nowrap text-gray-900 dark:text-gray-300">{{ disputeModal.params.dispute.order.amount }} {{disputeModal.params.dispute.order.currency.toUpperCase()}}</div>
                                                <div class="text-nowrap text-gray-500 dark:text-gray-500">{{ disputeModal.params.dispute.order.profit }} {{disputeModal.params.dispute.order.base_currency.toUpperCase()}}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="py-2 px-4 bg-white border border-gray-200 dark:bg-gray-700/50 dark:border-gray-700 rounded-plate dark:shadow">
                                    <div class="flex justify-between items-center">
                                        <div class="items-center">
                                            <div class="mr-3 text-sm text-nowrap text-gray-900 dark:text-gray-300">
                                                Сделка
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                                <div class="text-nowrap text-gray-900 dark:text-gray-300">UUID</div>
                                                <div class="text-nowrap text-gray-500 dark:text-gray-500">
                                                    <DisplayUUID :uuid="disputeModal.params.dispute.order.uuid" :copyable="false"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="py-2 px-4 bg-white border border-gray-200 dark:bg-gray-700/50 dark:border-gray-700 rounded-plate  dark:shadow">
                                    <div class="flex justify-between items-center">
                                        <div class="items-center">
                                            <div class="mr-3 text-sm text-nowrap text-gray-900 dark:text-gray-300">
                                                Реквизит #{{ disputeModal.params.dispute.payment_detail.id }}
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-sm dark:text-gray-400">
                                                <PaymentDetail
                                                    :detail="disputeModal.params.dispute.payment_detail.detail"
                                                    :type="disputeModal.params.dispute.payment_detail.type"
                                                    :copyable="false"
                                                    class="text-gray-900 dark:text-gray-300"
                                                ></PaymentDetail>
                                                <div class="text-nowrap text-gray-500 dark:text-gray-500">{{ disputeModal.params.dispute.payment_detail.name }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="viewStore.isAdminViewMode" class="py-2 px-4 bg-white border border-gray-200 dark:bg-gray-700/50 dark:border-gray-700 rounded-plate  dark:shadow">
                                    <div class="flex justify-between items-center">
                                        <div class="items-center">
                                            <div class="mr-3 text-sm text-nowrap text-gray-900 dark:text-gray-300">
                                                Трейдер #{{ disputeModal.params.dispute.user.id }}
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                                <div class="text-nowrap text-gray-900 dark:text-gray-300">{{ disputeModal.params.dispute.user.name }}</div>
                                                <div class="text-nowrap text-gray-500 dark:text-gray-500">{{ disputeModal.params.dispute.user.email }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="py-2 px-4 bg-white border border-gray-200 dark:bg-gray-700/50 dark:border-gray-700 rounded-plate dark:shadow">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <div class="mr-3 text-sm text-nowrap text-gray-900 dark:text-gray-300">
                                                Квитанция
                                            </div>
                                        </div>
                                        <div>
                                            <button
                                                @click.prevent="showReceipt"
                                                type="button"
                                                class="px-3 py-2 text-xs font-medium text-center inline-flex items-center text-gray-900 bg-gray-50 border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 rounded-xl  dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700"
                                            >
                                                Посмотреть
                                                <svg class="w-3 h-3 text-gray-900 dark:text-white ml-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m14 0-4 4m4-4-4-4"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="disputeModal.params.dispute.status === 'canceled'" class="p-3 bg-white border border-gray-200 dark:bg-gray-700/50 dark:border-gray-700 rounded-plate dark:shadow">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="mr-3 text-sm text-nowrap text-gray-900 dark:text-gray-300">
                                                Причина отклонения спора
                                            </div>
                                            <div class="mr-3 text-sm text-gray-500 dark:text-gray-400">
                                                {{ disputeModal.params.dispute.reason }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </ModalBody>
        <ModalFooter v-show="viewStore.isAdminViewMode || disputeModal.params.dispute.status === 'pending' || disputeModal.params.dispute.status === 'canceled'">
            <div class="flex justify-center">
                <template v-if="disputeModal.params.dispute.status === 'pending'">
                    <button
                        @click.prevent="cancel(disputeModal.params.dispute)"
                        type="button"
                        class="mr-2 inline-flex items-center text-red-700 hover:text-white border border-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-xl  text-sm px-5 py-2.5 text-center dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900"
                    >
                        <svg class="w-3.5 h-3.5 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/>
                        </svg>
                        Отклонить
                    </button>
                    <button
                        @click.prevent="accept(disputeModal.params.dispute)"
                        type="button"
                        class="inline-flex items-center text-blue-700 hover:text-white border border-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-xl  text-sm px-5 py-2.5 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-500 dark:focus:ring-blue-800"
                    >
                        <svg class="w-3.5 h-3.5 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 11.917 9.724 16.5 19 7.5"/>
                        </svg>
                        Принять
                    </button>
                </template>
                <template v-if="disputeModal.params.dispute.status !== 'pending'">
                    <button
                        @click.prevent="rollback(disputeModal.params.dispute)"
                        type="button"
                        class="inline-flex items-center text-yellow-400 hover:text-white border border-yellow-400 hover:bg-yellow-500 focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium rounded-xl  text-sm px-5 py-2.5 text-center dark:border-yellow-300 dark:text-yellow-300 dark:hover:text-white dark:hover:bg-yellow-400 dark:focus:ring-yellow-900"
                    >
                        <svg class="w-3.5 h-3.5 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16 10 3-3m0 0-3-3m3 3H5v3m3 4-3 3m0 0 3 3m-3-3h14v-3"/>
                        </svg>

                        Открыть спор
                    </button>
                </template>
            </div>
        </ModalFooter>
    </Modal>
</template>

<style scoped>

</style>
