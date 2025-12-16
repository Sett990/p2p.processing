<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import CountdownTimer from '@/Components/CountdownTimer.vue';

const props = defineProps({
    tempVip: {
        type: Object,
        required: true,
    },
});

const form = useForm({});

const progressPercent = computed(() => {
    const required = props.tempVip?.required ?? 0;
    const count = props.tempVip?.count ?? 0;
    if (!required) return 0;
    return Math.min(Math.round((count / required) * 100), 100);
});

const canActivate = computed(() => !!(props.tempVip?.can_activate || props.tempVip?.temp_vip_can_activate));

const activate = () => {
    if (!canActivate.value || form.processing) return;

    form.post(route('trader.temp-vip.activate'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <div
        v-if="tempVip?.enabled"
        class="card bg-primary text-primary-content shadow w-full sm:w-auto sm:max-w-md relative z-40"
    >
        <div class="card-body gap-2 py-3 sm:py-4">
            <div class="flex flex-wrap items-center gap-2 text-sm opacity-90">
                <span class="text-xs sm:text-sm opacity-80 inline-flex items-center gap-1">
                    <span>Временный VIP</span>
                    <span
                        class="tooltip tooltip-bottom relative z-50"
                        data-tip="Прогресс показывает, сколько успешных сделок нужно выполнить. Когда норма будет выполнена — можно включить временный VIP и получить расширенные лимиты на время."
                    >
                        <span class="btn btn-ghost btn-xs btn-circle text-primary-content/90">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                            </svg>
                        </span>
                    </span>
                </span>

                <template v-if="tempVip.active">
                    <span class="badge badge-sm badge-outline border-primary-content/40 text-primary-content">
                        Активен
                    </span>
                    <span class="flex items-center gap-1">
                        <CountdownTimer :end="tempVip.active_until" :muted="true" />
                    </span>
                </template>

                <template v-else>
                    <span class="font-semibold text-xs sm:text-sm">{{ tempVip.count }} / {{ tempVip.required }}</span>
                    <progress class="progress progress-accent w-28 h-2 bg-primary-content/20" :value="progressPercent" max="100"></progress>
                    <span v-if="canActivate" class="badge badge-xs sm:badge-sm badge-accent border-none">
                        Готов к включению
                    </span>
                    <button
                        type="button"
                        class="btn btn-ghost btn-xs text-primary-content"
                        :class="{ 'btn-disabled': !canActivate || form.processing }"
                        :disabled="!canActivate || form.processing"
                        @click="activate"
                    >
                        Включить
                    </button>
                </template>
            </div>
        </div>
    </div>
</template>

