<script setup>
import {usePage, router, Link} from '@inertiajs/vue3';
import {onMounted, ref} from 'vue'
import { initFlowbite } from 'flowbite'
import ViewModeSwitcher from "@/Layouts/Partials/ViewModeSwitcher.vue";
import TraderMenu from "@/Layouts/Partials/TraderMenu.vue";
import AdminMenu from "@/Layouts/Partials/AdminMenu.vue";
import NavBar from "@/Layouts/Partials/NavBar.vue";
import MerchantMenu from "@/Layouts/Partials/MerchantMenu.vue";
import {useViewStore} from "@/store/view.js";
import {useUserStore} from "@/store/user.js";

const viewStore = useViewStore();
const userStore = useUserStore();

const rates = ref(
    usePage().props.data.rates.sort((item) => {
        return ['rub', 'usd', 'eur'].includes(item.code)
    }).reverse()
);

const showAllRates = ref(false);

// initialize components based on data attribute selectors
onMounted(() => {
    viewStore.setTraderViewMode()

    if (route().current('admin.*')) {
        viewStore.setAdminViewMode()
    }

    //TODO это костыль для мерчантов
    if (route().current('merchant.*')) {
        viewStore.setMerchantViewMode()
    }
    if (route().current('merchants.*')) {
        viewStore.setMerchantViewMode()
    }
    if (route().current('integration.*')) {
        viewStore.setMerchantViewMode()
    }
    if (route().current('payments.*')) {
        viewStore.setMerchantViewMode()
    }

    initFlowbite();

    if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark')
    }
})

router.on('success', (event) => {
    initFlowbite();
    rates.value = usePage().props.data.rates.sort((item) => {
        return ['rub', 'usd', 'eur'].includes(item.code)
    }).reverse();
})

const openDocs = () => {
    window.open('/docs', '_blank');
}
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900 pt-10">
            <div class="container px-3 lg:px-10 mx-auto mb-5">
                <NavBar/>
            </div>

            <div class="container px-3 lg:px-10 mx-auto pt-5 pb-14">
                <div class="flex">
                    <aside class="h-full z-40 space-y-6 mr-6 hidden lg:block" aria-label="Sidebar">
                        <div class="p-5 overflow-y-auto bg-white dark:bg-gray-800 w-72 shadow-md rounded-xl">
                            <ViewModeSwitcher
                                v-if="userStore.isAdmin"
                            />
                        </div>
                        <div class="p-5 overflow-y-auto bg-white dark:bg-gray-800 w-72 shadow-md rounded-xl">
                            <TraderMenu
                                v-show="viewStore.isTraderViewMode"
                            />
                            <MerchantMenu
                                v-show="viewStore.isMerchantViewMode"
                            />
                            <AdminMenu
                                v-show="viewStore.isAdminViewMode"
                            />
                            <!--                    <div>
                                                    <ul class="pt-4 mt-4 space-y-2 font-medium border-t border-gray-200 dark:border-gray-700">
                                                        <li>
                                                            <Link @click.prevent="openDocs" href="#" class="flex items-center p-2 text-gray-900 rounded-xl  dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                                                                <svg class="w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10V7.914a1 1 0 0 1 .293-.707l3.914-3.914A1 1 0 0 1 9.914 3H18a1 1 0 0 1 1 1v6M5 19v1a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-1M10 3v4a1 1 0 0 1-1 1H5m14 9.006h-.335a1.647 1.647 0 0 1-1.647-1.647v-1.706a1.647 1.647 0 0 1 1.647-1.647L19 12M5 12v5h1.375A1.626 1.626 0 0 0 8 15.375v-1.75A1.626 1.626 0 0 0 6.375 12H5Zm9 1.5v2a1.5 1.5 0 0 1-1.5 1.5v0a1.5 1.5 0 0 1-1.5-1.5v-2a1.5 1.5 0 0 1 1.5-1.5v0a1.5 1.5 0 0 1 1.5 1.5Z"/>
                                                                </svg>
                                                                <span class="ms-3">Документация</span>
                                                            </Link>
                                                        </li>
                                                    </ul>
                                                </div>-->
                        </div>
                        <div v-show="rates.length" class="p-5 overflow-y-auto bg-white dark:bg-gray-800 w-72 shadow-md rounded-xl">
                            <div>
                                <div class="flex items-center mb-1">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Курс Tether TRC-20</span>
                                </div>
                                <div class="text-sm text-blue-800 dark:text-blue-400">
                                    <ul>
                                        <li v-for="(rate, index) in rates" v-show="index < 3 || showAllRates" class="flex justify-between items-end border-b border-gray-500 border-dotted last:border-none">
                                            <span class="text-sm mt-1 text-gray-700 dark:text-gray-200 mr-1.5">{{ rate.buy_price }}</span>
                                            <span class="text-sm text-blue-500 dark:text-blue-500">{{ rate.code.toUpperCase() }}</span>
                                        </li>
                                    </ul>
                                    <div class="flex justify-center mt-3">
                                <span @click="showAllRates = !showAllRates" class="cursor-pointer px-5">
                                    <span v-show="! showAllRates" class="text-gray-700 dark:text-gray-500 dark:hover:text-blue-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        Показать все
                                    </span>
                                    <span v-show="showAllRates" class="text-gray-700 dark:text-gray-500 dark:hover:text-blue-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        Спрятать
                                    </span>
                                </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </aside>

                    <main class="w-full lg:w-[calc(100%_-_19.5rem)]">
                        <slot />
                    </main>
                </div>
            </div>
        </div>
    </div>
</template>
