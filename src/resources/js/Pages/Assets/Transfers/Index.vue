<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PageHeader from '@/Components/shared/PageHeader.vue'
import DataTable from '@/Components/shared/DataTable.vue'
import StatusBadge from '@/Components/shared/StatusBadge.vue'
import { Button } from '@/Components/ui/button'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
import { Label } from '@/Components/ui/label'
import { Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { Plus, CheckCircle, Truck } from 'lucide-vue-next'
import { h, ref } from 'vue'
import type { ColumnDef } from '@tanstack/vue-table'

const { t, locale } = useI18n()

const props = defineProps<{
  transfers: {
    data: any[]
    current_page: number
    last_page: number
    per_page: number
    total: number
    links: any[]
  }
  statuses: any[]
  filters: Record<string, any>
}>()

const selectedStatus = ref(props.filters.status || '')

function applyFilters() {
  router.get(route('assets.transfers.index'), {
    status: selectedStatus.value || undefined,
  }, { preserveState: true })
}

const statusVariants: Record<string, string> = {
  pending: 'secondary',
  approved: 'primary',
  in_transit: 'warning',
  completed: 'success',
  cancelled: 'destructive',
}

function approveTransfer(transferId: number) {
  router.post(route('assets.transfers.approve', transferId))
}

function completeTransfer(transferId: number) {
  router.post(route('assets.transfers.complete', transferId))
}

const columns: ColumnDef<any, any>[] = [
  {
    accessorKey: 'asset',
    header: () => t('Asset'),
    cell: ({ row }) => {
      const asset = row.original.asset
      return h('div', {}, [
        h('div', { class: 'font-mono text-sm' }, asset.asset_code),
        h('div', { class: 'text-sm text-muted-foreground' }, locale.value === 'ar' ? (asset.name_ar || asset.name) : asset.name),
      ])
    },
  },
  {
    accessorKey: 'from_warehouse',
    header: () => t('From'),
    cell: ({ row }) => {
      const warehouse = row.original.from_warehouse
      if (!warehouse) return h('span', { class: 'text-muted-foreground' }, '-')
      return h('span', {}, locale.value === 'ar' ? (warehouse.name_ar || warehouse.name) : warehouse.name)
    },
  },
  {
    accessorKey: 'to_warehouse',
    header: () => t('To'),
    cell: ({ row }) => {
      const warehouse = row.original.to_warehouse
      if (!warehouse) return h('span', { class: 'text-muted-foreground' }, '-')
      return h('span', {}, locale.value === 'ar' ? (warehouse.name_ar || warehouse.name) : warehouse.name)
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
    accessorKey: 'transfer_date',
    header: () => t('Transfer Date'),
    cell: ({ row }) => h('span', {}, row.getValue('transfer_date')),
  },
  {
    accessorKey: 'reason',
    header: () => t('Reason'),
    cell: ({ row }) => h('span', { class: 'truncate max-w-xs' }, row.getValue('reason') || '-'),
  },
  {
    id: 'actions',
    header: () => t('common.actions'),
    cell: ({ row }) => {
      const transfer = row.original
      return h('div', { class: 'flex items-center gap-1' }, [
        transfer.status === 'pending' && h(Button, {
          variant: 'outline',
          size: 'sm',
          onClick: () => approveTransfer(transfer.id),
        }, () => [h(CheckCircle, { class: 'h-4 w-4 me-1' }), 'Approve']),
        transfer.status === 'in_transit' && h(Button, {
          variant: 'outline',
          size: 'sm',
          onClick: () => completeTransfer(transfer.id),
        }, () => [h(Truck, { class: 'h-4 w-4 me-1' }), 'Complete']),
      ])
    },
  },
]
</script>

<template>
  <AuthenticatedLayout>
    <div class="space-y-6">
      <PageHeader :title="t('Asset Transfers')" :description="t('Manage asset transfers between warehouses')">
        <template #actions>
          <Link :href="route('assets.transfers.create')">
            <Button>
              <Plus class="me-2 h-4 w-4" />
              {{ t('Create Transfer') }}
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
      </div>

      <DataTable
        :columns="columns"
        :data="transfers.data"
        :pagination="transfers"
        searchable
        :filter-route="route('assets.transfers.index')"
      />
    </div>
  </AuthenticatedLayout>
</template>
