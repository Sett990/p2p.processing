<script setup>
import {Link, router, usePage} from "@inertiajs/vue3";
import {ref} from "vue";

const user = usePage().props.auth.user;
const menu = ref(usePage().props.menu);

router.on('success', (event) => {
    menu.value = usePage().props.menu;
})
</script>

<template>
    <ul class="menu menu-sm lg:menu-md">
        <li>
            <Link preserve-scroll :href="route('trader.main.index')" :class="{'active': route().current('trader.main.index')}" class="flex items-center">
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m4 12 8-8 8 8M6 10.5V19a1 1 0 0 0 1 1h3v-3a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3h3a1 1 0 0 0 1-1v-8.5"/>
                </svg>
                <span class="ml-3">Главная</span>
            </Link>
        </li>
        <li>
            <Link preserve-scroll :href="route('payment-details.index')" :class="{'active': route().current('payment-details.*')}" class="flex items-center">
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M6 14h2m3 0h5M3 7v10a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1Z"/>
                </svg>
                <span class="ml-3">Реквизиты</span>
                <span v-if="menu.activeDetails" class="badge badge-success badge-sm ml-auto">
                    {{ menu.activeDetails }}
                </span>
            </Link>
        </li>
        <li>
            <Link preserve-scroll :href="route('orders.index')" :class="{'active': route().current('orders.*')}" class="flex items-center">
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17.345a4.76 4.76 0 0 0 2.558 1.618c2.274.589 4.512-.446 4.999-2.31.487-1.866-1.273-3.9-3.546-4.49-2.273-.59-4.034-2.623-3.547-4.488.486-1.865 2.724-2.899 4.998-2.31.982.236 1.87.793 2.538 1.592m-3.879 12.171V21m0-18v2.2"/>
                </svg>
                <span class="ml-3">Сделки</span>
                <span v-if="menu.pendingOrdersCount" class="badge badge-info badge-sm ml-auto">
                    {{ menu.pendingOrdersCount }}
                </span>
            </Link>
        </li>
        <li>
            <Link preserve-scroll :href="route('disputes.index')" :class="{'active': route().current('disputes.*')}" class="flex items-center">
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 13V8m0 8h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
                <span class="ml-3">Споры</span>
                <span v-if="menu.pendingDisputesCount" class="badge badge-error badge-sm ml-auto">
                    {{ menu.pendingDisputesCount }}
                </span>
            </Link>
        </li>
        <li>
            <Link preserve-scroll :href="route('wallet.index')" :class="{'active': route().current('wallet.*')}" class="flex items-center">
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8H5m12 0a1 1 0 0 1 1 1v2.6M17 8l-4-4M5 8a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.6M5 8l4-4 4 4m6 4h-4a2 2 0 1 0 0 4h4a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1Z"/>
                </svg>
                <span class="ml-3">Финансы</span>
            </Link>
        </li>
        <li>
            <Link preserve-scroll :href="route('trader.statistics.index')" :class="{'active': route().current('trader.statistics.*')}" class="flex items-center">
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15v4m6-6v6m6-4v4m6-6v6M3 11l6-5 6 5 5.5-5.5"/>
                </svg>
                <span class="ml-3">Статистика</span>
            </Link>
        </li>
        <li>
            <Link preserve-scroll :href="route('notifications.index')" :class="{'active': route().current('notifications.*')}" class="flex items-center">
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5.365V3m0 2.365a5.338 5.338 0 0 1 5.133 5.368v1.8c0 2.386 1.867 2.982 1.867 4.175 0 .593 0 1.292-.538 1.292H5.538C5 18 5 17.301 5 16.708c0-1.193 1.867-1.789 1.867-4.175v-1.8A5.338 5.338 0 0 1 12 5.365ZM8.733 18c.094.852.306 1.54.944 2.112a3.48 3.48 0 0 0 4.646 0c.638-.572 1.236-1.26 1.33-2.112h-6.92Z"/>
                </svg>
                <span class="ml-3">Уведомления</span>
            </Link>
        </li>
        <li>
            <Link preserve-scroll :href="route('sms-logs.index')" :class="{'active': route().current('sms-logs.*')}" class="flex items-center">
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.556 8.5h8m-8 3.5H12m7.111-7H4.89a.896.896 0 0 0-.629.256.868.868 0 0 0-.26.619v9.25c0 .232.094.455.26.619A.896.896 0 0 0 4.89 16H9l3 4 3-4h4.111a.896.896 0 0 0 .629-.256.868.868 0 0 0 .26-.619v-9.25a.868.868 0 0 0-.26-.619.896.896 0 0 0-.63-.256Z"/>
                </svg>
                <span class="ml-3">Сообщения</span>
            </Link>
        </li>
        <li>
            <Link preserve-scroll :href="route('trader.devices.index')" :class="{'active': route().current('trader.devices.*')}" class="flex items-center">
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 15h12M6 6h12m-6 12h.01M7 21h10a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1v16a1 1 0 0 0 1 1Z"/>
                </svg>
                <span class="ml-3">Устройства</span>
            </Link>
        </li>
    </ul>
</template>

<style scoped>

</style>
