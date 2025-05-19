<template>
    <div class="login-page">
        <div class="login-wrapper">
            <!-- Left side - Image and company information -->
            <div class="login-banner">
                <div class="overlay"></div>
                <div class="banner-content">
                    <div class="logo-container-banner">
                        <img
                            src="/public/img/Logo/TTC LOGO FFF.png"
                            alt="TTC Logo"
                            class="login-logo-banner"
                        />
                    </div>
                    <h1 class="company-name">TTC ATTAPEU SUGAR CANE</h1>
                    <p class="company-slogan">SOLE CO.,LTD</p>
                </div>
            </div>

            <!-- Right side - Login form -->
            <div class="login-form-container">
                <div class="login-form-wrapper">
                    <div class="logo-container-mobile">
                        <img
                            src="/public/img/Logo/TTC LOGO.png"
                            alt="TTC Logo"
                            class="login-logo"
                        />
                    </div>

                    <h3 class="welcome-text">Chào mừng!</h3>
                    <p class="subtitle">
                        Vui lòng đăng nhập vào tài khoản của bạn
                    </p>

                    <!-- Login Type Tabs -->
                    <div class="login-tabs">
                        <button
                            :class="[
                                'tab-btn',
                                activeTab === 'employee' ? 'active' : '',
                            ]"
                            @click="activeTab = 'employee'"
                        >
                            <i class="fas fa-user-tie mr-1"></i> Nhân viên
                        </button>
                        <button
                            :class="[
                                'tab-btn',
                                activeTab === 'farmer' ? 'active' : '',
                            ]"
                            @click="activeTab = 'farmer'"
                        >
                            <i class="fas fa-leaf mr-1"></i> Nông dân
                        </button>
                    </div>

                    <!-- Employee Login Form -->
                    <div v-if="activeTab === 'employee'" class="login-form">
                        <div class="form-group">
                            <label for="username">Tên đăng nhập</label>
                            <div class="input-with-icon">
                                <i class="fas fa-user"></i>
                                <input
                                    id="username"
                                    type="text"
                                    v-model="employee.username"
                                    class="form-control"
                                    placeholder="Nhập tên đăng nhập"
                                    @keyup.enter="loginEmployee"
                                />
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password">Mật khẩu</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input
                                    id="password"
                                    :type="showPassword ? 'text' : 'password'"
                                    v-model="employee.password"
                                    class="form-control"
                                    placeholder="Nhập mật khẩu"
                                    @keyup.enter="loginEmployee"
                                />
                                <i
                                    :class="[
                                        'password-toggle',
                                        showPassword
                                            ? 'fas fa-eye-slash'
                                            : 'fas fa-eye',
                                    ]"
                                    @click="showPassword = !showPassword"
                                ></i>
                            </div>
                        </div>

                        <div class="form-options">
                            <div class="custom-control custom-checkbox">
                                <input
                                    id="remember-employee"
                                    type="checkbox"
                                    class="custom-control-input"
                                    v-model="employee.remember_me"
                                />
                                <label
                                    class="custom-control-label"
                                    for="remember-employee"
                                    >Ghi nhớ đăng nhập</label
                                >
                            </div>
                        </div>

                        <button
                            type="button"
                            class="btn btn-login"
                            :disabled="!isEmployeeFormValid || isLoading"
                            @click="loginEmployee"
                        >
                            <span v-if="isLoading"
                                ><i class="fas fa-spinner fa-spin"></i> Đang xử
                                lý...</span
                            >
                            <span v-else>Đăng nhập</span>
                        </button>
                    </div>

                    <!-- Farmer Login Form -->
                    <div v-if="activeTab === 'farmer'" class="login-form">
                        <div class="form-group">
                            <label for="farmerIdentifier"
                                >Mã khách hàng / Số điện thoại</label
                            >
                            <div class="input-with-icon">
                                <i class="fas fa-id-card"></i>
                                <input
                                    id="farmerIdentifier"
                                    type="text"
                                    v-model="farmer.identifier"
                                    class="form-control"
                                    placeholder="Nhập mã KH cá nhân, doanh nghiệp hoặc SĐT"
                                    @keyup.enter="loginFarmer"
                                />
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="farmerPassword">Mật khẩu</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input
                                    id="farmerPassword"
                                    :type="showPassword ? 'text' : 'password'"
                                    v-model="farmer.password"
                                    class="form-control"
                                    placeholder="Nhập mật khẩu"
                                    @keyup.enter="loginFarmer"
                                />
                                <i
                                    :class="[
                                        'password-toggle',
                                        showPassword
                                            ? 'fas fa-eye-slash'
                                            : 'fas fa-eye',
                                    ]"
                                    @click="showPassword = !showPassword"
                                ></i>
                            </div>
                        </div>

                        <div class="form-options">
                            <div class="custom-control custom-checkbox">
                                <input
                                    id="remember-farmer"
                                    type="checkbox"
                                    class="custom-control-input"
                                    v-model="farmer.remember_me"
                                />
                                <label
                                    class="custom-control-label"
                                    for="remember-farmer"
                                    >Ghi nhớ đăng nhập</label
                                >
                            </div>
                        </div>

                        <button
                            type="button"
                            class="btn btn-login"
                            :disabled="!isFarmerFormValid || isLoading"
                            @click="loginFarmer"
                        >
                            <span v-if="isLoading"
                                ><i class="fas fa-spinner fa-spin"></i> Đang xử
                                lý...</span
                            >
                            <span v-else>Đăng nhập</span>
                        </button>
                    </div>

                    <!-- Error Message Display -->
                    <div class="alert alert-danger mt-3" v-if="errorMessage">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        {{ errorMessage }}
                    </div>

                    <div class="login-footer">
                        <p>
                            © 2025 TTC ATTAPEU SUGAR CANE. All rights reserved.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import { useStore } from "../Store/Auth"; // เพิ่มบรรทัดนี้
export default {
    setup() {
        const store = useStore(); // เพิ่ม setup method
        return { store };
    },
    data() {
        return {
            activeTab: "employee",
            showPassword: false,
            isLoading: false,
            errorMessage: "",

            employee: {
                username: "",
                password: "",
                remember_me: false,
            },

            farmer: {
                identifier: "",
                password: "",
                remember_me: false,
            },
        };
    },

    computed: {
        isEmployeeFormValid() {
            return this.employee.username && this.employee.password;
        },

        isFarmerFormValid() {
            return this.farmer.identifier && this.farmer.password;
        },
    },

    methods: {
        async loginEmployee() {
            if (!this.isEmployeeFormValid) return;

            this.isLoading = true;
            this.errorMessage = "";

            try {
                const response = await axios.post("api/login", {
                    username: this.employee.username,
                    password: this.employee.password,
                    remember_me: this.employee.remember_me,
                });

                this.handleLoginSuccess(response.data);
            } catch (error) {
                this.handleLoginError(error);
            } finally {
                this.isLoading = false;
            }
        },

        async loginFarmer() {
            if (!this.isFarmerFormValid) return;

            this.isLoading = true;
            this.errorMessage = "";

            try {
                const response = await axios.post("api/farmer-login", {
                    identifier: this.farmer.identifier,
                    password: this.farmer.password,
                    remember_me: this.farmer.remember_me,
                });

                this.handleLoginSuccess(response.data);
            } catch (error) {
                this.handleLoginError(error);
            } finally {
                this.isLoading = false;
            }
        },

        // ในฟังก์ชัน handleLoginSuccess ของหน้า Login.vue
        handleLoginSuccess(data) {
            if (data.success) {
                // บันทึกข้อมูลการรับรองความถูกต้องลงใน localStorage
                localStorage.setItem("web_token", data.token);
                localStorage.setItem("web_user", JSON.stringify(data.user));
                localStorage.setItem("user_type", this.activeTab);

                // บันทึกลงใน store
                this.store.setToken(data.token);
                this.store.setUser(data.user);
                this.store.setUserType(this.activeTab); // เพิ่มบรรทัดนี้

                // รีเซ็ตฟอร์ม
                if (this.activeTab === "employee") {
                    this.employee.username = "";
                    this.employee.password = "";
                } else {
                    this.farmer.identifier = "";
                    this.farmer.password = "";
                }

                // นำทางไปยังหน้า dashboard
                this.$router.push("/").then(() => {
                    this.$router.go(0);
                });
            } else {
                this.errorMessage =
                    data.message || "Đăng nhập không thành công";
            }
        },

        handleLoginError(error) {
            console.error("Login error:", error);

            if (error.response) {
                if (error.response.status === 403) {
                    this.errorMessage = "Tài khoản của bạn đã bị khóa 🔒";
                } else if (error.response.data && error.response.data.message) {
                    this.errorMessage = error.response.data.message;
                } else {
                    this.errorMessage =
                        "Không tìm thấy tài khoản hoặc mật khẩu không đúng";
                }
            } else {
                this.errorMessage =
                    "Không thể kết nối đến máy chủ. Vui lòng thử lại sau.";
            }
        },
    },
};
</script>

<style scoped>
.login-page {
    min-height: 100vh;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f5f8fa;
    position: relative;
    overflow: hidden;
}

.login-wrapper {
    display: flex;
    width: 100%;
    height: 100vh;
    max-width: 100%;
    overflow: hidden;
    box-shadow: 0 0 40px rgba(0, 0, 0, 0.1);
}

/* Left side - Banner */
.login-banner {
    flex: 1;
    background-image: url("/public/img/Logo/AdobeStock431936490Preview.JPG");
    background-size: cover;
    background-position: center;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    padding: 2rem;
    text-align: center;
}

.overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.4);
    z-index: 1;
}

.banner-content {
    position: relative;
    z-index: 2;
}

.logo-container-banner {
    margin-bottom: 2rem;
    justify-items: center;
}

.login-logo-banner {
    max-width: 180px;
    filter: drop-shadow(0 0 10px rgba(0, 0, 0, 0.2));
}

.company-name {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.company-slogan {
    font-size: 1.2rem;
    opacity: 0.9;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}

/* Right side - Form */
.login-form-container {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: white;
    padding: 2rem;
    overflow-y: auto;
}

.login-form-wrapper {
    width: 100%;
    max-width: 450px;
    padding: 2rem;
}

.logo-container-mobile {
    display: none;
    text-align: center;
    margin-bottom: 2rem;
    justify-items: center;
}

.login-logo {
    max-width: 140px;
}

.welcome-text {
    font-size: 2rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 0.5rem;
    text-align: center;
}

.subtitle {
    color: #666;
    margin-bottom: 2rem;
    font-size: 1.1rem;
    text-align: center;
}

/* Tabs styling */
.login-tabs {
    display: flex;
    margin-bottom: 2rem;
    border-bottom: 1px solid #eaeaea;
}

.tab-btn {
    flex: 1;
    padding: 1rem;
    background: none;
    border: none;
    border-bottom: 2px solid transparent;
    color: #777;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s ease;
    cursor: pointer;
}

.tab-btn:hover {
    color: #0056b3;
}

.tab-btn.active {
    color: #0056b3;
    border-bottom-color: #0056b3;
}

/* Form styling */
.login-form {
    animation: fadeIn 0.4s ease-in-out;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #444;
    font-size: 0.95rem;
}

.input-with-icon {
    position: relative;
}

.input-with-icon i:not(.password-toggle) {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #aaa;
}

.input-with-icon input {
    padding-left: 45px;
    height: 52px;
    font-size: 1rem;
    border-radius: 8px;
    border: 1px solid #ddd;
    transition: all 0.3s;
    width: 100%;
    background-color: #f8f9fa;
}

.input-with-icon input:focus {
    border-color: #0056b3;
    background-color: #fff;
    box-shadow: 0 0 0 3px rgba(0, 86, 179, 0.15);
    outline: none;
}

.password-toggle {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #aaa;
    cursor: pointer;
    transition: color 0.2s;
}

.password-toggle:hover {
    color: #666;
}

/* Checkbox styling */
.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.75rem;
}

.custom-control-input {
    position: absolute;
    opacity: 0;
}

.custom-control-label {
    position: relative;
    padding-left: 1.75rem;
    cursor: pointer;
    color: #666;
}

.custom-control-label::before {
    content: "";
    position: absolute;
    left: 0;
    top: 2px;
    width: 18px;
    height: 18px;
    border: 1px solid #ccc;
    border-radius: 3px;
    background-color: #fff;
}

.custom-control-input:checked ~ .custom-control-label::before {
    background-color: #0056b3;
    border-color: #0056b3;
}

.custom-control-input:checked ~ .custom-control-label::after {
    content: "✓";
    position: absolute;
    left: 4px;
    top: -1px;
    color: white;
    font-size: 14px;
}

/* Button styling */
.btn-login {
    width: 100%;
    padding: 14px;
    background-color: #0056b3;
    border: none;
    border-radius: 8px;
    color: white;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s;
    box-shadow: 0 4px 6px rgba(0, 86, 179, 0.2);
}

.btn-login:hover:not(:disabled) {
    background-color: #004494;
    transform: translateY(-2px);
    box-shadow: 0 6px 8px rgba(0, 86, 179, 0.25);
}

.btn-login:active:not(:disabled) {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(0, 86, 179, 0.2);
}

.btn-login:disabled {
    background-color: #92b4d3;
    cursor: not-allowed;
    box-shadow: none;
}

/* Error message */
.alert-danger {
    background-color: #fff2f2;
    color: #e74c3c;
    border: 1px solid #fadbd8;
    border-radius: 8px;
    padding: 1rem;
    animation: shake 0.5s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
}

@keyframes shake {
    10%,
    90% {
        transform: translateX(-1px);
    }
    20%,
    80% {
        transform: translateX(2px);
    }
    30%,
    50%,
    70% {
        transform: translateX(-4px);
    }
    40%,
    60% {
        transform: translateX(4px);
    }
}

/* Footer */
.login-footer {
    margin-top: 2rem;
    text-align: center;
    color: #999;
    font-size: 0.9rem;
}

/* Animations */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive styles */
@media (max-width: 991px) {
    .login-wrapper {
        flex-direction: column;
        height: auto;
    }

    .login-banner {
        display: none;
    }

    .logo-container-mobile {
        display: block;
    }

    .login-form-container {
        flex: none;
        height: 100vh;
        padding: 2rem 1.5rem;
    }

    .login-form-wrapper {
        padding: 0;
    }
}

@media (max-width: 576px) {
    .login-form-wrapper {
        max-width: 100%;
    }

    .welcome-text {
        font-size: 1.75rem;
    }

    .tab-btn {
        font-size: 0.9rem;
        padding: 0.75rem 0.5rem;
    }

    .form-group label {
        font-size: 0.9rem;
    }

    .input-with-icon input {
        height: 48px;
        font-size: 0.95rem;
    }

    .btn-login {
        padding: 12px;
    }
}

@media (max-width: 376px) {
    .login-form-wrapper {
        max-width: 100%;
    }

    .welcome-text {
        font-size: 1.75rem;
    }

    .tab-btn {
        font-size: 0.9rem;
        padding: 0.75rem 0.5rem;
    }

    .form-group label {
        font-size: 0.9rem;
    }

    .input-with-icon input {
        height: 48px;
        font-size: 0.95rem;
    }

    .btn-login {
        padding: 12px;
    }
    .login-logo {
        max-width: 100px;
        margin-top: 100px;
    }
}
</style>
