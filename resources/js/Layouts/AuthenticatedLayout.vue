<script setup>
import {usePage, router, Link} from '@inertiajs/vue3';
import {computed, onMounted, ref} from 'vue'
import {Drawer, initFlowbite} from 'flowbite'
import ViewModeSwitcher from "@/Layouts/Partials/ViewModeSwitcher.vue";
import TraderMenu from "@/Layouts/Partials/TraderMenu.vue";
import AdminMenu from "@/Layouts/Partials/AdminMenu.vue";
import NavBar from "@/Layouts/Partials/NavBar.vue";
import MerchantMenu from "@/Layouts/Partials/MerchantMenu.vue";
import {useViewStore} from "@/store/view.js";
import {useUserStore} from "@/store/user.js";
import OnlineSwitcher from "@/Layouts/Partials/OnlineSwitcher.vue";

const viewStore = useViewStore();
const userStore = useUserStore();

const rates = ref(usePage().props.data.rates);

const showAllRates = ref(false);

let $targetEl = null;
let drawer = null;

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
    if (route().current('payouts.*')) {
        viewStore.setMerchantViewMode()
    }
    if (route().current('payout-gateways.*')) {
        viewStore.setMerchantViewMode()
    }

    if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark')
    }

    $targetEl = document.getElementById('mobile-sidebar');
    drawer = new Drawer($targetEl);

    initFlowbite();
})

const toggleSidebar = () => {
    drawer.toggle();
}

router.on('success', (event) => {
    initFlowbite();

    rates.value = usePage().props.data.rates;
})

const openDocs = () => {
    window.open('/docs', '_blank');
}
</script>

<template>
    <div>
        <!-- drawer component -->
        <div id="mobile-sidebar" class="block lg:hidden fixed top-0 left-0 z-50 w-[21rem] h-screen p-4 overflow-y-auto transition-transform -translate-x-full bg-white dark:bg-gray-800" tabindex="-1" aria-labelledby="mobile-sidebar-label">
            <h5 id="mobile-sidebar-label" class="text-lg font-semibold text-gray-500 dark:text-gray-400 px-1">{{ appName }}</h5>
            <button type="button" data-drawer-hide="mobile-sidebar" aria-controls="mobile-sidebar" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 absolute top-2.5 end-2.5 inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white" >
                <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                <span class="sr-only">Закрыть меню</span>
            </button>
            <div class="py-4 overflow-y-auto flex justify-center">
                <div class="h-full z-40 space-y-6" aria-label="Sidebar">
                    <div v-if="userStore.isAdmin" class="p-2 overflow-y-auto bg-white dark:bg-gray-800 w-72 rounded-menu">
                        <ViewModeSwitcher/>
                    </div>
                    <div
                        v-show="viewStore.isTraderViewMode"
                        class="p-2 overflow-y-auto bg-white dark:bg-gray-800 w-72 rounded-menu"
                    >
                        <OnlineSwitcher/>
                    </div>
                    <div class="p-2 overflow-y-auto bg-white dark:bg-gray-800 w-72 rounded-menu">
                        <TraderMenu
                            v-show="viewStore.isTraderViewMode"
                        />
                        <MerchantMenu
                            v-show="viewStore.isMerchantViewMode"
                        />
                        <AdminMenu
                            v-show="viewStore.isAdminViewMode"
                        />
                    </div>
                    <div class="p-2 overflow-y-auto bg-white dark:bg-gray-800 w-72 rounded-menu">
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
                                    <span v-show="! showAllRates" class="text-gray-700 dark:text-gray-500 dark:hover:text-blue-500" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        Показать все
                                    </span>
                                    <span v-show="showAllRates" class="text-gray-700 dark:text-gray-500 dark:hover:text-blue-500" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        Спрятать
                                    </span>
                                </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="min-h-screen bg-gray-100 dark:bg-gray-900 pt-10">
            <div class="container px-3 lg:px-10 mx-auto mb-5">
                <NavBar @toggle-sidebar="toggleSidebar"/>
            </div>

            <div class="container px-3 lg:px-10 mx-auto pt-5 pb-14">
                <div class="flex">
                    <aside class="h-full z-40 space-y-6 mr-6 hidden lg:block" aria-label="Sidebar">
                        <div v-if="userStore.isAdmin" class="p-5 overflow-y-auto bg-white dark:bg-gray-800 w-72 shadow-md rounded-menu">
                            <ViewModeSwitcher/>
                        </div>
                        <div
                            v-show="viewStore.isTraderViewMode"
                            class="p-5 overflow-y-auto bg-white dark:bg-gray-800 w-72 shadow-md rounded-menu"
                        >
                            <OnlineSwitcher/>
                        </div>
                        <div class="p-5 overflow-y-auto bg-white dark:bg-gray-800 w-72 shadow-md rounded-menu">
                            <TraderMenu
                                v-show="viewStore.isTraderViewMode"
                            />
                            <MerchantMenu
                                v-show="viewStore.isMerchantViewMode"
                            />
                            <AdminMenu
                                v-show="viewStore.isAdminViewMode"
                            />
<!--                            <div>
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
                        <div class="p-5 overflow-y-auto bg-white dark:bg-gray-800 w-72 shadow-md rounded-menu">
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
                                    <span v-show="! showAllRates" class="text-gray-700 dark:text-gray-500 dark:hover:text-blue-500" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        Показать все
                                    </span>
                                    <span v-show="showAllRates" class="text-gray-700 dark:text-gray-500 dark:hover:text-blue-500" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
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
