<template>
    <div class="min-h-screen py-8">
        <!-- Loading Overlay -->
        <div
            v-if="isLoading"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        >
            <div class="bg-white rounded-lg p-6 flex items-center space-x-4">
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
                        class="w-6 h-6 text-gray-600"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>

                <!-- Image Container -->
                <div class="bg-white rounded-lg overflow-hidden shadow-2xl">
                    <div class="relative">
                        <img
                            :src="imagePreview || user.image"
                            alt="Profile Image"
                            class="max-w-full max-h-[70vh] object-contain"
                            @click.stop
                        />
                    </div>

                    <!-- Modal Footer with Delete Button -->
                    <div
                        v-if="user.image && !uploadingImage"
                        class="p-4 bg-gray-50 border-t"
                    >
                        <div class="flex justify-center">
                            <button
                                @click="confirmDeleteImage"
                                class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-lg transition-all duration-200 hover:scale-105 hover:shadow-lg flex items-center space-x-2"
                            >
                                <svg
                                    class="w-4 h-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                    />
                                </svg>
                                <span>Delete Image</span>
                            </button>
                        </div>
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
                    style="
                        background: linear-gradient(to right, #01902d, #22c55e);
                    "
                >
                    <!-- Delete User Button - Top Right -->
                    <button
                        @click="confirmDeleteUser"
                        class="absolute top-4 right-4 bg-red-500 hover:bg-red-600 text-white p-2 rounded-full shadow-lg transition-all duration-200 hover:scale-105 hover:shadow-xl group"
                        title="Delete User"
                    >
                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                            />
                        </svg>
                        <span
                            class="absolute top-full right-0 mt-2 px-2 py-1 bg-black text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap"
                        >
                            Delete User
                        </span>
                    </button>

                    <div
                        class="flex flex-col sm:flex-row items-center space-y-4 sm:space-y-0 sm:space-x-6"
                    >
                        <!-- Avatar with Upload -->
                        <div class="relative">
                            <div
                                class="w-24 h-24 bg-white rounded-full flex items-center justify-center shadow-lg overflow-hidden cursor-pointer hover:shadow-xl transition-shadow"
                                @click="openImageModal"
                                title="Click to view image"
                            >
                                <img
                                    v-if="imagePreview || user.image"
                                    :src="imagePreview || user.image"
                                    alt="Profile"
                                    class="w-full h-full rounded-full object-cover"
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

                            <!-- Upload Button -->
                            <button
                                type="button"
                                @click="triggerImageUpload"
                                :disabled="uploadingImage"
                                class="absolute bottom-0 right-0 bg-white rounded-full p-2 shadow-lg hover:shadow-xl transition-shadow"
                                title="Upload new image"
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
                                {{ user.fullName || "Full Name" }}
                            </h2>
                            <p class="text-blue-100">
                                {{ getPositionName(user.chucVu) || "Position" }}
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
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                    placeholder="Enter username"
                                />
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
                                    required
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
                                    maxlength="6"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                    placeholder="Enter employee ID (e.g., 000123)"
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
                                    Chức vụ<span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="user.chucVu"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                >
                                    <option value="">Select position</option>
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
                                    Trạm<span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="user.tram"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
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
                                    Role<span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="user.role"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                >
                                    <option value="">Select role</option>
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

                        <!-- Password Change Section -->
                        <div class="border-t border-gray-200 pt-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                Thay đổi mật khẩu
                            </h3>
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                                <!-- Current Password -->
                                <div class="space-y-2">
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        Mật khẩu cũ
                                    </label>
                                    <div class="relative">
                                        <input
                                            v-model="
                                                passwordForm.currentPassword
                                            "
                                            :type="
                                                showCurrentPassword
                                                    ? 'text'
                                                    : 'password'
                                            "
                                            class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                            placeholder="Enter current password"
                                        />
                                        <button
                                            type="button"
                                            @click="
                                                showCurrentPassword =
                                                    !showCurrentPassword
                                            "
                                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                        >
                                            <svg
                                                v-if="showCurrentPassword"
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

                                <!-- New Password -->
                                <div class="space-y-2">
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        Mật khẩu mới
                                    </label>
                                    <div class="relative">
                                        <input
                                            v-model="passwordForm.newPassword"
                                            :type="
                                                showNewPassword
                                                    ? 'text'
                                                    : 'password'
                                            "
                                            class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                            placeholder="Enter new password"
                                        />
                                        <button
                                            type="button"
                                            @click="
                                                showNewPassword =
                                                    !showNewPassword
                                            "
                                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                        >
                                            <svg
                                                v-if="showNewPassword"
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

                                <!-- Confirm Password -->
                                <div class="space-y-2">
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        Xác nhận mật khẩu mới
                                    </label>
                                    <div class="relative">
                                        <input
                                            v-model="
                                                passwordForm.confirmPassword
                                            "
                                            :type="
                                                showConfirmPassword
                                                    ? 'text'
                                                    : 'password'
                                            "
                                            class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                            placeholder="Confirm new password"
                                        />
                                        <button
                                            type="button"
                                            @click="
                                                showConfirmPassword =
                                                    !showConfirmPassword
                                            "
                                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                        >
                                            <svg
                                                v-if="showConfirmPassword"
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
                            </div>
                            <div
                                v-if="
                                    passwordForm.currentPassword ||
                                    passwordForm.newPassword ||
                                    passwordForm.confirmPassword
                                "
                                class="mt-2"
                            >
                                <small class="text-blue-600">
                                    📝 Nếu bạn muốn thay đổi mật khẩu, vui lòng
                                    điền vào cả 3 trường
                                </small>
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
                                {{
                                    isLoading ? "Updating..." : "Update Profile"
                                }}
                            </button>
                            <button
                                type="button"
                                @click="resetForm"
                                :disabled="isLoading"
                                class="flex-1 bg-gray-100 text-gray-700 px-6 py-3 rounded-lg font-medium hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
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
        return { store };
    },

    data() {
        return {
            isLoading: false,
            loadingMessage: "Processing...",
            uploadingImage: false,
            imagePreview: null,
            selectedImageFile: null,
            showImageModal: false, // Add this new data property

            // Password visibility toggles
            showCurrentPassword: false,
            showNewPassword: false,
            showConfirmPassword: false,

            // Data arrays
            positions: [],
            stations: [],
            roles: [],

            // User data
            user: {
                id: null,
                image: "",
                image_public_id: "",
                username: "",
                fullName: "",
                maNV: "",
                chucVu: "",
                tram: "",
                email: "",
                phone: "",
                role: "",
                status: "active",
            },

            // Password form data
            passwordForm: {
                currentPassword: "",
                newPassword: "",
                confirmPassword: "",
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

    async created() {
        await this.initializeComponent();
    },

    methods: {
        // Add new methods for modal handling
        openImageModal() {
            if (this.imagePreview || this.user.image) {
                this.showImageModal = true;
                // Prevent body scroll when modal is open
                document.body.style.overflow = "hidden";
            }
        },

        closeImageModal() {
            this.showImageModal = false;
            // Restore body scroll
            document.body.style.overflow = "auto";
        },
        async initializeComponent() {
            this.isLoading = true;
            this.loadingMessage = "Loading user data...";

            try {
                // Load all necessary data
                await Promise.all([
                    this.fetchPositions(),
                    this.fetchStations(),
                    this.fetchRoles(),
                    this.loadUserData(),
                ]);
            } catch (error) {
                console.error("Error initializing component:", error);
                this.handleAuthError(error);
            } finally {
                this.isLoading = false;
            }
        },

        async loadUserData() {
            const userId = this.$route.params.id;
            if (!userId) {
                this.$router.push("/User");
                return;
            }

            try {
                const response = await axios.get(`/api/user/edit/${userId}`, {
                    headers: { Authorization: "Bearer " + this.store.getToken },
                });

                const userData = response.data;

                // Map backend data to frontend model
                this.user = {
                    id: userData.id,
                    image: userData.image,
                    image_public_id: userData.image_public_id,
                    username: userData.username,
                    fullName: userData.full_name,
                    maNV: userData.ma_nhan_vien,
                    chucVu: userData.position,
                    tram: userData.station,
                    email: userData.email,
                    phone: userData.phone,
                    role: userData.role_id,
                    status: userData.status,
                };
            } catch (error) {
                console.error("Error loading user data:", error);
                this.handleAuthError(error);
            }
        },

        // Data fetching methods
        async fetchPositions() {
            try {
                const response = await axios.get("/api/positions", {
                    headers: { Authorization: "Bearer " + this.store.getToken },
                });
                this.positions = response.data;
            } catch (error) {
                console.error("Error fetching positions:", error);
                this.handleAuthError(error);
            }
        },

        async fetchStations() {
            try {
                const response = await axios.get("/api/stations", {
                    headers: { Authorization: "Bearer " + this.store.getToken },
                });
                this.stations = response.data;
            } catch (error) {
                console.error("Error fetching stations:", error);
                this.handleAuthError(error);
            }
        },

        async fetchRoles() {
            try {
                const response = await axios.get("/api/roles", {
                    headers: { Authorization: "Bearer " + this.store.getToken },
                });
                this.roles = response.data;
            } catch (error) {
                console.error("Error fetching roles:", error);
                this.handleAuthError(error);
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
            } finally {
                this.uploadingImage = false;
            }
        },
        // Delete methods
        async confirmDeleteUser() {
            try {
                const result = await this.$swal({
                    title: "Delete User?",
                    text: "Are you sure you want to delete this user? This action cannot be undone.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#ef4444",
                    cancelButtonColor: "#6b7280",
                    confirmButtonText: "Yes, delete it!",
                    cancelButtonText: "Cancel",
                    reverseButtons: true,
                });

                if (result.isConfirmed) {
                    await this.deleteUser();
                }
            } catch (error) {
                console.error("Error in confirmDeleteUser:", error);
            }
        },

        async deleteUser() {
            this.isLoading = true;
            this.loadingMessage = "Deleting user...";

            try {
                const response = await axios.delete(
                    `/api/user/delete/${this.user.id}`,
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
                        title: "Deleted!",
                        text: "User has been deleted successfully.",
                        showConfirmButton: false,
                        timer: 2000,
                    }).then(() => {
                        this.$router.push("/User");
                    });
                } else {
                    this.$swal({
                        icon: "error",
                        title: "Delete Failed!",
                        text: response.data.message || "Failed to delete user",
                        showConfirmButton: true,
                    });
                }
            } catch (error) {
                console.error("Delete user error:", error);

                let errorMessage = "An error occurred while deleting the user";

                if (error.response) {
                    if (error.response.status === 403) {
                        errorMessage = "You cannot delete your own account";
                    } else if (error.response.status === 404) {
                        errorMessage = "User not found";
                    } else if (error.response.status === 401) {
                        this.handleAuthError(error);
                        return;
                    } else {
                        errorMessage =
                            error.response.data.message || errorMessage;
                    }
                } else if (error.code === "ECONNABORTED") {
                    errorMessage = "Request timed out. Please try again.";
                }

                this.$swal({
                    icon: "error",
                    title: "Delete Failed!",
                    text: errorMessage,
                    showConfirmButton: true,
                });
            } finally {
                this.isLoading = false;
                this.loadingMessage = "Processing...";
            }
        },

        async confirmDeleteImage() {
            // Close modal first
            this.closeImageModal();
            try {
                const result = await this.$swal({
                    title: "Delete Image?",
                    text: "Are you sure you want to delete this image?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#ef4444",
                    cancelButtonColor: "#6b7280",
                    confirmButtonText: "Yes, delete it!",
                    cancelButtonText: "Cancel",
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
                const response = await axios.delete(
                    `/api/user/${this.user.id}/delete-image`,
                    {
                        headers: {
                            Authorization: "Bearer " + this.store.getToken,
                        },
                        timeout: 30000,
                    }
                );

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
                        title: "Image deleted successfully!",
                        showConfirmButton: false,
                        timer: 1500,
                    });
                } else {
                    this.$swal({
                        icon: "error",
                        title: "Delete Failed!",
                        text: response.data.message || "Failed to delete image",
                        showConfirmButton: true,
                    });
                }
            } catch (error) {
                console.error("Delete image error:", error);

                let errorMessage = "An error occurred while deleting the image";

                if (error.response) {
                    if (error.response.status === 400) {
                        errorMessage = "User has no image to delete";
                    } else if (error.response.status === 404) {
                        errorMessage = "User not found";
                    } else if (error.response.status === 401) {
                        this.handleAuthError(error);
                        return;
                    } else {
                        errorMessage =
                            error.response.data.message || errorMessage;
                    }
                } else if (error.code === "ECONNABORTED") {
                    errorMessage = "Request timed out. Please try again.";
                }

                this.$swal({
                    icon: "error",
                    title: "Delete Failed!",
                    text: errorMessage,
                    showConfirmButton: true,
                });
            } finally {
                this.uploadingImage = false;
            }
        },

        // Validation methods
        validateForm() {
            // Basic validation
            if (
                !this.user.username ||
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
                return false;
            }

            // Email validation
            if (this.user.email && !this.isValidEmail(this.user.email)) {
                this.$swal({
                    icon: "warning",
                    title: "Invalid Email",
                    text: "Please enter a valid email address",
                    showConfirmButton: true,
                });
                return false;
            }

            // Password validation
            if (
                this.passwordForm.currentPassword ||
                this.passwordForm.newPassword ||
                this.passwordForm.confirmPassword
            ) {
                if (!this.passwordForm.currentPassword) {
                    this.$swal({
                        icon: "warning",
                        title: "Current Password Required",
                        text: "Please enter your current password to change it",
                        showConfirmButton: true,
                    });
                    return false;
                }

                if (!this.passwordForm.newPassword) {
                    this.$swal({
                        icon: "warning",
                        title: "New Password Required",
                        text: "Please enter your new password",
                        showConfirmButton: true,
                    });
                    return false;
                }

                if (this.passwordForm.newPassword.length < 6) {
                    this.$swal({
                        icon: "warning",
                        title: "Password Too Short",
                        text: "New password must be at least 6 characters long",
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
                        title: "Password Mismatch",
                        text: "New password and confirmation do not match",
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

        // Main update method
        async updateProfile() {
            if (!this.validateForm()) return;

            this.isLoading = true;
            this.loadingMessage = "Updating profile...";

            try {
                let imagePublicId = this.user.image_public_id;

                // Upload new image if selected
                if (this.selectedImageFile) {
                    this.loadingMessage = "Uploading image...";
                    const imageResult = await this.uploadImageSeparately();
                    if (imageResult && imageResult.success) {
                        imagePublicId = imageResult.public_id;
                    } else {
                        this.$swal({
                            icon: "warning",
                            title: "Image Upload Failed",
                            text: "Profile will be updated without new image. You can try uploading again later.",
                            showConfirmButton: true,
                        });
                    }
                }

                // Prepare update data
                const updateData = {
                    username: this.user.username,
                    full_name: this.user.fullName,
                    ma_nhan_vien: this.user.maNV || "",
                    position: this.user.chucVu,
                    station: this.user.tram,
                    email: this.user.email || "",
                    phone: this.user.phone || "",
                    role_id: this.user.role,
                    status: this.user.status,
                };

                // Add image if available
                if (imagePublicId) {
                    updateData.image = imagePublicId;
                }

                // Add password fields if provided
                if (this.passwordForm.currentPassword) {
                    updateData.current_password =
                        this.passwordForm.currentPassword;
                    updateData.new_password = this.passwordForm.newPassword;
                    updateData.confirm_password =
                        this.passwordForm.confirmPassword;
                }

                this.loadingMessage = "Saving changes...";

                // Send update request
                const response = await axios.put(
                    `/api/user/update/${this.user.id}`,
                    updateData,
                    {
                        headers: {
                            "Content-Type": "application/json",
                            Authorization: "Bearer " + this.store.getToken,
                        },
                        timeout: 30000,
                    }
                );

                if (response.data.success) {
                    this.$swal({
                        icon: "success",
                        title: "Success!",
                        text: "User profile updated successfully!",
                        showConfirmButton: false,
                        timer: 2000,
                    }).then(() => {
                        // Clear password form
                        this.clearPasswordForm();
                        // Clear image upload state
                        this.clearImageUploadState();
                        // Optionally redirect
                        this.$router.push("/User");
                    });
                } else {
                    this.$swal({
                        icon: "error",
                        title: "Update Failed!",
                        text:
                            response.data.message ||
                            "Failed to update user profile",
                        showConfirmButton: true,
                    });
                }
            } catch (err) {
                console.error("Update user error:", err);

                let errorMessage =
                    "An error occurred while updating the profile";

                if (err.response) {
                    if (err.response.status === 422) {
                        if (
                            err.response.data.message ===
                            "รหัสผ่านเก่าไม่ถูกต้อง"
                        ) {
                            errorMessage =
                                "รหัสผ่านเก่าไม่ถูกต้อง กรุณาตรวจสอบและลองใหม่อีกครั้ง";
                        } else {
                            const errors = err.response.data.errors;
                            if (errors) {
                                errorMessage = "Validation errors:\n";
                                Object.keys(errors).forEach((key) => {
                                    errorMessage += `• ${errors[key][0]}\n`;
                                });
                            }
                        }
                    } else if (err.response.status === 401) {
                        this.handleAuthError(err);
                        return;
                    } else {
                        errorMessage =
                            err.response.data.message || errorMessage;
                    }
                } else if (err.code === "ECONNABORTED") {
                    errorMessage = "Request timed out. Please try again.";
                }

                this.$swal({
                    icon: "error",
                    title: "Update Failed!",
                    text: errorMessage,
                    showConfirmButton: true,
                });
            } finally {
                this.isLoading = false;
                this.loadingMessage = "Processing...";
            }
        },

        // Helper methods
        clearPasswordForm() {
            this.passwordForm = {
                currentPassword: "",
                newPassword: "",
                confirmPassword: "",
            };
            this.showCurrentPassword = false;
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
            this.$swal({
                title: "Reset Form?",
                text: "Are you sure you want to reset all changes?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, reset it!",
                cancelButtonText: "Cancel",
            }).then((result) => {
                if (result.isConfirmed) {
                    this.loadUserData();
                    this.clearPasswordForm();
                    this.clearImageUploadState();

                    this.$swal({
                        toast: true,
                        position: "top-end",
                        icon: "success",
                        title: "Form reset successfully!",
                        showConfirmButton: false,
                        timer: 1500,
                    });
                }
            });
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
</style>
