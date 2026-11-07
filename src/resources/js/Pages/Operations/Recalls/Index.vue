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
import { Plus, Eye, AlertTriangle } from 'lucide-vue-next'
import { ref } from 'vue'

const { t } = useI18n()

const props = defineProps<{
    recalls: any
    filters: { status?: string, recall_class?: string }
    statuses: any[]
    recallClasses: any[]
}>()

const statusFilter = ref(props.filters.status ?? '')
const classFilter = ref(props.filters.recall_class ?? '')

function applyFilters() {
    router.get('/operations/recalls', {
        status: statusFilter.value || undefined,
        recall_class: classFilter.value || undefined,
    }, { preserveState: true })
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
                        <AlertTriangle class="me-2 inline h-6 w-6" />
                        {{ t('operations.recalls') || 'Recalls' }}
                    </h1>
                    <p class="text-muted-foreground">{{ t('operations.recallsDesc') || 'Manage product recalls' }}</p>
                </div>
                <Link href="/operations/recalls/create">
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
                        <Select v-model="classFilter" @update:model-value="applyFilters">
                            <SelectTrigger class="w-48">
                                <SelectValue :placeholder="t('operations.recallClass') || 'Class'" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{{ t('common.all') }}</SelectItem>
                                <SelectItem v-for="cls in recallClasses" :key="cls.value" :value="cls.value">
                                    {{ cls.value }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{{ t('operations.recallNumber') || 'Recall #' }}</TableHead>
                                <TableHead>{{ t('common.item') }}</TableHead>
                                <TableHead>{{ t('operations.class') || 'Class' }}</TableHead>
                                <TableHead>{{ t('common.status') }}</TableHead>
                                <TableHead class="text-end">{{ t('operations.affected') || 'Affected' }}</TableHead>
                                <TableHead class="text-end">{{ t('operations.recovered') || 'Recovered' }}</TableHead>
                                <TableHead>{{ t('common.date') }}</TableHead>
                                <TableHead class="text-end">{{ t('common.actions') }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="recall in recalls.data" :key="recall.id">
                                <TableCell class="font-mono font-medium">{{ recall.recall_number }}</TableCell>
                                <TableCell>{{ recall.item?.name }}</TableCell>
                                <TableCell>
                                    <Badge :variant="(classVariants[recall.recall_class] as any) || 'secondary'">
                                        {{ recall.recall_class }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="(statusVariants[recall.status] as any) || 'secondary'">
                                        {{ recall.status }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-end">{{ recall.total_affected_qty }}</TableCell>
                                <TableCell class="text-end">{{ recall.total_recovered_qty }}</TableCell>
                                <TableCell>{{ formatDate(recall.initiated_date) }}</TableCell>
                                <TableCell class="text-end">
                                    <Link :href="`/operations/recalls/${recall.id}`">
                                        <Button variant="ghost" size="icon">
                                            <Eye class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="!recalls.data?.length">
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
