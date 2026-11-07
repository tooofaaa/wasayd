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
import { ArrowLeft, CheckCircle, XCircle, Play } from 'lucide-vue-next'
import { computed } from 'vue'

const { t } = useI18n()

const props = defineProps<{
    adjustment: any
}>()

const statusVariants: Record<string, string> = {
    pending_approval: 'secondary',
    approved: 'default',
    rejected: 'destructive',
    applied: 'outline',
}

const canApprove = computed(() => props.adjustment.status === 'pending_approval')
const canApply = computed(() => props.adjustment.status === 'approved')

function approve() {
    router.post(`/inventory/stock-adjustments/${props.adjustment.id}/approve`)
}

const rejectForm = useForm({
    reason: '',
})

function reject() {
    if (confirm(t('inventory.confirmReject') || 'Are you sure you want to reject this adjustment?')) {
        const reason = prompt(t('inventory.rejectReason') || 'Reason for rejection:')
        if (reason) {
            rejectForm.reason = reason
            rejectForm.post(`/inventory/stock-adjustments/${props.adjustment.id}/reject`)
        }
    }
}

function apply() {
    if (confirm(t('inventory.confirmApply') || 'Are you sure you want to apply this adjustment? Stock will be updated.')) {
        router.post(`/inventory/stock-adjustments/${props.adjustment.id}/apply`)
    }
}

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
                        {{ adjustment.adjustment_number }}
                    </h1>
                    <p class="text-muted-foreground">{{ t('inventory.adjustmentDetails') || 'Adjustment details' }}</p>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" as="a" href="/inventory/stock-adjustments">
                        <ArrowLeft class="me-2 h-4 w-4" />
                        {{ t('common.back') }}
                    </Button>
                    <Button v-if="canApprove" variant="default" @click="approve">
                        <CheckCircle class="me-2 h-4 w-4" />
                        {{ t('common.approve') }}
                    </Button>
                    <Button v-if="canApprove" variant="destructive" @click="reject">
                        <XCircle class="me-2 h-4 w-4" />
                        {{ t('common.reject') }}
                    </Button>
                    <Button v-if="canApply" variant="default" @click="apply">
                        <Play class="me-2 h-4 w-4" />
                        {{ t('inventory.applyAdjustment') || 'Apply' }}
                    </Button>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-4">
                <Card>
                    <CardHeader>
                        <CardTitle class="text-sm font-medium">{{ t('common.status') }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Badge :variant="(statusVariants[adjustment.status] as any) || 'secondary'" class="text-lg">
                            {{ adjustment.status }}
                        </Badge>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="text-sm font-medium">{{ t('inventory.type') }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Badge variant="outline" class="text-lg">{{ adjustment.adjustment_type }}</Badge>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="text-sm font-medium">{{ t('inventory.totalItems') }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-2xl font-bold">{{ adjustment.total_items }}</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="text-sm font-medium">{{ t('inventory.requestedBy') }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-lg font-semibold">{{ adjustment.requester?.name }}</p>
                    </CardContent>
                </Card>
            </div>

            <Card v-if="adjustment.reason">
                <CardHeader>
                    <CardTitle>{{ t('common.reason') }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <p>{{ adjustment.reason }}</p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>{{ t('inventory.adjustmentLines') || 'Adjustment Lines' }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{{ t('items.item') }}</TableHead>
                                <TableHead>{{ t('inventory.batch') }}</TableHead>
                                <TableHead>{{ t('locations.warehouse') }}</TableHead>
                                <TableHead>{{ t('locations.bin') }}</TableHead>
                                <TableHead class="text-end">{{ t('inventory.systemQty') }}</TableHead>
                                <TableHead class="text-end">{{ t('inventory.adjustedQty') }}</TableHead>
                                <TableHead class="text-end">{{ t('inventory.variance') }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="line in adjustment.lines" :key="line.id">
                                <TableCell>{{ line.item?.name }}</TableCell>
                                <TableCell>{{ line.batch?.batch_number || '-' }}</TableCell>
                                <TableCell>{{ line.warehouse?.name }}</TableCell>
                                <TableCell>{{ line.bin?.name || '-' }}</TableCell>
                                <TableCell class="text-end">{{ line.system_quantity }}</TableCell>
                                <TableCell class="text-end">{{ line.adjusted_quantity }}</TableCell>
                                <TableCell class="text-end" :class="getVarianceColor(line.variance || 0)">
                                    {{ line.variance > 0 ? '+' : '' }}{{ line.variance }}
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <Card v-if="adjustment.approval">
                <CardHeader>
                    <CardTitle>{{ t('inventory.approvalWorkflow') || 'Approval Workflow' }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div v-for="step in adjustment.approval.steps" :key="step.id" class="flex items-center gap-4 border-b pb-4">
                            <div class="flex-1">
                                <p class="font-medium">{{ t('inventory.step') }} {{ step.step_order }}: {{ step.role }}</p>
                                <p class="text-sm text-muted-foreground">{{ step.user?.name || t('inventory.anyUser') }}</p>
                            </div>
                            <Badge :variant="step.status === 'approved' ? 'default' : step.status === 'rejected' ? 'destructive' : 'secondary'">
                                {{ step.status }}
                            </Badge>
                            <p v-if="step.acted_at" class="text-sm text-muted-foreground">
                                {{ new Date(step.acted_at).toLocaleDateString() }}
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
