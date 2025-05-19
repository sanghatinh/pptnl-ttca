<template>
    <!-- Sidebar wrapper start -->
    <nav id="sidebar" class="sidebar-wrapper">
        <!-- Sidebar brand start  -->
        <div class="sidebar-brand">
            <router-link to="/" class="logo">
                <img
                    :src="`${url}/img/Logo/TTC LOGO FFF.png`"
                    alt="Le Rouge Admin Dashboard"
                />
                <span class="text-logo-company">
                    TTC ATTAPEU SUGAR CANE SOLE CO.,LTD
                </span>
            </router-link>
        </div>
        <!-- Sidebar brand end  -->

        <!-- Sidebar content start -->
        <div class="sidebar-content">
            <!-- sidebar menu start -->
            <div class="sidebar-menu">
                <ul>
                    <li class="header-menu">General</li>
                    <li class="sidebar-dropdown active">
                        <a href="#">
                            <i class="fa-solid fa-folder-open"></i>
                            <span class="menu-text">Quản lý hồ sơ</span>
                        </a>
                        <div class="sidebar-submenu">
                            <ul>
                                <!-- Assuming permission check for each dashboard if needed -->

                                <li
                                    v-if="hasAccessToComponent('Quản lý hồ sơ')"
                                >
                                    <router-link
                                        to="/DanhsachHoso"
                                        :class="
                                            $route.path === '/DanhsachHoso'
                                                ? 'current-page'
                                                : ''
                                        "
                                        @click="closeSidebar"
                                    >
                                        <span class="menu-text"
                                            >Danh sách giao nhận hồ sơ</span
                                        >
                                    </router-link>
                                </li>

                                <li
                                    v-if="userCanViewComponent('Quản lý hồ sơ')"
                                >
                                    <router-link
                                        to="/Bienbannghiemthudichvu"
                                        :class="
                                            $route.path ===
                                            '/Bienbannghiemthudichvu'
                                                ? 'current-page'
                                                : ''
                                        "
                                        @click="closeSidebar"
                                    >
                                        <span class="menu-text"
                                            >Biên bản nghiệm thu dịch vụ</span
                                        >
                                    </router-link>
                                </li>
                                <li
                                    v-if="userCanViewComponent('Quản lý hồ sơ')"
                                >
                                    <router-link
                                        to="/Phieugiaonhanhomgiong"
                                        :class="
                                            $route.path ===
                                            '/Phieugiaonhanhomgiong'
                                                ? 'current-page'
                                                : ''
                                        "
                                        @click="closeSidebar"
                                    >
                                        <span class="menu-text"
                                            >Phiếu giao nhận hom giống</span
                                        >
                                    </router-link>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>

                <!-- Quản lý tài chính -->
                <ul>
                    <li class="sidebar-dropdown active">
                        <a href="#">
                            <i class="fa-solid fa-hand-holding-dollar"></i>
                            <span class="menu-text">Quản lý tài chính</span>
                        </a>
                        <div class="sidebar-submenu">
                            <ul>
                                <li>
                                    <router-link
                                        to="/Phieutrinhthanhtoan"
                                        :class="
                                            $route.path ===
                                            '/Phieutrinhthanhtoan'
                                                ? 'current-page'
                                                : ''
                                        "
                                        @click="closeSidebar"
                                    >
                                        <span class="menu-text"
                                            >Phiếu trình thanh toán dịch
                                            vụ</span
                                        >
                                    </router-link>
                                </li>
                                <li>
                                    <router-link
                                        to="/Phieudenghithanhtoandichvu"
                                        :class="
                                            $route.path ===
                                            '/Phieudenghithanhtoandichvu'
                                                ? 'current-page'
                                                : ''
                                        "
                                        @click="closeSidebar"
                                    >
                                        <span class="menu-text"
                                            >Phiếu đề nghị thanh toán dịch
                                            vụ</span
                                        >
                                    </router-link>
                                </li>

                                <li>
                                    <router-link
                                        to="/Phieuthunodichvu"
                                        :class="
                                            $route.path === '/Phieuthunodichvu'
                                                ? 'current-page'
                                                : ''
                                        "
                                        @click="closeSidebar"
                                    >
                                        <span class="menu-text"
                                            >Phiếu thu nợ dịch vụ</span
                                        >
                                    </router-link>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>

                <!-- Quản lý công nợ-->
                <ul>
                    <li class="sidebar-dropdown active">
                        <a href="#">
                            <i class="fas fa-file-invoice-dollar"></i>
                            <span class="menu-text">Quản lý công nợ</span>
                        </a>
                        <div class="sidebar-submenu">
                            <ul>
                                <li>
                                    <router-link
                                        to="/CongnoDichvuKhautru"
                                        :class="
                                            $route.path ===
                                            '/CongnoDichvuKhautru'
                                                ? 'current-page'
                                                : ''
                                        "
                                        @click="closeSidebar"
                                    >
                                        <span class="menu-text"
                                            >Công nợ dịch vụ khấu trừ</span
                                        >
                                    </router-link>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>

                <!-- Quản lý hệ thống -->
                <ul>
                    <li class="sidebar-dropdown active">
                        <a href="#">
                            <i class="fa-solid fa-screwdriver-wrench"></i>
                            <span class="menu-text">Quản lý hệ thống</span>
                        </a>
                        <div class="sidebar-submenu">
                            <ul>
                                <li
                                    v-if="
                                        userCanViewComponent('Danh sách User')
                                    "
                                >
                                    <router-link
                                        to="/user"
                                        :class="
                                            $route.path === '/user'
                                                ? 'current-page'
                                                : ''
                                        "
                                        @click="closeSidebar"
                                    >
                                        <span>Danh sách User</span>
                                    </router-link>
                                </li>
                                <li v-if="userCanViewComponent('Cấp quyền')">
                                    <router-link
                                        to="/permission"
                                        :class="
                                            $route.path === '/permission'
                                                ? 'current-page'
                                                : ''
                                        "
                                        @click="closeSidebar"
                                    >
                                        <span>Cấp quyền</span>
                                    </router-link>
                                </li>
                                <li
                                    v-if="
                                        userCanViewComponent('Nhóm Cấp quyền')
                                    "
                                >
                                    <router-link
                                        to="/role"
                                        :class="
                                            $route.path === '/role'
                                                ? 'current-page'
                                                : ''
                                        "
                                        @click="closeSidebar"
                                    >
                                        <span>Nhóm Cấp quyền</span>
                                    </router-link>
                                </li>
                                <li>
                                    <router-link
                                        to="/Profile"
                                        :class="
                                            $route.path === '/Profile'
                                                ? 'current-page'
                                                : ''
                                        "
                                        @click="closeSidebar"
                                    >
                                        <span>Profile</span>
                                    </router-link>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>
            <!-- sidebar menu end -->
        </div>
        <!-- Sidebar content end -->
    </nav>
    <!-- Sidebar wrapper end -->
</template>

<script>
import axios from "axios";
import { useStore } from "../Store/Auth";
export default {
    setup() {
        const store = useStore();
        return {
            store,
        };
    },
    data() {
        return {
            url: window.location.origin,
            userPermissions: [],
            userComponents: [],
            isMobile: false,
        };
    },
    methods: {
        hasAccessToComponent(componentName) {
            return this.store.getUserComponents.includes(componentName);
        },

        // ตรวจสอบว่ามีสิทธิ์นั้นหรือไม่
        hasPermission(permissionName) {
            return this.store.getUserPermissions.includes(permissionName);
        },
        fetchUserData() {
            // กำหนด headers
            const headers = {
                Authorization: "Bearer " + this.store.getToken,
            };

            // เลือก endpoint ตามประเภทผู้ใช้
            const userType = this.store.getUserType;
            const permissionsEndpoint =
                userType === "farmer"
                    ? "/api/farmer/permissions"
                    : "/api/user/permissions";
            const componentsEndpoint =
                userType === "farmer"
                    ? "/api/farmer/components"
                    : "/api/user/components";

            // Fetch user permissions
            axios
                .get(permissionsEndpoint, { headers })
                .then((response) => {
                    this.userPermissions = response.data;
                })
                .catch((error) =>
                    console.error("Error fetching permissions:", error)
                );

            // Fetch user components
            axios
                .get(componentsEndpoint, { headers })
                .then((response) => {
                    this.userComponents = response.data;
                })
                .catch((error) =>
                    console.error("Error fetching components:", error)
                );
        },
        userHasPermission(permissionName) {
            return this.userPermissions.includes(permissionName);
        },
        userCanViewComponent(componentName) {
            return this.userComponents.includes(componentName);
        },
        // เพิ่มฟังก์ชันใหม่สำหรับการปิด sidebar บนมือถือ
        closeSidebar() {
            // เช็คว่าอุปกรณ์มีขนาดหน้าจอเล็กหรือไม่ (เช่น มือถือ)
            if (window.innerWidth <= 768) {
                // ปิด sidebar โดยการลบ class "toggled"
                document
                    .querySelector(".page-wrapper")
                    ?.classList.remove("toggled");
            }
        },
        // เพิ่มฟังก์ชันตรวจสอบขนาดหน้าจอ
        checkScreenSize() {
            this.isMobile = window.innerWidth <= 768;
        },
    },
    mounted() {
        this.fetchUserData();
        // ตรวจจับขนาดหน้าจอเมื่อโหลดครั้งแรก
        this.checkScreenSize();
        // ตรวจจับการเปลี่ยนแปลงขนาดหน้าจอ
        window.addEventListener("resize", this.checkScreenSize);
    },
    unmounted() {
        // ลบ event listener เมื่อคอมโพเนนต์ถูกทำลาย
        window.removeEventListener("resize", this.checkScreenSize);
    },
};
</script>

<style scoped>
.text-logo-company {
    font-size: 12px;
    margin-top: 10px;
    margin-left: 10px;
    color: #fff;
    font-weight: bold;
    /* For vertical alignment */
    transform: rotate(90deg);
    /* For horizontal alignment */
    transform: rotate(0deg);
}
.sidebar-menu ul {
    padding-left: 0;
}
</style>
