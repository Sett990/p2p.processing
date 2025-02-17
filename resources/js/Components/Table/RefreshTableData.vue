<script setup>
import {onMounted, ref, watch} from "vue";
import {router, usePoll} from "@inertiajs/vue3";

const intervals = ref([
    {name:'Не обновлять', value:0},
    {name:'Каждые 5с',  value:5000},
    {name:'Каждые 10с', value:10000},
    {name:'Каждые 20с', value:20000},
    {name:'Каждые 30с', value:30000},
]);

const emit = defineEmits(['refreshStarted', 'refreshFinished']);
const storageKey = `refresh-storage-orders`;
const refreshInterval = ref(localStorage.getItem(storageKey) ? localStorage.getItem(storageKey) : 15000);

const { start, stop } = usePoll(refreshInterval.value, {
        onStart() {
            emit('refreshStarted');
        },
        onFinish() {
            emit('refreshFinished');
        }
    }, {keepAlive: true, autoStart: false}
);

onMounted(() => {
    if (refreshInterval.value > 0) {
        start();
    } else {
        stop();
    }
});

const storeRefreshInterval = () => {
    localStorage.setItem(storageKey, refreshInterval.value);
}

const reloadPage = () => {
    storeRefreshInterval();

    router.visit(route(route().current()));
}


</script>

<template>
    <div class="w-48">
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
