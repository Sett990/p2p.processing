<script setup>
import { ref, defineProps, defineEmits } from 'vue';

const props = defineProps({
    paymentDetails: {
        type: Array,
        required: true
    }
});

const emit = defineEmits(['dateRangeChanged']);

// Фильтры для периода
const startDate = ref(new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]);
const endDate = ref(new Date().toISOString().split('T')[0]);

// Отслеживание изменений дат
const onDateChange = () => {
    emit('dateRangeChanged', { startDate: startDate.value, endDate: endDate.value });
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
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <h3 class="text-2xl font-semibold dark:text-white">Статистика по реквизитам</h3>
            <div class="flex flex-col md:flex-row items-start md:items-center gap-4">
                <div class="flex items-center space-x-2">
                    <label for="start-date" class="text-sm text-gray-500 dark:text-gray-400">От:</label>
                    <input type="date" id="start-date" v-model="startDate" @change="onDateChange" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>
                <div class="flex items-center space-x-2">
                    <label for="end-date" class="text-sm text-gray-500 dark:text-gray-400">До:</label>
                    <input type="date" id="end-date" v-model="endDate" @change="onDateChange" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>
            </div>
        </div>

        <div class="relative overflow-x-auto shadow-md rounded-table">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">ID</th>
                        <th scope="col" class="px-6 py-3">Название</th>
                        <th scope="col" class="px-6 py-3">Реквизит</th>
                        <th scope="col" class="px-6 py-3">Платежный метод</th>
                        <th scope="col" class="px-6 py-3">Оборот ($)</th>
                        <th scope="col" class="px-6 py-3">Кол-во сделок</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="detail in paymentDetails" :key="detail.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <td class="px-6 py-4">{{ detail.id }}</td>
                        <td class="px-6 py-4">{{ detail.name }}</td>
                        <td class="px-6 py-4">{{ detail.detail }}</td>
                        <td class="px-6 py-4">{{ detail.payment_gateway }}</td>
                        <td class="px-6 py-4">${{ formatNumber(detail.turnover) }}</td>
                        <td class="px-6 py-4">{{ detail.orders_count }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template> 