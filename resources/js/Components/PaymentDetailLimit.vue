<script setup>
import {computed} from "vue";

const props = defineProps({
    current_daily_limit: {
        type: String,
    },
    daily_limit: {
        type: String,
    },
});

const percent = computed(() => {
    return props.current_daily_limit / (props.daily_limit/100);
});

const color = computed(() => {
    if (percent.value < 40) {
        return 'bg-green-400';
    } else if (percent.value > 40 && percent.value < 80) {
        return 'bg-yellow-400';
    } else if (percent.value > 80) {
        return 'bg-red-600';
    }
})

</script>

<template>
    <div class="flex justify-end mb-1">
        <span class="text-xs font-semibold text-gray-900 dark:text-white text-nowrap">
            {{ current_daily_limit }} / {{ daily_limit }}
        </span>
    </div>
    <div class="w-full bg-gray-200 rounded-full h-1.5 dark:bg-gray-700">
        <div class="h-1.5 rounded-full" :class="color" :style="'width: '+ percent + '%'"></div>
    </div>
</template>

<style scoped>

</style>
