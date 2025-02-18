<script setup>
import {Head} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Главная"/>

        <div class="mx-auto space-y-6">
            <div class="flex justify-between items-center">
                <h2 class="text-xl text-gray-900 dark:text-white sm:text-4xl">Главная</h2>
                <slot name="button"></slot>
            </div>
            <div>
                <section>
                    <!-- Карточки статистики -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Заработано -->
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-plate shadow-md">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400">Заработано</p>
                                    <p class="text-2xl font-bold dark:text-white">$12,345</p>
                                </div>
                                <div class="bg-green-100 dark:bg-green-900 p-3 rounded-full">
                                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Выплачено -->
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-plate shadow-md">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400">Выплачено</p>
                                    <p class="text-2xl font-bold dark:text-white">$8,765</p>
                                </div>
                                <div class="bg-blue-100 dark:bg-blue-900 p-3 rounded-full">
                                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Баланс -->
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-plate shadow-md">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400">Баланс</p>
                                    <p class="text-2xl font-bold dark:text-white">$3,580</p>
                                </div>
                                <div class="bg-purple-100 dark:bg-purple-900 p-3 rounded-full">
                                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Сделки -->
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-plate shadow-md">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400">Сделки</p>
                                    <p class="text-2xl font-bold dark:text-white">1,234</p>
                                </div>
                                <div class="bg-yellow-100 dark:bg-yellow-900 p-3 rounded-full">
                                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- График (заглушка) -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-plate shadow-md mt-8">
                        <h2 class="text-xl font-bold mb-4 dark:text-white">График доходов</h2>
                        <div class="h-64 bg-gray-100 dark:bg-gray-700 rounded-plate flex items-center justify-center">
                            <p class="text-gray-500 dark:text-gray-400">График будет здесь</p>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>
