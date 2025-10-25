<script setup>
import SectionTitle from '@/Components/SectionTitle.vue';
import { formatDateTime } from '@/utils';

const props = defineProps({
    loginHistory: {
        type: Array,
        required: true,
    },
});

const formatDate = (date) => {
    return formatDateTime(date);
};

const getStatusClass = (isSuccessful) => {
    return isSuccessful ? 'text-success' : 'text-error';
};

const getStatusText = (isSuccessful) => {
    return isSuccessful ? 'Успешно' : 'Неудачно';
};
</script>

<template>
    <section>
        <SectionTitle>
            <template #title>История авторизаций</template>
            <template #description>
                Здесь вы можете просмотреть историю входов в ваш аккаунт.
            </template>
        </SectionTitle>

        <div class="mt-5">
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th>Устройство</th>
                            <th>IP адрес</th>
                            <th>Браузер</th>
                            <th>ОС</th>
<!--                            <th>Местоположение</th>-->
                            <th>Дата и время</th>
                            <th>Статус</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in loginHistory" :key="index">
                            <td>{{ item.device_type }}</td>
                            <td>{{ item.ip_address }}</td>
                            <td>{{ item.browser }}</td>
                            <td>{{ item.operating_system }}</td>
<!--                            <td>{{ item.location }}</td>-->
                            <td>{{ formatDate(item.created_at) }}</td>
                            <td class="text-sm" :class="getStatusClass(item.is_successful)">
                                {{ getStatusText(item.is_successful) }}
                            </td>
                        </tr>
                        <tr v-if="loginHistory.length === 0">
                            <td colspan="7" class="text-center text-base-content/60">
                                История авторизаций пуста
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</template>
