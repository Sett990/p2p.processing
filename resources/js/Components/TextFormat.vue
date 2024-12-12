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
    trim: {
        type: Number,
        default: 0
    },
});

const textFormated = computed(() => {
    var text = props.text;
    var textLength = text.length

    if (props.trim < textLength) {
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
            <div :id="`tooltip-${$.uid}`" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                {{ tooltip }}
            </div>
        </template>
        <template v-else>
            {{ textFormated }}
        </template>
    </span>
</template>

<style scoped>

</style>
