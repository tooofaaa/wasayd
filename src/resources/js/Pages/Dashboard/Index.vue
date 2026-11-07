<script setup lang="ts">
import { computed } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Badge } from '@/Components/ui/badge'
import { Package, DollarSign, AlertTriangle, Clock, Radio, ArrowUp, ArrowDown } from 'lucide-vue-next'

defineOptions({ layout: AppLayout })

const { t, locale } = useI18n()

const props = defineProps<{
    stats: {
        totalItems: number
        stockValue: number
        lowStockAlerts: number
        expiringSoon: number
        activeEpcs: number
    }
    stockByWarehouse?: Array<{ name: string; total: number }>
    stockByType?: Array<{ type: string; count: number }>
    recentTransactions?: Array<{
        id: number
        transaction_number: string
        transaction_type: string
        item_name: string
        quantity: number
        created_at: string
    }>
    lowStockItems?: Array<{
        id: number
        sku: string
        name: string
        available: number
        reorder_point: number
    }>
    expiringBatches?: Array<{
        id: number
        item_name: string
        batch_number: string
        expiry_date: string
        quantity: number
    }>
}>()

const kpiCards = computed(() => [
    { key: 'totalItems' as const, icon: Package, color: 'text-primary', bg: 'bg-primary/10' },
    { key: 'stockValue' as const, icon: DollarSign, color: 'text-emerald-600', bg: 'bg-emerald-50 dark:bg-emerald-950' },
    { key: 'lowStockAlerts' as const, icon: AlertTriangle, color: 'text-amber-600', bg: 'bg-amber-50 dark:bg-amber-950' },
    { key: 'expiringSoon' as const, icon: Clock, color: 'text-red-600', bg: 'bg-red-50 dark:bg-red-950' },
    { key: 'activeEpcs' as const, icon: Radio, color: 'text-violet-600', bg: 'bg-violet-50 dark:bg-violet-950' },
])

function formatCurrency(value: number) {
    return new Intl.NumberFormat(locale.value === 'ar' ? 'ar-SA' : 'en-SA', {
        style: 'currency', currency: 'SAR', maximumFractionDigits: 0
    }).format(value)
}

function formatDate(d: string) {
    return new Date(d).toLocaleDateString(locale.value === 'ar' ? 'ar-SA' : 'en-US', {
        month: 'short', day: 'numeric'
    })
}

const typeColors: Record<string, string> = {
    receiving: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200',
    issue: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    transfer: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
    adjustment: 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200',
    return: 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900 dark:text-cyan-200',
}
</script>

<template>
    <Head :title="t('dashboard.title')" />

    <div class="space-y-6">
        <h2 class="text-2xl font-bold tracking-tight">{{ t('dashboard.title') }}</h2>

        <!-- KPI Cards -->
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-3 xl:grid-cols-5">
            <Card v-for="card in kpiCards" :key="card.key" class="relative overflow-hidden">
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground">
                        {{ t(`dashboard.${card.key}`) }}
                    </CardTitle>
                    <div :class="[card.bg, 'rounded-lg p-2']">
                        <component :is="card.icon" class="h-4 w-4" :class="card.color" />
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">
                        {{ card.key === 'stockValue' ? formatCurrency(stats[card.key]) : stats[card.key]?.toLocaleString() }}
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Charts Row -->
        <div class="grid gap-4 lg:grid-cols-2">
            <!-- Stock by Warehouse -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-base">{{ t('dashboard.stockByWarehouse') }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="stockByWarehouse?.length" class="space-y-3">
                        <div v-for="wh in stockByWarehouse" :key="wh.name" class="space-y-1">
                            <div class="flex items-center justify-between text-sm">
                                <span>{{ wh.name }}</span>
                                <span class="font-medium">{{ wh.total.toLocaleString() }}</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-muted">
                                <div
                                    class="h-full rounded-full bg-primary transition-all"
                                    :style="{ width: `${Math.min((wh.total / Math.max(...stockByWarehouse!.map(w => w.total))) * 100, 100)}%` }"
                                />
                            </div>
                        </div>
                    </div>
                    <div v-else class="flex h-32 items-center justify-center text-muted-foreground">
                        {{ t('common.noData') }}
                    </div>
                </CardContent>
            </Card>

            <!-- Stock by Item Type -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-base">{{ t('dashboard.stockByType') || 'Stock by Item Type' }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="stockByType?.length" class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                        <div
                            v-for="item in stockByType"
                            :key="item.type"
                            class="rounded-lg border p-3 text-center"
                        >
                            <div class="text-2xl font-bold">{{ item.count }}</div>
                            <div class="text-xs text-muted-foreground">{{ t(`items.${item.type}`) || item.type }}</div>
                        </div>
                    </div>
                    <div v-else class="flex h-32 items-center justify-center text-muted-foreground">
                        {{ t('common.noData') }}
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Lists Row -->
        <div class="grid gap-4 lg:grid-cols-3">
            <!-- Recent Transactions -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-base">{{ t('dashboard.recentTransactions') }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="recentTransactions?.length" class="space-y-3">
                        <div
                            v-for="txn in recentTransactions"
                            :key="txn.id"
                            class="flex items-center justify-between gap-2 text-sm"
                        >
                            <div class="min-w-0 flex-1">
                                <div class="truncate font-medium">{{ txn.item_name }}</div>
                                <div class="text-xs text-muted-foreground">{{ txn.transaction_number }}</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <Badge :class="typeColors[txn.transaction_type] || ''">
                                    {{ txn.transaction_type }}
                                </Badge>
                                <span class="whitespace-nowrap font-mono text-xs">
                                    {{ txn.quantity > 0 ? '+' : '' }}{{ txn.quantity }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="flex h-32 items-center justify-center text-muted-foreground">
                        {{ t('common.noData') }}
                    </div>
                </CardContent>
            </Card>

            <!-- Low Stock Alerts -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-base">{{ t('dashboard.lowStockAlerts') }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="lowStockItems?.length" class="space-y-3">
                        <div
                            v-for="item in lowStockItems"
                            :key="item.id"
                            class="flex items-center justify-between text-sm"
                        >
                            <div class="min-w-0 flex-1">
                                <div class="truncate font-medium">{{ item.name }}</div>
                                <div class="text-xs text-muted-foreground">{{ item.sku }}</div>
                            </div>
                            <div class="text-end">
                                <div class="font-mono text-destructive">{{ item.available }}</div>
                                <div class="text-xs text-muted-foreground">/ {{ item.reorder_point }}</div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="flex h-32 items-center justify-center text-muted-foreground">
                        {{ t('common.noData') }}
                    </div>
                </CardContent>
            </Card>

            <!-- Expiring Soon -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-base">{{ t('dashboard.expiringSoon') }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="expiringBatches?.length" class="space-y-3">
                        <div
                            v-for="batch in expiringBatches"
                            :key="batch.id"
                            class="flex items-center justify-between text-sm"
                        >
                            <div class="min-w-0 flex-1">
                                <div class="truncate font-medium">{{ batch.item_name }}</div>
                                <div class="text-xs text-muted-foreground">{{ batch.batch_number }}</div>
                            </div>
                            <div class="text-end">
                                <Badge variant="destructive">{{ formatDate(batch.expiry_date) }}</Badge>
                            </div>
                        </div>
                    </div>
                    <div v-else class="flex h-32 items-center justify-center text-muted-foreground">
                        {{ t('common.noData') }}
                </div>
            </CardContent>
        </Card>
        </div>
    </div>
</template>
