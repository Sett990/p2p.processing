<script setup>
import {useModalStore} from "@/store/modal.js";
import {usePage} from "@inertiajs/vue3";
import {useViewStore} from "@/store/view.js";

const viewStore = useViewStore();
const modalStore = useModalStore();
const wallet = usePage().props.wallet;
const user = usePage().props.user;
const walletStats = usePage().props.walletStats;
const escrow_balance_rub = 0;
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
                           {{ walletStats.escrowBalances.orders.balance }} USDT
                        </span>
                    </div>

                    <div class="md:mt-2 mt-0">
                        <div class="inline-flex">
                            <div class="md:text-base text-sm text-gray-500 dark:text-gray-400">
                                {{ escrow_balance_rub }} RUB - Сделок - {{ walletStats.escrowBalances.orders.count }}
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
