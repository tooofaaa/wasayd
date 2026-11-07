<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Button } from '@/Components/ui/button'
import { Badge } from '@/Components/ui/badge'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Separator } from '@/Components/ui/separator'
import { ArrowLeft } from 'lucide-vue-next'

const { t } = useI18n()

defineProps<{
    log: {
        id: number
        event_type: string
        auditable_type: string
        auditable_id: number
        user: { id: number; name: string } | null
        old_values: Record<string, any> | null
        new_values: Record<string, any> | null
        description: string | null
        ip_address: string | null
        user_agent: string | null
        created_at: string
    }
}>()

function formatDate(d: string) {
    return new Date(d).toLocaleString()
}

function formatJson(obj: any) {
    if (!obj) return '{}'
    return JSON.stringify(obj, null, 2)
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="mx-auto max-w-3xl space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold tracking-tight">{{ t('nav.auditLog') }} #{{ log.id }}</h1>
                <Link href="/admin/audit-logs">
                    <Button variant="outline">
                        <ArrowLeft class="me-2 h-4 w-4" />
                        {{ t('common.back') }}
                    </Button>
                </Link>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>{{ t('common.details') }}</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-muted-foreground">{{ t('admin.eventType') || 'Event Type' }}</p>
                            <Badge>{{ log.event_type }}</Badge>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">{{ t('common.date') || 'Date' }}</p>
                            <p>{{ formatDate(log.created_at) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">{{ t('nav.users') }}</p>
                            <p>{{ log.user?.name ?? 'System' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">{{ t('admin.entity') || 'Entity' }}</p>
                            <p>{{ log.auditable_type?.split('\\').pop() }} #{{ log.auditable_id }}</p>
                        </div>
                        <div v-if="log.ip_address">
                            <p class="text-sm text-muted-foreground">IP</p>
                            <p class="font-mono text-sm">{{ log.ip_address }}</p>
                        </div>
                    </div>

                    <Separator v-if="log.description" />
                    <div v-if="log.description">
                        <p class="text-sm text-muted-foreground">{{ t('admin.description') || 'Description' }}</p>
                        <p>{{ log.description }}</p>
                    </div>
                </CardContent>
            </Card>

            <div class="grid gap-6 md:grid-cols-2" v-if="log.old_values || log.new_values">
                <Card v-if="log.old_values">
                    <CardHeader>
                        <CardTitle class="text-sm text-destructive">{{ t('admin.oldValues') || 'Old Values' }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <pre class="max-h-64 overflow-auto rounded bg-muted p-3 text-xs">{{ formatJson(log.old_values) }}</pre>
                    </CardContent>
                </Card>
                <Card v-if="log.new_values">
                    <CardHeader>
                        <CardTitle class="text-sm text-green-600">{{ t('admin.newValues') || 'New Values' }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <pre class="max-h-64 overflow-auto rounded bg-muted p-3 text-xs">{{ formatJson(log.new_values) }}</pre>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
