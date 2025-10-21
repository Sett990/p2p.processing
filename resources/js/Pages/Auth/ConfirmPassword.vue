<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Подтвердите пароль" />

        <div class="alert alert-info text-sm mb-4">
            Это защищённая область приложения. Пожалуйста, подтвердите Ваш пароль, прежде чем продолжить.
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div class="form-control">
                <InputLabel for="password" value="Пароль" class="label">
                    <span class="label-text">Пароль</span>
                </InputLabel>
                <TextInput
                    id="password"
                    type="password"
                    class="input input-bordered w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    autofocus
                />
                <InputError class="mt-2 text-error" :message="form.errors.password" />
            </div>

            <div class="flex justify-end">
                <PrimaryButton class="btn btn-primary ms-4" :class="{ 'btn-disabled opacity-50': form.processing }" :disabled="form.processing">
                    Подтвердить
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
