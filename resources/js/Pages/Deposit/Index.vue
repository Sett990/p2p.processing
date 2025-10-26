<script setup>
import {Head} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { usePage } from '@inertiajs/vue3';
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import InvoiceStatus from "@/Components/InvoiceStatus.vue";
import ConfirmModal from "@/Components/Modals/ConfirmModal.vue";
import InputFilter from "@/Components/Filters/Pertials/InputFilter.vue";
import DropdownFilter from "@/Components/Filters/Pertials/DropdownFilter.vue";
import FiltersPanel from "@/Components/Filters/FiltersPanel.vue";
import {ref} from "vue";
import DateTime from "@/Components/DateTime.vue";
import CopyAddress from "@/Components/CopyAddress.vue";

const invoices = usePage().props.invoices;

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Депозиты средств" />

        <MainTableSection
            title="Депозиты средств"
            :data="invoices"
        >
            <template v-slot:header>
                <FiltersPanel name="deposits">
                    <DropdownFilter
                        name="invoiceStatuses"
                        title="Статусы"
                    />
                    <InputFilter
                        name="id"
                        placeholder="ID депозита"
                    />
                    <InputFilter
                        name="amount"
                        placeholder="Сумма"
                    />
                    <InputFilter
                        name="user"
                        placeholder="Пользователь"
                    />
                </FiltersPanel>
            </template>
            <template v-slot:body>
                <div class="overflow-x-auto card bg-base-100 shadow">
                    <table class="table table-sm">
                        <thead class="text-xs uppercase bg-base-300">
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col" class="text-nowrap">Transaction ID</th>
                            <th scope="col">Сумма</th>
                            <th scope="col">Пользователь</th>
                            <th scope="col">txHash</th>
                            <th scope="col">Статус</th>
                            <th scope="col">Дата создания</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="invoice in invoices.data" class="hover">
                            <th scope="row" class="font-medium whitespace-nowrap">
                                {{ invoice.id }}
                            </th>
                            <td>
                                {{ invoice.transaction_id }}
                            </td>
                            <td>
                                <div class="text-nowrap">{{ invoice.amount }} {{invoice.currency.toUpperCase()}}</div>
                                <div v-show="invoice.balance_type === 'trust'" class="text-xs opacity-70">Траст</div>
                                <div v-show="invoice.balance_type === 'merchant'" class="text-xs opacity-70">Мерчант</div>
                            </td>
                            <td>
                                {{ invoice.user.email }}
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
                        </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </MainTableSection>

        <ConfirmModal/>
    </div>
</template>
