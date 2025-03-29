<script setup>
import { ref, defineProps, defineEmits } from 'vue';
import PaymentDetailsStats from './PaymentDetailsStats.vue';
import ClosedOrdersTable from './ClosedOrdersTable.vue';

const props = defineProps({
    paymentDetails: {
        type: Object,
        required: true
    },
    closedOrders: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({})
    }
});

const emit = defineEmits(['dateRangeChanged']);

// Активный таб (payment-details или closed-orders)
const activeTab = ref('payment-details');

// Обработка изменения диапазона дат
const handleDateRangeChanged = (dateRange) => {
    emit('dateRangeChanged', dateRange);
};

// Переключение таба
const setActiveTab = (tab) => {
    activeTab.value = tab;
};
</script>

<template>
    <section>
        <!-- Табы для переключения между таблицами -->
        <div class="flex flex-wrap gap-3 mb-6 justify-start">
            <!-- Таб платежных реквизитов -->
            <div 
                class="bg-white dark:bg-gray-800 p-3 rounded-lg shadow-sm cursor-pointer flex items-center gap-3 transition-all"
                :class="{ 'ring-2 ring-purple-500 bg-purple-50 dark:bg-purple-900/20': activeTab === 'payment-details' }"
                @click="setActiveTab('payment-details')"
            >
                <div class="bg-purple-100 dark:bg-purple-900 p-2 rounded-full">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium dark:text-white">Платежные реквизиты</p>
                </div>
            </div>

            <!-- Таб закрытых сделок -->
            <div 
                class="bg-white dark:bg-gray-800 p-3 rounded-lg shadow-sm cursor-pointer flex items-center gap-3 transition-all"
                :class="{ 'ring-2 ring-indigo-500 bg-indigo-50 dark:bg-indigo-900/20': activeTab === 'closed-orders' }"
                @click="setActiveTab('closed-orders')"
            >
                <div class="bg-indigo-100 dark:bg-indigo-900 p-2 rounded-full">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium dark:text-white">Успешные сделки</p>
                </div>
            </div>
        </div>

        <!-- Содержимое табов -->
        <div>
            <!-- Таблица платежных реквизитов -->
            <div v-if="activeTab === 'payment-details'">
                <PaymentDetailsStats 
                    :payment-details="paymentDetails"
                    @date-range-changed="handleDateRangeChanged"
                />
            </div>

            <!-- Таблица закрытых сделок -->
            <div v-if="activeTab === 'closed-orders'">
                <ClosedOrdersTable 
                    :closed-orders="closedOrders" 
                    :filters="filters"
                />
            </div>
        </div>
    </section>
</template> 