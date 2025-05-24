<script setup>
import {ref} from "vue";
import {router, useForm, usePage} from "@inertiajs/vue3";

const is_online = ref(!!usePage().props.auth.user.is_online);
const is_payout_online = ref(!!usePage().props.auth.user.is_payout_online);

router.on('success', (event) => {
    is_online.value = !!usePage().props.auth.user.is_online;
    is_payout_online.value = !!usePage().props.auth.user.is_payout_online;
})

const user = usePage().props.auth.user;
const form = useForm({});
const payoutForm = useForm({});
const submit = () => {
    form.patch(route('user.online.toggle'), {
        preserveScroll: true,
        onSuccess: (result) => {
            is_online.value = !!result.props.auth.user.is_online;
        },
    });
};
const payoutSubmit = () => {
    form.patch(route('user.payout.online.toggle'), {
        preserveScroll: true,
        onSuccess: (result) => {
            is_online.value = !!result.props.auth.user.is_online;
        },
    });
};
</script>

<template>
    <div class="space-y-2">
        <div class="flex justify-between">
            <div class="text-base text-gray-500 dark:text-gray-400">
                Сделки:
            </div>
            <div>
                <label class="inline-flex items-center cursor-pointer">
                    <input type="checkbox" v-model="is_online" class="sr-only peer" @change="submit" :disabled="form.processing">
                    <span v-if="is_online" class="me-3 text-sm font-medium text-green-500 dark:text-green-400">Онлайн</span>
                    <span v-else class="me-3 text-sm font-medium text-red-500 dark:text-red-500">Офлайн</span>
                    <div class="relative w-11 h-6 bg-red-500 rounded-full peer dark:bg-red-500 peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-green-500"></div>
                </label>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
