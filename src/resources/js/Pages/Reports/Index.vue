<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import {
  BarChart3,
  TrendingUp,
  DollarSign,
  AlertTriangle,
  PackageX,
  History,
  Radio,
  Activity,
  Wrench,
  Shield,
  BookOpen,
  Target
} from 'lucide-vue-next'

const reports = [
  {
    name: 'Stock Summary',
    description: 'View stock levels by warehouse, category, and type',
    icon: BarChart3,
    route: 'reports.stock-summary',
    color: 'text-blue-600'
  },
  {
    name: 'Stock Movement',
    description: 'Track inventory movements over time',
    icon: TrendingUp,
    route: 'reports.stock-movement',
    color: 'text-green-600'
  },
  {
    name: 'Stock Valuation',
    description: 'Calculate total inventory value by warehouse',
    icon: DollarSign,
    route: 'reports.stock-valuation',
    color: 'text-emerald-600'
  },
  {
    name: 'Expiry Report',
    description: 'Monitor batches approaching expiration',
    icon: AlertTriangle,
    route: 'reports.expiry',
    color: 'text-orange-600'
  },
  {
    name: 'Low Stock',
    description: 'Items below reorder point',
    icon: PackageX,
    route: 'reports.low-stock',
    color: 'text-red-600'
  },
  {
    name: 'Transaction History',
    description: 'Complete inventory transaction log',
    icon: History,
    route: 'reports.transaction-history',
    color: 'text-purple-600'
  },
  {
    name: 'EPC Status',
    description: 'RFID tag status summary',
    icon: Radio,
    route: 'reports.epc-status',
    color: 'text-cyan-600'
  },
  {
    name: 'Asset Utilization',
    description: 'Track asset usage by department',
    icon: Activity,
    route: 'reports.asset-utilization',
    color: 'text-indigo-600'
  },
  {
    name: 'Maintenance Due',
    description: 'Upcoming and overdue maintenance',
    icon: Wrench,
    route: 'reports.maintenance-due',
    color: 'text-amber-600'
  },
  {
    name: 'Controlled Medications',
    description: 'Reconciliation report for controlled items',
    icon: Shield,
    route: 'reports.controlled-med',
    color: 'text-rose-600'
  },
  {
    name: 'Audit Trail',
    description: 'System audit log and user activity',
    icon: BookOpen,
    route: 'reports.audit-trail',
    color: 'text-slate-600'
  },
  {
    name: 'Count Accuracy',
    description: 'Stock count variance analysis',
    icon: Target,
    route: 'reports.count-accuracy',
    color: 'text-teal-600'
  }
]
</script>

<template>
  <Head title="Reports" />

  <AuthenticatedLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-3xl font-bold tracking-tight">Reports</h1>
        <p class="text-muted-foreground mt-2">
          Generate comprehensive reports for inventory, assets, and operations
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <Card v-for="report in reports" :key="report.route" class="hover:shadow-lg transition-shadow">
          <CardHeader>
            <div class="flex items-start justify-between">
              <component :is="report.icon" :class="[report.color, 'w-10 h-10']" />
            </div>
            <CardTitle class="mt-4">{{ report.name }}</CardTitle>
            <CardDescription>{{ report.description }}</CardDescription>
          </CardHeader>
          <CardContent>
            <Link :href="route(report.route)">
              <Button class="w-full">
                View Report
              </Button>
            </Link>
          </CardContent>
        </Card>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
