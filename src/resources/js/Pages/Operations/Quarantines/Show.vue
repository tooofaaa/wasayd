<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Button } from '@/Components/ui/button'
import { Badge } from '@/Components/ui/badge'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Label } from '@/Components/ui/label'
import { Textarea } from '@/Components/ui/textarea'
import { ShieldAlert, ArrowLeft, CheckCircle, Trash2 } from 'lucide-vue-next'

const { t } = useI18n()

const props = defineProps<{
    quarantine: any
}>()

const releaseForm = useForm({
    resolution_notes: '',
})

const disposeForm = useForm({
    resolution_notes: '',
})

function release() {
    releaseForm.post(`/operations/quarantines/${props.quarantine.id}/release`)
}

function dispose() {
    if (confirm('Are you sure you want to dispose this quarantined stock? This action cannot be undone.')) {
        disposeForm.post(`/operations/quarantines/${props.quarantine.id}/dispose`)
    }
}

const statusVariants: Record<string, string> = {
    active: 'destructive',
    under_review: 'default',
    released: 'outline',
    disposed: 'secondary',
}

function formatDate(d: string) {
    return new Date(d).toLocaleString()
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
                        {{ quarantine.quarantine_number }}
                    </h1>
                    <p class="text-muted-foreground">{{ t('operations.quarantineDetails') || 'Quarantine Details' }}</p>
                </div>
                <Badge :variant="(statusVariants[quarantine.status] as any) || 'secondary'" class="ml-auto">
                    {{ quarantine.status }}
                </Badge>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>{{ t('operations.itemDetails') || 'Item Details' }}</CardTitle>
                </CardHeader>
                <CardContent class="grid grid-cols-2 gap-4">
                    <div>
                        <Label class="text-muted-foreground">{{ t('common.item') }}</Label>
                        <p class="font-medium">{{ quarantine.item?.name }}</p>
                    </div>
                    <div>
                        <Label class="text-muted-foreground">{{ t('common.batch') }}</Label>
                        <p class="font-medium">{{ quarantine.batch?.batch_number || '-' }}</p>
                    </div>
                    <div>
                        <Label class="text-muted-foreground">{{ t('common.warehouse') }}</Label>
                        <p class="font-medium">{{ quarantine.warehouse?.name }}</p>
                    </div>
                    <div>
                        <Label class="text-muted-foreground">{{ t('common.bin') }}</Label>
                        <p class="font-medium">{{ quarantine.bin?.name || '-' }}</p>
                    </div>
                    <div>
                        <Label class="text-muted-foreground">{{ t('common.quantity') }}</Label>
                        <p class="font-medium">{{ quarantine.quantity }}</p>
                    </div>
                    <div>
                        <Label class="text-muted-foreground">{{ t('operations.reasonCode') || 'Reason Code' }}</Label>
                        <p class="font-medium">{{ quarantine.reason_code }}</p>
                    </div>
                    <div class="col-span-2">
                        <Label class="text-muted-foreground">{{ t('operations.reasonDescription') || 'Reason Description' }}</Label>
                        <p class="font-medium">{{ quarantine.reason_description || '-' }}</p>
                    </div>
                    <div>
                        <Label class="text-muted-foreground">{{ t('operations.quarantinedBy') || 'Quarantined By' }}</Label>
                        <p class="font-medium">{{ quarantine.quarantined_by?.name }}</p>
                    </div>
                    <div>
                        <Label class="text-muted-foreground">{{ t('operations.quarantinedAt') || 'Quarantined At' }}</Label>
                        <p class="font-medium">{{ formatDate(quarantine.quarantined_at) }}</p>
                    </div>
                </CardContent>
            </Card>

            <Card v-if="quarantine.status === 'active'">
                <CardHeader>
                    <CardTitle>{{ t('operations.resolutionActions') || 'Resolution Actions' }}</CardTitle>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="space-y-4">
                        <div>
                            <Label for="release_notes">{{ t('operations.resolutionNotes') || 'Resolution Notes' }}</Label>
                            <Textarea
                                id="release_notes"
                                v-model="releaseForm.resolution_notes"
                                rows="3"
                                placeholder="Optional notes..."
                            />
                        </div>
                        <div class="flex gap-2">
                            <Button @click="release" :disabled="releaseForm.processing">
                                <CheckCircle class="me-2 h-4 w-4" />
                                {{ t('operations.releaseQuarantine') || 'Release Quarantine' }}
                            </Button>
                            <Button variant="destructive" @click="dispose" :disabled="disposeForm.processing">
                                <Trash2 class="me-2 h-4 w-4" />
                                {{ t('operations.disposeQuarantine') || 'Dispose Quarantine' }}
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card v-if="quarantine.resolution">
                <CardHeader>
                    <CardTitle>{{ t('operations.resolutionInfo') || 'Resolution Information' }}</CardTitle>
                </CardHeader>
                <CardContent class="grid grid-cols-2 gap-4">
                    <div>
                        <Label class="text-muted-foreground">{{ t('operations.resolution') || 'Resolution' }}</Label>
                        <p class="font-medium">{{ quarantine.resolution }}</p>
                    </div>
                    <div>
                        <Label class="text-muted-foreground">{{ t('operations.resolvedBy') || 'Resolved By' }}</Label>
                        <p class="font-medium">{{ quarantine.resolved_by?.name }}</p>
                    </div>
                    <div>
                        <Label class="text-muted-foreground">{{ t('operations.resolvedAt') || 'Resolved At' }}</Label>
                        <p class="font-medium">{{ formatDate(quarantine.resolved_at) }}</p>
                    </div>
                    <div class="col-span-2">
                        <Label class="text-muted-foreground">{{ t('operations.resolutionNotes') || 'Resolution Notes' }}</Label>
                        <p class="font-medium">{{ quarantine.resolution_notes || '-' }}</p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
