import { createRouter, createWebHistory } from "vue-router";
import Home from "../Pages/Dashboard.vue";
import Taohoso from "../Pages/Quanlyhoso/TaoGiaoNhanhoso.vue";
import Danhsachhoso from "../Pages/Quanlyhoso/DanhsachHoso.vue";
import EditGiaoNhanhoso from "../Pages/Quanlyhoso/EditGiaoNhanhoso.vue";
import login from "../Pages/Login.vue";
import Nopage from "../Pages/404.vue";
import Register from "../Pages/Register.vue";
import { useStore } from "../Store/Auth";
import ListUser from "../Pages/Admin/User.vue";
import Permission from "../Pages/Admin/Permission.vue";
import Role from "../Pages/Admin/Role.vue";
import Profile from "../Pages/Admin/UserProfile.vue";

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
        name: "DanhsachhosoEdit",
        path: "/Danhsachhoso/:id",
        component: EditGiaoNhanhoso,
        meta: {
            middleware: [authMiddleware],
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

export default router;
