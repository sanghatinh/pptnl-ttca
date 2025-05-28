<template>
    <!-- Loading starts -->
    <div id="loading-wrapper" v-if="isLoading">
        <div class="spinner-border" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <div class="min-h-screen py-8">
        <div class="max-w-6xl mx-auto px-2 sm:px-6 lg:px-8">
            <!-- Profile Card -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <!-- Header Section with Avatar -->
                <div
                    class="bg-gradient-to-r from-green-600 to-green-500 px-6 py-8 sm:px-8"
                    style="
                        background: linear-gradient(to right, #01902d, #22c55e);
                    "
                >
                    <div
                        class="flex flex-col sm:flex-row items-center space-y-4 sm:space-y-0 sm:space-x-6"
                    >
                        <!-- Avatar with Upload -->
                        <div class="relative">
                            <div
                                class="w-24 h-24 bg-white rounded-full flex items-center justify-center shadow-lg overflow-hidden"
                            >
                                <img
                                    v-if="imagePreview || user.image"
                                    :src="
                                        imagePreview ||
                                        getCloudinaryUrl(user.image)
                                    "
                                    class="w-full h-full rounded-full object-cover"
                                    alt="User Avatar"
                                />
                                <svg
                                    v-else
                                    class="w-12 h-12 text-gray-400"
                                    viewBox="0 0 24 24"
                                    fill="currentColor"
                                >
                                    <path
                                        d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"
                                    />
                                </svg>
                            </div>
                            <!-- Upload Button -->
                            <button
                                type="button"
                                @click="triggerImageUpload"
                                class="absolute bottom-0 right-0 bg-white rounded-full p-2 shadow-lg hover:shadow-xl transition-shadow"
                                :disabled="uploadingImage"
                            >
                                <svg
                                    v-if="!uploadingImage"
                                    class="w-4 h-4 text-gray-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                                    />
                                </svg>
                                <div v-else class="w-4 h-4">
                                    <div
                                        class="animate-spin rounded-full h-4 w-4 border-b-2 border-gray-600"
                                    ></div>
                                </div>
                            </button>
                            <!-- Hidden File Input -->
                            <input
                                ref="imageInput"
                                type="file"
                                @change="handleImageSelect"
                                accept="image/*"
                                class="hidden"
                            />
                        </div>

                        <!-- Basic Info -->
                        <div class="text-center sm:text-left text-white">
                            <h2 class="text-2xl font-bold">
                                {{ user.fullName || "Add New User" }}
                            </h2>
                            <p class="text-blue-100">
                                {{
                                    getPositionName(user.chucVu) ||
                                    "Select Position"
                                }}
                            </p>
                            <p class="text-blue-200 text-sm">
                                {{ user.maNV || "Employee ID" }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Form Section -->
                <div class="px-3 py-8 sm:px-8">
                    <form @submit.prevent="updateProfile" class="space-y-6">
                        <!-- Grid Layout for Form Fields -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Username -->
                            <div class="space-y-2">
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    <svg
                                        class="inline w-4 h-4 mr-2"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                        />
                                    </svg>
                                    Username<span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="user.username"
                                    type="text"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                    placeholder="Enter username"
                                    required
                                />
                            </div>

                            <!-- Password -->
                            <div class="space-y-2">
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    <svg
                                        class="inline w-4 h-4 mr-2"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                        />
                                    </svg>
                                    Password<span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input
                                        v-model="user.password"
                                        :type="
                                            showPassword ? 'text' : 'password'
                                        "
                                        class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                        placeholder="Enter password"
                                        required
                                    />
                                    <button
                                        type="button"
                                        @click="showPassword = !showPassword"
                                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                    >
                                        <svg
                                            v-if="showPassword"
                                            class="w-5 h-5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                            />
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                            />
                                        </svg>
                                        <svg
                                            v-else
                                            class="w-5 h-5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Full Name -->
                            <div class="space-y-2">
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    <svg
                                        class="inline w-4 h-4 mr-2"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                    Full Name<span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="user.fullName"
                                    type="text"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                    placeholder="Enter full name"
                                    required
                                />
                            </div>

                            <!-- Mã NV -->
                            <div class="space-y-2">
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    <svg
                                        class="inline w-4 h-4 mr-2"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"
                                        />
                                    </svg>
                                    Mã NV
                                </label>
                                <input
                                    v-model="user.maNV"
                                    type="text"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                    placeholder="Enter employee ID (e.g., 000123)"
                                    maxlength="6"
                                />
                                <small class="text-gray-500 text-sm">
                                    Nhập mã nhân viên dạng 6 chữ số (ví dụ:
                                    000123)
                                </small>
                            </div>

                            <!-- Chức vụ -->
                            <div class="space-y-2">
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    <svg
                                        class="inline w-4 h-4 mr-2"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H8a2 2 0 01-2-2V8a2 2 0 012-2V6"
                                        />
                                    </svg>
                                    Chức vụ<span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="user.chucVu"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                    required
                                >
                                    <option value="" disabled>
                                        Select Position
                                    </option>
                                    <option
                                        v-for="position in positions"
                                        :key="position.id_position"
                                        :value="position.id_position"
                                    >
                                        {{ position.position }}
                                    </option>
                                </select>
                            </div>

                            <!-- Trạm -->
                            <div class="space-y-2">
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    <svg
                                        class="inline w-4 h-4 mr-2"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                        />
                                    </svg>
                                    Trạm<span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="user.tram"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                    required
                                >
                                    <option value="" disabled>
                                        Select Station
                                    </option>
                                    <option
                                        v-for="station in stations"
                                        :key="station.ma_don_vi"
                                        :value="station.ma_don_vi"
                                    >
                                        {{ station.Name }}
                                    </option>
                                </select>
                            </div>

                            <!-- Email -->
                            <div class="space-y-2">
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    <svg
                                        class="inline w-4 h-4 mr-2"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                        />
                                    </svg>
                                    Email
                                </label>
                                <input
                                    v-model="user.email"
                                    type="email"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                    placeholder="Enter email address"
                                />
                            </div>

                            <!-- Phone -->
                            <div class="space-y-2">
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    <svg
                                        class="inline w-4 h-4 mr-2"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
                                        />
                                    </svg>
                                    Phone
                                </label>
                                <input
                                    v-model="user.phone"
                                    type="tel"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                    placeholder="Enter phone number"
                                />
                            </div>

                            <!-- Role -->
                            <div class="space-y-2">
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    <svg
                                        class="inline w-4 h-4 mr-2"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                                        />
                                    </svg>
                                    Role<span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="user.role"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                    required
                                >
                                    <option value="" disabled>
                                        Select role
                                    </option>
                                    <option
                                        v-for="role in roles"
                                        :key="role.id"
                                        :value="role.id"
                                    >
                                        {{ role.name }}
                                    </option>
                                </select>
                            </div>

                            <!-- Status -->
                            <div class="space-y-2">
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    <svg
                                        class="inline w-4 h-4 mr-2"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                    Status<span class="text-red-500">*</span>
                                </label>
                                <div class="flex space-x-4">
                                    <label class="flex items-center">
                                        <input
                                            v-model="user.status"
                                            type="radio"
                                            value="active"
                                            class="w-4 h-4 text-blue-600 focus:ring-blue-500"
                                        />
                                        <span class="ml-2 text-sm text-gray-700"
                                            >Active</span
                                        >
                                    </label>
                                    <label class="flex items-center">
                                        <input
                                            v-model="user.status"
                                            type="radio"
                                            value="inactive"
                                            class="w-4 h-4 text-red-600 focus:ring-red-500"
                                        />
                                        <span class="ml-2 text-sm text-gray-700"
                                            >Inactive</span
                                        >
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div
                            class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-gray-200"
                        >
                            <button
                                type="submit"
                                :disabled="isLoading || uploadingImage"
                                class="flex-1 text-white px-6 py-3 rounded-lg font-medium focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed"
                                style="
                                    background: linear-gradient(
                                        to right,
                                        #01902d,
                                        #22c55e
                                    );
                                "
                            >
                                <svg
                                    v-if="!isLoading"
                                    class="inline w-5 h-5 mr-2"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M5 13l4 4L19 7"
                                    />
                                </svg>
                                <div v-else class="inline-block w-5 h-5 mr-2">
                                    <div
                                        class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"
                                    ></div>
                                </div>
                                {{ isLoading ? "Adding User..." : "Add User" }}
                            </button>
                            <button
                                type="button"
                                @click="resetForm"
                                :disabled="isLoading"
                                class="flex-1 bg-gray-100 text-gray-700 px-6 py-3 rounded-lg font-medium hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <svg
                                    class="inline w-5 h-5 mr-2"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                    />
                                </svg>
                                Reset
                            </button>
                        </div>
                    </form>
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
        return {
            store,
        };
    },
    data() {
        return {
            showPassword: false,
            isLoading: false,
            uploadingImage: false,
            imagePreview: null,
            selectedImageFile: null,
            positions: [],
            stations: [],
            roles: [],
            user: {
                image: "",
                username: "",
                password: "",
                fullName: "",
                maNV: "",
                chucVu: "",
                tram: "",
                email: "",
                phone: "",
                role: "",
                status: "active",
            },
        };
    },
    computed: {
        getPositionName() {
            return (positionId) => {
                const position = this.positions.find(
                    (p) => p.id_position === positionId
                );
                return position ? position.position : "";
            };
        },
    },
    created() {
        this.fetchPositions();
        this.fetchStations();
        this.fetchRoles();
    },
    methods: {
        // Fetch methods (same as before)
        fetchPositions() {
            axios
                .get("/api/positions", {
                    headers: { Authorization: "Bearer " + this.store.getToken },
                })
                .then((response) => {
                    this.positions = response.data;
                })
                .catch((error) => {
                    console.error("Error fetching positions:", error);
                    this.handleAuthError(error);
                });
        },

        fetchStations() {
            axios
                .get("/api/stations", {
                    headers: { Authorization: "Bearer " + this.store.getToken },
                })
                .then((response) => {
                    this.stations = response.data;
                })
                .catch((error) => {
                    console.error("Error fetching stations:", error);
                    this.handleAuthError(error);
                });
        },

        fetchRoles() {
            axios
                .get("/api/roles", {
                    headers: { Authorization: "Bearer " + this.store.getToken },
                })
                .then((response) => {
                    this.roles = response.data;
                })
                .catch((error) => {
                    console.error("Error fetching roles:", error);
                    this.handleAuthError(error);
                });
        },

        // เพิ่ม method สำหรับสร้าง Cloudinary URL จาก public_id
        getCloudinaryUrl(publicId, transformation = {}) {
            if (!publicId) return null;

            // ถ้าเป็น URL อยู่แล้ว ให้ return ตรงๆ
            if (publicId.includes("http")) {
                return publicId;
            }

            // สร้าง transformation string
            const defaultTransform = {
                width: 200,
                height: 200,
                crop: "fill",
                gravity: "face",
                quality: "auto:good",
            };

            const transform = { ...defaultTransform, ...transformation };
            const transformString = Object.entries(transform)
                .map(([key, value]) => {
                    // แปลง key ให้เป็นรูปแบบ Cloudinary
                    const keyMap = {
                        width: "w",
                        height: "h",
                        crop: "c",
                        gravity: "g",
                        quality: "q",
                    };
                    return `${keyMap[key] || key}_${value}`;
                })
                .join(",");

            // สร้าง URL
            const cloudName = "dhtgcccax"; // จาก CLOUDINARY_CLOUD_NAME
            return `https://res.cloudinary.com/${cloudName}/image/upload/${transformString}/${publicId}`;
        },

        // Image upload methods
        triggerImageUpload() {
            this.$refs.imageInput.click();
        },

        // ปรับปรุง method handleImageSelect
        handleImageSelect(event) {
            const file = event.target.files[0];
            if (file) {
                // Validate file type
                if (!file.type.startsWith("image/")) {
                    this.$swal({
                        icon: "error",
                        title: "Invalid file type",
                        text: "Please select an image file",
                        showConfirmButton: true,
                    });
                    return;
                }

                // Validate file size (5MB max)
                if (file.size > 5 * 1024 * 1024) {
                    this.$swal({
                        icon: "error",
                        title: "File too large",
                        text: "Image size should be less than 5MB",
                        showConfirmButton: true,
                    });
                    return;
                }

                this.selectedImageFile = file;

                // Create preview
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.imagePreview = e.target.result;
                };
                reader.readAsDataURL(file);

                this.$swal({
                    toast: true,
                    position: "top-end",
                    icon: "info",
                    title: "Image ready for upload!",
                    showConfirmButton: false,
                    timer: 1500,
                });
            }
        },

        async uploadImage() {
            if (!this.selectedImageFile) return;

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
                    }
                );

                if (response.data.success) {
                    this.user.image = response.data.image_url;
                    this.$swal({
                        toast: true,
                        position: "top-end",
                        icon: "success",
                        title: "Image uploaded successfully!",
                        showConfirmButton: false,
                        timer: 1500,
                    });
                }
            } catch (error) {
                console.error("Image upload error:", error);
                this.$swal({
                    icon: "error",
                    title: "Upload failed",
                    text: "Failed to upload image. Please try again.",
                    showConfirmButton: true,
                });
                // Reset preview on error
                this.imagePreview = null;
                this.selectedImageFile = null;
            } finally {
                this.uploadingImage = false;
            }
        },

        handleAuthError(error) {
            if (error.response && error.response.status === 401) {
                localStorage.removeItem("web_token");
                localStorage.removeItem("web_user");
                this.store.logout();
                this.$router.push("/login");
            }
        },

        // ปรับปรุง method Saveuser
        async Saveuser() {
            // Validate required fields
            if (
                !this.user.username ||
                !this.user.password ||
                !this.user.fullName ||
                !this.user.chucVu ||
                !this.user.tram ||
                !this.user.role
            ) {
                this.$swal({
                    icon: "warning",
                    title: "Missing Required Fields",
                    text: "Please fill in all required fields marked with *",
                    showConfirmButton: true,
                });
                return;
            }

            if (this.user.password.length < 6) {
                this.$swal({
                    icon: "warning",
                    title: "Password Too Short",
                    text: "Password must be at least 6 characters long",
                    showConfirmButton: true,
                });
                return;
            }

            this.isLoading = true;

            try {
                let imagePublicId = null;

                // อัปโหลดรูปภาพก่อน (ถ้ามี)
                if (this.selectedImageFile) {
                    this.uploadingImage = true;
                    const imageResult = await this.uploadImageSeparately();
                    if (imageResult && imageResult.success) {
                        imagePublicId = imageResult.public_id;
                    } else {
                        // ถ้าอัปโหลดรูปภาพล้มเหลว ให้แสดงข้อความแต่ยังคงสร้าง user
                        this.$swal({
                            icon: "warning",
                            title: "Image Upload Failed",
                            text: "User will be created without profile image. You can add it later.",
                            showConfirmButton: true,
                        });
                    }
                    this.uploadingImage = false;
                }

                // เตรียมข้อมูลสำหรับสร้าง user
                const userData = {
                    username: this.user.username,
                    password: this.user.password,
                    full_name: this.user.fullName,
                    ma_nhan_vien: this.user.maNV || "",
                    position: this.user.chucVu,
                    station: this.user.tram,
                    email: this.user.email || "",
                    phone: this.user.phone || "",
                    role_id: this.user.role,
                    status: this.user.status,
                };

                // เพิ่ม image public_id ถ้ามี
                if (imagePublicId) {
                    userData.image = imagePublicId;
                }

                // สร้าง user
                const response = await axios.post("/api/user/add", userData, {
                    headers: {
                        "Content-Type": "application/json",
                        Authorization: "Bearer " + this.store.getToken,
                    },
                    timeout: 30000,
                });

                this.isLoading = false;

                if (
                    response.data.success &&
                    response.data.message === "success"
                ) {
                    this.$swal({
                        icon: "success",
                        title: "Success!",
                        text: "User created successfully!",
                        showConfirmButton: false,
                        timer: 2000,
                    }).then(() => {
                        this.resetForm();
                        this.$router.push("/admin/users");
                    });
                } else {
                    this.$swal({
                        icon: "error",
                        title: "Error!",
                        text: response.data.message || "Failed to create user",
                        showConfirmButton: true,
                    });
                }
            } catch (err) {
                this.isLoading = false;
                this.uploadingImage = false;
                console.error("Create user error:", err);

                if (err.response) {
                    if (err.response.status === 422) {
                        const errors = err.response.data.errors;
                        let errorMessage =
                            "Please check the following errors:\n";
                        Object.keys(errors).forEach((key) => {
                            errorMessage += `• ${errors[key][0]}\n`;
                        });

                        this.$swal({
                            icon: "error",
                            title: "Validation Error!",
                            text: errorMessage,
                            showConfirmButton: true,
                        });
                    } else if (err.response.status === 401) {
                        this.handleAuthError(err);
                    } else {
                        this.$swal({
                            icon: "error",
                            title: "Error!",
                            text:
                                err.response.data.message ||
                                "An error occurred while creating user",
                            showConfirmButton: true,
                        });
                    }
                } else if (err.code === "ECONNABORTED") {
                    this.$swal({
                        icon: "error",
                        title: "Timeout!",
                        text: "Request timed out. Please try again.",
                        showConfirmButton: true,
                    });
                } else {
                    this.$swal({
                        icon: "error",
                        title: "Network Error!",
                        text: "Please check your internet connection and try again.",
                        showConfirmButton: true,
                    });
                }
            }
        },

        // เพิ่ม method สำหรับอัปโหลดรูปภาพแยกต่างหาก
        async uploadImageSeparately() {
            if (!this.selectedImageFile) return null;

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
                        title: "Image uploaded successfully!",
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
                    title: "Image upload failed",
                    showConfirmButton: false,
                    timer: 2000,
                });
                return null;
            }
        },

        // ลบ method uploadImage เก่าออก และเปลี่ยนชื่อ
        async uploadImage() {
            return await this.uploadImageSeparately();
        },

        updateProfile() {
            this.Saveuser();
        },

        resetForm() {
            this.user = {
                image: "",
                username: "",
                password: "",
                fullName: "",
                maNV: "",
                chucVu: "",
                tram: "",
                email: "",
                phone: "",
                role: "",
                status: "active",
            };
            this.imagePreview = null;
            this.selectedImageFile = null;
            // Reset file input
            if (this.$refs.imageInput) {
                this.$refs.imageInput.value = "";
            }
        },
    },
};
</script>

<style scoped>
/* Loading animation */
#loading-wrapper {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(4px);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.spinner-border {
    width: 3rem;
    height: 3rem;
    color: #10b981;
}

/* Enhanced form styling */
.text-red-500 {
    color: #ef4444;
}

select:focus,
input:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Enhanced button hover effects */
button[type="submit"]:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 10px 20px rgba(1, 144, 45, 0.3);
}

button[type="button"]:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Image upload styling */
.image-upload-container {
    position: relative;
    display: inline-block;
}

.image-preview {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

/* Disable interactions when uploading */
.uploading {
    pointer-events: none;
    opacity: 0.7;
}
</style>
