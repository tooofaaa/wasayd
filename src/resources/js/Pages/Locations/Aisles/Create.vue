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

const props = defineProps<{ zones: any[] }>()

const form = useForm({
    zone_id: '',
    name: '',
    name_ar: '',
    code: '',
    sequence: '0',
    is_active: true,
})

function submit() {
    form.post(route('locations.aisles.store'))
}
</script>

<template>
    <Head :title="t('locations.createAisle')" />
    <div class="space-y-6">
        <div class="flex items-center gap-4">
            <Link :href="route('locations.aisles.index')">
                <Button variant="ghost" size="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                </Button>
            </Link>
            <h1 class="text-3xl font-bold">{{ t('locations.createAisle') }}</h1>
        </div>
        <form @submit.prevent="submit">
            <Card>
                <CardHeader><CardTitle>{{ t('locations.aisleDetails') }}</CardTitle></CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label>{{ t('locations.zone') }} *</Label>
                            <Select v-model="form.zone_id" required>
                                <SelectTrigger><SelectValue :placeholder="t('locations.selectZone')" /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="zone in zones" :key="zone.id" :value="zone.id.toString()">
                                        {{ locale === 'ar' && zone.name_ar ? zone.name_ar : zone.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.zone_id" class="text-sm text-destructive">{{ form.errors.zone_id }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label>{{ t('locations.code') }} *</Label>
                            <Input v-model="form.code" required />
                            <p v-if="form.errors.code" class="text-sm text-destructive">{{ form.errors.code }}</p>
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
                            <Label>{{ t('locations.sequence') }}</Label>
                            <Input v-model="form.sequence" type="number" />
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <Checkbox id="is_active" v-model:checked="form.is_active" />
                        <Label for="is_active" class="cursor-pointer">{{ t('common.active') }}</Label>
                    </div>
                    <div class="flex justify-end gap-2 pt-4">
                        <Link :href="route('locations.aisles.index')">
                            <Button variant="outline" type="button">{{ t('common.cancel') }}</Button>
                        </Link>
                        <Button type="submit" :disabled="form.processing">{{ form.processing ? t('common.loading') : t('common.save') }}</Button>
                    </div>
                </CardContent>
            </Card>
        </form>
    </div>
</template>
