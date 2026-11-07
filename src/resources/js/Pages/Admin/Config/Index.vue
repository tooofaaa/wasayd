<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Switch } from '@/Components/ui/switch'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs'
import { Settings, Save } from 'lucide-vue-next'

const { t } = useI18n()

const props = defineProps<{
    configGroups: Record<string, Array<{
        id: number
        config_group: string
        config_key: string
        config_value: any
        description: string | null
        data_type: string
    }>>
}>()

const editedValues = ref<Record<string, any>>({})

function getConfigKey(group: string, key: string) {
    return `${group}:${key}`
}

function getValue(config: any) {
    const k = getConfigKey(config.config_group, config.config_key)
    return k in editedValues.value ? editedValues.value[k] : config.config_value
}

function setValue(config: any, value: any) {
    editedValues.value[getConfigKey(config.config_group, config.config_key)] = value
}

function saveAll() {
    const configs = Object.entries(editedValues.value).map(([k, v]) => {
        const [group, ...keyParts] = k.split(':')
        return { group, key: keyParts.join(':'), value: v }
    })
    if (configs.length === 0) return
    router.put('/admin/config', { configs })
    editedValues.value = {}
}

const groupNames = Object.keys(props.configGroups)
</script>

<template>
    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        <Settings class="me-2 inline h-6 w-6" />
                        {{ t('nav.settings') }}
                    </h1>
                    <p class="text-muted-foreground">{{ t('admin.systemConfig') || 'System configuration settings' }}</p>
                </div>
                <Button @click="saveAll" :disabled="Object.keys(editedValues).length === 0">
                    <Save class="me-2 h-4 w-4" />
                    {{ t('common.save') }}
                </Button>
            </div>

            <Tabs :default-value="groupNames[0]" class="space-y-4">
                <TabsList>
                    <TabsTrigger v-for="group in groupNames" :key="group" :value="group" class="capitalize">
                        {{ group }}
                    </TabsTrigger>
                </TabsList>

                <TabsContent v-for="group in groupNames" :key="group" :value="group">
                    <Card>
                        <CardHeader>
                            <CardTitle class="capitalize">{{ group }}</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div
                                v-for="config in configGroups[group]"
                                :key="config.id"
                                class="flex items-center justify-between gap-4 rounded-lg border p-4"
                            >
                                <div class="flex-1">
                                    <Label class="font-medium">{{ config.config_key }}</Label>
                                    <p v-if="config.description" class="text-xs text-muted-foreground">{{ config.description }}</p>
                                </div>
                                <div class="w-64">
                                    <Switch
                                        v-if="config.data_type === 'boolean'"
                                        :checked="getValue(config) === true || getValue(config) === 'true' || getValue(config) === '1'"
                                        @update:checked="setValue(config, $event)"
                                    />
                                    <Input
                                        v-else
                                        :model-value="getValue(config)"
                                        @update:model-value="setValue(config, $event)"
                                        :type="config.data_type === 'integer' ? 'number' : 'text'"
                                    />
                                </div>
                            </div>
                            <div v-if="!configGroups[group]?.length" class="py-8 text-center text-muted-foreground">
                                {{ t('common.noData') }}
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>
            </Tabs>
        </div>
    </AuthenticatedLayout>
</template>
