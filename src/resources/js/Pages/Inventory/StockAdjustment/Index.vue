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
import { Plus, Eye, FileEdit } from 'lucide-vue-next'
import { ref } from 'vue'

const { t } = useI18n()

const props = defineProps<{
    adjustments: any
    filters: { status?: string, type?: string }
    statuses: any[]
    types: any[]
}>()

const statusFilter = ref(props.filters.status ?? '')
const typeFilter = ref(props.filters.type ?? '')

function applyFilters() {
    router.get('/inventory/stock-adjustments', {
        status: statusFilter.value || undefined,
        type: typeFilter.value || undefined,
    }, { preserveState: true })
}

const statusVariants: Record<string, string> = {
    pending_approval: 'secondary',
    approved: 'default',
    rejected: 'destructive',
    applied: 'outline',
}

const typeVariants: Record<string, string> = {
    count_variance: 'default',
    manual: 'secondary',
    damage: 'destructive',
    expiry: 'destructive',
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
                        <FileEdit class="me-2 inline h-6 w-6" />
                        {{ t('inventory.stockAdjustments') || 'Stock Adjustments' }}
                    </h1>
                    <p class="text-muted-foreground">{{ t('inventory.stockAdjustmentsDesc') || 'Manage inventory adjustments' }}</p>
                </div>
                <Link href="/inventory/stock-adjustments/create">
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
                                <SelectValue :placeholder="t('inventory.adjustmentType') || 'Type'" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{{ t('common.all') }}</SelectItem>
                                <SelectItem v-for="type in types" :key="type.value" :value="type.value">
                                    {{ type.value }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{{ t('inventory.adjustmentNumber') || 'Adjustment #' }}</TableHead>
                                <TableHead>{{ t('inventory.type') || 'Type' }}</TableHead>
                                <TableHead>{{ t('common.status') }}</TableHead>
                                <TableHead class="text-end">{{ t('inventory.totalItems') || 'Items' }}</TableHead>
                                <TableHead>{{ t('inventory.requestedBy') || 'Requested By' }}</TableHead>
                                <TableHead>{{ t('common.date') || 'Date' }}</TableHead>
                                <TableHead class="text-end">{{ t('common.actions') }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="adj in adjustments.data" :key="adj.id">
                                <TableCell class="font-mono font-medium">{{ adj.adjustment_number }}</TableCell>
                                <TableCell>
                                    <Badge :variant="(typeVariants[adj.adjustment_type] as any) || 'secondary'">
                                        {{ adj.adjustment_type }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="(statusVariants[adj.status] as any) || 'secondary'">
                                        {{ adj.status }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-end">{{ adj.total_items }}</TableCell>
                                <TableCell>{{ adj.requester?.name }}</TableCell>
                                <TableCell>{{ formatDate(adj.created_at) }}</TableCell>
                                <TableCell class="text-end">
                                    <Link :href="`/inventory/stock-adjustments/${adj.id}`">
                                        <Button variant="ghost" size="icon">
                                            <Eye class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="!adjustments.data?.length">
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
