<script setup>
import {computed} from "vue";

const model = defineModel({
    required: true,
});

const props = defineProps({
    title: {
        type: String,
        default: 'Нажмите, чтобы загрузить файл',
    },
    description: {
        type: String,
        default: null,
    },
});

const fileName = computed(() => {
    if (! model.value?.name) {
        return null;
    }

    var split = model.value.name.split('.');
    var filename = split[0];
    var extension = split[1];

    if (filename.length > 10) {
        filename = filename.substring(0, 5) + '...' + filename.substring(5, 10);
    }

    return filename + '.' + extension;
})
</script>

<template>
    <div>
        <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-20 border-2 border-gray-300 border-dashed rounded-xl  cursor-pointer bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600">
            <div class="flex items-center justify-center pt-6 pb-6">
                <svg class="w-10 h-10 mr-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                </svg>
                <div>
                    <p v-if="!model" class="text-sm text-gray-500 dark:text-gray-400">{{ title }}</p>
                    <p v-if="!model && description" class="text-xs text-gray-400 dark:text-gray-500 text-center">{{ description }}</p>
                    <p v-else class="text-sm text-gray-500 dark:text-gray-400">{{ fileName }}</p>
                </div>
            </div>
            <input id="dropzone-file" type="file" @input="model = $event.target.files[0]" class="hidden" />
        </label>
    </div>
</template>

<style scoped>

</style>
