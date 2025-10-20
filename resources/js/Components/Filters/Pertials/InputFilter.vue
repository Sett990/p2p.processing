<script setup>
import {useTableFiltersStore} from "@/store/tableFilters.js";
import {computed} from "vue";

const tableFiltersStore = useTableFiltersStore();

const props = defineProps({
    name: {
        type: String,
    },
    placeholder: {
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
    <div class="w-48">
        <input
            type="text"
            :id="$.uid"
            v-model="model"
            :placeholder="placeholder"
            class="input input-bordered input-sm w-full"
        >
    </div>
</template>

<style scoped>

</style>
