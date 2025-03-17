<script setup>
import {Head, usePage} from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import {useViewStore} from "@/store/view.js";
import {useModalStore} from "@/store/modal.js";
import DateTime from "@/Components/DateTime.vue";
import InputFilter from "@/Components/Filters/Pertials/InputFilter.vue";
import FiltersPanel from "@/Components/Filters/FiltersPanel.vue";
import {ref} from "vue";
import DisplayUUID from "@/Components/DisplayUUID.vue";
import StatusesFilter from "@/Components/Filters/Pertials/StatusesFilter.vue";

const viewStore = useViewStore();
const modalStore = useModalStore();

const logs = usePage().props.logs;
const filters = ref(usePage().props.filters);
const filtersVariants = ref(usePage().props.filtersVariants);
const expandedRows = ref({}); // Для отслеживания развернутых строк

// Получение статистических данных из props
const failedTotal = usePage().props.failedTotal;
const failedToday = usePage().props.failedToday;
const successTotal = usePage().props.successTotal;
const successToday = usePage().props.successToday;
const sumBySuccessCurrencyToday = usePage().props.sumBySuccessCurrencyToday;
const sumByFailedCurrencyToday = usePage().props.sumByFailedCurrencyToday;
const sumBySuccessCurrencyTotal = usePage().props.sumBySuccessCurrencyTotal;
const sumByFailedCurrencyTotal = usePage().props.sumByFailedCurrencyTotal;

// Функция для форматирования чисел
const formatNumber = (num) => {
    if (num === undefined || num === null) return '0';
    // Округляем до двух знаков после запятой, если есть дробная часть
    const roundedNum = Math.round(num * 100) / 100;

    // Форматируем число с разделителями тысяч
    return roundedNum.toLocaleString('ru-RU', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

// Функция для переключения состояния развернутой строки
const toggleRow = (logId) => {
    expandedRows.value[logId] = !expandedRows.value[logId];
};

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Логи API-запросов" />

        <MainTableSection
            title="Логи API-запросов"
            :data="logs"
            :query-data="{filters}"
        >
            <template v-slot:table-filters>
                <div>
                    <FiltersPanel name="merchant-api-logs" :filters="filters">
                        <InputFilter
                            v-model="filters.merchant"
                            placeholder="Мерчант (имя или uuid)"
                        />
                        <InputFilter
                            v-model="filters.externalID"
                            placeholder="Внешний ID"
                        />
                        <InputFilter
                            v-model="filters.amount"
                            placeholder="Сумма"
                        />
                        <InputFilter
                            v-model="filters.currency"
                            placeholder="Валюта"
                        />
                        <InputFilter
                            v-model="filters.method"
                            placeholder="Метод (код)"
                        />
                        <StatusesFilter
                            v-model="filters.apiLogStatuses"
                            :statuses-variants="filtersVariants.apiLogStatuses"
                        />
                    </FiltersPanel>
                </div>
            </template>

            <template v-slot:body>
                <!-- Панель статистики -->
                <div class="mb-6">
                    <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">Статистика запросов</h2>

                    <!-- Карточки статистики -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Успешные запросы сегодня -->
                        <div class="bg-white dark:bg-gray-800 p-4 rounded-plate shadow-md">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400">Успешно сегодня</p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ successToday }}</p>
                                </div>
                                <div class="bg-green-100 dark:bg-green-900 p-3 rounded-full">
                                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Неудачные запросы сегодня -->
                        <div class="bg-white dark:bg-gray-800 p-4 rounded-plate shadow-md">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400">Ошибок сегодня</p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ failedToday }}</p>
                                </div>
                                <div class="bg-red-100 dark:bg-red-900 p-3 rounded-full">
                                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Успешные запросы всего -->
                        <div class="bg-white dark:bg-gray-800 p-4 rounded-plate shadow-md">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400">Успешно всего</p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ successTotal }}</p>
                                </div>
                                <div class="bg-green-100 dark:bg-green-900 p-3 rounded-full">
                                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Неудачные запросы всего -->
                        <div class="bg-white dark:bg-gray-800 p-4 rounded-plate shadow-md">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400">Ошибок всего</p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ failedTotal }}</p>
                                </div>
                                <div class="bg-red-100 dark:bg-red-900 p-3 rounded-full">
                                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Суммы по валютам -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <!-- Суммы успешных запросов -->
                        <div class="bg-white dark:bg-gray-800 p-4 rounded-plate shadow-md">
                            <h3 class="text-lg font-semibold mb-3 text-gray-900 dark:text-white">Суммы успешных запросов</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Сегодня</h4>
                                    <div class="space-y-2">
                                        <div v-for="(amount, currency) in sumBySuccessCurrencyToday" :key="'success-today-' + currency" class="flex justify-between">
                                            <span class="text-gray-700 dark:text-gray-300">{{ currency.toUpperCase() }}</span>
                                            <span class="font-medium text-gray-900 dark:text-white">{{ formatNumber(amount) }}</span>
                                        </div>
                                        <div v-if="Object.keys(sumBySuccessCurrencyToday).length === 0" class="text-gray-500 dark:text-gray-400">
                                            Нет данных
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Всего</h4>
                                    <div class="space-y-2">
                                        <div v-for="(amount, currency) in sumBySuccessCurrencyTotal" :key="'success-total-' + currency" class="flex justify-between">
                                            <span class="text-gray-700 dark:text-gray-300">{{ currency.toUpperCase() }}</span>
                                            <span class="font-medium text-gray-900 dark:text-white">{{ formatNumber(amount) }}</span>
                                        </div>
                                        <div v-if="Object.keys(sumBySuccessCurrencyTotal).length === 0" class="text-gray-500 dark:text-gray-400">
                                            Нет данных
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Суммы неудачных запросов -->
                        <div class="bg-white dark:bg-gray-800 p-4 rounded-plate shadow-md">
                            <h3 class="text-lg font-semibold mb-3 text-gray-900 dark:text-white">Суммы неудачных запросов</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Сегодня</h4>
                                    <div class="space-y-2">
                                        <div v-for="(amount, currency) in sumByFailedCurrencyToday" :key="'failed-today-' + currency" class="flex justify-between">
                                            <span class="text-gray-700 dark:text-gray-300">{{ currency.toUpperCase() }}</span>
                                            <span class="font-medium text-gray-900 dark:text-white">{{ formatNumber(amount) }}</span>
                                        </div>
                                        <div v-if="Object.keys(sumByFailedCurrencyToday).length === 0" class="text-gray-500 dark:text-gray-400">
                                            Нет данных
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Всего</h4>
                                    <div class="space-y-2">
                                        <div v-for="(amount, currency) in sumByFailedCurrencyTotal" :key="'failed-total-' + currency" class="flex justify-between">
                                            <span class="text-gray-700 dark:text-gray-300">{{ currency.toUpperCase() }}</span>
                                            <span class="font-medium text-gray-900 dark:text-white">{{ formatNumber(amount) }}</span>
                                        </div>
                                        <div v-if="Object.keys(sumByFailedCurrencyTotal).length === 0" class="text-gray-500 dark:text-gray-400">
                                            Нет данных
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-x-auto shadow-md rounded-table">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">
                                    ID
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Мерчант
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Сделка
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Внешний ID
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Сумма
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Метод
                                </th>
                                <th scope="col" class="px-6 py-3 text-nowrap">
                                    Тип реквизита
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Статус
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Создан
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-for="log in logs.data" :key="log.id">
                                <tr
                                    class="bg-white dark:bg-gray-800 border-b dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/75"
                                    @click.stop="toggleRow(log.id)"
                                >
                                    <th scope="row" class="px-6 py-3 font-medium whitespace-nowrap text-gray-900 dark:text-gray-200">
                                        {{ log.id }}
                                    </th>
                                    <td class="px-6 py-3">
                                        {{ log.merchant.name }}
                                    </td>
                                    <td class="px-6 py-3">
                                        <DisplayUUID v-if="log.order" :uuid="log.order.uuid"/>
                                    </td>
                                    <td class="px-6 py-3">
                                        {{ log.external_id || '-' }}
                                    </td>
                                    <td class="px-6 py-3">
                                        <div v-if="log.amount" class="text-nowrap text-gray-900 dark:text-gray-200">
                                            {{ log.amount }} {{ log.currency?.toUpperCase() }}
                                        </div>
                                        <div v-else>-</div>
                                    </td>
                                    <td class="px-6 py-3">
                                        {{ log.payment_gateway || '-' }}
                                    </td>
                                    <td class="px-6 py-3">
                                        {{ log.payment_detail_type || '-' }}
                                    </td>
                                    <td class="px-6 py-3">
                                        <span
                                            :class="log.is_successful
                                                ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                                                : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'"
                                            class="text-xs font-medium px-2.5 py-0.5 rounded-full"
                                        >
                                            {{ log.is_successful ? 'Успешно' : 'Ошибка' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3">
                                        <DateTime :data="log.created_at"></DateTime>
                                    </td>
                                </tr>
                                <!-- Развернутая информация -->
                                <tr v-if="expandedRows[log.id]" class="bg-gray-50 dark:bg-gray-700">
                                    <td colspan="9" class="px-6 py-4">
                                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Детали</h4>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div v-if="log.request_data" class="mb-4">
                                                <div class="text-gray-700 dark:text-gray-300 mb-1">Данные запроса:</div>
                                                <pre class="bg-gray-100 dark:bg-gray-800 p-2 rounded overflow-auto max-h-40 text-xs">{{ JSON.stringify(log.request_data, null, 2) }}</pre>
                                            </div>

                                            <div v-if="log.response_data">
                                                <div class="text-gray-700 dark:text-gray-300 mb-1">Данные ответа:</div>
                                                <pre class="bg-gray-100 dark:bg-gray-800 p-2 rounded overflow-auto max-h-40 text-xs">{{ JSON.stringify(log.response_data, null, 2) }}</pre>
                                            </div>
                                        </div>
                                        <div class="mt-4 grid grid-cols-2 gap-4">
                                            <div v-if="log.user_agent">
                                                <div class="text-gray-700 dark:text-gray-300 mb-1">User Agent:</div>
                                                <div class="bg-gray-100 dark:bg-gray-800 p-2 rounded overflow-auto max-h-40 text-xs">{{ log.user_agent }}</div>
                                            </div>
                                            <div v-if="log.ip_address">
                                                <div class="text-gray-700 dark:text-gray-300 mb-1">IP адрес:</div>
                                                <div class="bg-gray-100 dark:bg-gray-800 p-2 rounded overflow-auto max-h-40 text-xs">{{ log.ip_address }}</div>
                                            </div>
                                        </div>
                                        <div v-if="log.error_message" class="mt-4">
                                            <div class="text-gray-700 dark:text-gray-300 mb-1">Сообщение об ошибке:</div>
                                            <div class="text-red-600 dark:text-red-400">{{ log.error_message }}</div>
                                        </div>
                                        <div v-if="log.exception_class" class="mt-4">
                                            <div class="text-gray-700 dark:text-gray-300 mb-1">Класс исключения:</div>
                                            <div class="text-red-600 dark:text-red-400">{{ log.exception_class }}</div>
                                        </div>
                                        <div v-if="log.exception_message" class="mt-4">
                                            <div class="text-gray-700 dark:text-gray-300 mb-1">Сообщение исключения:</div>
                                            <div class="text-red-600 dark:text-red-400">{{ log.exception_message }}</div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </template>
        </MainTableSection>
    </div>
</template>

<style scoped>
.cursor-pointer {
    cursor: pointer;
}
</style>
