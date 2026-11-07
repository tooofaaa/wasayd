<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Checkbox } from '@/Components/ui/checkbox'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select'
import { Switch } from '@/Components/ui/switch'

const { t } = useI18n()

const props = defineProps<{
    roles: string[]
    branches: Array<{ id: number; name: string; name_ar: string }>
}>()

const form = useForm({
    name: '',
    name_ar: '',
    email: '',
    password: '',
    password_confirmation: '',
    phone: '',
    branch_id: null as number | null,
    locale: 'ar',
    is_active: true,
    roles: [] as string[],
})

function toggleRole(role: string) {
    const idx = form.roles.indexOf(role)
    if (idx >= 0) {
        form.roles.splice(idx, 1)
    } else {
        form.roles.push(role)
    }
}

function submit() {
    form.post('/admin/users')
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="mx-auto max-w-2xl space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold tracking-tight">{{ t('common.create') }} {{ t('nav.users') }}</h1>
                <Link href="/admin/users">
                    <Button variant="outline">{{ t('common.back') }}</Button>
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>{{ t('common.details') }}</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label>{{ t('auth.name') || 'Name' }} *</Label>
                                <Input v-model="form.name" :class="{ 'border-destructive': form.errors.name }" />
                                <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label>{{ t('auth.nameAr') || 'Name (Arabic)' }}</Label>
                                <Input v-model="form.name_ar" dir="rtl" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label>{{ t('auth.email') }} *</Label>
                            <Input v-model="form.email" type="email" :class="{ 'border-destructive': form.errors.email }" />
                            <p v-if="form.errors.email" class="text-sm text-destructive">{{ form.errors.email }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label>{{ t('auth.password') }} *</Label>
                                <Input v-model="form.password" type="password" :class="{ 'border-destructive': form.errors.password }" />
                                <p v-if="form.errors.password" class="text-sm text-destructive">{{ form.errors.password }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label>{{ t('auth.confirmPassword') }} *</Label>
                                <Input v-model="form.password_confirmation" type="password" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label>{{ t('auth.phone') || 'Phone' }}</Label>
                                <Input v-model="form.phone" />
                            </div>
                            <div class="space-y-2">
                                <Label>{{ t('common.language') }}</Label>
                                <Select v-model="form.locale">
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="ar">العربية</SelectItem>
                                        <SelectItem value="en">English</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label>{{ t('locations.branch') || 'Branch' }}</Label>
                            <Select v-model="form.branch_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select branch" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="b in branches" :key="b.id" :value="b.id">
                                        {{ b.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="flex items-center gap-2">
                            <Switch :checked="form.is_active" @update:checked="form.is_active = $event" />
                            <Label>{{ t('common.active') }}</Label>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{{ t('nav.roles') }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                            <div
                                v-for="role in roles"
                                :key="role"
                                class="flex items-center gap-2"
                            >
                                <Checkbox
                                    :checked="form.roles.includes(role)"
                                    @update:checked="toggleRole(role)"
                                />
                                <Label class="text-sm">{{ role }}</Label>
                            </div>
                        </div>
                        <p v-if="form.errors.roles" class="mt-2 text-sm text-destructive">{{ form.errors.roles }}</p>
                    </CardContent>
                </Card>

                <div class="flex justify-end gap-2">
                    <Link href="/admin/users">
                        <Button type="button" variant="outline">{{ t('common.cancel') }}</Button>
                    </Link>
                    <Button type="submit" :disabled="form.processing">
                        {{ t('common.save') }}
                    </Button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
