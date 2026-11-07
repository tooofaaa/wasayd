<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table'

defineProps({ counts: Array, totalVariance: Number, avgAccuracy: Number, filters: Object })
</script>

<template>
  <Head title="Count Accuracy Report" />
  <AuthenticatedLayout>
    <div class="space-y-6">
      <div><h1 class="text-3xl font-bold">Count Accuracy</h1></div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <Card>
          <CardHeader><CardTitle>Average Accuracy</CardTitle></CardHeader>
          <CardContent><p class="text-4xl font-bold text-green-600">{{ avgAccuracy }}%</p></CardContent>
        </Card>
        <Card>
          <CardHeader><CardTitle>Total Variance</CardTitle></CardHeader>
          <CardContent><p class="text-4xl font-bold">{{ totalVariance }}</p></CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader><CardTitle>Count Details</CardTitle></CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Date</TableHead>
                <TableHead>Warehouse</TableHead>
                <TableHead>Item</TableHead>
                <TableHead class="text-right">System Qty</TableHead>
                <TableHead class="text-right">Counted Qty</TableHead>
                <TableHead class="text-right">Variance</TableHead>
                <TableHead class="text-right">Variance %</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="(count, idx) in counts" :key="idx">
                <TableCell>{{ count.count_date }}</TableCell>
                <TableCell>{{ count.warehouse }}</TableCell>
                <TableCell>{{ count.item_name }}</TableCell>
                <TableCell class="text-right">{{ count.system_qty }}</TableCell>
                <TableCell class="text-right">{{ count.counted_qty }}</TableCell>
                <TableCell class="text-right" :class="count.variance !== 0 ? 'text-orange-600 font-bold' : ''">
                  {{ count.variance }}
                </TableCell>
                <TableCell class="text-right">{{ count.variance_pct }}%</TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  </AuthenticatedLayout>
</template>
