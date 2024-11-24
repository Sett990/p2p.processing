<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import {Head, router, useForm, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {computed, onMounted, ref} from "vue";
import Select from "@/Components/Select.vue";
import NumberInput from "@/Components/NumberInput.vue";
import SaveButton from "@/Components/Form/SaveButton.vue";
import SecondaryPageSection from "@/Wrappers/SecondaryPageSection.vue";
import {useViewStore} from "@/store/view.js";

const viewStore = useViewStore();
const payment_gateways = usePage().props.paymentGateways;
const detail_type_names = {
    'card': 'Карта',
    'phone': 'Телефон',
    'account_number': 'Номер счета',
}
const detail_types = [
    {id: 'card', name: 'Карта'},
    {id: 'phone', name: 'Телефон'},
    {id: 'account_number', name: 'Номер счета'},
]


const selectedDetailType = ref(null);

const form = useForm({
    name: '',
    detail: '',
    initials: '',
    is_active: true,
    daily_limit: '',
    payment_gateway_id: 0,
    sub_payment_gateway_id: 0,
    detail_type: 'card',
});

const details = ref({
    'card': '',
    'phone': '',
    'account_number': '',
});

const submit = () => {
    form
        .transform((data) => {
            if (data.payment_gateway_id === 0) {
                data.payment_gateway_id = null;
            }
            if (data.sub_payment_gateway_id === 0) {
                data.sub_payment_gateway_id = null;
            }
            data.detail_type = selectedDetailType.value;

            data.detail = details.value[data.detail_type]

            return data;
        })
        .post(route('payment-details.store'), {
            preserveScroll: true,
            onSuccess: () => {
                router.visit(route(viewStore.adminPrefix + 'payment-details.index'))
            },
        });
};

const currentPaymentGateway = computed(() => {
    return payment_gateways.find((item) => {
        if (item.id === form.payment_gateway_id) {
            selectedDetailType.value = item.detail_types[0];

            return item;
        } else {
            return null;
        }
    });
})

const currentSubPaymentGateways = computed(() => {
    return payment_gateways.filter((gateway) => {
        return currentPaymentGateway.value.sub_methods.includes(gateway.code);
    });
})

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Создание нового реквизита" />

        <SecondaryPageSection
            :back-link="route(viewStore.adminPrefix + 'payment-details.index')"
            title="Создание нового реквизита"
            description="Здесь вы можете редактировать платежные реквизиты."
        >
            <form @submit.prevent="submit" class="mt-6 space-y-6">
                <div>
                    <InputLabel
                        for="payment_gateway_id"
                        value="Платежный метод"
                        :error="!!form.errors.payment_gateway_id"
                        class="mb-1"
                    />
                    <Select
                        id="payment_gateway_id"
                        v-model="form.payment_gateway_id"
                        :error="!!form.errors.payment_gateway_id"
                        :items="payment_gateways"
                        key="id"
                        value="id"
                        name="name"
                        default_title="Выберите платежный метод"
                        @change="form.clearErrors('payment_gateway_id');form.clearErrors('detail')"
                    ></Select>

                    <InputError :message="form.errors.payment_gateway_id" class="mt-2" />
                </div>
                <template v-if="form.payment_gateway_id">
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
                    <div class="mb-4">
                        <ul class="hidden border border-gray-200 dark:border-gray-700 text-sm font-medium text-center text-gray-500 rounded-lg sm:flex dark:divide-gray-700 dark:text-gray-400 overflow-hidden">
                            <li
                                v-for="(detail_type, index) in currentPaymentGateway.detail_types"
                                class="w-full focus-within:z-10"
                            >
                                <template v-if="index !== currentPaymentGateway.detail_types.length - 1">
                                    <a
                                        @click.prevent="selectedDetailType = detail_type;form.clearErrors('detail')"
                                        href="#"
                                        class="inline-block w-full p-2 border-r-0 border-gray-200 dark:border-gray-700 hover:text-gray-700 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:hover:text-white hover:bg-gray-200 dark:hover:bg-gray-700"
                                        :class="detail_type === selectedDetailType ? 'text-gray-900 bg-gray-200 border-r dark:bg-gray-700 dark:text-white' : 'bg-white dark:bg-gray-800'"
                                    >
                                        {{ detail_type_names[detail_type] }}
                                    </a>
                                </template>
                                <template v-else>
                                    <a
                                        @click.prevent="selectedDetailType = detail_type;form.clearErrors('detail')"
                                        href="#"
                                        class="inline-block w-full p-2 border-s-0 border-gray-200 dark:border-gray-700 hover:text-gray-700 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:hover:text-white hover:bg-gray-200 dark:hover:bg-gray-700"
                                        :class="detail_type === selectedDetailType ? 'text-gray-900 bg-gray-200 border-r dark:bg-gray-700 dark:text-white' : 'bg-white dark:bg-gray-800'"
                                    >
                                        {{ detail_type_names[detail_type] }}
                                    </a>
                                </template>
                            </li>
                        </ul>
                        <div class="block sm:hidden">
                            <Select
                                id="payment_detail_type"
                                v-model="selectedDetailType"
                                :items="detail_types.filter((item) => {return currentPaymentGateway.detail_types.includes(item.id)})"
                                key="id"
                                value="id"
                                name="name"
                                default_title="Выберите тип реквизитов"
                                @change="selectedDetailType = $event.target.value"
                            ></Select>
                        </div>
                    </div>
                    <template v-if="selectedDetailType === 'card'">
                        <div>
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
                    </template>
                    <template v-if="selectedDetailType === 'phone'">
                        <div>
                            <InputLabel
                                for="detail"
                                value="Номер телефона"
                                :error="!!form.errors.detail"
                            />

                            <div class="relative mb-6">
                                <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                                    <span class="text-gray-500 dark:text-gray-400">+</span>
                                </div>
                                <TextInput
                                    id="detail"
                                    v-model="details['phone']"
                                    type="text"
                                    class="mt-1 block w-full ps-7"
                                    placeholder="70000000000"
                                    :error="!!form.errors.detail"
                                    @input="form.clearErrors('detail')"
                                />
                            </div>

                            <InputError :message="form.errors.detail" class="mt-2" />
                        </div>
                    </template>
                    <template v-if="selectedDetailType === 'account_number'">
                        <div>
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
                    </template>
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
                            :value="'Лимит на объем операций в сутки (' +  currentPaymentGateway.currency?.toUpperCase() + ')'"
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
                    <div v-if="currentSubPaymentGateways.length">
                        <InputLabel
                            for="sub_payment_gateway_id"
                            value="Метод"
                            :error="!!form.errors.sub_payment_gateway_id"
                            class="mb-1"
                        />
                        <Select
                            id="sub_payment_gateway_id"
                            v-model="form.sub_payment_gateway_id"
                            :error="!!form.errors.sub_payment_gateway_id"
                            :items="currentSubPaymentGateways"
                            key="id"
                            value="id"
                            name="name"
                            default_title="Выберите метод"
                            @change="form.clearErrors('sub_payment_gateway_id')"
                        ></Select>

                        <InputError :message="form.errors.sub_payment_gateway_id" class="mt-2" />
                    </div>
                    <div>
                        <label class="inline-flex items-center mb-3 mt-3 cursor-pointer">
                            <input type="checkbox" value="" class="sr-only peer" v-model="form.is_active">
                            <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:w-5 after:h-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                            <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">Активен</span>
                        </label>
                    </div>
                </template>
                <SaveButton
                    v-if="form.payment_gateway_id"
                    :disabled="form.processing"
                    :saved="form.recentlySuccessful"
                ></SaveButton>
            </form>
        </SecondaryPageSection>
    </div>
</template>
