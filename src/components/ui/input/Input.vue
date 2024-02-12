<script setup lang="ts">
import { ref, onMounted, type HTMLAttributes } from "vue";
import { useVModel } from "@vueuse/core";
import { cn } from "@/lib/utils";

const props = defineProps<{
  defaultValue?: string | number
  modelValue?: string | number
  class?: HTMLAttributes["class"]
  error?: string
}>();

const emits = defineEmits<{
  (e: "update:modelValue", payload: string | number): void;
}>();

const modelValue = useVModel(props, "modelValue", emits, {
  passive: true,
  defaultValue: props.defaultValue,
});

const inputRef = ref<HTMLInputElement | undefined>()

onMounted(() => {
  if (inputRef.value?.hasAttribute('autofocus')) {
    inputRef.value?.focus();
  }
});

defineExpose({ focus: () => inputRef.value?.focus() });
</script>

<template>
  <input
    ref="inputRef"
    v-model="modelValue"
    :class="
      cn(
        'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
        { 'ring-1 ring-red-600' : error },
        props.class,
      )
    "
  />
</template>
