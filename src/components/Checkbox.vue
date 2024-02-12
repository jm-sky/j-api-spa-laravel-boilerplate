<script setup lang="ts">
import { Checkbox } from '@/components/ui/checkbox'

const modelValue = defineModel('modelValue')
</script>

<template>
  <Checkbox v-model="modelValue" />
</template>
