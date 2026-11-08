<script setup lang="ts">
import { X } from 'lucide-vue-next';
import DialogContent from './DialogContent.vue';
import DialogDescription from './DialogDescription.vue';
import DialogFooter from './DialogFooter.vue';
import DialogHeader from './DialogHeader.vue';
import DialogTitle from './DialogTitle.vue';
import { cn } from '@/lib/utils';

const props = defineProps<{
  open: boolean;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
}>();
</script>

<template>
  <DialogContent v-bind="$attrs" v-if="open" @close="emit('close')">
    <DialogHeader>
      <DialogTitle>{{ $props.title }}</DialogTitle>
      <DialogDescription>{{ $props.description }}</DialogDescription>
    </DialogHeader>
    <DialogFooter>
      <button @click="emit('close')" class="ml-2 text-sm text-muted-foreground">Cancel</button>
      <button @click="$emit('confirm')" class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">Delete</button>
    </DialogFooter>
  </DialogContent>
</template>