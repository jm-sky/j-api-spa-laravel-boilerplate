<script setup lang="ts">
import { computed } from 'vue'

const props = withDefaults(
  defineProps<{
    type?: string
    icon: string
    loading?: boolean
    loadingIcon?: string
    fw?: boolean
    ml?: boolean
    mr?: boolean
  }>(),
  {
    type: 'solid',
    loading: false,
    loadingIcon: 'sync',
    fw: false,
    ml: false,
    mr: false,
  },
)

const iconComp = computed(() => {
  if (props.loading) return `fa-${props.loadingIcon} fa-spin`
  if (`${props.icon}`.startsWith('fa-')) return props.icon

  return `fa-${props.icon}`
})

const fixedWidthComp = computed(() => (props.fw ? 'fa-fw' : ''))

const typeComp = computed(() => {
  if (props.icon?.includes('fa-brands')) return ''
  if (props.icon?.includes('fa-thin')) return ''
  if (props.icon?.includes('fa-regular')) return ''
  if (props.icon?.includes('fa-solid')) return ''
  if (props.type?.startsWith('fa-')) return props.type

  return `fa-${props.type}`
})
</script>

<template>
  <i :class="[typeComp, iconComp, fixedWidthComp, { 'mr-2': mr }, { 'ml-2': ml }]" />
</template>
