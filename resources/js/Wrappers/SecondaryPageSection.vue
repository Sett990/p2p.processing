<script setup>
import {router} from "@inertiajs/vue3";
import GoBackButton from "@/Components/GoBackButton.vue";

const props = defineProps({
    backLink: {
        type: String,
        default: null,
    },
    title: {
        type: String,
    },
    description: {
        type: String,
        default: null
    }
});
</script>

<template>
    <div class="mx-auto space-y-4">
        <div v-if="backLink" class="mb-3">
            <GoBackButton @click="router.visit(backLink)"/>
        </div>
        <div class="p-5 sm:p-8 bg-white dark:bg-gray-800 shadow rounded-plate">
            <section class="max-w-xl">
                <header>
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ title }}
                    </h2>

                    <p v-if="description" class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ description }}
                    </p>
                </header>

                <div>
                    <slot></slot>
                </div>
            </section>
        </div>
    </div>
</template>

<style scoped>

</style>
