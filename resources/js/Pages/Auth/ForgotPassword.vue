<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Забыли пароль" />

        <div class="alert alert-info text-sm mb-4">
            Забыли пароль? Не беда. Просто сообщите нам свой адрес электронной почты, и мы пришлем вам ссылку для сброса пароля
            ссылку, по которой вы сможете выбрать новый.
        </div>

        <div v-if="status" class="alert alert-success text-sm mb-4">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div class="form-control">
                <InputLabel for="email" value="Почта" class="label">
                    <span class="label-text">Почта</span>
                </InputLabel>

                <TextInput
                    id="email"
                    type="email"
                    class="input input-bordered w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2 text-error" :message="form.errors.email" />
            </div>

            <div class="flex items-center justify-end">
                <PrimaryButton class="btn btn-primary" :class="{ 'btn-disabled opacity-50': form.processing }" :disabled="form.processing">
                    Отправить ссылку
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
