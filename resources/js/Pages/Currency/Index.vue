<script setup>
import {Head, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import {computed, ref, watch} from "vue";
import { useModalStore } from "@/store/modal.js";
import PriceParserEditModal from "@/Modals/Currency/PriceParserEditModal.vue";

const markets = usePage().props.markets;

const selectedMarket = ref('bybit');

const currencies = computed(() => {
    return markets[selectedMarket.value];
});

const MARKET_INFO = {
    bybit: [
        { text: 'Парсер Bybit использует независимые фильтры для двух стаканов:' },
        { text: 'для покупки объявления сортируются от меньшей цены к большей;' },
        { text: 'для продажи данные берутся от большей цены к меньшей;' },
        { text: 'Все остальные параметры задаются вручную в модальном окне настроек Bybit.' },
        {
            text: 'Bybit P2P',
            href: 'https://www.bybit.com/en/p2p/buy/USDT/RUB'
        },
    ],
    rapira: [
        { text: 'Данные берём напрямую из торгового стакана Rapira.' },
        { text: 'Цена покупки (зелёный стакан) — используем самую верхнюю, первую запись.' },
        { text: 'Цена продажи (красный стакан) — берём самую нижнюю запись стакана.' },
        {
            text: 'Стакан USDT/RUB на Rapira',
            href: 'https://rapira.net/exchange/USDT_RUB',
        },
    ],
};

const marketInfo = computed(() => MARKET_INFO[selectedMarket.value] ?? null);

const modalStore = useModalStore();
const marketInfoModal = ref(null);

const openMarketInfoModal = () => {
    marketInfoModal.value?.showModal();
};

watch(selectedMarket, () => {
    if (marketInfoModal.value?.open) {
        marketInfoModal.value.close();
    }
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
                    <div class="flex justify-end">
                        <button
                            v-if="marketInfo"
                            type="button"
                            class="btn btn-info btn-sm"
                            @click="openMarketInfoModal"
                        >
                            Информация по парсингу
                        </button>
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
        <dialog
            v-if="marketInfo"
            ref="marketInfoModal"
            class="modal modal-bottom sm:modal-middle"
            tabindex="0"
        >
            <div class="modal-box space-y-3">
                <h3 class="font-bold text-lg">Информация по парсингу</h3>
                <ul class="list-disc list-inside text-base-content/80 space-y-1">
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
                <div class="modal-action">
                    <form method="dialog">
                        <button type="submit" class="btn btn-primary btn-sm">Понятно</button>
                    </form>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button type="submit" aria-label="Закрыть">close</button>
            </form>
        </dialog>
    </div>
</template>
