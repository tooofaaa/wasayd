<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Badge } from '@/Components/ui/badge'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/Components/ui/table'
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/Components/ui/select'
import { Search, Download, BarChart3 } from 'lucide-vue-next'

const { t } = useI18n()

const props = defineProps<{
    stocks: any
    warehouses: Array<{ id: number; name: string }>
    categories: Array<{ id: number; name: string }>
    itemTypes: string[]
    filters: Record<string, any>
}>()

const search = ref(props.filters?.search ?? '')
const warehouseId = ref(props.filters?.warehouse_id ?? '')
const categoryId = ref(props.filters?.category_id ?? '')
const itemType = ref(props.filters?.item_type ?? '')

function applyFilters() {
    router.get('/inventory/stock', {
        search: search.value || undefined,
        warehouse_id: warehouseId.value || undefined,
        category_id: categoryId.value || undefined,
        item_type: itemType.value || undefined,
    }, { preserveState: true })
}

function getStockStatus(available: number, reorderPoint: number | null) {
    if (!reorderPoint) return 'default'
    if (available <= 0) return 'destructive'
    if (available <= reorderPoint) return 'secondary'
    return 'default'
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        <BarChart3 class="me-2 inline h-6 w-6" />
                        {{ t('inventory.stockInquiry') || 'Stock Inquiry' }}
                    </h1>
                    <p class="text-muted-foreground">{{ t('inventory.stockInquiryDesc') || 'View current stock levels' }}</p>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="relative flex-1">
                            <Search class="absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                v-model="search"
                                :placeholder="t('common.search')"
                                class="ps-10"
                                @keyup.enter="applyFilters"
                            />
                        </div>
                        <Select v-model="warehouseId" @update:model-value="applyFilters">
                            <SelectTrigger class="w-48">
                                <SelectValue :placeholder="t('locations.warehouse') || 'Warehouse'" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{{ t('common.all') }}</SelectItem>
                                <SelectItem v-for="wh in warehouses" :key="wh.id" :value="String(wh.id)">
                                    {{ wh.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <Select v-model="itemType" @update:model-value="applyFilters">
                            <SelectTrigger class="w-48">
                                <SelectValue :placeholder="t('items.itemType') || 'Item Type'" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{{ t('common.all') }}</SelectItem>
                                <SelectItem v-for="type in itemTypes" :key="type" :value="type">
                                    {{ t(`items.${type}`) || type }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{{ t('items.itemCode') || 'Item Code' }}</TableHead>
                                <TableHead>{{ t('items.name') || 'Item' }}</TableHead>
                                <TableHead>{{ t('locations.warehouse') || 'Warehouse' }}</TableHead>
                                <TableHead>{{ t('items.itemType') || 'Type' }}</TableHead>
                                <TableHead class="text-center">{{ t('inventory.onHand') || 'On Hand' }}</TableHead>
                                <TableHead class="text-center">{{ t('inventory.reserved') || 'Reserved' }}</TableHead>
                                <TableHead class="text-center">{{ t('inventory.available') || 'Available' }}</TableHead>
                                <TableHead>{{ t('inventory.batch') || 'Batch' }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="stock in stocks.data" :key="stock.id">
                                <TableCell class="font-mono text-sm">{{ stock.item?.sku }}</TableCell>
                                <TableCell>
                                    <div class="font-medium">{{ stock.item?.name }}</div>
                                </TableCell>
                                <TableCell>{{ stock.warehouse?.name }}</TableCell>
                                <TableCell>
                                    <Badge variant="outline">{{ stock.item?.item_type }}</Badge>
                                </TableCell>
                                <TableCell class="text-center font-mono">{{ stock.quantity_on_hand }}</TableCell>
                                <TableCell class="text-center font-mono text-amber-600">{{ stock.quantity_reserved }}</TableCell>
                                <TableCell class="text-center">
                                    <Badge :variant="getStockStatus(stock.quantity_available, stock.item?.reorder_point) as any">
                                        {{ stock.quantity_available }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-xs">{{ stock.batch?.batch_number }}</TableCell>
                            </TableRow>
                            <TableRow v-if="!stocks.data?.length">
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
