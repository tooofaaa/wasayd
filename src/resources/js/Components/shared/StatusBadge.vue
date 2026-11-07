<script setup lang="ts">
import { Badge } from '@/Components/ui/badge'
import { computed } from 'vue'

const props = defineProps<{
  status: boolean | string
  activeLabel?: string
  inactiveLabel?: string
}>()

const variant = computed(() => {
  if (typeof props.status === 'boolean') {
    return props.status ? 'default' : 'secondary'
  }
  const map: Record<string, string> = {
    active: 'default',
    inactive: 'secondary',
    pending: 'outline',
    approved: 'default',
    rejected: 'destructive',
  }
  return (map[props.status] as any) ?? 'outline'
})

const label = computed(() => {
  if (typeof props.status === 'boolean') {
    return props.status
      ? (props.activeLabel ?? 'Active')
      : (props.inactiveLabel ?? 'Inactive')
  }
  return props.status
})
</script>

<template>
  <Badge :variant="variant" class="capitalize">{{ label }}</Badge>
</template>
