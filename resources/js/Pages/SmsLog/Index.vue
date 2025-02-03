<script setup>
import {Head, router, useForm} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { usePage } from '@inertiajs/vue3';
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import {useViewStore} from "@/store/view.js";
import ConfirmModal from "@/Components/Modals/ConfirmModal.vue";
import {useModalStore} from "@/store/modal.js";
import {onMounted, ref} from "vue";
import FiltersPanel from "@/Components/Filters/FiltersPanel.vue";
import InputFilter from "@/Components/Filters/Pertials/InputFilter.vue";
import FilterCheckbox from "@/Components/Filters/Pertials/FilterCheckbox.vue";

const modalStore = useModalStore();
const viewStore = useViewStore();
const smsLogs = usePage().props.smsLogs;
const smsLogsTotalCount = usePage().props.smsLogsTotalCount;
const senderStopList = usePage().props.senderStopList;
const currentTab = ref('logs');

const filters = ref(usePage().props.filters);
const filtersVariants = ref(usePage().props.filtersVariants);

const confirmAddSenderToStopLost = (smsLog) => {

    modalStore.openConfirmModal({
        title: `Добавить отправителя ${smsLog.sender} в стоп лист?`,
        body: `Все сообщения отправителя ${smsLog.sender} будут удалены, а новые сообщения будут игнорироваться.`,
        confirm_button_name: 'Подтвердить',
        confirm: () => {
            useForm({}).post(route('admin.sender-stop-list.store', smsLog.id), {
                preserveScroll: true,
                onFinish: () => {
                    router.visit(route('admin.sms-logs.index'))
                },
            });
        }
    });
};

const openPage = (tab) => {
    currentTab.value = tab;

    let data = {
        tab: tab,
        page: 1
    };

    router.visit(route(route().current()), {
        preserveScroll: true,
        data: data,
    })
}

const deleteSenderFromStopList = (senderStopList) => {
    useForm({}).delete(route('admin.sender-stop-list.destroy', senderStopList.id), {
        preserveScroll: true,
        onFinish: () => {
            router.visit(route('admin.sms-logs.index'), {
                data: {
                    tab: currentTab.value
                },
            })
        },
    });
}

onMounted(() => {
    let urlParams = new URLSearchParams(window.location.search);
    currentTab.value = urlParams.get('tab') ?? 'logs'
})

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Сообщения" />

        <MainTableSection
            title="Сообщения"
            :data="smsLogs"
            :display-pagination="currentTab === 'logs'"
            :query-data="{filters}"
        >
            <template v-slot:header>
                <ul v-if="viewStore.isAdminViewMode" class="flex flex-wrap text-sm font-medium text-center text-gray-500 dark:text-gray-400">
                    <li class="me-2">
                        <a @click.prevent="openPage('logs')" href="#" :class="currentTab === 'logs' ? 'shadow inline-flex items-center px-4 py-2 text-white bg-blue-600 rounded-xl active' : 'border border-gray-200 dark:border-gray-700 inline-flex items-center px-4 py-2 rounded-xl hover:text-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-white'" aria-current="page">
                            <svg class="w-4 h-4 sm:mr-2 mr-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.556 8.5h8m-8 3.5H12m7.111-7H4.89a.896.896 0 0 0-.629.256.868.868 0 0 0-.26.619v9.25c0 .232.094.455.26.619A.896.896 0 0 0 4.89 16H9l3 4 3-4h4.111a.896.896 0 0 0 .629-.256.868.868 0 0 0 .26-.619v-9.25a.868.868 0 0 0-.26-.619.896.896 0 0 0-.63-.256Z"/>
                            </svg>
                            <span class="sm:block hidden">Сообщения</span>
                        </a>
                    </li>
                    <li class="me-2">
                        <a @click.prevent="openPage('stop-list')" href="#" :class="currentTab === 'stop-list' ? 'shadow inline-flex items-center px-4 py-2 text-white bg-blue-600 rounded-xl active' : 'border border-gray-200 dark:border-gray-700 inline-flex items-center px-4 py-2 rounded-xl hover:text-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-white'" aria-current="page">
                            <svg class="w-4 h-4 sm:mr-2 mr-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="m6 6 12 12m3-6a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            </svg>
                            <span class="sm:block hidden">Стоп-лист</span>
                        </a>
                    </li>
                </ul>
            </template>
            <template v-slot:table-filters>
                <FiltersPanel name="sms-logs" :filters="filters">
                    <InputFilter
                        v-model="filters.search"
                        placeholder="Поиск"
                        class="w-64"
                    />
                    <FilterCheckbox
                        v-if="viewStore.isAdminViewMode"
                        v-model="filters.onlySuccessParsing"
                        title="Только зачисления"
                    />
                </FiltersPanel>
            </template>
            <template v-slot:body>
                <template v-if="currentTab === 'logs'">
                    <div v-if="viewStore.isAdminViewMode" class="flex gap-5">
                        <div class="text-base text-gray-500 dark:text-gray-400 mb-3">
                            Всего логов:
                            <span class="font-semibold text-gray-900 dark:text-gray-200 mr-1">
                            {{ smsLogsTotalCount}}
                        </span>
                        </div>
                    </div>
                    <div class="relative overflow-x-auto shadow-md rounded-table ">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">
                                    ID
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Отправитель
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Сообщение
                                </th>
                                <th scope="col" class="px-6 py-3" v-if="viewStore.isAdminViewMode">
                                    Парсинг
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Тип
                                </th>
                                <th scope="col" class="px-6 py-3" v-if="viewStore.isAdminViewMode">
                                    Трейдер
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Время
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="sms_log in smsLogs.data" class="bg-white border-b last:border-none dark:bg-gray-800 dark:border-gray-700">
                                <th scope="row" class="px-6 py-3 font-medium whitespace-nowrap text-gray-900 dark:text-gray-200">
                                    {{ sms_log.id }}
                                </th>
                                <td class="px-6 py-3">
                                    <div class="flex justify-between items-center gap-2">
                                        <template v-if="!viewStore.isAdminViewMode">
                                            <div>{{ sms_log.sender }}</div>
                                        </template>
                                        <template v-else>
                                            <div :class="{'text-green-500': sms_log.sender_exists}">
                                                {{ sms_log.sender }}
                                            </div>
                                            <div v-if="!sms_log.sender_exists">
                                                <button
                                                    @click.prevent="confirmAddSenderToStopLost(sms_log)"
                                                    class="px-0 py-0 text-red-500 hover:text-red-600 flex items-center hover:underline"
                                                >
                                                    <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </td>
                                <td class="px-6 py-3">
                                    <div style="min-width: 200px;">{{ sms_log.message }}</div>
                                </td>
                                <td class="px-6 py-3" v-if="viewStore.isAdminViewMode">
                                    <div v-if="sms_log.parsing_result">
                                        <div v-if="sms_log.parsing_result.amount" class="flex gap-1">
                                            <div class="text-gray-900 dark:text-gray-200">Сумма:</div>
                                            <div>{{sms_log.parsing_result.amount}}</div>
                                        </div>
                                        <div v-if="sms_log.parsing_result.card" class="flex gap-1">
                                            <div class="text-gray-900 dark:text-gray-200">Карта:</div>
                                            <div>*{{sms_log.parsing_result.card}}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-3">
                                    {{ sms_log.type }}
                                </td>
                                <td class="px-6 py-3" v-if="viewStore.isAdminViewMode">
                                    {{ sms_log.user.email }}
                                </td>
                                <td class="px-6 py-3 text-nowrap">
                                    {{ sms_log.created_at }}
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </template>
                <template v-else-if="currentTab === 'stop-list'">
                    <div class="flex flex-wrap gap-2">
                        <span v-for="(item, key) in senderStopList" :id="`sender-stop-list-${key}`" class="inline-flex items-center px-2 py-1 me-2 text-sm font-medium text-blue-800 bg-blue-100 rounded-lg dark:bg-blue-900 dark:text-blue-300">
                            {{ item.sender }}
                            <button @click.prevent="deleteSenderFromStopList(item)" type="button" class="inline-flex items-center p-1 text-sm text-blue-400 bg-transparent rounded-md hover:bg-blue-200 hover:text-blue-900 dark:hover:bg-blue-800 dark:hover:text-blue-300" :data-dismiss-target="`#sender-stop-list-${key}`" aria-label="Remove">
                                <svg class="w-2 h-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                                </svg>
                                <span class="sr-only">Удалить</span>
                            </button>
                        </span>
                    </div>
                </template>
            </template>
        </MainTableSection>

        <ConfirmModal/>
    </div>
</template>
