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
import { Plus, Eye, Package } from 'lucide-vue-next'
import { ref } from 'vue'

const { t } = useI18n()

const props = defineProps<{
    receivings: any
    filters: { status?: string }
}>()

const statusFilter = ref(props.filters.status ?? '')

function applyFilters() {
    router.get('/inventory/receivings', {
        status: statusFilter.value || undefined,
    }, { preserveState: true })
}

const statusVariants: Record<string, string> = {
    draft: 'secondary',
    in_progress: 'default',
    completed: 'outline',
    cancelled: 'destructive',
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
                        <Package class="me-2 inline h-6 w-6" />
                        {{ t('inventory.receiving') || 'Receiving' }}
                    </h1>
                    <p class="text-muted-foreground">{{ t('inventory.receivingDesc') || 'Manage incoming stock' }}</p>
                </div>
                <Link href="/inventory/receivings/create">
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
                                <SelectItem value="draft">Draft</SelectItem>
                                <SelectItem value="in_progress">In Progress</SelectItem>
                                <SelectItem value="completed">Completed</SelectItem>
                                <SelectItem value="cancelled">Cancelled</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{{ t('inventory.receivingNumber') || 'Receiving #' }}</TableHead>
                                <TableHead>{{ t('inventory.supplier') || 'Supplier' }}</TableHead>
                                <TableHead>{{ t('locations.warehouse') || 'Warehouse' }}</TableHead>
                                <TableHead>{{ t('common.date') || 'Date' }}</TableHead>
                                <TableHead>{{ t('common.status') }}</TableHead>
                                <TableHead class="text-end">{{ t('common.actions') }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="rcv in receivings.data" :key="rcv.id">
                                <TableCell class="font-mono font-medium">{{ rcv.receiving_number }}</TableCell>
                                <TableCell>{{ rcv.supplier_name }}</TableCell>
                                <TableCell>{{ rcv.warehouse?.name }}</TableCell>
                                <TableCell>{{ formatDate(rcv.receiving_date) }}</TableCell>
                                <TableCell>
                                    <Badge :variant="(statusVariants[rcv.status] as any) || 'secondary'">
                                        {{ rcv.status }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-end">
                                    <Link :href="`/inventory/receivings/${rcv.id}`">
                                        <Button variant="ghost" size="icon">
                                            <Eye class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="!receivings.data?.length">
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
