<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import {Head, router, useForm, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from "@/Components/TextInput.vue";
import GoBackButton from "@/Components/GoBackButton.vue";
import Select from "@/Components/Select.vue";

const roles = usePage().props.roles;

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role_id: 0,
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
    <Head title="Создание пользователя" />

    <div>
        <div class="mx-auto space-y-4">
            <div class="mb-3">
                <GoBackButton
                    @click="router.visit(route('admin.users.index'))"
                ></GoBackButton>
            </div>
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-plate">
                <section class="max-w-xl">
                    <header>
                        <h2 class="text-lg font-medium dark:text-gray-100 text-gray-900">Создание пользователя</h2>

                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Здесь вы можете создать пользователя
                        </p>
                    </header>

                    <form @submit.prevent="submit" class="mt-6 space-y-6">
                        <div>
                            <InputLabel
                                for="name"
                                value="Имя"
                                :error="!!form.errors.name"
                            />

                            <TextInput
                                id="name"
                                class="mt-1 block w-full"
                                v-model="form.name"
                                required
                                autofocus
                                autocomplete="name"
                                :error="!!form.errors.name"
                                @input="form.clearErrors('name')"
                            />

                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div>
                            <InputLabel
                                for="email"
                                value="Почта"
                                :error="!!form.errors.email"
                            />

                            <TextInput
                                id="email"
                                type="email"
                                class="mt-1 block w-full"
                                v-model="form.email"
                                required
                                autocomplete="username"
                                :error="!!form.errors.email"
                                @input="form.clearErrors('email')"
                            />

                            <InputError class="mt-2" :message="form.errors.email" />
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
                                key="id"
                                value="id"
                                name="name"
                                default_title="Выберите роль"
                                @change="form.clearErrors('role_id')"
                            ></Select>

                            <InputError class="mt-2" :message="form.errors.role_id" />
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
</template>
