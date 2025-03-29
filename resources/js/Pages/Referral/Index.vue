<script setup>
import {Head, router, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import DateTime from "@/Components/DateTime.vue";
import {ref} from "vue";

const props = defineProps({
    referrals: Object,
});

const referrals = ref(usePage().props.referrals);

router.on('success', (event) => {
    referrals.value = usePage().props.referrals;
})

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Рефералы" />

        <MainTableSection
            title="Рефералы"
            :data="referrals"
            description="Здесь вы можете увидеть список всех ваших рефералов."
        >
            <template v-slot:body>
                <div class="relative overflow-x-auto shadow-md rounded-table">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">
                                    ID
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Пользователь
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Промокод
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Дата привлечения
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="referral in referrals.data" :key="referral.id" class="bg-white border-b last:border-none dark:bg-gray-800 dark:border-gray-700">
                                <th scope="row" class="px-6 py-3 font-medium whitespace-nowrap text-gray-900 dark:text-gray-200">
                                    {{ referral.id }}
                                </th>
                                <td class="px-6 py-3 text-nowrap">
                                    <div class="inline-flex items-center gap-2">
                                        <img :src="'https://api.dicebear.com/9.x/'+referral.avatar_style+'/svg?seed='+referral.avatar_uuid" class="w-10 h-10 rounded-full" alt="user photo">
                                        <div>
                                            <div class="text-nowrap text-gray-900 dark:text-gray-200">
                                                {{ referral.email }}
                                            </div>
                                            <div class="text-nowrap text-xs">
                                                {{ referral.name }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-nowrap">
                                    {{ referral.promo_code?.code || '-' }}
                                </td>
                                <td class="px-6 py-3 text-nowrap">
                                    <DateTime :data="referral.promo_used_at"/>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </MainTableSection>
    </div>
</template>
