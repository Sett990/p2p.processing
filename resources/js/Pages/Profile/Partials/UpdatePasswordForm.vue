<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import NumberInput from "@/Components/NumberInput.vue";

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    //current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            /*if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }*/
        },
    });
};
</script>

<template>
    <div>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Обновить пароль</h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Убедитесь, что ваша учетная запись использует длинный и случайный пароль, чтобы оставаться в безопасности.
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="mt-6 space-y-6">
<!--            <div>
                <InputLabel
                    for="current_password"
                    value="Текущий пароль"
                    :error="!!form.errors.current_password"
                />

                <TextInput
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="current-password"
                    :error="!!form.errors.current_password"
                    @input="form.clearErrors('current_password')"
                />

                <InputError :message="form.errors.current_password" class="mt-2" />
            </div>-->

            <div>
                <InputLabel
                    for="password"
                    value="Новый пароль"
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
                <InputLabel for="password_confirmation" value="Подтвердите пароль" />

                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="new-password"
                />

                <InputError :message="form.errors.password_confirmation" class="mt-2" />
            </div>

            <div class="flex items-center p-4 mb-4 text-sm text-blue-800 border border-blue-300 rounded-xl  bg-blue-50 dark:bg-gray-800 dark:text-blue-400 dark:border-blue-800" role="alert">
                <svg class="flex-shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                </svg>
                <span class="sr-only">Информация</span>
                <div>
                    После смены пароля, все сессии кроме текущей будут удалены.
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
    </div>
</template>
