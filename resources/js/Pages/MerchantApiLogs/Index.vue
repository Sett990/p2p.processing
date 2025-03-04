<script setup>
import {Head, usePage} from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import {useViewStore} from "@/store/view.js";
import {useModalStore} from "@/store/modal.js";
import DateTime from "@/Components/DateTime.vue";
import ShowAction from "@/Components/Table/ShowAction.vue";
import InputFilter from "@/Components/Filters/Pertials/InputFilter.vue";
import FiltersPanel from "@/Components/Filters/FiltersPanel.vue";
import {ref} from "vue";
import DateFilter from "@/Components/Filters/Pertials/DateFilter.vue";
import DisplayUUID from "@/Components/DisplayUUID.vue";

const viewStore = useViewStore();
const modalStore = useModalStore();

const logs = usePage().props.logs;
const merchants = usePage().props.merchants;
const filters = ref(usePage().props.filters);
const expandedRows = ref({}); // Для отслеживания развернутых строк

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
            <template v-slot:body>
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
