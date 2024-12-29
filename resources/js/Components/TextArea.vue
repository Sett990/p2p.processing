<script setup>
const model = defineModel({
    required: true,
});
defineProps({
    error: {
        type: Boolean,
        default: false,
    },
    rows: {
        type: Number,
        default: 4
    }
});
</script>

<template>
    <textarea
        id="message"
        v-model="model"
        :rows="rows"
        required
        :class="!error
           ? 'mt-1 block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-xl  border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500'
           :'mt-1 p-2.5 bg-red-50 border border-red-500 text-sm text-red-900 rounded-xl  focus:ring-red-500 dark:bg-gray-900 focus:border-red-500 block w-full dark:text-red-500 dark:placeholder-red-500 dark:border-red-500'"
    >
    </textarea>
</template>
