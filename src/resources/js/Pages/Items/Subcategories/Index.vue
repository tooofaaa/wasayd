<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PageHeader from '@/Components/shared/PageHeader.vue'
import DataTable from '@/Components/shared/DataTable.vue'
import ConfirmDialog from '@/Components/shared/ConfirmDialog.vue'
import { Button } from '@/Components/ui/button'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
import { Label } from '@/Components/ui/label'
import { Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { Plus, Pencil, Trash2 } from 'lucide-vue-next'
import { h, ref } from 'vue'
import type { ColumnDef } from '@tanstack/vue-table'

const { t, locale } = useI18n()

const props = defineProps<{
  subcategories: {
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

const selectedCategory = ref(props.filters.category_id || '')

function filterByCategory(categoryId: string) {
  router.get(route('items.subcategories.index'), { category_id: categoryId }, { preserveState: true })
}

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
    accessorKey: 'category',
    header: () => t('items.category'),
    cell: ({ row }) => {
      const category = row.original.category
      if (!category) return h('span', { class: 'text-muted-foreground' }, '-')
      return h('span', {}, locale.value === 'ar' ? (category.name_ar || category.name) : category.name)
    },
  },
  {
    id: 'actions',
    header: () => t('common.actions'),
    cell: ({ row }) => {
      return h('div', { class: 'flex items-center gap-1' }, [
        h(Link, { href: route('items.subcategories.edit', row.original.id) }, () =>
          h(Button, { variant: 'ghost', size: 'icon' }, () => h(Pencil, { class: 'h-4 w-4' }))
        ),
        h(ConfirmDialog, {
          title: t('common.confirmDelete'),
          description: t('locations.confirmDelete'),
          variant: 'destructive',
          confirmLabel: t('common.delete'),
          onConfirm: () => router.delete(route('items.subcategories.destroy', row.original.id)),
        }, () => h(Button, { variant: 'ghost', size: 'icon' }, () => h(Trash2, { class: 'h-4 w-4 text-destructive' }))),
      ])
    },
  },
]
</script>

<template>
  <AuthenticatedLayout>
    <div class="space-y-6">
      <PageHeader :title="t('items.subcategories.title')" :description="t('nav.categories')">
        <template #actions>
          <Link :href="route('items.subcategories.create')">
            <Button>
              <Plus class="me-2 h-4 w-4" />
              {{ t('items.subcategories.createSubcategory') }}
            </Button>
          </Link>
        </template>
      </PageHeader>

      <div class="flex gap-4 items-end mb-4">
        <div class="w-64 space-y-2">
          <Label>{{ t('items.category') }}</Label>
          <Select v-model="selectedCategory" @update:model-value="filterByCategory">
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
      </div>

      <DataTable
        :columns="columns"
        :data="subcategories.data"
        :pagination="subcategories"
        searchable
        :filter-route="route('items.subcategories.index')"
      />
    </div>
  </AuthenticatedLayout>
</template>
