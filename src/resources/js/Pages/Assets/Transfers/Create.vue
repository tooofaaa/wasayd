<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PageHeader from '@/Components/shared/PageHeader.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Textarea } from '@/Components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/Components/ui/card'
import { useForm, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { computed } from 'vue'

const { t, locale } = useI18n()

const props = defineProps<{
  assets: any[]
  warehouses: any[]
}>()

const form = useForm({
  asset_id: null as number | null,
  from_warehouse_id: null as number | null,
  to_warehouse_id: null as number | null,
  from_department: '',
  to_department: '',
  transfer_date: new Date().toISOString().split('T')[0],
  reason: '',
  notes: '',
})

const selectedAsset = computed(() => {
  return props.assets.find(a => a.id === form.asset_id)
})

// Auto-populate from_warehouse_id when asset is selected
function onAssetChange() {
  if (selectedAsset.value) {
    form.from_warehouse_id = selectedAsset.value.current_warehouse_id
  }
}

function submit() {
  form.post(route('assets.transfers.store'))
}
</script>

<template>
  <AuthenticatedLayout>
    <div class="space-y-6">
      <PageHeader :title="t('Create Asset Transfer')" />

      <form @submit.prevent="submit">
        <Card>
          <CardHeader>
            <CardTitle>{{ t('Transfer Details') }}</CardTitle>
          </CardHeader>
          <CardContent class="grid gap-4 sm:grid-cols-2">
            <div class="space-y-2 sm:col-span-2">
              <Label for="asset_id">{{ t('Asset') }} *</Label>
              <Select v-model="form.asset_id" @update:model-value="onAssetChange">
                <SelectTrigger :class="{ 'border-destructive': form.errors.asset_id }">
                  <SelectValue :placeholder="t('Select Asset')" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="asset in assets" :key="asset.id" :value="asset.id">
                    <div class="flex items-center gap-2">
                      <span class="font-mono">{{ asset.asset_code }}</span>
                      <span>-</span>
                      <span>{{ locale === 'ar' ? (asset.name_ar || asset.name) : asset.name }}</span>
                    </div>
                  </SelectItem>
                </SelectContent>
              </Select>
              <p v-if="form.errors.asset_id" class="text-sm text-destructive">{{ form.errors.asset_id }}</p>
            </div>

            <div class="space-y-2">
              <Label for="from_warehouse_id">{{ t('From Warehouse') }} *</Label>
              <Select v-model="form.from_warehouse_id" disabled>
                <SelectTrigger :class="{ 'border-destructive': form.errors.from_warehouse_id }">
                  <SelectValue :placeholder="t('Select Warehouse')" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
                    {{ locale === 'ar' ? (warehouse.name_ar || warehouse.name) : warehouse.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
              <p v-if="form.errors.from_warehouse_id" class="text-sm text-destructive">{{ form.errors.from_warehouse_id }}</p>
            </div>

            <div class="space-y-2">
              <Label for="to_warehouse_id">{{ t('To Warehouse') }} *</Label>
              <Select v-model="form.to_warehouse_id">
                <SelectTrigger :class="{ 'border-destructive': form.errors.to_warehouse_id }">
                  <SelectValue :placeholder="t('Select Warehouse')" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
                    {{ locale === 'ar' ? (warehouse.name_ar || warehouse.name) : warehouse.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
              <p v-if="form.errors.to_warehouse_id" class="text-sm text-destructive">{{ form.errors.to_warehouse_id }}</p>
            </div>

            <div class="space-y-2">
              <Label for="from_department">{{ t('From Department') }}</Label>
              <Input id="from_department" v-model="form.from_department" :class="{ 'border-destructive': form.errors.from_department }" />
              <p v-if="form.errors.from_department" class="text-sm text-destructive">{{ form.errors.from_department }}</p>
            </div>

            <div class="space-y-2">
              <Label for="to_department">{{ t('To Department') }}</Label>
              <Input id="to_department" v-model="form.to_department" :class="{ 'border-destructive': form.errors.to_department }" />
              <p v-if="form.errors.to_department" class="text-sm text-destructive">{{ form.errors.to_department }}</p>
            </div>

            <div class="space-y-2">
              <Label for="transfer_date">{{ t('Transfer Date') }} *</Label>
              <Input id="transfer_date" v-model="form.transfer_date" type="date" :class="{ 'border-destructive': form.errors.transfer_date }" />
              <p v-if="form.errors.transfer_date" class="text-sm text-destructive">{{ form.errors.transfer_date }}</p>
            </div>

            <div class="space-y-2 sm:col-span-2">
              <Label for="reason">{{ t('Reason') }} *</Label>
              <Textarea id="reason" v-model="form.reason" rows="2" :class="{ 'border-destructive': form.errors.reason }" />
              <p v-if="form.errors.reason" class="text-sm text-destructive">{{ form.errors.reason }}</p>
            </div>

            <div class="space-y-2 sm:col-span-2">
              <Label for="notes">{{ t('Notes') }}</Label>
              <Textarea id="notes" v-model="form.notes" rows="2" :class="{ 'border-destructive': form.errors.notes }" />
              <p v-if="form.errors.notes" class="text-sm text-destructive">{{ form.errors.notes }}</p>
            </div>
          </CardContent>
          <CardFooter class="flex justify-end gap-2">
            <Link :href="route('assets.transfers.index')">
              <Button variant="outline" type="button">{{ t('common.cancel') }}</Button>
            </Link>
            <Button type="submit" :disabled="form.processing">{{ t('common.save') }}</Button>
          </CardFooter>
        </Card>
      </form>
    </div>
  </AuthenticatedLayout>
</template>
