<script setup>
const model = defineModel({
    required: true,
});
defineProps({
    error: {
        type: Boolean,
        default: false,
    },
    items: {
        type: Object,
    },
    value: {
        type: String,
    },
    name: {
        type: String,
    },
    default_title: {
        type: String,
    },
    default_value: {
        type: String,
        default: "0",
    },
});

</script>

<template>
    <select
        :class="!error
        ? 'bg-white border border-gray-200 text-gray-900 text-sm rounded-xl  focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-900 dark:border-gray-700 dark:placeholder-gray-400 dark:text-gray-300 dark:focus:ring-blue-500 dark:focus:border-blue-500'
        : 'bg-red-50 border border-red-500 text-red-700 text-sm rounded-xl  focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-gray-900 dark:text-red-500 dark:placeholder-red-500 dark:border-red-500'"
        required
        v-model="model"
    >
        <option :value="default_value" selected>{{ default_title }}</option>
        <option v-for="item in items" :value="item[value]">{{ item[name] }}</option>
    </select>
</template>

<style scoped>

</style>
