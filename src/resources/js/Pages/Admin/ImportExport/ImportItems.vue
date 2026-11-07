<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Alert, AlertDescription } from '@/Components/ui/alert'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table'
import { Upload, AlertCircle, CheckCircle } from 'lucide-vue-next'

const props = defineProps({
  preview: Object
})

const form = useForm({
  file: null,
  confirm: false
})

const fileInput = ref(null)

const handleFileUpload = () => {
  form.file = fileInput.value.files[0]
  form.confirm = false
  form.post(route('import-export.import.items'))
}

const confirmImport = () => {
  form.confirm = true
  form.post(route('import-export.import.items'))
}
</script>

<template>
  <Head title="Import Items" />

  <AuthenticatedLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-3xl font-bold tracking-tight">Import Items</h1>
        <p class="text-muted-foreground mt-2">Upload items from a CSV file</p>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Upload CSV File</CardTitle>
          <CardDescription>
            Select a CSV file containing item data. Required columns: sku, name, name_ar, category, item_type, unit_cost, min_stock
          </CardDescription>
        </CardHeader>
        <CardContent class="space-y-4">
          <div>
            <Label for="file">CSV File</Label>
            <Input
              id="file"
              ref="fileInput"
              type="file"
              accept=".csv,.txt"
              @change="handleFileUpload"
            />
          </div>

          <Alert v-if="form.errors.file" variant="destructive">
            <AlertCircle class="w-4 h-4" />
            <AlertDescription>{{ form.errors.file }}</AlertDescription>
          </Alert>
        </CardContent>
      </Card>

      <Card v-if="preview && preview.items">
        <CardHeader>
          <CardTitle>Preview ({{ preview.total }} items)</CardTitle>
          <CardDescription>
            Review the data before confirming the import
          </CardDescription>
        </CardHeader>
        <CardContent class="space-y-4">
          <Alert v-if="preview.errors && preview.errors.length > 0" variant="destructive">
            <AlertCircle class="w-4 h-4" />
            <AlertDescription>
              <div v-for="error in preview.errors" :key="error">{{ error }}</div>
            </AlertDescription>
          </Alert>

          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Row</TableHead>
                <TableHead>SKU</TableHead>
                <TableHead>Name</TableHead>
                <TableHead>Type</TableHead>
                <TableHead>Unit Cost</TableHead>
                <TableHead>Min Stock</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="item in preview.items.slice(0, 10)" :key="item.row">
                <TableCell>{{ item.row }}</TableCell>
                <TableCell>{{ item.sku }}</TableCell>
                <TableCell>{{ item.name }}</TableCell>
                <TableCell>{{ item.item_type }}</TableCell>
                <TableCell>{{ item.unit_cost }}</TableCell>
                <TableCell>{{ item.min_stock }}</TableCell>
              </TableRow>
            </TableBody>
          </Table>

          <div class="flex justify-end gap-4">
            <Button
              v-if="!preview.errors || preview.errors.length === 0"
              @click="confirmImport"
              :disabled="form.processing"
            >
              <CheckCircle class="w-4 h-4 mr-2" />
              Confirm Import
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  </AuthenticatedLayout>
</template>
