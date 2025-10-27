<script setup>
import {computed, ref, watch, onMounted, onUnmounted, nextTick} from "vue";
import {useTableFiltersStore} from "@/store/tableFilters.js";
import Pikaday from "pikaday";

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

const dateInputRef = ref(null);
let picker = null;

// Форматирование даты для отображения в формате DD/MM/YYYY
const formatDateForDisplay = (date) => {
    if (!date) return "";
    const d = new Date(date);
    if (isNaN(d.getTime())) return "";

    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();

    return `${day}/${month}/${year}`;
};

// Парсинг даты из формата DD.MM.YYYY
const parseDateFromDisplay = (dateString) => {
    if (!dateString) return null;

    const match = dateString.match(/^(\d{2})[./](\d{2})[./](\d{4})$/);
    if (match) {
        const [, day, month, year] = match;
        return new Date(year, month - 1, day);
    }

    return null;
};

onMounted(async () => {
    await nextTick();

    if (dateInputRef.value) {
        picker = new Pikaday({
            field: dateInputRef.value,
            format: 'DD/MM/YYYY',
            i18n: {
                previousMonth: 'Предыдущий месяц',
                nextMonth: 'Следующий месяц',
                months: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
                weekdays: ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'],
                weekdaysShort: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб']
            },
            onSelect: function(date) {
                if (date) {
                    model.value = formatDateForDisplay(date);
                } else {
                    model.value = "";
                }
            }
        });

        // Устанавливаем начальное значение если оно есть
        if (model.value) {
            const parsedDate = parseDateFromDisplay(model.value);
            if (parsedDate) {
                picker.setDate(parsedDate);
                dateInputRef.value.value = formatDateForDisplay(parsedDate);
            }
        }
    }
});

onUnmounted(() => {
    if (picker) {
        picker.destroy();
    }
});

// Следим за изменениями модели и обновляем picker
watch(model, (newValue) => {
    if (picker && newValue !== dateInputRef.value.value) {
        const parsedDate = parseDateFromDisplay(newValue);
        if (parsedDate) {
            picker.setDate(parsedDate);
            dateInputRef.value.value = formatDateForDisplay(parsedDate);
        } else {
            picker.setDate(null);
            dateInputRef.value.value = "";
        }
    }
});
</script>

<template>
    <div class="md:flex items-center gap-4 w-48">
        <div class="relative w-full">
        <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none z-10">
            <svg class="w-4 h-4 text-base-content opacity-60" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z"/>
                </svg>
            </div>
            <input
                ref="dateInputRef"
                type="text"
                class="input input-bordered input-sm w-48 ps-10 pika-single"
                :placeholder="title || 'Выберите дату'"
                readonly
            >
        </div>
    </div>
</template>