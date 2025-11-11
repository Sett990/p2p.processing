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
    // Поддержка ISO (с Z/offset) и наивного 'YYYY-MM-DD HH:MM[:SS]'
    let correctedDate;
    const isoMatch = (dateString ?? '').match(
        /^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})(?::(\d{2}))?(?:\.\d+)?(Z|[+-]\d{2}:\d{2})$/
    );
    if (isoMatch) {
        correctedDate = new Date(dateString);
    } else {
        const naive = (dateString ?? '').match(
            /^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})(?::(\d{2}))?(?:\.\d+)?$/
        );
        if (!naive) {
            // Неизвестный формат — показываем "только что", чтобы не ломать UI
            return 'только что';
        }
        const [, y, mo, d, h, mi, s] = naive;
        // Создаём локальную дату без смены таймзоны
        correctedDate = new Date(
            Number(y),
            Number(mo) - 1,
            Number(d),
            Number(h),
            Number(mi),
            s ? Number(s) : 0,
            0
        );
    }
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
    if (props.plural) {
        return formatDateRelative(props.data);
    }

    // Компактный абсолютный формат из ISO-строки БЕЗ смены таймзоны:
    // - если сегодня: HH:MM
    // - если год текущий: DD.MM HH:MM
    // - если год не текущий: DD.MM.YYYY HH:MM
    // - если вчера: DD.MM HH:MM (и .YYYY, если год отличается от текущего)
    // Парсим поля даты/времени напрямую из строки (локальное для её offset/Z) и используем тот же offset для "сегодня/вчера"
    const iso = props.data ?? '';
    const m = iso.match(/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})(?::(\d{2}))?(?:\.\d+)?(Z|([+-])(\d{2}):(\d{2}))$/);
    // Наивный формат без зоны
    const naive = !m ? iso.match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})(?::(\d{2}))?(?:\.\d+)?$/) : null;
    if (!m && !naive) {
        return iso; // fallback — показать как есть
    }

    let y, mo, d, h, mi, sign, offH, offM;
    if (m) {
        [, y, mo, d, h, mi, , sign, offH, offM] = m;
    } else if (naive) {
        [, y, mo, d, h, mi] = naive;
    }
    const pad = (n) => String(n).padStart(2, '0');
    const yearNum = Number(y);

    const day = pad(Number(d));
    const month = pad(Number(mo));
    const hours = pad(Number(h));
    const minutes = pad(Number(mi));

    let tzNowYear, tzNowMonth, tzNowDay, tzBaselineMs, tzIsIso = !!m;
    if (tzIsIso) {
        // Вычисляем "сейчас" в той же таймзоне, что и ISO (с учётом offset)
        const offsetTotalMin = sign ? (sign === '+' ? 1 : -1) * (Number(offH) * 60 + Number(offM)) : 0;
        const nowUtcMs = Date.now();
        tzBaselineMs = nowUtcMs + offsetTotalMin * 60 * 1000;
        const tzNow = new Date(tzBaselineMs);
        tzNowYear = tzNow.getUTCFullYear();
        tzNowMonth = tzNow.getUTCMonth() + 1;
        tzNowDay = tzNow.getUTCDate();
    } else {
        // Наивная дата — сравниваем с локальным "сегодня"
        const now = new Date();
        tzBaselineMs = now.getTime();
        tzNowYear = now.getFullYear();
        tzNowMonth = now.getMonth() + 1;
        tzNowDay = now.getDate();
    }

    const isToday = Number(y) === tzNowYear && Number(mo) === tzNowMonth && Number(d) === tzNowDay;

    const tzYesterday = new Date(tzBaselineMs - 24 * 60 * 60 * 1000);
    const tzYesterdayYear = tzIsIso ? tzYesterday.getUTCFullYear() : tzYesterday.getFullYear();
    const tzYesterdayMonth = (tzIsIso ? tzYesterday.getUTCMonth() : tzYesterday.getMonth()) + 1;
    const tzYesterdayDay = tzIsIso ? tzYesterday.getUTCDate() : tzYesterday.getDate();
    const isYesterday = Number(y) === tzYesterdayYear && Number(mo) === tzYesterdayMonth && Number(d) === tzYesterdayDay;

    if (isToday) {
        // Сегодня: только время
        return `${hours}:${minutes}`;
    }

    if (isYesterday) {
        // Вчера: день.месяц + время, и если год отличается от текущего — добавить год
        if (yearNum !== tzNowYear) {
            return `${day}.${month}.${yearNum} ${hours}:${minutes}`;
        }
        return `${day}.${month} ${hours}:${minutes}`;
    }

    // Остальные даты: если год текущий — без года, иначе с годом
    if (yearNum === tzNowYear) {
        return `${day}.${month} ${hours}:${minutes}`;
    }
    return `${day}.${month}.${yearNum} ${hours}:${minutes}`;
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
                <svg class="h-4 w-4 text-base-content/70" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 10h16m-8-3V4M7 7V4m10 3V4M5 20h14a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Zm3-7h.01v.01H8V13Zm4 0h.01v.01H12V13Zm4 0h.01v.01H16V13Zm-8 4h.01v.01H8V17Zm4 0h.01v.01H12V17Zm4 0h.01v.01H16V17Z" />
                </svg>
                <p class="inline-block align-middle text-base-content">{{ formatedData }}</p>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
