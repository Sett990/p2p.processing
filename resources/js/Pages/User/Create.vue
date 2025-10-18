<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import {Head, useForm, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from "@/Components/TextInput.vue";
import Select from "@/Components/Select.vue";
import SecondaryPageSection from "@/Wrappers/SecondaryPageSection.vue";

const roles = usePage().props.roles;

const form = useForm({
    login: '',
    password: '',
    password_confirmation: '',
    role_id: 0,
    promo_code: '',
});
const submit = () => {
    form.post(route('admin.users.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Создание пользователя" />

        <SecondaryPageSection
            :back-link="route('admin.users.index')"
            title="Создание пользователя"
            description="Здесь вы можете создать пользователя."
        >
            <form @submit.prevent="submit" class="mt-6 space-y-6">
                <div>
                    <InputLabel
                        for="login"
                        value="Логин"
                        :error="!!form.errors.login"
                    />

                    <TextInput
                        id="login"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.login"
                        required
                        autocomplete="username"
                        :error="!!form.errors.login"
                        @input="form.clearErrors('login')"
                    />

                    <InputError class="mt-2" :message="form.errors.login" />
                </div>

                <div>
                    <InputLabel
                        for="password"
                        value="Пароль"
                        :error="!!form.errors.password"
                    />

                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-full"
                        autocomplete="new-password"
                        :error="!!form.errors.password"
                        @input="form.clearErrors('password')"
                    />

                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <div>
                    <InputLabel
                        for="password_confirmation"
                        value="Подтвердите пароль"
                    />

                    <TextInput
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        class="mt-1 block w-full"
                        autocomplete="new-password"
                    />

                    <InputError :message="form.errors.password_confirmation" class="mt-2" />
                </div>

                <div>
                    <InputLabel
                        for="roles"
                        value="Роль"
                        :error="!!form.errors.role_id"
                        class="mb-1"
                    />

                    <Select
                        v-model="form.role_id"
                        :error="!!form.errors.role_id"
                        :items="roles"
                        value="id"
                        name="name"
                        default_title="Выберите роль"
                        @change="form.clearErrors('role_id')"
                    ></Select>

                    <InputError class="mt-2" :message="form.errors.role_id" />
                </div>

                <div>
                    <InputLabel
                        for="promo_code"
                        value="Промокод"
                        :error="!!form.errors.promo_code"
                    />

                    <TextInput
                        id="promo_code"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.promo_code"
                        autocomplete="off"
                        :error="!!form.errors.promo_code"
                        @input="form.clearErrors('promo_code')"
                    />
                    <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Введите промокод, если пользователь был привлечен через него. Нельзя изменить после сохранения.
                    </div>

                    <InputError class="mt-2" :message="form.errors.promo_code" />
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
        </SecondaryPageSection>
    </div>
</template>
