<script setup>
import ModalFooter from "@/Components/Modals/Components/ModalFooter.vue";
import ModalBody from "@/Components/Modals/Components/ModalBody.vue";
import Modal from "@/Components/Modals/Modal.vue";
import ModalHeader from "@/Components/Modals/Components/ModalHeader.vue";
import { storeToRefs } from 'pinia'
import { useModalStore } from "@/store/modal.js";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import NumberInput from "@/Components/NumberInput.vue";
import Select from "@/Components/Select.vue";
import {ref, watch, computed} from "vue";
import DateTime from "@/Components/DateTime.vue";
import { router } from '@inertiajs/vue3';

const modalStore = useModalStore();
const { userEditModal } = storeToRefs(modalStore);

const roles = ref([]);
const loading = ref(false);
const processing = ref(false);
const errors = ref({});
const user = ref(null);

const form = ref({
    login: '',
    role_id: 0,
    banned: false,
    payouts_enabled: false,
    stop_traffic: false,
    can_work_without_device: false,
    is_vip: false,
    referral_commission_percentage: 0,
    reserve_balance_limit: null,
    promo_code: '',
});

const isAdmin = (roleId) => roleId === 1;
const isTrader = (roleId) => roleId === 2;
const isMerchant = (roleId) => roleId === 3;
const isTeamLeader = (roleId) => roleId === 5;
const hasPayoutsAccess = (roleId) => isTrader(roleId) || isMerchant(roleId) || isAdmin(roleId);

const close = () => {
    modalStore.closeModal('userEdit');
};

const resetState = () => {
    user.value = null;
    roles.value = [];
    errors.value = {};
    form.value = {
        login: '',
        role_id: 0,
        banned: false,
        payouts_enabled: false,
        stop_traffic: false,
        can_work_without_device: false,
        is_vip: false,
        referral_commission_percentage: 0,
        reserve_balance_limit: null,
        promo_code: '',
    };
};

const loadRoles = () => {
    return axios.get(route('admin.users.roles'))
        .then(response => {
            roles.value = response.data?.data || response.data || [];
        });
};

const loadUser = () => {
    const id = userEditModal.value.params.user?.id || userEditModal.value.params.user_id;
    return axios.get(route('admin.users.show', id))
        .then(response => {
            const data = response.data?.data || response.data;
            user.value = data;
            form.value.login = data.email;
            form.value.role_id = data.role.id;
            form.value.banned = !!data.banned_at;
            form.value.payouts_enabled = !!data.payouts_enabled;
            form.value.stop_traffic = !!data.stop_traffic;
            form.value.can_work_without_device = !!data.can_work_without_device;
            form.value.is_vip = !!data.is_vip;
            form.value.referral_commission_percentage = data.referral_commission_percentage || 0;
            form.value.reserve_balance_limit = data.reserve_balance_limit;
            form.value.promo_code = '';
        });
};

const loadData = () => {
    loading.value = true;
    Promise.all([loadRoles(), loadUser()])
        .finally(() => {
            loading.value = false;
        });
};

const submit = () => {
    if (!user.value) return;
    processing.value = true;
    errors.value = {};

    axios.patch(route('admin.users.update', user.value.id), form.value, {
        headers: { 'Accept': 'application/json' }
    })
        .then(response => {
            processing.value = false;
            if (response.data?.success || response.status === 200) {
                close();
                router.reload({ only: ['users'] });
            }
        })
        .catch(error => {
            processing.value = false;
            if (error.response && error.response.data && error.response.data.errors) {
                errors.value = error.response.data.errors;
            }
        });
};

const reset2fa = () => {
    if (!user.value) return;
    processing.value = true;
    axios.delete(route('admin.users.reset-2fa', user.value.id), {
        headers: { 'Accept': 'application/json' }
    })
        .then(() => {
            processing.value = false;
            // Ничего не делаем дополнительно, действие побочное
        })
        .catch(() => {
            processing.value = false;
        });
};

watch(
    () => userEditModal.value.showed,
    (state) => {
        if (state) {
            resetState();
            loadData();
        } else {
            resetState();
        }
    }
);
</script>

<template>
    <Modal :show="userEditModal.showed" @close="close" maxWidth="xl">
        <ModalHeader @close="close" :title="user ? `Редактирование пользователя - ${user.login || user.email}` : 'Редактирование пользователя'" />

        <ModalBody>
            <div v-if="loading" class="py-6 text-center">
                <span class="loading loading-spinner loading-md"></span>
            </div>
            <form v-else @submit.prevent="submit" class="space-y-6">
                <div>
                    <InputLabel
                        for="login"
                        value="Логин"
                        :error="!!errors.login?.[0]"
                    />
                    <TextInput
                        id="login"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.login"
                        required
                        autocomplete="username"
                        :error="!!errors.login?.[0]"
                        @input="errors.login = null"
                        :disabled="processing"
                    />
                    <InputError class="mt-1" :message="errors.login?.[0]" />
                </div>

                <div v-if="user && user.id !== 1">
                    <InputLabel
                        for="roles"
                        value="Роль"
                        :error="!!errors.role_id?.[0]"
                        class="mb-1"
                    />
                    <Select
                        v-model="form.role_id"
                        :error="!!errors.role_id?.[0]"
                        :items="roles"
                        value="id"
                        name="name"
                        default_title="Выберите роль"
                        @change="errors.role_id = null"
                        :disabled="processing"
                    ></Select>
                    <InputError class="mt-1" :message="errors.role_id?.[0]" />
                </div>

                <div class="form-control w-fit">
                    <label class="label cursor-pointer gap-3">
                        <input type="checkbox" class="toggle toggle-primary" v-model="form.banned" :disabled="processing">
                        <span class="label-text">Заблокирован</span>
                    </label>
                </div>

<!--
                <div v-if="hasPayoutsAccess(form.role_id)" class="form-control w-fit">
                    <label class="label cursor-pointer gap-3">
                        <input type="checkbox" class="toggle toggle-primary" v-model="form.payouts_enabled" :disabled="processing">
                        <span class="label-text">Доступ к функционалу выплат</span>
                    </label>
                </div>
-->

                <div v-if="isTrader(form.role_id) || isAdmin(form.role_id)">
                    <div class="form-control w-fit">
                        <label class="label cursor-pointer gap-3">
                            <input type="checkbox" class="toggle toggle-error" v-model="form.stop_traffic" :disabled="processing">
                            <span class="label-text">Остановить трафик</span>
                        </label>
                    </div>
                    <div v-if="user?.traffic_enabled_at && !form.stop_traffic" class="text-xs opacity-70 flex items-center">
                        Трафик включен: <DateTime :data="user.traffic_enabled_at" />
                    </div>
                </div>

                <div v-if="isTrader(form.role_id) || isAdmin(form.role_id)">
                    <div class="form-control w-fit">
                        <label class="label cursor-pointer gap-3">
                            <input type="checkbox" class="toggle toggle-primary" v-model="form.can_work_without_device" :disabled="processing">
                            <span class="label-text">Работать без устройства</span>
                        </label>
                    </div>
                    <div class="mt-1 text-xs opacity-70">
                        При включении реквизиты можно создавать без привязки к устройству, страница устройств будет недоступна.
                    </div>
                </div>

                <div v-if="isTrader(form.role_id) || isAdmin(form.role_id)">
                    <div class="form-control w-fit">
                        <label class="label cursor-pointer gap-3">
                            <input type="checkbox" class="toggle toggle-primary" v-model="form.is_vip" :disabled="processing">
                            <span class="label-text">VIP статус</span>
                        </label>
                    </div>
                    <div class="mt-1 text-xs opacity-70">
                        VIP пользователи могут редактировать минимальную и максимальную сумму сделки
                    </div>
                </div>

                <div v-if="isTrader(form.role_id) || isAdmin(form.role_id)">
                    <InputLabel
                        for="reserve_balance_limit"
                        value="Страховой депозит (USDT)"
                        :error="!!errors.reserve_balance_limit?.[0]"
                    />
                    <NumberInput
                        id="reserve_balance_limit"
                        v-model="form.reserve_balance_limit"
                        class="mt-1 block w-full"
                        step="1"
                        min="0"
                        :error="!!errors.reserve_balance_limit?.[0]"
                        @input="errors.reserve_balance_limit = null"
                        :disabled="processing"
                    />
                    <InputError class="mt-1" :message="errors.reserve_balance_limit?.[0]" />
                    <div class="mt-1 text-xs opacity-70">
                        Сумма, до которой пополнения сначала идут в резервный баланс (страховой депозит).
                    </div>
                </div>

                <div v-if="isTeamLeader(form.role_id) || isAdmin(form.role_id)">
                    <InputLabel
                        for="referral_commission_percentage"
                        value="Процент комиссии от рефералов"
                        :error="!!errors.referral_commission_percentage?.[0]"
                    />
                    <NumberInput
                        id="referral_commission_percentage"
                        class="mt-1 block w-full"
                        v-model="form.referral_commission_percentage"
                        :error="!!errors.referral_commission_percentage?.[0]"
                        @input="errors.referral_commission_percentage = null"
                        step="0.01"
                        :disabled="processing"
                    />
                    <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Процент комиссии, который будет получать Team Leader со сделок привлеченных трейдеров
                    </div>
                    <InputError class="mt-1" :message="errors.referral_commission_percentage?.[0]" />
                </div>

                <div v-if="user && !user.promo_code_id && (isTrader(form.role_id) || isAdmin(form.role_id))">
                    <InputLabel
                        for="promo_code"
                        value="Промокод"
                        :error="!!errors.promo_code?.[0]"
                    />
                    <TextInput
                        id="promo_code"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.promo_code"
                        autocomplete="off"
                        :error="!!errors.promo_code?.[0]"
                        @input="errors.promo_code = null"
                        :disabled="processing"
                    />
                    <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Введите промокод, если пользователь был привлечен через него. Нельзя изменить после сохранения.
                    </div>
                    <InputError class="mt-1" :message="errors.promo_code?.[0]" />
                </div>

                <div v-else-if="user && user.promo_code_id && (isTrader(form.role_id) || isAdmin(form.role_id))">
                    <InputLabel value="Промокод" />
                    <div class="mt-1 p-2 rounded-btn bg-base-200">
                        <span class="font-medium">{{ user.promo_code?.code }}</span>
                    </div>
                    <div class="mt-1 text-sm opacity-70">
                        Пользователь был привлечен через этот промокод. Нельзя изменить.
                    </div>
                </div>
            </form>

            <div v-if="user?.has_2fa === true" class="mt-10 pt-6 border-t border-base-200">
                <h3 class="text-lg font-medium mb-4">Дополнительные действия</h3>
                <div class="space-y-4">
                    <div class="card bg-base-100 shadow-sm">
                        <div class="card-body p-4 flex-row items-center justify-between">
                            <div>
                                <h4 class="text-base font-medium">Двухфакторная аутентификация</h4>
                                <p class="text-sm opacity-70">Сброс 2FA позволит пользователю настроить его заново</p>
                            </div>
                            <button
                                @click="reset2fa"
                                type="button"
                                class="btn btn-error"
                                :class="{ 'btn-disabled': processing }"
                                :disabled="processing"
                            >
                                Сбросить 2FA
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </ModalBody>

        <ModalFooter>
            <button @click="close" type="button" class="btn btn-sm">
                Отмена
            </button>
            <button @click="submit" type="button" class="btn btn-sm btn-primary" :class="{ 'btn-disabled': processing }" :disabled="processing">
                Сохранить
            </button>
        </ModalFooter>
    </Modal>
</template>


