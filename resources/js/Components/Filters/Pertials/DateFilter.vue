<script setup>
import {computed, getCurrentInstance, onMounted} from "vue";
//import {Datepicker} from "flowbite-datepicker";
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

const instance = getCurrentInstance();
const uid = instance.uid;

onMounted(() => {
    const datepickerElement = document.getElementById(`date-datepicker-${uid}`)

    datepickerElement.addEventListener('changeDate', (e) => {
        model.value = e.target.value;
    });

    Datepicker.locales.ru = {
        days: ["Воскресенье", "Понедельник", "Вторник", "Среда", "Четверг", "Пятница", "Суббота"],
        daysShort: ["Вск", "Пнд", "Втр", "Срд", "Чтв", "Птн", "Суб"],
        daysMin: ["Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"],
        months: ["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"],
        monthsShort: ["Янв", "Фев", "Мар", "Апр", "Май", "Июн", "Июл", "Авг", "Сен", "Окт", "Ноя", "Дек"],
        today: "Сегодня",
        clear: "Очистить",
        format: "dd.mm.yyyy",
        weekStart: 1,
        monthsTitle: 'Месяцы'
    };

    new Datepicker(datepickerElement, {
        language: 'ru',
        format: 'dd/mm/yyyy',
    })
})
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
                datepicker
                :id="`date-datepicker-${uid}`"
                type="text"
                class="input input-bordered input-sm w-48 ps-10"
                :placeholder="title"
                :value="model"
            >
        </div>
    </div>
</template>

<style scoped>

</style>
