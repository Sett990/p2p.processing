<script setup>
import {onMounted, ref, watch} from "vue";
import {router, usePage, usePoll} from "@inertiajs/vue3";

const intervals = ref([
    {name:'Не обновлять', value:0},
    {name:'Каждые 5с',  value:5000},
    {name:'Каждые 10с', value:10000},
    {name:'Каждые 20с', value:20000},
    {name:'Каждые 30с', value:30000},
]);

const emit = defineEmits(['refreshStarted', 'refreshFinished']);
const storageKey = `refresh-storage-orders`;
const refreshInterval = ref(localStorage.getItem(storageKey) ? localStorage.getItem(storageKey) : 0);

const { start, stop } = usePoll(refreshInterval.value, {
        onStart() {
            emit('refreshStarted');
        },
        onFinish() {
            emit('refreshFinished');
            progressTime.value = refreshInterval.value;
        }
    }, {keepAlive: true, autoStart: false}
);

const progress = ref(100);
const progressTime = ref(refreshInterval.value);

const startProgressBar = () => {
    function updateClock() {
        if (refreshInterval.value <= 0) {
            clearInterval(timeinterval);
        }

        progress.value = progressTime.value / (refreshInterval.value / 100)
        progressTime.value = progressTime.value - 50;

        if (progressTime.value <= 0) {
            progressTime.value = refreshInterval.value;
        }
    }

    updateClock();
    let timeinterval = setInterval(updateClock, 50);//TODO stop on leave page
}

onMounted(() => {
    if (refreshInterval.value > 0) {
        start();
        startProgressBar();
    } else {
        stop();
    }
})

const storeRefreshInterval = () => {
    localStorage.setItem(storageKey, refreshInterval.value);
}

const reloadPage = () => {
    storeRefreshInterval();

    router.visit(route(route().current()), {preserveScroll: true});
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
                        d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="4"
                        stroke-dasharray="100, 100"
                        :stroke-dashoffset="progress"
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
