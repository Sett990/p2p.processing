<script setup>
import {computed} from "vue";
import {useTableFiltersStore} from "@/store/tableFilters.js";

const tableFiltersStore = useTableFiltersStore();

const props = defineProps({
    name: {
        type: String,
    },
    title: {
        type: String,
    },
});

const model = computed({
    get: () => tableFiltersStore.filters[props.name],
    set: (val) => {
        tableFiltersStore.filters[props.name] = val
    }
})
</script>

<template>
    <div class="flex items-center">
        <input
            v-model="model"
            :id="`checkbox-${$.uid}`"
            type="checkbox"
            value=""
            class="checkbox checkbox-sm"
        >
        <label
            :for="`checkbox-${$.uid}`"
            class="ms-2 text-sm"
        >{{ title }}</label>
    </div>
</template>

<style scoped>

</style>
