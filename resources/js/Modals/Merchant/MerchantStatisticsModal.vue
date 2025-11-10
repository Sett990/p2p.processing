<script setup>
import Modal from "@/Components/Modals/Modal.vue";
import ModalHeader from "@/Components/Modals/Components/ModalHeader.vue";
import ModalBody from "@/Components/Modals/Components/ModalBody.vue";
import ModalFooter from "@/Components/Modals/Components/ModalFooter.vue";
import {storeToRefs} from "pinia";
import {useModalStore} from "@/store/modal.js";
import {computed, ref, watch} from "vue";
import {useViewStore} from "@/store/view.js";
import Statistics from "@/Pages/Merchant/Tabs/Statistics.vue";

const modalStore = useModalStore();
const viewStore = useViewStore();
const {merchantStatisticsModal} = storeToRefs(modalStore);

const statistics = ref(null);
const merchant = ref(null);
const loading = ref(false);
const error = ref(null);

const title = computed(() => {
    if (!merchant.value) {
        return "Статистика мерчанта";
    }

    return `Статистика — ${merchant.value.name ?? `#${merchant.value.id}`}`;
});

const close = () => {
    modalStore.closeModal('merchantStatistics');
};

const resetState = () => {
    statistics.value = null;
    merchant.value = null;
    error.value = null;
};

const fetchStatistics = async () => {
    const merchantId = merchantStatisticsModal.value.params?.merchantId;

    if (!merchantId) {
        return;
    }

    loading.value = true;
    error.value = null;

    try {
        const prefix = viewStore.isAdminViewMode ? 'admin.' : '';
        const {data} = await axios.get(route(`${prefix}merchants.statistics`, merchantId), {
            headers: {Accept: 'application/json'},
        });

        statistics.value = data.statistics ?? null;
        merchant.value = data.merchant ?? null;
    } catch (e) {
        error.value = e.response?.data?.message ?? 'Не удалось загрузить статистику мерчанта.';
    } finally {
        loading.value = false;
    }
};

watch(
    () => merchantStatisticsModal.value.showed,
    (showed) => {
        if (showed) {
            fetchStatistics();
        } else {
            resetState();
        }
    }
);
</script>

<template>
    <Modal :show="merchantStatisticsModal.showed" maxWidth="5xl" @close="close">
        <ModalHeader :title="title" @close="close" />
        <ModalBody>
            <div v-if="loading" class="py-8 text-center text-sm text-base-content/60">
                Загрузка статистики...
            </div>
            <div v-else-if="error" class="alert alert-error shadow">
                {{ error }}
            </div>
            <Statistics v-else-if="statistics" :statistics="statistics" />
            <div v-else class="py-8 text-center text-sm text-base-content/60">
                Данные отсутствуют.
            </div>
        </ModalBody>
        <ModalFooter>
            <button type="button" class="btn btn-sm" @click="close">
                Закрыть
            </button>
        </ModalFooter>
    </Modal>
</template>

