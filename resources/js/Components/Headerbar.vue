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
            <!-- Custom search start -->
            <!-- <div class="custom-search">
                <input
                    type="text"
                    class="search-query"
                    placeholder="Search here ..."
                />
                <i class="icon-search1"></i>
            </div> -->
            <!-- Custom search end -->

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
                        <span class="user-name">{{ full_name }}</span>
                        <span class="avatar">
                            <!-- <img :src="`${url}/img/user24.png`" alt="avatar"> -->
                            <img
                                src="https://img.icons8.com/?size=100&id=z-JBA_KtSkxG&format=png&color=000000"
                                alt="avatar"
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
                                        src="https://img.icons8.com/?size=100&id=z-JBA_KtSkxG&format=png&color=000000"
                                        alt="avatar"
                                    />
                                </div>
                                <h5>{{ full_name }}</h5>
                                <p>{{ role }}</p>
                            </div>
                            <a href="user-profile.html"
                                ><i class="icon-user1"></i> My Profile</a
                            >
                            <a href="account-settings.html"
                                ><i class="icon-settings1"></i> Account
                                Settings</a
                            >
                            <a href="#" @click="Logout()"
                                ><i class="icon-log-out1"></i> Sign Out</a
                            >
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
            full_name: "",
            role: "",
        };
    },
    created() {
        const user = localStorage.getItem("web_user");
        if (user) {
            try {
                const parsedUser = JSON.parse(user);
                this.full_name = parsedUser.full_name;
                const roleId = parsedUser.role_id; // ได้รับ role_id จาก login

                // เรียก API เพื่อนำข้อมูล Role ทั้งหมดมา แล้ว map role ที่ตรงกับ role_id
                axios
                    .get("/api/roles", {
                        headers: {
                            Authorization: "Bearer " + this.store.getToken,
                        },
                    })
                    .then((response) => {
                        const roles = response.data;
                        const matchedRole = roles.find(
                            (roleItem) => roleItem.id == roleId
                        );
                        if (matchedRole) {
                            this.role = matchedRole.name;
                        } else {
                            this.role = "Unknown";
                        }
                    })
                    .catch((err) => {
                        console.error("Error fetching roles:", err);
                        this.role = "Unknown";
                    });
            } catch (e) {
                console.error("Error parsing user data", e);
            }
        }
    },
    setup() {
        const store = useStore();
        return { store };
    },
    methods: {
        Logout() {
            axios
                .get("api/logout", {
                    headers: { Authorization: "Bearer " + this.store.getToken },
                })
                .then((res) => {
                    if (res.data.success) {
                        this.clearAllLocalStorage();
                        this.store.logout();
                        this.$router.push("/login");
                    }
                })
                .catch((err) => {
                    console.log(err);
                    if (err.response && err.response.status === 401) {
                        this.clearAllLocalStorage();
                        this.store.logout();
                        this.$router.push("/login");
                    }
                });
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
            ];

            projectKeys.forEach((key) => localStorage.removeItem(key));

            // ลบ keys ที่มี pattern เฉพาะ
            Object.keys(localStorage).forEach((key) => {
                if (key.startsWith("import_congno_dichvu_khautru_")) {
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
</style>
