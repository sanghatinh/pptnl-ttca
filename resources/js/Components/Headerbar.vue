<template lang="">
    <!-- Header start -->
    <header class="header">
        <div class="toggle-btns">
            <a id="toggle-sidebar" href="#" @click.prevent="toggleSidebar">
                <i class="icon-list"></i>
            </a>
            <a id="pin-sidebar" href="#">
                <i class="icon-list"></i>
            </a>
        </div>
        <div class="header-items">
            <!-- Header actions start -->
            <ul class="header-actions">
                <li class="dropdown">
                    <a
                        href="#"
                        id="userSettings"
                        class="user-settings"
                        data-bs-toggle="dropdown"
                        aria-haspopup="true"
                        aria-expanded="false"
                    >
                        <span class="user-name">{{ displayName }}</span>
                        <span class="avatar">
                            <img
                                :src="userAvatar"
                                alt="avatar"
                                @error="onImageError"
                            />
                            <span class="status busy"></span>
                        </span>
                    </a>
                    <div
                        class="dropdown-menu dropdown-menu-right"
                        aria-labelledby="userSettings"
                    >
                        <div class="header-profile-actions">
                            <div class="header-user-profile">
                                <div class="header-user">
                                    <img
                                        :src="userAvatar"
                                        alt="avatar"
                                        @error="onImageError"
                                    />
                                </div>
                                <h6>{{ displayName }}</h6>
                                <p>{{ roleName }}</p>
                            </div>
                            <!-- แสดงเมนูสำหรับ Employee เท่านั้น -->
                            <a
                                v-if="userType === 'employee'"
                                href="#"
                                @click.prevent="Gotopath('/Myprofile')"
                                style="cursor: pointer"
                            >
                                <i class="icon-user"></i>Tài Khoản của tôi
                            </a>
                            <!-- แสดงเมนูสำหรับ Farmer เท่านั้น -->
                            <a
                                v-if="userType === 'farmer'"
                                href="#"
                                @click.prevent="Gotopath('/Myprofilefarmer')"
                                style="cursor: pointer"
                            >
                                <i class="icon-users"></i>Tài Khoản Nông dân
                            </a>
                            <a href="account-settings.html">
                                <i class="icon-settings1"></i> Account Settings
                            </a>
                            <a href="#" @click="Logout()">
                                <i class="icon-log-out1"></i> Log Out
                            </a>
                        </div>
                    </div>
                </li>
            </ul>
            <!-- Header actions end -->
        </div>
    </header>
    <!-- Header end -->

    <!-- Page header start -->
    <div class="page-header">
        <!-- <ol class="breadcrumb">
            <li class="breadcrumb-item">Home</li>
            <li class="breadcrumb-item active">Admin Dashboard</li>
        </ol> -->
    </div>
    <!-- Page header end -->
</template>
<script>
import { useStore } from "../Store/Auth";
import axios from "axios";

export default {
    data() {
        return {
            url: window.location.href,
            userType: "employee", // default
            displayName: "",
            roleName: "",
            userAvatar:
                "https://img.icons8.com/?size=100&id=z-JBA_KtSkxG&format=png&color=000000", // default avatar
            defaultAvatar:
                "https://img.icons8.com/?size=100&id=z-JBA_KtSkxG&format=png&color=000000",
        };
    },
    async created() {
        await this.initializeUserData();
    },
    setup() {
        const store = useStore();
        return { store };
    },
    methods: {
        Gotopath(path) {
            this.$router.push(path);
        },
        async initializeUserData() {
            try {
                // ตรวจสอบประเภทผู้ใช้จาก localStorage
                const user = localStorage.getItem("web_user");
                if (!user) {
                    console.error("No user data found in localStorage");
                    return;
                }

                const parsedUser = JSON.parse(user);
                this.userType = parsedUser.user_type || "employee";

                // โหลดข้อมูลตามประเภทผู้ใช้
                if (this.userType === "farmer") {
                    await this.loadFarmerProfile();
                } else {
                    await this.loadEmployeeProfile();
                }
            } catch (error) {
                console.error("Error initializing user data:", error);
                this.setDefaultUserData();
            }
        },

        async loadFarmerProfile() {
            try {
                const response = await axios.get("/api/farmer/my-profile", {
                    headers: {
                        Authorization: "Bearer " + this.store.getToken,
                    },
                });

                if (response.data.success) {
                    const farmerData = response.data.data;

                    // ตั้งค่าชื่อแสดง - ให้ความสำคัญกับ khach_hang_ca_nhan หรือ khach_hang_doanh_nghiep
                    this.displayName =
                        farmerData.khach_hang_ca_nhan ||
                        farmerData.khach_hang_doanh_nghiep ||
                        farmerData.supplier_number ||
                        "Farmer User";

                    this.roleName = farmerData.role_name || "Nông dân";

                    // ตั้งค่ารูปภาพ
                    if (farmerData.image_url) {
                        this.userAvatar = farmerData.image_url;
                    }
                } else {
                    throw new Error("Failed to load farmer profile");
                }
            } catch (error) {
                console.error("Error loading farmer profile:", error);
                this.setFarmerDefaultData();
            }
        },

        async loadEmployeeProfile() {
            try {
                const response = await axios.get("/api/my-profile", {
                    headers: {
                        Authorization: "Bearer " + this.store.getToken,
                    },
                });

                if (response.data.success) {
                    const employeeData = response.data.data;

                    this.displayName =
                        employeeData.full_name ||
                        employeeData.username ||
                        "Employee User";
                    this.roleName = employeeData.role_name || "Unknown Role";

                    // ตั้งค่ารูปภาพ
                    if (employeeData.image_url) {
                        this.userAvatar = employeeData.image_url;
                    }
                } else {
                    throw new Error("Failed to load employee profile");
                }
            } catch (error) {
                console.error("Error loading employee profile:", error);
                this.setEmployeeDefaultData();
            }
        },

        setFarmerDefaultData() {
            try {
                const user = localStorage.getItem("web_user");
                if (user) {
                    const parsedUser = JSON.parse(user);
                    this.displayName = parsedUser.name || "Farmer User";
                } else {
                    this.displayName = "Farmer User";
                }
                this.roleName = "Nông dân";
            } catch (error) {
                this.displayName = "Farmer User";
                this.roleName = "Nông dân";
            }
        },

        setEmployeeDefaultData() {
            try {
                const user = localStorage.getItem("web_user");
                if (user) {
                    const parsedUser = JSON.parse(user);
                    this.displayName =
                        parsedUser.full_name ||
                        parsedUser.username ||
                        "Employee User";

                    // ดึงข้อมูล role แบบเดิม
                    this.loadRoleFromAPI(parsedUser.role_id);
                } else {
                    this.displayName = "Employee User";
                    this.roleName = "Unknown Role";
                }
            } catch (error) {
                this.displayName = "Employee User";
                this.roleName = "Unknown Role";
            }
        },

        setDefaultUserData() {
            this.displayName = "User";
            this.roleName = "Unknown";
            this.userAvatar = this.defaultAvatar;
        },

        async loadRoleFromAPI(roleId) {
            try {
                const response = await axios.get("/api/roles", {
                    headers: {
                        Authorization: "Bearer " + this.store.getToken,
                    },
                });

                const roles = response.data;
                const matchedRole = roles.find((role) => role.id == roleId);
                this.roleName = matchedRole ? matchedRole.name : "Unknown Role";
            } catch (error) {
                console.error("Error fetching roles:", error);
                this.roleName = "Unknown Role";
            }
        },

        onImageError() {
            // ถ้ารูปภาพโหลดไม่ได้ ให้ใช้รูปเริ่มต้น
            this.userAvatar = this.defaultAvatar;
        },

        async Logout() {
            try {
                // Clear localStorage และ store ก่อนเรียก API
                this.clearAllLocalStorage();
                this.store.logout();

                // เพิ่มการ emit event เพื่อแจ้ง sidebar ให้ reset
                this.$eventBus?.emit("user-logged-out");

                // เรียก API logout (ไม่จำเป็นต้องรอผลลัพธ์)
                axios
                    .get("api/logout", {
                        headers: {
                            Authorization: "Bearer " + this.store.getToken,
                        },
                    })
                    .catch((err) => {
                        // แม้ API logout จะ error ก็ไม่เป็นไร เพราะเราได้ clear data แล้ว
                        console.log("Logout API error (ignored):", err);
                    });

                // Force navigate to login โดยไม่รอ API response
                await this.$router.replace("/login");

                // Force reload page เพื่อให้แน่ใจว่า state ถูก reset
                window.location.reload();
            } catch (error) {
                console.error("Logout error:", error);

                // กรณีที่เกิดข้อผิดพลาดใดๆ ให้ force logout
                this.forceLogout();
            }
        },

        forceLogout() {
            // Force logout method สำหรับกรณีฉุกเฉิน
            this.clearAllLocalStorage();
            this.store.logout();
            this.$eventBus?.emit("user-logged-out");

            // ใช้ window.location แทน router เพื่อ force navigation
            window.location.href = "/login";
        },

        clearAllLocalStorage() {
            // วิธีการลบ localStorage ทั้งหมด (ใช้ด้วยความระมัดระวัง)
            localStorage.clear();

            // หรือใช้วิธีการที่ปลอดภัยกว่า - ลบเฉพาะที่เกี่ยวข้องกับโปรเจค
            const projectKeys = [
                "web_token",
                "web_user",
                "user_type",
                "supplier_id",
                "bienban_filter_state",
                "payment_request_filter_state",
                "congno_filter_state",
                "phieuDichvu_filterState", // เพิ่ม filter state ของ Details
            ];

            projectKeys.forEach((key) => localStorage.removeItem(key));

            // ลบ keys ที่มี pattern เฉพาะ
            Object.keys(localStorage).forEach((key) => {
                if (
                    key.startsWith("import_congno_dichvu_khautru_") ||
                    key.includes("filter_state") ||
                    key.includes("filterState")
                ) {
                    localStorage.removeItem(key);
                }
            });
        },
    },
};
</script>

<style>
.header-actions > li:last-child .dropdown-menu {
    right: 0;
    left: auto !important;
}

.header-actions > li:last-child .dropdown-menu:before {
    right: 15px;
    left: auto !important;
}

/* Avatar styling */
.avatar img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #fff;
}

.header-user img {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #fff;
}

/* เพิ่ม CSS นี้เพื่อยกเลิกการซ่อนเมนู user-settings บนมือถือ */
@media (max-width: 576px) {
    .header-actions > li {
        display: block !important;
    }

    .header-actions > li > a.user-settings span.user-name {
        display: none;
    }

    /* กำหนดความกว้างสำหรับ dropdown ในหน้าจอเล็ก */
    .header-actions > li .dropdown-menu {
        width: 250px;
        max-width: 90vw;
    }
}

/* Loading state */
.user-name {
    transition: opacity 0.3s ease;
}

.user-name:empty {
    opacity: 0.5;
}

/* Enhanced styling for dropdown */
.header-profile-actions a {
    display: flex;
    align-items: center;
    padding: 8px 16px;
    text-decoration: none;
    color: #333;
    transition: background-color 0.2s ease;
    cursor: pointer; /* เพิ่มบรรทัดนี้ */
}

.header-profile-actions a:hover {
    background-color: #22c55e;
}

.header-profile-actions a i {
    margin-right: 8px;
    width: 16px;
    text-align: center;
}
/* Center align display name */
.header-user-profile h6 {
    text-align: center;
    margin: 10px 0 5px 0;
}

.header-user-profile p {
    text-align: center;
    margin: 0 0 15px 0;
}
</style>
