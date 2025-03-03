<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
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
    return isSuccessful ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';
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

        <div class="mt-5 space-y-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Дата и время
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                IP адрес
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Устройство
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Браузер
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                ОС
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Местоположение
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Статус
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-800">
                        <tr v-for="(item, index) in loginHistory" :key="index">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ formatDate(item.created_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ item.ip_address }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ item.device_type }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ item.browser }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ item.operating_system }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ item.location }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" :class="getStatusClass(item.is_successful)">
                                {{ getStatusText(item.is_successful) }}
                            </td>
                        </tr>
                        <tr v-if="loginHistory.length === 0">
                            <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                                История авторизаций пуста
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</template> 