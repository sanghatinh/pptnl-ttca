
import './bootstrap';
import { createApp } from 'vue';
import App from './App.vue';
import router from './Router';
import sidebarmenu from './components/Sidebarmenu.vue';
import headerbar from './components/Headerbar.vue';
import RegisterPage from './Pages/Register.vue';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

import { createPinia } from 'pinia'
const pinia = createPinia();

const app = createApp(App);
app.component('header-bar', headerbar);
app.component('side-barmenu', sidebarmenu);
app.component('Register-Page', RegisterPage);
app.use(pinia);
app.use(router);   // <--- Add this line
app.mount('#app-vue');



