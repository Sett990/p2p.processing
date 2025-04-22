<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import {Head, router, useForm, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {computed, ref, watch} from "vue";
import Select from "@/Components/Select.vue";
import NumberInput from "@/Components/NumberInput.vue";
import SaveButton from "@/Components/Form/SaveButton.vue";
import SecondaryPageSection from "@/Wrappers/SecondaryPageSection.vue";
import {useViewStore} from "@/store/view.js";
import NumberInputBlock from "@/Components/Form/NumberInputBlock.vue";
import Multiselect from "@/Components/Form/Multiselect.vue";

const viewStore = useViewStore();
const payment_gateways = usePage().props.paymentGateways;
const detail_type_names = {
    'card': 'Карта',
    'phone': 'Телефон',
    'account_number': 'Номер счета',
}

// Получаем уникальные валюты из платежных методов
const availableCurrencies = computed(() => {
    const currencies = [...new Set(payment_gateways.map(pg => pg.currency))];
    return currencies.map(currency => ({
        id: currency,
        name: currency.toUpperCase()
    }));
});

const selectedDetailType = ref(null);

const form = useForm({
    name: '',
    detail: '',
    initials: '',
    is_active: true,
    daily_limit: '',
    max_pending_orders_quantity: 1,
    payment_gateway_ids: [],
    detail_type: null,
    user_device_id: 0,
    order_interval_minutes: '',
    currency: null,
});

const details = ref({
    'card': '',
    'phone': '',
    'account_number': '',
});

// Доступные типы реквизитов для выбранной валюты
const availableDetailTypes = computed(() => {
    if (!form.currency) return [];

    // Получаем уникальные типы реквизитов из платежных методов с выбранной валютой
    const types = new Set();
    payment_gateways
        .filter(pg => pg.currency.toLowerCase() === form.currency.toLowerCase())
        .forEach(pg => {
            pg.detail_types.forEach(type => types.add(type));
        });

    return Array.from(types).map(type => ({
        id: type,
        name: detail_type_names[type]
    }));
});

// Доступные платежные методы с учетом валюты и типа реквизита
const formattedPaymentGateways = computed(() => {
    if (!form.currency || !selectedDetailType.value) return [];

    const gateways = payment_gateways
        .filter(pg =>
            pg.currency.toLowerCase() === form.currency.toLowerCase() &&
            pg.detail_types.includes(selectedDetailType.value)
        )
        .map(pg => ({
            value: pg.id,
            label: pg.name
        }));

    return gateways;
});

// Следим за изменением типа реквизита
watch(selectedDetailType, (newType) => {
    // Сбрасываем выбранные платежные методы при смене типа реквизита
    form.payment_gateway_ids = [];
    form.detail_type = newType;

    // Очищаем значение детали для предыдущего типа
    if (newType) {
        Object.keys(details.value).forEach(key => {
            if (key !== newType) {
                details.value[key] = '';
            }
        });
    }
});

// Определяем, можно ли выбрать несколько платежных методов
const isMultipleGatewaysAllowed = computed(() => {
    //return selectedDetailType.value === 'phone';
    return false;
});

const submit = () => {
    form
        .transform((data) => {
            if (data.user_device_id === 0) {
                data.user_device_id = null;
            }
            data.detail_type = selectedDetailType.value;
            data.detail = details.value[data.detail_type];

            return data;
        })
        .post(route('payment-details.store'), {
            preserveScroll: true,
            onSuccess: () => {
                router.visit(route(viewStore.adminPrefix + 'payment-details.index'))
            },
        });
};

const devices = usePage().props.devices;

const formattedDevices = computed(() => {
    return devices.map(device => ({
        ...device,
        name: `${device.name}`
    }));
});

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Создание нового реквизита" />

        <SecondaryPageSection
            :back-link="route(viewStore.adminPrefix + 'payment-details.index')"
            title="Создание нового реквизита"
            description="Здесь вы можете создать новые платежные реквизиты."
        >
            <form @submit.prevent="submit" class="mt-6 space-y-6">
                <div>
                    <InputLabel
                        for="currency"
                        value="Валюта"
                        :error="!!form.errors.currency"
                        class="mb-1"
                    />
                    <Select
                        id="currency"
                        v-model="form.currency"
                        :error="!!form.errors.currency"
                        :items="availableCurrencies"
                        value="id"
                        name="name"
                        default_title="Выберите валюту"
                        :default_value="null"
                        @change="selectedDetailType = null; form.payment_gateway_ids = []"
                    ></Select>
                    <InputError :message="form.errors.currency" class="mt-2" />
                </div>

                <div v-if="form.currency">
                    <InputLabel
                        for="detail_type"
                        value="Тип реквизита"
                        :error="!!form.errors.detail_type"
                        class="mb-1"
                    />
                    <Select
                        id="detail_type"
                        v-model="selectedDetailType"
                        :error="!!form.errors.detail_type"
                        :items="availableDetailTypes"
                        value="id"
                        name="name"
                        default_title="Выберите тип реквизита"
                        :default_value="null"
                    ></Select>
                    <InputError :message="form.errors.detail_type" class="mt-2" />
                </div>

                <div v-if="selectedDetailType">
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
                    />
                    <InputError :message="form.errors.payment_gateway_ids" class="mt-2"/>
                </div>

                <template v-if="selectedDetailType">
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
                    <div>
                        <InputLabel
                            for="name"
                            value="Никнейм реквизитов"
                            :error="!!form.errors.name"
                        />

                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full"
                            :error="!!form.errors.name"
                            @input="form.clearErrors('name')"
                        />

                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>

                    <!-- Поле для ввода карты -->
                    <div v-if="selectedDetailType === 'card'">
                        <InputLabel
                            for="detail"
                            value="Карта"
                            :error="!!form.errors.detail"
                        />

                        <TextInput
                            id="detail"
                            v-model="details['card']"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="0000 0000 0000 0000"
                            :error="!!form.errors.detail"
                            @input="form.clearErrors('detail')"
                        />

                        <InputError :message="form.errors.detail" class="mt-2" />
                    </div>

                    <!-- Поле для ввода телефона -->
                    <div v-if="selectedDetailType === 'phone'">
                        <InputLabel
                            for="detail"
                            value="Номер телефона"
                            :error="!!form.errors.detail"
                        />

                        <div class="relative">
                            <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                                <span class="text-gray-500 dark:text-gray-400">+</span>
                            </div>
                            <TextInput
                                id="detail"
                                v-model="details['phone']"
                                type="text"
                                class="mt-1 block w-full ps-7"
                                :error="!!form.errors.detail"
                                @input="form.clearErrors('detail')"
                            />
                        </div>

                        <InputError :message="form.errors.detail" class="mt-2" />
                    </div>

                    <!-- Поле для ввода номера счета -->
                    <div v-if="selectedDetailType === 'account_number'">
                        <InputLabel
                            for="detail"
                            value="Номер счета"
                            :error="!!form.errors.detail"
                        />

                        <TextInput
                            id="detail"
                            v-model="details['account_number']"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="00000000000000000000"
                            :error="!!form.errors.detail"
                            @input="form.clearErrors('detail')"
                        />

                        <InputError :message="form.errors.detail" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel
                            for="initials"
                            value="Инициалы (имя получателя)"
                            :error="!!form.errors.initials"
                        />

                        <TextInput
                            id="initials"
                            v-model="form.initials"
                            type="text"
                            class="mt-1 block w-full"
                            :error="!!form.errors.initials"
                            @input="form.clearErrors('initials')"
                        />

                        <InputError :message="form.errors.initials" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel
                            for="daily_limit"
                            :value="'Лимит на объем операций в сутки (' + form.currency?.toUpperCase() + ')'"
                            :error="!!form.errors.daily_limit"
                        />

                        <NumberInput
                            id="daily_limit"
                            v-model="form.daily_limit"
                            class="mt-1 block w-full"
                            :error="!!form.errors.daily_limit"
                            @input="form.clearErrors('daily_limit')"
                        />

                        <InputError :message="form.errors.daily_limit" class="mt-2" />
                    </div>
                    <NumberInputBlock
                        v-if="viewStore.isAdminViewMode"
                        v-model="form.max_pending_orders_quantity"
                        :form="form"
                        field="max_pending_orders_quantity"
                        label="Максимальное количество активных сделок"
                    />
                    <NumberInputBlock
                        v-model="form.order_interval_minutes"
                        :form="form"
                        field="order_interval_minutes"
                        label="Интервал между сделками (минуты)"
                        helper="Оставьте пустым для отключения интервала"
                    />
                    <div>
                        <label class="inline-flex items-center mb-3 mt-3 cursor-pointer">
                            <input type="checkbox" value="" class="sr-only peer" v-model="form.is_active">
                            <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:w-5 after:h-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                            <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">Активен</span>
                        </label>
                    </div>
                </template>
<!--v-if="form.payment_gateway_ids.length > 0"-->
                <SaveButton

                    :disabled="form.processing"
                    :saved="form.recentlySuccessful"
                ></SaveButton>
            </form>
        </SecondaryPageSection>
    </div>
</template>
