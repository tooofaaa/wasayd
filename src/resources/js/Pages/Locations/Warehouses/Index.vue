<script setup lang="ts">
import { ref, computed } from 'vue'
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

interface Warehouse {
    id: number
    name: string
    name_ar: string | null
    code: string
    type: string
    branch: { id: number; name: string; name_ar: string | null }
    is_active: boolean
}

interface Props {
    warehouses: {
        data: Warehouse[]
        current_page: number
        last_page: number
        per_page: number
        total: number
    }
    branches: Array<{ id: number; name: string; name_ar: string | null }>
    warehouseTypes: Array<{ value: string; label: string }>
    filters: {
        search: string | null
        branch_id: number | null
        type: string | null
    }
}

const props = defineProps<Props>()

const search = ref(props.filters.search || '')
const branchId = ref(props.filters.branch_id?.toString() || '')
const type = ref(props.filters.type || '')

function performSearch() {
    router.get(route('locations.warehouses.index'), {
        search: search.value,
        branch_id: branchId.value || undefined,
        type: type.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    })
}

function deleteWarehouse(warehouse: Warehouse) {
    if (confirm(t('locations.confirmDelete'))) {
        router.delete(route('locations.warehouses.destroy', warehouse.id))
    }
}
</script>

<template>
    <Head :title="t('nav.warehouses')" />

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">{{ t('nav.warehouses') }}</h1>
                <p class="text-muted-foreground">{{ t('locations.warehousesDescription') }}</p>
            </div>
            <Link :href="route('locations.warehouses.create')">
                <Button>
                    <Plus class="h-4 w-4 me-2" />
                    {{ t('locations.createWarehouse') }}
                </Button>
            </Link>
        </div>

        <Card>
            <CardHeader>
                <div class="flex flex-col gap-4 md:flex-row">
                    <div class="relative flex-1">
                        <Search class="absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            v-model="search"
                            :placeholder="t('common.search')"
                            class="ps-9"
                            @keyup.enter="performSearch"
                        />
                    </div>
                    <Select v-model="branchId">
                        <SelectTrigger class="w-full md:w-48">
                            <SelectValue :placeholder="t('locations.allBranches')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{ t('locations.allBranches') }}</SelectItem>
                            <SelectItem v-for="branch in branches" :key="branch.id" :value="branch.id.toString()">
                                {{ locale === 'ar' && branch.name_ar ? branch.name_ar : branch.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <Select v-model="type">
                        <SelectTrigger class="w-full md:w-48">
                            <SelectValue :placeholder="t('locations.allTypes')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{ t('locations.allTypes') }}</SelectItem>
                            <SelectItem v-for="wType in warehouseTypes" :key="wType.value" :value="wType.value">
                                {{ wType.label }}
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
                            <TableHead>{{ t('locations.branch') }}</TableHead>
                            <TableHead>{{ t('locations.type') }}</TableHead>
                            <TableHead>{{ t('common.status') }}</TableHead>
                            <TableHead>{{ t('common.actions') }}</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="warehouses.data.length === 0">
                            <TableCell colspan="6" class="text-center text-muted-foreground">
                                {{ t('common.noData') }}
                            </TableCell>
                        </TableRow>
                        <TableRow v-for="warehouse in warehouses.data" :key="warehouse.id">
                            <TableCell class="font-medium">{{ warehouse.code }}</TableCell>
                            <TableCell>
                                {{ locale === 'ar' && warehouse.name_ar ? warehouse.name_ar : warehouse.name }}
                            </TableCell>
                            <TableCell>
                                {{ locale === 'ar' && warehouse.branch.name_ar ? warehouse.branch.name_ar : warehouse.branch.name }}
                            </TableCell>
                            <TableCell>
                                <Badge variant="outline">{{ warehouse.type.replace('_', ' ') }}</Badge>
                            </TableCell>
                            <TableCell>
                                <Badge :variant="warehouse.is_active ? 'default' : 'secondary'">
                                    {{ warehouse.is_active ? t('common.active') : t('common.inactive') }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center gap-2">
                                    <Link :href="route('locations.warehouses.edit', warehouse.id)">
                                        <Button variant="ghost" size="icon">
                                            <Pencil class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        @click="deleteWarehouse(warehouse)"
                                    >
                                        <Trash2 class="h-4 w-4 text-destructive" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <div v-if="warehouses.last_page > 1" class="mt-4 flex items-center justify-between">
                    <div class="text-sm text-muted-foreground">
                        {{ t('common.showing', {
                            from: (warehouses.current_page - 1) * warehouses.per_page + 1,
                            to: Math.min(warehouses.current_page * warehouses.per_page, warehouses.total),
                            total: warehouses.total
                        }) }}
                    </div>
                    <div class="flex gap-2">
                        <Link
                            v-if="warehouses.current_page > 1"
                            :href="route('locations.warehouses.index', { page: warehouses.current_page - 1, search, branch_id: branchId, type })"
                            preserve-state
                        >
                            <Button variant="outline">{{ t('common.previous') }}</Button>
                        </Link>
                        <Link
                            v-if="warehouses.current_page < warehouses.last_page"
                            :href="route('locations.warehouses.index', { page: warehouses.current_page + 1, search, branch_id: branchId, type })"
                            preserve-state
                        >
                            <Button variant="outline">{{ t('common.next') }}</Button>
                        </Link>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
