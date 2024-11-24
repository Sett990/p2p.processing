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
    paginate: {
        type: Boolean,
        default: true
    }
});

const openPage = (page) => {
    router.visit(route(route().current()), { data: {
            page
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
                <div class="flex justify-between">
                    <h2 class="text-xl font-medium text-gray-900 dark:text-white sm:text-2xl">{{ title }}</h2>
                    <slot name="button"></slot>
                </div>
                <div>
                    <slot v-if="items.length" name="body"/>
                    <h2 v-else class="text-center text-lg font-medium text-gray-900 dark:text-white sm:text-xl mb-4">
                        Пока что тут пусто
                    </h2>
                </div>
                <div v-if="paginate">
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
