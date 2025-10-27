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
import {onMounted, reactive, ref} from 'vue'
import {useTableFiltersStore} from '@/store/tableFilters.js'
import TextInputBlock from '@/Components/Form/TextInputBlock.vue'
import NumberInputBlock from '@/Components/Form/NumberInputBlock.vue'
import Multiselect from '@/Components/Form/Multiselect.vue'
import DropDownWithCheckbox from '@/Components/Form/DropDownWithCheckbox.vue'
import DropDownWithRadio from '@/Components/Form/DropDownWithRadio.vue'
import TimepickerInput from '@/Components/Form/TimepickerInput.vue'
import Dropzone from '@/Components/Form/Dropzone.vue'
import SaveButton from '@/Components/Form/SaveButton.vue'
import TextInput from '@/Components/TextInput.vue'

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

// Демо-данные для блока форм
const demoForm = reactive({
    data: {
        title: '',
        amount: null,
        baseInput: '',
    },
    errors: {},
    clearErrors(field) {
        if (this.errors && this.errors[field]) {
            delete this.errors[field];
        }
    },
});

const demoMulti = ref([]);
const demoMultiOptions = [
    { label: 'Опция A', value: 'A' },
    { label: 'Опция B', value: 'B' },
    { label: 'Опция C', value: 'C' },
];

const demoCheckboxItems = [
    { name: '09:00', value: '09:00' },
    { name: '12:00', value: '12:00' },
    { name: '18:00', value: '18:00' },
];
const selectedTimes = ref([]);

const demoRadioItems = [
    { name: 'RUB', value: 'RUB' },
    { name: 'USD', value: 'USD' },
    { name: 'EUR', value: 'EUR' },
];
const selectedCurrency = ref(null);

const demoTime = ref('');
const demoFile = ref(null);
const saveDone = ref(false);
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
                    <div class="card bg-base-100 card-border shadow">
                        <div class="card-body">
                            <div class="font-semibold text-base mb-2 break-all">Form (демо)</div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <InputBlock :form="demoForm" field="baseInput" label="Базовый InputBlock" helper="Любая вложенная разметка">
                                        <TextInput
                                            id="baseInput"
                                            v-model="demoForm.data.baseInput"
                                            type="text"
                                            class="block w-full"
                                            placeholder="Пример вложенного поля"
                                            :error="!!demoForm.errors['baseInput']"
                                            @input="demoForm.clearErrors('baseInput')"
                                        />
                                    </InputBlock>
                                </div>
                                <TextInputBlock
                                    v-model="demoForm.data.title"
                                    :form="demoForm"
                                    field="title"
                                    label="Название"
                                    placeholder="Введите название"
                                    helper="Короткое описание поля"
                                />

                                <NumberInputBlock
                                    v-model="demoForm.data.amount"
                                    :form="demoForm"
                                    field="amount"
                                    label="Сумма"
                                    placeholder="0"
                                    helper="Только числа"
                                />

                                <div class="md:col-span-2">
                                    <label class="label pb-1"><span class="label-text">Мультивыбор</span></label>
                                    <Multiselect :options="demoMultiOptions" v-model="demoMulti" :enable-search="true" />
                                </div>

                                <div>
                                    <DropDownWithCheckbox
                                        v-model="selectedTimes"
                                        label="Время"
                                        :items="demoCheckboxItems"
                                        value="value"
                                        name="name"
                                    />
                                </div>
                                <div>
                                    <DropDownWithRadio
                                        v-model="selectedCurrency"
                                        label="Выберите валюту"
                                        :items="demoRadioItems"
                                        value="value"
                                        name="name"
                                    />
                                </div>

                                <div>
                                    <label class="label pb-1"><span class="label-text">Время</span></label>
                                    <TimepickerInput v-model="demoTime" />
                                </div>
                                <div>
                                    <label class="label pb-1"><span class="label-text">Файл</span></label>
                                    <Dropzone v-model="demoFile" title="Перетащите файл или кликните" />
                                </div>
                            </div>

                            <div class="mt-4">
                                <SaveButton :disabled="false" :saved="saveDone" />
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


