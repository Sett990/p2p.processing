<script setup>
import {Link, router, usePage} from "@inertiajs/vue3";
import {ref} from "vue";

const menu = ref(usePage().props.menu);

router.on('success', (event) => {
    menu.value = usePage().props.menu;
})
</script>

<template>
    <ul class="menu menu-sm lg:menu-md">
        <li>
            <Link preserve-scroll :href="route('leader.main.index')" :class="{'active': route().current('leader.main.index')}" class="flex items-center">
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m4 12 8-8 8 8M6 10.5V19a1 1 0 0 0 1 1h3v-3a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3h3a1 1 0 0 0 1-1v-8.5"/>
                </svg>
                <span class="ml-3">Главная</span>
            </Link>
        </li>
        <li>
            <Link preserve-scroll :href="route('leader.finances.index')" :class="{'active': route().current('leader.finances.*')}" class="flex items-center">
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8H5m12 0a1 1 0 0 1 1 1v2.6M17 8l-4-4M5 8a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.6M5 8l4-4 4 4m6 4h-4a2 2 0 1 0 0 4h4a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1Z"/>
                </svg>
                <span class="ml-3">Финансы</span>
            </Link>
        </li>
        <li>
            <Link preserve-scroll :href="route('leader.promo-codes.index')" :class="{'active': route().current('leader.promo-codes.*')}" class="flex items-center">
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M6 14h2m3 0h5M3 7v10a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1Z"/>
                </svg>
                <span class="ml-3">Промокоды</span>
            </Link>
        </li>
        <li>
            <Link preserve-scroll :href="route('leader.referrals.index')" :class="{'active': route().current('leader.referrals.*')}" class="flex items-center">
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 17H4a1 1 0 0 1-1-1 3 3 0 0 1 3-3h1m0-3a2.5 2.5 0 1 1 2-4.5M19.5 17h.5a1 1 0 0 0 1-1 3 3 0 0 0-3-3h-1m0-3a2.5 2.5 0 1 0-2-4.5m.5 14h-8a1 1 0 0 1-1-1 3 3 0 0 1 3-3h4a3 3 0 0 1 3 3 1 1 0 0 1-1 1Zm-2-7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                </svg>
                <span class="ml-3">Рефералы</span>
            </Link>
        </li>
    </ul>
</template>

<style scoped>

</style>
