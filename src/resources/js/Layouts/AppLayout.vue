<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { usePage, Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { toast } from 'vue-sonner'
import { Button } from '@/Components/ui/button'
import { Avatar, AvatarFallback } from '@/Components/ui/avatar'
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu'
import { Sheet, SheetContent, SheetTrigger } from '@/Components/ui/sheet'
import { Separator } from '@/Components/ui/separator'
import { Toaster } from '@/Components/ui/sonner'
import Sidebar from './partials/Sidebar.vue'

const { t, locale } = useI18n()
const page = usePage()

const user = computed(() => page.props.auth?.user)
const sidebarOpen = ref(false)

const initials = computed(() => {
    if (!user.value?.name) return '?'
    return user.value.name
        .split(' ')
        .map((n: string) => n[0])
        .join('')
        .substring(0, 2)
        .toUpperCase()
})

function toggleLocale() {
    const newLocale = locale.value === 'ar' ? 'en' : 'ar'
    locale.value = newLocale
    document.documentElement.lang = newLocale
    document.documentElement.dir = newLocale === 'ar' ? 'rtl' : 'ltr'
    router.post(route('locale.update'), { locale: newLocale }, { preserveState: true })
}

function logout() {
    router.post(route('logout'))
}

const flash = computed(() => page.props.flash as { success?: string; error?: string; warning?: string })
watch(() => flash.value, (f) => {
    if (f?.success) toast.success(f.success)
    if (f?.error) toast.error(f.error)
    if (f?.warning) toast.warning(f.warning)
}, { deep: true })
</script>

<template>
    <div class="min-h-screen bg-background">
        <Toaster position="top-right" :dir="locale === 'ar' ? 'rtl' : 'ltr'" />
        <!-- Mobile sidebar -->
        <Sheet v-model:open="sidebarOpen">
            <SheetContent side="start" class="w-72 p-0">
                <Sidebar @navigate="sidebarOpen = false" />
            </SheetContent>
        </Sheet>

        <!-- Desktop sidebar -->
        <aside class="fixed inset-y-0 start-0 z-30 hidden w-64 border-e bg-card lg:block">
            <Sidebar />
        </aside>

        <!-- Main content -->
        <div class="lg:ps-64">
            <!-- Top bar -->
            <header class="sticky top-0 z-20 flex h-16 items-center gap-4 border-b bg-card px-4 lg:px-6">
                <Button
                    variant="ghost"
                    size="icon"
                    class="lg:hidden"
                    @click="sidebarOpen = true"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" x2="21" y1="6" y2="6"/><line x1="3" x2="21" y1="12" y2="12"/><line x1="3" x2="21" y1="18" y2="18"/></svg>
                </Button>

                <div class="flex-1" />

                <Button variant="ghost" size="sm" @click="toggleLocale">
                    {{ locale === 'ar' ? 'EN' : 'عربي' }}
                </Button>

                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="ghost" class="relative h-9 w-9 rounded-full">
                            <Avatar class="h-9 w-9">
                                <AvatarFallback>{{ initials }}</AvatarFallback>
                            </Avatar>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-56">
                        <div class="flex items-center gap-2 p-2">
                            <div class="flex flex-col space-y-1">
                                <p class="text-sm font-medium">{{ user?.name }}</p>
                                <p class="text-xs text-muted-foreground">{{ user?.email }}</p>
                            </div>
                        </div>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem @click="logout">
                            {{ t('nav.logout') }}
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </header>

            <!-- Page content -->
            <main class="p-4 lg:p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
