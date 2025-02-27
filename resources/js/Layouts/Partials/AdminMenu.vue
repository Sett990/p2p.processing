<script setup>
import {Link, router, usePage} from "@inertiajs/vue3";
import {ref} from "vue";

const menu = ref(usePage().props.menu);

router.on('success', (event) => {
    menu.value = usePage().props.menu;
})

const openExternal = (link) => {
    window.open('/'+link, '_blank');
}
</script>

<template>
    <div>
        <ul class="space-y-2 font-medium">
            <li>
                <Link preserve-scroll :href="route('admin.main.index')" :class="{'bg-gray-100 dark:bg-gray-700': route().current('admin.main.index')}" class="flex items-center p-2 text-gray-900 rounded-xl  dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                    <svg class="w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m4 12 8-8 8 8M6 10.5V19a1 1 0 0 0 1 1h3v-3a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3h3a1 1 0 0 0 1-1v-8.5"/>
                    </svg>

                    <span class="ms-3">Главная</span>
                </Link>
            </li>
            <li>
                <Link preserve-scroll :href="route('admin.users.index')" :class="{'bg-gray-100 dark:bg-gray-700': route().current('admin.users.*')}" class="flex items-center p-2 text-gray-900 rounded-xl dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                    <svg class="w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M16 19h4a1 1 0 0 0 1-1v-1a3 3 0 0 0-3-3h-2m-2.236-4a3 3 0 1 0 0-4M3 18v-1a3 3 0 0 1 3-3h4a3 3 0 0 1 3 3v1a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1Zm8-10a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                    </svg>
                    <span class="ms-3">Пользователи</span>
                    <span v-if="menu.onlineUsers" class="ml-auto inline-flex items-center justify-center w-5 h-5 text-xs font-semibold text-white bg-purple-500 dark:bg-purple-500 rounded-full">
                        {{ menu.onlineUsers }}
                    </span>
                </Link>
            </li>
            <li>
                <Link preserve-scroll :href="route('admin.currencies.index')" :class="{'bg-gray-100 dark:bg-gray-700': route().current('admin.currencies.*')}" class="flex items-center p-2 text-gray-900 rounded-xl dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                    <svg class="w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M8 7V6a1 1 0 0 1 1-1h11a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1h-1M3 18v-7a1 1 0 0 1 1-1h11a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1Zm8-3.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z"/>
                    </svg>
                    <span class="ms-3">Валюты</span>
                </Link>
            </li>
            <li>
                <Link preserve-scroll :href="route('admin.payment-gateways.index')" :class="{'bg-gray-100 dark:bg-gray-700': route().current('admin.payment-gateways.*')}" class="flex items-center p-2 text-gray-900 rounded-xl dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                    <svg class="w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 18h14M5 18v3h14v-3M5 18l1-9h12l1 9M16 6v3m-4-3v3m-2-6h8v3h-8V3Zm-1 9h.01v.01H9V12Zm3 0h.01v.01H12V12Zm3 0h.01v.01H15V12Zm-6 3h.01v.01H9V15Zm3 0h.01v.01H12V15Zm3 0h.01v.01H15V15Z"/>
                    </svg>
                    <span class="ms-3">Платежные методы</span>
                </Link>
            </li>
            <li>
                <Link preserve-scroll :href="route('admin.payment-details.index')" :class="{'bg-gray-100 dark:bg-gray-700': route().current('admin.payment-details.*')}" class="flex items-center p-2 text-gray-900 rounded-xl dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                    <svg class="w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M6 14h2m3 0h5M3 7v10a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1Z"/>
                    </svg>
                    <span class="ms-3">Реквизиты</span>
                    <span v-if="menu.activeDetails" class="ml-auto inline-flex items-center justify-center w-5 h-5 text-xs font-semibold text-white bg-green-500 dark:bg-green-500 rounded-full">
                        {{ menu.activeDetails }}
                    </span>
                </Link>
            </li>
            <li>
                <Link preserve-scroll :href="route('admin.orders.index')" :class="{'bg-gray-100 dark:bg-gray-700': route().current('admin.orders.*')}" class="flex items-center p-2 text-gray-900 rounded-xl dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
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
                <Link preserve-scroll :href="route('admin.disputes.index')" :class="{'bg-gray-100 dark:bg-gray-700': route().current('admin.disputes.*')}" class="flex items-center p-2 text-gray-900 rounded-xl dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                    <svg class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 13V8m0 8h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                    <span class="ms-3">Споры</span>
                    <span v-if="menu.pendingDisputesCount" class="ml-auto inline-flex items-center justify-center w-5 h-5 text-xs font-semibold text-white bg-red-500 dark:bg-red-500 rounded-full">
                        {{ menu.pendingDisputesCount }}
                    </span>
                </Link>
            </li>
            <li>
                <Link preserve-scroll :href="route('admin.payouts.index')" :class="{'bg-gray-100 dark:bg-gray-700': route().current('admin.payouts.*')}" class="flex items-center p-2 text-gray-900 rounded-xl  dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                    <svg class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m11.0001 18-.8536-.8536c-.0937-.0937-.1464-.2209-.1464-.3535v-4.4172c0-.2422-.08794-.4762-.24744-.6585L4.45127 5.6585C3.88551 5.01192 4.34469 4 5.20385 4H18.7547c.8658 0 1.3225 1.02544.7433 1.66896L16.5001 9m-2.5 9.3754c.3347.3615.7824.6134 1.2788.7195.4771.1584 1.0002.1405 1.464-.05.4638-.1906.8338-.5396 1.0356-.977.2462-.8286-.6363-1.7337-1.7735-1.9948-1.1372-.2611-2.016-1.1604-1.7735-1.9948.2016-.4375.5716-.7868 1.0354-.9774.4639-.1905.9871-.2082 1.4643-.0496.491.1045.9348.3517 1.2689.7067m-1.9397 5.41V20m0-8v.9771"/>
                    </svg>
                    <span class="ms-3">Выплаты</span>
                </Link>
            </li>
            <li>
                <Link preserve-scroll :href="route('admin.merchants.index')" :class="{'bg-gray-100 dark:bg-gray-700': route().current('admin.merchants.*')}" class="flex items-center p-2 text-gray-900 rounded-xl dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                    <svg class="w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12c.263 0 .524-.06.767-.175a2 2 0 0 0 .65-.491c.186-.21.333-.46.433-.734.1-.274.15-.568.15-.864a2.4 2.4 0 0 0 .586 1.591c.375.422.884.659 1.414.659.53 0 1.04-.237 1.414-.659A2.4 2.4 0 0 0 12 9.736a2.4 2.4 0 0 0 .586 1.591c.375.422.884.659 1.414.659.53 0 1.04-.237 1.414-.659A2.4 2.4 0 0 0 16 9.736c0 .295.052.588.152.861s.248.521.434.73a2 2 0 0 0 .649.488 1.809 1.809 0 0 0 1.53 0 2.03 2.03 0 0 0 .65-.488c.185-.209.332-.457.433-.73.1-.273.152-.566.152-.861 0-.974-1.108-3.85-1.618-5.121A.983.983 0 0 0 17.466 4H6.456a.986.986 0 0 0-.93.645C5.045 5.962 4 8.905 4 9.736c.023.59.241 1.148.611 1.567.37.418.865.667 1.389.697Zm0 0c.328 0 .651-.091.94-.266A2.1 2.1 0 0 0 7.66 11h.681a2.1 2.1 0 0 0 .718.734c.29.175.613.266.942.266.328 0 .651-.091.94-.266.29-.174.537-.427.719-.734h.681a2.1 2.1 0 0 0 .719.734c.289.175.612.266.94.266.329 0 .652-.091.942-.266.29-.174.536-.427.718-.734h.681c.183.307.43.56.719.734.29.174.613.266.941.266a1.819 1.819 0 0 0 1.06-.351M6 12a1.766 1.766 0 0 1-1.163-.476M5 12v7a1 1 0 0 0 1 1h2v-5h3v5h7a1 1 0 0 0 1-1v-7m-5 3v2h2v-2h-2Z"/>
                    </svg>
                    <span class="ms-3">Мерчанты</span>
                </Link>
            </li>
            <li>
                <Link preserve-scroll :href="route('admin.deposits.index')" :class="{'bg-gray-100 dark:bg-gray-700': route().current('admin.deposits.*')}" class="flex items-center p-2 text-gray-900 rounded-xl dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                    <svg class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 15v2a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3v-2m-8 1V4m0 12-4-4m4 4 4-4"/>
                    </svg>

                    <span class="ms-3">Депозиты средств</span>
                </Link>
            </li>
            <li>
                <Link preserve-scroll :href="route('admin.withdrawals.index')" :class="{'bg-gray-100 dark:bg-gray-700': route().current('admin.withdrawals.*')}" class="flex items-center p-2 text-gray-900 rounded-xl dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                    <svg class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 15v2a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3v-2M12 4v12m0-12 4 4m-4-4L8 8"/>
                    </svg>
                    <span class="ms-3">Вывод средств</span>
                    <span v-if="menu.pendingWithdrawals" class="ml-auto inline-flex items-center justify-center w-5 h-5 text-xs font-semibold text-white bg-yellow-500 dark:bg-yellow-500 rounded-full">
                        {{ menu.pendingWithdrawals }}
                    </span>
                </Link>
            </li>
            <li>
                <Link preserve-scroll :href="route('admin.sms-logs.index')" :class="{'bg-gray-100 dark:bg-gray-700': route().current('admin.sms-logs.*')}" class="flex items-center p-2 text-gray-900 rounded-xl dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                    <svg class="w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.556 8.5h8m-8 3.5H12m7.111-7H4.89a.896.896 0 0 0-.629.256.868.868 0 0 0-.26.619v9.25c0 .232.094.455.26.619A.896.896 0 0 0 4.89 16H9l3 4 3-4h4.111a.896.896 0 0 0 .629-.256.868.868 0 0 0 .26-.619v-9.25a.868.868 0 0 0-.26-.619.896.896 0 0 0-.63-.256Z"/>
                    </svg>
                    <span class="ms-3">Сообщения</span>
                </Link>
            </li>
            <li>
                <Link preserve-scroll :href="route('admin.settings.index')" :class="{'bg-gray-100 dark:bg-gray-700': route().current('admin.settings.*')}" class="flex items-center p-2 text-gray-900 rounded-xl dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                    <svg class="w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13v-2a1 1 0 0 0-1-1h-.757l-.707-1.707.535-.536a1 1 0 0 0 0-1.414l-1.414-1.414a1 1 0 0 0-1.414 0l-.536.535L14 4.757V4a1 1 0 0 0-1-1h-2a1 1 0 0 0-1 1v.757l-1.707.707-.536-.535a1 1 0 0 0-1.414 0L4.929 6.343a1 1 0 0 0 0 1.414l.536.536L4.757 10H4a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h.757l.707 1.707-.535.536a1 1 0 0 0 0 1.414l1.414 1.414a1 1 0 0 0 1.414 0l.536-.535 1.707.707V20a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1v-.757l1.707-.708.536.536a1 1 0 0 0 1.414 0l1.414-1.414a1 1 0 0 0 0-1.414l-.535-.536.707-1.707H20a1 1 0 0 0 1-1Z"/>
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/>
                    </svg>
                    <span class="ms-3">Настройки</span>
                </Link>
            </li>
            <li>
                <Link preserve-scroll :href="route('admin.notifications.index')" :class="{'bg-gray-100 dark:bg-gray-700': route().current('admin.notifications.*')}" class="flex items-center p-2 text-gray-900 rounded-xl dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                    <svg class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5.365V3m0 2.365a5.338 5.338 0 0 1 5.133 5.368v1.8c0 2.386 1.867 2.982 1.867 4.175 0 .593 0 1.292-.538 1.292H5.538C5 18 5 17.301 5 16.708c0-1.193 1.867-1.789 1.867-4.175v-1.8A5.338 5.338 0 0 1 12 5.365ZM8.733 18c.094.852.306 1.54.944 2.112a3.48 3.48 0 0 0 4.646 0c.638-.572 1.236-1.26 1.33-2.112h-6.92Z"/>
                    </svg>
                    <span class="ms-3">Уведомления</span>
                </Link>
            </li>
            <li>
                <Link @click.prevent="openExternal('horizon')" href="#" class="flex items-center p-2 text-gray-900 rounded-xl  dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                    <svg
                        class="flex-shrink-0 w-5 h-5 p-0.5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"
                        aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="currentColor"
                        viewBox="0 0 32 32"
                    >
                        <path
                            d="M27.313 4.688c-6.245-6.25-16.375-6.25-22.625-0.005-3.005 3-4.693 7.073-4.688 11.313-0.010 4.688 2.036 9.135 5.599 12.177h0.016c6.349 5.417 15.797 5.042 21.698-0.859 6.25-6.245 6.25-16.375 0-22.625zM21.333 18.661c-5.333 0-5.333-5.333-10.667-5.333-3.333 0-4.589 2.089-6.354 3.656h-0.005c-0.547-6.458 4.25-12.135 10.708-12.682 6.458-0.542 12.13 4.25 12.677 10.708-1.771 1.568-3.026 3.651-6.359 3.651z"
                        />
                    </svg>

                    <span class="ms-3">Horizon</span>
                </Link>
            </li>
            <li>
                <Link @click.prevent="openExternal('pulse')" href="#" class="flex items-center p-2 text-gray-900 rounded-xl  dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                    <svg
                        class="flex-shrink-0 w-5 h-5 p-0.5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"
                        aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="3"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M3 12h4l3 8 4-16 3 8h4"
                        />
                    </svg>

                    <span class="ms-3">Pulse</span>
                </Link>
            </li>
        </ul>
    </div>
</template>

<style scoped>

</style>
