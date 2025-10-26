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
    <div class="flex items-center text-nowrap text-base-content">
        <template v-if="status === 'success'">
            <div aria-label="success" class="status status-success mr-2"></div> {{ status_name }}
        </template>
        <template v-if="status === 'fail'">
            <div aria-label="success" class="status status-error mr-2"></div> {{ status_name }}
        </template>
        <template v-if="status === 'pending'">
            <div aria-label="success" class="status status-warning mr-2"></div> {{ status_name }}
        </template>
    </div>
</template>
