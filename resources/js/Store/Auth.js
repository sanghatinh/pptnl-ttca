import { defineStore } from "pinia";
import axios from "axios";

export const useStore = defineStore("auth", {
    state: () => ({
        token: localStorage.getItem("web_token") || "",
        user: JSON.parse(localStorage.getItem("web_user") || "{}"),
        userType: localStorage.getItem("user_type") || "employee",
        supplierId: localStorage.getItem("supplier_id") || "",
        userPermissions: [],
        userComponents: [],
        isLoading: false,
        isInitialized: false,
    }),

    getters: {
        getToken: (state) => state.token,
        getUser: (state) => state.user,
        getUserType: (state) => state.userType,
        getSupplierId: (state) => state.supplierId,
        getUserPermissions: (state) => state.userPermissions,
        getUserComponents: (state) => state.userComponents,
        getIsLoading: (state) => state.isLoading,
        getIsInitialized: (state) => state.isInitialized,

        // Helper getters
        hasPermission: (state) => (permissionName) => {
            return state.userPermissions.includes(permissionName);
        },

        canViewComponent: (state) => (componentName) => {
            return state.userComponents.includes(componentName);
        },

        isAuthenticated: (state) => {
            return !!state.token && Object.keys(state.user).length > 0;
        },

        // เพิ่ม getAuthHeaders method
        getAuthHeaders: (state) => () => {
            const headers = {
                Authorization: `Bearer ${state.token}`,
                "Content-Type": "application/json",
                Accept: "application/json",
            };

            if (state.userType === "farmer" && state.supplierId) {
                headers["X-User-Type"] = "farmer";
                headers["X-Supplier-Number"] = state.supplierId;
            } else {
                headers["X-User-Type"] = "employee";
            }

            return headers;
        },
    },

    actions: {
        setToken(token) {
            this.token = token;
            localStorage.setItem("web_token", token);
            this.setupAxiosInterceptors();
        },

        setUser(user) {
            this.user = user;
            localStorage.setItem("web_user", JSON.stringify(user));
        },

        setUserType(type) {
            this.userType = type;
            localStorage.setItem("user_type", type);
        },

        setSupplierId(id) {
            this.supplierId = id;
            localStorage.setItem("supplier_id", id);
        },

        setUserPermissions(permissions) {
            this.userPermissions = permissions;
        },

        setUserComponents(components) {
            this.userComponents = components;
        },

        setIsInitialized(status) {
            this.isInitialized = status;
        },

        logout() {
            this.token = "";
            this.user = {};
            this.userType = "employee";
            this.supplierId = "";
            this.userPermissions = [];
            this.userComponents = [];
            this.isInitialized = false;

            // Clear localStorage
            const keysToRemove = [
                "web_token",
                "web_user",
                "user_type",
                "supplier_id",
                "bienban_filter_state",
                "payment_request_filter_state",
                "congno_filter_state",
            ];

            keysToRemove.forEach((key) => localStorage.removeItem(key));
        },

        setupAxiosInterceptors() {
            // Set default authorization header
            axios.defaults.headers.common[
                "Authorization"
            ] = `Bearer ${this.token}`;

            // Add request interceptor for user type and supplier ID
            axios.interceptors.request.use((config) => {
                if (this.userType === "farmer" && this.supplierId) {
                    config.headers["X-User-Type"] = this.userType;
                    config.headers["X-Supplier-Number"] = this.supplierId;
                } else {
                    config.headers["X-User-Type"] = "employee";
                }
                return config;
            });

            // Add response interceptor for handling 401 errors
            axios.interceptors.response.use(
                (response) => response,
                (error) => {
                    if (error.response?.status === 401) {
                        this.logout();
                        window.location.href = "/login";
                    }
                    return Promise.reject(error);
                }
            );
        },

        async initializeAuth() {
            if (this.isInitialized) {
                return true;
            }

            if (!this.token) {
                return false;
            }

            this.isLoading = true;

            try {
                this.setupAxiosInterceptors();
                await this.loadPermissionsAndComponents();
                this.isInitialized = true;
                return true;
            } catch (error) {
                console.error("Error initializing auth:", error);
                this.logout();
                return false;
            } finally {
                this.isLoading = false;
            }
        },

        async loadPermissionsAndComponents() {
            try {
                const headers = {
                    Authorization: `Bearer ${this.token}`,
                };

                let permissionsEndpoint, componentsEndpoint;

                if (this.userType === "farmer") {
                    permissionsEndpoint = "/api/farmer/permissions";
                    componentsEndpoint = "/api/farmer/components";
                    headers["X-User-Type"] = "farmer";
                    headers["X-Supplier-Number"] = this.supplierId;
                } else {
                    permissionsEndpoint = "/api/user/permissions";
                    componentsEndpoint = "/api/user/components";
                    headers["X-User-Type"] = "employee";
                }

                // Load permissions and components in parallel
                const [permissionsResponse, componentsResponse] =
                    await Promise.all([
                        axios.get(permissionsEndpoint, { headers }),
                        axios.get(componentsEndpoint, { headers }),
                    ]);

                this.setUserPermissions(permissionsResponse.data);
                this.setUserComponents(componentsResponse.data);

                return true;
            } catch (error) {
                console.error(
                    "Error loading permissions and components:",
                    error
                );
                throw error;
            }
        },

        async refreshPermissions() {
            if (!this.token) return false;

            try {
                await this.loadPermissionsAndComponents();
                return true;
            } catch (error) {
                console.error("Error refreshing permissions:", error);
                return false;
            }
        },

        // เพิ่ม method สำหรับสร้าง headers (alternative approach)
        getHeaders() {
            const headers = {
                Authorization: `Bearer ${this.token}`,
                "Content-Type": "application/json",
                Accept: "application/json",
            };

            if (this.userType === "farmer" && this.supplierId) {
                headers["X-User-Type"] = "farmer";
                headers["X-Supplier-Number"] = this.supplierId;
            } else {
                headers["X-User-Type"] = "employee";
            }

            return headers;
        },
    },
});
