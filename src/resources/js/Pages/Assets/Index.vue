<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PageHeader from '@/Components/shared/PageHeader.vue'
import DataTable from '@/Components/shared/DataTable.vue'
import StatusBadge from '@/Components/shared/StatusBadge.vue'
import ConfirmDialog from '@/Components/shared/ConfirmDialog.vue'
import { Button } from '@/Components/ui/button'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
import { Label } from '@/Components/ui/label'
import { Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { Plus, Eye, Pencil, Trash2 } from 'lucide-vue-next'
import { h, ref } from 'vue'
import type { ColumnDef } from '@tanstack/vue-table'

const { t, locale } = useI18n()

const props = defineProps<{
  assets: {
    data: any[]
    current_page: number
    last_page: number
    per_page: number
    total: number
    links: any[]
  }
  warehouses: any[]
  statuses: any[]
  filters: Record<string, any>
}>()

const selectedStatus = ref(props.filters.status || '')
const selectedWarehouse = ref(props.filters.warehouse_id || '')

function applyFilters() {
  router.get(route('assets.index'), {
    status: selectedStatus.value || undefined,
    warehouse_id: selectedWarehouse.value || undefined,
  }, { preserveState: true })
}

const statusVariants: Record<string, string> = {
  available: 'success',
  in_use: 'primary',
  maintenance: 'warning',
  repair: 'warning',
  calibration: 'warning',
  quarantine: 'destructive',
  retired: 'secondary',
  decommissioned: 'secondary',
  disposed: 'secondary',
  lost: 'destructive',
}

const columns: ColumnDef<any, any>[] = [
  {
    accessorKey: 'asset_code',
    header: () => t('Asset Code'),
    cell: ({ row }) => h('span', { class: 'font-mono text-sm' }, row.getValue('asset_code')),
  },
  {
    accessorKey: 'name',
    header: () => t('Name'),
    cell: ({ row }) => h('span', {}, locale.value === 'ar' ? (row.original.name_ar || row.original.name) : row.original.name),
  },
  {
    accessorKey: 'item',
    header: () => t('Item'),
    cell: ({ row }) => {
      const item = row.original.item
      if (!item) return h('span', { class: 'text-muted-foreground' }, '-')
      return h('span', {}, locale.value === 'ar' ? (item.name_ar || item.name) : item.name)
    },
  },
  {
    accessorKey: 'status',
    header: () => t('Status'),
    cell: ({ row }) => {
      const status = row.original.status
      return h(StatusBadge, {
        variant: statusVariants[status] || 'secondary',
        label: status.replace(/_/g, ' ').replace(/\b\w/g, (l: string) => l.toUpperCase()),
      })
    },
  },
  {
    accessorKey: 'warehouse',
    header: () => t('Warehouse'),
    cell: ({ row }) => {
      const warehouse = row.original.warehouse
      if (!warehouse) return h('span', { class: 'text-muted-foreground' }, '-')
      return h('span', {}, locale.value === 'ar' ? (warehouse.name_ar || warehouse.name) : warehouse.name)
    },
  },
  {
    id: 'actions',
    header: () => t('common.actions'),
    cell: ({ row }) => {
      return h('div', { class: 'flex items-center gap-1' }, [
        h(Link, { href: route('assets.show', row.original.id) }, () =>
          h(Button, { variant: 'ghost', size: 'icon' }, () => h(Eye, { class: 'h-4 w-4' }))
        ),
        h(Link, { href: route('assets.edit', row.original.id) }, () =>
          h(Button, { variant: 'ghost', size: 'icon' }, () => h(Pencil, { class: 'h-4 w-4' }))
        ),
        h(ConfirmDialog, {
          title: t('common.confirmDelete'),
          description: 'Are you sure you want to delete this asset?',
          variant: 'destructive',
          confirmLabel: t('common.delete'),
          onConfirm: () => router.delete(route('assets.destroy', row.original.id)),
        }, () => h(Button, { variant: 'ghost', size: 'icon' }, () => h(Trash2, { class: 'h-4 w-4 text-destructive' }))),
      ])
    },
  },
]
</script>

<template>
  <AuthenticatedLayout>
    <div class="space-y-6">
      <PageHeader :title="t('Assets')" :description="t('Manage your medical assets')">
        <template #actions>
          <Link :href="route('assets.create')">
            <Button>
              <Plus class="me-2 h-4 w-4" />
              {{ t('Create Asset') }}
            </Button>
          </Link>
        </template>
      </PageHeader>

      <div class="flex gap-4 items-end mb-4">
        <div class="w-64 space-y-2">
          <Label>{{ t('Status') }}</Label>
          <Select v-model="selectedStatus" @update:model-value="applyFilters">
            <SelectTrigger>
              <SelectValue :placeholder="t('All Statuses')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">{{ t('All Statuses') }}</SelectItem>
              <SelectItem v-for="status in statuses" :key="status.value" :value="status.value">
                {{ status.label }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div class="w-64 space-y-2">
          <Label>{{ t('Warehouse') }}</Label>
          <Select v-model="selectedWarehouse" @update:model-value="applyFilters">
            <SelectTrigger>
              <SelectValue :placeholder="t('All Warehouses')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">{{ t('All Warehouses') }}</SelectItem>
              <SelectItem v-for="warehouse in warehouses" :key="warehouse.id" :value="String(warehouse.id)">
                {{ locale === 'ar' ? (warehouse.name_ar || warehouse.name) : warehouse.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
      </div>

      <DataTable
        :columns="columns"
        :data="assets.data"
        :pagination="assets"
        searchable
        :filter-route="route('assets.index')"
      />
    </div>
  </AuthenticatedLayout>
</template>
