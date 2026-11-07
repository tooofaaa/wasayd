<script setup lang="ts">
import { router, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Button } from '@/Components/ui/button'
import { Badge } from '@/Components/ui/badge'
import { Card, CardContent, CardHeader } from '@/Components/ui/card'
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/Components/ui/table'
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/Components/ui/select'
import { Plus, Eye, ClipboardList } from 'lucide-vue-next'
import { ref } from 'vue'

const { t } = useI18n()

const props = defineProps<{
    stockCounts: any
    filters: { status?: string, type?: string, warehouse_id?: number }
    statuses: any[]
    types: any[]
    warehouses: any[]
}>()

const statusFilter = ref(props.filters.status ?? '')
const typeFilter = ref(props.filters.type ?? '')
const warehouseFilter = ref(props.filters.warehouse_id ?? '')

function applyFilters() {
    router.get('/inventory/stock-counts', {
        status: statusFilter.value || undefined,
        type: typeFilter.value || undefined,
        warehouse_id: warehouseFilter.value || undefined,
    }, { preserveState: true })
}

const statusVariants: Record<string, string> = {
    planned: 'secondary',
    in_progress: 'default',
    counting: 'default',
    submitted: 'outline',
    reviewing: 'outline',
    approved: 'outline',
    cancelled: 'destructive',
}

const typeVariants: Record<string, string> = {
    full: 'default',
    cycle: 'secondary',
    spot: 'outline',
    rfid: 'default',
}

function formatDate(d: string) {
    return new Date(d).toLocaleDateString()
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        <ClipboardList class="me-2 inline h-6 w-6" />
                        {{ t('inventory.stockCounts') || 'Stock Counts' }}
                    </h1>
                    <p class="text-muted-foreground">{{ t('inventory.stockCountsDesc') || 'Physical inventory counts' }}</p>
                </div>
                <Link href="/inventory/stock-counts/create">
                    <Button>
                        <Plus class="me-2 h-4 w-4" />
                        {{ t('common.create') }}
                    </Button>
                </Link>
            </div>

            <Card>
                <CardHeader>
                    <div class="flex items-center gap-4">
                        <Select v-model="statusFilter" @update:model-value="applyFilters">
                            <SelectTrigger class="w-48">
                                <SelectValue :placeholder="t('common.status')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{{ t('common.all') }}</SelectItem>
                                <SelectItem v-for="status in statuses" :key="status.value" :value="status.value">
                                    {{ status.value }}
                                </SelectItem>
                            </SelectContent>
                        </Select>

                        <Select v-model="typeFilter" @update:model-value="applyFilters">
                            <SelectTrigger class="w-48">
                                <SelectValue :placeholder="t('inventory.countType') || 'Type'" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{{ t('common.all') }}</SelectItem>
                                <SelectItem v-for="type in types" :key="type.value" :value="type.value">
                                    {{ type.value }}
                                </SelectItem>
                            </SelectContent>
                        </Select>

                        <Select v-model="warehouseFilter" @update:model-value="applyFilters">
                            <SelectTrigger class="w-48">
                                <SelectValue :placeholder="t('locations.warehouse') || 'Warehouse'" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{{ t('common.all') }}</SelectItem>
                                <SelectItem v-for="wh in warehouses" :key="wh.id" :value="wh.id.toString()">
                                    {{ wh.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{{ t('inventory.countNumber') || 'Count #' }}</TableHead>
                                <TableHead>{{ t('inventory.type') || 'Type' }}</TableHead>
                                <TableHead>{{ t('locations.warehouse') || 'Warehouse' }}</TableHead>
                                <TableHead>{{ t('common.status') }}</TableHead>
                                <TableHead>{{ t('inventory.plannedDate') || 'Planned Date' }}</TableHead>
                                <TableHead>{{ t('inventory.assignedTo') || 'Assigned To' }}</TableHead>
                                <TableHead class="text-end">{{ t('common.actions') }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="count in stockCounts.data" :key="count.id">
                                <TableCell class="font-mono font-medium">{{ count.count_number }}</TableCell>
                                <TableCell>
                                    <Badge :variant="(typeVariants[count.count_type] as any) || 'secondary'">
                                        {{ count.count_type }}
                                    </Badge>
                                </TableCell>
                                <TableCell>{{ count.warehouse?.name }}</TableCell>
                                <TableCell>
                                    <Badge :variant="(statusVariants[count.status] as any) || 'secondary'">
                                        {{ count.status }}
                                    </Badge>
                                </TableCell>
                                <TableCell>{{ formatDate(count.planned_date) }}</TableCell>
                                <TableCell>{{ count.assignee?.name || '-' }}</TableCell>
                                <TableCell class="text-end">
                                    <Link :href="`/inventory/stock-counts/${count.id}`">
                                        <Button variant="ghost" size="icon">
                                            <Eye class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="!stockCounts.data?.length">
                                <TableCell :colspan="7" class="py-8 text-center text-muted-foreground">
                                    {{ t('common.noData') }}
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
