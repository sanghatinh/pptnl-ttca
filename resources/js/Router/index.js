import { createRouter, createWebHistory } from 'vue-router';
import Home from '../Pages/Dashboard.vue';
import Taohoso from '../Pages/Taomoihoso.vue';
import Danhsachhoso from '../Pages/DanhsachHoso.vue';
import login from '../Pages/Login.vue';

//สรา้ง middleware สำหรับตรวจสอบการ login
const auth = (to, from, next) => {
    if (!localStorage.getItem('token')) {
        return next('/login');
    }
    next();
}


const routes = [
        {
            name: 'Home',
            path: '/Home',
            component: Home
        },
        {
            name: 'Taohoso',
            path: '/Taohoso',
            component: Taohoso
        },
        {
            name: 'Danhsachhoso',
            path: '/Danhsachhoso',
            component: Danhsachhoso
        },
        {
            name: 'login',
            path: '/login',
            component: login
        },

    ];

    const router = createRouter({
        history: createWebHistory(),
        routes: routes,
        scrollBehavior() {
            return { top: 0 }
        }
    });

    export default router;
