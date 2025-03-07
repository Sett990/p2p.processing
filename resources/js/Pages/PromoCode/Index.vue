<script setup>
import {Head, router, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MainTableSection from "@/Wrappers/MainTableSection.vue";
import {ref} from "vue";

const promoCodes = ref(usePage().props.promoCodes);
const filters = ref(usePage().props.filters);

router.on('success', (event) => {
    promoCodes.value = usePage().props.promoCodes;
})

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Промокоды" />

        <MainTableSection
            title="Промокоды"
            :data="promoCodes"
            :query-data="{filters}"
        >
            <template v-slot:header>

            </template>
            <template v-slot:body>

            </template>
        </MainTableSection>
    </div>
</template>
