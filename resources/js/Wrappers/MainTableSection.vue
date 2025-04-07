<script setup>
import {router, usePage} from "@inertiajs/vue3";
import {computed, ref, getCurrentInstance} from "vue";
import Pagination from "@/Components/Pagination/Pagination.vue";
import AlertError from "@/Components/Alerts/AlertError.vue";
import AlertInfo from "@/Components/Alerts/AlertInfo.vue";
import {useTableFiltersStore} from "@/store/tableFilters.js";

const tableFiltersStore = useTableFiltersStore();

const props = defineProps({
    title: {
        type: String,
    },
    data: {
        type: Object,
        default: {}
    },
    paginate: {
        type: Boolean,
        default: true
    },
    displayPagination: {
        type: Boolean,
        default: true
    }
});

tableFiltersStore.setMeta(props.data?.meta);
tableFiltersStore.setFilters(usePage().props.filters);
tableFiltersStore.setFiltersVariants(usePage().props.filtersVariants);

const items = computed(() => {
    if (props.paginate) {
        return props.data.data;
    } else {
        return props.data;
    }
});

const perPageOptions = [
    { value: 5, name: '5 строк' },
    { value: 10, name: '10 строк' },
    { value: 15, name: '15 строк' },
    { value: 20, name: '20 строк' },
    { value: 25, name: '25 строк' },
    { value: 50, name: '50 строк' },
    { value: 100, name: '100 строк' }
];

const changeCurrentPage = (value) => {
    tableFiltersStore.setCurrentPage(value ?? 1);

    openPage();
}

const changePerPage = (value) => {
    tableFiltersStore.setCurrentPage(1);
    tableFiltersStore.setPerPage(value ?? 10);

    openPage();
}

const openPage = () => {
    router.visit(route(route().current()), {
        data: tableFiltersStore.getQueryData,
        preserveScroll: true
    })
}


const {uid} = getCurrentInstance();

const hasPendingDisputes = ref(usePage().props.data.hasPendingDisputes);

router.on('success', (event) => {
    hasPendingDisputes.value = usePage().props.data.hasPendingDisputes;
})
</script>

<template>
    <div>
        <div>
            <div class="mx-auto space-y-6">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl text-gray-900 dark:text-white sm:text-4xl">{{ title }}</h2>
                    <slot name="button"></slot>
                </div>

                <AlertError v-if="hasPendingDisputes" message="У вас есть не закрытый спор!"></AlertError>

                <AlertError :message="$page.props.flash.error"></AlertError>
                <AlertInfo :message="$page.props.flash.message"></AlertInfo>

                <div>
                    <slot name="header"/>
                </div>
                <div>
                    <slot name="table-filters"/>
                </div>
                <div>

                    <slot v-if="items.length" name="body"/>
                    <h2 v-else class="text-center text-lg font-medium text-gray-900 dark:text-white sm:text-xl mb-4">
                        Пока что тут пусто
                    </h2>
                </div>
                <div v-if="paginate && displayPagination" class="flex justify-between items-center">
                    <Pagination
                        v-model="tableFiltersStore.page"
                        :total-items="tableFiltersStore.getTotal"
                        previous-label="Назад" next-label="Вперед"
                        @page-changed="changeCurrentPage"
                        :per-page="tableFiltersStore.getPerPage"
                    ></Pagination>

                    <div>
                        <button
                            :id="'perPageDropdownButton'+uid"
                            :data-dropdown-toggle="'perPageDropdown'+uid"
                            class="flex items-center justify-center px-4 h-8 ml-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-xl hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
                            type="button"
                        >
                            {{ tableFiltersStore.getPerPage }} строк
                            <svg class="w-2.5 h-2.5 ms-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                            </svg>
                        </button>

                        <div :id="'perPageDropdown'+uid" class="z-10 hidden bg-white rounded-xl shadow dark:bg-gray-700">
                            <ul class="py-1 text-sm text-gray-700 dark:text-gray-200" :aria-labelledby="'perPageDropdownButton'+uid">
                                <li v-for="(option, index) in perPageOptions" :key="option.value">
                                    <div class="flex items-center px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-600">
                                        <input
                                            :id="'perPage-'+uid+'-'+index"
                                            type="radio"
                                            :name="'perPageRadio'+uid"
                                            :value="option.value"
                                            :checked="tableFiltersStore.getPerPage === option.value"
                                            @change="changePerPage(option.value)"
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500"
                                        >
                                        <label :for="'perPage-'+uid+'-'+index" class="w-full ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                                            {{ option.name }}
                                        </label>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
