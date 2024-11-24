<script setup>
import {Head, router} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { usePage } from '@inertiajs/vue3';
import IsActiveStatus from "@/Components/IsActiveStatus.vue";
import EditAction from "@/Components/Table/EditAction.vue";
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import AddMobileIcon from "@/Components/AddMobileIcon.vue";

const payment_gateways = usePage().props.paymentGateways;

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Платежные методы" />

        <MainTableSection
            title="Платежные методы"
            :data="payment_gateways"
        >
            <template v-slot:button>
                <button
                    @click="router.visit(route('admin.payment-gateways.create'))"
                    type="button"
                    class="hidden md:block text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800"
                >
                    Создать метод
                </button>
                <AddMobileIcon
                    @click="router.visit(route('admin.payment-gateways.create'))"
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
                                Код
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Валюта
                            </th>
                            <th scope="col" class="px-6 py-3 text-nowrap">
                                Мин/Макс лимит
                            </th>
                            <th scope="col" class="px-6 py-3 text-nowrap">
                                Комиссия %
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Парсеры
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
                        <tr v-for="payment_gateway in payment_gateways.data" class="bg-white border-b last:border-none dark:bg-gray-800 dark:border-gray-700">
                            <th scope="row" class="px-6 py-3 font-medium whitespace-nowrap text-gray-900 dark:text-gray-200">
                                {{ payment_gateway.id }}
                            </th>
                            <td class="px-6 py-3 text-nowrap">
                                {{ payment_gateway.name }}
                            </td>
                            <td class="px-6 py-3">
                                {{ payment_gateway.code }}
                            </td>
                            <td class="px-6 py-3">
                                {{ payment_gateway.currency }}
                            </td>
                            <td class="px-6 py-3">
                                {{ payment_gateway.min_limit }}/{{ payment_gateway.max_limit }}
                            </td>
                            <td class="px-6 py-3">
                                {{ payment_gateway.commission_rate }}% / {{ payment_gateway.service_commission_rate }}%
                            </td>
                            <td class="px-6 py-3">
                                {{ payment_gateway.sms_parsers_count }}
                            </td>
                            <td class="px-6 py-3 text-nowrap">
                                <IsActiveStatus :is_active="payment_gateway.is_active"></IsActiveStatus>
                            </td>
                            <td class="px-6 py-3 text-nowrap text-right">
                                <EditAction :link="route('admin.payment-gateways.edit', payment_gateway.id)"></EditAction>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </MainTableSection>
    </div>
</template>
