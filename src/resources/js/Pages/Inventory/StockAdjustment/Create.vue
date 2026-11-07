<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Button } from '@/Components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Label } from '@/Components/ui/label'
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/Components/ui/select'
import { Input } from '@/Components/ui/input'
import { Textarea } from '@/Components/ui/textarea'
import { ArrowLeft, Save } from 'lucide-vue-next'

const { t } = useI18n()

const props = defineProps<{
    warehouses: any[]
    items: any[]
    adjustmentTypes: any[]
    stockCount?: any
}>()

const form = useForm({
    stock_count_id: props.stockCount?.id || null,
    adjustment_type: props.stockCount ? 'count_variance' : 'manual',
    reason: '',
    notes: '',
    lines: props.stockCount ? [] : [
        {
            item_id: '',
            warehouse_id: '',
            batch_id: null,
            bin_id: null,
            system_quantity: 0,
            adjusted_quantity: 0,
            reason: '',
        }
    ],
})

function addLine() {
    form.lines.push({
        item_id: '',
        warehouse_id: '',
        batch_id: null,
        bin_id: null,
        system_quantity: 0,
        adjusted_quantity: 0,
        reason: '',
    })
}

function removeLine(index: number) {
    form.lines.splice(index, 1)
}

function submit() {
    form.post('/inventory/stock-adjustments')
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        {{ t('inventory.createAdjustment') || 'Create Stock Adjustment' }}
                    </h1>
                    <p class="text-muted-foreground">{{ t('inventory.createAdjustmentDesc') || 'Adjust inventory quantities' }}</p>
                </div>
                <Button variant="outline" as="a" href="/inventory/stock-adjustments">
                    <ArrowLeft class="me-2 h-4 w-4" />
                    {{ t('common.back') }}
                </Button>
            </div>

            <form @submit.prevent="submit">
                <div class="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>{{ t('inventory.adjustmentDetails') || 'Adjustment Details' }}</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="adjustment_type">{{ t('inventory.adjustmentType') || 'Type' }}</Label>
                                    <Select v-model="form.adjustment_type" :disabled="!!stockCount" required>
                                        <SelectTrigger id="adjustment_type">
                                            <SelectValue :placeholder="t('common.select')" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="type in adjustmentTypes" :key="type.value" :value="type.value">
                                                {{ type.value }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="reason">{{ t('common.reason') }}</Label>
                                <Textarea id="reason" v-model="form.reason" rows="2" required />
                            </div>

                            <div class="space-y-2">
                                <Label for="notes">{{ t('common.notes') }}</Label>
                                <Textarea id="notes" v-model="form.notes" rows="2" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card v-if="!stockCount">
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <CardTitle>{{ t('inventory.adjustmentLines') || 'Lines' }}</CardTitle>
                                <Button type="button" variant="outline" size="sm" @click="addLine">
                                    {{ t('common.addLine') || 'Add Line' }}
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <Card v-for="(line, index) in form.lines" :key="index" class="border-2">
                                <CardContent class="space-y-4 pt-6">
                                    <div class="flex justify-between">
                                        <h4 class="font-medium">{{ t('common.line') }} {{ index + 1 }}</h4>
                                        <Button
                                            v-if="form.lines.length > 1"
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            @click="removeLine(index)"
                                        >
                                            {{ t('common.remove') }}
                                        </Button>
                                    </div>

                                    <div class="grid gap-4 md:grid-cols-2">
                                        <div class="space-y-2">
                                            <Label>{{ t('items.item') }}</Label>
                                            <Select v-model="line.item_id" required>
                                                <SelectTrigger>
                                                    <SelectValue :placeholder="t('common.select')" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem v-for="item in items" :key="item.id" :value="item.id.toString()">
                                                        {{ item.name }} ({{ item.sku }})
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>

                                        <div class="space-y-2">
                                            <Label>{{ t('locations.warehouse') }}</Label>
                                            <Select v-model="line.warehouse_id" required>
                                                <SelectTrigger>
                                                    <SelectValue :placeholder="t('common.select')" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem v-for="wh in warehouses" :key="wh.id" :value="wh.id.toString()">
                                                        {{ wh.name }}
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>

                                        <div class="space-y-2">
                                            <Label>{{ t('inventory.systemQty') }}</Label>
                                            <Input v-model.number="line.system_quantity" type="number" step="0.001" required />
                                        </div>

                                        <div class="space-y-2">
                                            <Label>{{ t('inventory.adjustedQty') }}</Label>
                                            <Input v-model.number="line.adjusted_quantity" type="number" step="0.001" required />
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        <Label>{{ t('common.reason') }}</Label>
                                        <Input v-model="line.reason" />
                                    </div>
                                </CardContent>
                            </Card>
                        </CardContent>
                    </Card>

                    <div class="flex justify-end gap-2">
                        <Button type="button" variant="outline" @click="$inertia.visit('/inventory/stock-adjustments')">
                            {{ t('common.cancel') }}
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            <Save class="me-2 h-4 w-4" />
                            {{ t('common.create') }}
                        </Button>
                    </div>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
