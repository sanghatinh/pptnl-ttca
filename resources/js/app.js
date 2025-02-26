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
import VueNumberFormat from "@coders-tm/vue-number-format";

import { createPinia } from "pinia";
const pinia = createPinia();

const app = createApp(App);

app.use(VueSweetalert2);

app.use(VueNumberFormat);
app.component("header-bar", headerbar);
app.component("side-barmenu", sidebarmenu);
app.component("Register-Page", RegisterPage);
app.use(pinia);
app.use(router); // <--- Add this line
app.mount("#app-vue");
