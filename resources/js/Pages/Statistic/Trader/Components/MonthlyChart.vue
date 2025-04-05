<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { format, addDays, startOfMonth, endOfMonth, parseISO } from 'date-fns';
import { ru } from 'date-fns/locale';
import ApexCharts from 'apexcharts';
import { router, usePage } from '@inertiajs/vue3';

// Получаем данные из контроллера
const props = defineProps({
    chartData: {
        type: Object,
        required: true
    },
    currentMonth: {
        type: String,
        required: true
    },
    prevMonth: {
        type: String,
        required: true
    },
    nextMonth: {
        type: String,
        required: true
    },
    initialChartType: {
        type: String,
        default: 'turnover'
    }
});

const emit = defineEmits(['chart-type-changed']);

// Тип графика (оборот, количество сделок, доход)
const chartType = ref(props.initialChartType); // Используем переданный тип графика

// Функция форматирования чисел
const formatNumber = (num) => {
    // Округляем до двух знаков после запятой, если есть дробная часть
    const roundedNum = Math.round(num * 100) / 100;

    // Форматируем число с разделителями тысяч
    return roundedNum.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

// Переключение месяца для графиков
const prevMonth = () => {
    router.visit(route(route().current()), {
        data: {
            month: props.prevMonth,
            chartType: chartType.value
        },
        preserveScroll: true
    });
};

const nextMonth = () => {
    router.visit(route(route().current()), {
        data: {
            month: props.nextMonth,
            chartType: chartType.value
        },
        preserveScroll: true
    });
};

// Форматирование текущего месяца
const currentMonthDisplay = computed(() => {
    if (!props.currentMonth) return '';
    const [year, month] = props.currentMonth.split('-');
    return format(new Date(parseInt(year), parseInt(month) - 1, 1), 'LLLL yyyy', { locale: ru });
});

// Ссылка на DOM-элемент для графика
const chart = ref(null);

// Получение настроек графика в зависимости от выбранного типа
const getChartOptions = () => {
    let seriesName, seriesData, color, formatter;

    switch(chartType.value) {
        case 'orders':
            seriesName = 'Количество сделок';
            seriesData = props.chartData.ordersCountData;
            color = '#f59e0b'; // Оранжевый/Янтарный
            formatter = (value) => Math.round(value);
            break;
        case 'income':
            seriesName = 'Доход ($)';
            seriesData = props.chartData.incomeData;
            color = '#3b82f6'; // Синий
            formatter = (value) => '$' + value;
            break;
        case 'turnover':
        default:
            seriesName = 'Оборот ($)';
            seriesData = props.chartData.turnoverData;
            color = '#10b981'; // Зеленый
            formatter = (value) => '$' + value;
            break;
    }

    return {
        chart: {
            type: 'line',
            height: '100%',
            background: 'transparent',
            toolbar: {
                show: false,
            },
        },
        series: [{
            name: seriesName,
            data: seriesData,
        }],
        xaxis: {
            categories: props.chartData.labels,
            labels: {
                style: {
                    colors: '#999',
                },
                rotateAlways: false,
                hideOverlappingLabels: true,
            },
            axisBorder: {
                show: false,
            },
            axisTicks: {
                show: false,
            },
        },
        yaxis: {
            labels: {
                style: {
                    colors: '#999',
                },
                formatter: formatter
            },
        },
        grid: {
            borderColor: 'rgba(200, 200, 200, 0.1)',
        },
        stroke: {
            width: 2,
            curve: 'smooth',
        },
        colors: [color],
        markers: {
            size: 4,
            colors: [color],
            strokeColors: '#fff',
            strokeWidth: 2,
        },
        tooltip: {
            theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
            x: {
                formatter: (index) => props.chartData.fullDates[index - 1]
            },
            y: {
                formatter: formatter
            }
        },
    };
};

// Функция для рендеринга графика
const renderChart = () => {
    // Уничтожаем предыдущий график, если он существует
    if (chart.value && chart.value.__chartInstance) {
        chart.value.__chartInstance.destroy();
    }

    // Создаем новый график
    const options = getChartOptions();
    const apexChart = new ApexCharts(chart.value, options);
    chart.value.__chartInstance = apexChart;
    apexChart.render();
};

// Следим за изменением типа графика
watch(chartType, (newType) => {
    renderChart();
    emit('chart-type-changed', newType);
});

// Следим за изменением initialChartType из props
watch(() => props.initialChartType, (newType) => {
    if (newType !== chartType.value) {
        chartType.value = newType;
    }
});

// Следим за изменением данных графика
watch(() => props.chartData, () => {
    renderChart();
}, { deep: true });

// Рендерим график при монтировании компонента
onMounted(() => {
    renderChart();
});

// Изменение типа графика
const setChartType = (type) => {
    chartType.value = type;
};

// Получение иконки для типа графика
const getIconForType = (type) => {
    switch(type) {
        case 'orders':
            return 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'; // График
        case 'income':
            return 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'; // Деньги
        case 'turnover':
        default:
            return 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'; // Монета
    }
};

// Получение цвета для типа графика
const getColorForType = (type) => {
    switch(type) {
        case 'orders':
            return 'yellow'; // Желтый
        case 'income':
            return 'blue'; // Синий
        case 'turnover':
        default:
            return 'green'; // Зеленый
    }
};

// Получение заголовка для типа графика
const getTitleForType = (type) => {
    switch(type) {
        case 'orders':
            return 'Количество сделок';
        case 'income':
            return 'Доход';
        case 'turnover':
        default:
            return 'Оборот';
    }
};

// Получение значения для типа графика
const getValueForType = (type) => {
    switch(type) {
        case 'orders':
            return props.chartData.totalOrders;
        case 'income':
            return '$' + formatNumber(props.chartData.totalIncome);
        case 'turnover':
        default:
            return '$' + formatNumber(props.chartData.totalTurnover);
    }
};
</script>

<template>
    <section>
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-2xl font-semibold dark:text-white">Статистика за {{ currentMonthDisplay }}</h3>
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

        <!-- Переключатели типа графика -->
        <div class="flex flex-wrap gap-3 mb-6 justify-start">
            <!-- Оборот -->
            <div
                class="bg-white dark:bg-gray-800 p-3 rounded-lg shadow-sm cursor-pointer flex items-center gap-3 transition-all"
                :class="{ 'ring-2 ring-green-500 bg-green-50 dark:bg-green-900/20': chartType === 'turnover' }"
                @click="setChartType('turnover')"
            >
                <div class="bg-green-100 dark:bg-green-900 p-2 rounded-full">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12V8H6a2 2 0 00-2 2v4m16 0v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 12H4"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium dark:text-white">Оборот</p>
                    <p class="text-base font-bold dark:text-white">${{ formatNumber(chartData.totalTurnover) }}</p>
                </div>
            </div>

            <!-- Доход -->
            <div
                class="bg-white dark:bg-gray-800 p-3 rounded-lg shadow-sm cursor-pointer flex items-center gap-3 transition-all"
                :class="{ 'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900/20': chartType === 'income' }"
                @click="setChartType('income')"
            >
                <div class="bg-blue-100 dark:bg-blue-900 p-2 rounded-full">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium dark:text-white">Доход</p>
                    <p class="text-base font-bold dark:text-white">${{ formatNumber(chartData.totalIncome) }}</p>
                </div>
            </div>

            <!-- Количество сделок -->
            <div
                class="bg-white dark:bg-gray-800 p-3 rounded-lg shadow-sm cursor-pointer flex items-center gap-3 transition-all"
                :class="{ 'ring-2 ring-amber-500 bg-amber-50 dark:bg-amber-900/20': chartType === 'orders' }"
                @click="setChartType('orders')"
            >
                <div class="bg-amber-100 dark:bg-amber-900 p-2 rounded-full">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium dark:text-white">Сделки</p>
                    <p class="text-base font-bold dark:text-white">{{ chartData.totalOrders }}</p>
                </div>
            </div>
        </div>

        <!-- График -->
        <div class="bg-white dark:bg-gray-800 shadow-md p-6 rounded-plate">
            <h4 class="text-xl font-bold mb-4 dark:text-white">
                {{ chartType === 'turnover' ? 'График оборота' : chartType === 'orders' ? 'График количества сделок' : 'График доходов' }} за месяц
            </h4>
            <div ref="chart" class="h-100"></div>
        </div>
    </section>
</template>
