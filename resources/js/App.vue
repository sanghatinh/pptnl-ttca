<template lang="">
    <!-- Loading starts -->
    <div id="loading-wrapper">
        <div class="spinner-border" role="status">
            <span class="sr-only">Loading...</span>
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
    setup() {
        const store = useStore();
        return {
            store,
        };
    },
    data() {
        return {
            url: window.location.href,
        };
    },
    name: "Sidebarmenu",
};
</script>
<style>
.auth-wrapper {
    min-height: 100vh;
    width: 100%;
    overflow-x: hidden;
    position: relative;
}
</style>
