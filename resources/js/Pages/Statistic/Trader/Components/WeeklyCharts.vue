<script setup>
import { ref, computed, onMounted } from 'vue';
import { format, addDays, startOfWeek } from 'date-fns';
import { ru } from 'date-fns/locale';
import ApexCharts from 'apexcharts';

// Данные для графиков
const currentWeekStart = ref(startOfWeek(new Date(), { weekStartsOn: 1 })); // Начало недели (понедельник)

// Генерация тестовых данных для графиков за неделю
const generateChartData = () => {
    const daysInWeek = 7;
    const labels = Array.from({ length: daysInWeek }, (_, i) => {
        const date = addDays(currentWeekStart.value, i);
        return format(date, 'EEE', { locale: ru });
    });

    const fullDates = Array.from({ length: daysInWeek }, (_, i) => {
        return format(addDays(currentWeekStart.value, i), 'dd.MM.yyyy');
    });

    // Данные для графика количества сделок
    const ordersCountData = Array.from({ length: daysInWeek }, () => Math.floor(Math.random() * 10));

    // Данные для графика дохода
    const incomeData = Array.from({ length: daysInWeek }, () => Math.floor(Math.random() * 500));

    // Данные для графика оборота
    const turnoverData = Array.from({ length: daysInWeek }, () => Math.floor(Math.random() * 5000));

    return {
        labels,
        fullDates,
        ordersCountData,
        incomeData,
        turnoverData
    };
};

const chartData = ref(generateChartData());

// Переключение недели для графиков
const prevWeek = () => {
    currentWeekStart.value = addDays(currentWeekStart.value, -7);
    chartData.value = generateChartData();
    renderCharts();
};

const nextWeek = () => {
    currentWeekStart.value = addDays(currentWeekStart.value, 7);
    chartData.value = generateChartData();
    renderCharts();
};

// Форматирование текущей недели
const currentWeekRange = computed(() => {
    const weekEnd = addDays(currentWeekStart.value, 6);
    return `${format(currentWeekStart.value, 'd MMMM', { locale: ru })} - ${format(weekEnd, 'd MMMM yyyy', { locale: ru })}`;
});

// Ссылки на DOM-элементы для графиков
const ordersChart = ref(null);
const incomeChart = ref(null);
const turnoverChart = ref(null);

// Функция для рендеринга графиков
const renderCharts = () => {
    // Опции для графика количества сделок
    const ordersOptions = {
        chart: {
            type: 'line',
            height: '100%',
            background: 'transparent',
            toolbar: {
                show: false,
            },
        },
        series: [{
            name: 'Количество сделок',
            data: chartData.value.ordersCountData,
        }],
        xaxis: {
            categories: chartData.value.labels,
            labels: {
                style: {
                    colors: '#999',
                },
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
                formatter: (value) => { return Math.round(value); }
            },
        },
        grid: {
            borderColor: 'rgba(200, 200, 200, 0.1)',
        },
        stroke: {
            width: 2,
            curve: 'smooth',
        },
        colors: ['#3b82f6'], // Синий цвет
        markers: {
            size: 4,
            colors: ['#3b82f6'],
            strokeColors: '#fff',
            strokeWidth: 2,
        },
        tooltip: {
            theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
            x: {
                formatter: (index) => chartData.value.fullDates[index - 1]
            }
        },
    };

    // Опции для графика дохода
    const incomeOptions = {
        chart: {
            type: 'line',
            height: '100%',
            background: 'transparent',
            toolbar: {
                show: false,
            },
        },
        series: [{
            name: 'Доход ($)',
            data: chartData.value.incomeData,
        }],
        xaxis: {
            categories: chartData.value.labels,
            labels: {
                style: {
                    colors: '#999',
                },
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
                formatter: (value) => { return '$' + value; }
            },
        },
        grid: {
            borderColor: 'rgba(200, 200, 200, 0.1)',
        },
        stroke: {
            width: 2,
            curve: 'smooth',
        },
        colors: ['#10b981'], // Зеленый цвет
        markers: {
            size: 4,
            colors: ['#10b981'],
            strokeColors: '#fff',
            strokeWidth: 2,
        },
        tooltip: {
            theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
            x: {
                formatter: (index) => chartData.value.fullDates[index - 1]
            },
            y: {
                formatter: (value) => { return '$' + value; }
            }
        },
    };

    // Опции для графика оборота
    const turnoverOptions = {
        chart: {
            type: 'line',
            height: '100%',
            background: 'transparent',
            toolbar: {
                show: false,
            },
        },
        series: [{
            name: 'Оборот ($)',
            data: chartData.value.turnoverData,
        }],
        xaxis: {
            categories: chartData.value.labels,
            labels: {
                style: {
                    colors: '#999',
                },
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
                formatter: (value) => { return '$' + value; }
            },
        },
        grid: {
            borderColor: 'rgba(200, 200, 200, 0.1)',
        },
        stroke: {
            width: 2,
            curve: 'smooth',
        },
        colors: ['#8b5cf6'], // Фиолетовый цвет
        markers: {
            size: 4,
            colors: ['#8b5cf6'],
            strokeColors: '#fff',
            strokeWidth: 2,
        },
        tooltip: {
            theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
            x: {
                formatter: (index) => chartData.value.fullDates[index - 1]
            },
            y: {
                formatter: (value) => { return '$' + value; }
            }
        },
    };

    // Уничтожаем предыдущие графики, если они существуют
    if (ordersChart.value && ordersChart.value.chart) {
        ordersChart.value.chart.destroy();
    }
    if (incomeChart.value && incomeChart.value.chart) {
        incomeChart.value.chart.destroy();
    }
    if (turnoverChart.value && turnoverChart.value.chart) {
        turnoverChart.value.chart.destroy();
    }

    // Создаем новые графики
    if (document.getElementById('orders-chart')) {
        ordersChart.value = new ApexCharts(document.getElementById('orders-chart'), ordersOptions);
        ordersChart.value.render();
    }

    if (document.getElementById('income-chart')) {
        incomeChart.value = new ApexCharts(document.getElementById('income-chart'), incomeOptions);
        incomeChart.value.render();
    }

    if (document.getElementById('turnover-chart')) {
        turnoverChart.value = new ApexCharts(document.getElementById('turnover-chart'), turnoverOptions);
        turnoverChart.value.render();
    }
};

// Рендерим графики при монтировании компонента
onMounted(() => {
    renderCharts();
});
</script>

<template>
    <section>
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-2xl font-semibold dark:text-white">Статистика за {{ currentWeekRange }}</h3>
            <div class="flex items-center space-x-4">
                <button @click="prevWeek" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white">
                    <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <button @click="nextWeek" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white">
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
                <div id="orders-chart" class="h-40"></div>
            </div>

            <!-- График дохода -->
            <div class="bg-white dark:bg-gray-800 shadow-md p-6 rounded-plate">
                <h4 class="text-lg font-medium mb-4 dark:text-white">Доход ($)</h4>
                <div id="income-chart" class="h-40"></div>
            </div>

            <!-- График оборота -->
            <div class="bg-white dark:bg-gray-800 shadow-md p-6 rounded-plate">
                <h4 class="text-lg font-medium mb-4 dark:text-white">Оборот ($)</h4>
                <div id="turnover-chart" class="h-40"></div>
            </div>
        </div>
    </section>
</template>
