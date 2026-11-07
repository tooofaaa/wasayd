<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PageHeader from '@/Components/shared/PageHeader.vue'
import DataTable from '@/Components/shared/DataTable.vue'
import ConfirmDialog from '@/Components/shared/ConfirmDialog.vue'
import { Button } from '@/Components/ui/button'
import { Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { Plus, Pencil, Trash2 } from 'lucide-vue-next'
import { h } from 'vue'
import type { ColumnDef } from '@tanstack/vue-table'

const { t, locale } = useI18n()

const props = defineProps<{
  categories: {
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
    accessorKey: 'code',
    header: () => t('items.itemCode'),
    cell: ({ row }) => h('span', { class: 'font-mono text-sm' }, row.getValue('code')),
  },
  {
    accessorKey: 'name',
    header: () => t('items.name'),
    cell: ({ row }) => h('span', {}, locale.value === 'ar' ? (row.original.name_ar || row.original.name) : row.original.name),
  },
  {
    accessorKey: 'parent',
    header: () => t('items.parentCategory'),
    cell: ({ row }) => {
      const parent = row.original.parent
      if (!parent) return h('span', { class: 'text-muted-foreground' }, '-')
      return h('span', {}, locale.value === 'ar' ? (parent.name_ar || parent.name) : parent.name)
    },
  },
  {
    accessorKey: 'items_count',
    header: () => t('items.itemsCount'),
    cell: ({ row }) => h('span', { class: 'font-medium' }, row.getValue('items_count') || 0),
  },
  {
    id: 'actions',
    header: () => t('common.actions'),
    cell: ({ row }) => {
      return h('div', { class: 'flex items-center gap-1' }, [
        h(Link, { href: route('items.categories.edit', row.original.id) }, () =>
          h(Button, { variant: 'ghost', size: 'icon' }, () => h(Pencil, { class: 'h-4 w-4' }))
        ),
        h(ConfirmDialog, {
          title: t('common.confirmDelete'),
          description: t('locations.confirmDelete'),
          variant: 'destructive',
          confirmLabel: t('common.delete'),
          onConfirm: () => router.delete(route('items.categories.destroy', row.original.id)),
        }, () => h(Button, { variant: 'ghost', size: 'icon' }, () => h(Trash2, { class: 'h-4 w-4 text-destructive' }))),
      ])
    },
  },
]
</script>

<template>
  <AuthenticatedLayout>
    <div class="space-y-6">
      <PageHeader :title="t('items.categories.title')" :description="t('nav.categories')">
        <template #actions>
          <Link :href="route('items.categories.create')">
            <Button>
              <Plus class="me-2 h-4 w-4" />
              {{ t('items.categories.createCategory') }}
            </Button>
          </Link>
        </template>
      </PageHeader>

      <DataTable
        :columns="columns"
        :data="categories.data"
        :pagination="categories"
        searchable
        :filter-route="route('items.categories.index')"
      />
    </div>
  </AuthenticatedLayout>
</template>
