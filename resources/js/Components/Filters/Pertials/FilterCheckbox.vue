<script setup>
const props = defineProps({
    title: {
        type: String,
    },
});

const model = defineModel({
    required: true,
});
</script>

<template>
    <div class="flex items-center">
        <input
            v-model="model"
            :id="`checkbox-${$.uid}`"
            type="checkbox"
            value=""
            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded-md focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
        >
        <label
            :for="`checkbox-${$.uid}`"
            class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300"
        >{{ title }}</label>
    </div>
</template>

<style scoped>

</style>
