<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table'

defineProps({ logs: Object, filters: Object })
</script>

<template>
  <Head title="Audit Trail Report" />
  <AuthenticatedLayout>
    <div class="space-y-6">
      <div><h1 class="text-3xl font-bold">Audit Trail</h1></div>
      <Card>
        <CardHeader><CardTitle>System Audit Log</CardTitle></CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Timestamp</TableHead>
                <TableHead>User</TableHead>
                <TableHead>Event</TableHead>
                <TableHead>Type</TableHead>
                <TableHead>ID</TableHead>
                <TableHead>IP Address</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="log in logs.data" :key="log.id">
                <TableCell>{{ log.timestamp }}</TableCell>
                <TableCell>{{ log.user }}</TableCell>
                <TableCell>{{ log.event }}</TableCell>
                <TableCell>{{ log.auditable_type }}</TableCell>
                <TableCell>{{ log.auditable_id }}</TableCell>
                <TableCell>{{ log.ip_address }}</TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  </AuthenticatedLayout>
</template>
