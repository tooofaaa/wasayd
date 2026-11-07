<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Badge } from '@/Components/ui/badge'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table'

defineProps({ maintenances: Array, filters: Object })
</script>

<template>
  <Head title="Maintenance Due Report" />
  <AuthenticatedLayout>
    <div class="space-y-6">
      <div><h1 class="text-3xl font-bold">Maintenance Due</h1></div>
      <Card>
        <CardHeader><CardTitle>Upcoming & Overdue Maintenance</CardTitle></CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Asset</TableHead>
                <TableHead>Tag</TableHead>
                <TableHead>Type</TableHead>
                <TableHead>Scheduled Date</TableHead>
                <TableHead>Days Until Due</TableHead>
                <TableHead>Status</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="m in maintenances" :key="m.asset_code">
                <TableCell>{{ m.asset_name }}</TableCell>
                <TableCell>{{ m.asset_code }}</TableCell>
                <TableCell>{{ m.maintenance_type }}</TableCell>
                <TableCell>{{ m.scheduled_date }}</TableCell>
                <TableCell>{{ m.days_until_due }}</TableCell>
                <TableCell>
                  <Badge :variant="m.urgency === 'overdue' ? 'destructive' : 'warning'">
                    {{ m.urgency }}
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
