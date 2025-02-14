<script setup>
import {Head, router, useForm} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { usePage } from '@inertiajs/vue3';
import EditAction from "@/Components/Table/EditAction.vue";
import PaymentDetail from "@/Components/PaymentDetail.vue";
import PaymentDetailLimit from "@/Components/PaymentDetailLimit.vue";
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import {useViewStore} from "@/store/view.js";
import AddMobileIcon from "@/Components/AddMobileIcon.vue";
import {ref} from "vue";
import InputFilter from "@/Components/Filters/Pertials/InputFilter.vue";
import FiltersPanel from "@/Components/Filters/FiltersPanel.vue";
import FilterCheckbox from "@/Components/Filters/Pertials/FilterCheckbox.vue";

const viewStore = useViewStore();
const paymentDetails = ref(usePage().props.paymentDetails)
const filters = ref(usePage().props.filters);

const detailActiveToggleForm = useForm({});

const toggleActive = (detail_id) => {
    detailActiveToggleForm.patch(route('payment-details.toggle-active', detail_id), {
        preserveScroll: true,
        onSuccess: (result) => {
            paymentDetails.value = result.props.paymentDetails;
        },
    });
};

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
                <FiltersPanel name="payment-details" :filters="filters">
                    <InputFilter
                        v-model="filters.id"
                        placeholder="ID реквизита"
                    />
                    <InputFilter
                        v-model="filters.name"
                        placeholder="Название"
                    />
                    <InputFilter
                        v-model="filters.paymentDetail"
                        placeholder="Реквизит"
                    />
                    <InputFilter
                        v-if="viewStore.isAdminViewMode"
                        v-model="filters.user"
                        placeholder="Пользователь"
                    />
                    <FilterCheckbox
                        v-model="filters.active"
                        title="Только активные"
                    />
                </FiltersPanel>
            </template>
            <template v-slot:body>
                <div class="relative overflow-x-auto shadow-md sm:rounded-table ">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">
                                    ID
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Название
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Реквизит
                                </th>
                                <th scope="col" class="px-6 py-3" v-if="viewStore.isAdminViewMode">
                                    Трейдер
                                </th>
                                <th scope="col" class="px-6 py-3 text-nowrap">
                                    Сделок
                                </th>
                                <th scope="col" class="px-6 py-3 text-nowrap">
                                    Дневной лимит
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
                                    <div class="text-nowrap text-gray-900 dark:text-gray-200">
                                        {{ payment_detail.name }}
                                    </div>
                                    <div class="text-nowrap text-xs">
                                        {{ payment_detail.payment_gateway_name }}
                                    </div>
                                </td>
                                <td class="px-6 py-3">
                                    <PaymentDetail :detail="payment_detail.detail" :type="payment_detail.detail_type"></PaymentDetail>
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
                                    {{ payment_detail.pending_orders_count }}/{{ payment_detail.max_pending_orders_quantity }}
                                </td>
                                <td class="px-6 py-3">
                                    <PaymentDetailLimit :current_daily_limit="payment_detail.current_daily_limit" :daily_limit="payment_detail.daily_limit"></PaymentDetailLimit>
                                </td>
                                <td class="px-6 py-3">
                                    <div class="flex items-center">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" :checked="payment_detail.is_active" class="sr-only peer" @change="toggleActive(payment_detail.id)" :disabled="detailActiveToggleForm.processing">
                                            <div class="relative w-9 h-5 bg-gray-200 rounded-full peer dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-green-600 dark:peer-checked:bg-green-600"></div>
                                        </label>
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <div class="flex justify-center gap-2">
                                        <EditAction :link="route(viewStore.adminPrefix + 'payment-details.edit', payment_detail.id)"></EditAction>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </MainTableSection>
    </div>
</template>
