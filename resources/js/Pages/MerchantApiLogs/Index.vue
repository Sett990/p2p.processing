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

const viewStore = useViewStore();
const modalStore = useModalStore();

const logs = usePage().props.logs;
const merchants = usePage().props.merchants;
const filters = ref(usePage().props.filters);

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Логи API-запросов" />

        <MainTableSection
            title="Логи API-запросов мерчантов"
            :data="logs"
            :query-data="{filters}"
        >
            <template v-slot:header>
                <div>
                    <FiltersPanel name="merchant-api-logs" :filters="filters">
                        <InputFilter
                            v-model="filters.search"
                            placeholder="Поиск"
                        />
                        <div class="w-48">
                            <select
                                v-model="filters.merchant_id"
                                class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-2.5 h-[38px] dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                            >
                                <option value="">Все мерчанты</option>
                                <option v-for="merchant in merchants" :key="merchant.id" :value="merchant.id">
                                    {{ merchant.name }}
                                </option>
                            </select>
                        </div>
                        <div class="w-48">
                            <select
                                v-model="filters.is_successful"
                                class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-2.5 h-[38px] dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                            >
                                <option value="">Все запросы</option>
                                <option value="1">Успешные</option>
                                <option value="0">Неуспешные</option>
                            </select>
                        </div>
                        <DateFilter v-model="filters.date_from" title="Дата с"/>
                        <DateFilter v-model="filters.date_to" title="Дата по"/>
                    </FiltersPanel>
                </div>
            </template>
            <template v-slot:body>
                <div class="relative overflow-x-auto shadow-md sm:rounded-table">
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
                                    Внешний ID
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Сумма
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Платежный шлюз
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Статус
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Создан
                                </th>
                                <th scope="col" class="px-6 py-3 flex justify-center">
                                    <span class="sr-only">Действия</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="log in logs.data" class="bg-white border-b last:border-none dark:bg-gray-800 dark:border-gray-700">
                                <th scope="row" class="px-6 py-3 font-medium whitespace-nowrap text-gray-900 dark:text-gray-200">
                                    {{ log.id }}
                                </th>
                                <td class="px-6 py-3">
                                    {{ log.merchant.name }}
                                </td>
                                <td class="px-6 py-3">
                                    {{ log.external_id || '-' }}
                                </td>
                                <td class="px-6 py-3">
                                    <div v-if="log.amount" class="text-nowrap text-gray-900 dark:text-gray-200">
                                        {{ log.amount }} {{ log.currency }}
                                    </div>
                                    <div v-else>-</div>
                                </td>
                                <td class="px-6 py-3">
                                    {{ log.payment_gateway || '-' }}
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
                                <td class="px-6 py-3 text-right">
                                    <ShowAction :link="route('admin.merchant-api-logs.show', log.id)"></ShowAction>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </MainTableSection>
    </div>
</template>

<style scoped>

</style>
