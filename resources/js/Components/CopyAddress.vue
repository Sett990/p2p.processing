<script setup>
import { useClipboard } from '@vueuse/core'

const props = defineProps({
    text: {
        type: String,
    },
});
const { copy, copied } = useClipboard()
</script>

<template>
    <div>
        <a
            href="#"
            :data-tooltip-target="'tooltip'+$.uid"
            @click.prevent="copy(text)"
            class="hover:bg-gray-100 dark:hover:bg-gray-700 p-1 rounded text-nowrap"
        >
            {{ text.substring(0, 4) + '...' +  text.substring(text.length - 4) }}
        </a>
        <div :id="'tooltip'+$.uid" role="tooltip"
             class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
            <span v-if="!copied">Скопировать</span>
            <span v-else>Скопировано!</span>
            <div class="tooltip-arrow" data-popper-arrow></div>
        </div>
    </div>
</template>

<style scoped>

</style>
