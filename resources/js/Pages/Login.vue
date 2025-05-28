<template>
    <div class="login-page">
        <div class="login-center-box">
            <!-- ส่วนที่ 2: Gradient + Logo -->
            <div class="login-gradient">
                <div class="logo-center">
                    <img
                        src="/public/img/Logo/TTC LOGO FFF.png"
                        alt="TTC Logo"
                    />
                </div>
                <div class="company-info">
                    <h1 class="company-name">TTC ATTAPEU SUGAR CANE</h1>
                    <p class="company-slogan">SOLE CO.,LTD</p>
                </div>
                <div class="dev-credit">
                    Developed By <b>Sang sengtongdeng</b>
                </div>
            </div>
            <!-- ส่วนที่ 3: Login Form -->
            <div class="login-form-modern">
                <h2 class="login-title">Login</h2>
                <p class="login-welcome">
                    Vui lòng đăng nhập vào tài khoản của bạn
                </p>
                <div class="login-tabs">
                    <button
                        :class="[
                            'tab-btn',
                            activeTab === 'employee' ? 'active' : '',
                        ]"
                        @click="activeTab = 'employee'"
                    >
                        <i class="fas fa-user-tie mr-1"></i>Nhân viên
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
                <form
                    v-if="activeTab === 'employee'"
                    @submit.prevent="loginEmployee"
                >
                    <div class="form-group">
                        <span class="input-icon">
                            <i class="fas fa-user"></i>
                        </span>
                        <input
                            type="text"
                            v-model="employee.username"
                            placeholder="Tên đăng nhập"
                            required
                        />
                    </div>
                    <div class="form-group">
                        <input
                            :type="showPassword ? 'text' : 'password'"
                            v-model="employee.password"
                            placeholder="Mật khẩu"
                            required
                        />
                        <span
                            class="toggle-password"
                            @click="showPassword = !showPassword"
                        >
                            <i
                                :class="
                                    showPassword
                                        ? 'fas fa-eye-slash'
                                        : 'fas fa-eye'
                                "
                            ></i>
                        </span>
                    </div>
                    <div class="form-options">
                        <label>
                            <input
                                type="checkbox"
                                v-model="employee.remember_me"
                            />
                            Ghi nhớ đăng nhập
                        </label>
                    </div>
                    <button
                        type="submit"
                        class="btn-modern"
                        :disabled="!isEmployeeFormValid || isLoading"
                    >
                        <span v-if="isLoading"
                            ><i class="fas fa-spinner fa-spin"></i> Đang xử
                            lý...</span
                        >
                        <span v-else>Đăng nhập</span>
                    </button>
                </form>
                <!-- Farmer Login Form -->
                <form
                    v-if="activeTab === 'farmer'"
                    @submit.prevent="loginFarmer"
                >
                    <div class="form-group">
                        <span class="input-icon">
                            <i class="fas fa-id-card"></i>
                        </span>
                        <input
                            type="text"
                            v-model="farmer.identifier"
                            placeholder="Nhập mã KH cá nhân, doanh nghiệp hoặc SĐT"
                            required
                        />
                    </div>
                    <div class="form-group">
                        <input
                            :type="showPassword ? 'text' : 'password'"
                            v-model="farmer.password"
                            placeholder="Mật khẩu"
                            required
                        />
                        <span
                            class="toggle-password"
                            @click="showPassword = !showPassword"
                        >
                            <i
                                :class="
                                    showPassword
                                        ? 'fas fa-eye-slash'
                                        : 'fas fa-eye'
                                "
                            ></i>
                        </span>
                    </div>
                    <div class="form-options">
                        <label>
                            <input
                                type="checkbox"
                                v-model="farmer.remember_me"
                            />
                            Ghi nhớ đăng nhập
                        </label>
                    </div>
                    <button
                        type="submit"
                        class="btn-modern"
                        :disabled="!isFarmerFormValid || isLoading"
                    >
                        <span v-if="isLoading"
                            ><i class="fas fa-spinner fa-spin"></i> Đang xử
                            lý...</span
                        >
                        <span v-else>Đăng nhập</span>
                    </button>
                </form>
                <div class="alert-modern" v-if="errorMessage">
                    <i class="fas fa-exclamation-circle"></i> {{ errorMessage }}
                </div>
                <div class="login-footer">
                    © 2025 TTC ATTAPEU SUGAR CANE. All rights reserved.
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
                this.store.setUserType(this.activeTab);

                // เพิ่มเก็บข้อมูล supplier_number กรณีเป็น farmer
                if (this.activeTab === "farmer" && data.user.ma_kh_ca_nhan) {
                    this.store.setSupplierId(data.user.ma_kh_ca_nhan);
                    localStorage.setItem(
                        "supplier_id",
                        data.user.ma_kh_ca_nhan
                    );
                } else if (
                    this.activeTab === "farmer" &&
                    data.user.ma_kh_doanh_nghiep
                ) {
                    this.store.setSupplierId(data.user.ma_kh_doanh_nghiep);
                    localStorage.setItem(
                        "supplier_id",
                        data.user.ma_kh_doanh_nghiep
                    );
                }

                // โหลดสิทธิ์และ components
                this.store.loadPermissionsAndComponents().then(() => {
                    // นำทางไปยังหน้า dashboard
                    this.$router.push("/").then(() => {
                        this.$router.go(0);
                    });
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
    width: 100vw;
    background: url("/public/img/Logo/AdobeStock431936490Preview.JPG")
        center/cover no-repeat;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.login-center-box {
    display: flex;
    width: 900px;
    max-width: 98vw;
    min-height: 520px;
    border-radius: 24px;
    box-shadow: 0 8px 40px rgba(0, 0, 0, 0.18);
    overflow: hidden;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(2px);
}

.login-gradient {
    flex: 1.1;
    background: linear-gradient(135deg, #01902d 0%, #38b87c 100%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    position: relative;
    padding: 32px 16px;
    color: #fff;
}

.logo-center img {
    width: 120px;
    height: 120px;
    object-fit: contain;
    margin-bottom: 16px;
    border-radius: 16px;
    /* background: rgba(255, 255, 255, 0.1);
    box-shadow: 0 2px 16px rgba(0, 0, 0, 0.13); */
}

.company-info {
    text-align: center;
    margin-bottom: 24px;
}

.company-name {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 0.2rem;
    letter-spacing: 1px;
    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.13);
}

.company-slogan {
    font-size: 1.1rem;
    opacity: 0.95;
    letter-spacing: 0.5px;
}

.dev-credit {
    position: absolute;
    bottom: 18px;
    left: 0;
    width: 100%;
    text-align: center;
    font-size: 1rem;
    opacity: 0.85;
    letter-spacing: 0.5px;
}

.login-form-modern {
    flex: 1.2;
    background: #fff;
    padding: 48px 36px 32px 36px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    border-radius: 0 24px 24px 0;
    min-width: 320px;
    box-shadow: 0 2px 12px rgba(1, 144, 45, 0.06);
}

.login-title {
    font-size: 2rem;
    font-weight: 700;
    color: #01902d;
    margin-bottom: 0.5rem;
    text-align: left;
}

.login-welcome {
    color: #555;
    margin-bottom: 1.5rem;
    font-size: 1.1rem;
    text-align: left;
}

.login-tabs {
    display: flex;
    margin-bottom: 1.5rem;
    border-radius: 8px;
    overflow: hidden;
    background: #f3f3f3;
}

.tab-btn {
    flex: 1;
    padding: 0.85rem 0;
    background: none;
    border: none;
    color: #01902d;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: background 0.2s, color 0.2s;
}

.tab-btn.active,
.tab-btn:focus {
    background: #01902d;
    color: #fff;
}

.form-group {
    margin-bottom: 1.25rem;
    position: relative;
}

.input-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #b0b0b0;
    font-size: 1.1rem;
    z-index: 2;
}

.form-group input {
    width: 100%;
    padding: 14px 44px 14px 40px; /* เพิ่ม padding ซ้ายสำหรับ icon */
    border: 1.5px solid #e0e0e0;
    border-radius: 8px;
    font-size: 1rem;
    background: #f8f8f8;
    transition: border 0.2s;
}

.form-group input:focus {
    border: 1.5px solid #01902d;
    background: #fff;
    outline: none;
}

.toggle-password {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #aaa;
    cursor: pointer;
    font-size: 1.1rem;
}

.form-options {
    display: flex;
    align-items: center;
    margin-bottom: 1.5rem;
    font-size: 0.98rem;
    color: #555;
}

.form-options label {
    cursor: pointer;
    user-select: none;
}

.btn-modern {
    width: 100%;
    padding: 14px;
    background: linear-gradient(90deg, #01902d 0%, #38b87c 100%);
    border: none;
    border-radius: 8px;
    color: #fff;
    font-weight: 700;
    font-size: 1.08rem;
    transition: background 0.2s, box-shadow 0.2s;
    box-shadow: 0 4px 16px rgba(1, 144, 45, 0.1);
    margin-bottom: 8px;
}

.btn-modern:hover:not(:disabled) {
    background: linear-gradient(90deg, #38b87c 0%, #01902d 100%);
    box-shadow: 0 6px 18px rgba(1, 144, 45, 0.13);
}

.btn-modern:disabled {
    background: #b7e1c3;
    color: #fff;
    cursor: not-allowed;
    box-shadow: none;
}

.alert-modern {
    background: #fff2f2;
    color: #e74c3c;
    border: 1px solid #fadbd8;
    border-radius: 8px;
    padding: 0.8rem 1rem;
    margin-top: 0.8rem;
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.login-footer {
    margin-top: 2rem;
    text-align: center;
    color: #999;
    font-size: 0.9rem;
}

/* Responsive */
@media (max-width: 900px) {
    .login-center-box {
        flex-direction: column;
        width: 98vw;
        min-height: 0;
        border-radius: 24px;
    }
    .login-gradient,
    .login-form-modern {
        border-radius: 0;
        min-width: 0;
    }
    .login-gradient {
        border-radius: 24px 24px 0 0;
        padding: 28px 10vw 18px 10vw;
    }
    .login-form-modern {
        border-radius: 0 0 24px 24px;
        padding: 32px 10vw 24px 10vw;
    }
}

@media (max-width: 600px) {
    .login-center-box {
        width: 90vw;
        min-height: 0;
        box-shadow: none;
        border-radius: 10px;
    }
    .login-gradient,
    .login-form-modern {
        padding: 24px 6vw;
        border-radius: 0;
    }
    .login-form-modern {
        padding-bottom: 16px;
    }
    .company-name {
        font-size: 1rem;
    }
    .logo-center img {
        width: 80px;
        height: 80px;
    }
}
</style>
