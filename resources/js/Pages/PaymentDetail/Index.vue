<script setup>
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { usePage } from '@inertiajs/vue3';
import IsActiveStatus from "@/Components/IsActiveStatus.vue";
import EditAction from "@/Components/Table/EditAction.vue";
import PaymentDetail from "@/Components/PaymentDetail.vue";
import PaymentDetailLimit from "@/Components/PaymentDetailLimit.vue";
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import HeadllesTable from "@/Components/HeadlesTable/HeadllesTable.vue";
import HeadlessTableTr from "@/Components/HeadlesTable/HeadlessTableTr.vue";
import HeadlessTableTh from "@/Components/HeadlesTable/HeadlessTableTh.vue";
import HeadlessTableTd from "@/Components/HeadlesTable/HeadlessTableTd.vue";
import {useViewStore} from "@/store/view.js";
import AddMobileIcon from "@/Components/AddMobileIcon.vue";

const viewStore = useViewStore();
const payment_details = usePage().props.paymentDetails;

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Реквизиты" />

        <MainTableSection
            title="Реквизиты"
            :data="payment_details"
        >
            <template v-slot:button>
                <button
                    @click="router.visit(route(viewStore.adminPrefix + 'payment-details.create'))"
                    type="button"
                    class="hidden md:block text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800"
                >
                    Создать реквизиты
                </button>
                <AddMobileIcon
                    @click="router.visit(route(viewStore.adminPrefix + 'payment-details.create'))"
                />
            </template>
            <template v-slot:body>
                <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
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
                            <tr v-for="payment_detail in payment_details.data" class="bg-white border-b last:border-none dark:bg-gray-800 dark:border-gray-700">
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
                                <td class="px-6 py-3">
                                    <PaymentDetailLimit :current_daily_limit="payment_detail.current_daily_limit" :daily_limit="payment_detail.daily_limit"></PaymentDetailLimit>
                                </td>
                                <td class="px-6 py-3">
                                    <IsActiveStatus :is_active="payment_detail.is_active"></IsActiveStatus>
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
<!--                <div class="relative overflow-x-auto">
                    <HeadllesTable>
                        <HeadlessTableTr v-for="payment_detail in payment_details.data">
                            <HeadlessTableTh>#{{ payment_detail.id }}</HeadlessTableTh>
                            <HeadlessTableTd>
                                <div class="text-nowrap text-gray-900 dark:text-gray-200">{{ payment_detail.name }}</div>
                                <div class="text-nowrap text-gray-500 dark:text-gray-500">
                                    {{ payment_detail.payment_gateway_name }}
                                </div>
                            </HeadlessTableTd>
                            <HeadlessTableTd class="text-gray-900 dark:text-gray-200">
                                <PaymentDetail :detail="payment_detail.detail" :type="payment_detail.detail_type"></PaymentDetail>
                            </HeadlessTableTd>
                            <HeadlessTableTd v-if="viewStore.isAdminViewMode" class="text-gray-900 dark:text-gray-200">
                                {{ payment_detail.owner_email }}
                            </HeadlessTableTd>
                            <HeadlessTableTd>
                                <PaymentDetailLimit :current_daily_limit="payment_detail.current_daily_limit" :daily_limit="payment_detail.daily_limit"></PaymentDetailLimit>
                            </HeadlessTableTd>
                            <HeadlessTableTd>
                                <IsActiveStatus :is_active="payment_detail.is_active"></IsActiveStatus>
                            </HeadlessTableTd>
                            <HeadlessTableTd>
                                <div class="flex justify-center gap-2">
                                    <EditAction :link="route(viewStore.adminPrefix + 'payment-details.edit', payment_detail.id)"></EditAction>
                                </div>
                            </HeadlessTableTd>
                        </HeadlessTableTr>
                    </HeadllesTable>
                </div>-->
            </template>
        </MainTableSection>
    </div>
</template>
