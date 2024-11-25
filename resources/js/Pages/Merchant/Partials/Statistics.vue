<script setup>
import {usePage} from "@inertiajs/vue3";

const statistics = usePage().props.statistics;
</script>

<template>
    <div>
        <section>
            <div class="mx-auto text-center">
                <div class="bg-white dark:bg-gray-800 border shadow border-gray-200 dark:border-gray-700 rounded-lg grid max-w-full mx-auto text-gray-900 lg:grid-cols-4 md:grid-cols-2 sm:grid-cols-1 dark:text-white">
                    <div class="lg:border-b-0 border-b md:border-r border-gray-200 dark:border-gray-700 py-5 flex flex-col items-center justify-center">
                        <div class="mb-2 text-3xl md:text-3xl font-extrabold">
                            {{ statistics.today_profit }}
                            <span class="text-xl text-gray-500 dark:text-gray-400">
                                {{ statistics.currency.toUpperCase() }}
                            </span>
                        </div>
                        <div class="font-light text-gray-500 dark:text-gray-400">Доход за сегодня</div>
                        <div class="flex mt-1 font-light text-xs text-gray-900 dark:text-gray-200 border border-gray-200 dark:border-gray-600 rounded-lg p-1 px-2">
                            <svg class="w-4 h-4 mr-1 text-blue-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8.032 12 1.984 1.984 4.96-4.96m4.55 5.272.893-.893a1.984 1.984 0 0 0 0-2.806l-.893-.893a1.984 1.984 0 0 1-.581-1.403V7.04a1.984 1.984 0 0 0-1.984-1.984h-1.262a1.983 1.983 0 0 1-1.403-.581l-.893-.893a1.984 1.984 0 0 0-2.806 0l-.893.893a1.984 1.984 0 0 1-1.403.581H7.04A1.984 1.984 0 0 0 5.055 7.04v1.262c0 .527-.209 1.031-.581 1.403l-.893.893a1.984 1.984 0 0 0 0 2.806l.893.893c.372.372.581.876.581 1.403v1.262a1.984 1.984 0 0 0 1.984 1.984h1.262c.527 0 1.031.209 1.403.581l.893.893a1.984 1.984 0 0 0 2.806 0l.893-.893a1.985 1.985 0 0 1 1.403-.581h1.262a1.984 1.984 0 0 0 1.984-1.984V15.7c0-.527.209-1.031.581-1.403Z"/>
                            </svg>
                            Платежей {{ statistics.today_orders_count }}
                        </div>
                    </div>
                    <div class="lg:border-b-0 border-b lg:border-r border-gray-200 dark:border-gray-700 py-5 flex flex-col items-center justify-center">
                        <div class="mb-2 text-3xl md:text-3xl font-extrabold">
                            {{ statistics.yesterday_profit }}
                            <span class="text-xl text-gray-500 dark:text-gray-400">
                                {{ statistics.currency.toUpperCase() }}
                            </span>
                        </div>
                        <div class="font-light text-gray-500 dark:text-gray-400">Доход за вчера</div>
                        <div class="flex mt-1 font-light text-xs text-gray-900 dark:text-gray-200 border border-gray-200 dark:border-gray-600 rounded-lg p-1 px-2">
                            <svg class="w-4 h-4 mr-1 text-blue-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8.032 12 1.984 1.984 4.96-4.96m4.55 5.272.893-.893a1.984 1.984 0 0 0 0-2.806l-.893-.893a1.984 1.984 0 0 1-.581-1.403V7.04a1.984 1.984 0 0 0-1.984-1.984h-1.262a1.983 1.983 0 0 1-1.403-.581l-.893-.893a1.984 1.984 0 0 0-2.806 0l-.893.893a1.984 1.984 0 0 1-1.403.581H7.04A1.984 1.984 0 0 0 5.055 7.04v1.262c0 .527-.209 1.031-.581 1.403l-.893.893a1.984 1.984 0 0 0 0 2.806l.893.893c.372.372.581.876.581 1.403v1.262a1.984 1.984 0 0 0 1.984 1.984h1.262c.527 0 1.031.209 1.403.581l.893.893a1.984 1.984 0 0 0 2.806 0l.893-.893a1.985 1.985 0 0 1 1.403-.581h1.262a1.984 1.984 0 0 0 1.984-1.984V15.7c0-.527.209-1.031.581-1.403Z"/>
                            </svg>
                            Платежей {{ statistics.yesterday_orders_count }}
                        </div>
                    </div>
                    <div class="md:border-b-0 border-b md:border-r border-gray-200 dark:border-gray-700 py-5 flex flex-col items-center justify-center">
                        <div class="mb-2 text-3xl md:text-3xl font-extrabold">
                            {{ statistics.month_profit }}
                            <span class="text-xl text-gray-500 dark:text-gray-400">
                                {{ statistics.currency.toUpperCase() }}
                            </span>
                        </div>
                        <div class="font-light text-gray-500 dark:text-gray-400">Доход за месяц</div>
                        <div class="flex mt-1 font-light text-xs text-gray-900 dark:text-gray-200 border border-gray-200 dark:border-gray-600 rounded-lg p-1 px-2">
                            <svg class="w-4 h-4 mr-1 text-blue-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8.032 12 1.984 1.984 4.96-4.96m4.55 5.272.893-.893a1.984 1.984 0 0 0 0-2.806l-.893-.893a1.984 1.984 0 0 1-.581-1.403V7.04a1.984 1.984 0 0 0-1.984-1.984h-1.262a1.983 1.983 0 0 1-1.403-.581l-.893-.893a1.984 1.984 0 0 0-2.806 0l-.893.893a1.984 1.984 0 0 1-1.403.581H7.04A1.984 1.984 0 0 0 5.055 7.04v1.262c0 .527-.209 1.031-.581 1.403l-.893.893a1.984 1.984 0 0 0 0 2.806l.893.893c.372.372.581.876.581 1.403v1.262a1.984 1.984 0 0 0 1.984 1.984h1.262c.527 0 1.031.209 1.403.581l.893.893a1.984 1.984 0 0 0 2.806 0l.893-.893a1.985 1.985 0 0 1 1.403-.581h1.262a1.984 1.984 0 0 0 1.984-1.984V15.7c0-.527.209-1.031.581-1.403Z"/>
                            </svg>
                            Платежей {{ statistics.month_orders_count }}
                        </div>
                    </div>
                    <div class="flex flex-col items-center justify-center py-5">
                        <div class="mb-2 text-3xl md:text-3xl font-extrabold">
                            {{ statistics.total_profit }}
                            <span class="text-xl text-gray-500 dark:text-gray-400">
                                {{ statistics.currency.toUpperCase() }}
                            </span>
                        </div>
                        <div class="font-light text-gray-500 dark:text-gray-400">Доход за все время</div>
                        <div class="flex mt-1 font-light text-xs text-gray-900 dark:text-gray-200 border border-gray-200 dark:border-gray-600 rounded-lg p-1 px-2">
                            <svg class="w-4 h-4 mr-1 text-blue-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8.032 12 1.984 1.984 4.96-4.96m4.55 5.272.893-.893a1.984 1.984 0 0 0 0-2.806l-.893-.893a1.984 1.984 0 0 1-.581-1.403V7.04a1.984 1.984 0 0 0-1.984-1.984h-1.262a1.983 1.983 0 0 1-1.403-.581l-.893-.893a1.984 1.984 0 0 0-2.806 0l-.893.893a1.984 1.984 0 0 1-1.403.581H7.04A1.984 1.984 0 0 0 5.055 7.04v1.262c0 .527-.209 1.031-.581 1.403l-.893.893a1.984 1.984 0 0 0 0 2.806l.893.893c.372.372.581.876.581 1.403v1.262a1.984 1.984 0 0 0 1.984 1.984h1.262c.527 0 1.031.209 1.403.581l.893.893a1.984 1.984 0 0 0 2.806 0l.893-.893a1.985 1.985 0 0 1 1.403-.581h1.262a1.984 1.984 0 0 0 1.984-1.984V15.7c0-.527.209-1.031.581-1.403Z"/>
                            </svg>
                            Платежей {{ statistics.total_orders_count }}
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>

</style>
