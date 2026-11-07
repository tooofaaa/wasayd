<script setup lang="ts">
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Card, CardContent, CardHeader } from '@/Components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table'
import { Badge } from '@/Components/ui/badge'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
import { Search, Plus, Pencil, Trash2 } from 'lucide-vue-next'

defineOptions({ layout: AppLayout })

const { t, locale } = useI18n()

const props = defineProps<{
    zones: any
    warehouses: any[]
    filters: any
}>()

const search = ref(props.filters.search || '')
const warehouseId = ref(props.filters.warehouse_id?.toString() || '')

function performSearch() {
    router.get(route('locations.zones.index'), {
        search: search.value,
        warehouse_id: warehouseId.value || undefined,
    }, { preserveState: true, replace: true })
}

function deleteZone(zone: any) {
    if (confirm(t('locations.confirmDelete'))) {
        router.delete(route('locations.zones.destroy', zone.id))
    }
}
</script>

<template>
    <Head :title="t('nav.zones')" />
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">{{ t('locations.zones') }}</h1>
            </div>
            <Link :href="route('locations.zones.create')">
                <Button><Plus class="h-4 w-4 me-2" />{{ t('locations.createZone') }}</Button>
            </Link>
        </div>
        <Card>
            <CardHeader>
                <div class="flex gap-4">
                    <div class="relative flex-1">
                        <Search class="absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input v-model="search" :placeholder="t('common.search')" class="ps-9" @keyup.enter="performSearch" />
                    </div>
                    <Select v-model="warehouseId">
                        <SelectTrigger class="w-48">
                            <SelectValue :placeholder="t('locations.allWarehouses')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{ t('locations.allWarehouses') }}</SelectItem>
                            <SelectItem v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id.toString()">
                                {{ locale === 'ar' && warehouse.name_ar ? warehouse.name_ar : warehouse.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <Button @click="performSearch">{{ t('common.filter') }}</Button>
                </div>
            </CardHeader>
            <CardContent>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>{{ t('locations.code') }}</TableHead>
                            <TableHead>{{ t('locations.name') }}</TableHead>
                            <TableHead>{{ t('locations.warehouse') }}</TableHead>
                            <TableHead>{{ t('common.status') }}</TableHead>
                            <TableHead>{{ t('common.actions') }}</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="zones.data.length === 0">
                            <TableCell colspan="5" class="text-center text-muted-foreground">{{ t('common.noData') }}</TableCell>
                        </TableRow>
                        <TableRow v-for="zone in zones.data" :key="zone.id">
                            <TableCell class="font-medium">{{ zone.code }}</TableCell>
                            <TableCell>{{ locale === 'ar' && zone.name_ar ? zone.name_ar : zone.name }}</TableCell>
                            <TableCell>{{ locale === 'ar' && zone.warehouse.name_ar ? zone.warehouse.name_ar : zone.warehouse.name }}</TableCell>
                            <TableCell>
                                <Badge :variant="zone.is_active ? 'default' : 'secondary'">
                                    {{ zone.is_active ? t('common.active') : t('common.inactive') }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <div class="flex gap-2">
                                    <Link :href="route('locations.zones.edit', zone.id)">
                                        <Button variant="ghost" size="icon"><Pencil class="h-4 w-4" /></Button>
                                    </Link>
                                    <Button variant="ghost" size="icon" @click="deleteZone(zone)">
                                        <Trash2 class="h-4 w-4 text-destructive" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </CardContent>
        </Card>
    </div>
</template>
