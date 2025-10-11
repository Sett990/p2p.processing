<script setup>
import {usePage, router, Link, useForm} from '@inertiajs/vue3';
import {computed, onMounted, ref} from 'vue'
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
        } else if (role.name === 'Team Leader') {
            viewStore.setTeamLeaderViewMode();
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
})

const toggleSidebar = () => {
    const drawer = document.getElementById('mobile-drawer');
    if (drawer) {
        drawer.checked = !drawer.checked;
    }
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
        } else if (role.name === 'Team Leader') {
            viewStore.setTeamLeaderViewMode();
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
    <div class="drawer lg:drawer-open bg-base-200">
        <!-- Mobile drawer toggle -->
        <input id="mobile-drawer" type="checkbox" class="drawer-toggle" />

        <!-- Mobile drawer content -->
        <div class=" hidden">
            <label for="mobile-drawer" class="drawer-overlay"></label>
            <aside class="min-h-full w-80">
                <div class="p-4">
                    <h5 class="text-lg font-semibold text-base-content">{{ appName }}</h5>
                </div>

                <div class="p-4 space-y-4">
                    <div v-if="userStore.isAdmin" class="card bg-base-100 shadow-sm">
                        <div class="card-body p-4">
                            <ViewModeSwitcher/>
                        </div>
                    </div>

                    <div v-if="viewStore.isTraderViewMode" class="card bg-base-100 shadow-sm">
                        <div class="card-body p-4">
                            <OnlineSwitcher/>
                        </div>
                    </div>

                    <div class="card bg-base-100 shadow-sm">
                        <div class="card-body p-4">
                            <TraderMenu v-show="viewStore.isTraderViewMode" />
                            <MerchantMenu v-show="viewStore.isMerchantViewMode" />
                            <TeamLeaderMenu v-show="viewStore.isTeamLeaderViewMode" />
                            <AdminMenu v-show="viewStore.isAdminViewMode" />
                            <SupportMenu v-show="viewStore.isSupportViewMode" />
                            <MerchantSupportMenu v-show="viewStore.isMerchantSupportViewMode" />
                        </div>
                    </div>

                    <div class="card bg-base-100 shadow-sm">
                        <div class="card-body p-4">
                            <div class="flex items-center mb-2">
                                <span class="text-sm text-base-content/70">Курс Tether TRC-20</span>
                            </div>
                            <div class="text-sm">
                                <ul class="space-y-1">
                                    <li v-for="(rate, index) in rates" v-show="index < 3 || showAllRates" class="flex justify-between items-center border-b border-base-300 pb-1 last:border-none">
                                        <span class="text-sm text-base-content">{{ rate.buy_price }}</span>
                                        <span class="text-sm text-primary">{{ rate.code.toUpperCase() }}</span>
                                    </li>
                                </ul>
                                <div class="flex justify-center mt-3">
                                    <button @click="showAllRates = !showAllRates" class="btn btn-ghost btn-sm">
                                        <span v-show="!showAllRates">Показать все</span>
                                        <span v-show="showAllRates">Спрятать</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>

        <!-- Main content -->
        <div class="drawer-content flex flex-col min-h-screen">
            <!-- Navbar -->
            <div class="navbar">
                <div class="container mx-auto px-4 pt-6">
                    <NavBar @toggle-sidebar="toggleSidebar"/>
                </div>
            </div>

            <!-- Page content -->
            <div class="container mx-auto px-4 pb-6 pt-2 flex-1">
                <div class="flex gap-6">
                    <!-- Desktop sidebar -->
                    <aside class="hidden lg:block space-y-4 pt-4" aria-label="Sidebar">
                        <button
                            v-if="isImpersonated"
                            @click="leaveImpersonate"
                            class="btn btn-warning w-full"
                        >
                            Выйти
                            <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H8m12 0-4 4m4-4-4-4M9 4H7a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h2"/>
                            </svg>
                        </button>

                        <div v-if="viewStore.isTraderViewMode" class="card bg-base-100 card-border border-base-300">
                            <div class="card-body">
                                <OnlineSwitcher/>
                            </div>
                        </div>

                        <div class="card bg-base-100 card-border border-base-300">
                            <TraderMenu v-show="viewStore.isTraderViewMode" />
                            <MerchantMenu v-show="viewStore.isMerchantViewMode" />
                            <TeamLeaderMenu v-show="viewStore.isTeamLeaderViewMode" />
                            <AdminMenu v-show="viewStore.isAdminViewMode" />
                            <SupportMenu v-show="viewStore.isSupportViewMode" />
                            <MerchantSupportMenu v-show="viewStore.isMerchantSupportViewMode" />
                        </div>

                        <div class="card bg-base-100 card-border border-base-300">
                            <div class="card-body">
                                <div class="flex items-center mb-2">
                                    <span class="text-sm text-base-content/70">Курс Tether TRC-20</span>
                                </div>
                                <div class="text-sm">
                                    <ul class="space-y-1">
                                        <li v-for="(rate, index) in rates" v-show="index < 3 || showAllRates" class="flex justify-between items-center border-b border-base-300 pb-1 last:border-none">
                                            <span class="text-sm text-base-content">{{ rate.buy_price }}</span>
                                            <span class="text-sm text-primary">{{ rate.code.toUpperCase() }}</span>
                                        </li>
                                    </ul>
                                    <div class="flex justify-center mt-3">
                                        <button @click="showAllRates = !showAllRates" class="btn btn-ghost btn-sm">
                                            <span v-show="!showAllRates">Показать все</span>
                                            <span v-show="showAllRates">Спрятать</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </aside>

                    <!-- Main content area -->
                    <main class="w-full lg:w-[calc(100%_-_19.5rem)]">
                        <slot />
                    </main>
                </div>
            </div>
        </div>
    </div>
</template>
