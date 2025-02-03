import { defineStore } from "pinia";
export const useStore = defineStore('auth',{

    state: () => ({
        user: null,
        token: null
    }),
    getters: {
        getUser: (state) => state.user,
        getToken: (state) => state.token
    },
    actions: {
        setUser(new_user){
            this.user = new_user;
        },
        setToken(new_token){
            this.token = new_token;
        },
        logout(){
            this.user = null;
            this.token = null;
        }
    }

});