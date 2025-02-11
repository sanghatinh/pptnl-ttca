<template lang="">
    <div class="row d-flex justify-content-center background">
        <div class="col-xl-4 col-lg-5 col-md-6 col-sm-12">
            <div class="login-screen">
                <div class="login-box">
                    <a href="#" class="login-logo">
                        <img
                            src="/public/img/Logo/TTC LOGO.png"
                            alt="Le Rouge Admin Dashboard"
                        />
                    </a>
                    <h5>
                        Xin chào !,<br />Vui lòng đăng nhập vào tài khoản của
                        bạn.
                    </h5>
                    <div class="form-group">
                        <input
                            type="text"
                            v-model="username"
                            class="form-control"
                            placeholder="Username"
                        />
                    </div>
                    <div class="form-group">
                        <input
                            type="password"
                            v-model="password"
                            class="form-control"
                            placeholder="Password"
                        />
                    </div>

                    <div class="alert alert-warning" v-if="message_error">
                        {{ message_error }}
                    </div>
                    <div class="actions mb-4">
                        <div class="custom-control custom-checkbox">
                            <input
                                type="checkbox"
                                class="custom-control-input"
                                v-model="remember_me"
                            />
                            <label
                                class="custom-control-label"
                                for="remember_pwd"
                                >Remember me</label
                            >
                        </div>
                        <button
                            type="submit"
                            :disabled="checkFromLogin"
                            @click="Login()"
                            class="btn btn-primary"
                        >
                            Đăng nhập
                        </button>
                    </div>

                    <!-- <div class="forgot-pwd">
                        <a class="link" href="">Forgot password?</a>
                    </div> -->
                    <hr />
                    <!-- <div class="actions align-left">
                        <span class="additional-link">New here?</span>
                        <<router-link to="/register">
                            <span class="btn btn-dark">Create an Account</span>
                        </router-link>
                    </div> -->
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import axios from "axios";
export default {
    data() {
        return {
            url: window.location.href,
            username: "",
            password: "",
            message_error: "",
            remember_me: false,
        };
    },
    computed: {
        //ກວດສອບຂໍ້ມູນຜິດພາດ
        checkFromLogin() {
            //ປຶດປຸ່ມສະແດງຂໍ້ມູນຜິດພາດ
            if (this.username == "" || this.password == "") {
                return true;
            } else {
                return false;
            }
        },
    },
    methods: {
        Login() {
            //ກວດສອບຂໍ້ມູນຜິດພາດ
            if (this.username === "" || this.password === "") {
                this.message_error = "Vui lòng chờ thông tin đầy đủ.";
                return;
            }
            axios
                .post("api/login", {
                    username: this.username,
                    password: this.password,
                    remember_me: this.remember_me,
                })
                .then((res) => {
                    console.log(res.data);

                    if (res.data.success) {
                        // Save Token to LocalStorage
                        localStorage.setItem("web_token", res.data.token);
                        localStorage.setItem(
                            "web_user",
                            JSON.stringify(res.data.user)
                        );
                        // console.log('dot save token');

                        // clear from
                        this.username = "";
                        this.password = "";

                        // go to root page
                        this.$router.push("/");
                    } else {
                        this.message_error = res.data.message;
                    }
                })
                .catch((err) => {
                    console.log(err);
                    if (err.response && err.response.status === 403) {
                        this.message_error = "tài khoản bạn đã bị khóa 🔒";
                    } else {
                        this.message_error =
                            "Không tìm thấy tài khoản hoặc mật khẩu không đúng";
                    }
                });
        },
    },
};
</script>
<style scoped>
.row.d-flex.justify-content-center {
    position: absolute;
    right: 0;
    left: 0;
}
.background {
    background-image: url("/public/img/Logo/AdobeStock431936490Preview.JPG");
    background-size: cover;
    background-position: center;
    width: 100%;
    height: 100vh; /* หรือขนาดที่คุณต้องการ */
}
</style>
