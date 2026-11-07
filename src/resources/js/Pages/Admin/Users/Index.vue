<script setup lang="ts">
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Badge } from '@/Components/ui/badge'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select'
import { Plus, Pencil, Trash2, Search } from 'lucide-vue-next'

const { t } = useI18n()

const props = defineProps<{
    users: any
    roles: string[]
    filters: { search?: string; role?: string; is_active?: string }
}>()

const search = ref(props.filters.search ?? '')
const roleFilter = ref(props.filters.role ?? '')

function applyFilters() {
    router.get('/admin/users', {
        search: search.value || undefined,
        role: roleFilter.value || undefined,
    }, { preserveState: true })
}

function deleteUser(id: number) {
    if (confirm(t('common.confirmDelete') || 'Are you sure?')) {
        router.delete(`/admin/users/${id}`)
    }
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">{{ t('nav.users') }}</h1>
                    <p class="text-muted-foreground">{{ t('admin.manageUsers') || 'Manage system users and their roles' }}</p>
                </div>
                <Link href="/admin/users/create">
                    <Button>
                        <Plus class="me-2 h-4 w-4" />
                        {{ t('common.create') }}
                    </Button>
                </Link>
            </div>

            <Card>
                <CardHeader>
                    <div class="flex items-center gap-4">
                        <div class="relative flex-1">
                            <Search class="absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                v-model="search"
                                :placeholder="t('common.search')"
                                class="ps-10"
                                @keyup.enter="applyFilters"
                            />
                        </div>
                        <Select v-model="roleFilter" @update:model-value="applyFilters">
                            <SelectTrigger class="w-48">
                                <SelectValue :placeholder="t('admin.filterByRole') || 'Filter by role'" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{{ t('common.all') }}</SelectItem>
                                <SelectItem v-for="role in roles" :key="role" :value="role">
                                    {{ role }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{{ t('auth.name') || 'Name' }}</TableHead>
                                <TableHead>{{ t('auth.email') }}</TableHead>
                                <TableHead>{{ t('admin.roles') || 'Roles' }}</TableHead>
                                <TableHead>{{ t('common.status') }}</TableHead>
                                <TableHead class="text-end">{{ t('common.actions') }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="user in users.data" :key="user.id">
                                <TableCell class="font-medium">
                                    <div>{{ user.name }}</div>
                                    <div v-if="user.name_ar" class="text-xs text-muted-foreground">{{ user.name_ar }}</div>
                                </TableCell>
                                <TableCell>{{ user.email }}</TableCell>
                                <TableCell>
                                    <div class="flex flex-wrap gap-1">
                                        <Badge v-for="role in user.roles" :key="role.id" variant="secondary">
                                            {{ role.name }}
                                        </Badge>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="user.is_active ? 'default' : 'destructive'">
                                        {{ user.is_active ? t('common.active') : t('common.inactive') }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-end">
                                    <div class="flex items-center justify-end gap-1">
                                        <Link :href="`/admin/users/${user.id}/edit`">
                                            <Button variant="ghost" size="icon">
                                                <Pencil class="h-4 w-4" />
                                            </Button>
                                        </Link>
                                        <Button variant="ghost" size="icon" @click="deleteUser(user.id)">
                                            <Trash2 class="h-4 w-4 text-destructive" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="!users.data?.length">
                                <TableCell :colspan="5" class="text-center py-8 text-muted-foreground">
                                    {{ t('common.noData') }}
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
