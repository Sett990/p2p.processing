<script setup>
import {Head, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {useClipboard} from "@vueuse/core";

const user = usePage().props.auth.user;
const token = usePage().props.token;

const { text, copy, copied } = useClipboard()

const openDocs = () => {
    window.open('/docs', '_blank');
}

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <Head title="Интеграция по API"/>

    <div>
        <section class="antialiased">
            <div class="mx-auto">
                <div class="mx-auto">
                    <h2 class="text-xl text-base-content sm:text-4xl mb-6">Интеграция по API</h2>

                    <h3 class="mb-1.5 text-lgleading-none text-base-content">
                        Изучите инструкцию по интеграции сервиса
                    </h3>
                    <p class="text-base text-base-content/70">
                        <a @click.prevent="openDocs" href="#" class="link link-primary">Открыть документацию</a>
                    </p>


                    <div class="mt-5 card w-full max-w-lg bg-base-100 shadow p-5">
                        <label for="api-key" class="text-sm font-medium text-base-content mb-2 block">API токен:</label>
                        <div class="relative mb-4">
                            <input
                                id="api-key"
                                type="text"
                                class="col-span-6 bg-base-200 border border-base-300 text-base-content/70 text-sm rounded-xl focus:ring-primary focus:border-primary block w-full p-2.5"
                                :value="token"
                                disabled
                                readonly
                            >
                            <button
                                data-copy-to-clipboard-target="api-key"
                                data-tooltip-target="tooltip-api-key"
                                @click="copy(token)"
                                class="absolute end-2 top-1/2 -translate-y-1/2 text-base-content/70 hover:bg-base-200 rounded-xl p-2 inline-flex items-center justify-center"
                                type="button"
                                aria-label="Скопировать токен"
                            >
                                    <span v-if="!copied" id="default-icon-api-key">
                                        <svg class="w-3.5 h-3.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                             viewBox="0 0 18 20">
                                            <path
                                                d="M16 1h-3.278A1.992 1.992 0 0 0 11 0H7a1.993 1.993 0 0 0-1.722 1H2a2 2 0 0 0-2 2v15a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2Zm-3 14H5a1 1 0 0 1 0-2h8a1 1 0 0 1 0 2Zm0-4H5a1 1 0 0 1 0-2h8a1 1 0 1 1 0 2Zm0-5H5a1 1 0 0 1 0-2h2V2h4v2h2a1 1 0 1 1 0 2Z"/>
                                        </svg>
                                    </span>
                                <span v-else id="success-icon-api-key" class="inline-flex items-center">
                                        <svg class="w-3.5 h-3.5 text-primary" aria-hidden="true"
                                             xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 16 12">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M1 5.917 5.724 10.5 15 1.5"/>
                                        </svg>
                                    </span>
                            </button>
                            <div id="tooltip-api-key" role="tooltip"
                                 class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-neutral-content transition-opacity duration-300 bg-neutral rounded-xl shadow-sm opacity-0 tooltip">
                                <span v-if="!copied" id="default-tooltip-message-api-key">Скопировать</span>
                                <span v-else id="success-tooltip-message-api-key">Скопировано!</span>
                                <div class="tooltip-arrow" data-popper-arrow></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>
