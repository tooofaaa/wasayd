<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Button } from '@/Components/ui/button'
import { Badge } from '@/Components/ui/badge'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/Components/ui/table'
import { ArrowLeft, Play, Send, CheckCircle } from 'lucide-vue-next'
import { computed } from 'vue'

const { t } = useI18n()

const props = defineProps<{
    stockCount: any
    summary: {
        total_items: number
        matched: number
        positive_variance: number
        negative_variance: number
        uncounted: number
    }
}>()

const statusVariants: Record<string, string> = {
    planned: 'secondary',
    in_progress: 'default',
    counting: 'default',
    submitted: 'outline',
    reviewing: 'outline',
    approved: 'outline',
    cancelled: 'destructive',
}

function startCount() {
    router.post(`/inventory/stock-counts/${props.stockCount.id}/start`)
}

function submitCount() {
    router.post(`/inventory/stock-counts/${props.stockCount.id}/submit`)
}

function approveCount() {
    router.post(`/inventory/stock-counts/${props.stockCount.id}/approve`)
}

const canStart = computed(() => props.stockCount.status === 'planned')
const canSubmit = computed(() => ['in_progress', 'counting'].includes(props.stockCount.status))
const canApprove = computed(() => props.stockCount.status === 'submitted')

function getVarianceColor(variance: number): string {
    if (variance > 0) return 'text-green-600'
    if (variance < 0) return 'text-red-600'
    return 'text-muted-foreground'
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        {{ stockCount.count_number }}
                    </h1>
                    <p class="text-muted-foreground">{{ t('inventory.stockCountDetails') || 'Stock count details' }}</p>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" as="a" href="/inventory/stock-counts">
                        <ArrowLeft class="me-2 h-4 w-4" />
                        {{ t('common.back') }}
                    </Button>
                    <Button v-if="canStart" @click="startCount">
                        <Play class="me-2 h-4 w-4" />
                        {{ t('inventory.startCount') || 'Start Count' }}
                    </Button>
                    <Button v-if="canSubmit" @click="submitCount">
                        <Send class="me-2 h-4 w-4" />
                        {{ t('inventory.submitCount') || 'Submit for Review' }}
                    </Button>
                    <Button v-if="canApprove" @click="approveCount">
                        <CheckCircle class="me-2 h-4 w-4" />
                        {{ t('inventory.approveCount') || 'Approve' }}
                    </Button>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <Card>
                    <CardHeader>
                        <CardTitle class="text-sm font-medium">{{ t('common.status') }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Badge :variant="(statusVariants[stockCount.status] as any) || 'secondary'" class="text-lg">
                            {{ stockCount.status }}
                        </Badge>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="text-sm font-medium">{{ t('locations.warehouse') }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-lg font-semibold">{{ stockCount.warehouse?.name }}</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="text-sm font-medium">{{ t('inventory.countType') }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Badge variant="outline" class="text-lg">{{ stockCount.count_type }}</Badge>
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>{{ t('inventory.varianceSummary') || 'Variance Summary' }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-5">
                        <div>
                            <p class="text-sm text-muted-foreground">{{ t('inventory.totalItems') }}</p>
                            <p class="text-2xl font-bold">{{ summary.total_items }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">{{ t('inventory.matched') }}</p>
                            <p class="text-2xl font-bold text-green-600">{{ summary.matched }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">{{ t('inventory.positiveVariance') }}</p>
                            <p class="text-2xl font-bold text-green-600">+{{ summary.positive_variance }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">{{ t('inventory.negativeVariance') }}</p>
                            <p class="text-2xl font-bold text-red-600">{{ summary.negative_variance }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">{{ t('inventory.uncounted') }}</p>
                            <p class="text-2xl font-bold text-muted-foreground">{{ summary.uncounted }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>{{ t('inventory.countLines') || 'Count Lines' }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{{ t('items.item') }}</TableHead>
                                <TableHead>{{ t('inventory.batch') }}</TableHead>
                                <TableHead>{{ t('locations.bin') }}</TableHead>
                                <TableHead class="text-end">{{ t('inventory.systemQty') }}</TableHead>
                                <TableHead class="text-end">{{ t('inventory.countedQty') }}</TableHead>
                                <TableHead class="text-end">{{ t('inventory.variance') }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="line in stockCount.lines" :key="line.id">
                                <TableCell>{{ line.item?.name }}</TableCell>
                                <TableCell>{{ line.batch?.batch_number || '-' }}</TableCell>
                                <TableCell>{{ line.bin?.name || '-' }}</TableCell>
                                <TableCell class="text-end">{{ line.system_quantity }}</TableCell>
                                <TableCell class="text-end">{{ line.counted_quantity ?? '-' }}</TableCell>
                                <TableCell class="text-end" :class="getVarianceColor(line.variance || 0)">
                                    <span v-if="line.variance !== null">
                                        {{ line.variance > 0 ? '+' : '' }}{{ line.variance }}
                                    </span>
                                    <span v-else>-</span>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="!stockCount.lines?.length">
                                <TableCell :colspan="6" class="py-8 text-center text-muted-foreground">
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
