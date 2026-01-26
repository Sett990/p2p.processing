<script setup>
import { Head } from '@inertiajs/vue3';
import { usePage, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MainTableSection from '@/Wrappers/MainTableSection.vue';
import DateTime from '@/Components/DateTime.vue';
import FiltersPanel from '@/Components/Filters/FiltersPanel.vue';
import InputFilter from '@/Components/Filters/Pertials/InputFilter.vue';

defineOptions({ layout: AuthenticatedLayout });

const logs = usePage().props.logs;
</script>

<template>
    <div>
        <Head title="История антифрода" />

        <MainTableSection
            title="История антифрода"
            :data="logs"
        >
            <template v-slot:button>
                <button
                    type="button"
                    class="btn btn-outline"
                    @click="router.visit(route('admin.anti-fraud-settings.index'), { preserveScroll: true })"
                >
                    К настройкам
                </button>
            </template>
            <template v-slot:table-filters>
                <FiltersPanel name="anti-fraud-history">
                    <InputFilter
                        name="merchant"
                        placeholder="Мерчант (имя или uuid)"
                    />
                    <InputFilter
                        name="clientId"
                        placeholder="Client ID"
                    />
                </FiltersPanel>
            </template>

            <template v-slot:body>
                <div class="relative">
                    <div class="overflow-x-auto card bg-base-100 shadow">
                        <table class="table table-sm">
                            <thead class="text-xs uppercase bg-base-300">
                            <tr>
                                <th>Мерчант</th>
                                <th>Client ID</th>
                                <th>Решение</th>
                                <th>Сообщение</th>
                                <th class="text-right">Дата</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="log in logs.data" :key="log.id">
                                <td>
                                    {{ log.merchant?.name || log.merchant?.uuid || `#${log.merchant_id}` }}
                                </td>
                                <td class="whitespace-nowrap">
                                    {{ log.client_id || '—' }}
                                </td>
                                <td>
                                    <span v-if="log.decision === 'allow'" class="badge badge-success badge-sm">Разрешено</span>
                                    <span v-else class="badge badge-error badge-sm">Отклонено</span>
                                </td>
                                <td class="text-sm text-base-content/80">
                                    {{ log.message || '—' }}
                                </td>
                                <td class="whitespace-nowrap text-right">
                                    <DateTime :data="log.created_at" />
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>
        </MainTableSection>
    </div>
</template>
