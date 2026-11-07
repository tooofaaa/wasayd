<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Button } from '@/Components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Badge } from '@/Components/ui/badge'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table'
import { ArrowLeft } from 'lucide-vue-next'

defineOptions({ layout: AppLayout })

const { t } = useI18n()

interface Epc {
    id: number
    epc: string
    sgtin: string | null
    tid: string | null
    status: string
    item: { id: number; name: string; sku: string } | null
    batch: { id: number; batch_number: string } | null
    warehouse: { id: number; name: string } | null
    encoded_at: string | null
    last_seen_at: string | null
    last_seen_reader: number | null
    created_at: string
}

interface Props {
    epc: Epc
    history: {
        read_events: Array<any>
        encoding_logs: Array<any>
        lifecycle: Array<{
            type: string
            timestamp: string
            description: string
            user?: string
            reader?: string
        }>
    }
}

const props = defineProps<Props>()

function getStatusColor(status: string): string {
    const colors: Record<string, string> = {
        unassigned: 'bg-gray-100 text-gray-800',
        active: 'bg-green-100 text-green-800',
        in_use: 'bg-blue-100 text-blue-800',
        decommissioned: 'bg-red-100 text-red-800',
        lost: 'bg-orange-100 text-orange-800',
        damaged: 'bg-yellow-100 text-yellow-800',
        returned: 'bg-purple-100 text-purple-800',
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
}
</script>

<template>
    <Head :title="`EPC: ${epc.epc}`" />

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <Link :href="route('rfid.epcs.index')">
                    <Button variant="ghost" size="sm">
                        <ArrowLeft class="h-4 w-4 me-2" />
                        Back
                    </Button>
                </Link>
                <div>
                    <h1 class="text-3xl font-bold tracking-tight font-mono">{{ epc.epc }}</h1>
                    <p class="text-muted-foreground">Electronic Product Code Details</p>
                </div>
            </div>
            <Badge :class="getStatusColor(epc.status)">
                {{ epc.status.replace('_', ' ').toUpperCase() }}
            </Badge>
        </div>

        <Tabs default-value="overview">
            <TabsList>
                <TabsTrigger value="overview">Overview</TabsTrigger>
                <TabsTrigger value="history">Read History</TabsTrigger>
                <TabsTrigger value="lifecycle">Lifecycle</TabsTrigger>
            </TabsList>

            <TabsContent value="overview">
                <div class="grid gap-6 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>EPC Information</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <div class="text-sm font-medium text-muted-foreground">EPC Code</div>
                                <div class="font-mono text-sm">{{ epc.epc }}</div>
                            </div>
                            <div v-if="epc.sgtin">
                                <div class="text-sm font-medium text-muted-foreground">SGTIN</div>
                                <div class="font-mono text-sm">{{ epc.sgtin }}</div>
                            </div>
                            <div v-if="epc.tid">
                                <div class="text-sm font-medium text-muted-foreground">TID</div>
                                <div class="font-mono text-sm">{{ epc.tid }}</div>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-muted-foreground">Status</div>
                                <Badge :class="getStatusColor(epc.status)">
                                    {{ epc.status.replace('_', ' ') }}
                                </Badge>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Associated Data</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div v-if="epc.item">
                                <div class="text-sm font-medium text-muted-foreground">Item</div>
                                <div class="font-medium">{{ epc.item.name }}</div>
                                <div class="text-sm text-muted-foreground">SKU: {{ epc.item.sku }}</div>
                            </div>
                            <div v-if="epc.batch">
                                <div class="text-sm font-medium text-muted-foreground">Batch</div>
                                <div>{{ epc.batch.batch_number }}</div>
                            </div>
                            <div v-if="epc.warehouse">
                                <div class="text-sm font-medium text-muted-foreground">Warehouse</div>
                                <div>{{ epc.warehouse.name }}</div>
                            </div>
                            <div v-if="epc.last_seen_at">
                                <div class="text-sm font-medium text-muted-foreground">Last Seen</div>
                                <div>{{ new Date(epc.last_seen_at).toLocaleString() }}</div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </TabsContent>

            <TabsContent value="history">
                <Card>
                    <CardHeader>
                        <CardTitle>Read Events</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Reader</TableHead>
                                    <TableHead>Event Type</TableHead>
                                    <TableHead>RSSI</TableHead>
                                    <TableHead>Read Count</TableHead>
                                    <TableHead>Timestamp</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="event in history.read_events" :key="event.id">
                                    <TableCell>{{ event.reader?.name || 'Unknown' }}</TableCell>
                                    <TableCell>{{ event.event_type }}</TableCell>
                                    <TableCell>{{ event.rssi || '-' }}</TableCell>
                                    <TableCell>{{ event.read_count }}</TableCell>
                                    <TableCell>{{ new Date(event.first_read_at).toLocaleString() }}</TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                        <div v-if="history.read_events.length === 0" class="py-8 text-center text-muted-foreground">
                            No read events recorded
                        </div>
                    </CardContent>
                </Card>
            </TabsContent>

            <TabsContent value="lifecycle">
                <Card>
                    <CardHeader>
                        <CardTitle>Lifecycle Timeline</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-for="(event, index) in history.lifecycle" :key="index" class="flex gap-4">
                                <div class="flex flex-col items-center">
                                    <div class="h-3 w-3 rounded-full bg-primary"></div>
                                    <div v-if="index < history.lifecycle.length - 1" class="w-px flex-1 bg-border"></div>
                                </div>
                                <div class="pb-8">
                                    <div class="font-medium">{{ event.description }}</div>
                                    <div class="text-sm text-muted-foreground">
                                        {{ new Date(event.timestamp).toLocaleString() }}
                                    </div>
                                    <div v-if="event.user" class="text-sm text-muted-foreground">
                                        By: {{ event.user }}
                                    </div>
                                    <div v-if="event.reader" class="text-sm text-muted-foreground">
                                        Reader: {{ event.reader }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-if="history.lifecycle.length === 0" class="py-8 text-center text-muted-foreground">
                            No lifecycle events
                        </div>
                    </CardContent>
                </Card>
            </TabsContent>
        </Tabs>
    </div>
</template>
