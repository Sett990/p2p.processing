<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import {Head, router, useForm, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import NumberInput from "@/Components/NumberInput.vue";
import InputHelper from "@/Components/InputHelper.vue";
import GoBackButton from "@/Components/GoBackButton.vue";
import DropDownWithCheckbox from "@/Components/Form/DropDownWithCheckbox.vue";
import DropDownWithRadio from "@/Components/Form/DropDownWithRadio.vue";
import TextInputBlock from "@/Components/Form/TextInputBlock.vue";
import {ref} from "vue";
import TextInput from "@/Components/TextInput.vue";

const currencies = usePage().props.currencies;
const detail_types = usePage().props.detailTypes;
const payment_gateways = usePage().props.paymentGateways;

const form = useForm({
    name: null,
    code: null,
    min_limit: null,
    max_limit: null,
    commission_rate: null,
    service_commission_rate: null,
    is_active: true,
    reservation_time: null,
    currency: 'RUB',
    detail_types: [],
    sub_payment_gateways: [],
    sms_senders: [],
});
const submit = () => {
    form.post(route('admin.payment-gateways.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

const sms_sender = ref(null);

const addSender = () => {
    if (! sms_sender.value) {
        return;
    }

    form.sms_senders.push(sms_sender.value)

    form.sms_senders = form.sms_senders.filter((value, index, array) => {
        return array.indexOf(value) === index;
    });

    sms_sender.value = null;
}

const removeSender = (sender) => {
    form.sms_senders = form.sms_senders.filter((item) => {
        return item !== sender
    });
}

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <Head title="Создание платежного метода" />

    <div>
        <div>
            <div class="mx-auto space-y-4">
                <div class="mb-3">
                    <GoBackButton
                        @click="router.visit(route('admin.payment-gateways.index'))"
                    ></GoBackButton>
                </div>
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <section class="max-w-xl">
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Создание платежного метода</h2>

                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Здесь вы можете создать новый платежный метод
                            </p>
                        </header>

                        <form @submit.prevent="submit" class="mt-6 space-y-6">
                            <TextInputBlock
                                v-model="form.name"
                                :form="form"
                                field="name"
                                label="Название"
                                placeholder="Сбербанк"
                            />

                            <TextInputBlock
                                v-model="form.code"
                                :form="form"
                                field="code"
                                label="Код метода"
                                placeholder="sberbank"
                                helper="К коду будет добавлен код валюты. Например: sberbank_rub"
                            />

                            <div>
                                <DropDownWithCheckbox
                                    v-model="form.sub_payment_gateways"
                                    :items="payment_gateways"
                                    value="id"
                                    name="name"
                                    label="Вспомогательный метод"
                                />
                                <InputError :message="form.errors.sub_payment_gateways" class="mt-2" />
                            </div>

                            <div>
                                <DropDownWithCheckbox
                                    v-model="form.detail_types"
                                    :items="detail_types"
                                    value="code"
                                    name="name"
                                    label="Тип реквизитов"
                                />
                                <InputError :message="form.errors.detail_types" class="mt-2" />
                            </div>

                            <div>
                                <DropDownWithRadio
                                    v-model="form.currency"
                                    :items="currencies"
                                    value="code"
                                    name="code"
                                    label="Валюта"
                                />
                                <InputError :message="form.errors.currency" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel
                                    for="min_limit"
                                    :value="'Минимальная сумма в ' + form.currency?.toUpperCase()"
                                    :error="!!form.errors.min_limit"
                                />

                                <NumberInput
                                    id="min_limit"
                                    v-model="form.min_limit"
                                    class="mt-1 block w-full"
                                    placeholder="0"
                                    :error="!!form.errors.min_limit"
                                    @input="form.clearErrors('min_limit')"
                                />

                                <InputError :message="form.errors.min_limit" class="mt-2" />
                                <InputHelper v-if="! form.errors.min_limit" model-value="Минимальный лимит на одну операцию"></InputHelper>
                            </div>
                            <div>
                                <InputLabel
                                    for="max_limit"
                                    :value="'Максимальная сумма в ' + form.currency?.toUpperCase()"
                                    :error="!!form.errors.max_limit"
                                />

                                <NumberInput
                                    id="max_limit"
                                    v-model="form.max_limit"
                                    class="mt-1 block w-full"
                                    placeholder="0"
                                    :error="!!form.errors.max_limit"
                                    @input="form.clearErrors('max_limit')"
                                />

                                <InputError :message="form.errors.max_limit" class="mt-2" />
                                <InputHelper v-if="! form.errors.max_limit" model-value="Минимальный лимит на одну операцию"></InputHelper>
                            </div>
                            <div>
                                <InputLabel
                                    for="commission_rate"
                                    value="Комиссия трейдера %"
                                    :error="!!form.errors.commission_rate"
                                />

                                <NumberInput
                                    id="commission_rate"
                                    v-model="form.commission_rate"
                                    class="mt-1 block w-full"
                                    step="0.1"
                                    placeholder="0.0"
                                    :error="!!form.errors.commission_rate"
                                    @input="form.clearErrors('commission_rate')"
                                />

                                <InputError :message="form.errors.commission_rate" class="mt-2" />
                                <InputHelper v-if="! form.errors.commission_rate" model-value="Наценка на курс в %, которую забирает себе трейдер"></InputHelper>
                            </div>

                            <div>
                                <InputLabel
                                    for="service_commission_rate"
                                    value="Комиссия сервиса %"
                                    :error="!!form.errors.service_commission_rate"
                                />

                                <NumberInput
                                    id="service_commission_rate"
                                    v-model="form.service_commission_rate"
                                    class="mt-1 block w-full"
                                    step="0.1"
                                    placeholder="0.0"
                                    :error="!!form.errors.service_commission_rate"
                                    @input="form.clearErrors('service_commission_rate')"
                                />

                                <InputError :message="form.errors.service_commission_rate" class="mt-2" />
                                <InputHelper v-if="! form.errors.service_commission_rate" model-value="Наценка в % на базовую сумму сделки, которую забирает себе сервис."></InputHelper>
                            </div>

                            <div>
                                <InputLabel
                                    for="reservation_time"
                                    value="Время удержания реквизитов"
                                    :error="!!form.errors.reservation_time"
                                />

                                <NumberInput
                                    id="reservation_time"
                                    v-model="form.reservation_time"
                                    class="mt-1 block w-full"
                                    placeholder="0"
                                    :error="!!form.errors.reservation_time"
                                    @input="form.clearErrors('reservation_time')"
                                />

                                <InputError :message="form.errors.reservation_time" class="mt-2" />
                                <InputHelper v-if="! form.errors.reservation_time" model-value="Время на одну операцию обмена в минутах"></InputHelper>
                            </div>

                            <div>
                                <InputLabel
                                    for="sms_senders"
                                    value="Отправители смс/push"
                                    :error="!!form.errors.sms_senders"
                                    class="mb-1"
                                />

                                <div class="relative">
                                    <TextInput
                                        id="sms_senders"
                                        v-model="sms_sender"
                                        class="block w-full"
                                        :error="!!form.errors.sms_senders"
                                        @input="form.clearErrors('sms_senders')"
                                    />

                                    <button @click.prevent="addSender" type="button" class="text-white absolute end-1.5 sm:bottom-1 bottom-1.5 bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm sm:px-3 sm:py-1.5 px-2 py-1 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Добавить</button>
                                </div>

                                <InputError :message="form.errors.sms_senders" class="mt-2" />
                                <InputHelper v-if="! form.errors.sms_senders" model-value="Например: 900, Alfabank"></InputHelper>

                                <div class="flex flex-wrap gap-0.5 mt-2">
                                    <div v-for="sender in form.sms_senders">
                                        <span class="bg-gray-100 text-gray-800 text-xs font-medium inline-flex items-center px-2.5 py-0.5 rounded me-2 dark:bg-gray-700 dark:text-gray-400 border border-gray-500">
                                            {{ sender }}
                                            <svg @click="removeSender(sender)" class="w-2.5 h-2.5 ml-1.5 cursor-pointer" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/>
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="inline-flex items-center mb-3 mt-3 cursor-pointer">
                                    <input type="checkbox" value="" class="sr-only peer" v-model="form.is_active">
                                    <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:w-5 after:h-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                    <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">Метод активен</span>
                                </label>
                            </div>


                            <div class="flex items-center gap-4">
                                <PrimaryButton :disabled="form.processing">Сохранить</PrimaryButton>

                                <Transition
                                    enter-active-class="transition ease-in-out"
                                    enter-from-class="opacity-0"
                                    leave-active-class="transition ease-in-out"
                                    leave-to-class="opacity-0"
                                >
                                    <p v-if="form.recentlySuccessful" class="text-sm text-gray-600 dark:text-gray-400">Сохранено.</p>
                                </Transition>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </div>
</template>
