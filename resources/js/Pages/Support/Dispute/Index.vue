<script setup>
import {Head, usePage} from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PaymentDetail from "@/Components/PaymentDetail.vue";
import DisputeStatus from "@/Components/DisputeStatus.vue";
import {useModalStore} from "@/store/modal.js";
import DisputeModal from "@/Modals/DisputeModal.vue";
import ConfirmModal from "@/Components/Modals/ConfirmModal.vue";
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import DateTime from "@/Components/DateTime.vue";
import ShowAction from "@/Components/Table/ShowAction.vue";
import DisplayUUID from "@/Components/DisplayUUID.vue";
import InputFilter from "@/Components/Filters/Pertials/InputFilter.vue";
import FiltersPanel from "@/Components/Filters/FiltersPanel.vue";
import DropdownFilter from "@/Components/Filters/Pertials/DropdownFilter.vue";

const modalStore = useModalStore();

const disputes = usePage().props.disputes;
const oldestDisputeCreatedAt = usePage().props.oldestDisputeCreatedAt;

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Споры" />

        <MainTableSection
            title="Споры по сделкам"
            :data="disputes"
        >
            <template v-slot:table-filters>
                <div>
                    <FiltersPanel name="orders">
                        <InputFilter
                            name="uuid"
                            placeholder="UUID"
                        />
                        <InputFilter
                            name="externalID"
                            placeholder="Внешний ID"
                        />
                        <InputFilter
                            name="amount"
                            placeholder="Сумма"
                        />
                        <InputFilter
                            name="paymentDetail"
                            placeholder="Реквизит"
                        />
                        <InputFilter
                            name="user"
                            placeholder="Пользователь"
                        />
                        <DropdownFilter
                            name="disputeStatuses"
                            title="Статусы"
                        />
                    </FiltersPanel>
                </div>
            </template>
            <template v-slot:body>
                <div v-if="oldestDisputeCreatedAt" class="flex gap-5">
                    <div class="flex text-base text-base-content/70 mb-3 gap-3">
                        <div>Самый старый:</div>
                        <div>
                            <DateTime :data="oldestDisputeCreatedAt" :plural="true"></DateTime>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto card bg-base-100 shadow">
                    <table class="table table-md">
                        <thead class="text-xs uppercase bg-base-300">
                            <tr>
                                <th scope="col">
                                    ID
                                </th>
                                <th scope="col" class=" text-nowrap">
                                    Сделка
                                </th>
                                <th scope="col">
                                    Реквизит
                                </th>
                                <th scope="col">
                                    Сумма
                                </th>
                                <th scope="col">
                                    Трейдер
                                </th>
                                <th scope="col">
                                    Статус
                                </th>
                                <th scope="col">
                                    Создан
                                </th>
                                <th scope="col" class=" flex justify-center">
                                    <span class="sr-only">Действия</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="dispute in disputes.data" class="bg-base-100 border-b last:border-none">
                                <th scope="row" class=" font-medium whitespace-nowrap">
                                    {{ dispute.id }}
                                </th>
                                <td>
                                    <DisplayUUID :uuid="dispute.order.uuid"/>
                                </td>
                                <td>
                                    <PaymentDetail
                                        :detail="dispute.payment_detail.detail"
                                        :type="dispute.payment_detail.type"
                                        :copyable="false"
                                        class=""
                                    ></PaymentDetail>
                                    <div class="text-nowrap text-xs">{{ dispute.payment_detail.name }}</div>
                                </td>
                                <td>
                                    <div class="text-nowrap">{{ dispute.order.amount }} {{dispute.order.currency.toUpperCase()}}</div>
                                    <div class="text-nowrap text-xs">{{ dispute.order.total_profit }} {{dispute.order.base_currency.toUpperCase()}}</div>
                                </td>
                                <td>
                                    {{ dispute.user.email }}
                                </td>
                                <td>
                                    <DisputeStatus :status="dispute.status"></DisputeStatus>
                                </td>
                                <td>
                                    <DateTime :data="dispute.created_at"></DateTime>
                                </td>
                                <td class=" text-right">
                                    <ShowAction @click="modalStore.openDisputeModal({dispute})"></ShowAction>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </MainTableSection>

        <DisputeModal />
        <ConfirmModal />
    </div>
</template>
