<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';
import UpdateAvatarForm from "@/Pages/Profile/Partials/UpdateAvatarForm.vue";

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <Head title="Профиль" />

    <div>
        <div>
            <div class="max-w-7xl grid grid-cols-1 xl:grid-cols-2 gap-6">
                <div class="p-5 sm:p-8 bg-white dark:bg-gray-800 shadow rounded-plate lg:w-fit w-full">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="w-full lg:max-w-lg"
                    />
                </div>

                <div class="p-5 sm:p-8 bg-white dark:bg-gray-800 shadow rounded-plate lg:w-fit w-full">
                    <UpdatePasswordForm class="w-full lg:max-w-lg" />
                </div>
                <div class="p-5 sm:p-8 bg-white dark:bg-gray-800 shadow rounded-plate lg:w-fit w-full">
                    <UpdateAvatarForm class="w-full lg:max-w-lg"/>
                </div>
            </div>
        </div>
    </div>
</template>
