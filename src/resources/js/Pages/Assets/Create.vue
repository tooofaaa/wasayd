<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PageHeader from '@/Components/shared/PageHeader.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Textarea } from '@/Components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/Components/ui/card'
import { useForm, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const { t, locale } = useI18n()

const props = defineProps<{
  items: any[]
  warehouses: any[]
}>()

const form = useForm({
  item_id: null as number | null,
  serial_number: '',
  name: '',
  name_ar: '',
  category: '',
  current_warehouse_id: null as number | null,
  current_department: '',
  purchase_date: '',
  purchase_price: '',
  warranty_start: '',
  warranty_end: '',
  expected_life_years: '',
  notes: '',
})

function submit() {
  form.post(route('assets.store'))
}
</script>

<template>
  <AuthenticatedLayout>
    <div class="space-y-6">
      <PageHeader :title="t('Create Asset')" />

      <form @submit.prevent="submit">
        <Card>
          <CardHeader>
            <CardTitle>{{ t('Asset Details') }}</CardTitle>
          </CardHeader>
          <CardContent class="grid gap-4 sm:grid-cols-2">
            <div class="space-y-2">
              <Label for="item_id">{{ t('Item') }} *</Label>
              <Select v-model="form.item_id">
                <SelectTrigger :class="{ 'border-destructive': form.errors.item_id }">
                  <SelectValue :placeholder="t('Select Item')" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="item in items" :key="item.id" :value="item.id">
                    {{ locale === 'ar' ? (item.name_ar || item.name) : item.name }} ({{ item.sku }})
                  </SelectItem>
                </SelectContent>
              </Select>
              <p v-if="form.errors.item_id" class="text-sm text-destructive">{{ form.errors.item_id }}</p>
            </div>

            <div class="space-y-2">
              <Label for="serial_number">{{ t('Serial Number') }}</Label>
              <Input id="serial_number" v-model="form.serial_number" class="font-mono" :class="{ 'border-destructive': form.errors.serial_number }" />
              <p v-if="form.errors.serial_number" class="text-sm text-destructive">{{ form.errors.serial_number }}</p>
            </div>

            <div class="space-y-2">
              <Label for="name">{{ t('Asset Name (EN)') }} *</Label>
              <Input id="name" v-model="form.name" :class="{ 'border-destructive': form.errors.name }" />
              <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
            </div>

            <div class="space-y-2">
              <Label for="name_ar">{{ t('Asset Name (AR)') }}</Label>
              <Input id="name_ar" v-model="form.name_ar" dir="rtl" :class="{ 'border-destructive': form.errors.name_ar }" />
              <p v-if="form.errors.name_ar" class="text-sm text-destructive">{{ form.errors.name_ar }}</p>
            </div>

            <div class="space-y-2">
              <Label for="category">{{ t('Category') }}</Label>
              <Input id="category" v-model="form.category" :class="{ 'border-destructive': form.errors.category }" />
              <p v-if="form.errors.category" class="text-sm text-destructive">{{ form.errors.category }}</p>
            </div>

            <div class="space-y-2">
              <Label for="current_warehouse_id">{{ t('Warehouse') }}</Label>
              <Select v-model="form.current_warehouse_id">
                <SelectTrigger :class="{ 'border-destructive': form.errors.current_warehouse_id }">
                  <SelectValue :placeholder="t('Select Warehouse')" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
                    {{ locale === 'ar' ? (warehouse.name_ar || warehouse.name) : warehouse.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
              <p v-if="form.errors.current_warehouse_id" class="text-sm text-destructive">{{ form.errors.current_warehouse_id }}</p>
            </div>

            <div class="space-y-2">
              <Label for="current_department">{{ t('Department') }}</Label>
              <Input id="current_department" v-model="form.current_department" :class="{ 'border-destructive': form.errors.current_department }" />
              <p v-if="form.errors.current_department" class="text-sm text-destructive">{{ form.errors.current_department }}</p>
            </div>

            <div class="space-y-2">
              <Label for="purchase_date">{{ t('Purchase Date') }}</Label>
              <Input id="purchase_date" v-model="form.purchase_date" type="date" :class="{ 'border-destructive': form.errors.purchase_date }" />
              <p v-if="form.errors.purchase_date" class="text-sm text-destructive">{{ form.errors.purchase_date }}</p>
            </div>

            <div class="space-y-2">
              <Label for="purchase_price">{{ t('Purchase Price') }}</Label>
              <Input id="purchase_price" v-model="form.purchase_price" type="number" step="0.01" :class="{ 'border-destructive': form.errors.purchase_price }" />
              <p v-if="form.errors.purchase_price" class="text-sm text-destructive">{{ form.errors.purchase_price }}</p>
            </div>

            <div class="space-y-2">
              <Label for="warranty_start">{{ t('Warranty Start') }}</Label>
              <Input id="warranty_start" v-model="form.warranty_start" type="date" :class="{ 'border-destructive': form.errors.warranty_start }" />
              <p v-if="form.errors.warranty_start" class="text-sm text-destructive">{{ form.errors.warranty_start }}</p>
            </div>

            <div class="space-y-2">
              <Label for="warranty_end">{{ t('Warranty End') }}</Label>
              <Input id="warranty_end" v-model="form.warranty_end" type="date" :class="{ 'border-destructive': form.errors.warranty_end }" />
              <p v-if="form.errors.warranty_end" class="text-sm text-destructive">{{ form.errors.warranty_end }}</p>
            </div>

            <div class="space-y-2">
              <Label for="expected_life_years">{{ t('Expected Life (Years)') }}</Label>
              <Input id="expected_life_years" v-model="form.expected_life_years" type="number" :class="{ 'border-destructive': form.errors.expected_life_years }" />
              <p v-if="form.errors.expected_life_years" class="text-sm text-destructive">{{ form.errors.expected_life_years }}</p>
            </div>

            <div class="space-y-2 sm:col-span-2">
              <Label for="notes">{{ t('Notes') }}</Label>
              <Textarea id="notes" v-model="form.notes" rows="3" :class="{ 'border-destructive': form.errors.notes }" />
              <p v-if="form.errors.notes" class="text-sm text-destructive">{{ form.errors.notes }}</p>
            </div>
          </CardContent>
          <CardFooter class="flex justify-end gap-2">
            <Link :href="route('assets.index')">
              <Button variant="outline" type="button">{{ t('common.cancel') }}</Button>
            </Link>
            <Button type="submit" :disabled="form.processing">{{ t('common.save') }}</Button>
          </CardFooter>
        </Card>
      </form>
    </div>
  </AuthenticatedLayout>
</template>
