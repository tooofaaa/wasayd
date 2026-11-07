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
    aisles: any
    zones: any[]
    filters: any
}>()

const search = ref(props.filters.search || '')
const zoneId = ref(props.filters.zone_id?.toString() || '')

function performSearch() {
    router.get(route('locations.aisles.index'), {
        search: search.value,
        zone_id: zoneId.value || undefined,
    }, { preserveState: true, replace: true })
}

function deleteAisle(aisle: any) {
    if (confirm(t('locations.confirmDelete'))) {
        router.delete(route('locations.aisles.destroy', aisle.id))
    }
}
</script>

<template>
    <Head :title="t('locations.aisles')" />
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">{{ t('locations.aisles') }}</h1>
            </div>
            <Link :href="route('locations.aisles.create')">
                <Button><Plus class="h-4 w-4 me-2" />{{ t('locations.createAisle') }}</Button>
            </Link>
        </div>
        <Card>
            <CardHeader>
                <div class="flex gap-4">
                    <div class="relative flex-1">
                        <Search class="absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input v-model="search" :placeholder="t('common.search')" class="ps-9" @keyup.enter="performSearch" />
                    </div>
                    <Select v-model="zoneId">
                        <SelectTrigger class="w-48">
                            <SelectValue :placeholder="t('locations.allZones')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{ t('locations.allZones') }}</SelectItem>
                            <SelectItem v-for="zone in zones" :key="zone.id" :value="zone.id.toString()">
                                {{ locale === 'ar' && zone.name_ar ? zone.name_ar : zone.name }}
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
                            <TableHead>{{ t('locations.zone') }}</TableHead>
                            <TableHead>{{ t('locations.sequence') }}</TableHead>
                            <TableHead>{{ t('common.status') }}</TableHead>
                            <TableHead>{{ t('common.actions') }}</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="aisles.data.length === 0">
                            <TableCell colspan="6" class="text-center text-muted-foreground">{{ t('common.noData') }}</TableCell>
                        </TableRow>
                        <TableRow v-for="aisle in aisles.data" :key="aisle.id">
                            <TableCell class="font-medium">{{ aisle.code }}</TableCell>
                            <TableCell>{{ locale === 'ar' && aisle.name_ar ? aisle.name_ar : aisle.name }}</TableCell>
                            <TableCell>{{ locale === 'ar' && aisle.zone.name_ar ? aisle.zone.name_ar : aisle.zone.name }}</TableCell>
                            <TableCell>{{ aisle.sequence }}</TableCell>
                            <TableCell>
                                <Badge :variant="aisle.is_active ? 'default' : 'secondary'">
                                    {{ aisle.is_active ? t('common.active') : t('common.inactive') }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <div class="flex gap-2">
                                    <Link :href="route('locations.aisles.edit', aisle.id)">
                                        <Button variant="ghost" size="icon"><Pencil class="h-4 w-4" /></Button>
                                    </Link>
                                    <Button variant="ghost" size="icon" @click="deleteAisle(aisle)">
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
