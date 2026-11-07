<script setup lang="ts">
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Badge } from '@/Components/ui/badge'
import { Card, CardContent, CardHeader } from '@/Components/ui/card'
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/Components/ui/table'
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/Components/ui/select'
import { Eye, Search, FileText } from 'lucide-vue-next'

const { t } = useI18n()

const props = defineProps<{
    logs: any
    filters: { event_type?: string; user_id?: string; from?: string; to?: string }
}>()

const eventType = ref(props.filters.event_type ?? '')
const dateFrom = ref(props.filters.from ?? '')
const dateTo = ref(props.filters.to ?? '')

function applyFilters() {
    router.get('/admin/audit-logs', {
        event_type: eventType.value || undefined,
        from: dateFrom.value || undefined,
        to: dateTo.value || undefined,
    }, { preserveState: true })
}

function formatDate(d: string) {
    return new Date(d).toLocaleString()
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">
                    <FileText class="me-2 inline h-6 w-6" />
                    {{ t('nav.auditLog') }}
                </h1>
                <p class="text-muted-foreground">{{ t('admin.auditTrail') || 'System audit trail' }}</p>
            </div>

            <Card>
                <CardHeader>
                    <div class="flex flex-wrap items-center gap-4">
                        <Select v-model="eventType" @update:model-value="applyFilters">
                            <SelectTrigger class="w-48">
                                <SelectValue :placeholder="t('admin.eventType') || 'Event type'" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{{ t('common.all') }}</SelectItem>
                                <SelectItem value="created">Created</SelectItem>
                                <SelectItem value="updated">Updated</SelectItem>
                                <SelectItem value="deleted">Deleted</SelectItem>
                                <SelectItem value="login">Login</SelectItem>
                                <SelectItem value="logout">Logout</SelectItem>
                            </SelectContent>
                        </Select>
                        <Input v-model="dateFrom" type="date" class="w-44" @change="applyFilters" />
                        <Input v-model="dateTo" type="date" class="w-44" @change="applyFilters" />
                    </div>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{{ t('common.date') || 'Date' }}</TableHead>
                                <TableHead>{{ t('admin.eventType') || 'Event' }}</TableHead>
                                <TableHead>{{ t('nav.users') }}</TableHead>
                                <TableHead>{{ t('admin.entity') || 'Entity' }}</TableHead>
                                <TableHead>{{ t('admin.description') || 'Description' }}</TableHead>
                                <TableHead class="text-end">{{ t('common.actions') }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="log in logs.data" :key="log.id">
                                <TableCell class="whitespace-nowrap text-xs">
                                    {{ formatDate(log.created_at) }}
                                </TableCell>
                                <TableCell>
                                    <Badge variant="outline">{{ log.event_type }}</Badge>
                                </TableCell>
                                <TableCell>{{ log.user?.name ?? 'System' }}</TableCell>
                                <TableCell class="text-xs">
                                    {{ log.auditable_type?.split('\\').pop() }}
                                    #{{ log.auditable_id }}
                                </TableCell>
                                <TableCell class="max-w-[200px] truncate text-xs text-muted-foreground">
                                    {{ log.description }}
                                </TableCell>
                                <TableCell class="text-end">
                                    <Link :href="`/admin/audit-logs/${log.id}`">
                                        <Button variant="ghost" size="icon">
                                            <Eye class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="!logs.data?.length">
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
