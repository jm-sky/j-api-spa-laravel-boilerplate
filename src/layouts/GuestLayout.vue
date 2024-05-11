<script setup lang="ts">
import ApplicationLogo from '@/components/ApplicationLogo.vue';
import { RouteMap } from '@/router/routeMap';
import { RouterLink } from 'vue-router';
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div>
            <RouterLink :to="RouteMap.HOME">
                <ApplicationLogo class="w-32 h-20 text-lg fill-current text-gray-500" />
            </RouterLink>
        </div>

        <div
            class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg"
        >
            <slot />
        </div>
    </div>
</template>
