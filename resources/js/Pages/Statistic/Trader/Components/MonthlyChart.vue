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
    // Получаем текущие параметры URL
    const urlParams = new URLSearchParams(window.location.search);
    const tableType = urlParams.get('tableType') || 'payment-details';

    router.visit(route(route().current()), {
        data: {
            month: props.prevMonth,
            chartType: chartType.value,
            tableType: tableType,
            page: 1 // Сбрасываем пагинацию при смене месяца
        },
        preserveScroll: true,
        preserveState: false // Сбрасываем состояние для корректного обновления данных
    });
};

const nextMonth = () => {
    // Получаем текущие параметры URL
    const urlParams = new URLSearchParams(window.location.search);
    const tableType = urlParams.get('tableType') || 'payment-details';

    router.visit(route(route().current()), {
        data: {
            month: props.nextMonth,
            chartType: chartType.value,
            tableType: tableType,
            page: 1 // Сбрасываем пагинацию при смене месяца
        },
        preserveScroll: true,
        preserveState: false // Сбрасываем состояние для корректного обновления данных
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
            height: '95%',
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

    // Обновляем URL параметры без перезагрузки страницы
    const urlParams = new URLSearchParams(window.location.search);
    const month = urlParams.get('month') || props.currentMonth;
    const tableType = urlParams.get('tableType') || 'payment-details';

    router.visit(route(route().current()), {
        data: {
            month: month,
            chartType: newType,
            tableType: tableType,
            page: 1 // Сбрасываем пагинацию при смене типа графика
        },
        preserveScroll: true,
        preserveState: true,
        only: []
    });
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
    // Проверяем URL параметры при загрузке
    const urlParams = new URLSearchParams(window.location.search);
    const chartTypeParam = urlParams.get('chartType');

    if (chartTypeParam && ['turnover', 'income', 'orders'].includes(chartTypeParam)) {
        chartType.value = chartTypeParam;
    }

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
        <!-- Переключатели типа графика -->
        <div class="flex justify-between items-end">
            <div class="join mb-6">
                <button class="btn btn-sm join-item" :class="{ 'btn-active btn-primary': chartType === 'turnover' }" @click="setChartType('turnover')">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Оборот: ${{ formatNumber(chartData.totalTurnover) }}
                </button>
                <button class="btn btn-sm join-item" :class="{ 'btn-active btn-primary': chartType === 'income' }" @click="setChartType('income')">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Доход: ${{ formatNumber(chartData.totalIncome) }}
                </button>
                <button class="btn btn-sm join-item" :class="{ 'btn-active btn-primary': chartType === 'orders' }" @click="setChartType('orders')">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                    Сделки: {{ chartData.totalOrders }}
                </button>
            </div>
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center space-x-2">
                    <button @click="prevMonth" class="btn btn-ghost btn-square btn-sm">
                        <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <button @click="nextMonth" class="btn btn-ghost btn-square btn-sm">
                        <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- График -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h4 class="card-title">
                    {{ chartType === 'turnover' ? 'График оборота' : chartType === 'orders' ? 'График количества сделок' : 'График доходов' }} за {{ currentMonthDisplay }}
                </h4>
                <div ref="chart" class="h-50"></div>
            </div>
        </div>
    </section>
</template>
