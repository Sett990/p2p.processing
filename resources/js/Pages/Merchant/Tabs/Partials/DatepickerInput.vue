<script setup>
import {computed, getCurrentInstance} from "vue";

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

const instance = getCurrentInstance();
const uid = instance.uid;

// Convert incoming modelValue (dd/mm/yyyy or dd.mm.yyyy) -> yyyy-mm-dd for native input
const internalValue = computed({
    get() {
        const value = (props.modelValue || '').trim();
        if (!value) return '';
        const normalized = value.replaceAll('.', '/');
        const parts = normalized.split('/');
        if (parts.length !== 3) return '';
        const [dd, mm, yyyy] = parts;
        if (!dd || !mm || !yyyy) return '';
        const d = dd.padStart(2, '0');
        const m = mm.padStart(2, '0');
        return `${yyyy}-${m}-${d}`;
    },
    set(isoDate) {
        // isoDate: yyyy-mm-dd -> emit dd/mm/yyyy
        if (!isoDate) {
            emit('update:modelValue', '');
            emit('change', '');
            return;
        }
        const [yyyy, mm, dd] = isoDate.split('-');
        const out = `${dd}/${mm}/${yyyy}`;
        emit('update:modelValue', out);
        emit('change', out);
    }
});
</script>

<template>
    <div class="relative w-full">
        <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
            <svg class="w-4 h-4 text-base-content/60" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z"/>
            </svg>
        </div>
        <input
            :id="`date-datepicker-${uid}`"
            type="date"
            :class="['input input-bordered w-full ps-10', error ? 'input-error' : '']"
            :placeholder="placeholder"
            :value="internalValue"
            @input="internalValue = $event.target.value"
        >
    </div>
</template>

<style scoped>
</style> 