<script setup>
import InputLabel from "@/Components/InputLabel.vue";
import InputHelper from "@/Components/InputHelper.vue";
import TextInput from "@/Components/TextInput.vue";
import { onMounted, ref, computed } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { useViewStore } from "@/store/view.js";

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
        for (const key in gatewaySettings.value) {
            gatewaySettings.value[key]["custom_gateway_commission"] = normalizeValue(
                macros.value.commission,
                0,
                100
            );
        }
    }
    if (type === "reservation_time") {
        for (const key in gatewaySettings.value) {
            gatewaySettings.value[key]["custom_gateway_reservation_time"] =
                normalizeValue(macros.value.reservation_time);
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
            <h3 class="text-xl font-medium text-gray-900 dark:text-white">Методы</h3>
            <div class="flex items-center">
                <button
                    v-if="gatewayEditMode === false"
                    @click.prevent="gatewayEditMode = true"
                    type="button"
                    class="px-2 py-1 text-xs shadow font-medium text-blue-700 hover:text-white border border-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-xl text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-500 dark:focus:ring-blue-800"
                >
                    Изменить
                </button>
                <button
                    v-else
                    @click.prevent="submitGatewaySettings(); gatewayEditMode = false"
                    type="button"
                    class="px-2 py-1 text-xs shadow font-medium text-green-700 hover:text-white border border-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 rounded-xl text-center dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-800"
                >
                    Сохранить
                </button>
            </div>
        </div>
        <div
            v-if="gatewayEditMode === true && viewStore.isAdminViewMode"
            class="p-5 sm:p-8 w-full bg-white dark:bg-gray-800 shadow rounded-plate"
        >
            <div>
                <header>
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
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
            class="bg-white text-xs shadow-md font-semibold py-1.5 px-3.5 dark:text-gray-200 rounded-xl dark:bg-gray-800"
        >
          {{ currency.toUpperCase() }}
        </span>
            </div>
            <div class="mt-3 gap-3 grid 2xl:grid-cols-4 xl:grid-cols-2">
                <div
                    class="rounded-plate bg-gray-200 dark:bg-gray-700 shadow-md"
                    v-for="gateway in gateways"
                >
                    <div
                        class="rounded-plate bg-white shadow text-sm font-semibold py-2 px-3 dark:bg-gray-800"
                    >
                        <div class="flex justify-between items-center">
                            <div :class="gatewayEditMode ? 'w-2/5' : 'w-3/5'">
                                <div
                                    class="truncate"
                                    :class="
                                        getSetting(gateway.id, 'active')
                                        ? 'text-gray-900 dark:text-gray-200'
                                        : 'text-red-700 dark:text-red-400'
                                      "
                                >
                                    {{ gateway.original_name }}
                                </div>
                            </div>
                            <div
                                class="text-gray-900 dark:text-gray-200 text-xl flex justify-between items-end gap-2"
                                :class="
                                        getSetting(gateway.id, 'active')
                                        ? 'text-gray-900 dark:text-gray-200'
                                        : 'text-red-700 dark:text-red-400'
                                    "
                                 >
                                <div class="flex items-center gap-2">
                                    <template
                                        v-if="
                                          getSetting(gateway.id, 'custom_gateway_commission') > 0 ||
                                          getSetting(gateway.id, 'custom_gateway_commission') === 0
                                        "
                                    >
                                        <div class="text-sm text-green-500 line-through">
                                            {{ gateway.order_service_commission_rate }}%
                                        </div>
                                        <div>
                                            {{ getSetting(gateway.id, "custom_gateway_commission") }}%
                                        </div>
                                    </template>
                                    <template v-else>
                                        <div>{{ gateway.order_service_commission_rate }}%</div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        v-if="gatewayEditMode === true"
                        class="py-2 px-4 flex justify-between items-center"
                    >
                        <span class="text-xs text-gray-700 dark:text-gray-400">Включен</span>
                        <label class="inline-flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                value=""
                                class="sr-only peer"
                                :checked="getSetting(gateway.id, 'active')"
                                @change="setSetting(gateway.id, 'active', $event.target.checked)"
                            />
                            <div
                                class="relative w-7 h-4 bg-gray-400 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"
                            ></div>
                        </label>
                    </div>
                    <div
                        v-if="viewStore.isAdminViewMode && gatewayEditMode === true"
                        class="py-2 px-4 flex justify-between items-center"
                    >
                        <span class="text-xs text-gray-700 dark:text-gray-400">Комиссия</span>
                        <input
                            type="text"
                            class="w-16 p-0 m-0 bg-transparent text-center dark:text-gray-200 text-base focus:ring-0 border-0 border-b border-gray-400"
                            :value="getSetting(gateway.id, 'custom_gateway_commission')"
                            @input="setSetting(gateway.id, 'custom_gateway_commission', $event.target.value)"
                        />
                    </div>
                    <div
                        v-if="viewStore.isAdminViewMode && gatewayEditMode === true"
                        class="py-2 px-4 flex justify-between items-center"
                    >
                        <span class="text-xs text-gray-700 dark:text-gray-400">Время на сделку</span>
                        <input
                            type="text"
                            class="w-16 p-0 m-0 bg-transparent text-center dark:text-gray-200 text-base focus:ring-0 border-0 border-b border-gray-400"
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
