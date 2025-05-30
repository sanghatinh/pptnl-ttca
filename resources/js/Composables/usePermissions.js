import { computed } from "vue";
import { useStore } from "../Store/Auth";

export function usePermissions() {
    const store = useStore();

    const permissions = computed(() => store.getUserPermissions);
    const components = computed(() => store.getUserComponents);
    const isLoading = computed(() => store.getIsLoading);
    const isInitialized = computed(() => store.getIsInitialized);

    const hasPermission = (permissionName) => {
        return store.hasPermission(permissionName);
    };

    const canViewComponent = (componentName) => {
        return store.canViewComponent(componentName);
    };

    const hasAnyPermission = (permissionsList) => {
        return permissionsList.some((permission) => hasPermission(permission));
    };

    const hasAllPermissions = (permissionsList) => {
        return permissionsList.every((permission) => hasPermission(permission));
    };

    const canViewAnyComponent = (componentsList) => {
        return componentsList.some((component) => canViewComponent(component));
    };

    const canViewAllComponents = (componentsList) => {
        return componentsList.every((component) => canViewComponent(component));
    };

    const refreshPermissions = async () => {
        return await store.refreshPermissions();
    };

    return {
        permissions,
        components,
        isLoading,
        isInitialized,
        hasPermission,
        canViewComponent,
        hasAnyPermission,
        hasAllPermissions,
        canViewAnyComponent,
        canViewAllComponents,
        refreshPermissions,
    };
}
