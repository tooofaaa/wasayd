<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table'
import { Download } from 'lucide-vue-next'

const props = defineProps({
  valuations: Array,
  totalValue: Number,
  warehouses: Array,
  filters: Object
})

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value)
}
</script>

<template>
  <Head title="Stock Valuation Report" />

  <AuthenticatedLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Stock Valuation</h1>
          <p class="text-muted-foreground mt-2">
            Calculate total inventory value by warehouse
          </p>
        </div>
        <Button variant="outline">
          <Download class="w-4 h-4 mr-2" />
          Export CSV
        </Button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Card>
          <CardHeader>
            <CardTitle>Total Inventory Value</CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-4xl font-bold text-green-600">{{ formatCurrency(totalValue) }}</p>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Stock Valuation Details</CardTitle>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Item</TableHead>
                <TableHead>SKU</TableHead>
                <TableHead>Warehouse</TableHead>
                <TableHead class="text-right">Quantity</TableHead>
                <TableHead class="text-right">Unit Cost</TableHead>
                <TableHead class="text-right">Total Value</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="(val, idx) in valuations" :key="idx">
                <TableCell class="font-medium">{{ val.item_name }}</TableCell>
                <TableCell>{{ val.sku }}</TableCell>
                <TableCell>{{ val.warehouse }}</TableCell>
                <TableCell class="text-right">{{ val.quantity }}</TableCell>
                <TableCell class="text-right">{{ formatCurrency(val.unit_cost) }}</TableCell>
                <TableCell class="text-right font-semibold">{{ formatCurrency(val.total_value) }}</TableCell>
              </TableRow>
              <TableRow class="bg-muted/50 font-bold">
                <TableCell colspan="5" class="text-right">Total</TableCell>
                <TableCell class="text-right">{{ formatCurrency(totalValue) }}</TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  </AuthenticatedLayout>
</template>
