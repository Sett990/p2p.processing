<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import SectionTitle from '@/Components/SectionTitle.vue'

// Alerts
import AlertError from '@/Components/Alerts/AlertError.vue'
import AlertInfo from '@/Components/Alerts/AlertInfo.vue'
import AlertWarning from '@/Components/Alerts/AlertWarning.vue'
import {Head} from "@inertiajs/vue3";
import FiltersPanel from '@/Components/Filters/FiltersPanel.vue'
import InputFilter from '@/Components/Filters/Pertials/InputFilter.vue'
import DropdownFilter from '@/Components/Filters/Pertials/DropdownFilter.vue'
import DateFilter from '@/Components/Filters/Pertials/DateFilter.vue'
import FilterCheckbox from '@/Components/Filters/Pertials/FilterCheckbox.vue'
import {onMounted} from 'vue'
import {useTableFiltersStore} from '@/store/tableFilters.js'

defineOptions({ layout: AuthenticatedLayout })

const tableFiltersStore = useTableFiltersStore();

// Открываем панель фильтров по умолчанию для демо
if (typeof window !== 'undefined') {
    localStorage.setItem('display-filters-components-gallery-demo', 'display');
}

onMounted(() => {
    tableFiltersStore.setFiltersVariants({
        status: [
            { name: 'Новые', value: 'new' },
            { name: 'В работе', value: 'in_progress' },
            { name: 'Завершённые', value: 'done' },
        ],
        category: [
            { name: 'Карты', value: 'card' },
            { name: 'Банки', value: 'bank' },
            { name: 'Крипто', value: 'crypto' },
        ],
    });

    tableFiltersStore.setFilters({
        id: '',
        name: '',
        status: '',
        category: '',
        date: '',
        onlyActive: false,
    });
});
</script>

<template>
    <div>
        <Head title="Галерея компонентов" />

        <SectionTitle>Галерея компонентов</SectionTitle>

        <div class="w-full">
            <div>
                <div class="grid grid-cols-1 gap-2">
                    <div class="card bg-base-100 card-border shadow">
                        <div class="card-body">
                            <div class="font-semibold text-base mb-2 break-all">Alerts</div>
                            <div class="space-y-2">
                                <AlertError message="Произошла ошибка при обработке запроса. Повторите попытку позже." />
                                <AlertInfo message="Это информационное сообщение для пользователя." />
                                <AlertWarning message="Внимание: проверьте корректность введённых данных." />
                            </div>
                        </div>
                    </div>
                    <div class="card bg-base-100 card-border shadow">
                        <div class="card-body">
                            <div class="font-semibold text-base mb-2 break-all">Фильтры (демо)</div>
                            <div class="space-y-3">
                                <FiltersPanel name="components-gallery-demo">
                                    <InputFilter name="id" placeholder="ID" />
                                    <InputFilter name="name" placeholder="Название" />
                                    <DropdownFilter name="status" title="Статус" />
                                    <DropdownFilter name="category" title="Категория" />
                                    <DateFilter name="date" title="Дата" />
                                    <FilterCheckbox name="onlyActive" title="Только активные" />
                                </FiltersPanel>
                                <div class="text-sm opacity-70">
                                    Это демонстрация внешнего вида фильтров. Кнопки применят фильтры, перезагрузив текущую страницу.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>


