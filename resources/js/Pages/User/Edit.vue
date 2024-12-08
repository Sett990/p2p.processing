<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import {Head, router, useForm, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from "@/Components/TextInput.vue";
import GoBackButton from "@/Components/GoBackButton.vue";
import Select from "@/Components/Select.vue";

const user = usePage().props.user;
const roles = usePage().props.roles;

const form = useForm({
    name: user.name,
    email: user.email,
    role_id: user.role.id,
    banned: user.banned_at ? true : false,
});
const submit = () => {
    form.patch(route('admin.users.update', user.id), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <Head title="Редактирование пользователя" />

    <div>
        <div class="mx-auto space-y-4">
            <div class="mb-3">
                <GoBackButton
                    @click="router.visit(route('admin.users.index'))"
                ></GoBackButton>
            </div>
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-xl ">
                <section class="max-w-xl">
                    <header>
                        <h2 class="text-lg font-medium dark:text-gray-100 text-gray-900">Редактирование пользователя - {{ user.email }}</h2>

                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Здесь вы можете отредактировать пользователя
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

                        <div v-if="user.id !== 1">
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

                        <div>
                            <label class="inline-flex items-center mb-3 mt-3 cursor-pointer">
                                <input type="checkbox" value="" class="sr-only peer" v-model="form.banned">
                                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:w-5 after:h-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">Заблокирован</span>
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
</template>
