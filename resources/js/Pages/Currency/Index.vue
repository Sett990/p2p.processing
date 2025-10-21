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
                <div class="tabs tabs-box bg-base-300 inline-flex">
                    <input type="radio" name="market_tabs" class="tab" aria-label="ByBit" :checked="selectedMarket === 'bybit'" @change="selectedMarket = 'bybit'" />
                    <input type="radio" name="market_tabs" class="tab" aria-label="Rapira" :checked="selectedMarket === 'rapira'" @change="selectedMarket = 'rapira'" />
                </div>
            </template>
            <template v-slot:body>
                <div class="overflow-x-auto card bg-base-100 shadow">
                    <table class="table text-sm">
                        <thead class="text-xs uppercase bg-base-300">
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
                        <tr v-for="currency in currencies" class="">
                            <th scope="row" class="px-6 py-3 font-medium whitespace-nowrap">
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
