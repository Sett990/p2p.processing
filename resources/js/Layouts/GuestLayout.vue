<script setup>

</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-base-200 space-y-4">
        <div>
            <div class="block">
                <div class="text-5xl font-semibold">P2P</div>
                <div class="text-xl font-semibold">Processing</div>
            </div>
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
