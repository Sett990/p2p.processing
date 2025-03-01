<script setup>
import {Head, router, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import GoBackButton from "@/Components/GoBackButton.vue";
import {onMounted, ref} from "vue";
import Statistics from "@/Pages/Merchant/Tabs/Statistics.vue";
import Payments from "@/Pages/Merchant/Tabs/Payments.vue";
import Settings from "@/Pages/Merchant/Tabs/Settings.vue";
import {useViewStore} from "@/store/view.js";

const tab = ref('statistics');
const viewStore = useViewStore();
const merchant = usePage().props.merchant;

const openPage = (page = null) => {
    let data = {
        tab: tab.value
    };
    if (page) {
        data.page = page;
    }
    router.visit(route(route().current(), merchant.id), { data: data})
}

onMounted(() => {
    let urlParams = new URLSearchParams(window.location.search);
    tab.value = urlParams.get('tab') ?? 'statistics'
})

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head :title="'Мерчант - ' + merchant.name"/>

        <div class="mx-auto space-y-4">
            <div class="mb-3">
                <GoBackButton @click="router.visit(route(viewStore.adminPrefix + 'merchants.index'))"/>
            </div>
            <div>
                <section>
                    <div>
                        <div class="mt-6 space-y-6">
                            <ul class="flex flex-wrap text-sm font-medium text-center text-gray-500 dark:text-gray-400">
                                <li class="me-2">
                                    <a @click.prevent="tab = 'statistics'; openPage()" href="#" :class="tab === 'statistics' ? 'shadow inline-flex px-4 py-2 text-white bg-blue-600 rounded-xl active' : 'border border-gray-200 dark:border-gray-700 inline-flex px-4 py-2 rounded-xl hover:text-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-white'" aria-current="page">
                                        <svg class="w-4 h-4 sm:mr-2 mr-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15v4m6-6v6m6-4v4m6-6v6M3 11l6-5 6 5 5.5-5.5"/>
                                        </svg>
                                        <span class="sm:block hidden">Статистика</span>
                                    </a>
                                </li>
                                <li class="me-2">
                                    <a @click.prevent="tab = 'payments'; openPage(1)" href="#" :class="tab === 'payments' ? 'shadow inline-flex px-4 py-2 text-white bg-blue-600 rounded-xl active' : 'border border-gray-200 dark:border-gray-700 inline-flex px-4 py-2 rounded-xl hover:text-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-white'" aria-current="page">
                                        <svg class="w-4 h-4 sm:mr-2 mr-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17.345a4.76 4.76 0 0 0 2.558 1.618c2.274.589 4.512-.446 4.999-2.31.487-1.866-1.273-3.9-3.546-4.49-2.273-.59-4.034-2.623-3.547-4.488.486-1.865 2.724-2.899 4.998-2.31.982.236 1.87.793 2.538 1.592m-3.879 12.171V21m0-18v2.2"/>
                                        </svg>
                                        <span class="sm:block hidden">Платежи</span>
                                    </a>
                                </li>
                                <li class="me-2">
                                    <a @click.prevent="tab = 'settings'; openPage()" href="#" :class="tab === 'settings' ? 'shadow inline-flex px-4 py-2 text-white bg-blue-600 rounded-xl active' : 'border border-gray-200 dark:border-gray-700 inline-flex px-4 py-2 rounded-xl hover:text-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-white'" aria-current="page">
                                        <svg class="w-4 h-4 sm:mr-2 mr-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13v-2a1 1 0 0 0-1-1h-.757l-.707-1.707.535-.536a1 1 0 0 0 0-1.414l-1.414-1.414a1 1 0 0 0-1.414 0l-.536.535L14 4.757V4a1 1 0 0 0-1-1h-2a1 1 0 0 0-1 1v.757l-1.707.707-.536-.535a1 1 0 0 0-1.414 0L4.929 6.343a1 1 0 0 0 0 1.414l.536.536L4.757 10H4a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h.757l.707 1.707-.535.536a1 1 0 0 0 0 1.414l1.414 1.414a1 1 0 0 0 1.414 0l.536-.535 1.707.707V20a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1v-.757l1.707-.708.536.536a1 1 0 0 0 1.414 0l1.414-1.414a1 1 0 0 0 0-1.414l-.535-.536.707-1.707H20a1 1 0 0 0 1-1Z"/>
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/>
                                        </svg>
                                        <span class="sm:block hidden">Настройки</span>
                                    </a>
                                </li>
                            </ul>
                            <div v-if="! merchant.validated_at" class="flex items-center p-4 mb-4 text-sm text-yellow-800 shadow-md rounded-alert bg-yellow-50 dark:bg-gray-800 dark:text-yellow-300" role="alert">
                                <svg class="flex-shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                                </svg>
                                <span class="sr-only">Инфо</span>
                                <div>
                                    <span class="font-medium">Внимание!</span> Мерчант находится на модерации.
                                </div>
                            </div>
                            <div v-if="merchant.banned_at" class="flex items-center p-4 mb-4 text-sm text-red-800 shadow-md rounded-alert bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
                                <svg class="flex-shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                                </svg>
                                <span class="sr-only">Инфо</span>
                                <div>
                                    <span class="font-medium">Внимание!</span> Мерчант заблокирован администратором.
                                </div>
                            </div>
                            <Statistics v-show="tab === 'statistics'"/>
                            <Payments v-show="tab === 'payments'" @openPage="openPage"/>
                            <Settings v-show="tab === 'settings'"/>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>
