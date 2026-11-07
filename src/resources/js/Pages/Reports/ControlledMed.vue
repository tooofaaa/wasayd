<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table'

defineProps({ reconciliation: Array, filters: Object })
</script>

<template>
  <Head title="Controlled Medications Report" />
  <AuthenticatedLayout>
    <div class="space-y-6">
      <div><h1 class="text-3xl font-bold">Controlled Medications</h1></div>
      <Card>
        <CardHeader><CardTitle>Reconciliation Report</CardTitle></CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Item</TableHead>
                <TableHead>SKU</TableHead>
                <TableHead class="text-right">Opening</TableHead>
                <TableHead class="text-right">Received</TableHead>
                <TableHead class="text-right">Dispensed</TableHead>
                <TableHead class="text-right">Closing</TableHead>
                <TableHead class="text-right">Variance</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="item in reconciliation" :key="item.sku">
                <TableCell>{{ item.item_name }}</TableCell>
                <TableCell>{{ item.sku }}</TableCell>
                <TableCell class="text-right">{{ item.opening_balance }}</TableCell>
                <TableCell class="text-right text-green-600">{{ item.received }}</TableCell>
                <TableCell class="text-right text-red-600">{{ item.dispensed }}</TableCell>
                <TableCell class="text-right font-semibold">{{ item.closing_balance }}</TableCell>
                <TableCell class="text-right" :class="item.variance !== 0 ? 'text-orange-600 font-bold' : ''">
                  {{ item.variance }}
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  </AuthenticatedLayout>
</template>
