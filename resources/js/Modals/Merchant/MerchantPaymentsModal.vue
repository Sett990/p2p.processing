<script setup>
import Modal from "@/Components/Modals/Modal.vue";
import ModalHeader from "@/Components/Modals/Components/ModalHeader.vue";
import ModalBody from "@/Components/Modals/Components/ModalBody.vue";
import ModalFooter from "@/Components/Modals/Components/ModalFooter.vue";
import {storeToRefs} from "pinia";
import {useModalStore} from "@/store/modal.js";
import {computed, ref, watch} from "vue";
import {useViewStore} from "@/store/view.js";
import Payments from "@/Pages/Merchant/Tabs/Payments.vue";

const modalStore = useModalStore();
const viewStore = useViewStore();
const {merchantPaymentsModal} = storeToRefs(modalStore);

const merchant = ref(null);
const orders = ref({
    data: [],
    meta: {
        current_page: 1,
        per_page: 10,
        total: 0,
    },
});
const loading = ref(false);
const error = ref(null);

const title = computed(() => {
    if (!merchant.value) {
        return "Оплаченные сделки";
    }

    return `Оплаченные сделки — ${merchant.value.name ?? `#${merchant.value.id}`}`;
});

const close = () => {
    modalStore.closeModal('merchantPayments');
};

const resetState = () => {
    merchant.value = null;
    orders.value = {
        data: [],
        meta: {
            current_page: 1,
            per_page: 10,
            total: 0,
        },
    };
    error.value = null;
};

const fetchPayments = async (page = 1) => {
    const merchantId = merchantPaymentsModal.value.params?.merchantId;

    if (!merchantId) {
        return;
    }

    loading.value = true;
    error.value = null;

    try {
        const prefix = viewStore.isAdminViewMode ? 'admin.' : '';
        const {data} = await axios.get(route(`${prefix}merchants.payments`, merchantId), {
            params: {page},
            headers: {Accept: 'application/json'},
        });

        merchant.value = data.merchant ?? null;
        orders.value = data.orders ?? orders.value;
    } catch (e) {
        error.value = e.response?.data?.message ?? 'Не удалось загрузить список сделок.';
    } finally {
        loading.value = false;
    }
};

const handlePageChange = (page) => {
    fetchPayments(page);
};

watch(
    () => merchantPaymentsModal.value.showed,
    (showed) => {
        if (showed) {
            fetchPayments(merchantPaymentsModal.value.params?.page ?? 1);
        } else {
            resetState();
        }
    }
);
</script>

<template>
    <Modal :show="merchantPaymentsModal.showed" maxWidth="6xl" @close="close">
        <ModalHeader :title="title" @close="close" />
        <ModalBody>
            <div v-if="loading" class="py-8 text-center text-sm text-base-content/60">
                Загрузка списка сделок...
            </div>
            <div v-else-if="error" class="alert alert-error shadow">
                {{ error }}
            </div>
            <Payments
                v-else
                :orders="orders"
                :merchant="merchant"
                :loading="loading"
                @openPage="handlePageChange"
            />
        </ModalBody>
        <ModalFooter>
            <button type="button" class="btn btn-sm" @click="close">
                Закрыть
            </button>
        </ModalFooter>
    </Modal>
</template>

