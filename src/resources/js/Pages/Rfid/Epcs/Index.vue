<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Card, CardContent, CardHeader } from '@/Components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table'
import { Badge } from '@/Components/ui/badge'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
import { Search, Plus, Eye, Trash2 } from 'lucide-vue-next'

defineOptions({ layout: AppLayout })

const { t } = useI18n()

interface Epc {
    id: number
    epc: string
    sgtin: string | null
    status: string
    item: { id: number; name: string; sku: string } | null
    batch: { id: number; batch_number: string } | null
    warehouse: { id: number; name: string } | null
    last_seen_at: string | null
}

interface Props {
    epcs: {
        data: Epc[]
        current_page: number
        last_page: number
        per_page: number
        total: number
    }
    warehouses: Array<{ id: number; name: string }>
    items: Array<{ id: number; name: string; sku: string }>
    statuses: Array<{ value: string; label: string }>
    filters: {
        search: string | null
        status: string | null
        warehouse_id: number | null
        item_id: number | null
    }
}

const props = defineProps<Props>()

const search = ref(props.filters.search || '')
const status = ref(props.filters.status || '')
const warehouseId = ref(props.filters.warehouse_id?.toString() || '')
const itemId = ref(props.filters.item_id?.toString() || '')

function performSearch() {
    router.get(route('rfid.epcs.index'), {
        search: search.value,
        status: status.value || undefined,
        warehouse_id: warehouseId.value || undefined,
        item_id: itemId.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    })
}

function deleteEpc(epc: Epc) {
    if (confirm('Are you sure you want to delete this EPC?')) {
        router.delete(route('rfid.epcs.destroy', epc.id))
    }
}

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
    <Head title="EPCs" />

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">EPCs</h1>
                <p class="text-muted-foreground">Manage RFID Electronic Product Codes</p>
            </div>
            <div class="flex gap-2">
                <Link :href="route('rfid.epcs.create')">
                    <Button variant="outline">
                        <Plus class="h-4 w-4 me-2" />
                        Create EPC
                    </Button>
                </Link>
            </div>
        </div>

        <Card>
            <CardHeader>
                <div class="flex flex-col gap-4 md:flex-row">
                    <div class="relative flex-1">
                        <Search class="absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            v-model="search"
                            placeholder="Search by EPC or SGTIN..."
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
                    <Select v-model="itemId" @update:model-value="performSearch">
                        <SelectTrigger class="w-full md:w-48">
                            <SelectValue placeholder="All Items" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Items</SelectItem>
                            <SelectItem v-for="item in items" :key="item.id" :value="item.id.toString()">
                                {{ item.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </CardHeader>
            <CardContent>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>EPC Code</TableHead>
                            <TableHead>Item</TableHead>
                            <TableHead>Batch</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Warehouse</TableHead>
                            <TableHead>Last Seen</TableHead>
                            <TableHead class="text-end">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="epc in epcs.data" :key="epc.id">
                            <TableCell class="font-mono text-sm">{{ epc.epc }}</TableCell>
                            <TableCell>
                                <div v-if="epc.item">
                                    <div class="font-medium">{{ epc.item.name }}</div>
                                    <div class="text-sm text-muted-foreground">{{ epc.item.sku }}</div>
                                </div>
                                <span v-else class="text-muted-foreground">-</span>
                            </TableCell>
                            <TableCell>
                                {{ epc.batch?.batch_number || '-' }}
                            </TableCell>
                            <TableCell>
                                <Badge :class="getStatusColor(epc.status)">
                                    {{ epc.status.replace('_', ' ') }}
                                </Badge>
                            </TableCell>
                            <TableCell>{{ epc.warehouse?.name || '-' }}</TableCell>
                            <TableCell>
                                <span v-if="epc.last_seen_at" class="text-sm">
                                    {{ new Date(epc.last_seen_at).toLocaleString() }}
                                </span>
                                <span v-else class="text-muted-foreground">-</span>
                            </TableCell>
                            <TableCell class="text-end">
                                <div class="flex justify-end gap-2">
                                    <Link :href="route('rfid.epcs.show', epc.id)">
                                        <Button variant="ghost" size="sm">
                                            <Eye class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                    <Button variant="ghost" size="sm" @click="deleteEpc(epc)">
                                        <Trash2 class="h-4 w-4 text-red-600" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <div v-if="epcs.data.length === 0" class="py-8 text-center text-muted-foreground">
                    No EPCs found
                </div>
            </CardContent>
        </Card>
    </div>
</template>
