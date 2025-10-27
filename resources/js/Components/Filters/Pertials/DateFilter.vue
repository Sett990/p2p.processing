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

// Форматирование даты для отображения в формате DD.MM.YYYY
const formatDateForDisplay = (date) => {
    if (!date) return "";
    const d = new Date(date);
    if (isNaN(d.getTime())) return "";

    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();

    return `${day}.${month}.${year}`;
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
            format: 'DD.MM.YYYY',
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
                dateInputRef.value.value = model.value;
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
        } else {
            picker.setDate(null);
        }
        dateInputRef.value.value = newValue || "";
    }
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
                ref="dateInputRef"
                type="text"
                class="input input-bordered input-sm w-48 ps-10 pika-single"
                :placeholder="title || 'Выберите дату'"
                readonly
            >
        </div>
    </div>
</template>

<style scoped>
/* Стили для Pikaday календаря */
:deep(.pika-single) {
    z-index: 9999;
}

:deep(.pika-lendar) {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    font-family: inherit;
}

:deep(.pika-title) {
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
    padding: 0.75rem;
    text-align: center;
}

:deep(.pika-label) {
    font-weight: 600;
    color: #374151;
}

:deep(.pika-prev),
:deep(.pika-next) {
    background: transparent;
    border: none;
    color: #6b7280;
    cursor: pointer;
    padding: 0.5rem;
    transition: color 0.2s;
}

:deep(.pika-prev:hover),
:deep(.pika-next:hover) {
    color: #374151;
}

:deep(.pika-table) {
    width: 100%;
    border-collapse: collapse;
}

:deep(.pika-table th) {
    background: #f3f4f6;
    color: #6b7280;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.5rem;
    text-align: center;
}

:deep(.pika-table td) {
    padding: 0;
    text-align: center;
}

:deep(.pika-button) {
    background: transparent;
    border: none;
    color: #374151;
    cursor: pointer;
    display: block;
    font-size: 0.875rem;
    padding: 0.5rem;
    transition: all 0.2s;
    width: 100%;
}

:deep(.pika-button:hover) {
    background: #f3f4f6;
    color: #111827;
}

:deep(.is-today .pika-button) {
    background: #3b82f6;
    color: white;
    font-weight: 600;
}

:deep(.is-selected .pika-button) {
    background: #1d4ed8;
    color: white;
    font-weight: 600;
}

:deep(.is-disabled .pika-button) {
    color: #d1d5db;
    cursor: not-allowed;
}

:deep(.is-disabled .pika-button:hover) {
    background: transparent;
    color: #d1d5db;
}

:deep(.is-outside-the-month .pika-button) {
    color: #d1d5db;
}

:deep(.is-outside-the-month .pika-button:hover) {
    background: #f9fafb;
    color: #9ca3af;
}
</style>
