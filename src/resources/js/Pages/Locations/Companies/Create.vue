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

const form = useForm({
  name: '',
  name_ar: '',
  code: '',
  address: '',
  phone: '',
  email: '',
  vat_number: '',
  cr_number: '',
  is_active: true,
})

function submit() {
  form.post(route('locations.companies.store'))
}
</script>

<template>
  <AuthenticatedLayout>
    <div class="space-y-6 p-6">
      <PageHeader :title="t('locations.addCompany')" />

      <form @submit.prevent="submit">
        <Card>
          <CardHeader>
            <CardTitle>{{ t('locations.companyDetails') }}</CardTitle>
          </CardHeader>
          <CardContent class="grid gap-4 sm:grid-cols-2">
            <div class="space-y-2">
              <Label for="name">{{ t('locations.nameEn') }} *</Label>
              <Input id="name" v-model="form.name" :class="{ 'border-destructive': form.errors.name }" />
              <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
            </div>
            <div class="space-y-2">
              <Label for="name_ar">{{ t('locations.nameAr') }} *</Label>
              <Input id="name_ar" v-model="form.name_ar" dir="rtl" :class="{ 'border-destructive': form.errors.name_ar }" />
              <p v-if="form.errors.name_ar" class="text-sm text-destructive">{{ form.errors.name_ar }}</p>
            </div>
            <div class="space-y-2">
              <Label for="code">{{ t('locations.code') }} *</Label>
              <Input id="code" v-model="form.code" class="font-mono" :class="{ 'border-destructive': form.errors.code }" />
              <p v-if="form.errors.code" class="text-sm text-destructive">{{ form.errors.code }}</p>
            </div>
            <div class="space-y-2">
              <Label for="email">{{ t('common.email') }}</Label>
              <Input id="email" v-model="form.email" type="email" />
              <p v-if="form.errors.email" class="text-sm text-destructive">{{ form.errors.email }}</p>
            </div>
            <div class="space-y-2">
              <Label for="phone">{{ t('common.phone') }}</Label>
              <Input id="phone" v-model="form.phone" />
              <p v-if="form.errors.phone" class="text-sm text-destructive">{{ form.errors.phone }}</p>
            </div>
            <div class="space-y-2">
              <Label for="vat_number">{{ t('locations.vatNumber') }}</Label>
              <Input id="vat_number" v-model="form.vat_number" />
              <p v-if="form.errors.vat_number" class="text-sm text-destructive">{{ form.errors.vat_number }}</p>
            </div>
            <div class="space-y-2">
              <Label for="cr_number">{{ t('locations.crNumber') }}</Label>
              <Input id="cr_number" v-model="form.cr_number" />
              <p v-if="form.errors.cr_number" class="text-sm text-destructive">{{ form.errors.cr_number }}</p>
            </div>
            <div class="space-y-2 sm:col-span-2">
              <Label for="address">{{ t('locations.address') }}</Label>
              <Input id="address" v-model="form.address" />
              <p v-if="form.errors.address" class="text-sm text-destructive">{{ form.errors.address }}</p>
            </div>
            <div class="flex items-center gap-2">
              <Switch id="is_active" v-model:checked="form.is_active" />
              <Label for="is_active">{{ t('common.active') }}</Label>
            </div>
          </CardContent>
          <CardFooter class="flex justify-end gap-2">
            <Link :href="route('locations.companies.index')">
              <Button variant="outline" type="button">{{ t('common.cancel') }}</Button>
            </Link>
            <Button type="submit" :disabled="form.processing">{{ t('common.save') }}</Button>
          </CardFooter>
        </Card>
      </form>
    </div>
  </AuthenticatedLayout>
</template>
