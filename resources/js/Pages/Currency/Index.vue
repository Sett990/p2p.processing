<script setup>
import {Head, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EditAction from "@/Components/Table/EditAction.vue";
import MainTableSection from "@/Wrappers/MainTableSection.vue";

const currencies = usePage().props.currencies;

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
            <template v-slot:body>
                <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">
                                Код валюты
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Покупка USDT
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Продажа USDT
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
                                {{ currency.buy_price }}
                            </td>
                            <td class="px-6 py-3">
                                {{ currency.sell_price }}
                            </td>
                            <td class="px-6 py-3 text-nowrap text-right">
                                <EditAction :link="route('admin.currencies.price-parsers.edit', currency.code)"></EditAction>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </MainTableSection>
    </div>
</template>
