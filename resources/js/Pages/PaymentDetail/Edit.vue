<script setup>
import TextInput from '@/Components/TextInput.vue';
import {Head, useForm, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {computed, onMounted, ref} from "vue";
import Select from "@/Components/Select.vue";
import SaveButton from "@/Components/Form/SaveButton.vue";
import SecondaryPageSection from "@/Wrappers/SecondaryPageSection.vue";
import TextInputBlock from "@/Components/Form/TextInputBlock.vue";
import NumberInputBlock from "@/Components/Form/NumberInputBlock.vue";
import InputBlock from "@/Components/Form/InputBlock.vue";
import {useViewStore} from "@/store/view.js";
const viewStore = useViewStore();

const payment_detail = usePage().props.paymentDetail;
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


const selectedDetailType = ref(payment_detail.detail_type);

const form = useForm({
    name: payment_detail.name,
    detail: payment_detail.detail,
    initials: payment_detail.initials,
    is_active: !!payment_detail.is_active,
    daily_limit: payment_detail.daily_limit,
    payment_gateway_id: payment_detail.payment_gateway_id,
    sub_payment_gateway_id: payment_detail.sub_payment_gateway_id ?? 0,
    detail_type: payment_detail.detail_type,
});

const details = ref({
    'card': '',
    'phone': '',
    'account_number': '',
});

onMounted(() => {
    details.value[payment_detail.detail_type] = payment_detail.detail;
})

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
        .patch(route('payment-details.update', payment_detail.id), {
            preserveScroll: true,
        });
};

const currentPaymentGateway = computed(() => {
    return payment_gateways.find((item) => {
        if (item.id === form.payment_gateway_id) {
            return item;
        } else {
            return null;
        }
    });
})

const currentSubPaymentGateways = computed(() => {
    return payment_gateways.filter((gateway) => {
        return currentPaymentGateway.value.sub_methods.includes(gateway.code);
    })
});

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
                <InputBlock
                    :form="form"
                    field="payment_gateway_id"
                    label="Платежный метод"
                >
                    <Select
                        id="payment_gateway_id"
                        v-model="form.payment_gateway_id"
                        :error="!!form.errors.payment_gateway_id"
                        :items="payment_gateways"
                        key="id"
                        value="id"
                        name="name"
                        default_title="Выберите платежный метод"
                        @change="form.clearErrors('role_id'); form.clearErrors('detail'); selectedDetailType = currentPaymentGateway.detail_types[0];"
                    ></Select>
                </InputBlock>

                <template v-if="form.payment_gateway_id">
                    <TextInputBlock
                        v-model="form.name"
                        :form="form"
                        field="name"
                        label="Никнейм реквизитов"
                    />
                    <div class="mb-4">
                        <ul class="hidden border border-gray-200 dark:border-gray-700 text-sm font-medium text-center text-gray-500 rounded-lg sm:flex dark:divide-gray-700 dark:text-gray-400 overflow-hidden">
                            <li
                                v-for="(detail_type, index) in currentPaymentGateway.detail_types"
                                class="w-full focus-within:z-10"
                            >
                                <template v-if="index !== currentPaymentGateway.detail_types.length - 1">
                                    <a
                                        @click.prevent="selectedDetailType = detail_type; form.clearErrors('detail')"
                                        href="#"
                                        class="inline-block w-full p-2 border-r-0 border-gray-200 dark:border-gray-700 hover:text-gray-700 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:hover:text-white hover:bg-gray-200 dark:hover:bg-gray-700"
                                        :class="detail_type === selectedDetailType ? 'text-gray-900 bg-gray-200 border-r dark:bg-gray-700 dark:text-white' : 'bg-white dark:bg-gray-800'"
                                    >
                                        {{ detail_type_names[detail_type] }}
                                    </a>
                                </template>
                                <template v-else>
                                    <a
                                        @click.prevent="selectedDetailType = detail_type; form.clearErrors('detail')"
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

                    <TextInputBlock
                        v-if="selectedDetailType === 'card'"
                        v-model="details['card']"
                        :form="form"
                        field="detail"
                        placeholder="0000 0000 0000 0000"
                        label="Карта"
                    />

                    <InputBlock
                        v-if="selectedDetailType === 'phone'"
                        :form="form"
                        field="detail"
                        label="Номер телефона"
                    >
                        <div class="relative mb-6">
                            <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                                <span class="text-gray-500 dark:text-gray-400">+</span>
                            </div>
                            <TextInput
                                id="detail"
                                v-model="details['phone']"
                                type="text"
                                class="block w-full ps-7"
                                placeholder="70000000000"
                                :error="!!form.errors.detail"
                                @input="form.clearErrors('detail')"
                            />
                        </div>
                    </InputBlock>

                    <TextInputBlock
                        v-if="selectedDetailType === 'account_number'"
                        v-model="details['account_number']"
                        :form="form"
                        field="detail"
                        placeholder="00000000000000000000"
                        label="Номер счета"
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
                        :label="'Лимит на объем операций в сутки (' +  currentPaymentGateway.currency?.toUpperCase() + ')'"
                    />

                    <InputBlock
                        v-if="currentSubPaymentGateways.length"
                        :form="form"
                        field="sub_payment_gateway_id"
                        label="Метод"
                    >
                        <Select
                            class=""
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
                    </InputBlock>

                    <div>
                        <label class="inline-flex items-center mb-3 mt-3 cursor-pointer">
                            <input type="checkbox" value="" class="sr-only peer" v-model="form.is_active">
                            <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:w-5 after:h-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                            <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">Активен</span>
                        </label>
                    </div>
                </template>

                <div v-if="viewStore.isAdminViewMode" class="pb-2">
                    <label for="owner_email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Владелец</label>
                    <div
                        class="dark:text-gray-300 mt-1 block w-full"
                    >
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
