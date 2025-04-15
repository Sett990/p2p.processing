<script setup>
import {onMounted, ref} from "vue";
import {router, usePoll} from "@inertiajs/vue3";
import {useTableFiltersStore} from "@/store/tableFilters.js";

const tableFiltersStore = useTableFiltersStore();

const intervals = ref([
    {name:'Не обновлять', value:0},
    {name:'Каждые 15с', value:15000},
    {name:'Каждые 30с', value:30000},
    {name:'Каждые 60с', value:60000},
]);

const emit = defineEmits(['refreshStarted', 'refreshFinished']);
const storageKey = `refresh-storage-orders`;
let interval = localStorage.getItem(storageKey) ? localStorage.getItem(storageKey) : 0
if (parseInt(interval, 10) === 5000) {
    interval = 10000;
}
if (parseInt(interval, 10) === 10000) {
    interval = 15000;
}
if (parseInt(interval, 10) === 20000) {
    interval = 30000;
}

const refreshInterval = ref(interval);

const { start, stop } = usePoll(refreshInterval.value, {
        onStart() {
            emit('refreshStarted');
            animateProgress(100, refreshInterval.value);
        },
        async onFinish() {
            await new Promise(resolve => setTimeout(resolve, 2500));
            emit('refreshFinished');
        }
    }, {keepAlive: true, autoStart: false}
);

const offset = ref(100); // Начальное значение stroke-dashoffset

function animateProgress(targetPercent, duration = 1000) {
    const startOffset = 100; // Начальное значение
    const targetOffset = 100 - targetPercent; // Конечное значение
    const startTime = performance.now();

    function step(currentTime) {
        const elapsedTime = currentTime - startTime;
        const progress = Math.min(elapsedTime / duration, 1); // Прогресс анимации (от 0 до 1)
        offset.value = startOffset - (startOffset - targetOffset) * progress;

        if (progress < 1) {
            requestAnimationFrame(step); // Продолжаем анимацию
        }
    }

    requestAnimationFrame(step);
}

onMounted(() => {
    if (refreshInterval.value > 0) {
        start();
        animateProgress(100, refreshInterval.value);
    } else {
        stop();
    }
})

const storeRefreshInterval = () => {
    localStorage.setItem(storageKey, refreshInterval.value);
}

const reloadPage = () => {
    storeRefreshInterval();

    router.visit(route(route().current()), {
        data: tableFiltersStore.getQueryData,
        preserveScroll: true
    });
}


</script>

<template>
    <div class="flex items-center gap-3">
        <div v-show="refreshInterval > 0" class="flex justify-center items-center">
            <div class="relative w-6 h-6">
                <!-- Фоновый круг -->
                <svg class="w-full h-full" viewBox="0 0 36 36">
                    <path
                        class="text-gray-200"
                        d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="4"
                    />
                </svg>
                <!-- Прогресс -->
                <svg class="absolute top-0 left-0 w-full h-full" viewBox="0 0 36 36">
                    <path
                        class="text-blue-600"
                        :style="{ strokeDashoffset: offset }"
                        d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="4"
                        stroke-dasharray="100, 100"
                    />
                </svg>
            </div>
        </div>

        <select
            class="block py-2.5 px-0 w-full text-sm text-gray-500 bg-transparent border-0 border-b-2 border-gray-200 appearance-none dark:text-gray-400 dark:border-gray-700 focus:outline-none focus:ring-0 focus:border-gray-200 peer"
            required
            v-model="refreshInterval"
            @change="reloadPage"
        >
            <option
                v-for="interval in intervals"
                :value="interval.value"
            >
                {{ interval.name }}
            </option>
        </select>
    </div>
</template>

<style scoped>

</style>
