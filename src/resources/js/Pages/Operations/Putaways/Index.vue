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
import { Eye, PackageCheck } from 'lucide-vue-next'
import { ref } from 'vue'

const { t } = useI18n()

const props = defineProps<{
    putaways: any
    filters: { status?: string }
    statuses: any[]
}>()

const statusFilter = ref(props.filters.status ?? '')

function applyFilters() {
    router.get('/operations/putaways', {
        status: statusFilter.value || undefined,
    }, { preserveState: true })
}

const statusVariants: Record<string, string> = {
    pending: 'destructive',
    in_progress: 'default',
    completed: 'outline',
    cancelled: 'secondary',
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
                        <PackageCheck class="me-2 inline h-6 w-6" />
                        {{ t('operations.putaways') || 'Putaways' }}
                    </h1>
                    <p class="text-muted-foreground">{{ t('operations.putawaysDesc') || 'Manage inventory putaway tasks' }}</p>
                </div>
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
                    </div>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{{ t('operations.putawayNumber') || 'Putaway #' }}</TableHead>
                                <TableHead>{{ t('operations.receivingRef') || 'Receiving Ref' }}</TableHead>
                                <TableHead>{{ t('common.warehouse') }}</TableHead>
                                <TableHead>{{ t('common.status') }}</TableHead>
                                <TableHead>{{ t('operations.assignedTo') || 'Assigned To' }}</TableHead>
                                <TableHead>{{ t('common.date') }}</TableHead>
                                <TableHead class="text-end">{{ t('common.actions') }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="putaway in putaways.data" :key="putaway.id">
                                <TableCell class="font-mono font-medium">{{ putaway.putaway_number }}</TableCell>
                                <TableCell class="font-mono">{{ putaway.receiving?.receiving_number || '-' }}</TableCell>
                                <TableCell>{{ putaway.warehouse?.name }}</TableCell>
                                <TableCell>
                                    <Badge :variant="(statusVariants[putaway.status] as any) || 'secondary'">
                                        {{ putaway.status }}
                                    </Badge>
                                </TableCell>
                                <TableCell>{{ putaway.assigned_to?.name || '-' }}</TableCell>
                                <TableCell>{{ formatDate(putaway.created_at) }}</TableCell>
                                <TableCell class="text-end">
                                    <Link :href="`/operations/putaways/${putaway.id}`">
                                        <Button variant="ghost" size="icon">
                                            <Eye class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="!putaways.data?.length">
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
