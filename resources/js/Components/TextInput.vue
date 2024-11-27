<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    required: true,
});
defineProps({
    error: {
        type: Boolean,
        default: false,
    },
});


const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        :class="!error
        ? 'border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm'
        : 'bg-red-50 border border-red-500 text-red-700 placeholder-red-700 rounded-lg focus:ring-red-500 dark:bg-gray-900 focus:border-red-500 block w-full dark:text-red-500 dark:placeholder-red-500 dark:border-red-500'"
        v-model="model"
        ref="input"
        type="text"
    />
</template>
