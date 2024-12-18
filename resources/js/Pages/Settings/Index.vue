<script setup>
import {Head} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimeTimeBonus from "@/Pages/Settings/Partials/PrimeTimeBonus.vue";
import SupportLink from "@/Pages/Settings/Partials/SupportLink.vue";

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <Head title="Настройки системы" />

    <div>
        <div>
            <div class="mx-auto space-y-6">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl text-gray-900 dark:text-white sm:text-4xl">Настройки системы</h2>
                </div>

                <div class="p-5 sm:p-8 bg-white dark:bg-gray-800 shadow rounded-plate ">
                    <PrimeTimeBonus class="max-w-xl"></PrimeTimeBonus>
                </div>
                <div class="p-5 sm:p-8 bg-white dark:bg-gray-800 shadow rounded-plate ">
                    <SupportLink class="max-w-xl"></SupportLink>
                </div>
            </div>
        </div>
    </div>
</template>
