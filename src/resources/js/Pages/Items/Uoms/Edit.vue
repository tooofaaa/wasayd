<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PageHeader from '@/Components/shared/PageHeader.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Switch } from '@/Components/ui/switch'
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/Components/ui/card'
import { useForm, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps<{
  uom: any
}>()

const form = useForm({
  name: props.uom.name,
  name_ar: props.uom.name_ar || '',
  code: props.uom.code,
  symbol: props.uom.symbol || '',
  is_base: props.uom.is_base,
  is_active: props.uom.is_active,
})

function submit() {
  form.put(route('items.uoms.update', props.uom.id))
}
</script>

<template>
  <AuthenticatedLayout>
    <div class="space-y-6">
      <PageHeader :title="t('items.uoms.editUom')" />

      <form @submit.prevent="submit">
        <Card>
          <CardHeader>
            <CardTitle>{{ t('items.uoms.uomDetails') }}</CardTitle>
          </CardHeader>
          <CardContent class="grid gap-4 sm:grid-cols-2">
            <div class="space-y-2">
              <Label for="name">{{ t('items.nameEn') }} *</Label>
              <Input id="name" v-model="form.name" :class="{ 'border-destructive': form.errors.name }" />
              <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
            </div>
            <div class="space-y-2">
              <Label for="name_ar">{{ t('items.nameAr') }}</Label>
              <Input id="name_ar" v-model="form.name_ar" dir="rtl" :class="{ 'border-destructive': form.errors.name_ar }" />
              <p v-if="form.errors.name_ar" class="text-sm text-destructive">{{ form.errors.name_ar }}</p>
            </div>
            <div class="space-y-2">
              <Label for="code">{{ t('items.code') }} *</Label>
              <Input id="code" v-model="form.code" class="font-mono" :class="{ 'border-destructive': form.errors.code }" />
              <p v-if="form.errors.code" class="text-sm text-destructive">{{ form.errors.code }}</p>
            </div>
            <div class="space-y-2">
              <Label for="symbol">{{ t('items.symbol') }}</Label>
              <Input id="symbol" v-model="form.symbol" :class="{ 'border-destructive': form.errors.symbol }" />
              <p v-if="form.errors.symbol" class="text-sm text-destructive">{{ form.errors.symbol }}</p>
            </div>
            <div class="flex items-center gap-3 pt-6">
              <Switch id="is_base" v-model:checked="form.is_base" />
              <Label for="is_base">{{ t('items.isBase') }}</Label>
            </div>
            <div class="flex items-center gap-3 pt-6">
              <Switch id="is_active" v-model:checked="form.is_active" />
              <Label for="is_active">{{ t('common.active') }}</Label>
            </div>
          </CardContent>
          <CardFooter class="flex justify-end gap-2">
            <Link :href="route('items.uoms.index')">
              <Button variant="outline" type="button">{{ t('common.cancel') }}</Button>
            </Link>
            <Button type="submit" :disabled="form.processing">{{ t('common.save') }}</Button>
          </CardFooter>
        </Card>
      </form>
    </div>
  </AuthenticatedLayout>
</template>
