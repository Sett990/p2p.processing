<script setup>
import {usePage} from "@inertiajs/vue3";

const statistics = usePage().props.statistics;
</script>

<template>
    <div>
        <section>
            <div class="mx-auto">
                <div class="stats shadow w-full grid xl:grid-cols-4 md:grid-cols-2 sm:grid-cols-1">
                    <div class="stat place-items-center">
                        <div class="stat-title">Доход за сегодня</div>
                        <div class="stat-value text-primary">{{ statistics.today_profit }} <span class="text-xl">{{ statistics.currency.toUpperCase() }}</span></div>
                        <div class="stat-desc"><span class="badge badge-outline badge-primary">Платежей {{ statistics.today_orders_count }}</span></div>
                    </div>
                    <div class="stat place-items-center">
                        <div class="stat-title">Доход за вчера</div>
                        <div class="stat-value">{{ statistics.yesterday_profit }} <span class="text-xl">{{ statistics.currency.toUpperCase() }}</span></div>
                        <div class="stat-desc"><span class="badge badge-outline">Платежей {{ statistics.yesterday_orders_count }}</span></div>
                    </div>
                    <div class="stat place-items-center">
                        <div class="stat-title">Доход за месяц</div>
                        <div class="stat-value">{{ statistics.month_profit }} <span class="text-xl">{{ statistics.currency.toUpperCase() }}</span></div>
                        <div class="stat-desc"><span class="badge badge-outline">Платежей {{ statistics.month_orders_count }}</span></div>
                    </div>
                    <div class="stat place-items-center">
                        <div class="stat-title">Доход за все время</div>
                        <div class="stat-value">{{ statistics.total_profit }} <span class="text-xl">{{ statistics.currency.toUpperCase() }}</span></div>
                        <div class="stat-desc"><span class="badge badge-outline">Платежей {{ statistics.total_orders_count }}</span></div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>

</style>
