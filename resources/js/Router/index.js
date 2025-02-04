import { createRouter, createWebHistory } from 'vue-router';
import Home from '../Pages/Dashboard.vue';
import Taohoso from '../Pages/Taomoihoso.vue';
import Danhsachhoso from '../Pages/DanhsachHoso.vue';
import login from '../Pages/Login.vue';
import Nopage from '../Pages/404.vue';
import Register from '../Pages/Register.vue';
import { useStore } from '../Store/Auth';
import ListUser from '../Pages/Admin/User.vue';
import Permission from '../Pages/Admin/Permission.vue'; 
import Role from '../Pages/Admin/Role.vue';




// created middleware
const authMiddleware = (to, from, next) => {

    // ກວດ token ຈາກ localStorage
    const token = localStorage.getItem('web_token');
    const user = localStorage.getItem('web_user');
    // console.log(user);
    const store = useStore();

    if(token){
        store.setToken(token);
        store.setUser(user);
        next();
        // console.log('middleware next');

    } else {
        // console.log('middleware no ');
        next({
            path: '/login',
            replace: true
        });
    }
        
};

const routes = [
    
        {
            name: 'Home',
            path: '/',
            component: Home,
            meta: {
                middleware: [authMiddleware]
            }
            
        },
       
        {
            name: 'Taohoso',
            path: '/Taohoso',
            component: Taohoso,
          
            meta: {
                middleware: [authMiddleware]
            }
        },
        {
            name: 'Danhsachhoso',
            path: '/Danhsachhoso',
            component: Danhsachhoso,

            meta: { 
                middleware: [authMiddleware]
            }
        },
        {
            name: 'login',
            path: '/login',
            component: login
        },
        {
            name: 'Register',   
            path: '/register',
            component: Register
        },
        {
            name: '404',
            path: '/:pathMatch(.*)*',
            component: Nopage,
            meta: {
                middleware: [authMiddleware]
            }
        },
        {
            name: 'User',
            path: '/User',
            component: ListUser,
            meta: {
                middleware: [authMiddleware]
            }
            
        },
        {
            name: 'Permission',
            path: '/Permission',
            component: Permission,
            meta: {
                middleware: [authMiddleware]
            }
        },
        {
            name: 'Role',
            path: '/Role',
            component: Role,
            meta: {
                middleware: [authMiddleware]
            }
        },
        
    
    ];



    const router = createRouter({
        history: createWebHistory(),
        routes: routes,
        scrollBehavior() {
            return { top: 0 }
        }
    });



    router.beforeEach((to,from,next)=>{
    
        const token = localStorage.getItem('web_token');
        if(to.meta.middleware){
            to.meta.middleware.forEach(middleware=>middleware(to,from,next))
            // console.log(token);
        } else {
            
            if(to.path == '/login' || to.path == '/'){
                if(token){
                   
                    next({
                       
                        name: 'Home',
                        replace: true
                        
                    })
                } else {
                    next();
                    
                }
            } else {
                next();
            }
        }
    });
    


    export default router;


