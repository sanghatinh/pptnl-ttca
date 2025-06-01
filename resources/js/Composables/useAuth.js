import { computed } from "vue";
import { useStore } from "../Store/Auth";

export function useAuth() {
    const store = useStore();

    const isAuthenticated = computed(() => store.isAuthenticated);
    const isReady = computed(() => store.isReady);
    const isLoading = computed(() => store.isLoading);
    const userPermissions = computed(() => store.userPermissions);
    const userComponents = computed(() => store.userComponents);

    const hasPermission = (permissionName) => {
        return store.hasPermission(permissionName);
    };

    const canViewComponent = (componentName) => {
        return store.canViewComponent(componentName);
    };

    const waitForAuth = async () => {
        if (!store.isAuthenticated) {
            throw new Error("User not authenticated");
        }

        if (!store.permissionsLoaded) {
            await store.waitForPermissions();
        }

        return true;
    };

    return {
        store,
        isAuthenticated,
        isReady,
        isLoading,
        userPermissions,
        userComponents,
        hasPermission,
        canViewComponent,
        waitForAuth,
    };
}
