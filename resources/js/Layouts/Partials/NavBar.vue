<script setup>
import {Link, router, usePage} from "@inertiajs/vue3";
import {Dropdown} from "flowbite";
import {ref} from "vue";
import {useViewStore} from "@/store/view.js";

const viewStore = useViewStore();

let dropdown = null;

const hideDropdown = () => {
    if (!dropdown) {
        dropdown = new Dropdown(
            document.getElementById("dropdown-user"),
            document.getElementById("dropdown-user-button")
        );
    }
    dropdown.hide()
};

const isDarkMode = localStorage.getItem('color-theme') === 'dark';

const switchThemeColorMode = () => {
    // if set via local storage previously
    if (localStorage.getItem('color-theme')) {
        if (localStorage.getItem('color-theme') === 'light') {
            document.documentElement.classList.add('dark');
            localStorage.setItem('color-theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('color-theme', 'light');
        }

        // if NOT set via local storage previously
    } else {
        if (document.documentElement.classList.contains('dark')) {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('color-theme', 'light');
        } else {
            document.documentElement.classList.add('dark');
            localStorage.setItem('color-theme', 'dark');
        }
    }
}

const wallet = ref(usePage().props.data.wallet);

router.on('success', (event) => {
    wallet.value = usePage().props.data.wallet;
})
</script>

<template>
    <nav class="z-50 w-full">
        <div>
            <div class="flex items-center justify-between">
                <div class="flex items-center justify-start rtl:justify-end">
                    <button data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar" aria-controls="logo-sidebar" type="button" class="inline-flex items-center p-2 text-sm text-gray-500 rounded-xl  sm:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600">
                        <span class="sr-only">Open sidebar</span>
                        <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path>
                        </svg>
                    </button>
                    <Link :href="route('dashboard')" class="flex ms-2 md:me-24">
                        <span class="self-center text-xl font-semibold sm:text-3xl whitespace-nowrap dark:text-white">{{ appName }}</span>
                    </Link>
                </div>
                <div class="flex items-center space-x-3">
                    <div v-show="viewStore.isMerchantViewMode" class="sm:flex items-center hidden">
                        <div class="font-semibold">
                            <span class="text-base text-gray-900 dark:text-gray-200 mx-1">{{ wallet.merchant_balance }}</span>
                            <span class="text-blue-500 text-sm">USDT</span>
                        </div>
                    </div>
                    <div v-show="viewStore.isTraderViewMode" class="sm:flex items-center hidden">
                        <div class="font-semibold">
                            <span class="text-lg text-gray-900 dark:text-gray-200 mx-1">{{ wallet.trust_balance }}</span>
                            <span class="text-blue-500 text-base">USDT</span>
                        </div>
                        <span class="ml-3 inline-flex bg-gray-200/75 text-gray-700 text-sm font-medium me-2 px-3 py-1.5 rounded-full dark:bg-gray-700 dark:text-gray-300">
                            <svg class="w-4 h-4 mr-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                 <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14v3m-3-6V7a3 3 0 1 1 6 0v4m-8 0h10a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1v-7a1 1 0 0 1 1-1Z"/>
                             </svg>
                            {{ wallet.reserve_balance }} USDT
                        </span>
                    </div>
                    <div class="flex items-center">
                        <div id="dropdown-user-button" data-dropdown-toggle="dropdown-user" class="flex items-center space-x-4 cursor-pointer dark:hover:bg-gray-800/75 py-2 px-4 rounded-xl">
                            <div class="flex text-sm bg-gray-400 rounded-full focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600">
                                <span class="sr-only">Open user menu</span>
                                <img :src="'https://api.dicebear.com/8.x/adventurer/svg?seed='+$page.props.auth.user.email" class="w-12 h-12 rounded-full" alt="user photo">
                            </div>
                            <div>
                                <p class="text-lg text-gray-900 dark:text-gray-200" role="none">
                                    {{ $page.props.auth.user.email }}
                                </p>
                                <p class="text-gray-500 dark:text-gray-400 text-base" role="none">
                                    {{ $page.props.auth.user.name }}
                                </p>
                            </div>
                            <div>
                                <svg class="w-6 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>
                        <div class="z-50 hidden my-4 text-base list-none bg-white divide-y divide-gray-100 rounded shadow dark:bg-gray-700 dark:divide-gray-600" id="dropdown-user">
                            <div class="px-4 py-3" role="none">
                                <p class="text-sm text-gray-900 dark:text-white" role="none">
                                    {{ $page.props.auth.user.name }}
                                </p>
                                <p class="text-sm font-medium text-gray-900 truncate dark:text-gray-300" role="none">
                                    {{ $page.props.auth.user.email }}
                                </p>
                                <div class="mt-2 sm:hidden">
                                    <div v-show="viewStore.isMerchantViewMode" class="flex items-center">
                                        <div class="font-semibold">
                                            <span class="text-base text-gray-900 dark:text-gray-200 mr-1">{{ wallet.merchant_balance }}</span>
                                            <span class="text-blue-500 text-sm">USDT</span>
                                        </div>
                                    </div>
                                    <div v-show="viewStore.isTraderViewMode" class="flex items-center">
                                        <div class="font-semibold">
                                            <span class="text-base text-gray-900 dark:text-gray-200 mr-1">{{ wallet.trust_balance }}</span>
                                            <span class="text-blue-500 text-sm">USDT</span>
                                        </div>
                                        <span class="ml-3 inline-flex bg-gray-200/75 text-gray-700 text-xs font-medium me-2 px-3 py-1.5 rounded-full dark:bg-gray-500 dark:text-gray-200">
                                            <svg class="w-4 h-4 mr-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                 <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14v3m-3-6V7a3 3 0 1 1 6 0v4m-8 0h10a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1v-7a1 1 0 0 1 1-1Z"/>
                                             </svg>
                                            {{ wallet.reserve_balance }} USDT
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <ul class="py-1" role="none">
                                <li>
                                    <div class="flex p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600">
                                        <label class="inline-flex items-center w-full cursor-pointer">
                                            <input type="checkbox" value="" class="sr-only peer" v-model="isDarkMode" @change="switchThemeColorMode">
                                            <div class="relative w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full rtl:peer-checked:after:translate-x-[-100%] peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-500 peer-checked:bg-blue-600"></div>
                                            <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">Темная тема</span>
                                        </label>
                                    </div>
                                </li>
                                <li>
                                    <Link @click="hideDropdown" :href="route('profile.edit')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:text-white" role="menuitem">
                                        Профиль
                                    </Link>
                                </li>
                                <li>
                                    <Link @click="hideDropdown" :href="route('logout')" method="post" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:text-white" role="menuitem">
                                        Выход
                                    </Link>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</template>

<style scoped>

</style>
