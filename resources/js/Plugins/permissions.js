import { useStore } from "../Store/Auth";

export default {
    install(app) {
        const store = useStore();

        // Global properties
        app.config.globalProperties.$hasPermission = (permissionName) => {
            return store.hasPermission(permissionName);
        };

        app.config.globalProperties.$canViewComponent = (componentName) => {
            return store.canViewComponent(componentName);
        };

        app.config.globalProperties.$userPermissions = () => {
            return store.getUserPermissions;
        };

        app.config.globalProperties.$userComponents = () => {
            return store.getUserComponents;
        };

        app.config.globalProperties.$refreshPermissions = async () => {
            return await store.refreshPermissions();
        };

        // Global methods for checking multiple permissions
        app.config.globalProperties.$hasAnyPermission = (permissions) => {
            return permissions.some((permission) =>
                store.hasPermission(permission)
            );
        };

        app.config.globalProperties.$hasAllPermissions = (permissions) => {
            return permissions.every((permission) =>
                store.hasPermission(permission)
            );
        };

        app.config.globalProperties.$canViewAnyComponent = (components) => {
            return components.some((component) =>
                store.canViewComponent(component)
            );
        };

        app.config.globalProperties.$canViewAllComponents = (components) => {
            return components.every((component) =>
                store.canViewComponent(component)
            );
        };
    },
};
