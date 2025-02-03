<script setup>
import {Head, router, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import OrderStatus from "@/Components/OrderStatus.vue";
import PaymentDetail from "@/Components/PaymentDetail.vue";
import SmsLogsModal from "@/Modals/SmsLogsModal.vue";
import ConfirmModal from "@/Components/Modals/ConfirmModal.vue";
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import OrderModal from "@/Modals/OrderModal.vue";
import {useModalStore} from "@/store/modal.js";
import DateTime from "@/Components/DateTime.vue";
import {useViewStore} from "@/store/view.js";
import ShowAction from "@/Components/Table/ShowAction.vue";
import {onMounted, ref, computed} from "vue";
import {Datepicker} from 'flowbite-datepicker';
import DisplayUUID from "@/Components/DisplayUUID.vue";
import FiltersPanel from "@/Components/Filters/FiltersPanel.vue";

const viewStore = useViewStore();
const orders = usePage().props.orders;
const modalStore = useModalStore();

const filters = ref(usePage().props.filters);
const filtersVariants = ref(usePage().props.filtersVariants);

onMounted(() => {
    const startDateDatepickerElement = document.getElementById('start-date-datepicker')
    const endDateDatepickerElement = document.getElementById('end-date-datepicker')

    startDateDatepickerElement.addEventListener('changeDate', (e) => {
        filters.value.startDate = e.target.value;
    });
    endDateDatepickerElement.addEventListener('changeDate', (e) => {
        filters.value.endDate = e.target.value;
    });

    Datepicker.locales.ru = {
        days: ["Воскресенье", "Понедельник", "Вторник", "Среда", "Четверг", "Пятница", "Суббота"],
        daysShort: ["Вск", "Пнд", "Втр", "Срд", "Чтв", "Птн", "Суб"],
        daysMin: ["Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"],
        months: ["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"],
        monthsShort: ["Янв", "Фев", "Мар", "Апр", "Май", "Июн", "Июл", "Авг", "Сен", "Окт", "Ноя", "Дек"],
        today: "Сегодня",
        clear: "Очистить",
        format: "dd.mm.yyyy",
        weekStart: 1,
        monthsTitle: 'Месяцы'
    };

    new Datepicker(startDateDatepickerElement, {
        language: 'ru',
        format: 'dd/mm/yyyy',
    })
    new Datepicker(endDateDatepickerElement, {
        language: 'ru',
        format: 'dd/mm/yyyy',
    })
})

/*const orderStatusesSelected = computed(() => {
    return filtersVariants.value.orderStatuses.map(i => {
        i.selected = filters.value.statuses.includes(i.value);

        return i;
    })
})*/

router.on('success', (event) => {
    orders.value = usePage().props.orders;
})

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Сделки" />

        <MainTableSection
            title="Сделки"
            :data="orders"
            :query-data="{filters}"
        >
            <template v-slot:header>
                <FiltersPanel name="orders" :filters="filters">
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
<!--                        <div id="filterDropdown" class="z-10 hidden w-48 p-3 bg-white rounded-xl shadow dark:bg-gray-700">
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
                        </div>-->
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="relative lg:max-w-sm w-full">
                            <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z"/>
                                </svg>
                            </div>
                            <input
                                datepicker
                                id="start-date-datepicker"
                                type="text"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                placeholder="Начальная дата"
                                :value="filters.startDate"
                            >
                        </div>
                        <span class="hidden lg:block mx-4 text-gray-500">до</span>
                        <div class="relative lg:max-w-sm w-full">
                            <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z"/>
                                </svg>
                            </div>
                            <input
                                datepicker
                                id="end-date-datepicker"
                                type="text"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                placeholder="Конечная дата"
                                :value="filters.endDate"
                            >
                        </div>
                    </div>
                    <div v-if="viewStore.isAdminViewMode">
                        <input
                            type="text"
                            id="external_id"
                            v-model="filters.externalID"
                            placeholder="Внешний ID"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 h-[38px] dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        >
                    </div>
                    <div >
                        <input
                            type="text"
                            id="uuid"
                            v-model="filters.uuid"
                            placeholder="UUID"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 h-[38px] dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        >
                    </div>
                </FiltersPanel>
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
                                    Реквизит
                                </th>
                                <th scope="col" class="px-6 py-3" v-if="viewStore.isAdminViewMode">
                                    Трейдер
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
                            <tr v-for="order in orders.data" class="bg-white border-b last:border-none dark:bg-gray-800 dark:border-gray-700">
                            <th scope="row" class="px-6 py-3 font-medium whitespace-nowrap text-gray-900 dark:text-gray-200">
                                <DisplayUUID :uuid="order.uuid"/>
                            </th>
                            <td class="px-6 py-3">
                                <div class="text-nowrap text-gray-900 dark:text-gray-200">{{ order.amount }} {{ order.currency.toUpperCase() }}</div>
                                <div class="text-nowrap text-xs">{{ order.profit }} {{ order.base_currency.toUpperCase() }}</div>
                            </td>
                            <td class="px-6 py-3">
                                <PaymentDetail
                                    :detail="order.payment_detail"
                                    :type="order.payment_detail_type"
                                    :copyable="false"
                                    class="text-gray-900 dark:text-gray-200"
                                ></PaymentDetail>
                                <div class="text-xs">{{ order.payment_detail_name }}</div>
                            </td>
                            <td class="px-6 py-3" v-if="viewStore.isAdminViewMode">
                                {{ order.user.email }}
                            </td>
                            <td class="px-6 py-3">
                                <OrderStatus :status="order.status" :status_name="order.status_name"></OrderStatus>
                            </td>
                            <td class="px-6 py-3">
                                <DateTime class="justify-center" :data="order.created_at"/>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <ShowAction link="#" @click.prevent="modalStore.openOrderModal({order})"></ShowAction>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </MainTableSection>

        <OrderModal/>
        <SmsLogsModal/>
        <ConfirmModal/>
    </div>
</template>
