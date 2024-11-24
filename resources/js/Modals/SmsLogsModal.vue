<script setup>

import ModalBody from "@/Components/Modals/Components/ModalBody.vue";
import Modal from "@/Components/Modals/Modal.vue";
import ModalHeader from "@/Components/Modals/Components/ModalHeader.vue";

import { storeToRefs } from 'pinia'
import { useModalStore } from "@/store/modal.js";
import {ref, watch} from "vue";
import Pagination from "@/Components/Pagination/Pagination.vue";

const modalStore = useModalStore();
const { userSmsLogsModal } = storeToRefs(modalStore);

const smsLogs = ref({});

const close = () => {
    modalStore.closeModal('userSmsLogs')
};

watch(
    () => modalStore.userSmsLogsModal.showed,
    (state) => {
        if (state) {
            loadSmsLogs(currentPage.value)
        } else {
            smsLogs.value = {};
        }
    }
)

const currentPage = ref(1)

const openPage = (page) => {
    loadSmsLogs(page);
}

const loadSmsLogs = (page) => {
    axios.get(route('modal.user.sms-logs', modalStore.userSmsLogsModal.params.user.id), {
        params: {
            page: page,
        }
    })
        .then(response => {
            if (response.data.success) {
                smsLogs.value = response.data;
                currentPage.value = response.data.meta.current_page
            }
        });
}
</script>

<template>
    <Modal :show="userSmsLogsModal.showed" @close="close" maxWidth="lg">
        <ModalHeader
            title="Сообщения"
            @close="close"
        />
        <ModalBody>
            <div class="mx-auto space-y-6" v-if="smsLogs.data">
                <div class="relative overflow-x-auto">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400 border-separate border-spacing-y-3 rounded">
                        <tbody>
                        <tr
                            v-for="smsLog in smsLogs.data"
                            class="bg-gray-50/50 dark:bg-gray-700/50 rounded-lg"
                        >
                            <th
                                scope="row"
                                class="pl-3 pr-1 py-2 font-medium text-gray-900 whitespace-nowrap dark:text-gray-200 rounded-l-xl first:border-l last:border-r border-t border-b border-gray-300 dark:border-gray-700"
                            >
                                <div>#{{ smsLog.id }}</div>
                            </th>
                            <td class="px-3 py-2 first:border-l last:border-r border-t border-b border-gray-300 dark:border-gray-700">
                                <div class="flex text-nowrap text-gray-900 dark:text-gray-200">
                                    <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0 0a8.949 8.949 0 0 0 4.951-1.488A3.987 3.987 0 0 0 13 16h-2a3.987 3.987 0 0 0-3.951 3.512A8.948 8.948 0 0 0 12 21Zm3-11a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                    </svg>
                                    <span class="pl-1">{{ smsLog.sender }}</span>
                                </div>
                                <div class="flex text-nowrap text-gray-500 dark:text-gray-500">
                                    <svg class="h-4 w-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 10h16m-8-3V4M7 7V4m10 3V4M5 20h14a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Zm3-7h.01v.01H8V13Zm4 0h.01v.01H12V13Zm4 0h.01v.01H16V13Zm-8 4h.01v.01H8V17Zm4 0h.01v.01H12V17Zm4 0h.01v.01H16V17Z" />
                                    </svg>
                                    <span class="pl-1">
                                    {{ smsLog.timestamp }}
                                </span>
                                </div>
                            </td>
                            <td class="px-3 py-2 rounded-r-xl first:border-l last:border-r border-t border-b border-gray-300 dark:border-gray-700">
                                <div class="text-gray-900 dark:text-gray-200" style="min-width: 200px;">{{ smsLog.message }}</div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <Pagination
                    v-model="currentPage"
                    :total-items="smsLogs.meta.total"
                    previous-label="Назад" next-label="Вперед"
                    @page-changed="openPage"
                    :per-page="smsLogs.meta.per_page"
                ></Pagination>
            </div>
        </ModalBody>
    </Modal>
</template>

<style scoped>

</style>
