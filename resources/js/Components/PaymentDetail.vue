<script setup>
import {computed, ref} from "vue";
import { useClipboard } from '@vueuse/core'

const props = defineProps({
    detail: {
        type: String,
    },
    type: {
        type: String,
    },
    copyable: {
        type: Boolean,
        default: true
    },
});
const { text, copy, copied, isSupported } = useClipboard()

const phone = computed(() => {
    if (props.type !== 'phone') {
        return null;
    }

    let x = props.detail.replace(/\D/g, '').match(/(\d{1})(\d{0,3})(\d{0,3})(\d{0,2})(\d{0,2})/);

    return  !x[2] ? x[1] : '+' + x[1] + ' (' + x[2] + ') ' + x[3] + '-' + x[4] + '-' + x[5];
})
</script>

<template>
    <template v-if="copyable">
        <a
            href="#"
            :data-tooltip-target="'tooltip-payment-detail'+$.uid"
            @click.prevent="copy(detail)"
            class="hover:bg-gray-100 dark:hover:bg-gray-700 p-1 rounded text-nowrap"
        >
            <template v-if="type === 'card'">
                {{ detail.match(/.{1,4}/g).join(' ') }}
            </template>
            <template v-if="type === 'phone'">
                {{ phone }}
            </template>
            <template v-if="type === 'account_number'">
                {{ detail }}
            </template>
        </a>
        <div :id="'tooltip-payment-detail'+$.uid" role="tooltip"
             class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-xl  shadow-sm opacity-0 tooltip dark:bg-gray-700">
            <span v-if="!copied">Скопировать</span>
            <span v-else>Скопировано!</span>
            <div class="tooltip-arrow" data-popper-arrow></div>
        </div>
    </template>
    <template v-else>
        <span class="text-nowrap">
            <template v-if="type === 'card'">
                {{ detail.match(/.{1,4}/g).join(' ') }}
            </template>
            <template v-if="type === 'phone'">
                {{ phone }}
            </template>
            <template v-if="type === 'account_number'">
                {{ detail }}
            </template>
        </span>
    </template>
</template>

<style scoped>

</style>
