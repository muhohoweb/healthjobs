import { usePage } from '@inertiajs/vue3'

export const useAuth = () => {
    const page = usePage()
    const user = page.props.auth.user

    const hasRole = (role:string) => {
        if (!user?.roles || !Array.isArray(user.roles)) return false
        return user.roles.some(r =>
            (typeof r === 'string' && r === role) ||
            (typeof r === 'object' && r.name === role)
        )
    }

    const hasPermission = (permission:string) => {
        if (!user?.permissions || !Array.isArray(user.permissions)) return false
        return user.permissions.some(p =>
            (typeof p === 'string' && p === permission) ||
            (typeof p === 'object' && p.name === permission)
        )
    }

    const hasAnyRole = (roles:string) => {
        if (!user?.roles || !Array.isArray(user.roles)) return false
        return roles.some(role =>
            user.roles.some(r =>
                (typeof r === 'string' && r === role) ||
                (typeof r === 'object' && r.name === role)
            )
        )
    }

    const hasAllRoles = (roles) => {
        if (!user?.roles || !Array.isArray(user.roles)) return false
        return roles.every(role =>
            user.roles.some(r =>
                (typeof r === 'string' && r === role) ||
                (typeof r === 'object' && r.name === role)
            )
        )
    }

    const hasAnyPermission = (permissions:string) => {
        if (!user?.permissions || !Array.isArray(user.permissions)) return false
        return permissions.some(permission =>
            user.permissions.some(p =>
                (typeof p === 'string' && p === permission) ||
                (typeof p === 'object' && p.name === permission)
            )
        )
    }

    const hasAllPermissions = (permissions:string) => {
        if (!user?.permissions || !Array.isArray(user.permissions)) return false
        return permissions.every(permission =>
            user.permissions.some(p =>
                (typeof p === 'string' && p === permission) ||
                (typeof p === 'object' && p.name === permission)
            )
        )
    }

    const canAccess = (requirements = {}) => {
        const {
            requiredRoles = [],
            requiredPermissions = [],
            requireAnyRole = false,
            requireAnyPermission = false
        } = requirements

        let hasRequiredRoles = true
        let hasRequiredPermissions = true

        if (requiredRoles.length > 0) {
            hasRequiredRoles = requireAnyRole ? hasAnyRole(requiredRoles) : hasAllRoles(requiredRoles)
        }

        if (requiredPermissions.length > 0) {
            hasRequiredPermissions = requireAnyPermission ? hasAnyPermission(requiredPermissions) : hasAllPermissions(requiredPermissions)
        }

        return hasRequiredRoles && hasRequiredPermissions
    }

    // ─── Subscription helpers ────────────────────────────────────
    const hasActiveSubscription = (): boolean => {
        return user?.hasActiveSubscription ?? false
    }

    const activeSubscription = () => {
        return user?.activeSubscription ?? null
    }

    const daysUntilExpiry = (): number | null => {
        const expiry = user?.activeSubscription?.expires_at
        if (!expiry) return null
        const diff = new Date(expiry).getTime() - new Date().getTime()
        return Math.ceil(diff / (1000 * 60 * 60 * 24))
    }

    return {
        ...user,
        hasRole,
        hasPermission,
        hasAnyRole,
        hasAllRoles,
        hasAnyPermission,
        hasAllPermissions,
        canAccess,

        // Subscription
        hasActiveSubscription,
        activeSubscription,
        daysUntilExpiry,
    }
}