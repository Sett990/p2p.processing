<script setup>
import {Head, useForm, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {computed} from "vue";
import Select from "@/Components/Select.vue";
import SaveButton from "@/Components/Form/SaveButton.vue";
import SecondaryPageSection from "@/Wrappers/SecondaryPageSection.vue";
import TextInputBlock from "@/Components/Form/TextInputBlock.vue";
import NumberInputBlock from "@/Components/Form/NumberInputBlock.vue";
import {useViewStore} from "@/store/view.js";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import Multiselect from "@/Components/Form/Multiselect.vue";

const viewStore = useViewStore();
const currentUser = usePage().props.auth?.user;

const payment_detail = usePage().props.paymentDetail;
const payment_gateways = usePage().props.paymentGateways;
const devices = usePage().props.devices;

// Определяем, является ли текущий пользователь VIP
const isVipUser = computed(() => {
    return currentUser?.is_vip === true || currentUser?.is_vip === 1;
});

// Определяем, можно ли выбрать несколько платежных методов
const isMultipleGatewaysAllowed = computed(() => {
    //return payment_detail.detail_type === 'phone';
    return false;
});

// Доступные платежные методы с учетом валюты и типа реквизита
const formattedPaymentGateways = computed(() => {
    return payment_gateways
        .filter(pg =>
            pg.currency.toLowerCase() === payment_detail.currency.toLowerCase() &&
            pg.detail_types.includes(payment_detail.detail_type)
        )
        .map(pg => ({
            value: pg.id,
            label: pg.name
        }));
});

const form = useForm({
    name: payment_detail.name,
    initials: payment_detail.initials,
    is_active: !!payment_detail.is_active,
    daily_limit: payment_detail.daily_limit,
    max_pending_orders_quantity: payment_detail.max_pending_orders_quantity,
    min_order_amount: payment_detail.min_order_amount,
    max_order_amount: payment_detail.max_order_amount,
    order_interval_minutes: payment_detail.order_interval_minutes,
    user_device_id: payment_detail.user_device_id ?? 0,
    payment_gateway_ids: payment_detail.payment_gateway_ids ?? [],
});

// Сохраняем изначальные ID платежных методов
const initialPaymentGatewayIds = [...payment_detail.payment_gateway_ids];

// Функция для проверки, можно ли снять выбор с метода
const canUnselectPaymentGateway = (id) => {
    return !initialPaymentGatewayIds.includes(id);
};

const formattedDevices = computed(() => {
    return devices.map(device => ({
        ...device,
        name: `${device.name}`
    }));
});

const submit = () => {
    form
        .patch(route('payment-details.update', payment_detail.id), {
            preserveScroll: true,
        });
};

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head :title="'Редактирование реквизита - ' + form.name" />

        <SecondaryPageSection
            :back-link="route(viewStore.adminPrefix + 'payment-details.index')"
            :title="'Редактирование реквизита - ' + form.name"
            description="Здесь вы можете редактировать платежные реквизиты."
        >
            <form @submit.prevent="submit" class="mt-6 space-y-6">
                <div class="mt-4">
                    <InputLabel
                        for="user_device_id"
                        value="Устройство"
                        :error="!!form.errors.user_device_id"
                        class="mb-1"
                    />
                    <Select
                        id="user_device_id"
                        v-model="form.user_device_id"
                        :error="!!form.errors.user_device_id"
                        :items="formattedDevices"
                        value="id"
                        name="name"
                        default_title="Выберите устройство"
                        @change="form.clearErrors('user_device_id')"
                    ></Select>
                    <InputError :message="form.errors.user_device_id" class="mt-2"/>
                </div>

                <div class="mt-4">
                    <InputLabel
                        for="payment_gateway_ids"
                        :value="isMultipleGatewaysAllowed ? 'Платежные методы' : 'Платежный метод'"
                        :error="!!form.errors.payment_gateway_ids"
                        class="mb-1"
                    />
                    <Multiselect
                        id="payment_gateway_ids"
                        v-model="form.payment_gateway_ids"
                        :options="formattedPaymentGateways"
                        :error="!!form.errors.payment_gateway_ids"
                        @change="form.clearErrors('payment_gateway_ids')"
                        :enable-search="true"
                        :single-select="!isMultipleGatewaysAllowed"
                        :placeholder="isMultipleGatewaysAllowed ? 'Выберите платежные методы' : 'Выберите платежный метод'"
                        :can-unselect="canUnselectPaymentGateway"
                    />
                    <InputError :message="form.errors.payment_gateway_ids" class="mt-2"/>
                </div>

                <TextInputBlock
                    v-model="form.name"
                    :form="form"
                    field="name"
                    label="Никнейм реквизитов"
                />

                <TextInputBlock
                    v-model="form.initials"
                    :form="form"
                    field="initials"
                    label="Инициалы (имя получателя)"
                />

                <NumberInputBlock
                    v-model="form.daily_limit"
                    :form="form"
                    field="daily_limit"
                    :label="'Лимит на объем операций в сутки (' + payment_detail.currency?.toUpperCase() + ')'"
                />

                <NumberInputBlock
                    v-if="viewStore.isAdminViewMode"
                    v-model="form.max_pending_orders_quantity"
                    :form="form"
                    field="max_pending_orders_quantity"
                    label="Максимальное количество активных сделок"
                />

                <NumberInputBlock
                    v-if="viewStore.isAdminViewMode || isVipUser"
                    v-model="form.min_order_amount"
                    :form="form"
                    field="min_order_amount"
                    :label="'Минимальная сумма сделки (' + payment_detail.currency?.toUpperCase() + ')'"
                    helper="Оставьте пустым для отключения лимита"
                />

                <NumberInputBlock
                    v-if="viewStore.isAdminViewMode || isVipUser"
                    v-model="form.max_order_amount"
                    :form="form"
                    field="max_order_amount"
                    :label="'Максимальная сумма сделки (' + payment_detail.currency?.toUpperCase() + ')'"
                    helper="Оставьте пустым для отключения лимита"
                />

                <NumberInputBlock
                    v-model="form.order_interval_minutes"
                    :form="form"
                    field="order_interval_minutes"
                    label="Интервал между сделками (минуты)"
                    helper="Оставьте пустым для отключения интервала"
                />

                <div>
                    <label class="label cursor-pointer mb-3 mt-3 justify-start gap-3">
                        <span class="label-text">Активен</span>
                        <input type="checkbox" class="toggle toggle-primary" v-model="form.is_active" />
                    </label>
                </div>

                <div v-if="viewStore.isAdminViewMode" class="pb-2">
                    <label for="owner_email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Владелец</label>
                    <div class="dark:text-gray-300 mt-1 block w-full">
                        {{payment_detail.owner_email}}
                    </div>
                </div>

                <SaveButton
                    :disabled="form.processing"
                    :saved="form.recentlySuccessful"
                ></SaveButton>
            </form>
        </SecondaryPageSection>
    </div>
</template>
