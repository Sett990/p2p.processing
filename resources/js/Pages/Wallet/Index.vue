<script setup>
import {Head, router, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import GoBackButton from "@/Components/GoBackButton.vue";
import DepositModal from "@/Modals/Wallet/DepositModal.vue";
import WithdrawalModal from "@/Modals/Wallet/WithdrawalModal.vue";
import TraderBalance from "@/Pages/Wallet/Partials/TraderBalance.vue";
import MerchantBalance from "@/Pages/Wallet/Partials/MerchantBalance.vue";
import {useViewStore} from "@/store/view.js";
import OperationsHistory from "@/Pages/Wallet/Partials/OperationsHistory.vue";
import {ref} from "vue";
import EscrowBalance from "@/Pages/Wallet/Partials/EscrowBalance.vue";
import DisputeBalance from "@/Pages/Wallet/Partials/DisputeBalance.vue";

const user = usePage().props.user;
const viewStore = useViewStore();

const sourceType = ref('trust');

const setSourceType = (type) => {
    sourceType.value = type;
}

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <Head title="Финансы"/>

    <div>
        <h2 class="text-xl text-gray-900 dark:text-white sm:text-4xl mb-6">Финансы</h2>

        <div v-if="viewStore.isAdminViewMode" class="mb-3">
            <GoBackButton
                @click="router.visit(route('admin.users.index'))"
            ></GoBackButton>
        </div>

        <h2
            v-if="viewStore.isAdminViewMode"
            class="text-xl text-gray-900 dark:text-white sm:text-2xl mb-3"
        >
            Пользователь: <span class="text-blue-500">{{user.email}}</span>
        </h2>

        <div v-if="$page.props.flash.message" class="flex items-center p-4 mb-4 text-sm text-red-800 border border-red-300 rounded-alert  bg-red-50 dark:bg-gray-800 dark:text-red-400 dark:border-red-800" role="alert">
            <svg class="flex-shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
            </svg>
            <div>
                <span class="font-medium">Внимание</span> {{ $page.props.flash.message }}
            </div>
        </div>

        <div class="grid lg:grid-cols-2 grid-cols-1 gap-6 mb-6">
            <TraderBalance v-show="viewStore.isTraderViewMode || viewStore.isAdminViewMode" @setSourceType="setSourceType"/>
            <MerchantBalance v-show="viewStore.isMerchantViewMode || viewStore.isAdminViewMode" @setSourceType="setSourceType"/>
            <EscrowBalance v-show="viewStore.isTraderViewMode || viewStore.isAdminViewMode" @setSourceType="setSourceType"/>
            <DisputeBalance v-show="viewStore.isTraderViewMode || viewStore.isAdminViewMode" @setSourceType="setSourceType"/>
        </div>

        <OperationsHistory/>

        <DepositModal :sourceType="sourceType"/>
        <WithdrawalModal :sourceType="sourceType"/>
    </div>
</template>

