<script setup>
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { usePage } from '@inertiajs/vue3';
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import {useViewStore} from "@/store/view.js";
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
                    <button @click="router.visit(route('merchants.create'))" type="button" class="btn btn-primary">
                        Создать мерчант
                    </button>
                </div>
            </template>
            <template v-slot:body>
                <div class="overflow-x-auto card bg-base-100 shadow" v-if="viewStore.isAdminViewMode">
                    <table class="table table-sm">
                        <thead class="text-xs uppercase bg-base-300">
                            <tr>
                                <th class="px-6 py-3">ID</th>
                                <th class="px-6 py-3">Название</th>
                                <th class="px-6 py-3">Владелец</th>
                                <th v-if="viewStore.isAdminViewMode" class="px-6 py-3">Статус</th>
                                <th class="px-6 py-3 text-center">
                                    <span class="sr-only">Действия</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="merchant in merchants.data" :key="merchant.id">
                                <th class="whitespace-nowrap px-6 py-3">{{ merchant.id }}</th>
                                <td class="px-6 py-3">
                                    <div class="truncate max-w-48">{{ merchant.name }}</div>
                                    <div class="text-xs truncate max-w-36">{{ merchant.domain }}</div>
                                </td>
                                <td class="px-6 py-3">{{ merchant.owner.email }}</td>
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-2">
                                        <template v-if="!merchant.validated_at">
                                            <span class="badge badge-warning badge-sm"></span>
                                            <span>На модерации</span>
                                        </template>
                                        <template v-else-if="merchant.banned_at">
                                            <span class="badge badge-error badge-sm"></span>
                                            <span>Заблокирован</span>
                                        </template>
                                        <template v-else-if="merchant.active">
                                            <span class="badge badge-success badge-sm"></span>
                                            <span>Включен</span>
                                        </template>
                                        <template v-else>
                                            <span class="badge badge-error badge-sm"></span>
                                            <span>Выключен</span>
                                        </template>
                                    </div>
                                </td>
                                <td class="text-right px-6 py-3">
                                    <ShowAction :link="route('admin.merchants.show', merchant.id)"></ShowAction>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <section v-if="viewStore.isMerchantViewMode" class="antialiased">
                    <div class="mx-auto">
                        <div class="mb-4 grid gap-4 md:mb-8 grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-3">
                            <div
                                v-for="(merchant, index) in merchants.data"
                                class="card bg-base-100 shadow"
                            >
                                <div class="card-body">
                                    <h2 class="card-title truncate">{{ merchant.name }}</h2>
                                    <div class="mt-1 flex items-center gap-2">
                                        <p class="text-sm text-base-content/60">доход за сегодня</p>
                                        <p class="text-sm font-medium">{{ merchant.today_profit }} {{ merchant.profit_currency?.toUpperCase() }}</p>
                                    </div>
                                    <p class="mt-2 text-lg font-extrabold text-primary truncate">{{ merchant.domain }}</p>
                                    <div class="mt-4 text-sm flex items-end justify-between">
                                        <div class="flex items-center gap-2">
                                            <template v-if="! merchant.validated_at">
                                                <span class="badge badge-warning badge-xs"></span>
                                                <span>На модерации</span>
                                            </template>
                                            <template v-else-if="merchant.banned_at">
                                                <span class="badge badge-error badge-xs"></span>
                                                <span>Заблокирован</span>
                                            </template>
                                            <template v-else-if="merchant.active">
                                                <span class="badge badge-success badge-xs"></span>
                                                <span>Включен</span>
                                            </template>
                                            <template v-else>
                                                <span class="badge badge-error badge-xs"></span>
                                                <span>Выключен</span>
                                            </template>
                                        </div>
                                        <div class="card-actions justify-end">
                                            <button type="button" class="btn btn-ghost btn-sm" @click.prevent="router.visit(route('merchants.show', merchant.id))">
                                                Перейти
                                                <svg class="ml-1 h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m14 0-4 4m4-4-4-4"/>
                                                </svg>
                                            </button>
                                        </div>
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
