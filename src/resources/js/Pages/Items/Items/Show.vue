<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PageHeader from '@/Components/shared/PageHeader.vue'
import { Button } from '@/Components/ui/button'
import { Badge } from '@/Components/ui/badge'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs'
import { Separator } from '@/Components/ui/separator'
import { Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { Pencil, Package, Barcode, Tag, FileText, History } from 'lucide-vue-next'

const { t, locale } = useI18n()

const props = defineProps<{
  item: any
}>()

const itemTypeColors: Record<string, string> = {
  medical_consumable: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
  medicine: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
  medical_device: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
  medical_equipment: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
  narcotic_controlled: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
}

const statusColors: Record<string, string> = {
  draft: 'secondary',
  active: 'default',
  discontinued: 'outline',
  recalled: 'destructive',
}

function getLocalizedName(obj: any) {
  if (!obj) return '-'
  return locale.value === 'ar' ? (obj.name_ar || obj.name) : obj.name
}

interface DetailItem {
  label: string
  value: string | number | null | undefined
  type?: 'text' | 'number' | 'boolean'
}

function formatDetailValue(item: DetailItem): string {
  if (item.value === null || item.value === undefined || item.value === '') {
    return '-'
  }
  if (item.type === 'boolean') {
    return item.value ? t('common.yes') : t('common.no')
  }
  return String(item.value)
}
</script>

<template>
  <AuthenticatedLayout>
    <div class="space-y-6">
      <PageHeader :title="getLocalizedName(item)" :description="item.sku">
        <template #actions>
          <Link :href="route('items.items.edit', item.id)">
            <Button>
              <Pencil class="me-2 h-4 w-4" />
              {{ t('common.edit') }}
            </Button>
          </Link>
        </template>
      </PageHeader>

      <div class="flex gap-2">
        <Badge :class="itemTypeColors[item.item_type]">
          {{ t(`items.types.${item.item_type}`) }}
        </Badge>
        <Badge :variant="statusColors[item.status]">
          {{ t(`items.statuses.${item.status}`) }}
        </Badge>
        <Badge v-if="item.requires_batch" variant="outline">{{ t('items.batchControlled') }}</Badge>
        <Badge v-if="item.requires_expiry" variant="outline">{{ t('items.expiryControlled') }}</Badge>
        <Badge v-if="item.requires_serial" variant="outline">{{ t('items.serialControlled') }}</Badge>
        <Badge v-if="item.requires_rfid" variant="outline">{{ t('items.rfidEnabled') }}</Badge>
      </div>

      <Tabs default-value="overview" class="space-y-4">
        <TabsList>
          <TabsTrigger value="overview">
            <Package class="me-2 h-4 w-4" />
            {{ t('items.overview') }}
          </TabsTrigger>
          <TabsTrigger value="stock">
            <Package class="me-2 h-4 w-4" />
            {{ t('items.stock') }}
          </TabsTrigger>
          <TabsTrigger value="barcodes">
            <Barcode class="me-2 h-4 w-4" />
            {{ t('items.barcodes') }}
          </TabsTrigger>
          <TabsTrigger value="attributes">
            <Tag class="me-2 h-4 w-4" />
            {{ t('items.attributes') }}
          </TabsTrigger>
          <TabsTrigger value="transactions">
            <FileText class="me-2 h-4 w-4" />
            {{ t('items.transactions') }}
          </TabsTrigger>
          <TabsTrigger value="audit">
            <History class="me-2 h-4 w-4" />
            {{ t('items.audit') }}
          </TabsTrigger>
        </TabsList>

        <!-- Overview Tab -->
        <TabsContent value="overview" class="space-y-4">
          <!-- Basic Information -->
          <Card>
            <CardHeader>
              <CardTitle>{{ t('items.basicInfo') }}</CardTitle>
            </CardHeader>
            <CardContent class="grid gap-6 sm:grid-cols-2">
              <div class="space-y-1">
                <p class="text-sm text-muted-foreground">{{ t('items.itemCode') }}</p>
                <p class="font-mono font-medium">{{ item.sku }}</p>
              </div>
              <div class="space-y-1">
                <p class="text-sm text-muted-foreground">{{ t('items.name') }}</p>
                <p class="font-medium">{{ getLocalizedName(item) }}</p>
              </div>
              <div class="space-y-1">
                <p class="text-sm text-muted-foreground">{{ t('items.category') }}</p>
                <p>{{ getLocalizedName(item.category) }}</p>
              </div>
              <div class="space-y-1">
                <p class="text-sm text-muted-foreground">{{ t('items.subcategory') }}</p>
                <p>{{ getLocalizedName(item.subcategory) }}</p>
              </div>
              <div v-if="item.description" class="space-y-1 sm:col-span-2">
                <p class="text-sm text-muted-foreground">{{ t('items.description') }}</p>
                <p>{{ locale === 'ar' ? (item.description_ar || item.description) : item.description }}</p>
              </div>
            </CardContent>
          </Card>

          <!-- Units & Stock Levels -->
          <Card>
            <CardHeader>
              <CardTitle>{{ t('items.unitsStock') }}</CardTitle>
            </CardHeader>
            <CardContent class="grid gap-6 sm:grid-cols-3">
              <div class="space-y-1">
                <p class="text-sm text-muted-foreground">{{ t('items.primaryUom') }}</p>
                <p class="font-medium">{{ getLocalizedName(item.base_uom) }}</p>
              </div>
              <div class="space-y-1">
                <p class="text-sm text-muted-foreground">{{ t('items.secondaryUom') }}</p>
                <p>{{ getLocalizedName(item.secondary_uom) }}</p>
              </div>
              <div class="space-y-1">
                <p class="text-sm text-muted-foreground">{{ t('items.uomConversionFactor') }}</p>
                <p class="font-mono">{{ item.uom_conversion_factor || '-' }}</p>
              </div>
              <div class="space-y-1">
                <p class="text-sm text-muted-foreground">{{ t('items.minStockLevel') }}</p>
                <p class="font-medium">{{ item.min_stock_level || '-' }}</p>
              </div>
              <div class="space-y-1">
                <p class="text-sm text-muted-foreground">{{ t('items.maxStockLevel') }}</p>
                <p class="font-medium">{{ item.max_stock_level || '-' }}</p>
              </div>
              <div class="space-y-1">
                <p class="text-sm text-muted-foreground">{{ t('items.reorderPoint') }}</p>
                <p class="font-medium">{{ item.reorder_point || '-' }}</p>
              </div>
            </CardContent>
          </Card>

          <!-- Type-Specific Details -->
          <Card v-if="item.shelf_life_days || item.expected_life_years">
            <CardHeader>
              <CardTitle>{{ t('items.typeSpecific') }}</CardTitle>
            </CardHeader>
            <CardContent class="grid gap-6 sm:grid-cols-2">
              <div v-if="item.shelf_life_days" class="space-y-1">
                <p class="text-sm text-muted-foreground">{{ t('items.shelfLifeDays') }}</p>
                <p class="font-medium">{{ item.shelf_life_days }} {{ t('common.days') }}</p>
              </div>
              <div v-if="item.expected_life_years" class="space-y-1">
                <p class="text-sm text-muted-foreground">{{ t('items.expectedLifeYears') }}</p>
                <p class="font-medium">{{ item.expected_life_years }} {{ t('common.years') }}</p>
              </div>
            </CardContent>
          </Card>

          <!-- Identification -->
          <Card v-if="item.manufacturer || item.gtin || item.udi">
            <CardHeader>
              <CardTitle>{{ t('items.identification') }}</CardTitle>
            </CardHeader>
            <CardContent class="grid gap-6 sm:grid-cols-2">
              <div v-if="item.manufacturer" class="space-y-1">
                <p class="text-sm text-muted-foreground">{{ t('items.manufacturer') }}</p>
                <p>{{ item.manufacturer }}</p>
              </div>
              <div v-if="item.manufacturer_part_number" class="space-y-1">
                <p class="text-sm text-muted-foreground">{{ t('items.manufacturerPartNumber') }}</p>
                <p class="font-mono">{{ item.manufacturer_part_number }}</p>
              </div>
              <div v-if="item.brand" class="space-y-1">
                <p class="text-sm text-muted-foreground">{{ t('items.brand') }}</p>
                <p>{{ item.brand }}</p>
              </div>
              <div v-if="item.model" class="space-y-1">
                <p class="text-sm text-muted-foreground">{{ t('items.model') }}</p>
                <p>{{ item.model }}</p>
              </div>
              <div v-if="item.gtin" class="space-y-1">
                <p class="text-sm text-muted-foreground">{{ t('items.gtin') }}</p>
                <p class="font-mono">{{ item.gtin }}</p>
              </div>
              <div v-if="item.udi" class="space-y-1">
                <p class="text-sm text-muted-foreground">{{ t('items.udi') }}</p>
                <p class="font-mono">{{ item.udi }}</p>
              </div>
              <div v-if="item.hs_code" class="space-y-1">
                <p class="text-sm text-muted-foreground">{{ t('items.hsCode') }}</p>
                <p class="font-mono">{{ item.hs_code }}</p>
              </div>
            </CardContent>
          </Card>

          <!-- Storage -->
          <Card v-if="item.temperature_min || item.temperature_max || item.storage_conditions">
            <CardHeader>
              <CardTitle>{{ t('items.storage') }}</CardTitle>
            </CardHeader>
            <CardContent class="grid gap-6 sm:grid-cols-2">
              <div v-if="item.temperature_min" class="space-y-1">
                <p class="text-sm text-muted-foreground">{{ t('items.temperatureMin') }}</p>
                <p>{{ item.temperature_min }}°C</p>
              </div>
              <div v-if="item.temperature_max" class="space-y-1">
                <p class="text-sm text-muted-foreground">{{ t('items.temperatureMax') }}</p>
                <p>{{ item.temperature_max }}°C</p>
              </div>
              <div v-if="item.weight_kg" class="space-y-1">
                <p class="text-sm text-muted-foreground">{{ t('items.weightKg') }}</p>
                <p>{{ item.weight_kg }} kg</p>
              </div>
              <div v-if="item.volume_m3" class="space-y-1">
                <p class="text-sm text-muted-foreground">{{ t('items.volumeM3') }}</p>
                <p>{{ item.volume_m3 }} m³</p>
              </div>
              <div v-if="item.storage_conditions" class="space-y-1 sm:col-span-2">
                <p class="text-sm text-muted-foreground">{{ t('items.storageConditions') }}</p>
                <p>{{ item.storage_conditions }}</p>
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <!-- Stock Tab -->
        <TabsContent value="stock">
          <Card>
            <CardHeader>
              <CardTitle>{{ t('items.stock') }}</CardTitle>
              <CardDescription>Stock levels by warehouse and location</CardDescription>
            </CardHeader>
            <CardContent>
              <div class="flex items-center justify-center py-12 text-muted-foreground">
                <p>{{ t('common.noData') }}</p>
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <!-- Barcodes Tab -->
        <TabsContent value="barcodes">
          <Card>
            <CardHeader>
              <CardTitle>{{ t('items.barcodes') }}</CardTitle>
              <CardDescription>Associated barcodes and RFID tags</CardDescription>
            </CardHeader>
            <CardContent>
              <div class="flex items-center justify-center py-12 text-muted-foreground">
                <p>{{ t('common.noData') }}</p>
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <!-- Attributes Tab -->
        <TabsContent value="attributes">
          <Card>
            <CardHeader>
              <CardTitle>{{ t('items.attributes') }}</CardTitle>
              <CardDescription>Custom attributes and metadata</CardDescription>
            </CardHeader>
            <CardContent>
              <div class="flex items-center justify-center py-12 text-muted-foreground">
                <p>{{ t('common.noData') }}</p>
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <!-- Transactions Tab -->
        <TabsContent value="transactions">
          <Card>
            <CardHeader>
              <CardTitle>{{ t('items.transactions') }}</CardTitle>
              <CardDescription>Transaction history for this item</CardDescription>
            </CardHeader>
            <CardContent>
              <div class="flex items-center justify-center py-12 text-muted-foreground">
                <p>{{ t('common.noData') }}</p>
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <!-- Audit Tab -->
        <TabsContent value="audit">
          <Card>
            <CardHeader>
              <CardTitle>{{ t('items.audit') }}</CardTitle>
              <CardDescription>Audit trail and change history</CardDescription>
            </CardHeader>
            <CardContent>
              <div class="space-y-4">
                <div class="flex justify-between text-sm">
                  <span class="text-muted-foreground">{{ t('common.createdAt') }}</span>
                  <span>{{ item.created_at }}</span>
                </div>
                <Separator />
                <div class="flex justify-between text-sm">
                  <span class="text-muted-foreground">{{ t('common.updatedAt') }}</span>
                  <span>{{ item.updated_at }}</span>
                </div>
              </div>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  </AuthenticatedLayout>
</template>
