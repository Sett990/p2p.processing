<script setup>
import {Link, router, usePage} from "@inertiajs/vue3";
import {Dropdown} from "flowbite";
import {computed, ref} from "vue";
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

const isDarkMode = ref(localStorage.getItem('color-theme') === 'dark');

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

const emit = defineEmits(['toggleSidebar']);
const toggleSidebar = () => {
    emit('toggleSidebar');
}

const formatNumber = (num) => { //TODO move to utils
    // Округляем до двух знаков после запятой, если есть дробная часть
    const roundedNum = Math.round(num * 100) / 100;

    // Форматируем число с разделителями тысяч
    return roundedNum.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

const walletFormated = computed(() => {
    return {
        merchant_balance: formatNumber(wallet.value.merchant_balance),
        trust_balance: formatNumber(wallet.value.trust_balance),
        reserve_balance: formatNumber(wallet.value.reserve_balance),
    }
});

router.on('success', (event) => {
    wallet.value = usePage().props.data.wallet;
})
</script>

<template>
    <nav class="z-50 w-full">
        <div>
            <div class="flex items-center justify-between">
                <div class="flex items-center justify-start rtl:justify-end">
                    <!--data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar" aria-controls="logo-sidebar"-->
                    <button
                        type="button"
                        class="inline-flex items-center p-2 text-sm text-gray-500 rounded-xl  lg:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600"
                        @click.prevent="toggleSidebar"
                    >
                        <span class="sr-only">Открыть меню</span>
                        <svg class="sm:w-8 sm:h-8 w-7 h-7" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path>
                        </svg>
                    </button>
                    <Link :href="route('dashboard')" class="flex ms-2 md:me-24">
                        <img
                            :src="isDarkMode
                              ? '/images/light.svg'
                              : '/images/dark.svg'"
                            loading="lazy"
                            alt="Логотип"
                            style="width: 100%; max-width: 200px;"
                        />
                    </Link>
                </div>
                <div class="flex items-center space-x-3">
                    <div v-show="viewStore.isMerchantViewMode" class="lg:flex items-center hidden text-nowrap">
                        <svg class="w-6 h-6 text-blue-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8H5m12 0a1 1 0 0 1 1 1v2.6M17 8l-4-4M5 8a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.6M5 8l4-4 4 4m6 4h-4a2 2 0 1 0 0 4h4a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1Z"/>
                        </svg>
                        <div class="font-semibold">
                            <span class="text-lg text-gray-900 dark:text-gray-200 mx-1">{{ walletFormated.merchant_balance }}</span>
                            <span class="text-gray-900 dark:text-gray-200 text-sm">USDT</span>
                        </div>
                    </div>
                    <div v-show="viewStore.isTraderViewMode" class="lg:flex items-center hidden text-nowrap">
                        <svg class="w-6 h-6 text-blue-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8H5m12 0a1 1 0 0 1 1 1v2.6M17 8l-4-4M5 8a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.6M5 8l4-4 4 4m6 4h-4a2 2 0 1 0 0 4h4a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1Z"/>
                        </svg>
                        <div class="font-semibold">
                            <span class="text-lg text-gray-900 dark:text-gray-200 mx-1">{{ walletFormated.trust_balance }}</span>
                            <span class="text-gray-900 dark:text-gray-200 text-sm">USDT</span>
                        </div>
                        <span class="ml-3 inline-flex items-center bg-gray-200/75 text-gray-700 text-sm font-medium me-2 px-3 py-1.5 rounded-full dark:bg-gray-700 dark:text-gray-300">
                            <svg class="w-4 h-4 mr-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                 <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14v3m-3-6V7a3 3 0 1 1 6 0v4m-8 0h10a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1v-7a1 1 0 0 1 1-1Z"/>
                             </svg>
                            {{ wallet.reserve_balance }} USDT
                        </span>
                    </div>
                    <div class="flex items-center">
                        <div id="dropdown-user-button" data-dropdown-toggle="dropdown-user" class="flex items-center space-x-4 cursor-pointer dark:hover:bg-gray-800/75 py-2 px-4 pr-2 rounded-xl">
                            <div class="flex text-sm bg-gray-400 rounded-full focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600">
                                <span class="sr-only">Open user menu</span>
                                <img :src="'https://api.dicebear.com/9.x/'+$page.props.auth.user.avatar_style+'/svg?seed='+$page.props.auth.user.avatar_uuid" class="w-12 h-12 rounded-full" alt="user photo">
                            </div>
                            <div class="sm:block hidden">
                                <p class="text-lg text-gray-900 dark:text-gray-200" role="none">
                                    {{ $page.props.auth.user.email }}
                                </p>
                                <p class="text-gray-500 dark:text-gray-400 text-base" role="none">
                                    {{ $page.props.auth.user.name }}
                                </p>
                            </div>
                            <div class="sm:block hidden">
                                <svg class="w-6 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>
                        <div class="z-50 hidden my-4 text-base list-none bg-white divide-none sm:divide-y sm:divide-gray-100 rounded-plate shadow dark:bg-gray-700 dark:divide-gray-600" id="dropdown-user">
                            <div class="px-4 py-3 lg:hidden block border-b dark:border-gray-600" role="none">
                                <p class="text-base text-gray-900 dark:text-white" role="none">
                                    {{ $page.props.auth.user.name }}
                                </p>
                                <p class="text-base font-medium text-gray-900 truncate dark:text-gray-300" role="none">
                                    {{ $page.props.auth.user.email }}
                                </p>
                                <div class="mt-2">
                                    <div v-show="viewStore.isMerchantViewMode" class="flex items-center">
                                        <svg class="w-5 h-5 text-blue-500 mr-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8H5m12 0a1 1 0 0 1 1 1v2.6M17 8l-4-4M5 8a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.6M5 8l4-4 4 4m6 4h-4a2 2 0 1 0 0 4h4a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1Z"/>
                                        </svg>
                                        <div class="font-semibold items-center">
                                            <span class="text-base text-gray-900 dark:text-gray-200 mr-1">{{ walletFormated.merchant_balance }}</span>
                                            <span class="text-blue-500 text-sm">USDT</span>
                                        </div>
                                    </div>
                                    <div v-show="viewStore.isTraderViewMode" class="flex items-center">
                                        <svg class="w-5 h-5 text-blue-500 mr-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8H5m12 0a1 1 0 0 1 1 1v2.6M17 8l-4-4M5 8a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.6M5 8l4-4 4 4m6 4h-4a2 2 0 1 0 0 4h4a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1Z"/>
                                        </svg>
                                        <div class="font-semibold">
                                            <span class="text-base text-gray-900 dark:text-gray-200 mr-1">{{ walletFormated.trust_balance }}</span>
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
                            <ul role="none" class="w-full pt-0 lg:pt-2 rounded-plate overflow-hidden">
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
                                <li class="border-t dark:border-gray-600">
                                    <div class="flex items-center p-3 px-4 rounded">
                                        <label class="flex items-center w-full cursor-pointer">
                                            <input type="checkbox" value="" class="sr-only peer" v-model="isDarkMode" @change="switchThemeColorMode">
                                            <div class="relative w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full rtl:peer-checked:after:translate-x-[-100%] peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-500 peer-checked:bg-blue-600"></div>
                                            <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">Темная тема</span>
                                        </label>
                                    </div>
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
