<script setup>
import {Link, Head, router, usePage, useForm} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EditAction from "@/Components/Table/EditAction.vue";
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import AddMobileIcon from "@/Components/AddMobileIcon.vue";
import InputFilter from "@/Components/Filters/Pertials/InputFilter.vue";
import FiltersPanel from "@/Components/Filters/FiltersPanel.vue";
import {ref, onUnmounted} from "vue";
import FilterCheckbox from "@/Components/Filters/Pertials/FilterCheckbox.vue";
import DateTime from "@/Components/DateTime.vue";
import UserNotesModal from "@/Modals/User/UserNotesModal.vue";
import {useModalStore} from "@/store/modal.js";
import DropdownFilter from "@/Components/Filters/Pertials/DropdownFilter.vue";

const users = ref(usePage().props.users);
const modalStore = useModalStore();

const isCooldown = ref(false);
let cooldownTimer = null;
onUnmounted(() => {
    if (cooldownTimer) {
        clearTimeout(cooldownTimer);
        cooldownTimer = null;
    }
});

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
            onFinish: () => {
                if (cooldownTimer) {
                    clearTimeout(cooldownTimer);
                }
                isCooldown.value = true;
                cooldownTimer = setTimeout(() => {
                    isCooldown.value = false;
                    cooldownTimer = null;
                }, 300);
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
        >
            <template v-slot:button>
                <button
                    @click="router.visit(route('admin.users.create'))"
                    type="button"
                    class="hidden md:block btn btn-sm btn-primary"
                >
                    Создать пользователя
                </button>
                <AddMobileIcon
                    @click="router.visit(route('admin.users.create'))"
                />
            </template>
            <template v-slot:table-filters>
                <FiltersPanel name="users">
                    <InputFilter
                        name="user"
                        placeholder="Поиск (почта или имя)"
                        class="w-64"
                    />
                    <DropdownFilter
                        name="roles"
                        title="Роли"
                    />
                    <FilterCheckbox
                        name="online"
                        title="Онлайн"
                    />
                    <FilterCheckbox
                        name="traffic_disabled"
                        title="Трафик выключен"
                    />
                </FiltersPanel>
            </template>
            <template v-slot:body>
                <div class="overflow-x-auto card bg-base-100 shadow">
                    <table class="table table-sm">
                        <thead class="text-xs uppercase bg-base-300">
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
                                Онлайн
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
                        <tr v-for="user in users.data" class="hover">
                            <th scope="row" class="px-6 py-3 font-medium whitespace-nowrap">
                                {{ user.id }}
                            </th>
                            <td class="px-6 py-3 text-nowrap">
                                <div class="inline-flex items-center gap-2">
                                    <img :src="'https://api.dicebear.com/9.x/'+user.avatar_style+'/svg?seed='+user.avatar_uuid" class="w-10 h-10 rounded-full" alt="user photo">
                                    <div>
                                        <div class="text-nowrap">
                                            {{ user.email }}
                                        </div>
                                        <div class="text-nowrap text-xs">
                                            {{ user.role.name }}
                                        </div>
                                    </div>
                                    <span
                                        v-if="user.banned_at"
                                        title="Пользователь заблокирован"
                                    >
                                        <svg class="w-4 h-4 text-danger" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                            <path fill-rule="evenodd" d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12Zm7.707-3.707a1 1 0 0 0-1.414 1.414L10.586 12l-2.293 2.293a1 1 0 1 0 1.414 1.414L12 13.414l2.293 2.293a1 1 0 0 0 1.414-1.414L13.414 12l2.293-2.293a1 1 0 0 0-1.414-1.414L12 10.586 9.707 8.293Z" clip-rule="evenodd"/>
                                        </svg>
                                    </span>
                                    <span
                                        v-if="user.stop_traffic"
                                        title="Трафик остановлен"
                                    >
                                        <svg class="w-4 h-4 text-error" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                            <path fill-rule="evenodd" d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12Zm3-1a1 1 0 0 1 1-1h12a1 1 0 1 1 0 2H6a1 1 0 0 1-1-1Z" clip-rule="evenodd"/>
                                        </svg>
                                    </span>
                                    <span
                                        v-else-if="user.traffic_enabled_at"
                                        :title="'Трафик включен: ' + user.traffic_enabled_at"
                                    >
                                        <svg class="w-4 h-4 text-success" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                            <path fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2s10 4.477 10 10Zm-11.99 4a1 1 0 0 1-.705-.292l-3.99-3.96a1 1 0 0 1 1.41-1.419l3.285 3.26 6.289-6.254a1 1 0 0 1 1.41 1.418l-6.99 6.955a1 1 0 0 1-.709.292Z" clip-rule="evenodd"/>
                                        </svg>
                                    </span>
                                    <span
                                        v-if="user.is_vip"
                                        title="VIP пользователь"
                                    >
                                        <svg class="w-4 h-4 text-primary" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                            <path fill-rule="evenodd" d="M10.788 3.103c.495-1.004 1.926-1.004 2.421 0l2.358 4.777 5.273.766c1.107.16 1.55 1.522.748 2.303l-3.816 3.72.9 5.25c.19 1.104-.968 1.945-1.959 1.424l-4.716-2.48-4.715 2.48c-.99.52-2.148-.32-1.96-1.424l.9-5.25-3.815-3.72c-.8-.78-.36-2.142.748-2.303l5.274-.766 2.358-4.777Z" clip-rule="evenodd"/>
                                        </svg>
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-nowrap">
                                {{ user.balance }} $
                            </td>
                            <td class="px-6 py-3 text-nowrap">
                                <DateTime v-if="user.online_at" :data="user.online_at" :plural="true"/>
                            </td>
                            <td class="px-6 py-3 text-nowrap">
                                {{ user.created_at }}
                            </td>
                            <td class="px-6 py-3 text-nowrap">
                                <div class="space-y-1">
                                    <div class="flex items-center">
                                        <input type="checkbox" :checked="user.is_online" class="toggle toggle-success" @change="toggleOnline(user, 'order')" :disabled="onlineForm.processing || isCooldown">
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
                                    class="mr-2 btn btn-ghost btn-xs"
                                >
                                    <svg class="w-[22px] h-[22px] text-warning" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12H4m12 0-4 4m4-4-4-4m3-4h2a3 3 0 0 1 3 3v10a3 3 0 0 1-3 3h-2"/>
                                    </svg>
                                </Link>
                                <Link
                                    :href="route('admin.users.wallet.index', user.id)"
                                    class="mr-2 btn btn-ghost btn-xs"
                                >
                                    <svg class="w-[22px] h-[22px] text-info" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8H5m12 0a1 1 0 0 1 1 1v2.6M17 8l-4-4M5 8a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.6M5 8l4-4 4 4m6 4h-4a2 2 0 1 0 0 4h4a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1Z"/>
                                    </svg>
                                </Link>
                                <button
                                    @click="openUserNotesModal(user)"
                                    class="mr-2 btn btn-ghost btn-xs text-accent"
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
