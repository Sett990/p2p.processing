<script setup>
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import SaveButton from "@/Components/Form/SaveButton.vue";
import {usePage} from "@inertiajs/vue3";
import {computed, reactive, ref, watch} from "vue";
import CopyUUID from "@/Components/CopyUUID.vue";
import {useViewStore} from "@/store/view.js";
import Select from "@/Components/Select.vue";
import Gateways from "@/Pages/Merchant/Tabs/Partials/Gateways.vue";
import Multiselect from "@/Components/Form/Multiselect.vue";
import DatepickerInput from "@/Pages/Merchant/Tabs/Partials/DatepickerInput.vue";

const viewStore = useViewStore();
const emit = defineEmits(['updated']);

const props = defineProps({
    merchant: {
        type: Object,
        default: null,
    },
    markets: {
        type: Array,
        default: () => [],
    },
    categories: {
        type: Array,
        default: () => [],
    },
    currencies: {
        type: Array,
        default: () => [],
    },
    gatewaySettings: {
        type: [Object, Array],
        default: () => ({}),
    },
    paymentGateways: {
        type: Object,
        default: () => ({ data: [] }),
    },
});

const page = usePage();

const deepClone = (value, fallback = undefined) => {
    if (value === undefined || value === null) {
        return fallback ?? null;
    }

    try {
        return JSON.parse(JSON.stringify(value));
    } catch (e) {
        return fallback ?? value;
    }
};

const merchant = ref(deepClone(props.merchant ?? page?.props?.merchant ?? null));
const markets = ref(deepClone(props.markets?.length ? props.markets : page?.props?.markets ?? []));
const categories = ref(deepClone(props.categories?.length ? props.categories : page?.props?.categories ?? []));
const currencies = ref(deepClone(props.currencies?.length ? props.currencies : page?.props?.currencies ?? []));
const gatewaySettings = ref(deepClone(
    Object.keys(props.gatewaySettings ?? {}).length ? props.gatewaySettings : page?.props?.gatewaySettings ?? {}
));
const paymentGateways = ref(deepClone(
    (props.paymentGateways && Object.keys(props.paymentGateways).length)
        ? props.paymentGateways
        : page?.props?.paymentGateways ?? { data: [] }
));

const selectedCurrency = ref('');
const minOrderAmounts = ref(merchant.value?.min_order_amounts ? {...merchant.value.min_order_amounts} : {});

const formCallback = reactive({
    callback_url: merchant.value?.callback_url ?? '',
    errors: {},
    processing: false,
    recentlySuccessful: false,
    _successTimer: null,
});

const formSettings = reactive({
    market: merchant.value?.market ?? '',
    categories: merchant.value?.categories ?? [],
    max_order_wait_time: merchant.value?.max_order_wait_time ?? null,
    errors: {},
    processing: false,
    recentlySuccessful: false,
    _successTimer: null,
});

const formStatus = reactive({
    processing: false,
});

const formResendCallback = reactive({
    start_date: '',
    end_date: '',
    errors: {},
    processing: false,
    recentlySuccessful: false,
    _successTimer: null,
});

const availableCurrencies = computed(() => {
    return currencies.value.filter(
        (currency) => !Object.keys(minOrderAmounts.value || {}).includes(currency.value)
    );
});

const resetFormsFromMerchant = (value) => {
    if (!value) {
        return;
    }

    formCallback.callback_url = value.callback_url ?? '';
    formSettings.market = value.market ?? '';
    formSettings.categories = value.categories ?? [];
    formSettings.max_order_wait_time = value.max_order_wait_time ?? null;
    minOrderAmounts.value = value.min_order_amounts ? {...value.min_order_amounts} : {};
};

watch(
    () => props.merchant,
    (value) => {
        if (value !== undefined) {
            merchant.value = value ? deepClone(value) : null;
            resetFormsFromMerchant(merchant.value);
        }
    },
    { immediate: false }
);

watch(
    () => page.props?.merchant,
    (value) => {
        if (!props.merchant && value) {
            merchant.value = deepClone(value);
            resetFormsFromMerchant(merchant.value);
        }
    },
    { immediate: true }
);

watch(
    () => props.gatewaySettings,
    (value) => {
        if (value !== undefined) {
            gatewaySettings.value = deepClone(value ?? {}, {});
        }
    },
    { immediate: false }
);

watch(
    () => page.props?.gatewaySettings,
    (value) => {
        if (value !== undefined && Object.keys(props.gatewaySettings ?? {}).length === 0) {
            gatewaySettings.value = deepClone(value ?? {}, {});
        }
    },
    { immediate: true }
);

watch(
    () => props.paymentGateways,
    (value) => {
        if (value !== undefined) {
            paymentGateways.value = deepClone(value ?? { data: [] }, { data: [] });
        }
    },
    { immediate: false }
);

watch(
    () => page.props?.paymentGateways,
    (value) => {
        if (value !== undefined && (!props.paymentGateways || !Object.keys(props.paymentGateways).length)) {
            paymentGateways.value = deepClone(value ?? { data: [] }, { data: [] });
        }
    },
    { immediate: true }
);

watch(
    () => props.markets,
    (value) => {
        if (value !== undefined) {
            markets.value = deepClone(value ?? [], []);
        }
    },
    { immediate: false }
);

watch(
    () => page.props?.markets,
    (value) => {
        if (value !== undefined && (!props.markets || !props.markets.length)) {
            markets.value = deepClone(value ?? [], []);
        }
    },
    { immediate: true }
);

watch(
    () => props.categories,
    (value) => {
        if (value !== undefined) {
            categories.value = deepClone(value ?? [], []);
        }
    },
    { immediate: false }
);

watch(
    () => page.props?.categories,
    (value) => {
        if (value !== undefined && (!props.categories || !props.categories.length)) {
            categories.value = deepClone(value ?? [], []);
        }
    },
    { immediate: true }
);

watch(
    () => props.currencies,
    (value) => {
        if (value !== undefined) {
            currencies.value = deepClone(value ?? [], []);
        }
    },
    { immediate: false }
);

watch(
    () => page.props?.currencies,
    (value) => {
        if (value !== undefined && (!props.currencies || !props.currencies.length)) {
            currencies.value = deepClone(value ?? [], []);
        }
    },
    { immediate: true }
);

const markRecentlySuccessful = (form) => {
    if (!form) {
        return;
    }

    if (form._successTimer) {
        clearTimeout(form._successTimer);
    }

    form.recentlySuccessful = true;
    form._successTimer = setTimeout(() => {
        form.recentlySuccessful = false;
        form._successTimer = null;
    }, 2000);
};

const clearFormError = (form, field) => {
    if (form?.errors && Object.prototype.hasOwnProperty.call(form.errors, field)) {
        const errors = {...form.errors};
        delete errors[field];
        form.errors = errors;
    }
};

const handleValidationError = (error, form) => {
    if (error.response?.data?.errors) {
        form.errors = error.response.data.errors;
    }
};

const submitCallback = () => {
    if (!merchant.value || formCallback.processing) {
        return;
    }

    formCallback.processing = true;
    formCallback.errors = {};

    axios.patch(route('merchants.callback.update', merchant.value.id), {
        callback_url: formCallback.callback_url,
    }, {
        headers: {Accept: 'application/json'},
    }).then(({data}) => {
        if (data?.merchant) {
            merchant.value = data.merchant;
            resetFormsFromMerchant(merchant.value);
            emit('updated', merchant.value);
        }
        markRecentlySuccessful(formCallback);
    }).catch((error) => {
        handleValidationError(error, formCallback);
    }).finally(() => {
        formCallback.processing = false;
    });
};

const submitSettings = () => {
    if (!merchant.value || formSettings.processing) {
        return;
    }

    formSettings.processing = true;
    formSettings.errors = {};

    axios.patch(route('admin.merchants.settings.update', merchant.value.id), {
        market: formSettings.market,
        categories: formSettings.categories,
        max_order_wait_time: formSettings.max_order_wait_time,
        min_order_amounts: minOrderAmounts.value,
    }, {
        headers: {Accept: 'application/json'},
    }).then(({data}) => {
        if (data?.merchant) {
            merchant.value = data.merchant;
            resetFormsFromMerchant(merchant.value);
            emit('updated', merchant.value);
        }
        markRecentlySuccessful(formSettings);
    }).catch((error) => {
        handleValidationError(error, formSettings);
    }).finally(() => {
        formSettings.processing = false;
    });
};

const performStatusAction = (routeName) => {
    if (!merchant.value || formStatus.processing) {
        return;
    }

    formStatus.processing = true;

    axios.patch(route(routeName, merchant.value.id), {}, {
        headers: {Accept: 'application/json'},
    }).then(({data}) => {
        if (data?.merchant) {
            merchant.value = data.merchant;
            resetFormsFromMerchant(merchant.value);
            emit('updated', merchant.value);
        }
    }).finally(() => {
        formStatus.processing = false;
    });
};

const submitBan = () => performStatusAction('admin.merchants.ban');
const submitUnban = () => performStatusAction('admin.merchants.unban');
const submitValidated = () => performStatusAction('admin.merchants.validated');

const submitResendCallback = () => {
    if (!merchant.value || formResendCallback.processing) {
        return;
    }

    formResendCallback.processing = true;
    formResendCallback.errors = {};

    axios.post(route('admin.merchants.resend-callback', merchant.value.id), {
        start_date: formResendCallback.start_date,
        end_date: formResendCallback.end_date,
    }, {
        headers: {Accept: 'application/json'},
    }).then(() => {
        markRecentlySuccessful(formResendCallback);
    }).catch((error) => {
        handleValidationError(error, formResendCallback);
    }).finally(() => {
        formResendCallback.processing = false;
    });
};

const addMinOrderAmount = () => {
    if (!selectedCurrency.value) {
        return;
    }

    if (!minOrderAmounts.value[selectedCurrency.value]) {
        minOrderAmounts.value = {
            ...minOrderAmounts.value,
            [selectedCurrency.value]: "",
        };
    }

    selectedCurrency.value = '';
};

const removeMinOrderAmount = (currency) => {
    if (minOrderAmounts.value[currency] !== undefined) {
        const updated = {...minOrderAmounts.value};
        delete updated[currency];
        minOrderAmounts.value = updated;
    }
};

const handleGatewaySettingsUpdated = (payload) => {
    if (payload?.gateway_settings) {
        gatewaySettings.value = payload.gateway_settings;
    }
    if (payload?.merchant) {
        merchant.value = payload.merchant;
        resetFormsFromMerchant(merchant.value);
        emit('updated', merchant.value);
    }
};
</script>

<template>
    <div class="space-y-6">
        <div class="mb-6">
            <div class="gap-8 grid grid-cols-1 2xl:grid-cols-7 xl:grid-cols-5">
                <div class="2xl:col-span-3 xl:col-span-2 space-y-6">
                    <div>
                        <h3 class="mb-3 text-xl font-medium text-base-content">Магазин</h3>
                        <ul class="text-sm font-medium shadow bg-base-100 rounded-box">
                            <li class="w-full sm:px-6 px-5 py-3 border-b border-base-300 gap-5 rounded-t-xl flex justify-between">
                                <span class="text-base-content">Название</span>
                                <span class="text-base-content/70 truncate break-all">
                        {{ merchant.name }}
                    </span>
                            </li>
                            <li class="w-full sm:px-6 px-5 py-3 border-b border-base-300 gap-5 rounded-t-xl flex justify-between">
                                <span class="text-base-content col-span-2">Описание</span>
                                <span class="text-base-content/70 col-span-3 text-right break-all">
                        {{ merchant.description }}
                    </span>
                            </li>
                            <li class="w-full sm:px-6 px-5 py-3 border-b border-base-300 gap-5 rounded-t-xl flex justify-between">
                                <span class="text-base-content">Домен</span>
                                <span class="text-base-content/70 break-all">
                        {{ merchant.domain }}
                    </span>
                            </li>
                            <li class="w-full sm:px-6 px-5 py-3 border-b border-base-300 rounded-t-xl flex justify-between">
                                <span class="text-base-content">Статус</span>
                                <span>
                        <span v-if="merchant.active" class="badge badge-sm badge-success">Активен</span>
                        <span v-else class="badge badge-sm badge-error">Остановлен</span>
                    </span>
                            </li>
                            <li v-if="viewStore.isAdminViewMode" class="w-full sm:px-6 px-5 py-3 border-b border-base-300 rounded-t-xl flex justify-between">
                                <span class="text-base-content">Владелец</span>
                                <span class="text-base-content/70">{{ merchant.owner.email }}</span>
                            </li>
                            <li class="w-full sm:px-6 px-5 py-3 rounded-b-xl flex justify-between">
                                <span class="text-base-content">Merchant ID</span>
                                <span class="text-base-content/70">
                        <CopyUUID :text="merchant.uuid"></CopyUUID>
                    </span>
                            </li>
                        </ul>
                    </div>
                    <div v-if="viewStore.isAdminViewMode">
                        <h3 class="text-xl font-medium text-base-content mb-3">Модерация</h3>
                        <div class="p-5 sm:p-6 bg-base-100 shadow rounded-box">
                            <p class="mb-3 text-sm font-medium text-base-content/70">
                                Разрешите работу мерчанта или заблокируйте его.
                            </p>
                            <form @submit.prevent="submitCallback">
                                <div class="flex items-center justify-center">
                                    <h1 class="text-base-content/70 text-sm mr-3">Текущий статус:</h1>
                                    <div class="flex items-center text-nowrap text-base-content">
                                        <template v-if="! merchant.validated_at">
                                            <div class="h-2.5 w-2.5 rounded-full bg-warning me-2"></div> На модерации
                                        </template>
                                        <template v-else-if="merchant.banned_at">
                                            <div class="h-2.5 w-2.5 rounded-full bg-error me-2"></div> Заблокирован
                                        </template>
                                        <template v-else-if="merchant.active">
                                            <div class="h-2.5 w-2.5 rounded-full bg-success me-2"></div> Включен
                                        </template>
                                        <template v-else>
                                            <div class="h-2.5 w-2.5 rounded-full bg-danger me-2"></div> Выключен
                                        </template>
                                    </div>
                                </div>
                                <div class="flex justify-center mt-3 gap-2">
                                    <button
                                        @click="submitValidated"
                                        v-if="! merchant.validated_at"
                                        type="button"
                                        class="btn btn-sm btn-success"
                                        :disabled="formStatus.processing"
                                    >
                                        Разрешить
                                    </button>
                                    <button
                                        @click="submitUnban"
                                        v-if="merchant.banned_at"
                                        type="button"
                                        class="btn btn-sm btn-primary"
                                        :disabled="formStatus.processing"
                                    >
                                        Разблокировать
                                    </button>
                                    <button
                                        @click="submitBan"
                                        v-else
                                        type="button"
                                        class="btn btn-sm btn-error"
                                        :disabled="formStatus.processing"
                                    >
                                        Заблокировать
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="2xl:col-span-4 xl:col-span-3 space-y-6">
                    <div>
                        <h3 class="text-xl font-medium text-base-content mb-3">Обработчик платежей</h3>
                        <div class="p-5 sm:p-6 bg-base-100 shadow rounded-box">
                            <p class="mb-5 text-sm font-medium text-base-content/70">
                                Установите ссылку на Ваш обработчик для получения уведомлений. По ней мы будем отправлять POST запросы о статусах платежей.
                            </p>
                            <form class="space-y-4" @submit.prevent="submitCallback">
                                <div>
                                    <InputLabel
                                        for="callback_url"
                                        value="Укажите ссылку"
                                        :error="!!formCallback.errors.callback_url"
                                    />

                                    <TextInput
                                        id="callback_url"
                                        v-model="formCallback.callback_url"
                                        type="text"
                                        class="mt-1 block w-full"
                                        placeholder="https://example.com/callback"
                                        :error="!!formCallback.errors.callback_url"
                                    @input="clearFormError(formCallback, 'callback_url')"
                                    />

                                    <InputError :message="formCallback.errors.callback_url" class="mt-2" />
                                </div>

                                <SaveButton
                                    :disabled="formCallback.processing"
                                    :saved="formCallback.recentlySuccessful"
                                ></SaveButton>
                            </form>
                        </div>
                    </div>
                    <div v-if="viewStore.isAdminViewMode">
                        <h3 class="text-xl font-medium text-base-content mb-3">Настройки для администратора</h3>
                        <div class="p-5 sm:p-6 bg-base-100 shadow rounded-box">
                            <form class="space-y-4" @submit.prevent="submitSettings">
                                <div>
                                    <InputLabel
                                        for="payment_gateway_id"
                                        value="Источник курсов (маркет)"
                                        :error="!!formSettings.errors.market"
                                        class="mb-1"
                                    />
                                    <Select
                                        id="market"
                                        v-model="formSettings.market"
                                        :error="!!formSettings.errors.market"
                                        :items="markets"
                                        value="value"
                                        name="name"
                                        default_title="Выберите маркет"
                                        @change="formSettings.clearErrors('market');"
                                    ></Select>

                                    <InputError :message="formSettings.errors.market" class="mt-2" />
                                </div>

<!--                                <div>
                                    <InputLabel
                                        for="categories"
                                        value="Категории"
                                        :error="!!formSettings.errors.categories"
                                        class="mb-1"
                                    />
                                    <Multiselect
                                        id="categories"
                                        v-model="formSettings.categories"
                                        :options="categories"
                                        labelKey="name"
                                        valueKey="id"
                                    />
                                    <InputError :message="formSettings.errors.categories" class="mt-2" />
                                </div>-->

                                <div>
                                    <InputLabel
                                        for="max_order_wait_time"
                                        value="Максимальное время ожидания выдачи реквизита (мс)"
                                        :error="!!formSettings.errors.max_order_wait_time"
                                        class="mb-1"
                                    />
                                    <TextInput
                                        id="max_order_wait_time"
                                        v-model="formSettings.max_order_wait_time"
                                        type="number"
                                        min="1"
                                        placeholder="Введите время в миллисекундах (1 сек = 1000 мс)"
                                        class="mt-1 block w-full"
                                        :error="!!formSettings.errors.max_order_wait_time"
                                        @input="formSettings.clearErrors('max_order_wait_time')"
                                    />
                                    <p class="mt-1 text-sm text-base-content/70">
                                        Примеры: 3000 мс = 3 секунды, 60000 мс = 1 минута
                                    </p>
                                    <InputError :message="formSettings.errors.max_order_wait_time" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel
                                        value="Минимальная сумма сделки по валютам"
                                        class="mb-1"
                                    />

                                    <!-- Выбор валюты -->
                                    <div class="flex gap-2 mb-2">
                                        <div class="w-full">
                                            <Select
                                                v-model="selectedCurrency"
                                            :items="availableCurrencies"
                                                value="value"
                                                name="name"
                                                default_title="Выберите валюту"
                                                :required="false"
                                            ></Select>
                                        </div>
                                        <button
                                            type="button"
                                            class="btn btn-primary"
                                            @click="addMinOrderAmount"
                                            :disabled="!selectedCurrency"
                                        >
                                            Добавить
                                        </button>
                                    </div>

                                    <!-- Список минимальных сумм по валютам -->
                                    <div v-if="Object.keys(minOrderAmounts).length > 0" class="mt-3 space-y-2">
                                        <div
                                            v-for="(amount, currency) in minOrderAmounts"
                                            :key="currency"
                                            class="flex items-center gap-2 p-2 rounded-lg bg-base-200"
                                        >
                                            <div class="flex-1">
                                                <div class="text-sm font-medium text-base-content mb-1">
                                                    {{ currencies.find(c => c.value === currency)?.name || currency.toUpperCase() }}
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <TextInput
                                                        v-model="minOrderAmounts[currency]"
                                                        type="number"
                                                        min="0"
                                                        step="0.01"
                                                        placeholder="Мин. сумма"
                                                        class="block w-full"
                                                    />

                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-ghost btn-square text-error"
                                                        @click.prevent="removeMinOrderAmount(currency)"
                                                    >
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <p v-else class="mt-1 text-sm text-base-content/70">
                                        Нет настроенных минимальных сумм. Добавьте валюту для настройки.
                                    </p>
                                </div>

                                <SaveButton
                                    :disabled="formSettings.processing"
                                    :saved="formSettings.recentlySuccessful"
                                ></SaveButton>
                            </form>
                        </div>
                    </div>

                    <div v-if="viewStore.isAdminViewMode" class="mt-6">
                        <h3 class="text-xl font-medium text-base-content mb-3">Повторная отправка callback</h3>
                        <div class="p-5 sm:p-6 bg-base-100 shadow rounded-box">
                            <p class="mb-5 text-sm font-medium text-base-content/70">
                                Выберите период дат для повторной отправки callback по всем сделкам мерчанта за указанный период.
                            </p>
                            <form class="space-y-4" @submit.prevent="submitResendCallback">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel
                                            for="start_date"
                                            value="Дата начала"
                                            :error="!!formResendCallback.errors.start_date"
                                        />
                                        <DatepickerInput
                                            id="start_date"
                                            v-model="formResendCallback.start_date"
                                            placeholder="дд/мм/гггг"
                                            :error="!!formResendCallback.errors.start_date"
                                            @change="clearFormError(formResendCallback, 'start_date')"
                                        />
                                        <InputError :message="formResendCallback.errors.start_date" class="mt-2" />
                                    </div>
                                    <div>
                                        <InputLabel
                                            for="end_date"
                                            value="Дата окончания"
                                            :error="!!formResendCallback.errors.end_date"
                                        />
                                        <DatepickerInput
                                            id="end_date"
                                            v-model="formResendCallback.end_date"
                                            placeholder="дд/мм/гггг"
                                            :error="!!formResendCallback.errors.end_date"
                                            @change="clearFormError(formResendCallback, 'end_date')"
                                        />
                                        <InputError :message="formResendCallback.errors.end_date" class="mt-2" />
                                    </div>
                                </div>
                                <InputError :message="formResendCallback.errors.date_range" class="mt-2" />
                                <SaveButton
                                    :disabled="formResendCallback.processing"
                                    :saved="formResendCallback.recentlySuccessful"
                                >
                                    Отправить callback
                                </SaveButton>
                            </form>
                        </div>
                    </div>
<!--                    <ExchangeRateMarkup
                        v-if="viewStore.isAdminViewMode"
                    />-->
                </div>
            </div>
        </div>

        <Gateways
            v-if="paymentGateways"
            :merchant-id="merchant?.id"
            :gateway-settings="gatewaySettings"
            :payment-gateways="paymentGateways"
            :is-admin="viewStore.isAdminViewMode"
            @updated="handleGatewaySettingsUpdated"
        />
    </div>
</template>

<style scoped>

</style>
