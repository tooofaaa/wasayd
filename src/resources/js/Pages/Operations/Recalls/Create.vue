<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Button } from '@/Components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Label } from '@/Components/ui/label'
import { Input } from '@/Components/ui/input'
import { Textarea } from '@/Components/ui/textarea'
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/Components/ui/select'
import { AlertTriangle, ArrowLeft } from 'lucide-vue-next'

const { t } = useI18n()

const props = defineProps<{
    items: any[]
    batches: any[]
    recallClasses: any[]
}>()

const form = useForm({
    item_id: null,
    recall_class: null,
    manufacturer_recall_id: '',
    description: '',
    instructions: '',
    affected_batches: [],
})

function submit() {
    form.post('/operations/recalls')
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" @click="$inertia.visit('/operations/recalls')">
                    <ArrowLeft class="h-5 w-5" />
                </Button>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        <AlertTriangle class="me-2 inline h-6 w-6" />
                        {{ t('operations.initiateRecall') || 'Initiate Recall' }}
                    </h1>
                    <p class="text-muted-foreground">{{ t('operations.initiateRecallDesc') || 'Start a product recall process' }}</p>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>{{ t('operations.recallDetails') || 'Recall Details' }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="item_id">{{ t('common.item') }}*</Label>
                                <Select v-model="form.item_id" required>
                                    <SelectTrigger>
                                        <SelectValue :placeholder="t('common.selectItem')" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="item in items" :key="item.id" :value="item.id">
                                            {{ item.sku }} - {{ item.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.item_id" class="text-sm text-destructive">{{ form.errors.item_id }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="recall_class">{{ t('operations.recallClass') || 'Recall Class' }}*</Label>
                                <Select v-model="form.recall_class" required>
                                    <SelectTrigger>
                                        <SelectValue :placeholder="t('common.select')" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="cls in recallClasses" :key="cls.value" :value="cls.value">
                                            {{ cls.value }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.recall_class" class="text-sm text-destructive">{{ form.errors.recall_class }}</p>
                            </div>

                            <div class="space-y-2 col-span-2">
                                <Label for="manufacturer_recall_id">{{ t('operations.manufacturerRecallId') || 'Manufacturer Recall ID' }}</Label>
                                <Input
                                    id="manufacturer_recall_id"
                                    v-model="form.manufacturer_recall_id"
                                />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">{{ t('common.description') }}*</Label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                required
                            />
                            <p v-if="form.errors.description" class="text-sm text-destructive">{{ form.errors.description }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="instructions">{{ t('operations.instructions') || 'Instructions' }}</Label>
                            <Textarea
                                id="instructions"
                                v-model="form.instructions"
                                rows="3"
                            />
                        </div>

                        <div class="flex gap-2 justify-end">
                            <Button type="button" variant="outline" @click="$inertia.visit('/operations/recalls')">
                                {{ t('common.cancel') }}
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                {{ t('operations.initiateRecall') || 'Initiate Recall' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
