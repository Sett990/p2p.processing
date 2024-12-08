<script setup>
import {Head, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {useClipboard} from "@vueuse/core";

const user = usePage().props.auth.user;

const { text, copy, copied } = useClipboard()

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <Head title="Приложение для подтверждения по СМС и Push"/>

    <div>
        <section class="py-8 antialiased md:py-16">
            <div class="mx-auto max-w-screen-xl px-4 2xl:px-0">
                <div class="mx-auto max-w-3xl space-y-6 sm:space-y-8">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white sm:text-2xl">Приложение для подтверждения по СМС и Push</h2>

                    <ol class="relative border-s border-gray-200 dark:border-gray-700">
                        <li class="mb-8 ms-6">
                                  <span
                                      class="absolute -start-2.5 flex h-5 w-5 items-center justify-center rounded-full bg-gray-200 dark:bg-gray-800 dark:ring-gray-900">
                                    <svg class="h-3 w-3 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                         xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                                         viewBox="0 0 24 24">
                                      <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="M5 11.917 9.724 16.5 19 7.5"/>
                                    </svg>
                                  </span>
                            <h3 class="mb-1.5 text-lg font-semibold leading-none text-gray-900 dark:text-white">
                                1. Скачайте и установите APK
                            </h3>
                            <p class="mb-3 text-base font-normal text-gray-500 dark:text-gray-400">
                                Для получения СМС нужно приложение, которое доступно только для Android
                            </p>
                            <p class="text-base font-normal text-gray-500 dark:text-gray-400">
                                <a :href="route('app.download')" class="text-blue-500">Скачать</a>
                            </p>
                        </li>
                        <li class="mb-10 ms-6">
                                  <span
                                      class="absolute -start-2.5 flex h-5 w-5 items-center justify-center rounded-full bg-gray-200 dark:bg-gray-800 dark:ring-gray-900">
                                    <svg class="h-3 w-3 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                         xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                                         viewBox="0 0 24 24">
                                      <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="M5 11.917 9.724 16.5 19 7.5"/>
                                    </svg>
                                  </span>
                            <h3 class="mb-1.5 text-lg font-semibold leading-none text-gray-900 dark:text-white">
                                2. Запустите приложение и укажите данные
                            </h3>
                            <p class="text-base font-normal text-gray-500 dark:text-gray-400">
                                Откройте приложение, нажмите на плюс и создайте новый метод. При создании впишите:
                            </p>

                            <div class="mt-5 w-full max-w-lg bg-white dark:bg-gray-800 border-gray-200 border dark:border-gray-700 shadow rounded-xl  p-5">
                                <label for="api-key" class="text-sm font-medium text-gray-900 dark:text-white mb-2 block">Ваш код:</label>
                                <div class="relative mb-4">
                                    <input
                                        id="api-key"
                                        type="text"
                                        class="col-span-6 bg-gray-50 border border-gray-300 text-gray-500 text-sm rounded-xl  focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-gray-400 dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                        :value="user.apk_access_token"
                                        disabled
                                        readonly
                                    >
                                    <button
                                        data-copy-to-clipboard-target="api-key"
                                        data-tooltip-target="tooltip-api-key"
                                        @click="copy(user.apk_access_token)"
                                        class="absolute end-2 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl  p-2 inline-flex items-center justify-center"
                                    >
                                    <span v-if="!copied" id="default-icon-api-key">
                                        <svg class="w-3.5 h-3.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                             viewBox="0 0 18 20">
                                            <path
                                                d="M16 1h-3.278A1.992 1.992 0 0 0 11 0H7a1.993 1.993 0 0 0-1.722 1H2a2 2 0 0 0-2 2v15a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2Zm-3 14H5a1 1 0 0 1 0-2h8a1 1 0 0 1 0 2Zm0-4H5a1 1 0 0 1 0-2h8a1 1 0 1 1 0 2Zm0-5H5a1 1 0 0 1 0-2h2V2h4v2h2a1 1 0 1 1 0 2Z"/>
                                        </svg>
                                    </span>
                                    <span v-else id="success-icon-api-key" class="inline-flex items-center">
                                        <svg class="w-3.5 h-3.5 text-blue-700 dark:text-blue-500" aria-hidden="true"
                                             xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 16 12">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M1 5.917 5.724 10.5 15 1.5"/>
                                        </svg>
                                    </span>
                                    </button>
                                    <div id="tooltip-api-key" role="tooltip"
                                         class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-xl  shadow-sm opacity-0 tooltip dark:bg-gray-700">
                                        <span v-if="!copied" id="default-tooltip-message-api-key">Скопировать</span>
                                        <span v-else id="success-tooltip-message-api-key">Скопировано!</span>
                                        <div class="tooltip-arrow" data-popper-arrow></div>
                                    </div>
                                </div>
                            </div>
                        </li>

                        <li class="ms-6">
                                  <span
                                      class="absolute -start-2.5 flex h-5 w-5 items-center justify-center rounded-full bg-gray-200 dark:bg-gray-800 dark:ring-gray-900">
                                    <svg class="h-3 w-3 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                         xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                                         viewBox="0 0 24 24">
                                      <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="M5 11.917 9.724 16.5 19 7.5"/>
                                    </svg>
                                  </span>
                            <h3 class="mb-1.5 text-lg font-semibold leading-none text-gray-900 dark:text-white">
                                3. Сохраните и всё. Всё готово!
                            </h3>
                            <p class="text-base font-normal text-gray-500 dark:text-gray-400">
                                При получении СМС от банка, мы его получим от Вашего телефона. Главное, чтобы он работал
                                :)
                            </p>
                        </li>
                    </ol>
                </div>
            </div>
        </section>
    </div>
</template>

