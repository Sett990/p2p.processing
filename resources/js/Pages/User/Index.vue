<script setup>
import {Link, Head, router, usePage, useForm} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EditAction from "@/Components/Table/EditAction.vue";
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import AddMobileIcon from "@/Components/AddMobileIcon.vue";
import InputFilter from "@/Components/Filters/Pertials/InputFilter.vue";
import FiltersPanel from "@/Components/Filters/FiltersPanel.vue";
import {ref} from "vue";
import FilterCheckbox from "@/Components/Filters/Pertials/FilterCheckbox.vue";
import DateTime from "@/Components/DateTime.vue";
import UserNotesModal from "@/Modals/User/UserNotesModal.vue";
import {useModalStore} from "@/store/modal.js";

const users = ref(usePage().props.users);
const filters = ref(usePage().props.filters);
const modalStore = useModalStore();

const onlineForm = useForm({
    is_online: 0,
    is_payout_online: 0
});

const toggleOnline = (order, type) => {

    onlineForm
        .transform((data) => {
            data.is_online = order.is_online;
            data.is_payout_online = order.is_payout_online;

            if (type === 'order') {
                order.is_online = !order.is_online
                data.is_online = order.is_online;
            } else if (type === 'payout') {
                order.is_payout_online = !order.is_payout_online
                data.is_payout_online = order.is_payout_online;
            }

            return data;
        })
        .patch(route('admin.users.toggle-online', order.id), {
            preserveScroll: true,
            onSuccess: (result) => {
                users.value = result.props.users;
            },
        });
};

const impersonate = (user) => {
    useForm().post(route('admin.impersonate.start', { user: user.id }));
};

const openUserNotesModal = (user) => {
    modalStore.openUserNotesModal({user});
};

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Пользователи" />

        <UserNotesModal />

        <MainTableSection
            title="Пользователи"
            :data="users"
            :query-data="{filters}"
        >
            <template v-slot:button>
                <button
                    @click="router.visit(route('admin.users.create'))"
                    type="button"
                    class="hidden md:block text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-xl text-base px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800"
                >
                    Создать пользователя
                </button>
                <AddMobileIcon
                    @click="router.visit(route('admin.users.create'))"
                />
            </template>
            <template v-slot:header>
                <FiltersPanel name="users" :filters="filters">
                    <InputFilter
                        v-model="filters.user"
                        placeholder="Поиск (почта или имя)"
                        class="w-64"
                    />
                    <FilterCheckbox
                        v-model="filters.online"
                        title="Онлайн"
                    />
                    <FilterCheckbox
                        v-model="filters.traffic_disabled"
                        title="Трафик выключен"
                    />
                </FiltersPanel>
            </template>
            <template v-slot:body>
                <div class="relative overflow-x-auto shadow-md rounded-table">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">
                                ID
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Пользователь
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Баланс
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Роль
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Пинг
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Создан
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Онлайн
                            </th>
                            <th scope="col" class="px-6 py-3 flex justify-center">
                                <span class="sr-only">Действия</span>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="user in users.data" class="bg-white border-b last:border-none dark:bg-gray-800 dark:border-gray-700">
                            <th scope="row" class="px-6 py-3 font-medium whitespace-nowrap text-gray-900 dark:text-gray-200">
                                {{ user.id }}
                            </th>
                            <td class="px-6 py-3 text-nowrap">
                                <div class="inline-flex items-center gap-2">
                                    <img :src="'https://api.dicebear.com/9.x/'+user.avatar_style+'/svg?seed='+user.avatar_uuid" class="w-10 h-10 rounded-full" alt="user photo">
                                    <div>
                                        <div class="text-nowrap text-gray-900 dark:text-gray-200">
                                            {{ user.email }}
                                        </div>
                                        <div class="text-nowrap text-xs">
                                            {{ user.name }}
                                        </div>
                                    </div>
                                    <span
                                        v-if="user.banned_at"
                                        title="Пользователь заблокирован"
                                    >
                                        <svg class="w-4 h-4 text-red-600" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                            <path fill-rule="evenodd" d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12Zm7.707-3.707a1 1 0 0 0-1.414 1.414L10.586 12l-2.293 2.293a1 1 0 1 0 1.414 1.414L12 13.414l2.293 2.293a1 1 0 0 0 1.414-1.414L13.414 12l2.293-2.293a1 1 0 0 0-1.414-1.414L12 10.586 9.707 8.293Z" clip-rule="evenodd"/>
                                        </svg>
                                    </span>
                                    <span
                                        v-if="user.stop_traffic"
                                        title="Трафик остановлен"
                                    >
                                        <svg class="w-4 h-4 text-red-600" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                            <path fill-rule="evenodd" d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12Zm3-1a1 1 0 0 1 1-1h12a1 1 0 1 1 0 2H6a1 1 0 0 1-1-1Z" clip-rule="evenodd"/>
                                        </svg>
                                    </span>
                                    <span
                                        v-else-if="user.traffic_enabled_at"
                                        :title="'Трафик включен: ' + user.traffic_enabled_at"
                                    >
                                        <svg class="w-4 h-4 text-green-600" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                            <path fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2s10 4.477 10 10Zm-11.99 4a1 1 0 0 1-.705-.292l-3.99-3.96a1 1 0 0 1 1.41-1.419l3.285 3.26 6.289-6.254a1 1 0 0 1 1.41 1.418l-6.99 6.955a1 1 0 0 1-.709.292Z" clip-rule="evenodd"/>
                                        </svg>
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-nowrap">
                                {{ user.balance }} $
                            </td>
                            <td class="px-6 py-3 text-nowrap">
                                {{ user.role.name }}
                            </td>
                            <td class="px-6 py-3 text-nowrap">
                                <DateTime v-if="user.apk_latest_ping_at" :data="user.apk_latest_ping_at" :plural="true"/>
                            </td>
                            <td class="px-6 py-3 text-nowrap">
                                {{ user.created_at }}
                            </td>
                            <td class="px-6 py-3 text-nowrap">
                                <div class="space-y-1">
                                    <div class="flex items-center">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" :checked="user.is_online" class="sr-only peer" @change="toggleOnline(user, 'order')" :disabled="onlineForm.processing">
                                            <div class="me-3 relative w-9 h-5 bg-gray-200 rounded-full peer dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-green-600 dark:peer-checked:bg-green-600"></div>
                                            <span :class="user.is_online ? 'text-sm font-medium text-green-500 dark:text-green-400' : 'text-sm font-medium text-red-500 dark:text-red-500'"><!--Сделки--></span>
                                        </label>
                                    </div>
<!--                                    <div class="flex items-center">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" :checked="user.is_payout_online" class="sr-only peer" @change="toggleOnline(user, 'payout')" :disabled="onlineForm.processing">
                                            <div class="me-3 relative w-9 h-5 bg-gray-200 rounded-full peer dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-green-600 dark:peer-checked:bg-green-600"></div>
                                            <span :class="user.is_payout_online ? 'text-sm font-medium text-green-500 dark:text-green-400' : 'text-sm font-medium text-red-500 dark:text-red-500'">Выплаты</span>
                                        </label>
                                    </div>-->
                                </div>
                            </td>
                            <td class="px-6 py-3 text-nowrap text-right">
                                <Link
                                    v-if="user.can_be_impersonated"
                                    @click="impersonate(user)"
                                    href="#"
                                    class="mr-2 px-0 py-0 text-red-400 hover:text-red-500 dark:text-red-500 dark:hover:text-red-600 inline-flex items-center hover:underline"
                                >
                                    <svg class="w-[22px] h-[22px]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12H4m12 0-4 4m4-4-4-4m3-4h2a3 3 0 0 1 3 3v10a3 3 0 0 1-3 3h-2"/>
                                    </svg>
                                </Link>
                                <Link
                                    :href="route('admin.users.wallet.index', user.id)"
                                    class="mr-2 px-0 py-0 text-yellow-400 hover:text-yellow-500 dark:text-yellow-500 dark:hover:text-yellow-600 inline-flex items-center hover:underline"
                                >
                                    <svg class="w-[22px] h-[22px]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8H5m12 0a1 1 0 0 1 1 1v2.6M17 8l-4-4M5 8a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.6M5 8l4-4 4 4m6 4h-4a2 2 0 1 0 0 4h4a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1Z"/>
                                    </svg>
                                </Link>
                                <button
                                    @click="openUserNotesModal(user)"
                                    class="mr-2 px-0 py-0 text-purple-500 hover:text-purple-600 dark:text-purple-400 dark:hover:text-purple-300 inline-flex items-center hover:underline"
                                >
                                    <svg class="w-[22px] h-[22px]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5h8m-8 5h8m-8 5h4.5M5 4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V4Z"/>
                                    </svg>
                                </button>
                                <EditAction :link="route('admin.users.edit', user.id)"></EditAction>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </MainTableSection>
    </div>
</template>
