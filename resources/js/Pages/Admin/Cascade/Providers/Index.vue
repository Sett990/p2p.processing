<script setup>
import {Head, router} from '@inertiajs/vue3';
import {computed, ref} from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modals/Modal.vue';
import ModalHeader from '@/Components/Modals/Components/ModalHeader.vue';
import ModalBody from '@/Components/Modals/Components/ModalBody.vue';
import ModalFooter from '@/Components/Modals/Components/ModalFooter.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import NumberInput from '@/Components/NumberInput.vue';
import TextArea from '@/Components/TextArea.vue';
import InputError from '@/Components/InputError.vue';

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    providers: {
        type: Array,
        default: () => [],
    },
    nextIntegrationCode: {
        type: String,
        default: null,
    },
});

const showModal = ref(false);
const isEditMode = ref(false);
const processing = ref(false);
const errors = ref({});
const activeProvider = ref(null);

const form = ref({
    code: '',
    name: '',
    provider_type: 'external',
    is_active: true,
    weight: null,
    priority: null,
    description: '',
    config_json: '',
});

const modalTitle = computed(() => {
    if (isEditMode.value) {
        return activeProvider.value
            ? `Редактирование провайдера - ${activeProvider.value.name}`
            : 'Редактирование провайдера';
    }
    return 'Создание провайдера';
});

const resetForm = () => {
    form.value = {
        code: props.nextIntegrationCode ?? '',
        name: '',
        provider_type: 'external',
        is_active: true,
        weight: null,
        priority: null,
        description: '',
        config_json: '',
    };
    errors.value = {};
};

const openCreate = () => {
    isEditMode.value = false;
    activeProvider.value = null;
    resetForm();
    showModal.value = true;
};

const openEdit = (provider) => {
    isEditMode.value = true;
    activeProvider.value = provider;
    form.value = {
        code: provider.code,
        name: provider.name,
        provider_type: 'external',
        is_active: !!provider.is_active,
        weight: provider.weight ?? null,
        priority: provider.priority ?? null,
        description: provider.description ?? '',
        config_json: provider.config_json ?? '',
    };
    errors.value = {};
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    errors.value = {};
};

const submit = () => {
    processing.value = true;
    errors.value = {};

    const payload = {
        name: form.value.name,
        is_active: !!form.value.is_active,
        weight: form.value.weight !== null && form.value.weight !== '' ? Number(form.value.weight) : null,
        priority: form.value.priority !== null && form.value.priority !== '' ? Number(form.value.priority) : null,
        description: form.value.description || null,
        config_json: form.value.config_json || null,
    };

    if (isEditMode.value && activeProvider.value) {
        axios
            .patch(route('admin.cascade.providers.update', activeProvider.value.id), payload, {
                headers: { 'Accept': 'application/json' },
            })
            .then(() => {
                processing.value = false;
                closeModal();
                router.reload({ only: ['providers', 'nextIntegrationCode'] });
            })
            .catch((error) => {
                processing.value = false;
                if (error.response?.data?.errors) {
                    errors.value = error.response.data.errors;
                }
            });
        return;
    }

    axios
        .post(route('admin.cascade.providers.store'), {
            ...payload,
            code: form.value.code,
        }, {
            headers: { 'Accept': 'application/json' },
        })
        .then(() => {
            processing.value = false;
            closeModal();
            router.reload({ only: ['providers', 'nextIntegrationCode'] });
        })
        .catch((error) => {
            processing.value = false;
            if (error.response?.data?.errors) {
                errors.value = error.response.data.errors;
            }
        });
};
</script>

<template>
    <Head title="Каскад: провайдеры" />

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-base-content">Провайдеры каскада</h1>
                <p class="text-sm text-base-content/70">Список интеграций и их настройки</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    class="btn btn-outline btn-sm"
                    @click="router.visit(route('admin.cascade.index'), { preserveScroll: true })"
                >
                    Назад к каскаду
                </button>
                <button
                    v-if="nextIntegrationCode"
                    type="button"
                    class="btn btn-primary btn-sm"
                    @click="openCreate"
                >
                    Добавить интеграцию
                </button>
            </div>
        </div>

        <div v-if="nextIntegrationCode" class="alert alert-info">
            <span>
                Доступна новая интеграция <b>{{ nextIntegrationCode }}</b>. Вы можете создать запись провайдера.
            </span>
        </div>

        <div class="card bg-base-100 shadow">
            <div class="card-body p-0">
                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>Код</th>
                                <th>Название</th>
                                <th>Тип</th>
                                <th>Активен</th>
                                <th>Вес</th>
                                <th>Приоритет</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="!providers.length">
                                <td colspan="7" class="text-center text-sm text-base-content/70 py-6">
                                    Провайдеры пока не добавлены
                                </td>
                            </tr>
                            <tr v-for="provider in providers" :key="provider.id">
                                <td class="font-mono text-sm">{{ provider.code }}</td>
                                <td>{{ provider.name }}</td>
                                <td>
                                    <span class="badge badge-ghost">{{ provider.provider_type }}</span>
                                </td>
                                <td>
                                    <span
                                        class="badge"
                                        :class="provider.is_active ? 'badge-success' : 'badge-neutral'"
                                    >
                                        {{ provider.is_active ? 'Да' : 'Нет' }}
                                    </span>
                                </td>
                                <td>{{ provider.weight ?? '—' }}</td>
                                <td>{{ provider.priority ?? '—' }}</td>
                                <td class="text-right">
                                    <button
                                        type="button"
                                        class="btn btn-ghost btn-xs"
                                        @click="openEdit(provider)"
                                    >
                                        Открыть
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <Modal :show="showModal" @close="closeModal" maxWidth="3xl">
        <ModalHeader :title="modalTitle" @close="closeModal" />
        <ModalBody>
            <form @submit.prevent="submit" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="code" value="Код интеграции" />
                        <div class="mt-1">
                            <span class="badge badge-ghost font-mono">
                                {{ form.code || '—' }}
                            </span>
                        </div>
                        <InputError :message="errors.code?.[0]" class="mt-1" />
                        <p class="text-xs text-base-content/60 mt-1">
                            Код провайдера задается в коде интеграции и не редактируется.
                        </p>
                    </div>

                    <div>
                        <InputLabel for="name" value="Название" />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            class="mt-1 block w-full"
                            :error="!!errors.name?.[0]"
                        />
                        <InputError :message="errors.name?.[0]" class="mt-1" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="weight" value="Вес (%)" />
                        <NumberInput
                            id="weight"
                            v-model="form.weight"
                            class="mt-1 block w-full"
                            placeholder="0"
                            :error="!!errors.weight?.[0]"
                        />
                        <InputError :message="errors.weight?.[0]" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="priority" value="Приоритет" />
                        <NumberInput
                            id="priority"
                            v-model="form.priority"
                            class="mt-1 block w-full"
                            placeholder="0"
                            :error="!!errors.priority?.[0]"
                        />
                        <InputError :message="errors.priority?.[0]" class="mt-1" />
                    </div>
                </div>

                <div>
                    <InputLabel for="description" value="Описание" />
                    <TextArea
                        id="description"
                        v-model="form.description"
                        class="mt-1"
                        :rows="3"
                        :error="!!errors.description?.[0]"
                    />
                    <InputError :message="errors.description?.[0]" class="mt-1" />
                </div>

                <div>
                    <InputLabel for="config_json" value="Конфигурация (JSON)" />
                    <TextArea
                        id="config_json"
                        v-model="form.config_json"
                        class="mt-1 font-mono text-xs"
                        :rows="6"
                        :error="!!errors.config_json?.[0]"
                    />
                    <InputError :message="errors.config_json?.[0]" class="mt-1" />
                </div>

                <div>
                    <label class="label cursor-pointer justify-start gap-3">
                        <input type="checkbox" class="toggle toggle-primary" v-model="form.is_active">
                        <span class="label-text text-sm">Провайдер активен</span>
                    </label>
                    <InputError :message="errors.is_active?.[0]" class="mt-1" />
                </div>
            </form>
        </ModalBody>
        <ModalFooter>
            <button type="button" class="btn btn-sm" @click="closeModal">
                Отмена
            </button>
            <button
                type="button"
                class="btn btn-sm btn-primary"
                :class="{ 'btn-disabled': processing }"
                :disabled="processing"
                @click="submit"
            >
                Сохранить
            </button>
        </ModalFooter>
    </Modal>
</template>
