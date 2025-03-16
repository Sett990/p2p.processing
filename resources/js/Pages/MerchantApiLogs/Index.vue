<script setup>
import {Head, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import DateTime from "@/Components/DateTime.vue";
import {ref} from "vue";
import FiltersPanel from "@/Components/Filters/FiltersPanel.vue";
import InputFilter from "@/Components/Filters/Pertials/InputFilter.vue";
import StatusesFilter from "@/Components/Filters/Pertials/StatusesFilter.vue";
import FilterCheckbox from "@/Components/Filters/Pertials/FilterCheckbox.vue";
import DisplayUUID from "@/Components/DisplayUUID.vue";

const logs = usePage().props.logs;
const filters = ref(usePage().props.filters);
const filtersVariants = ref(usePage().props.filtersVariants);
const failedTotal = usePage().props.failedTotal;
const failedToday = usePage().props.failedToday;
const successTotal = usePage().props.successTotal;
const successToday = usePage().props.successToday;
const sumBySuccessCurrencyToday = usePage().props.sumBySuccessCurrencyToday;
const sumByFailedCurrencyToday = usePage().props.sumByFailedCurrencyToday;
const sumBySuccessCurrencyTotal = usePage().props.sumBySuccessCurrencyTotal;
const sumByFailedCurrencyTotal = usePage().props.sumByFailedCurrencyTotal;

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="API логи" />

        <MainTableSection
            title="API логи мерчантов"
            :data="logs"
            :query-data="{filters}"
        >
            <template v-slot:header>
                <div>
                    <FiltersPanel name="merchant-api-logs" :filters="filters">
                        <InputFilter
                            v-model="filters.merchant"
                            placeholder="Мерчант"
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
                            placeholder="Метод"
                        />
                        <StatusesFilter
                            v-model="filters.status"
                            :statuses-variants="filtersVariants.statusVariants"
                        />
                    </FiltersPanel>
                </div>
            </template>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow">
                    <h3 class="text-lg font-semibold mb-2 text-gray-700 dark:text-gray-300">Успешные запросы</h3>
                    <div class="flex justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Сегодня</p>
                            <p class="text-xl font-bold text-green-600">{{ successToday }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Всего</p>
                            <p class="text-xl font-bold text-green-600">{{ successTotal }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow">
                    <h3 class="text-lg font-semibold mb-2 text-gray-700 dark:text-gray-300">Неудачные запросы</h3>
                    <div class="flex justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Сегодня</p>
                            <p class="text-xl font-bold text-red-600">{{ failedToday }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Всего</p>
                            <p class="text-xl font-bold text-red-600">{{ failedTotal }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow">
                    <h3 class="text-lg font-semibold mb-2 text-gray-700 dark:text-gray-300">Успешные суммы</h3>
                    <div class="flex flex-col gap-1">
                        <div v-for="(amount, currency) in sumBySuccessCurrencyToday" :key="currency" class="flex justify-between">
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ currency }} (сегодня)</p>
                            <p class="text-sm font-bold text-green-600">{{ amount }}</p>
                        </div>
                        <div v-for="(amount, currency) in sumBySuccessCurrencyTotal" :key="currency" class="flex justify-between">
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ currency }} (всего)</p>
                            <p class="text-sm font-bold text-green-600">{{ amount }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow">
                    <h3 class="text-lg font-semibold mb-2 text-gray-700 dark:text-gray-300">Неудачные суммы</h3>
                    <div class="flex flex-col gap-1">
                        <div v-for="(amount, currency) in sumByFailedCurrencyToday" :key="currency" class="flex justify-between">
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ currency }} (сегодня)</p>
                            <p class="text-sm font-bold text-red-600">{{ amount }}</p>
                        </div>
                        <div v-for="(amount, currency) in sumByFailedCurrencyTotal" :key="currency" class="flex justify-between">
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ currency }} (всего)</p>
                            <p class="text-sm font-bold text-red-600">{{ amount }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="relative overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">ID</th>
                        <th scope="col" class="px-6 py-3">Мерчант</th>
                        <th scope="col" class="px-6 py-3">Внешний ID</th>
                        <th scope="col" class="px-6 py-3">Сделка</th>
                        <th scope="col" class="px-6 py-3">Сумма</th>
                        <th scope="col" class="px-6 py-3">Валюта</th>
                        <th scope="col" class="px-6 py-3">Метод</th>
                        <th scope="col" class="px-6 py-3">Статус</th>
                        <th scope="col" class="px-6 py-3">Дата</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="log in logs.data" :key="log.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                        <td class="px-6 py-4">{{ log.id }}</td>
                        <td class="px-6 py-4">
                            {{ log.merchant.name }}
                            <div class="text-xs text-gray-500">
                                <DisplayUUID :uuid="log.merchant.uuid" />
                            </div>
                        </td>
                        <td class="px-6 py-4">{{ log.external_id || '-' }}</td>
                        <td class="px-6 py-4">
                            <div v-if="log.order">
                                <DisplayUUID :uuid="log.order.uuid" />
                            </div>
                            <div v-else>-</div>
                        </td>
                        <td class="px-6 py-4">{{ log.amount || '-' }}</td>
                        <td class="px-6 py-4">{{ log.currency || '-' }}</td>
                        <td class="px-6 py-4">{{ log.payment_gateway || '-' }}</td>
                        <td class="px-6 py-4">
                            <span v-if="log.is_successful" class="bg-green-100 text-green-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">
                                Успешно
                            </span>
                            <span v-else class="bg-red-100 text-red-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300">
                                Ошибка
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <DateTime :date="log.created_at" />
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </MainTableSection>
    </div>
</template>

<style scoped>
.cursor-pointer {
    cursor: pointer;
}
</style>
