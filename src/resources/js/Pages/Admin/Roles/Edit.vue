<script setup lang="ts">
import { computed } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Checkbox } from '@/Components/ui/checkbox'

const { t } = useI18n()

const props = defineProps<{
    role: { id: number; name: string; permissions: Array<{ id: number; name: string }> }
    permissions: Array<{ id: number; name: string; group: string }>
}>()

const form = useForm({
    name: props.role.name,
    permissions: props.role.permissions.map(p => p.name),
})

const permissionGroups = computed(() => {
    const groups: Record<string, Array<{ id: number; name: string }>> = {}
    for (const p of props.permissions) {
        const group = p.name.split('.')[0]
        if (!groups[group]) groups[group] = []
        groups[group].push(p)
    }
    return groups
})

function togglePermission(name: string) {
    const idx = form.permissions.indexOf(name)
    if (idx >= 0) form.permissions.splice(idx, 1)
    else form.permissions.push(name)
}

function toggleGroup(group: string) {
    const groupPerms = permissionGroups.value[group].map(p => p.name)
    const allSelected = groupPerms.every(p => form.permissions.includes(p))
    if (allSelected) {
        form.permissions = form.permissions.filter(p => !groupPerms.includes(p))
    } else {
        for (const p of groupPerms) {
            if (!form.permissions.includes(p)) form.permissions.push(p)
        }
    }
}

function isGroupSelected(group: string) {
    return permissionGroups.value[group].every(p => form.permissions.includes(p.name))
}

function submit() {
    form.put(`/admin/roles/${props.role.id}`)
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="mx-auto max-w-4xl space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold tracking-tight">{{ t('common.edit') }} {{ t('nav.roles') }}: {{ role.name }}</h1>
                <Link href="/admin/roles">
                    <Button variant="outline">{{ t('common.back') }}</Button>
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>{{ t('common.details') }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="max-w-sm space-y-2">
                            <Label>{{ t('auth.name') || 'Name' }} *</Label>
                            <Input v-model="form.name" :class="{ 'border-destructive': form.errors.name }" />
                            <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{{ t('admin.permissions') || 'Permissions' }}</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div v-for="(perms, group) in permissionGroups" :key="group" class="space-y-3">
                            <div class="flex items-center gap-2 border-b pb-2">
                                <Checkbox
                                    :checked="isGroupSelected(group as string)"
                                    @update:checked="toggleGroup(group as string)"
                                />
                                <Label class="text-sm font-semibold capitalize">{{ group }}</Label>
                            </div>
                            <div class="grid grid-cols-2 gap-2 ps-6 sm:grid-cols-3 lg:grid-cols-4">
                                <div v-for="perm in perms" :key="perm.id" class="flex items-center gap-2">
                                    <Checkbox
                                        :checked="form.permissions.includes(perm.name)"
                                        @update:checked="togglePermission(perm.name)"
                                    />
                                    <Label class="text-xs">{{ perm.name.split('.').slice(1).join('.') }}</Label>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <div class="flex justify-end gap-2">
                    <Link href="/admin/roles">
                        <Button type="button" variant="outline">{{ t('common.cancel') }}</Button>
                    </Link>
                    <Button type="submit" :disabled="form.processing">{{ t('common.save') }}</Button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
