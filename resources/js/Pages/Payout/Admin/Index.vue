<script setup>
import {Head, router} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { usePage } from '@inertiajs/vue3';
import IsActiveStatus from "@/Components/IsActiveStatus.vue";
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import {computed, onMounted, ref} from "vue";
import DateTime from "@/Components/DateTime.vue";
import PayoutStatus from "@/Components/PayoutStatus.vue";
import PayoutModal from "@/Modals/PayoutModal.vue";
import {useModalStore} from "@/store/modal.js";
import ShowAction from "@/Components/Table/ShowAction.vue";

const problematicPayouts = usePage().props.problematicPayouts;
const payouts = usePage().props.payouts;
const payoutGateways = usePage().props.payoutGateways;
const payoutOffers = usePage().props.payoutOffers;
const currentTab = ref('payouts');
const modalStore = useModalStore();

const openPage = (tab) => {
    currentTab.value = tab;

    let data = {
        tab: tab,
        page: 1
    };

    router.visit(route(route().current()), {
        preserveScroll: true,
        data: data,
    })
}

const tableData = computed(() => {
    if (currentTab.value === 'payouts') {
        return payouts;
    } else if (currentTab.value === 'payout-gateways') {
        return payoutGateways;
    } else if (currentTab.value === 'payout-offers') {
        return payoutOffers
    }
})

onMounted(() => {
    let urlParams = new URLSearchParams(window.location.search);
    currentTab.value = urlParams.get('tab') ?? 'payouts'
})

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Выплаты" />

        <MainTableSection
            title="Выплаты"
            :data="tableData"
        >
            <template v-slot:header>
                <ul class="flex flex-wrap text-sm font-medium text-center text-gray-500 dark:text-gray-400">
                    <li class="me-2">
                        <a @click.prevent="openPage('payouts')" href="#" :class="currentTab === 'payouts' ? 'shadow inline-flex px-4 py-2 text-white bg-blue-600 rounded-xl active' : 'border border-gray-200 dark:border-gray-700 inline-flex px-4 py-2 rounded-xl hover:text-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-white'" aria-current="page">
                            <svg class="w-4 h-4 sm:mr-2 mr-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m11.0001 18-.8536-.8536c-.0937-.0937-.1464-.2209-.1464-.3535v-4.4172c0-.2422-.08794-.4762-.24744-.6585L4.45127 5.6585C3.88551 5.01192 4.34469 4 5.20385 4H18.7547c.8658 0 1.3225 1.02544.7433 1.66896L16.5001 9m-2.5 9.3754c.3347.3615.7824.6134 1.2788.7195.4771.1584 1.0002.1405 1.464-.05.4638-.1906.8338-.5396 1.0356-.977.2462-.8286-.6363-1.7337-1.7735-1.9948-1.1372-.2611-2.016-1.1604-1.7735-1.9948.2016-.4375.5716-.7868 1.0354-.9774.4639-.1905.9871-.2082 1.4643-.0496.491.1045.9348.3517 1.2689.7067m-1.9397 5.41V20m0-8v.9771"/>
                            </svg>
                            <span class="sm:block hidden">Выплаты</span>
                        </a>
                    </li>
                    <li class="me-2">
                        <a @click.prevent="openPage('payout-gateways')" href="#" :class="currentTab === 'payout-gateways' ? 'shadow inline-flex px-4 py-2 text-white bg-blue-600 rounded-xl active' : 'border border-gray-200 dark:border-gray-700 inline-flex px-4 py-2 rounded-xl hover:text-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-white'" aria-current="page">
                            <svg class="w-4 h-4 sm:mr-2 mr-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12v4m0 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4ZM8 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm0 0v2a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2V8m0 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"/>
                            </svg>
                            <span class="sm:block hidden">Направления</span>
                        </a>
                    </li>
                    <li class="me-2">
                        <a @click.prevent="openPage('payout-offers')" href="#" :class="currentTab === 'payout-offers' ? 'shadow inline-flex px-4 py-2 text-white bg-blue-600 rounded-xl active' : 'border border-gray-200 dark:border-gray-700 inline-flex px-4 py-2 rounded-xl hover:text-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-white'" aria-current="page">
                            <svg class="w-4 h-4 sm:mr-2 mr-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 6H5m2 3H5m2 3H5m2 3H5m2 3H5m11-1a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2M7 3h11a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Zm8 7a2 2 0 1 1-4 0 2 2 0 0 1 4 0Z"/>
                            </svg>
                            <span class="sm:block hidden">Предложения</span>
                        </a>
                    </li>
                </ul>
            </template>
            <template v-slot:body>
                <h3 class="text-xl text-gray-900 dark:text-white sm:text-2xl mb-3">Отказ выплаты</h3>
                <div class="relative overflow-x-auto shadow-md sm:rounded-table mb-6">
                    <table v-if="currentTab === 'payouts'" class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">
                                ID
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Сумма
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Трейдер
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Причина отказа
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Статус
                            </th>
                            <th scope="col" class="px-6 py-3 flex justify-center">
                                <span class="sr-only">Действия</span>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="payout in problematicPayouts.data" class="bg-white border-b last:border-none dark:bg-gray-800 dark:border-gray-700">
                            <th scope="row" class="px-6 py-3 font-medium whitespace-nowrap text-gray-900 dark:text-gray-200">#{{ payout.id }}</th>
                            <td class="px-6 py-3">
                                <div class="text-nowrap">{{ payout.payout_amount }} {{ payout.currency.toUpperCase() }}</div>
                            </td>
                            <td class="px-6 py-3">
                                <div class="text-nowrap">{{ payout.previous_trader.email }}</div>
                            </td>
                            <td class="px-6 py-3">
                                <div class="w-48 break-words">
                                    {{ payout.refuse_reason}}
                                </div>
                            </td>
                            <td class="px-6 py-3">
                                <PayoutStatus :status="payout.status" :status_name="payout.status_name"></PayoutStatus>
                            </td>
                            <td class="px-6 py-3 text-right">

                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <h3 class="text-xl text-gray-900 dark:text-white sm:text-2xl mb-3">Все выплаты</h3>
                <div class="relative overflow-x-auto shadow-md sm:rounded-table">
                    <table v-if="currentTab === 'payouts'" class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">
                                ID
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Сумма
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Владелец
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Трейдер
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Статус
                            </th>
                            <th scope="col" class="px-6 py-3 text-center">
                                Создан
                            </th>
                            <th scope="col" class="px-6 py-3 flex justify-center">
                                <span class="sr-only">Действия</span>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="payout in payouts.data" class="bg-white border-b last:border-none dark:bg-gray-800 dark:border-gray-700">
                            <th scope="row" class="px-6 py-3 font-medium whitespace-nowrap text-gray-900 dark:text-gray-200">#{{ payout.id }}</th>
                            <td class="px-6 py-3">
                                <div class="text-nowrap">{{ payout.payout_amount }} {{ payout.currency.toUpperCase() }}</div>
                            </td>
                            <td class="px-6 py-3">
                                <div class="text-nowrap">{{ payout.owner.email }}</div>
                            </td>
                            <td class="px-6 py-3">
                                <div class="text-nowrap">{{ payout.trader.email }}</div>
                            </td>
                            <td class="px-6 py-3">
                                <PayoutStatus :status="payout.status" :status_name="payout.status_name"></PayoutStatus>
                            </td>
                            <td class="px-6 py-3">
                                <DateTime class="justify-center" :data="payout.created_at"/>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <ShowAction link="#" @click.prevent="modalStore.openPayoutModal({payout})"></ShowAction>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <table v-if="currentTab === 'payout-gateways'" class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">
                                ID
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Название
                            </th>
                            <th scope="col" class="px-6 py-3 text-nowrap">
                                Payout Gateway ID
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Владелец
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Статус
                            </th>
                            <th scope="col" class="px-6 py-3 text-center">
                                Создан
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="payoutGateway in payoutGateways.data" class="bg-white border-b last:border-none dark:bg-gray-800 dark:border-gray-700">
                            <th scope="row" class="px-6 py-3 font-medium whitespace-nowrap text-gray-900 dark:text-gray-200">{{ payoutGateway.id }}</th>
                            <td class="px-6 py-3">
                                {{ payoutGateway.name }}
                            </td>
                            <td class="px-6 py-3">
                                {{ payoutGateway.uuid }}
                            </td>
                            <td class="px-6 py-3">
                                {{ payoutGateway.owner.email }}
                            </td>
                            <td class="px-6 py-3">
                                <IsActiveStatus :is_active="payoutGateway.enabled"></IsActiveStatus>
                            </td>
                            <td class="px-6 py-3 text-center">
                                <DateTime class="justify-center" :data="payoutGateway.created_at"/>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <table v-if="currentTab === 'payout-offers'" class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">
                                ID
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Лимиты
                            </th>
                            <th scope="col" class="px-6 py-3 text-nowrap">
                                Метод
                            </th>
                            <th scope="col" class="px-6 py-3 text-nowrap">
                                Владелец
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Типы реквизитов
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Статус
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="payoutOffer in payoutOffers.data" class="bg-white border-b last:border-none dark:bg-gray-800 dark:border-gray-700">
                            <th scope="row" class="px-6 py-3 font-medium whitespace-nowrap text-gray-900 dark:text-gray-200">{{ payoutOffer.id }}</th>
                            <td class="px-6 py-3">
                                <div class="text-nowrap text-gray-900 dark:text-gray-200">
                                    Макс: {{ payoutOffer.max_amount }} {{ payoutOffer.currency.toUpperCase() }}
                                </div>
                                <div class="text-nowrap">
                                    Мин: {{ payoutOffer.min_amount }} {{ payoutOffer.currency.toUpperCase() }}
                                </div>
                            </td>
                            <td class="px-6 py-3">
                                {{ payoutOffer.payment_gateway_name }}
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex items-center">
                                    <template v-if="payoutOffer.owner.is_payout_online">
                                        <div class="h-2.5 w-2.5 rounded-full bg-green-500 me-2"></div>
                                    </template>
                                    <template v-else>
                                        <div class="h-2.5 w-2.5 rounded-full bg-red-500 me-2"></div>
                                    </template>
                                    {{ payoutOffer.owner.email}}
                                </div>
                            </td>
                            <td class="px-6 py-3">
                                <span
                                    v-for="detailType in payoutOffer.detail_types"
                                    class="inline-flex items-center bg-indigo-100 text-indigo-800 text-xs font-medium me-1 px-1.5 py-0.5 rounded-lg dark:bg-indigo-900 dark:text-indigo-300"
                                >
                                    {{ detailType.name }}
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                <IsActiveStatus :is_active="payoutOffer.active"></IsActiveStatus>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </MainTableSection>

        <PayoutModal/>
    </div>
</template>
