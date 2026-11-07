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
import { Alert, AlertDescription } from '@/Components/ui/alert'
import { CheckCircle, ArrowLeft, AlertTriangle } from 'lucide-vue-next'
import { computed } from 'vue'

const { t } = useI18n()

const props = defineProps<{
    items: any[]
    batches: any[]
    warehouses: any[]
    users: any[]
}>()

const form = useForm({
    item_id: null,
    batch_id: null,
    warehouse_id: null,
    expected_balance: 0,
    actual_balance: 0,
    dispensed_by: null,
    witnessed_by: null,
    reason: '',
    notes: '',
})

const filteredBatches = computed(() => {
    if (!form.item_id) return []
    return props.batches.filter(b => b.item_id === form.item_id)
})

const variance = computed(() => {
    return form.actual_balance - form.expected_balance
})

function submit() {
    form.post('/operations/controlled-medications/reconcile')
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" @click="$inertia.visit('/operations/controlled-medications')">
                    <ArrowLeft class="h-5 w-5" />
                </Button>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        <CheckCircle class="me-2 inline h-6 w-6" />
                        {{ t('operations.reconcileControlled') || 'Reconcile Controlled Medication' }}
                    </h1>
                    <p class="text-muted-foreground">{{ t('operations.reconcileControlledDesc') || 'Verify and reconcile controlled substance inventory' }}</p>
                </div>
            </div>

            <Alert variant="destructive">
                <AlertTriangle class="h-4 w-4" />
                <AlertDescription>
                    <strong>{{ t('operations.witnessRequired') || 'WITNESS REQUIRED' }}</strong><br />
                    {{ t('operations.reconcileWitnessDesc') || 'Reconciliation must be witnessed by a different user. This creates an immutable audit record.' }}
                </AlertDescription>
            </Alert>

            <Card>
                <CardHeader>
                    <CardTitle>{{ t('operations.reconciliationDetails') || 'Reconciliation Details' }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="item_id">{{ t('common.item') }}* (Controlled)</Label>
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
                                <Label for="expected_balance">{{ t('operations.expectedBalance') || 'Expected Balance' }}*</Label>
                                <Input
                                    id="expected_balance"
                                    v-model.number="form.expected_balance"
                                    type="number"
                                    min="0"
                                    required
                                />
                                <p v-if="form.errors.expected_balance" class="text-sm text-destructive">{{ form.errors.expected_balance }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="actual_balance">{{ t('operations.actualBalance') || 'Actual Balance' }}*</Label>
                                <Input
                                    id="actual_balance"
                                    v-model.number="form.actual_balance"
                                    type="number"
                                    min="0"
                                    required
                                />
                                <p v-if="form.errors.actual_balance" class="text-sm text-destructive">{{ form.errors.actual_balance }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label>{{ t('operations.variance') || 'Variance' }}</Label>
                                <div class="text-2xl font-bold" :class="variance !== 0 ? 'text-destructive' : 'text-muted-foreground'">
                                    {{ variance > 0 ? '+' : '' }}{{ variance }}
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="dispensed_by">{{ t('operations.reconciledBy') || 'Reconciled By' }}*</Label>
                                <Select v-model="form.dispensed_by" required>
                                    <SelectTrigger>
                                        <SelectValue :placeholder="t('common.selectUser')" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="user in users" :key="user.id" :value="user.id">
                                            {{ user.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.dispensed_by" class="text-sm text-destructive">{{ form.errors.dispensed_by }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="witnessed_by" class="text-destructive">{{ t('operations.witnessedBy') || 'Witnessed By' }}* (Required)</Label>
                                <Select v-model="form.witnessed_by" required>
                                    <SelectTrigger>
                                        <SelectValue :placeholder="t('common.selectUser')" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="user in users" :key="user.id" :value="user.id">
                                            {{ user.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.witnessed_by" class="text-sm text-destructive">{{ form.errors.witnessed_by }}</p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="reason">{{ t('operations.reconciliationReason') || 'Reason' }}</Label>
                            <Input
                                id="reason"
                                v-model="form.reason"
                                placeholder="Optional reason for reconciliation"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="notes">{{ t('common.notes') }}</Label>
                            <Textarea
                                id="notes"
                                v-model="form.notes"
                                rows="3"
                            />
                        </div>

                        <div class="flex gap-2 justify-end">
                            <Button type="button" variant="outline" @click="$inertia.visit('/operations/controlled-medications')">
                                {{ t('common.cancel') }}
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                {{ t('operations.completeReconciliation') || 'Complete Reconciliation' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
