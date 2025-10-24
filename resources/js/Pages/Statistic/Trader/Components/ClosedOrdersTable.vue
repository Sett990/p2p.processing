<script setup>
import {defineProps, computed, ref} from 'vue';
import DisplayUUID from "@/Components/DisplayUUID.vue";
import DateTime from "@/Components/DateTime.vue";
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import {router} from "@inertiajs/vue3";
import AlertError from "@/Components/Alerts/AlertError.vue";
import Pagination from "@/Components/Pagination/Pagination.vue";
import AlertInfo from "@/Components/Alerts/AlertInfo.vue";
import GatewayLogo from "@/Components/GatewayLogo.vue";
import PaymentDetail from "@/Components/PaymentDetail.vue";

const props = defineProps({
    closedOrders: {
        type: Object,
        required: true
    }
});

// Форматирование числа
const formatNumber = (num) => {
    // Округляем до двух знаков после запятой, если есть дробная часть
    const roundedNum = Math.round(num * 100) / 100;

    // Форматируем число с разделителями тысяч
    return roundedNum.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

const currentPage = ref(props.closedOrders?.meta?.current_page);

const openPage = (page) => {
    // Получаем текущие параметры URL
    const urlParams = new URLSearchParams(window.location.search);
    const month = urlParams.get('month') || '';
    const chartType = urlParams.get('chartType') || 'turnover';
    const tableType = urlParams.get('tableType') || 'closed-orders';

    router.visit(route(route().current()), {
        data: {
            page,
            month,
            chartType,
            tableType
        },
        preserveScroll: true,
        only: ['closedOrders'] // Обновляем только данные таблицы заказов
    });
}
</script>

<template>
    <section class="space-y-4">
        <div>
            <div>
                <div class="mx-auto space-y-6">
                    <div>
                <div class="overflow-x-auto card bg-base-100 shadow">
                    <table class="table table-sd">
                        <thead class="text-xs uppercase bg-base-300">
                                <tr>
                                <th>UUID</th>
                                <th>Сумма</th>
                                <th>Списание c траста</th>
                                <th>Доход</th>
                                <th>Комиссия</th>
                                <th>Реквизит</th>
                                <th>Дата</th>
                                </tr>
                                </thead>
                        <tbody>
                        <tr v-for="order in closedOrders.data" :key="order.id" class="hover">
                            <th scope="row" class="font-medium whitespace-nowrap">
                                        <DisplayUUID :uuid="order.uuid"/>
                                    </th>
                            <td>
                                <div class="text-nowrap">{{ order.amount }} {{ order.currency.toUpperCase() }}</div>
                                <div class="text-nowrap text-xs">{{ order.total_profit }} {{ order.base_currency.toUpperCase() }}</div>
                                    </td>
                            <td>${{ formatNumber(order.trader_paid_for_order) }}</td>
                            <td>${{ formatNumber(order.trader_profit) }}</td>
                            <td>{{ order.trader_commission_rate }}%</td>
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
                                        <DateTime :data="order.finished_at"/>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div>
                        <Pagination
                            v-model="currentPage"
                            :total-items="closedOrders.meta.total"
                            previous-label="Назад" next-label="Вперед"
                            @page-changed="openPage"
                            :per-page="closedOrders.meta.per_page"
                        ></Pagination>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
