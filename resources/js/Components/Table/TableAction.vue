<script setup>
import { inject, defineEmits } from "vue";

const closeMenu = inject("closeMenu");
const emit = defineEmits(["click"]);

const handleClick = (event) => {
    event.stopPropagation(); // Предотвращаем закрытие меню до выполнения действия
    emit("click"); // Вызываем переданный @click в родителе
    closeMenu(); // Закрываем меню
};
</script>

<template>
    <li>
        <button @click="handleClick" class="block w-full px-4 py-2 text-left hover:bg-gray-100">
            <slot />
        </button>
    </li>
</template>
