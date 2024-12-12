<script setup>
import {computed} from "vue";

const props = defineProps({
    text: {
        type: String,
    },
    tooltip: {
        type: String,
        default: ''
    },
    popover: {
        type: String,
        default: ''
    },
    trim: {
        type: Number,
        default: 0
    },
});

const textFormated = computed(() => {
    var text = props.text;
    var textLength = text.length

    if (props.trim > 0 && props.trim < textLength) {
        var startPartLength = Math.round(props.trim / 2 - 2);
        var endPartLength = Math.round(props.trim / 2 - 1);

        text = text.substring(0, startPartLength) + '...' + text.substring(textLength - endPartLength, textLength);
    }

    return text;
})
</script>

<template>
    <span>
        <template v-if="tooltip.length">
            <span :data-tooltip-target="`tooltip-${$.uid}`">
                {{ textFormated }}
            </span>
            <div :id="`tooltip-${$.uid}`" role="tooltip" class="absolute break-all z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-xl shadow-sm opacity-0 tooltip dark:bg-gray-700">
                {{ tooltip }}
            </div>
        </template>
        <template v-else-if="popover.length">
            <span :data-popover-target="`popover-${$.uid}`">
                {{ textFormated }}
            </span>
            <div data-popover :id="`popover-${$.uid}`" role="tooltip" class="absolute break-all z-50 invisible inline-block w-64 text-sm text-gray-500 transition-opacity duration-300 bg-white border border-gray-200 rounded-xl shadow-sm opacity-0 dark:text-gray-400 dark:border-gray-600 dark:bg-gray-800">
                <div class="px-3 py-2 w-72">
                    <p>{{ popover }}</p>
                </div>
                <div data-popper-arrow></div>
            </div>
        </template>
        <template v-else>
            {{ textFormated }}
        </template>
    </span>
</template>

<style scoped>

</style>
