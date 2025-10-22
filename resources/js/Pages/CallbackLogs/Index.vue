<script setup>
import {Head} from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import {ref} from "vue";
import DisplayUUID from "@/Components/DisplayUUID.vue";
import DateTime from "@/Components/DateTime.vue";
import FiltersPanel from "@/Components/Filters/FiltersPanel.vue";
import InputFilter from "@/Components/Filters/Pertials/InputFilter.vue";

const props = defineProps({
    logs: Object,
    filters: Object,
});

// Состояние для отслеживания развернутых строк
const expandedRows = ref({});

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
            <template v-slot:table-filters>
                <div>
                    <FiltersPanel name="callback-logs">
                        <InputFilter
                            name="uuid"
                            placeholder="UUID сделки"
                        />
                        <InputFilter
                            name="merchant"
                            placeholder="Мерчант (имя или uuid)"
                        />
                    </FiltersPanel>
                </div>
            </template>

            <template v-slot:body>
                <div class="overflow-x-auto card bg-base-100 shadow">
                    <table class="table table-sm">
                        <thead class="text-xs uppercase bg-base-300">
                            <tr>
                                <th scope="col" class="px-6 py-3">
                                    ID
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Тип
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    UUID сделки
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    URL
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    HTTP код
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
                                    class="hover cursor-pointer"
                                    @click.stop="toggleRow(log.id)"
                                >
                                    <th scope="row" class="px-6 py-3 font-medium whitespace-nowrap">
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
                                        <span :class="log.status_code && log.status_code >= 200 && log.status_code < 300 ? 'badge badge-success' : 'badge badge-error'">
                                            {{ log.status_code || '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3">
                                        <span :class="log.is_success ? 'badge badge-success' : 'badge badge-error'">
                                            {{ log.is_success ? 'Успешно' : 'Ошибка' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3">
                                        <DateTime :data="log.created_at" show-time />
                                    </td>
                                </tr>

                                <!-- Развернутая информация -->
                                <tr v-if="expandedRows[log.id]" class="bg-base-200">
                                    <td colspan="7" class="px-6 py-4">
                                        <h4 class="font-semibold mb-2">Детали</h4>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div v-if="log.request_data" class="mb-4">
                                                <div class="opacity-70 mb-1">Данные запроса:</div>
                                                <pre class="bg-base-100 p-2 rounded overflow-auto max-h-40 text-xs">{{ JSON.stringify(log.request_data, null, 2) }}</pre>
                                            </div>

                                            <div v-if="log.response_data">
                                                <div class="opacity-70 mb-1">Данные ответа:</div>
                                                <pre class="bg-base-100 p-2 rounded overflow-auto max-h-40 text-xs">{{ JSON.stringify(log.response_data, null, 2) }}</pre>
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

<style scoped>
.cursor-pointer {
    cursor: pointer;
}
</style>
