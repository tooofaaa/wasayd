<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PageHeader from '@/Components/shared/PageHeader.vue'
import StatusBadge from '@/Components/shared/StatusBadge.vue'
import { Button } from '@/Components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/Components/ui/tabs'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger, DialogFooter } from '@/Components/ui/dialog'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Textarea } from '@/Components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table'
import { useForm, Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { Pencil, Calendar, ClipboardCheck, FileWarning, ArrowLeftRight } from 'lucide-vue-next'
import { ref } from 'vue'

const { t, locale } = useI18n()

const props = defineProps<{
  asset: any
  users: any[]
  warehouses: any[]
}>()

const statusVariants: Record<string, string> = {
  available: 'success',
  in_use: 'primary',
  maintenance: 'warning',
  repair: 'warning',
  calibration: 'warning',
  quarantine: 'destructive',
  retired: 'secondary',
  decommissioned: 'secondary',
  disposed: 'secondary',
  lost: 'destructive',
}

const assignOpen = ref(false)
const maintenanceOpen = ref(false)
const inspectionOpen = ref(false)

const assignForm = useForm({
  assignment_type: 'user',
  assigned_to_user_id: null as number | null,
  assigned_to_department: '',
  condition_at_assignment: 'good',
  expected_return_at: '',
  notes: '',
})

const maintenanceForm = useForm({
  maintenance_type: 'preventive',
  scheduled_date: '',
  performed_by: null as number | null,
  notes: '',
})

const inspectionForm = useForm({
  inspection_date: new Date().toISOString().split('T')[0],
  inspector: null as number | null,
  condition_rating: 5,
  result: 'pass',
  findings: '',
  recommendations: '',
  next_inspection_date: '',
})

function submitAssign() {
  assignForm.post(route('assets.assign', props.asset.id), {
    onSuccess: () => {
      assignOpen.value = false
      assignForm.reset()
    }
  })
}

function submitMaintenance() {
  maintenanceForm.post(route('assets.maintenance.store', props.asset.id), {
    onSuccess: () => {
      maintenanceOpen.value = false
      maintenanceForm.reset()
    }
  })
}

function submitInspection() {
  inspectionForm.post(route('assets.inspections.store', props.asset.id), {
    onSuccess: () => {
      inspectionOpen.value = false
      inspectionForm.reset()
    }
  })
}

function startMaintenance(maintenanceId: number) {
  router.post(route('assets.maintenance.start', [props.asset.id, maintenanceId]))
}

function completeMaintenance(maintenanceId: number) {
  router.post(route('assets.maintenance.complete', [props.asset.id, maintenanceId]), {
    completed_date: new Date().toISOString().split('T')[0],
  })
}
</script>

<template>
  <AuthenticatedLayout>
    <div class="space-y-6">
      <PageHeader :title="asset.asset_code">
        <template #actions>
          <Link :href="route('assets.edit', asset.id)">
            <Button variant="outline">
              <Pencil class="me-2 h-4 w-4" />
              {{ t('common.edit') }}
            </Button>
          </Link>
        </template>
      </PageHeader>

      <Card>
        <CardHeader>
          <CardTitle>{{ t('Asset Overview') }}</CardTitle>
        </CardHeader>
        <CardContent class="grid gap-4 sm:grid-cols-3">
          <div>
            <p class="text-sm text-muted-foreground">{{ t('Asset Code') }}</p>
            <p class="font-mono font-semibold">{{ asset.asset_code }}</p>
          </div>
          <div>
            <p class="text-sm text-muted-foreground">{{ t('Name') }}</p>
            <p class="font-semibold">{{ locale === 'ar' ? (asset.name_ar || asset.name) : asset.name }}</p>
          </div>
          <div>
            <p class="text-sm text-muted-foreground">{{ t('Status') }}</p>
            <StatusBadge :variant="statusVariants[asset.status]" :label="asset.status.replace(/_/g, ' ')" />
          </div>
          <div>
            <p class="text-sm text-muted-foreground">{{ t('Item') }}</p>
            <p>{{ asset.item ? (locale === 'ar' ? (asset.item.name_ar || asset.item.name) : asset.item.name) : '-' }}</p>
          </div>
          <div>
            <p class="text-sm text-muted-foreground">{{ t('Serial Number') }}</p>
            <p class="font-mono">{{ asset.serial_number || '-' }}</p>
          </div>
          <div>
            <p class="text-sm text-muted-foreground">{{ t('Warehouse') }}</p>
            <p>{{ asset.warehouse ? (locale === 'ar' ? (asset.warehouse.name_ar || asset.warehouse.name) : asset.warehouse.name) : '-' }}</p>
          </div>
          <div>
            <p class="text-sm text-muted-foreground">{{ t('Purchase Date') }}</p>
            <p>{{ asset.purchase_date || '-' }}</p>
          </div>
          <div>
            <p class="text-sm text-muted-foreground">{{ t('Purchase Price') }}</p>
            <p>{{ asset.purchase_price ? `${asset.purchase_price} SAR` : '-' }}</p>
          </div>
          <div>
            <p class="text-sm text-muted-foreground">{{ t('Category') }}</p>
            <p>{{ asset.category || '-' }}</p>
          </div>
        </CardContent>
      </Card>

      <Tabs default-value="assignments">
        <TabsList>
          <TabsTrigger value="assignments">{{ t('Assignments') }}</TabsTrigger>
          <TabsTrigger value="maintenance">{{ t('Maintenance') }}</TabsTrigger>
          <TabsTrigger value="inspections">{{ t('Inspections') }}</TabsTrigger>
          <TabsTrigger value="warranties">{{ t('Warranties') }}</TabsTrigger>
          <TabsTrigger value="transfers">{{ t('Transfers') }}</TabsTrigger>
        </TabsList>

        <TabsContent value="assignments">
          <Card>
            <CardHeader class="flex flex-row items-center justify-between">
              <CardTitle>{{ t('Assignment History') }}</CardTitle>
              <Dialog v-model:open="assignOpen">
                <DialogTrigger as-child>
                  <Button size="sm">{{ t('Assign Asset') }}</Button>
                </DialogTrigger>
                <DialogContent>
                  <DialogHeader>
                    <DialogTitle>{{ t('Assign Asset') }}</DialogTitle>
                  </DialogHeader>
                  <form @submit.prevent="submitAssign" class="space-y-4">
                    <div class="space-y-2">
                      <Label>{{ t('Assignment Type') }}</Label>
                      <Select v-model="assignForm.assignment_type">
                        <SelectTrigger>
                          <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem value="user">{{ t('User') }}</SelectItem>
                          <SelectItem value="department">{{ t('Department') }}</SelectItem>
                          <SelectItem value="location">{{ t('Location') }}</SelectItem>
                        </SelectContent>
                      </Select>
                    </div>
                    <div v-if="assignForm.assignment_type === 'user'" class="space-y-2">
                      <Label>{{ t('Assign To') }}</Label>
                      <Select v-model="assignForm.assigned_to_user_id">
                        <SelectTrigger>
                          <SelectValue :placeholder="t('Select User')" />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem v-for="user in users" :key="user.id" :value="user.id">
                            {{ user.name }}
                          </SelectItem>
                        </SelectContent>
                      </Select>
                    </div>
                    <div v-if="assignForm.assignment_type === 'department'" class="space-y-2">
                      <Label>{{ t('Department') }}</Label>
                      <Input v-model="assignForm.assigned_to_department" />
                    </div>
                    <div class="space-y-2">
                      <Label>{{ t('Condition') }}</Label>
                      <Select v-model="assignForm.condition_at_assignment">
                        <SelectTrigger>
                          <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem value="excellent">Excellent</SelectItem>
                          <SelectItem value="good">Good</SelectItem>
                          <SelectItem value="fair">Fair</SelectItem>
                          <SelectItem value="poor">Poor</SelectItem>
                        </SelectContent>
                      </Select>
                    </div>
                    <div class="space-y-2">
                      <Label>{{ t('Expected Return Date') }}</Label>
                      <Input v-model="assignForm.expected_return_at" type="date" />
                    </div>
                    <div class="space-y-2">
                      <Label>{{ t('Notes') }}</Label>
                      <Textarea v-model="assignForm.notes" rows="2" />
                    </div>
                    <DialogFooter>
                      <Button type="submit" :disabled="assignForm.processing">{{ t('Assign') }}</Button>
                    </DialogFooter>
                  </form>
                </DialogContent>
              </Dialog>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>{{ t('Assigned To') }}</TableHead>
                    <TableHead>{{ t('Type') }}</TableHead>
                    <TableHead>{{ t('Assigned At') }}</TableHead>
                    <TableHead>{{ t('Returned At') }}</TableHead>
                    <TableHead>{{ t('Condition') }}</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-for="assignment in asset.assignments" :key="assignment.id">
                    <TableCell>
                      {{ assignment.assigned_to_user ? assignment.assigned_to_user.name : assignment.assigned_to_department }}
                    </TableCell>
                    <TableCell>{{ assignment.assignment_type }}</TableCell>
                    <TableCell>{{ new Date(assignment.assigned_at).toLocaleDateString() }}</TableCell>
                    <TableCell>{{ assignment.returned_at ? new Date(assignment.returned_at).toLocaleDateString() : 'Active' }}</TableCell>
                    <TableCell>{{ assignment.condition_at_assignment }}</TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="maintenance">
          <Card>
            <CardHeader class="flex flex-row items-center justify-between">
              <CardTitle>{{ t('Maintenance Records') }}</CardTitle>
              <Dialog v-model:open="maintenanceOpen">
                <DialogTrigger as-child>
                  <Button size="sm">
                    <Calendar class="me-2 h-4 w-4" />
                    {{ t('Schedule Maintenance') }}
                  </Button>
                </DialogTrigger>
                <DialogContent>
                  <DialogHeader>
                    <DialogTitle>{{ t('Schedule Maintenance') }}</DialogTitle>
                  </DialogHeader>
                  <form @submit.prevent="submitMaintenance" class="space-y-4">
                    <div class="space-y-2">
                      <Label>{{ t('Type') }}</Label>
                      <Select v-model="maintenanceForm.maintenance_type">
                        <SelectTrigger>
                          <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem value="preventive">Preventive</SelectItem>
                          <SelectItem value="corrective">Corrective</SelectItem>
                          <SelectItem value="calibration">Calibration</SelectItem>
                        </SelectContent>
                      </Select>
                    </div>
                    <div class="space-y-2">
                      <Label>{{ t('Scheduled Date') }}</Label>
                      <Input v-model="maintenanceForm.scheduled_date" type="date" />
                    </div>
                    <div class="space-y-2">
                      <Label>{{ t('Performed By') }}</Label>
                      <Select v-model="maintenanceForm.performed_by">
                        <SelectTrigger>
                          <SelectValue :placeholder="t('Select User')" />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem v-for="user in users" :key="user.id" :value="user.id">
                            {{ user.name }}
                          </SelectItem>
                        </SelectContent>
                      </Select>
                    </div>
                    <div class="space-y-2">
                      <Label>{{ t('Notes') }}</Label>
                      <Textarea v-model="maintenanceForm.notes" rows="2" />
                    </div>
                    <DialogFooter>
                      <Button type="submit" :disabled="maintenanceForm.processing">{{ t('Schedule') }}</Button>
                    </DialogFooter>
                  </form>
                </DialogContent>
              </Dialog>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>{{ t('Type') }}</TableHead>
                    <TableHead>{{ t('Status') }}</TableHead>
                    <TableHead>{{ t('Scheduled') }}</TableHead>
                    <TableHead>{{ t('Completed') }}</TableHead>
                    <TableHead>{{ t('Actions') }}</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-for="maintenance in asset.maintenance_records" :key="maintenance.id">
                    <TableCell>{{ maintenance.maintenance_type }}</TableCell>
                    <TableCell>
                      <StatusBadge :variant="maintenance.status === 'completed' ? 'success' : 'warning'" :label="maintenance.status" />
                    </TableCell>
                    <TableCell>{{ maintenance.scheduled_date }}</TableCell>
                    <TableCell>{{ maintenance.completed_date || '-' }}</TableCell>
                    <TableCell>
                      <div class="flex gap-2">
                        <Button v-if="maintenance.status === 'scheduled'" size="sm" variant="outline" @click="startMaintenance(maintenance.id)">
                          Start
                        </Button>
                        <Button v-if="maintenance.status === 'in_progress'" size="sm" @click="completeMaintenance(maintenance.id)">
                          Complete
                        </Button>
                      </div>
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="inspections">
          <Card>
            <CardHeader class="flex flex-row items-center justify-between">
              <CardTitle>{{ t('Inspection History') }}</CardTitle>
              <Dialog v-model:open="inspectionOpen">
                <DialogTrigger as-child>
                  <Button size="sm">
                    <ClipboardCheck class="me-2 h-4 w-4" />
                    {{ t('Record Inspection') }}
                  </Button>
                </DialogTrigger>
                <DialogContent>
                  <DialogHeader>
                    <DialogTitle>{{ t('Record Inspection') }}</DialogTitle>
                  </DialogHeader>
                  <form @submit.prevent="submitInspection" class="space-y-4">
                    <div class="space-y-2">
                      <Label>{{ t('Inspection Date') }}</Label>
                      <Input v-model="inspectionForm.inspection_date" type="date" />
                    </div>
                    <div class="space-y-2">
                      <Label>{{ t('Inspector') }}</Label>
                      <Select v-model="inspectionForm.inspector">
                        <SelectTrigger>
                          <SelectValue :placeholder="t('Select Inspector')" />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem v-for="user in users" :key="user.id" :value="user.id">
                            {{ user.name }}
                          </SelectItem>
                        </SelectContent>
                      </Select>
                    </div>
                    <div class="space-y-2">
                      <Label>{{ t('Condition Rating (1-5)') }}</Label>
                      <Input v-model.number="inspectionForm.condition_rating" type="number" min="1" max="5" />
                    </div>
                    <div class="space-y-2">
                      <Label>{{ t('Result') }}</Label>
                      <Select v-model="inspectionForm.result">
                        <SelectTrigger>
                          <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem value="pass">Pass</SelectItem>
                          <SelectItem value="fail">Fail</SelectItem>
                          <SelectItem value="conditional">Conditional</SelectItem>
                        </SelectContent>
                      </Select>
                    </div>
                    <div class="space-y-2">
                      <Label>{{ t('Findings') }}</Label>
                      <Textarea v-model="inspectionForm.findings" rows="2" />
                    </div>
                    <div class="space-y-2">
                      <Label>{{ t('Recommendations') }}</Label>
                      <Textarea v-model="inspectionForm.recommendations" rows="2" />
                    </div>
                    <div class="space-y-2">
                      <Label>{{ t('Next Inspection Date') }}</Label>
                      <Input v-model="inspectionForm.next_inspection_date" type="date" />
                    </div>
                    <DialogFooter>
                      <Button type="submit" :disabled="inspectionForm.processing">{{ t('Save') }}</Button>
                    </DialogFooter>
                  </form>
                </DialogContent>
              </Dialog>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>{{ t('Date') }}</TableHead>
                    <TableHead>{{ t('Inspector') }}</TableHead>
                    <TableHead>{{ t('Rating') }}</TableHead>
                    <TableHead>{{ t('Result') }}</TableHead>
                    <TableHead>{{ t('Findings') }}</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-for="inspection in asset.inspections" :key="inspection.id">
                    <TableCell>{{ inspection.inspection_date }}</TableCell>
                    <TableCell>{{ inspection.inspector_user?.name || '-' }}</TableCell>
                    <TableCell>{{ inspection.condition_rating }}/5</TableCell>
                    <TableCell>
                      <StatusBadge
                        :variant="inspection.result === 'pass' ? 'success' : inspection.result === 'fail' ? 'destructive' : 'warning'"
                        :label="inspection.result"
                      />
                    </TableCell>
                    <TableCell>{{ inspection.findings || '-' }}</TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="warranties">
          <Card>
            <CardHeader>
              <CardTitle>{{ t('Warranties') }}</CardTitle>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>{{ t('Type') }}</TableHead>
                    <TableHead>{{ t('Provider') }}</TableHead>
                    <TableHead>{{ t('Start Date') }}</TableHead>
                    <TableHead>{{ t('End Date') }}</TableHead>
                    <TableHead>{{ t('Coverage') }}</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-for="warranty in asset.warranties" :key="warranty.id">
                    <TableCell>{{ warranty.warranty_type }}</TableCell>
                    <TableCell>{{ warranty.provider }}</TableCell>
                    <TableCell>{{ warranty.start_date }}</TableCell>
                    <TableCell>{{ warranty.end_date }}</TableCell>
                    <TableCell>{{ warranty.coverage || '-' }}</TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="transfers">
          <Card>
            <CardHeader>
              <CardTitle>{{ t('Transfer History') }}</CardTitle>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>{{ t('From') }}</TableHead>
                    <TableHead>{{ t('To') }}</TableHead>
                    <TableHead>{{ t('Status') }}</TableHead>
                    <TableHead>{{ t('Date') }}</TableHead>
                    <TableHead>{{ t('Reason') }}</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-for="transfer in asset.transfers" :key="transfer.id">
                    <TableCell>{{ transfer.from_warehouse ? (locale === 'ar' ? (transfer.from_warehouse.name_ar || transfer.from_warehouse.name) : transfer.from_warehouse.name) : '-' }}</TableCell>
                    <TableCell>{{ transfer.to_warehouse ? (locale === 'ar' ? (transfer.to_warehouse.name_ar || transfer.to_warehouse.name) : transfer.to_warehouse.name) : '-' }}</TableCell>
                    <TableCell>
                      <StatusBadge
                        :variant="transfer.status === 'completed' ? 'success' : transfer.status === 'pending' ? 'secondary' : 'warning'"
                        :label="transfer.status"
                      />
                    </TableCell>
                    <TableCell>{{ transfer.transfer_date }}</TableCell>
                    <TableCell>{{ transfer.reason || '-' }}</TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  </AuthenticatedLayout>
</template>
