<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Download, Upload, FileSpreadsheet } from 'lucide-vue-next'

const exportItems = () => {
  window.location.href = route('import-export.export.items')
}

const exportStock = () => {
  window.location.href = route('import-export.export.stock')
}
</script>

<template>
  <Head title="Import / Export" />

  <AuthenticatedLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-3xl font-bold tracking-tight">Import / Export</h1>
        <p class="text-muted-foreground mt-2">
          Export data or import items from CSV files
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Card>
          <CardHeader>
            <FileSpreadsheet class="w-10 h-10 text-blue-600 mb-2" />
            <CardTitle>Export Items</CardTitle>
            <CardDescription>Download all items as CSV file</CardDescription>
          </CardHeader>
          <CardContent>
            <Button @click="exportItems" class="w-full">
              <Download class="w-4 h-4 mr-2" />
              Export Items
            </Button>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <FileSpreadsheet class="w-10 h-10 text-green-600 mb-2" />
            <CardTitle>Export Stock</CardTitle>
            <CardDescription>Download stock balances as CSV file</CardDescription>
          </CardHeader>
          <CardContent>
            <Button @click="exportStock" class="w-full">
              <Download class="w-4 h-4 mr-2" />
              Export Stock
            </Button>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <Upload class="w-10 h-10 text-purple-600 mb-2" />
            <CardTitle>Import Items</CardTitle>
            <CardDescription>Upload items from CSV file</CardDescription>
          </CardHeader>
          <CardContent>
            <Link :href="route('import-export.import.items.form')">
              <Button class="w-full">
                <Upload class="w-4 h-4 mr-2" />
                Import Items
              </Button>
            </Link>
          </CardContent>
        </Card>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
