<script setup lang="ts">
import { computed, ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { ScrollArea } from '@/Components/ui/scroll-area'
import {
    LayoutDashboard,
    MapPin,
    Building2,
    GitBranch,
    Warehouse,
    Layers,
    ArrowRightLeft,
    Server,
    BookOpen,
    Box,
    Settings,
    Users,
    Shield,
    Cog,
    FileText,
    ChevronDown,
    ChevronRight,
    Package,
    Tag,
    Ruler,
    PackageOpen,
    ClipboardList,
    BarChart3,
    Cpu,
    Radio,
    ScanLine,
    Activity,
    Monitor,
    ClipboardCheck,
    Scale,
    Receipt,
    CheckSquare,
    HardDrive,
    Wrench,
    ArrowLeftRight,
    ShieldAlert,
    AlertTriangle,
    Ban,
    Pill,
    BarChart,
    Download,
    Bell,
    Search,
} from 'lucide-vue-next'

const emit = defineEmits<{ navigate: [] }>()
const { t } = useI18n()
const page = usePage()

const currentRoute = computed(() => {
    try { return route().current() } catch { return '' }
})

interface NavItem {
    label: string
    icon: any
    route?: string
    routePrefix?: string
}

interface NavGroup {
    label: string
    icon: any
    routePrefix: string
    items: NavItem[]
}

const groups: NavGroup[] = [
    {
        label: 'nav.itemCatalog',
        icon: Package,
        routePrefix: 'items.',
        items: [
            { label: 'nav.categories', icon: Tag, route: 'items.categories.index' },
            { label: 'nav.subcategories', icon: Tag, route: 'items.subcategories.index' },
            { label: 'nav.uom', icon: Ruler, route: 'items.uoms.index' },
            { label: 'nav.items', icon: Package, route: 'items.items.index' },
        ],
    },
    {
        label: 'nav.inventory',
        icon: PackageOpen,
        routePrefix: 'inventory.',
        items: [
            { label: 'nav.receiving', icon: ClipboardList, route: 'inventory.receivings.index' },
            { label: 'nav.stock', icon: BarChart3, route: 'inventory.stock.index' },
        ],
    },
    {
        label: 'nav.rfid',
        icon: Cpu,
        routePrefix: 'rfid.',
        items: [
            { label: 'nav.epcs', icon: ScanLine, route: 'rfid.epcs.index' },
            { label: 'nav.readers', icon: Radio, route: 'rfid.readers.index' },
            { label: 'nav.readEvents', icon: Activity, route: 'rfid.read-events.index' },
            { label: 'nav.encodingLogs', icon: Monitor, route: 'rfid.encoding-logs.index' },
        ],
    },
    {
        label: 'nav.control',
        icon: ClipboardCheck,
        routePrefix: 'inventory.stock-',
        items: [
            { label: 'nav.stockCounts', icon: ClipboardCheck, route: 'inventory.stock-counts.index' },
            { label: 'nav.stockAdjustments', icon: Scale, route: 'inventory.stock-adjustments.index' },
            { label: 'nav.transactions', icon: Receipt, route: 'inventory.transactions.index' },
            { label: 'nav.approvals', icon: CheckSquare, route: 'inventory.approvals.index' },
        ],
    },
    {
        label: 'nav.assets',
        icon: HardDrive,
        routePrefix: 'assets.',
        items: [
            { label: 'nav.assetRegistry', icon: HardDrive, route: 'assets.index' },
            { label: 'nav.assetTransfers', icon: ArrowLeftRight, route: 'assets.transfers.index' },
        ],
    },
    {
        label: 'nav.operations',
        icon: ShieldAlert,
        routePrefix: 'operations.',
        items: [
            { label: 'nav.quarantines', icon: Ban, route: 'operations.quarantines.index' },
            { label: 'nav.recalls', icon: AlertTriangle, route: 'operations.recalls.index' },
            { label: 'nav.controlledMeds', icon: Pill, route: 'operations.controlled-medications.index' },
            { label: 'nav.putaways', icon: Box, route: 'operations.putaways.index' },
        ],
    },
    {
        label: 'nav.reports',
        icon: BarChart,
        routePrefix: 'reports.',
        items: [
            { label: 'nav.reportsDashboard', icon: BarChart, route: 'reports.index' },
        ],
    },
    {
        label: 'nav.locations',
        icon: MapPin,
        routePrefix: 'locations.',
        items: [
            { label: 'nav.branches', icon: GitBranch, route: 'locations.branches.index' },
            { label: 'nav.warehouses', icon: Warehouse, route: 'locations.warehouses.index' },
            { label: 'nav.zones', icon: Layers, route: 'locations.zones.index' },
            { label: 'nav.aisles', icon: ArrowRightLeft, route: 'locations.aisles.index' },
            { label: 'nav.racks', icon: Server, route: 'locations.racks.index' },
            { label: 'nav.shelves', icon: BookOpen, route: 'locations.shelves.index' },
            { label: 'nav.bins', icon: Box, route: 'locations.bins.index' },
        ],
    },
    {
        label: 'nav.administration',
        icon: Settings,
        routePrefix: 'admin.',
        items: [
            { label: 'nav.users', icon: Users, route: 'admin.users.index' },
            { label: 'nav.roles', icon: Shield, route: 'admin.roles.index' },
            { label: 'nav.systemConfig', icon: Cog, route: 'admin.config.index' },
            { label: 'nav.auditLog', icon: FileText, route: 'admin.audit-logs.index' },
            { label: 'nav.importExport', icon: Download, route: 'import-export.index' },
        ],
    },
]

const expandedGroups = ref<Record<string, boolean>>({})

function isActive(routeName: string) {
    return currentRoute.value === routeName
}

function isGroupActive(group: NavGroup) {
    if (group.routePrefix) {
        return currentRoute.value?.startsWith(group.routePrefix) || false
    }
    return group.items.some(item => item.route && currentRoute.value === item.route)
}

function toggleGroup(label: string) {
    expandedGroups.value[label] = !expandedGroups.value[label]
}

function isExpanded(group: NavGroup) {
    if (expandedGroups.value[group.label] !== undefined) {
        return expandedGroups.value[group.label]
    }
    return isGroupActive(group)
}

function routeExists(name: string): boolean {
    try {
        route(name)
        return true
    } catch {
        return false
    }
}
</script>

<template>
    <div class="flex h-full flex-col">
        <div class="flex h-16 items-center px-6 border-b">
            <Link :href="route('dashboard')" class="flex items-center gap-2">
                <span class="text-xl font-bold text-primary">WASAYD</span>
            </Link>
        </div>
        <ScrollArea class="flex-1 px-3 py-4">
            <nav class="space-y-1">
                <!-- Dashboard -->
                <div class="mb-4">
                    <Link
                        :href="route('dashboard')"
                        class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors"
                        :class="[
                            isActive('dashboard')
                                ? 'bg-primary/10 text-primary'
                                : 'text-muted-foreground hover:bg-muted hover:text-foreground',
                        ]"
                        @click="emit('navigate')"
                    >
                        <LayoutDashboard class="h-4 w-4" />
                        {{ t('nav.dashboard') }}
                    </Link>
                </div>

                <!-- Dynamic Groups -->
                <div v-for="group in groups" :key="group.label" class="mb-1">
                    <button
                        @click="toggleGroup(group.label)"
                        class="flex w-full items-center justify-between gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors"
                        :class="[
                            isGroupActive(group)
                                ? 'text-primary'
                                : 'text-muted-foreground hover:bg-muted hover:text-foreground',
                        ]"
                        :aria-expanded="isExpanded(group)"
                    >
                        <div class="flex items-center gap-3">
                            <component :is="group.icon" class="h-4 w-4" />
                            {{ t(group.label) }}
                        </div>
                        <ChevronDown v-if="isExpanded(group)" class="h-4 w-4" />
                        <ChevronRight v-else class="h-4 w-4" />
                    </button>
                    <div v-if="isExpanded(group)" class="mt-1 space-y-1 ps-4">
                        <template v-for="item in group.items" :key="item.route">
                            <Link
                                v-if="item.route && routeExists(item.route)"
                                :href="route(item.route)"
                                class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors"
                                :class="[
                                    isActive(item.route!)
                                        ? 'bg-primary/10 text-primary'
                                        : 'text-muted-foreground hover:bg-muted hover:text-foreground',
                                ]"
                                @click="emit('navigate')"
                            >
                                <component :is="item.icon" class="h-4 w-4" />
                                {{ t(item.label) }}
                            </Link>
                        </template>
                    </div>
                </div>
            </nav>
        </ScrollArea>
    </div>
</template>
