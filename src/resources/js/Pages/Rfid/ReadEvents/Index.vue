<script setup lang="ts">
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Card, CardContent, CardHeader } from '@/Components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table'
import { Badge } from '@/Components/ui/badge'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
import { Eye } from 'lucide-vue-next'

defineOptions({ layout: AppLayout })

const { t } = useI18n()

interface ReadEvent {
    id: number
    epc_code: string
    reader: { id: number; name: string } | null
    epc: { id: number; epc: string } | null
    event_type: string
    rssi: number | null
    antenna_port: number | null
    read_count: number
    first_read_at: string
    processed: boolean
}

interface Props {
    events: {
        data: ReadEvent[]
        current_page: number
        last_page: number
        per_page: number
        total: number
    }
    readers: Array<{ id: number; name: string }>
    eventTypes: Array<{ value: string; label: string }>
    filters: {
        reader_id: number | null
        event_type: string | null
        date_from: string | null
        date_to: string | null
        processed: string | null
    }
}

const props = defineProps<Props>()

const readerId = ref(props.filters.reader_id?.toString() || '')
const eventType = ref(props.filters.event_type || '')
const dateFrom = ref(props.filters.date_from || '')
const dateTo = ref(props.filters.date_to || '')
const processed = ref(props.filters.processed || '')

function performSearch() {
    router.get(route('rfid.read-events.index'), {
        reader_id: readerId.value || undefined,
        event_type: eventType.value || undefined,
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
        processed: processed.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    })
}
</script>

<template>
    <Head title="RFID Read Events" />

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Read Events</h1>
                <p class="text-muted-foreground">RFID tag read event log</p>
            </div>
        </div>

        <Card>
            <CardHeader>
                <div class="flex flex-col gap-4 md:flex-row">
                    <Select v-model="readerId" @update:model-value="performSearch">
                        <SelectTrigger class="w-full md:w-48">
                            <SelectValue placeholder="All Readers" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Readers</SelectItem>
                            <SelectItem v-for="r in readers" :key="r.id" :value="r.id.toString()">
                                {{ r.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <Select v-model="eventType" @update:model-value="performSearch">
                        <SelectTrigger class="w-full md:w-48">
                            <SelectValue placeholder="All Event Types" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Event Types</SelectItem>
                            <SelectItem v-for="et in eventTypes" :key="et.value" :value="et.value">
                                {{ et.label }}
                            </SelectItem>
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
                    <Select v-model="processed" @update:model-value="performSearch">
                        <SelectTrigger class="w-full md:w-48">
                            <SelectValue placeholder="All" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All</SelectItem>
                            <SelectItem value="true">Processed</SelectItem>
                            <SelectItem value="false">Unprocessed</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </CardHeader>
            <CardContent>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Reader</TableHead>
                            <TableHead>EPC Code</TableHead>
                            <TableHead>Event Type</TableHead>
                            <TableHead>RSSI</TableHead>
                            <TableHead>Antenna</TableHead>
                            <TableHead>Reads</TableHead>
                            <TableHead>Timestamp</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="text-end">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="event in events.data" :key="event.id">
                            <TableCell>{{ event.reader?.name || 'Unknown' }}</TableCell>
                            <TableCell class="font-mono text-sm">{{ event.epc_code }}</TableCell>
                            <TableCell>
                                <Badge variant="outline">{{ event.event_type }}</Badge>
                            </TableCell>
                            <TableCell>{{ event.rssi || '-' }}</TableCell>
                            <TableCell>{{ event.antenna_port || '-' }}</TableCell>
                            <TableCell>{{ event.read_count }}</TableCell>
                            <TableCell>{{ new Date(event.first_read_at).toLocaleString() }}</TableCell>
                            <TableCell>
                                <Badge :class="event.processed ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'">
                                    {{ event.processed ? 'Processed' : 'Pending' }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-end">
                                <Link :href="route('rfid.read-events.show', event.id)">
                                    <Button variant="ghost" size="sm">
                                        <Eye class="h-4 w-4" />
                                    </Button>
                                </Link>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <div v-if="events.data.length === 0" class="py-8 text-center text-muted-foreground">
                    No read events found
                </div>
            </CardContent>
        </Card>
    </div>
</template>
