<script setup lang="ts">
import { Input } from '@/components/ui/input'

const model = defineModel<string | undefined>({ required: true });

defineProps<{
  error?: string
}>()
</script>

<template>
  <Input
    v-model="model"
    :error="error"
  />
</template>
