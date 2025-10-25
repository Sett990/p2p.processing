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
    <div v-show="stage !== 'select_gateway'" class="sm:mx-0 mx-2 rounded-plate">
        <div class="flex justify-between mt-3 w-full px-6 py-5 text-sm text-base-content bg-base-100 rounded-plate shadow">
            <div>
                <div class="text-2xl font-semibold">{{ data.amount_formated }}{{ data.currency_symbol }}</div>
                <div class="text-base-content/60">Сумма для оплаты</div>
            </div>
            <div v-show="stage === 'payment'">
                <div class="text-2xl font-semibold">
                    <slot name="clock"></slot>
                </div>
                <div class="text-base-content/60">Время на оплату</div>
            </div>
        </div>

        <div>
            <div class="w-full p-2 text-center text-sm text-base-content/80">
                <span class="text-primary">ID:</span> {{ data.uuid }}
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
