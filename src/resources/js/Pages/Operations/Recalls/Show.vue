<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Button } from '@/Components/ui/button'
import { Badge } from '@/Components/ui/badge'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Label } from '@/Components/ui/label'
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/Components/ui/table'
import { AlertTriangle, ArrowLeft, ShieldAlert, CheckCircle } from 'lucide-vue-next'

const { t } = useI18n()

const props = defineProps<{
    recall: any
}>()

function quarantineLine(lineId: number) {
    const form = useForm({})
    form.post(`/operations/recalls/${props.recall.id}/quarantine-line/${lineId}`)
}

function recoverLine(lineId: number, quantity: number) {
    const qty = prompt('Enter recovered quantity:', quantity.toString())
    if (qty) {
        const form = useForm({ quantity: parseFloat(qty) })
        form.post(`/operations/recalls/${props.recall.id}/recover-line/${lineId}`)
    }
}

function completeRecall() {
    if (confirm('Complete this recall? All lines must be recovered or disposed.')) {
        const form = useForm({})
        form.post(`/operations/recalls/${props.recall.id}/complete`)
    }
}

const statusVariants: Record<string, string> = {
    initiated: 'destructive',
    in_progress: 'default',
    completed: 'outline',
    cancelled: 'secondary',
}

const classVariants: Record<string, string> = {
    class_i: 'destructive',
    class_ii: 'default',
    class_iii: 'secondary',
}

const lineStatusVariants: Record<string, string> = {
    identified: 'secondary',
    quarantined: 'destructive',
    recovered: 'default',
    disposed: 'outline',
}

function formatDate(d: string) {
    return new Date(d).toLocaleDateString()
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" @click="$inertia.visit('/operations/recalls')">
                    <ArrowLeft class="h-5 w-5" />
                </Button>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        <AlertTriangle class="me-2 inline h-6 w-6" />
                        {{ recall.recall_number }}
                    </h1>
                    <p class="text-muted-foreground">{{ t('operations.recallDetails') || 'Recall Details' }}</p>
                </div>
                <Badge :variant="(statusVariants[recall.status] as any) || 'secondary'" class="ml-auto">
                    {{ recall.status }}
                </Badge>
                <Badge :variant="(classVariants[recall.recall_class] as any) || 'secondary'">
                    {{ recall.recall_class }}
                </Badge>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>{{ t('operations.recallInformation') || 'Recall Information' }}</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label class="text-muted-foreground">{{ t('common.item') }}</Label>
                            <p class="font-medium">{{ recall.item?.name }}</p>
                        </div>
                        <div>
                            <Label class="text-muted-foreground">{{ t('operations.manufacturerRecallId') || 'Manufacturer Recall ID' }}</Label>
                            <p class="font-medium">{{ recall.manufacturer_recall_id || '-' }}</p>
                        </div>
                        <div class="col-span-2">
                            <Label class="text-muted-foreground">{{ t('common.description') }}</Label>
                            <p class="font-medium">{{ recall.description }}</p>
                        </div>
                        <div class="col-span-2">
                            <Label class="text-muted-foreground">{{ t('operations.instructions') || 'Instructions' }}</Label>
                            <p class="font-medium">{{ recall.instructions || '-' }}</p>
                        </div>
                        <div>
                            <Label class="text-muted-foreground">{{ t('operations.initiatedBy') || 'Initiated By' }}</Label>
                            <p class="font-medium">{{ recall.initiated_by?.name }}</p>
                        </div>
                        <div>
                            <Label class="text-muted-foreground">{{ t('operations.initiatedDate') || 'Initiated Date' }}</Label>
                            <p class="font-medium">{{ formatDate(recall.initiated_date) }}</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <Label>{{ t('operations.recoveryProgress') || 'Recovery Progress' }}</Label>
                            <span class="font-medium">{{ recall.total_recovered_qty }} / {{ recall.total_affected_qty }} ({{ recall.recovery_percentage }}%)</span>
                        </div>
                        <div class="h-2 w-full bg-secondary rounded-full overflow-hidden">
                            <div class="h-full bg-primary transition-all" :style="`width: ${recall.recovery_percentage}%`"></div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <CardTitle>{{ t('operations.affectedStock') || 'Affected Stock' }}</CardTitle>
                    <Button v-if="recall.status !== 'completed'" @click="completeRecall">
                        <CheckCircle class="me-2 h-4 w-4" />
                        {{ t('operations.completeRecall') || 'Complete Recall' }}
                    </Button>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{{ t('common.warehouse') }}</TableHead>
                                <TableHead>{{ t('common.batch') }}</TableHead>
                                <TableHead class="text-end">{{ t('operations.affected') || 'Affected' }}</TableHead>
                                <TableHead class="text-end">{{ t('operations.recovered') || 'Recovered' }}</TableHead>
                                <TableHead>{{ t('common.status') }}</TableHead>
                                <TableHead class="text-end">{{ t('common.actions') }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="line in recall.lines" :key="line.id">
                                <TableCell>{{ line.warehouse?.name }}</TableCell>
                                <TableCell class="font-mono">{{ line.batch?.batch_number || '-' }}</TableCell>
                                <TableCell class="text-end">{{ line.affected_quantity }}</TableCell>
                                <TableCell class="text-end">{{ line.recovered_quantity }}</TableCell>
                                <TableCell>
                                    <Badge :variant="(lineStatusVariants[line.status] as any) || 'secondary'">
                                        {{ line.status }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-end">
                                    <div class="flex gap-1 justify-end">
                                        <Button
                                            v-if="line.status === 'identified'"
                                            size="sm"
                                            variant="destructive"
                                            @click="quarantineLine(line.id)"
                                        >
                                            <ShieldAlert class="me-1 h-3 w-3" />
                                            Quarantine
                                        </Button>
                                        <Button
                                            v-if="line.status === 'quarantined'"
                                            size="sm"
                                            @click="recoverLine(line.id, line.affected_quantity)"
                                        >
                                            <CheckCircle class="me-1 h-3 w-3" />
                                            Recover
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
