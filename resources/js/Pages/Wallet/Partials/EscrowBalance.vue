<script setup>
import {useModalStore} from "@/store/modal.js";
import {usePage} from "@inertiajs/vue3";
import {useViewStore} from "@/store/view.js";

const viewStore = useViewStore();
const modalStore = useModalStore();

const user = usePage().props.user;
const walletStats = usePage().props.walletStats;
const escrowBalance = {
    primary: walletStats.escrowBalances.orders.balance.primary,
    secondary: walletStats.escrowBalances.orders.balance.secondary,
    count: walletStats.escrowBalances.orders.count,
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
                    <div class="flex justify-between">
                        <div class="md:text-xl text-lg text-gray-900 dark:text-gray-200">Эскроу-счет <span class="md:inline-block hidden">(проводится сделка)</span></div>
                    </div>

                    <div class="md:pt-5 pt-1 inline-block align-middle">
                        <span class="md:text-xl text-lg font-bold text-gray-900 dark:text-gray-200">
                           {{ escrowBalance.primary }} {{ currency.primary }}
                        </span>
                    </div>

                    <div class="md:mt-2 mt-0">
                        <div class="inline-flex">
                            <div class="md:text-base text-sm text-gray-500 dark:text-gray-400">
                                {{ escrowBalance.secondary }} {{ currency.secondary }} - Сделок - {{ escrowBalance.count }}
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
