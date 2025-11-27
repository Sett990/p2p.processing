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
    merchantId: {
        type: String,
        default: ''
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

// H2H API формы
const h2hOrderForm = ref({
    external_id: `test_h2h_${Date.now()}`,
    amount: '1000',
    payment_gateway: '',
    currency: 'rub',
    payment_detail_type: '',
    merchant_id: props.merchantId || '',
    callback_url: '',
    'X-Max-Wait-Ms': '30000'
});

const h2hGetOrderForm = ref({
    order_id: '',
    merchant_id: props.merchantId || '',
    external_id: ''
});

const h2hCancelOrderForm = ref({
    order_id: ''
});

const h2hDisputeForm = ref({
    order_id: '',
    receipt: ''
});

const h2hGetDisputeForm = ref({
    order_id: ''
});
</script>

<template>
    <div class="space-y-6">
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <h3 class="card-title mb-4">Создать сделку</h3>
                        <p class="text-sm text-base-content/70 mb-4">POST /api/h2h/order</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">external_id <span class="text-error">*</span></span>
                                </label>
                                <input v-model="h2hOrderForm.external_id" type="text" class="input input-bordered" placeholder="Уникальный ID сделки">
                            </div>
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">amount <span class="text-error">*</span></span>
                                </label>
                                <input v-model="h2hOrderForm.amount" type="number" class="input input-bordered" placeholder="1000">
                            </div>
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">payment_gateway</span>
                                </label>
                                <input v-model="h2hOrderForm.payment_gateway" type="text" class="input input-bordered" placeholder="sberbank">
                            </div>
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">currency</span>
                                </label>
                                <input v-model="h2hOrderForm.currency" type="text" class="input input-bordered" placeholder="rub">
                            </div>
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">payment_detail_type</span>
                                </label>
                                <select v-model="h2hOrderForm.payment_detail_type" class="select select-bordered">
                                    <option value="">Не указано</option>
                                    <option value="card">card</option>
                                    <option value="phone">phone</option>
                                    <option value="account_number">account_number</option>
                                </select>
                            </div>
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">merchant_id <span class="text-error">*</span></span>
                                </label>
                                <input v-model="h2hOrderForm.merchant_id" type="text" class="input input-bordered" placeholder="UUID мерчанта">
                            </div>
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">callback_url</span>
                                </label>
                                <input v-model="h2hOrderForm.callback_url" type="url" class="input input-bordered" placeholder="https://example.com/callback">
                            </div>
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">X-Max-Wait-Ms</span>
                                </label>
                                <input v-model="h2hOrderForm['X-Max-Wait-Ms']" type="number" class="input input-bordered" placeholder="30000">
                            </div>
                        </div>
                        <div class="card-actions justify-end mt-4">
                            <button @click="executeRequest('POST', 'h2h/order', Object.fromEntries(Object.entries(h2hOrderForm).filter(([key]) => key !== 'X-Max-Wait-Ms')), { 'X-Max-Wait-Ms': h2hOrderForm['X-Max-Wait-Ms'] })"
                                    class="btn btn-primary" :disabled="loading">
                                <span v-if="loading" class="loading loading-spinner loading-sm"></span>
                                Отправить запрос
                            </button>
                        </div>
                    </div>
                    <div class="lg:border-l lg:pl-6 lg:border-base-300">
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
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <h3 class="card-title mb-4">Получить сделку</h3>
                        <p class="text-sm text-base-content/70 mb-4">GET /api/h2h/order/{order_id} или GET /api/h2h/order/{merchant_id}/{external_id}</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">order_id</span>
                                </label>
                                <input v-model="h2hGetOrderForm.order_id" type="text" class="input input-bordered" placeholder="UUID сделки">
                            </div>
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">merchant_id</span>
                                </label>
                                <input v-model="h2hGetOrderForm.merchant_id" type="text" class="input input-bordered" placeholder="UUID мерчанта">
                            </div>
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">external_id</span>
                                </label>
                                <input v-model="h2hGetOrderForm.external_id" type="text" class="input input-bordered" placeholder="Внешний ID сделки">
                            </div>
                        </div>
                        <div class="card-actions justify-end mt-4">
                            <button @click="executeRequest('GET', h2hGetOrderForm.order_id ? `h2h/order/${h2hGetOrderForm.order_id}` : `h2h/order/${h2hGetOrderForm.merchant_id}/${h2hGetOrderForm.external_id}`)"
                                    class="btn btn-primary" :disabled="loading || (!h2hGetOrderForm.order_id && (!h2hGetOrderForm.merchant_id || !h2hGetOrderForm.external_id))">
                                <span v-if="loading" class="loading loading-spinner loading-sm"></span>
                                Отправить запрос
                            </button>
                        </div>
                    </div>
                    <div class="lg:border-l lg:pl-6 lg:border-base-300">
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
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <h3 class="card-title mb-4">Закрыть сделку</h3>
                        <p class="text-sm text-base-content/70 mb-4">PATCH /api/h2h/order/{order_id}/cancel</p>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">order_id <span class="text-error">*</span></span>
                            </label>
                            <input v-model="h2hCancelOrderForm.order_id" type="text" class="input input-bordered" placeholder="UUID сделки">
                        </div>
                        <div class="card-actions justify-end mt-4">
                            <button @click="executeRequest('PATCH', `h2h/order/${h2hCancelOrderForm.order_id}/cancel`)"
                                    class="btn btn-primary" :disabled="loading || !h2hCancelOrderForm.order_id">
                                <span v-if="loading" class="loading loading-spinner loading-sm"></span>
                                Отправить запрос
                            </button>
                        </div>
                    </div>
                    <div class="lg:border-l lg:pl-6 lg:border-base-300">
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
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <h3 class="card-title mb-4">Открыть спор</h3>
                        <p class="text-sm text-base-content/70 mb-4">POST /api/h2h/order/{order_id}/dispute</p>

                        <div class="grid grid-cols-1 gap-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">order_id <span class="text-error">*</span></span>
                                </label>
                                <input v-model="h2hDisputeForm.order_id" type="text" class="input input-bordered" placeholder="UUID сделки">
                            </div>
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">receipt <span class="text-error">*</span></span>
                                </label>
                                <textarea v-model="h2hDisputeForm.receipt" class="textarea textarea-bordered" placeholder="Base64 изображения (jpeg, jpg, png, pdf)"></textarea>
                                <label class="label">
                                    <span class="label-text-alt">Изображение в формате base64 (до 5МБ)</span>
                                </label>
                            </div>
                        </div>
                        <div class="card-actions justify-end mt-4">
                            <button @click="executeRequest('POST', `h2h/order/${h2hDisputeForm.order_id}/dispute`, { receipt: h2hDisputeForm.receipt })"
                                    class="btn btn-primary" :disabled="loading || !h2hDisputeForm.order_id || !h2hDisputeForm.receipt">
                                <span v-if="loading" class="loading loading-spinner loading-sm"></span>
                                Отправить запрос
                            </button>
                        </div>
                    </div>
                    <div class="lg:border-l lg:pl-6 lg:border-base-300">
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
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <h3 class="card-title mb-4">Получить спор</h3>
                        <p class="text-sm text-base-content/70 mb-4">GET /api/h2h/order/{order_id}/dispute</p>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">order_id <span class="text-error">*</span></span>
                            </label>
                            <input v-model="h2hGetDisputeForm.order_id" type="text" class="input input-bordered" placeholder="UUID сделки">
                        </div>
                        <div class="card-actions justify-end mt-4">
                            <button @click="executeRequest('GET', `h2h/order/${h2hGetDisputeForm.order_id}/dispute`)"
                                    class="btn btn-primary" :disabled="loading || !h2hGetDisputeForm.order_id">
                                <span v-if="loading" class="loading loading-spinner loading-sm"></span>
                                Отправить запрос
                            </button>
                        </div>
                    </div>
                    <div class="lg:border-l lg:pl-6 lg:border-base-300">
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

