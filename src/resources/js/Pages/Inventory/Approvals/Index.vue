<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Button } from '@/Components/ui/button'
import { Badge } from '@/Components/ui/badge'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { CheckCircle, XCircle, Clock } from 'lucide-vue-next'
import { ref } from 'vue'

const { t } = useI18n()

const props = defineProps<{
    pendingApprovals: any
    filters: { type?: string }
}>()

function approve(approval: any) {
    if (confirm(t('inventory.confirmApproval') || 'Are you sure you want to approve this?')) {
        router.post(`/inventory/approvals/${approval.approval_id}/approve`, {
            comments: '',
        })
    }
}

function reject(approval: any) {
    const reason = prompt(t('inventory.rejectReason') || 'Reason for rejection (minimum 10 characters):')
    if (reason && reason.length >= 10) {
        router.post(`/inventory/approvals/${approval.approval_id}/reject`, {
            reason: reason,
        })
    } else if (reason) {
        alert(t('inventory.rejectReasonTooShort') || 'Reason must be at least 10 characters')
    }
}

function getApprovableTypeName(type: string): string {
    const parts = type.split('\\')
    return parts[parts.length - 1]
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        <Clock class="me-2 inline h-6 w-6" />
                        {{ t('inventory.pendingApprovals') || 'Pending Approvals' }}
                    </h1>
                    <p class="text-muted-foreground">{{ t('inventory.pendingApprovalsDesc') || 'Items awaiting your approval' }}</p>
                </div>
            </div>

            <div v-if="!pendingApprovals.data?.length" class="py-12 text-center">
                <Clock class="mx-auto h-12 w-12 text-muted-foreground" />
                <h3 class="mt-4 text-lg font-semibold">{{ t('inventory.noApprovals') || 'No Pending Approvals' }}</h3>
                <p class="mt-2 text-sm text-muted-foreground">
                    {{ t('inventory.allCaughtUp') || 'You are all caught up!' }}
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <Card v-for="step in pendingApprovals.data" :key="step.id" class="border-2">
                    <CardHeader>
                        <div class="flex items-start justify-between">
                            <div>
                                <CardTitle class="text-lg">
                                    <Badge variant="outline" class="me-2">
                                        {{ getApprovableTypeName(step.approval.approvable_type) }}
                                    </Badge>
                                    {{ step.approval.approvable?.adjustment_number || step.approval.approvable?.count_number || 'N/A' }}
                                </CardTitle>
                                <p class="mt-1 text-sm text-muted-foreground">
                                    {{ t('inventory.step') }} {{ step.step_order }} - {{ step.role }}
                                </p>
                            </div>
                            <Badge variant="secondary">
                                {{ step.approval.approval_type }}
                            </Badge>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">{{ t('inventory.requestedBy') }}</span>
                                <span class="font-medium">{{ step.approval.requested_by?.name }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">{{ t('common.date') }}</span>
                                <span class="font-medium">{{ new Date(step.approval.created_at).toLocaleDateString() }}</span>
                            </div>
                        </div>

                        <div v-if="step.approval.notes" class="rounded-md bg-muted p-3">
                            <p class="text-sm">{{ step.approval.notes }}</p>
                        </div>

                        <div class="flex gap-2 pt-2">
                            <Button variant="default" class="flex-1" @click="approve(step)">
                                <CheckCircle class="me-2 h-4 w-4" />
                                {{ t('common.approve') }}
                            </Button>
                            <Button variant="destructive" class="flex-1" @click="reject(step)">
                                <XCircle class="me-2 h-4 w-4" />
                                {{ t('common.reject') }}
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
