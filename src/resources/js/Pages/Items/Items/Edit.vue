<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PageHeader from '@/Components/shared/PageHeader.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Textarea } from '@/Components/ui/textarea'
import { Switch } from '@/Components/ui/switch'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs'
import { Alert, AlertDescription } from '@/Components/ui/alert'
import { useForm, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { AlertTriangle, Info } from 'lucide-vue-next'
import { watch, computed } from 'vue'

const { t, locale } = useI18n()

const props = defineProps<{
  item: any
  categories: any[]
  subcategories: any[]
  uoms: any[]
}>()

const form = useForm({
  sku: props.item.sku,
  name: props.item.name,
  name_ar: props.item.name_ar,
  description: props.item.description || '',
  description_ar: props.item.description_ar || '',
  item_type: props.item.item_type,
  category_id: props.item.category_id,
  subcategory_id: props.item.subcategory_id,
  status: props.item.status,
  base_uom_id: props.item.base_uom_id,
  secondary_uom_id: props.item.secondary_uom_id,
  uom_conversion_factor: props.item.uom_conversion_factor,
  min_stock: props.item.min_stock,
  max_stock: props.item.max_stock,
  reorder_point: props.item.reorder_point,
  reorder_qty: props.item.reorder_qty,
  requires_batch: props.item.requires_batch,
  requires_expiry: props.item.requires_expiry,
  requires_serial: props.item.requires_serial,
  requires_rfid: props.item.requires_rfid,
  shelf_life_days: props.item.shelf_life_days,
  storage_conditions: props.item.storage_conditions || '',
  manufacturer: props.item.manufacturer || '',
  manufacturer_part_number: props.item.manufacturer_part_number || '',
  brand: props.item.brand || '',
  model: props.item.model_number || '',
  gtin: props.item.gtin || '',
  udi: props.item.udi || '',
  hs_code: props.item.hs_code || '',
  expected_life_years: props.item.expected_life_years,
  temperature_min: props.item.temperature_min,
  temperature_max: props.item.temperature_max,
  weight_kg: props.item.weight_kg,
  volume_m3: props.item.volume_m3,
})

const filteredSubcategories = computed(() => {
  if (!form.category_id) return []
  return props.subcategories.filter((sub: any) => sub.category_id === form.category_id)
})

const isNarcotic = computed(() => form.item_type === 'narcotic_controlled')
const isMedicine = computed(() => form.item_type === 'medicine')
const isDevice = computed(() => form.item_type === 'medical_device')
const isEquipment = computed(() => form.item_type === 'medical_equipment')

watch(() => form.category_id, () => {
  if (form.subcategory_id) {
    const subExists = filteredSubcategories.value.some((sub: any) => sub.id === form.subcategory_id)
    if (!subExists) {
      form.subcategory_id = null
    }
  }
})

function submit() {
  form.put(route('items.items.update', props.item.id))
}
</script>

<template>
  <AuthenticatedLayout>
    <div class="space-y-6">
      <PageHeader :title="t('items.editItem')" />

      <Alert>
        <Info class="h-4 w-4" />
        <AlertDescription>{{ t('items.typeCannotChange') }}</AlertDescription>
      </Alert>

      <form @submit.prevent="submit">
        <Tabs default-value="basic" class="space-y-4">
          <TabsList class="grid w-full grid-cols-5">
            <TabsTrigger value="basic">{{ t('items.basicInfo') }}</TabsTrigger>
            <TabsTrigger value="units">{{ t('items.unitsStock') }}</TabsTrigger>
            <TabsTrigger value="type-specific">{{ t('items.typeSpecific') }}</TabsTrigger>
            <TabsTrigger value="identification">{{ t('items.identification') }}</TabsTrigger>
            <TabsTrigger value="storage">{{ t('items.storage') }}</TabsTrigger>
          </TabsList>

          <!-- Basic Info Tab -->
          <TabsContent value="basic">
            <Card>
              <CardHeader>
                <CardTitle>{{ t('items.basicInfo') }}</CardTitle>
                <CardDescription>Essential item information and classification</CardDescription>
              </CardHeader>
              <CardContent class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-2">
                  <Label for="sku">{{ t('items.itemCode') }} *</Label>
                  <Input id="sku" v-model="form.sku" class="font-mono" :class="{ 'border-destructive': form.errors.sku }" />
                  <p v-if="form.errors.sku" class="text-sm text-destructive">{{ form.errors.sku }}</p>
                </div>
                <div class="space-y-2">
                  <Label for="item_type">{{ t('items.itemType') }} *</Label>
                  <Select v-model="form.item_type" disabled>
                    <SelectTrigger class="bg-muted">
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="medical_consumable">{{ t('items.types.medical_consumable') }}</SelectItem>
                      <SelectItem value="medicine">{{ t('items.types.medicine') }}</SelectItem>
                      <SelectItem value="medical_device">{{ t('items.types.medical_device') }}</SelectItem>
                      <SelectItem value="medical_equipment">{{ t('items.types.medical_equipment') }}</SelectItem>
                      <SelectItem value="narcotic_controlled">{{ t('items.types.narcotic_controlled') }}</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
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
                  <Label for="category_id">{{ t('items.category') }}</Label>
                  <Select v-model="form.category_id">
                    <SelectTrigger>
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
                <div class="space-y-2">
                  <Label for="subcategory_id">{{ t('items.subcategory') }}</Label>
                  <Select v-model="form.subcategory_id" :disabled="!form.category_id">
                    <SelectTrigger>
                      <SelectValue :placeholder="t('items.selectSubcategory')" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem :value="null">{{ t('common.none') }}</SelectItem>
                      <SelectItem v-for="sub in filteredSubcategories" :key="sub.id" :value="sub.id">
                        {{ locale === 'ar' ? (sub.name_ar || sub.name) : sub.name }}
                      </SelectItem>
                    </SelectContent>
                  </Select>
                  <p v-if="form.errors.subcategory_id" class="text-sm text-destructive">{{ form.errors.subcategory_id }}</p>
                </div>
                <div class="space-y-2">
                  <Label for="status">{{ t('items.status') }}</Label>
                  <Select v-model="form.status">
                    <SelectTrigger>
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="draft">{{ t('items.statuses.draft') }}</SelectItem>
                      <SelectItem value="active">{{ t('items.statuses.active') }}</SelectItem>
                      <SelectItem value="discontinued">{{ t('items.statuses.discontinued') }}</SelectItem>
                      <SelectItem value="recalled">{{ t('items.statuses.recalled') }}</SelectItem>
                    </SelectContent>
                  </Select>
                  <p v-if="form.errors.status" class="text-sm text-destructive">{{ form.errors.status }}</p>
                </div>
                <div class="space-y-2 sm:col-span-2">
                  <Label for="description">{{ t('items.descriptionEn') }}</Label>
                  <Textarea id="description" v-model="form.description" rows="2" />
                  <p v-if="form.errors.description" class="text-sm text-destructive">{{ form.errors.description }}</p>
                </div>
                <div class="space-y-2 sm:col-span-2">
                  <Label for="description_ar">{{ t('items.descriptionAr') }}</Label>
                  <Textarea id="description_ar" v-model="form.description_ar" dir="rtl" rows="2" />
                  <p v-if="form.errors.description_ar" class="text-sm text-destructive">{{ form.errors.description_ar }}</p>
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          <!-- Units & Stock Tab -->
          <TabsContent value="units">
            <Card>
              <CardHeader>
                <CardTitle>{{ t('items.unitsStock') }}</CardTitle>
                <CardDescription>Units of measure and stock level settings</CardDescription>
              </CardHeader>
              <CardContent class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-2">
                  <Label for="base_uom_id">{{ t('items.primaryUom') }} *</Label>
                  <Select v-model="form.base_uom_id">
                    <SelectTrigger :class="{ 'border-destructive': form.errors.base_uom_id }">
                      <SelectValue :placeholder="t('items.selectUom')" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem v-for="uom in uoms" :key="uom.id" :value="uom.id">
                        {{ locale === 'ar' ? (uom.name_ar || uom.name) : uom.name }}
                      </SelectItem>
                    </SelectContent>
                  </Select>
                  <p v-if="form.errors.base_uom_id" class="text-sm text-destructive">{{ form.errors.base_uom_id }}</p>
                </div>
                <div class="space-y-2">
                  <Label for="secondary_uom_id">{{ t('items.secondaryUom') }}</Label>
                  <Select v-model="form.secondary_uom_id">
                    <SelectTrigger>
                      <SelectValue :placeholder="t('items.selectUom')" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem :value="null">{{ t('common.none') }}</SelectItem>
                      <SelectItem v-for="uom in uoms" :key="uom.id" :value="uom.id">
                        {{ locale === 'ar' ? (uom.name_ar || uom.name) : uom.name }}
                      </SelectItem>
                    </SelectContent>
                  </Select>
                  <p v-if="form.errors.secondary_uom_id" class="text-sm text-destructive">{{ form.errors.secondary_uom_id }}</p>
                </div>
                <div class="space-y-2">
                  <Label for="uom_conversion_factor">{{ t('items.uomConversionFactor') }}</Label>
                  <Input
                    id="uom_conversion_factor"
                    v-model="form.uom_conversion_factor"
                    type="number"
                    step="0.0001"
                    :disabled="!form.secondary_uom_id"
                  />
                  <p v-if="form.errors.uom_conversion_factor" class="text-sm text-destructive">{{ form.errors.uom_conversion_factor }}</p>
                </div>
                <div class="space-y-2">
                  <Label for="min_stock">{{ t('items.minStockLevel') }}</Label>
                  <Input id="min_stock" v-model="form.min_stock" type="number" />
                  <p v-if="form.errors.min_stock" class="text-sm text-destructive">{{ form.errors.min_stock }}</p>
                </div>
                <div class="space-y-2">
                  <Label for="max_stock">{{ t('items.maxStockLevel') }}</Label>
                  <Input id="max_stock" v-model="form.max_stock" type="number" />
                  <p v-if="form.errors.max_stock" class="text-sm text-destructive">{{ form.errors.max_stock }}</p>
                </div>
                <div class="space-y-2">
                  <Label for="reorder_point">{{ t('items.reorderPoint') }}</Label>
                  <Input id="reorder_point" v-model="form.reorder_point" type="number" />
                  <p v-if="form.errors.reorder_point" class="text-sm text-destructive">{{ form.errors.reorder_point }}</p>
                </div>
                <div class="space-y-2">
                  <Label for="reorder_qty">{{ t('items.reorderQuantity') }}</Label>
                  <Input id="reorder_qty" v-model="form.reorder_qty" type="number" />
                  <p v-if="form.errors.reorder_qty" class="text-sm text-destructive">{{ form.errors.reorder_qty }}</p>
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          <!-- Type-Specific Tab -->
          <TabsContent value="type-specific">
            <Card>
              <CardHeader>
                <CardTitle>{{ t('items.typeSpecific') }}</CardTitle>
                <CardDescription>Type-specific tracking and control settings</CardDescription>
              </CardHeader>
              <CardContent class="space-y-6">
                <Alert v-if="isNarcotic" variant="destructive">
                  <AlertTriangle class="h-4 w-4" />
                  <AlertDescription>{{ t('items.narcoticWarning') }}</AlertDescription>
                </Alert>

                <div class="grid gap-4 sm:grid-cols-2">
                  <div class="flex items-center gap-2">
                    <Switch id="requires_batch" v-model:checked="form.requires_batch" :disabled="isNarcotic || isMedicine" />
                    <Label for="requires_batch">{{ t('items.batchControlled') }}</Label>
                  </div>
                  <div class="flex items-center gap-2">
                    <Switch id="requires_expiry" v-model:checked="form.requires_expiry" :disabled="isNarcotic || isMedicine" />
                    <Label for="requires_expiry">{{ t('items.expiryControlled') }}</Label>
                  </div>
                  <div class="flex items-center gap-2">
                    <Switch id="requires_serial" v-model:checked="form.requires_serial" :disabled="isNarcotic || isDevice || isEquipment" />
                    <Label for="requires_serial">{{ t('items.serialControlled') }}</Label>
                  </div>
                  <div class="flex items-center gap-2">
                    <Switch id="requires_rfid" v-model:checked="form.requires_rfid" :disabled="isNarcotic" />
                    <Label for="requires_rfid">{{ t('items.rfidEnabled') }}</Label>
                  </div>
                </div>

                <div v-if="isMedicine || isNarcotic" class="grid gap-4 sm:grid-cols-2 pt-4 border-t">
                  <div class="space-y-2">
                    <Label for="shelf_life_days">{{ t('items.shelfLifeDays') }} {{ isMedicine ? '*' : '' }}</Label>
                    <Input
                      id="shelf_life_days"
                      v-model="form.shelf_life_days"
                      type="number"
                      :class="{ 'border-destructive': form.errors.shelf_life_days }"
                    />
                    <p v-if="form.errors.shelf_life_days" class="text-sm text-destructive">{{ form.errors.shelf_life_days }}</p>
                  </div>
                </div>

                <div v-if="isEquipment" class="grid gap-4 sm:grid-cols-2 pt-4 border-t">
                  <div class="space-y-2">
                    <Label for="expected_life_years">{{ t('items.expectedLifeYears') }}</Label>
                    <Input id="expected_life_years" v-model="form.expected_life_years" type="number" />
                    <p v-if="form.errors.expected_life_years" class="text-sm text-destructive">{{ form.errors.expected_life_years }}</p>
                  </div>
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          <!-- Identification Tab -->
          <TabsContent value="identification">
            <Card>
              <CardHeader>
                <CardTitle>{{ t('items.identification') }}</CardTitle>
                <CardDescription>Manufacturer details and identification codes</CardDescription>
              </CardHeader>
              <CardContent class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-2">
                  <Label for="manufacturer">{{ t('items.manufacturer') }}</Label>
                  <Input id="manufacturer" v-model="form.manufacturer" />
                  <p v-if="form.errors.manufacturer" class="text-sm text-destructive">{{ form.errors.manufacturer }}</p>
                </div>
                <div class="space-y-2">
                  <Label for="manufacturer_part_number">{{ t('items.manufacturerPartNumber') }}</Label>
                  <Input id="manufacturer_part_number" v-model="form.manufacturer_part_number" class="font-mono" />
                  <p v-if="form.errors.manufacturer_part_number" class="text-sm text-destructive">{{ form.errors.manufacturer_part_number }}</p>
                </div>
                <div class="space-y-2">
                  <Label for="brand">{{ t('items.brand') }}</Label>
                  <Input id="brand" v-model="form.brand" />
                  <p v-if="form.errors.brand" class="text-sm text-destructive">{{ form.errors.brand }}</p>
                </div>
                <div class="space-y-2">
                  <Label for="model">{{ t('items.model') }}</Label>
                  <Input id="model_number" v-model="form.model_number" />
                  <p v-if="form.errors.model_number" class="text-sm text-destructive">{{ form.errors.model_number }}</p>
                </div>
                <div class="space-y-2">
                  <Label for="gtin">{{ t('items.gtin') }}</Label>
                  <Input id="gtin" v-model="form.gtin" class="font-mono" />
                  <p v-if="form.errors.gtin" class="text-sm text-destructive">{{ form.errors.gtin }}</p>
                </div>
                <div class="space-y-2">
                  <Label for="udi">{{ t('items.udi') }}</Label>
                  <Input id="udi" v-model="form.udi" class="font-mono" />
                  <p v-if="form.errors.udi" class="text-sm text-destructive">{{ form.errors.udi }}</p>
                </div>
                <div class="space-y-2">
                  <Label for="hs_code">{{ t('items.hsCode') }}</Label>
                  <Input id="hs_code" v-model="form.hs_code" class="font-mono" />
                  <p v-if="form.errors.hs_code" class="text-sm text-destructive">{{ form.errors.hs_code }}</p>
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          <!-- Storage Tab -->
          <TabsContent value="storage">
            <Card>
              <CardHeader>
                <CardTitle>{{ t('items.storage') }}</CardTitle>
                <CardDescription>Storage requirements and physical dimensions</CardDescription>
              </CardHeader>
              <CardContent class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-2">
                  <Label for="temperature_min">{{ t('items.temperatureMin') }}</Label>
                  <Input id="temperature_min" v-model="form.temperature_min" type="number" step="0.1" />
                  <p v-if="form.errors.temperature_min" class="text-sm text-destructive">{{ form.errors.temperature_min }}</p>
                </div>
                <div class="space-y-2">
                  <Label for="temperature_max">{{ t('items.temperatureMax') }}</Label>
                  <Input id="temperature_max" v-model="form.temperature_max" type="number" step="0.1" />
                  <p v-if="form.errors.temperature_max" class="text-sm text-destructive">{{ form.errors.temperature_max }}</p>
                </div>
                <div class="space-y-2">
                  <Label for="weight_kg">{{ t('items.weightKg') }}</Label>
                  <Input id="weight_kg" v-model="form.weight_kg" type="number" step="0.001" />
                  <p v-if="form.errors.weight_kg" class="text-sm text-destructive">{{ form.errors.weight_kg }}</p>
                </div>
                <div class="space-y-2">
                  <Label for="volume_m3">{{ t('items.volumeM3') }}</Label>
                  <Input id="volume_m3" v-model="form.volume_m3" type="number" step="0.0001" />
                  <p v-if="form.errors.volume_m3" class="text-sm text-destructive">{{ form.errors.volume_m3 }}</p>
                </div>
                <div class="space-y-2 sm:col-span-2">
                  <Label for="storage_conditions">{{ t('items.storageConditions') }}</Label>
                  <Textarea id="storage_conditions" v-model="form.storage_conditions" rows="3" />
                  <p v-if="form.errors.storage_conditions" class="text-sm text-destructive">{{ form.errors.storage_conditions }}</p>
                </div>
              </CardContent>
            </Card>
          </TabsContent>
        </Tabs>

        <div class="flex justify-end gap-2 mt-6">
          <Link :href="route('items.items.index')">
            <Button variant="outline" type="button">{{ t('common.cancel') }}</Button>
          </Link>
          <Button type="submit" :disabled="form.processing">{{ t('common.save') }}</Button>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>
