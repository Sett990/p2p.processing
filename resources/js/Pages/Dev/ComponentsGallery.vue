<script setup>
import {computed} from 'vue'
import {usePage} from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import SectionTitle from '@/Components/SectionTitle.vue'

const props = defineProps({})

// Автоматически импортируем все Vue-компоненты из каталога Components
const modules = import.meta.glob('@/Components/**/*.vue', { eager: true })

const components = computed(() => {
    return Object.entries(modules)
        .map(([path, mod]) => {
            const name = path.split('/').slice(-1)[0].replace('.vue','')
            const component = mod.default || mod
            return { path, name, component }
        })
        .sort((a,b) => a.name.localeCompare(b.name))
})
</script>

<template>
    <AuthenticatedLayout title="Компоненты (DEV)">
        <div class="space-y-6">
            <SectionTitle>Галерея компонентов</SectionTitle>

            <div class="space-y-6">
                <div class="text-sm opacity-60">
                    Найдено компонентов: {{ components.length }}
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    <div v-for="item in components" :key="item.path" class="card bg-base-100 card-border border-base-300 shadow">
                        <div class="card-body">
                            <div class="font-semibold text-base mb-2 break-all">{{ item.name }}</div>
                            <div class="text-xs opacity-50 mb-3 break-all">{{ item.path }}</div>
                            <div class="p-4 rounded-xl bg-base-200">
                                <component :is="item.component" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
    
</template>

<style scoped>

</style>


