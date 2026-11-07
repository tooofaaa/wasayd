<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { Button } from '@/Components/ui/button'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/Components/ui/card'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Checkbox } from '@/Components/ui/checkbox'
import { Activity, Globe } from 'lucide-vue-next'

const { t, locale } = useI18n()

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

function submit() {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    })
}

function toggleLocale() {
    locale.value = locale.value === 'ar' ? 'en' : 'ar'
    document.documentElement.lang = locale.value
    document.documentElement.dir = locale.value === 'ar' ? 'rtl' : 'ltr'
}
</script>

<template>
    <Head :title="t('auth.login')" />

    <div class="relative flex min-h-screen items-center justify-center bg-gradient-to-br from-primary/5 via-background to-primary/10 p-4">
        <!-- Language toggle -->
        <button
            class="absolute top-4 end-4 inline-flex items-center gap-2 rounded-lg border border-border bg-card px-3 py-2 text-sm font-medium text-muted-foreground shadow-sm transition-colors hover:bg-accent hover:text-accent-foreground"
            @click="toggleLocale"
        >
            <Globe class="h-4 w-4" />
            {{ locale === 'ar' ? 'English' : 'العربية' }}
        </button>

        <div class="w-full max-w-md">
            <!-- Logo area -->
            <div class="mb-8 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-primary shadow-lg shadow-primary/25">
                    <Activity class="h-8 w-8 text-primary-foreground" />
                </div>
                <h1 class="text-3xl font-bold tracking-tight text-foreground">WASAYD</h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    {{ t('common.appDescription') }}
                </p>
            </div>

            <Card class="shadow-xl border-border/50">
                <CardHeader class="space-y-1 pb-4">
                    <CardTitle class="text-xl text-center">{{ t('auth.login') }}</CardTitle>
                    <CardDescription class="text-center">
                        {{ t('auth.loginDescription') }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="space-y-2">
                            <Label for="email">{{ t('auth.email') }}</Label>
                            <Input
                                id="email"
                                v-model="form.email"
                                type="email"
                                :placeholder="t('auth.emailPlaceholder')"
                                required
                                autofocus
                                autocomplete="email"
                                :class="{ 'border-destructive focus-visible:ring-destructive': form.errors.email }"
                            />
                            <p v-if="form.errors.email" class="text-sm text-destructive">
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="password">{{ t('auth.password') }}</Label>
                            <Input
                                id="password"
                                v-model="form.password"
                                type="password"
                                :placeholder="t('auth.passwordPlaceholder')"
                                required
                                autocomplete="current-password"
                                :class="{ 'border-destructive focus-visible:ring-destructive': form.errors.password }"
                            />
                            <p v-if="form.errors.password" class="text-sm text-destructive">
                                {{ form.errors.password }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <Checkbox id="remember" v-model:checked="form.remember" />
                            <Label for="remember" class="text-sm font-normal cursor-pointer">
                                {{ t('auth.rememberMe') }}
                            </Label>
                        </div>

                        <Button type="submit" class="w-full" size="lg" :disabled="form.processing">
                            <span v-if="form.processing" class="flex items-center gap-2">
                                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                </svg>
                                {{ t('common.loading') }}
                            </span>
                            <span v-else>{{ t('auth.login') }}</span>
                        </Button>
                    </form>
                </CardContent>
            </Card>

            <p class="mt-6 text-center text-xs text-muted-foreground">
                WASAYD Platform &copy; {{ new Date().getFullYear() }}
            </p>
        </div>
    </div>
</template>
