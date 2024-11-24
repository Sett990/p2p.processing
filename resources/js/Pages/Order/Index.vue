<script setup>
import {Head, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import OrderStatus from "@/Components/OrderStatus.vue";
import PaymentDetail from "@/Components/PaymentDetail.vue";
import SmsLogsModal from "@/Modals/SmsLogsModal.vue";
import ConfirmModal from "@/Components/Modals/ConfirmModal.vue";
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import OrderModal from "@/Modals/OrderModal.vue";
import {useModalStore} from "@/store/modal.js";
import HeadllesTable from "@/Components/HeadlesTable/HeadllesTable.vue";
import HeadlessTableTr from "@/Components/HeadlesTable/HeadlessTableTr.vue";
import HeadlessTableTh from "@/Components/HeadlesTable/HeadlessTableTh.vue";
import HeadlessTableTd from "@/Components/HeadlesTable/HeadlessTableTd.vue";
import DateTime from "@/Components/DateTime.vue";
import {useViewStore} from "@/store/view.js";
import ShowAction from "@/Components/Table/ShowAction.vue";

const viewStore = useViewStore();
const orders = usePage().props.orders;
const modalStore = useModalStore();

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Сделки" />

        <MainTableSection
            title="Сделки"
            :data="orders"
        >
            <template v-slot:body>
<!--                <div class="relative overflow-x-auto">
                    <HeadllesTable>
                        <HeadlessTableTr
                            v-for="order in orders.data"
                            @click="modalStore.openOrderModal({order})"
                            :hoverable="true"
                        >
                            <HeadlessTableTh>#{{ order.id }}</HeadlessTableTh>
                            <HeadlessTableTd class="px-6 py-4">
                                <div class="text-nowrap text-gray-900 dark:text-gray-200">{{ order.amount }} {{ order.currency.toUpperCase() }}</div>
                                <div class="text-nowrap text-gray-500 dark:text-gray-500">{{ order.profit }} {{ order.base_currency.toUpperCase() }}</div>
                            </HeadlessTableTd>
                            <HeadlessTableTd>
                                <PaymentDetail
                                    :detail="order.payment_detail"
                                    :type="order.payment_detail_type"
                                    :copyable="false"
                                    class="text-gray-900 dark:text-gray-200"
                                ></PaymentDetail>
                                <div class="text-gray-500 dark:text-gray-500">{{ order.payment_detail_name }}</div>
                            </HeadlessTableTd>
                            <HeadlessTableTd v-if="viewStore.isAdminViewMode">
                                <div class="text-nowrap text-gray-900 dark:text-gray-200">Трейдер</div>
                                <div class="text-nowrap text-gray-500 dark:text-gray-500">{{ order.user.email }}</div>
                            </HeadlessTableTd>
                            <HeadlessTableTd>
                                <OrderStatus :status="order.status" :status_name="order.status_name"></OrderStatus>
                            </HeadlessTableTd>
                            <HeadlessTableTd>
                                <DateTime class="justify-center" :data="order.created_at"/>
                            </HeadlessTableTd>
                        </HeadlessTableTr>
                    </HeadllesTable>
                </div>-->

                <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">
                                    ID
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Сумма
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Реквизит
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
                            <tr v-for="order in orders.data" class="bg-white border-b last:border-none dark:bg-gray-800 dark:border-gray-700">
                            <th scope="row" class="px-6 py-3 font-medium whitespace-nowrap text-gray-900 dark:text-gray-200">
                                {{ order.id }}
                            </th>
                            <td class="px-6 py-3">
                                <div class="text-nowrap text-gray-900 dark:text-gray-200">{{ order.amount }} {{ order.currency.toUpperCase() }}</div>
                                <div class="text-nowrap text-xs">{{ order.profit }} {{ order.base_currency.toUpperCase() }}</div>
                            </td>
                            <td class="px-6 py-3">
                                <PaymentDetail
                                    :detail="order.payment_detail"
                                    :type="order.payment_detail_type"
                                    :copyable="false"
                                    class="text-gray-900 dark:text-gray-200"
                                ></PaymentDetail>
                                <div class="text-xs">{{ order.payment_detail_name }}</div>
                            </td>
                            <td class="px-6 py-3" v-if="viewStore.isAdminViewMode">
                                {{ order.user.email }}
                            </td>
                            <td class="px-6 py-3">
                                <OrderStatus :status="order.status" :status_name="order.status_name"></OrderStatus>
                            </td>
                            <td class="px-6 py-3">
                                <DateTime class="justify-center" :data="order.created_at"/>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <ShowAction link="#" @click.prevent="modalStore.openOrderModal({order})"></ShowAction>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </MainTableSection>

        <OrderModal/>
        <SmsLogsModal/>
        <ConfirmModal/>
    </div>
</template>
