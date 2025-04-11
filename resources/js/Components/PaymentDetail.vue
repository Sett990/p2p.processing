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
    name: {
        type: String,
        default: null
    },
    copyable: {
        type: Boolean,
        default: true
    },
    short: {
        type: Boolean,
        default: false
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
    <div>
        <template v-if="copyable">
            <a
                href="#"
                :data-tooltip-target="'tooltip-payment-detail'+$.uid"
                @click.prevent="copy(detail)"
                class="hover:bg-gray-100 dark:hover:bg-gray-700 p-1 rounded text-nowrap"
                :class="name ? 'text-gray-900 dark:text-gray-200' : ''"
            >
                <template v-if="type === 'card'">
                    <template v-if="short">
                        {{ detail.substring(0, 4) }}**{{ detail.substring(detail.length - 4) }}
                    </template>
                    <template v-else>
                        {{ detail.match(/.{1,4}/g).join(' ') }}
                    </template>
                </template>
                <template v-if="type === 'phone'">
                    <template v-if="short">
                        {{ phone.substring(0,2) }} **** {{ phone.substring(phone.length - 5) }}
                    </template>
                    <template v-else>
                        {{ phone }}
                    </template>
                </template>
                <template v-if="type === 'account_number'">
                    <template v-if="short">
                        ***{{ detail.substring(detail.length - 6) }}
                    </template>
                    <template v-else>
                        {{ detail }}
                    </template>
                </template>
            </a>
            <div :id="'tooltip-payment-detail'+$.uid" role="tooltip"
                 class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-xl  shadow-sm opacity-0 tooltip dark:bg-gray-700">
                <span v-if="!copied">Скопировать</span>
                <span v-else>Скопировано!</span>
                <div class="tooltip-arrow" data-popper-arrow></div>
            </div>
            <div v-if="name" class="text-nowrap text-xs ml-1">
                {{ name }}
            </div>
        </template>
        <template v-else>
            <span class="text-nowrap" :class="name ? 'text-gray-900 dark:text-gray-200' : ''">
                <template v-if="type === 'card'">
                    <template v-if="short">
                        {{ detail.substring(0, 4) }}**{{ detail.substring(detail.length - 4) }}
                    </template>
                    <template v-else>
                        {{ detail.match(/.{1,4}/g).join(' ') }}
                    </template>
                </template>
                <template v-if="type === 'phone'">
                    <template v-if="short">
                        **** {{ phone.substring(phone.length - 4) }}
                    </template>
                    <template v-else>
                        {{ phone }}
                    </template>
                </template>
                <template v-if="type === 'account_number'">
                    <template v-if="short">
                        ***{{ detail.substring(detail.length - 6) }}
                    </template>
                    <template v-else>
                        {{ detail }}
                    </template>
                </template>
            </span>
            <div v-if="name" class="text-nowrap text-xs">
                {{ name }}
            </div>
        </template>
    </div>
</template>

<style scoped>

</style>
