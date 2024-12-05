<script setup>
import {onMounted, ref} from "vue";

const isDarkColorTheme = ref(false);

const switchThemeColorMode = () => {
    // if set via local storage previously
    if (localStorage.getItem('color-theme-payment')) {
        if (localStorage.getItem('color-theme-payment') === 'light') {
            document.documentElement.classList.add('dark');
            localStorage.setItem('color-theme-payment', 'dark');
            isDarkColorTheme.value = true;
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('color-theme-payment', 'light');
            isDarkColorTheme.value = false;
        }

        // if NOT set via local storage previously
    } else {
        if (document.documentElement.classList.contains('dark')) {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('color-theme-payment', 'light');
            isDarkColorTheme.value = false;
        } else {
            document.documentElement.classList.add('dark');
            localStorage.setItem('color-theme-payment', 'dark');
            isDarkColorTheme.value = true;
        }
    }
}

onMounted(() => {
    if (localStorage.getItem('color-theme-payment') === 'dark' || (!('color-theme-payment' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('color-theme-payment', 'dark');
        isDarkColorTheme.value = true;
    } else {
        document.documentElement.classList.remove('dark')
        localStorage.setItem('color-theme-payment', 'light');
        isDarkColorTheme.value = false;
    }
})
</script>

<template>
    <button @click.prevent="switchThemeColorMode" type="button" class="text-gray-800 dark:text-white border-none hover:text-gray-500 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-full text-sm p-2.5 text-center inline-flex items-center dark:hover:text-gray-500 dark:focus:ring-blue-800">
        <svg v-if="isDarkColorTheme" class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
            <path fill-rule="evenodd" d="M13 3a1 1 0 1 0-2 0v2a1 1 0 1 0 2 0V3ZM6.343 4.929A1 1 0 0 0 4.93 6.343l1.414 1.414a1 1 0 0 0 1.414-1.414L6.343 4.929Zm12.728 1.414a1 1 0 0 0-1.414-1.414l-1.414 1.414a1 1 0 0 0 1.414 1.414l1.414-1.414ZM12 7a5 5 0 1 0 0 10 5 5 0 0 0 0-10Zm-9 4a1 1 0 1 0 0 2h2a1 1 0 1 0 0-2H3Zm16 0a1 1 0 1 0 0 2h2a1 1 0 1 0 0-2h-2ZM7.757 17.657a1 1 0 1 0-1.414-1.414l-1.414 1.414a1 1 0 1 0 1.414 1.414l1.414-1.414Zm9.9-1.414a1 1 0 0 0-1.414 1.414l1.414 1.414a1 1 0 0 0 1.414-1.414l-1.414-1.414ZM13 19a1 1 0 1 0-2 0v2a1 1 0 1 0 2 0v-2Z" clip-rule="evenodd"/>
        </svg>
        <svg v-else class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5V3m0 18v-2M7.05 7.05 5.636 5.636m12.728 12.728L16.95 16.95M5 12H3m18 0h-2M7.05 16.95l-1.414 1.414M18.364 5.636 16.95 7.05M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z"/>
        </svg>
    </button>
</template>

<style scoped>

</style>
