<script setup>
import {Head, router, useForm} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { usePage } from '@inertiajs/vue3';
import PaymentDetail from "@/Components/PaymentDetail.vue";
import PaymentDetailLimit from "@/Components/PaymentDetailLimit.vue";
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import {useViewStore} from "@/store/view.js";
import AddMobileIcon from "@/Components/AddMobileIcon.vue";
import {computed, onMounted, ref} from "vue";
import InputFilter from "@/Components/Filters/Pertials/InputFilter.vue";
import FiltersPanel from "@/Components/Filters/FiltersPanel.vue";
import FilterCheckbox from "@/Components/Filters/Pertials/FilterCheckbox.vue";
import GatewayLogo from "@/Components/GatewayLogo.vue";
import TableActionsDropdown from "@/Components/Table/TableActionsDropdown.vue";
import TableAction from "@/Components/Table/TableAction.vue";
import ConfirmModal from "@/Components/Modals/ConfirmModal.vue";
import {useModalStore} from "@/store/modal.js";
import {useTableFiltersStore} from "@/store/tableFilters.js";
import DropdownFilter from "@/Components/Filters/Pertials/DropdownFilter.vue";

const modalStore = useModalStore();
const viewStore = useViewStore();
const paymentDetails = ref(usePage().props.paymentDetails)
const detailActiveToggleForm = useForm({});
const currentTab = ref('active');
const tableFiltersStore = useTableFiltersStore();
const displayShortDetail = ref(true);

const currentUser = usePage().props.auth?.user;

// Определяем, является ли текущий пользователь VIP
const isVipUser = computed(() => {
    return currentUser?.is_vip === true || currentUser?.is_vip === 1;
});

const toggleActive = (detail_id) => {
    detailActiveToggleForm.patch(route('payment-details.toggle-active', detail_id), {
        preserveScroll: true,
        onSuccess: (result) => {
            paymentDetails.value = result.props.paymentDetails;
        },
    });
};

router.on('success', (event) => {
    paymentDetails.value = usePage().props.paymentDetails;
})

const confirmArchiveDetail = (detail) => {
    modalStore.openConfirmModal({
        title: 'Вы уверены что хотите архивировать реквизит #' + detail.id + '?',
        body: 'Действие можно отменить.',
        confirm_button_name: 'Архивировать',
        confirm: () => {
            router.post(route('payment-details.archive', detail.id), {}, {
                preserveScroll: true
            });
        }
    });
};

const confirmUnarchiveDetail = (detail) => {
    modalStore.openConfirmModal({
        title: 'Вы уверены что хотите вернуть реквизит из архива #' + detail.id + '?',
        body: 'Действие можно отменить.',
        confirm_button_name: 'Вернуть',
        confirm: () => {
            router.delete(route('payment-details.unarchive', detail.id), {}, {
                preserveScroll: true
            });
        }
    });
};

const openPage = (tab) => {
    tableFiltersStore.setTab(tab);
    tableFiltersStore.setCurrentPage(1);

    router.visit(route(route().current()), {
        preserveScroll: true,
        data: tableFiltersStore.getQueryData,
    })
}

onMounted(() => {
    if (tableFiltersStore.getTab === '') {
        tableFiltersStore.setTab('active');
    }
    currentTab.value = tableFiltersStore.getTab
})

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Реквизиты" />

        <MainTableSection
            title="Реквизиты"
            :data="paymentDetails"
        >
            <template v-slot:button>
                <button
                    @click="router.visit(route(viewStore.adminPrefix + 'payment-details.create'))"
                    type="button"
                    class="hidden md:block text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-xl  text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800"
                >
                    Создать реквизиты
                </button>
                <AddMobileIcon
                    @click="router.visit(route(viewStore.adminPrefix + 'payment-details.create'))"
                />
            </template>
            <template v-slot:header>
                <ul class="flex flex-wrap text-sm font-medium text-center text-gray-500 dark:text-gray-400">
                    <li class="me-2">
                        <a @click.prevent="openPage('active')" href="#" :class="currentTab === 'active' ? 'border border-blue-600 shadow inline-flex items-center px-4 py-2 text-white bg-blue-600 rounded-xl active' : 'border border-gray-200 dark:border-gray-700 inline-flex items-center px-4 py-2 rounded-xl hover:text-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-white'" aria-current="page">
                            <svg class="w-4 h-4 sm:mr-2 mr-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.03v13m0-13c-2.819-.831-4.715-1.076-8.029-1.023A.99.99 0 0 0 3 6v11c0 .563.466 1.014 1.03 1.007 3.122-.043 5.018.212 7.97 1.023m0-13c2.819-.831 4.715-1.076 8.029-1.023A.99.99 0 0 1 21 6v11c0 .563-.466 1.014-1.03 1.007-3.122-.043-5.018.212-7.97 1.023"/>
                            </svg>
                            <span class="sm:block hidden">Активные</span>
                        </a>
                    </li>
                    <li class="me-2">
                        <a @click.prevent="openPage('archived')" href="#" :class="currentTab === 'archived' ? 'border border-blue-600 shadow inline-flex items-center px-4 py-2 text-white bg-blue-600 rounded-xl active' : 'border border-gray-200 dark:border-gray-700 inline-flex items-center px-4 py-2 rounded-xl hover:text-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-white'" aria-current="page">
                            <svg class="w-4 h-4 sm:mr-2 mr-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linejoin="round" stroke-width="2" d="M10 12v1h4v-1m4 7H6a1 1 0 0 1-1-1V9h14v9a1 1 0 0 1-1 1ZM4 5h16a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z"/>
                            </svg>
                            <span class="sm:block hidden">Архив</span>
                        </a>
                    </li>
                </ul>
            </template>
            <template v-slot:table-filters>
                <FiltersPanel name="payment-details">
                    <InputFilter
                        name="id"
                        placeholder="ID реквизита"
                    />
                    <InputFilter
                        name="name"
                        placeholder="Название"
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
                        name="paymentDetail"
                        placeholder="Реквизит"
                    />
                    <InputFilter
                        v-if="viewStore.isAdminViewMode"
                        name="user"
                        placeholder="Пользователь"
                    />
                    <FilterCheckbox
                        name="active"
                        title="Включенные"
                    />
                    <FilterCheckbox
                        v-if="viewStore.isAdminViewMode"
                        name="multipliedDetails"
                        title="Размноженные"
                    />
                    <FilterCheckbox
                        v-if="viewStore.isAdminViewMode"
                        name="online"
                        title="Онлайн"
                    />
                </FiltersPanel>
            </template>
            <template v-slot:body>
                <div class="relative overflow-x-auto shadow-md sm:rounded-table">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">
                                    ID
                                </th>
                                <th scope="col" class="px-6 py-3 flex items-center">
                                    Реквизит
                                    <div class="inline-flex items-center ml-2">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" v-model="displayShortDetail" class="sr-only peer">
                                            <div class="w-7 h-4 bg-gray-200 rounded-full peer dark:bg-gray-700 peer-checked:bg-blue-600 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border after:rounded-full after:h-3 after:w-3 after:transition-all"></div>
                                        </label>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Устройство
                                </th>
                                <th scope="col" class="px-6 py-3" v-if="viewStore.isAdminViewMode">
                                    Трейдер
                                </th>
                                <th scope="col" class="px-6 py-3 text-nowrap">
                                    Сделок
                                </th>
                                <th scope="col" class="px-6 py-3 text-nowrap" v-if="viewStore.isAdminViewMode || isVipUser">
                                    Лимиты
                                </th>
                                <th scope="col" class="px-6 py-3 text-nowrap">
                                    Интервал
                                </th>
                                <th scope="col" class="px-6 py-3 text-nowrap">
                                    Лимит
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
                            <tr v-for="payment_detail in paymentDetails.data" class="bg-white border-b last:border-none dark:bg-gray-800 dark:border-gray-700">
                                <th scope="row" class="px-6 py-3 font-medium whitespace-nowrap text-gray-900 dark:text-gray-200">{{ payment_detail.id }}</th>
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-3">
                                        <GatewayLogo :img_path="payment_detail.payment_gateway.logo_path" :name="payment_detail.payment_gateway.name" class="w-10 h-10 text-gray-500 dark:text-gray-400"/>
                                        <PaymentDetail
                                            :detail="payment_detail.detail"
                                            :type="payment_detail.detail_type"
                                            :name="payment_detail.name"
                                            :short="displayShortDetail"
                                        ></PaymentDetail>
                                    </div>
                                </td>
                                <td
                                    class="px-6 py-3"
                                >
                                    {{ payment_detail.device_name }}
                                </td>
                                <td
                                    v-if="viewStore.isAdminViewMode"
                                    class="px-6 py-3"
                                >
                                    {{ payment_detail.owner_email }}
                                </td>
                                <td
                                    class="px-6 py-3 text-nowrap"
                                >
                                    <!-- Сделать компонент универсальным в будущем -->
                                    {{payment_detail.pending_orders_count }}/{{ payment_detail.max_pending_orders_quantity}}
                                </td>
                                <td class="px-6 py-3" v-if="viewStore.isAdminViewMode || isVipUser">
                                    <div class="text-nowrap ">
                                        <span class="text-gray-900 dark:text-gray-200">min: </span>
                                        {{ payment_detail.min_order_amount !== null ? payment_detail.min_order_amount : '&infin;' }}
                                    </div>
                                    <div class="text-nowrap">
                                        <span class="text-gray-900 dark:text-gray-200">max: </span>
                                        {{ payment_detail.max_order_amount !== null ? payment_detail.max_order_amount : '&infin;' }}
                                    </div>
                                </td>
                                <td class="px-6 py-3">
                                    <div class="text-nowrap ">
                                        <span class="text-gray-900 dark:text-gray-200"></span>
                                        {{ payment_detail.order_interval_minutes !== null ? payment_detail.order_interval_minutes + ' мин' : '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-3">
                                    <PaymentDetailLimit :current_daily_limit="payment_detail.current_daily_limit" :daily_limit="payment_detail.daily_limit"></PaymentDetailLimit>
                                </td>
                                <td class="px-6 py-3">
                                    <div class="flex items-center">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" :checked="payment_detail.is_active" class="sr-only peer" @change="toggleActive(payment_detail.id)" :disabled="detailActiveToggleForm.processing || currentTab === 'archived'">
                                            <div class="relative w-9 h-5 bg-gray-200 rounded-full peer dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-green-600 dark:peer-checked:bg-green-600"></div>
                                        </label>
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-right relative">
                                    <TableActionsDropdown v-if="currentTab === 'active'">
                                        <TableAction @click="router.visit(route(viewStore.adminPrefix + 'payment-details.edit', payment_detail.id))">
                                            Редактировать
                                        </TableAction>
                                        <TableAction @click="confirmArchiveDetail(payment_detail)">
                                            Архивировать
                                        </TableAction>
                                    </TableActionsDropdown>
                                    <TableActionsDropdown v-else>
                                        <TableAction @click="confirmUnarchiveDetail(payment_detail)">
                                            Вернуть из архива
                                        </TableAction>
                                    </TableActionsDropdown>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </MainTableSection>

        <ConfirmModal/>
    </div>
</template>
