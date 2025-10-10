<script setup>
import Select from "@/Components/Select.vue";

import {useViewStore} from "@/store/view.js";
import {watch} from "vue";
import {router} from "@inertiajs/vue3";

const viewStore = useViewStore();


const visitDefaultPage = () => {
    if (viewStore.viewMode === 'admin') {
        router.visit(route('admin.main.index'), {
            preserveScroll: true
        })
    }
    if (viewStore.viewMode === 'trader') {
        router.visit(route('trader.main.index'), {
            preserveScroll: true
        })
    }
    if (viewStore.viewMode === 'merchant') {
        router.visit(route('merchant.main.index'), {
            preserveScroll: true
        })
    }
    if (viewStore.viewMode === 'leader') {
        router.visit(route('leader.main.index'), {
            preserveScroll: true
        })
    }
    if (viewStore.viewMode === 'support') {
        router.visit(route('support.users.index'), {
            preserveScroll: true
        })
    }
    if (viewStore.viewMode === 'merchant-support') {
        router.visit(route('merchant-support.payments.index'), {
            preserveScroll: true
        })
    }
}
</script>

<template>
    <div>
        <p class="text-sm text-base-content mb-1">Посмотреть сайт как</p>
        <select
            class="select select-bordered w-full"
            required
            v-model="viewStore.viewMode"
            @change="visitDefaultPage"
        >
            <option value="admin" selected>Админ</option>
            <option value="trader" selected>Трейдер</option>
            <option value="merchant" selected>Мерчант</option>
            <option value="leader" selected>Тимлидер</option>
            <option value="support" selected>Саппорт</option>
            <option value="merchant-support" selected>Мерчант Саппорт</option>
        </select>
    </div>
</template>

<style scoped>

</style>
