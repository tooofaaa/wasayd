<script setup lang="ts">
import { router, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Button } from '@/Components/ui/button'
import { Badge } from '@/Components/ui/badge'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/Components/ui/table'
import { Plus, Pencil, Trash2, Shield } from 'lucide-vue-next'

const { t } = useI18n()

defineProps<{
    roles: Array<{
        id: number
        name: string
        permissions: Array<{ id: number; name: string }>
        users_count: number
    }>
}>()

function deleteRole(id: number) {
    if (confirm(t('common.confirmDelete') || 'Are you sure?')) {
        router.delete(`/admin/roles/${id}`)
    }
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">{{ t('nav.roles') }}</h1>
                    <p class="text-muted-foreground">{{ t('admin.manageRoles') || 'Manage roles and their permissions' }}</p>
                </div>
                <Link href="/admin/roles/create">
                    <Button>
                        <Plus class="me-2 h-4 w-4" />
                        {{ t('common.create') }}
                    </Button>
                </Link>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card v-for="role in roles" :key="role.id" class="relative overflow-hidden">
                    <CardHeader class="pb-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <Shield class="h-5 w-5 text-primary" />
                                <CardTitle class="text-base">{{ role.name }}</CardTitle>
                            </div>
                            <div class="flex gap-1">
                                <Link :href="`/admin/roles/${role.id}/edit`">
                                    <Button variant="ghost" size="icon" class="h-8 w-8">
                                        <Pencil class="h-3.5 w-3.5" />
                                    </Button>
                                </Link>
                                <Button
                                    v-if="role.users_count === 0"
                                    variant="ghost" size="icon" class="h-8 w-8"
                                    @click="deleteRole(role.id)"
                                >
                                    <Trash2 class="h-3.5 w-3.5 text-destructive" />
                                </Button>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-center justify-between text-sm text-muted-foreground">
                            <span>{{ role.permissions.length }} {{ t('admin.permissions') || 'permissions' }}</span>
                            <Badge variant="secondary">{{ role.users_count }} {{ t('nav.users') }}</Badge>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
