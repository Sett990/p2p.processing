<script setup>
import {Head, router} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { usePage } from '@inertiajs/vue3';
import IsActiveStatus from "@/Components/IsActiveStatus.vue";
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import EditAction from "@/Components/Table/EditAction.vue";
import AddMobileIcon from "@/Components/AddMobileIcon.vue";
import PaymentDetail from "@/Components/PaymentDetail.vue";
import PaymentDetailLimit from "@/Components/PaymentDetailLimit.vue";

const payoutOffers = usePage().props.payoutOffers;

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Выплаты" />

        <MainTableSection
            title="Выплаты"
            :data="payoutOffers"
        >
            <template v-slot:button>
                <button
                    @click="router.visit(route('payout-offers.create'))"
                    type="button"
                    class="hidden md:block text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-xl  text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800"
                >
                    Создать предложение
                </button>
                <AddMobileIcon
                    @click="router.visit(route('payout-offers.create'))"
                />
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
                                Лимиты
                            </th>
                            <th scope="col" class="px-6 py-3 text-nowrap">
                                Метод
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Типы реквизитов
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
                            <td class="px-6 py-3 text-right">
                                <div class="flex justify-center gap-2">

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
