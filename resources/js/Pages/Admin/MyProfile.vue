<template>
    <div class="card shadow">
        <div class="min-h-screen py-8">
            <!-- Loading Overlay -->
            <div
                v-if="isLoading"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            >
                <div
                    class="bg-white rounded-lg p-6 flex items-center space-x-4"
                >
                    <div
                        class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-500"
                    ></div>
                    <span class="text-gray-700">{{ loadingMessage }}</span>
                </div>
            </div>

            <!-- Image Modal -->
            <div
                v-if="showImageModal"
                class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50"
                @click="closeImageModal"
            >
                <div class="relative max-w-4xl max-h-full p-4">
                    <!-- Close Button -->
                    <button
                        @click="closeImageModal"
                        class="absolute top-2 right-2 bg-white rounded-full p-2 shadow-lg hover:bg-gray-100 transition-colors z-10"
                    >
                        <svg
                            class="w-6 h-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            ></path>
                        </svg>
                    </button>

                    <!-- Image Container -->
                    <div class="bg-white rounded-lg overflow-hidden shadow-2xl">
                        <div class="relative">
                            <img
                                v-if="imagePreview || user.image"
                                :src="imagePreview || user.image"
                                alt="Profile Picture"
                                class="w-full h-auto max-h-[80vh] object-contain"
                            />
                            <div
                                v-else
                                class="w-full h-64 bg-gray-200 flex items-center justify-center"
                            >
                                <div class="text-center text-gray-500">
                                    <i class="fas fa-user text-4xl mb-2"></i>
                                    <p>No Image</p>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Footer with Delete Button -->
                        <div
                            class="p-4 bg-gray-50 border-t flex justify-center"
                        >
                            <button
                                @click="confirmDeleteImage"
                                :disabled="
                                    uploadingImage ||
                                    (!imagePreview && !user.image)
                                "
                                class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50"
                            >
                                <i class="fas fa-trash mr-2"></i>
                                Xóa hình ảnh
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-6xl mx-auto px-2 sm:px-6 lg:px-8">
                <!-- Profile Card -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <!-- Header Section with Avatar -->
                    <div
                        class="bg-gradient-to-r from-green-600 to-green-500 px-6 py-8 sm:px-8 relative"
                    >
                        <div
                            class="flex flex-col sm:flex-row items-center space-y-4 sm:space-y-0 sm:space-x-6"
                        >
                            <!-- Avatar Section -->
                            <div class="relative group">
                                <div
                                    class="w-32 h-32 rounded-full overflow-hidden bg-white shadow-lg cursor-pointer transition-transform hover:scale-105"
                                    @click="openImageModal"
                                >
                                    <img
                                        v-if="imagePreview || user.image"
                                        :src="imagePreview || user.image"
                                        alt="Profile Picture"
                                        class="w-full h-full object-cover"
                                    />
                                    <div
                                        v-else
                                        class="w-full h-full bg-gray-200 flex items-center justify-center"
                                    >
                                        <i
                                            class="fas fa-user text-gray-400 text-4xl"
                                        ></i>
                                    </div>
                                </div>
                                <!-- Upload Button Overlay -->
                                <button
                                    @click="triggerImageUpload"
                                    :disabled="uploadingImage"
                                    class="absolute bottom-0 right-0 bg-blue-600 text-white p-2 rounded-full shadow-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
                                    title="Tải ảnh lên"
                                >
                                    <svg
                                        v-if="!uploadingImage"
                                        class="w-4 h-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                                        ></path>
                                    </svg>
                                    <div
                                        v-else
                                        class="animate-spin w-4 h-4 border-2 border-white border-t-transparent rounded-full"
                                    ></div>
                                </button>
                                <!-- Hidden File Input -->
                                <input
                                    ref="imageInput"
                                    type="file"
                                    accept="image/*"
                                    @change="handleImageSelect"
                                    class="hidden"
                                />
                            </div>

                            <!-- User Info -->
                            <div class="text-center sm:text-left text-white">
                                <h1 class="text-3xl font-bold">
                                    {{ user.fullName || "Tên không có sẵn" }}
                                </h1>
                                <p class="text-green-100 text-lg">
                                    @{{ user.username || "username" }}
                                </p>
                                <div class="mt-2 space-y-1">
                                    <p class="text-green-100">
                                        <i class="fas fa-id-card mr-2"></i>
                                        {{ user.maNV || "Mã NV không có sẵn" }}
                                    </p>
                                    <p class="text-green-100">
                                        <i class="fas fa-briefcase mr-2"></i>
                                        {{
                                            user.positionName ||
                                            "Chức vụ không xác định"
                                        }}
                                    </p>
                                    <p class="text-green-100">
                                        <i class="fas fa-building mr-2"></i>
                                        {{
                                            user.stationName ||
                                            "Trạm không xác định"
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Section -->
                    <div class="px-3 py-8 sm:px-8">
                        <form @submit.prevent="updateProfile" class="space-y-6">
                            <!-- Personal Information Section -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h3
                                    class="text-lg font-semibold text-gray-800 mb-4 border-b border-gray-200 pb-2"
                                >
                                    <i
                                        class="fas fa-user mr-2 text-blue-600"
                                    ></i>
                                    Thông tin cá nhân
                                </h3>

                                <div
                                    class="grid grid-cols-1 md:grid-cols-2 gap-6"
                                >
                                    <!-- Username (Read-only) -->
                                    <div class="space-y-2">
                                        <label
                                            class="block text-sm font-medium text-gray-700"
                                        >
                                            Tên người dùng
                                        </label>
                                        <input
                                            type="text"
                                            :value="user.username"
                                            readonly
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed"
                                        />
                                    </div>

                                    <!-- Full Name (Read-only) -->
                                    <div class="space-y-2">
                                        <label
                                            class="block text-sm font-medium text-gray-700"
                                        >
                                            Họ và tên
                                        </label>
                                        <input
                                            type="text"
                                            :value="user.fullName"
                                            readonly
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed"
                                        />
                                    </div>

                                    <!-- Employee ID (Read-only) -->
                                    <div class="space-y-2">
                                        <label
                                            class="block text-sm font-medium text-gray-700"
                                        >
                                            Mã nhân viên
                                        </label>
                                        <input
                                            type="text"
                                            :value="user.maNV"
                                            readonly
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed"
                                        />
                                    </div>

                                    <!-- Position (Read-only) -->
                                    <div class="space-y-2">
                                        <label
                                            class="block text-sm font-medium text-gray-700"
                                        >
                                            Chức vụ
                                        </label>
                                        <input
                                            type="text"
                                            :value="user.positionName"
                                            readonly
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed"
                                        />
                                    </div>

                                    <!-- Station (Read-only) -->
                                    <div class="space-y-2">
                                        <label
                                            class="block text-sm font-medium text-gray-700"
                                        >
                                            Trạm
                                        </label>
                                        <input
                                            type="text"
                                            :value="user.stationName"
                                            readonly
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed"
                                        />
                                    </div>

                                    <!-- Role (Read-only) -->
                                    <div class="space-y-2">
                                        <label
                                            class="block text-sm font-medium text-gray-700"
                                        >
                                            Vai trò
                                        </label>
                                        <input
                                            type="text"
                                            :value="user.roleName"
                                            readonly
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed"
                                        />
                                    </div>

                                    <!-- Status (Read-only) -->
                                    <div class="space-y-2">
                                        <label
                                            class="block text-sm font-medium text-gray-700"
                                        >
                                            Trạng thái
                                        </label>
                                        <input
                                            type="text"
                                            :value="
                                                user.status === 'active'
                                                    ? 'Hoạt động'
                                                    : 'Không hoạt động'
                                            "
                                            readonly
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Information Section (Editable) -->
                            <div class="bg-blue-50 rounded-lg p-6">
                                <h3
                                    class="text-lg font-semibold text-gray-800 mb-4 border-b border-blue-200 pb-2"
                                >
                                    <i
                                        class="fas fa-envelope mr-2 text-blue-600"
                                    ></i>
                                    Thông tin liên hệ (Có thể chỉnh sửa)
                                </h3>

                                <div
                                    class="grid grid-cols-1 md:grid-cols-2 gap-6"
                                >
                                    <!-- Email (Editable) -->
                                    <div class="space-y-2">
                                        <label
                                            class="block text-sm font-medium text-gray-700"
                                        >
                                            Email
                                        </label>
                                        <input
                                            type="email"
                                            v-model="user.email"
                                            placeholder="Nhập địa chỉ email"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                        />
                                    </div>

                                    <!-- Phone (Editable) -->
                                    <div class="space-y-2">
                                        <label
                                            class="block text-sm font-medium text-gray-700"
                                        >
                                            Số điện thoại
                                        </label>
                                        <input
                                            type="tel"
                                            v-model="user.phone"
                                            placeholder="Nhập số điện thoại"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Password Change Section -->
                            <div
                                class="bg-yellow-50 rounded-lg p-6 border-t border-gray-200"
                            >
                                <h3
                                    class="text-lg font-semibold text-gray-800 mb-4 border-b border-yellow-200 pb-2"
                                >
                                    <i
                                        class="fas fa-lock mr-2 text-yellow-600"
                                    ></i>
                                    Thay đổi mật khẩu (Tùy chọn)
                                </h3>

                                <div
                                    class="grid grid-cols-1 md:grid-cols-2 gap-6"
                                >
                                    <!-- New Password -->
                                    <div class="space-y-2">
                                        <label
                                            class="block text-sm font-medium text-gray-700"
                                        >
                                            Mật khẩu mới
                                        </label>
                                        <div class="relative">
                                            <input
                                                :type="
                                                    showNewPassword
                                                        ? 'text'
                                                        : 'password'
                                                "
                                                v-model="
                                                    passwordForm.newPassword
                                                "
                                                placeholder="Nhập mật khẩu mới (tối thiểu 6 ký tự)"
                                                class="w-full px-4 py-3 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                            />
                                            <button
                                                type="button"
                                                @click="
                                                    showNewPassword =
                                                        !showNewPassword
                                                "
                                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700"
                                            >
                                                <i
                                                    :class="
                                                        showNewPassword
                                                            ? 'fas fa-eye-slash'
                                                            : 'fas fa-eye'
                                                    "
                                                ></i>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Confirm Password -->
                                    <div class="space-y-2">
                                        <label
                                            class="block text-sm font-medium text-gray-700"
                                        >
                                            Xác nhận mật khẩu mới
                                        </label>
                                        <div class="relative">
                                            <input
                                                :type="
                                                    showConfirmPassword
                                                        ? 'text'
                                                        : 'password'
                                                "
                                                v-model="
                                                    passwordForm.confirmPassword
                                                "
                                                placeholder="Nhập lại mật khẩu mới"
                                                class="w-full px-4 py-3 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                            />
                                            <button
                                                type="button"
                                                @click="
                                                    showConfirmPassword =
                                                        !showConfirmPassword
                                                "
                                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700"
                                            >
                                                <i
                                                    :class="
                                                        showConfirmPassword
                                                            ? 'fas fa-eye-slash'
                                                            : 'fas fa-eye'
                                                    "
                                                ></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div
                                class="flex flex-col sm:flex-row justify-end space-y-4 sm:space-y-0 sm:space-x-4 pt-6"
                            >
                                <button
                                    type="button"
                                    @click="resetForm"
                                    class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                                >
                                    <i class="fas fa-arrow-left mr-2"></i>
                                    Quay lại
                                </button>

                                <button
                                    type="submit"
                                    :disabled="isLoading"
                                    class="px-8 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-shadow hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <i
                                        v-if="!isLoading"
                                        class="fas fa-save mr-2"
                                    ></i>
                                    <div
                                        v-else
                                        class="animate-spin w-4 h-4 border-2 border-white border-t-transparent rounded-full inline-block mr-2"
                                    ></div>
                                    {{
                                        isLoading
                                            ? "Đang lưu..."
                                            : "Lưu thay đổi"
                                    }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import { useStore } from "../../Store/Auth";

export default {
    setup() {
        const store = useStore();
        return { store };
    },

    data() {
        return {
            isLoading: false,
            loadingMessage: "Processing...",
            uploadingImage: false,
            imagePreview: null,
            selectedImageFile: null,
            showImageModal: false,

            // Password visibility toggles
            showNewPassword: false,
            showConfirmPassword: false,

            // User data
            user: {
                id: null,
                image: "",
                image_public_id: "",
                username: "",
                fullName: "",
                maNV: "",
                chucVu: "",
                positionName: "", // เพิ่มฟิลด์นี้
                tram: "",
                stationName: "", // เพิ่มฟิลด์นี้
                email: "",
                phone: "",
                role: "",
                roleName: "", // เพิ่มฟิลด์นี้
                status: "active",
            },

            // Password form data
            passwordForm: {
                newPassword: "",
                confirmPassword: "",
            },
        };
    },

    async created() {
        await this.initializeComponent();
    },

    methods: {
        // Modal methods
        openImageModal() {
            if (this.imagePreview || this.user.image) {
                this.showImageModal = true;
                document.body.style.overflow = "hidden";
            }
        },

        closeImageModal() {
            this.showImageModal = false;
            document.body.style.overflow = "auto";
        },

        async initializeComponent() {
            this.isLoading = true;
            this.loadingMessage = "Loading profile data...";

            try {
                await this.loadUserData();
            } catch (error) {
                console.error("Error initializing component:", error);
                this.handleAuthError(error);
            } finally {
                this.isLoading = false;
            }
        },

        async loadUserData() {
            try {
                const response = await axios.get("/api/my-profile", {
                    headers: { Authorization: "Bearer " + this.store.getToken },
                });

                if (response.data.success) {
                    const userData = response.data.data;

                    // Map backend data to frontend model
                    this.user = {
                        id: userData.id,
                        image: userData.image_url || userData.image,
                        image_public_id: userData.image_public_id,
                        username: userData.username,
                        fullName: userData.full_name,
                        maNV: userData.ma_nhan_vien,
                        chucVu: userData.position,
                        positionName:
                            userData.position_name || "Chức vụ không xác định", // แสดงชื่อตำแหน่งที่ map แล้ว
                        tram: userData.station,
                        stationName:
                            userData.station_name || "Trạm không xác định", // แสดงชื่อสถานีที่ map แล้ว
                        email: userData.email,
                        phone: userData.phone,
                        role: userData.role_id,
                        roleName:
                            userData.role_name || "Vai trò không xác định", // แสดงชื่อบทบาทที่ map แล้ว
                        status: userData.status,
                    };
                } else {
                    throw new Error(
                        response.data.message || "Failed to load profile data"
                    );
                }
            } catch (error) {
                console.error("Error loading user data:", error);
                this.handleAuthError(error);
            }
        },

        // Main update method
        async updateProfile() {
            if (!this.validateForm()) return;

            this.isLoading = true;
            this.loadingMessage = "Đang cập nhật hồ sơ...";

            try {
                let imagePublicId = this.user.image_public_id;

                // Upload new image if selected
                if (this.selectedImageFile) {
                    this.loadingMessage = "Đang tải lên hình ảnh...";
                    const imageResult = await this.uploadImageSeparately();
                    if (imageResult && imageResult.success) {
                        imagePublicId = imageResult.public_id;
                    } else {
                        this.$swal({
                            icon: "warning",
                            title: "Tải lên hình ảnh thất bại",
                            text: "Hồ sơ sẽ được cập nhật mà không có hình ảnh mới.",
                            showConfirmButton: true,
                        });
                    }
                }

                // Prepare update data - เฉพาะฟิลด์ที่แก้ไขได้
                const updateData = {};

                // เฉพาะฟิลด์ที่อนุญาตให้แก้ไข
                if (this.user.email) {
                    updateData.email = this.user.email;
                }

                if (this.user.phone) {
                    updateData.phone = this.user.phone;
                }

                // Add image if available
                if (imagePublicId) {
                    updateData.image = imagePublicId;
                }

                // Add password fields if provided
                if (this.passwordForm.newPassword) {
                    updateData.new_password = this.passwordForm.newPassword;
                    updateData.confirm_password =
                        this.passwordForm.confirmPassword;
                }

                this.loadingMessage = "Đang lưu thay đổi...";

                // Send update request ไปยัง my-profile API
                const response = await axios.put(
                    "/api/my-profile",
                    updateData,
                    {
                        headers: {
                            Authorization: "Bearer " + this.store.getToken,
                        },
                        timeout: 30000,
                    }
                );

                if (response.data.success) {
                    this.$swal({
                        icon: "success",
                        title: "Thành công!",
                        text: "Hồ sơ của bạn đã được cập nhật thành công!",
                        showConfirmButton: false,
                        timer: 2000,
                    }).then(() => {
                        // แจ้ง Headerbar ให้รีเฟรช avatar
                        this.$root.$emitter?.emit("refresh-user-avatar");
                        // Clear password form
                        this.clearPasswordForm();
                        // Clear image upload state
                        this.clearImageUploadState();
                        // Reload profile data
                        this.loadUserData();
                    });
                } else {
                    this.$swal({
                        icon: "error",
                        title: "Cập nhật thất bại!",
                        text:
                            response.data.message || "Không thể cập nhật hồ sơ",
                        showConfirmButton: true,
                    });
                }
            } catch (err) {
                console.error("Update profile error:", err);

                let errorMessage = "Đã xảy ra lỗi khi cập nhật hồ sơ";

                if (err.response) {
                    if (err.response.status === 422) {
                        const errors = err.response.data.errors;
                        if (errors) {
                            errorMessage = "Lỗi xác thực:\n";
                            Object.keys(errors).forEach((key) => {
                                errorMessage += `• ${errors[key][0]}\n`;
                            });
                        } else {
                            errorMessage =
                                err.response.data.message || errorMessage;
                        }
                    } else if (err.response.status === 401) {
                        this.handleAuthError(err);
                        return;
                    } else {
                        errorMessage =
                            err.response.data.message || errorMessage;
                    }
                } else if (err.code === "ECONNABORTED") {
                    errorMessage =
                        "Hết thời gian chờ yêu cầu. Vui lòng thử lại.";
                }

                this.$swal({
                    icon: "error",
                    title: "Cập nhật thất bại!",
                    text: errorMessage,
                    showConfirmButton: true,
                });
            } finally {
                this.isLoading = false;
                this.loadingMessage = "Đang xử lý...";
            }
        },

        // Image handling methods
        triggerImageUpload() {
            this.$refs.imageInput.click();
        },

        handleImageSelect(event) {
            const file = event.target.files[0];
            if (file) {
                // Validate file type
                if (!file.type.startsWith("image/")) {
                    this.$swal({
                        icon: "error",
                        title: "Loại tệp không hợp lệ",
                        text: "Vui lòng chọn tệp hình ảnh",
                        showConfirmButton: true,
                    });
                    return;
                }

                // ตรวจสอบ HEIC/HEIF
                if (
                    file.type === "image/heic" ||
                    file.type === "image/heif" ||
                    file.name.toLowerCase().endsWith(".heic") ||
                    file.name.toLowerCase().endsWith(".heif")
                ) {
                    this.$swal({
                        icon: "warning",
                        title: "Không hỗ trợ định dạng HEIC",
                        text: "Vui lòng chuyển đổi hình ảnh sang JPG hoặc PNG trước khi tải lên.",
                        showConfirmButton: true,
                    });
                    return;
                }

                // Validate file size (5MB max)
                if (file.size > 5 * 1024 * 1024) {
                    this.$swal({
                        icon: "error",
                        title: "Tệp quá lớn",
                        text: "Kích thước hình ảnh phải nhỏ hơn 5MB",
                        showConfirmButton: true,
                    });
                    return;
                }

                this.selectedImageFile = file;

                // Create preview
                try {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.imagePreview = e.target.result;
                    };
                    reader.onerror = () => {
                        this.$swal({
                            icon: "error",
                            title: "Lỗi đọc file",
                            text: "Không thể đọc file hình ảnh này. Vui lòng thử lại với file khác.",
                            showConfirmButton: true,
                        });
                    };
                    reader.readAsDataURL(file);
                } catch (err) {
                    this.$swal({
                        icon: "error",
                        title: "Lỗi đọc file",
                        text: "Không thể đọc file hình ảnh này trên thiết bị của bạn.",
                        showConfirmButton: true,
                    });
                    return;
                }

                this.$swal({
                    toast: true,
                    position: "top-end",
                    icon: "info",
                    title: "Hình ảnh đã sẵn sàng để tải lên!",
                    showConfirmButton: false,
                    timer: 1500,
                });
            }
        },

        async uploadImageSeparately() {
            if (!this.selectedImageFile) return null;

            this.uploadingImage = true;
            const formData = new FormData();
            formData.append("image", this.selectedImageFile);

            try {
                const response = await axios.post(
                    "/api/user/upload-image",
                    formData,
                    {
                        headers: {
                            "Content-Type": "multipart/form-data",
                            Authorization: "Bearer " + this.store.getToken,
                        },
                        timeout: 30000,
                    }
                );

                if (response.data.success) {
                    this.$swal({
                        toast: true,
                        position: "top-end",
                        icon: "success",
                        title: "Tải lên hình ảnh thành công!",
                        showConfirmButton: false,
                        timer: 1500,
                    });
                    return response.data;
                }
                return null;
            } catch (error) {
                console.error("Image upload error:", error);
                this.$swal({
                    toast: true,
                    position: "top-end",
                    icon: "error",
                    title: "Tải lên hình ảnh thất bại",
                    showConfirmButton: false,
                    timer: 2000,
                });
                return null;
            } finally {
                this.uploadingImage = false;
            }
        },

        async confirmDeleteImage() {
            // Close modal first
            this.closeImageModal();
            try {
                const result = await this.$swal({
                    title: "Xóa hình ảnh?",
                    text: "Bạn có chắc chắn muốn xóa hình ảnh này?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#ef4444",
                    cancelButtonColor: "#6b7280",
                    confirmButtonText: "Có, xóa nó!",
                    cancelButtonText: "Hủy",
                    reverseButtons: true,
                });

                if (result.isConfirmed) {
                    await this.deleteUserImage();
                }
            } catch (error) {
                console.error("Error in confirmDeleteImage:", error);
            }
        },

        async deleteUserImage() {
            this.uploadingImage = true;

            try {
                const response = await axios.delete("/api/my-profile/image", {
                    headers: {
                        Authorization: "Bearer " + this.store.getToken,
                    },
                    timeout: 30000,
                });

                if (response.data.success) {
                    // Update local state
                    this.user.image = "";
                    this.user.image_public_id = "";
                    this.imagePreview = null;
                    this.selectedImageFile = null;

                    // Clear file input
                    if (this.$refs.imageInput) {
                        this.$refs.imageInput.value = "";
                    }

                    this.$swal({
                        toast: true,
                        position: "top-end",
                        icon: "success",
                        title: "Xóa hình ảnh thành công!",
                        showConfirmButton: false,
                        timer: 1500,
                    });
                } else {
                    this.$swal({
                        icon: "error",
                        title: "Xóa thất bại!",
                        text: response.data.message || "Không thể xóa hình ảnh",
                        showConfirmButton: true,
                    });
                }
            } catch (error) {
                console.error("Delete image error:", error);

                let errorMessage = "Đã xảy ra lỗi khi xóa hình ảnh";

                if (error.response) {
                    if (error.response.status === 400) {
                        errorMessage = "Không có hình ảnh để xóa";
                    } else if (error.response.status === 404) {
                        errorMessage = "Không tìm thấy người dùng";
                    } else if (error.response.status === 401) {
                        this.handleAuthError(error);
                        return;
                    } else {
                        errorMessage =
                            error.response.data.message || errorMessage;
                    }
                } else if (error.code === "ECONNABORTED") {
                    errorMessage =
                        "Hết thời gian chờ yêu cầu. Vui lòng thử lại.";
                }

                this.$swal({
                    icon: "error",
                    title: "Xóa thất bại!",
                    text: errorMessage,
                    showConfirmButton: true,
                });
            } finally {
                this.uploadingImage = false;
            }
        },

        // Validation methods
        validateForm() {
            // Email validation เท่านั้น เพราะฟิลด์อื่นเป็น read-only
            if (this.user.email && !this.isValidEmail(this.user.email)) {
                this.$swal({
                    icon: "warning",
                    title: "Email không hợp lệ",
                    text: "Vui lòng nhập địa chỉ email hợp lệ",
                    showConfirmButton: true,
                });
                return false;
            }

            // Password validation - เฉพาะเมื่อมีการกรอก
            if (
                this.passwordForm.newPassword ||
                this.passwordForm.confirmPassword
            ) {
                if (!this.passwordForm.newPassword) {
                    this.$swal({
                        icon: "warning",
                        title: "Yêu cầu mật khẩu mới",
                        text: "Vui lòng nhập mật khẩu mới",
                        showConfirmButton: true,
                    });
                    return false;
                }

                if (this.passwordForm.newPassword.length < 6) {
                    this.$swal({
                        icon: "warning",
                        title: "Mật khẩu quá ngắn",
                        text: "Mật khẩu mới phải có ít nhất 6 ký tự",
                        showConfirmButton: true,
                    });
                    return false;
                }

                if (
                    this.passwordForm.newPassword !==
                    this.passwordForm.confirmPassword
                ) {
                    this.$swal({
                        icon: "warning",
                        title: "Mật khẩu không khớp",
                        text: "Mật khẩu mới và xác nhận không khớp",
                        showConfirmButton: true,
                    });
                    return false;
                }
            }

            return true;
        },

        isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        },

        // Helper methods
        clearPasswordForm() {
            this.passwordForm = {
                newPassword: "",
                confirmPassword: "",
            };
            this.showNewPassword = false;
            this.showConfirmPassword = false;
        },

        clearImageUploadState() {
            this.imagePreview = null;
            this.selectedImageFile = null;
            if (this.$refs.imageInput) {
                this.$refs.imageInput.value = "";
            }
        },

        resetForm() {
            this.$router.push("/User");
        },

        handleAuthError(error) {
            if (error.response && error.response.status === 401) {
                localStorage.removeItem("web_token");
                localStorage.removeItem("web_user");
                this.store.logout();
                this.$router.push("/login");
            }
        },
    },

    // Add cleanup when component is destroyed
    beforeUnmount() {
        // Ensure body scroll is restored if modal was open
        document.body.style.overflow = "auto";
    },
};
</script>

<style scoped>
/* Enhanced styling for better UX */
.text-red-500 {
    color: #ef4444;
}

.grid {
    gap: 1.5rem;
}

/* Loading animation */
.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

/* Form field focus states */
input:focus,
select:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Button hover effects */
button[type="submit"]:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 10px 20px rgba(1, 144, 45, 0.3);
}

button[type="button"]:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Image upload styling */
.relative {
    position: relative;
}

.absolute {
    position: absolute;
}

/* Disabled state styling */
.disabled\:opacity-50:disabled {
    opacity: 0.5;
}

.disabled\:cursor-not-allowed:disabled {
    cursor: not-allowed;
}

/* Password form styling */
.border-t {
    border-top-width: 1px;
}

.pt-6 {
    padding-top: 1.5rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .grid {
        grid-template-columns: 1fr;
    }

    .flex-col {
        flex-direction: column;
    }

    .space-x-4 > * + * {
        margin-left: 0;
        margin-top: 1rem;
    }
}

/* Smooth transitions */
.transition-shadow {
    transition: box-shadow 0.2s ease-in-out;
}

.transition-colors {
    transition: background-color 0.2s ease-in-out, color 0.2s ease-in-out;
}

.hover\:shadow-xl:hover {
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1),
        0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Read-only field styling */
input[readonly] {
    background-color: #f3f4f6;
    color: #6b7280;
    cursor: not-allowed;
}

/* Avatar styling */
.rounded-full {
    border-radius: 9999px;
}

.overflow-hidden {
    overflow: hidden;
}

.object-cover {
    object-fit: cover;
}

/* Modal styling */
.fixed {
    position: fixed;
}

.inset-0 {
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
}

.z-50 {
    z-index: 50;
}
</style>
