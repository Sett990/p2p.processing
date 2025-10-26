<script setup>
import { useClipboard } from '@vueuse/core'
import {computed} from "vue";

const props = defineProps({
    uuid: {
        type: String,
    },
    copyable: {
        type: Boolean,
        default: true
    }
});

const uuidShort = computed(() => {
    var items = props.uuid.split('-');
    if (! items.length) {
        return 'Пусто';
    }
    return items[items.length - 1];
});

const { copy, copied } = useClipboard()
</script>

<template>
    <div>
        <template v-if="! copyable">
            <span class="text-nowrap text-base-content">
                {{uuidShort}}
            </span>
        </template>
        <template v-else>
            <div class="tooltip tooltip-top" :data-tip="copied ? 'Скопировано!' : 'Скопировать'">
                <button
                    type="button"
                    @click.prevent.stop="copy(uuid)"
                    class="btn btn-ghost btn-xs text-nowrap text-base-content"
                >
                    {{ uuidShort }}
                </button>
            </div>
        </template>
    </div>
</template>

<style scoped>

</style>
