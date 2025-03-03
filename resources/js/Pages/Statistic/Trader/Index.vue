<script setup>
import {Head, router, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AddMobileIcon from "@/Components/AddMobileIcon.vue";
import { ref, onMounted, computed } from 'vue';
import { format, subDays, parseISO } from 'date-fns';
import { ru } from 'date-fns/locale';

// Тестовые данные для статистики
const paymentDetails = ref([
    { id: 1, name: 'Сбербанк 1234', detail: '1234 5678 9012 3456', payment_gateway: 'Сбербанк', turnover: 15000, orders_count: 25 },
    { id: 2, name: 'Тинькофф 5678', detail: '5678 9012 3456 7890', payment_gateway: 'Тинькофф', turnover: 12500, orders_count: 18 },
    { id: 3, name: 'СБП 9012', detail: '9012 3456 7890 1234', payment_gateway: 'СБП', turnover: 8700, orders_count: 12 },
    { id: 4, name: 'Альфа-Банк 3456', detail: '3456 7890 1234 5678', payment_gateway: 'Альфа-Банк', turnover: 6300, orders_count: 9 },
]);

const closedOrders = ref([
    { id: 1, uuid: 'ORD-12345', amount_usdt: 1500, trader_paid_for_order: 1485, trader_profit: 15, commission_rate: 1.0, payment_gateway_name: 'Сбербанк', payment_detail_name: 'Сбербанк 1234', finished_at: '2024-07-01T14:30:00' },
    { id: 2, uuid: 'ORD-12346', amount_usdt: 2000, trader_paid_for_order: 1980, trader_profit: 20, commission_rate: 1.0, payment_gateway_name: 'Тинькофф', payment_detail_name: 'Тинькофф 5678', finished_at: '2024-07-02T10:15:00' },
    { id: 3, uuid: 'ORD-12347', amount_usdt: 1200, trader_paid_for_order: 1188, trader_profit: 12, commission_rate: 1.0, payment_gateway_name: 'СБП', payment_detail_name: 'СБП 9012', finished_at: '2024-07-03T16:45:00' },
    { id: 4, uuid: 'ORD-12348', amount_usdt: 800, trader_paid_for_order: 792, trader_profit: 8, commission_rate: 1.0, payment_gateway_name: 'Альфа-Банк', payment_detail_name: 'Альфа-Банк 3456', finished_at: '2024-07-04T09:20:00' },
    { id: 5, uuid: 'ORD-12349', amount_usdt: 1800, trader_paid_for_order: 1782, trader_profit: 18, commission_rate: 1.0, payment_gateway_name: 'Сбербанк', payment_detail_name: 'Сбербанк 1234', finished_at: '2024-07-05T11:10:00' },
]);

// Данные для графиков
const currentMonth = ref(new Date().getMonth());
const currentYear = ref(new Date().getFullYear());

// Генерация тестовых данных для графиков
const generateChartData = () => {
    const daysInMonth = new Date(currentYear.value, currentMonth.value + 1, 0).getDate();
    const labels = Array.from({ length: daysInMonth }, (_, i) => i + 1);

    // Данные для графика количества сделок
    const ordersCountData = Array.from({ length: daysInMonth }, () => Math.floor(Math.random() * 10));

    // Данные для графика дохода
    const incomeData = Array.from({ length: daysInMonth }, () => Math.floor(Math.random() * 500));

    // Данные для графика оборота
    const turnoverData = Array.from({ length: daysInMonth }, () => Math.floor(Math.random() * 5000));

    return {
        labels,
        ordersCountData,
        incomeData,
        turnoverData
    };
};

const chartData = ref(generateChartData());

// Фильтры для периода
const startDate = ref(format(subDays(new Date(), 30), 'yyyy-MM-dd'));
const endDate = ref(format(new Date(), 'yyyy-MM-dd'));

// Переключение месяца для графиков
const prevMonth = () => {
    if (currentMonth.value === 0) {
        currentMonth.value = 11;
        currentYear.value--;
    } else {
        currentMonth.value--;
    }
    chartData.value = generateChartData();
};

const nextMonth = () => {
    if (currentMonth.value === 11) {
        currentMonth.value = 0;
        currentYear.value++;
    } else {
        currentMonth.value++;
    }
    chartData.value = generateChartData();
};

// Форматирование текущего месяца и года
const currentMonthYear = computed(() => {
    const date = new Date(currentYear.value, currentMonth.value, 1);
    return format(date, 'LLLL yyyy', { locale: ru });
});

// Экспорт сделок
const exportOrders = () => {
    window.open(route('trader.export.orders'), '_blank');
};

defineOptions({ layout: AuthenticatedLayout });
</script>

<template>
    <div>
        <Head title="Статистика"/>

        <div class="mx-auto space-y-6">
            <div class="flex justify-between items-center">
                <h2 class="text-xl text-gray-900 dark:text-white sm:text-4xl">Статистика</h2>
                <div>
                    <button
                        @click="exportOrders"
                        type="button"
                        class="hidden md:flex items-center text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-xl text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800"
                    >
                        <svg class="w-6 h-6 mr-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 10V4a1 1 0 0 0-1-1H9.914a1 1 0 0 0-.707.293L5.293 7.207A1 1 0 0 0 5 7.914V20a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2M10 3v4a1 1 0 0 1-1 1H5m5 6h9m0 0-2-2m2 2-2 2"/>
                        </svg>
                        Выгрузить сделки
                    </button>
                    <button
                        @click="exportOrders"
                        type="button"
                        class="md:hidden text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-xl text-sm p-2 text-center inline-flex items-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                    >
                        <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 10V4a1 1 0 0 0-1-1H9.914a1 1 0 0 0-.707.293L5.293 7.207A1 1 0 0 0 5 7.914V20a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2M10 3v4a1 1 0 0 1-1 1H5m5 6h9m0 0-2-2m2 2-2 2"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Графики -->
            <section>
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-semibold dark:text-white">Статистика за {{ currentMonthYear }}</h3>
                    <div class="flex items-center space-x-4">
                        <button @click="prevMonth" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white">
                            <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                        <button @click="nextMonth" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white">
                            <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- График количества сделок -->
                    <div class="bg-white dark:bg-gray-800 shadow-md p-6 rounded-plate">
                        <h4 class="text-lg font-medium mb-4 dark:text-white">Количество сделок</h4>
                        <div class="h-40 flex items-end space-x-1">
                            <div v-for="(value, index) in chartData.ordersCountData" :key="index"
                                class="bg-blue-500 dark:bg-blue-600 rounded-t-sm"
                                :style="{
                                    height: `${(value / Math.max(...chartData.ordersCountData)) * 100}%`,
                                    width: `${100 / chartData.ordersCountData.length}%`
                                }"
                                :title="`День ${chartData.labels[index]}: ${value} сделок`">
                            </div>
                        </div>
                        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400 flex justify-between">
                            <span>1</span>
                            <span>{{ chartData.labels.length }}</span>
                        </div>
                    </div>

                    <!-- График дохода -->
                    <div class="bg-white dark:bg-gray-800 shadow-md p-6 rounded-plate">
                        <h4 class="text-lg font-medium mb-4 dark:text-white">Доход ($)</h4>
                        <div class="h-40 flex items-end space-x-1">
                            <div v-for="(value, index) in chartData.incomeData" :key="index"
                                class="bg-green-500 dark:bg-green-600 rounded-t-sm"
                                :style="{
                                    height: `${(value / Math.max(...chartData.incomeData)) * 100}%`,
                                    width: `${100 / chartData.incomeData.length}%`
                                }"
                                :title="`День ${chartData.labels[index]}: $${value}`">
                            </div>
                        </div>
                        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400 flex justify-between">
                            <span>1</span>
                            <span>{{ chartData.labels.length }}</span>
                        </div>
                    </div>

                    <!-- График оборота -->
                    <div class="bg-white dark:bg-gray-800 shadow-md p-6 rounded-plate">
                        <h4 class="text-lg font-medium mb-4 dark:text-white">Оборот ($)</h4>
                        <div class="h-40 flex items-end space-x-1">
                            <div v-for="(value, index) in chartData.turnoverData" :key="index"
                                class="bg-purple-500 dark:bg-purple-600 rounded-t-sm"
                                :style="{
                                    height: `${(value / Math.max(...chartData.turnoverData)) * 100}%`,
                                    width: `${100 / chartData.turnoverData.length}%`
                                }"
                                :title="`День ${chartData.labels[index]}: $${value}`">
                            </div>
                        </div>
                        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400 flex justify-between">
                            <span>1</span>
                            <span>{{ chartData.labels.length }}</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Статистика по реквизитам -->
            <section class="space-y-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-2xl font-semibold dark:text-white">Статистика по реквизитам</h3>
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-2">
                            <label for="start-date" class="text-sm text-gray-500 dark:text-gray-400">От:</label>
                            <input type="date" id="start-date" v-model="startDate" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div class="flex items-center space-x-2">
                            <label for="end-date" class="text-sm text-gray-500 dark:text-gray-400">До:</label>
                            <input type="date" id="end-date" v-model="endDate" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
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
                                <td class="px-6 py-4">${{ detail.turnover }}</td>
                                <td class="px-6 py-4">{{ detail.orders_count }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Закрытые сделки -->
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
                                <td class="px-6 py-4">${{ order.amount_usdt }}</td>
                                <td class="px-6 py-4">${{ order.trader_paid_for_order }}</td>
                                <td class="px-6 py-4">${{ order.trader_profit }}</td>
                                <td class="px-6 py-4">{{ order.commission_rate }}%</td>
                                <td class="px-6 py-4">{{ order.payment_gateway_name }}</td>
                                <td class="px-6 py-4">{{ order.payment_detail_name }}</td>
                                <td class="px-6 py-4">{{ new Date(order.finished_at).toLocaleString() }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</template>
