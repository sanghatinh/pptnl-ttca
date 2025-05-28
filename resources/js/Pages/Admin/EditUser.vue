<template>
    <div class="min-h-screen py-8">
        <div class="max-w-6xl mx-auto px-2 sm:px-6 lg:px-8">
            <!-- Header -->
            <!-- <div class="text-left mb-3">
                <h1 class="text-3xl font-bold mb-2" style="color: #01902d">
                    User Profile
                </h1>
            </div> -->

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
                        <!-- Avatar -->
                        <div class="relative">
                            <div
                                class="w-24 h-24 bg-white rounded-full flex items-center justify-center shadow-lg"
                            >
                                <img
                                    v-if="user.image"
                                    :src="user.image"
                                    alt="Profile"
                                    class="w-22 h-22 rounded-full object-cover"
                                />
                                <svg
                                    v-else
                                    class="w-12 h-12 text-gray-400"
                                    fill="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"
                                    />
                                </svg>
                            </div>
                            <button
                                class="absolute bottom-0 right-0 bg-white rounded-full p-2 shadow-lg hover:shadow-xl transition-shadow"
                            >
                                <svg
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
                            </button>
                        </div>

                        <!-- Basic Info -->
                        <div class="text-center sm:text-left text-white">
                            <h2 class="text-2xl font-bold">
                                {{ user.fullName || "Full Name" }}
                            </h2>
                            <p class="text-blue-100">
                                {{ user.chucVu || "Position" }}
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
                                    Username
                                </label>
                                <input
                                    v-model="user.username"
                                    type="text"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                    placeholder="Enter username"
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
                                    Password
                                </label>
                                <div class="relative">
                                    <input
                                        v-model="user.password"
                                        :type="
                                            showPassword ? 'text' : 'password'
                                        "
                                        class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                        placeholder="Enter password"
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
                                    Full Name
                                </label>
                                <input
                                    v-model="user.fullName"
                                    type="text"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                    placeholder="Enter full name"
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
                                    placeholder="Enter employee ID"
                                />
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
                                    Chức vụ
                                </label>
                                <select
                                    v-model="user.chucVu"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                >
                                    <option value="">Select position</option>
                                    <option value="Manager">Manager</option>
                                    <option value="Staff">Staff</option>
                                    <option value="Supervisor">
                                        Supervisor
                                    </option>
                                    <option value="Administrator">
                                        Administrator
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
                                    Trạm
                                </label>
                                <input
                                    v-model="user.tram"
                                    type="text"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                    placeholder="Enter station"
                                />
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
                                    Role
                                </label>
                                <select
                                    v-model="user.role"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                >
                                    <option value="">Select role</option>
                                    <option value="admin">Admin</option>
                                    <option value="user">User</option>
                                    <option value="moderator">Moderator</option>
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
                                    Status
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
                                class="flex-1 text-white px-6 py-3 rounded-lg font-medium focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl"
                                style="background: linear-gradient(to right, #01902d, #22c55e); hover:background: linear-gradient(to right, #016a20, #16a34a);"
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
                                        d="M5 13l4 4L19 7"
                                    />
                                </svg>
                                Update Profile
                            </button>
                            <button
                                type="button"
                                @click="resetForm"
                                class="flex-1 bg-gray-100 text-gray-700 px-6 py-3 rounded-lg font-medium hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200"
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
export default {
    data() {
        return {
            showPassword: false,
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
    methods: {
        updateProfile() {
            // Handle form submission
            console.log("Updating profile:", this.user);
            // Add your update logic here
        },
        resetForm() {
            // Reset form to initial state
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
        },
    },
};
</script>

<style scoped>
/* Additional custom styles if needed */
</style>
