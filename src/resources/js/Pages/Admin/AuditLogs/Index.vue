<script setup>
import { usePage } from '@inertiajs/vue3';
import { Table } from '@/Components/ui/table';
import { Badge } from '@/Components/ui/badge';
import { Link } from '@inertiajs/vue3';

const { data } = usePage();
const logs = data.logs || [];
const filters = data.filters || {};

const ACTION_LABELS = {
    'created': 'Created',
    'updated': 'Updated',
    'deleted': 'Deleted',
    'restored': 'Restored',
    'logged_in': 'Login',
    'logged_out': 'Logout',
    'password_reset': 'Password Reset',
    'role_assigned': 'Role Assigned',
    'role_removed': 'Role Removed',
};
</script>

<template>
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Audit Logs</h1>
                <p class="text-sm text-muted-foreground">System audit trail</p>
            </div>
            <div class="flex items-center gap-2">
                <Link :href="route('admin.audit-logs.index')" class="px-3 py-2 text-sm font-medium transition-colors hover:bg-muted rounded-md">
                    Refresh
                </Link>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left rtl:text-right">
                <thead class="bg-muted text-sm text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3">Time</th>
                        <th class="px-4 py-3">User</th>
                        <th class="px-4 py-3">Action</th>
                        <th class="px-4 py-3">Resource</th>
                        <th class="px-4 py-3">IP Address</th>
                        <th class="px-4 py-3 text-right">Changes</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="log in logs.data" :key="log.id" class="border-b hover:bg-muted/50">
                        <td class="px-4 py-3 text-sm font-mono">{{ new Date(log.created_at).toLocaleString() }}</td>
                        <td class="px-4 py-3 text-sm font-medium">
                            {{ log.user?.name || 'System' }}
                            <span class="text-xs text-muted-foreground ml-1">{{ log.user?.email }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 rounded-md bg-blue-50 text-blue-600 text-xs font-medium">
                                {{ ACTION_LABELS[log.event_type] || log.event_type }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm font-mono">{{ log.auditable_type || '-' }}</td>
                        <td class="px-4 py-3 text-sm">{{ log.ip_address || '-' }}</td>
                        <td class="px-4 py-3 text-right">
                            <Link :href="route('admin.audit-logs.show', log.id)">
                                <Button variant="outline" size="sm">View</Button>
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex justify-between items-center mt-4">
            <div class="flex-1 text-sm text-muted-foreground">
                Showing {{ logs.from }} to {{ logs.to }} of {{ logs.total }} records
            </div>
            <div class="flex gap-2">
                <Link
                    v-for="page in logs.links.pages"
                    :key="page.url"
                    :href="page.url"
                    :class="[
                        'px-3 py-1 rounded-md text-sm',
                        logs.current_page === page.url ? 'bg-primary text-white' : 'bg-muted hover:bg-muted/50',
                    ]"
                >
                    {{ page.label }}
                </Link>
            </div>
        </div>
    </div>
</template>