<script setup>
import Modal from "@/Components/Modals/Modal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import { storeToRefs } from 'pinia'
import {ref} from "vue";
import { useModalStore } from "@/store/modal.js";

const modalStore = useModalStore();
const { confirmModal } = storeToRefs(modalStore);

const processing = ref(false);

const close = () => {
    modalStore.closeModal('confirm')
};
const confirm = () => {
    processing.value = true;
    confirmModal.value.params.confirm()
    processing.value = false;
    modalStore.closeModal('confirm')
};
</script>

<template>
    <Modal :show="confirmModal.showed" @close="close">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ confirmModal.params.title }}
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ confirmModal.params.body }}
            </p>

            <div class="mt-6 flex justify-end">
                <SecondaryButton @click="close">{{ confirmModal.params.cancel_button_name }}</SecondaryButton>

                <button
                    type="button"
                    :class="{ 'opacity-25': processing }"
                    :disabled="processing"
                    @click="confirm"
                    class="ms-3 inline-flex items-center px-4 py-2 bg-blue-800 focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest focus:outline-none dark:focus:ring-blue-800 hover:bg-blue-800 dark:hover:bg-blue-700 focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    {{ confirmModal.params.confirm_button_name }}
                </button>
            </div>
        </div>
    </Modal>
</template>

<style scoped>

</style>
