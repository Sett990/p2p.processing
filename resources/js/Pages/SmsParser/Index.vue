<script setup>
import {Head, router, useForm} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { usePage } from '@inertiajs/vue3';
import EditAction from "@/Components/Table/EditAction.vue";
import DeleteAction from "@/Components/Table/DeleteAction.vue";
import {useModalStore} from "@/store/modal.js";
import ConfirmModal from "@/Components/Modals/ConfirmModal.vue";
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import AddMobileIcon from "@/Components/AddMobileIcon.vue";

const sms_parsers = usePage().props.smsParsers;
const modalStore = useModalStore();

const confirmDeleteParser = (parser) => {
    modalStore.openConfirmModal({
        title: 'Вы уверены что хотите удалить этот парсер?',
        confirm_button_name: 'Удалить парсер',
        confirm: () => {
            useForm({}).delete(route('admin.sms-parsers.destroy', parser), {
                preserveScroll: true,
                onFinish: () => {
                    router.visit(route('admin.sms-parsers.index'), {
                        only: ['smsParsers'],
                    })
                },
            });
        }
    });
};

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Парсеры" />

        <MainTableSection
            title="Парсеры"
            :data="sms_parsers"
        >
            <template v-slot:button>
                <button
                    @click="router.visit(route('admin.sms-parsers.create'))"
                    type="button"
                    class="hidden md:block text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800"
                >
                    Создать парсер
                </button>
                <AddMobileIcon
                    @click="router.visit(route('admin.sms-parsers.create'))"
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
                                Метод
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Формат
                            </th>
                            <th scope="col" class="px-6 py-3 flex justify-center">
                                <span class="sr-only">Действия</span>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="sms_parser in sms_parsers.data" class="bg-white border-b last:border-none dark:bg-gray-800 dark:border-gray-700">
                            <th scope="row" class="px-6 py-3 font-medium whitespace-nowrap text-gray-900 dark:text-gray-200">
                                {{ sms_parser.id }}
                            </th>
                            <td class="px-6 py-3">
                                {{ sms_parser.payment_gateway_name }}
                            </td>
                            <td class="px-6 py-3">
                                <div style="min-width: 200px;">
                                    {{ sms_parser.format }}
                                </div>
                            </td>
                            <td class="px-6 py-3 inline-flex text-nowrap text-right">
                                <EditAction class="mr-2" :link="route('admin.sms-parsers.edit', sms_parser.id)"></EditAction>
                                <DeleteAction @click.prevent="confirmDeleteParser(sms_parser)"></DeleteAction>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </MainTableSection>

        <ConfirmModal/>
    </div>
</template>
