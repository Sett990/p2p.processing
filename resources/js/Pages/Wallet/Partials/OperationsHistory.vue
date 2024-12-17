<script setup>
import EmptyTable from "@/Components/EmptyTable.vue";
import {router, usePage} from "@inertiajs/vue3";
import {onMounted, ref} from "vue";
import {useViewStore} from "@/store/view.js";
import Pagination from "@/Components/Pagination/Pagination.vue";
import Select from "@/Components/Select.vue";

const viewStore = useViewStore();

const user = usePage().props.user;
const invoices = usePage().props.invoices;
const transactions = usePage().props.transactions;
const currentTab = ref(usePage().props.tab);
const currentFilters = ref(usePage().props.currentFilters);
console.log(currentFilters.value);
const currentInvoiceType = ref(usePage().props.currentTab);
const tabs = ref(usePage().props.tabs);
const filters = ref(usePage().props.filters);
const balanceTypes = ref(usePage().props.balanceTypes);
const transactionDirections = ref(usePage().props.transactionDirections);

const openPage = (page) => {
    if (viewStore.isAdminViewMode) {
        router.visit(route('admin.users.wallet.index', user.id), {
            data: {
                page,
                tab: currentTab.value,
                currentFilters: currentFilters.value,
            },
        })
    } else {
        router.visit(route('wallet.index'), {
            data: {
                page,
                tab: currentTab.value
            },
        })
    }
}

const currentPage = ref(1);

onMounted(() => {
    let urlParams = new URLSearchParams(window.location.search);
    currentTab.value = urlParams.get('tab') ?? 'invoices'

    currentPage.value = urlParams.get('page') ?? 1;
})
</script>

<template>
    <h2 class="text-xl font-medium text-gray-900 dark:text-white sm:text-2xl mb-3">История операций</h2>

    <ul class="flex flex-wrap text-sm font-medium text-center text-gray-500 dark:text-gray-400">
        <li
            v-for="tab in tabs"
            class="me-2"
        >
            <a
                @click.prevent="currentTab = tab.key; openPage(1)"
                href="#"
                :class="currentTab === tab.key ? 'inline-block px-4 py-3 text-white bg-blue-600 rounded-xl  active' : 'inline-block px-4 py-3 rounded-xl  hover:text-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-white'"
                aria-current="page"
            >
                {{ tab.name }}
            </a>
        </li>
    </ul>

    <div
        v-if="currentTab === 'invoices'"
        class="mt-3 grid grid-cols-4 gap-3"
    >
        <div
            v-for="(invoiceFilters, filterKey) in filters.invoices"
        >
            <select
                class="block w-full p-2 text-sm text-gray-900 border border-gray-300 rounded-xl bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                required
                v-model="currentFilters.invoices[filterKey]"
                @change="openPage(1)"
            >
                <option
                    v-for="filter in invoiceFilters"
                    :value="filter.key"
                >{{ filter.name }}</option>
            </select>
        </div>
    </div>

    <div v-if="currentTab === 'invoices'">
        <div class="mx-auto space-y-2">
            <EmptyTable v-if="!invoices.data.length"/>
            <template v-else>
                <div class="relative overflow-x-auto">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400 border-separate border-spacing-y-4 rounded-xl">
                    <tbody>
                    <tr
                        v-for="invoice in invoices.data"
                        class="bg-white dark:bg-gray-800 rounded-table-raw shadow-md"
                    >
                        <th
                            scope="row"
                            class="p-3 font-medium text-gray-900 whitespace-nowrap dark:text-gray-200 rounded-l-table-raw"
                        >
                            <div class="flex items-center">
                                <div class="mr-3">
                                    <span v-if="invoice.status === 'success'" class="inline-flex px-2.5 py-2.5 rounded-2xl bg-green-500 text-green-100 dark:bg-green-800/50 dark:text-green-300">
                                        <svg class="w-5 h-5 dark:text-green-200/80" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M3 21h18M4 18h16M6 10v8m4-8v8m4-8v8m4-8v8M4 9.5v-.955a1 1 0 0 1 .458-.84l7-4.52a1 1 0 0 1 1.084 0l7 4.52a1 1 0 0 1 .458.84V9.5a.5.5 0 0 1-.5.5h-15a.5.5 0 0 1-.5-.5Z"/>
                                        </svg>
                                    </span>
                                    <span v-if="invoice.status === 'pending'" class="inline-flex px-2.5 py-2.5 rounded-2xl bg-yellow-500 text-white dark:bg-yellow-700/50 dark:text-yellow-300/80">
                                        <svg class="w-5 h-5 dark:text-red-200/80" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M3 21h18M4 18h16M6 10v8m4-8v8m4-8v8m4-8v8M4 9.5v-.955a1 1 0 0 1 .458-.84l7-4.52a1 1 0 0 1 1.084 0l7 4.52a1 1 0 0 1 .458.84V9.5a.5.5 0 0 1-.5.5h-15a.5.5 0 0 1-.5-.5Z"/>
                                        </svg>
                                    </span>
                                    <span v-if="invoice.status === 'fail'" class="inline-flex px-2.5 py-2.5 rounded-2xl bg-red-500 text-red-100 dark:bg-red-800/50 dark:text-red-300">
                                        <svg class="w-5 h-5 dark:text-red-200/80" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M3 21h18M4 18h16M6 10v8m4-8v8m4-8v8m4-8v8M4 9.5v-.955a1 1 0 0 1 .458-.84l7-4.52a1 1 0 0 1 1.084 0l7 4.52a1 1 0 0 1 .458.84V9.5a.5.5 0 0 1-.5.5h-15a.5.5 0 0 1-.5-.5Z"/>
                                        </svg>
                                    </span>
                                </div>
                                <div class="text-gray-900 dark:text-gray-400">#{{ invoice.id }}</div>
                            </div>
                        </th>
                        <td class="p-3 text-gray-900">
                            <div class="text-nowrap dark:text-gray-400 text-center">
                                <template v-if="invoice.type === 'deposit'">Пополнение</template>
                                <template v-if="invoice.type === 'withdrawal'">Вывод</template>
                            </div>
                        </td>
                        <td v-show="viewStore.isAdminViewMode" class="p-3 text-gray-900">
                            <div class="text-nowrap dark:text-gray-400 text-center">
                                <template v-if="invoice.balance_type === 'trust'">Траст</template>
                                <template v-if="invoice.balance_type === 'merchant'">Мерчант</template>
                            </div>
                        </td>
                        <td class="p-3 text-gray-900">
                            <div class="text-nowrap dark:text-gray-400 text-center">
                                <template v-if="invoice.type === 'deposit'">+</template>
                                <template v-if="invoice.type === 'withdrawal'">-</template>
                                {{ invoice.amount }} {{ invoice.currency.toUpperCase() }}
                            </div>
                        </td>
                        <td class="p-3">
                            <div class="flex justify-center gap-2 text-gray-900 dark:text-gray-400 text-nowrap">
                                <svg class="h-4 w-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 10h16m-8-3V4M7 7V4m10 3V4M5 20h14a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Zm3-7h.01v.01H8V13Zm4 0h.01v.01H12V13Zm4 0h.01v.01H16V13Zm-8 4h.01v.01H8V17Zm4 0h.01v.01H12V17Zm4 0h.01v.01H16V17Z" />
                                </svg>
                                <p class="font-medium inline-block align-middle">{{ invoice.created_at }}</p>
                            </div>
                        </td>
                        <td class="p-3 rounded-r-table-raw">
                            <div class="flex justify-end">
                                <span v-if="invoice.status === 'success'" class="inline-flex mr-2 px-4 py-2.5 rounded-xl  bg-green-500 text-green-100 dark:bg-green-800/50 dark:text-green-200/80">
                                    Успешно
                                </span>
                                <span v-if="invoice.status === 'pending'" class="inline-flex mr-2 px-4 py-2.5 rounded-xl  bg-yellow-500 text-white dark:bg-yellow-700/50 dark:text-yellow-300/80">
                                    Ожидание
                                </span>
                                <span v-if="invoice.status === 'fail'" class="inline-flex mr-2 px-4 py-2.5 rounded-xl  bg-red-500 text-red-100 dark:bg-red-800/50 dark:text-red-200/80">
                                    Ошибка
                                </span>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
                </div>
                <Pagination
                    v-model="currentPage"
                    :total-items="invoices.meta.total"
                    previous-label="Назад" next-label="Вперед"
                    @page-changed="openPage"
                    :per-page="invoices.meta.per_page"
                ></Pagination>
            </template>
        </div>
    </div>

<!--    <div v-if="currentTab === 'transactions'">
        <div class="mx-auto space-y-2">
            <EmptyTable v-if="!transactions.data.length"/>
            <template v-else>
                <div class="relative overflow-x-auto">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400 border-separate border-spacing-y-4 rounded-xl">
                    <tbody>
                    <tr
                        v-for="transaction in transactions.data"
                        class="bg-white dark:bg-gray-800 rounded-table-raw shadow-md"
                    >
                        <th
                            scope="row"
                            class="p-3 font-medium text-gray-900 whitespace-nowrap dark:text-gray-200 rounded-l-table-raw"
                        >
                            <div class="flex items-center">
                                <div class="mr-3">
                                    <span v-if="transaction.direction === 'in'" class="inline-flex px-2.5 py-2.5 rounded-2xl bg-green-500 text-green-100 dark:bg-green-800/50 dark:text-green-300">
                                        <svg class="w-5 h-5 dark:text-green-200/80" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M3 21h18M4 18h16M6 10v8m4-8v8m4-8v8m4-8v8M4 9.5v-.955a1 1 0 0 1 .458-.84l7-4.52a1 1 0 0 1 1.084 0l7 4.52a1 1 0 0 1 .458.84V9.5a.5.5 0 0 1-.5.5h-15a.5.5 0 0 1-.5-.5Z"/>
                                        </svg>
                                    </span>
                                    <span v-if="transaction.direction === 'out'" class="inline-flex px-2.5 py-2.5 rounded-2xl bg-red-500 text-red-100 dark:bg-red-800/50 dark:text-red-300">
                                        <svg class="w-5 h-5 dark:text-red-200/80" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M3 21h18M4 18h16M6 10v8m4-8v8m4-8v8m4-8v8M4 9.5v-.955a1 1 0 0 1 .458-.84l7-4.52a1 1 0 0 1 1.084 0l7 4.52a1 1 0 0 1 .458.84V9.5a.5.5 0 0 1-.5.5h-15a.5.5 0 0 1-.5-.5Z"/>
                                        </svg>
                                    </span>
                                </div>
                                <div class="text-gray-900 dark:text-gray-400">#{{ transaction.id }}</div>
                            </div>
                        </th>
                        <td class="p-3">
                            <div class="text-nowrap text-gray-900 dark:text-gray-400 text-center">
                                <template v-if="transaction.direction === 'in'">+</template>
                                <template v-if="transaction.direction === 'out'">-</template>
                                {{ transaction.amount }} {{ transaction.currency.toUpperCase() }}
                            </div>
                        </td>
                        <td class="p-3">
                            <div class="flex justify-center gap-2 text-gray-900 dark:text-gray-400">
                                <p class="font-medium">{{ transaction.type_name }}</p>
                            </div>
                        </td>
                        <td class="p-3 text-nowrap">
                            <div class="flex justify-center gap-2 text-gray-900 dark:text-gray-400">
                                <svg class="h-4 w-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 10h16m-8-3V4M7 7V4m10 3V4M5 20h14a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Zm3-7h.01v.01H8V13Zm4 0h.01v.01H12V13Zm4 0h.01v.01H16V13Zm-8 4h.01v.01H8V17Zm4 0h.01v.01H12V17Zm4 0h.01v.01H16V17Z" />
                                </svg>
                                <p class="font-medium inline-block align-middle">{{ transaction.created_at }}</p>
                            </div>
                        </td>
                        <td class="p-3 rounded-r-table-raw">
                            <div class="flex justify-end">
                                <span v-if="transaction.direction === 'in'" class="inline-flex mr-2 px-4 py-2.5 rounded-xl  bg-green-500 text-green-100 dark:bg-green-800/50 dark:text-green-200/80">
                                    Зачисление
                                </span>
                                <span v-if="transaction.direction === 'out'" class="inline-flex mr-2 px-4 py-2.5 rounded-xl  bg-red-500 text-red-100 dark:bg-red-800/50 dark:text-red-200/80">
                                    Снятие
                                </span>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
                </div>
                <Pagination
                    v-model="currentPage"
                    :total-items="transactions.meta.total"
                    previous-label="Назад" next-label="Вперед"
                    @page-changed="openPage"
                    :per-page="transactions.meta.per_page"
                ></Pagination>
            </template>
        </div>
    </div>-->
</template>

<style scoped>

</style>
