<script setup>
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { usePage } from '@inertiajs/vue3';
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import HeadllesTable from "@/Components/HeadlesTable/HeadllesTable.vue";
import HeadlessTableTr from "@/Components/HeadlesTable/HeadlessTableTr.vue";
import HeadlessTableTh from "@/Components/HeadlesTable/HeadlessTableTh.vue";
import HeadlessTableTd from "@/Components/HeadlesTable/HeadlessTableTd.vue";
import {useViewStore} from "@/store/view.js";
import IsActiveStatus from "@/Components/IsActiveStatus.vue";
import EditAction from "@/Components/Table/EditAction.vue";
import PaymentDetailLimit from "@/Components/PaymentDetailLimit.vue";
import PaymentDetail from "@/Components/PaymentDetail.vue";
import ShowAction from "@/Components/Table/ShowAction.vue";

const viewStore = useViewStore();

const merchants = usePage().props.merchants;

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Мерчанты" />

        <MainTableSection
            title="Мерчанты"
            :data="merchants"
        >
            <template v-slot:button>
                <div v-if="viewStore.isMerchantViewMode">
                    <button
                        @click="router.visit(route('merchants.create'))"
                        type="button"
                        class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800"
                    >
                        Создать мерчант
                    </button>
                </div>
            </template>
            <template v-slot:body>
                <div class="relative overflow-x-auto shadow-md sm:rounded-lg" v-if="viewStore.isAdminViewMode">
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
                                    Владелец
                                </th>
                                <th scope="col" class="px-6 py-3" v-if="viewStore.isAdminViewMode">
                                    Статус
                                </th>
                                <th scope="col" class="px-6 py-3 flex justify-center">
                                    <span class="sr-only">Действия</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="merchant in merchants.data" class="bg-white border-b last:border-none dark:bg-gray-800 dark:border-gray-700">
                            <th scope="row" class="px-6 py-3 font-medium whitespace-nowrap text-gray-900 dark:text-gray-200">
                                {{ merchant.id }}
                            </th>
                            <td class="px-6 py-3">
                               <div class="text-gray-900 dark:text-gray-200">{{merchant.name}}</div>
                                <div class="text-xs">{{merchant.domain}}</div>
                            </td>
                            <td class="px-6 py-3">
                                {{merchant.owner.email}}
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex items-center text-nowrap">
                                    <template v-if="!merchant.validated_at">
                                        <div class="h-2.5 w-2.5 rounded-full bg-yellow-400 dark:bg-yellow-500 me-2"></div> На модерации
                                    </template>
                                    <template v-else-if="merchant.banned_at">
                                        <div class="h-2.5 w-2.5 rounded-full bg-red-500 dark:bg-red-500 me-2"></div> Заблокирован
                                    </template>
                                    <template v-else-if="merchant.active">
                                        <div class="h-2.5 w-2.5 rounded-full bg-green-400 dark:bg-green-500 me-2"></div> Включен
                                    </template>
                                    <template v-else>
                                        <div class="h-2.5 w-2.5 rounded-full bg-red-500 dark:bg-red-500 me-2"></div> Выключен
                                    </template>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <ShowAction :link="route('admin.merchants.show', merchant.id)"></ShowAction>
                            </td>
                        </tr>
                        </tbody>
                    </table>
<!--                    <HeadllesTable>
                        <HeadlessTableTr
                            v-for="merchant in merchants.data"
                            @click="router.visit(route('admin.merchants.show', merchant.id))"
                            :hoverable="true"
                        >
                            <HeadlessTableTh class="text-gray-900 dark:text-gray-200">#{{ merchant.id }}</HeadlessTableTh>
                            <HeadlessTableTd>
                                <div class="text-gray-900 dark:text-gray-200">{{merchant.name}}</div>
                                <div class="text-gray-400 dark:text-gray-500 text-xs">{{merchant.domain}}</div>
                            </HeadlessTableTd>
                            <HeadlessTableTd class="text-gray-900 dark:text-gray-200">{{merchant.owner.email}}</HeadlessTableTd>
                            <HeadlessTableTd>
                                <div class="flex items-center text-nowrap text-gray-900 dark:text-gray-200">
                                    <template v-if="! merchant.validated_at">
                                        <div class="h-2.5 w-2.5 rounded-full bg-yellow-400 dark:bg-yellow-500 me-2"></div> На модерации
                                    </template>
                                    <template v-else-if="merchant.banned_at">
                                        <div class="h-2.5 w-2.5 rounded-full bg-red-500 dark:bg-red-500 me-2"></div> Заблокирован
                                    </template>
                                    <template v-else-if="merchant.active">
                                        <div class="h-2.5 w-2.5 rounded-full bg-green-400 dark:bg-green-500 me-2"></div> Включен
                                    </template>
                                    <template v-else>
                                        <div class="h-2.5 w-2.5 rounded-full bg-red-500 dark:bg-red-500 me-2"></div> Выключен
                                    </template>
                                </div>
                            </HeadlessTableTd>
                        </HeadlessTableTr>
                    </HeadllesTable>-->
                </div>

                <section v-if="viewStore.isMerchantViewMode" class="antialiased dark:bg-gray-900">
                    <div class="mx-auto">
                        <div class="mb-4 grid gap-4 md:mb-8 lg:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                            <div
                                v-for="merchant in merchants.data"
                                class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800"
                            >
                                <div>
                                    <div class="text-lg font-semibold leading-tight text-gray-900 dark:text-gray-200">
                                        {{ merchant.name }}
                                    </div>

                                    <div class="mt-2 flex items-center gap-2">
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">доход за сегодня</p>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ merchant.today_profit }} {{ merchant.profit_currency?.toUpperCase() }}</p>
                                    </div>

                                    <p class="mt-2 text-lg font-extrabold leading-tight text-blue-500 dark:text-blue-500">{{ merchant.domain }}</p>

                                    <div class="mt-4 text-sm flex items-end justify-between">
                                        <ul class="flex items-center">
                                            <div class="flex items-center text-nowrap text-gray-900 dark:text-gray-200">
                                                <template v-if="! merchant.validated_at">
                                                    <div class="h-2.5 w-2.5 rounded-full bg-yellow-400 dark:bg-yellow-500 me-2"></div> На модерации
                                                </template>
                                                <template v-else-if="merchant.banned_at">
                                                    <div class="h-2.5 w-2.5 rounded-full bg-red-500 dark:bg-red-500 me-2"></div> Заблокирован
                                                </template>
                                                <template v-else-if="merchant.active">
                                                    <div class="h-2.5 w-2.5 rounded-full bg-green-400 dark:bg-green-500 me-2"></div> Включен
                                                </template>
                                                <template v-else>
                                                    <div class="h-2.5 w-2.5 rounded-full bg-red-500 dark:bg-red-500 me-2"></div> Выключен
                                                </template>
                                            </div>
                                        </ul>

                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-lg bg-primary-700 px-0 py-0 text-sm font-medium text-blue-500 dark:text-gray-200 hover:text-blue-700 dark:hover:text-blue-500 hover:bg-primary-800 focus:outline-none focus:ring-4  focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800"
                                            @click.prevent="router.visit(route('merchants.show', merchant.id))"
                                        >
                                            Перейти
                                            <svg class="ml-2 h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m14 0-4 4m4-4-4-4"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </template>
        </MainTableSection>
    </div>
</template>
