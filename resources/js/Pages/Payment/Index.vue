<script setup>
import {Head, router, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import OrderStatus from "@/Components/OrderStatus.vue";
import SmsLogsModal from "@/Modals/SmsLogsModal.vue";
import ConfirmModal from "@/Components/Modals/ConfirmModal.vue";
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import OrderModal from "@/Modals/OrderModal.vue";
import {useModalStore} from "@/store/modal.js";
import DateTime from "@/Components/DateTime.vue";
import {useViewStore} from "@/store/view.js";
import AddMobileIcon from "@/Components/AddMobileIcon.vue";

const viewStore = useViewStore();
const orders = usePage().props.orders;
const modalStore = useModalStore();

const orderPaymentLink = (payment_link) => {
    window.open(payment_link, '_blank')
}

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Платежи" />

        <MainTableSection
            title="Платежи"
            :data="orders"
        >
            <template v-slot:button>
                <button
                    @click="router.visit(route('payments.create'))"
                    type="button"
                    class="hidden md:block text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800"
                >
                    Создать платеж
                </button>
                <AddMobileIcon
                    @click="router.visit(route('payments.create'))"
                />
            </template>
            <template v-slot:body>
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
                                Прибыль
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Комиссия
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Статус
                            </th>
                            <th scope="col" class="px-6 py-3 text-center">
                                Создан
                            </th>
                            <th scope="col" class="px-6 py-3 flex justify-center">
                                <span class="sr-only">Действия</span>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="order in orders.data" class="bg-white border-b last:border-none dark:bg-gray-800 dark:border-gray-700">
                            <th scope="row" class="px-6 py-3 font-medium whitespace-nowrap text-gray-900 dark:text-gray-200">#{{ order.id }}</th>
                            <td class="px-6 py-3">
                                <div class="text-nowrap text-gray-900 dark:text-gray-200">{{ order.amount }} {{ order.currency.toUpperCase() }}</div>
                                <div class="text-nowrap text-xs">{{ order.profit }} {{ order.base_currency.toUpperCase() }}</div>
                            </td>
                            <td class="px-6 py-3">
                                <div class="text-nowrap">{{ order.merchant_profit }} {{ order.base_currency.toUpperCase() }}</div>
                            </td>
                            <td class="px-6 py-3">
                                {{ order.service_commission_amount_total }} {{ order.base_currency.toUpperCase() }}
                            </td>
                            <td class="px-6 py-3">
                                <OrderStatus :status="order.status" :status_name="order.status_name"></OrderStatus>
                            </td>
                            <td class="px-6 py-3">
                                <DateTime class="justify-center" :data="order.created_at"/>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <button
                                    v-if="! order.is_h2h"
                                    @click="orderPaymentLink(order.payment_link)"
                                    type="button"
                                    class="px-0 py-0 justify-items-center text-blue-500 hover:text-blue-600 inline-flex items-center hover:underline"
                                >
                                    <svg class="w-[22px] h-[22px]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 3v4a1 1 0 0 1-1 1H5m8-2h3m-3 3h3m-4 3v6m4-3H8M19 4v16a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7.914a1 1 0 0 1 .293-.707l3.914-3.914A1 1 0 0 1 9.914 3H18a1 1 0 0 1 1 1ZM8 12v6h8v-6H8Z"/>
                                    </svg>
                                </button>
                                <div v-else>
                                    <button
                                        @click.prevent="false"
                                        type="button"
                                        class="ml-1.5 px-2 py-1 text-xs font-medium text-center inline-flex items-center text-gray-900 bg-white border border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100 rounded-lg dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700"
                                        :data-tooltip-target="'tooltip-h2h-'+order.id"
                                    >
                                        H2H
                                    </button>
                                    <div :id="'tooltip-h2h-'+order.id" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                                        Для сделок созданных через H2H API невозможно открыть платежную ссылку. Так как управление платежом передается на внешний сервис.
                                    </div>
                                </div>
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
