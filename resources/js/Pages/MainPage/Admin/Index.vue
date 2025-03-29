<script setup>
import {Head, usePage, router} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {ref, onMounted, computed} from 'vue';
import ApexCharts from 'apexcharts';

const statistics = usePage().props.statistics;
const chartData = usePage().props.chart;
const conversionChartData = usePage().props.conversionChart;
const hourlyConversionChartData = usePage().props.hourlyConversionChart;
const merchants = usePage().props.merchants;
const selectedMerchantId = usePage().props.selectedMerchantId;

const selectedMerchant = ref(selectedMerchantId || '');
const processing = ref(false);

const chart = ref(null);
const conversionChart = ref(null);
const hourlyConversionChart = ref(null);
const apexChart = ref(null);
const conversionApexChart = ref(null);
const hourlyConversionApexChart = ref(null);

// Функция для перерисовки графиков после обновления данных
const updateCharts = () => {
    if (apexChart.value) {
        apexChart.value.updateSeries([{
            name: 'Доходы ($)',
            data: chartData.data,
        }]);
    }

    if (conversionApexChart.value) {
        conversionApexChart.value.updateSeries([{
            name: 'Конверсия (%)',
            data: conversionChartData.data,
        }]);
    }

    if (hourlyConversionApexChart.value) {
        hourlyConversionApexChart.value.updateSeries([{
            name: 'Конверсия по часам (%)',
            data: hourlyConversionChartData.data,
        }]);
    }
};

// Функция для обновления статистики при нажатии на кнопку "Применить"
const applyFilter = () => {
    processing.value = true;

    // Используем location.href для добавления параметра в URL
    const baseUrl = route('admin.main.index');
    const url = selectedMerchant.value
        ? `${baseUrl}?merchant_id=${selectedMerchant.value}`
        : baseUrl;

    window.location.href = url;
};

const formatNumber = (num) => { //TODO move to utils
    // Округляем до двух знаков после запятой, если есть дробная часть
    const roundedNum = Math.round(num * 100) / 100;

    // Форматируем число с разделителями тысяч
    return roundedNum.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

const statisticsFormated = computed(() => {
    return {
        totalTurnover: formatNumber(statistics.totalTurnover),
        totalProfit: formatNumber(statistics.totalProfit),
        successOrderCount: statistics.successOrderCount,
        failedOrderCount: statistics.failedOrderCount,
        conversionRate: statistics.conversionRate,
    }
});

onMounted(() => {
    // График доходов
    const options = {
        chart: {
            type: 'line',
            height: '100%',
            background: 'transparent',
            toolbar: {
                show: false,
            },
        },
        series: [{
            name: 'Доходы ($)',
            data: chartData.data,
        }],
        xaxis: {
            categories: chartData.labels, // Дни месяца
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
            },
        },
        grid: {
            borderColor: 'rgba(200, 200, 200, 0.1)',
        },
        stroke: {
            width: 2,
            curve: 'smooth',
        },
        colors: ['#6366f1'],
        markers: {
            size: 4,
            colors: ['#6366f1'],
            strokeColors: '#fff',
            strokeWidth: 2,
        },
        tooltip: {
            theme: 'dark',
        },
    };

    apexChart.value = new ApexCharts(chart.value, options);
    apexChart.value.render();

    // График конверсии
    const conversionOptions = {
        chart: {
            type: 'line',
            height: '100%',
            background: 'transparent',
            toolbar: {
                show: false,
            },
        },
        series: [{
            name: 'Конверсия (%)',
            data: conversionChartData.data,
        }],
        xaxis: {
            categories: conversionChartData.labels, // Дни месяца
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
                formatter: function (value) {
                    return value + '%';
                }
            },
            min: 0,
            max: 100,
        },
        grid: {
            borderColor: 'rgba(200, 200, 200, 0.1)',
        },
        stroke: {
            width: 2,
            curve: 'smooth',
        },
        colors: ['#10b981'],
        markers: {
            size: 4,
            colors: ['#10b981'],
            strokeColors: '#fff',
            strokeWidth: 2,
        },
        tooltip: {
            theme: 'dark',
            y: {
                formatter: function(value) {
                    return value + '%';
                }
            }
        },
    };

    conversionApexChart.value = new ApexCharts(conversionChart.value, conversionOptions);
    conversionApexChart.value.render();

    // График конверсии за 24 часа
    const hourlyConversionOptions = {
        chart: {
            type: 'line',
            height: '100%',
            background: 'transparent',
            toolbar: {
                show: false,
            },
        },
        series: [{
            name: 'Конверсия по часам (%)',
            data: hourlyConversionChartData.data,
        }],
        xaxis: {
            categories: hourlyConversionChartData.labels, // Часы (0-23)
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
                formatter: function (value) {
                    return value + '%';
                }
            },
            min: 0,
            max: 100,
        },
        grid: {
            borderColor: 'rgba(200, 200, 200, 0.1)',
        },
        stroke: {
            width: 2,
            curve: 'smooth',
        },
        colors: ['#9333ea'], // Фиолетовый цвет
        markers: {
            size: 4,
            colors: ['#9333ea'], // Фиолетовый цвет
            strokeColors: '#fff',
            strokeWidth: 2,
        },
        tooltip: {
            theme: 'dark',
            y: {
                formatter: function(value) {
                    return value + '%';
                }
            }
        },
    };

    hourlyConversionApexChart.value = new ApexCharts(hourlyConversionChart.value, hourlyConversionOptions);
    hourlyConversionApexChart.value.render();
});



defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Главная"/>

        <div class="mx-auto space-y-6">
            <div class="flex justify-between items-center">
                <h2 class="text-xl text-gray-900 dark:text-white sm:text-4xl">Главная</h2>
                <slot name="button"></slot>
            </div>

            <!-- Фильтр по мерчантам -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-plate shadow-md max-w-md">
                <h3 class="text-md font-semibold mb-3 dark:text-white">Фильтрация данных</h3>
                <div class="flex items-center space-x-3">
                    <div class="flex-grow">
                        <select
                            id="merchant-filter"
                            v-model="selectedMerchant"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        >
                            <option value="">Все мерчанты</option>
                            <option v-for="merchant in merchants" :key="merchant.id" :value="merchant.id">
                                {{ merchant.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <button
                            @click="applyFilter"
                            :disabled="processing"
                            class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-opacity-75 disabled:opacity-50 whitespace-nowrap"
                        >
                            {{ processing ? 'Загрузка...' : 'Применить' }}
                        </button>
                    </div>
                </div>
            </div>

            <div>
                <section>
                    <!-- Карточки статистики -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 2xl:grid-cols-3 gap-6">
                        <!-- Заработано -->
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-plate shadow-md">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400">Оборот</p>
                                    <p class="text-2xl font-bold dark:text-white">${{ statisticsFormated.totalTurnover }}</p>
                                </div>
                                <div class="bg-green-100 dark:bg-green-900 p-3 rounded-full">
                                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Выплачено -->
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-plate shadow-md">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400">Доход</p>
                                    <p class="text-2xl font-bold dark:text-white">${{ statisticsFormated.totalProfit }}</p>
                                </div>
                                <div class="bg-blue-100 dark:bg-blue-900 p-3 rounded-full">
                                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Сделки -->
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-plate shadow-md">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400">Сделки</p>
                                    <p class="text-2xl font-bold dark:text-white">{{ statisticsFormated.successOrderCount }}</p>
                                </div>
                                <div class="bg-yellow-100 dark:bg-yellow-900 p-3 rounded-full">
                                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- График доходов -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-plate shadow-md mt-8">
                        <h2 class="text-xl font-bold mb-4 dark:text-white">График доходов за месяц</h2>
                        <div ref="chart" class="h-100"></div>
                    </div>

                    <!-- Панель конверсии -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 2xl:grid-cols-3 gap-6 mt-8">
                        <!-- Успешные сделки -->
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-plate shadow-md">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400">Успешные сделки</p>
                                    <p class="text-2xl font-bold dark:text-white">{{ statisticsFormated.successOrderCount }}</p>
                                </div>
                                <div class="bg-green-100 dark:bg-green-900 p-3 rounded-full">
                                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Неуспешные сделки -->
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-plate shadow-md">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400">Неуспешные сделки</p>
                                    <p class="text-2xl font-bold dark:text-white">{{ statisticsFormated.failedOrderCount }}</p>
                                </div>
                                <div class="bg-red-100 dark:bg-red-900 p-3 rounded-full">
                                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Конверсия -->
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-plate shadow-md">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400">Конверсия</p>
                                    <p class="text-2xl font-bold dark:text-white">{{ statisticsFormated.conversionRate }}</p>
                                </div>
                                <div class="bg-purple-100 dark:bg-purple-900 p-3 rounded-full">
                                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- График конверсии -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-plate shadow-md mt-8">
                        <h2 class="text-xl font-bold mb-4 dark:text-white">График конверсии за месяц</h2>
                        <div ref="conversionChart" class="h-100"></div>
                    </div>

                    <!-- График конверсии за 24 часа -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-plate shadow-md mt-8">
                        <h2 class="text-xl font-bold mb-4 dark:text-white">График конверсии за 24 часа</h2>
                        <div ref="hourlyConversionChart" class="h-100"></div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>
