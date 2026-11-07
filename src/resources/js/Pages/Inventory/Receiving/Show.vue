<script setup lang="ts">
import { ref } from 'vue'
import { router, useForm, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Badge } from '@/Components/ui/badge'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Separator } from '@/Components/ui/separator'
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/Components/ui/table'
import {
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter,
} from '@/Components/ui/dialog'
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/Components/ui/select'
import { ArrowLeft, Plus, CheckCircle, Package } from 'lucide-vue-next'

const { t } = useI18n()

const props = defineProps<{
    receiving: any
    items: Array<{ id: number; name: string; sku: string; requires_batch: boolean; requires_expiry: boolean }>
    uoms: Array<{ id: number; name: string }>
}>()

const showAddLine = ref(false)
const showReceiveLine = ref(false)
const selectedLine = ref<any>(null)

const addLineForm = useForm({
    item_id: null as number | null,
    expected_quantity: 1,
    uom_id: null as number | null,
    unit_cost: 0,
})

const receiveForm = useForm({
    received_quantity: 0,
    rejected_quantity: 0,
    batch_number: '',
    expiry_date: '',
    rejection_reason: '',
})

function addLine() {
    addLineForm.post(`/inventory/receivings/${props.receiving.id}/lines`, {
        onSuccess: () => {
            showAddLine.value = false
            addLineForm.reset()
        },
    })
}

function openReceive(line: any) {
    selectedLine.value = line
    receiveForm.received_quantity = line.expected_quantity - (line.received_quantity || 0)
    receiveForm.rejected_quantity = 0
    receiveForm.batch_number = line.batch_number || ''
    receiveForm.expiry_date = line.expiry_date || ''
    showReceiveLine.value = true
}

function receiveLine() {
    if (!selectedLine.value) return
    receiveForm.put(`/inventory/receivings/${props.receiving.id}/lines/${selectedLine.value.id}/receive`, {
        onSuccess: () => {
            showReceiveLine.value = false
            receiveForm.reset()
        },
    })
}

function complete() {
    if (confirm(t('inventory.confirmComplete') || 'Complete this receiving? This cannot be undone.')) {
        router.post(`/inventory/receivings/${props.receiving.id}/complete`)
    }
}

const statusVariants: Record<string, string> = {
    draft: 'secondary',
    in_progress: 'default',
    completed: 'outline',
    cancelled: 'destructive',
}

function selectedItem() {
    return props.items?.find(i => i.id === addLineForm.item_id)
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <Link href="/inventory/receivings" class="mb-2 flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground">
                        <ArrowLeft class="h-4 w-4" />
                        {{ t('inventory.receiving') || 'Receiving' }}
                    </Link>
                    <h1 class="text-2xl font-bold tracking-tight">
                        {{ receiving.receiving_number }}
                    </h1>
                </div>
                <div class="flex gap-2">
                    <Badge :variant="(statusVariants[receiving.status] as any) || 'secondary'" class="text-sm">
                        {{ receiving.status }}
                    </Badge>
                    <Button
                        v-if="receiving.status === 'draft' || receiving.status === 'in_progress'"
                        @click="showAddLine = true"
                        variant="outline"
                    >
                        <Plus class="me-2 h-4 w-4" />
                        {{ t('inventory.addLine') || 'Add Line' }}
                    </Button>
                    <Button
                        v-if="receiving.status === 'in_progress'"
                        @click="complete"
                    >
                        <CheckCircle class="me-2 h-4 w-4" />
                        {{ t('inventory.complete') || 'Complete' }}
                    </Button>
                </div>
            </div>

            <!-- Header Info -->
            <Card>
                <CardContent class="grid grid-cols-2 gap-4 pt-6 sm:grid-cols-4">
                    <div>
                        <p class="text-sm text-muted-foreground">{{ t('inventory.supplier') || 'Supplier' }}</p>
                        <p class="font-medium">{{ receiving.supplier_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">{{ t('locations.warehouse') || 'Warehouse' }}</p>
                        <p class="font-medium">{{ receiving.warehouse?.name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">{{ t('common.date') || 'Date' }}</p>
                        <p class="font-medium">{{ new Date(receiving.receiving_date).toLocaleDateString() }}</p>
                    </div>
                    <div v-if="receiving.purchase_order_number">
                        <p class="text-sm text-muted-foreground">{{ t('inventory.poNumber') || 'PO #' }}</p>
                        <p class="font-medium">{{ receiving.purchase_order_number }}</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Lines -->
            <Card>
                <CardHeader>
                    <CardTitle>{{ t('inventory.receivingLines') || 'Receiving Lines' }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>#</TableHead>
                                <TableHead>{{ t('items.item') || 'Item' }}</TableHead>
                                <TableHead class="text-center">{{ t('inventory.expected') || 'Expected' }}</TableHead>
                                <TableHead class="text-center">{{ t('inventory.received') || 'Received' }}</TableHead>
                                <TableHead class="text-center">{{ t('inventory.rejected') || 'Rejected' }}</TableHead>
                                <TableHead>{{ t('inventory.batch') || 'Batch' }}</TableHead>
                                <TableHead class="text-end">{{ t('common.actions') }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="(line, idx) in receiving.lines" :key="line.id">
                                <TableCell>{{ idx + 1 }}</TableCell>
                                <TableCell>
                                    <div class="font-medium">{{ line.item?.name }}</div>
                                    <div class="text-xs text-muted-foreground">{{ line.item?.sku }}</div>
                                </TableCell>
                                <TableCell class="text-center font-mono">{{ line.expected_quantity }}</TableCell>
                                <TableCell class="text-center font-mono text-emerald-600">{{ line.received_quantity || 0 }}</TableCell>
                                <TableCell class="text-center font-mono text-destructive">{{ line.rejected_quantity || 0 }}</TableCell>
                                <TableCell>
                                    <div v-if="line.batch_number" class="text-sm">{{ line.batch_number }}</div>
                                    <div v-if="line.expiry_date" class="text-xs text-muted-foreground">
                                        {{ new Date(line.expiry_date).toLocaleDateString() }}
                                    </div>
                                </TableCell>
                                <TableCell class="text-end">
                                    <Button
                                        v-if="(receiving.status === 'draft' || receiving.status === 'in_progress') && (line.received_quantity || 0) < line.expected_quantity"
                                        variant="outline"
                                        size="sm"
                                        @click="openReceive(line)"
                                    >
                                        <Package class="me-1 h-3 w-3" />
                                        {{ t('inventory.receive') || 'Receive' }}
                                    </Button>
                                    <Badge v-else variant="outline" class="text-emerald-600">
                                        <CheckCircle class="me-1 h-3 w-3" />
                                        {{ t('common.done') || 'Done' }}
                                    </Badge>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="!receiving.lines?.length">
                                <TableCell :colspan="7" class="py-8 text-center text-muted-foreground">
                                    {{ t('inventory.noLines') || 'No lines added yet' }}
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <!-- Add Line Dialog -->
            <Dialog v-model:open="showAddLine">
                <DialogContent class="max-w-md">
                    <DialogHeader>
                        <DialogTitle>{{ t('inventory.addLine') || 'Add Line Item' }}</DialogTitle>
                    </DialogHeader>
                    <form @submit.prevent="addLine" class="space-y-4">
                        <div class="space-y-2">
                            <Label>{{ t('items.item') || 'Item' }} *</Label>
                            <Select v-model="addLineForm.item_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select item" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="item in items" :key="item.id" :value="item.id">
                                        {{ item.sku }} - {{ item.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label>{{ t('inventory.expectedQty') || 'Expected Qty' }} *</Label>
                                <Input v-model.number="addLineForm.expected_quantity" type="number" min="1" />
                            </div>
                            <div class="space-y-2">
                                <Label>{{ t('inventory.unitCost') || 'Unit Cost' }}</Label>
                                <Input v-model.number="addLineForm.unit_cost" type="number" step="0.01" min="0" />
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label>{{ t('items.uom') || 'UoM' }}</Label>
                            <Select v-model="addLineForm.uom_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select UoM" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="uom in uoms" :key="uom.id" :value="uom.id">
                                        {{ uom.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <DialogFooter>
                            <Button type="button" variant="outline" @click="showAddLine = false">{{ t('common.cancel') }}</Button>
                            <Button type="submit" :disabled="addLineForm.processing">{{ t('common.add') || 'Add' }}</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <!-- Receive Line Dialog -->
            <Dialog v-model:open="showReceiveLine">
                <DialogContent class="max-w-md">
                    <DialogHeader>
                        <DialogTitle>{{ t('inventory.receiveLine') || 'Receive Line' }}</DialogTitle>
                    </DialogHeader>
                    <form @submit.prevent="receiveLine" class="space-y-4">
                        <div v-if="selectedLine" class="rounded-lg bg-muted p-3 text-sm">
                            <div class="font-medium">{{ selectedLine.item?.name }}</div>
                            <div class="text-muted-foreground">{{ t('inventory.expected') || 'Expected' }}: {{ selectedLine.expected_quantity }}</div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label>{{ t('inventory.receivedQty') || 'Received Qty' }} *</Label>
                                <Input v-model.number="receiveForm.received_quantity" type="number" min="0" />
                            </div>
                            <div class="space-y-2">
                                <Label>{{ t('inventory.rejectedQty') || 'Rejected Qty' }}</Label>
                                <Input v-model.number="receiveForm.rejected_quantity" type="number" min="0" />
                            </div>
                        </div>
                        <div v-if="selectedLine?.item?.requires_batch" class="space-y-2">
                            <Label>{{ t('inventory.batchNumber') || 'Batch Number' }} *</Label>
                            <Input v-model="receiveForm.batch_number" />
                        </div>
                        <div v-if="selectedLine?.item?.requires_expiry" class="space-y-2">
                            <Label>{{ t('inventory.expiryDate') || 'Expiry Date' }} *</Label>
                            <Input v-model="receiveForm.expiry_date" type="date" />
                        </div>
                        <div v-if="receiveForm.rejected_quantity > 0" class="space-y-2">
                            <Label>{{ t('inventory.rejectionReason') || 'Rejection Reason' }}</Label>
                            <Input v-model="receiveForm.rejection_reason" />
                        </div>
                        <DialogFooter>
                            <Button type="button" variant="outline" @click="showReceiveLine = false">{{ t('common.cancel') }}</Button>
                            <Button type="submit" :disabled="receiveForm.processing">{{ t('inventory.receive') || 'Receive' }}</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    </AuthenticatedLayout>
</template>
