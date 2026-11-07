<script setup>
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table'
import { Download } from 'lucide-vue-next'

const props = defineProps({
  movements: Array,
  warehouses: Array,
  filters: Object
})

const dateFrom = ref(props.filters.date_from)
const dateTo = ref(props.filters.date_to)

const applyFilters = () => {
  router.get(route('reports.stock-movement'), {
    date_from: dateFrom.value,
    date_to: dateTo.value
  })
}
</script>

<template>
  <Head title="Stock Movement Report" />

  <AuthenticatedLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Stock Movement</h1>
          <p class="text-muted-foreground mt-2">
            Track inventory movements over time
          </p>
        </div>
        <Button variant="outline">
          <Download class="w-4 h-4 mr-2" />
          Export CSV
        </Button>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Filters</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <Label>Date From</Label>
              <Input type="date" v-model="dateFrom" />
            </div>
            <div>
              <Label>Date To</Label>
              <Input type="date" v-model="dateTo" />
            </div>
            <div class="flex items-end">
              <Button @click="applyFilters">Apply Filters</Button>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardContent class="pt-6">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Date</TableHead>
                <TableHead>Item</TableHead>
                <TableHead>SKU</TableHead>
                <TableHead>Warehouse</TableHead>
                <TableHead>Type</TableHead>
                <TableHead class="text-right">Quantity In</TableHead>
                <TableHead class="text-right">Quantity Out</TableHead>
                <TableHead>Reference</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="(movement, idx) in movements" :key="idx">
                <TableCell>{{ movement.date }}</TableCell>
                <TableCell>{{ movement.item_name }}</TableCell>
                <TableCell>{{ movement.sku }}</TableCell>
                <TableCell>{{ movement.warehouse }}</TableCell>
                <TableCell>{{ movement.type }}</TableCell>
                <TableCell class="text-right text-green-600 font-semibold">
                  {{ movement.quantity_in || '-' }}
                </TableCell>
                <TableCell class="text-right text-red-600 font-semibold">
                  {{ movement.quantity_out || '-' }}
                </TableCell>
                <TableCell>{{ movement.reference }}</TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  </AuthenticatedLayout>
</template>
