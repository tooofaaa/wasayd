<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Button } from '@/Components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Badge } from '@/Components/ui/badge'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table'
import { ArrowLeft, Circle } from 'lucide-vue-next'

defineOptions({ layout: AppLayout })

const { t } = useI18n()

interface Reader {
    id: number
    name: string
    reader_id: string
    model: string | null
    manufacturer: string | null
    ip_address: string
    port: number | null
    status: string
    warehouse: { id: number; name: string } | null
    zone: { id: number; name: string } | null
    last_heartbeat_at: string | null
    firmware_version: string | null
    configuration: Record<string, any> | null
}

interface Props {
    reader: Reader
    recentReads: Array<{
        id: number
        epc: { id: number; epc: string } | null
        epc_code: string
        event_type: string
        rssi: number | null
        read_count: number
        first_read_at: string
    }>
    isOnline: boolean
}

const props = defineProps<Props>()

function getStatusColor(status: string): string {
    const colors: Record<string, string> = {
        online: 'bg-green-100 text-green-800',
        offline: 'bg-red-100 text-red-800',
        maintenance: 'bg-yellow-100 text-yellow-800',
        decommissioned: 'bg-gray-100 text-gray-800',
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
}
</script>

<template>
    <Head :title="`Reader: ${reader.name}`" />

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <Link :href="route('rfid.readers.index')">
                    <Button variant="ghost" size="sm">
                        <ArrowLeft class="h-4 w-4 me-2" />
                        Back
                    </Button>
                </Link>
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">{{ reader.name }}</h1>
                    <p class="text-muted-foreground font-mono">{{ reader.reader_id }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <Badge :class="getStatusColor(reader.status)">
                    {{ reader.status.toUpperCase() }}
                </Badge>
                <Badge v-if="isOnline" variant="outline" class="border-green-500 text-green-700">
                    <Circle class="h-2 w-2 fill-green-500 me-2" />
                    Live
                </Badge>
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardTitle>Reader Information</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div>
                        <div class="text-sm font-medium text-muted-foreground">Reader ID</div>
                        <div class="font-mono">{{ reader.reader_id }}</div>
                    </div>
                    <div v-if="reader.model">
                        <div class="text-sm font-medium text-muted-foreground">Model</div>
                        <div>{{ reader.model }}</div>
                    </div>
                    <div v-if="reader.manufacturer">
                        <div class="text-sm font-medium text-muted-foreground">Manufacturer</div>
                        <div>{{ reader.manufacturer }}</div>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-muted-foreground">IP Address</div>
                        <div class="font-mono">{{ reader.ip_address }}{{ reader.port ? ':' + reader.port : '' }}</div>
                    </div>
                    <div v-if="reader.firmware_version">
                        <div class="text-sm font-medium text-muted-foreground">Firmware Version</div>
                        <div>{{ reader.firmware_version }}</div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Location & Status</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div v-if="reader.warehouse">
                        <div class="text-sm font-medium text-muted-foreground">Warehouse</div>
                        <div>{{ reader.warehouse.name }}</div>
                    </div>
                    <div v-if="reader.zone">
                        <div class="text-sm font-medium text-muted-foreground">Zone</div>
                        <div>{{ reader.zone.name }}</div>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-muted-foreground">Status</div>
                        <Badge :class="getStatusColor(reader.status)">
                            {{ reader.status }}
                        </Badge>
                    </div>
                    <div v-if="reader.last_heartbeat_at">
                        <div class="text-sm font-medium text-muted-foreground">Last Heartbeat</div>
                        <div>{{ new Date(reader.last_heartbeat_at).toLocaleString() }}</div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <Card v-if="reader.configuration">
            <CardHeader>
                <CardTitle>Configuration</CardTitle>
            </CardHeader>
            <CardContent>
                <pre class="text-sm bg-muted p-4 rounded-lg overflow-x-auto">{{ JSON.stringify(reader.configuration, null, 2) }}</pre>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Recent Read Events</CardTitle>
            </CardHeader>
            <CardContent>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>EPC Code</TableHead>
                            <TableHead>Event Type</TableHead>
                            <TableHead>RSSI</TableHead>
                            <TableHead>Read Count</TableHead>
                            <TableHead>Timestamp</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="read in recentReads" :key="read.id">
                            <TableCell class="font-mono text-sm">{{ read.epc_code }}</TableCell>
                            <TableCell>{{ read.event_type }}</TableCell>
                            <TableCell>{{ read.rssi || '-' }}</TableCell>
                            <TableCell>{{ read.read_count }}</TableCell>
                            <TableCell>{{ new Date(read.first_read_at).toLocaleString() }}</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
                <div v-if="recentReads.length === 0" class="py-8 text-center text-muted-foreground">
                    No recent read events
                </div>
            </CardContent>
        </Card>
    </div>
</template>
