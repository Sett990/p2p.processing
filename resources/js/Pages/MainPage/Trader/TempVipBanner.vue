<script setup>
import { computed, ref, watch } from 'vue';
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

const storageKey = computed(() => {
    const current = typeof route === 'function' ? route().current() : 'default';
    return `tempVipBannerCollapsed_${current}`;
});

const loadCollapsed = () => {
    if (typeof window === 'undefined') return false;
    const key = storageKey.value;
    const fromLs = window.localStorage?.getItem(key);
    if (fromLs !== null) {
        return fromLs === '1';
    }
    const match = document.cookie.match(new RegExp('(^| )' + key + '=([^;]+)'));
    return match ? match[2] === '1' : false;
};

const saveCollapsed = (val) => {
    if (typeof window === 'undefined') return;
    const key = storageKey.value;
    try {
        window.localStorage.setItem(key, val ? '1' : '0');
    } catch (e) {
        // ignore
    }
    document.cookie = `${key}=${val ? '1' : '0'}; path=/; max-age=31536000`;
};

const collapsed = ref(loadCollapsed());

const toggleCollapsed = () => {
    collapsed.value = !collapsed.value;
    saveCollapsed(collapsed.value);
};

watch(storageKey, () => {
    collapsed.value = loadCollapsed();
});
</script>

<template>
    <div
        v-if="tempVip?.enabled"
        class="card bg-primary text-primary-content shadow"
        :class="collapsed ? 'w-full sm:w-auto sm:max-w-md' : 'w-full'"
    >
        <div class="card-body gap-4 py-3 sm:py-4">
            <div class="flex items-start justify-between gap-3">
                <div class="space-y-1">
                    <p class="text-xs sm:text-sm opacity-80">Временный VIP</p>
                    <h3 class="text-lg sm:text-xl font-semibold">
                        {{ tempVip.active ? 'VIP активен' : 'Выполните норму, чтобы включить VIP' }}
                    </h3>
                </div>
                <button
                    type="button"
                    class="btn btn-ghost btn-xs text-primary-content border border-primary-content/20"
                    @click="toggleCollapsed"
                >
                    {{ collapsed ? 'Развернуть' : 'Свернуть' }}
                </button>
            </div>

            <template v-if="collapsed">
                <div class="flex flex-wrap items-center gap-2 text-sm opacity-90">
                    <span class="badge badge-sm badge-outline border-primary-content/40 text-primary-content">
                        Активен
                    </span>
                    <span v-if="tempVip.active" class="flex items-center gap-1">
                        <CountdownTimer :end="tempVip.active_until" :muted="true" />
                    </span>
                    <span v-else class="flex items-center gap-2">
                        <span class="font-semibold text-xs sm:text-sm">{{ tempVip.count }} / {{ tempVip.required }}</span>
                        <progress class="progress progress-accent w-28 h-2 bg-primary-content/20" :value="progressPercent" max="100"></progress>
                    </span>
                    <span v-if="!tempVip.active && canActivate" class="badge badge-xs sm:badge-sm badge-accent border-none">
                        Готов к включению
                    </span>
                    <button
                        v-if="!tempVip.active"
                        type="button"
                        class="btn btn-ghost btn-xs text-primary-content"
                        :class="{ 'btn-disabled': !canActivate || form.processing }"
                        :disabled="!canActivate || form.processing"
                        @click="activate"
                    >
                        Включить
                    </button>
                </div>
            </template>

            <template v-else>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div class="space-y-1">
                        <p v-if="tempVip.active_until" class="text-sm opacity-90">
                            До окончания: <CountdownTimer :end="tempVip.active_until" />
                        </p>
                    </div>
                    <div class="text-right space-y-1">
                        <p class="text-xs opacity-80">Прогресс</p>
                        <p class="text-lg font-semibold">
                            {{ tempVip.count }} / {{ tempVip.required }}
                        </p>
                    </div>
                </div>

                <div v-if="!tempVip.active" class="space-y-3">
                    <progress class="progress w-full" :value="progressPercent" max="100"></progress>
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <p class="text-sm opacity-90">
                            Осталось сделок: <span class="font-semibold">{{ tempVip.remaining }}</span>
                        </p>
                        <button
                            type="button"
                            class="btn btn-sm sm:btn-md btn-accent"
                            :class="{ 'btn-disabled': !canActivate || form.processing }"
                            :disabled="!canActivate || form.processing"
                            @click="activate"
                        >
                            Включить VIP
                        </button>
                    </div>
                </div>

                <div v-else class="flex flex-wrap items-center justify-between gap-2 text-sm opacity-90">
                    <span>Лимиты реквизитов активированы на время VIP</span>
                    <span>После окончания прогресс начнется заново</span>
                </div>
            </template>
        </div>
    </div>
</template>

