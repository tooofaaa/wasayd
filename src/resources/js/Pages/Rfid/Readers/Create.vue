<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
import { ArrowLeft } from 'lucide-vue-next'

defineOptions({ layout: AppLayout })

const { t } = useI18n()

interface Props {
    warehouses: Array<{ id: number; name: string }>
    zones: Array<{ id: number; name: string; warehouse_id: number }>
    statuses: Array<{ value: string; label: string }>
}

const props = defineProps<Props>()

const form = useForm({
    name: '',
    reader_id: '',
    model: '',
    manufacturer: '',
    ip_address: '',
    port: '',
    warehouse_id: '',
    zone_id: '',
    status: 'offline',
    firmware_version: '',
})

const availableZones = computed(() => {
    if (!form.warehouse_id) return []
    return props.zones.filter(z => z.warehouse_id.toString() === form.warehouse_id)
})

function submit() {
    form.post(route('rfid.readers.store'))
}
</script>

<template>
    <Head title="Create RFID Reader" />

    <div class="space-y-6">
        <div class="flex items-center gap-4">
            <Link :href="route('rfid.readers.index')">
                <Button variant="ghost" size="sm">
                    <ArrowLeft class="h-4 w-4 me-2" />
                    Back
                </Button>
            </Link>
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Create RFID Reader</h1>
                <p class="text-muted-foreground">Add a new RFID reader device</p>
            </div>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Reader Details</CardTitle>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-6 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="name">Name *</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="e.g., Receiving Gate Reader 1"
                                required
                            />
                            <p v-if="form.errors.name" class="text-sm text-red-600">{{ form.errors.name }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="reader_id">Reader ID *</Label>
                            <Input
                                id="reader_id"
                                v-model="form.reader_id"
                                type="text"
                                placeholder="e.g., RFID-001"
                                class="font-mono"
                                required
                            />
                            <p v-if="form.errors.reader_id" class="text-sm text-red-600">{{ form.errors.reader_id }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="model">Model</Label>
                            <Input
                                id="model"
                                v-model="form.model"
                                type="text"
                                placeholder="e.g., FX9600"
                            />
                            <p v-if="form.errors.model" class="text-sm text-red-600">{{ form.errors.model }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="manufacturer">Manufacturer</Label>
                            <Input
                                id="manufacturer"
                                v-model="form.manufacturer"
                                type="text"
                                placeholder="e.g., Zebra"
                            />
                            <p v-if="form.errors.manufacturer" class="text-sm text-red-600">{{ form.errors.manufacturer }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="ip_address">IP Address *</Label>
                            <Input
                                id="ip_address"
                                v-model="form.ip_address"
                                type="text"
                                placeholder="e.g., 192.168.1.100"
                                class="font-mono"
                                required
                            />
                            <p v-if="form.errors.ip_address" class="text-sm text-red-600">{{ form.errors.ip_address }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="port">Port</Label>
                            <Input
                                id="port"
                                v-model="form.port"
                                type="number"
                                placeholder="e.g., 5084"
                            />
                            <p v-if="form.errors.port" class="text-sm text-red-600">{{ form.errors.port }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="warehouse_id">Warehouse *</Label>
                            <Select v-model="form.warehouse_id" required>
                                <SelectTrigger id="warehouse_id">
                                    <SelectValue placeholder="Select warehouse" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id.toString()">
                                        {{ warehouse.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.warehouse_id" class="text-sm text-red-600">{{ form.errors.warehouse_id }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="zone_id">Zone</Label>
                            <Select v-model="form.zone_id" :disabled="!form.warehouse_id">
                                <SelectTrigger id="zone_id">
                                    <SelectValue placeholder="Select zone" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">None</SelectItem>
                                    <SelectItem v-for="zone in availableZones" :key="zone.id" :value="zone.id.toString()">
                                        {{ zone.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.zone_id" class="text-sm text-red-600">{{ form.errors.zone_id }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="status">Status *</Label>
                            <Select v-model="form.status" required>
                                <SelectTrigger id="status">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="s in statuses" :key="s.value" :value="s.value">
                                        {{ s.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.status" class="text-sm text-red-600">{{ form.errors.status }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="firmware_version">Firmware Version</Label>
                            <Input
                                id="firmware_version"
                                v-model="form.firmware_version"
                                type="text"
                                placeholder="e.g., 3.12.5"
                            />
                            <p v-if="form.errors.firmware_version" class="text-sm text-red-600">{{ form.errors.firmware_version }}</p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-4">
                        <Link :href="route('rfid.readers.index')">
                            <Button type="button" variant="outline">Cancel</Button>
                        </Link>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Creating...' : 'Create Reader' }}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
