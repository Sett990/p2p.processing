<script setup>
import {Head, router, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import OrderStatus from "@/Components/OrderStatus.vue";
import PaymentDetail from "@/Components/PaymentDetail.vue";
import ConfirmModal from "@/Components/Modals/ConfirmModal.vue";
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import OrderModal from "@/Modals/OrderModal.vue";
import {useModalStore} from "@/store/modal.js";
import DateTime from "@/Components/DateTime.vue";
import ShowAction from "@/Components/Table/ShowAction.vue";
import {ref} from "vue";
import DisplayUUID from "@/Components/DisplayUUID.vue";
import FiltersPanel from "@/Components/Filters/FiltersPanel.vue";
import DropdownFilter from "@/Components/Filters/Pertials/DropdownFilter.vue";
import InputFilter from "@/Components/Filters/Pertials/InputFilter.vue";
import DateFilter from "@/Components/Filters/Pertials/DateFilter.vue";
import GatewayLogo from "@/Components/GatewayLogo.vue";
import RefreshTableData from "@/Components/Table/RefreshTableData.vue";

const orders = ref(usePage().props.orders);
const modalStore = useModalStore();

router.on('success', (event) => {
    orders.value = usePage().props.orders;
})

const reloadingTableData = ref(false);

const openOrderModal = (order) => {
    if (reloadingTableData.value) {
        return;
    }
    modalStore.openOrderModal({order_id: order.id})
}

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Сделки" />

        <MainTableSection
            title="Сделки"
            :data="orders"
        >
            <template v-slot:header>
                <div>
                    <FiltersPanel name="orders">
                        <DateFilter name="startDate" title="Начальная дата"/>
                        <DateFilter name="endDate" title="Конечная дата"/>
                        <InputFilter
                            name="uuid"
                            placeholder="UUID"
                        />
                        <InputFilter
                            name="amount"
                            placeholder="Сумма"
                        />
                        <InputFilter
                            name="paymentDetail"
                            placeholder="Реквизит"
                        />
                        <DropdownFilter
                            name="detailTypes"
                            title="Тип реквизита"
                        />
                        <InputFilter
                            name="paymentGateway"
                            placeholder="Платежный метод"
                        />
                        <InputFilter
                            name="user"
                            placeholder="Пользователь"
                        />
                        <DropdownFilter
                            name="orderStatuses"
                            title="Статусы"
                        />
                    </FiltersPanel>

                    <div class="flex justify-end">
                        <RefreshTableData
                            @refresh-started="reloadingTableData = true"
                            @refresh-finished="reloadingTableData = false"
                        />
                    </div>
                </div>
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
                                    Реквизит
                                </th>
                                <th scope="col">
                                    Трейдер
                                </th>
                                <th scope="col">
                                    Статус
                                </th>
                                <th scope="col">
                                    Создан
                                </th>
                                <th scope="col" class=" flex justify-center">
                                    <span class="sr-only">Действия</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="order in orders.data" class="bg-base-100 border-b last:border-none">
                            <th scope="row" class=" font-medium whitespace-nowrap">
                                <DisplayUUID :uuid="order.uuid"/>
                            </th>
                            <td>
                                <div class="text-nowrap">{{ order.amount }} {{ order.currency.toUpperCase() }}</div>
                                <div class="text-nowrap text-xs">{{ order.total_profit }} {{ order.base_currency.toUpperCase() }}</div>
                            </td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <GatewayLogo :img_path="order.payment_gateway_logo_path" class="w-10 h-10"/>
                                    <div>
                                        <PaymentDetail
                                            :detail="order.payment_detail"
                                            :type="order.payment_detail_type"
                                            :copyable="false"
                                            class=""
                                        ></PaymentDetail>
                                        <div class="text-xs text-nowrap">{{ order.payment_detail_name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                {{ order.trader_email }}
                            </td>
                            <td>
                                <OrderStatus :status="order.status" :status_name="order.status_name"></OrderStatus>
                            </td>
                            <td>
                                <DateTime class="justify-start" :data="order.created_at"/>
                            </td>
                            <td class=" text-right">
                                <ShowAction @click.prevent="openOrderModal(order)"></ShowAction>
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
