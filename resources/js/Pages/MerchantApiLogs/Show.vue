<script setup>
import {Head, Link, usePage} from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import DateTime from "@/Components/DateTime.vue";
import DisplayUUID from "@/Components/DisplayUUID.vue";

const log = usePage().props.log;

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Детали лога API-запроса" />

        <div class="mx-auto space-y-6">
            <div class="flex justify-between items-center">
                <h2 class="text-xl text-gray-900 dark:text-white sm:text-4xl">Детали лога API-запроса #{{ log.id }}</h2>
                <Link
                    :href="route('admin.merchant-api-logs.index')"
                    class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-xl text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800"
                >
                    Назад к списку
                </Link>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Основная информация -->
                <div class="bg-white dark:bg-gray-800 shadow-md rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Основная информация</h3>

                    <div class="grid grid-cols-1 gap-4">
                        <div class="flex flex-col">
                            <span class="text-sm text-gray-500 dark:text-gray-400">ID:</span>
                            <span class="text-gray-900 dark:text-white">{{ log.id }}</span>
                        </div>

                        <div class="flex flex-col">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Мерчант:</span>
                            <span class="text-gray-900 dark:text-white">{{ log.merchant.name }}</span>
                        </div>

                        <div class="flex flex-col">
                            <span class="text-sm text-gray-500 dark:text-gray-400">UUID мерчанта:</span>
                            <span class="text-gray-900 dark:text-white">{{ log.merchant.uuid }}</span>
                        </div>

                        <div class="flex flex-col">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Внешний ID:</span>
                            <span class="text-gray-900 dark:text-white">{{ log.external_id || '-' }}</span>
                        </div>

                        <div class="flex flex-col">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Сумма:</span>
                            <span class="text-gray-900 dark:text-white">{{ log.amount ? `${log.amount} ${log.currency}` : '-' }}</span>
                        </div>

                        <div class="flex flex-col">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Платежный шлюз:</span>
                            <span class="text-gray-900 dark:text-white">{{ log.payment_gateway || '-' }}</span>
                        </div>

                        <div class="flex flex-col">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Статус:</span>
                            <span
                                :class="log.is_successful
                                    ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                                    : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'"
                                class="text-xs font-medium px-2.5 py-0.5 rounded-full w-fit"
                            >
                                {{ log.is_successful ? 'Успешно' : 'Ошибка' }}
                            </span>
                        </div>

                        <div v-if="!log.is_successful" class="flex flex-col">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Сообщение об ошибке:</span>
                            <span class="text-red-600 dark:text-red-400">{{ log.error_message || 'Нет сообщения' }}</span>
                        </div>

                        <div class="flex flex-col">
                            <span class="text-sm text-gray-500 dark:text-gray-400">IP-адрес:</span>
                            <span class="text-gray-900 dark:text-white">{{ log.ip_address || '-' }}</span>
                        </div>

                        <div class="flex flex-col">
                            <span class="text-sm text-gray-500 dark:text-gray-400">User Agent:</span>
                            <span class="text-gray-900 dark:text-white text-xs break-all">{{ log.user_agent || '-' }}</span>
                        </div>

                        <div class="flex flex-col">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Создан:</span>
                            <DateTime :data="log.created_at" class="text-gray-900 dark:text-white"></DateTime>
                        </div>
                    </div>
                </div>

                <!-- Информация о сделке -->
                <div v-if="log.order" class="bg-white dark:bg-gray-800 shadow-md rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Информация о сделке</h3>

                    <div class="grid grid-cols-1 gap-4">
                        <div class="flex flex-col">
                            <span class="text-sm text-gray-500 dark:text-gray-400">ID сделки:</span>
                            <span class="text-gray-900 dark:text-white">{{ log.order.id }}</span>
                        </div>

                        <div class="flex flex-col">
                            <span class="text-sm text-gray-500 dark:text-gray-400">UUID сделки:</span>
                            <DisplayUUID :uuid="log.order.uuid" />
                        </div>

                        <div class="flex flex-col">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Внешний ID сделки:</span>
                            <span class="text-gray-900 dark:text-white">{{ log.order.external_id || '-' }}</span>
                        </div>

                        <div class="flex flex-col">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Сумма сделки:</span>
                            <span class="text-gray-900 dark:text-white">{{ log.order.amount }} {{ log.order.currency }}</span>
                        </div>

                        <div class="flex flex-col">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Статус сделки:</span>
                            <span class="text-gray-900 dark:text-white">{{ log.order.status_name }}</span>
                        </div>

                        <div class="flex flex-col">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Подстатус сделки:</span>
                            <span class="text-gray-900 dark:text-white">{{ log.order.sub_status_name }}</span>
                        </div>

                        <div class="flex justify-end mt-4">
                            <Link
                                :href="route('orders.show', log.order.id)"
                                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-xl text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800"
                            >
                                Перейти к сделке
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Данные запроса и ответа -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 shadow-md rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Данные запроса</h3>
                    <pre class="bg-gray-100 dark:bg-gray-700 p-4 rounded-xl overflow-auto max-h-96 text-xs">{{ JSON.stringify(log.request_data, null, 2) }}</pre>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-md rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Данные ответа</h3>
                    <pre class="bg-gray-100 dark:bg-gray-700 p-4 rounded-xl overflow-auto max-h-96 text-xs">{{ JSON.stringify(log.response_data, null, 2) }}</pre>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
