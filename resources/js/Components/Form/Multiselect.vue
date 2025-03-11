<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    options: {
        type: Array,
        required: true
    },
    modelValue: {
        type: Array,
        default: () => []
    },
    labelKey: {
        type: String,
        default: 'label'
    },
    valueKey: {
        type: String,
        default: 'value'
    }
});

const emit = defineEmits(['update:modelValue']);

const selectedOptions = ref(props.modelValue);
const isOpen = ref(false);

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
};

const selectOption = (option) => {
    if (selectedOptions.value.includes(option[props.valueKey])) {
        selectedOptions.value = selectedOptions.value.filter(item => item !== option[props.valueKey]);
    } else {
        selectedOptions.value.push(option[props.valueKey]);
    }
    emit('update:modelValue', selectedOptions.value);
};

const isSelected = (option) => selectedOptions.value.includes(option[props.valueKey]);

const selectedLabels = computed(() =>
    props.options.filter(opt => selectedOptions.value.includes(opt[props.valueKey])).map(opt => opt[props.labelKey]).join(', ')
);
</script>

<template>
    <div class="relative w-full">
        <div
            class="flex items-center justify-between border rounded-xl p-2 cursor-pointer border-gray-300 bg-white text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
            @click="toggleDropdown"
        >
            <span>{{ selectedLabels || 'Выберите опции' }}</span>
            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
        <div v-if="isOpen" class="absolute z-10 w-full border rounded-xl mt-1 shadow-lg bg-white border-gray-300 text-gray-700 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
            <ul class="max-h-60 overflow-y-auto">
                <li v-for="option in options" :key="option[valueKey]" @click="selectOption(option)"
                    class="px-4 py-2 cursor-pointer flex items-center hover:bg-gray-100 dark:hover:bg-gray-700">
                    <input type="checkbox" class="mr-2 rounded-md" :checked="isSelected(option)" />
                    {{ option[labelKey] }}
                </li>
            </ul>
        </div>
    </div>
</template>
