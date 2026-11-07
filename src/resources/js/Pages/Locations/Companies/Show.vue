<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PageHeader from '@/Components/shared/PageHeader.vue'
import StatusBadge from '@/Components/shared/StatusBadge.vue'
import { Button } from '@/Components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { Pencil, ArrowLeft, Building2, Warehouse } from 'lucide-vue-next'

const { t, locale } = useI18n()
const props = defineProps<{ company: any }>()
const displayName = locale.value === 'ar' ? (props.company.name_ar || props.company.name) : props.company.name
</script>

<template>
  <AuthenticatedLayout>
    <div class="space-y-6 p-6">
      <PageHeader :title="displayName" :description="company.code">
        <template #actions>
          <Link :href="route('locations.companies.index')">
            <Button variant="outline">
              <ArrowLeft class="me-2 h-4 w-4" />
              {{ t('common.back') }}
            </Button>
          </Link>
          <Link :href="route('locations.companies.edit', company.id)">
            <Button>
              <Pencil class="me-2 h-4 w-4" />
              {{ t('common.edit') }}
            </Button>
          </Link>
        </template>
      </PageHeader>

      <div class="grid gap-6 md:grid-cols-3">
        <Card>
          <CardHeader class="pb-2">
            <CardTitle class="text-sm text-muted-foreground">{{ t('common.status') }}</CardTitle>
          </CardHeader>
          <CardContent>
            <StatusBadge :status="company.is_active" />
          </CardContent>
        </Card>
        <Card>
          <CardHeader class="pb-2">
            <CardTitle class="flex items-center gap-2 text-sm text-muted-foreground">
              <Building2 class="h-4 w-4" />
              {{ t('locations.branches') }}
            </CardTitle>
          </CardHeader>
          <CardContent>
            <span class="text-2xl font-bold">{{ company.branches_count ?? 0 }}</span>
          </CardContent>
        </Card>
        <Card>
          <CardHeader class="pb-2">
            <CardTitle class="flex items-center gap-2 text-sm text-muted-foreground">
              <Warehouse class="h-4 w-4" />
              {{ t('locations.warehouses') }}
            </CardTitle>
          </CardHeader>
          <CardContent>
            <span class="text-2xl font-bold">{{ company.warehouses_count ?? 0 }}</span>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>{{ t('locations.companyDetails') }}</CardTitle>
        </CardHeader>
        <CardContent>
          <dl class="grid gap-4 sm:grid-cols-2">
            <div>
              <dt class="text-sm text-muted-foreground">{{ t('locations.nameEn') }}</dt>
              <dd class="mt-1 font-medium">{{ company.name }}</dd>
            </div>
            <div>
              <dt class="text-sm text-muted-foreground">{{ t('locations.nameAr') }}</dt>
              <dd class="mt-1 font-medium" dir="rtl">{{ company.name_ar }}</dd>
            </div>
            <div v-if="company.email">
              <dt class="text-sm text-muted-foreground">{{ t('common.email') }}</dt>
              <dd class="mt-1 font-medium">{{ company.email }}</dd>
            </div>
            <div v-if="company.phone">
              <dt class="text-sm text-muted-foreground">{{ t('common.phone') }}</dt>
              <dd class="mt-1 font-medium">{{ company.phone }}</dd>
            </div>
            <div v-if="company.vat_number">
              <dt class="text-sm text-muted-foreground">{{ t('locations.vatNumber') }}</dt>
              <dd class="mt-1 font-mono font-medium">{{ company.vat_number }}</dd>
            </div>
            <div v-if="company.cr_number">
              <dt class="text-sm text-muted-foreground">{{ t('locations.crNumber') }}</dt>
              <dd class="mt-1 font-mono font-medium">{{ company.cr_number }}</dd>
            </div>
            <div v-if="company.address" class="sm:col-span-2">
              <dt class="text-sm text-muted-foreground">{{ t('locations.address') }}</dt>
              <dd class="mt-1 font-medium">{{ company.address }}</dd>
            </div>
          </dl>
        </CardContent>
      </Card>
    </div>
  </AuthenticatedLayout>
</template>
