<script setup>
import {Head, router, usePage} from '@inertiajs/vue3';
import {computed, onBeforeUnmount, onMounted, ref, watch} from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MainTableSection from '@/Wrappers/MainTableSection.vue';
import GatewayLogo from '@/Components/GatewayLogo.vue';
import DateTime from '@/Components/DateTime.vue';
import Pagination from '@/Components/Pagination/Pagination.vue';
import ConfirmModal from '@/Components/Modals/ConfirmModal.vue';
import {useModalStore} from '@/store/modal.js';
import { formatDistanceStrict } from 'date-fns';
import DisplayUUID from "../../../Components/DisplayUUID.vue";

const props = defineProps({
    orderBook: {
        type: Array,
        required: true,
    },
    activePayouts: {
        type: Array,
        required: true,
    },
    history: {
        type: Object,
        required: true,
    },
    refresh: {
        type: Object,
        required: true,
    },
    limits: {
        type: Object,
        required: true,
    },
});

const page = usePage();

const trader = computed(() => page.props.auth?.user ?? {});
const orderBook = computed(() => props.orderBook ?? []);
const activePayouts = computed(() => props.activePayouts ?? []);
const history = computed(() => props.history ?? { data: [], meta: {} });

const normalizeCollection = (collection) => {
    if (Array.isArray(collection)) {
        return collection;
    }

    if (Array.isArray(collection?.data)) {
        return collection.data;
    }

    return [];
};

const orderBookList = computed(() => normalizeCollection(orderBook.value));
const activePayoutsList = computed(() => normalizeCollection(activePayouts.value));

const refreshInterval = ref(props.refresh.interval ?? 5);
const autoRefreshTimer = ref(null);
const isRefreshing = ref(false);
const historyPage = ref(history.value?.meta?.current_page ?? 1);

watch(
    () => history.value?.meta?.current_page,
    (value) => {
        historyPage.value = value ?? 1;
    },
);

const canTakeMore = computed(() => (props.limits?.currentActive ?? 0) < (props.limits?.maxActive ?? 1));

const reloadData = (targetHistoryPage = historyPage.value, replace = true) => {
    isRefreshing.value = true;
    router.visit(route('trader.payouts.index'), {
        method: 'get',
        data: {
            refresh_interval: refreshInterval.value,
            history_page: targetHistoryPage,
        },
        preserveScroll: true,
        preserveState: true,
        replace,
        onFinish: () => {
            isRefreshing.value = false;
        },
    });
};

const startAutoRefresh = () => {
    stopAutoRefresh();

    if (refreshInterval.value > 0) {
        autoRefreshTimer.value = setInterval(() => {
            reloadData(historyPage.value, false);
        }, refreshInterval.value * 1000);
    }
};

const stopAutoRefresh = () => {
    if (autoRefreshTimer.value) {
        clearInterval(autoRefreshTimer.value);
        autoRefreshTimer.value = null;
    }
};

watch(refreshInterval, () => {
    startAutoRefresh();

    if (refreshInterval.value > 0) {
        reloadData(historyPage.value, false);
    }
});

onMounted(() => {
    startAutoRefresh();
});

onBeforeUnmount(() => {
    stopAutoRefresh();
});

const takePayout = (payout) => {
    router.post(route('trader.payouts.take', payout.uuid), {}, {
        preserveScroll: true,
        onStart: () => {
            stopAutoRefresh();
        },
        onFinish: () => {
            startAutoRefresh();
        },
    });
};

const markPayoutSent = (payout) => {
    router.post(route('trader.payouts.mark-sent', payout.uuid), {}, {
        preserveScroll: true,
        onStart: () => {
            stopAutoRefresh();
        },
        onFinish: () => {
            startAutoRefresh();
        },
    });
};

const changeHistoryPage = (pageNumber) => {
    historyPage.value = pageNumber;
    reloadData(pageNumber, true);
};

const formatHoldCountdown = (timestamp) => {
    if (!timestamp) {
        return null;
    }

    const target = new Date(timestamp);
    const now = new Date();

    if (target < now) {
        return 'ожидает подтверждения';
    }

    return formatDistanceStrict(now, target, { roundingMethod: 'floor', addSuffix: true });
};

const payoutEmptyState = computed(() => orderBookList.value.length === 0);
const activeEmptyState = computed(() => activePayoutsList.value.length === 0);

const modalStore = useModalStore();

const confirmMarkPayoutSent = (payout) => {
    modalStore.openConfirmModal({
        title: 'Подтверждение отправки средств',
        body: 'Вы уверены, что отправили средства по этой выплате? Действие изменить будет нельзя.',
        confirm_button_name: 'Да, отправил',
        cancel_button_name: 'Отмена',
        confirm: () => {
            markPayoutSent(payout);
        },
    });
};

defineOptions({ layout: AuthenticatedLayout });
</script>

<template>
    <div>
        <Head title="Выплаты" />

        <MainTableSection
            title="Выплаты"
            :data="history"
        >
            <template #header>
                <div class="space-y-6">
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="stat rounded-box shadow bg-base-100 w-full sm:w-auto">
                            <div class="stat-title">Активных выплат</div>
                            <div class="stat-value text-primary text-3xl">{{ limits.currentActive }}</div>
                            <div class="stat-desc">из {{ limits.maxActive }}</div>
                        </div>
                        <div class="stat rounded-box shadow bg-base-100 w-full sm:w-auto">
                            <div class="stat-title">Холд для вас</div>
                            <div class="stat-value text-secondary text-3xl">
                                {{ trader.payout_hold_enabled ? trader.payout_hold_minutes : 0 }}
                            </div>
                            <div class="stat-desc">
                                {{ trader.payout_hold_enabled ? 'минут ожидания' : 'Холд отключен' }}
                            </div>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-semibold text-base-content">Автообновление</span>
                            <select
                                class="select select-bordered select-sm w-40"
                                v-model.number="refreshInterval"
                            >
                                <option
                                    v-for="interval in refresh.options"
                                    :value="interval"
                                    :key="interval"
                                >
                                    {{ interval === 0 ? 'Выкл.' : `${interval} c` }}
                                </option>
                            </select>
                        </div>
                        <button
                            class="btn btn-sm btn-outline"
                            :disabled="isRefreshing"
                            @click="reloadData(historyPage, false)"
                        >
                            <span class="flex items-center gap-2">
                                <span>Обновить</span>
                                <span v-if="isRefreshing" class="loading loading-spinner loading-xs"></span>
                            </span>
                        </button>
                    </div>

                    <div class="space-y-6">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <h2 class="text-xl font-semibold">Ваши активные выплаты</h2>
                                <span v-if="activeEmptyState" class="text-sm text-base-content/60">Нет активных выплат</span>
                            </div>
                            <div class="space-y-3">
                                <div
                                    v-for="payout in activePayoutsList"
                                    :key="payout.id"
                                    class="card bg-base-100 shadow"
                                >
                                    <div class="card-body space-y-3">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="inline-flex items-center gap-7">
                                                <div class="flex items-center gap-3">
                                                    <div v-if="payout.payout_method_type.value === 'sbp'" class="relative">
                                                        <img src="/images/sbp.svg" class="w-10 h-10">
                                                        <GatewayLogo
                                                            :img_path="payout.payment_gateway.logo"
                                                            :name="payout.payment_gateway.name"
                                                            class="absolute right-[-3px] bottom-[-3px] w-5 h-5 bg-base-100 border border-base-300 rounded-full"
                                                        />
                                                    </div>
                                                    <div v-else>
                                                        <GatewayLogo
                                                            :img_path="payout.payment_gateway.logo"
                                                            :name="payout.payment_gateway.name"
                                                            class="w-10 h-10"
                                                        />
                                                    </div>
                                                    <div>
                                                        <div class="text-nowrap font-semibold">
                                                            {{ payout.requisites }}
                                                        </div>
                                                        <div class="text-xs text-base-content/60">
                                                            {{ payout.payment_gateway.name }} · {{ payout.payout_method_type.label }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="text-base-content/60 text-xs uppercase">Сумма</div>
                                                    <div class="font-semibold">
                                                        {{ payout.amount.fiat }} {{ payout.amount.currency }}
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="text-base-content/60 text-xs uppercase">Получатель</div>
                                                    <div class="font-semibold">{{ payout.initials }}</div>
                                                </div>
                                            </div>
                                            <div class="inline-flex items-center gap-5">
                                                <div>
                                                    <div class="badge badge-outline badge-sm">
                                                        {{ payout.status_label }}
                                                    </div>
                                                </div>
                                                <button
                                                    class="btn btn-sm btn-success"
                                                    v-if="payout.status === 'taken'"
                                                    @click="confirmMarkPayoutSent(payout)"
                                                >
                                                    Отправил средства
                                                </button>
                                                <div v-else class="text-sm text-base-content/70">
                                                    Холд: {{ formatHoldCountdown(payout.timings.hold_until) ?? 'ожидаем завершения' }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between bg-base-300 py-2 px-4 rounded-box text-xs">
                                            <div class="content-center">
                                                <div class="text-base-content/60 uppercase">Сумма в USDT</div>
                                                <div class="font-semibold">
                                                    {{ payout.usdt_body.value }} {{ payout.usdt_body.currency }}
                                                </div>
                                            </div>
                                            <div class="content-center">
                                                <div class="text-base-content/60 uppercase">Будет зачислено</div>
                                                <div class="font-semibold">
                                                    {{ payout.trader_credit.value }} {{ payout.trader_credit.currency }}
                                                </div>
                                            </div>
                                            <div>
                                                <div class="text-base-content/60 text-xs uppercase">Ваша прибыль</div>
                                                <div class="font-semibold">{{ payout.commissions.trader_fee }} USDT ({{ payout.commissions.trader_rate }}%)</div>
                                            </div>
                                            <div>
                                                <div class="text-base-content/60 text-xs uppercase">Взяли в работу</div>
                                                <DateTime :data="payout.timings.taken_at" simple class="justify-start font-semibold" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <h2 class="text-xl font-semibold">Стакан доступных выплат</h2>
                                <span v-if="payoutEmptyState" class="text-sm text-base-content/60">Пока нет заявок</span>
                            </div>
                            <div class="relative">
                                <!-- Desktop / tablet (table) -->
                                <div class="hidden xl:block rounded-table relative">
                                    <div class="overflow-x-auto card bg-base-100 shadow">
                                        <table class="table table-sm">
                                            <thead class="text-xs uppercase bg-base-300">
                                            <tr>
                                                <th scope="col">
                                                    Реквизит
                                                </th>
                                                <th scope="col">
                                                    К отправке
                                                </th>
                                                <th scope="col">
                                                    К получению
                                                </th>
                                                <th scope="col">
                                                    Доход
                                                </th>
                                                <th scope="col">
                                                    Истекает
                                                </th>
                                                <th scope="col" class="text-right">
                                                    <span class="sr-only">Действия</span>
                                                </th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr
                                                v-for="payout in orderBookList"
                                                :key="payout.id"
                                                class="bg-base-100 border-b last:border-none border-base-200"
                                            >
                                                <td>
                                                    <div class="flex items-center gap-3">
                                                        <div v-if="payout.payout_method_type.value === 'sbp'" class="relative">
                                                            <img src="/images/sbp.svg" class="w-10 h-10">
                                                            <GatewayLogo
                                                                :img_path="payout.payment_gateway.logo"
                                                                :name="payout.payment_gateway.name"
                                                                class="absolute right-[-3px] bottom-[-3px] w-5 h-5 bg-base-100 border border-base-300 rounded-full"
                                                            />
                                                        </div>
                                                        <div v-else>
                                                            <GatewayLogo
                                                                :img_path="payout.payment_gateway.logo"
                                                                :name="payout.payment_gateway.name"
                                                                class="w-10 h-10"
                                                            />
                                                        </div>
                                                        <div>
                                                            <div class="text-nowrap text-base-content">
                                                                {{ payout.requisites }}
                                                            </div>
                                                            <div class="text-xs text-base-content/60">
                                                                {{ payout.payment_gateway.name }} · {{ payout.payout_method_type.label }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div>
                                                        {{ payout.amount.fiat }} {{ payout.amount.currency }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div>
                                                        {{ payout.trader_credit.value }} {{ payout.trader_credit.currency }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div >
                                                        {{ payout.commissions.trader_fee }} USDT
                                                    </div>
                                                </td>
                                                <td>
                                                    <DateTime :data="payout.timings.expires_at" simple class="justify-start" />
                                                </td>
                                                <td class="text-right">
                                                    <button
                                                        class="btn btn-primary btn-sm"
                                                        @click="takePayout(payout)"
                                                        :disabled="!canTakeMore || isRefreshing"
                                                    >
                                                        Взять
                                                    </button>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Mobile (cards list) -->
                                <div class="xl:hidden space-y-3">
                                    <div
                                        v-for="payout in orderBookList"
                                        :key="payout.id"
                                        class="card bg-base-100 shadow-sm border border-base-200"
                                    >
                                        <div class="card-body space-y-3">
                                            <div class="flex flex-wrap items-center gap-3 justify-between">
                                                <div class="inline-flex items-center gap-3">
                                                    <div class="relative">
                                                        <img src="/images/sbp.svg" class="w-10 h-10">
                                                        <GatewayLogo
                                                            :img_path="payout.payment_gateway.logo"
                                                            :name="payout.payment_gateway.name"
                                                            class="absolute right-[-3px] bottom-[-3px] w-5 h-5 bg-base-100 border border-base-300 rounded-full"
                                                        />
                                                    </div>
                                                    <div>
                                                        <div><span class="font-semibold">{{ payout.requisites }}</span></div>
                                                        <div class="text-sm text-base-content/60">
                                                            {{ payout.payment_gateway.name }} · {{ payout.payout_method_type.label }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <button
                                                    class="btn btn-primary btn-sm"
                                                    @click="takePayout(payout)"
                                                    :disabled="!canTakeMore || isRefreshing"
                                                >
                                                    Взять
                                                </button>
                                            </div>
                                            <div class="grid md:grid-cols-4 gap-4 text-sm">
                                                <div class="space-y-1">
                                                    <div class="text-base-content/60 text-xs uppercase">Отправляете</div>
                                                    <div class="font-semibold">
                                                        {{ payout.amount.fiat }} {{ payout.amount.currency }}
                                                    </div>
                                                </div>
                                                <div class="space-y-1">
                                                    <div class="text-base-content/60 text-xs uppercase">Получаете</div>
                                                    <div class="font-semibold">
                                                        {{ payout.trader_credit.value }} {{ payout.trader_credit.currency }}
                                                    </div>
                                                </div>
                                                <div class="space-y-1">
                                                    <div class="text-base-content/60 text-xs uppercase">Зарабатываете</div>
                                                    <div>{{ payout.commissions.trader_fee }} USDT</div>
                                                </div>
                                                <div class="space-y-1">
                                                    <div class="text-base-content/60 text-xs uppercase">Создано</div>
                                                    <div><DateTime :data="payout.timings.created_at" simple /></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
            <template #body>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold">История выплат</h2>
                    </div>
                    <div class="rounded-table relative">
                        <div class="overflow-x-auto card bg-base-100 shadow">
                            <table class="table table-sm">
                                <thead class="text-xs uppercase bg-base-300">
                                <tr>
                                    <th>UUID</th>
                                    <th>Реквизит</th>
                                    <th>Сумма</th>
                                    <th>Зачислено</th>
                                    <th>Доход</th>
                                    <th>Статус</th>
                                    <th>Завершено</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="payout in history.data" :key="payout.id">
                                    <td class="font-mono text-xs">
                                        <DisplayUUID :uuid="payout.uuid"/>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div v-if="payout.payout_method_type.value === 'sbp'" class="relative">
                                                <img src="/images/sbp.svg" class="w-8 h-8">
                                                <GatewayLogo
                                                    :img_path="payout.payment_gateway.logo"
                                                    :name="payout.payment_gateway.name"
                                                    class="absolute right-[-3px] bottom-[-3px] w-5 h-5 bg-base-100 border border-base-300 rounded-full"
                                                />
                                            </div>
                                            <div v-else>
                                                <GatewayLogo
                                                    :img_path="payout.payment_gateway.logo"
                                                    :name="payout.payment_gateway.name"
                                                    class="w-10 h-10"
                                                />
                                            </div>
                                            <div>
                                                <div class="text-nowrap text-base-content">
                                                    {{ payout.requisites }}
                                                </div>
                                                <div class="text-xs text-base-content/60">
                                                    {{ payout.payment_gateway.name }} · {{ payout.payout_method_type.label }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            {{ payout.amount.fiat }} {{ payout.amount.currency }}
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            {{ payout.trader_credit.value }} {{ payout.trader_credit.currency }}
                                        </div>
                                    </td>
                                    <td>
                                        {{ payout.commissions.trader_fee }} USDT
                                    </td>
                                    <td>
                                        <div class="badge badge-outline badge-sm">{{ payout.status_label }}</div>
                                    </td>
                                    <td>
                                        <DateTime :data="payout.timings.completed_at" simple class="justify-start" />
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <div v-if="(history?.data?.length ?? 0) === 0" class="py-6 text-center text-sm text-base-content/60">
                                История пока пуста.
                            </div>
                        </div>
                    </div>
                </div>
            </template>
            <template #footer>
                <div class="flex justify-end">
                    <Pagination
                        v-if="history?.meta"
                        :model-value="historyPage"
                        :total-pages="history.meta.last_page"
                        :per-page="history.meta.per_page"
                        :total-items="history.meta.total"
                        @page-changed="changeHistoryPage"
                    />
                </div>
            </template>
        </MainTableSection>
        <ConfirmModal />
    </div>
</template>

