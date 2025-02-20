<script setup>
import {Head, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

import { ref } from 'vue';

const trades = ref([
    { id: "65ab43...9726fe", send: "2.72 USDT", receive: "250 ₽ VTB Bank", rate: "91.80 ₽", profit: "+0.09 USDT", status: "Отменён" },
    { id: "65ab41...3c74b9", send: "2.72 USDT", receive: "250 ₽ AdvCash", rate: "91.90 ₽", profit: "+0.09 USDT", status: "Отменён" },
    { id: "65a7f1...79ea68", send: "2.16 USDT", receive: "200.57 ₽ Tinkoff", rate: "92.98 ₽", profit: "+0.06 USDT", status: "Трейд завершён" },
    { id: "65a6f6...366266", send: "2.83 USDT", receive: "262.50 ₽", rate: "92.70 ₽", profit: "+0.09 USDT", status: "Отменён по времени" }
]);

const statusClass = (status) => {
    return status.includes("завершён") ? "text-green-500" : "text-gray-400";
};

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Статистика"/>

        <div class="container mx-auto p-4">
            <h1 class="text-3xl font-bold mb-6">Статистика P2P сделок</h1>

            <!-- Фильтры -->
            <div class="mb-6 bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Фильтры</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="date" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Дата</label>
                        <input type="date" id="date" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    </div>
                    <div>
                        <label for="currency" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Валюта</label>
                        <select id="currency" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            <option value="BTC">BTC</option>
                            <option value="ETH">ETH</option>
                            <option value="USDT">USDT</option>
                        </select>
                    </div>
                    <div>
                        <label for="status" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Статус</label>
                        <select id="status" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            <option value="completed">Завершено</option>
                            <option value="pending">В ожидании</option>
                            <option value="cancelled">Отменено</option>
                        </select>
                    </div>
                </div>
                <button class="mt-4 bg-blue-700 hover:bg-blue-800 text-white font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700">
                    Применить фильтры
                </button>
            </div>

            <!-- График -->
            <div class="mb-6 bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">График сделок</h2>
                <div class="h-64 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                    <span class="text-gray-500 dark:text-gray-400">График будет здесь</span>
                </div>
            </div>

            <!-- Таблица сделок -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Список сделок</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">ID</th>
                            <th scope="col" class="px-6 py-3">Валюта</th>
                            <th scope="col" class="px-6 py-3">Сумма</th>
                            <th scope="col" class="px-6 py-3">Статус</th>
                            <th scope="col" class="px-6 py-3">Дата</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="px-6 py-4">1</td>
                            <td class="px-6 py-4">BTC</td>
                            <td class="px-6 py-4">0.05</td>
                            <td class="px-6 py-4 text-green-500">Завершено</td>
                            <td class="px-6 py-4">2023-10-01</td>
                        </tr>
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="px-6 py-4">2</td>
                            <td class="px-6 py-4">ETH</td>
                            <td class="px-6 py-4">1.2</td>
                            <td class="px-6 py-4 text-yellow-500">В ожидании</td>
                            <td class="px-6 py-4">2023-10-02</td>
                        </tr>
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="px-6 py-4">3</td>
                            <td class="px-6 py-4">USDT</td>
                            <td class="px-6 py-4">500</td>
                            <td class="px-6 py-4 text-red-500">Отменено</td>
                            <td class="px-6 py-4">2023-10-03</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>
