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
                    <label class="label" for="name">
                        <span class="label-text">Название проекта</span>
                    </label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="input input-bordered w-full"
                        :class="{ 'input-error': form.errors.name }"
                        @input="form.clearErrors('name')"
                    />
                    <label v-if="form.errors.name" class="label">
                        <span class="label-text-alt text-error">{{ form.errors.name }}</span>
                    </label>
                </div>

                <div class="form-control w-full">
                    <label class="label" for="description">
                        <span class="label-text">Опишите деятельность проекта</span>
                    </label>
                    <input
                        id="description"
                        v-model="form.description"
                        type="text"
                        class="input input-bordered w-full"
                        :class="{ 'input-error': form.errors.description }"
                        @input="form.clearErrors('description')"
                    />
                    <label v-if="form.errors.description" class="label">
                        <span class="label-text-alt text-error">{{ form.errors.description }}</span>
                    </label>
                </div>

                <div class="form-control w-full">
                    <label class="label" for="project_link">
                        <span class="label-text">Укажите ссылку на проект</span>
                    </label>
                    <input
                        id="project_link"
                        v-model="form.project_link"
                        type="text"
                        class="input input-bordered w-full"
                        :class="{ 'input-error': form.errors.project_link }"
                        @input="form.clearErrors('project_link')"
                    />
                    <label class="label" v-if="form.errors.project_link">
                        <span class="label-text-alt text-error">{{ form.errors.project_link }}</span>
                    </label>
                    <label class="label" v-else>
                        <span class="label-text-alt">Указывайте ссылку в формате https://example.com/</span>
                    </label>
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn btn-primary" :disabled="form.processing">
                        {{ form.processing ? 'Сохранение...' : 'Сохранить' }}
                    </button>
                    <span v-if="form.recentlySuccessful" class="ml-3 text-success">Сохранено</span>
                </div>
            </form>
        </SecondaryPageSection>
    </div>
</template>
