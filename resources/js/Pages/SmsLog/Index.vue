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
import DateTime from "@/Components/DateTime.vue";
import DisplayUUID from "@/Components/DisplayUUID.vue";
import GatewayLogo from "@/Components/GatewayLogo.vue";
import {useTableFiltersStore} from "@/store/tableFilters.js";

const modalStore = useModalStore();
const viewStore = useViewStore();
const smsLogs = usePage().props.smsLogs;
const smsLogsTotalCount = usePage().props.smsLogsTotalCount;
const senderStopList = usePage().props.senderStopList;
const smsStopWords = usePage().props.smsStopWords;
const currentTab = ref('logs');
const newStopWord = ref('');
const tableFiltersStore = useTableFiltersStore();

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
    tableFiltersStore.setTab(tab);
    tableFiltersStore.setCurrentPage(1);

    router.visit(route(route().current()), {
        preserveScroll: true,
        data: tableFiltersStore.getQueryData,
    })
}

const deleteSenderFromStopList = (senderStopList) => {
    useForm({}).delete(route('admin.sender-stop-list.destroy', senderStopList.id), {
        preserveScroll: true,
        onFinish: () => {
            router.visit(route('admin.sms-logs.index'), {
                data: tableFiltersStore.getQueryData,
            })
        },
    });
}

const deleteSmsStopWord = (smsStopWord) => {
    useForm({}).delete(route('admin.sms-stop-word.destroy', smsStopWord.id), {
        preserveScroll: true,
        onFinish: () => {
            router.visit(route('admin.sms-logs.index'), {
                data: tableFiltersStore.getQueryData,
            })
        },
    });
}

const addSmsStopWord = () => {
    if (!newStopWord.value.trim()) return;

    useForm({
        word: newStopWord.value.trim()
    }).post(route('admin.sms-stop-word.store'), {
        preserveScroll: true,
        onFinish: () => {
            newStopWord.value = '';
            router.visit(route('admin.sms-logs.index'), {
                data: tableFiltersStore.getQueryData,
            })
        },
    });
}

onMounted(() => {
    if (tableFiltersStore.getTab === '') {
        tableFiltersStore.setTab('logs');
    }

    currentTab.value = tableFiltersStore.getTab
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
        >
            <template v-slot:header>
                <div v-if="viewStore.isAdminViewMode" role="tablist" class="tabs tabs-boxed">
                    <a role="tab" href="#" @click.prevent="openPage('logs')" :class="['tab flex items-center gap-2', currentTab === 'logs' && 'tab-active']">
                        <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.556 8.5h8m-8 3.5H12m7.111-7H4.89a.896.896 0 0 0-.629.256.868.868 0 0 0-.26.619v9.25c0 .232.094.455.26.619A.896.896 0 0 0 4.89 16H9l3 4 3-4h4.111a.896.896 0 0 0 .629-.256.868.868 0 0 0 .26-.619v-9.25a.868.868 0 0 0-.26-.619.896.896 0 0 0-.63-.256Z"/>
                        </svg>
                        <span class="sm:block hidden">Сообщения</span>
                    </a>
                    <a role="tab" href="#" @click.prevent="openPage('stop-list')" :class="['tab flex items-center gap-2', currentTab === 'stop-list' && 'tab-active']">
                        <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="m6 6 12 12m3-6a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                        <span class="sm:block hidden">Стоп-лист (отправители)</span>
                    </a>
                    <a role="tab" href="#" @click.prevent="openPage('stop-words')" :class="['tab flex items-center gap-2', currentTab === 'stop-words' && 'tab-active']">
                        <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 6H6m12 4H6m12 4H6m12 4H6"/>
                        </svg>
                        <span class="sm:block hidden">Стоп-слова</span>
                    </a>
                </div>
            </template>
            <template v-slot:table-filters>
                <FiltersPanel name="sms-logs" v-if="currentTab === 'logs'">
                    <InputFilter
                        name="search"
                        placeholder="Поиск"
                        class="w-64"
                    />
                    <FilterCheckbox
                        v-if="viewStore.isAdminViewMode"
                        name="onlySuccessParsing"
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
                    <div class="overflow-x-auto card bg-base-100 shadow">
                        <table class="table table-sm">
                            <thead class="text-xs uppercase bg-base-300">
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
                                <th scope="col" class="px-6 py-3 text-nowrap">
                                    UUID сделки
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Устройство
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
                            <tr v-for="sms_log in smsLogs.data" class="hover">
                                <th scope="row" class="px-6 py-3 font-medium whitespace-nowrap">
                                    {{ sms_log.id }}
                                </th>
                                <td class="px-6 py-3">
                                    <div class="flex justify-between items-center gap-2">
                                        <template v-if="!viewStore.isAdminViewMode">
                                            <div>{{ sms_log.sender }}</div>
                                        </template>
                                        <template v-else>
                                            <div class="flex items-center gap-3">
                                                <GatewayLogo v-if="sms_log.payment_gateway" :img_path="sms_log.payment_gateway.logo_path" class="w-10 h-10"/>
                                                <div>
                                                    <div :class="{'text-success': sms_log.sender_exists}">
                                                        {{ sms_log.sender }}
                                                    </div>
                                                    <div v-if="sms_log.payment_gateway" class="text-nowrap text-xs">
                                                        {{ sms_log.payment_gateway.name }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div v-if="!sms_log.sender_exists">
                                                <button
                                                    @click.prevent="confirmAddSenderToStopLost(sms_log)"
                                                    class="btn btn-ghost btn-xs text-error"
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
                                            <div class="font-medium">Сумма:</div>
                                            <div>{{sms_log.parsing_result.amount}}</div>
                                        </div>
                                        <div v-if="sms_log.parsing_result.card" class="flex gap-1">
                                            <div class="font-medium">Карта:</div>
                                            <div>*{{sms_log.parsing_result.card}}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-3">
                                    {{ sms_log.type }}
                                </td>
                                <td class="px-6 py-3">
                                    <DisplayUUID v-if="sms_log.order?.uuid" :uuid="sms_log.order?.uuid"/>
                                </td>
                                <td class="px-6 py-3 text-nowrap">
                                    {{ sms_log.device?.name }}
                                </td>
                                <td class="px-6 py-3" v-if="viewStore.isAdminViewMode">
                                    {{ sms_log.user.email }}
                                </td>
                                <td class="px-6 py-3 text-nowrap">
                                    <DateTime :data="sms_log.created_at"></DateTime>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </template>
                <template v-else-if="currentTab === 'stop-list'">
                    <div class="flex flex-wrap gap-2">
                        <span v-for="(item, key) in senderStopList" :id="`sender-stop-list-${key}`" class="badge badge-primary gap-1">
                            {{ item.sender }}
                            <button @click.prevent="deleteSenderFromStopList(item)" type="button" class="btn btn-ghost btn-xs" :data-dismiss-target="`#sender-stop-list-${key}`" aria-label="Remove">
                                ✕
                            </button>
                        </span>
                    </div>
                </template>
                <template v-else-if="currentTab === 'stop-words'">
                    <div class="mb-5">
                        <div class="flex items-center gap-2 mb-4">
                            <input
                                type="text"
                                v-model="newStopWord"
                                placeholder="Добавить стоп-слово"
                                class="input input-bordered w-52"
                            >
                            <button @click="addSmsStopWord" class="btn btn-primary">Добавить</button>
                        </div>
                        <p class="text-sm opacity-70 mb-4">
                            Стоп-слова используются для фильтрации SMS сообщений. Сообщения, содержащие эти слова, будут игнорироваться при парсинге.
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span v-for="(item, key) in smsStopWords" :id="`sms-stop-word-${key}`" class="badge badge-success gap-1">
                            {{ item.word }}
                            <button @click.prevent="deleteSmsStopWord(item)" type="button" class="btn btn-ghost btn-xs" :data-dismiss-target="`#sms-stop-word-${key}`" aria-label="Remove">✕</button>
                        </span>
                    </div>
                </template>
            </template>
        </MainTableSection>

        <ConfirmModal/>
    </div>
</template>
