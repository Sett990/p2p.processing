<script setup>
import Modal from "@/Components/Modals/Modal.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import TextInput from "@/Components/TextInput.vue";
import InputHelper from "@/Components/InputHelper.vue";
import {router, useForm} from "@inertiajs/vue3";
import ModalHeader from "@/Components/Modals/Components/ModalHeader.vue";
import ModalBody from "@/Components/Modals/Components/ModalBody.vue";
import { storeToRefs } from 'pinia'
import { useModalStore } from "@/store/modal.js";

const modalStore = useModalStore();
const { disputeCancelModal } = storeToRefs(modalStore);

const close = () => {
    modalStore.closeModal('disputeCancel');
};

const form = useForm({
    reason: '',
});

const cancel = (dispute) => {
    form.patch(route('disputes.cancel', dispute.id), {
        preserveScroll: true,
        onSuccess: () => {
            modalStore.closeAll()
            router.visit(route(route().current()))
        },
    });
};
</script>

<template>
    <Modal :show="disputeCancelModal.showed" @close="close" maxWidth="md">
        <ModalHeader
            :title="'Отклонение спора #' + disputeCancelModal.params.dispute.id"
            @close="close"
        />
        <ModalBody>
            <form action="#" class="py-3">
                <div>
                    <InputLabel
                        for="reason"
                        value="Причина"
                        :error="!!form.errors.reason"
                    />

                    <TextInput
                        id="reason"
                        v-model="form.reason"
                        class="mt-1 block w-full"
                        placeholder="Неверные реквизиты"
                        :error="!!form.errors.reason"
                        @input="form.clearErrors('reason')"
                    />

                    <InputError :message="form.errors.reason" class="mt-2" />
                    <InputHelper v-if="! form.errors.reason" model-value="Укажите причину отклонения спора"></InputHelper>
                </div>
            </form>
        </ModalBody>
        <div class="flex justify-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
            <button
                @click.prevent="close"
                type="button"
                class="mr-2 text-red-700 hover:text-white border border-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-xl  text-sm px-5 py-2.5 text-center dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900 inline-flex items-center"
            >
                Отмена
            </button>
            <button
                @click.prevent="cancel(disputeCancelModal.params.dispute)"
                :disabled="form.processing"
                type="button"
                class="text-blue-700 hover:text-white border border-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-xl  text-sm px-5 py-2.5 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-500 dark:focus:ring-blue-800"
            >
                Подтвердить
            </button>
        </div>
    </Modal>
</template>

<style scoped>

</style>
