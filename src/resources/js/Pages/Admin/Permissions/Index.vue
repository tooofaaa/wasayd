<script setup>
import { usePage } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';

const { data } = usePage();
const permissions = data.permissions || [];

const PERMISSION_GROUPS = {
    'auth.login': 'Auth',
    'auth.logout': 'Auth',
    'users.view': 'Users',
    'users.create': 'Users',
    'users.update': 'Users',
    'users.delete': 'Users',
    'users.reset-password': 'Users',
    'roles.view': 'Roles',
    'roles.create': 'Roles',
    'roles.update': 'Roles',
    'roles.delete': 'Roles',
    'permissions.view': 'Permissions',
    'permissions.create': 'Permissions',
    'permissions.update': 'Permissions',
    'permissions.delete': 'Permissions',
    'companies.view': 'Companies',
    'companies.create': 'Companies',
    'companies.update': 'Companies',
    'companies.delete': 'Companies',
    'audit-logs.view': 'Audit Logs',
    'settings.view': 'Settings',
    'settings.update': 'Settings',
};

function getPermissionGroup(permission) {
    for (const [key, group] of Object.entries(PERMISSION_GROUPS)) {
        if (permission.startsWith(key.replace(/\*/g, ''))) return group;
    }
    return 'Other';
}

const groups = {};
permissions.data.forEach(p => {
    const group = getPermissionGroup(p.name);
    if (!groups[group]) groups[group] = [];
    groups[group].push(p);
});
</script>

<template>
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Permissions</h1>
                <p class="text-sm text-muted-foreground">Manage system permissions</p>
            </div>
            <div class="flex items-center gap-2">
                <Link :href="route('admin.permissions.create')">
                    <Button>Add Permission</Button>
                </Link>
            </div>
        </div>

        <div v-for="(permissions, groupName) in groups" :key="groupName" class="border rounded-lg overflow-hidden">
            <div class="bg-muted px-4 py-2 text-sm font-medium">
                {{ groupName }} ({{ permissions.length }})
            </div>
            <table class="w-full text-sm">
                <thead class="bg-muted/50 text-xs text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3 text-left">Permission</th>
                        <th class="px-4 py-3 text-left">Description</th>
                        <th class="px-4 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="perm in permissions" :key="perm.name" class="border-b">
                        <td class="px-4 py-2 font-mono text-sm">{{ perm.name }}</td>
                        <td class="px-4 py-2 text-muted-foreground">{{ perm.description || '-' }}</td>
                        <td class="px-4 py-2 flex gap-1">
                            <Link :href="route('admin.permissions.edit', perm.name)">
                                <Button variant="outline" size="sm">Edit</Button>
                            </Link>
                            <Button
                                variant="outline destructive"
                                size="sm"
                                @click="confirmDelete(perm.name)"
                            >
                                Delete
                            </Button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>