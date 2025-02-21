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

const modalStore = useModalStore();
const { editOrderAmountModal } = storeToRefs(modalStore);

const close = () => {
    modalStore.closeModal('editOrderAmount')
};

const form = useForm({
    amount: null,
});

const submit = () => {
    form
        .patch(route('orders.update.amount', editOrderAmountModal.value.params.order.id), {
            preserveScroll: true,
            onSuccess: () => {
                modalStore.closeAll();
            },
        });
}
</script>

<template>
    <Modal :show="editOrderAmountModal.showed" @close="close" maxWidth="md">
        <ModalHeader
            :title="`Cделка # ${editOrderAmountModal.params.order.uuid_short}`"
            @close="close"
        />
        <ModalBody>
            <form action="#" class="mx-auto max-w-screen-xl px-6 2xl:px-0 py-3">
                <div class="mx-auto max-w-3xl">
                    <div>
                        <div>
                            <InputLabel
                                for="amount"
                                value="Сумма сделки"
                                :error="!!form.errors.amount"
                            />
                            <NumberInput
                                id="amount"
                                class="mt-1 block w-full"
                                v-model="form.amount"
                                :placeholder="`Сумма в `+editOrderAmountModal.params.order.currency.toUpperCase()"
                                required
                                autofocus
                                :error="!!form.errors.amount"
                                @input="form.clearErrors('amount')"
                            />
                            <InputError class="mt-2" :message="form.errors.amount" />
                            <InputHelper v-if="! form.errors.amount" model-value="Прибыль мерчанта и комиссия сервиса будут пересчитаны по курсу и проценту комиссии на момент открытия сделки."></InputHelper>
                        </div>
                    </div>
                </div>
            </form>
        </ModalBody>
        <ModalFooter>
            <div class="flex justify-center">
                <button
                    @click.prevent="submit"
                    type="button"
                    class="inline-flex items-center text-blue-700 hover:text-white border border-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-500 dark:focus:ring-blue-800"
                >
                    Обновить
                </button>
            </div>
        </ModalFooter>
    </Modal>
</template>

<style scoped>

</style>
