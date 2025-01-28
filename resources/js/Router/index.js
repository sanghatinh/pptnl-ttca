import { createRouter, createWebHistory } from 'vue-router';
import Home from '../Pages/Dashboard.vue';
import Taohoso from '../Pages/Taomoihoso.vue';
import Danhsachhoso from '../Pages/DanhsachHoso.vue';


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
    ];

    const router = createRouter({
        history: createWebHistory(),
        routes: routes,
        scrollBehavior() {
            return { top: 0 }
        }
    });

    export default router;
