<script setup lang="ts">
import { ref } from 'vue'
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
    items: Array<{ id: number; name: string; sku: string }>
    batches: Array<{ id: number; batch_number: string; item_id: number }>
    warehouses: Array<{ id: number; name: string }>
}

const props = defineProps<Props>()

const form = useForm({
    item_id: '',
    batch_id: '',
    warehouse_id: '',
    epc: '',
    tid: '',
    sgtin: '',
})

function submit() {
    form.post(route('rfid.epcs.store'))
}
</script>

<template>
    <Head title="Create EPC" />

    <div class="space-y-6">
        <div class="flex items-center gap-4">
            <Link :href="route('rfid.epcs.index')">
                <Button variant="ghost" size="sm">
                    <ArrowLeft class="h-4 w-4 me-2" />
                    Back
                </Button>
            </Link>
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Create EPC</h1>
                <p class="text-muted-foreground">Add a new Electronic Product Code</p>
            </div>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>EPC Details</CardTitle>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-6 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="item_id">Item *</Label>
                            <Select v-model="form.item_id" required>
                                <SelectTrigger id="item_id">
                                    <SelectValue placeholder="Select item" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="item in items" :key="item.id" :value="item.id.toString()">
                                        {{ item.name }} ({{ item.sku }})
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.item_id" class="text-sm text-red-600">{{ form.errors.item_id }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="batch_id">Batch</Label>
                            <Select v-model="form.batch_id">
                                <SelectTrigger id="batch_id">
                                    <SelectValue placeholder="Select batch" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">None</SelectItem>
                                    <SelectItem v-for="batch in batches" :key="batch.id" :value="batch.id.toString()">
                                        {{ batch.batch_number }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.batch_id" class="text-sm text-red-600">{{ form.errors.batch_id }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="warehouse_id">Warehouse</Label>
                            <Select v-model="form.warehouse_id">
                                <SelectTrigger id="warehouse_id">
                                    <SelectValue placeholder="Select warehouse" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">None</SelectItem>
                                    <SelectItem v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id.toString()">
                                        {{ warehouse.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.warehouse_id" class="text-sm text-red-600">{{ form.errors.warehouse_id }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="epc">EPC Code *</Label>
                            <Input
                                id="epc"
                                v-model="form.epc"
                                type="text"
                                placeholder="e.g., 3018AB3C2D4E5F60"
                                class="font-mono"
                                required
                            />
                            <p v-if="form.errors.epc" class="text-sm text-red-600">{{ form.errors.epc }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="tid">TID</Label>
                            <Input
                                id="tid"
                                v-model="form.tid"
                                type="text"
                                placeholder="Tag Identifier"
                                class="font-mono"
                            />
                            <p v-if="form.errors.tid" class="text-sm text-red-600">{{ form.errors.tid }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="sgtin">SGTIN</Label>
                            <Input
                                id="sgtin"
                                v-model="form.sgtin"
                                type="text"
                                placeholder="e.g., urn:epc:id:sgtin:0614141.812345.10000"
                                class="font-mono"
                            />
                            <p v-if="form.errors.sgtin" class="text-sm text-red-600">{{ form.errors.sgtin }}</p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-4">
                        <Link :href="route('rfid.epcs.index')">
                            <Button type="button" variant="outline">Cancel</Button>
                        </Link>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Creating...' : 'Create EPC' }}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
