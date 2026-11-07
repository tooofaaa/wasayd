<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Button } from '@/Components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Label } from '@/Components/ui/label'
import { Input } from '@/Components/ui/input'
import { Textarea } from '@/Components/ui/textarea'
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/Components/ui/select'
import { ShieldAlert, ArrowLeft } from 'lucide-vue-next'
import { computed } from 'vue'

const { t } = useI18n()

const props = defineProps<{
    items: any[]
    batches: any[]
    warehouses: any[]
    bins: any[]
}>()

const form = useForm({
    item_id: null,
    batch_id: null,
    warehouse_id: null,
    bin_id: null,
    quantity: 0,
    reason_code: '',
    reason_description: '',
})

const filteredBatches = computed(() => {
    if (!form.item_id) return []
    return props.batches.filter(b => b.item_id === form.item_id)
})

function submit() {
    form.post('/operations/quarantines')
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" @click="$inertia.visit('/operations/quarantines')">
                    <ArrowLeft class="h-5 w-5" />
                </Button>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        <ShieldAlert class="me-2 inline h-6 w-6" />
                        {{ t('operations.createQuarantine') || 'Create Quarantine' }}
                    </h1>
                    <p class="text-muted-foreground">{{ t('operations.createQuarantineDesc') || 'Quarantine inventory items' }}</p>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>{{ t('operations.quarantineDetails') || 'Quarantine Details' }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="item_id">{{ t('common.item') }}*</Label>
                                <Select v-model="form.item_id" required>
                                    <SelectTrigger>
                                        <SelectValue :placeholder="t('common.selectItem')" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="item in items" :key="item.id" :value="item.id">
                                            {{ item.sku }} - {{ item.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.item_id" class="text-sm text-destructive">{{ form.errors.item_id }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="batch_id">{{ t('common.batch') }}</Label>
                                <Select v-model="form.batch_id">
                                    <SelectTrigger>
                                        <SelectValue :placeholder="t('common.selectBatch')" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="batch in filteredBatches" :key="batch.id" :value="batch.id">
                                            {{ batch.batch_number }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.batch_id" class="text-sm text-destructive">{{ form.errors.batch_id }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="warehouse_id">{{ t('common.warehouse') }}*</Label>
                                <Select v-model="form.warehouse_id" required>
                                    <SelectTrigger>
                                        <SelectValue :placeholder="t('common.selectWarehouse')" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
                                            {{ warehouse.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.warehouse_id" class="text-sm text-destructive">{{ form.errors.warehouse_id }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="bin_id">{{ t('common.bin') }}</Label>
                                <Select v-model="form.bin_id">
                                    <SelectTrigger>
                                        <SelectValue :placeholder="t('common.selectBin')" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="bin in bins" :key="bin.id" :value="bin.id">
                                            {{ bin.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.bin_id" class="text-sm text-destructive">{{ form.errors.bin_id }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="quantity">{{ t('common.quantity') }}*</Label>
                                <Input
                                    id="quantity"
                                    v-model.number="form.quantity"
                                    type="number"
                                    min="1"
                                    required
                                />
                                <p v-if="form.errors.quantity" class="text-sm text-destructive">{{ form.errors.quantity }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="reason_code">{{ t('operations.reasonCode') || 'Reason Code' }}*</Label>
                                <Input
                                    id="reason_code"
                                    v-model="form.reason_code"
                                    required
                                />
                                <p v-if="form.errors.reason_code" class="text-sm text-destructive">{{ form.errors.reason_code }}</p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="reason_description">{{ t('operations.reasonDescription') || 'Reason Description' }}</Label>
                            <Textarea
                                id="reason_description"
                                v-model="form.reason_description"
                                rows="3"
                            />
                            <p v-if="form.errors.reason_description" class="text-sm text-destructive">{{ form.errors.reason_description }}</p>
                        </div>

                        <div class="flex gap-2 justify-end">
                            <Button type="button" variant="outline" @click="$inertia.visit('/operations/quarantines')">
                                {{ t('common.cancel') }}
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                {{ t('common.create') }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
