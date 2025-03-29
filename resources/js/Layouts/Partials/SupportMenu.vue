<script setup>
import {Link, router, usePage} from '@inertiajs/vue3';
import {ref} from "vue";

const menu = ref(usePage().props.menu);

router.on('success', (event) => {
    menu.value = usePage().props.menu;
})
</script>

<template>
    <ul class="space-y-2 font-medium">
        <li>
            <Link
                :href="route('support.users.index')"
                :class="{ 'bg-gray-100 dark:bg-gray-700': route().current('support.users.*') }"
                class="flex items-center p-2 text-gray-900 rounded-xl dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
            >
                <svg class="w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 17H4a1 1 0 0 1-1-1 3 3 0 0 1 3-3h1m0-3a2.5 2.5 0 1 1 2-4.5M19.5 17h.5c.6 0 1-.4 1-1a3 3 0 0 0-3-3h-1m0-3a2.5 2.5 0 1 0-2-4.5m.5 13.5h-7a1 1 0 0 1-1-1 3 3 0 0 1 3-3h3a3 3 0 0 1 3 3c0 .6-.4 1-1 1Zm-1-9.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0Z"/>
                </svg>
                <span class="ms-3">Пользователи</span>
                <span v-if="menu.onlineUsers" class="ml-auto inline-flex items-center justify-center w-5 h-5 text-xs font-semibold text-white bg-purple-500 dark:bg-purple-500 rounded-full">
                    {{ menu.onlineUsers }}
                </span>
            </Link>
        </li>
        <li>
            <Link
                :href="route('support.orders.index')"
                :class="{ 'bg-gray-100 dark:bg-gray-700': route().current('support.orders.*') }"
                class="flex items-center p-2 text-gray-900 rounded-xl dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
            >
                <svg class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17.345a4.76 4.76 0 0 0 2.558 1.618c2.274.589 4.512-.446 4.999-2.31.487-1.866-1.273-3.9-3.546-4.49-2.273-.59-4.034-2.623-3.547-4.488.486-1.865 2.724-2.899 4.998-2.31.982.236 1.87.793 2.538 1.592m-3.879 12.171V21m0-18v2.2"/>
                </svg>
                <span class="ms-3">Сделки</span>
                <span v-if="menu.pendingOrdersCount" class="ml-auto inline-flex items-center justify-center w-5 h-5 text-xs font-semibold text-white bg-blue-500 dark:bg-blue-500 rounded-full">
                    {{ menu.pendingOrdersCount }}
                </span>
            </Link>
        </li>
        <li>
            <Link
                :href="route('support.disputes.index')"
                :class="{ 'bg-gray-100 dark:bg-gray-700': route().current('support.disputes.*') }"
                class="flex items-center p-2 text-gray-900 rounded-xl dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
            >
                <svg class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 13V8m0 8h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
                <span class="ms-3">Споры</span>
                <span v-if="menu.pendingDisputesCount" class="ml-auto inline-flex items-center justify-center w-5 h-5 text-xs font-semibold text-white bg-red-500 dark:bg-red-500 rounded-full">
                    {{ menu.pendingDisputesCount }}
                </span>
            </Link>
        </li>
    </ul>
</template> 