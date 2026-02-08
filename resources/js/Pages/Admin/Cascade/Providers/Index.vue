<script setup>
import {Head, router} from '@inertiajs/vue3';
import {computed, ref} from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MainTableSection from "@/Wrappers/MainTableSection.vue";
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
    currencyCodes: {
        type: Array,
        default: () => [],
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
    base_url: '',
    access_token: '',
    merchant_id: '',
    callback_url: '',
    currency_code: '',
    timeout: null,
    verify_ssl: true,
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
        base_url: '',
        access_token: '',
        merchant_id: '',
        callback_url: '',
        currency_code: '',
        timeout: null,
        verify_ssl: true,
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
        base_url: provider.base_url ?? '',
        access_token: provider.access_token ?? '',
        merchant_id: provider.merchant_id ?? '',
        callback_url: provider.callback_url ?? '',
        currency_code: provider.currency_code ?? '',
        timeout: provider.timeout ?? null,
        verify_ssl: provider.verify_ssl ?? true,
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
        base_url: form.value.base_url || null,
        access_token: form.value.access_token || null,
        merchant_id: form.value.merchant_id || null,
        callback_url: form.value.callback_url || null,
        currency_code: form.value.currency_code || null,
        timeout: form.value.timeout !== null && form.value.timeout !== '' ? Number(form.value.timeout) : null,
        verify_ssl: !!form.value.verify_ssl,
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

    <MainTableSection
        title="Провайдеры каскада"
        :data="providers"
        :paginate="false"
        :displayPagination="false"
    >
        <template v-slot:button>
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
        </template>

        <template v-slot:header>
            <div class="space-y-3">
                <p class="text-sm text-base-content/70">Список интеграций и их настройки</p>
                <div v-if="nextIntegrationCode" class="alert alert-info">
                    <span>
                        Доступна новая интеграция <b>{{ nextIntegrationCode }}</b>. Вы можете создать запись провайдера.
                    </span>
                </div>
            </div>
        </template>

        <template v-slot:body>
            <div class="relative">
                <div class="hidden xl:block">
                    <div class="overflow-x-auto card bg-base-100 shadow">
                        <table class="table table-sm">
                            <thead class="text-xs uppercase bg-base-300">
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

                <div class="xl:hidden space-y-3">
                    <div class="space-y-2">
                        <div
                            v-for="provider in providers"
                            :key="provider.id"
                            class="card bg-base-100 shadow-sm"
                        >
                            <div class="card-body p-4 pt-2 pb-3">
                                <div class="flex justify-between items-center border-b border-base-content/10 mb-1 pb-2">
                                    <div class="inline-flex items-center gap-2">
                                        <span class="text-base-content/70">Код:</span>
                                        <span class="font-medium font-mono">{{ provider.code }}</span>
                                    </div>
                                    <span
                                        class="badge"
                                        :class="provider.is_active ? 'badge-success' : 'badge-neutral'"
                                    >
                                        {{ provider.is_active ? 'Да' : 'Нет' }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between gap-2">
                                    <div class="min-w-0">
                                        <div class="text-base-content">{{ provider.name }}</div>
                                        <div class="text-xs text-base-content/70">{{ provider.provider_type }}</div>
                                    </div>
                                    <div>
                                        <button
                                            type="button"
                                            class="btn btn-ghost btn-xs"
                                            @click="openEdit(provider)"
                                        >
                                            Открыть
                                        </button>
                                    </div>
                                </div>

                                <div class="border-b border-base-content/10 my-2"></div>
                                <div class="flex items-center justify-between text-xs text-base-content/80">
                                    <div>Вес: {{ provider.weight ?? '—' }}</div>
                                    <div>Приоритет: {{ provider.priority ?? '—' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </MainTableSection>

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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="base_url" value="Base URL" />
                        <TextInput
                            id="base_url"
                            v-model="form.base_url"
                            class="mt-1 block w-full"
                            :error="!!errors.base_url?.[0]"
                        />
                        <InputError :message="errors.base_url?.[0]" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="access_token" value="Access Token" />
                        <TextInput
                            id="access_token"
                            v-model="form.access_token"
                            class="mt-1 block w-full"
                            :error="!!errors.access_token?.[0]"
                        />
                        <InputError :message="errors.access_token?.[0]" class="mt-1" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="merchant_id" value="Merchant ID" />
                        <TextInput
                            id="merchant_id"
                            v-model="form.merchant_id"
                            class="mt-1 block w-full"
                            :error="!!errors.merchant_id?.[0]"
                        />
                        <InputError :message="errors.merchant_id?.[0]" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="callback_url" value="Callback URL" />
                        <TextInput
                            id="callback_url"
                            v-model="form.callback_url"
                            class="mt-1 block w-full"
                            :error="!!errors.callback_url?.[0]"
                        />
                        <InputError :message="errors.callback_url?.[0]" class="mt-1" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="currency_code" value="Валюта" />
                        <select
                            id="currency_code"
                            v-model="form.currency_code"
                            class="select select-bordered w-full mt-1"
                            :class="{ 'select-error': !!errors.currency_code?.[0] }"
                        >
                            <option value="">—</option>
                            <option v-for="code in currencyCodes" :key="code" :value="code">
                                {{ code }}
                            </option>
                        </select>
                        <InputError :message="errors.currency_code?.[0]" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="timeout" value="Timeout (сек)" />
                        <NumberInput
                            id="timeout"
                            v-model="form.timeout"
                            class="mt-1 block w-full"
                            placeholder="10"
                            :error="!!errors.timeout?.[0]"
                        />
                        <InputError :message="errors.timeout?.[0]" class="mt-1" />
                    </div>
                </div>

                <div>
                    <label class="label cursor-pointer justify-start gap-3">
                        <input type="checkbox" class="toggle toggle-primary" v-model="form.is_active">
                        <span class="label-text text-sm">Провайдер активен</span>
                    </label>
                    <InputError :message="errors.is_active?.[0]" class="mt-1" />
                </div>

                <div>
                    <label class="label cursor-pointer justify-start gap-3">
                        <input type="checkbox" class="toggle toggle-primary" v-model="form.verify_ssl">
                        <span class="label-text text-sm">Проверять SSL</span>
                    </label>
                    <InputError :message="errors.verify_ssl?.[0]" class="mt-1" />
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
