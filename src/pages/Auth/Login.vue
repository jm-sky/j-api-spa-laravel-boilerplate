<script setup lang="ts">
import Checkbox from "@/components/Checkbox.vue";
import GuestLayout from "@/layouts/GuestLayout.vue";
import InputError from "@/components/InputError.vue";
import InputLabel from "@/components/InputLabel.vue";
import TextInput from "@/components/TextInput.vue";
import { RouteMap } from "@/router/routeMap";
import { RouterLink } from "vue-router";
import { useForm } from "@/helpers/useForm";
import { Button } from '@/components/ui/button'
import { DEFAULT_USER_EMAIL, DEFAULT_USER_PASSWORD } from '@/config';
import { useToast } from 'vue-toast-notification';

const toast = useToast()

defineProps<{
  canResetPassword?: boolean;
  status?: string;
}>();

const form = useForm({
  email: DEFAULT_USER_EMAIL ?? '',
  password: DEFAULT_USER_PASSWORD ?? '',
  remember: false,
  errors: {},
});

const submit = () => {
  form.post(RouteMap.API.LOGIN, {
    onSuccess: () => form.reset(),
    onError: () => toast.error('Could not login'),
  });
};
</script>

<template>
  <GuestLayout>
    <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
      {{ status }}
    </div>

    <form @submit.prevent="submit">
      <div>
        <InputLabel for="email" value="Email" />

        <TextInput
          id="email"
          type="email"
          class="mt-1 block w-full"
          :class="{ 'ring-1 ring-red-600' : form.hasErrors }"
          v-model="form.email"
          required
          autofocus
          autocomplete="username"
        />

        <InputError class="mt-2" :message="form.errors.email" />
      </div>

      <div class="mt-4">
        <InputLabel for="password" value="Password" />

        <TextInput
          id="password"
          type="password"
          class="mt-1 block w-full"
          :class="{ 'ring-1 ring-red-600' : form.hasErrors }"
          v-model="form.password"
          required
          autocomplete="current-password"
        />

        <InputError class="mt-2" :message="form.errors.password" />
      </div>

      <div class="block mt-4">
        <label class="flex items-center">
          <Checkbox name="remember" v-model:checked="form.remember" />
          <span class="ms-2 text-sm text-gray-600">Remember me</span>
        </label>
      </div>

      <div class="flex items-center justify-end mt-4">
        <RouterLink
          v-if="canResetPassword"
          :to="RouteMap.PASSWORD_FORGOT"
          class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
        >
          Forgot your password?
        </RouterLink>

        <Button
          class="ms-4"
          :disabled="form.processing"
        >
          Log in
        </Button>
      </div>

      <div class="mt-6 text-center text-sm">
        Don't have account?
        <RouterLink
          :to="RouteMap.REGISTER"
          class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
        >
          Register
        </RouterLink>
      </div>
    </form>
  </GuestLayout>
</template>
