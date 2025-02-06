<script setup>
import {ref} from "vue";
import {router} from "@inertiajs/vue3";

const props = defineProps({
    name: {
        type: String,
    },
    filters: {
        type: Object
    }
});
const filtersStorageKey = `display-filters-${props.name}`;
const displayFilters = ref(localStorage.getItem(filtersStorageKey) === 'display');

const toggleFiltersDisplay = () => {
    displayFilters.value = localStorage.getItem(filtersStorageKey) === 'display';
    displayFilters.value = !displayFilters.value;
    localStorage.setItem(filtersStorageKey, displayFilters.value ? 'display' : 'hide');
}

const applyFilters = () => {
    router.visit(route(route().current()), {
        data: {
            filters: props.filters,
            page: 1
        },
        preserveScroll: true
    })
}

const clearFilters = () => {
    router.visit(route(route().current()), {
        data: {
            filters: {},
            page: 1
        },
        preserveScroll: true
    })
}
</script>

<template>
    <section>
        <a
            @click.prevent="toggleFiltersDisplay"
            href="#"
            class="font-medium text-blue-600 dark:text-blue-500 hover:underline flex justify-end mb-1 mr-1"
        >
            {{ displayFilters ? 'Скрыть фильтры' : 'Показать фильтры' }}
        </a>
        <div v-show="displayFilters" class="flex items-center mb-5">
            <div class="mx-auto w-full">
                <div class="relative bg-white shadow-md dark:bg-gray-800 rounded-plate">
                    <div class="flex flex-col items-center justify-between p-2 space-y-3 lg:flex-row lg:space-y-0 lg:space-x-4">
                        <div class="flex items-center gap-4 space-y-0 space-y-3 flex-wrap">
                            <slot/>
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                @click.prevent="applyFilters"
                                type="button"
                                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-xl text-sm p-2 text-center inline-flex items-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                            >
                                <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M18.796 4H5.204a1 1 0 0 0-.753 1.659l5.302 6.058a1 1 0 0 1 .247.659v4.874a.5.5 0 0 0 .2.4l3 2.25a.5.5 0 0 0 .8-.4v-7.124a1 1 0 0 1 .247-.659l5.302-6.059c.566-.646.106-1.658-.753-1.658Z"/>
                                </svg>
                                <span class="sr-only">Фильтровать</span>
                            </button>
                            <button
                                @click.prevent="clearFilters"
                                type="button"
                                class="text-center inline-flex items-center focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-xl text-sm p-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900"
                            >
                                <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z"/>
                                </svg>
                                <span class="sr-only">Очистить</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>

</style>
