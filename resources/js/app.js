import "./bootstrap";
import { createApp } from "vue";
import App from "./App.vue";
import router from "./Router";
import sidebarmenu from "./components/Sidebarmenu.vue";
import headerbar from "./components/Headerbar.vue";
import RegisterPage from "./Pages/Register.vue";
import "bootstrap/dist/js/bootstrap.bundle.min.js";
import "bootstrap/dist/css/bootstrap.min.css";
//sweetalert2
import VueSweetalert2 from "vue-sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";
//laravel-vue-pagination
// import * as LaravelVuePagination from "laravel-vue-pagination";
import { createPinia } from "pinia";
const pinia = createPinia();

const app = createApp(App);

app.use(VueSweetalert2);

// app.component(
//     "Pagination",
//     LaravelVuePagination.default || LaravelVuePagination
// );
app.component("header-bar", headerbar);
app.component("side-barmenu", sidebarmenu);
app.component("Register-Page", RegisterPage);
app.use(pinia);
app.use(router); // <--- Add this line
app.mount("#app-vue");
