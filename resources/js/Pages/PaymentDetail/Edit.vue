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

const viewStore = useViewStore();

const payment_detail = usePage().props.paymentDetail;

const form = useForm({
    name: payment_detail.name,
    initials: payment_detail.initials,
    is_active: !!payment_detail.is_active,
    daily_limit: payment_detail.daily_limit,
    max_pending_orders_quantity: payment_detail.max_pending_orders_quantity,
    min_order_amount: payment_detail.min_order_amount,
    max_order_amount: payment_detail.max_order_amount,
    user_device_id: payment_detail.user_device_id ?? 0,
});

const submit = () => {
    form
        .patch(route('payment-details.update', payment_detail.id), {
            preserveScroll: true,
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
                    :label="'Лимит на объем операций в сутки (' +  payment_detail.currency?.toUpperCase() + ')'"
                />

                <NumberInputBlock
                    v-if="viewStore.isAdminViewMode"
                    v-model="form.max_pending_orders_quantity"
                    :form="form"
                    field="max_pending_orders_quantity"
                    label="Максимальное количество активных сделок"
                />

                <NumberInputBlock
                    v-if="viewStore.isAdminViewMode"
                    v-model="form.min_order_amount"
                    :form="form"
                    field="min_order_amount"
                    :label="'Минимальная сумма сделки (' +  payment_detail.currency?.toUpperCase() + ')'"
                    helper="Оставьте пустым для отключения лимита"
                />

                <NumberInputBlock
                    v-if="viewStore.isAdminViewMode"
                    v-model="form.max_order_amount"
                    :form="form"
                    field="max_order_amount"
                    :label="'Максимальная сумма сделки (' +  payment_detail.currency?.toUpperCase() + ')'"
                    helper="Оставьте пустым для отключения лимита"
                />

                <div>
                    <label class="inline-flex items-center mb-3 mt-3 cursor-pointer">
                        <input type="checkbox" value="" class="sr-only peer" v-model="form.is_active">
                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:w-5 after:h-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                        <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">Активен</span>
                    </label>
                </div>

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
