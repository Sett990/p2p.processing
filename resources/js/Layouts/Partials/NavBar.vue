<script setup>
import {Link, router, usePage} from "@inertiajs/vue3";
import {computed, ref, onMounted} from "vue";
import {useViewStore} from "@/store/view.js";

const viewStore = useViewStore();

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

const role = usePage().props.auth.role;
const email = usePage().props.auth.user.email;

const login = computed(() =>
    email.charAt(0).toUpperCase() + email.slice(1)
)

router.on('success', (event) => {
    wallet.value = usePage().props.data.wallet;
})

// Темы: все светлые, выбираем одну из трёх палитр
const availableThemes = ['forest', 'dim', 'nord'];
const currentTheme = ref(document.documentElement.getAttribute('data-theme') || availableThemes[0]);

const selectTheme = (theme) => {
    if (!availableThemes.includes(theme)) return;
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('ui-theme', theme);
    currentTheme.value = theme;
}

onMounted(() => {
    const saved = localStorage.getItem('ui-theme');
    if (saved && availableThemes.includes(saved)) {
        document.documentElement.setAttribute('data-theme', saved);
        currentTheme.value = saved;
    } else {
        // Берём тему с HTML или по умолчанию
        const htmlTheme = document.documentElement.getAttribute('data-theme');
        if (htmlTheme && availableThemes.includes(htmlTheme)) {
            currentTheme.value = htmlTheme;
            localStorage.setItem('ui-theme', htmlTheme);
        } else {
            document.documentElement.setAttribute('data-theme', availableThemes[0]);
            currentTheme.value = availableThemes[0];
            localStorage.setItem('ui-theme', availableThemes[0]);
        }
    }
});
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
                        <div class="block">
                            <div class="text-5xl font-semibold">P2P</div>
                            <div class="text-xl font-semibold">Processing</div>
                        </div>
                    </Link>
                </div>
                <div class="flex items-center space-x-3">
                    <div v-show="viewStore.isMerchantViewMode" class="lg:flex items-center hidden text-nowrap">
                        <svg class="w-6 h-6 text-primary" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8H5m12 0a1 1 0 0 1 1 1v2.6M17 8l-4-4M5 8a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.6M5 8l4-4 4 4m6 4h-4a2 2 0 1 0 0 4h4a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1Z"/>
                        </svg>
                        <div class="font-semibold text-base-content">
                            <span class="text-lg mx-1">{{ walletFormated.merchant_balance }}</span>
                            <span class="badge badge-ghost">USDT</span>
                        </div>
                    </div>
                    <div v-show="viewStore.isTraderViewMode" class="lg:flex items-center hidden text-nowrap">
                        <svg class="w-6 h-6 text-primary" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8H5m12 0a1 1 0 0 1 1 1v2.6M17 8l-4-4M5 8a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.6M5 8l4-4 4 4m6 4h-4a2 2 0 1 0 0 4h4a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1Z"/>
                        </svg>
                        <div class="font-semibold text-base-content">
                            <span class="text-lg mx-1">{{ walletFormated.trust_balance }}</span>
                            <span class="badge badge-ghost">USDT</span>
                        </div>
                        <span class="ml-3 inline-flex items-center text-sm me-2 px-3 py-1.5 rounded-full badge badge-outline">
                            <svg class="w-4 h-4 mr-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                 <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14v3m-3-6V7a3 3 0 1 1 6 0v4m-8 0h10a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1v-7a1 1 0 0 1 1-1Z"/>
                              </svg>
                            {{ wallet.reserve_balance }} USDT
                        </span>
                    </div>
                    <div class="flex items-center">
                        <div class="dropdown dropdown-end">
                            <div tabindex="0" role="button" class="flex items-center space-x-4 cursor-pointer py-2 px-4 pr-2 rounded-xl hover:bg-base-200">
                                <div class="flex text-sm bg-gray-200 dark:bg-gray-700 rounded-full">
                                    <span class="sr-only">Open user menu</span>
                                    <img :src="'https://api.dicebear.com/9.x/'+$page.props.auth.user.avatar_style+'/svg?seed='+$page.props.auth.user.avatar_uuid" class="w-12 h-12 rounded-full" alt="user photo">
                                </div>
                                <div class="sm:block hidden">
                                    <p class="text-lg text-gray-900 dark:text-gray-200" role="none">
                                        {{ login }}
                                    </p>
                                    <p class="text-gray-500 dark:text-gray-400 text-base" role="none">
                                        {{ role.name }}
                                    </p>
                                </div>
                                <div class="sm:block hidden">
                                    <svg class="w-6 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/>
                                    </svg>
                                </div>
                            </div>
                            <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-0 sm:p-2 shadow bg-base-100 rounded-plate w-72 sm:w-80">
                                <li class="lg:hidden block px-4 py-3 border-b dark:border-gray-600">
                                    <div class="text-base text-base-content">{{ $page.props.auth.user.name }}</div>
                                    <div class="text-base font-medium text-base-content/70 truncate">{{ $page.props.auth.user.email }}</div>
                                    <div class="mt-2">
                                        <div v-show="viewStore.isMerchantViewMode" class="flex items-center">
                                            <svg class="w-5 h-5 text-primary mr-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8H5m12 0a1 1 0 0 1 1 1v2.6M17 8l-4-4M5 8a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.6M5 8l4-4 4 4m6 4h-4a2 2 0 1 0 0 4h4a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1Z"/>
                                            </svg>
                                            <div class="font-semibold items-center">
                                                <span class="text-base text-base-content mr-1">{{ walletFormated.merchant_balance }}</span>
                                                <span class="badge badge-ghost badge-sm">USDT</span>
                                            </div>
                                        </div>
                                        <div v-show="viewStore.isTraderViewMode" class="flex items-center">
                                            <svg class="w-5 h-5 text-primary mr-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8H5m12 0a1 1 0 0 1 1 1v2.6M17 8l-4-4M5 8a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.6M5 8l4-4 4 4m6 4h-4a2 2 0 1 0 0 4h4a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1Z"/>
                                            </svg>
                                            <div class="font-semibold">
                                                <span class="text-base text-base-content mr-1">{{ walletFormated.trust_balance }}</span>
                                                <span class="badge badge-ghost badge-sm">USDT</span>
                                            </div>
                                            <span class="ml-3 inline-flex items-center text-xs font-medium me-2 px-3 py-1.5 rounded-full badge badge-outline">
                                                <svg class="w-4 h-4 mr-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                     <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14v3m-3-6V7a3 3 0 1 1 6 0v4m-8 0h10a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1v-7a1 1 0 0 1 1-1Z"/>
                                                 </svg>
                                                {{ wallet.reserve_balance }} USDT
                                            </span>
                                        </div>
                                    </div>
                                </li>
                                <li class="menu-title px-4">Аккаунт</li>
                                <li>
                                    <Link :href="route('profile.edit')" class="justify-start">
                                        Профиль
                                    </Link>
                                </li>
                                <li>
                                    <Link :href="route('logout')" method="post" class="justify-start">
                                        Выход
                                    </Link>
                                </li>
                                <li class="menu-title px-4">Настройки</li>
                                <li>
                                    <div class="px-4 py-2">
                                        <div class="label-text mb-2">Тема интерфейса</div>
                                        <div class="flex items-center gap-3">
                                            <button
                                                v-for="t in availableThemes"
                                                :key="t"
                                                type="button"
                                                :data-theme="t"
                                                :aria-label="'Тема ' + t"
                                                @click="selectTheme(t)"
                                                class="w-8 h-8 rounded-full bg-primary transition-all"
                                                :class="currentTheme === t ? 'outline outline-2 outline-primary' : 'outline outline-1 outline-base-300'"
                                            />
                                        </div>
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
