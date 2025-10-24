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
                <div class="overflow-x-auto card bg-base-100 shadow">
                    <table class="table table-md">
                        <thead class="text-xs uppercase bg-base-300">
                            <tr>
                                <th scope="col" class="whitespace-nowrap">
                                    ID
                                </th>
                                <th scope="col" class="whitespace-nowrap">
                                    Пользователь
                                </th>
                                <th scope="col" class="whitespace-nowrap">
                                    Промокод
                                </th>
                                <th scope="col" class="whitespace-nowrap">
                                    Сделок
                                </th>
                                <th scope="col" class="whitespace-nowrap">
                                    Доход
                                </th>
                                <th scope="col" class="whitespace-nowrap">
                                    Дата привлечения
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="referral in referrals.data" :key="referral.id" class="hover">
                                <th scope="row" class="font-medium whitespace-nowrap">
                                    {{ referral.id }}
                                </th>
                                <td class="whitespace-nowrap">
                                    <div class="inline-flex items-center gap-3">
                                        <div class="avatar">
                                            <div class="w-10 rounded-full">
                                                <img :src="'https://api.dicebear.com/9.x/'+referral.avatar_style+'/svg?seed='+referral.avatar_uuid" alt="user photo">
                                            </div>
                                        </div>
                                        <div class="leading-tight">
                                            <div class="whitespace-nowrap">
                                                {{ referral.email }}
                                            </div>
                                            <div class="text-xs text-base-content/70 whitespace-nowrap">
                                                {{ referral.name }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap">
                                    <span v-if="referral.promo_code?.code" class="badge badge-ghost">
                                        {{ referral.promo_code.code }}
                                    </span>
                                    <span v-else class="badge badge-ghost">-</span>
                                </td>
                                <td class="whitespace-nowrap">
                                    {{ referral.orders_count }}
                                </td>
                                <td class="whitespace-nowrap">
                                    <span class="badge badge-outline">
                                        {{ referral.total_profit || '0.00' }} USDT
                                    </span>
                                </td>
                                <td class="whitespace-nowrap">
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
