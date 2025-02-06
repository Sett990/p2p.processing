<script setup>
import {Head, router, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import OrderStatus from "@/Components/OrderStatus.vue";
import PaymentDetail from "@/Components/PaymentDetail.vue";
import SmsLogsModal from "@/Modals/SmsLogsModal.vue";
import ConfirmModal from "@/Components/Modals/ConfirmModal.vue";
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import OrderModal from "@/Modals/OrderModal.vue";
import {useModalStore} from "@/store/modal.js";
import DateTime from "@/Components/DateTime.vue";
import {useViewStore} from "@/store/view.js";
import ShowAction from "@/Components/Table/ShowAction.vue";
import {ref} from "vue";
import DisplayUUID from "@/Components/DisplayUUID.vue";
import FiltersPanel from "@/Components/Filters/FiltersPanel.vue";
import StatusesFilter from "@/Components/Filters/Pertials/StatusesFilter.vue";
import InputFilter from "@/Components/Filters/Pertials/InputFilter.vue";
import DateRangeFilter from "@/Components/Filters/Pertials/DateRangeFilter.vue";
import EditOrderAmountModal from "@/Modals/Order/EditOrderAmountModal.vue";

const viewStore = useViewStore();
const orders = ref(usePage().props.orders);
const modalStore = useModalStore();

const filters = ref(usePage().props.filters);
const filtersVariants = ref(usePage().props.filtersVariants);

router.on('success', (event) => {
    orders.value = usePage().props.orders;
})

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Сделки" />

        <MainTableSection
            title="Сделки"
            :data="orders"
            :query-data="{filters}"
        >
            <template v-slot:header>
                <FiltersPanel name="orders" :filters="filters">
                    <StatusesFilter
                        v-model="filters.orderStatuses"
                        :statuses-variants="filtersVariants.orderStatuses"
                    />
                    <DateRangeFilter v-model="filters.dateRange"/>
                    <InputFilter
                        v-if="viewStore.isAdminViewMode"
                        v-model="filters.externalID"
                        placeholder="Внешний ID"
                    />
                    <InputFilter
                        v-model="filters.uuid"
                        placeholder="UUID"
                    />
                    <InputFilter
                        v-model="filters.amount"
                        placeholder="Сумма"
                    />
                    <InputFilter
                        v-model="filters.paymentDetail"
                        placeholder="Реквизит"
                    />
                    <InputFilter
                        v-if="viewStore.isAdminViewMode"
                        v-model="filters.user"
                        placeholder="Пользователь"
                    />
                </FiltersPanel>
            </template>
            <template v-slot:body>
                <div class="relative overflow-x-auto shadow-md sm:rounded-table ">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">
                                    UUID
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
                                <DisplayUUID :uuid="order.uuid"/>
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
        <EditOrderAmountModal/>
    </div>
</template>
