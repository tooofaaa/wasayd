<script setup lang="ts" generic="T">
import {
  FlexRender,
  getCoreRowModel,
  useVueTable,
  type ColumnDef,
} from '@tanstack/vue-table'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table'
import { Input } from '@/Components/ui/input'
import { Button } from '@/Components/ui/button'
import { Search, ChevronLeft, ChevronRight } from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps<{
  columns: ColumnDef<T, any>[]
  data: T[]
  pagination?: {
    current_page: number
    last_page: number
    per_page: number
    total: number
    links: { url: string | null; label: string; active: boolean }[]
  }
  searchable?: boolean
  searchPlaceholder?: string
  filterRoute?: string
}>()

const { t } = useI18n()
const search = ref('')

const table = useVueTable({
  get data() { return props.data },
  get columns() { return props.columns },
  getCoreRowModel: getCoreRowModel(),
})

let debounce: ReturnType<typeof setTimeout>
watch(search, (val) => {
  if (!props.filterRoute) return
  clearTimeout(debounce)
  debounce = setTimeout(() => {
    router.get(props.filterRoute!, { 'filter[search]': val || undefined }, {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    })
  }, 300)
})

function goToPage(url: string | null) {
  if (url) router.get(url, {}, { preserveState: true, preserveScroll: true })
}
</script>

<template>
  <div class="space-y-4">
    <div v-if="searchable" class="flex items-center gap-2">
      <div class="relative max-w-sm flex-1">
        <Search class="absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
        <Input
          v-model="search"
          :placeholder="searchPlaceholder ?? t('common.search')"
          class="ps-9"
        />
      </div>
      <slot name="filters" />
    </div>

    <div class="rounded-md border">
      <Table>
        <TableHeader>
          <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
            <TableHead v-for="header in headerGroup.headers" :key="header.id">
              <FlexRender
                v-if="!header.isPlaceholder"
                :render="header.column.columnDef.header"
                :props="header.getContext()"
              />
            </TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <template v-if="table.getRowModel().rows?.length">
            <TableRow
              v-for="row in table.getRowModel().rows"
              :key="row.id"
            >
              <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
              </TableCell>
            </TableRow>
          </template>
          <template v-else>
            <TableRow>
              <TableCell :colspan="columns.length" class="h-24 text-center text-muted-foreground">
                {{ t('common.noResults') }}
              </TableCell>
            </TableRow>
          </template>
        </TableBody>
      </Table>
    </div>

    <div v-if="pagination && pagination.last_page > 1" class="flex items-center justify-between">
      <p class="text-sm text-muted-foreground">
        {{ t('common.showingOf', { total: pagination.total }) }}
      </p>
      <div class="flex items-center gap-1">
        <Button
          variant="outline"
          size="icon"
          :disabled="pagination.current_page === 1"
          @click="goToPage(pagination.links[0]?.url)"
        >
          <ChevronLeft class="h-4 w-4" />
        </Button>
        <template v-for="link in pagination.links.slice(1, -1)" :key="link.label">
          <Button
            :variant="link.active ? 'default' : 'outline'"
            size="sm"
            :disabled="!link.url"
            @click="goToPage(link.url)"
            v-html="link.label"
          />
        </template>
        <Button
          variant="outline"
          size="icon"
          :disabled="pagination.current_page === pagination.last_page"
          @click="goToPage(pagination.links[pagination.links.length - 1]?.url)"
        >
          <ChevronRight class="h-4 w-4" />
        </Button>
      </div>
    </div>
  </div>
</template>
