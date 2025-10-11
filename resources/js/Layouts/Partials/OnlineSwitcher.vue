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
    <div>
        <div class="inline-flex items-center cursor-pointer">
            <input type="checkbox" v-model="is_online" @change="submit" class="toggle toggle-success" />

            <span v-if="is_online" class="tooltip" data-tip="Трафик включен">
                <span class="ml-2 text-xs font-medium text-success">Онлайн</span>
            </span>
            <span v-else class="tooltip" data-tip="Трафик выключен">
                <span class="ml-2 text-xs font-medium">Офлайн</span>
            </span>
        </div>
    </div>
</template>

<style scoped>

</style>
