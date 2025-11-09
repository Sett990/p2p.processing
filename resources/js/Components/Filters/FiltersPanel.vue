<script setup>
import {ref, provide} from "vue";
import {router} from "@inertiajs/vue3";
import {useTableFiltersStore} from "@/store/tableFilters.js";

const tableFiltersStore = useTableFiltersStore();

const props = defineProps({
    name: {
        type: String,
    },
    query: {
        type: Object,
        default: {}
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
    tableFiltersStore.setCurrentPage(1);

    router.visit(route(route().current()), {
        data: {
            ...tableFiltersStore.getQueryData,
            ...props.query
        },
        preserveScroll: true
    })
}

const clearFilters = () => {
    tableFiltersStore.setCurrentPage(1);
    tableFiltersStore.setFilters({});

    router.visit(route(route().current()), {
        data: {
            ...tableFiltersStore.getQueryData,
            ...props.query
        },
        preserveScroll: true
    })
}

// Применение фильтров по Enter только из текстовых/числовых инпутов
const onKeydownEnter = (event) => {
    const target = event?.target;
    if (!target) {
        return;
    }
    const tagName = (target.tagName || '').toUpperCase();
    const type = (target.type || '').toLowerCase();
    const isTextLike =
        tagName === 'INPUT' && (type === 'text' || type === 'search' || type === 'number' || type === 'email');
    const isTextarea = tagName === 'TEXTAREA';

    if (isTextLike || isTextarea) {
        event.preventDefault();
        applyFilters();
    }
}

// Делаем доступным в дочерних инпутах, чтобы по Enter применять всегда
provide('applyFilters', applyFilters);
</script>

<template>
    <section>
        <div class="w-full flex justify-end mb-1 mr-1">
            <a
                @click.prevent="toggleFiltersDisplay"
                href="#"
                class="link link-primary"
            >
                {{ displayFilters ? 'Скрыть фильтры' : 'Показать фильтры' }}
            </a>
        </div>
        <div
            v-show="displayFilters"
            class="mb-5"
        >
            <div class="mx-auto w-full">
                <div class="card bg-base-100 shadow">
                    <div
                        class="p-3 lg:p-4"
                        @keydown.enter.stop="onKeydownEnter"
                    >
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5">
                            <slot/>
                            <div class="col-span-full flex flex-wrap items-center justify-end gap-2 pt-1">
                                <button
                                    @click.prevent="applyFilters"
                                    type="button"
                                    class="btn btn-primary btn-sm"
                                >
                                    <svg class="w-4 h-4 mr-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M18.796 4H5.204a1 1 0 0 0-.753 1.659l5.302 6.058a1 1 0 0 1 .247.659v4.874a.5.5 0 0 0 .2.4l3 2.25a.5.5 0 0 0 .8-.4v-7.124a1 1 0 0 1 .247-.659l5.302-6.059c.566-.646.106-1.658-.753-1.658Z"/>
                                    </svg>
                                    <span>Фильтровать</span>
                                </button>
                                <button
                                    @click.prevent="clearFilters"
                                    type="button"
                                    class="btn btn-error btn-sm btn-outline"
                                >
                                    <svg class="w-4 h-4 mr-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z"/>
                                    </svg>
                                    <span>Сбросить</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>

</style>
