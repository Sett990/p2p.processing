<script setup>
import {Head, router, useForm, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import MainTableSection from '@/Wrappers/MainTableSection.vue';
import DateTime from '@/Components/DateTime.vue';
import {ref} from "vue";

const devices = ref(usePage().props.devices.data);

const form = useForm({
    name: '',
});

const submit = () => {
    form.post(route('trader.devices.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

router.on('success', (event) => {
    devices.value = usePage().props.devices.data;
})

const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text).then(() => {
        alert('Токен скопирован в буфер обмена');
    });
};

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head title="Устройства" />

        <MainTableSection title="Устройства" :data="devices" :paginate="false">
            <template v-slot:header>
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md rounded-plate mb-6">
                    <div>
                        <h3 class="mb-1.5 text-lg font-semibold leading-none text-gray-900 dark:text-white">
                            Скачайте и установите APK
                        </h3>
                        <p class="text-base font-normal text-gray-600 dark:text-gray-400">
                            Для получения СМС нужно приложение, которое доступно только для Android - <a :href="route('app.download')" class="text-blue-500">Скачать</a>
                        </p>
                    </div>
                </div>
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md rounded-plate">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-200">
                                Создать новый токен для устройства
                            </h2>

                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Создайте новый токен для подключения устройства. Один токен может быть использован только для одного устройства.
                            </p>
                        </header>

                        <form @submit.prevent="submit" class="mt-6 space-y-6">
                            <div>
                                <InputLabel for="name" value="Название устройства" />

                                <TextInput
                                    id="name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.name"
                                    required
                                    autofocus
                                    placeholder="Например: Samsung Galaxy S21"
                                />

                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>

                            <div class="flex items-center gap-4">
                                <PrimaryButton :disabled="form.processing">
                                    Создать токен
                                </PrimaryButton>

                                <Transition
                                    enter-active-class="transition ease-in-out"
                                    enter-from-class="opacity-0"
                                    leave-active-class="transition ease-in-out"
                                    leave-to-class="opacity-0"
                                >
                                    <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">
                                        Токен создан.
                                    </p>
                                </Transition>
                            </div>
                        </form>
                    </section>
                </div>
            </template>

            <template v-slot:body>
                <div class="relative overflow-x-auto shadow-md rounded-table">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">
                                Название
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Токен
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Статус
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Создан
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Подключен
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="device in devices" :key="device.id" class="bg-white border-b last:border-none dark:bg-gray-800 dark:border-gray-700">
                            <th scope="row" class="px-6 py-3 font-medium whitespace-nowrap text-gray-900 dark:text-gray-200">
                                {{ device.name }}
                            </th>
                            <td class="px-6 py-3">
                                <div class="flex items-center">
                                    <span class="truncate max-w-36 text-gray-900 dark:text-gray-200">{{ device.token }}</span>
                                    <button
                                        @click="copyToClipboard(device.token)"
                                        class="ml-2 text-indigo-600 hover:text-indigo-900"
                                        title="Копировать токен"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                            <td class="px-6 py-3">
                                <span
                                    :class="[
                                        'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                        device.android_id ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                                    ]"
                                >
                                    {{ device.android_id ? 'Подключено' : 'Не подключено' }}
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                <DateTime class="justify-start" :data="device.created_at"/>
                            </td>
                            <td class="px-6 py-3">
                                <DateTime v-if="device.connected_at" class="justify-start" :data="device.connected_at"/>
                                <span v-else class="text-gray-500">Не подключено</span>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </MainTableSection>
    </div>
</template>
