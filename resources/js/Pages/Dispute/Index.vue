<script setup>
import {Head, router, useForm, usePage} from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PaymentDetail from "@/Components/PaymentDetail.vue";
import DisputeStatus from "@/Components/DisputeStatus.vue";
import {useModalStore} from "@/store/modal.js";
import DisputeModal from "@/Modals/DisputeModal.vue";
import CancelDisputeModal from "@/Modals/CancelDisputeModal.vue";
import SmsLogsModal from "@/Modals/SmsLogsModal.vue";
import ConfirmModal from "@/Components/Modals/ConfirmModal.vue";
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import HeadllesTable from "@/Components/HeadlesTable/HeadllesTable.vue";
import HeadlessTableTr from "@/Components/HeadlesTable/HeadlessTableTr.vue";
import HeadlessTableTh from "@/Components/HeadlesTable/HeadlessTableTh.vue";
import HeadlessTableTd from "@/Components/HeadlesTable/HeadlessTableTd.vue";
import DateTime from "@/Components/DateTime.vue";
import {useViewStore} from "@/store/view.js";
import ShowAction from "@/Components/Table/ShowAction.vue";
import OrderStatus from "@/Components/OrderStatus.vue";

const viewStore = useViewStore();
const modalStore = useModalStore();

const disputes = usePage().props.disputes;

const confirmAcceptDispute = (dispute) => {
    modalStore.openConfirmModal({
        title: 'Вы уверены что хотите принять спор #' + dispute?.id + '?',
        body: 'В таком случае, сделка будет закрыта как оплаченная.',
        confirm_button_name: 'Принять спор',
        confirm: () => {
            useForm({}).patch(route('disputes.accept', dispute.id), {
                preserveScroll: true,
                onFinish: () => {
                    modalStore.closeAll()
                    router.visit(route(viewStore.adminPrefix + 'disputes.index'), {
                        only: ['disputes'],
                    })
                },
            });
        }
    });
}

const confirmRollbackDispute = (dispute) => {
    modalStore.openConfirmModal({
        title: 'Вы уверены что хотите открыть спор #' + dispute?.id + '?',
        body: 'Референтная сделка не изменит свой статус.',
        confirm_button_name: 'Открыть спор',
        confirm: () => {
            useForm({}).patch(route('disputes.rollback', dispute.id), {
                preserveScroll: true,
                onFinish: () => {
                    modalStore.closeAll()
                    router.visit(route(viewStore.adminPrefix + 'disputes.index'), {
                        only: ['disputes'],
                    })
                },
            });
        }
    });
};

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Споры" />

        <MainTableSection
            title="Споры по сделкам"
            :data="disputes"
        >
            <template v-slot:body>
                <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">
                                    ID
                                </th>
                                <th scope="col" class="px-6 py-3 text-nowrap">
                                    ID сделки
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Реквизит
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Сумма
                                </th>
                                <th scope="col" class="px-6 py-3" v-if="viewStore.isAdminViewMode">
                                    Трейдер
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Статус
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Создан
                                </th>
                                <th scope="col" class="px-6 py-3 flex justify-center">
                                    <span class="sr-only">Действия</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="dispute in disputes.data" class="bg-white border-b last:border-none dark:bg-gray-800 dark:border-gray-700">
                                <th scope="row" class="px-6 py-3 font-medium whitespace-nowrap text-gray-900 dark:text-gray-200">
                                    {{ dispute.id }}
                                </th>
                                <td class="px-6 py-3">
                                    {{ dispute.order.id }}
                                </td>
                                <td class="px-6 py-3">
                                    <PaymentDetail
                                        :detail="dispute.payment_detail.detail"
                                        :type="dispute.payment_detail.type"
                                        :copyable="false"
                                        class="text-gray-900 dark:text-gray-200"
                                    ></PaymentDetail>
                                    <div class="text-nowrap text-xs">{{ dispute.payment_detail.name }}</div>
                                </td>
                                <td class="px-6 py-3">
                                    <div class="text-nowrap text-gray-900 dark:text-gray-200">{{ dispute.order.amount }} {{dispute.order.currency.toUpperCase()}}</div>
                                    <div class="text-nowrap text-xs">{{ dispute.order.profit }} {{dispute.order.base_currency.toUpperCase()}}</div>
                                </td>
                                <td class="px-6 py-3" v-if="viewStore.isAdminViewMode">
                                    {{ dispute.user.email }}
                                </td>
                                <td class="px-6 py-3">
                                    <DisputeStatus :status="dispute.status"></DisputeStatus>
                                </td>
                                <td class="px-6 py-3">
                                    <DateTime :data="dispute.created_at"></DateTime>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <ShowAction link="#" @click="modalStore.openDisputeModal({dispute})"></ShowAction>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
<!--                <div class="relative overflow-x-auto">
                    <HeadllesTable>
                        <HeadlessTableTr
                            v-for="dispute in disputes.data"
                            @click="modalStore.openDisputeModal({dispute})"
                            :hoverable="true"
                        >
                            <HeadlessTableTh>#{{ dispute.id }}</HeadlessTableTh>
                            <HeadlessTableTd>
                                <PaymentDetail
                                    :detail="dispute.payment_detail.detail"
                                    :type="dispute.payment_detail.type"
                                    :copyable="false"
                                    class="text-gray-900 dark:text-gray-200"
                                ></PaymentDetail>
                                <div class="text-nowrap text-gray-500 dark:text-gray-500">{{ dispute.payment_detail.name }}</div>
                            </HeadlessTableTd>
                            <HeadlessTableTd>
                                <div class="text-nowrap text-gray-900 dark:text-gray-200">{{ dispute.order.amount }} {{dispute.order.currency.toUpperCase()}}</div>
                                <div class="text-nowrap text-gray-500 dark:text-gray-500">{{ dispute.order.profit }} {{dispute.order.base_currency.toUpperCase()}}</div>
                            </HeadlessTableTd>
                            <HeadlessTableTd v-if="viewStore.isAdminViewMode">
                                <div class="text-nowrap text-gray-900 dark:text-gray-200">Трейдер</div>
                                <div class="text-nowrap text-gray-500 dark:text-gray-500">{{ dispute.user.email }}</div>
                            </HeadlessTableTd>
                            <HeadlessTableTd>
                                <DisputeStatus :status="dispute.status"></DisputeStatus>
                            </HeadlessTableTd>
                            <HeadlessTableTd>
                                <DateTime :data="dispute.created_at"></DateTime>
                            </HeadlessTableTd>
                        </HeadlessTableTr>
                    </HeadllesTable>
                </div>-->
            </template>
        </MainTableSection>

        <DisputeModal
            @accept="confirmAcceptDispute"
            @cancel="modalStore.openDisputeCancelModal({dispute:$event})"
            @rollback="confirmRollbackDispute"
        />

        <CancelDisputeModal/>

        <SmsLogsModal/>
        <ConfirmModal/>
    </div>
</template>

<style scoped>

</style>
