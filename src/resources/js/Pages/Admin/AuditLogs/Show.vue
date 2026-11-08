<script setup>
import { usePage } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';

const { data } = usePage();
const log = data.log || {};
</script>

<template>
    <div>
        <div class="flex items-center gap-4 mb-8">
            <Link :href="route('admin.audit-logs.index')" class="flex items-center gap-2 text-sm text-muted-foreground hover:text-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Audit Logs
            </Link>
            <h1 class="text-2xl font-semibold text-gray-900">Audit Log Details</h1>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="bg-muted p-4 rounded-lg">
                <h3 class="text-xs font-medium text-muted-foreground uppercase tracking-wider mb-2">Action</h3>
                <p class="text-sm font-medium">{{ log.event_type || 'N/A' }}</p>
            </div>
            <div class="bg-muted p-4 rounded-lg">
                <h3 class="text-xs font-medium text-muted-foreground uppercase tracking-wider mb-2">Resource Type</h3>
                <p class="text-sm font-mono text-gray-600">{{ log.auditable_type || 'N/A' }}</p>
            </div>
            <div class="bg-muted p-4 rounded-lg">
                <h3 class="text-xs font-medium text-muted-foreground uppercase tracking-wider mb-2">Resource ID</h3>
                <p class="text-sm font-mono text-gray-600">{{ log.auditable_id || 'N/A' }}</p>
            </div>
            <div class="bg-muted p-4 rounded-lg">
                <h3 class="text-xs font-medium text-muted-foreground uppercase tracking-wider mb-2">IP Address</h3>
                <p class="text-sm font-mono text-gray-600">{{ log.ip_address || 'N/A' }}</p>
            </div>
            <div class="bg-muted p-4 rounded-lg">
                <h3 class="text-xs font-medium text-muted-foreground uppercase tracking-wider mb-2">Time</h3>
                <p class="text-sm font-mono text-gray-600">{{ new Date(log.created_at).toLocaleString() }}</p>
            </div>
        </div>

        <div class="mt-8">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Changes</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left rtl:text-right">
                    <thead class="bg-muted text-xs text-muted-foreground">
                        <tr>
                            <th class="px-4 py-3 w-1/3">Field</th>
                            <th class="px-4 py-3 w-1/3">Old Value</th>
                            <th class="px-4 py-3 w-1/3">New Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(value, key) in log.old_values || {}" :key="key" class="border-b">
                            <td class="px-4 py-2 text-sm font-mono">{{ key }}</td>
                            <td class="px-4 py-2 text-sm">{{ value || '-' }}</td>
                            <td class="px-4 py-2 text-sm">
                                {{ log.new_values?.[key] || '-' }}
                            </td>
                        </tr>
                        <tr v-for="(value, key) in log.new_values || {}" :key="`new-${key}`" v-if="!(key in (log.old_values || {}))" class="border-b">
                            <td class="px-4 py-2 text-sm font-mono text-green-600">{{ key }}</td>
                            <td class="px-4 py-2 text-sm text-muted-foreground">-</td>
                            <td class="px-4 py-2 text-sm text-green-600">{{ value }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-8">
            <Link :href="route('admin.audit-logs.index')">
                <Button variant="outline">Back to Audit Logs</Button>
            </Link>
        </div>
    </div>
</template>