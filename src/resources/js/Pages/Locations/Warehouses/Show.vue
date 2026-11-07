<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PageHeader from '@/Components/shared/PageHeader.vue'
import StatusBadge from '@/Components/shared/StatusBadge.vue'
import { Button } from '@/Components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Badge } from '@/Components/ui/badge'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import {
  Dialog,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/Components/ui/dialog'
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/Components/ui/collapsible'
import { Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { ref, onMounted } from 'vue'
import {
  Pencil, ArrowLeft, Plus, Trash2, ChevronRight, ChevronDown,
  MapPin, LayoutGrid, Columns3, ShelfIcon, Box, Thermometer,
} from 'lucide-vue-next'

const { t, locale } = useI18n()
const props = defineProps<{ warehouse: any }>()
const displayName = locale.value === 'ar' ? (props.warehouse.name_ar || props.warehouse.name) : props.warehouse.name

const zones = ref<any[]>(props.warehouse.zones ?? [])
const openZones = ref<Set<number>>(new Set())
const openAisles = ref<Set<number>>(new Set())
const openRacks = ref<Set<number>>(new Set())
const openShelves = ref<Set<number>>(new Set())

const dialogOpen = ref(false)
const dialogType = ref<'zone' | 'aisle' | 'rack' | 'shelf' | 'bin'>('zone')
const dialogMode = ref<'create' | 'edit'>('create')
const dialogParentId = ref<number>(0)
const dialogEditId = ref<number>(0)
const dialogForm = ref({ name: '', code: '', type: '' })

function toggleZone(id: number) { openZones.value.has(id) ? openZones.value.delete(id) : openZones.value.add(id) }
function toggleAisle(id: number) { openAisles.value.has(id) ? openAisles.value.delete(id) : openAisles.value.add(id) }
function toggleRack(id: number) { openRacks.value.has(id) ? openRacks.value.delete(id) : openRacks.value.add(id) }
function toggleShelf(id: number) { openShelves.value.has(id) ? openShelves.value.delete(id) : openShelves.value.add(id) }

function openCreateDialog(type: typeof dialogType.value, parentId: number) {
  dialogType.value = type
  dialogMode.value = 'create'
  dialogParentId.value = parentId
  dialogForm.value = { name: '', code: '', type: '' }
  dialogOpen.value = true
}

async function submitDialog() {
  const f = dialogForm.value
  let url = ''
  const base = '/locations/hierarchy'

  switch (dialogType.value) {
    case 'zone': url = `${base}/warehouses/${dialogParentId.value}/zones`; break
    case 'aisle': url = `${base}/zones/${dialogParentId.value}/aisles`; break
    case 'rack': url = `${base}/aisles/${dialogParentId.value}/racks`; break
    case 'shelf': url = `${base}/racks/${dialogParentId.value}/shelves`; break
    case 'bin': url = `${base}/shelves/${dialogParentId.value}/bins`; break
  }

  const res = await fetch(url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-XSRF-TOKEN': decodeURIComponent(document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ?? ''),
      'Accept': 'application/json',
    },
    body: JSON.stringify(f),
  })

  if (res.ok) {
    await refreshZones()
    dialogOpen.value = false
  }
}

async function deleteNode(type: string, id: number) {
  const base = '/locations/hierarchy'
  const url = `${base}/${type}s/${id}`
  await fetch(url, {
    method: 'DELETE',
    headers: {
      'X-XSRF-TOKEN': decodeURIComponent(document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ?? ''),
      'Accept': 'application/json',
    },
  })
  await refreshZones()
}

async function refreshZones() {
  const res = await fetch(`/locations/hierarchy/warehouses/${props.warehouse.id}/zones`, {
    headers: { 'Accept': 'application/json' },
  })
  if (res.ok) zones.value = await res.json()
}

const typeColors: Record<string, string> = {
  receiving: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
  storage: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
  picking: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
  shipping: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
  quarantine: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
  controlled: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
}
</script>

<template>
  <AuthenticatedLayout>
    <div class="space-y-6 p-6">
      <PageHeader :title="displayName" :description="warehouse.code">
        <template #actions>
          <Link :href="route('locations.warehouses.index')">
            <Button variant="outline">
              <ArrowLeft class="me-2 h-4 w-4" />
              {{ t('common.back') }}
            </Button>
          </Link>
          <Link :href="route('locations.warehouses.edit', warehouse.id)">
            <Button>
              <Pencil class="me-2 h-4 w-4" />
              {{ t('common.edit') }}
            </Button>
          </Link>
        </template>
      </PageHeader>

      <!-- Info cards -->
      <div class="grid gap-4 md:grid-cols-4">
        <Card>
          <CardHeader class="pb-2">
            <CardTitle class="text-sm text-muted-foreground">{{ t('common.status') }}</CardTitle>
          </CardHeader>
          <CardContent><StatusBadge :status="warehouse.is_active" /></CardContent>
        </Card>
        <Card>
          <CardHeader class="pb-2">
            <CardTitle class="text-sm text-muted-foreground">{{ t('locations.type') }}</CardTitle>
          </CardHeader>
          <CardContent><Badge variant="outline" class="capitalize">{{ warehouse.type?.replace('_', ' ') }}</Badge></CardContent>
        </Card>
        <Card>
          <CardHeader class="pb-2">
            <CardTitle class="text-sm text-muted-foreground">{{ t('locations.branch') }}</CardTitle>
          </CardHeader>
          <CardContent class="text-sm font-medium">
            {{ warehouse.branch ? (locale === 'ar' ? (warehouse.branch.name_ar || warehouse.branch.name) : warehouse.branch.name) : '-' }}
          </CardContent>
        </Card>
        <Card v-if="warehouse.temperature_min || warehouse.temperature_max">
          <CardHeader class="pb-2">
            <CardTitle class="flex items-center gap-1 text-sm text-muted-foreground">
              <Thermometer class="h-4 w-4" />
              {{ t('locations.temperature') }}
            </CardTitle>
          </CardHeader>
          <CardContent class="text-sm font-medium">
            {{ warehouse.temperature_min }}°C - {{ warehouse.temperature_max }}°C
          </CardContent>
        </Card>
      </div>

      <!-- Location Hierarchy -->
      <Card>
        <CardHeader class="flex flex-row items-center justify-between">
          <CardTitle>{{ t('locations.locationHierarchy') }}</CardTitle>
          <Button size="sm" @click="openCreateDialog('zone', warehouse.id)">
            <Plus class="me-2 h-4 w-4" />
            {{ t('locations.addZone') }}
          </Button>
        </CardHeader>
        <CardContent>
          <div v-if="zones.length === 0" class="py-8 text-center text-muted-foreground">
            {{ t('locations.noZonesYet') }}
          </div>

          <div class="space-y-1">
            <!-- Zones -->
            <div v-for="zone in zones" :key="zone.id" class="rounded-md border">
              <div class="flex items-center gap-2 p-2 hover:bg-muted/50 cursor-pointer" @click="toggleZone(zone.id)">
                <component :is="openZones.has(zone.id) ? ChevronDown : ChevronRight" class="h-4 w-4 shrink-0 text-muted-foreground" />
                <MapPin class="h-4 w-4 shrink-0 text-primary" />
                <span class="font-medium">{{ zone.name }}</span>
                <span class="font-mono text-xs text-muted-foreground">{{ zone.code }}</span>
                <span v-if="zone.type" :class="['rounded px-1.5 py-0.5 text-xs', typeColors[zone.type] ?? '']">{{ zone.type }}</span>
                <div class="ms-auto flex items-center gap-1">
                  <Button variant="ghost" size="icon" class="h-6 w-6" @click.stop="openCreateDialog('aisle', zone.id)">
                    <Plus class="h-3 w-3" />
                  </Button>
                  <Button variant="ghost" size="icon" class="h-6 w-6" @click.stop="deleteNode('zone', zone.id)">
                    <Trash2 class="h-3 w-3 text-destructive" />
                  </Button>
                </div>
              </div>

              <div v-if="openZones.has(zone.id)" class="ms-6 space-y-1 pb-2">
                <!-- Aisles -->
                <div v-for="aisle in zone.aisles" :key="aisle.id" class="rounded border-s-2 border-muted">
                  <div class="flex items-center gap-2 p-2 hover:bg-muted/50 cursor-pointer" @click="toggleAisle(aisle.id)">
                    <component :is="openAisles.has(aisle.id) ? ChevronDown : ChevronRight" class="h-3 w-3 shrink-0 text-muted-foreground" />
                    <Columns3 class="h-4 w-4 shrink-0 text-blue-500" />
                    <span class="text-sm">{{ aisle.name }}</span>
                    <span class="font-mono text-xs text-muted-foreground">{{ aisle.code }}</span>
                    <div class="ms-auto flex items-center gap-1">
                      <Button variant="ghost" size="icon" class="h-6 w-6" @click.stop="openCreateDialog('rack', aisle.id)">
                        <Plus class="h-3 w-3" />
                      </Button>
                      <Button variant="ghost" size="icon" class="h-6 w-6" @click.stop="deleteNode('aisle', aisle.id)">
                        <Trash2 class="h-3 w-3 text-destructive" />
                      </Button>
                    </div>
                  </div>

                  <div v-if="openAisles.has(aisle.id)" class="ms-6 space-y-1 pb-2">
                    <!-- Racks -->
                    <div v-for="rack in aisle.racks" :key="rack.id" class="rounded border-s-2 border-muted">
                      <div class="flex items-center gap-2 p-2 hover:bg-muted/50 cursor-pointer" @click="toggleRack(rack.id)">
                        <component :is="openRacks.has(rack.id) ? ChevronDown : ChevronRight" class="h-3 w-3 shrink-0 text-muted-foreground" />
                        <LayoutGrid class="h-4 w-4 shrink-0 text-orange-500" />
                        <span class="text-sm">{{ rack.name }}</span>
                        <span class="font-mono text-xs text-muted-foreground">{{ rack.code }}</span>
                        <div class="ms-auto flex items-center gap-1">
                          <Button variant="ghost" size="icon" class="h-6 w-6" @click.stop="openCreateDialog('shelf', rack.id)">
                            <Plus class="h-3 w-3" />
                          </Button>
                          <Button variant="ghost" size="icon" class="h-6 w-6" @click.stop="deleteNode('rack', rack.id)">
                            <Trash2 class="h-3 w-3 text-destructive" />
                          </Button>
                        </div>
                      </div>

                      <div v-if="openRacks.has(rack.id)" class="ms-6 space-y-1 pb-2">
                        <!-- Shelves -->
                        <div v-for="shelf in rack.shelves" :key="shelf.id" class="rounded border-s-2 border-muted">
                          <div class="flex items-center gap-2 p-2 hover:bg-muted/50 cursor-pointer" @click="toggleShelf(shelf.id)">
                            <component :is="openShelves.has(shelf.id) ? ChevronDown : ChevronRight" class="h-3 w-3 shrink-0 text-muted-foreground" />
                            <LayoutGrid class="h-4 w-4 shrink-0 text-green-500" />
                            <span class="text-sm">{{ shelf.name }}</span>
                            <span class="font-mono text-xs text-muted-foreground">{{ shelf.code }}</span>
                            <div class="ms-auto flex items-center gap-1">
                              <Button variant="ghost" size="icon" class="h-6 w-6" @click.stop="openCreateDialog('bin', shelf.id)">
                                <Plus class="h-3 w-3" />
                              </Button>
                              <Button variant="ghost" size="icon" class="h-6 w-6" @click.stop="deleteNode('shelf', shelf.id)">
                                <Trash2 class="h-3 w-3 text-destructive" />
                              </Button>
                            </div>
                          </div>

                          <div v-if="openShelves.has(shelf.id)" class="ms-6 space-y-1 pb-2">
                            <!-- Bins -->
                            <div v-for="bin in shelf.bins" :key="bin.id" class="flex items-center gap-2 rounded p-2 hover:bg-muted/50">
                              <Box class="h-4 w-4 shrink-0 text-purple-500" />
                              <span class="text-sm">{{ bin.name }}</span>
                              <span class="font-mono text-xs text-muted-foreground">{{ bin.code }}</span>
                              <Badge v-if="bin.barcode" variant="outline" class="text-xs">{{ bin.barcode }}</Badge>
                              <div class="ms-auto">
                                <Button variant="ghost" size="icon" class="h-6 w-6" @click.stop="deleteNode('bin', bin.id)">
                                  <Trash2 class="h-3 w-3 text-destructive" />
                                </Button>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Add dialog -->
      <Dialog v-model:open="dialogOpen">
        <DialogContent>
          <DialogHeader>
            <DialogTitle>{{ t('locations.add') }} {{ dialogType }}</DialogTitle>
          </DialogHeader>
          <div class="grid gap-4 py-4">
            <div class="space-y-2">
              <Label>{{ t('locations.name') }} *</Label>
              <Input v-model="dialogForm.name" />
            </div>
            <div class="space-y-2">
              <Label>{{ t('locations.code') }} *</Label>
              <Input v-model="dialogForm.code" class="font-mono" />
            </div>
            <div v-if="dialogType === 'zone'" class="space-y-2">
              <Label>{{ t('locations.type') }}</Label>
              <select v-model="dialogForm.type" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm">
                <option value="">{{ t('common.none') }}</option>
                <option value="receiving">Receiving</option>
                <option value="storage">Storage</option>
                <option value="picking">Picking</option>
                <option value="shipping">Shipping</option>
                <option value="quarantine">Quarantine</option>
                <option value="controlled">Controlled</option>
              </select>
            </div>
          </div>
          <DialogFooter>
            <Button variant="outline" @click="dialogOpen = false">{{ t('common.cancel') }}</Button>
            <Button @click="submitDialog">{{ t('common.save') }}</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  </AuthenticatedLayout>
</template>
