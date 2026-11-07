<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table'

defineProps({
  utilization: Array,
  utilizationRate: Number,
  filters: Object
})
</script>

<template>
  <Head title="Asset Utilization Report" />

  <AuthenticatedLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-3xl font-bold tracking-tight">Asset Utilization</h1>
        <p class="text-muted-foreground mt-2">Track asset usage by department</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Card>
          <CardHeader>
            <CardTitle>Overall Utilization Rate</CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-4xl font-bold text-blue-600">{{ utilizationRate }}%</p>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Asset Utilization by Department</CardTitle>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Department</TableHead>
                <TableHead>Status</TableHead>
                <TableHead class="text-right">Count</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="(item, idx) in utilization" :key="idx">
                <TableCell class="font-medium">{{ item.department }}</TableCell>
                <TableCell>{{ item.status }}</TableCell>
                <TableCell class="text-right">{{ item.count }}</TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  </AuthenticatedLayout>
</template>
