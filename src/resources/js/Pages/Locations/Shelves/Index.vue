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
    shelves: any
    racks: any[]
    filters: any
}>()

const search = ref(props.filters.search || '')
const rackId = ref(props.filters.rack_id?.toString() || '')

function performSearch() {
    router.get(route('locations.shelves.index'), {
        search: search.value,
        rack_id: rackId.value || undefined,
    }, { preserveState: true, replace: true })
}

function deleteShelf(shelf: any) {
    if (confirm(t('locations.confirmDelete'))) {
        router.delete(route('locations.shelves.destroy', shelf.id))
    }
}
</script>

<template>
    <Head :title="t('nav.shelves')" />
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">{{ t('locations.shelves') }}</h1>
            </div>
            <Link :href="route('locations.shelves.create')">
                <Button><Plus class="h-4 w-4 me-2" />{{ t('locations.createShelf') }}</Button>
            </Link>
        </div>
        <Card>
            <CardHeader>
                <div class="flex gap-4">
                    <div class="relative flex-1">
                        <Search class="absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input v-model="search" :placeholder="t('common.search')" class="ps-9" @keyup.enter="performSearch" />
                    </div>
                    <Select v-model="rackId">
                        <SelectTrigger class="w-48">
                            <SelectValue :placeholder="t('locations.allRacks')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{ t('locations.allRacks') }}</SelectItem>
                            <SelectItem v-for="rack in racks" :key="rack.id" :value="rack.id.toString()">
                                {{ locale === 'ar' && rack.name_ar ? rack.name_ar : rack.name }}
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
                            <TableHead>{{ t('locations.rack') }}</TableHead>
                            <TableHead>{{ t('locations.level') }}</TableHead>
                            <TableHead>{{ t('locations.maxWeight') }}</TableHead>
                            <TableHead>{{ t('common.status') }}</TableHead>
                            <TableHead>{{ t('common.actions') }}</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="shelves.data.length === 0">
                            <TableCell colspan="7" class="text-center text-muted-foreground">{{ t('common.noData') }}</TableCell>
                        </TableRow>
                        <TableRow v-for="shelf in shelves.data" :key="shelf.id">
                            <TableCell class="font-medium">{{ shelf.code }}</TableCell>
                            <TableCell>{{ locale === 'ar' && shelf.name_ar ? shelf.name_ar : shelf.name }}</TableCell>
                            <TableCell>{{ locale === 'ar' && shelf.rack.name_ar ? shelf.rack.name_ar : shelf.rack.name }}</TableCell>
                            <TableCell>{{ shelf.level }}</TableCell>
                            <TableCell>{{ shelf.max_weight_kg ? `${shelf.max_weight_kg} kg` : '-' }}</TableCell>
                            <TableCell>
                                <Badge :variant="shelf.is_active ? 'default' : 'secondary'">
                                    {{ shelf.is_active ? t('common.active') : t('common.inactive') }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <div class="flex gap-2">
                                    <Link :href="route('locations.shelves.edit', shelf.id)">
                                        <Button variant="ghost" size="icon"><Pencil class="h-4 w-4" /></Button>
                                    </Link>
                                    <Button variant="ghost" size="icon" @click="deleteShelf(shelf)">
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

