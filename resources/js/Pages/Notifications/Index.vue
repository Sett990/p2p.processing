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
import ConfirmModal from "@/Components/Modals/ConfirmModal.vue";

const modalStore = useModalStore();
const viewStore = useViewStore();

const tgBot = ref(usePage().props.tgBot);
const notifications = usePage().props.notifications;

const form = useForm({
    message: '',
});

const openPage = (page) => {
    router.visit(route(route().current()), { data: {
            page
        } })
}

const unlinkTelegram = () => {
    modalStore.openConfirmModal({
        title: 'Отвязка Telegram',
        body: 'Вы уверены, что хотите отвязать Telegram от вашего аккаунта?',
        confirm_button_name: 'Отвязать',
        confirm: () => {
            router.delete(route('notifications.unlink_telegram'));
        }
    });
}

router.on('success', (event) => {
    tgBot.value = usePage().props.tgBot;
})

const currentPage = ref(notifications?.meta?.current_page)

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <Head title="Уведомления" />

    <div class="mx-auto space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-3xl font-bold text-base-content">Уведомления в телеграм</h2>
        </div>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-3 lg:grid-cols-2 md:grid-cols-2 mb-6">
            <div class="grow sm:mt-8 lg:mt-0">
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <div class="flex justify-between">
                            <div class="card-title text-xl">Телеграм</div>
                        </div>

                        <template v-if="! tgBot.user_telegram_id">
                            <div class="inline-flex py-3">
                                <div class="text-sm">
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
                                <div class="text-sm">
                                    Для получения уведомлений, и управления аккаунтом через телеграм —
                                    <a :href="tgBot.openTelegramBot" target="_blank" class="link link-primary">запустите телеграм бот</a>.
                                </div>
                            </div>
                            <div class="mt-3">
                                <button @click="unlinkTelegram" type="button" class="btn btn-error">
                                    Отвязать Telegram
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="viewStore.isAdminViewMode">
            <div class="flex justify-between mb-3">
                <h2 class="text:xl font-medium sm:text-2xl">Отправленные уведомления</h2>
                <div>
                    <button @click="modalStore.openNotificationModal({})" type="button" class="hidden md:inline-flex btn btn-primary rounded-xl">
                        Новое уведомление
                    </button>
                    <AddMobileIcon
                        @click="modalStore.openNotificationModal({})" color="default"
                    />
                </div>
            </div>
            <div class="overflow-x-auto card bg-base-100 shadow mb-3">
                <table class="table table-sm">
                    <thead class="text-xs uppercase bg-base-300">
                        <tr>
                            <th scope="col" class="px-6 py-3">ID</th>
                            <th scope="col" class="px-6 py-3">Сообщение</th>
                            <th scope="col" class="px-6 py-3 text-nowrap">Прогресс доставки</th>
                            <th scope="col" class="px-6 py-3 text-nowrap">Дата отправки</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="notification in notifications.data" class="hover">
                            <th scope="row" class="px-6 py-3 font-medium whitespace-nowrap">
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
        <ConfirmModal/>
    </div>
</template>
