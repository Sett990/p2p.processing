<script setup>
import {router, usePage} from "@inertiajs/vue3";
import {ref} from "vue";
import ViewModeSwitcher from "@/Layouts/Partials/ViewModeSwitcher.vue";
import {useUserStore} from "@/store/user.js";

const menu = ref(usePage().props.menu);
const userStore = useUserStore();

router.on('success', (event) => {
    menu.value = usePage().props.menu;
})
</script>

<template>
    <ul class="menu menu-md w-full space-y-0.5">
        <ViewModeSwitcher v-if="userStore.isAdmin" class="mb-2"/>
        <li :class="[{ 'bg-base-content/10 rounded-lg': route().current('trader.main.index') }]">
            <span
                @click="router.visit(route('trader.main.index'), { preserveScroll: true })"
                @keydown.enter.space="router.visit(route('trader.main.index'), { preserveScroll: true })"
                role="link"
                tabindex="0"
            >
                <svg class="size-5 opacity-30" stroke-width="1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m4 12 8-8 8 8M6 10.5V19a1 1 0 0 0 1 1h3v-3a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3h3a1 1 0 0 0 1-1v-8.5"/>
                </svg>
                Главная
            </span>
        </li>
        <li :class="[{ 'bg-base-content/10 rounded-lg': route().current('payment-details.*') }]">
            <span
                @click="router.visit(route('payment-details.index'), { preserveScroll: true })"
                @keydown.enter.space="router.visit(route('payment-details.index'), { preserveScroll: true })"
                role="link"
                tabindex="0"
            >
                <svg class="size-5 opacity-30" stroke-width="1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M6 14h2m3 0h5M3 7v10a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1Z"/>
                </svg>
                Реквизиты
                <span v-if="menu.activeDetails" class="badge badge-success badge-sm justify-self-end">
                    {{ menu.activeDetails }}
                </span>
            </span>
        </li>
        <li :class="[{ 'bg-base-content/10 rounded-lg': route().current('orders.*') }]">
            <span
                @click="router.visit(route('orders.index'), { preserveScroll: true })"
                @keydown.enter.space="router.visit(route('orders.index'), { preserveScroll: true })"
                role="link"
                tabindex="0"
            >
                <svg class="size-5 opacity-30" stroke-width="1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17.345a4.76 4.76 0 0 0 2.558 1.618c2.274.589 4.512-.446 4.999-2.31.487-1.866-1.273-3.9-3.546-4.49-2.273-.59-4.034-2.623-3.547-4.488.486-1.865 2.724-2.899 4.998-2.31.982.236 1.87.793 2.538 1.592m-3.879 12.171V21m0-18v2.2"/>
                </svg>
                Сделки
                <span v-if="menu.pendingOrdersCount" class="badge badge-info badge-sm justify-self-end">
                    {{ menu.pendingOrdersCount }}
                </span>
            </span>
        </li>
        <li :class="[{ 'bg-base-content/10 rounded-lg': route().current('disputes.*') }]">
            <span
                @click="router.visit(route('disputes.index'), { preserveScroll: true })"
                @keydown.enter.space="router.visit(route('disputes.index'), { preserveScroll: true })"
                role="link"
                tabindex="0"
            >
                <svg class="size-5 opacity-30" stroke-width="1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 13V8m0 8h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
                Споры
                <span v-if="menu.pendingDisputesCount" class="badge badge-error badge-sm justify-self-end">
                    {{ menu.pendingDisputesCount }}
                </span>
            </span>
        </li>
        <li :class="[{ 'bg-base-content/10 rounded-lg': route().current('wallet.*') }]">
            <span
                @click="router.visit(route('wallet.index'), { preserveScroll: true })"
                @keydown.enter.space="router.visit(route('wallet.index'), { preserveScroll: true })"
                role="link"
                tabindex="0"
            >
                <svg class="size-5 opacity-30" stroke-width="1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8H5m12 0a1 1 0 0 1 1 1v2.6M17 8l-4-4M5 8a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.6M5 8l4-4 4 4m6 4h-4a2 2 0 1 0 0 4h4a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1Z"/>
                </svg>
                Финансы
            </span>
        </li>
        <li :class="[{ 'bg-base-content/10 rounded-lg': route().current('trader.statistics.*') }]">
            <span
                @click="router.visit(route('trader.statistics.index'), { preserveScroll: true })"
                @keydown.enter.space="router.visit(route('trader.statistics.index'), { preserveScroll: true })"
                role="link"
                tabindex="0"
            >
                <svg class="size-5 opacity-30" stroke-width="1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15v4m6-6v6m6-4v4m6-6v6M3 11l6-5 6 5 5.5-5.5"/>
                </svg>
                Статистика
            </span>
        </li>
        <li :class="[{ 'bg-base-content/10 rounded-lg': route().current('notifications.*') }]">
            <span
                @click="router.visit(route('notifications.index'), { preserveScroll: true })"
                @keydown.enter.space="router.visit(route('notifications.index'), { preserveScroll: true })"
                role="link"
                tabindex="0"
            >
                <svg class="size-5 opacity-30" stroke-width="1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5.365V3m0 2.365a5.338 5.338 0 0 1 5.133 5.368v1.8c0 2.386 1.867 2.982 1.867 4.175 0 .593 0 1.292-.538 1.292H5.538C5 18 5 17.301 5 16.708c0-1.193 1.867-1.789 1.867-4.175v-1.8A5.338 5.338 0 0 1 12 5.365ZM8.733 18c.094.852.306 1.54.944 2.112a3.48 3.48 0 0 0 4.646 0c.638-.572 1.236-1.26 1.33-2.112h-6.92Z"/>
                </svg>
                Уведомления
            </span>
        </li>
        <li :class="[{ 'bg-base-content/10 rounded-lg': route().current('sms-logs.*') }]">
            <span
                @click="router.visit(route('sms-logs.index'), { preserveScroll: true })"
                @keydown.enter.space="router.visit(route('sms-logs.index'), { preserveScroll: true })"
                role="link"
                tabindex="0"
            >
                <svg class="size-5 opacity-30" stroke-width="1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.556 8.5h8m-8 3.5H12m7.111-7H4.89a.896.896 0 0 0-.629.256.868.868 0 0 0-.26.619v9.25c0 .232.094.455.26.619A.896.896 0 0 0 4.89 16H9l3 4 3-4h4.111a.896.896 0 0 0 .629-.256.868.868 0 0 0 .26-.619v-9.25a.868.868 0 0 0-.26-.619.896.896 0 0 0-.63-.256Z"/>
                </svg>
                Сообщения
            </span>
        </li>
        <li :class="[{ 'bg-base-content/10 rounded-lg': route().current('trader.devices.*') }]">
            <span
                @click="router.visit(route('trader.devices.index'), { preserveScroll: true })"
                @keydown.enter.space="router.visit(route('trader.devices.index'), { preserveScroll: true })"
                role="link"
                tabindex="0"
            >
                <svg class="size-5 opacity-30" stroke-width="1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 15h12M6 6h12m-6 12h.01M7 21h10a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1v16a1 1 0 0 0 1 1Z"/>
                </svg>
                Устройства
            </span>
        </li>
    </ul>
</template>

<style scoped>

</style>
