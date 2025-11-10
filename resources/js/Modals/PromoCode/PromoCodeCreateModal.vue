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
import { ref, watch } from "vue";
import { router } from '@inertiajs/vue3';
import {useViewStore} from "@/store/view.js";

const modalStore = useModalStore();
const { promoCodeCreateModal } = storeToRefs(modalStore);
const viewStore = useViewStore();

const processing = ref(false);
const errors = ref({});

const form = ref({
    code: '',
    max_uses: 10,
    is_active: true,
});

const resetForm = () => {
    form.value = {
        code: '',
        max_uses: 10,
        is_active: true,
    };
    errors.value = {};
};

const close = () => {
    modalStore.closeModal('promoCodeCreate');
};

const routePrefix = () => viewStore.isAdminViewMode ? 'admin' : 'leader';

const submit = () => {
    processing.value = true;
    errors.value = {};
    axios.post(route(routePrefix() + '.promo-codes.store'), form.value, {
        headers: { 'Accept': 'application/json' }
    })
        .then(response => {
            processing.value = false;
            if (response.data?.success || response.status === 200 || response.status === 201) {
                close();
                resetForm();
                router.reload({ only: ['promoCodes'] });
            }
        })
        .catch(error => {
            processing.value = false;
            if (error.response && error.response.data && error.response.data.errors) {
                errors.value = error.response.data.errors;
            }
        });
};

watch(
    () => promoCodeCreateModal.value.showed,
    (state) => {
        if (state) {
            resetForm();
        } else {
            resetForm();
        }
    }
);
</script>

<template>
    <Modal :show="promoCodeCreateModal.showed" @close="close" maxWidth="xl">
        <ModalHeader @close="close" title="Создание промокода" />

        <ModalBody>
            <form @submit.prevent="submit" class="mt-2 space-y-6">
                <div>
                    <InputLabel
                        for="code"
                        value="Код (оставьте пустым для автогенерации)"
                        :error="!!errors.code?.[0]"
                    />
                    <TextInput
                        id="code"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.code"
                        placeholder="Введите код или оставьте пустым"
                        :error="!!errors.code?.[0]"
                        @input="errors.code = null"
                        :disabled="processing"
                    />
                    <InputError :message="errors.code?.[0]" class="mt-1" />
                </div>

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
                    <label class="flex items-center">
                        <Checkbox v-model:checked="form.is_active" :disabled="processing" />
                        <span class="ml-2 text-sm">Активен</span>
                    </label>
                    <InputError :message="errors.is_active?.[0]" class="mt-1" />
                </div>
            </form>
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


