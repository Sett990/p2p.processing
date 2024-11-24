<script setup>
import {Head, router, useForm, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { LoginWidget } from 'vue-tg'
import HeadllesTable from "@/Components/HeadlesTable/HeadllesTable.vue";
import HeadlessTableTh from "@/Components/HeadlesTable/HeadlessTableTh.vue";
import HeadlessTableTd from "@/Components/HeadlesTable/HeadlessTableTd.vue";
import HeadlessTableTr from "@/Components/HeadlesTable/HeadlessTableTr.vue";
import DateTime from "@/Components/DateTime.vue";
import {useModalStore} from "@/store/modal.js";
import NotificationModal from "@/Modals/NotificationModal.vue";
import ProgressNumber from "@/Components/ProgressNumber.vue";
import {ref} from "vue";
import {useViewStore} from "@/store/view.js";
import AddMobileIcon from "@/Components/AddMobileIcon.vue";
import Pagination from "@/Components/Pagination/Pagination.vue";

const modalStore = useModalStore();
const viewStore = useViewStore();

const tgBot = usePage().props.tgBot;
const notifications = usePage().props.notifications;

const form = useForm({
    message: '',
});

const openPage = (page) => {
    router.visit(route(route().current()), { data: {
            page
        } })
}

const currentPage = ref(notifications?.meta?.current_page)

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <Head title="Уведомления" />

    <div>
        <h2 class="text-xl font-medium text-gray-900 dark:text-white sm:text-2xl mb-4">Уведомления</h2>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3 md:grid-cols-2 mb-6">
            <div class="grow sm:mt-8 lg:mt-0">

                <div class="rounded-lg border shadow-md border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                    <div>
                        <div class="flex justify-between">
                            <div class="text-xl text-gray-900 dark:text-gray-200">Телеграм</div>
                        </div>

                        <template
                            v-if="! tgBot.user_telegram_id"
                        >
                            <div class="inline-flex py-3">
                                <div class="text-sm text-gray-900 dark:text-gray-400">
                                    Авторизуйтесь через телеграм, чтобы связать аккаунты.
                                </div>
                            </div>
                            <LoginWidget
                                :bot-username="tgBot.username"
                                :redirect-url="tgBot.redirectUrl"
                            />
                        </template>
                        <template v-else>
                            <div class="inline-flex py-3">
                                <div class="text-sm text-gray-900 dark:text-gray-400">
                                    Для получения уведомлений, и управления аккаунтом через телеграм - <a  :href="tgBot.openTelegramBot" target="_blank" class="text-blue-500 hover:text-blue-600">запустите телеграм бот</a>.
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="viewStore.isAdminViewMode">
            <div class="flex justify-between mb-3">
                <h2 class="text:xl font-medium text-gray-900 dark:text-white sm:text-2xl">Отправленные уведомления</h2>
                <div>
                    <button
                        @click="modalStore.openNotificationModal({})"
                        type="button"
                        class="hidden md:block text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800"
                    >
                        Новое уведомление
                    </button>
                    <AddMobileIcon
                        @click="modalStore.openNotificationModal({})" color="default"
                    />
                </div>
            </div>
            <div class="relative overflow-x-auto shadow-md sm:rounded-lg mb-3">
                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">
                                ID
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Сообщение
                            </th>
                            <th scope="col" class="px-6 py-3 text-nowrap">
                                Прогресс доставки
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-nowrap">
                                Дата отправки
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="notification in notifications.data" class="bg-white border-b last:border-none dark:bg-gray-800 dark:border-gray-700">
                            <th scope="row" class="px-6 py-3 font-medium whitespace-nowrap text-gray-900 dark:text-gray-200">
                                {{ notification.id }}
                            </th>
                            <td class="px-6 py-3">
                                {{ notification.message }}
                            </td>
                            <td class="px-6 py-3" style="width: 200px">
                                <ProgressNumber :current="notification.delivered_count" :total="notification.recipients_count"></ProgressNumber>
                            </td>
                            <td class="px-6 py-3">
                                <DateTime class="justify-end text-nowrap" :data="notification.created_at"/>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
<!--            <div class="relative overflow-x-auto mb-3">
                <HeadllesTable>
                    <HeadlessTableTr v-for="notification in notifications.data">
                        <HeadlessTableTh>#{{ notification.id }}</HeadlessTableTh>
                        <HeadlessTableTd>
                            {{ notification.message }}
                        </HeadlessTableTd>
                        <HeadlessTableTd style="width: 150px">
                            <ProgressNumber :current="notification.delivered_count" :total="notification.recipients_count"></ProgressNumber>
                        </HeadlessTableTd>
                        <HeadlessTableTd>
                            <DateTime class="justify-end text-nowrap" :data="notification.created_at"/>
                        </HeadlessTableTd>
                    </HeadlessTableTr>
                </HeadllesTable>
            </div>-->
            <Pagination
                v-model="currentPage"
                :total-items="notifications.meta.total"
                previous-label="Назад" next-label="Вперед"
                @page-changed="openPage"
                :per-page="notifications.meta.per_page"
            ></Pagination>
        </div>
        <NotificationModal/>
    </div>
</template>
