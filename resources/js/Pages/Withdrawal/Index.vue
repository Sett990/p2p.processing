<script setup>
import {Head, router, useForm} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { usePage } from '@inertiajs/vue3';
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import InvoiceStatus from "@/Components/InvoiceStatus.vue";
import SuccessAction from "@/Components/Table/SuccessAction.vue";
import FailAction from "@/Components/Table/FailAction.vue";
import {useModalStore} from "@/store/modal.js";
import ConfirmModal from "@/Components/Modals/ConfirmModal.vue";

const modalStore = useModalStore();

const invoices = usePage().props.invoices;

const confirmSuccessWithdrawal = (invoice) => {
    modalStore.openConfirmModal({
        title: 'Вы уверены что хотите завершить заявку как успешную?',
        confirm_button_name: 'Подтвердить',
        confirm: () => {
            useForm({}).patch(route('admin.withdrawals.success', invoice.id), {
                preserveScroll: true,
                onFinish: () => {
                    router.visit(route('admin.withdrawals.index'))
                },
            });
        }
    });
};

const confirmFailParser = (invoice) => {
    modalStore.openConfirmModal({
        title: 'Вы уверены что хотите отклонить заявку?',
        confirm_button_name: 'Отклонить',
        confirm: () => {
            useForm({}).patch(route('admin.withdrawals.fail', invoice.id), {
                preserveScroll: true,
                onFinish: () => {
                    router.visit(route('admin.withdrawals.index'))
                },
            });
        }
    });
};

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Заявки на вывод средств" />

        <MainTableSection
            title="Заявки на вывод средств"
            :data="invoices"
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
                                Сумма
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Пользователь
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Баланс
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Статус
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Дата создания
                            </th>
                            <th scope="col" class="px-6 py-3 flex justify-center">
                                <span class="sr-only">Действия</span>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="invoice in invoices.data" class="bg-white border-b last:border-none dark:bg-gray-800 dark:border-gray-700">
                            <th scope="row" class="px-6 py-3 font-medium whitespace-nowrap text-gray-900 dark:text-gray-200">
                                {{ invoice.id }}
                            </th>
                            <td class="px-6 py-3 text-nowrap">
                                {{ invoice.amount }} {{invoice.currency.toUpperCase()}}
                            </td>
                            <td class="px-6 py-3">
                                {{ invoice.user.email }}
                            </td>
                            <td class="px-6 py-3">
                                <span v-show="invoice.source_type === 'trust'">
                                    Траст
                                </span>
                                <span v-show="invoice.source_type === 'merchant'">
                                    Мерчант
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                <InvoiceStatus :status="invoice.status"></InvoiceStatus>
                            </td>
                            <td class="px-6 py-3 text-nowrap">
                                {{ invoice.created_at }}
                            </td>
                            <td class="px-6 py-3 text-nowrap text-right">
                                <template v-if="invoice.status === 'pending'">
                                    <SuccessAction @click.prevent="confirmSuccessWithdrawal(invoice)"/>
                                    <FailAction class="ml-3" @click.prevent="confirmFailParser(invoice)"/>
                                </template>
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
