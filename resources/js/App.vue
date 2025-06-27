<template lang="">
    <!-- Loading starts -->
    <div v-if="store.isLoading" class="loading-overlay">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    <!-- Loading ends -->

    <!-- For login/non-authenticated pages -->
    <div
        v-if="!store.getToken || $route.path === '/login'"
        class="auth-wrapper"
    >
        <router-view></router-view>
    </div>

    <!-- For authenticated pages -->
    <div v-else class="page-wrapper">
        <!-- Page content start  -->
        <div class="page-content">
            <!-- Sidebar menu -->
            <side-barmenu></side-barmenu>
            <!-- Sidebar menu end -->
            <!-- Header start -->
            <header-bar></header-bar>
            <!--Heder END-->

            <!-- Main container stars -->
            <div class="main-container">
                <router-view></router-view>
                <!-- Main container end -->
            </div>
            <!-- Page content end -->
        </div>
    </div>
</template>
<script>
import { useStore } from "./Store/Auth";
export default {
    name: "App",
    setup() {
        const store = useStore();
        return { store };
    },

    async created() {
        // เรียก initializeAuth เมื่อ app เริ่มต้น
        if (this.store.token) {
            try {
                await this.store.initializeAuth();
            } catch (error) {
                console.error("Failed to initialize auth:", error);
            }
        }
    },

    mounted() {
        this.rebindSidebarToggle();
        // rebind ทุกครั้งที่เปลี่ยน route
        this.$watch(
            () => this.$route.fullPath,
            () => {
                this.$nextTick(() => {
                    this.rebindSidebarToggle();
                });
            }
        );
    },
    methods: {
        rebindSidebarToggle() {
            if (typeof window.$ !== "undefined") {
                window
                    .$("#toggle-sidebar")
                    .off("click")
                    .on("click", function (event) {
                        event.preventDefault();
                        window.$(".page-wrapper").toggleClass("toggled");
                    });
            }
        },
    },
};
</script>
<style>
.auth-wrapper {
    min-height: 100vh;
    width: 100%;
    overflow-x: hidden;
    position: relative;
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}
</style>
