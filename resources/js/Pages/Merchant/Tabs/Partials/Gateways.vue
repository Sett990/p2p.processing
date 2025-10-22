<script setup>
import InputLabel from "@/Components/InputLabel.vue";
import InputHelper from "@/Components/InputHelper.vue";
import TextInput from "@/Components/TextInput.vue";
import { onMounted, ref, computed } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { useViewStore } from "@/store/view.js";
import GatewayLogo from "@/Components/GatewayLogo.vue";

const viewStore = useViewStore();

const merchant = ref(usePage().props.merchant);
const paymentGateways = usePage().props.paymentGateways;
const gatewaySettings = ref(Array.isArray(usePage().props.gatewaySettings) && usePage().props.gatewaySettings.length === 0 ? {} : usePage().props.gatewaySettings);

const formCommission = useForm({
    gateway_settings: null,
});

const gatewayEditMode = ref(false);

const groupedGateways = ref(null);

const macros = ref({
    commission: null,
    reservation_time: null,
});

const getSetting = (gatewayId, settingName) => {
    if (!gatewaySettings.value[gatewayId]) {
        if (settingName === 'active') {
            return true;
        }
        return null;
    }

    if (gatewaySettings.value[gatewayId][settingName] === undefined && settingName === 'active') {
        return true;
    }

    return gatewaySettings.value[gatewayId][settingName] ?? null;
};

const setSetting = (gatewayId, settingName, value) => {
    if (!gatewaySettings.value[gatewayId]) {
        gatewaySettings.value[gatewayId] = {};
    }

    if (settingName === "custom_gateway_commission") {
        value = normalizeValue(value, 0, 100);
    }

    if (settingName === "custom_gateway_reservation_time") {
        value = normalizeValue(value, 1, 10000);
    }

    gatewaySettings.value[gatewayId][settingName] = value;
};

const submitGatewaySettings = () => {
    formCommission
        .transform((data) => {
            data.gateway_settings = gatewaySettings.value;

            return data;
        })
        .patch(route("merchants.gateway-settings.update", merchant.value.id), {
            preserveScroll: true,
        });
};

const normalizeValue = (value, min = 1, max = 1000) => {
    if (value === "" || value === null || value === undefined) {
        return null;
    }

    let num = Number(value);

    if (isNaN(num)) {
        return min;
    }

    return Math.min(Math.max(num, min), max);
};

const applyMacros = (type) => {
    if (type === "commission") {
        for (const key in paymentGateways.data) {
            setSetting(paymentGateways.data[key].id, 'custom_gateway_commission', macros.value.commission)
        }
    }
    if (type === "reservation_time") {
        for (const key in paymentGateways.data) {
            setSetting(paymentGateways.data[key].id, 'custom_gateway_reservation_time', macros.value.reservation_time)
        }
    }
};

onMounted(() => {
    groupedGateways.value = Object.groupBy(
        paymentGateways.data,
        ({ currency }) => currency
    );
});
</script>

<template>
    <div class="space-y-3">
        <div class="lg:flex block justify-between items-center">
            <h3 class="text-xl font-medium">Методы</h3>
            <div class="flex items-center">
                <button
                    v-if="gatewayEditMode === false"
                    @click.prevent="gatewayEditMode = true"
                    type="button"
                    class="btn btn-outline btn-primary btn-xs"
                >
                    Изменить
                </button>
                <button
                    v-else
                    @click.prevent="submitGatewaySettings(); gatewayEditMode = false"
                    type="button"
                    class="btn btn-outline btn-success btn-xs"
                >
                    Сохранить
                </button>
            </div>
        </div>
        <div
            v-if="gatewayEditMode === true && viewStore.isAdminViewMode"
            class="p-5 sm:p-8 w-full bg-base-100 shadow rounded-plate"
        >
            <div>
                <header>
                    <h2 class="text-lg font-medium">
                        Макросы для настроек
                    </h2>
                </header>
                <form class="mt-6 space-y-6">
                    <div class="grid lg:grid-cols-2 grid-cols-1 gap-6">
                        <div>
                            <InputLabel for="commission_macros" value="Комиссия" />

                            <TextInput
                                id="commission_macros"
                                v-model="macros.commission"
                                class="mt-1 block w-full"
                                step="1"
                                @input="applyMacros('commission')"
                            />

                            <InputHelper
                                model-value="Установит у всех методов указанную комиссию."
                            ></InputHelper>
                        </div>
                        <div>
                            <InputLabel
                                for="reservation_time_macros"
                                value="Время на сделку"
                            />

                            <TextInput
                                id="reservation_time_macros"
                                v-model="macros.reservation_time"
                                class="mt-1 block w-full"
                                step="1"
                                @input="applyMacros('reservation_time')"
                            />

                            <InputHelper
                                model-value="Установит у всех методов указанную время на сделку"
                            ></InputHelper>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="mb-5" v-for="(gateways, currency) in groupedGateways">
            <div>
        <span
            class="badge badge-outline"
        >
          {{ currency.toUpperCase() }}
        </span>
            </div>
            <div class="mt-3 gap-3 grid 2xl:grid-cols-4 xl:grid-cols-2">
                <div
                    class="rounded-plate bg-base-200 shadow"
                    v-for="gateway in gateways"
                >
                    <div
                        class="rounded-plate shadow text-sm font-semibold py-2 px-3"
                        :class="
                                        getSetting(gateway.id, 'active')
                                        ? 'bg-base-100'
                                        : 'bg-error text-error-content'
                                      "
                    >
                        <div class="flex justify-between gap-2 items-center">
                            <div>
                                <GatewayLogo :img_path="gateway.logo_path" class="w-8 h-8"/>
                            </div>
                            <div :class="gatewayEditMode ? 'w-2/5' : 'w-3/5'">
                                <div class="truncate">
                                    {{ gateway.original_name }}
                                </div>
                            </div>
                            <div class="text-xl flex justify-between items-end gap-2">
                                <div class="flex items-center gap-2">
                                    <template
                                        v-if="
                                          getSetting(gateway.id, 'custom_gateway_commission') > 0 ||
                                          getSetting(gateway.id, 'custom_gateway_commission') === 0
                                        "
                                    >
                                        <div class="text-sm text-success line-through">
                                            {{ gateway.total_service_commission_rate_for_orders }}%
                                        </div>
                                        <div>
                                            {{ getSetting(gateway.id, "custom_gateway_commission") }}%
                                        </div>
                                    </template>
                                    <template v-else>
                                        <div>{{ gateway.total_service_commission_rate_for_orders }}%</div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        v-if="gatewayEditMode === true"
                        class="py-2 px-4 flex justify-between items-center"
                    >
                        <span class="text-xs">Включен</span>
                        <input type="checkbox" class="toggle toggle-primary toggle-sm" :checked="getSetting(gateway.id, 'active')" @change="setSetting(gateway.id, 'active', $event.target.checked)" />
                    </div>
                    <div
                        v-if="viewStore.isAdminViewMode && gatewayEditMode === true"
                        class="py-2 px-4 flex justify-between items-center"
                    >
                        <span class="text-xs">Комиссия</span>
                        <input
                            type="text"
                            class="input input-bordered input-sm w-20 text-center"
                            :value="getSetting(gateway.id, 'custom_gateway_commission')"
                            @input="setSetting(gateway.id, 'custom_gateway_commission', $event.target.value)"
                        />
                    </div>
                    <div
                        v-if="viewStore.isAdminViewMode && gatewayEditMode === true"
                        class="py-2 px-4 flex justify-between items-center"
                    >
                        <span class="text-xs">Время на сделку</span>
                        <input
                            type="text"
                            class="input input-bordered input-sm w-20 text-center"
                            :value="getSetting(gateway.id, 'custom_gateway_reservation_time')"
                            @input="setSetting(gateway.id, 'custom_gateway_reservation_time', $event.target.value)"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
