<script setup>
import Modal from "@/Components/Modals/Modal.vue";
import ModalHeader from "@/Components/Modals/Components/ModalHeader.vue";
import ModalBody from "@/Components/Modals/Components/ModalBody.vue";
import DateTime from "@/Components/DateTime.vue";
import {useModalStore} from "@/store/modal.js";
import {useViewStore} from "@/store/view.js";
import {storeToRefs} from "pinia";
import {computed, ref} from "vue";

const viewStore = useViewStore();
const modalStore = useModalStore();
const {orderCalcModal} = storeToRefs(modalStore);

const order = ref(null);
const loading = ref(false);
const error = ref(null);

const closeModal = () => {
    modalStore.closeModal('orderCalc');
};

const show = () => {
    const orderId = orderCalcModal.value.params?.order_id;
    if (!orderId) {
        return;
    }

    loading.value = true;
    error.value = null;
    order.value = null;

    axios.get(route('admin.orders.calc', orderId))
        .then((response) => {
            if (response.data?.success) {
                order.value = response.data.data?.order ?? null;
            } else {
                error.value = 'Не удалось получить данные';
            }
        })
        .catch(() => {
            error.value = 'Не удалось получить данные';
        })
        .finally(() => {
            loading.value = false;
        });
};

const calcMeta = computed(() => order.value?.calc_meta ?? null);
const getCalcValue = (section, key) => calcMeta.value?.[section]?.[key] ?? null;
const getCalcCurrency = () => calcMeta.value?.exchange?.currency ?? 'USDT';
const getCalcAmountCurrency = () => calcMeta.value?.inputs?.amount_currency ?? 'RUB';
const merchantMovementLabel = 'Получит мерчант';
const shouldShowSplitPercent = () => {
    const teamLeadRate = getCalcValue('inputs', 'teamlead_commission_rate');
    if (teamLeadRate === null || teamLeadRate === undefined) {
        return false;
    }

    return Number(teamLeadRate) > 0;
};
const getServiceCommissionRate = () => {
    const direct = getCalcValue('inputs', 'service_commission_rate');
    if (direct !== null && direct !== undefined) {
        return direct;
    }

    const totalRate = getCalcValue('inputs', 'total_commission_rate');
    const traderRate = getCalcValue('inputs', 'trader_commission_rate');
    const teamleadRate = getCalcValue('inputs', 'teamlead_commission_rate');
    if (totalRate === null || traderRate === null || teamleadRate === null) {
        return null;
    }

    const result = Number(totalRate) - Number(traderRate) - Number(teamleadRate);
    if (Number.isNaN(result)) {
        return null;
    }

    return Math.max(result, 0);
};
const formatCalcMoney = (value, currency, empty = '—') => {
    if (value === null || value === undefined || value === '') {
        return empty;
    }

    return `${value} ${currency ?? ''}`.trim();
};
const formatMeta = (meta) => {
    if (!meta) {
        return 'Нет данных';
    }

    try {
        return JSON.stringify(meta, null, 2);
    } catch (error) {
        return String(meta);
    }
};
</script>

<template>
    <Modal
        :show="!!orderCalcModal.showed"
        @close="closeModal"
        maxWidth="6xl"
        @on-show="show"
    >
        <ModalHeader
            :title="order ? `Математика по сделке #${order.uuid_short}` : 'Математика по сделке'"
            @close="closeModal"
        />
        <ModalBody>
            <div v-if="loading" class="flex items-center justify-center py-10">
                <span class="loading loading-spinner loading-lg text-primary" />
            </div>
            <div v-else-if="error" class="text-sm text-error text-center py-6">
                {{ error }}
            </div>
            <div v-else-if="!calcMeta" class="text-sm text-base-content/60 text-center py-6">
                Нет данных для расчета.
            </div>
            <div v-else class="text-sm">
                <div class="card">
                    <div class="card-body text-sm space-y-4 pt-0">
                        <div class="flex items-center justify-between gap-2">
                            <div class="text-xs uppercase text-base-content/50">Математика (calc meta)</div>
                            <div v-if="calcMeta.logic" class="badge badge-outline text-xs">
                                {{ calcMeta.logic }}
                            </div>
                        </div>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <div class="text-xs uppercase text-base-content/50">Входные данные</div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-base-content/60">Сумма</span>
                                    <span class="font-semibold">
                                        {{ formatCalcMoney(getCalcValue('inputs', 'amount'), getCalcAmountCurrency()) }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-base-content/60">Комиссия всего</span>
                                    <span class="font-semibold">{{ getCalcValue('inputs', 'total_commission_rate') ?? '—' }}%</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-base-content/60">Комиссия трейдера</span>
                                    <span class="font-semibold">{{ getCalcValue('inputs', 'trader_commission_rate') ?? '—' }}%</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-base-content/60">Комиссия тимлида</span>
                                    <span class="font-semibold">{{ getCalcValue('inputs', 'teamlead_commission_rate') ?? '—' }}%</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-base-content/60">Комиссия сервиса</span>
                                    <span class="font-semibold">{{ getServiceCommissionRate() ?? '—' }}%</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <div class="text-xs uppercase text-base-content/50">Курс</div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-base-content/60">Маркет</span>
                                    <span class="font-semibold">{{ getCalcValue('exchange', 'market') ?? '—' }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-base-content/60">Цена</span>
                                    <span class="font-semibold">
                                        {{ formatCalcMoney(getCalcValue('exchange', 'price'), getCalcCurrency()) }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-base-content/60">Зафиксирован</span>
                                    <DateTime :data="getCalcValue('exchange', 'fixed_at')" simple class="justify-start font-semibold" />
                                </div>
                            </div>
                        </div>

                        <div class="divider my-1"></div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <div class="text-xs uppercase text-base-content/50">Выходные суммы (USDT)</div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-base-content/60">Тело</span>
                                    <span class="font-semibold">{{ formatCalcMoney(getCalcValue('outputs', 'usdt_body'), 'USDT') }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-base-content/60">Комиссия всего</span>
                                    <span class="font-semibold">{{ formatCalcMoney(getCalcValue('outputs', 'total_fee'), 'USDT') }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-base-content/60">Комиссия трейдера (база)</span>
                                    <span class="font-semibold">{{ formatCalcMoney(getCalcValue('outputs', 'trader_fee_base'), 'USDT') }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-base-content/60">Комиссия трейдера (итог)</span>
                                    <span class="font-semibold">{{ formatCalcMoney(getCalcValue('outputs', 'trader_fee'), 'USDT') }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-base-content/60">Комиссия тимлида</span>
                                    <span class="font-semibold">{{ formatCalcMoney(getCalcValue('outputs', 'teamlead_fee'), 'USDT') }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-base-content/60">Комиссия сервиса (база)</span>
                                    <span class="font-semibold">{{ formatCalcMoney(getCalcValue('outputs', 'service_fee_base'), 'USDT') }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-base-content/60">Комиссия сервиса (итог)</span>
                                    <span class="font-semibold">{{ formatCalcMoney(getCalcValue('outputs', 'service_fee'), 'USDT') }}</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <div class="text-xs uppercase text-base-content/50">Движение средств (USDT)</div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-base-content/60">Списано у трейдера</span>
                                    <span class="font-semibold">{{ formatCalcMoney(getCalcValue('outputs', 'trader_debit'), 'USDT') }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-base-content/60">{{ merchantMovementLabel }}</span>
                                    <span class="font-semibold">{{ formatCalcMoney(getCalcValue('outputs', 'merchant_credit'), 'USDT') }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-base-content/60">Получит тимлидер</span>
                                    <span class="font-semibold">{{ formatCalcMoney(getCalcValue('outputs', 'teamlead_fee'), 'USDT') }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-base-content/60">Получит сервис</span>
                                    <span class="font-semibold">{{ formatCalcMoney(getCalcValue('outputs', 'service_fee'), 'USDT') }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-base-content/60">Прибыль трейдера</span>
                                    <span class="font-semibold">{{ formatCalcMoney(getCalcValue('outputs', 'trader_receive'), 'USDT') }}</span>
                                </div>
                                <div class="divider my-1"></div>
                                <div class="text-xs uppercase text-base-content/50">Сплит тимлида (USDT)</div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-base-content/60">Из сервиса</span>
                                    <span class="font-semibold">{{ formatCalcMoney(getCalcValue('split', 'from_service'), 'USDT') }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-base-content/60">Из трейдера</span>
                                    <span class="font-semibold">{{ formatCalcMoney(getCalcValue('split', 'from_trader'), 'USDT') }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-base-content/60">Процент от сервиса</span>
                                    <span class="font-semibold">
                                        <template v-if="shouldShowSplitPercent()">
                                            {{ getCalcValue('split', 'from_service_percent') ?? '—' }}%
                                        </template>
                                        <template v-else>—</template>
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-base-content/60">Процент от трейдера</span>
                                    <span class="font-semibold">
                                        <template v-if="shouldShowSplitPercent()">
                                            {{ getCalcValue('split', 'from_trader_percent') ?? '—' }}%
                                        </template>
                                        <template v-else>—</template>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="divider my-1"></div>

                        <div class="collapse collapse-arrow border border-base-200 bg-base-200/40 rounded-box">
                            <input type="checkbox" />
                            <div class="collapse-title text-xs font-semibold uppercase text-base-content/50">
                                Raw calc_meta
                            </div>
                            <div class="collapse-content">
                                <pre class="text-xs whitespace-pre-wrap bg-base-200/70 p-3 rounded-box">{{ formatMeta(calcMeta) }}</pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </ModalBody>
    </Modal>
</template>
