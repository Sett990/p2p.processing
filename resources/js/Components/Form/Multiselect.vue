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
    },
    enableSearch: {
        type: Boolean,
        default: false
    },
    placeholder: {
        type: String,
        default: 'Выберите опции'
    },
    singleSelect: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:modelValue']);

const selectedOptions = ref(props.modelValue);
const isOpen = ref(false);
const searchQuery = ref('');

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
    if (!isOpen.value) {
        searchQuery.value = '';
    }
};

const selectOption = (option) => {
    if (props.singleSelect) {
        selectedOptions.value = [option[props.valueKey]];
    } else {
        if (selectedOptions.value.includes(option[props.valueKey])) {
            selectedOptions.value = selectedOptions.value.filter(item => item !== option[props.valueKey]);
        } else {
            selectedOptions.value.push(option[props.valueKey]);
        }
    }
    emit('update:modelValue', selectedOptions.value);
};

const isSelected = (option) => selectedOptions.value.includes(option[props.valueKey]);

const selectedLabels = computed(() =>
    props.options.filter(opt => selectedOptions.value.includes(opt[props.valueKey])).map(opt => opt[props.labelKey]).join(', ')
);

const filteredOptions = computed(() => {
    if (!searchQuery.value) return props.options;
    const query = searchQuery.value.toLowerCase();
    return props.options.filter(option => 
        option[props.labelKey].toLowerCase().includes(query)
    );
});

const onSearchInput = (event) => {
    event.stopPropagation();
};
</script>

<template>
    <div class="relative w-full">
        <div
            class="flex items-center justify-between border rounded-xl p-2 cursor-pointer border-gray-300 bg-white text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
            @click="toggleDropdown"
        >
            <span>{{ selectedLabels || placeholder }}</span>
            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
        <div v-if="isOpen" class="absolute z-10 w-full border rounded-xl mt-1 shadow-lg bg-white border-gray-300 text-gray-700 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
            <div v-if="enableSearch" class="p-2 border-b border-gray-200 dark:border-gray-600">
                <input
                    type="text"
                    v-model="searchQuery"
                    class="w-full px-3 py-1 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    placeholder="Поиск..."
                    @click="onSearchInput"
                />
            </div>
            <ul class="max-h-60 overflow-y-auto">
                <li v-for="option in filteredOptions" :key="option[valueKey]" @click="selectOption(option)"
                    class="px-4 py-2 cursor-pointer flex items-center hover:bg-gray-100 dark:hover:bg-gray-700">
                    <input :type="singleSelect ? 'radio' : 'checkbox'" class="mr-2" :checked="isSelected(option)" :name="singleSelect ? 'multiselect-radio' : ''" />
                    {{ option[labelKey] }}
                </li>
                <li v-if="enableSearch && filteredOptions.length === 0" class="px-4 py-2 text-gray-500 dark:text-gray-400">
                    Ничего не найдено
                </li>
            </ul>
        </div>
    </div>
</template>
