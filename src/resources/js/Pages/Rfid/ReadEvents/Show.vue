<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Button } from '@/Components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Badge } from '@/Components/ui/badge'
import { ArrowLeft } from 'lucide-vue-next'

defineOptions({ layout: AppLayout })

const { t } = useI18n()

interface ReadEvent {
    id: number
    epc_code: string
    reader: { id: number; name: string } | null
    epc: {
        id: number
        epc: string
        item: { id: number; name: string; sku: string } | null
        batch: { id: number; batch_number: string } | null
        warehouse: { id: number; name: string } | null
    } | null
    event_type: string
    rssi: number | null
    antenna_port: number | null
    read_count: number
    first_read_at: string
    last_read_at: string
    session_id: string | null
    processed: boolean
}

interface Props {
    event: ReadEvent
}

const props = defineProps<Props>()
</script>

<template>
    <Head title="Read Event Details" />

    <div class="space-y-6">
        <div class="flex items-center gap-4">
            <Link :href="route('rfid.read-events.index')">
                <Button variant="ghost" size="sm">
                    <ArrowLeft class="h-4 w-4 me-2" />
                    Back
                </Button>
            </Link>
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Read Event Details</h1>
                <p class="text-muted-foreground font-mono">{{ event.epc_code }}</p>
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardTitle>Event Information</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div>
                        <div class="text-sm font-medium text-muted-foreground">EPC Code</div>
                        <div class="font-mono">{{ event.epc_code }}</div>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-muted-foreground">Reader</div>
                        <div>{{ event.reader?.name || 'Unknown' }}</div>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-muted-foreground">Event Type</div>
                        <Badge variant="outline">{{ event.event_type }}</Badge>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-muted-foreground">Read Count</div>
                        <div>{{ event.read_count }}</div>
                    </div>
                    <div v-if="event.rssi">
                        <div class="text-sm font-medium text-muted-foreground">RSSI</div>
                        <div>{{ event.rssi }} dBm</div>
                    </div>
                    <div v-if="event.antenna_port">
                        <div class="text-sm font-medium text-muted-foreground">Antenna Port</div>
                        <div>{{ event.antenna_port }}</div>
                    </div>
                    <div v-if="event.session_id">
                        <div class="text-sm font-medium text-muted-foreground">Session ID</div>
                        <div class="font-mono text-sm">{{ event.session_id }}</div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Timing & Status</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div>
                        <div class="text-sm font-medium text-muted-foreground">First Read</div>
                        <div>{{ new Date(event.first_read_at).toLocaleString() }}</div>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-muted-foreground">Last Read</div>
                        <div>{{ new Date(event.last_read_at).toLocaleString() }}</div>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-muted-foreground">Processing Status</div>
                        <Badge :class="event.processed ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'">
                            {{ event.processed ? 'Processed' : 'Pending' }}
                        </Badge>
                    </div>
                </CardContent>
            </Card>
        </div>

        <Card v-if="event.epc">
            <CardHeader>
                <CardTitle>Associated EPC</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
                <div v-if="event.epc.item">
                    <div class="text-sm font-medium text-muted-foreground">Item</div>
                    <div class="font-medium">{{ event.epc.item.name }}</div>
                    <div class="text-sm text-muted-foreground">SKU: {{ event.epc.item.sku }}</div>
                </div>
                <div v-if="event.epc.batch">
                    <div class="text-sm font-medium text-muted-foreground">Batch</div>
                    <div>{{ event.epc.batch.batch_number }}</div>
                </div>
                <div v-if="event.epc.warehouse">
                    <div class="text-sm font-medium text-muted-foreground">Warehouse</div>
                    <div>{{ event.epc.warehouse.name }}</div>
                </div>
                <div class="pt-4">
                    <Link :href="route('rfid.epcs.show', event.epc.id)">
                        <Button variant="outline">View EPC Details</Button>
                    </Link>
                </div>
            </CardContent>
        </Card>

        <Card v-else>
            <CardContent class="py-8 text-center text-muted-foreground">
                This EPC is not registered in the system
            </CardContent>
        </Card>
    </div>
</template>
