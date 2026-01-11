<script setup>
import {Head, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import {computed, ref} from "vue";
import { useModalStore } from "@/store/modal.js";
import PriceParserEditModal from "@/Modals/Currency/PriceParserEditModal.vue";

const markets = usePage().props.markets;

const selectedMarket = ref('bybit');

const currencies = computed(() => {
    return markets[selectedMarket.value];
});

const MARKET_INFO = {
    rapira: [
        { text: 'Данные берём напрямую из торгового стакана Rapira.' },
        { text: 'Цена покупки (зелёный стакан) — это самая верхняя, первая запись стакана (best bid).' },
        { text: 'Цена продажи (красный стакан) — это самая нижняя запись стакана (best ask).' },
        {
            text: 'Стакан USDT/RUB на Rapira',
            href: 'https://rapira.net/exchange/USDT_RUB',
        },
    ],
};

const marketInfo = computed(() => MARKET_INFO[selectedMarket.value] ?? null);

const modalStore = useModalStore();

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
                <div>
                    <ul class="flex flex-wrap text-sm font-medium text-center">
                        <li class="me-2">
                            <a @click.prevent="selectedMarket = 'bybit'" href="#" :class="selectedMarket === 'bybit' ? 'btn btn-sm btn-primary' : 'btn btn-sm btn-outline'" aria-current="page">
                                <span>ByBit</span>
                            </a>
                        </li>
                        <li class="me-2">
                            <a @click.prevent="selectedMarket = 'rapira'" href="#" :class="selectedMarket === 'rapira' ? 'btn btn-sm btn-primary' : 'btn btn-sm btn-outline'" aria-current="page">
                                <span>Rapira</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </template>
            <template v-slot:body>
                <div class="relative space-y-4">
                    <div v-if="marketInfo" class="alert alert-info shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 stroke-current" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="space-y-1 text-sm">
                            <p class="font-medium text-base-content">Информация по рынку</p>
                            <ul class="list-inside list-disc text-base-content/80 space-y-1">
                                <li v-for="info in marketInfo" :key="info.text">
                                    <template v-if="info.href">
                                        <a
                                            :href="info.href"
                                            target="_blank"
                                            rel="noopener"
                                            class="link link-primary"
                                        >
                                            {{ info.text }}
                                        </a>
                                    </template>
                                    <template v-else>
                                        {{ info.text }}
                                    </template>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!-- Desktop/tablet view (table) -->
                    <div class="hidden xl:block">
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
                                        <button
                                            v-if="selectedMarket === 'bybit'"
                                            type="button"
                                            class="btn btn-ghost btn-xs"
                                            @click="modalStore.openPriceParserEditModal({ currency: currency.code })"
                                        >
                                            <svg class="w-[22px] h-[22px] text-success" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.779 17.779 4.36 19.918 6.5 13.5m4.279 4.279 8.364-8.643a3.027 3.027 0 0 0-2.14-5.165 3.03 3.03 0 0 0-2.14.886L6.5 13.5m4.279 4.279L6.499 13.5m2.14 2.14 6.213-6.504M12.75 7.04 17 11.28"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Mobile view (cards list) -->
                    <div class="xl:hidden space-y-3">
                        <div class="space-y-2">
                            <div
                                v-for="currency in currencies"
                                :key="currency.code"
                                class="card bg-base-100 shadow-sm"
                            >
                                <div class="card-body p-4 pt-2 pb-3">
                                    <!-- Шапка: Код валюты и кнопка редактирования -->
                                    <div class="flex justify-between items-center border-b border-base-content/10 mb-1 pb-2">
                                        <div class="inline-flex items-center gap-2">
                                            <span class="text-base-content/70 font-medium text-lg">{{ currency.symbol }}</span>
                                            <span class="text-base-content font-medium text-lg">{{ currency.code.toUpperCase() }}</span>
                                        </div>
                                        <div class="inline-flex items-center">
                                            <button
                                                v-if="selectedMarket === 'bybit'"
                                                type="button"
                                                class="btn btn-ghost btn-xs"
                                                @click="modalStore.openPriceParserEditModal({ currency: currency.code })"
                                            >
                                                <svg class="w-[22px] h-[22px] text-success" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.779 17.779 4.36 19.918 6.5 13.5m4.279 4.279 8.364-8.643a3.027 3.027 0 0 0-2.14-5.165 3.03 3.03 0 0 0-2.14.886L6.5 13.5m4.279 4.279L6.499 13.5m2.14 2.14 6.213-6.504M12.75 7.04 17 11.28"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="flex flex-col gap-2">
                                        <div class="flex items-center justify-between">
                                            <div class="text-base-content/70 text-sm">Покупка USDT</div>
                                            <div>
                                                <span
                                                    :class="currency.buy_price === '0.00' ? 'text-red-500' : 'text-base-content'"
                                                    class="text-nowrap font-medium"
                                                >
                                                    {{ currency.buy_price }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <div class="text-base-content/70 text-sm">Продажа USDT</div>
                                            <div>
                                                <span
                                                    :class="currency.sell_price === '0.00' ? 'text-red-500' : 'text-base-content'"
                                                    class="text-nowrap font-medium"
                                                >
                                                    {{ currency.sell_price }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </MainTableSection>
        <PriceParserEditModal/>
    </div>
</template>
