<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Checkbox } from '@/Components/ui/checkbox'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'

defineOptions({ layout: AppLayout })

const { t, locale } = useI18n()

interface Warehouse {
    id: number
    branch_id: number
    name: string
    name_ar: string | null
    code: string
    type: string
    address: string | null
    total_capacity: number | null
    temperature_min: number | null
    temperature_max: number | null
    humidity_min: number | null
    humidity_max: number | null
    is_active: boolean
}

interface Props {
    warehouse: Warehouse
    branches: Array<{ id: number; name: string; name_ar: string | null }>
    warehouseTypes: Array<{ value: string; label: string }>
}

const props = defineProps<Props>()

const form = useForm({
    branch_id: props.warehouse.branch_id.toString(),
    name: props.warehouse.name,
    name_ar: props.warehouse.name_ar || '',
    code: props.warehouse.code,
    type: props.warehouse.type,
    address: props.warehouse.address || '',
    total_capacity: props.warehouse.total_capacity?.toString() || '',
    temperature_min: props.warehouse.temperature_min?.toString() || '',
    temperature_max: props.warehouse.temperature_max?.toString() || '',
    humidity_min: props.warehouse.humidity_min?.toString() || '',
    humidity_max: props.warehouse.humidity_max?.toString() || '',
    is_active: props.warehouse.is_active,
})

function submit() {
    form.put(route('locations.warehouses.update', props.warehouse.id))
}
</script>

<template>
    <Head :title="t('locations.editWarehouse')" />

    <div class="space-y-6">
        <div class="flex items-center gap-4">
            <Link :href="route('locations.warehouses.index')">
                <Button variant="ghost" size="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                </Button>
            </Link>
            <div>
                <h1 class="text-3xl font-bold tracking-tight">{{ t('locations.editWarehouse') }}</h1>
                <p class="text-muted-foreground">{{ t('locations.editWarehouseDescription') }}</p>
            </div>
        </div>

        <form @submit.prevent="submit">
            <Card>
                <CardHeader>
                    <CardTitle>{{ t('locations.warehouseDetails') }}</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="branch_id">{{ t('locations.branch') }} *</Label>
                            <Select v-model="form.branch_id" required>
                                <SelectTrigger :class="{ 'border-destructive': form.errors.branch_id }">
                                    <SelectValue :placeholder="t('locations.selectBranch')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="branch in branches" :key="branch.id" :value="branch.id.toString()">
                                        {{ locale === 'ar' && branch.name_ar ? branch.name_ar : branch.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.branch_id" class="text-sm text-destructive">{{ form.errors.branch_id }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="type">{{ t('locations.type') }} *</Label>
                            <Select v-model="form.type" required>
                                <SelectTrigger :class="{ 'border-destructive': form.errors.type }">
                                    <SelectValue :placeholder="t('locations.selectType')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="wType in warehouseTypes" :key="wType.value" :value="wType.value">
                                        {{ wType.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.type" class="text-sm text-destructive">{{ form.errors.type }}</p>
                        </div>

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
                            <Label for="total_capacity">{{ t('locations.totalCapacity') }}</Label>
                            <Input id="total_capacity" v-model="form.total_capacity" type="number" step="0.01" />
                        </div>

                        <div class="space-y-2">
                            <Label for="temperature_min">{{ t('locations.temperatureMin') }}</Label>
                            <Input id="temperature_min" v-model="form.temperature_min" type="number" step="0.1" />
                        </div>

                        <div class="space-y-2">
                            <Label for="temperature_max">{{ t('locations.temperatureMax') }}</Label>
                            <Input id="temperature_max" v-model="form.temperature_max" type="number" step="0.1" />
                        </div>

                        <div class="space-y-2">
                            <Label for="humidity_min">{{ t('locations.humidityMin') }}</Label>
                            <Input id="humidity_min" v-model="form.humidity_min" type="number" step="0.1" />
                        </div>

                        <div class="space-y-2">
                            <Label for="humidity_max">{{ t('locations.humidityMax') }}</Label>
                            <Input id="humidity_max" v-model="form.humidity_max" type="number" step="0.1" />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="address">{{ t('locations.address') }}</Label>
                        <Input id="address" v-model="form.address" />
                    </div>

                    <div class="flex items-center gap-2">
                        <Checkbox id="is_active" v-model:checked="form.is_active" />
                        <Label for="is_active" class="cursor-pointer">{{ t('common.active') }}</Label>
                    </div>

                    <div class="flex justify-end gap-2 pt-4">
                        <Link :href="route('locations.warehouses.index')">
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
