<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import {Head, useForm, usePage, router} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from "@/Components/TextInput.vue";
import Select from "@/Components/Select.vue";
import SecondaryPageSection from "@/Wrappers/SecondaryPageSection.vue";
import ConfirmModal from "@/Components/Modals/ConfirmModal.vue";
import {useModalStore} from "@/store/modal.js";
import {ref} from "vue";

const modalStore = useModalStore();
const user = ref(usePage().props.user);
const roles = usePage().props.roles;

const form = useForm({
    name: user.value.name,
    email: user.value.email,
    role_id: user.value.role.id,
    banned: user.value.banned_at ? true : false,
    payouts_enabled: user.value.payouts_enabled ? true : false,
    promo_code: '',
});

const submit = () => {
    form.patch(route('admin.users.update', user.value.id), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

const reset2fa = () => {
    modalStore.openConfirmModal({
        title: 'Сброс 2FA',
        body: 'Вы уверены, что хотите сбросить двухфакторную аутентификацию для этого пользователя?',
        confirm_button_name: 'Сбросить',
        confirm: () => {
            router.delete(route('admin.users.reset-2fa', user.value.id));
        }
    });
};

router.on('success', (event) => {
    user.value = usePage().props.user;
})

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Редактирование пользователя" />

        <SecondaryPageSection
            :back-link="route('admin.users.index')"
            :title="'Редактирование пользователя - ' + user.email"
            description="Здесь вы можете отредактировать пользователя."
        >
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

                <div>
                    <label class="inline-flex items-center mb-3 mt-3 cursor-pointer">
                        <input type="checkbox" value="" class="sr-only peer" v-model="form.payouts_enabled">
                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:w-5 after:h-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                        <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">Доступ к функционалу выплат</span>
                    </label>
                </div>

                <div v-if="!user.promo_code_id">
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

                <div v-else>
                    <InputLabel
                        value="Промокод"
                    />
                    <div class="mt-1 p-2 bg-gray-50 dark:bg-gray-700 rounded">
                        <span class="font-medium">{{ user.promo_code?.code }}</span>
                    </div>
                    <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Пользователь был привлечен через этот промокод. Нельзя изменить.
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

            <div class="mt-10 pt-6 border-t border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Дополнительные действия</h3>

                <div class="space-y-4">
                    <div
                        v-show="user.has_2fa === true"
                        class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg"
                    >
                        <div>
                            <h4 class="text-base font-medium text-gray-900 dark:text-gray-100">Двухфакторная аутентификация</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Сброс 2FA позволит пользователю настроить его заново</p>
                        </div>
                        <button
                            @click="reset2fa"
                            type="button"
                            class="text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-red-600 dark:hover:bg-red-700 focus:outline-none dark:focus:ring-red-800"
                        >
                            Сбросить 2FA
                        </button>
                    </div>
                </div>
            </div>
        </SecondaryPageSection>

        <ConfirmModal />
    </div>
</template>
