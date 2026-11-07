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
import { PackageCheck, ArrowLeft, CheckCircle } from 'lucide-vue-next'

const { t } = useI18n()

const props = defineProps<{
    putaway: any
}>()

function confirmLine(lineId: number, binId: number) {
    const form = useForm({
        to_bin_id: binId
    })
    form.post(`/operations/putaways/${props.putaway.id}/lines/${lineId}/confirm`)
}

function completePutaway() {
    if (confirm('Complete this putaway? All lines must be placed.')) {
        const form = useForm({})
        form.post(`/operations/putaways/${props.putaway.id}/complete`)
    }
}

const statusVariants: Record<string, string> = {
    pending: 'destructive',
    placed: 'outline',
    skipped: 'secondary',
}

function formatDate(d: string) {
    return new Date(d).toLocaleString()
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" @click="$inertia.visit('/operations/putaways')">
                    <ArrowLeft class="h-5 w-5" />
                </Button>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        <PackageCheck class="me-2 inline h-6 w-6" />
                        {{ putaway.putaway_number }}
                    </h1>
                    <p class="text-muted-foreground">{{ t('operations.putawayDetails') || 'Putaway Details' }}</p>
                </div>
                <Badge :variant="(statusVariants[putaway.status] as any) || 'secondary'" class="ml-auto">
                    {{ putaway.status }}
                </Badge>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>{{ t('operations.headerInfo') || 'Header Information' }}</CardTitle>
                </CardHeader>
                <CardContent class="grid grid-cols-2 gap-4">
                    <div>
                        <Label class="text-muted-foreground">{{ t('operations.receivingRef') || 'Receiving Reference' }}</Label>
                        <p class="font-medium font-mono">{{ putaway.receiving?.receiving_number || '-' }}</p>
                    </div>
                    <div>
                        <Label class="text-muted-foreground">{{ t('common.warehouse') }}</Label>
                        <p class="font-medium">{{ putaway.warehouse?.name }}</p>
                    </div>
                    <div>
                        <Label class="text-muted-foreground">{{ t('operations.assignedTo') || 'Assigned To' }}</Label>
                        <p class="font-medium">{{ putaway.assigned_to?.name || '-' }}</p>
                    </div>
                    <div>
                        <Label class="text-muted-foreground">{{ t('common.createdAt') || 'Created At' }}</Label>
                        <p class="font-medium">{{ formatDate(putaway.created_at) }}</p>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <CardTitle>{{ t('operations.putawayLines') || 'Putaway Lines' }}</CardTitle>
                    <Button v-if="putaway.status !== 'completed'" @click="completePutaway">
                        <CheckCircle class="me-2 h-4 w-4" />
                        {{ t('operations.completePutaway') || 'Complete Putaway' }}
                    </Button>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{{ t('common.item') }}</TableHead>
                                <TableHead class="text-end">{{ t('common.quantity') }}</TableHead>
                                <TableHead>{{ t('operations.suggestedBin') || 'Suggested Bin' }}</TableHead>
                                <TableHead>{{ t('operations.actualBin') || 'Actual Bin' }}</TableHead>
                                <TableHead>{{ t('common.status') }}</TableHead>
                                <TableHead class="text-end">{{ t('common.actions') }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="line in putaway.lines" :key="line.id">
                                <TableCell>{{ line.item?.name }}</TableCell>
                                <TableCell class="text-end">{{ line.quantity }}</TableCell>
                                <TableCell class="font-mono">{{ line.suggested_bin?.name || '-' }}</TableCell>
                                <TableCell class="font-mono">{{ line.to_bin?.name || '-' }}</TableCell>
                                <TableCell>
                                    <Badge :variant="(statusVariants[line.status] as any) || 'secondary'">
                                        {{ line.status }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-end">
                                    <Button
                                        v-if="line.status === 'pending'"
                                        size="sm"
                                        @click="confirmLine(line.id, line.suggested_bin?.id || line.to_bin_id)"
                                    >
                                        {{ t('operations.confirmPlacement') || 'Confirm' }}
                                    </Button>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
