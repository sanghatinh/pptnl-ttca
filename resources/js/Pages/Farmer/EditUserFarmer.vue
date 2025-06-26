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

            <!-- 1. เพิ่ม Image Modal หลัง Loading Overlay -->
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
                                :src="
                                    imagePreview
                                        ? imagePreview
                                        : getCloudinaryUrl(farmer.image)
                                "
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
                        <!-- Delete User Button - Top Right -->
                        <button
                            @click="confirmDeleteUser"
                            class="absolute top-4 right-4 bg-white hover:bg-gray-50 text-green-600 hover:text-green-700 p-2 rounded-full shadow-lg transition-all duration-200 hover:scale-105 hover:shadow-xl group border-2 border-white hover:border-gray-100"
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
                                        v-if="imagePreview"
                                        :src="imagePreview"
                                        alt="Profile"
                                        class="w-full h-full rounded-full object-cover"
                                    />
                                    <img
                                        v-else-if="farmer.image"
                                        :src="getCloudinaryUrl(farmer.image)"
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
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                            />
                                        </svg>
                                        Trạm<span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="farmer.tram"
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                        @change="loadEmployeesByStation"
                                    >
                                        <option value="" disabled>
                                            Chọn trạm
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

                                <!-- Nhân viên -->
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
                                        Nhân viên<span class="text-red-500"
                                            >*</span
                                        >
                                    </label>
                                    <select
                                        v-model="farmer.ma_nhan_vien"
                                        required
                                        :disabled="
                                            !farmer.tram || loadingEmployees
                                        "
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 disabled:bg-gray-100"
                                    >
                                        <option value="" disabled>
                                            {{
                                                loadingEmployees
                                                    ? "Đang tải..."
                                                    : "Chọn nhân viên"
                                            }}
                                        </option>
                                        <option
                                            v-for="employee in employees"
                                            :key="employee.ma_nhan_vien"
                                            :value="employee.ma_nhan_vien"
                                        >
                                            {{ employee.full_name }} ({{
                                                employee.ma_nhan_vien
                                            }})
                                        </option>
                                    </select>
                                </div>

                                <!-- Supplier Number -->
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
                                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
                                            />
                                        </svg>
                                        Supplier Number<span
                                            class="text-red-500"
                                            >*</span
                                        >
                                    </label>
                                    <input
                                        v-model="farmer.supplier_number"
                                        type="text"
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                        placeholder="Enter supplier number"
                                    />
                                </div>

                                <!-- Mã KH cá nhân -->
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
                                        Mã KH cá nhân
                                        <span
                                            v-if="!farmer.ma_kh_doanh_nghiep"
                                            class="text-red-500"
                                            >*</span
                                        >
                                    </label>
                                    <input
                                        v-model="farmer.ma_kh_ca_nhan"
                                        type="text"
                                        :required="!farmer.ma_kh_doanh_nghiep"
                                        :disabled="!!farmer.ma_kh_doanh_nghiep"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 disabled:bg-gray-100"
                                        placeholder="Enter individual customer code"
                                    />
                                    <small
                                        v-if="farmer.ma_kh_doanh_nghiep"
                                        class="text-gray-500 text-sm"
                                    >
                                        Đã có mã KH doanh nghiệp nên không cần
                                        điền trường này
                                    </small>
                                </div>

                                <!-- Khách hàng cá nhân -->
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
                                        Khách hàng cá nhân
                                        <span
                                            v-if="
                                                !farmer.khach_hang_doanh_nghiep
                                            "
                                            class="text-red-500"
                                            >*</span
                                        >
                                    </label>
                                    <input
                                        v-model="farmer.khach_hang_ca_nhan"
                                        type="text"
                                        :required="
                                            !farmer.khach_hang_doanh_nghiep
                                        "
                                        :disabled="
                                            !!farmer.khach_hang_doanh_nghiep
                                        "
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 disabled:bg-gray-100"
                                        placeholder="Enter individual customer name"
                                    />
                                    <small
                                        v-if="farmer.khach_hang_doanh_nghiep"
                                        class="text-gray-500 text-sm"
                                    >
                                        Đã có khách hàng doanh nghiệp nên không
                                        cần điền trường này
                                    </small>
                                </div>

                                <!-- Mã KH doanh nghiệp -->
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
                                        <span
                                            v-if="!farmer.ma_kh_ca_nhan"
                                            class="text-red-500"
                                            >*</span
                                        >
                                    </label>
                                    <input
                                        v-model="farmer.ma_kh_doanh_nghiep"
                                        type="text"
                                        :required="!farmer.ma_kh_ca_nhan"
                                        :disabled="!!farmer.ma_kh_ca_nhan"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 disabled:bg-gray-100"
                                        placeholder="Enter business customer code"
                                    />
                                    <small
                                        v-if="farmer.ma_kh_ca_nhan"
                                        class="text-gray-500 text-sm"
                                    >
                                        Đã có mã KH cá nhân nên không cần điền
                                        trường này
                                    </small>
                                </div>

                                <!-- Khách hàng doanh nghiệp -->
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
                                        <span
                                            v-if="!farmer.khach_hang_ca_nhan"
                                            class="text-red-500"
                                            >*</span
                                        >
                                    </label>
                                    <input
                                        v-model="farmer.khach_hang_doanh_nghiep"
                                        type="text"
                                        :required="!farmer.khach_hang_ca_nhan"
                                        :disabled="!!farmer.khach_hang_ca_nhan"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 disabled:bg-gray-100"
                                        placeholder="Enter business customer name"
                                    />
                                    <small
                                        v-if="farmer.khach_hang_ca_nhan"
                                        class="text-gray-500 text-sm"
                                    >
                                        Đã có khách hàng cá nhân nên không cần
                                        điền trường này
                                    </small>
                                </div>

                                <!-- Số điện thoại -->
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
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                        placeholder="Enter phone number"
                                    />
                                </div>

                                <!-- Địa chỉ thường trú -->
                                <div class="space-y-2 lg:col-span-2">
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
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                        placeholder="Enter permanent address"
                                    ></textarea>
                                </div>

                                <!-- Chủ tài khoản -->
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
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                        placeholder="Enter account holder name"
                                    />
                                </div>

                                <!-- Ngân hàng -->
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
                                        Ngân hàng
                                    </label>
                                    <select
                                        v-model="farmer.ngan_hang"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                    >
                                        <option value="">Chọn ngân hàng</option>
                                        <option
                                            v-for="bank in banks"
                                            :key="bank.code_banking"
                                            :value="bank.code_banking"
                                        >
                                            {{ bank.name_banking }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Số tài khoản -->
                                <div class="space-y-2 lg:col-span-2">
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
                                                d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"
                                            />
                                        </svg>
                                        Số tài khoản
                                    </label>
                                    <input
                                        v-model="farmer.so_tai_khoan"
                                        type="text"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                        placeholder="Enter bank account number"
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
                                        v-model="farmer.role_id"
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
                                        Status<span class="text-red-500"
                                            >*</span
                                        >
                                    </label>
                                    <div class="flex space-x-4">
                                        <label class="flex items-center">
                                            <input
                                                v-model="farmer.status"
                                                type="radio"
                                                value="active"
                                                class="w-4 h-4 text-green-600 focus:ring-green-500"
                                            />
                                            <span
                                                class="ml-2 text-sm text-gray-700"
                                                >Active</span
                                            >
                                        </label>
                                        <label class="flex items-center">
                                            <input
                                                v-model="farmer.status"
                                                type="radio"
                                                value="inactive"
                                                class="w-4 h-4 text-red-600 focus:ring-red-500"
                                            />
                                            <span
                                                class="ml-2 text-sm text-gray-700"
                                                >Inactive</span
                                            >
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Password Section -->
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
                                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                            >
                                                <svg
                                                    v-if="!showNewPassword"
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
                                                    v-if="!showConfirmPassword"
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
                                <div class="mt-2">
                                    <small class="text-blue-600">
                                        📝 Mật khẩu phải có ít nhất 6 ký tự
                                    </small>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div
                                class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-gray-200"
                            >
                                <button
                                    type="submit"
                                    :disabled="isLoading"
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
                                            ? "Updating..."
                                            : "Update Farmer"
                                    }}
                                </button>
                                <button
                                    type="button"
                                    @click="resetForm"
                                    :disabled="isLoading"
                                    class="flex-1 bg-gray-100 text-gray-700 px-6 py-3 rounded-lg font-medium hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    Cancel
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

    async created() {
        await this.initializeComponent();
    },

    methods: {
        getCloudinaryUrl(publicId, transformation = {}) {
            if (!publicId) return null;
            if (publicId.includes("http")) return publicId;
            const defaultTransform = {
                width: 400,
                height: 400,
                crop: "fill",
                gravity: "face",
                quality: "auto:good",
            };
            const transform = { ...defaultTransform, ...transformation };
            const transformString = Object.entries(transform)
                .map(([key, value]) => {
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
            const cloudName = "dhtgcccax"; // เปลี่ยนตาม config จริง
            return `https://res.cloudinary.com/${cloudName}/image/upload/${transformString}/${publicId}`;
        },
        async initializeComponent() {
            this.isLoading = true;
            this.loadingMessage = "Loading farmer data...";

            try {
                // Load all necessary data
                await Promise.all([
                    this.fetchStations(),
                    this.fetchBanks(),
                    this.fetchRoles(),
                    this.loadFarmerData(),
                ]);
            } catch (error) {
                console.error("Error initializing component:", error);
                this.handleAuthError(error);
            } finally {
                this.isLoading = false;
            }
        },

        async loadFarmerData() {
            const farmerId = this.$route.params.id;
            if (!farmerId) {
                this.$router.push("/UserFarmer");
                return;
            }

            try {
                const response = await axios.get(
                    `/api/farmer-users/${farmerId}`,
                    {
                        headers: {
                            Authorization: "Bearer " + this.store.getToken,
                        },
                    }
                );

                const farmerData = response.data.data;

                // Map backend data to frontend model
                this.farmer = {
                    id: farmerData.id,
                    image: farmerData.image_url || "", // ใช้ image_url จาก response
                    image_public_id: farmerData.url_image || "", // ใช้ url_image เป็น public_id
                    tram: farmerData.tram,
                    ma_nhan_vien: farmerData.ma_nhan_vien,
                    supplier_number: farmerData.supplier_number,
                    ma_kh_ca_nhan: farmerData.ma_kh_ca_nhan,
                    khach_hang_ca_nhan: farmerData.khach_hang_ca_nhan,
                    ma_kh_doanh_nghiep: farmerData.ma_kh_doanh_nghiep,
                    khach_hang_doanh_nghiep: farmerData.khach_hang_doanh_nghiep,
                    phone: farmerData.phone,
                    dia_chi_thuong_tru: farmerData.dia_chi_thuong_tru,
                    chu_tai_khoan: farmerData.chu_tai_khoan,
                    ngan_hang: farmerData.ngan_hang,
                    so_tai_khoan: farmerData.so_tai_khoan,
                    role_id: farmerData.role_id,
                    status: farmerData.status,
                };

                // Load employees if station is selected
                if (this.farmer.tram) {
                    await this.loadEmployeesByStation();
                }
            } catch (error) {
                console.error("Error loading farmer data:", error);
                this.handleAuthError(error);
            }
        },

        // Data fetching methods
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

        async fetchBanks() {
            try {
                // Fetch banks from filter options API
                const response = await axios.get(
                    "/api/farmer-users/filter-options",
                    {
                        headers: {
                            Authorization: "Bearer " + this.store.getToken,
                        },
                    }
                );
                this.banks = response.data.data.banks;
            } catch (error) {
                console.error("Error fetching banks:", error);
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

        async loadEmployeesByStation() {
            if (!this.farmer.tram) {
                this.employees = [];
                return;
            }

            this.loadingEmployees = true;
            try {
                const response = await axios.get("/api/employees/by-station", {
                    params: { station: this.farmer.tram },
                    headers: { Authorization: "Bearer " + this.store.getToken },
                });

                if (response.data.success) {
                    this.employees = response.data.data;
                } else {
                    this.employees = [];
                }
            } catch (error) {
                console.error("Error loading employees:", error);
                this.employees = [];
                this.handleAuthError(error);
            } finally {
                this.loadingEmployees = false;
            }
        },

        // Delete methods
        async confirmDeleteUser() {
            try {
                const result = await this.$swal({
                    title: "Xóa người dùng farmer?",
                    text: "Bạn có chắc chắn muốn xóa người dùng farmer này? Hành động này không thể hoàn tác.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#ef4444",
                    cancelButtonColor: "#6b7280",
                    confirmButtonText: "Có, xóa nó!",
                    cancelButtonText: "Hủy",
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
            this.loadingMessage = "Đang xóa người dùng farmer...";

            try {
                const response = await axios.delete(
                    `/api/farmer-users/${this.farmer.id}`,
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
                        title: "Đã xóa!",
                        text: "Người dùng farmer đã được xóa thành công.",
                        showConfirmButton: false,
                        timer: 2000,
                    }).then(() => {
                        this.$router.push("/UserFarmer");
                    });
                } else {
                    this.$swal({
                        icon: "error",
                        title: "Xóa thất bại!",
                        text:
                            response.data.message ||
                            "Không thể xóa người dùng farmer",
                        showConfirmButton: true,
                    });
                }
            } catch (error) {
                console.error("Delete farmer error:", error);

                let errorMessage = "Đã xảy ra lỗi khi xóa người dùng farmer";

                if (error.response) {
                    if (error.response.status === 404) {
                        errorMessage = "Không tìm thấy người dùng farmer";
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
                this.isLoading = false;
                this.loadingMessage = "Đang xử lý...";
            }
        },

        // Validation methods
        validateForm() {
            // Basic validation (ลบ password จาก required fields)
            if (
                !this.farmer.tram ||
                !this.farmer.ma_nhan_vien ||
                !this.farmer.supplier_number ||
                !this.farmer.role_id
            ) {
                this.$swal({
                    icon: "warning",
                    title: "Thiếu trường bắt buộc",
                    text: "Vui lòng điền vào tất cả các trường bắt buộc được đánh dấu *",
                    showConfirmButton: true,
                });
                return false;
            }

            // Check either individual or business customer info
            if (
                (!this.farmer.ma_kh_ca_nhan ||
                    !this.farmer.khach_hang_ca_nhan) &&
                (!this.farmer.ma_kh_doanh_nghiep ||
                    !this.farmer.khach_hang_doanh_nghiep)
            ) {
                this.$swal({
                    icon: "warning",
                    title: "Thiếu thông tin khách hàng",
                    text: "Vui lòng điền thông tin khách hàng cá nhân HOẶC doanh nghiệp",
                    showConfirmButton: true,
                });
                return false;
            }

            // Password validation - chỉ validate ถ้ามีการกรอกข้อมูล
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
                        text: "Mật khẩu mới và xác nhận không khớp",
                        showConfirmButton: true,
                    });
                    return false;
                }
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
                        icon: "error",
                        title: "Loại tệp không hợp lệ",
                        text: "Vui lòng chọn tệp hình ảnh",
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
                    "/api/farmer-users/upload-image", // ✅ ใช้ API endpoint ที่ถูกต้อง
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
                    `/api/farmer-users/${this.farmer.id}/delete-image`,
                    {
                        headers: {
                            Authorization: "Bearer " + this.store.getToken,
                        },
                        timeout: 30000,
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
                        text: response.data.message || "Không thể xóa hình ảnh",
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

        // Validation methods
        validateForm() {
            // Basic validation (ลบ password จาก required fields)
            if (
                !this.farmer.tram ||
                !this.farmer.ma_nhan_vien ||
                !this.farmer.supplier_number ||
                !this.farmer.role_id
            ) {
                this.$swal({
                    icon: "warning",
                    title: "Thiếu trường bắt buộc",
                    text: "Vui lòng điền vào tất cả các trường bắt buộc được đánh dấu *",
                    showConfirmButton: true,
                });
                return false;
            }

            // Check either individual or business customer info
            if (
                (!this.farmer.ma_kh_ca_nhan ||
                    !this.farmer.khach_hang_ca_nhan) &&
                (!this.farmer.ma_kh_doanh_nghiep ||
                    !this.farmer.khach_hang_doanh_nghiep)
            ) {
                this.$swal({
                    icon: "warning",
                    title: "Thiếu thông tin khách hàng",
                    text: "Vui lòng điền thông tin khách hàng cá nhân HOẶC doanh nghiệp",
                    showConfirmButton: true,
                });
                return false;
            }

            // Password validation - chỉ validate ถ้ามีการกรอกข้อมูล
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
                        text: "Mật khẩu mới và xác nhận không khớp",
                        showConfirmButton: true,
                    });
                    return false;
                }
            }

            return true;
        },

        // Main update method
        async updateProfile() {
            if (!this.validateForm()) return;

            this.isLoading = true;
            this.loadingMessage = "Đang cập nhật hồ sơ farmer...";

            try {
                let imagePublicId = this.farmer.image_public_id;

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
                            text: "Hồ sơ sẽ được cập nhật mà không có hình ảnh mới. Bạn có thể thử tải lên lại sau.",
                            showConfirmButton: true,
                        });
                    }
                }

                // Prepare update data
                const updateData = {
                    tram: this.farmer.tram,
                    ma_nhan_vien: this.farmer.ma_nhan_vien, // ✅ Make sure this is included
                    supplier_number: this.farmer.supplier_number,
                    ma_kh_ca_nhan: this.farmer.ma_kh_ca_nhan || "",
                    khach_hang_ca_nhan: this.farmer.khach_hang_ca_nhan || "",
                    ma_kh_doanh_nghiep: this.farmer.ma_kh_doanh_nghiep || "",
                    khach_hang_doanh_nghiep:
                        this.farmer.khach_hang_doanh_nghiep || "",
                    phone: this.farmer.phone || "",
                    dia_chi_thuong_tru: this.farmer.dia_chi_thuong_tru || "",
                    chu_tai_khoan: this.farmer.chu_tai_khoan || "",
                    ngan_hang: this.farmer.ngan_hang || "",
                    so_tai_khoan: this.farmer.so_tai_khoan || "",
                    role_id: this.farmer.role_id,
                    status: this.farmer.status,
                };

                // เพิ่ม password เฉพาะเมื่อมีการกรอกข้อมูล
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
                    `/api/farmer-users/${this.farmer.id}`,
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
                        title: "Thành công!",
                        text: "Hồ sơ farmer đã được cập nhật thành công!",
                        showConfirmButton: false,
                        timer: 2000,
                    }).then(() => {
                        // Clear password form
                        this.clearPasswordForm();
                        // Clear image upload state
                        this.clearImageUploadState();
                        // Reload farmer data to show updated image
                        this.loadFarmerData();
                    });
                } else {
                    this.$swal({
                        icon: "error",
                        title: "Cập nhật thất bại!",
                        text:
                            response.data.message ||
                            "Không thể cập nhật hồ sơ farmer",
                        showConfirmButton: true,
                    });
                }
            } catch (err) {
                console.error("Update farmer error:", err);
                // ... existing error handling ...
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
            this.$router.push("/UserFarmer");
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
