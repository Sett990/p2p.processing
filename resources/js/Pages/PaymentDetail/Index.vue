<script setup>
import {Head, router, useForm} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { usePage } from '@inertiajs/vue3';
import PaymentDetail from "@/Components/PaymentDetail.vue";
import PaymentDetailLimit from "@/Components/PaymentDetailLimit.vue";
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import {useViewStore} from "@/store/view.js";
import AddMobileIcon from "@/Components/AddMobileIcon.vue";
import {computed, onMounted, ref, watch} from "vue";
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
import PaymentDetailCreateModal from "@/Modals/PaymentDetail/PaymentDetailCreateModal.vue";
import PaymentDetailEditModal from "@/Modals/PaymentDetail/PaymentDetailEditModal.vue";

const modalStore = useModalStore();
const openCreateModal = () => {
    modalStore.openPaymentDetailCreateModal();
};
const openEditModal = (paymentDetail) => {
    modalStore.openPaymentDetailEditModal({ paymentDetail });
};
const viewStore = useViewStore();
const paymentDetails = ref(usePage().props.paymentDetails)
const detailActiveToggleForm = useForm({});
const currentTab = ref('active');
const tableFiltersStore = useTableFiltersStore();
const toggleBlocked = ref(false);

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
            // Блокируем тоггл на дополнительные 300 миллисекунд после получения ответа
            toggleBlocked.value = true;
            setTimeout(() => {
                toggleBlocked.value = false;
            }, 300);
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
                    @click="openCreateModal"
                    type="button"
                    class="hidden md:block btn btn-sm btn-primary"
                >
                    Создать реквизиты
                </button>
                <AddMobileIcon
                    @click="openCreateModal"
                />
            </template>
            <template v-slot:header>
                <ul class="flex flex-wrap text-sm font-medium text-center">
                    <li class="me-2">
                        <a @click.prevent="openPage('active')" href="#" :class="currentTab === 'active' ? 'btn btn-sm btn-primary' : 'btn btn-sm btn-outline'" aria-current="page">
                            <svg class="w-4 h-4 sm:mr-2 mr-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.03v13m0-13c-2.819-.831-4.715-1.076-8.029-1.023A.99.99 0 0 0 3 6v11c0 .563.466 1.014 1.03 1.007 3.122-.043 5.018.212 7.97 1.023m0-13c2.819-.831 4.715-1.076 8.029-1.023A.99.99 0 0 1 21 6v11c0 .563-.466 1.014-1.03 1.007-3.122-.043-5.018.212-7.97 1.023"/>
                            </svg>
                            <span class="sm:block hidden">Активные</span>
                        </a>
                    </li>
                    <li class="me-2">
                        <a @click.prevent="openPage('archived')" href="#" :class="currentTab === 'archived' ? 'btn btn-sm btn-primary' : 'btn btn-sm btn-outline'" aria-current="page">
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
                <div class="overflow-x-auto card bg-base-100 shadow">
                    <table class="table table-md">
                        <thead class="text-xs uppercase bg-base-300">
                            <tr>
                                <th scope="col">
                                    ID
                                </th>
                                <th scope="col" class="flex items-center">
                                    Реквизит
                                    <div class="inline-flex items-center ml-2">
                                        <input type="checkbox" v-model="displayShortDetail" class="toggle toggle-primary toggle-xs">
                                    </div>
                                </th>
                                <th scope="col">
                                    {{viewStore.isAdminViewMode ? 'Профиль' : 'Устройство'}}
                                </th>
                                <th scope="col" class="text-nowrap">
                                    Сделок
                                </th>
                                <th scope="col" class="text-nowrap" v-if="viewStore.isAdminViewMode || isVipUser">
                                    Лимиты
                                </th>
                                <th scope="col" class="text-nowrap">
                                    Интервал
                                </th>
                                <th scope="col" class="text-nowrap">
                                    Лимит
                                </th>
                                <th scope="col">
                                    Статус
                                </th>
                                <th scope="col" class="flex justify-center">
                                    <span class="sr-only">Действия</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="payment_detail in paymentDetails.data">
                                <th scope="row" class="font-medium whitespace-nowrap">{{ payment_detail.id }}</th>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <GatewayLogo :img_path="payment_detail.payment_gateway.logo_path" :name="payment_detail.payment_gateway.name" class="w-10 h-10"/>
                                        <PaymentDetail
                                            :detail="payment_detail.detail"
                                            :type="payment_detail.detail_type"
                                            :name="payment_detail.name"
                                            :short="displayShortDetail"
                                        ></PaymentDetail>
                                    </div>
                                </td>
                                <td

                                >
                                    <div>
                                        <div
                                            v-if="viewStore.isAdminViewMode"
                                            class="flex items-center gap-2 text-nowrap"
                                        >
                                            <svg class="w-5 h-5 text-info transition" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-width="1.5" d="M7 17v1a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1a3 3 0 0 0-3-3h-4a3 3 0 0 0-3 3Zm8-9a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                            </svg>
                                            <span>{{ payment_detail.owner_email }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-nowrap">
                                            <svg class="w-4 h-4 ml-0.5 mr-0.5 text-info transition" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 15h12M6 6h12m-6 12h.01M7 21h10a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1v16a1 1 0 0 0 1 1Z"/>
                                            </svg>
                                            <span>{{ payment_detail.device_name }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td
                                    class="text-nowrap"
                                >
                                    <div class="flex items-center space-x-2">
                                        <div class="relative">
                                            <span
                                                class="text-sm font-semibold"
                                                :class="{
                                                    'text-success': payment_detail.pending_orders_count / payment_detail.max_pending_orders_quantity < 0.5,
                                                    'text-warning': payment_detail.pending_orders_count / payment_detail.max_pending_orders_quantity >= 0.5 && payment_detail.pending_orders_count / payment_detail.max_pending_orders_quantity < 0.8,
                                                    'text-error': payment_detail.pending_orders_count / payment_detail.max_pending_orders_quantity >= 0.8
                                                }"
                                            >
                                                {{payment_detail.pending_orders_count}}
                                            </span>
                                            <span class="mx-1 opacity-70">из</span>
                                            <span class="text-sm font-semibold">
                                                {{payment_detail.max_pending_orders_quantity}}
                                            </span>
                                        </div>
<!--                                        <div v-if="payment_detail.pending_orders_count / payment_detail.max_pending_orders_quantity >= 0.8" class="animate-pulse">
                                            <svg class="w-5 h-5 text-red-500 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <rect x="2" y="7" width="16" height="10" rx="2" stroke="currentColor" stroke-width="2"/>
                                                <rect x="4" y="9" width="12" height="6" fill="currentColor" stroke="none"/>
                                                <rect x="18" y="10" width="2" height="4" rx="0.5" stroke="currentColor" stroke-width="2"/>
                                            </svg>
                                        </div>
                                        <div v-else-if="payment_detail.pending_orders_count / payment_detail.max_pending_orders_quantity >= 0.5">
                                            <svg class="w-5 h-5 text-yellow-500 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <rect x="2" y="7" width="16" height="10" rx="2" stroke="currentColor" stroke-width="2"/>
                                                <rect x="4" y="11" width="6" height="2" fill="currentColor" stroke="none"/>
                                                <rect x="18" y="10" width="2" height="4" rx="0.5" stroke="currentColor" stroke-width="2"/>
                                            </svg>
                                        </div>
                                        <div v-else-if="payment_detail.pending_orders_count / payment_detail.max_pending_orders_quantity >= 0">
                                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <rect x="2" y="7" width="16" height="10" rx="2" stroke="currentColor" stroke-width="2"/>
                                                <rect x="4" y="11" width="2" height="2" fill="currentColor" stroke="none"/>
                                                <rect x="18" y="10" width="2" height="4" rx="0.5" stroke="currentColor" stroke-width="2"/>
                                            </svg>
                                        </div>-->
                                    </div>
                                </td>
                                <td v-if="viewStore.isAdminViewMode || isVipUser">
                                    <div class="text-nowrap ">
                                        <span class="text-base-content/70">min: </span>
                                        {{ payment_detail.min_order_amount !== null ? payment_detail.min_order_amount : '&infin;' }}
                                    </div>
                                    <div class="text-nowrap">
                                        <span class="text-base-content/70">max: </span>
                                        {{ payment_detail.max_order_amount !== null ? payment_detail.max_order_amount : '&infin;' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="text-nowrap ">
                                        <span class="text-base-content"></span>
                                        {{ payment_detail.order_interval_minutes !== null ? payment_detail.order_interval_minutes + ' мин' : '-' }}
                                    </div>
                                </td>
                                <td>
                                    <PaymentDetailLimit :current_daily_limit="payment_detail.current_daily_limit" :daily_limit="payment_detail.daily_limit"></PaymentDetailLimit>
                                </td>
                                <td>
                                    <div class="flex items-center">
                                        <label class="label cursor-pointer justify-start gap-3">
                                            <input type="checkbox" :checked="payment_detail.is_active" class="toggle toggle-success" @change="toggleActive(payment_detail.id)" :disabled="detailActiveToggleForm.processing || toggleBlocked || currentTab === 'archived'">
                                        </label>
                                    </div>
                                </td>
                                <td class="text-right">
                                    <TableActionsDropdown v-if="currentTab === 'active'">
                                        <TableAction @click="openEditModal(payment_detail)">
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

        <PaymentDetailCreateModal />
        <PaymentDetailEditModal />
        <ConfirmModal/>
    </div>
</template>
