import { createRouter, createWebHistory } from "vue-router";
import { useStore } from "../Store/Auth";
import axios from "axios"; // เพิ่มบรรทัดนี้ถ้ายังไม่มี
import AddUser from "../Pages/Admin/AddUser.vue";
import Home from "../Pages/Dashboard.vue";
import Taohoso from "../Pages/Quanlyhoso/TaoGiaoNhanhoso.vue";
import Danhsachhoso from "../Pages/Quanlyhoso/DanhsachHoso.vue";
import EditGiaoNhanhoso from "../Pages/Quanlyhoso/EditGiaoNhanhoso.vue";
import BienBanNTDV from "../Pages/Quanlyhoso/BienBanNTDV.vue";
import Details_NghiemthuDV from "../Pages/Quanlyhoso/Details_NghiemthuDV.vue";
import Details_Phieugiaonhanhomgiong from "../Pages/Quanlyhoso/Details_Phieugiaonhanhomgiong.vue";
import Phieugiaonhanhomgiong from "../Pages/Quanlyhoso/Phieugiaonhanhomgiong.vue";
import Phieutrinhthanhtoan from "../Pages/QuanlyTaichinh/Phieutrinhthanhtoan.vue";
import Phieudenghithanhtoandichvu from "../Pages/QuanlyTaichinh/Phieudenghithanhtoan_dichvu.vue";
import Details_PhieuDNTTDV from "../Pages/QuanlyTaichinh/Details_Phieudenghithanhtoandichvu.vue";
import Phieuthunodichvu from "../Pages/QuanlyTaichinh/Phieuthunodichvu.vue";
import Details_Phieutrinhthanhtoan from "../Pages/QuanlyTaichinh/Details_Phieutrinhthanhtoan.vue";
import CongnoDichvuKhautru from "../Pages/QuanlyCongno/CongnoDichvuKhautru.vue";
import Details_CongnoDichvuKhautru from "../Pages/QuanlyCongno/Details_CongnoDichvuKhautru.vue";
import login from "../Pages/Login.vue";
import Nopage from "../Pages/404.vue";
import Register from "../Pages/Register.vue";

import ListUser from "../Pages/Admin/User.vue";
import Permission from "../Pages/Admin/Permission.vue";
import Role from "../Pages/Admin/Role.vue";
import Profile from "../Pages/Admin/UserProfile.vue";
import Unauthorized from "../Pages/Unauthorized.vue";
import PhieutrinhthanhtoanHomgiong from "../Pages/QuanlyTaichinh/PhieutrinhthanhtoanHomgiong.vue";

const authMiddleware = async (to, from, next) => {
    const store = useStore();

    // Check if user is authenticated
    if (!store.isAuthenticated) {
        next("/login");
        return;
    }

    // รอให้ permissions โหลดเสร็จก่อน (สำหรับกรณีรีเฟรช)
    if (!store.permissionsLoaded) {
        try {
            await store.waitForPermissions();
        } catch (error) {
            console.error("Failed to load permissions:", error);
            // ถ้าโหลด permissions ไม่สำเร็จ ให้ลองโหลดใหม่
            try {
                await store.loadPermissionsAndComponents();
            } catch (retryError) {
                console.error("Retry loading permissions failed:", retryError);
                next("/login");
                return;
            }
        }
    }

    // เพิ่มการตรวจสอบ component permissions
    if (to.meta.requiresComponent) {
        // ตรวจสอบว่าผู้ใช้มีสิทธิ์เข้าถึง component หรือไม่
        if (!store.canViewComponent(to.meta.componentName)) {
            next("/unauthorized");
            return;
        }
    }

    next();
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
        name: "UserFarmer",
        path: "/UserFarmer",
        component: () => import("../Pages/Farmer/UserFarmer.vue"),
        meta: {
            middleware: [authMiddleware],
        },
    },
    {
        name: "UserFarmerDetails",
        path: "/UserFarmerDetails/:id",
        component: () => import("../Pages/Farmer/EditUserFarmer.vue"),
        meta: {
            middleware: [authMiddleware],
        },
    },
    {
        name: "Myprofilefarmer",
        path: "/Myprofilefarmer",
        component: () => import("../Pages/Farmer/MyProfileUserFarmer.vue"),
        meta: {
            middleware: [authMiddleware],
        },
    },
    {
        name: "Myprofile",
        path: "/Myprofile",
        component: () => import("../Pages/Admin/MyProfile.vue"),
        meta: {
            middleware: [authMiddleware],
        },
    },
    {
        name: "AddUser",
        path: "/AddUser",
        component: AddUser,
        meta: {
            middleware: [authMiddleware],
        },
    },
    {
        name: "EditUser",
        path: "/EditUser/:id",
        component: () => import("../Pages/Admin/EditUser.vue"),
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
            requiresComponent: true,
            componentName: "Quản lý hồ sơ",
        },
    },
    {
        path: "/Danhsachhoso/:id",
        name: "EditGiaoNhanhoso",
        component: EditGiaoNhanhoso,
        meta: {
            middleware: [authMiddleware],
            requiresAccess: true,
        },
        beforeEnter: async (to, from, next) => {
            // ดึงค่า id จาก route parameters
            const id = to.params.id;

            // ดึง token จาก localStorage
            let token = localStorage.getItem("web_token");
            const store = useStore();

            // ถ้าไม่มี token ใน localStorage ให้ลองดึงจาก store
            if (!token && store.getToken) {
                token = store.getToken;
            }

            // ถ้ายังไม่มี token ให้รอสักครู่เผื่อ store กำลังโหลด
            if (!token) {
                // รอ 100ms เผื่อ store กำลังโหลดข้อมูล
                await new Promise((resolve) => setTimeout(resolve, 100));
                token = localStorage.getItem("web_token") || store.getToken;
            }

            if (!token) {
                next("/login");
                return;
            }

            try {
                // ตรวจสอบสิทธิ์การเข้าถึง
                const response = await axios.get(
                    `/api/document-deliveries/${id}/check-access`,
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
        name: "Danhsachhoso",
        path: "/Danhsachhoso",
        component: Danhsachhoso,
        meta: {
            middleware: [authMiddleware],
            requiresComponent: true,
            componentName: "Quản lý hồ sơ",
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
        meta: {
            middleware: [authMiddleware],
            requiresAccess: true, // เพิ่ม flag นี้เพื่อบอกว่าต้องตรวจสอบสิทธิ์
        },
        beforeEnter: async (to, from, next) => {
            // ดึงค่า id จาก route parameters
            const id = to.params.id;

            // ดึง token จาก localStorage
            let token = localStorage.getItem("web_token");
            const store = useStore();

            // ถ้าไม่มี token ใน localStorage ให้ลองดึงจาก store
            if (!token && store.getToken) {
                token = store.getToken;
            }

            // ถ้ายังไม่มี token ให้รอสักครู่เผื่อ store กำลังโหลด
            if (!token) {
                // รอ 100ms เผื่อ store กำลังโหลดข้อมูล
                await new Promise((resolve) => setTimeout(resolve, 100));
                token = localStorage.getItem("web_token") || store.getToken;
            }

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
        meta: {
            middleware: [authMiddleware],
            requiresAccess: true,
        },
        beforeEnter: async (to, from, next) => {
            // ดึงค่า id จาก route parameters
            const id = to.params.id;

            // ดึง token จาก localStorage
            let token = localStorage.getItem("web_token");
            const store = useStore();

            // ถ้าไม่มี token ใน localStorage ให้ลองดึงจาก store
            if (!token && store.getToken) {
                token = store.getToken;
            }

            // ถ้ายังไม่มี token ให้รอสักครู่เผื่อ store กำลังโหลด
            if (!token) {
                // รอ 100ms เผื่อ store กำลังโหลดข้อมูล
                await new Promise((resolve) => setTimeout(resolve, 100));
                token = localStorage.getItem("web_token") || store.getToken;
            }

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
        name: "PhieutrinhthanhtoanHomgiong",
        path: "/PhieutrinhthanhtoanHomgiong",
        component: () =>
            import("../Pages/QuanlyTaichinh/PhieutrinhthanhtoanHomgiong.vue"),
        meta: {
            middleware: [authMiddleware],
        },
    },
    {
        name: "Phieudenghithanhtoandichvu",
        path: "/Phieudenghithanhtoandichvu",
        component: Phieudenghithanhtoandichvu,
        meta: {
            middleware: [authMiddleware],
        },
    },
    {
        name: "Details_Phieudenghithanhtoandichvu",
        path: "/Details_Phieudenghithanhtoandichvu/:id",
        component: Details_PhieuDNTTDV,
        meta: {
            middleware: [authMiddleware],
        },
    },

    {
        name: "Phieuthunodichvu",
        path: "/Phieuthunodichvu",
        component: Phieuthunodichvu,
        meta: {
            middleware: [authMiddleware],
        },
    },
    {
        CongnoDichvuKhautru: "CongnoDichvuKhautru",
        path: "/CongnoDichvuKhautru",
        component: CongnoDichvuKhautru,
        meta: {
            middleware: [authMiddleware],
        },
    },
    {
        name: "Details_CongnoDichvuKhautru",
        path: "/Details_CongnoDichvuKhautru/:id",
        component: Details_CongnoDichvuKhautru,
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

router.beforeEach(async (to, from, next) => {
    const store = useStore();
    const publicRoutes = ["/login", "/register"];

    // Public routes
    if (publicRoutes.includes(to.path)) {
        if (store.isAuthenticated) {
            next({ name: "Home", replace: true });
        } else {
            next();
        }
        return;
    }

    // Protected routes
    if (to.meta.middleware) {
        for (const middleware of to.meta.middleware) {
            await middleware(to, from, next);
        }
    } else {
        await authMiddleware(to, from, next);
    }
});

//ກວດສອບສິດໃນການແກ້ໄຂເອກະສານມອບສົ່ງວ່າມີສິດໃນການແກ້ໄຂເອກະສານບໍ່

export default router;
