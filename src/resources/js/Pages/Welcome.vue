<script setup lang="ts">
import { useI18n } from 'vue-i18n'
import { Head } from '@inertiajs/vue3'

const { t, locale } = useI18n()

function toggleLocale() {
    locale.value = locale.value === 'ar' ? 'en' : 'ar'
    document.documentElement.lang = locale.value
    document.documentElement.dir = locale.value === 'ar' ? 'rtl' : 'ltr'
}
</script>

<template>
    <Head title="Welcome" />
    <div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-primary-50 to-primary-100 dark:from-gray-900 dark:to-gray-800">
        <div class="w-full max-w-lg rounded-2xl bg-white p-8 shadow-xl dark:bg-gray-900">
            <div class="mb-8 text-center">
                <h1 class="text-4xl font-bold text-primary-600">WASAYD</h1>
                <p class="mt-2 text-lg text-gray-500 dark:text-gray-400">
                    {{ t('common.appDescription') }}
                </p>
            </div>

            <div class="space-y-4 text-center">
                <div class="flex items-center justify-center gap-4">
                    <span class="text-sm text-gray-500">{{ t('common.language') }}</span>
                    <button
                        class="rounded-lg border border-primary-200 bg-primary-50 px-4 py-2 text-sm font-medium text-primary-700 transition hover:bg-primary-100 dark:border-primary-800 dark:bg-primary-950 dark:text-primary-300"
                        @click="toggleLocale"
                    >
                        {{ locale === 'ar' ? 'English' : 'العربية' }}
                    </button>
                </div>

                <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        {{ t('common.welcome') }}
                    </p>
                </div>

                <div class="grid grid-cols-3 gap-3 pt-4">
                    <div class="rounded-lg bg-success-50 p-3 text-center dark:bg-success-500/10">
                        <div class="text-2xl font-bold text-success-700 dark:text-success-500">✓</div>
                        <div class="text-xs text-gray-500">Vue 3</div>
                    </div>
                    <div class="rounded-lg bg-primary-50 p-3 text-center dark:bg-primary-500/10">
                        <div class="text-2xl font-bold text-primary-700 dark:text-primary-500">✓</div>
                        <div class="text-xs text-gray-500">Inertia</div>
                    </div>
                    <div class="rounded-lg bg-warning-50 p-3 text-center dark:bg-warning-500/10">
                        <div class="text-2xl font-bold text-warning-700 dark:text-warning-500">✓</div>
                        <div class="text-xs text-gray-500">Tailwind 4</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
