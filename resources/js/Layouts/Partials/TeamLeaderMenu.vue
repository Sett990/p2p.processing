<script setup>
import {Link, router, usePage} from "@inertiajs/vue3";
import {ref} from "vue";

const menu = ref(usePage().props.menu);

router.on('success', (event) => {
    menu.value = usePage().props.menu;
})
</script>

<template>
    <div>
        <ul class="space-y-2 font-medium">
            <li>
                <Link preserve-scroll :href="route('leader.main.index')" :class="{'bg-gray-100 dark:bg-gray-700': route().current('leader.main.index')}" class="flex items-center p-2 text-gray-900 rounded-xl  dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                    <svg class="w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m4 12 8-8 8 8M6 10.5V19a1 1 0 0 0 1 1h3v-3a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3h3a1 1 0 0 0 1-1v-8.5"/>
                    </svg>

                    <span class="ms-3">Главная</span>
                </Link>
            </li>
            <li>
                <Link preserve-scroll :href="route('leader.promo-codes.index')" :class="{'bg-gray-100 dark:bg-gray-700': route().current('leader.promo-codes.*')}" class="flex items-center p-2 text-gray-900 rounded-xl  dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                    <svg class="w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M6 14h2m3 0h5M3 7v10a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1Z"/>
                    </svg>
                    <span class="ms-3">Промокоды</span>
                </Link>
            </li>
        </ul>
    </div>
</template>

<style scoped>

</style>
