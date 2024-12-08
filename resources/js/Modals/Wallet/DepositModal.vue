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

const props = defineProps({
    sourceType: {
        type: String,
    },
});

const modalStore = useModalStore();
const { depositModal } = storeToRefs(modalStore);

const close = () => {
    modalStore.closeModal('deposit')
};

const form = useForm({
    amount: null,
    source_type: null,
});

const deposit = () => {
    form
        .transform((data) => {
            data.source_type = props.sourceType;

            return data;
        })
        .post(route('admin.users.wallet.deposit', depositModal.value.params.user.id), {
            preserveScroll: true,
            onSuccess: () => {
                router.visit(route('admin.users.wallet.index', depositModal.value.params.user.id));
                modalStore.closeAll()
            },
        });
}
</script>

<template>
    <Modal :show="depositModal.showed" @close="close" maxWidth="md">
        <template v-if="sourceType === 'trust'">
            <ModalHeader
                title="Пополнение траст баланса"
                @close="close"
            />
        </template>
        <template v-if="sourceType === 'merchant'">
            <ModalHeader
                title="Пополнение мерчант баланса"
                @close="close"
            />
        </template>
        <ModalBody>
            <h1 class="text-gray-900 dark:text-gray-200 text-center">Введите сумму пополнения в USDT и нажмите «Продолжить»</h1>
            <form action="#" class="mx-auto max-w-screen-xl px-6 2xl:px-0 mt-8 mb-5">
                <div class="mx-auto max-w-3xl">
                    <div>
                        <div>
                            <InputLabel
                                for="amount"
                                value="Сумма пополнения"
                                :error="!!form.errors.amount"
                            />

                            <NumberInput
                                id="amount"
                                class="mt-1 block w-full"
                                v-model="form.amount"
                                placeholder="Сумма в USDT"
                                required
                                autofocus
                                :error="!!form.errors.amount"
                                @input="form.clearErrors('amount')"
                            />

                            <InputError class="mt-2" :message="form.errors.amount" />
                            <template v-if="sourceType === 'trust'">
                                <InputHelper v-if="! form.errors.amount" model-value="Если резерв меньше 1000 USDT, то часть депозита зачислится в резерв."></InputHelper>
                            </template>
                        </div>
                    </div>
                </div>
            </form>
        </ModalBody>
        <ModalFooter>
            <div class="flex justify-center">
                <button
                    @click.prevent="deposit"
                    type="button"
                    class="inline-flex items-center text-blue-700 hover:text-white border border-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-xl  text-sm px-5 py-2.5 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-500 dark:focus:ring-blue-800"
                >
                    Пополнить
                </button>
            </div>
        </ModalFooter>
    </Modal>
</template>

<style scoped>

</style>
