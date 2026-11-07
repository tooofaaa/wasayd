<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Button } from '@/Components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Label } from '@/Components/ui/label'
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/Components/ui/select'
import { Input } from '@/Components/ui/input'
import { Textarea } from '@/Components/ui/textarea'
import { ArrowLeft, Save } from 'lucide-vue-next'

const { t } = useI18n()

const props = defineProps<{
    warehouses: any[]
    users: any[]
    zones: any[]
    categories: any[]
    countTypes: any[]
}>()

const form = useForm({
    warehouse_id: '',
    count_type: '',
    planned_date: '',
    assigned_to: '',
    zone_ids: [],
    category_ids: [],
    notes: '',
})

function submit() {
    form.post('/inventory/stock-counts')
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        {{ t('inventory.createStockCount') || 'Create Stock Count' }}
                    </h1>
                    <p class="text-muted-foreground">{{ t('inventory.createStockCountDesc') || 'Plan a physical inventory count' }}</p>
                </div>
                <Button variant="outline" as="a" href="/inventory/stock-counts">
                    <ArrowLeft class="me-2 h-4 w-4" />
                    {{ t('common.back') }}
                </Button>
            </div>

            <form @submit.prevent="submit">
                <Card>
                    <CardHeader>
                        <CardTitle>{{ t('inventory.countDetails') || 'Count Details' }}</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="warehouse_id">{{ t('locations.warehouse') || 'Warehouse' }}</Label>
                                <Select v-model="form.warehouse_id" required>
                                    <SelectTrigger id="warehouse_id">
                                        <SelectValue :placeholder="t('common.select')" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="wh in warehouses" :key="wh.id" :value="wh.id.toString()">
                                            {{ wh.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="space-y-2">
                                <Label for="count_type">{{ t('inventory.countType') || 'Count Type' }}</Label>
                                <Select v-model="form.count_type" required>
                                    <SelectTrigger id="count_type">
                                        <SelectValue :placeholder="t('common.select')" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="type in countTypes" :key="type.value" :value="type.value">
                                            {{ type.value }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="space-y-2">
                                <Label for="planned_date">{{ t('inventory.plannedDate') || 'Planned Date' }}</Label>
                                <Input id="planned_date" v-model="form.planned_date" type="date" required />
                            </div>

                            <div class="space-y-2">
                                <Label for="assigned_to">{{ t('inventory.assignedTo') || 'Assigned To' }}</Label>
                                <Select v-model="form.assigned_to">
                                    <SelectTrigger id="assigned_to">
                                        <SelectValue :placeholder="t('common.select')" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">{{ t('common.none') }}</SelectItem>
                                        <SelectItem v-for="user in users" :key="user.id" :value="user.id.toString()">
                                            {{ user.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="notes">{{ t('common.notes') }}</Label>
                            <Textarea id="notes" v-model="form.notes" rows="3" />
                        </div>

                        <div class="flex justify-end gap-2">
                            <Button type="button" variant="outline" @click="$inertia.visit('/inventory/stock-counts')">
                                {{ t('common.cancel') }}
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                <Save class="me-2 h-4 w-4" />
                                {{ t('common.create') }}
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
