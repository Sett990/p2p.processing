<script setup>
import {usePage, router, Link, useForm} from '@inertiajs/vue3';
import {computed, onMounted, ref} from 'vue'
import {Drawer, initFlowbite} from 'flowbite'
import ViewModeSwitcher from "@/Layouts/Partials/ViewModeSwitcher.vue";
import TraderMenu from "@/Layouts/Partials/TraderMenu.vue";
import AdminMenu from "@/Layouts/Partials/AdminMenu.vue";
import NavBar from "@/Layouts/Partials/NavBar.vue";
import MerchantMenu from "@/Layouts/Partials/MerchantMenu.vue";
import MerchantSupportMenu from "@/Layouts/Partials/MerchantSupportMenu.vue";
import {useViewStore} from "@/store/view.js";
import {useUserStore} from "@/store/user.js";
import OnlineSwitcher from "@/Layouts/Partials/OnlineSwitcher.vue";
import TeamLeaderMenu from "@/Layouts/Partials/TeamLeaderMenu.vue";
import SupportMenu from "@/Layouts/Partials/SupportMenu.vue";

const viewStore = useViewStore();
const userStore = useUserStore();

const rates = ref(usePage().props.data.rates);
const role = usePage().props.auth.role;
const showAllRates = ref(false);
const isImpersonated = ref(usePage().props.auth.is_impersonated);

let $targetEl = null;
let drawer = null;

// initialize components based on data attribute selectors
onMounted(() => {
    viewStore.setTraderViewMode()

    if (route().current('admin.*')) {
        viewStore.setAdminViewMode()
    }

    if (route().current('leader.*')) {
        viewStore.setTeamLeaderViewMode()
    }

    if (route().current('support.*')) {
        viewStore.setSupportViewMode()
    }

    if (route().current('merchant-support.*')) {
        viewStore.setMerchantSupportViewMode()
    }

    //TODO это костыль для мерчантов
    if (route().current('profile.*')) {
        if (role.name === 'Super Admin') {
            viewStore.setAdminViewMode();
        } else if (role.name === 'Merchant') {
            viewStore.setMerchantViewMode();
        } else if (role.name === 'Trader') {
            viewStore.setTraderViewMode();
        } else if (role.name === 'Support') {
            viewStore.setSupportViewMode();
        } else if (role.name === 'Merchant Support') {
            viewStore.setMerchantSupportViewMode();
        }
    }
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
    viewStore.setTraderViewMode()

    if (route().current('admin.*')) {
        viewStore.setAdminViewMode()
    }

    if (route().current('leader.*')) {
        viewStore.setTeamLeaderViewMode()
    }

    if (route().current('support.*')) {
        viewStore.setSupportViewMode()
    }

    if (route().current('merchant-support.*')) {
        viewStore.setMerchantSupportViewMode()
    }

    //TODO это костыль для мерчантов
    if (route().current('profile.*')) {
        if (role.name === 'Super Admin') {
            viewStore.setAdminViewMode();
        } else if (role.name === 'Merchant') {
            viewStore.setMerchantViewMode();
        } else if (role.name === 'Trader') {
            viewStore.setTraderViewMode();
        } else if (role.name === 'Support') {
            viewStore.setSupportViewMode();
        } else if (role.name === 'Merchant Support') {
            viewStore.setMerchantSupportViewMode();
        }
    }
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

    initFlowbite();

    rates.value = usePage().props.data.rates;
    isImpersonated.value = usePage().props.auth.is_impersonated;
})

const leaveImpersonate = () => {
    useForm().post(route('impersonate.leave'));
};

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
                        v-if="viewStore.isTraderViewMode"
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
                        <TeamLeaderMenu
                            v-show="viewStore.isTeamLeaderViewMode"
                        />
                        <AdminMenu
                            v-show="viewStore.isAdminViewMode"
                        />
                        <SupportMenu
                            v-show="viewStore.isSupportViewMode"
                        />
                        <MerchantSupportMenu
                            v-show="viewStore.isMerchantSupportViewMode"
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
                        <button
                            v-if="isImpersonated"
                            @click="leaveImpersonate"
                            class="flex items-center bg-orange-500 hover:bg-orange-600 text-white font-medium py-2 px-4 rounded-xl transition-colors duration-200"
                        >
                            Выйти
                            <svg class="w-5 h-5 ml-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H8m12 0-4 4m4-4-4-4M9 4H7a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h2"/>
                            </svg>
                        </button>

                        <div v-if="userStore.isAdmin" class="p-5 overflow-y-auto bg-white dark:bg-gray-800 w-72 shadow-md rounded-menu">
                            <ViewModeSwitcher/>
                        </div>

                        <div
                            v-if="viewStore.isTraderViewMode"
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
                            <TeamLeaderMenu
                                v-show="viewStore.isTeamLeaderViewMode"
                            />
                            <AdminMenu
                                v-show="viewStore.isAdminViewMode"
                            />
                            <SupportMenu
                                v-show="viewStore.isSupportViewMode"
                            />
                            <MerchantSupportMenu
                                v-show="viewStore.isMerchantSupportViewMode"
                            />
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
