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
import CopyAddress from "@/Components/CopyAddress.vue";
import InputFilter from "@/Components/Filters/Pertials/InputFilter.vue";
import DropdownFilter from "@/Components/Filters/Pertials/DropdownFilter.vue";
import FiltersPanel from "@/Components/Filters/FiltersPanel.vue";
import {ref} from "vue";
import DateTime from "@/Components/DateTime.vue";

const modalStore = useModalStore();

const invoices = ref(usePage().props.invoices);

const confirmSuccessWithdrawal = (invoice) => {
    modalStore.openConfirmModal({
        title: 'Вы уверены что хотите завершить заявку как успешную?',
        confirm_button_name: 'Подтвердить',
        confirm: () => {
            useForm({}).patch(route('admin.withdrawals.success', invoice.id), {
                preserveScroll: true,
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
            });
        }
    });
};

router.on('success', () => {
    invoices.value = usePage().props.invoices;
})

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Заявки на вывод средств" />

        <MainTableSection
            title="Заявки на вывод средств"
            :data="invoices"
        >
            <template v-slot:header>
                <FiltersPanel name="withdrawals">
                    <DropdownFilter
                        name="invoiceStatuses"
                        title="Статусы"
                    />
                    <InputFilter
                        name="id"
                        placeholder="ID вывода"
                    />
                    <InputFilter
                        name="amount"
                        placeholder="Сумма"
                    />
                    <InputFilter
                        name="user"
                        placeholder="Пользователь"
                    />
                    <InputFilter
                        name="address"
                        placeholder="Адрес"
                    />
                </FiltersPanel>
            </template>
            <template v-slot:body>
                <div class="overflow-x-auto card bg-base-100 shadow">
                    <table class="table table-sm">
                        <thead class="text-xs uppercase bg-base-300">
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col" class="text-nowrap">External ID</th>
                            <th scope="col">Сумма</th>
                            <th scope="col">Пользователь</th>
                            <th scope="col">Адрес</th>
                            <th scope="col">txHash</th>
                            <th scope="col">Статус</th>
                            <th scope="col">Дата создания</th>
                            <th scope="col" class="flex justify-center">
                                <span class="sr-only">Действия</span>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="invoice in invoices.data" class="hover">
                            <th scope="row" class="font-medium whitespace-nowrap">
                                {{ invoice.id }}
                            </th>
                            <td>
                                {{ invoice.external_id }}
                            </td>
                            <td>
                                <div class="text-nowrap">{{ invoice.amount }} {{invoice.currency.toUpperCase()}}</div>
                                <div v-show="invoice.balance_type === 'trust'" class="text-xs text-base-content/70">
                                    Траст
                                </div>
                                <div v-show="invoice.balance_type === 'merchant'" class="text-xs text-base-content/70">
                                    Мерчант
                                </div>
                            </td>
                            <td>
                                {{ invoice.user.email }}
                            </td>
                            <td>
                                <div class="flex gap-2">
                                    <CopyAddress v-if="invoice.address" :text="invoice.address"></CopyAddress>
                                    <div class="text-primary">{{ invoice.network?.toUpperCase() }}</div>
                                </div>
                            </td>
                            <td>
                                <CopyAddress v-if="invoice.tx_hash" :text="invoice.tx_hash"></CopyAddress>
                            </td>
                            <td>
                                <InvoiceStatus :status="invoice.status"></InvoiceStatus>
                            </td>
                            <td class="text-nowrap">
                                <DateTime :data="invoice.created_at"></DateTime>
                            </td>
                            <td class="text-nowrap text-right">
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
