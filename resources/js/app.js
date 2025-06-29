import "./bootstrap";
import { createApp } from "vue";
import App from "./App.vue";
import router from "./Router";
import sidebarmenu from "./components/Sidebarmenu.vue";
import headerbar from "./components/Headerbar.vue";
import breadcrumbVue from "./Components/breadcrumb.Vue";
import RegisterPage from "./Pages/Register.vue";
import "bootstrap/dist/js/bootstrap.bundle.min.js";
import "bootstrap/dist/css/bootstrap.min.css";
import { ResizableColumns } from "./Directives/ResizableColumns";
import permissionsPlugin from "./Plugins/permissions";
import mitt from "mitt";

//sweetalert2
import VueSweetalert2 from "vue-sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";
import VueNumberFormat from "@coders-tm/vue-number-format";

import { PerfectScrollbarPlugin } from "vue3-perfect-scrollbar";
import "vue3-perfect-scrollbar/style.css";

import { createPinia } from "pinia";
const pinia = createPinia();

const app = createApp(App);

app.use(pinia);
app.use(router); // <--- Add this line
app.use(permissionsPlugin); // Add the permissions plugin
app.mount("#app-vue");

app.use(VueSweetalert2);

app.use(VueNumberFormat);
app.use(PerfectScrollbarPlugin);
app.directive("resizable-columns", ResizableColumns);
app.component("header-bar", headerbar);
app.component("side-barmenu", sidebarmenu);
app.component("breadcrumb-vue", breadcrumbVue);
app.component("Register-Page", RegisterPage);

const emitter = mitt();
app.config.globalProperties.$emitter = emitter;
