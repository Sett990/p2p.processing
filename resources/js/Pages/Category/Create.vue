<script setup>
import {Head, router, useForm} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import SaveButton from '@/Components/Form/SaveButton.vue';
import GoBackButton from '@/Components/GoBackButton.vue';

const form = useForm({
    name: '',
    description: '',
});

const submit = () => {
    form.post(route('admin.categories.store'), {
        preserveScroll: true,
    });
};

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Создание категории"/>

        <div class="mx-auto space-y-4">
            <div class="mb-3">
                <GoBackButton @click="router.visit(route('admin.categories.index'))"/>
            </div>
            <div class="p-5 sm:p-6 bg-white shadow-md rounded-plate dark:bg-gray-800">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">Создание категории</h1>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <InputLabel for="name" value="Название" :error="!!form.errors.name"/>
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full"
                            :error="!!form.errors.name"
                            @input="form.clearErrors('name')"
                        />
                        <InputError :message="form.errors.name" class="mt-2"/>
                    </div>

                    <div>
                        <InputLabel for="description" value="Описание" :error="!!form.errors.description"/>
                        <TextInput
                            id="description"
                            v-model="form.description"
                            type="text"
                            class="mt-1 block w-full"
                            :error="!!form.errors.description"
                            @input="form.clearErrors('description')"
                        />
                        <InputError :message="form.errors.description" class="mt-2"/>
                    </div>

                    <SaveButton :disabled="form.processing" :saved="form.recentlySuccessful"/>
                </form>
            </div>
        </div>
    </div>
</template>
