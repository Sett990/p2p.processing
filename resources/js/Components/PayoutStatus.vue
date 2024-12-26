<script setup>
defineProps({
    status: {
        type: String,
    },
    status_name: {
        type: String,
    },
});
</script>

<template>
    <div class="flex items-center text-nowrap">
        <template v-if="status === 'success'">
            <div class="h-2.5 w-2.5 rounded-full bg-green-400 dark:bg-green-500 me-2"></div> {{ status_name }}
        </template>
        <template v-if="status === 'fail'">
            <div class="h-2.5 w-2.5 rounded-full bg-red-500 dark:bg-red-500 me-2"></div> {{ status_name }}
        </template>
        <template v-if="status === 'pending'">
            <div class="h-2.5 w-2.5 rounded-full bg-yellow-300 dark:bg-yellow-500 me-2"></div> {{ status_name }}
        </template>
    </div>
</template>
