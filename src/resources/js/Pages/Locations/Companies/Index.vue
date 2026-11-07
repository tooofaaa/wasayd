<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PageHeader from '@/Components/shared/PageHeader.vue'
import DataTable from '@/Components/shared/DataTable.vue'
import StatusBadge from '@/Components/shared/StatusBadge.vue'
import ConfirmDialog from '@/Components/shared/ConfirmDialog.vue'
import { Button } from '@/Components/ui/button'
import { Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { Plus, Eye, Pencil, Trash2 } from 'lucide-vue-next'
import { h } from 'vue'
import type { ColumnDef } from '@tanstack/vue-table'

const { t, locale } = useI18n()

const props = defineProps<{
  companies: {
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
    header: () => t('locations.code'),
    cell: ({ row }) => h('span', { class: 'font-mono text-sm' }, row.getValue('code')),
  },
  {
    accessorKey: 'name',
    header: () => t('locations.name'),
    cell: ({ row }) => h('span', {}, locale.value === 'ar' ? (row.original.name_ar || row.original.name) : row.original.name),
  },
  {
    accessorKey: 'is_active',
    header: () => t('common.status'),
    cell: ({ row }) => h(StatusBadge, { status: row.getValue('is_active') }),
  },
  {
    id: 'actions',
    header: () => t('common.actions'),
    cell: ({ row }) => {
      return h('div', { class: 'flex items-center gap-1' }, [
        h(Link, { href: route('locations.companies.show', row.original.id) }, () =>
          h(Button, { variant: 'ghost', size: 'icon' }, () => h(Eye, { class: 'h-4 w-4' }))
        ),
        h(Link, { href: route('locations.companies.edit', row.original.id) }, () =>
          h(Button, { variant: 'ghost', size: 'icon' }, () => h(Pencil, { class: 'h-4 w-4' }))
        ),
        h(ConfirmDialog, {
          title: t('common.confirmDelete'),
          description: t('locations.deleteCompanyConfirm'),
          variant: 'destructive',
          confirmLabel: t('common.delete'),
          onConfirm: () => router.delete(route('locations.companies.destroy', row.original.id)),
        }, () => h(Button, { variant: 'ghost', size: 'icon' }, () => h(Trash2, { class: 'h-4 w-4 text-destructive' }))),
      ])
    },
  },
]
</script>

<template>
  <AuthenticatedLayout>
    <div class="space-y-6 p-6">
      <PageHeader :title="t('locations.companies')" :description="t('locations.companiesDescription')">
        <template #actions>
          <Link :href="route('locations.companies.create')">
            <Button>
              <Plus class="me-2 h-4 w-4" />
              {{ t('locations.addCompany') }}
            </Button>
          </Link>
        </template>
      </PageHeader>

      <DataTable
        :columns="columns"
        :data="companies.data"
        :pagination="companies"
        searchable
        :filter-route="route('locations.companies.index')"
      />
    </div>
  </AuthenticatedLayout>
</template>
