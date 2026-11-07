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
import { Eye, FileText, Lock } from 'lucide-vue-next'
import { ref } from 'vue'

const { t } = useI18n()

const props = defineProps<{
    transactions: any
    filters: { type?: string, warehouse_id?: number, item_id?: number }
    transactionTypes: any[]
    warehouses: any[]
    items: any[]
}>()

const typeFilter = ref(props.filters.type ?? '')
const warehouseFilter = ref(props.filters.warehouse_id ?? '')

function applyFilters() {
    router.get('/inventory/transactions', {
        type: typeFilter.value || undefined,
        warehouse_id: warehouseFilter.value || undefined,
    }, { preserveState: true })
}

const typeVariants: Record<string, string> = {
    receiving: 'default',
    issue: 'secondary',
    transfer: 'outline',
    adjustment: 'default',
    return: 'outline',
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
                        <FileText class="me-2 inline h-6 w-6" />
                        {{ t('inventory.transactionLedger') || 'Transaction Ledger' }}
                    </h1>
                    <p class="text-muted-foreground">
                        <Lock class="me-1 inline h-4 w-4" />
                        {{ t('inventory.immutableTransactions') || 'Immutable audit trail of all inventory movements' }}
                    </p>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <div class="flex items-center gap-4">
                        <Select v-model="typeFilter" @update:model-value="applyFilters">
                            <SelectTrigger class="w-48">
                                <SelectValue :placeholder="t('inventory.transactionType') || 'Type'" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{{ t('common.all') }}</SelectItem>
                                <SelectItem v-for="type in transactionTypes" :key="type.value" :value="type.value">
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
                                <TableHead>{{ t('inventory.txnNumber') || 'Transaction #' }}</TableHead>
                                <TableHead>{{ t('inventory.type') || 'Type' }}</TableHead>
                                <TableHead>{{ t('items.item') }}</TableHead>
                                <TableHead>{{ t('inventory.fromWarehouse') || 'From' }}</TableHead>
                                <TableHead>{{ t('inventory.toWarehouse') || 'To' }}</TableHead>
                                <TableHead class="text-end">{{ t('inventory.quantity') || 'Quantity' }}</TableHead>
                                <TableHead>{{ t('common.date') || 'Date' }}</TableHead>
                                <TableHead class="text-end">{{ t('common.actions') }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="txn in transactions.data" :key="txn.id">
                                <TableCell class="font-mono text-xs">
                                    {{ txn.transaction_number }}
                                    <Badge v-if="txn.is_reversal" variant="destructive" class="ms-2">
                                        {{ t('inventory.reversal') || 'REVERSAL' }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="(typeVariants[txn.transaction_type] as any) || 'secondary'">
                                        {{ txn.transaction_type }}
                                    </Badge>
                                </TableCell>
                                <TableCell>{{ txn.item?.name }}</TableCell>
                                <TableCell>{{ txn.from_warehouse?.name || '-' }}</TableCell>
                                <TableCell>{{ txn.to_warehouse?.name || '-' }}</TableCell>
                                <TableCell class="text-end" :class="txn.quantity < 0 ? 'text-red-600' : 'text-green-600'">
                                    {{ txn.quantity > 0 ? '+' : '' }}{{ txn.quantity }}
                                </TableCell>
                                <TableCell>{{ formatDate(txn.transaction_date) }}</TableCell>
                                <TableCell class="text-end">
                                    <Link :href="`/inventory/transactions/${txn.id}`">
                                        <Button variant="ghost" size="icon">
                                            <Eye class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="!transactions.data?.length">
                                <TableCell :colspan="8" class="py-8 text-center text-muted-foreground">
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
