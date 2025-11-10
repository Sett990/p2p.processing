<script setup>
import Modal from "@/Components/Modals/Modal.vue";
import ModalHeader from "@/Components/Modals/Components/ModalHeader.vue";
import ModalBody from "@/Components/Modals/Components/ModalBody.vue";
import ModalFooter from "@/Components/Modals/Components/ModalFooter.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import Select from "@/Components/Select.vue";
import Multiselect from "@/Components/Form/Multiselect.vue";
import TextInputBlock from "@/Components/Form/TextInputBlock.vue";
import NumberInputBlock from "@/Components/Form/NumberInputBlock.vue";
import { useModalStore } from "@/store/modal.js";
import { storeToRefs } from "pinia";
import { ref, computed, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";

const modalStore = useModalStore();
const { paymentDetailEditModal } = storeToRefs(modalStore);

const processing = ref(false);
const loading = ref(false);
const errors = ref({});

const payment_detail = ref(null);
const payment_gateways = ref([]);
const devices = ref([]);

const currentUser = usePage().props.auth?.user;
const isVipUser = computed(() => currentUser?.is_vip === true || currentUser?.is_vip === 1);

const form = ref({
    name: '',
    initials: '',
    is_active: false,
    daily_limit: '',
    max_pending_orders_quantity: null,
    min_order_amount: null,
    max_order_amount: null,
    order_interval_minutes: null,
    user_device_id: 0,
    payment_gateway_ids: [],
});

const initialPaymentGatewayIds = ref([]);

const formattedDevices = computed(() => {
    return (devices.value || []).map(device => ({
        ...device,
        name: `${device.name}`
    }));
});

const isMultipleGatewaysAllowed = computed(() => {
    // по логике сейчас запрещено
    return false;
});

const formattedPaymentGateways = computed(() => {
    if (!payment_detail.value) return [];
    return (payment_gateways.value || [])
        .filter(pg =>
            pg.currency?.toLowerCase() === payment_detail.value.currency?.toLowerCase() &&
            (pg.detail_types || []).includes(payment_detail.value.detail_type)
        )
        .map(pg => ({
            value: pg.id,
            label: pg.name
        }));
});

const canUnselectPaymentGateway = (id) => {
    return !initialPaymentGatewayIds.value.includes(id);
};

const resetState = () => {
    errors.value = {};
    processing.value = false;
    loading.value = false;
    payment_detail.value = null;
    payment_gateways.value = [];
    devices.value = [];
    initialPaymentGatewayIds.value = [];
    form.value = {
        name: '',
        initials: '',
        is_active: false,
        daily_limit: '',
        max_pending_orders_quantity: null,
        min_order_amount: null,
        max_order_amount: null,
        order_interval_minutes: null,
        user_device_id: 0,
        payment_gateway_ids: [],
    };
};

const close = () => {
    modalStore.closeModal('paymentDetailEdit');
};

const loadCreateData = () => {
    // те же данные, что и при создании (список активных ГП и устройства)
    return axios.get(route('payment-details.create-data'))
        .then((res) => {
            const data = res.data?.data || res.data || {};
            payment_gateways.value = data.paymentGateways || [];
            devices.value = (data.devices || []).map(device => ({
                ...device,
                name: `${device.name}`
            }));
        });
};

const loadPaymentDetail = (id) => {
    return axios.get(route('payment-details.show', id), {
        headers: { 'Accept': 'application/json' }
    }).then((res) => {
        const detail = res.data?.data || res.data;
        payment_detail.value = detail;
        // подготовка формы
        form.value = {
            name: detail.name,
            initials: detail.initials,
            is_active: !!detail.is_active,
            daily_limit: detail.daily_limit,
            max_pending_orders_quantity: detail.max_pending_orders_quantity,
            min_order_amount: detail.min_order_amount,
            max_order_amount: detail.max_order_amount,
            order_interval_minutes: detail.order_interval_minutes,
            user_device_id: detail.user_device_id ?? 0,
            payment_gateway_ids: detail.payment_gateway_ids ?? [],
        };
        initialPaymentGatewayIds.value = [...(detail.payment_gateway_ids ?? [])];
    });
};

const loadData = async () => {
    loading.value = true;
    errors.value = {};
    try {
        const id = paymentDetailEditModal.value.params?.paymentDetail?.id ?? paymentDetailEditModal.value.params?.id;
        await Promise.all([
            loadCreateData(),
            loadPaymentDetail(id),
        ]);
    } finally {
        loading.value = false;
    }
};

const submit = () => {
    if (!payment_detail.value) return;
    processing.value = true;
    errors.value = {};

    const payload = { ...form.value };
    if (payload.user_device_id === 0) {
        payload.user_device_id = null;
    }

    axios.patch(route('payment-details.update', payment_detail.value.id), payload, {
        headers: { 'Accept': 'application/json' }
    })
        .then((res) => {
            processing.value = false;
            if (res.data?.success || res.status === 200) {
                close();
                router.reload({ only: ['paymentDetails'] });
            }
        })
        .catch((error) => {
            processing.value = false;
            if (error.response && error.response.data) {
                // валидация
                if (error.response.data.errors) {
                    errors.value = error.response.data.errors;
                } else if (error.response.data.message) {
                    // серверная бизнес-ошибка по payment_gateway_ids, если будет
                    errors.value = { _error: [error.response.data.message] };
                }
            }
        });
};

watch(
    () => paymentDetailEditModal.value.showed,
    async (state) => {
        if (state) {
            resetState();
            await loadData();
        } else {
            resetState();
        }
    }
);
</script>

<template>
    <Modal :show="paymentDetailEditModal.showed" @close="close" maxWidth="xl">
        <ModalHeader @close="close" :title="'Редактирование реквизита - ' + (form.name || '')" />
        <ModalBody>
            <div v-if="loading" class="py-6 text-center">
                <span class="loading loading-spinner loading-md"></span>
            </div>
            <form v-else @submit.prevent="submit" class="space-y-6">
                <div class="mt-4">
                    <InputLabel
                        for="user_device_id"
                        value="Устройство"
                        :error="!!errors.user_device_id?.[0]"
                        class="mb-1"
                    />
                    <Select
                        id="user_device_id"
                        v-model="form.user_device_id"
                        :error="!!errors.user_device_id?.[0]"
                        :items="formattedDevices"
                        value="id"
                        name="name"
                        default_title="Выберите устройство"
                        @change="errors.user_device_id = null"
                        :disabled="processing"
                    />
                    <InputError :message="errors.user_device_id?.[0]" class="mt-2"/>
                </div>

                <div class="mt-4">
                    <InputLabel
                        for="payment_gateway_ids"
                        :value="isMultipleGatewaysAllowed ? 'Платежные методы' : 'Платежный метод'"
                        :error="!!errors.payment_gateway_ids?.[0]"
                        class="mb-1"
                    />
                    <Multiselect
                        id="payment_gateway_ids"
                        v-model="form.payment_gateway_ids"
                        :options="formattedPaymentGateways"
                        :error="!!errors.payment_gateway_ids?.[0]"
                        @change="errors.payment_gateway_ids = null"
                        :enable-search="true"
                        :single-select="!isMultipleGatewaysAllowed"
                        :placeholder="isMultipleGatewaysAllowed ? 'Выберите платежные методы' : 'Выберите платежный метод'"
                        :can-unselect="canUnselectPaymentGateway"
                        :disabled="processing"
                    />
                    <InputError :message="errors.payment_gateway_ids?.[0]" class="mt-2"/>
                </div>

                <TextInputBlock
                    v-model="form.name"
                    :form="{}"
                    :errors="errors"
                    field="name"
                    label="Никнейм реквизитов"
                />

                <TextInputBlock
                    v-model="form.initials"
                    :form="{}"
                    :errors="errors"
                    field="initials"
                    label="Инициалы (имя получателя)"
                />

                <NumberInputBlock
                    v-model="form.daily_limit"
                    :form="{}"
                    :errors="errors"
                    :on-clear="(field) => (errors[field] = null)"
                    field="daily_limit"
                    :label="'Лимит на объем операций в сутки (' + (payment_detail?.currency?.toUpperCase() || '') + ')'"
                />

                <NumberInputBlock
                    v-if="isVipUser"
                    v-model="form.min_order_amount"
                    :form="{}"
                    :errors="errors"
                    :on-clear="(field) => (errors[field] = null)"
                    field="min_order_amount"
                    :label="'Минимальная сумма сделки (' + (payment_detail?.currency?.toUpperCase() || '') + ')'"
                    helper="Оставьте пустым для отключения лимита"
                />

                <NumberInputBlock
                    v-if="isVipUser"
                    v-model="form.max_order_amount"
                    :form="{}"
                    :errors="errors"
                    :on-clear="(field) => (errors[field] = null)"
                    field="max_order_amount"
                    :label="'Максимальная сумма сделки (' + (payment_detail?.currency?.toUpperCase() || '') + ')'"
                    helper="Оставьте пустым для отключения лимита"
                />

                <NumberInputBlock
                    v-model="form.order_interval_minutes"
                    :form="{}"
                    :errors="errors"
                    :on-clear="(field) => (errors[field] = null)"
                    field="order_interval_minutes"
                    label="Интервал между сделками (минуты)"
                    helper="Оставьте пустым для отключения интервала"
                />

                <div>
                    <label class="label cursor-pointer mb-3 mt-3 justify-start gap-3">
                        <span class="label-text">Активен</span>
                        <input type="checkbox" class="toggle toggle-primary" v-model="form.is_active" :disabled="processing" />
                    </label>
                </div>
            </form>
        </ModalBody>
        <ModalFooter>
            <button @click="close" type="button" class="btn btn-sm">Отмена</button>
            <button @click="submit" type="button" class="btn btn-sm btn-primary" :class="{ 'btn-disabled': processing }" :disabled="processing">
                Сохранить
            </button>
        </ModalFooter>
    </Modal>
</template>


