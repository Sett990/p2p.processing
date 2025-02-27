<script setup>
import {Head, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EditAction from "@/Components/Table/EditAction.vue";
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import {computed, ref} from "vue";

const markets = usePage().props.markets;

const selectedMarket = ref('bybit');

const currencies = computed(() => {
    return markets[selectedMarket.value];
});

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Валюты" />

        <MainTableSection
            title="Валюты"
            :data="currencies"
            :paginate="false"
        >
            <template v-slot:header>
                <ul class="flex flex-wrap text-sm font-medium text-center text-gray-500 dark:text-gray-400">
                    <li class="me-2">
                        <a @click.prevent="selectedMarket = 'bybit'" href="#" :class="selectedMarket === 'bybit' ? 'shadow inline-flex items-center px-4 py-2 text-white bg-blue-600 rounded-xl active' : 'border border-gray-200 dark:border-gray-700 inline-flex items-center px-4 py-2 rounded-xl hover:text-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-white'" aria-current="page">
                            <span class="sm:block hidden">ByBit</span>
                        </a>
                    </li>
                    <li class="me-2">
                        <a @click.prevent="selectedMarket = 'garantex'" href="#" :class="selectedMarket === 'garantex' ? 'shadow inline-flex items-center px-4 py-2 text-white bg-blue-600 rounded-xl active' : 'border border-gray-200 dark:border-gray-700 inline-flex items-center px-4 py-2 rounded-xl hover:text-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-white'" aria-current="page">
                            <span class="sm:block hidden">Garantex</span>
                        </a>
                    </li>
                </ul>
            </template>
            <template v-slot:body>
                <div class="relative overflow-x-auto shadow-md sm:rounded-table ">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">
                                Код
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Покупка USDT
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Продажа USDT
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Символ
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Название
                            </th>
                            <th scope="col" class="px-6 py-3 flex justify-center">
                                <span class="sr-only">Действия</span>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="currency in currencies" class="bg-white border-b last:border-none dark:bg-gray-800 dark:border-gray-700">
                            <th scope="row" class="px-6 py-3 font-medium whitespace-nowrap text-gray-900 dark:text-gray-200">
                                {{ currency.code.toUpperCase() }}
                            </th>
                            <td class="px-6 py-3 text-nowrap">
                                <span
                                    :class="currency.buy_price === '0.00' ? 'text-red-500' : ''"
                                >
                                    {{ currency.buy_price }}
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                <span
                                    :class="currency.buy_price === '0.00' ? 'text-red-500' : ''"
                                >
                                    {{ currency.sell_price }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-nowrap">
                                {{ currency.symbol }}
                            </td>
                            <td class="px-6 py-3 text-nowrap">
                                {{ currency.name }}
                            </td>
                            <td class="px-6 py-3 text-nowrap text-right">
                                <EditAction v-if="selectedMarket === 'bybit'" :link="route('admin.currencies.price-parsers.edit', currency.code)"></EditAction>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </MainTableSection>
    </div>
</template>
