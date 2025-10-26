<script setup>
import {computed} from "vue";
import {useClipboard} from "@vueuse/core";

const props = defineProps({
    data: {
        type: String,
    },
    plural: {
        type: Boolean,
        default: null,
    },
});

const formatDateRelative = (dateString) => {
    // Создаем дату из строки как московское время (UTC+3)
    const moscowDate = new Date(dateString);

    // Получаем разницу между московским временем и локальным временем пользователя в минутах
    const moscowOffset = 3 * 60; // Москва UTC+3 (в минутах)
    const localOffset = new Date().getTimezoneOffset() * -1; // Локальное смещение в минутах (с обратным знаком)
    const offsetDiff = moscowOffset - localOffset; // Разница в минутах

    // Корректируем дату с учетом разницы часовых поясов
    const correctedDate = new Date(moscowDate.getTime() - offsetDiff * 60 * 1000);

    const now = new Date();
    const diffInSeconds = Math.floor((now - correctedDate) / 1000);

    const intervals = {
        'год': 31536000,
        'месяц': 2592000,
        'неделя': 604800,
        'день': 86400,
        'час': 3600,
        'минута': 60,
        'секунда': 1,
    };

    for (const [unit, seconds] of Object.entries(intervals)) {
        const interval = Math.floor(diffInSeconds / seconds);
        if (interval >= 1) {
            if (unit === 'секунда' && interval < 5) {
                return 'только что';
            }
            return `${interval} ${getPluralForm(interval, unit)} назад`;
        }
    }

    return 'только что';
}

const getPluralForm = (number, unit) => {
    const pluralRules = {
        'год': ['год', 'года', 'лет'],
        'месяц': ['месяц', 'месяца', 'месяцев'],
        'неделя': ['неделя', 'недели', 'недель'],
        'день': ['день', 'дня', 'дней'],
        'час': ['час', 'часа', 'часов'],
        'минута': ['минута', 'минуты', 'минут'],
        'секунда': ['секунда', 'секунды', 'секунд'],
    };

    const cases = [2, 0, 1, 1, 1, 2];
    return pluralRules[unit][
        number % 100 > 4 && number % 100 < 20
            ? 2
            : cases[Math.min(number % 10, 5)]
        ];
}

const formatedData = computed(() => {
    return props.plural ? formatDateRelative(props.data) : props.data;
});

const { copy, copied } = useClipboard();
</script>

<template>
    <div>
        <div class="tooltip" :data-tip="copied ? 'Скопировано!' : 'Скопировать'">
            <div
                class="btn btn-ghost btn-xs gap-2 inline-flex items-center text-nowrap"
                role="button"
                tabindex="0"
                @click.prevent="copy(data)"
            >
                <svg class="h-4 w-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 10h16m-8-3V4M7 7V4m10 3V4M5 20h14a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Zm3-7h.01v.01H8V13Zm4 0h.01v.01H12V13Zm4 0h.01v.01H16V13Zm-8 4h.01v.01H8V17Zm4 0h.01v.01H12V17Zm4 0h.01v.01H16V17Z" />
                </svg>
                <p class="inline-block align-middle">{{ formatedData }}</p>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
