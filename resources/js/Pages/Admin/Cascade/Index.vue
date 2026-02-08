<script setup>
import {Head, router} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <Head title="Каскад" />

    <div class="space-y-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-base-content">Каскад</h1>
            <p class="text-sm text-base-content/70">Раздел каскадной логики и интеграций</p>
        </div>

        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <p class="text-sm text-base-content/70">
                    Страница в разработке. Здесь появятся настройки и мониторинг каскада.
                </p>
                <div class="mt-4">
                    <button
                        type="button"
                        class="btn btn-primary btn-sm"
                        @click="router.visit(route('admin.cascade.providers.index'), { preserveScroll: true })"
                    >
                        Перейти к провайдерам
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
