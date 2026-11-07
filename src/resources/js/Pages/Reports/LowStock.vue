<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Badge } from '@/Components/ui/badge'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table'
import { Download, AlertTriangle } from 'lucide-vue-next'

const props = defineProps({
  items: Array,
  filters: Object
})
</script>

<template>
  <Head title="Low Stock Report" />

  <AuthenticatedLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Low Stock Report</h1>
          <p class="text-muted-foreground mt-2">Items below reorder point</p>
        </div>
        <Button variant="outline">
          <Download class="w-4 h-4 mr-2" />
          Export CSV
        </Button>
      </div>

      <Card v-if="items.length === 0">
        <CardContent class="py-12 text-center">
          <p class="text-lg font-medium">All stock levels are healthy</p>
        </CardContent>
      </Card>

      <Card v-else>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <AlertTriangle class="w-5 h-5 text-orange-600" />
            {{ items.length }} Items Below Reorder Point
          </CardTitle>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Item</TableHead>
                <TableHead>SKU</TableHead>
                <TableHead>Category</TableHead>
                <TableHead class="text-right">Current Stock</TableHead>
                <TableHead class="text-right">Min Stock</TableHead>
                <TableHead class="text-right">Reorder Point</TableHead>
                <TableHead class="text-right">Shortfall</TableHead>
                <TableHead>Status</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="item in items" :key="item.sku">
                <TableCell class="font-medium">{{ item.item_name }}</TableCell>
                <TableCell>{{ item.sku }}</TableCell>
                <TableCell>{{ item.category }}</TableCell>
                <TableCell class="text-right">{{ item.current_stock }}</TableCell>
                <TableCell class="text-right">{{ item.min_stock }}</TableCell>
                <TableCell class="text-right">{{ item.reorder_point }}</TableCell>
                <TableCell class="text-right font-semibold text-red-600">{{ item.shortfall }}</TableCell>
                <TableCell>
                  <Badge :variant="item.status === 'out_of_stock' ? 'destructive' : 'warning'">
                    {{ item.status === 'out_of_stock' ? 'Out of Stock' : 'Low Stock' }}
                  </Badge>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  </AuthenticatedLayout>
</template>
