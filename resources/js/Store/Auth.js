import { defineStore } from "pinia";
export const useStore = defineStore("auth", {
    state: () => ({
        user: null,
        token: null,
        userType: null, // เพิ่มค่าเก็บประเภทผู้ใช้ ('employee' หรือ 'farmer')
    }),
    getters: {
        getUser: (state) => state.user,
        getToken: (state) => state.token,
        getUserType: (state) => state.userType,
        isFarmer: (state) => state.userType === "farmer",
        isEmployee: (state) => state.userType === "employee",
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
        logout() {
            this.user = null;
            this.token = null;
            this.userType = null;
        },
    },
});
