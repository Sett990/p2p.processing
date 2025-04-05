<script setup>
import { ref, defineProps, onMounted, watch } from 'vue';
import PaymentDetailsStats from './PaymentDetailsStats.vue';
import ClosedOrdersTable from './ClosedOrdersTable.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    paymentDetails: {
        type: Object,
        required: true
    },
    closedOrders: {
        type: Object,
        required: true
    },
    initialTableType: {
        type: String,
        default: 'payment-details'
    }
});

const emit = defineEmits(['table-type-changed']);

// Активный таб (payment-details или closed-orders)
const activeTab = ref(props.initialTableType);

// Переключение таба
const setActiveTab = (tab) => {
    activeTab.value = tab;
    emit('table-type-changed', tab);

    // Обновляем URL параметры без перезагрузки страницы
    const urlParams = new URLSearchParams(window.location.search);
    const month = urlParams.get('month') || '';
    const chartType = urlParams.get('chartType') || 'turnover';

    router.visit(route(route().current()), {
        data: {
            month: month,
            chartType: chartType,
            tableType: tab,
            page: 1 // Сбрасываем пагинацию при переключении вкладок
        },
        preserveScroll: true,
        preserveState: false, // Отключаем сохранение состояния для обновления данных
        only: []
    });
};

// Следим за изменением initialTableType из props
watch(() => props.initialTableType, (newType) => {
    if (newType !== activeTab.value) {
        activeTab.value = newType;
    }
});

// При монтировании проверяем URL параметры
onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const tableTypeParam = urlParams.get('tableType');

    if (tableTypeParam && (tableTypeParam === 'payment-details' || tableTypeParam === 'closed-orders')) {
        activeTab.value = tableTypeParam;
    }
});
</script>

<template>
    <section class="space-y-6">
        <!-- Табы для переключения между таблицами -->
        <div class="flex flex-wrap gap-3 justify-start">
            <!-- Таб платежных реквизитов -->
            <div
                class="bg-white dark:bg-gray-800 p-3 rounded-xl shadow-sm cursor-pointer flex items-center gap-3 transition-all"
                :class="{ 'ring-2 ring-purple-500 bg-purple-50 dark:bg-purple-900/20': activeTab === 'payment-details' }"
                @click="setActiveTab('payment-details')"
            >
                <div class="bg-purple-100 dark:bg-purple-900 p-2 rounded-full">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium dark:text-white">Реквизиты</p>
                </div>
            </div>

            <!-- Таб закрытых сделок -->
            <div
                class="bg-white dark:bg-gray-800 p-3 rounded-xl shadow-sm cursor-pointer flex items-center gap-3 transition-all"
                :class="{ 'ring-2 ring-indigo-500 bg-indigo-50 dark:bg-indigo-900/20': activeTab === 'closed-orders' }"
                @click="setActiveTab('closed-orders')"
            >
                <div class="bg-indigo-100 dark:bg-indigo-900 p-2 rounded-full">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium dark:text-white">Сделки</p>
                </div>
            </div>
        </div>

        <!-- Содержимое табов -->
        <div>
            <!-- Таблица платежных реквизитов -->
            <div v-if="activeTab === 'payment-details'">
                <PaymentDetailsStats
                    :payment-details="paymentDetails"
                />
            </div>

            <!-- Таблица закрытых сделок -->
            <div v-if="activeTab === 'closed-orders'">
                <ClosedOrdersTable
                    :closed-orders="closedOrders"
                />
            </div>
        </div>
    </section>
</template>
