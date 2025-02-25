<script setup>
import { ref, onMounted, onUnmounted, provide } from "vue";

const isOpen = ref(false);

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
};

const closeDropdown = () => {
    isOpen.value = false;
};

// Передаём `closeMenu` дочерним компонентам
provide("closeMenu", closeDropdown);

// Закрытие при клике вне меню
const handleClickOutside = (event) => {
    if (!event.target.closest(".dropdown-container")) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);
});
</script>

<template>
    <div class="relative inline-block text-left dropdown-container">
        <button
            @click="toggleDropdown"
            class="p-2 text-gray-500 hover:text-gray-700 rounded-full focus:outline-none focus:ring-2 focus:ring-gray-300"
        >
            <svg class="w-[24px] h-[24px] text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M12 6h.01M12 12h.01M12 18h.01"/>
            </svg>
        </button>

        <div
            v-if="isOpen"
            class="absolute right-0 z-10 mt-2 w-40 bg-white border border-gray-200 rounded-lg shadow-lg"
        >
            <ul class="py-2 text-gray-700">
                <slot />
            </ul>
        </div>
    </div>
</template>
