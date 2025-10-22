<script setup>
import { ref } from "vue";
import {usePage} from "@inertiajs/vue3";

const exchangeRateMarkup = ref(usePage().props.exchangeRateMarkup);

// Функция обновления наценки с проверкой корректности ввода
const updateMarkup = (index, event) => {
    let value = event.target.value.replace(',', '.'); // Заменяем запятую на точку

    // Оставляем только цифры и точку, удаляем любые другие символы
    value = value.replace(/[^0-9.]/g, '');

    exchangeRateMarkup.value[index].markup = value;
    event.target.value = value;
};
</script>

<template>
    <div class="overflow-x-auto rounded-table shadow">
        <table class="table table-sm">
            <thead class="text-xs uppercase bg-base-300">
            <tr>
                <th class="px-6 py-3">Валюта</th>
                <th class="px-6 py-3">Наценка (%)</th>
            </tr>
            </thead>
            <tbody>
            <tr
                v-for="(item, index) in exchangeRateMarkup"
                :key="item.currency"
                class="hover"
            >
                <td class="px-6 py-2 font-medium">
                    {{ item.currency.toUpperCase() }}
                </td>
                <td class="px-6 py-2">
                    <input
                        type="text"
                        inputmode="decimal"
                        class="input input-bordered w-28 h-8"
                        :value="item.markup"
                        @input="updateMarkup(index, $event)"
                    />
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</template>
