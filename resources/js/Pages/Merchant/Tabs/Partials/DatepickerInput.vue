<script setup>
import {computed} from "vue";

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: 'Выберите дату',
    },
    error: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue', 'change']);

const inputClasses = computed(() => [
    'input',
    'input-bordered',
    'w-full',
    props.error ? 'input-error' : ''
]);

const onInput = (e) => {
    emit('update:modelValue', e.target.value);
    emit('change', e.target.value);
};
</script>

<template>
    <div class="w-full">
        <input
            type="date"
            :class="inputClasses"
            :placeholder="placeholder"
            :value="modelValue"
            @input="onInput"
        />
    </div>
    
</template>

<style scoped>
</style>
