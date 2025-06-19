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

                    <li
                        class="sidebar-dropdown"
                        :class="{ active: activeDropdowns.dashboard }"
                    >
                        <router-link
                            to="/Dashboard"
                            :class="
                                $route.path === '/Dashboard'
                                    ? 'current-page'
                                    : ''
                            "
                            @click="closeSidebar"
                        >
                            <i class="fa-solid fa-house"></i>
                            <span class="menu-text">Dashboard</span>
                        </router-link>
                    </li>
                    <li
                        class="sidebar-dropdown"
                        :class="{ active: activeDropdowns.dashboardFarmer }"
                    >
                        <router-link
                            to="/DashboardFarmer"
                            :class="
                                $route.path === '/DashboardFarmer'
                                    ? 'current-page'
                                    : ''
                            "
                            @click="closeSidebar"
                        >
                            <i class="fa-solid fa-house"></i>
                            <span class="menu-text">Dashboard</span>
                        </router-link>
                    </li>

                    <li
                        class="sidebar-dropdown"
                        :class="{ active: activeDropdowns.documents }"
                        v-if="canViewComponent('Quản lý hồ sơ')"
                    >
                        <a
                            href="#"
                            @click.prevent="toggleDropdown('documents')"
                        >
                            <i class="fa-solid fa-folder-open"></i>
                            <span class="menu-text">Quản lý hồ sơ</span>
                        </a>
                        <div
                            class="sidebar-submenu"
                            :style="{
                                display: activeDropdowns.documents
                                    ? 'block'
                                    : 'none',
                            }"
                        >
                            <ul>
                                <li
                                    v-if="
                                        canViewComponent(
                                            'Danh sách giao nhận hồ sơ'
                                        )
                                    "
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
                                    v-if="
                                        canViewComponent(
                                            'Biên bản nghiệm thu dịch vụ'
                                        )
                                    "
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
                                    v-if="
                                        canViewComponent(
                                            'Phiếu giao nhận hom giống'
                                        )
                                    "
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
                    <li
                        class="sidebar-dropdown"
                        :class="{ active: activeDropdowns.finance }"
                        v-if="canViewComponent('Quản lý tài chính')"
                    >
                        <a href="#" @click.prevent="toggleDropdown('finance')">
                            <i class="fa-solid fa-hand-holding-dollar"></i>
                            <span class="menu-text">Quản lý tài chính</span>
                        </a>
                        <div
                            class="sidebar-submenu"
                            :style="{
                                display: activeDropdowns.finance
                                    ? 'block'
                                    : 'none',
                            }"
                        >
                            <ul>
                                <li
                                    v-if="
                                        canViewComponent(
                                            'Phiếu trình TT dịch vụ'
                                        )
                                    "
                                >
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
                                            >Phiếu trình TT dịch vụ</span
                                        >
                                    </router-link>
                                </li>
                                <li
                                    v-if="
                                        canViewComponent(
                                            'Phiếu trình TT hom giống'
                                        )
                                    "
                                >
                                    <router-link
                                        to="/PhieutrinhthanhtoanHomgiong"
                                        :class="
                                            $route.path ===
                                            '/PhieutrinhthanhtoanHomgiong'
                                                ? 'current-page'
                                                : ''
                                        "
                                        @click="closeSidebar"
                                    >
                                        <span class="menu-text"
                                            >Phiếu trình TT hom giống</span
                                        >
                                    </router-link>
                                </li>
                                <li
                                    v-if="
                                        canViewComponent(
                                            'Phiếu đề nghị TT dịch vụ'
                                        )
                                    "
                                >
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
                                            >Phiếu đề nghị TT dịch vụ</span
                                        >
                                    </router-link>
                                </li>
                                <li
                                    v-if="
                                        canViewComponent(
                                            'Phiếu đề nghị TT Hom giống'
                                        )
                                    "
                                >
                                    <router-link
                                        to="/PhieudenghithanhtoanHomgiong"
                                        :class="
                                            $route.path ===
                                            '/PhieudenghithanhtoanHomgiong'
                                                ? 'current-page'
                                                : ''
                                        "
                                        @click="closeSidebar"
                                    >
                                        <span class="menu-text"
                                            >Phiếu đề nghị TT Hom giống</span
                                        >
                                    </router-link>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>

                <!-- Quản lý công nợ-->
                <ul>
                    <li
                        class="sidebar-dropdown"
                        :class="{ active: activeDropdowns.debt }"
                        v-if="canViewComponent('Quản lý công nợ')"
                    >
                        <a href="#" @click.prevent="toggleDropdown('debt')">
                            <i class="fas fa-file-invoice-dollar"></i>
                            <span class="menu-text">Quản lý công nợ</span>
                        </a>
                        <div
                            class="sidebar-submenu"
                            :style="{
                                display: activeDropdowns.debt
                                    ? 'block'
                                    : 'none',
                            }"
                        >
                            <ul>
                                <li
                                    v-if="
                                        canViewComponent(
                                            'Công nợ dịch vụ phải thu'
                                        )
                                    "
                                >
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
                                            >Công nợ dịch vụ phải thu</span
                                        >
                                    </router-link>
                                </li>
                                <li
                                    v-if="
                                        canViewComponent('Phiếu thu nợ dịch vụ')
                                    "
                                >
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

                <!-- Quản lý hệ thống -->
                <ul>
                    <li
                        class="sidebar-dropdown"
                        :class="{ active: activeDropdowns.system }"
                        v-if="canViewComponent('Quản lý hệ thống')"
                    >
                        <a href="#" @click.prevent="toggleDropdown('system')">
                            <i class="fa-solid fa-screwdriver-wrench"></i>
                            <span class="menu-text">Quản lý hệ thống</span>
                        </a>
                        <div
                            class="sidebar-submenu"
                            :style="{
                                display: activeDropdowns.system
                                    ? 'block'
                                    : 'none',
                            }"
                        >
                            <ul>
                                <li v-if="canViewComponent('Danh sách User')">
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
                                <li
                                    v-if="
                                        canViewComponent('Danh sách Khách Hàng')
                                    "
                                >
                                    <router-link
                                        to="/UserFarmer"
                                        :class="
                                            $route.path === '/UserFarmer'
                                                ? 'current-page'
                                                : ''
                                        "
                                        @click="closeSidebar"
                                    >
                                        <span>Danh sách Khách Hàng</span>
                                    </router-link>
                                </li>
                                <li v-if="canViewComponent('Cấp quyền')">
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
                            </ul>
                        </div>
                    </li>
                </ul>

                <ul>
                    <li
                        class="sidebar-dropdown"
                        :class="{ active: activeDropdowns.calendar }"
                        v-if="canViewComponent('Calendar')"
                    >
                        <a href="#" @click.prevent="toggleDropdown('calendar')">
                            <i class="fa-solid fa-calendar"></i>
                            <span class="menu-text">Calendar</span>
                        </a>
                        <div
                            class="sidebar-submenu"
                            :style="{
                                display: activeDropdowns.calendar
                                    ? 'block'
                                    : 'none',
                            }"
                        >
                            <ul>
                                <li v-if="canViewComponent('Lịch thanh toán')">
                                    <router-link
                                        to="/LichThanhToan"
                                        :class="
                                            $route.path === '/LichThanhToan'
                                                ? 'current-page'
                                                : ''
                                        "
                                        @click="closeSidebar"
                                    >
                                        <span>Lịch thanh toán</span>
                                    </router-link>
                                </li>
                                <li
                                    v-if="
                                        canViewComponent('Lịch giao nhận hồ sơ')
                                    "
                                >
                                    <router-link
                                        to="/LichGiaoNhanHoso"
                                        :class="
                                            $route.path === '/LichGiaoNhanHoso'
                                                ? 'current-page'
                                                : ''
                                        "
                                        @click="closeSidebar"
                                    >
                                        <span>Lịch giao nhận hồ sơ </span>
                                    </router-link>
                                </li>

                                <li
                                    v-if="
                                        canViewComponent('Kế hoạch nộp hồ sơ')
                                    "
                                >
                                    <router-link
                                        to="/PlanNopHoso"
                                        :class="
                                            $route.path === '/PlanNopHoso'
                                                ? 'current-page'
                                                : ''
                                        "
                                        @click="closeSidebar"
                                    >
                                        <span>Kế hoạch nộp hồ sơ</span>
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
import { usePermissions } from "../Composables/usePermissions";
export default {
    setup() {
        const store = useStore();
        const { hasPermission, canViewComponent } = usePermissions();
        return {
            store,
            hasPermission,
            canViewComponent,
        };
    },
    data() {
        return {
            url: window.location.origin,
            userPermissions: [],
            userComponents: [],
            isMobile: false,
            // เพิ่ม state สำหรับควบคุม dropdown
            activeDropdowns: {
                documents: false,
                finance: false,
                debt: false,
                system: false,
                calendar: false, // เพิ่ม calendar dropdown
            },
        };
    },
    watch: {
        // ตรวจสอบเมื่อ route เปลี่ยน เพื่อเปิด dropdown ที่เกี่ยวข้อง
        "$route.path": {
            handler(newPath) {
                this.setActiveDropdownBasedOnRoute(newPath);
            },
            immediate: true, // เพิ่ม immediate เพื่อให้ทำงานทันทีเมื่อ component โหลด
        },
        // ตรวจสอบการเปลี่ยนแปลงใน store (เช่น หลัง login)
        "store.token"(newToken) {
            if (newToken) {
                // หลัง login เรียกใช้ function เพื่อ reset dropdown
                this.initializeDropdowns();
            }
        },
    },
    methods: {
        // ฟังก์ชันสำหรับ toggle dropdown - ปรับปรุงให้ทำงานได้ครั้งแรก
        toggleDropdown(dropdownName) {
            // Toggle dropdown ที่เลือกโดยตรง
            this.activeDropdowns[dropdownName] =
                !this.activeDropdowns[dropdownName];

            // Force reactivity update
            this.$forceUpdate();
        },

        // ฟังก์ชันเพื่อกำหนด dropdown ที่ active ตาม route - ปรับปรุงให้ครอบคลุมมากขึ้น
        setActiveDropdownBasedOnRoute(path) {
            // Reset ทุก dropdown ก่อน
            Object.keys(this.activeDropdowns).forEach((key) => {
                this.activeDropdowns[key] = false;
            });

            // เปิด dropdown ตาม route ปัจจุบัน
            if (
                path.includes("/Danhsachhoso") ||
                path.includes("/Bienbannghiemthudichvu") ||
                path.includes("/Phieugiaonhanhomgiong") ||
                path.includes("/Details_NghiemthuDV") ||
                path.includes("/Details_Phieugiaonhanhomgiong")
            ) {
                this.activeDropdowns.documents = true;
            } else if (
                path.includes("/Phieutrinhthanhtoan") ||
                path.includes("/PhieutrinhthanhtoanHomgiong") ||
                path.includes("/Phieudenghithanhtoandichvu") ||
                path.includes("/PhieudenghithanhtoanHomgiong") ||
                path.includes("/Details_Phieutrinhthanhtoandv") ||
                path.includes("/Details_PhieutrinhthanhtoanHomgiong") ||
                path.includes("/Details_Phieudenghithanhtoandichvu") ||
                path.includes("/Details_PhieudenghithanhtoanHomgiong")
            ) {
                this.activeDropdowns.finance = true;
            } else if (
                path.includes("/CongnoDichvuKhautru") ||
                path.includes("/Phieuthunodichvu") ||
                path.includes("/Details_CongnoDichvuKhautru")
            ) {
                this.activeDropdowns.debt = true;
            } else if (
                path.includes("/user") ||
                path.includes("/UserFarmer") ||
                path.includes("/permission") ||
                path.includes("/role") ||
                path.includes("/Profile") ||
                path.includes("/Myprofile") ||
                path.includes("/Myprofilefarmer") ||
                path.includes("/AddUser") ||
                path.includes("/EditUser")
            ) {
                this.activeDropdowns.system = true;
            } else if (
                path.includes("/LichThanhToan") ||
                path.includes("/LichGiaoNhanHoso") ||
                path.includes("/PlanNopHoso")
            ) {
                this.activeDropdowns.calendar = true;
            }
        },

        // ฟังก์ชันเพื่อ initialize dropdown หลัง login - ปรับปรุง
        initializeDropdowns() {
            this.$nextTick(() => {
                this.setActiveDropdownBasedOnRoute(this.$route.path);
            });
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
        // ตรวจจับขนาดหน้าจอเมื่อโหลดครั้งแรก
        this.checkScreenSize();
        // ตรวจจับการเปลี่ยนแปลงขนาดหน้าจอ
        window.addEventListener("resize", this.checkScreenSize);

        // Initialize dropdown ตาม route ปัจจุบัน - ปรับปรุงให้ทำงานได้ดีขึ้น
        this.$nextTick(() => {
            this.setActiveDropdownBasedOnRoute(this.$route.path);
        });
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

/* เพิ่ม CSS สำหรับ transition ของ dropdown */
.sidebar-submenu {
    transition: all 0.3s ease;
    overflow: hidden;
}

.sidebar-dropdown.active > a::after {
    transform: rotate(90deg);
}

.sidebar-dropdown > a::after {
    content: "\f107"; /* FontAwesome chevron down */
    font-family: "Font Awesome 5 Free";
    font-weight: 900;
    float: right;
    transition: transform 0.3s ease;
}
</style>
