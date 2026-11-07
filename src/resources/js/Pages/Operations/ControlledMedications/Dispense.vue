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
import { Pill, ArrowLeft, AlertTriangle } from 'lucide-vue-next'
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
    quantity: 0,
    dispensed_by: null,
    witnessed_by: null,
    patient_id: '',
    prescription_number: '',
    physician_name: '',
    notes: '',
})

const filteredBatches = computed(() => {
    if (!form.item_id) return []
    return props.batches.filter(b => b.item_id === form.item_id)
})

function submit() {
    form.post('/operations/controlled-medications/dispense')
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
                        <Pill class="me-2 inline h-6 w-6" />
                        {{ t('operations.dispenseControlled') || 'Dispense Controlled Medication' }}
                    </h1>
                    <p class="text-muted-foreground">{{ t('operations.dispenseControlledDesc') || 'Dual authorization required' }}</p>
                </div>
            </div>

            <Alert variant="destructive">
                <AlertTriangle class="h-4 w-4" />
                <AlertDescription>
                    <strong>{{ t('operations.dualAuthRequired') || 'DUAL AUTHORIZATION REQUIRED' }}</strong><br />
                    {{ t('operations.dualAuthDesc') || 'Both dispenser and witness must be different users. This creates an immutable audit record.' }}
                </AlertDescription>
            </Alert>

            <Card>
                <CardHeader>
                    <CardTitle>{{ t('operations.dispenseDetails') || 'Dispense Details' }}</CardTitle>
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
                                <Label for="dispensed_by">{{ t('operations.dispensedBy') || 'Dispensed By' }}*</Label>
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

                            <div class="space-y-2">
                                <Label for="patient_id">{{ t('operations.patientId') || 'Patient ID' }}</Label>
                                <Input
                                    id="patient_id"
                                    v-model="form.patient_id"
                                />
                            </div>

                            <div class="space-y-2">
                                <Label for="prescription_number">{{ t('operations.prescriptionNumber') || 'Prescription #' }}</Label>
                                <Input
                                    id="prescription_number"
                                    v-model="form.prescription_number"
                                />
                            </div>

                            <div class="space-y-2 col-span-2">
                                <Label for="physician_name">{{ t('operations.physicianName') || 'Physician Name' }}</Label>
                                <Input
                                    id="physician_name"
                                    v-model="form.physician_name"
                                />
                            </div>
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
                                {{ t('operations.dispense') || 'Dispense' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
