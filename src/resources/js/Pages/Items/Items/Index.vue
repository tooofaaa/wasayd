<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PageHeader from '@/Components/shared/PageHeader.vue'
import DataTable from '@/Components/shared/DataTable.vue'
import ConfirmDialog from '@/Components/shared/ConfirmDialog.vue'
import { Button } from '@/Components/ui/button'
import { Badge } from '@/Components/ui/badge'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { Plus, Eye, Pencil, Trash2 } from 'lucide-vue-next'
import { h, ref } from 'vue'
import type { ColumnDef } from '@tanstack/vue-table'

const { t, locale } = useI18n()

const props = defineProps<{
  items: {
    data: any[]
    current_page: number
    last_page: number
    per_page: number
    total: number
    links: any[]
  }
  categories: any[]
  filters: Record<string, any>
}>()

const selectedType = ref(props.filters.item_type || 'all')
const selectedCategory = ref(props.filters.category_id || 'all')
const selectedStatus = ref(props.filters.status || 'all')
const searchQuery = ref(props.filters.search || '')

function applyFilters() {
  router.get(route('items.items.index'), {
    item_type: selectedType.value === 'all' ? undefined : selectedType.value,
    category_id: selectedCategory.value === 'all' ? undefined : selectedCategory.value,
    status: selectedStatus.value === 'all' ? undefined : selectedStatus.value,
    search: searchQuery.value || undefined,
  }, { preserveState: true })
}

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

const columns: ColumnDef<any, any>[] = [
  {
    accessorKey: 'sku',
    header: () => t('items.itemCode'),
    cell: ({ row }) => h('span', { class: 'font-mono text-sm font-medium' }, row.getValue('sku')),
  },
  {
    accessorKey: 'name',
    header: () => t('items.name'),
    cell: ({ row }) => h('span', {}, locale.value === 'ar' ? (row.original.name_ar || row.original.name) : row.original.name),
  },
  {
    accessorKey: 'item_type',
    header: () => t('items.itemType'),
    cell: ({ row }) => {
      const type = row.getValue('item_type') as string
      return h(Badge, { class: itemTypeColors[type] }, () => t(`items.types.${type}`))
    },
  },
  {
    accessorKey: 'category',
    header: () => t('items.category'),
    cell: ({ row }) => {
      const category = row.original.category
      if (!category) return h('span', { class: 'text-muted-foreground' }, '-')
      return h('span', {}, locale.value === 'ar' ? (category.name_ar || category.name) : category.name)
    },
  },
  {
    accessorKey: 'status',
    header: () => t('items.status'),
    cell: ({ row }) => {
      const status = row.getValue('status') as string
      return h(Badge, { variant: statusColors[status] as any }, () => t(`items.statuses.${status}`))
    },
  },
  {
    accessorKey: 'stock_summary',
    header: () => t('items.stockSummary'),
    cell: ({ row }) => {
      const stock = row.original.stock_summary || 0
      return h('span', { class: 'font-medium' }, stock)
    },
  },
  {
    id: 'actions',
    header: () => t('common.actions'),
    cell: ({ row }) => {
      return h('div', { class: 'flex items-center gap-1' }, [
        h(Link, { href: route('items.items.show', row.original.id) }, () =>
          h(Button, { variant: 'ghost', size: 'icon' }, () => h(Eye, { class: 'h-4 w-4' }))
        ),
        h(Link, { href: route('items.items.edit', row.original.id) }, () =>
          h(Button, { variant: 'ghost', size: 'icon' }, () => h(Pencil, { class: 'h-4 w-4' }))
        ),
        h(ConfirmDialog, {
          title: t('common.confirmDelete'),
          description: t('locations.confirmDelete'),
          variant: 'destructive',
          confirmLabel: t('common.delete'),
          onConfirm: () => router.delete(route('items.items.destroy', row.original.id)),
        }, () => h(Button, { variant: 'ghost', size: 'icon' }, () => h(Trash2, { class: 'h-4 w-4 text-destructive' }))),
      ])
    },
  },
]
</script>

<template>
  <AuthenticatedLayout>
    <div class="space-y-6">
      <PageHeader :title="t('items.title')" :description="t('nav.itemMaster')">
        <template #actions>
          <Link :href="route('items.items.create')">
            <Button>
              <Plus class="me-2 h-4 w-4" />
              {{ t('items.createItem') }}
            </Button>
          </Link>
        </template>
      </PageHeader>

      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="space-y-2">
          <Label>{{ t('items.itemType') }}</Label>
          <Select v-model="selectedType" @update:model-value="applyFilters">
            <SelectTrigger>
              <SelectValue :placeholder="t('items.allTypes')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">{{ t('items.allTypes') }}</SelectItem>
              <SelectItem value="medical_consumable">{{ t('items.types.medical_consumable') }}</SelectItem>
              <SelectItem value="medicine">{{ t('items.types.medicine') }}</SelectItem>
              <SelectItem value="medical_device">{{ t('items.types.medical_device') }}</SelectItem>
              <SelectItem value="medical_equipment">{{ t('items.types.medical_equipment') }}</SelectItem>
              <SelectItem value="narcotic_controlled">{{ t('items.types.narcotic_controlled') }}</SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div class="space-y-2">
          <Label>{{ t('items.category') }}</Label>
          <Select v-model="selectedCategory" @update:model-value="applyFilters">
            <SelectTrigger>
              <SelectValue :placeholder="t('items.allCategories')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">{{ t('items.allCategories') }}</SelectItem>
              <SelectItem v-for="cat in categories" :key="cat.id" :value="String(cat.id)">
                {{ locale === 'ar' ? (cat.name_ar || cat.name) : cat.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div class="space-y-2">
          <Label>{{ t('items.status') }}</Label>
          <Select v-model="selectedStatus" @update:model-value="applyFilters">
            <SelectTrigger>
              <SelectValue :placeholder="t('items.allStatuses')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">{{ t('items.allStatuses') }}</SelectItem>
              <SelectItem value="draft">{{ t('items.statuses.draft') }}</SelectItem>
              <SelectItem value="active">{{ t('items.statuses.active') }}</SelectItem>
              <SelectItem value="discontinued">{{ t('items.statuses.discontinued') }}</SelectItem>
              <SelectItem value="recalled">{{ t('items.statuses.recalled') }}</SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div class="space-y-2">
          <Label>{{ t('common.search') }}</Label>
          <Input
            v-model="searchQuery"
            :placeholder="t('common.search')"
            @input="applyFilters"
          />
        </div>
      </div>

      <DataTable
        :columns="columns"
        :data="items.data"
        :pagination="items"
        :filter-route="route('items.items.index')"
      />
    </div>
  </AuthenticatedLayout>
</template>
