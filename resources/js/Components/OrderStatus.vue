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
            <span class="badge badge-success badge-xs mr-2"></span> {{ status_name }}
        </template>
        <template v-if="status === 'fail'">
            <span class="badge badge-error badge-xs mr-2"></span> {{ status_name }}
        </template>
        <template v-if="status === 'pending'">
            <span class="badge badge-warning badge-xs mr-2"></span> {{ status_name }}
        </template>
    </div>
</template>
