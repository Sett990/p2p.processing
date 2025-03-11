<script setup>
import { ref, defineProps, defineEmits, computed } from 'vue';
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import { router } from '@inertiajs/vue3';

const props = defineProps({
    paymentDetails: {
        type: Object,
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

// Данные для запроса пагинации
const queryData = computed(() => {
    return {
        startDate: startDate.value,
        endDate: endDate.value
    };
});
</script>

<template>
    <section class="space-y-4">
        <div class="flex flex-col md:flex-row justify-end items-start md:items-center gap-4 mb-4">
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

        <MainTableSection
            :data="paymentDetails"
            :query-data="queryData"
            :title="null"
        >
            <template v-slot:body>
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
                            <tr v-for="detail in paymentDetails.data" :key="detail.id" class="bg-white border-b last:border-none dark:bg-gray-800 dark:border-gray-700">
                                <th scope="row" class="px-6 py-3 font-medium whitespace-nowrap text-gray-900 dark:text-gray-200">
                                    {{ detail.id }}
                                </th>
                                <td class="px-6 py-4">{{ detail.name }}</td>
                                <td class="px-6 py-4">{{ detail.detail }}</td>
                                <td class="px-6 py-4">{{ detail.payment_gateway_name }}</td>
                                <td class="px-6 py-4">${{ formatNumber(detail.turnover || 1000) }}</td>
                                <td class="px-6 py-4">{{ detail.orders_count }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </MainTableSection>
    </section>
</template>
