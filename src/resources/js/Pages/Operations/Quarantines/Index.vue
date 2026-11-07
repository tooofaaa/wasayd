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
import { Plus, Eye, ShieldAlert } from 'lucide-vue-next'
import { ref } from 'vue'

const { t } = useI18n()

const props = defineProps<{
    quarantines: any
    filters: { status?: string }
    statuses: any[]
}>()

const statusFilter = ref(props.filters.status ?? '')

function applyFilters() {
    router.get('/operations/quarantines', {
        status: statusFilter.value || undefined,
    }, { preserveState: true })
}

const statusVariants: Record<string, string> = {
    active: 'destructive',
    under_review: 'default',
    released: 'outline',
    disposed: 'secondary',
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
                        <ShieldAlert class="me-2 inline h-6 w-6" />
                        {{ t('operations.quarantines') || 'Quarantines' }}
                    </h1>
                    <p class="text-muted-foreground">{{ t('operations.quarantinesDesc') || 'Manage quarantined inventory' }}</p>
                </div>
                <Link href="/operations/quarantines/create">
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
                    </div>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{{ t('operations.quarantineNumber') || 'Quarantine #' }}</TableHead>
                                <TableHead>{{ t('common.item') }}</TableHead>
                                <TableHead>{{ t('common.batch') }}</TableHead>
                                <TableHead>{{ t('common.warehouse') }}</TableHead>
                                <TableHead class="text-end">{{ t('common.quantity') }}</TableHead>
                                <TableHead>{{ t('common.status') }}</TableHead>
                                <TableHead>{{ t('common.date') }}</TableHead>
                                <TableHead class="text-end">{{ t('common.actions') }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="quarantine in quarantines.data" :key="quarantine.id">
                                <TableCell class="font-mono font-medium">{{ quarantine.quarantine_number }}</TableCell>
                                <TableCell>{{ quarantine.item?.name }}</TableCell>
                                <TableCell>{{ quarantine.batch?.batch_number || '-' }}</TableCell>
                                <TableCell>{{ quarantine.warehouse?.name }}</TableCell>
                                <TableCell class="text-end">{{ quarantine.quantity }}</TableCell>
                                <TableCell>
                                    <Badge :variant="(statusVariants[quarantine.status] as any) || 'secondary'">
                                        {{ quarantine.status }}
                                    </Badge>
                                </TableCell>
                                <TableCell>{{ formatDate(quarantine.quarantined_at) }}</TableCell>
                                <TableCell class="text-end">
                                    <Link :href="`/operations/quarantines/${quarantine.id}`">
                                        <Button variant="ghost" size="icon">
                                            <Eye class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="!quarantines.data?.length">
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
