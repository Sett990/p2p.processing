<script setup>
import ModalFooter from "@/Components/Modals/Components/ModalFooter.vue";
import ModalBody from "@/Components/Modals/Components/ModalBody.vue";
import Modal from "@/Components/Modals/Modal.vue";
import ModalHeader from "@/Components/Modals/Components/ModalHeader.vue";
import { storeToRefs } from 'pinia';
import { useModalStore } from "@/store/modal.js";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import Checkbox from "@/Components/Checkbox.vue";
import { computed, ref, watch } from "vue";
import { router } from '@inertiajs/vue3';
import {useViewStore} from "@/store/view.js";

const modalStore = useModalStore();
const { promoCodeEditModal } = storeToRefs(modalStore);
const viewStore = useViewStore();

const loading = ref(false);
const processing = ref(false);
const errors = ref({});

const promoCode = ref(null);

const form = ref({
    max_uses: 0,
    is_active: false,
});

const isMaxUsesReached = computed(() => {
    return promoCode.value && promoCode.value.max_uses > 0 && promoCode.value.used_count >= promoCode.value.max_uses;
});

watch(isMaxUsesReached, (reached) => {
    if (reached) {
        form.value.is_active = false;
    }
}, { immediate: true });

const resetForm = () => {
    form.value = {
        max_uses: 0,
        is_active: false,
    };
    errors.value = {};
    promoCode.value = null;
};

const close = () => {
    modalStore.closeModal('promoCodeEdit');
};

const routePrefix = () => viewStore.isAdminViewMode ? 'admin' : 'leader';

const loadData = () => {
    if (!promoCodeEditModal.value.params?.promoCodeId) return;
    loading.value = true;
    axios.get(route(routePrefix() + '.promo-codes.edit-data', promoCodeEditModal.value.params.promoCodeId))
        .then(response => {
            const data = response.data?.data || response.data || {};
            promoCode.value = data.promoCode;
            form.value.max_uses = promoCode.value.max_uses;
            form.value.is_active = promoCode.value.is_active;
            loading.value = false;
        })
        .catch(() => {
            loading.value = false;
        });
};

const submit = () => {
    if (!promoCode.value) return;
    processing.value = true;
    errors.value = {};
    axios.patch(route(routePrefix() + '.promo-codes.update', promoCode.value.id), form.value, {
        headers: { 'Accept': 'application/json' }
    })
        .then(response => {
            processing.value = false;
            if (response.data?.success || response.status === 200 || response.status === 204) {
                close();
                resetForm();
                router.reload({ only: ['promoCodes'] });
            }
        })
        .catch(error => {
            processing.value = false;
            if (error.response && error.response.data) {
                if (error.response.data.errors) {
                    errors.value = error.response.data.errors;
                } else if (error.response.data.message) {
                    errors.value = { is_active: [error.response.data.message] };
                }
            }
        });
};

watch(
    () => promoCodeEditModal.value.showed,
    (state) => {
        if (state) {
            resetForm();
            loadData();
        } else {
            resetForm();
        }
    }
);
</script>

<template>
    <Modal :show="promoCodeEditModal.showed" @close="close" maxWidth="xl">
        <ModalHeader @close="close" :title="promoCode ? ('Редактирование промокода - ' + promoCode.code) : 'Редактирование промокода'" />

        <ModalBody>
            <div v-if="loading" class="py-6 text-center">
                <span class="loading loading-spinner loading-md"></span>
            </div>
            <div v-else>
                <div class="mt-1 space-y-4" v-if="promoCode">
                    <div>
                        <InputLabel value="Код" />
                        <div class="mt-1 p-2 bg-base-200 rounded-xl">
                            {{ promoCode.code }}
                        </div>
                        <p class="text-sm opacity-70 mt-1">Код промокода нельзя изменить</p>
                    </div>
                    <div v-if="viewStore.isAdminViewMode && promoCode.team_leader">
                        <InputLabel value="Владелец" />
                        <div class="mt-1 p-2 bg-base-200 rounded-xl">
                            {{ promoCode.team_leader?.name || 'Не указан' }}
                        </div>
                    </div>
                    <div>
                        <InputLabel value="Использовано" />
                        <div class="mt-1 p-2 bg-base-200 rounded-xl">
                            {{ promoCode.used_count }}
                        </div>
                    </div>
                </div>

                <form @submit.prevent="submit" class="mt-4 space-y-6">
                    <div>
                        <InputLabel
                            for="max_uses"
                            value="Максимальное количество использований"
                            :error="!!errors.max_uses?.[0]"
                        />
                        <TextInput
                            id="max_uses"
                            type="number"
                            class="mt-1 block w-full"
                            v-model="form.max_uses"
                            min="0"
                            required
                            :error="!!errors.max_uses?.[0]"
                            @input="errors.max_uses = null"
                            :disabled="processing"
                        />
                        <InputError :message="errors.max_uses?.[0]" class="mt-1" />
                        <p class="text-sm opacity-70 mt-1">Установите 0 для неограниченного использования</p>
                    </div>

                    <div>
                        <label class="flex items-center" :class="{ 'opacity-50': isMaxUsesReached }">
                            <Checkbox
                                v-model:checked="form.is_active"
                                :disabled="isMaxUsesReached || processing"
                            />
                            <span class="ml-2 text-sm">Активен</span>
                        </label>
                        <InputError :message="errors.is_active?.[0]" class="mt-1" />
                        <p v-if="isMaxUsesReached" class="text-sm text-error mt-1">
                            Промокод нельзя активировать, так как достигнуто максимальное количество использований
                        </p>
                    </div>
                </form>
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


