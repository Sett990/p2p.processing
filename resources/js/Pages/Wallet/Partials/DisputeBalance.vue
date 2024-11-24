<script setup>
import {useModalStore} from "@/store/modal.js";
import {usePage} from "@inertiajs/vue3";
import {useViewStore} from "@/store/view.js";

const viewStore = useViewStore();
const modalStore = useModalStore();

const wallet = usePage().props.wallet;
const user = usePage().props.user;
const dispute_balance = usePage().props.dispute_balance;
const dispute_balance_rub = usePage().props.dispute_balance_rub;
const disputes_count = usePage().props.disputes_count;
</script>

<template>
    <div>
        <div class="grow lg:mt-0">
            <div class="rounded-lg border border-gray-200 bg-white shadow-md p-4 dark:border-gray-700 dark:bg-gray-800">
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
                           {{ dispute_balance }} USDT
                        </span>
                    </div>

                    <div class="md:mt-2 mt-0">
                        <div class="inline-flex">
                            <div class="md:text-base text-sm text-gray-500 dark:text-gray-400">
                                {{ dispute_balance_rub }} RUB - Споров - {{ disputes_count }}
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
