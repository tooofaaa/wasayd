<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PageHeader from '@/Components/shared/PageHeader.vue'
import StatusBadge from '@/Components/shared/StatusBadge.vue'
import { Button } from '@/Components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { Pencil, ArrowLeft, Warehouse } from 'lucide-vue-next'

const { t, locale } = useI18n()
const props = defineProps<{ branch: any }>()
const displayName = locale.value === 'ar' ? (props.branch.name_ar || props.branch.name) : props.branch.name
</script>

<template>
  <AuthenticatedLayout>
    <div class="space-y-6 p-6">
      <PageHeader :title="displayName" :description="branch.code">
        <template #actions>
          <Link :href="route('locations.branches.index')">
            <Button variant="outline">
              <ArrowLeft class="me-2 h-4 w-4" />
              {{ t('common.back') }}
            </Button>
          </Link>
          <Link :href="route('locations.branches.edit', branch.id)">
            <Button>
              <Pencil class="me-2 h-4 w-4" />
              {{ t('common.edit') }}
            </Button>
          </Link>
        </template>
      </PageHeader>

      <div class="grid gap-6 md:grid-cols-2">
        <Card>
          <CardHeader class="pb-2">
            <CardTitle class="text-sm text-muted-foreground">{{ t('common.status') }}</CardTitle>
          </CardHeader>
          <CardContent>
            <StatusBadge :status="branch.is_active" />
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
            <span class="text-2xl font-bold">{{ branch.warehouses_count ?? 0 }}</span>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>{{ t('locations.branchDetails') }}</CardTitle>
        </CardHeader>
        <CardContent>
          <dl class="grid gap-4 sm:grid-cols-2">
            <div>
              <dt class="text-sm text-muted-foreground">{{ t('locations.nameEn') }}</dt>
              <dd class="mt-1 font-medium">{{ branch.name }}</dd>
            </div>
            <div>
              <dt class="text-sm text-muted-foreground">{{ t('locations.nameAr') }}</dt>
              <dd class="mt-1 font-medium" dir="rtl">{{ branch.name_ar }}</dd>
            </div>
            <div>
              <dt class="text-sm text-muted-foreground">{{ t('locations.company') }}</dt>
              <dd class="mt-1 font-medium">
                {{ branch.company ? (locale === 'ar' ? (branch.company.name_ar || branch.company.name) : branch.company.name) : '-' }}
              </dd>
            </div>
            <div v-if="branch.city">
              <dt class="text-sm text-muted-foreground">{{ t('locations.city') }}</dt>
              <dd class="mt-1 font-medium">{{ branch.city }}</dd>
            </div>
            <div v-if="branch.email">
              <dt class="text-sm text-muted-foreground">{{ t('common.email') }}</dt>
              <dd class="mt-1 font-medium">{{ branch.email }}</dd>
            </div>
            <div v-if="branch.phone">
              <dt class="text-sm text-muted-foreground">{{ t('common.phone') }}</dt>
              <dd class="mt-1 font-medium">{{ branch.phone }}</dd>
            </div>
            <div v-if="branch.address" class="sm:col-span-2">
              <dt class="text-sm text-muted-foreground">{{ t('locations.address') }}</dt>
              <dd class="mt-1 font-medium">{{ branch.address }}</dd>
            </div>
          </dl>
        </CardContent>
      </Card>
    </div>
  </AuthenticatedLayout>
</template>
