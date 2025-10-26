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
    <div class="tooltip tooltip-top" :data-tip="copied ? 'Скопировано!' : 'Скопировать'">
        <a
            href="#"
            @click.prevent="copy(text)"
            class="hover:bg-gray-100 dark:hover:bg-gray-700 p-1 rounded text-nowrap cursor-pointer"
        >
            {{ text.substring(0, 4) + '...' +  text.substring(text.length - 4) }}
        </a>
    </div>
</template>

<style scoped>

</style>
