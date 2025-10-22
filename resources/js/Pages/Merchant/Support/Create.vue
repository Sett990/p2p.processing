<script setup>
import {Head, useForm, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SecondaryPageSection from "@/Wrappers/SecondaryPageSection.vue";
import Multiselect from "@/Components/Form/Multiselect.vue";

const merchants = usePage().props.merchants;

const form = useForm({
    email: '',
    password: '',
    password_confirmation: '',
    merchant_ids: [],
});

const submit = () => {
    form.post(route('merchant.support.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Добавление саппорта" />

        <SecondaryPageSection
            :back-link="route('merchant.support.index')"
            title="Добавление саппорта"
            description="Здесь вы можете добавить нового сотрудника поддержки."
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

                <div class="form-control w-full">
                    <label for="password" class="label">
                        <span class="label-text" :class="{'text-error': !!form.errors.password}">Пароль</span>
                    </label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        class="input input-bordered w-full"
                        autocomplete="new-password"
                        :class="{'input-error': !!form.errors.password}"
                        @input="form.clearErrors('password')"
                    />
                    <p v-if="form.errors.password" class="mt-2 text-sm text-error">{{ form.errors.password }}</p>
                </div>

                <div class="form-control w-full">
                    <label for="password_confirmation" class="label">
                        <span class="label-text">Подтвердите пароль</span>
                    </label>
                    <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        class="input input-bordered w-full"
                        autocomplete="new-password"
                    />
                    <p v-if="form.errors.password_confirmation" class="mt-2 text-sm text-error">{{ form.errors.password_confirmation }}</p>
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
