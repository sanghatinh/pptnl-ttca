
import './bootstrap';
import { createApp } from 'vue';
import App from './App.vue';
import router from './Router';
import Sidebarmenu from './components/SidebarMenu.vue';
import headerbar from './components/Headerbar.vue';


const app = createApp(App);
app.component('header-bar', headerbar);
app.component('side-barmenu', Sidebarmenu);
app.use(router);   // <--- Add this line
app.mount('#app-vue');



