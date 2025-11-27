<script setup>
import ApiResponse from './ApiResponse.vue';

const props = defineProps({
    executeRequest: {
        type: Function,
        required: true
    },
    loading: {
        type: Boolean,
        required: true
    },
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

const handleRequest = (method, endpoint) => {
    props.executeRequest(method, endpoint);
};
</script>

<template>
    <div class="space-y-6">
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="space-y-4">
                        <h3 class="card-title mb-4">Получить доступные валюты</h3>
                        <p class="text-sm text-base-content/70 mb-4">GET /api/currencies</p>

                        <div class="card-actions justify-end">
                            <button @click="handleRequest('GET', 'currencies')"
                                    class="btn btn-primary" :disabled="loading">
                                <span v-if="loading" class="loading loading-spinner loading-sm"></span>
                                Отправить запрос
                            </button>
                        </div>
                    </div>
                    <div class="lg:col-span-2 lg:border-l lg:pl-6 lg:border-base-300">
                        <ApiResponse
                            :response="response"
                            :response-error="responseError"
                            @clear="$emit('clear')"
                        />
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="space-y-4">
                        <h3 class="card-title mb-4">Получить доступные платежные методы</h3>
                        <p class="text-sm text-base-content/70 mb-4">GET /api/payment-gateways</p>

                        <div class="card-actions justify-end">
                            <button @click="handleRequest('GET', 'payment-gateways')"
                                    class="btn btn-primary" :disabled="loading">
                                <span v-if="loading" class="loading loading-spinner loading-sm"></span>
                                Отправить запрос
                            </button>
                        </div>
                    </div>
                    <div class="lg:col-span-2 lg:border-l lg:pl-6 lg:border-base-300">
                        <ApiResponse
                            :response="response"
                            :response-error="responseError"
                            @clear="$emit('clear')"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

