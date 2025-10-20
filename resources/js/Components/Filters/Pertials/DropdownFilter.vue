<script setup>
import {computed, watch} from "vue";
import {useTableFiltersStore} from "@/store/tableFilters.js";

const tableFiltersStore = useTableFiltersStore();

const props = defineProps({
    name: {
        type: String,
    },
    title: {
        type: String,
        default: 'Фильтр'
    }
});

const model = computed({
    get: () => tableFiltersStore.filters[props.name] ?? [],
    set: (val) => {
        tableFiltersStore.filters[props.name] = val
    }
})

const selectedOptions = computed(() => {
    let options = tableFiltersStore.getFiltersVariants[props.name] ?? [];

    return options.map(i => {
        i.selected = model.value.includes(i.value);

        return i;
    })
})

watch(
    () => selectedOptions.value,
    () => {
        model.value = selectedOptions.value.filter(o => o.selected).map(o => o.value).join(',');
    },
    { deep: true }
);

const selectedCount = computed(() => {
    return selectedOptions.value.filter(o => o.selected).length
})
</script>

<template>
    <div class="w-48 dropdown dropdown-end">
        <button :id="`filterDropdownButton-${$.uid}`" :data-dropdown-toggle="`filterDropdown-${$.uid}`" class="btn btn-sm btn-outline w-full justify-between" type="button">
            <span v-if="selectedCount" class="badge badge-primary badge-xs mr-2">
                {{ selectedCount }}
            </span>
            <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="w-4 h-4 mr-2 opacity-60" viewbox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
            </svg>
            <span class="text-nowrap">{{ title }}</span>
            <svg class="-mr-1 ml-1.5 w-5 h-5 opacity-60" fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path clip-rule="evenodd" fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
            </svg>
        </button>
        <div :id="`filterDropdown-${$.uid}`" class="dropdown-content z-10 w-48 p-3 bg-base-100 rounded-box shadow">
            <h6 class="mb-3 text-sm font-medium">
                {{ title }}
            </h6>
            <ul class="space-y-2 text-sm" aria-labelledby="dropdownDefault">
                <li
                    v-for="option in selectedOptions"
                    class="flex items-center"
                >
                    <input
                        :id="`option-${option.value}`"
                        type="checkbox"
                        :value="option.value"
                        v-model="option.selected"
                        class="checkbox checkbox-sm"
                    />
                    <label :for="`option-${option.value}`" class="ml-2 text-sm font-medium">
                        {{ option.name }}
                    </label>
                </li>
            </ul>
        </div>
    </div>
</template>

<style scoped>

</style>
