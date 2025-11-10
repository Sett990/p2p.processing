<script setup>
import {Head, router, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import OrderStatus from "@/Components/OrderStatus.vue";
import PaymentDetail from "@/Components/PaymentDetail.vue";
import ConfirmModal from "@/Components/Modals/ConfirmModal.vue";
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import OrderModal from "@/Modals/OrderModal.vue";
import {useModalStore} from "@/store/modal.js";
import DateTime from "@/Components/DateTime.vue";
import {useViewStore} from "@/store/view.js";
import ShowAction from "@/Components/Table/ShowAction.vue";
import {ref, watch} from "vue";
import DisplayUUID from "@/Components/DisplayUUID.vue";
import FiltersPanel from "@/Components/Filters/FiltersPanel.vue";
import DropdownFilter from "@/Components/Filters/Pertials/DropdownFilter.vue";
import InputFilter from "@/Components/Filters/Pertials/InputFilter.vue";
import EditOrderAmountModal from "@/Modals/Order/EditOrderAmountModal.vue";
import GatewayLogo from "@/Components/GatewayLogo.vue";
import RefreshTableData from "@/Components/Table/RefreshTableData.vue";
import DateFilter from "@/Components/Filters/Pertials/DateFilter.vue";

const viewStore = useViewStore();
const orders = ref(usePage().props.orders);
const modalStore = useModalStore();

const displayShortDetail = ref(getCookieValue('displayShortDetail', true));

function getCookieValue(name, defaultValue) {
    const currentRoute = route().current();
    const cookieName = `${name}_${currentRoute}`;
    const match = document.cookie.match(new RegExp('(^| )' + cookieName + '=([^;]+)'));
    return match ? match[2] === 'true' : defaultValue;
}

function updateDisplayShortDetailCookie() {
    const currentRoute = route().current();
    const cookieName = `displayShortDetail_${currentRoute}`;
    document.cookie = `${cookieName}=${displayShortDetail.value}; path=/; max-age=31536000`; // 1 год
}

// Следим за изменениями и обновляем cookie
watch(displayShortDetail, () => {
    updateDisplayShortDetailCookie();
});

const filtersVariants = ref(usePage().props.filtersVariants);

router.on('success', (event) => {
    orders.value = usePage().props.orders;
})

const reloadingTableData = ref(false);

const openOrderModal = (order) => {
    if (reloadingTableData.value) {
        return;
    }
    modalStore.openOrderModal({order_id: order.id})
}

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Сделки" />

        <MainTableSection
            title="Сделки"
            :data="orders"
        >
            <template v-slot:header>
                <div class="space-y-4">
                    <FiltersPanel name="orders">
                        <DateFilter name="startDate" title="Начальная дата"/>
                        <DateFilter name="endDate" title="Конечная дата"/>
                        <InputFilter
                            v-if="viewStore.isAdminViewMode"
                            name="externalID"
                            placeholder="Внешний ID"
                        />
                        <InputFilter
                            name="uuid"
                            placeholder="UUID"
                        />
                        <InputFilter
                            name="amount"
                            placeholder="Сумма"
                        />
                        <InputFilter
                            name="paymentDetail"
                            placeholder="Реквизит"
                        />
                        <DropdownFilter
                            name="detailTypes"
                            title="Тип реквизита"
                        />
                        <InputFilter
                            name="paymentGateway"
                            placeholder="Платежный метод"
                        />
                        <InputFilter
                            v-if="viewStore.isAdminViewMode"
                            name="user"
                            placeholder="Пользователь"
                        />
                        <DropdownFilter
                            name="orderStatuses"
                            :options="filtersVariants.orderStatuses"
                            title="Статусы"
                        />
                    </FiltersPanel>

                    <div class="flex justify-end">
                        <RefreshTableData
                            @refresh-started="reloadingTableData = true"
                            @refresh-finished="reloadingTableData = false"
                        />
                    </div>
                </div>
            </template>
            <template v-slot:body>
                <div class="relative shadow-md rounded-table">
                    <div
                        class="card sticky top-0 left-0 bg-base-100/50 z-10 flex items-center justify-center backdrop-blur-sm transition-all duration-300 ease-in-out opacity-0 pointer-events-none"
                        :class="{'opacity-0 pointer-events-none': !reloadingTableData, 'opacity-100': reloadingTableData}"
                        style="position: absolute; inset: 0; width: 100%; height: 100%;"
                    >
                        <div class="flex flex-col items-center transition-transform duration-300" :class="{'scale-90 opacity-0': !reloadingTableData, 'scale-100 opacity-100': reloadingTableData}">
                            <div class="animate-spin inline-block w-8 h-8 border-[3px] border-current border-t-transparent text-primary rounded-full" role="status" aria-label="loading">
                                <span class="sr-only">Загрузка...</span>
                            </div>
                            <div class="mt-2 text-sm font-medium text-base-content">Загрузка данных...</div>
                        </div>
                    </div>

                    <!-- Контейнер для таблицы с overflow-x-auto -->
                    <div class="overflow-x-auto card bg-base-100 shadow">
                        <!-- Оверлей с лоадером -->

                        <table class="table table-sm" :class="{'pointer-events-none': reloadingTableData}">
                            <thead class="text-xs uppercase bg-base-300">
                                <tr>
                                    <th scope="col">
                                        UUID
                                    </th>
                                    <th scope="col">
                                        Сумма
                                    </th>
                                    <th scope="col" class="flex items-center">
                                        Реквизит
                                        <label class="swap swap-rotate inline-grid place-items-center ml-2 cursor-pointer w-6 h-6">
                                            <input type="checkbox" v-model="displayShortDetail" class="sr-only" />
                                            <!-- Коротко (скрываем детали) -->
                                            <svg class="swap-on w-5 h-5 text-base-content/70" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                            </svg>
                                            <!-- Полностью (показываем), праймари -->
                                            <svg class="swap-off w-5 h-5 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>
                                        </label>
                                    </th>
                                    <th scope="col" v-if="viewStore.isAdminViewMode">
                                        Профиль
                                    </th>
                                    <th scope="col">
                                        Статус
                                    </th>
                                    <th scope="col">
                                        Создан
                                    </th>
                                    <th scope="col" class="flex justify-center">
                                        <span class="sr-only">Действия</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="order in orders.data" class="bg-base-100 border-b last:border-none border-base-200">
                                <th scope="row" class="font-medium whitespace-nowrap text-gray-900 dark:text-gray-200">
                                    <DisplayUUID :uuid="order.uuid"/>
                                </th>
                                <td>
                                    <div class="text-nowrap text-base-content">{{ order.amount }} {{ order.currency.toUpperCase() }}</div>
                                    <div class="text-nowrap text-xs opacity-70">{{ order.total_profit }} {{ order.base_currency.toUpperCase() }}</div>
                                </td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <GatewayLogo :img_path="order.payment_gateway_logo_path" :name="order.payment_gateway_name" class="w-10 h-10 text-base-content/50"/>
                                        <PaymentDetail
                                            :detail="order.payment_detail"
                                            :type="order.payment_detail_type"
                                            :name="order.payment_detail_name"
                                            :short="displayShortDetail"
                                        ></PaymentDetail>
                                    </div>
                                </td>
                                <td v-if="viewStore.isAdminViewMode">
                                    <div>
                                        <div class="flex items-center gap-2 text-nowrap">
                                            <svg class="w-5 h-5 text-primary" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-width="1.5" d="M7 17v1a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1a3 3 0 0 0-3-3h-4a3 3 0 0 0-3 3Zm8-9a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                            </svg>
                                            <span class="text-base-content">{{ order.trader_email }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-nowrap">
                                            <svg class="w-4 h-4 ml-0.5 mr-0.5 text-primary" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 15h12M6 6h12m-6 12h.01M7 21h10a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1v16a1 1 0 0 0 1 1Z"/>
                                            </svg>
                                            <span class="text-base-content/70">{{ order.device_name }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <OrderStatus :status="order.status" :status_name="order.status_name"></OrderStatus>
                                </td>
                                <td>
                                    <DateTime class="justify-start" :data="order.created_at"/>
                                </td>
                                <td class="text-right">
                                    <ShowAction @click.prevent="openOrderModal(order)"></ShowAction>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>
        </MainTableSection>

        <OrderModal/>
        <ConfirmModal/>
        <EditOrderAmountModal/>
    </div>
</template>
