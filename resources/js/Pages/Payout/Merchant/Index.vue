<script setup>
import {Head, usePage} from '@inertiajs/vue3';
import {computed, ref} from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MainTableSection from '@/Wrappers/MainTableSection.vue';
import FiltersPanel from '@/Components/Filters/FiltersPanel.vue';
import DateFilter from '@/Components/Filters/Pertials/DateFilter.vue';
import InputFilter from '@/Components/Filters/Pertials/InputFilter.vue';
import DropdownFilter from '@/Components/Filters/Pertials/DropdownFilter.vue';
import RefreshTableData from '@/Components/Table/RefreshTableData.vue';
import GatewayLogo from '@/Components/GatewayLogo.vue';
import DisplayUUID from '@/Components/DisplayUUID.vue';
import DateTime from '@/Components/DateTime.vue';

const payouts = computed(() => usePage().props.payouts ?? { data: [] });
const payoutItems = computed(() => payouts.value?.data ?? []);
const reloadingTableData = ref(false);
const expandedRows = ref({});

const toggleRow = (id) => {
    expandedRows.value[id] = !expandedRows.value[id];
};

const isExpanded = (id) => !!expandedRows.value[id];

const statusClasses = {
    open: 'badge-warning',
    taken: 'badge-info',
    sent: 'badge-accent',
    completed: 'badge-success',
    canceled: 'badge-error',
};

const statusBadge = (status) => statusClasses[status] ?? 'badge-ghost';

const formatMoney = (money, empty = '—') => {
    if (!money) {
        return empty;
    }

    return `${money.value} ${money.currency ?? ''}`.trim();
};

const formatMeta = (meta) => {
    if (!meta) {
        return 'Нет данных';
    }

    try {
        return JSON.stringify(meta, null, 2);
    } catch (error) {
        return String(meta);
    }
};

defineOptions({ layout: AuthenticatedLayout });
</script>

<template>
    <div>
        <Head title="Выплаты" />

        <MainTableSection
            title="Выплаты"
            :data="payouts"
        >
            <template #header>
                <div class="space-y-4">
                    <FiltersPanel name="merchant-payouts">
                        <DateFilter name="startDate" title="Создано с" />
                        <DateFilter name="endDate" title="Создано по" />
                        <InputFilter name="uuid" placeholder="UUID" />
                        <InputFilter name="paymentDetail" placeholder="Реквизит" />
                        <DropdownFilter name="merchantIds" title="Мерчант" />
                        <DropdownFilter name="payoutStatuses" title="Статусы" />
                        <DropdownFilter name="payoutMethodTypes" title="Тип реквизитов" />
                        <InputFilter name="paymentGateway" placeholder="Банк / метод" />
                        <InputFilter name="amount" placeholder="Сумма (точная)" />
                        <InputFilter name="minAmount" placeholder="Мин. сумма" />
                        <InputFilter name="maxAmount" placeholder="Макс. сумма" />
                        <InputFilter name="currency" placeholder="Валюта (например, RUB)" />
                    </FiltersPanel>

                    <div class="flex items-center justify-between">
                        <div
                            v-if="reloadingTableData"
                            class="px-2 text-sm text-base-content/80 flex items-center gap-2"
                            aria-live="polite"
                        >
                            <span class="loading loading-spinner loading-sm text-primary" />
                            <span>Обновляем данные...</span>
                        </div>

                        <RefreshTableData
                            @refresh-started="reloadingTableData = true"
                            @refresh-finished="reloadingTableData = false"
                        />
                    </div>
                </div>
            </template>
            <template #body>
                <div class="relative">
                    <div class="hidden xl:block rounded-table relative">
                        <div
                            class="card sticky top-0 left-0 bg-base-100/40 z-10 flex items-center justify-center backdrop-blur-sm transition-all duration-300 ease-in-out opacity-0 pointer-events-none"
                            :class="{'opacity-100 pointer-events-auto': reloadingTableData}"
                            style="position: absolute; inset: 0; width: 100%; height: 100%;"
                        >
                            <div class="flex flex-col items-center transition-transform duration-300" :class="{'scale-90 opacity-0': !reloadingTableData, 'scale-100 opacity-100': reloadingTableData}">
                                <span class="loading loading-spinner loading-lg text-primary" />
                                <span class="mt-3 text-sm font-medium text-base-content">Загрузка данных...</span>
                            </div>
                        </div>

                        <div class="overflow-x-auto card bg-base-100 shadow">
                            <table class="table table-sm" :class="{'pointer-events-none': reloadingTableData}">
                                <thead class="text-xs uppercase bg-base-300">
                                <tr>
                                    <th>UUID</th>
                                    <th>Реквизиты</th>
                                    <th>Сумма</th>
                                    <th>Комиссия</th>
                                    <th>Статус</th>
                                    <th>Мерчант</th>
                                    <th class="w-24">Подробнее</th>
                                </tr>
                                </thead>
                                <tbody>
                                <template v-for="payout in payoutItems" :key="payout.id">
                                    <tr class="bg-base-100 border-base-200 border-b last:border-none align-top">
                                        <td>
                                            <DisplayUUID :uuid="payout.uuid" class="text-sm font-semibold" />
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-3">
                                                <div v-if="payout.payout_method_type.value === 'sbp'" class="relative">
                                                    <img src="/images/sbp.svg" class="w-10 h-10" alt="СБП">
                                                    <GatewayLogo
                                                        v-if="payout.payment_gateway?.logo"
                                                        :img_path="payout.payment_gateway.logo"
                                                        :name="payout.payment_gateway?.name"
                                                        class="absolute right-[-4px] bottom-[-4px] w-5 h-5 bg-base-100 border border-base-300 rounded-full"
                                                    />
                                                </div>
                                                <div v-else>
                                                    <GatewayLogo
                                                        :img_path="payout.payment_gateway?.logo"
                                                        :name="payout.payment_gateway?.name"
                                                        class="w-10 h-10"
                                                    />
                                                </div>
                                                <div>
                                                    <div class="font-semibold text-base-content">{{ payout.requisites }}</div>
                                                    <div class="text-xs text-base-content/60">
                                                        {{ payout.payment_gateway?.name }} · {{ payout.payout_method_type.label }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <div class="text-nowrap text-base-content">
                                                    {{ formatMoney(payout.amount) }}
                                                </div>
                                                <div class="text-nowrap text-xs text-base-content/60">
                                                    {{ formatMoney(payout.usdt_body) }}
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                {{ payout.fees?.total ?? '—' }} {{ payout.fees?.currency ?? '' }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="badge badge-sm" :class="statusBadge(payout.status)">
                                                {{ payout.status_label }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-xs text-base-content max-w-35">
                                                {{ payout.merchant?.name ?? '—' }}
                                            </div>
                                        </td>
                                        <td class="text-center align-center">
                                            <button
                                                class="btn btn-ghost btn-xs text-xs"
                                                type="button"
                                                @click="toggleRow(payout.id)"
                                            >
                                                <span>{{ isExpanded(payout.id) ? 'Скрыть' : 'Подробнее' }}</span>
                                                <svg class="size-4 transition-transform" :class="{'rotate-180': isExpanded(payout.id)}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="isExpanded(payout.id)" class="bg-base-100 border-base-200 border-b last:border-none">
                                        <td colspan="7">
                                            <div class="bg-base-200/40 border border-base-300 rounded-box p-4 space-y-4">
                                                <div class="grid grid-cols-1 lg:grid-cols-4 gap-2">
                                                    <div class="card bg-base-100 shadow-sm">
                                                        <div class="card-body text-sm space-y-3">
                                                            <div>
                                                                <div class="text-xs uppercase text-base-content/50">Сумма</div>
                                                                <div class="space-y-1">
                                                                    <div class="flex items-center justify-between">
                                                                        <span>Клиент</span>
                                                                        <span class="font-semibold">{{ formatMoney(payout.amount) }}</span>
                                                                    </div>
                                                                    <div class="flex items-center justify-between">
                                                                        <span>Тело</span>
                                                                        <span class="font-semibold">{{ formatMoney(payout.usdt_body) }}</span>
                                                                    </div>
                                                                    <div class="flex items-center justify-between">
                                                                        <span>Комиссия</span>
                                                                        <span class="font-semibold">{{ payout.fees?.total ?? '—' }} {{ payout.fees?.currency ?? '' }}</span>
                                                                    </div>
                                                                    <div class="flex items-center justify-between">
                                                                        <span>Списано</span>
                                                                        <span class="font-semibold">{{ formatMoney(payout.merchant_debit) }}</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="divider my-0"></div>
                                                            <div>
                                                                <div class="text-xs uppercase text-base-content/50">Ставка</div>
                                                                <div class="text-sm">
                                                                    Итого: <span class="font-semibold">{{ payout.commissions?.total ?? '—' }}%</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card bg-base-100 shadow-sm">
                                                        <div class="card-body text-sm">
                                                            <div class="text-xs uppercase text-base-content/50">Банковские данные</div>
                                                            <div>
                                                                <div class="text-xs text-base-content/60">Метод</div>
                                                                <div class="font-semibold">{{ payout.payout_method_type.label }}</div>
                                                            </div>
                                                            <div>
                                                                <div class="text-xs text-base-content/60">Платёжный метод</div>
                                                                <div class="font-semibold">{{ payout.payment_gateway?.name ?? '—' }} ({{ payout.payment_gateway?.code ?? '—' }})</div>
                                                            </div>
                                                            <div>
                                                                <div class="text-xs text-base-content/60">Реквизит</div>
                                                                <div class="font-semibold break-all">{{ payout.requisites }}</div>
                                                            </div>
                                                            <div>
                                                                <div class="text-xs text-base-content/60">Получатель</div>
                                                                <div class="font-semibold">{{ payout.initials ?? '—' }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card bg-base-100 shadow-sm">
                                                        <div class="card-body text-sm">
                                                            <div class="text-xs uppercase text-base-content/50">Курс</div>
                                                            <div>
                                                                <div class="text-xs text-base-content/60">Маркет</div>
                                                                <div class="font-semibold">{{ payout.rate.market ?? '—' }}</div>
                                                            </div>
                                                            <div>
                                                                <div class="text-xs text-base-content/60">Цена</div>
                                                                <div class="font-semibold">{{ payout.rate.price ?? '—' }} {{ payout.rate.currency }}</div>
                                                            </div>
                                                            <div>
                                                                <div class="text-xs text-base-content/60">Зафиксирован</div>
                                                                <DateTime :data="payout.rate.fixed_at" simple class="justify-start font-semibold" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card bg-base-100 shadow-sm">
                                                        <div class="card-body text-sm space-y-3">
                                                            <div>
                                                                <div class="text-xs uppercase text-base-content/50">Создано</div>
                                                                <DateTime :data="payout.timings.created_at" simple class="justify-start font-semibold" />
                                                            </div>
                                                            <div v-if="payout.timings.completed_at">
                                                                <div class="text-xs uppercase text-base-content/50">Завершено</div>
                                                                <DateTime :data="payout.timings.completed_at" simple class="justify-start font-semibold" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="xl:hidden space-y-3">
                        <div
                            v-for="payout in payoutItems"
                            :key="`mobile-${payout.id}`"
                            class="card bg-base-100 shadow-sm border border-base-200"
                        >
                            <div class="card-body space-y-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="text-xs uppercase text-base-content/60">UUID</div>
                                        <DisplayUUID :uuid="payout.uuid" />
                                    </div>
                                    <div class="badge badge-sm" :class="statusBadge(payout.status)">
                                        {{ payout.status_label }}
                                    </div>
                                </div>
                                <div class="space-y-2 text-sm">
                                    <div class="text-xs uppercase text-base-content/50">Реквизит</div>
                                    <div class="font-semibold break-all">{{ payout.requisites }}</div>
                                    <div class="text-xs text-base-content/60">{{ payout.payment_gateway?.name }} · {{ payout.payout_method_type.label }}</div>
                                </div>
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <div class="text-xs uppercase text-base-content/50">Мерчант</div>
                                        <div class="font-semibold">{{ payout.merchant?.name ?? '—' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs uppercase text-base-content/50">Сумма клиента</div>
                                        <div class="font-semibold">{{ formatMoney(payout.amount) }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs uppercase text-base-content/50">Списано</div>
                                        <div class="font-semibold">{{ formatMoney(payout.merchant_debit) }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs uppercase text-base-content/50">Комиссия</div>
                                        <div class="font-semibold">{{ payout.fees?.total ?? '—' }} {{ payout.fees?.currency ?? '' }}</div>
                                    </div>
                                </div>
                                <button
                                    class="btn btn-outline btn-sm w-full"
                                    type="button"
                                    @click="toggleRow(payout.id)"
                                >
                                    <span>{{ isExpanded(payout.id) ? 'Скрыть детали' : 'Показать детали' }}</span>
                                    <svg class="size-4 transition-transform" :class="{'rotate-180': isExpanded(payout.id)}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                                <transition name="fade">
                                    <div v-if="isExpanded(payout.id)" class="space-y-3">
                                        <div class="divider my-0"></div>
                                        <div class="space-y-3 text-sm">
                                            <div class="text-xs uppercase text-base-content/50">Фиат / USDT</div>
                                            <div>Клиент: <span class="font-semibold">{{ formatMoney(payout.amount) }}</span></div>
                                            <div>Тело (USDT): <span class="font-semibold">{{ formatMoney(payout.usdt_body) }}</span></div>
                                            <div>Комиссия (USDT): <span class="font-semibold">{{ payout.fees?.total ?? '—' }} {{ payout.fees?.currency ?? '' }}</span></div>
                                            <div>Списано (USDT): <span class="font-semibold">{{ formatMoney(payout.merchant_debit) }}</span></div>
                                            <div class="pt-2 border-t border-base-200">Ставка: <span class="font-semibold">{{ payout.commissions?.total ?? '—' }}%</span></div>
                                        </div>
                                        <div class="space-y-2">
                                            <h4 class="text-sm font-semibold">Операции</h4>
                                            <div v-if="(payout.operations ?? []).length" class="space-y-2">
                                                <div
                                                    v-for="operation in payout.operations"
                                                    :key="`mobile-op-${operation.id}`"
                                                    class="border border-base-200 rounded-box p-2 text-xs space-y-1"
                                                >
                                                    <div class="font-semibold">{{ operation.type_label }}</div>
                                                    <div>Сумма: {{ formatMoney(operation.amount) }}</div>
                                                    <div>Когда: <DateTime :data="operation.created_at" simple class="justify-start font-semibold" /></div>
                                                    <div>
                                                        <div class="text-[10px] uppercase text-base-content/50">Meta</div>
                                                        <pre class="bg-base-100 p-2 rounded-box whitespace-pre-wrap break-all max-h-48 overflow-auto">{{ formatMeta(operation.meta) }}</pre>
                                                    </div>
                                                </div>
                                            </div>
                                            <div v-else class="text-xs text-base-content/60">Операции не найдены.</div>
                                        </div>
                                        <div class="space-y-2">
                                            <h4 class="text-sm font-semibold">Математика</h4>
                                            <pre class="bg-base-100 p-2 rounded-box whitespace-pre-wrap break-all max-h-60 overflow-auto text-xs">{{ formatMeta(payout.calc_meta) }}</pre>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </MainTableSection>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.25s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>

