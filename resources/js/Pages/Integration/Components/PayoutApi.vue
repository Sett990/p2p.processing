<script setup>
import { ref } from 'vue';
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

// Payout формы
const payoutForm = ref({
    payout_gateway_id: '',
    external_id: `test_payout_${Date.now()}`,
    detail: '',
    detail_type: 'card',
    detail_initials: '',
    amount: '',
    payment_gateway: '',
    callback_url: ''
});

const payoutGetForm = ref({
    uuid: ''
});
</script>

<template>
    <div class="space-y-6">
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="space-y-4">
                        <h3 class="card-title mb-4">Получить список предложений на выплату</h3>
                        <p class="text-sm text-base-content/70 mb-4">GET /api/payout/offers</p>

                        <div class="card-actions justify-end">
                            <button @click="executeRequest('GET', 'payout/offers')"
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
                        <h3 class="card-title mb-4">Создать выплату</h3>
                        <p class="text-sm text-base-content/70 mb-4">POST /api/payout</p>

                        <div class="grid grid-cols-1 gap-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">payout_gateway_id</span>
                                </label>
                                <input v-model="payoutForm.payout_gateway_id" type="text" class="input input-bordered" placeholder="ID направления">
                            </div>
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">external_id</span>
                                </label>
                                <input v-model="payoutForm.external_id" type="text" class="input input-bordered" placeholder="Внешний ID">
                            </div>
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">detail <span class="text-error">*</span></span>
                                </label>
                                <input v-model="payoutForm.detail" type="text" class="input input-bordered" placeholder="Реквизиты">
                            </div>
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">detail_type</span>
                                </label>
                                <select v-model="payoutForm.detail_type" class="select select-bordered">
                                    <option value="card">card</option>
                                    <option value="phone">phone</option>
                                    <option value="account_number">account_number</option>
                                </select>
                            </div>
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">detail_initials</span>
                                </label>
                                <input v-model="payoutForm.detail_initials" type="text" class="input input-bordered" placeholder="Держатель реквизитов">
                            </div>
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">amount</span>
                                </label>
                                <input v-model="payoutForm.amount" type="number" class="input input-bordered" placeholder="1000">
                            </div>
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">payment_gateway</span>
                                </label>
                                <input v-model="payoutForm.payment_gateway" type="text" class="input input-bordered" placeholder="sberbank_rub">
                            </div>
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">callback_url</span>
                                </label>
                                <input v-model="payoutForm.callback_url" type="url" class="input input-bordered" placeholder="https://example.com/callback">
                            </div>
                        </div>
                        <div class="card-actions justify-end mt-4">
                            <button @click="executeRequest('POST', 'payout', payoutForm)"
                                    class="btn btn-primary" :disabled="loading || !payoutForm.detail">
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
                        <h3 class="card-title mb-4">Получить выплату</h3>
                        <p class="text-sm text-base-content/70 mb-4">GET /api/payout/{uuid}</p>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">uuid <span class="text-error">*</span></span>
                            </label>
                            <input v-model="payoutGetForm.uuid" type="text" class="input input-bordered" placeholder="UUID выплаты">
                        </div>
                        <div class="card-actions justify-end mt-4">
                            <button @click="executeRequest('GET', `payout/${payoutGetForm.uuid}`)"
                                    class="btn btn-primary" :disabled="loading || !payoutGetForm.uuid">
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

