<script setup lang="ts">
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Input } from '@/Components/ui/input'
import { Card, CardContent, CardHeader } from '@/Components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table'
import { Badge } from '@/Components/ui/badge'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'

defineOptions({ layout: AppLayout })

const { t } = useI18n()

interface EncodingLog {
    id: number
    epc: { id: number; epc: string } | null
    encoded_by_user: { id: number; name: string } | null
    reader: { id: number; name: string } | null
    status: string
    error_message: string | null
    created_at: string
}

interface Props {
    logs: {
        data: EncodingLog[]
        current_page: number
        last_page: number
        per_page: number
        total: number
    }
    filters: {
        status: string | null
        date_from: string | null
        date_to: string | null
    }
}

const props = defineProps<Props>()

const status = ref(props.filters.status || '')
const dateFrom = ref(props.filters.date_from || '')
const dateTo = ref(props.filters.date_to || '')

function performSearch() {
    router.get(route('rfid.encoding-logs.index'), {
        status: status.value || undefined,
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    })
}

function getStatusColor(status: string): string {
    return status === 'success'
        ? 'bg-green-100 text-green-800'
        : 'bg-red-100 text-red-800'
}
</script>

<template>
    <Head title="Encoding Logs" />

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Encoding Logs</h1>
                <p class="text-muted-foreground">RFID tag encoding activity log</p>
            </div>
        </div>

        <Card>
            <CardHeader>
                <div class="flex flex-col gap-4 md:flex-row">
                    <Select v-model="status" @update:model-value="performSearch">
                        <SelectTrigger class="w-full md:w-48">
                            <SelectValue placeholder="All Statuses" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Statuses</SelectItem>
                            <SelectItem value="success">Success</SelectItem>
                            <SelectItem value="failed">Failed</SelectItem>
                        </SelectContent>
                    </Select>
                    <Input
                        v-model="dateFrom"
                        type="date"
                        placeholder="From Date"
                        @change="performSearch"
                    />
                    <Input
                        v-model="dateTo"
                        type="date"
                        placeholder="To Date"
                        @change="performSearch"
                    />
                </div>
            </CardHeader>
            <CardContent>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>EPC</TableHead>
                            <TableHead>Encoded By</TableHead>
                            <TableHead>Reader</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Error</TableHead>
                            <TableHead>Timestamp</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="log in logs.data" :key="log.id">
                            <TableCell class="font-mono text-sm">
                                {{ log.epc?.epc || 'N/A' }}
                            </TableCell>
                            <TableCell>{{ log.encoded_by_user?.name || 'System' }}</TableCell>
                            <TableCell>{{ log.reader?.name || '-' }}</TableCell>
                            <TableCell>
                                <Badge :class="getStatusColor(log.status)">
                                    {{ log.status }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <span v-if="log.error_message" class="text-sm text-red-600">
                                    {{ log.error_message }}
                                </span>
                                <span v-else class="text-muted-foreground">-</span>
                            </TableCell>
                            <TableCell>{{ new Date(log.created_at).toLocaleString() }}</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <div v-if="logs.data.length === 0" class="py-8 text-center text-muted-foreground">
                    No encoding logs found
                </div>
            </CardContent>
        </Card>
    </div>
</template>
