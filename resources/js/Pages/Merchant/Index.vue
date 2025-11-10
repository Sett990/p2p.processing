<script setup>
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { usePage } from '@inertiajs/vue3';
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import {useViewStore} from "@/store/view.js";
import ShowAction from "@/Components/Table/ShowAction.vue";
import {useModalStore} from "@/store/modal.js";
import MerchantCreateModal from "@/Modals/Merchant/MerchantCreateModal.vue";
import { ref } from 'vue';

const viewStore = useViewStore();
const modalStore = useModalStore();

const merchants = ref(usePage().props.merchants);

router.on('success', () => {
    merchants.value = usePage().props.merchants;
});

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
                        @click="modalStore.openMerchantCreateModal()"
                        type="button"
                        class="btn btn-primary"
                    >
                        Создать мерчант
                    </button>
                </div>
            </template>
            <template v-slot:body>
                <div class="overflow-x-auto card bg-base-100 shadow" v-if="viewStore.isAdminViewMode">
                    <table class="table table-md">
                        <thead class="text-xs uppercase bg-base-300">
                            <tr>
                                <th>ID</th>
                                <th>Название</th>
                                <th>Владелец</th>
                                <th v-if="viewStore.isAdminViewMode">Статус</th>
                                <th class="text-center">
                                    <span class="sr-only">Действия</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="merchant in merchants.data">
                                <th class="whitespace-nowrap">{{ merchant.id }}</th>
                                <td>
                                    <div class="truncate max-w-48">{{ merchant.name }}</div>
                                    <div class="text-xs truncate max-w-36 text-base-content/70">{{ merchant.domain }}</div>
                                </td>
                                <td>
                                    {{ merchant.owner.email }}
                                </td>
                                <td>
                                    <div class="flex items-center text-nowrap">
                                        <template v-if="!merchant.validated_at">
                                            <div class="h-2.5 w-2.5 rounded-full bg-warning me-2"></div> На модерации
                                        </template>
                                        <template v-else-if="merchant.banned_at">
                                            <div class="h-2.5 w-2.5 rounded-full bg-error me-2"></div> Заблокирован
                                        </template>
                                        <template v-else-if="merchant.active">
                                            <div class="h-2.5 w-2.5 rounded-full bg-success me-2"></div> Включен
                                        </template>
                                        <template v-else>
                                            <div class="h-2.5 w-2.5 rounded-full bg-error me-2"></div> Выключен
                                        </template>
                                    </div>
                                </td>
                                <td class="text-right">
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
                                <div class="card-body p-5 sm:p-6">
                                    <h3 class="card-title truncate">{{ merchant.name }}</h3>

                                    <div class="mt-1 flex items-center gap-2">
                                        <p class="text-sm text-base-content/70">доход за сегодня</p>
                                        <p class="text-sm font-medium">{{ merchant.today_profit }} {{ merchant.profit_currency?.toUpperCase() }}</p>
                                    </div>

                                    <p class="mt-2 text-lg font-semibold leading-tight text-primary truncate">
                                        {{ merchant.domain }}
                                    </p>

                                    <div class="mt-4 text-sm flex items-end justify-between">
                                        <div class="flex items-center text-nowrap">
                                            <template v-if="! merchant.validated_at">
                                                <div class="h-2.5 w-2.5 rounded-full bg-warning me-2"></div> На модерации
                                            </template>
                                            <template v-else-if="merchant.banned_at">
                                                <div class="h-2.5 w-2.5 rounded-full bg-error me-2"></div> Заблокирован
                                            </template>
                                            <template v-else-if="merchant.active">
                                                <div class="h-2.5 w-2.5 rounded-full bg-success me-2"></div> Включен
                                            </template>
                                            <template v-else>
                                                <div class="h-2.5 w-2.5 rounded-full bg-error me-2"></div> Выключен
                                            </template>
                                        </div>

                                        <button
                                            type="button"
                                            class="link link-primary no-underline hover:underline inline-flex items-center"
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
        <MerchantCreateModal />
    </div>
</template>
