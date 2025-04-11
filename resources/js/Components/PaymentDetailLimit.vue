<script setup>
import {computed, ref} from "vue";

const props = defineProps({
    current_daily_limit: {
        type: String,
    },
    daily_limit: {
        type: String,
    },
});

const showTooltip = ref(false);

const percent = computed(() => {
    return props.current_daily_limit / (props.daily_limit/100);
});

const color = computed(() => {
    if (percent.value < 40) {
        return 'text-green-400';
    } else if (percent.value > 40 && percent.value < 80) {
        return 'text-yellow-400';
    } else if (percent.value > 80) {
        return 'text-red-600';
    }
});

const circumference = computed(() => {
    return 2 * Math.PI * 11; // 11 - радиус круга (уменьшен с 12)
});

const dashOffset = computed(() => {
    return circumference.value - (percent.value / 100) * circumference.value;
});
</script>

<template>
    <div class="relative inline-block" 
         @mouseenter="showTooltip = true" 
         @mouseleave="showTooltip = false">
        <svg class="w-8 h-8" viewBox="0 0 28 28">
            <circle cx="14" cy="14" r="11" 
                    stroke-width="3.5" 
                    stroke="currentColor" 
                    fill="transparent" 
                    class="text-gray-200 dark:text-gray-700" />
            <circle cx="14" cy="14" r="11" 
                    stroke-width="3.5" 
                    stroke="currentColor" 
                    fill="transparent" 
                    :class="color"
                    stroke-linecap="round"
                    :stroke-dasharray="circumference"
                    :stroke-dashoffset="dashOffset"
                    transform="rotate(-90 14 14)" />
        </svg>
        
        <div v-if="showTooltip" 
             class="absolute z-10 px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-xl shadow-sm dark:bg-gray-700"
             style="bottom: 100%; left: 50%; transform: translateX(-50%); margin-bottom: 5px; white-space: nowrap;">
            {{ current_daily_limit }} / {{ daily_limit }}
            <div class="tooltip-arrow" data-popper-arrow></div>
        </div>
    </div>
</template>

<style scoped>
.tooltip-arrow {
    position: absolute;
    width: 8px;
    height: 8px;
    background: inherit;
    bottom: -4px;
    left: 50%;
    transform: translateX(-50%) rotate(45deg);
}
</style>
