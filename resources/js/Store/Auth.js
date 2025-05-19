import { defineStore } from "pinia";
export const useStore = defineStore("auth", {
    state: () => ({
        user: null,
        token: null,
        userType: null,
        userPermissions: [],
        userComponents: [],
        supplierId: null, // เพิ่มสำหรับเก็บ supplier_number กรณีเป็น farmer
    }),
    getters: {
        getUser: (state) => state.user,
        getToken: (state) => state.token,
        getUserType: (state) => state.userType,
        isFarmer: (state) => state.userType === "farmer",
        isEmployee: (state) => state.userType === "employee",
        getUserPermissions: (state) => state.userPermissions,
        getUserComponents: (state) => state.userComponents,
        getSupplierId: (state) => state.supplierId,
    },
    actions: {
        setUser(new_user) {
            this.user = new_user;
        },
        setToken(new_token) {
            this.token = new_token;
        },
        setUserType(type) {
            this.userType = type;
        },
        setSupplierId(id) {
            this.supplierId = id;
        },
        setUserPermissions(permissions) {
            this.userPermissions = permissions;
        },
        setUserComponents(components) {
            this.userComponents = components;
        },
        logout() {
            this.user = null;
            this.token = null;
            this.userType = null;
            this.userPermissions = [];
            this.userComponents = [];
            this.supplierId = null;
        },
        // เพิ่มฟังก์ชันสำหรับโหลดสิทธิ์และ components
        async loadPermissionsAndComponents() {
            try {
                // กำหนด headers สำหรับการดึงข้อมูล
                const headers = {
                    Authorization: `Bearer ${this.token}`,
                };

                let permissionsEndpoint, componentsEndpoint;

                // เลือก endpoint ตามประเภทผู้ใช้
                if (this.userType === "farmer") {
                    permissionsEndpoint = "/api/farmer/permissions";
                    componentsEndpoint = "/api/farmer/components";
                } else {
                    permissionsEndpoint = "/api/user/permissions";
                    componentsEndpoint = "/api/user/components";
                }

                // เรียกดึงข้อมูลสิทธิ์
                const permissionsResponse = await axios.get(
                    permissionsEndpoint,
                    { headers }
                );
                this.setUserPermissions(permissionsResponse.data);

                // เรียกดึงข้อมูล components
                const componentsResponse = await axios.get(componentsEndpoint, {
                    headers,
                });
                this.setUserComponents(componentsResponse.data);

                return true;
            } catch (error) {
                console.error(
                    "Error loading permissions and components:",
                    error
                );
                return false;
            }
        },
    },
});
