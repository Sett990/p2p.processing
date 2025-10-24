<script setup>
import DateTime from "@/Components/DateTime.vue";
import {usePage} from "@inertiajs/vue3";
import {ref} from "vue";
import Pagination from "@/Components/Pagination/Pagination.vue";
import DisplayUUID from "@/Components/DisplayUUID.vue";

const emit = defineEmits(['openPage']);

const props = defineProps({
    tab: {},
});

const orders = usePage().props.orders;
const merchant = usePage().props.merchant;

const openPage = (page) => {
    emit("openPage", page);
};

const currentPage = ref(orders?.meta?.current_page)
</script>

<template>
    <div>
        <h2 class="text-xs text-base-content/60 mb-3">Здесь отображаются только оплаченные сделки</h2>

        <div class="overflow-x-auto card bg-base-100 shadow mb-5">
            <table class="table table-md">
                <thead class="text-xs uppercase bg-base-300">
                <tr>
                    <th scope="col">
                        UUID
                    </th>
                    <th scope="col">
                        Сумма
                    </th>
                    <th scope="col">
                        Прибыль
                    </th>
                    <th scope="col">
                        Комиссия
                    </th>
                    <th scope="col">
                        Создан
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="order in orders.data" class="hover">
                    <th scope="row" class="font-medium whitespace-nowrap">
                        <DisplayUUID :uuid="order.uuid"/>
                    </th>
                    <td>
                        <div class="text-nowrap">{{ order.amount }} {{ order.currency.toUpperCase() }}</div>
                        <div class="text-nowrap text-xs text-base-content/60">{{ order.total_profit }} {{ order.base_currency.toUpperCase() }}</div>
                    </td>
                    <td>
                        <div class="text-nowrap">{{ order.merchant_profit }} {{ order.base_currency.toUpperCase() }}</div>
                    </td>
                    <td>
                        {{ order.service_commission_amount_total }} {{ order.base_currency.toUpperCase() }}
                    </td>
                    <td>
                        <DateTime class="justify-center" :data="order.created_at"/>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <Pagination
            v-model="currentPage"
            :total-items="orders.meta.total"
            previous-label="Назад" next-label="Вперед"
            @page-changed="openPage"
            :per-page="orders.meta.per_page"
        ></Pagination>
    </div>
</template>

<style scoped>

</style>
