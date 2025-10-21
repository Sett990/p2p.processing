<script setup>
import {computed, ref, watch} from "vue";
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

// Значение для input[type=date] в формате YYYY-MM-DD
const dateValue = ref("");

const normalizeToInputDate = (raw) => {
    if (!raw) return "";
    const m = raw.match(/^(\d{2})[./](\d{2})[./](\d{4})$/);
    if (m) {
        const [, dd, mm, yyyy] = m;
        return `${yyyy}-${mm}-${dd}`;
    }
    if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) return raw;
    return "";
};

const toDisplayFormat = (raw) => {
    if (!raw) return "";
    const m = raw.match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (m) {
        const [, yyyy, mm, dd] = m;
        return `${dd}.${mm}.${yyyy}`;
    }
    return raw;
};

dateValue.value = normalizeToInputDate(model.value);

watch(dateValue, (val) => {
    model.value = toDisplayFormat(val);
});
</script>

<template>
    <div class="md:flex items-center gap-4 w-48">
        <div class="relative w-full">
            <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                <svg class="w-4 h-4 opacity-60" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z"/>
                </svg>
            </div>
            <input
                type="date"
                class="input input-bordered input-sm w-48 ps-10"
                :placeholder="title"
                v-model="dateValue"
            >
        </div>
    </div>
</template>

<style scoped>

</style>
