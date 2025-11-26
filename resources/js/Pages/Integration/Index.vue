<script setup>
import {Head, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {useClipboard} from "@vueuse/core";
import {ref} from 'vue';
import axios from 'axios';
import ApiDocumentation from '@/Pages/Integration/Components/ApiDocumentation.vue';
import MerchantApi from '@/Pages/Integration/Components/MerchantApi.vue';
import H2HApi from '@/Pages/Integration/Components/H2HApi.vue';
import WalletApi from '@/Pages/Integration/Components/WalletApi.vue';
import PayoutApi from '@/Pages/Integration/Components/PayoutApi.vue';
import CommonApi from '@/Pages/Integration/Components/CommonApi.vue';

const user = usePage().props.auth.user;
const token = usePage().props.token;
const merchantId = usePage().props.merchantId;

const { text, copy, copied } = useClipboard();

const activeTab = ref('merchant');
const loading = ref(false);
const response = ref(null);
const responseError = ref(null);

const executeRequest = async (method, endpoint, data = {}, headers = {}) => {
    loading.value = true;
    response.value = null;
    responseError.value = null;

    try {
        // Фильтруем пустые значения
        const cleanData = Object.fromEntries(
            Object.entries(data).filter(([_, value]) => value !== '' && value !== null && value !== undefined)
        );

        const cleanHeaders = Object.fromEntries(
            Object.entries(headers).filter(([_, value]) => value !== '' && value !== null && value !== undefined)
        );

        const result = await axios.post(route('integration.api-proxy'), {
            method,
            endpoint,
            data: cleanData,
            headers: cleanHeaders
        });

        if (result.data.success) {
            response.value = result.data;
        } else {
            responseError.value = result.data;
        }
    } catch (error) {
        responseError.value = {
            message: error.response?.data?.message || error.message,
            errors: error.response?.data?.errors || {}
        };
    } finally {
        loading.value = false;
    }
};

const clearResponse = () => {
    response.value = null;
    responseError.value = null;
};

defineOptions({ layout: AuthenticatedLayout });
</script>

<template>
    <Head title="Интеграция по API"/>

    <div class="antialiased">
        <div class="mx-auto max-w-7xl">
            <h2 class="text-3xl font-bold text-base-content mb-6">Интеграция по API</h2>

            <!-- Блок с токеном -->
            <div class="card w-full bg-base-100 shadow-lg mb-6">
                <div class="card-body">
                    <label for="api-key" class="text-sm font-medium text-base-content mb-2 block">API токен:</label>
                    <div class="relative">
                        <input
                            id="api-key"
                            type="text"
                            class="col-span-6 bg-base-200 border border-base-300 text-base-content/70 text-sm rounded-xl focus:ring-primary focus:border-primary block w-full p-2.5 pr-12"
                            :value="token"
                            disabled
                            readonly
                        >
                        <button
                            @click="copy(token)"
                            class="absolute end-2 top-1/2 -translate-y-1/2 text-base-content/70 hover:bg-base-200 rounded-xl p-2 inline-flex items-center justify-center"
                            type="button"
                            aria-label="Скопировать токен"
                        >
                            <span v-if="!copied">
                                <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 20">
                                    <path d="M16 1h-3.278A1.992 1.992 0 0 0 11 0H7a1.993 1.993 0 0 0-1.722 1H2a2 2 0 0 0-2 2v15a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2Zm-3 14H5a1 1 0 0 1 0-2h8a1 1 0 0 1 0 2Zm0-4H5a1 1 0 0 1 0-2h8a1 1 0 1 1 0 2Zm0-5H5a1 1 0 0 1 0-2h2V2h4v2h2a1 1 0 1 1 0 2Z"/>
                                </svg>
                            </span>
                            <span v-else class="inline-flex items-center">
                                <svg class="w-4 h-4 text-primary" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 16 12">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5.917 5.724 10.5 15 1.5"/>
                                </svg>
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Табы для разделов API -->
            <div class="tabs tabs-boxed mb-6">
                <a class="tab" :class="{ 'tab-active': activeTab === 'merchant' }" @click="activeTab = 'merchant'; clearResponse()">
                    Merchant API
                </a>
                <a class="tab" :class="{ 'tab-active': activeTab === 'h2h' }" @click="activeTab = 'h2h'; clearResponse()">
                    H2H API
                </a>
                <a class="tab" :class="{ 'tab-active': activeTab === 'wallet' }" @click="activeTab = 'wallet'; clearResponse()">
                    Авто вывод
                </a>
                <a class="tab" :class="{ 'tab-active': activeTab === 'payout' }" @click="activeTab = 'payout'; clearResponse()">
                    Выплаты
                </a>
                <a class="tab" :class="{ 'tab-active': activeTab === 'common' }" @click="activeTab = 'common'; clearResponse()">
                    Общие методы
                </a>
                <a class="tab" :class="{ 'tab-active': activeTab === 'docs' }" @click="activeTab = 'docs'; clearResponse()">
                    Документация
                </a>
            </div>

            <!-- Merchant API -->
            <MerchantApi 
                v-if="activeTab === 'merchant'"
                :execute-request="executeRequest"
                :loading="loading"
                :merchant-id="merchantId"
                :response="response"
                :response-error="responseError"
                @clear="clearResponse"
            />

            <!-- H2H API -->
            <H2HApi 
                v-if="activeTab === 'h2h'"
                :execute-request="executeRequest"
                :loading="loading"
                :merchant-id="merchantId"
                :response="response"
                :response-error="responseError"
                @clear="clearResponse"
            />

            <!-- Wallet API -->
            <WalletApi 
                v-if="activeTab === 'wallet'"
                :execute-request="executeRequest"
                :loading="loading"
                :response="response"
                :response-error="responseError"
                @clear="clearResponse"
            />

            <!-- Payout API -->
            <PayoutApi 
                v-if="activeTab === 'payout'"
                :execute-request="executeRequest"
                :loading="loading"
                :response="response"
                :response-error="responseError"
                @clear="clearResponse"
            />

            <!-- Общие методы -->
            <CommonApi 
                v-if="activeTab === 'common'"
                :execute-request="executeRequest"
                :loading="loading"
                :response="response"
                :response-error="responseError"
                @clear="clearResponse"
            />

            <!-- Документация -->
            <div v-if="activeTab === 'docs'">
                <ApiDocumentation />
            </div>
        </div>
    </div>
</template>
