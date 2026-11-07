<script setup lang="ts">
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-muted/30 px-4 py-12">
        <div class="w-full max-w-md space-y-8">
            <div class="text-center">
                <h1 class="text-3xl font-bold tracking-tight text-primary">WASAYD</h1>
                <p class="mt-2 text-sm text-muted-foreground">
                    {{ t('common.app_description') }}
                </p>
            </div>
            <slot />
        </div>
    </div>
</template>
