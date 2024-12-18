<script setup>
import {computed, getCurrentInstance, ref} from "vue"


const props = defineProps({
    label: {
        type: String,
    },
    items: {
        type: Array,
    },
    value: {
        type: String,
    },
    name: {
        type: String,
    },
});

const model = defineModel({
    required: true,
});

const selectedTimes = computed(() => {
    return props.items.filter((item) => {
        return model.value.includes(item[props.value]);
    })
})

const change = (item, event) => {
    if (event.target.checked) {
        model.value.push(item);
    } else {
        const index = model.value.indexOf(item);
        if (index > -1) {
            model.value.splice(index, 1);
        }
    }
};

const {uid} = getCurrentInstance()
</script>

<template>
    <div class="sm:flex">
        <div>
            <button :id="'dropdownButton'+uid" :data-dropdown-toggle="'dropdown'+uid" class="text-nowrap text-blue-700 hover:text-white border border-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-xl  text-sm px-4 py-2 text-center me-2 dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-500 dark:focus:ring-blue-800 inline-flex items-center" type="button">
                {{ label }}
                <svg class="w-2.5 h-2.5 ms-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                </svg>
            </button>
            <!-- Dropdown menu -->
            <div :id="'dropdown'+uid" class="z-10 hidden w-48 bg-white rounded-xl  shadow dark:bg-gray-700">
                <ul class="p-3 space-y-1 text-sm text-gray-700 dark:text-gray-200" :aria-labelledby="'dropdownButton'+uid">
                    <li v-for="(item, index) in items">
                        <div class="flex items-center p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600">
                            <input
                                :id="'checkbox-'+uid+'-'+index"
                                type="checkbox"
                                :value="item[value]"
                                :checked="selectedTimes.some(e => e[value] === item[value])"
                                @change="change(item[value], $event)"
                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500"
                            >
                            <label :for="'checkbox-'+uid+'-'+index" class="w-full ms-2 text-sm font-medium text-gray-900 rounded dark:text-gray-300">
                                {{ item[name] }}
                            </label>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <div class="sm:ml-3 mt-3 sm:mt-0">
            <span v-for="item in selectedTimes" class="inline-block text-gray-900 text-xs font-medium me-2 px-2.5 py-0.5 my-1 rounded bg-gray-100 border-gray-300 dark:bg-gray-700 dark:text-gray-400 border dark:border-gray-500 text-nowrap">
                {{item[name]}}
            </span>
        </div>
    </div>
</template>

<style scoped>

</style>
