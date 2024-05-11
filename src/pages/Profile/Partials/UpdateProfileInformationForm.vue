<script setup lang="ts">
import InputError from "@/components/InputError.vue";
import InputLabel from "@/components/InputLabel.vue";
import TextInput from "@/components/TextInput.vue";
import { Button } from '@/components/ui/button';
import { useForm } from "@/helpers/useForm";
import { RouteMap } from '@/router/routeMap';
import { useAuthStore } from "@/stores";
import { User } from "@/types/user.type";

defineProps<{
  mustVerifyEmail?: Boolean;
  status?: String;
}>();

const user = useAuthStore().user as User;

const form = useForm({
  name: user.name,
  email: user.email,
});

const submit = () => form.patch(RouteMap.API.PROFILE_UPDATE)
</script>

<template>
  <section>
    <header>
      <h2 class="text-lg font-medium text-gray-900">Profile Information</h2>

      <p class="mt-1 text-sm text-gray-600">
        Update your account's profile information and email address.
      </p>
    </header>

    <form @submit.prevent="submit" class="mt-6 space-y-6">
      <div>
        <InputLabel for="name" value="Name" />
        <TextInput
          id="name"
          type="text"
          class="mt-1 block w-full"
          v-model="form.name"
          required
          autofocus
          autocomplete="name"
        />
        <InputError class="mt-2" :message="form.errors.name" />
      </div>

      <div>
        <InputLabel for="email" value="Email" />
        <TextInput
          id="email"
          type="email"
          class="mt-1 block w-full"
          v-model="form.email"
          required
          autocomplete="username"
        />
        <InputError class="mt-2" :message="form.errors.email" />
      </div>

      <div v-if="mustVerifyEmail && !user.emailVerifiedAt">
        <p class="text-sm mt-2 text-gray-800">
          Your email address is unverified.
          <RouterLink
            :to="RouteMap.VERIFICATION_SEND"
            method="post"
            as="button"
            class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
          >
            Click here to re-send the verification email.
          </RouterLink>
        </p>

        <div
          v-show="status === 'verification-link-sent'"
          class="mt-2 font-medium text-sm text-green-600"
        >
          A new verification link has been sent to your email address.
        </div>
      </div>

      <div class="flex items-center gap-4">
        <Button :disabled="form.processing" :loading="form.processing">Save</Button>

        <Transition
          enter-active-class="transition ease-in-out"
          enter-from-class="opacity-0"
          leave-active-class="transition ease-in-out"
          leave-to-class="opacity-0"
        >
          <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">
            Saved.
          </p>
        </Transition>
      </div>
    </form>
  </section>
</template>
