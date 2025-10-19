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

    <div>
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <div class="p-5 sm:p-8 bg-white dark:bg-gray-800 shadow rounded-plate">
                <UpdatePasswordForm class="w-full"/>
            </div>
            <div class="p-5 sm:p-8 bg-white dark:bg-gray-800 shadow rounded-plate">
                <UpdateAvatarForm class="w-full"/>
            </div>
            <div class="p-5 sm:p-8 bg-white dark:bg-gray-800 shadow rounded-plate">
                <Update2faForm class="w-full"/>
            </div>
<!--            <div class="p-5 sm:p-8 bg-white dark:bg-gray-800 shadow rounded-plate col-span-1 xl:col-span-2">
                <LoginHistoryForm :login-history="loginHistory" class="w-full"/>
            </div>-->
        </div>
    </div>
</template>
