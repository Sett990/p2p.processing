<script setup>
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import SaveButton from "@/Components/Form/SaveButton.vue";
import {useForm, usePage} from "@inertiajs/vue3";
import {ref} from "vue";
import CopyUUID from "@/Components/CopyUUID.vue";
import {useViewStore} from "@/store/view.js";
import Select from "@/Components/Select.vue";
import Gateways from "@/Pages/Merchant/Tabs/Partials/Gateways.vue";
import Multiselect from "@/Components/Form/Multiselect.vue";
import DatepickerInput from "@/Pages/Merchant/Tabs/Partials/DatepickerInput.vue";

const viewStore = useViewStore();

const merchant = ref(usePage().props.merchant);
const markets = ref(usePage().props.markets);
const categories = ref(usePage().props.categories);
const currencies = ref(usePage().props.currencies || []);

// Состояние для выбора валюты
const selectedCurrency = ref('');

// Состояние для отображения минимальных сумм сделок по валютам
const minOrderAmounts = ref(merchant.value.min_order_amounts || {});

const formCallback = useForm({
    callback_url: merchant.value.callback_url,
});

const formSettings = useForm({
    market: merchant.value.market,
    categories: merchant.value.categories,
    max_order_wait_time: merchant.value.max_order_wait_time,
    min_order_amounts: minOrderAmounts.value
});

const formStatus = useForm({});

const formResendCallback = useForm({
    start_date: '',
    end_date: '',
});

// Добавление минимальной суммы для валюты
const addMinOrderAmount = () => {
    if (!selectedCurrency.value) return;

    // Если не существует, добавляем со значением по умолчанию
    if (!minOrderAmounts.value[selectedCurrency.value]) {
        minOrderAmounts.value[selectedCurrency.value] = "";
    }

    // Сбрасываем выбранную валюту
    selectedCurrency.value = '';
};

// Удаление минимальной суммы для валюты
const removeMinOrderAmount = (currency) => {
    if (minOrderAmounts.value[currency] || minOrderAmounts.value[currency] === "") {
        const updatedAmounts = {...minOrderAmounts.value};
        delete updatedAmounts[currency];
        // Принудительное обновление реактивной переменной
        minOrderAmounts.value = updatedAmounts;
    }
};

// Фильтрация доступных валют (исключаем уже добавленные)
const availableCurrencies = () => {
    return currencies.value.filter(
        currency => !Object.keys(minOrderAmounts.value).includes(currency.value)
    );
};

const submitCallback = () => {
    formCallback.patch(route('merchants.callback.update', merchant.value.id), {
        preserveScroll: true,
    });
};

const submitSettings = () => {
    formSettings
        .transform((data) => {
            data.min_order_amounts = minOrderAmounts.value;
            return data;
        })
        .patch(route('admin.merchants.settings.update', merchant.value.id), {
            preserveScroll: true,
            onSuccess: (result) => {
                merchant.value = result.props.merchant;
                minOrderAmounts.value = merchant.value.min_order_amounts || {};
            },
        });
};

const submitBan = () => {
    formStatus.patch(route('admin.merchants.ban', merchant.value.id), {
        preserveScroll: true,
        onSuccess: (result) => {
            merchant.value = result.props.merchant;
        },
    });
};
const submitUnban = () => {
    formStatus.patch(route('admin.merchants.unban', merchant.value.id), {
        preserveScroll: true,
        onSuccess: (result) => {
            merchant.value = result.props.merchant;
        },
    });
};

const submitValidated = () => {
    formStatus.patch(route('admin.merchants.validated', merchant.value.id), {
        preserveScroll: true,
        onSuccess: (result) => {
            merchant.value = result.props.merchant;
        },
    });
};

const submitResendCallback = () => {
    formResendCallback.post(route('admin.merchants.resend-callback', merchant.value.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="space-y-6">
        <div class="mb-6">
            <div class="gap-8 grid grid-cols-1 2xl:grid-cols-7 xl:grid-cols-5">
                <div class="2xl:col-span-3 xl:col-span-2 space-y-6">
                    <div>
                        <h3 class="mb-3 text-xl font-medium text-gray-900 dark:text-white">Магазин</h3>
                        <ul class="text-sm font-medium shadow-md text-gray-900 bg-white rounded-plate dark:bg-gray-800 dark:text-white">
                            <li class="w-full sm:px-6 px-5 py-3 border-b border-gray-200 gap-5 rounded-t-xl dark:border-gray-700 flex justify-between">
                                <span class="text-gray-900 dark:text-gray-200">Название</span>
                                <span class="text-gray-500 dark:text-gray-400 truncate break-all">
                        {{ merchant.name }}
                    </span>
                            </li>
                            <li class="w-full sm:px-6 px-5 py-3 border-b border-gray-200 gap-5 rounded-t-xl dark:border-gray-700 flex justify-between">
                                <span class="text-gray-900 dark:text-gray-200 col-span-2">Описание</span>
                                <span class="text-gray-500 dark:text-gray-400 col-span-3 text-right break-all">
                        {{ merchant.description }}
                    </span>
                            </li>
                            <li class="w-full sm:px-6 px-5 py-3 border-b border-gray-200 gap-5 rounded-t-xl dark:border-gray-700 flex justify-between">
                                <span class="text-gray-900 dark:text-gray-200">Домен</span>
                                <span class="text-gray-500 dark:text-gray-400 break-all">
                        {{ merchant.domain }}
                    </span>
                            </li>
                            <li class="w-full sm:px-6 px-5 py-3 border-b border-gray-200 rounded-t-xl dark:border-gray-700 flex justify-between">
                                <span class="dark:text-gray-200">Статус</span>
                                <span>
                        <span v-if="merchant.active" class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-xl dark:bg-green-900 dark:text-green-300">Активен</span>
                        <span v-else class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-xl dark:bg-red-900 dark:text-red-300">Остановлен</span>
                    </span>
                            </li>
                            <li v-if="viewStore.isAdminViewMode" class="w-full sm:px-6 px-5 py-3 border-b border-gray-200 rounded-t-xl dark:border-gray-700 flex justify-between">
                                <span class="text-gray-900 dark:text-gray-200">Владелец</span>
                                <span class="text-gray-500 dark:text-gray-400">{{ merchant.owner.email }}</span>
                            </li>
                            <li class="w-full sm:px-6 px-5 py-3 rounded-b-xl flex justify-between">
                                <span class="text-gray-900 dark:text-gray-200">Merchant ID</span>
                                <span class="text-gray-500 dark:text-gray-400">
                        <CopyUUID :text="merchant.uuid"></CopyUUID>
                    </span>
                            </li>
                        </ul>
                    </div>
                    <div v-if="viewStore.isAdminViewMode">
                        <h3 class="text-xl font-medium mb-3">Модерация</h3>
                        <div class="p-5 sm:p-6 bg-base-100 shadow rounded-plate">
                            <p class="mb-3 text-sm opacity-70">
                                Разрешите работу мерчанта или заблокируйте его.
                            </p>
                            <form @submit.prevent="submitCallback">
                                <div class="flex items-center justify-center">
                                    <h1 class="opacity-70 text-sm mr-3">Текущий статус:</h1>
                                    <div class="flex items-center text-nowrap">
                                        <template v-if="! merchant.validated_at">
                                            <span class="badge badge-warning">На модерации</span>
                                        </template>
                                        <template v-else-if="merchant.banned_at">
                                            <span class="badge badge-error">Заблокирован</span>
                                        </template>
                                        <template v-else-if="merchant.active">
                                            <span class="badge badge-success">Включен</span>
                                        </template>
                                        <template v-else>
                                            <span class="badge badge-error">Выключен</span>
                                        </template>
                                    </div>
                                </div>
                                <div class="flex justify-center mt-3 gap-2">
                                    <button
                                        @click="submitValidated"
                                        v-if="! merchant.validated_at"
                                        type="button"
                                        class="btn btn-success btn-sm"
                                    >
                                        Разрешить
                                    </button>
                                    <button
                                        @click="submitUnban"
                                        v-if="merchant.banned_at"
                                        type="button"
                                        class="btn btn-primary btn-sm"
                                    >
                                        Разблокировать
                                    </button>
                                    <button
                                        @click="submitBan"
                                        v-else
                                        type="button"
                                        class="btn btn-error btn-sm"
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
                        <h3 class="text-xl font-medium text-gray-900 dark:text-white mb-3">Обработчик платежей</h3>
                        <div class="p-5 sm:p-6 bg-white shadow-md rounded-plate dark:bg-gray-800">
                            <p class="mb-5 text-sm font-medium text-gray-500 dark:text-gray-300">
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
                                        @input="formCallback.clearErrors('callback_url')"
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
                        <h3 class="text-xl font-medium text-gray-900 dark:text-white mb-3">Настройки для администратора</h3>
                        <div class="p-5 sm:p-6 bg-white shadow-md rounded-plate dark:bg-gray-800">
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
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
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
                                                :items="availableCurrencies()"
                                                value="value"
                                                name="name"
                                                default_title="Выберите валюту"
                                                :required="false"
                                            ></Select>
                                        </div>
                                        <button
                                            type="button"
                                            class="btn btn-primary btn-sm"
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
                                            class="flex items-center gap-2 p-2 rounded-plate bg-base-200"
                                        >
                                            <div class="flex-1">
                                                <div class="text-sm font-medium mb-1">
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
                                                        class="btn btn-ghost btn-sm text-error"
                                                        @click.prevent="removeMinOrderAmount(currency)"
                                                    >
                                                        Удалить
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <p v-else class="mt-1 text-sm opacity-70">
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
                        <h3 class="text-xl font-medium text-gray-900 dark:text-white mb-3">Повторная отправка callback</h3>
                        <div class="p-5 sm:p-6 bg-white shadow-md rounded-plate dark:bg-gray-800">
                            <p class="mb-5 text-sm font-medium text-gray-500 dark:text-gray-300">
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
                                            @change="formResendCallback.clearErrors('start_date')"
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
                                            @change="formResendCallback.clearErrors('end_date')"
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

        <Gateways/>
    </div>
</template>

<style scoped>

</style>
