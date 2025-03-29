<script setup>
import {Head, router, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AddMobileIcon from "@/Components/AddMobileIcon.vue";
import { ref } from 'vue';
import { format, subDays } from 'date-fns';
import MonthlyChart from './Components/MonthlyChart.vue';
import TablesSection from './Components/TablesSection.vue';

// Получаем данные из контроллера
const paymentDetails = ref(usePage().props.paymentDetails || {});
const closedOrders = ref(usePage().props.closedOrders || {});
const filters = ref(usePage().props.filters || {});

// Обработка изменения диапазона дат
const handleDateRangeChanged = ({ startDate, endDate }) => {
    // Обновляем фильтры
    filters.value.startDate = startDate;
    filters.value.endDate = endDate;

    // Запрос к API для получения данных за выбранный период
    router.visit(route(route().current()), {
        data: {
            startDate,
            endDate
        },
        preserveState: true,
        only: ['paymentDetails', 'closedOrders']
    });
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

<!--            <MonthlyChart />

            <TablesSection
                :payment-details="paymentDetails"
                :closed-orders="closedOrders"
                :filters="filters"
                @date-range-changed="handleDateRangeChanged"
            />-->
        </div>
    </div>
</template>
