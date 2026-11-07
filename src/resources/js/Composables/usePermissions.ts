import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

export function usePermissions() {
    const page = usePage()

    const user = computed(() => page.props.auth?.user)
    const permissions = computed(() => (user.value as any)?.permissions ?? [])
    const roles = computed(() => (user.value as any)?.roles ?? [])

    function can(permission: string): boolean {
        if (hasRole('super_admin')) return true
        return permissions.value.includes(permission)
    }

    function canAny(perms: string[]): boolean {
        if (hasRole('super_admin')) return true
        return perms.some((p) => permissions.value.includes(p))
    }

    function hasRole(role: string): boolean {
        return roles.value.some((r: any) => r.name === role || r === role)
    }

    return { user, can, canAny, hasRole }
}
