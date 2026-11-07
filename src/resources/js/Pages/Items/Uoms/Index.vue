<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PageHeader from '@/Components/shared/PageHeader.vue'
import DataTable from '@/Components/shared/DataTable.vue'
import ConfirmDialog from '@/Components/shared/ConfirmDialog.vue'
import { Button } from '@/Components/ui/button'
import { Badge } from '@/Components/ui/badge'
import { Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { Plus, Pencil, Trash2 } from 'lucide-vue-next'
import { h } from 'vue'
import type { ColumnDef } from '@tanstack/vue-table'

const { t, locale } = useI18n()

defineProps<{
  uoms: {
    data: any[]
    current_page: number
    last_page: number
    per_page: number
    total: number
    links: any[]
  }
  filters: Record<string, any>
}>()

const columns: ColumnDef<any, any>[] = [
  {
    accessorKey: 'name',
    header: () => t('items.name'),
    cell: ({ row }) => h('span', {}, locale.value === 'ar' ? (row.original.name_ar || row.original.name) : row.original.name),
  },
  {
    accessorKey: 'code',
    header: () => t('items.code'),
    cell: ({ row }) => h('span', { class: 'font-mono font-medium' }, row.getValue('code')),
  },
  {
    accessorKey: 'symbol',
    header: () => t('items.symbol'),
    cell: ({ row }) => h('span', { class: 'font-mono' }, row.getValue('symbol') || '-'),
  },
  {
    accessorKey: 'is_base',
    header: () => t('items.isBase'),
    cell: ({ row }) => row.getValue('is_base') ? h(Badge, { variant: 'default' }, () => t('common.yes')) : h('span', { class: 'text-muted-foreground' }, t('common.no')),
  },
  {
    accessorKey: 'is_active',
    header: () => t('common.status'),
    cell: ({ row }) => row.getValue('is_active')
      ? h(Badge, { variant: 'default', class: 'bg-green-500' }, () => t('common.active'))
      : h(Badge, { variant: 'secondary' }, () => t('common.inactive')),
  },
  {
    id: 'actions',
    header: () => t('common.actions'),
    cell: ({ row }) => {
      return h('div', { class: 'flex items-center gap-1' }, [
        h(Link, { href: route('items.uoms.edit', row.original.id) }, () =>
          h(Button, { variant: 'ghost', size: 'icon' }, () => h(Pencil, { class: 'h-4 w-4' }))
        ),
        h(ConfirmDialog, {
          title: t('common.confirmDelete'),
          description: t('locations.confirmDelete'),
          variant: 'destructive',
          confirmLabel: t('common.delete'),
          onConfirm: () => router.delete(route('items.uoms.destroy', row.original.id)),
        }, () => h(Button, { variant: 'ghost', size: 'icon' }, () => h(Trash2, { class: 'h-4 w-4 text-destructive' }))),
      ])
    },
  },
]
</script>

<template>
  <AuthenticatedLayout>
    <div class="space-y-6">
      <PageHeader :title="t('items.uoms.title')" :description="t('nav.uom')">
        <template #actions>
          <Link :href="route('items.uoms.create')">
            <Button>
              <Plus class="me-2 h-4 w-4" />
              {{ t('items.uoms.createUom') }}
            </Button>
          </Link>
        </template>
      </PageHeader>

      <DataTable
        :columns="columns"
        :data="uoms.data"
        :pagination="uoms"
        searchable
        :filter-route="route('items.uoms.index')"
      />
    </div>
  </AuthenticatedLayout>
</template>
