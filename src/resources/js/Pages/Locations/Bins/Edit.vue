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

const props = defineProps<{ bin: any; shelves: any[] }>()

const form = useForm({
    shelf_id: props.bin.shelf_id.toString(),
    name: props.bin.name,
    name_ar: props.bin.name_ar || '',
    code: props.bin.code,
    barcode: props.bin.barcode || '',
    max_weight_kg: props.bin.max_weight_kg?.toString() || '',
    max_volume_m3: props.bin.max_volume_m3?.toString() || '',
    is_active: props.bin.is_active,
})

function submit() {
    form.put(route('locations.bins.update', props.bin.id))
}
</script>

<template>
    <Head :title="t('locations.editBin')" />
    <div class="space-y-6">
        <div class="flex items-center gap-4">
            <Link :href="route('locations.bins.index')">
                <Button variant="ghost" size="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                </Button>
            </Link>
            <h1 class="text-3xl font-bold">{{ t('locations.editBin') }}</h1>
        </div>
        <form @submit.prevent="submit">
            <Card>
                <CardHeader><CardTitle>{{ t('locations.binDetails') }}</CardTitle></CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label>{{ t('locations.shelf') }} *</Label>
                            <Select v-model="form.shelf_id" required>
                                <SelectTrigger><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="shelf in shelves" :key="shelf.id" :value="shelf.id.toString()">
                                        {{ locale === 'ar' && shelf.name_ar ? shelf.name_ar : shelf.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label>{{ t('locations.code') }} *</Label>
                            <Input v-model="form.code" required />
                        </div>
                        <div class="space-y-2">
                            <Label>{{ t('locations.nameEn') }} *</Label>
                            <Input v-model="form.name" required />
                        </div>
                        <div class="space-y-2">
                            <Label>{{ t('locations.nameAr') }}</Label>
                            <Input v-model="form.name_ar" />
                        </div>
                        <div class="space-y-2">
                            <Label>{{ t('locations.barcode') }}</Label>
                            <Input v-model="form.barcode" />
                        </div>
                        <div class="space-y-2">
                            <Label>{{ t('locations.maxWeight') }}</Label>
                            <Input v-model="form.max_weight_kg" type="number" step="0.01" />
                        </div>
                        <div class="space-y-2">
                            <Label>{{ t('locations.maxVolume') }}</Label>
                            <Input v-model="form.max_volume_m3" type="number" step="0.01" />
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <Checkbox id="is_active" v-model:checked="form.is_active" />
                        <Label for="is_active" class="cursor-pointer">{{ t('common.active') }}</Label>
                    </div>
                    <div class="flex justify-end gap-2 pt-4">
                        <Link :href="route('locations.bins.index')">
                            <Button variant="outline" type="button">{{ t('common.cancel') }}</Button>
                        </Link>
                        <Button type="submit" :disabled="form.processing">{{ form.processing ? t('common.loading') : t('common.save') }}</Button>
                    </div>
                </CardContent>
            </Card>
        </form>
    </div>
</template>

