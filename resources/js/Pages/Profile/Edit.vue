<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import { Head } from '@inertiajs/vue3';
import UpdateAvatarForm from "@/Pages/Profile/Partials/UpdateAvatarForm.vue";
import Update2faForm from "@/Pages/Profile/Partials/Update2faForm.vue";
import LoginHistoryForm from "@/Pages/Profile/Partials/LoginHistoryForm.vue";

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    auth2fa: {
        type: Object,
        default: () => ({}),
    },
    loginHistory: {
        type: Array,
        default: () => [],
    },
});

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <Head title="Профиль" />

    <div class="mx-auto space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-xl text-gray-900 dark:text-white sm:text-4xl">Профиль</h2>
            <slot name="button"></slot>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <div class="card bg-base-100 card-border border-base-300 shadow-md">
                <div class="card-body">
                    <UpdatePasswordForm class="w-full"/>
                </div>
            </div>

            <div class="card bg-base-100 card-border border-base-300 shadow-md">
                <div class="card-body">
                    <UpdateAvatarForm class="w-full"/>
                </div>
            </div>

            <div class="card bg-base-100 card-border border-base-300 shadow-md xl:col-span-2">
                <div class="card-body">
                    <Update2faForm class="w-full"/>
                </div>
            </div>

            <div class="card bg-base-100 card-border border-base-300 shadow-md xl:col-span-2">
                <div class="card-body">
                    <LoginHistoryForm :login-history="loginHistory" class="w-full"/>
                </div>
            </div>
        </div>
    </div>
</template>
