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
        <div class="relative text-nowrap">
            <span
                class="text-xs font-semibold"
                :class="{
                    'text-green-600 dark:text-green-400': current_daily_limit / daily_limit < 0.4,
                    'text-yellow-600 dark:text-yellow-400': current_daily_limit / daily_limit >= 0.4 && current_daily_limit / daily_limit < 0.8,
                    'text-red-600 dark:text-red-400': current_daily_limit / daily_limit >= 0.8
                }"
            >
                {{current_daily_limit}}
            </span>
            <span class="mx-1 text-gray-500 dark:text-gray-400">из</span>
            <span class="text-xs font-semibold text-gray-900 dark:text-white">
                {{daily_limit}}
            </span>
        </div>
    </div>
    <div class="w-full bg-gray-200 rounded-full h-1.5 dark:bg-gray-700">
        <div class="h-1.5 rounded-full" :class="color" :style="'width: '+ percent + '%'"></div>
    </div>
</template>

<style scoped>

</style>
