<script setup>
import { Head, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table'
import { Download } from 'lucide-vue-next'

const props = defineProps({
  stocks: Array,
  warehouses: Array,
  chartData: Array,
  filters: Object
})

const warehouseFilter = ref(props.filters.warehouse_id)
const categoryFilter = ref(props.filters.category_id)
const itemTypeFilter = ref(props.filters.item_type)

const applyFilters = () => {
  router.get(route('reports.stock-summary'), {
    warehouse_id: warehouseFilter.value,
    category_id: categoryFilter.value,
    item_type: itemTypeFilter.value
  }, {
    preserveState: true
  })
}

const exportData = () => {
  window.open(route('reports.stock-summary', { ...props.filters, export: 'csv' }), '_blank')
}
</script>

<template>
  <Head title="Stock Summary Report" />

  <AuthenticatedLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Stock Summary</h1>
          <p class="text-muted-foreground mt-2">
            Stock levels by warehouse, category, and type
          </p>
        </div>
        <Button @click="exportData" variant="outline">
          <Download class="w-4 h-4 mr-2" />
          Export CSV
        </Button>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Filters</CardTitle>
          <CardDescription>Filter stock summary by warehouse, category, or type</CardDescription>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <Select v-model="warehouseFilter">
              <SelectTrigger>
                <SelectValue placeholder="All Warehouses" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem :value="null">All Warehouses</SelectItem>
                <SelectItem v-for="wh in warehouses" :key="wh.id" :value="wh.id">
                  {{ wh.name }}
                </SelectItem>
              </SelectContent>
            </Select>

            <Button @click="applyFilters">Apply Filters</Button>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Stock Levels</CardTitle>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Item</TableHead>
                <TableHead>SKU</TableHead>
                <TableHead>Category</TableHead>
                <TableHead>Warehouse</TableHead>
                <TableHead class="text-right">On Hand</TableHead>
                <TableHead class="text-right">Reserved</TableHead>
                <TableHead class="text-right">Available</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="stock in stocks" :key="stock.id">
                <TableCell class="font-medium">{{ stock.item_name }}</TableCell>
                <TableCell>{{ stock.sku }}</TableCell>
                <TableCell>{{ stock.category }}</TableCell>
                <TableCell>{{ stock.warehouse }}</TableCell>
                <TableCell class="text-right">{{ stock.on_hand }}</TableCell>
                <TableCell class="text-right">{{ stock.reserved }}</TableCell>
                <TableCell class="text-right font-semibold">{{ stock.available }}</TableCell>
              </TableRow>
              <TableRow v-if="stocks.length === 0">
                <TableCell colspan="7" class="text-center text-muted-foreground py-8">
                  No stock data available
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  </AuthenticatedLayout>
</template>
