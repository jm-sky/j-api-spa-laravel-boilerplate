<script setup lang="ts">
import axiosInstance from '@/helpers/axiosInstance';
import GuestLayout from "@/layouts/GuestLayout.vue";
import FaIcon from "@/components/FaIcon.vue";
import router from '@/router';
import { RouteMap } from '@/router/routeMap';
import { onMounted } from 'vue';

onMounted(() => axiosInstance.post(RouteMap.API.LOGOUT).finally(() => router.push(RouteMap.LOGIN)))
</script>

<template>
  <GuestLayout>
    <div class="flex flex-col items-center justify-center text-xl p-4">
      <div>Logging out...</div>
      <div class="mt-5">
        <FaIcon icon="refresh" loading />
      </div>
    </div>
  </GuestLayout>
</template>
