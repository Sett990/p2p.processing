<script setup>
import { defineProps, computed } from 'vue';
import DisplayUUID from "@/Components/DisplayUUID.vue";
import DateTime from "@/Components/DateTime.vue";
import MainTableSection from "@/Wrappers/MainTableSection.vue";

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

</script>

<template>
    <section class="space-y-4">
        <MainTableSection
            :data="closedOrders"
            :title="null"
        >
            <template v-slot:body>
                <div class="relative overflow-x-auto shadow-md rounded-table">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">UUID</th>
                                <th scope="col" class="px-6 py-3">Сумма ($)</th>
                                <th scope="col" class="px-6 py-3">Списание со счета</th>
                                <th scope="col" class="px-6 py-3">Доход</th>
                                <th scope="col" class="px-6 py-3">Комиссия (%)</th>
                                <th scope="col" class="px-6 py-3">Платежный метод</th>
                                <th scope="col" class="px-6 py-3">Реквизит</th>
                                <th scope="col" class="px-6 py-3">Дата</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="order in closedOrders.data" :key="order.id" class="bg-white border-b last:border-none dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                <th scope="row" class="px-6 py-3 font-medium whitespace-nowrap text-gray-900 dark:text-gray-200">
                                    <DisplayUUID :uuid="order.uuid"/>
                                </th>
                                <td class="px-6 py-4">${{ formatNumber(order.amount_usdt) }}</td>
                                <td class="px-6 py-4">${{ formatNumber(order.trader_paid_for_order) }}</td>
                                <td class="px-6 py-4">${{ formatNumber(order.trader_profit) }}</td>
                                <td class="px-6 py-4">{{ order.commission_rate }}%</td>
                                <td class="px-6 py-4">{{ order.payment_gateway_name }}</td>
                                <td class="px-6 py-4">{{ order.payment_detail_name }}</td>
                                <td class="px-6 py-4">
                                    <DateTime :data="order.finished_at"/>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </MainTableSection>
    </section>
</template>
