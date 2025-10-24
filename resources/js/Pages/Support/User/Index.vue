<script setup>
import {Head, usePage, useForm} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import InputFilter from "@/Components/Filters/Pertials/InputFilter.vue";
import FiltersPanel from "@/Components/Filters/FiltersPanel.vue";
import {ref} from "vue";
import FilterCheckbox from "@/Components/Filters/Pertials/FilterCheckbox.vue";
import DateTime from "@/Components/DateTime.vue";

const users = ref(usePage().props.users);

const form = useForm({});

const toggleTraffic = (user) => {
    form.patch(route('support.users.toggle-traffic', user.id), {
        preserveScroll: true,
        onSuccess: (result) => {
            users.value = result.props.users;
        },
    });
};

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Пользователи" />

        <MainTableSection
            title="Пользователи"
            :data="users"
        >
            <template v-slot:header>
                <FiltersPanel name="users">
                    <InputFilter
                        name="user"
                        placeholder="Поиск (почта или имя)"
                        class="w-64"
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
                    <table class="table table-md">
                        <thead class="text-xs uppercase bg-base-300">
                        <tr>
                            <th scope="col">
                                ID
                            </th>
                            <th scope="col">
                                Пользователь
                            </th>
                            <th scope="col">
                                Баланс
                            </th>
                            <th scope="col">
                                Роль
                            </th>
                            <th scope="col">
                                Пинг
                            </th>
                            <th scope="col">
                                Создан
                            </th>
                            <th scope="col">
                                Онлайн
                            </th>
                            <th scope="col">
                                Трафик
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="user in users.data" class="bg-base-100 border-b last:border-none">
                            <th scope="row" class=" font-medium whitespace-nowrap">
                                {{ user.id }}
                            </th>
                            <td class=" whitespace-nowrap">
                                <div class="inline-flex items-center gap-3">
                                    <div class="avatar">
                                        <div class="w-10 rounded-full">
                                            <img :src="'https://api.dicebear.com/9.x/'+user.avatar_style+'/svg?seed='+user.avatar_uuid" alt="user photo">
                                        </div>
                                    </div>
                                    <div>
                                        <div class="whitespace-nowrap">
                                            {{ user.email }}
                                        </div>
                                        <div class="whitespace-nowrap text-xs text-base-content/70">
                                            {{ user.name }}
                                        </div>
                                    </div>
                                    <span v-if="user.banned_at" class="badge badge-error badge-sm" title="Пользователь заблокирован">Ban</span>
                                    <span v-if="user.stop_traffic" class="badge badge-error badge-sm" title="Трафик остановлен">Stop</span>
                                </div>
                            </td>
                            <td class=" whitespace-nowrap">
                                {{ user.balance }} $
                            </td>
                            <td class=" whitespace-nowrap">
                                {{ user.role.name }}
                            </td>
                            <td class=" whitespace-nowrap">
                                <DateTime v-if="user.apk_latest_ping_at" :data="user.apk_latest_ping_at" :plural="true"/>
                            </td>
                            <td class=" whitespace-nowrap">
                                {{ user.created_at }}
                            </td>
                            <td class=" whitespace-nowrap">
                                <span v-if="user.is_online" class="badge badge-success badge-sm">Онлайн</span>
                                <span v-else class="badge badge-error badge-sm">Офлайн</span>
                            </td>
                            <td class=" whitespace-nowrap">
                                <label class="inline-flex items-center cursor-pointer gap-2">
                                    <input
                                        type="checkbox"
                                        class="toggle toggle-success toggle-sm"
                                        :checked="!user.stop_traffic"
                                        @change="toggleTraffic(user)"
                                        :disabled="form.processing"
                                    >
                                    <span class="text-xs">
                                        {{ user.stop_traffic ? 'Выкл.' : 'Вкл.' }}
                                    </span>
                                </label>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </MainTableSection>
    </div>
</template>
