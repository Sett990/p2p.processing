<script setup>
import {Head, router, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import IsActiveStatus from "@/Components/IsActiveStatus.vue";
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import AddMobileIcon from "@/Components/AddMobileIcon.vue";
import GatewayLogo from "@/Components/GatewayLogo.vue";
import InputFilter from "@/Components/Filters/Pertials/InputFilter.vue";
import FiltersPanel from "@/Components/Filters/FiltersPanel.vue";
import {ref} from "vue";
import {useModalStore} from "@/store/modal.js";
import PaymentGatewayCreateModal from "@/Modals/PaymentGateway/PaymentGatewayCreateModal.vue";
import PaymentGatewayEditModal from "@/Modals/PaymentGateway/PaymentGatewayEditModal.vue";

const modalStore = useModalStore();
const payment_gateways = ref(usePage().props.paymentGateways);

router.on('success', () => {
    payment_gateways.value = usePage().props.paymentGateways;
});

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Платежные методы" />

        <MainTableSection
            title="Платежные методы"
            :data="payment_gateways"
        >
            <template v-slot:button>
                <button
                    @click="modalStore.openPaymentGatewayCreateModal()"
                    type="button"
                    class="hidden md:block btn btn-sm btn-primary"
                >
                    Создать метод
                </button>
                <AddMobileIcon
                    @click="modalStore.openPaymentGatewayCreateModal()"
                />
            </template>
            <template v-slot:header>
                <FiltersPanel name="payment-gateways">
                    <InputFilter
                        name="search"
                        placeholder="Поиск (название или код)"
                        class="w-64"
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
                                Метод
                            </th>
                            <th scope="col" class="px-6 py-3 text-nowrap">
                                Лимиты для сделок
                            </th>
                            <th scope="col" class="px-6 py-3 text-nowrap">
                                Комиссия %
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Статус
                            </th>
                            <th scope="col" class="px-6 py-3 flex justify-center">
                                <span class="sr-only">Действия</span>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="payment_gateway in payment_gateways.data">
                            <th scope="row" class="px-6 py-3 font-medium whitespace-nowrap">
                                {{ payment_gateway.id }}
                            </th>
                            <td class="px-6 py-3">
                                <div class="flex gap-3 items-center">
                                    <GatewayLogo :img_path="payment_gateway.logo_path" class="w-10 h-10"/>
                                    <div>
                                        <div class="text-nowrap">{{ payment_gateway.name }}</div>
                                        <div class="text-nowrap">{{ payment_gateway.code }} | {{ payment_gateway.nspk_schema }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3">
                                <div class="text-nowrap">Max {{ payment_gateway.max_limit }} {{ payment_gateway.currency.toUpperCase() }}</div>
                                <div class="text-nowrap">Min {{ payment_gateway.min_limit }} {{ payment_gateway.currency.toUpperCase() }}</div>
                            </td>
                            <td class="px-6 py-3">
                                <div class="text-nowrap">{{ payment_gateway.trader_commission_rate_for_orders }}% / {{ payment_gateway.total_service_commission_rate_for_orders }}%</div>
                            </td>
                            <td class="px-6 py-3 text-nowrap">
                                <IsActiveStatus :is_active="payment_gateway.is_active"></IsActiveStatus>
                            </td>
                            <td class="px-6 py-3 text-nowrap text-right">
                                <button
                                    type="button"
                                    class="btn btn-ghost btn-xs"
                                    @click="modalStore.openPaymentGatewayEditModal({ paymentGatewayId: payment_gateway.id })"
                                >
                                    <svg class="w-[22px] h-[22px] text-success" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.779 17.779 4.36 19.918 6.5 13.5m4.279 4.279 8.364-8.643a3.027 3.027 0 0 0-2.14-5.165 3.03 3.03 0 0 0-2.14.886L6.5 13.5m4.279 4.279L6.499 13.5m2.14 2.14 6.213-6.504M12.75 7.04 17 11.28"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </MainTableSection>

        <PaymentGatewayCreateModal/>
        <PaymentGatewayEditModal/>
    </div>
</template>
