<script setup>
import {Head, router, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AddMobileIcon from "@/Components/AddMobileIcon.vue";
import { ref } from 'vue';
import MonthlyChart from './Components/MonthlyChart.vue';
import TablesSection from './Components/TablesSection.vue';

// Получаем данные из контроллера
const paymentDetails = ref(usePage().props.paymentDetails || {});
const closedOrders = ref(usePage().props.closedOrders || {});
const chartData = ref(usePage().props.chartData || {});
const currentMonth = ref(usePage().props.currentMonth || '');
const prevMonth = ref(usePage().props.prevMonth || '');
const nextMonth = ref(usePage().props.nextMonth || '');
const chartType = ref(usePage().props.chartType || 'turnover');
const tableType = ref(usePage().props.tableType || 'payment-details');

// Обработка изменения типа графика
const handleChartTypeChanged = (type) => {
    chartType.value = type;
    
    // URL параметры обновляются прямо в компоненте MonthlyChart
};

// Обработка изменения типа таблицы
const handleTableTypeChanged = (type) => {
    tableType.value = type;
    
    // URL параметры обновляются прямо в компоненте TablesSection
};

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
                        class="hidden md:flex btn btn-primary btn-md"
                    >
                        <svg class="w-6 h-6 mr-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 10V4a1 1 0 0 0-1-1H9.914a1 1 0 0 0-.707.293L5.293 7.207A1 1 0 0 0 5 7.914V20a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2M10 3v4a1 1 0 0 1-1 1H5m5 6h9m0 0-2-2m2 2-2 2"/>
                        </svg>
                        Выгрузить сделки
                    </button>
                    <button
                        @click="exportOrders"
                        type="button"
                        class="md:hidden btn btn-primary btn-square btn-md"
                    >
                        <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 10V4a1 1 0 0 0-1-1H9.914a1 1 0 0 0-.707.293L5.293 7.207A1 1 0 0 0 5 7.914V20a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2M10 3v4a1 1 0 0 1-1 1H5m5 6h9m0 0-2-2m2 2-2 2"/>
                        </svg>
                    </button>
                </div>
            </div>

            <MonthlyChart 
                :chart-data="chartData"
                :current-month="currentMonth"
                :prev-month="prevMonth"
                :next-month="nextMonth"
                :initial-chart-type="chartType"
                @chart-type-changed="handleChartTypeChanged"
            />

            <TablesSection
                :payment-details="paymentDetails"
                :closed-orders="closedOrders"
                :initial-table-type="tableType"
                @table-type-changed="handleTableTypeChanged"
            />
        </div>
    </div>
</template>
