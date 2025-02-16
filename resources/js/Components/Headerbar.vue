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
            <div class="custom-search">
                <input
                    type="text"
                    class="search-query"
                    placeholder="Search here ..."
                />
                <i class="icon-search1"></i>
            </div>
            <!-- Custom search end -->

            <!-- Header actions start -->
            <ul class="header-actions">
                <li class="dropdown">
                    <a
                        href="#"
                        id="notifications"
                        data-toggle="dropdown"
                        aria-haspopup="true"
                    >
                        <i class="icon-box"></i>
                    </a>
                    <div
                        class="dropdown-menu dropdown-menu-right lrg"
                        aria-labelledby="notifications"
                    >
                        <div class="dropdown-menu-header">Tasks (05)</div>
                        <ul class="header-tasks">
                            <li>
                                <p>#20 - Dashboard UI<span>90%</span></p>
                                <div class="progress">
                                    <div
                                        class="progress-bar bg-primary"
                                        role="progressbar"
                                        aria-valuenow="90"
                                        aria-valuemin="0"
                                        aria-valuemax="100"
                                        style="width: 90%"
                                    >
                                        <span class="sr-only"
                                            >90% Complete (success)</span
                                        >
                                    </div>
                                </div>
                            </li>
                            <li>
                                <p>#35 - Alignment Fix<span>60%</span></p>
                                <div class="progress">
                                    <div
                                        class="progress-bar bg-primary"
                                        role="progressbar"
                                        aria-valuenow="60"
                                        aria-valuemin="0"
                                        aria-valuemax="100"
                                        style="width: 60%"
                                    >
                                        <span class="sr-only"
                                            >60% Complete (success)</span
                                        >
                                    </div>
                                </div>
                            </li>
                            <li>
                                <p>#50 - Broken Button<span>40%</span></p>
                                <div class="progress">
                                    <div
                                        class="progress-bar bg-secondary"
                                        role="progressbar"
                                        aria-valuenow="40"
                                        aria-valuemin="0"
                                        aria-valuemax="100"
                                        style="width: 40%"
                                    >
                                        <span class="sr-only"
                                            >40% Complete (success)</span
                                        >
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="dropdown">
                    <a
                        href="#"
                        id="notifications"
                        data-toggle="dropdown"
                        aria-haspopup="true"
                    >
                        <i class="icon-bell"></i>
                        <span class="count-label">8</span>
                    </a>
                    <div
                        class="dropdown-menu dropdown-menu-right lrg"
                        aria-labelledby="notifications"
                    >
                        <div class="dropdown-menu-header">
                            Notifications (40)
                        </div>
                        <ul class="header-notifications">
                            <li>
                                <a href="#">
                                    <div class="user-img away">
                                        <img
                                            src="/public/img/bg2.jpeg"
                                            alt="User"
                                        />
                                    </div>
                                    <div class="details">
                                        <div class="user-title">Abbott</div>
                                        <div class="noti-details">
                                            Membership has been ended.
                                        </div>
                                        <div class="noti-date">
                                            Oct 20, 07:30 pm
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <div class="user-img busy">
                                        <img
                                            src="/public/img/bg4.jpeg"
                                            alt="User"
                                        />
                                    </div>
                                    <div class="details">
                                        <div class="user-title">Braxten</div>
                                        <div class="noti-details">
                                            Approved new design.
                                        </div>
                                        <div class="noti-date">
                                            Oct 10, 12:00 am
                                        </div>
                                    </div>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="dropdown">
                    <a
                        href="#"
                        id="userSettings"
                        class="user-settings"
                        data-toggle="dropdown"
                        aria-haspopup="true"
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
        <ol class="breadcrumb">
            <li class="breadcrumb-item">Home</li>
            <li class="breadcrumb-item active">Admin Dashboard</li>
        </ol>
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
                        localStorage.removeItem("web_token");
                        localStorage.removeItem("web_user");
                        this.store.logout();
                        this.$router.push("/login");
                    }
                })
                .catch((err) => {
                    console.log(err);
                    if (err.response && err.response.status === 401) {
                        localStorage.removeItem("web_token");
                        localStorage.removeItem("web_user");
                        this.store.logout();
                        this.$router.push("/login");
                    }
                });
        },
    },
};
</script>
<style lang=""></style>
