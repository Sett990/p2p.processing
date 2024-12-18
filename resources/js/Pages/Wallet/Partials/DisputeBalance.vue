<script setup>
import {useModalStore} from "@/store/modal.js";
import {usePage} from "@inertiajs/vue3";
import {useViewStore} from "@/store/view.js";

const viewStore = useViewStore();
const modalStore = useModalStore();

const user = usePage().props.user;
const walletStats = usePage().props.walletStats;
const disputeBalance = {
    primary: walletStats.escrowBalances.disputes.balance.primary,
    secondary: walletStats.escrowBalances.disputes.balance.secondary,
    count: walletStats.escrowBalances.disputes.count,
};
const currency = {
    primary: walletStats.currency.primary.toUpperCase(),
    secondary: walletStats.currency.secondary.toUpperCase(),
};
</script>

<template>
    <div>
        <div class="grow lg:mt-0">
            <div class="rounded-plate bg-white shadow-md p-4 dark:bg-gray-800">
                <div>
                    <div class="flex justify-between items-center">
                        <div class="md:text-xl text-lg text-gray-900 dark:text-gray-200">Спорные сделки</div>
                        <div>
                            <svg class="md:w-5 md:h-5 w-4 h-4 text-blue-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            </svg>
                        </div>
                    </div>

                    <div class="md:pt-5 pt-1 inline-block align-middle">
                        <span class="md:text-xl text-lg font-bold text-gray-900 dark:text-gray-200">
                           {{ disputeBalance.primary }} {{ currency.primary }}
                        </span>
                    </div>

                    <div class="md:mt-2 mt-0">
                        <div class="inline-flex">
                            <div class="md:text-base text-sm text-gray-500 dark:text-gray-400">
                                {{ disputeBalance.secondary }} {{ currency.secondary }} - Споров - {{ disputeBalance.count }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
