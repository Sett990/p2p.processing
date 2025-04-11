<script setup>
import { useClipboard } from '@vueuse/core'
import {computed} from "vue";

const props = defineProps({
    id: {
        type: String,
    },
    copyable: {
        type: Boolean,
        default: true
    }
});

const idShort = computed(() => {
    if (!props.id) {
        return 'Пусто';
    }

    if (props.id.length > 8) {
        const last = props.id.substring(props.id.length - 8);
        return `${last}`;
    }

    return props.id;
});

const { copy, copied } = useClipboard()
</script>

<template>
    <div>
        <template v-if="! copyable">
            <span class="text-nowrap">
                {{idShort}}
            </span>
        </template>
        <template v-else>
            <a
                href="#"
                :data-tooltip-target="'tooltip'+$.uid"
                @click.prevent.stop="copy(id)"
                class="hover:bg-gray-100 dark:hover:bg-gray-700 p-1 rounded text-nowrap"
            >
                {{ idShort }}
            </a>
            <div :id="'tooltip'+$.uid" role="tooltip"
                 class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-xl  shadow-sm opacity-0 tooltip dark:bg-gray-700">
                <span v-if="!copied">Скопировать</span>
                <span v-else>Скопировано!</span>
                <div class="tooltip-arrow" data-popper-arrow></div>
            </div>
        </template>
    </div>
</template>

<style scoped>

</style>
