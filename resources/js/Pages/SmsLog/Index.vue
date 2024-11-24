<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { usePage } from '@inertiajs/vue3';
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import {useViewStore} from "@/store/view.js";

const viewStore = useViewStore();
const sms_logs = usePage().props.sms_logs;

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Сообщения" />

        <MainTableSection
            title="Сообщения"
            :data="sms_logs"
        >
            <template v-slot:body>
                <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">
                                    ID
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Отправитель
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Сообщение
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Тип
                                </th>
                                <th scope="col" class="px-6 py-3" v-if="viewStore.isAdminViewMode">
                                    Трейдер
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Время
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="sms_log in sms_logs.data" class="bg-white border-b last:border-none dark:bg-gray-800 dark:border-gray-700">
                                <th scope="row" class="px-6 py-3 font-medium whitespace-nowrap text-gray-900 dark:text-gray-200">
                                    {{ sms_log.id }}
                                </th>
                                <td class="px-6 py-3">
                                    {{ sms_log.sender }}
                                </td>
                                <td class="px-6 py-3">
                                    <div style="min-width: 200px;">{{ sms_log.message }}</div>
                                </td>
                                <td class="px-6 py-3">
                                    {{ sms_log.type }}
                                </td>
                                <td class="px-6 py-3" v-if="viewStore.isAdminViewMode">
                                    {{ sms_log.user.email }}
                                </td>
                                <td class="px-6 py-3 text-nowrap">
                                    {{ sms_log.timestamp }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </MainTableSection>
    </div>
</template>
