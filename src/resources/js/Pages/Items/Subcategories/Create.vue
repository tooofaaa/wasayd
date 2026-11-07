<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PageHeader from '@/Components/shared/PageHeader.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/Components/ui/card'
import { useForm, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const { t, locale } = useI18n()

const props = defineProps<{
  categories: any[]
}>()

const form = useForm({
  name: '',
  name_ar: '',
  code: '',
  category_id: null as number | null,
})

function submit() {
  form.post(route('items.subcategories.store'))
}
</script>

<template>
  <AuthenticatedLayout>
    <div class="space-y-6">
      <PageHeader :title="t('items.subcategories.createSubcategory')" />

      <form @submit.prevent="submit">
        <Card>
          <CardHeader>
            <CardTitle>{{ t('items.subcategories.subcategoryDetails') }}</CardTitle>
          </CardHeader>
          <CardContent class="grid gap-4 sm:grid-cols-2">
            <div class="space-y-2">
              <Label for="name">{{ t('items.nameEn') }} *</Label>
              <Input id="name" v-model="form.name" :class="{ 'border-destructive': form.errors.name }" />
              <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
            </div>
            <div class="space-y-2">
              <Label for="name_ar">{{ t('items.nameAr') }} *</Label>
              <Input id="name_ar" v-model="form.name_ar" dir="rtl" :class="{ 'border-destructive': form.errors.name_ar }" />
              <p v-if="form.errors.name_ar" class="text-sm text-destructive">{{ form.errors.name_ar }}</p>
            </div>
            <div class="space-y-2">
              <Label for="code">{{ t('locations.code') }} *</Label>
              <Input id="code" v-model="form.code" class="font-mono" :class="{ 'border-destructive': form.errors.code }" />
              <p v-if="form.errors.code" class="text-sm text-destructive">{{ form.errors.code }}</p>
            </div>
            <div class="space-y-2">
              <Label for="category_id">{{ t('items.category') }} *</Label>
              <Select v-model="form.category_id">
                <SelectTrigger :class="{ 'border-destructive': form.errors.category_id }">
                  <SelectValue :placeholder="t('items.selectCategory')" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="cat in categories" :key="cat.id" :value="cat.id">
                    {{ locale === 'ar' ? (cat.name_ar || cat.name) : cat.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
              <p v-if="form.errors.category_id" class="text-sm text-destructive">{{ form.errors.category_id }}</p>
            </div>
          </CardContent>
          <CardFooter class="flex justify-end gap-2">
            <Link :href="route('items.subcategories.index')">
              <Button variant="outline" type="button">{{ t('common.cancel') }}</Button>
            </Link>
            <Button type="submit" :disabled="form.processing">{{ t('common.save') }}</Button>
          </CardFooter>
        </Card>
      </form>
    </div>
  </AuthenticatedLayout>
</template>
