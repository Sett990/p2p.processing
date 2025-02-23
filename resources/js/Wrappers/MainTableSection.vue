<script setup>
import {router, usePage} from "@inertiajs/vue3";
import {computed, ref} from "vue";
import Pagination from "@/Components/Pagination/Pagination.vue";
import AlertError from "@/Components/Alerts/AlertError.vue";
import AlertInfo from "@/Components/Alerts/AlertInfo.vue";

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
