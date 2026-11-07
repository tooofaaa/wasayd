<script setup lang="ts">
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Card, CardContent, CardHeader } from '@/Components/ui/card'
import { Badge } from '@/Components/ui/badge'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
import { Search, Plus, Eye, Pencil, Trash2 } from 'lucide-vue-next'

defineOptions({ layout: AppLayout })

const { t } = useI18n()

interface Reader {
    id: number
    name: string
    reader_id: string
    ip_address: string
    status: string
    warehouse: { id: number; name: string } | null
    zone: { id: number; name: string } | null
    last_heartbeat_at: string | null
}

interface Props {
    readers: {
        data: Reader[]
        current_page: number
        last_page: number
        per_page: number
        total: number
    }
    warehouses: Array<{ id: number; name: string }>
    statuses: Array<{ value: string; label: string; color: string }>
    filters: {
        search: string | null
        status: string | null
        warehouse_id: number | null
    }
}

const props = defineProps<Props>()

const search = ref(props.filters.search || '')
const status = ref(props.filters.status || '')
const warehouseId = ref(props.filters.warehouse_id?.toString() || '')

function performSearch() {
    router.get(route('rfid.readers.index'), {
        search: search.value,
        status: status.value || undefined,
        warehouse_id: warehouseId.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    })
}

function deleteReader(reader: Reader) {
    if (confirm('Are you sure you want to delete this reader?')) {
        router.delete(route('rfid.readers.destroy', reader.id))
    }
}

function getStatusDotColor(status: string): string {
    const colors: Record<string, string> = {
        online: 'bg-green-500',
        offline: 'bg-red-500',
        maintenance: 'bg-yellow-500',
        decommissioned: 'bg-gray-400',
    }
    return colors[status] || 'bg-gray-400'
}

function isRecentHeartbeat(lastHeartbeat: string | null): boolean {
    if (!lastHeartbeat) return false
    const fiveMinutesAgo = new Date(Date.now() - 5 * 60 * 1000)
    return new Date(lastHeartbeat) > fiveMinutesAgo
}
</script>

<template>
    <Head title="RFID Readers" />

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">RFID Readers</h1>
                <p class="text-muted-foreground">Manage RFID reader devices</p>
            </div>
            <Link :href="route('rfid.readers.create')">
                <Button>
                    <Plus class="h-4 w-4 me-2" />
                    Add Reader
                </Button>
            </Link>
        </div>

        <Card>
            <CardHeader>
                <div class="flex flex-col gap-4 md:flex-row">
                    <div class="relative flex-1">
                        <Search class="absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            v-model="search"
                            placeholder="Search by name, ID, or IP..."
                            class="ps-9"
                            @keyup.enter="performSearch"
                        />
                    </div>
                    <Select v-model="status" @update:model-value="performSearch">
                        <SelectTrigger class="w-full md:w-48">
                            <SelectValue placeholder="All Statuses" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Statuses</SelectItem>
                            <SelectItem v-for="s in statuses" :key="s.value" :value="s.value">
                                {{ s.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <Select v-model="warehouseId" @update:model-value="performSearch">
                        <SelectTrigger class="w-full md:w-48">
                            <SelectValue placeholder="All Warehouses" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Warehouses</SelectItem>
                            <SelectItem v-for="w in warehouses" :key="w.id" :value="w.id.toString()">
                                {{ w.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </CardHeader>
            <CardContent>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <Card v-for="reader in readers.data" :key="reader.id" class="relative">
                        <CardHeader>
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="h-3 w-3 rounded-full"
                                        :class="[
                                            getStatusDotColor(reader.status),
                                            isRecentHeartbeat(reader.last_heartbeat_at) ? 'animate-pulse' : ''
                                        ]"
                                    ></div>
                                    <div>
                                        <h3 class="font-semibold">{{ reader.name }}</h3>
                                        <p class="text-sm text-muted-foreground font-mono">{{ reader.reader_id }}</p>
                                    </div>
                                </div>
                                <Badge variant="outline">{{ reader.status }}</Badge>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground">IP Address:</span>
                                    <span class="font-mono">{{ reader.ip_address }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground">Warehouse:</span>
                                    <span>{{ reader.warehouse?.name || '-' }}</span>
                                </div>
                                <div v-if="reader.zone" class="flex justify-between">
                                    <span class="text-muted-foreground">Zone:</span>
                                    <span>{{ reader.zone.name }}</span>
                                </div>
                                <div v-if="reader.last_heartbeat_at" class="flex justify-between">
                                    <span class="text-muted-foreground">Last Heartbeat:</span>
                                    <span>{{ new Date(reader.last_heartbeat_at).toLocaleTimeString() }}</span>
                                </div>
                            </div>
                            <div class="mt-4 flex gap-2">
                                <Link :href="route('rfid.readers.show', reader.id)" class="flex-1">
                                    <Button variant="outline" size="sm" class="w-full">
                                        <Eye class="h-4 w-4 me-2" />
                                        View
                                    </Button>
                                </Link>
                                <Link :href="route('rfid.readers.edit', reader.id)">
                                    <Button variant="outline" size="sm">
                                        <Pencil class="h-4 w-4" />
                                    </Button>
                                </Link>
                                <Button variant="outline" size="sm" @click="deleteReader(reader)">
                                    <Trash2 class="h-4 w-4 text-red-600" />
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div v-if="readers.data.length === 0" class="py-8 text-center text-muted-foreground">
                    No RFID readers found
                </div>
            </CardContent>
        </Card>
    </div>
</template>
