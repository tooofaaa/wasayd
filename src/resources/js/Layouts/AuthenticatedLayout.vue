<script setup lang="ts">
import { ref, computed } from 'vue'
import { router, usePage, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import {
    SidebarProvider,
    Sidebar,
    SidebarContent,
    SidebarHeader,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarGroupContent,
    SidebarMenu,
    SidebarMenuItem,
    SidebarMenuButton,
    SidebarTrigger,
    SidebarInset,
} from '@/Components/ui/sidebar'
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu'
import { Avatar, AvatarFallback } from '@/Components/ui/avatar'
import { Button } from '@/Components/ui/button'
import { Badge } from '@/Components/ui/badge'
import { Separator } from '@/Components/ui/separator'
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/Components/ui/collapsible'
import {
    LayoutDashboard,
    Package,
    Warehouse,
    Scan,
    ClipboardCheck,
    HardDrive,
    ArrowLeftRight,
    MapPin,
    BarChart3,
    Settings,
    ChevronDown,
    LogOut,
    User,
    Languages,
    Sun,
    Moon,
    Bell,
} from 'lucide-vue-next'

const { t, locale } = useI18n()
const page = usePage()

const user = computed(() => page.props.auth?.user as any)
const currentPath = computed(() => window.location.pathname)

const isDark = ref(document.documentElement.classList.contains('dark'))

function toggleTheme() {
    isDark.value = !isDark.value
    document.documentElement.classList.toggle('dark', isDark.value)
    localStorage.setItem('theme', isDark.value ? 'dark' : 'light')
}

function toggleLocale() {
    const newLocale = locale.value === 'ar' ? 'en' : 'ar'
    locale.value = newLocale
    document.documentElement.lang = newLocale
    document.documentElement.dir = newLocale === 'ar' ? 'rtl' : 'ltr'
    router.post('/locale', { locale: newLocale }, { preserveState: true, preserveScroll: true })
}

function logout() {
    router.post('/logout')
}

function getInitials(name: string): string {
    return name
        ?.split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2) ?? 'U'
}

interface NavItem {
    label: string
    href: string
    icon: any
    active?: boolean
}

interface NavGroup {
    label: string
    icon: any
    items: NavItem[]
}

const navigation = computed<NavGroup[]>(() => [
    {
        label: t('nav.dashboard'),
        icon: LayoutDashboard,
        items: [
            { label: t('nav.dashboard'), href: '/dashboard', icon: LayoutDashboard },
        ],
    },
    {
        label: t('nav.items'),
        icon: Package,
        items: [
            { label: t('nav.itemMaster'), href: '/items', icon: Package },
            { label: t('nav.categories'), href: '/items/categories', icon: Package },
            { label: t('nav.uom'), href: '/items/uoms', icon: Package },
        ],
    },
    {
        label: t('nav.inventory'),
        icon: Warehouse,
        items: [
            { label: t('nav.receiving'), href: '/inventory/receiving', icon: Warehouse },
            { label: t('nav.putaway'), href: '/inventory/putaway', icon: Warehouse },
            { label: t('nav.stockInquiry'), href: '/inventory/stock', icon: Warehouse },
        ],
    },
    {
        label: t('nav.rfid'),
        icon: Scan,
        items: [
            { label: t('nav.epcManagement'), href: '/rfid/epcs', icon: Scan },
            { label: t('nav.epcGeneration'), href: '/rfid/generate', icon: Scan },
            { label: t('nav.rfidReaders'), href: '/rfid/readers', icon: Scan },
            { label: t('nav.readEvents'), href: '/rfid/events', icon: Scan },
        ],
    },
    {
        label: t('nav.stockControl'),
        icon: ClipboardCheck,
        items: [
            { label: t('nav.stockCount'), href: '/control/counts', icon: ClipboardCheck },
            { label: t('nav.stockAdjustment'), href: '/control/adjustments', icon: ClipboardCheck },
            { label: t('nav.transactionLedger'), href: '/control/transactions', icon: ClipboardCheck },
        ],
    },
    {
        label: t('nav.assets'),
        icon: HardDrive,
        items: [
            { label: t('nav.assetRegistry'), href: '/assets', icon: HardDrive },
            { label: t('nav.assignments'), href: '/assets/assignments', icon: HardDrive },
            { label: t('nav.maintenance'), href: '/assets/maintenance', icon: HardDrive },
        ],
    },
    {
        label: t('nav.batchExpiry'),
        icon: Package,
        items: [
            { label: t('nav.batches'), href: '/batch/list', icon: Package },
            { label: t('nav.quarantine'), href: '/batch/quarantine', icon: Package },
            { label: t('nav.recall'), href: '/batch/recalls', icon: Package },
        ],
    },
    {
        label: t('nav.operations'),
        icon: ArrowLeftRight,
        items: [
            { label: t('nav.transfers'), href: '/operations/transfers', icon: ArrowLeftRight },
            { label: t('nav.issues'), href: '/operations/issues', icon: ArrowLeftRight },
            { label: t('nav.returns'), href: '/operations/returns', icon: ArrowLeftRight },
        ],
    },
    {
        label: t('nav.locations'),
        icon: MapPin,
        items: [
            { label: t('nav.companies'), href: '/locations/companies', icon: MapPin },
            { label: t('nav.branches'), href: '/locations/branches', icon: MapPin },
            { label: t('nav.warehouses'), href: '/locations/warehouses', icon: MapPin },
        ],
    },
    {
        label: t('nav.reports'),
        icon: BarChart3,
        items: [
            { label: t('nav.reports'), href: '/reports', icon: BarChart3 },
        ],
    },
    {
        label: t('nav.administration'),
        icon: Settings,
        items: [
            { label: t('nav.users'), href: '/admin/users', icon: Settings },
            { label: t('nav.roles'), href: '/admin/roles', icon: Settings },
            { label: t('nav.systemConfig'), href: '/admin/config', icon: Settings },
            { label: t('nav.auditLog'), href: '/admin/audit', icon: Settings },
            { label: t('nav.approvals'), href: '/admin/approvals', icon: Settings },
        ],
    },
])

function isGroupActive(group: NavGroup): boolean {
    return group.items.some((item) => currentPath.value.startsWith(item.href))
}
</script>

<template>
    <SidebarProvider>
        <Sidebar collapsible="icon" side="left">
            <SidebarHeader>
                <div class="flex items-center gap-2 px-2 py-3">
                    <div
                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary text-primary-foreground"
                    >
                        <span class="text-sm font-bold">W</span>
                    </div>
                    <div class="flex flex-col group-data-[collapsible=icon]:hidden">
                        <span class="text-sm font-semibold">WASAYD</span>
                        <span class="text-[10px] text-muted-foreground">
                            {{ t('common.appDescription') }}
                        </span>
                    </div>
                </div>
            </SidebarHeader>

            <SidebarContent>
                <template v-for="group in navigation" :key="group.label">
                    <SidebarGroup v-if="group.items.length === 1">
                        <SidebarMenu>
                            <SidebarMenuItem>
                                <SidebarMenuButton
                                    as-child
                                    :is-active="currentPath.startsWith(group.items[0].href)"
                                >
                                    <Link :href="group.items[0].href">
                                        <component :is="group.icon" class="h-4 w-4" />
                                        <span>{{ group.items[0].label }}</span>
                                    </Link>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                        </SidebarMenu>
                    </SidebarGroup>

                    <Collapsible
                        v-else
                        :default-open="isGroupActive(group)"
                        class="group/collapsible"
                    >
                        <SidebarGroup>
                            <SidebarGroupLabel as-child>
                                <CollapsibleTrigger
                                    class="flex w-full items-center gap-2"
                                >
                                    <component :is="group.icon" class="h-4 w-4" />
                                    <span class="flex-1 text-start">{{ group.label }}</span>
                                    <ChevronDown
                                        class="h-4 w-4 transition-transform group-data-[state=open]/collapsible:rotate-180"
                                    />
                                </CollapsibleTrigger>
                            </SidebarGroupLabel>
                            <CollapsibleContent>
                                <SidebarGroupContent>
                                    <SidebarMenu>
                                        <SidebarMenuItem
                                            v-for="item in group.items"
                                            :key="item.href"
                                        >
                                            <SidebarMenuButton
                                                as-child
                                                :is-active="currentPath.startsWith(item.href)"
                                            >
                                                <Link :href="item.href">
                                                    <span>{{ item.label }}</span>
                                                </Link>
                                            </SidebarMenuButton>
                                        </SidebarMenuItem>
                                    </SidebarMenu>
                                </SidebarGroupContent>
                            </CollapsibleContent>
                        </SidebarGroup>
                    </Collapsible>
                </template>
            </SidebarContent>

            <SidebarFooter>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <SidebarMenuButton class="w-full">
                                    <Avatar class="h-6 w-6">
                                        <AvatarFallback class="text-xs">
                                            {{ getInitials(user?.name ?? '') }}
                                        </AvatarFallback>
                                    </Avatar>
                                    <div class="flex flex-col group-data-[collapsible=icon]:hidden">
                                        <span class="text-sm font-medium">{{
                                            user?.name
                                        }}</span>
                                        <span class="text-xs text-muted-foreground">{{
                                            user?.email
                                        }}</span>
                                    </div>
                                </SidebarMenuButton>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="start" class="w-56">
                                <DropdownMenuItem @click="toggleLocale">
                                    <Languages class="me-2 h-4 w-4" />
                                    {{ locale === 'ar' ? 'English' : 'العربية' }}
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="toggleTheme">
                                    <component
                                        :is="isDark ? Sun : Moon"
                                        class="me-2 h-4 w-4"
                                    />
                                    {{ isDark ? t('common.lightMode') : t('common.darkMode') }}
                                </DropdownMenuItem>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem @click="logout" class="text-destructive">
                                    <LogOut class="me-2 h-4 w-4" />
                                    {{ t('auth.logout') }}
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarFooter>
        </Sidebar>

        <SidebarInset>
            <!-- Top bar -->
            <header
                class="flex h-14 items-center gap-2 border-b bg-background px-4"
            >
                <SidebarTrigger />
                <Separator orientation="vertical" class="mx-2 h-6" />

                <div class="flex-1" />

                <!-- Notifications -->
                <Button variant="ghost" size="icon" class="relative">
                    <Bell class="h-4 w-4" />
                </Button>
            </header>

            <!-- Page content -->
            <main class="flex-1 p-6">
                <slot />
            </main>
        </SidebarInset>
    </SidebarProvider>
</template>
