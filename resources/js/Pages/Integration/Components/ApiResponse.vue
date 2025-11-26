<script setup>
const props = defineProps({
    response: {
        type: Object,
        default: null
    },
    responseError: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['clear']);

const formatJSON = (obj) => {
    return JSON.stringify(obj, null, 2);
};
</script>

<template>
    <div v-if="response || responseError" class="space-y-3">
        <div class="flex justify-between items-center">
            <h4 class="font-semibold text-sm">Результат запроса</h4>
            <button @click="$emit('clear')" class="btn btn-xs btn-ghost">Закрыть</button>
        </div>

        <div v-if="response" class="space-y-2">
            <div>
                <span class="badge badge-sm" :class="response.status < 400 ? 'badge-success' : 'badge-error'">
                    HTTP {{ response.status }}
                </span>
            </div>
            <div>
                <pre class="bg-base-200 p-3 rounded-lg overflow-x-auto text-xs max-h-96 overflow-y-auto"><code>{{ formatJSON(response.data) }}</code></pre>
            </div>
        </div>

        <div v-if="responseError" class="space-y-2">
            <div>
                <span class="badge badge-sm badge-error">Ошибка</span>
            </div>
            <div>
                <p class="text-error text-sm">{{ responseError.message }}</p>
            </div>
            <div v-if="responseError.errors && Object.keys(responseError.errors).length > 0">
                <pre class="bg-base-200 p-3 rounded-lg overflow-x-auto text-xs max-h-96 overflow-y-auto"><code>{{ formatJSON(responseError.errors) }}</code></pre>
            </div>
        </div>
    </div>
</template>

