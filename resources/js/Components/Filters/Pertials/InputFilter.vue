<script setup>
const props = defineProps({
    placeholder: {
        type: String,
    },
});

const model = defineModel({
    required: true,
});
</script>

<template>
    <div class="w-48">
        <input
            type="text"
            :id="$.uid"
            v-model="model"
            :placeholder="placeholder"
            class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-2.5 h-[38px] dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
        >
    </div>
</template>

<style scoped>

</style>
