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
import { Plus, Pill, Trash2, CheckCircle } from 'lucide-vue-next'
import { ref } from 'vue'

const { t } = useI18n()

const props = defineProps<{
    logs: any
    filters: { action?: string, item_id?: number }
    actions: any[]
    items: any[]
}>()

const actionFilter = ref(props.filters.action ?? '')
const itemFilter = ref(props.filters.item_id ?? null)

function applyFilters() {
    router.get('/operations/controlled-medications', {
        action: actionFilter.value || undefined,
        item_id: itemFilter.value || undefined,
    }, { preserveState: true })
}

const actionVariants: Record<string, string> = {
    dispense: 'default',
    reconcile: 'secondary',
    waste: 'destructive',
    receive: 'outline',
    return: 'secondary',
    adjustment: 'default',
}

function formatDate(d: string) {
    return new Date(d).toLocaleString()
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        <Pill class="me-2 inline h-6 w-6" />
                        {{ t('operations.controlledMedications') || 'Controlled Medications' }}
                    </h1>
                    <p class="text-muted-foreground">{{ t('operations.controlledMedicationsDesc') || 'Immutable audit log of controlled substances' }}</p>
                </div>
                <div class="flex gap-2">
                    <Link href="/operations/controlled-medications/dispense">
                        <Button>
                            <Plus class="me-2 h-4 w-4" />
                            {{ t('operations.dispense') || 'Dispense' }}
                        </Button>
                    </Link>
                    <Link href="/operations/controlled-medications/waste">
                        <Button variant="destructive">
                            <Trash2 class="me-2 h-4 w-4" />
                            {{ t('operations.waste') || 'Waste' }}
                        </Button>
                    </Link>
                    <Link href="/operations/controlled-medications/reconcile">
                        <Button variant="secondary">
                            <CheckCircle class="me-2 h-4 w-4" />
                            {{ t('operations.reconcile') || 'Reconcile' }}
                        </Button>
                    </Link>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <div class="flex items-center gap-4">
                        <Select v-model="actionFilter" @update:model-value="applyFilters">
                            <SelectTrigger class="w-48">
                                <SelectValue :placeholder="t('common.action')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{{ t('common.all') }}</SelectItem>
                                <SelectItem v-for="action in actions" :key="action.value" :value="action.value">
                                    {{ action.value }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <Select v-model="itemFilter" @update:model-value="applyFilters">
                            <SelectTrigger class="w-64">
                                <SelectValue :placeholder="t('common.item')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem :value="null">{{ t('common.all') }}</SelectItem>
                                <SelectItem v-for="item in items" :key="item.id" :value="item.id">
                                    {{ item.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{{ t('common.date') }}</TableHead>
                                <TableHead>{{ t('common.action') }}</TableHead>
                                <TableHead>{{ t('common.item') }}</TableHead>
                                <TableHead class="text-end">{{ t('common.quantity') }}</TableHead>
                                <TableHead class="text-end">{{ t('operations.balance') || 'Balance' }}</TableHead>
                                <TableHead>{{ t('operations.dispensedBy') || 'Dispensed By' }}</TableHead>
                                <TableHead>{{ t('operations.witnessedBy') || 'Witnessed By' }}</TableHead>
                                <TableHead>{{ t('operations.patient') || 'Patient' }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="log in logs.data" :key="log.id">
                                <TableCell class="font-mono">{{ formatDate(log.action_at) }}</TableCell>
                                <TableCell>
                                    <Badge :variant="(actionVariants[log.action] as any) || 'secondary'">
                                        {{ log.action }}
                                    </Badge>
                                </TableCell>
                                <TableCell>{{ log.item?.name }}</TableCell>
                                <TableCell class="text-end">{{ log.quantity }}</TableCell>
                                <TableCell class="text-end font-mono">{{ log.balance_before }} → {{ log.balance_after }}</TableCell>
                                <TableCell>{{ log.dispensed_by?.name }}</TableCell>
                                <TableCell>{{ log.witnessed_by?.name || '-' }}</TableCell>
                                <TableCell>{{ log.patient_id || '-' }}</TableCell>
                            </TableRow>
                            <TableRow v-if="!logs.data?.length">
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
