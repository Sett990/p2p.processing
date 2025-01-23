<script setup>
import {router} from "@inertiajs/vue3";
import {computed, ref} from "vue";
import Pagination from "@/Components/Pagination/Pagination.vue";

const props = defineProps({
    title: {
        type: String,
    },
    data: {
        type: Object,
        default: {}
    },
    queryData: {
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

const openPage = (page) => {
    router.visit(route(route().current()), { data: {
            page,
            ...props.queryData
        } })
}

const items = computed(() => {
    if (props.paginate) {
        return props.data.data;
    } else {
        return props.data;
    }
});

const currentPage = ref(props.data?.meta?.current_page)
</script>

<template>
    <div>
        <div>
            <div class="mx-auto space-y-6">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl text-gray-900 dark:text-white sm:text-4xl">{{ title }}</h2>
                    <slot name="button"></slot>
                </div>
                <div>
                    <slot name="header"/>
                </div>
                <div v-show="$page.props.flash.message" class="flex items-center p-4 mb-4 text-sm text-blue-800 border border-blue-300 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400 dark:border-blue-800" role="alert">
                    <svg class="flex-shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                    </svg>
                    <span class="sr-only">Внимание</span>
                    <div>
                        <span class="font-medium">Внимание!</span> {{ $page.props.flash.message }}
                    </div>
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
                <div v-if="paginate && displayPagination">
                    <Pagination
                        v-model="currentPage"
                        :total-items="data.meta.total"
                        previous-label="Назад" next-label="Вперед"
                        @page-changed="openPage"
                        :per-page="data.meta.per_page"
                    ></Pagination>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
