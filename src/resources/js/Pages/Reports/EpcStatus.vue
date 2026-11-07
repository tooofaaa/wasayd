<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table'

defineProps({
  summary: Array,
  filters: Object
})
</script>

<template>
  <Head title="EPC Status Report" />

  <AuthenticatedLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-3xl font-bold tracking-tight">EPC Status</h1>
        <p class="text-muted-foreground mt-2">RFID tag status summary</p>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>EPC Summary by Item</CardTitle>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Item</TableHead>
                <TableHead>SKU</TableHead>
                <TableHead class="text-right">Total EPCs</TableHead>
                <TableHead class="text-right">Active</TableHead>
                <TableHead class="text-right">In Use</TableHead>
                <TableHead class="text-right">Decommissioned</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="item in summary" :key="item.sku">
                <TableCell class="font-medium">{{ item.item_name }}</TableCell>
                <TableCell>{{ item.sku }}</TableCell>
                <TableCell class="text-right font-semibold">{{ item.total_epcs }}</TableCell>
                <TableCell class="text-right text-green-600">{{ item.active }}</TableCell>
                <TableCell class="text-right text-blue-600">{{ item.in_use }}</TableCell>
                <TableCell class="text-right text-gray-600">{{ item.decommissioned }}</TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  </AuthenticatedLayout>
</template>
