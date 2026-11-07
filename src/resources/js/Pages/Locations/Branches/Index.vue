<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table'
import { Badge } from '@/Components/ui/badge'
import { Search, Plus, Pencil, Trash2 } from 'lucide-vue-next'

defineOptions({ layout: AppLayout })

const { t } = useI18n()

interface Branch {
    id: number
    name: string
    name_ar: string | null
    code: string
    address: string | null
    city: string | null
    phone: string | null
    is_active: boolean
}

interface Props {
    branches: {
        data: Branch[]
        current_page: number
        last_page: number
        per_page: number
        total: number
    }
    filters: {
        search: string | null
    }
}

const props = defineProps<Props>()

const search = ref(props.filters.search || '')

function performSearch() {
    router.get(route('locations.branches.index'), { search: search.value }, {
        preserveState: true,
        replace: true,
    })
}

function deleteBranch(branch: Branch) {
    if (confirm(t('locations.confirmDelete'))) {
        router.delete(route('locations.branches.destroy', branch.id))
    }
}
</script>

<template>
    <Head :title="t('nav.branches')" />

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">{{ t('nav.branches') }}</h1>
                <p class="text-muted-foreground">{{ t('locations.branchesDescription') }}</p>
            </div>
            <Link :href="route('locations.branches.create')">
                <Button>
                    <Plus class="h-4 w-4 me-2" />
                    {{ t('locations.createBranch') }}
                </Button>
            </Link>
        </div>

        <Card>
            <CardHeader>
                <div class="flex items-center gap-4">
                    <div class="relative flex-1">
                        <Search class="absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            v-model="search"
                            :placeholder="t('common.search')"
                            class="ps-9"
                            @keyup.enter="performSearch"
                        />
                    </div>
                    <Button @click="performSearch">{{ t('common.filter') }}</Button>
                </div>
            </CardHeader>
            <CardContent>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>{{ t('locations.code') }}</TableHead>
                            <TableHead>{{ t('locations.nameEn') }}</TableHead>
                            <TableHead>{{ t('locations.nameAr') }}</TableHead>
                            <TableHead>{{ t('locations.city') }}</TableHead>
                            <TableHead>{{ t('locations.phone') }}</TableHead>
                            <TableHead>{{ t('common.status') }}</TableHead>
                            <TableHead>{{ t('common.actions') }}</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="branches.data.length === 0">
                            <TableCell colspan="7" class="text-center text-muted-foreground">
                                {{ t('common.noData') }}
                            </TableCell>
                        </TableRow>
                        <TableRow v-for="branch in branches.data" :key="branch.id">
                            <TableCell class="font-medium">{{ branch.code }}</TableCell>
                            <TableCell>{{ branch.name }}</TableCell>
                            <TableCell>{{ branch.name_ar || '-' }}</TableCell>
                            <TableCell>{{ branch.city || '-' }}</TableCell>
                            <TableCell>{{ branch.phone || '-' }}</TableCell>
                            <TableCell>
                                <Badge :variant="branch.is_active ? 'default' : 'secondary'">
                                    {{ branch.is_active ? t('common.active') : t('common.inactive') }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center gap-2">
                                    <Link :href="route('locations.branches.edit', branch.id)">
                                        <Button variant="ghost" size="icon">
                                            <Pencil class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        @click="deleteBranch(branch)"
                                    >
                                        <Trash2 class="h-4 w-4 text-destructive" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <div v-if="branches.last_page > 1" class="mt-4 flex items-center justify-between">
                    <div class="text-sm text-muted-foreground">
                        {{ t('common.showing', {
                            from: (branches.current_page - 1) * branches.per_page + 1,
                            to: Math.min(branches.current_page * branches.per_page, branches.total),
                            total: branches.total
                        }) }}
                    </div>
                    <div class="flex gap-2">
                        <Link
                            v-if="branches.current_page > 1"
                            :href="route('locations.branches.index', { page: branches.current_page - 1, search: search })"
                            preserve-state
                        >
                            <Button variant="outline">{{ t('common.previous') }}</Button>
                        </Link>
                        <Link
                            v-if="branches.current_page < branches.last_page"
                            :href="route('locations.branches.index', { page: branches.current_page + 1, search: search })"
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
