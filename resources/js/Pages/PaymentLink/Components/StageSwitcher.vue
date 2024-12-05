<script setup>
const props = defineProps({
    stage: {
        type: String,
    },
});

</script>

<template>
    <div class="text-xs text-black dark:text-white flex gap-2 cursor-pointer">
        <div @click="stage = 'select_gateway'" :class="{'text-blue-500' : stage === 'select_gateway'}">select_gateway</div>
        <div @click="stage = 'payment'" :class="{'text-blue-500' : stage === 'payment'}">payment</div>
        <div @click="stage = 'success'" :class="{'text-blue-500' : stage === 'success'}">success</div>
        <div @click="stage = 'fail'" :class="{'text-blue-500' : stage === 'fail'}">fail</div>
        <div @click="stage = 'dispute_review'" :class="{'text-blue-500' : stage === 'dispute_review'}">dispute_review</div>
        <div @click="stage = 'dispute_canceled'" :class="{'text-blue-500' : stage === 'dispute_canceled'}">dispute_canceled</div>
    </div>
</template>

<style scoped>

</style>
