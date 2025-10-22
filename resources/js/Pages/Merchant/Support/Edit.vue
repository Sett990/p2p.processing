<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SecondaryPageSection from "@/Wrappers/SecondaryPageSection.vue";
import Multiselect from "@/Components/Form/Multiselect.vue";
import { ref } from 'vue';

const support = usePage().props.support;
const merchants = usePage().props.merchants;
const supportMerchantIds = usePage().props.supportMerchantIds || [];

const form = useForm({
    name: '',
    email: support.email,
    banned: !!support.banned_at,
    merchant_ids: supportMerchantIds,
});

const submit = () => {
    form.patch(route('merchant.support.update', support.id), {
        preserveScroll: true
    });
};

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Редактирование саппорта" />

        <SecondaryPageSection
            :back-link="route('merchant.support.index')"
            title="Редактирование саппорта"
            description="Здесь вы можете изменить данные сотрудника поддержки."
        >
            <form @submit.prevent="submit" class="mt-6 space-y-6">
                <div class="form-control w-full">
                    <label for="email" class="label">
                        <span class="label-text" :class="{'text-error': !!form.errors.email}">Логин</span>
                    </label>
                    <input
                        id="email"
                        type="text"
                        class="input input-bordered w-full"
                        v-model="form.email"
                        required
                        autocomplete="username"
                        :class="{'input-error': !!form.errors.email}"
                        @input="form.clearErrors('email')"
                    />
                    <p v-if="form.errors.email" class="mt-2 text-sm text-error">{{ form.errors.email }}</p>
                </div>

                <div class="form-control w-full">
                    <label for="merchant_ids" class="label">
                        <span class="label-text" :class="{'text-error': !!form.errors.merchant_ids}">Доступные мерчанты</span>
                    </label>
                    <Multiselect
                        id="merchant_ids"
                        v-model="form.merchant_ids"
                        :options="merchants"
                        label-key="label"
                        value-key="value"
                        :enable-search="true"
                        placeholder="Выберите доступные мерчанты"
                        @input="form.clearErrors('merchant_ids')"
                    />
                    <p v-if="form.errors.merchant_ids" class="mt-2 text-sm text-error">{{ form.errors.merchant_ids }}</p>
                </div>

                <div class="form-control">
                    <label class="label cursor-pointer justify-start gap-3">
                        <input type="checkbox" class="checkbox checkbox-primary" v-model="form.banned" />
                        <span class="label-text">Заблокировать</span>
                    </label>
                </div>

                <div class="flex items-center gap-4">
                    <button class="btn btn-primary" :disabled="form.processing">
                        {{ form.processing ? 'Сохранение...' : 'Сохранить' }}
                    </button>
                    <Transition
                        enter-active-class="transition ease-in-out"
                        enter-from-class="opacity-0"
                        leave-active-class="transition ease-in-out"
                        leave-to-class="opacity-0"
                    >
                        <p v-if="form.recentlySuccessful" class="text-sm opacity-70">Сохранено.</p>
                    </Transition>
                </div>
            </form>
        </SecondaryPageSection>
    </div>
</template>
