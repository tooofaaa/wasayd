<script setup>
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Badge } from '@/Components/ui/badge'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table'
import { Download, AlertCircle } from 'lucide-vue-next'

const props = defineProps({
  batches: Array,
  filters: Object
})

const daysAhead = ref(props.filters.days_ahead)

const applyFilters = () => {
  router.get(route('reports.expiry'), { days_ahead: daysAhead.value })
}

const getUrgencyBadge = (urgency) => {
  const variants = {
    critical: { variant: 'destructive', label: 'Critical' },
    urgent: { variant: 'warning', label: 'Urgent' },
    warning: { variant: 'secondary', label: 'Warning' },
    normal: { variant: 'default', label: 'Normal' }
  }
  return variants[urgency] || variants.normal
}
</script>

<template>
  <Head title="Expiry Report" />

  <AuthenticatedLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Expiry Report</h1>
          <p class="text-muted-foreground mt-2">
            Monitor batches approaching expiration
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
          <div class="flex gap-4 items-end">
            <div>
              <label class="text-sm font-medium mb-2 block">Days Ahead</label>
              <div class="flex gap-2">
                <Button
                  v-for="days in [7, 15, 30, 60, 90]"
                  :key="days"
                  :variant="daysAhead === days ? 'default' : 'outline'"
                  size="sm"
                  @click="daysAhead = days; applyFilters()"
                >
                  {{ days }} days
                </Button>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card v-if="batches.length === 0">
        <CardContent class="py-12 text-center">
          <AlertCircle class="w-12 h-12 mx-auto text-muted-foreground mb-4" />
          <p class="text-lg font-medium">No expiring batches</p>
          <p class="text-muted-foreground">There are no batches expiring in the next {{ daysAhead }} days</p>
        </CardContent>
      </Card>

      <Card v-else>
        <CardHeader>
          <CardTitle>Expiring Batches ({{ batches.length }})</CardTitle>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Item</TableHead>
                <TableHead>SKU</TableHead>
                <TableHead>Batch Number</TableHead>
                <TableHead>Expiry Date</TableHead>
                <TableHead>Days Remaining</TableHead>
                <TableHead class="text-right">Quantity</TableHead>
                <TableHead>Warehouse</TableHead>
                <TableHead>Urgency</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="batch in batches" :key="batch.batch_number">
                <TableCell class="font-medium">{{ batch.item_name }}</TableCell>
                <TableCell>{{ batch.sku }}</TableCell>
                <TableCell>{{ batch.batch_number }}</TableCell>
                <TableCell>{{ batch.expiry_date }}</TableCell>
                <TableCell :class="{
                  'text-red-600 font-bold': batch.days_remaining <= 7,
                  'text-orange-600 font-semibold': batch.days_remaining <= 15
                }">
                  {{ batch.days_remaining }} days
                </TableCell>
                <TableCell class="text-right">{{ batch.quantity }}</TableCell>
                <TableCell>{{ batch.warehouse }}</TableCell>
                <TableCell>
                  <Badge :variant="getUrgencyBadge(batch.urgency).variant">
                    {{ getUrgencyBadge(batch.urgency).label }}
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
