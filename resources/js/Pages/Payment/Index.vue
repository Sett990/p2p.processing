<script setup>
import {Head, router, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import OrderStatus from "@/Components/OrderStatus.vue";
import ConfirmModal from "@/Components/Modals/ConfirmModal.vue";
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import OrderModal from "@/Modals/OrderModal.vue";
import {useModalStore} from "@/store/modal.js";
import DateTime from "@/Components/DateTime.vue";
import {useViewStore} from "@/store/view.js";
import AddMobileIcon from "@/Components/AddMobileIcon.vue";
import DisplayUUID from "@/Components/DisplayUUID.vue";
import {computed, onMounted, ref} from "vue";
import {Datepicker} from "flowbite-datepicker";

const viewStore = useViewStore();
const orders = usePage().props.orders;
const modalStore = useModalStore();

const orderPaymentLink = (payment_link) => {
    window.open(payment_link, '_blank')
}

const currentFilters = ref(usePage().props.currentFilters);
const orderStatuses = ref(usePage().props.orderStatuses);

const orderStatusesSelected = computed(() => {
    return orderStatuses.value.map(i => {
        i.selected = currentFilters.value.statuses.includes(i.value);

        return i;
    })
})

const filters = computed(() => {
    return {
        statuses: orderStatuses.value.filter(o => o.selected).map(o => o.value).join(','),
        external_id: currentFilters.value.externalID,
        uuid: currentFilters.value.uuid,
    }
})

const applyFilters = () => {
    router.visit(route(route().current()), {
        data: {
            filters: filters.value,
            page: 1
        },
        preserveScroll: true
    })
}

const clearFilters = () => {
    router.visit(route(route().current()), {
        data: {
            filters: {},
            page: 1
        },
        preserveScroll: true
    })
}

const displayFilters = ref(localStorage.getItem('display-table-filters') === 'display');

const toggleFiltersDisplay = () => {
    displayFilters.value = localStorage.getItem('display-table-filters') === 'display';
    displayFilters.value = !displayFilters.value;
    localStorage.setItem('display-table-filters', displayFilters.value ? 'display' : 'hide');
}

router.on('success', (event) => {
    orders.value = usePage().props.orders;
})

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Платежи" />

        <MainTableSection
            title="Платежи"
            :data="orders"
            :query-data="{filters}"
        >
            <template v-slot:button>
                <button
                    @click="router.visit(route('payments.create'))"
                    type="button"
                    class="hidden md:block text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-xl  text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800"
                >
                    Создать платеж
                </button>
                <AddMobileIcon
                    @click="router.visit(route('payments.create'))"
                />
            </template>
            <template v-slot:header>
                <section>
                    <a
                        @click.prevent="toggleFiltersDisplay"
                        href="#"
                        class="font-medium text-blue-600 dark:text-blue-500 hover:underline flex justify-end mb-1 mr-1"
                    >
                        {{ displayFilters ? 'Скрыть фильтры' : 'Показать фильтры' }}
                    </a>
                    <div v-show="displayFilters" class="flex items-center mb-5">
                        <div class="mx-auto w-full">
                            <div class="relative bg-white shadow-md dark:bg-gray-800 sm:rounded-plate">
                                <div class="flex flex-col xl:items-center justify-between p-2 space-y-3 lg:flex-row lg:space-y-0 lg:space-x-4">
                                    <div class="xl:flex items-center gap-4 xl:space-y-0 space-y-3">
                                        <div class="flex items-center w-full space-x-3 lg:w-auto">
                                            <button id="filterDropdownButton" data-dropdown-toggle="filterDropdown" class="flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-xl lg:w-auto focus:outline-none hover:bg-gray-100 hover:text-primary-700 focus:z-10 focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700" type="button">
                                                <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="w-4 h-4 mr-2 text-gray-400" viewbox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                                                </svg>
                                                Статус
                                                <svg class="-mr-1 ml-1.5 w-5 h-5" fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                    <path clip-rule="evenodd" fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                                </svg>
                                            </button>
                                            <!-- Dropdown menu -->
                                            <div id="filterDropdown" class="z-10 hidden w-48 p-3 bg-white rounded-xl shadow dark:bg-gray-700">
                                                <h6 class="mb-3 text-sm font-medium text-gray-900 dark:text-white">
                                                    Статус
                                                </h6>
                                                <ul class="space-y-2 text-sm" aria-labelledby="dropdownDefault">
                                                    <li
                                                        v-for="orderStatus in orderStatusesSelected"
                                                        class="flex items-center"
                                                    >
                                                        <input
                                                            :id="`orderStatus-${orderStatus.value}`"
                                                            type="checkbox"
                                                            :value="orderStatus.value"
                                                            v-model="orderStatus.selected"
                                                            class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500"
                                                        />
                                                        <label :for="`orderStatus-${orderStatus.value}`" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">
                                                            {{ orderStatus.name }}
                                                        </label>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div>
                                            <input
                                                type="text"
                                                id="uuid"
                                                v-model="currentFilters.uuid"
                                                placeholder="UUID"
                                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 h-[38px] dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                @keyup.enter="applyFilters"
                                            >
                                        </div>
                                        <div>
                                            <input
                                                type="text"
                                                id="external_id"
                                                v-model="currentFilters.externalID"
                                                placeholder="Внешний ID"
                                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 h-[38px] dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                @keyup.enter="applyFilters"
                                            >
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button
                                            @click.prevent="applyFilters"
                                            type="button"
                                            class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-xl text-sm p-2 text-center inline-flex items-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                                        >
                                            <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M18.796 4H5.204a1 1 0 0 0-.753 1.659l5.302 6.058a1 1 0 0 1 .247.659v4.874a.5.5 0 0 0 .2.4l3 2.25a.5.5 0 0 0 .8-.4v-7.124a1 1 0 0 1 .247-.659l5.302-6.059c.566-.646.106-1.658-.753-1.658Z"/>
                                            </svg>
                                            <span class="sr-only">Фильтровать</span>
                                        </button>
                                        <button
                                            @click.prevent="clearFilters"
                                            type="button"
                                            class="text-center inline-flex items-center focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-xl text-sm p-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900"
                                        >
                                            <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z"/>
                                            </svg>
                                            <span class="sr-only">Очистить</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </template>
            <template v-slot:body>
                <div class="relative overflow-x-auto shadow-md sm:rounded-table ">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">
                                UUID
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Сумма
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Прибыль
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Комиссия
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Статус
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Внешний ID
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
                        <tr v-for="order in orders.data" class="bg-white border-b last:border-none dark:bg-gray-800 dark:border-gray-700">
                            <th scope="row" class="px-6 py-3 font-medium whitespace-nowrap text-gray-900 dark:text-gray-200">
                                <DisplayUUID :uuid="order.uuid"/>
                            </th>
                            <td class="px-6 py-3">
                                <div class="text-nowrap text-gray-900 dark:text-gray-200">{{ order.amount }} {{ order.currency.toUpperCase() }}</div>
                                <div class="text-nowrap text-xs">{{ order.profit }} {{ order.base_currency.toUpperCase() }}</div>
                            </td>
                            <td class="px-6 py-3">
                                <div class="text-nowrap">{{ order.merchant_profit }} {{ order.base_currency.toUpperCase() }}</div>
                            </td>
                            <td class="px-6 py-3">
                                {{ order.service_commission_amount_total }} {{ order.base_currency.toUpperCase() }}
                            </td>
                            <td class="px-6 py-3">
                                <OrderStatus :status="order.status" :status_name="order.status_name"></OrderStatus>
                            </td>
                            <td class="px-6 py-3">
                                {{ order.external_id }}
                            </td>
                            <td class="px-6 py-3">
                                <DateTime class="justify-center" :data="order.created_at"/>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <button
                                    v-if="! order.is_h2h"
                                    @click="orderPaymentLink(order.payment_link)"
                                    type="button"
                                    class="px-0 py-0 justify-items-center text-blue-500 hover:text-blue-600 inline-flex items-center hover:underline"
                                >
                                    <svg class="w-[22px] h-[22px]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 3v4a1 1 0 0 1-1 1H5m8-2h3m-3 3h3m-4 3v6m4-3H8M19 4v16a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7.914a1 1 0 0 1 .293-.707l3.914-3.914A1 1 0 0 1 9.914 3H18a1 1 0 0 1 1 1ZM8 12v6h8v-6H8Z"/>
                                    </svg>
                                </button>
                                <div v-else>
                                    <button
                                        @click.prevent="false"
                                        type="button"
                                        class="ml-1.5 px-2 py-1 text-xs font-medium text-center inline-flex items-center text-gray-900 bg-white border border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100 rounded-xl  dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700"
                                        :data-tooltip-target="'tooltip-h2h-'+order.id"
                                    >
                                        H2H
                                    </button>
                                    <div :id="'tooltip-h2h-'+order.id" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-xl  shadow-sm opacity-0 tooltip dark:bg-gray-700">
                                        Для сделок созданных через H2H API невозможно открыть платежную ссылку. Так как управление платежом передается на внешний сервис.
                                    </div>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </MainTableSection>

        <OrderModal/>
        <ConfirmModal/>
    </div>
</template>
