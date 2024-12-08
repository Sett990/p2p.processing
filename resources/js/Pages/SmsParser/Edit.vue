<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import {Head, router, useForm, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import GoBackButton from "@/Components/GoBackButton.vue";
import {computed} from "vue";
import Select from "@/Components/Select.vue";
import NumberInput from "@/Components/NumberInput.vue";
import SmsParserRequirementsSection from "@/Components/SmsParserRequirementsSection.vue";
import InputHelper from "@/Components/InputHelper.vue";

const sms_parser = usePage().props.smsParser;
const payment_gateways = usePage().props.paymentGateways;

const form = useForm({
    format: sms_parser.format,
    regex: sms_parser.regex,
    payment_gateway_id: sms_parser.payment_gateway_id,
});
const submit = () => {
    form
        .patch(route('admin.sms-parsers.update', sms_parser.id), {
            preserveScroll: true,
            onSuccess: () => form.reset(),
        });
};

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <Head title="Редактирование парсера" />

    <div>
        <div>
            <div class="mx-auto space-y-4">
                <div class="mb-3">
                    <GoBackButton
                        @click="router.visit(route('admin.sms-parsers.index'))"
                    ></GoBackButton>
                </div>
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-xl ">
                    <section class="max-w-xl">
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Редактирование парсера</h2>

                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Здесь вы можете редактировать см парсер.
                            </p>
                        </header>

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
                                    @change="form.clearErrors('payment_gateway_id');"
                                ></Select>

                                <InputError :message="form.errors.payment_gateway_id" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel
                                    for="format"
                                    value="Формат"
                                    :error="!!form.errors.format"
                                />

                                <TextInput
                                    id="format"
                                    v-model="form.format"
                                    type="text"
                                    class="mt-1 block w-full"
                                    :error="!!form.errors.format"
                                    @input="form.clearErrors('format')"
                                />

                                <InputError :message="form.errors.format" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel
                                    for="regex"
                                    value="Regex"
                                    :error="!!form.errors.regex"
                                />

                                <TextInput
                                    id="regex"
                                    v-model="form.regex"
                                    type="text"
                                    class="mt-1 block w-full"
                                    :error="!!form.errors.regex"
                                    @input="form.clearErrors('regex')"
                                />

                                <InputError :message="form.errors.regex" class="mt-2" />
                            </div>

                            <div class="flex items-center p-4 text-sm text-gray-800 border border-gray-300 rounded-xl  bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600" role="alert">
                                <svg class="flex-shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                                </svg>
                                <span class="sr-only">Информация</span>
                                <div>
                                    Regex можно составить тут <a href="https://regex101.com" target="_blank" class="font-semibold text-gray-900 dark:text-white">regex101.com</a>
                                </div>
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
                    <div class="mt-5">
                        <SmsParserRequirementsSection></SmsParserRequirementsSection>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
