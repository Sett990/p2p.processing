<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';
import Code2FA from "@/Pages/Auth/Components/Code2FA.vue";

const form = useForm({
    code_2fa: ''
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Введите 2FA Код" />

        <form @submit.prevent="submit">
            <div class="my-6">
                <h2 class="font-semibold text-gray-900 dark:text-gray-200 text-center mb-3">Введите 2FA Код</h2>
                <Code2FA @codeInput="form.code_2fa = $event"/>
            </div>

            <div class="flex items-center justify-end mt-4">
                <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Войти
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
