<script setup>
import {Head, router, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import GoBackButton from "@/Components/GoBackButton.vue";
import {onMounted, ref} from "vue";
import Statistics from "@/Pages/Merchant/Tabs/Statistics.vue";
import Payments from "@/Pages/Merchant/Tabs/Payments.vue";
import Settings from "@/Pages/Merchant/Tabs/Settings.vue";
import {useViewStore} from "@/store/view.js";
import AlertError from "@/Components/Alerts/AlertError.vue";
import AlertInfo from "@/Components/Alerts/AlertInfo.vue";

const tab = ref('statistics');
const viewStore = useViewStore();
const merchant = usePage().props.merchant;

const openPage = (page = null) => {
    let data = {
        tab: tab.value
    };
    if (page) {
        data.page = page;
    }
    router.visit(route(route().current(), merchant.id), { data: data})
}

onMounted(() => {
    let urlParams = new URLSearchParams(window.location.search);
    tab.value = urlParams.get('tab') ?? 'statistics'
})

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <div>
        <Head :title="'Мерчант - ' + merchant.name"/>

        <div class="mx-auto space-y-4">
            <div class="mb-3">
                <GoBackButton @click="router.visit(route(viewStore.adminPrefix + 'merchants.index'))"/>
            </div>
            <div>
                <section>
                    <div>
                        <div class="mt-6 space-y-6">
                            <div role="tablist" class="tabs tabs-lifted">
                                <a role="tab" href="#" @click.prevent="tab = 'statistics'; openPage()" :class="['tab', {'tab-active': tab === 'statistics'}]">
                                    <svg class="w-4 h-4 sm:mr-2 mr-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15v4m6-6v6m6-4v4m6-6v6M3 11l6-5 6 5 5.5-5.5"/>
                                    </svg>
                                    <span class="sm:block hidden">Статистика</span>
                                </a>
                                <a role="tab" href="#" @click.prevent="tab = 'payments'; openPage(1)" :class="['tab', {'tab-active': tab === 'payments'}]">
                                    <svg class="w-4 h-4 sm:mr-2 mr-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17.345a4.76 4.76 0 0 0 2.558 1.618c2.274.589 4.512-.446 4.999-2.31.487-1.866-1.273-3.9-3.546-4.49-2.273-.59-4.034-2.623-3.547-4.488.486-1.865 2.724-2.899 4.998-2.31.982.236 1.87.793 2.538 1.592m-3.879 12.171V21m0-18в2.2"/>
                                    </svg>
                                    <span class="sm:block hidden">Платежи</span>
                                </a>
                                <a role="tab" href="#" @click.prevent="tab = 'settings'; openPage()" :class="['tab', {'tab-active': tab === 'settings'}]">
                                    <svg class="w-4 h-4 sm:mr-2 mr-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13v-2a1 1 0 0 0-1-1h-.757l-.707-1.707.535-.536a1 1 0 0 0 0-1.414l-1.414-1.414a1 1 0 0 0-1.414 0l-.536.535L14 4.757V4a1 1 0 0 0-1-1h-2a1 1 0 0 0-1 1v.757l-1.707.707-.536-.535a1 1 0 0 0-1.414 0L4.929 6.343a1 1 0 0 0 0 1.414л.536.536L4.757 10H4a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h.757l.707 1.707-.535.536a1 1 0 0 0 0 1.414л1.414 1.414a1 1 0 0 0 1.414 0l.536-.535 1.707.707V20a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1v-.757l1.707-.708.536.536a1 1 0 0 0 1.414 0л1.414-1.414a1 1 0 0 0 0-1.414л-.535-.536.707-1.707H20a1 1 0 0 0 1-1Z"/>
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/>
                                    </svg>
                                    <span class="sm:block hidden">Настройки</span>
                                </a>
                            </div>

                            <div v-if="! merchant.validated_at" class="alert alert-warning shadow" role="alert">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="h-6 w-6 shrink-0 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z"/></svg>
                                <span><span class="font-medium">Внимание!</span> Мерчант находится на модерации.</span>
                            </div>

                            <div v-if="merchant.banned_at" class="alert alert-error shadow" role="alert">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 stroke-current" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M12 8v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span><span class="font-medium">Внимание!</span> Мерчант заблокирован администратором.</span>
                            </div>

                            <AlertError :message="$page.props.flash.error"></AlertError>
                            <AlertInfo :message="$page.props.flash.message"></AlertInfo>
                            <Statistics v-show="tab === 'statistics'"/>
                            <Payments v-show="tab === 'payments'" @openPage="openPage"/>
                            <Settings v-show="tab === 'settings'"/>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>
