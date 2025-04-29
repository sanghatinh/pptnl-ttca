import { createRouter, createWebHistory } from "vue-router";
import { useStore } from "../Store/Auth";
import axios from "axios"; // เพิ่มบรรทัดนี้ถ้ายังไม่มี
import Home from "../Pages/Dashboard.vue";
import Taohoso from "../Pages/Quanlyhoso/TaoGiaoNhanhoso.vue";
import Danhsachhoso from "../Pages/Quanlyhoso/DanhsachHoso.vue";
import EditGiaoNhanhoso from "../Pages/Quanlyhoso/EditGiaoNhanhoso.vue";
import BienBanNTDV from "../Pages/Quanlyhoso/BienBanNTDV.vue";
import Details_NghiemthuDV from "../Pages/Quanlyhoso/Details_NghiemthuDV.vue";
import Details_Phieugiaonhanhomgiong from "../Pages/Quanlyhoso/Details_Phieugiaonhanhomgiong.vue";
import Phieugiaonhanhomgiong from "../Pages/Quanlyhoso/Phieugiaonhanhomgiong.vue";
import Phieutrinhthanhtoan from "../Pages/QuanlyTaichinh/Phieutrinhthanhtoan.vue";
import Details_Phieutrinhthanhtoan from "../Pages/QuanlyTaichinh/Details_Phieutrinhthanhtoan.vue";
import login from "../Pages/Login.vue";
import Nopage from "../Pages/404.vue";
import Register from "../Pages/Register.vue";

import ListUser from "../Pages/Admin/User.vue";
import Permission from "../Pages/Admin/Permission.vue";
import Role from "../Pages/Admin/Role.vue";
import Profile from "../Pages/Admin/UserProfile.vue";
import Unauthorized from "../Pages/Unauthorized.vue";

const authMiddleware = (to, from, next) => {
    const token = localStorage.getItem("web_token");
    const user = localStorage.getItem("web_user");
    const store = useStore();

    if (token) {
        store.setToken(token);
        store.setUser(user);
        next();
    } else {
        next({
            path: "/login",
            replace: true,
        });
    }
};

const routes = [
    {
        name: "Home",
        path: "/",
        component: Home,
        meta: {
            middleware: [authMiddleware],
        },
    },
    {
        name: "Taohoso",
        path: "/Taonewhoso",
        component: Taohoso,
        meta: {
            middleware: [authMiddleware],
        },
    },
    {
        path: "/Danhsachhoso/:id",
        name: "EditGiaoNhanhoso",
        component: EditGiaoNhanhoso,
        meta: { requiresAuth: true },
        beforeEnter: async (to, from, next) => {
            // ดึงค่า id จาก route parameters
            const id = to.params.id;

            // ดึง token จาก localStorage
            const token = localStorage.getItem("web_token");
            const store = useStore();

            if (!token) {
                next("/login");
                return;
            }

            try {
                const response = await axios.get(
                    `/api/document-deliveries/${id}/check-access`,
                    {
                        headers: {
                            Authorization: `Bearer ${token}`,
                        },
                    }
                );

                if (response.data.hasAccess) {
                    next();
                } else {
                    next("/unauthorized");
                }
            } catch (error) {
                console.error("Error checking access:", error);

                if (error.response?.status === 401) {
                    localStorage.removeItem("web_token");
                    localStorage.removeItem("web_user");
                    store.logout();
                    next("/login");
                } else {
                    next("/unauthorized");
                }
            }
        },
    },
    {
        name: "Danhsachhoso",
        path: "/Danhsachhoso",
        component: Danhsachhoso,
        meta: {
            middleware: [authMiddleware],
        },
    },
    {
        name: "BienBanNTDV",
        path: "/Bienbannghiemthudichvu",
        component: BienBanNTDV,
        meta: {
            middleware: [authMiddleware],
        },
    },
    {
        name: "Details_NghiemthuDV",
        path: "/Details_NghiemthuDV/:id",
        component: Details_NghiemthuDV,
        meta: { requiresAuth: true },
        beforeEnter: async (to, from, next) => {
            // ดึงค่า id จาก route parameters
            const id = to.params.id;

            // ดึง token จาก localStorage
            const token = localStorage.getItem("web_token");
            const store = useStore();

            if (!token) {
                next("/login");
                return;
            }

            try {
                // ตรวจสอบสิทธิ์การเข้าถึง
                const response = await axios.get(
                    `/api/bien-ban-nghiemthu/${id}/check-access`,
                    {
                        headers: {
                            Authorization: `Bearer ${token}`,
                        },
                    }
                );

                if (response.data.hasAccess) {
                    next(); // มีสิทธิ์เข้าถึง - อนุญาตให้เข้าถึงหน้านี้
                } else {
                    next("/unauthorized"); // ไม่มีสิทธิ์เข้าถึง - นำทางไปยังหน้า Unauthorized
                }
            } catch (error) {
                console.error("Error checking access:", error);

                if (error.response?.status === 401) {
                    // Token หมดอายุหรือไม่ถูกต้อง - logout และนำทางไปยังหน้า login
                    localStorage.removeItem("web_token");
                    localStorage.removeItem("web_user");
                    store.logout();
                    next("/login");
                } else {
                    // ข้อผิดพลาดอื่นๆ - นำทางไปยังหน้า Unauthorized
                    next("/unauthorized");
                }
            }
        },
    },
    {
        name: "Phieugiaonhanhomgiong",
        path: "/Phieugiaonhanhomgiong",
        component: Phieugiaonhanhomgiong,
        meta: {
            middleware: [authMiddleware],
        },
    },
    {
        name: "Details_Phieugiaonhanhomgiong",
        path: "/Details_Phieugiaonhanhomgiong/:id",
        component: Details_Phieugiaonhanhomgiong,
        meta: { requiresAuth: true },
        beforeEnter: async (to, from, next) => {
            // ดึงค่า id จาก route parameters
            const id = to.params.id;

            // ดึง token จาก localStorage
            const token = localStorage.getItem("web_token");
            const store = useStore();

            if (!token) {
                next("/login");
                return;
            }

            try {
                // ตรวจสอบสิทธิ์การเข้าถึง
                const response = await axios.get(
                    `/api/bienban-nghiemthu-homgiong/${id}/check-access`,
                    {
                        headers: {
                            Authorization: `Bearer ${token}`,
                        },
                    }
                );

                if (response.data.hasAccess) {
                    next(); // มีสิทธิ์เข้าถึง - อนุญาตให้เข้าถึงหน้านี้
                } else {
                    next("/unauthorized"); // ไม่มีสิทธิ์เข้าถึง - นำทางไปยังหน้า Unauthorized
                }
            } catch (error) {
                console.error("Error checking access:", error);

                if (error.response?.status === 401) {
                    // Token หมดอายุหรือไม่ถูกต้อง - logout และนำทางไปยังหน้า login
                    localStorage.removeItem("web_token");
                    localStorage.removeItem("web_user");
                    store.logout();
                    next("/login");
                } else {
                    // ข้อผิดพลาดอื่นๆ - นำทางไปยังหน้า Unauthorized
                    next("/unauthorized");
                }
            }
        },
    },
    {
        name: "Phieutrinhthanhtoan",
        path: "/Phieutrinhthanhtoan",
        component: Phieutrinhthanhtoan,
        meta: {
            middleware: [authMiddleware],
        },
    },
    {
        name: "Details_Phieutrinhthanhtoan",
        path: "/Details_Phieutrinhthanhtoan/:id",
        component: Details_Phieutrinhthanhtoan,
        meta: {
            middleware: [authMiddleware],
        },
    },
    {
        name: "login",
        path: "/login",
        component: login,
    },
    {
        name: "Register",
        path: "/register",
        component: Register,
    },
    {
        name: "404",
        path: "/:pathMatch(.*)*",
        component: Nopage,
        meta: {
            middleware: [authMiddleware],
        },
    },
    {
        name: "User",
        path: "/User",
        component: ListUser,
        meta: {
            middleware: [authMiddleware],
        },
    },
    {
        name: "Permission",
        path: "/Permission",
        component: Permission,
        meta: {
            middleware: [authMiddleware],
        },
    },
    {
        name: "Role",
        path: "/Role",
        component: Role,
        meta: {
            middleware: [authMiddleware],
        },
    },
    {
        name: "Profile",
        path: "/Profile",
        component: Profile,
        meta: {
            middleware: [authMiddleware],
        },
    },
    {
        path: "/unauthorized",
        name: "Unauthorized",
        component: Unauthorized,
        meta: { requiresAuth: true },
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes: routes,
    scrollBehavior() {
        return { top: 0 };
    },
});

router.beforeEach((to, from, next) => {
    const token = localStorage.getItem("web_token");
    if (to.meta.middleware) {
        to.meta.middleware.forEach((middleware) => middleware(to, from, next));
    } else {
        if (to.path == "/login" || to.path == "/") {
            if (token) {
                next({
                    name: "Home",
                    replace: true,
                });
            } else {
                next();
            }
        } else {
            next();
        }
    }
});

//ກວດສອບສິດໃນການແກ້ໄຂເອກະສານມອບສົ່ງວ່າມີສິດໃນການແກ້ໄຂເອກະສານບໍ່

export default router;
