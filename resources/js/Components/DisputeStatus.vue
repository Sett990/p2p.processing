<script setup>
defineProps({
    status: {
        type: String,
    },
});
</script>

<template>
    <div class="flex items-center text-nowrap text-base-content">
        <template v-if="status === 'accepted'">
            <div aria-label="success" class="status status-success mr-2"></div> Принят
        </template>
        <template v-if="status === 'canceled'">
            <div aria-label="success" class="status status-error mr-2"></div> Отклонен
        </template>
        <template v-if="status === 'pending'">
            <div aria-label="success" class="status status-warning mr-2"></div> Ожидает
        </template>
    </div>
</template>
