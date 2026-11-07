<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Checkbox } from '@/Components/ui/checkbox'

defineOptions({ layout: AppLayout })

const { t } = useI18n()

interface Branch {
    id: number
    name: string
    name_ar: string | null
    code: string
    address: string | null
    city: string | null
    phone: string | null
    manager_name: string | null
    is_active: boolean
}

const props = defineProps<{
    branch: Branch
}>()

const form = useForm({
    name: props.branch.name,
    name_ar: props.branch.name_ar || '',
    code: props.branch.code,
    address: props.branch.address || '',
    city: props.branch.city || '',
    phone: props.branch.phone || '',
    manager_name: props.branch.manager_name || '',
    is_active: props.branch.is_active,
})

function submit() {
    form.put(route('locations.branches.update', props.branch.id))
}
</script>

<template>
    <Head :title="t('locations.editBranch')" />

    <div class="space-y-6">
        <div class="flex items-center gap-4">
            <Link :href="route('locations.branches.index')">
                <Button variant="ghost" size="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                </Button>
            </Link>
            <div>
                <h1 class="text-3xl font-bold tracking-tight">{{ t('locations.editBranch') }}</h1>
                <p class="text-muted-foreground">{{ t('locations.editBranchDescription') }}</p>
            </div>
        </div>

        <form @submit.prevent="submit">
            <Card>
                <CardHeader>
                    <CardTitle>{{ t('locations.branchDetails') }}</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="name">{{ t('locations.nameEn') }} *</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                :class="{ 'border-destructive': form.errors.name }"
                                required
                            />
                            <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="name_ar">{{ t('locations.nameAr') }}</Label>
                            <Input id="name_ar" v-model="form.name_ar" />
                            <p v-if="form.errors.name_ar" class="text-sm text-destructive">{{ form.errors.name_ar }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="code">{{ t('locations.code') }} *</Label>
                            <Input
                                id="code"
                                v-model="form.code"
                                :class="{ 'border-destructive': form.errors.code }"
                                required
                            />
                            <p v-if="form.errors.code" class="text-sm text-destructive">{{ form.errors.code }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="city">{{ t('locations.city') }}</Label>
                            <Input id="city" v-model="form.city" />
                            <p v-if="form.errors.city" class="text-sm text-destructive">{{ form.errors.city }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="phone">{{ t('locations.phone') }}</Label>
                            <Input id="phone" v-model="form.phone" type="tel" />
                            <p v-if="form.errors.phone" class="text-sm text-destructive">{{ form.errors.phone }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="manager_name">{{ t('locations.managerName') }}</Label>
                            <Input id="manager_name" v-model="form.manager_name" />
                            <p v-if="form.errors.manager_name" class="text-sm text-destructive">{{ form.errors.manager_name }}</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="address">{{ t('locations.address') }}</Label>
                        <Input id="address" v-model="form.address" />
                        <p v-if="form.errors.address" class="text-sm text-destructive">{{ form.errors.address }}</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <Checkbox id="is_active" v-model:checked="form.is_active" />
                        <Label for="is_active" class="cursor-pointer">{{ t('common.active') }}</Label>
                    </div>

                    <div class="flex justify-end gap-2 pt-4">
                        <Link :href="route('locations.branches.index')">
                            <Button variant="outline" type="button">{{ t('common.cancel') }}</Button>
                        </Link>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? t('common.loading') : t('common.save') }}
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </form>
    </div>
</template>
