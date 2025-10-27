<script setup>
import {onMounted} from "vue";
//import {initFlowbite} from "flowbite";
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-base-200">
        <div>
<!--        logo    -->
        </div>

        <div
            class="w-full sm:max-w-md mt-6 card bg-base-100 shadow-md overflow-hidden sm:rounded-plate"
        >
            <div class="card-body p-6">
            <slot />
            </div>
        </div>
    </div>
</template>
