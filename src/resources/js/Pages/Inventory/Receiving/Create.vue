<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Textarea } from '@/Components/ui/textarea'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/Components/ui/select'

const { t } = useI18n()

defineProps<{
    warehouses: Array<{ id: number; name: string }>
}>()

const form = useForm({
    warehouse_id: null as number | null,
    supplier_name: '',
    supplier_reference: '',
    purchase_order_number: '',
    receiving_date: new Date().toISOString().split('T')[0],
    notes: '',
})

function submit() {
    form.post('/inventory/receivings')
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="mx-auto max-w-2xl space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold tracking-tight">{{ t('inventory.newReceiving') || 'New Receiving' }}</h1>
                <Link href="/inventory/receivings">
                    <Button variant="outline">{{ t('common.back') }}</Button>
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>{{ t('inventory.receivingDetails') || 'Receiving Details' }}</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label>{{ t('locations.warehouse') || 'Warehouse' }} *</Label>
                            <Select v-model="form.warehouse_id">
                                <SelectTrigger :class="{ 'border-destructive': form.errors.warehouse_id }">
                                    <SelectValue placeholder="Select warehouse" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="wh in warehouses" :key="wh.id" :value="wh.id">
                                        {{ wh.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.warehouse_id" class="text-sm text-destructive">{{ form.errors.warehouse_id }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label>{{ t('inventory.supplier') || 'Supplier' }} *</Label>
                                <Input v-model="form.supplier_name" :class="{ 'border-destructive': form.errors.supplier_name }" />
                                <p v-if="form.errors.supplier_name" class="text-sm text-destructive">{{ form.errors.supplier_name }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label>{{ t('inventory.supplierRef') || 'Supplier Reference' }}</Label>
                                <Input v-model="form.supplier_reference" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label>{{ t('inventory.poNumber') || 'PO Number' }}</Label>
                                <Input v-model="form.purchase_order_number" />
                            </div>
                            <div class="space-y-2">
                                <Label>{{ t('common.date') || 'Date' }} *</Label>
                                <Input v-model="form.receiving_date" type="date" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label>{{ t('common.notes') || 'Notes' }}</Label>
                            <Textarea v-model="form.notes" rows="3" />
                        </div>
                    </CardContent>
                </Card>

                <div class="flex justify-end gap-2">
                    <Link href="/inventory/receivings">
                        <Button type="button" variant="outline">{{ t('common.cancel') }}</Button>
                    </Link>
                    <Button type="submit" :disabled="form.processing">{{ t('common.save') }}</Button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
