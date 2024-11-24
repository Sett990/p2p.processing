<script setup>
import { Link, Head, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EditAction from "@/Components/Table/EditAction.vue";
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import AddMobileIcon from "@/Components/AddMobileIcon.vue";

const users = usePage().props.users;

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Пользователи" />

        <MainTableSection
            title="Пользователи"
            :data="users"
        >
            <template v-slot:button>
                <button
                    @click="router.visit(route('admin.users.create'))"
                    type="button"
                    class="hidden md:block text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800"
                >
                    Создать пользователя
                </button>
                <AddMobileIcon
                    @click="router.visit(route('admin.users.create'))"
                />
            </template>
            <template v-slot:body>
                <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">
                                ID
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Имя
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Почта
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Роль
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Создан
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
                                <div class="inline-flex">
                                    <span>{{ user.name }}</span>
                                    <span
                                        v-if="user.banned_at"
                                        class="ml-2"
                                    >
                                        <svg class="w-4 h-4 text-red-600" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                            <path fill-rule="evenodd" d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12Zm7.707-3.707a1 1 0 0 0-1.414 1.414L10.586 12l-2.293 2.293a1 1 0 1 0 1.414 1.414L12 13.414l2.293 2.293a1 1 0 0 0 1.414-1.414L13.414 12l2.293-2.293a1 1 0 0 0-1.414-1.414L12 10.586 9.707 8.293Z" clip-rule="evenodd"/>
                                        </svg>
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-3">
                                {{ user.email }}
                            </td>
                            <td class="px-6 py-3 text-nowrap">
                                {{ user.role.name }}
                            </td>
                            <td class="px-6 py-3 text-nowrap">
                                {{ user.created_at }}
                            </td>
                            <td class="px-6 py-3 text-nowrap text-right">
                                <Link
                                    :href="route('admin.users.wallet.index', user.id)"
                                    class="mr-2 px-0 py-0 text-yellow-400 hover:text-yellow-500 dark:text-yellow-500 dark:hover:text-yellow-600 inline-flex items-center hover:underline"
                                >
                                    <svg class="w-[22px] h-[22px]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8H5m12 0a1 1 0 0 1 1 1v2.6M17 8l-4-4M5 8a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.6M5 8l4-4 4 4m6 4h-4a2 2 0 1 0 0 4h4a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1Z"/>
                                    </svg>
                                </Link>
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
