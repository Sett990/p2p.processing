<script setup>
import { defineProps } from 'vue';

const props = defineProps({
    closedOrders: {
        type: Array,
        required: true
    }
});

// Форматирование даты
const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString();
};

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
        <h3 class="text-2xl font-semibold dark:text-white">Успешные сделки</h3>

        <div class="relative overflow-x-auto shadow-md rounded-table">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">ID</th>
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
                    <tr v-for="order in closedOrders" :key="order.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <td class="px-6 py-4">{{ order.uuid }}</td>
                        <td class="px-6 py-4">${{ formatNumber(order.amount_usdt) }}</td>
                        <td class="px-6 py-4">${{ formatNumber(order.trader_paid_for_order) }}</td>
                        <td class="px-6 py-4">${{ formatNumber(order.trader_profit) }}</td>
                        <td class="px-6 py-4">{{ order.commission_rate }}%</td>
                        <td class="px-6 py-4">{{ order.payment_gateway_name }}</td>
                        <td class="px-6 py-4">{{ order.payment_detail_name }}</td>
                        <td class="px-6 py-4">{{ formatDate(order.finished_at) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template> 