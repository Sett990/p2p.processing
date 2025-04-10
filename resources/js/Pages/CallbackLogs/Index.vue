<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head} from '@inertiajs/vue3';
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import {ref} from 'vue';
import DisplayUUID from "@/Components/DisplayUUID.vue";
import DateTime from '@/Components/DateTime.vue';

const props = defineProps({
    logs: Object,
});

// Состояние для отслеживания развернутых строк
const expandedRows = ref({});

// Форматирование статус-кода
const getStatusCodeClass = (statusCode) => {
    if (!statusCode) return '';
    if (statusCode >= 200 && statusCode < 300) return 'text-green-600 dark:text-green-400';
    return 'text-red-600 dark:text-red-400';
};

// Функция для переключения состояния развернутой строки
const toggleRow = (logId) => {
    expandedRows.value[logId] = !expandedRows.value[logId];
};

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Логи колбеков" />

        <MainTableSection
            title="Логи колбеков"
            :data="logs"
        >
            <template v-slot:body>
                <div class="relative overflow-x-auto">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">
                                    ID
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Тип
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    UUID
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    URL
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Статус код
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Статус
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Дата создания
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
                                        {{ log.type }}
                                    </td>
                                    <td class="px-6 py-3">
                                        <DisplayUUID v-if="log.callbackable" :uuid="log.callbackable.uuid" />
                                        <span v-else>-</span>
                                    </td>
                                    <td class="px-6 py-3 max-w-64 truncate">
                                        {{ log.url }}
                                    </td>
                                    <td class="px-6 py-3">
                                        <span :class="getStatusCodeClass(log.status_code)">
                                            {{ log.status_code || '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3">
                                        <span v-if="log.is_success" class="text-green-600 dark:text-green-400">
                                            Успешно
                                        </span>
                                        <span v-else class="text-red-600 dark:text-red-400">
                                            Ошибка
                                        </span>
                                    </td>
                                    <td class="px-6 py-3">
                                        <DateTime :value="log.created_at" show-time/>
                                    </td>
                                </tr>

                                <!-- Развернутая информация -->
                                <tr v-if="expandedRows[log.id]" class="bg-gray-50 dark:bg-gray-700">
                                    <td colspan="7" class="px-6 py-4">
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
