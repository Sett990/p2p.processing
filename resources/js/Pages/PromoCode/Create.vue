<script setup>
import {Head, router} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SecondaryPageSection from "@/Wrappers/SecondaryPageSection.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import {ref} from "vue";
import Checkbox from "@/Components/Checkbox.vue";

const form = ref({
    code: '',
    max_uses: 10,
    is_active: true,
    processing: false,
});

const errors = ref({});

const submit = () => {
    form.value.processing = true;

    router.post(route('leader.promo-codes.store'), form.value, {
        onSuccess: () => {
            form.value.processing = false;
        },
        onError: (e) => {
            errors.value = e;
            form.value.processing = false;
        }
    });
};

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Создание промокода" />

        <SecondaryPageSection title="Создание промокода">
            <form @submit.prevent="submit">
                <div class="mt-4">
                    <InputLabel for="code" value="Код (оставьте пустым для автогенерации)" />
                    <TextInput
                        id="code"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.code"
                        placeholder="Введите код или оставьте пустым"
                    />
                    <InputError :message="errors.code" class="mt-2" />
                </div>

                <div class="mt-4">
                    <InputLabel for="max_uses" value="Максимальное количество использований" />
                    <TextInput
                        id="max_uses"
                        type="number"
                        class="mt-1 block w-full"
                        v-model="form.max_uses"
                        min="0"
                        required
                    />
                    <InputError :message="errors.max_uses" class="mt-2" />
                    <p class="text-sm text-gray-500 mt-1">Установите 0 для неограниченного использования</p>
                </div>

                <div class="mt-4">
                    <label class="flex items-center">
                        <Checkbox v-model:checked="form.is_active" />
                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Активен</span>
                    </label>
                    <InputError :message="errors.is_active" class="mt-2" />
                </div>

                <div class="flex items-center justify-end mt-4">
                    <button
                        type="button"
                        class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-xl font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150 mr-2"
                        @click="router.visit(route('leader.promo-codes.index'))"
                    >
                        Отмена
                    </button>
                    <button
                        type="submit"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                        :disabled="form.processing"
                    >
                        Создать
                    </button>
                </div>
            </form>
        </SecondaryPageSection>
    </div>
</template>
