<script setup lang="ts">
import { onMounted, ref } from 'vue';

const model = defineModel<string>({ required: true });

const input = ref<HTMLInputElement | null>(null);

defineProps<{
  error?: string
}>()

onMounted(() => {
    if (input.value?.hasAttribute('autofocus')) {
        input.value?.focus();
    }
});

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <input
        class="border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm"
        :class="{ 'ring-1 ring-red-600' : error }"
        v-model="model"
        ref="input"
    />
</template>
