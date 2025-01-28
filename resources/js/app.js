
import './bootstrap';
import { createApp } from 'vue';
import App from './App.vue';
import router from './Router';


const app = createApp(App);
app.use(router);   // <--- Add this line
app.mount('#app-vue');



