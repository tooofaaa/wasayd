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
    racks: any
    aisles: any[]
    filters: any
}>()

const search = ref(props.filters.search || '')
const aisleId = ref(props.filters.aisle_id?.toString() || '')

function performSearch() {
    router.get(route('locations.racks.index'), {
        search: search.value,
        aisle_id: aisleId.value || undefined,
    }, { preserveState: true, replace: true })
}

function deleteRack(rack: any) {
    if (confirm(t('locations.confirmDelete'))) {
        router.delete(route('locations.racks.destroy', rack.id))
    }
}
</script>

<template>
    <Head :title="t('locations.racks')" />
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">{{ t('locations.racks') }}</h1>
            </div>
            <Link :href="route('locations.racks.create')">
                <Button><Plus class="h-4 w-4 me-2" />{{ t('locations.createRack') }}</Button>
            </Link>
        </div>
        <Card>
            <CardHeader>
                <div class="flex gap-4">
                    <div class="relative flex-1">
                        <Search class="absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input v-model="search" :placeholder="t('common.search')" class="ps-9" @keyup.enter="performSearch" />
                    </div>
                    <Select v-model="aisleId">
                        <SelectTrigger class="w-48">
                            <SelectValue :placeholder="t('locations.allAisles')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{ t('locations.allAisles') }}</SelectItem>
                            <SelectItem v-for="aisle in aisles" :key="aisle.id" :value="aisle.id.toString()">
                                {{ locale === 'ar' && aisle.name_ar ? aisle.name_ar : aisle.name }}
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
                            <TableHead>{{ t('locations.aisle') }}</TableHead>
                            <TableHead>{{ t('locations.levels') }}</TableHead>
                            <TableHead>{{ t('locations.maxWeight') }}</TableHead>
                            <TableHead>{{ t('common.status') }}</TableHead>
                            <TableHead>{{ t('common.actions') }}</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="racks.data.length === 0">
                            <TableCell colspan="7" class="text-center text-muted-foreground">{{ t('common.noData') }}</TableCell>
                        </TableRow>
                        <TableRow v-for="rack in racks.data" :key="rack.id">
                            <TableCell class="font-medium">{{ rack.code }}</TableCell>
                            <TableCell>{{ locale === 'ar' && rack.name_ar ? rack.name_ar : rack.name }}</TableCell>
                            <TableCell>{{ locale === 'ar' && rack.aisle.name_ar ? rack.aisle.name_ar : rack.aisle.name }}</TableCell>
                            <TableCell>{{ rack.levels }}</TableCell>
                            <TableCell>{{ rack.max_weight_kg ? `${rack.max_weight_kg} kg` : '-' }}</TableCell>
                            <TableCell>
                                <Badge :variant="rack.is_active ? 'default' : 'secondary'">
                                    {{ rack.is_active ? t('common.active') : t('common.inactive') }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <div class="flex gap-2">
                                    <Link :href="route('locations.racks.edit', rack.id)">
                                        <Button variant="ghost" size="icon"><Pencil class="h-4 w-4" /></Button>
                                    </Link>
                                    <Button variant="ghost" size="icon" @click="deleteRack(rack)">
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
