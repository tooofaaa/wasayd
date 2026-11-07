<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Button } from '@/Components/ui/button'
import { Badge } from '@/Components/ui/badge'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { ArrowLeft, RotateCcw, Lock, AlertCircle } from 'lucide-vue-next'
import { computed } from 'vue'

const { t } = useI18n()

const props = defineProps<{
    transaction: any
    reversedTransaction?: any
    reversalTransaction?: any
}>()

const canReverse = computed(() => !props.transaction.is_reversal && !props.reversalTransaction)

const reverseForm = useForm({
    reason: '',
})

function reverse() {
    const reason = prompt(t('inventory.reverseReason') || 'Reason for reversal (minimum 10 characters):')
    if (reason && reason.length >= 10) {
        reverseForm.reason = reason
        reverseForm.post(`/inventory/transactions/${props.transaction.id}/reverse`)
    } else if (reason) {
        alert(t('inventory.reverseReasonTooShort') || 'Reason must be at least 10 characters')
    }
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        {{ transaction.transaction_number }}
                    </h1>
                    <p class="text-muted-foreground">
                        <Lock class="me-1 inline h-4 w-4" />
                        {{ t('inventory.transactionDetails') || 'Transaction details (immutable)' }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" as="a" href="/inventory/transactions">
                        <ArrowLeft class="me-2 h-4 w-4" />
                        {{ t('common.back') }}
                    </Button>
                    <Button v-if="canReverse" variant="destructive" @click="reverse">
                        <RotateCcw class="me-2 h-4 w-4" />
                        {{ t('inventory.reverseTransaction') || 'Reverse' }}
                    </Button>
                </div>
            </div>

            <Card v-if="transaction.is_reversal" class="border-red-500 bg-red-50 dark:bg-red-950">
                <CardContent class="pt-6">
                    <div class="flex items-center gap-2 text-red-700 dark:text-red-300">
                        <AlertCircle class="h-5 w-5" />
                        <p class="font-medium">
                            {{ t('inventory.thisIsReversal') || 'This is a reversal transaction' }}
                        </p>
                    </div>
                </CardContent>
            </Card>

            <Card v-if="reversalTransaction" class="border-orange-500 bg-orange-50 dark:bg-orange-950">
                <CardContent class="pt-6">
                    <div class="flex items-center gap-2 text-orange-700 dark:text-orange-300">
                        <AlertCircle class="h-5 w-5" />
                        <p class="font-medium">
                            {{ t('inventory.hasBeenReversed') || 'This transaction has been reversed' }}
                        </p>
                    </div>
                </CardContent>
            </Card>

            <div class="grid gap-6 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>{{ t('inventory.transactionInfo') || 'Transaction Information' }}</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div>
                            <p class="text-sm text-muted-foreground">{{ t('inventory.type') }}</p>
                            <Badge variant="default" class="text-base">{{ transaction.transaction_type }}</Badge>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">{{ t('common.date') }}</p>
                            <p class="text-lg font-semibold">{{ new Date(transaction.transaction_date).toLocaleString() }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">{{ t('inventory.createdBy') }}</p>
                            <p class="text-lg">{{ transaction.created_by?.name || '-' }}</p>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{{ t('items.item') || 'Item Details' }}</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div>
                            <p class="text-sm text-muted-foreground">{{ t('items.item') }}</p>
                            <p class="text-lg font-semibold">{{ transaction.item?.name }}</p>
                            <p class="text-sm text-muted-foreground">{{ transaction.item?.sku }}</p>
                        </div>
                        <div v-if="transaction.batch">
                            <p class="text-sm text-muted-foreground">{{ t('inventory.batch') }}</p>
                            <p class="text-lg">{{ transaction.batch?.batch_number }}</p>
                        </div>
                        <div v-if="transaction.epc">
                            <p class="text-sm text-muted-foreground">{{ t('rfid.epc') }}</p>
                            <p class="font-mono text-sm">{{ transaction.epc?.epc_code }}</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <Card>
                    <CardHeader>
                        <CardTitle>{{ t('inventory.fromLocation') || 'From Location' }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-lg font-semibold">{{ transaction.from_warehouse?.name || '-' }}</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{{ t('inventory.toLocation') || 'To Location' }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-lg font-semibold">{{ transaction.to_warehouse?.name || '-' }}</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{{ t('inventory.quantity') || 'Quantity' }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-2xl font-bold" :class="transaction.quantity < 0 ? 'text-red-600' : 'text-green-600'">
                            {{ transaction.quantity > 0 ? '+' : '' }}{{ transaction.quantity }}
                        </p>
                    </CardContent>
                </Card>
            </div>

            <Card v-if="transaction.notes">
                <CardHeader>
                    <CardTitle>{{ t('common.notes') }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <p>{{ transaction.notes }}</p>
                </CardContent>
            </Card>

            <Card v-if="reversedTransaction">
                <CardHeader>
                    <CardTitle>{{ t('inventory.originalTransaction') || 'Original Transaction' }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-mono text-sm">{{ reversedTransaction.transaction_number }}</p>
                            <p class="text-sm text-muted-foreground">
                                {{ reversedTransaction.item?.name }} - {{ reversedTransaction.quantity }}
                            </p>
                        </div>
                        <Button variant="outline" size="sm" as="a" :href="`/inventory/transactions/${reversedTransaction.id}`">
                            {{ t('common.view') }}
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <Card v-if="reversalTransaction">
                <CardHeader>
                    <CardTitle>{{ t('inventory.reversalTransaction') || 'Reversal Transaction' }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-mono text-sm">{{ reversalTransaction.transaction_number }}</p>
                            <p class="text-sm text-muted-foreground">
                                {{ reversalTransaction.notes }}
                            </p>
                        </div>
                        <Button variant="outline" size="sm" as="a" :href="`/inventory/transactions/${reversalTransaction.id}`">
                            {{ t('common.view') }}
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
