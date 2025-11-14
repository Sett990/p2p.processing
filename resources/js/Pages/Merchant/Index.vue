<script setup>
import {Head, router, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import {useViewStore} from "@/store/view.js";
import {useModalStore} from "@/store/modal.js";
import MerchantCreateModal from "@/Modals/Merchant/MerchantCreateModal.vue";
import MerchantStatisticsModal from "@/Modals/Merchant/MerchantStatisticsModal.vue";
import MerchantPaymentsModal from "@/Modals/Merchant/MerchantPaymentsModal.vue";
import MerchantSettingsModal from "@/Modals/Merchant/MerchantSettingsModal.vue";
import TableActionsDropdown from "@/Components/Table/TableActionsDropdown.vue";
import TableAction from "@/Components/Table/TableAction.vue";
import {computed, ref} from 'vue';

const viewStore = useViewStore();
const modalStore = useModalStore();

const page = usePage();
const merchants = ref(page.props.merchants);
const loading = ref(false);

const isAdminView = computed(() => viewStore.isAdminViewMode);

const fetchMerchants = async (pageNumber = null) => {
    loading.value = true;

    try {
        const prefix = isAdminView.value ? 'admin.' : '';
        const params = {};
        const currentPage = pageNumber ?? merchants.value?.meta?.current_page;

        if (currentPage) {
            params.page = currentPage;
        }

        const {data} = await axios.get(route(`${prefix}merchants.data`), {
            params,
            headers: {Accept: 'application/json'},
        });

        merchants.value = data;
    } catch (error) {
        console.error('[MerchantIndex] Не удалось обновить список мерчантов', error);
    } finally {
        loading.value = false;
    }
};

const openCreateModal = () => {
    modalStore.openMerchantCreateModal({
        onCreated: fetchMerchants,
    });
};

const openStatistics = (merchant) => {
    modalStore.openMerchantStatisticsModal({
        merchantId: merchant.id,
    });
};

const openPayments = (merchant) => {
    modalStore.openMerchantPaymentsModal({
        merchantId: merchant.id,
    });
};

const openSettings = (merchant) => {
    modalStore.openMerchantSettingsModal({
        merchantId: merchant.id,
        onUpdated: fetchMerchants,
    });
};

router.on('success', () => {
    merchants.value = usePage().props.merchants;
});

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Мерчанты" />

        <MainTableSection
            title="Мерчанты"
            :data="merchants"
        >
            <template v-slot:button>
                <div v-if="viewStore.isMerchantViewMode">
                    <button
                        @click="openCreateModal"
                        type="button"
                        class="btn btn-primary btn-sm sm:btn-md"
                    >
                        Создать мерчант
                    </button>
                </div>
            </template>
            <template v-slot:body>
                <div class="overflow-x-auto card bg-base-100 shadow" v-if="viewStore.isAdminViewMode">
                    <table class="table table-md">
                        <thead class="text-xs uppercase bg-base-300">
                            <tr>
                                <th>ID</th>
                                <th>Название</th>
                                <th>Владелец</th>
                                <th v-if="viewStore.isAdminViewMode">Статус</th>
                                <th class="text-center">
                                    <span class="sr-only">Действия</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="merchant in merchants.data">
                                <th class="whitespace-nowrap">{{ merchant.id }}</th>
                                <td>
                                    <div class="truncate max-w-48">{{ merchant.name }}</div>
                                    <div class="text-xs truncate max-w-36 text-base-content/70">{{ merchant.domain }}</div>
                                </td>
                                <td>
                                    {{ merchant.owner.email }}
                                </td>
                                <td>
                                    <div class="flex items-center text-nowrap">
                                        <template v-if="!merchant.validated_at">
                                            <div class="h-2.5 w-2.5 rounded-full bg-warning me-2"></div> На модерации
                                        </template>
                                        <template v-else-if="merchant.banned_at">
                                            <div class="h-2.5 w-2.5 rounded-full bg-error me-2"></div> Заблокирован
                                        </template>
                                        <template v-else-if="merchant.active">
                                            <div class="h-2.5 w-2.5 rounded-full bg-success me-2"></div> Включен
                                        </template>
                                        <template v-else>
                                            <div class="h-2.5 w-2.5 rounded-full bg-error me-2"></div> Выключен
                                        </template>
                                    </div>
                                </td>
                                <td class="text-right">
                                    <TableActionsDropdown>
                                        <TableAction @click="openStatistics(merchant)">
                                            Статистика
                                        </TableAction>
                                        <TableAction @click="openPayments(merchant)">
                                            Платежи
                                        </TableAction>
                                        <TableAction @click="openSettings(merchant)">
                                            Настройки
                                        </TableAction>
                                    </TableActionsDropdown>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <section v-if="viewStore.isMerchantViewMode" class="antialiased">
                    <div class="mx-auto">
                        <div class="mb-4 grid gap-4 md:mb-8 grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-3">
                            <div
                                v-for="(merchant, index) in merchants.data"
                                class="card bg-base-100 shadow"
                            >
                                <div class="card-body p-5 sm:p-6">
                                    <div class="flex items-start justify-between gap-2">
                                        <h3 class="card-title truncate">{{ merchant.name }}</h3>
                                        <TableActionsDropdown>
                                            <TableAction @click="openStatistics(merchant)">
                                                Статистика
                                            </TableAction>
                                            <TableAction @click="openPayments(merchant)">
                                                Платежи
                                            </TableAction>
                                            <TableAction @click="openSettings(merchant)">
                                                Настройки
                                            </TableAction>
                                        </TableActionsDropdown>
                                    </div>

                                    <div class="mt-1 flex items-center gap-2">
                                        <p class="text-sm text-base-content/70">доход за сегодня</p>
                                        <p class="text-sm font-medium">{{ merchant.today_profit }} {{ merchant.profit_currency?.toUpperCase() }}</p>
                                    </div>

                                    <p class="mt-2 text-lg font-semibold leading-tight text-primary truncate">
                                        {{ merchant.domain }}
                                    </p>

                                    <div class="mt-4 text-sm flex items-end justify-start">
                                        <div class="flex items-center text-nowrap">
                                            <template v-if="! merchant.validated_at">
                                                <div class="h-2.5 w-2.5 rounded-full bg-warning me-2"></div> На модерации
                                            </template>
                                            <template v-else-if="merchant.banned_at">
                                                <div class="h-2.5 w-2.5 rounded-full bg-error me-2"></div> Заблокирован
                                            </template>
                                            <template v-else-if="merchant.active">
                                                <div class="h-2.5 w-2.5 rounded-full bg-success me-2"></div> Включен
                                            </template>
                                            <template v-else>
                                                <div class="h-2.5 w-2.5 rounded-full bg-error me-2"></div> Выключен
                                            </template>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </template>
        </MainTableSection>
        <MerchantCreateModal />
        <MerchantStatisticsModal />
        <MerchantPaymentsModal />
        <MerchantSettingsModal />
    </div>
</template>
