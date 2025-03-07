<script setup>
import ModalFooter from "@/Components/Modals/Components/ModalFooter.vue";
import ModalBody from "@/Components/Modals/Components/ModalBody.vue";
import Modal from "@/Components/Modals/Modal.vue";
import ModalHeader from "@/Components/Modals/Components/ModalHeader.vue";

import { storeToRefs } from 'pinia'
import { useModalStore } from "@/store/modal.js";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import {router, useForm} from "@inertiajs/vue3";
import InputHelper from "@/Components/InputHelper.vue";
import NumberInput from "@/Components/NumberInput.vue";
import TextArea from "@/Components/TextArea.vue";

const modalStore = useModalStore();
const { notificationModal } = storeToRefs(modalStore);

const close = () => {
    modalStore.closeModal('notification')
};

const form = useForm({
    message: null,
});

const send = () => {
    form.post(route('admin.notifications.store'), {
        preserveScroll: true,
        onSuccess: () => {
            router.visit(route('admin.notifications.index'));
            modalStore.closeAll()
        },
    });
}
</script>

<template>
    <Modal :show="notificationModal.showed" @close="close" maxWidth="md">
        <ModalHeader
            title="Рассылка уведомления"
            @close="close"
        />
        <ModalBody>
            <h1 class="text-gray-900 dark:text-gray-200 text-center">Введите сообщение которое будет отправлено всем пользователям в телеграмм.</h1>
            <form action="#" class="mx-auto max-w-screen-xl px-6 2xl:px-0 mt-8 mb-5">
                <div class="mx-auto max-w-3xl">
                    <div>
                        <div>
                            <InputLabel
                                for="message"
                                value="Сообщение"
                                :error="!!form.errors.message"
                            />

                            <TextArea
                                id="message"
                                class="mt-1 block w-full"
                                v-model="form.message"
                                required
                                :error="!!form.errors.message"
                                @input="form.clearErrors('message')"
                            />

                            <InputError class="mt-2" :message="form.errors.message" />
                        </div>
                    </div>
                </div>
            </form>
        </ModalBody>
        <ModalFooter>
            <div class="flex justify-center">
                <button
                    @click.prevent="send"
                    :disabled="form.processing"
                    type="button"
                    class="inline-flex items-center text-blue-700 hover:text-white border border-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-xl  text-sm px-5 py-2.5 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-500 dark:focus:ring-blue-800"
                >
                    Отправить
                </button>
            </div>
        </ModalFooter>
    </Modal>
</template>

<style scoped>

</style>
