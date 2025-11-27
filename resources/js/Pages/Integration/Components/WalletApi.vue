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

// Wallet формы
const walletWithdrawForm = ref({
    amount: '',
    address: '',
    network: 'bsc'
});
</script>

<template>
    <div class="space-y-6">
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <h3 class="card-title mb-4">Получить баланс</h3>
                        <p class="text-sm text-base-content/70 mb-4">GET /api/wallet/balance</p>

                        <div class="card-actions justify-end">
                            <button @click="executeRequest('GET', 'wallet/balance')"
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
                        <h3 class="card-title mb-4">Создать запрос на вывод</h3>
                        <p class="text-sm text-base-content/70 mb-4">POST /api/wallet/withdraw</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">amount <span class="text-error">*</span></span>
                                </label>
                                <input v-model="walletWithdrawForm.amount" type="number" class="input input-bordered" placeholder="1000">
                            </div>
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">address <span class="text-error">*</span></span>
                                </label>
                                <input v-model="walletWithdrawForm.address" type="text" class="input input-bordered" placeholder="Адрес кошелька">
                            </div>
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">network <span class="text-error">*</span></span>
                                </label>
                                <select v-model="walletWithdrawForm.network" class="select select-bordered">
                                    <option value="bsc">BSC</option>
                                    <option value="arb">ARB</option>
                                    <option value="trx">TRX</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-actions justify-end mt-4">
                            <button @click="executeRequest('POST', 'wallet/withdraw', walletWithdrawForm)"
                                    class="btn btn-primary" :disabled="loading || !walletWithdrawForm.amount || !walletWithdrawForm.address">
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

