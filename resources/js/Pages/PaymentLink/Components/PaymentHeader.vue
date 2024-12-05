<script setup>
const props = defineProps({
    stage: {
        type: String,
    },
    data: {
        type: Object,
        default: {}
    }
});
</script>

<template>
    <div v-if="stage !== 'select_gateway'" class="sm:mx-0 mx-2 bg-gray-200 dark:bg-gray-700 rounded-xl">
        <div class="flex justify-between mt-3 w-full px-6 py-5 text-sm text-gray-800 bg-white dark:bg-gray-800 rounded-xl dark:text-gray-300">
            <div>
                <div class="text-gray-900 dark:text-gray-200 text-2xl">{{ data.amount_formated }}{{ data.currency_symbol }}</div>
                <div class="text-gray-400 dark:text-gray-500">Сумма для оплаты</div>
            </div>
            <div v-show="stage === 'payment'">
                <div class="text-gray-900 dark:text-gray-200 text-2xl">
                    <slot name="clock"></slot>
                </div>
                <div class="text-gray-400 dark:text-gray-500">Время на оплату</div>
            </div>
        </div>

        <div>
            <div class="w-full p-2 text-center text-sm text-gray-800 dark:text-gray-300">
                <span class="text-blue-500 dark:text-blue-500">ID:</span> {{ data.uuid }}
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
