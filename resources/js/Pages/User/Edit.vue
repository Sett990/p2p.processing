<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import {Head, useForm, usePage, router} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from "@/Components/TextInput.vue";
import NumberInput from "@/Components/NumberInput.vue";
import Select from "@/Components/Select.vue";
import SecondaryPageSection from "@/Wrappers/SecondaryPageSection.vue";
import ConfirmModal from "@/Components/Modals/ConfirmModal.vue";
import {useModalStore} from "@/store/modal.js";
import {ref} from "vue";
import DateTime from "@/Components/DateTime.vue";

const modalStore = useModalStore();
const user = ref(usePage().props.user);
const roles = usePage().props.roles;

const form = useForm({
    login: user.value.email,
    role_id: user.value.role.id,
    banned: user.value.banned_at ? true : false,
    payouts_enabled: user.value.payouts_enabled ? true : false,
    stop_traffic: user.value.stop_traffic ? true : false,
    is_vip: user.value.is_vip ? true : false,
    referral_commission_percentage: user.value.referral_commission_percentage || 0,
    promo_code: '',
});

// Проверка, является ли пользователь админом (role_id === 1)
const isAdmin = (roleId) => roleId === 1;
const isTrader = (roleId) => roleId === 2;
// Проверка, является ли пользователь мерчантом (role_id === 3)
const isMerchant = (roleId) => roleId === 3;
// Проверка, является ли пользователь Team Leader (role_id === 5)
const isTeamLeader = (roleId) => roleId === 5;
// Проверка, имеет ли пользователь доступ к функционалу выплат
const hasPayoutsAccess = (roleId) => isTrader(roleId) || isMerchant(roleId) || isAdmin(roleId);

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
            :title="'Редактирование пользователя - ' + user.login"
            description="Здесь вы можете отредактировать пользователя."
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

                <div class="form-control w-fit">
                    <label class="label cursor-pointer gap-3">
                        <input type="checkbox" class="toggle toggle-primary" v-model="form.banned">
                        <span class="label-text">Заблокирован</span>
                    </label>
                </div>

                <div v-if="hasPayoutsAccess(form.role_id)" class="form-control w-fit">
                    <label class="label cursor-pointer gap-3">
                        <input type="checkbox" class="toggle toggle-primary" v-model="form.payouts_enabled">
                        <span class="label-text">Доступ к функционалу выплат</span>
                    </label>
                </div>

                <div v-if="isTrader(form.role_id) || isAdmin(form.role_id)">
                    <div class="form-control w-fit">
                        <label class="label cursor-pointer gap-3">
                            <input type="checkbox" class="toggle toggle-error" v-model="form.stop_traffic">
                            <span class="label-text">Остановить трафик</span>
                        </label>
                    </div>

                    <div v-if="user.traffic_enabled_at && !form.stop_traffic" class="text-xs opacity-70 flex items-center">
                        Трафик включен: <DateTime :data="user.traffic_enabled_at" />
                    </div>
                </div>

                <div v-if="isTrader(form.role_id) || isAdmin(form.role_id)">
                    <div class="form-control w-fit">
                        <label class="label cursor-pointer gap-3">
                            <input type="checkbox" class="toggle toggle-primary" v-model="form.is_vip">
                            <span class="label-text">VIP статус</span>
                        </label>
                    </div>

                    <div class="mt-1 text-xs opacity-70">
                        VIP пользователи могут редактировать минимальную и максимальную сумму сделки
                    </div>
                </div>

                <div v-if="isTeamLeader(form.role_id) || isAdmin(form.role_id)">
                    <InputLabel
                        for="referral_commission_percentage"
                        value="Процент комиссии от рефералов"
                        :error="!!form.errors.referral_commission_percentage"
                    />

                    <NumberInput
                        id="referral_commission_percentage"
                        class="mt-1 block w-full"
                        v-model="form.referral_commission_percentage"
                        :error="!!form.errors.referral_commission_percentage"
                        @input="form.clearErrors('referral_commission_percentage')"
                        step="0.01"
                    />

                    <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Процент комиссии, который будет получать Team Leader со сделок привлеченных трейдеров
                    </div>

                    <InputError class="mt-2" :message="form.errors.referral_commission_percentage" />
                </div>

                <div v-if="!user.promo_code_id && (isTrader(form.role_id) || isAdmin(form.role_id))">
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

                <div v-else-if="user.promo_code_id && (isTrader(form.role_id) || isAdmin(form.role_id))">
                    <InputLabel
                        value="Промокод"
                    />
                    <div class="mt-1 p-2 rounded-btn bg-base-200">
                        <span class="font-medium">{{ user.promo_code?.code }}</span>
                    </div>
                    <div class="mt-1 text-sm opacity-70">
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

            <div v-show="user.has_2fa === true" class="mt-10 pt-6 border-t border-base-200">
                <h3 class="text-lg font-medium mb-4">Дополнительные действия</h3>

                <div class="space-y-4">
                    <div
                        class="card bg-base-100 shadow-sm"
                    >
                        <div class="card-body p-4 flex-row items-center justify-between">
                            <div>
                                <h4 class="text-base font-medium">Двухфакторная аутентификация</h4>
                                <p class="text-sm opacity-70">Сброс 2FA позволит пользователю настроить его заново</p>
                            </div>
                            <button
                                @click="reset2fa"
                                type="button"
                                class="btn btn-error"
                            >
                                Сбросить 2FA
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </SecondaryPageSection>

        <ConfirmModal />
    </div>
</template>
