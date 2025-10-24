<script setup>
import {Head, router, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import OrderStatus from "@/Components/OrderStatus.vue";
import ConfirmModal from "@/Components/Modals/ConfirmModal.vue";
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import OrderModal from "@/Modals/OrderModal.vue";
import DateTime from "@/Components/DateTime.vue";
import AddMobileIcon from "@/Components/AddMobileIcon.vue";
import DisplayUUID from "@/Components/DisplayUUID.vue";
import FiltersPanel from "@/Components/Filters/FiltersPanel.vue";
import DropdownFilter from "@/Components/Filters/Pertials/DropdownFilter.vue";
import InputFilter from "@/Components/Filters/Pertials/InputFilter.vue";
import TableActionsDropdown from "@/Components/Table/TableActionsDropdown.vue";
import TableAction from "@/Components/Table/TableAction.vue";

const orders = usePage().props.orders;

const orderPaymentLink = (payment_link) => {
    window.open(payment_link, '_blank')
}

router.on('success', (event) => {
    orders.value = usePage().props.orders;
})

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
                    class="hidden md:block btn btn-primary"
                >
                    Создать платеж
                </button>
                <AddMobileIcon
                    @click="router.visit(route('payments.create'))"
                />
            </template>
            <template v-slot:table-filters>
                <FiltersPanel name="payments">
                    <DropdownFilter
                        name="orderStatuses"
                        title="Статусы"
                    />
                    <InputFilter
                        name="externalID"
                        placeholder="Внешний ID"
                    />
                    <InputFilter
                        name="uuid"
                        placeholder="UUID"
                    />
                    <InputFilter
                        name="amount"
                        placeholder="Сумма"
                    />
                </FiltersPanel>
            </template>
            <template v-slot:body>
                <div class="overflow-x-auto card bg-base-100 shadow">
                    <table class="table table-md">
                        <thead class="text-xs uppercase bg-base-300">
                        <tr>
                            <th scope="col">
                                UUID
                            </th>
                            <th scope="col">
                                Сумма
                            </th>
                            <th scope="col">
                                Прибыль
                            </th>
                            <th scope="col">
                                Комиссия
                            </th>
                            <th scope="col">
                                Курс
                            </th>
                            <th scope="col">
                                Статус
                            </th>
<!--                            <th scope="col" class="px-6 py-3 text-nowrap">
                                Внешний ID
                            </th>-->
                            <th scope="col">
                                Создан
                            </th>
                            <th scope="col" class="px-0 py-3"></th>
                            <th scope="col" class="flex justify-center">
                                <span class="sr-only">Действия</span>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="order in orders.data" class="bg-base-100 border-b last:border-none">
                            <th scope="row" class="font-medium whitespace-nowrap text-gray-900 dark:text-gray-200">
                                <DisplayUUID :uuid="order.uuid"/>
                            </th>
                            <td>
                                <div class="text-nowrap text-base-content">{{ order.amount }} {{ order.currency.toUpperCase() }}</div>
                                <div class="text-nowrap text-xs">{{ order.total_profit }} {{ order.base_currency.toUpperCase() }}</div>
                            </td>
                            <td>
                                <div class="text-nowrap">{{ order.merchant_profit }} {{ order.base_currency.toUpperCase() }}</div>
                            </td>
                            <td class="text-nowrap">
                                {{ order.service_commission_amount_total }} {{ order.base_currency.toUpperCase() }}
                            </td>
                            <td>
                                {{ order.conversion_price }}
                            </td>
                            <td>
                                <OrderStatus :status="order.status" :status_name="order.status_name"></OrderStatus>
                            </td>
<!--                            <td class="px-6 py-3">
                                {{ order.external_id }}
                            </td>-->
                            <td>
                                <DateTime class="justify-center" :data="order.created_at"/>
                            </td>
                            <td class="px-0 py-3">
                                <div>
                                    <button
                                        v-if="order.is_h2h"
                                        @click.prevent="false"
                                        type="button"
                                        class="btn btn-xs btn-outline"
                                    >
                                        H2H
                                    </button>
                                    <button
                                        v-else
                                        @click.prevent="false"
                                        type="button"
                                        class="btn btn-xs btn-outline"
                                    >
                                        Merchant
                                    </button>
                                </div>
                            </td>
                            <td class="text-right">
                                <TableActionsDropdown>
                                    <TableAction v-if="!order.is_h2h" @click="orderPaymentLink(order.payment_link)">
                                        Платежная страница
                                    </TableAction>
                                    <TableAction @click="router.post(route('payment.callback.resend', order.id))">
                                        Отправить Callback
                                    </TableAction>
                                </TableActionsDropdown>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </MainTableSection>

        <OrderModal/>
        <ConfirmModal/>
    </div>
</template>
