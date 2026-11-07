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
    bins: any
    shelves: any[]
    filters: any
}>()

const search = ref(props.filters.search || '')
const shelfId = ref(props.filters.shelf_id?.toString() || '')

function performSearch() {
    router.get(route('locations.bins.index'), {
        search: search.value,
        shelf_id: shelfId.value || undefined,
    }, { preserveState: true, replace: true })
}

function deleteBin(bin: any) {
    if (confirm(t('locations.confirmDelete'))) {
        router.delete(route('locations.bins.destroy', bin.id))
    }
}
</script>

<template>
    <Head :title="t('nav.bins')" />
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">{{ t('locations.bins') }}</h1>
            </div>
            <Link :href="route('locations.bins.create')">
                <Button><Plus class="h-4 w-4 me-2" />{{ t('locations.createBin') }}</Button>
            </Link>
        </div>
        <Card>
            <CardHeader>
                <div class="flex gap-4">
                    <div class="relative flex-1">
                        <Search class="absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input v-model="search" :placeholder="t('common.search')" class="ps-9" @keyup.enter="performSearch" />
                    </div>
                    <Select v-model="shelfId">
                        <SelectTrigger class="w-48">
                            <SelectValue :placeholder="t('locations.allShelves')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{ t('locations.allShelves') }}</SelectItem>
                            <SelectItem v-for="shelf in shelves" :key="shelf.id" :value="shelf.id.toString()">
                                {{ locale === 'ar' && shelf.name_ar ? shelf.name_ar : shelf.name }}
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
                            <TableHead>{{ t('locations.barcode') }}</TableHead>
                            <TableHead>{{ t('locations.shelf') }}</TableHead>
                            <TableHead>{{ t('locations.maxWeight') }}</TableHead>
                            <TableHead>{{ t('locations.isOccupied') }}</TableHead>
                            <TableHead>{{ t('common.status') }}</TableHead>
                            <TableHead>{{ t('common.actions') }}</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="bins.data.length === 0">
                            <TableCell colspan="8" class="text-center text-muted-foreground">{{ t('common.noData') }}</TableCell>
                        </TableRow>
                        <TableRow v-for="bin in bins.data" :key="bin.id">
                            <TableCell class="font-medium">{{ bin.code }}</TableCell>
                            <TableCell>{{ locale === 'ar' && bin.name_ar ? bin.name_ar : bin.name }}</TableCell>
                            <TableCell>{{ bin.barcode || '-' }}</TableCell>
                            <TableCell>{{ locale === 'ar' && bin.shelf.name_ar ? bin.shelf.name_ar : bin.shelf.name }}</TableCell>
                            <TableCell>{{ bin.max_weight_kg ? `${bin.max_weight_kg} kg` : '-' }}</TableCell>
                            <TableCell>
                                <Badge :variant="bin.is_occupied ? 'destructive' : 'outline'">
                                    {{ bin.is_occupied ? t('common.yes') : t('common.no') }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <Badge :variant="bin.is_active ? 'default' : 'secondary'">
                                    {{ bin.is_active ? t('common.active') : t('common.inactive') }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <div class="flex gap-2">
                                    <Link :href="route('locations.bins.edit', bin.id)">
                                        <Button variant="ghost" size="icon"><Pencil class="h-4 w-4" /></Button>
                                    </Link>
                                    <Button variant="ghost" size="icon" @click="deleteBin(bin)">
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

