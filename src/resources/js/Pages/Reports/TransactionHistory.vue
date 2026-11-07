<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table'

const props = defineProps({
  transactions: Object,
  warehouses: Array,
  filters: Object
})
</script>

<template>
  <Head title="Transaction History" />

  <AuthenticatedLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-3xl font-bold tracking-tight">Transaction History</h1>
        <p class="text-muted-foreground mt-2">Complete inventory transaction log</p>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Recent Transactions</CardTitle>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Date</TableHead>
                <TableHead>Type</TableHead>
                <TableHead>Item</TableHead>
                <TableHead>Warehouse</TableHead>
                <TableHead class="text-right">Quantity</TableHead>
                <TableHead>Reference</TableHead>
                <TableHead>User</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="txn in transactions.data" :key="txn.id">
                <TableCell>{{ txn.date }}</TableCell>
                <TableCell>{{ txn.type }}</TableCell>
                <TableCell>{{ txn.item_name }}</TableCell>
                <TableCell>{{ txn.warehouse }}</TableCell>
                <TableCell class="text-right" :class="{
                  'text-green-600': txn.quantity > 0,
                  'text-red-600': txn.quantity < 0
                }">{{ txn.quantity }}</TableCell>
                <TableCell>{{ txn.reference }}</TableCell>
                <TableCell>{{ txn.user }}</TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  </AuthenticatedLayout>
</template>
