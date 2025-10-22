<script setup>
import {Head, useForm} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SecondaryPageSection from "@/Wrappers/SecondaryPageSection.vue";

const form = useForm({
    name: '',
    description: '',
    project_link: '',
});

const submit = () => {
    form
        .post(route('merchants.store'), {
            preserveScroll: true,
            onSuccess: () => form.reset(),
        });
};

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Создание мерчанта" />

        <SecondaryPageSection
            :back-link="route('merchants.index')"
            title="Создание мерчанта"
            description="Здесь вы можете создать мерчант."
        >
            <form @submit.prevent="submit" class="mt-6 space-y-6">
                <div class="form-control w-full">
                    <label for="name" class="label">
                        <span class="label-text" :class="{'text-error': !!form.errors.name}">Название проекта</span>
                    </label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="input input-bordered w-full"
                        :class="{'input-error': !!form.errors.name}"
                        @input="form.clearErrors('name')"
                    />
                    <p v-if="form.errors.name" class="mt-2 text-sm text-error">{{ form.errors.name }}</p>
                </div>

                <div class="form-control w-full">
                    <label for="description" class="label">
                        <span class="label-text" :class="{'text-error': !!form.errors.description}">Опишите деятельность проекта</span>
                    </label>
                    <input
                        id="description"
                        v-model="form.description"
                        type="text"
                        class="input input-bordered w-full"
                        :class="{'input-error': !!form.errors.description}"
                        @input="form.clearErrors('description')"
                    />
                    <p v-if="form.errors.description" class="mt-2 text-sm text-error">{{ form.errors.description }}</p>
                </div>

                <div class="form-control w-full">
                    <label for="project_link" class="label">
                        <span class="label-text" :class="{'text-error': !!form.errors.project_link}">Укажите ссылку на проект</span>
                    </label>
                    <input
                        id="project_link"
                        v-model="form.project_link"
                        type="text"
                        class="input input-bordered w-full"
                        :class="{'input-error': !!form.errors.project_link}"
                        @input="form.clearErrors('project_link')"
                    />
                    <p v-if="form.errors.project_link" class="mt-2 text-sm text-error">{{ form.errors.project_link }}</p>
                    <span v-else class="label-text-alt">Указывайте ссылку в формате https://example.com/</span>
                </div>

                <button
                    type="submit"
                    class="btn btn-primary"
                    :class="{loading: form.processing}"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Сохранение...' : (form.recentlySuccessful ? 'Сохранено' : 'Сохранить') }}
                </button>
            </form>
        </SecondaryPageSection>
    </div>
</template>
