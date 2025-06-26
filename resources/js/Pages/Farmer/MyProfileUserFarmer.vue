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
                                :src="imagePreview || farmer.image"
                                alt="Profile Image"
                                class="max-w-full max-h-[70vh] object-contain"
                                @click.stop
                            />
                        </div>

                        <!-- Modal Footer with Delete Button -->
                        <div
                            v-if="farmer.image && !uploadingImage"
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
                    <!-- Header Section -->
                    <div
                        class="bg-gradient-to-r from-green-600 to-green-500 px-6 py-8 sm:px-8 relative"
                        style="
                            background: linear-gradient(
                                to right,
                                #01902d,
                                #22c55e
                            );
                        "
                    >
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
                                        v-if="imagePreview || farmer.image"
                                        :src="imagePreview || farmer.image"
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
                                    {{
                                        farmer.khach_hang_ca_nhan ||
                                        farmer.khach_hang_doanh_nghiep ||
                                        "Farmer User"
                                    }}
                                </h2>
                                <p class="text-blue-100">
                                    Supplier Number:
                                    {{
                                        farmer.supplier_number ||
                                        "Supplier Number"
                                    }}
                                </p>
                                <p class="text-blue-200 text-sm">
                                    Mã KH:
                                    {{
                                        farmer.ma_kh_ca_nhan ||
                                        farmer.ma_kh_doanh_nghiep ||
                                        "Customer Code"
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Form Section -->
                    <div class="px-3 py-8 sm:px-8">
                        <form @submit.prevent="updateProfile" class="space-y-6">
                            <!-- Grid Layout for Form Fields -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <!-- Trạm (Read-only) -->
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
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                            />
                                        </svg>
                                        Trạm
                                    </label>
                                    <input
                                        v-model="farmer.tram"
                                        type="text"
                                        readonly
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed"
                                        placeholder="Station"
                                    />
                                </div>

                                <!-- Nhân viên (Read-only) -->
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
                                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                            />
                                        </svg>
                                        Nhân viên phụ trách
                                    </label>
                                    <input
                                        :value="
                                            farmer.employee_name ||
                                            'Chưa có nhân viên phụ trách'
                                        "
                                        type="text"
                                        readonly
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed"
                                    />
                                    <small class="text-gray-500 text-sm">
                                        Mã NV:
                                        {{ farmer.ma_nhan_vien || "N/A" }}
                                    </small>
                                </div>

                                <!-- Supplier Number (Read-only) -->
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
                                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
                                            />
                                        </svg>
                                        Supplier Number
                                    </label>
                                    <input
                                        v-model="farmer.supplier_number"
                                        type="text"
                                        readonly
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed"
                                        placeholder="Supplier number"
                                    />
                                </div>

                                <!-- Mã KH cá nhân (Read-only) -->
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
                                        Mã KH cá nhân
                                    </label>
                                    <input
                                        v-model="farmer.ma_kh_ca_nhan"
                                        type="text"
                                        readonly
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed"
                                        placeholder="Individual customer code"
                                    />
                                </div>

                                <!-- Khách hàng cá nhân (Read-only) -->
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
                                        Khách hàng cá nhân
                                    </label>
                                    <input
                                        v-model="farmer.khach_hang_ca_nhan"
                                        type="text"
                                        readonly
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed"
                                        placeholder="Individual customer name"
                                    />
                                </div>

                                <!-- Mã KH doanh nghiệp (Read-only) -->
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
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                            />
                                        </svg>
                                        Mã KH doanh nghiệp
                                    </label>
                                    <input
                                        v-model="farmer.ma_kh_doanh_nghiep"
                                        type="text"
                                        readonly
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed"
                                        placeholder="Business customer code"
                                    />
                                </div>

                                <!-- Khách hàng doanh nghiệp (Read-only) -->
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
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                            />
                                        </svg>
                                        Khách hàng doanh nghiệp
                                    </label>
                                    <input
                                        v-model="farmer.khach_hang_doanh_nghiep"
                                        type="text"
                                        readonly
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed"
                                        placeholder="Business customer name"
                                    />
                                </div>

                                <!-- Số điện thoại (Editable) -->
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
                                        Số điện thoại
                                    </label>
                                    <input
                                        v-model="farmer.phone"
                                        type="tel"
                                        maxlength="11"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                        placeholder="Enter phone number"
                                    />
                                </div>

                                <!-- Địa chỉ thường trú (Editable) -->
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
                                        Địa chỉ thường trú
                                    </label>
                                    <textarea
                                        v-model="farmer.dia_chi_thuong_tru"
                                        rows="3"
                                        maxlength="500"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                        placeholder="Enter permanent address"
                                    ></textarea>
                                </div>
                            </div>

                            <!-- Banking Information Section (All Editable) -->
                            <div class="border-t border-gray-200 pt-6">
                                <h3
                                    class="text-lg font-medium text-gray-900 mb-4"
                                >
                                    Thông tin tài khoản ngân hàng
                                </h3>
                                <div
                                    class="grid grid-cols-1 lg:grid-cols-2 gap-6"
                                >
                                    <!-- Chủ tài khoản (Editable) -->
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
                                            Chủ tài khoản
                                        </label>
                                        <input
                                            v-model="farmer.chu_tai_khoan"
                                            type="text"
                                            maxlength="255"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                            placeholder="Enter account holder name"
                                        />
                                    </div>

                                    <!-- Ngân hàng (Editable) -->
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
                                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                                />
                                            </svg>
                                            Ngân hàng
                                        </label>
                                        <select
                                            v-model="farmer.ngan_hang"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                        >
                                            <option value="">
                                                Chọn ngân hàng
                                            </option>
                                            <option
                                                v-for="bank in banks"
                                                :key="bank.code_banking"
                                                :value="bank.code_banking"
                                            >
                                                {{ bank.name_banking }}
                                            </option>
                                        </select>
                                    </div>

                                    <!-- Số tài khoản (Editable) -->
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
                                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
                                                />
                                            </svg>
                                            Số tài khoản
                                        </label>
                                        <input
                                            v-model="farmer.so_tai_khoan"
                                            type="text"
                                            maxlength="50"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                            placeholder="Enter account number"
                                        />
                                    </div>

                                    <!-- Role (Read-only) -->
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
                                        <input
                                            :value="getRoleName(farmer.role_id)"
                                            type="text"
                                            readonly
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed"
                                            placeholder="Role"
                                        />
                                    </div>

                                    <!-- Status (Read-only) -->
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
                                            Trạng thái
                                        </label>
                                        <input
                                            :value="
                                                farmer.status === 'active'
                                                    ? 'Hoạt động'
                                                    : 'Không hoạt động'
                                            "
                                            type="text"
                                            readonly
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed"
                                            placeholder="Status"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Password Change Section -->
                            <div class="border-t border-gray-200 pt-6">
                                <h3
                                    class="text-lg font-medium text-gray-900 mb-4"
                                >
                                    Thay đổi mật khẩu
                                </h3>
                                <div
                                    class="grid grid-cols-1 lg:grid-cols-2 gap-4"
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
                                                v-model="
                                                    passwordForm.newPassword
                                                "
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
                                                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                            >
                                                <svg
                                                    v-if="!showNewPassword"
                                                    class="h-5 w-5 text-gray-400"
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
                                                    class="h-5 w-5 text-gray-400"
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
                                                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                            >
                                                <svg
                                                    v-if="!showConfirmPassword"
                                                    class="h-5 w-5 text-gray-400"
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
                                                    class="h-5 w-5 text-gray-400"
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
                                        passwordForm.newPassword ||
                                        passwordForm.confirmPassword
                                    "
                                    class="mt-2"
                                >
                                    <small class="text-blue-600">
                                        📝 Nếu bạn muốn thay đổi mật khẩu, vui
                                        lòng điền vào cả 2 trường
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
                                    <div
                                        v-else
                                        class="inline-block w-5 h-5 mr-2"
                                    >
                                        <div
                                            class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"
                                        ></div>
                                    </div>
                                    {{
                                        isLoading
                                            ? loadingMessage
                                            : "Cập nhật hồ sơ"
                                    }}
                                </button>

                                <button
                                    type="button"
                                    @click="resetForm"
                                    :disabled="isLoading"
                                    class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed"
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
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                    Hủy
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
import Swal from "sweetalert2";

export default {
    setup() {
        const store = useStore();
        return { store };
    },

    data() {
        return {
            isLoading: false,
            loadingMessage: "Processing...",
            loadingEmployees: false,

            // Password visibility toggles
            showNewPassword: false,
            showConfirmPassword: false,
            // Image handling
            uploadingImage: false,
            imagePreview: null,
            selectedImageFile: null,
            showImageModal: false,

            // Data arrays
            stations: [],
            employees: [],
            banks: [],
            roles: [],

            // Farmer data
            farmer: {
                id: null,
                tram: "",
                ma_nhan_vien: "",
                supplier_number: "",
                ma_kh_ca_nhan: "",
                khach_hang_ca_nhan: "",
                ma_kh_doanh_nghiep: "",
                khach_hang_doanh_nghiep: "",
                phone: "",
                dia_chi_thuong_tru: "",
                chu_tai_khoan: "",
                ngan_hang: "",
                so_tai_khoan: "",
                role_id: "",
                status: "active",
                image: "",
                image_public_id: "",
            },

            // Password form data
            passwordForm: {
                newPassword: "",
                confirmPassword: "",
            },

            // Image upload states
            showImageModal: false,
            imagePreview: null,
            uploadingImage: false,
        };
    },

    computed: {
        // Helper method to get role name
        getRoleName() {
            return (roleId) => {
                const role = this.roles.find((r) => r.id == roleId);
                return role ? role.name : "N/A";
            };
        },
    },

    async created() {
        await this.initializeComponent();
    },

    methods: {
        async initializeComponent() {
            this.isLoading = true;
            this.loadingMessage = "Loading profile data...";

            try {
                // Load all necessary data
                await Promise.all([
                    this.fetchBanks(),
                    // this.fetchRoles(),
                    this.loadMyProfileData(),
                ]);
            } catch (error) {
                console.error("Error initializing component:", error);
                this.handleAuthError(error);
            } finally {
                this.isLoading = false;
            }
        },

        async loadMyProfileData() {
            try {
                // Use store's getAuthHeaders for consistent header management
                const headers = this.store.getAuthHeaders();
                const response = await axios.get("/api/farmer/my-profile", {
                    headers,
                });

                if (response.data.success) {
                    const farmerData = response.data.data;

                    // Map backend data to frontend model
                    this.farmer = {
                        id: farmerData.id,
                        image: farmerData.image_url || "",
                        image_public_id: farmerData.url_image || "",
                        tram: farmerData.tram || "",
                        ma_nhan_vien: farmerData.ma_nhan_vien || "",
                        employee_name: farmerData.employee_name || "",
                        supplier_number: farmerData.supplier_number || "",
                        ma_kh_ca_nhan: farmerData.ma_kh_ca_nhan || "",
                        khach_hang_ca_nhan: farmerData.khach_hang_ca_nhan || "",
                        ma_kh_doanh_nghiep: farmerData.ma_kh_doanh_nghiep || "",
                        khach_hang_doanh_nghiep:
                            farmerData.khach_hang_doanh_nghiep || "",
                        phone: farmerData.phone || "",
                        dia_chi_thuong_tru: farmerData.dia_chi_thuong_tru || "",
                        chu_tai_khoan: farmerData.chu_tai_khoan || "",
                        ngan_hang: farmerData.ngan_hang || "",
                        so_tai_khoan: farmerData.so_tai_khoan || "",
                        role_id: farmerData.role_id || "",
                        status: farmerData.status || "active",
                    };
                } else {
                    throw new Error(
                        response.data.message || "Failed to load profile data"
                    );
                }
            } catch (error) {
                console.error("Error loading profile data:", error);

                if (error.response && error.response.status === 401) {
                    this.handleAuthError(error);
                } else {
                    this.$swal({
                        icon: "error",
                        title: "Lỗi tải dữ liệu",
                        text: "Không thể tải thông tin hồ sơ",
                        showConfirmButton: true,
                    });
                }
            }
        },

        // Data fetching methods
        async fetchBanks() {
            try {
                const headers = this.store.getAuthHeaders();
                const response = await axios.get("/api/farmer/banks", {
                    headers,
                });

                if (response.data.success) {
                    this.banks = response.data.data;
                } else {
                    this.banks = [];
                }
            } catch (error) {
                console.error("Error fetching banks:", error);
                this.banks = [];
                this.handleAuthError(error);
            }
        },

        // async fetchRoles() {
        //     try {
        //         const response = await axios.get("/api/roles", {
        //             headers: { Authorization: "Bearer " + this.store.getToken },
        //         });
        //         this.roles = response.data;
        //     } catch (error) {
        //         console.error("Error fetching roles:", error);
        //         this.roles = [];
        //         this.handleAuthError(error);
        //     }
        // },

        // Validation methods
        validateForm() {
            // Clear previous validation errors
            this.validationErrors = {};

            // Password validation - only validate if password fields are filled
            if (
                this.passwordForm.newPassword ||
                this.passwordForm.confirmPassword
            ) {
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
                        text: "Mật khẩu mới và xác nhận mật khẩu không khớp",
                        showConfirmButton: true,
                    });
                    return false;
                }
            }

            // Validate bank code length
            if (this.farmer.ngan_hang && this.farmer.ngan_hang.length > 50) {
                this.$swal({
                    icon: "warning",
                    title: "Mã ngân hàng không hợp lệ",
                    text: "Mã ngân hàng không được vượt quá 50 ký tự",
                    showConfirmButton: true,
                });
                return false;
            }

            // Validate phone number
            if (this.farmer.phone && this.farmer.phone.length > 20) {
                this.$swal({
                    icon: "warning",
                    title: "Số điện thoại quá dài",
                    text: "Số điện thoại không được vượt quá 20 ký tự",
                    showConfirmButton: true,
                });
                return false;
            }

            // Validate account number
            if (
                this.farmer.so_tai_khoan &&
                this.farmer.so_tai_khoan.length > 50
            ) {
                this.$swal({
                    icon: "warning",
                    title: "Số tài khoản quá dài",
                    text: "Số tài khoản không được vượt quá 50 ký tự",
                    showConfirmButton: true,
                });
                return false;
            }

            // Validate account holder name
            if (
                this.farmer.chu_tai_khoan &&
                this.farmer.chu_tai_khoan.length > 255
            ) {
                this.$swal({
                    icon: "warning",
                    title: "Tên chủ tài khoản quá dài",
                    text: "Tên chủ tài khoản không được vượt quá 255 ký tự",
                    showConfirmButton: true,
                });
                return false;
            }

            return true;
        },

        // Image handling methods
        openImageModal() {
            if (this.imagePreview || this.farmer.image) {
                this.showImageModal = true;
                document.body.style.overflow = "hidden";
            }
        },

        closeImageModal() {
            this.showImageModal = false;
            document.body.style.overflow = "auto";
        },

        triggerImageUpload() {
            this.$refs.imageInput.click();
        },

        handleImageSelect(event) {
            const file = event.target.files[0];
            if (file) {
                // Validate file type
                if (!file.type.startsWith("image/")) {
                    this.$swal({
                        icon: "warning",
                        title: "Định dạng file không hợp lệ",
                        text: "Vui lòng chọn file hình ảnh (JPG, PNG, GIF, WEBP)",
                        showConfirmButton: true,
                    });
                    return;
                }

                // Validate file size (5MB max)
                if (file.size > 5 * 1024 * 1024) {
                    this.$swal({
                        icon: "warning",
                        title: "File quá lớn",
                        text: "Kích thước file không được vượt quá 5MB",
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
                    "/api/farmer/upload-image",
                    formData,
                    {
                        headers: {
                            "Content-Type": "multipart/form-data",
                            Authorization: "Bearer " + this.store.getToken,
                        },
                        timeout: 30000, // 30 seconds timeout
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
                    await this.deleteFarmerImage();
                }
            } catch (error) {
                console.error("Error in confirmDeleteImage:", error);
            }
        },

        async deleteFarmerImage() {
            this.uploadingImage = true;

            try {
                const response = await axios.delete(
                    "/api/farmer/delete-image",
                    {
                        headers: {
                            Authorization: "Bearer " + this.store.getToken,
                        },
                    }
                );

                if (response.data.success) {
                    // Update local state
                    this.farmer.image = "";
                    this.farmer.image_public_id = "";
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
                        text: "Không thể xóa hình ảnh",
                        showConfirmButton: true,
                    });
                }
            } catch (error) {
                console.error("Delete image error:", error);
                this.$swal({
                    icon: "error",
                    title: "Xóa thất bại!",
                    text: "Đã xảy ra lỗi khi xóa hình ảnh",
                    showConfirmButton: true,
                });
            } finally {
                this.uploadingImage = false;
            }
        },

        clearImageUploadState() {
            this.imagePreview = null;
            this.selectedImageFile = null;
            if (this.$refs.imageInput) {
                this.$refs.imageInput.value = "";
            }
        },

        // Main update method
        async updateProfile() {
            if (!this.validateForm()) return;

            this.isLoading = true;
            this.loadingMessage = "Đang cập nhật hồ sơ...";

            try {
                let imagePublicId = this.farmer.image_public_id;

                // Upload new image if selected
                if (this.selectedImageFile) {
                    this.loadingMessage = "Đang tải lên hình ảnh...";
                    const imageResult = await this.uploadImageSeparately();
                    if (imageResult && imageResult.success) {
                        imagePublicId = imageResult.public_id;
                    } else {
                        throw new Error("Failed to upload image");
                    }
                }

                // Prepare update data (only editable fields)
                const updateData = {
                    phone: this.farmer.phone || "",
                    dia_chi_thuong_tru: this.farmer.dia_chi_thuong_tru || "",
                    chu_tai_khoan: this.farmer.chu_tai_khoan || "",
                    ngan_hang: this.farmer.ngan_hang || "",
                    so_tai_khoan: this.farmer.so_tai_khoan || "",
                };

                // Add password only if provided
                if (
                    this.passwordForm.newPassword &&
                    this.passwordForm.newPassword.trim() !== ""
                ) {
                    updateData.password = this.passwordForm.newPassword;
                }

                // Add image if available
                if (imagePublicId) {
                    updateData.url_image = imagePublicId;
                }

                this.loadingMessage = "Đang lưu thay đổi...";

                // Send update request
                const response = await axios.put(
                    "/api/farmer/update-profile",
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
                        title: "Cập nhật thành công!",
                        text: "Hồ sơ của bạn đã được cập nhật",
                        showConfirmButton: false,
                        timer: 2000,
                    }).then(() => {
                        // Clear password form
                        this.clearPasswordForm();
                        // Clear image upload state
                        this.clearImageUploadState();
                        // Reload profile data
                        this.loadMyProfileData();
                    });
                } else {
                    this.$swal({
                        icon: "error",
                        title: "Cập nhật thất bại!",
                        text:
                            response.data.message ||
                            "Đã xảy ra lỗi khi cập nhật hồ sơ",
                        showConfirmButton: true,
                    });
                }
            } catch (error) {
                console.error("Update profile error:", error);

                let errorMessage = "Đã xảy ra lỗi khi cập nhật hồ sơ";

                if (error.response) {
                    if (error.response.status === 401) {
                        this.handleAuthError(error);
                        return;
                    } else if (error.response.status === 422) {
                        // Handle validation errors
                        const errors = error.response.data.errors;
                        if (errors) {
                            this.validationErrors = errors;

                            // Show specific validation error messages
                            const errorMessages = [];

                            if (errors.ngan_hang) {
                                errorMessages.push(
                                    "Mã ngân hàng: " + errors.ngan_hang[0]
                                );
                            }
                            if (errors.phone) {
                                errorMessages.push(
                                    "Số điện thoại: " + errors.phone[0]
                                );
                            }
                            if (errors.so_tai_khoan) {
                                errorMessages.push(
                                    "Số tài khoản: " + errors.so_tai_khoan[0]
                                );
                            }
                            if (errors.chu_tai_khoan) {
                                errorMessages.push(
                                    "Chủ tài khoản: " + errors.chu_tai_khoan[0]
                                );
                            }
                            if (errors.dia_chi_thuong_tru) {
                                errorMessages.push(
                                    "Địa chỉ: " + errors.dia_chi_thuong_tru[0]
                                );
                            }

                            errorMessage =
                                errorMessages.length > 0
                                    ? errorMessages.join("\n")
                                    : "Dữ liệu không hợp lệ. Vui lòng kiểm tra lại thông tin.";
                        } else {
                            errorMessage =
                                error.response.data.message || errorMessage;
                        }
                    } else {
                        errorMessage =
                            error.response.data?.message || errorMessage;
                    }
                } else if (error.code === "ECONNABORTED") {
                    errorMessage =
                        "Kết nối bị gián đoạn. Vui lòng kiểm tra mạng và thử lại.";
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

        // Helper methods
        clearPasswordForm() {
            this.passwordForm = {
                newPassword: "",
                confirmPassword: "",
            };
            this.showNewPassword = false;
            this.showConfirmPassword = false;
        },

        resetForm() {
            // Go back to dashboard or main page
            this.$router.push("/");
        },

        handleAuthError(error) {
            if (error.response && error.response.status === 401) {
                localStorage.removeItem("web_token");
                localStorage.removeItem("web_user");
                localStorage.removeItem("user_type");
                localStorage.removeItem("supplier_id");
                this.store.logout();
                this.$router.push("/login");
            }
        },

        // Add global method access for SweetAlert2
        $swal(options) {
            return Swal.fire(options);
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
select:focus,
textarea:focus {
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
