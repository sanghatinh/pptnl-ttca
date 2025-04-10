<template>
    <!-- Loading starts -->
    <div id="loading-wrapper" v-if="isLoading">
        <div class="spinner-border" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <div class="container-fluid mx-auto p-2">
        <div class="row align-items-center mb-2" v-if="!isMobile">
            <div class="col d-flex justify-content-start gap-3">
                <div class="status-filter">
                    <select
                        v-model="statusFilter"
                        class="form-select status-select"
                    >
                        <option
                            v-for="option in statusOptions"
                            :key="option.code"
                            :value="option.code"
                            class="status-option"
                        >
                            <span
                                v-if="option.code !== 'all'"
                                class="status-icon"
                            >
                                <i :class="statusIcon(option.code)"></i>
                            </span>
                            {{ option.name }} ({{
                                statusCounts[option.code] || 0
                            }})
                        </option>
                    </select>
                </div>
                <div class="investment-filter ms-3">
                    <select
                        v-model="investmentFilter"
                        class="form-select investment-select"
                    >
                        <option value="all">Tất cả vụ đầu tư</option>
                        <option
                            v-for="project in investmentProjects"
                            :key="project"
                            :value="project"
                        >
                            {{ project }}
                        </option>
                    </select>
                </div>
                <div
                    class="col d-flex justify-content-end gap-3 align-items-center"
                >
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search..."
                        class="search-input px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500"
                    />
                </div>
            </div>
        </div>

        <!-- Mobile Controls -->
        <div
            class="mobile-controls p-3 bg-white rounded-lg shadow-sm mb-3"
            v-if="isMobile"
        >
            <!-- Search and Create Button Row -->
            <div class="flex gap-2 mb-3">
                <div class="flex-1 relative">
                    <span
                        class="absolute inset-y-0 left-0 flex items-center pl-3"
                    >
                        <i class="fas fa-search text-gray-400"></i>
                    </span>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Tìm kiếm biên bản..."
                        class="w-full pl-10 pr-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm"
                    />
                </div>
            </div>

            <!-- Filters Section -->
            <div class="filter-section">
                <!-- Status Filter -->
                <div class="mb-2.5">
                    <label class="text-sm font-medium text-gray-700 mb-1 block"
                        >Trạng thái</label
                    >
                    <div class="relative">
                        <select
                            v-model="statusFilter"
                            class="w-full py-2.5 pl-3 pr-10 border rounded-lg appearance-none bg-white focus:outline-none focus:ring-2 focus:ring-green-500 text-sm"
                        >
                            <option
                                v-for="option in statusOptions"
                                :key="option.code"
                                :value="option.code"
                            >
                                <span v-if="option.code !== 'all'">
                                    <i :class="statusIcon(option.code)"></i>
                                </span>
                                {{ option.name }} ({{
                                    statusCounts[option.code] || 0
                                }})
                            </option>
                        </select>
                        <div
                            class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"
                        >
                            <i class="fas fa-chevron-down text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <!-- Investment Filter -->
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1 block"
                        >Vụ đầu tư</label
                    >
                    <div class="relative">
                        <select
                            v-model="investmentFilter"
                            class="w-full py-2.5 pl-3 pr-10 border rounded-lg appearance-none bg-white focus:outline-none focus:ring-2 focus:ring-green-500 text-sm"
                        >
                            <option value="all">Tất cả vụ đầu tư</option>
                            <option
                                v-for="project in investmentProjects"
                                :key="project"
                                :value="project"
                            >
                                {{ project }}
                            </option>
                        </select>
                        <div
                            class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"
                        >
                            <i class="fas fa-chevron-down text-gray-400"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <!-- Desktop view table -->
            <div v-if="!isMobile" class="card">
                <div class="card-body">
                    <div class="overflow-x-auto">
                        <table class="table-auto w-full">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2">
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <span>Mã nghiệm thu</span>
                                            <button
                                                @click="
                                                    toggleFilter(
                                                        'ma_nghiem_thu'
                                                    )
                                                "
                                                class="ml-2 text-gray-500 hover:text-gray-700"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.ma_nghiem_thu,
                                                    }"
                                                ></i>
                                            </button>
                                        </div>
                                        <div
                                            v-if="
                                                activeFilter === 'ma_nghiem_thu'
                                            "
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10 w-64"
                                        >
                                            <input
                                                v-model="
                                                    columnFilters.ma_nghiem_thu
                                                "
                                                type="text"
                                                placeholder="Tìm kiếm mã nghiệm thu..."
                                                class="w-full p-2 border rounded mb-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                            />
                                            <div class="flex justify-between">
                                                <button
                                                    @click="
                                                        resetFilter(
                                                            'ma_nghiem_thu'
                                                        )
                                                    "
                                                    class="px-2 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-xs"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="activeFilter = null"
                                                    class="px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-xs"
                                                >
                                                    Áp dụng
                                                </button>
                                            </div>
                                        </div>
                                    </th>
                                    <th class="px-4 py-2">
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <span>Trạm</span>
                                            <button
                                                @click="toggleFilter('tram')"
                                                class="ml-2 text-gray-500 hover:text-gray-700"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.tram,
                                                    }"
                                                ></i>
                                            </button>
                                        </div>
                                        <div
                                            v-if="activeFilter === 'tram'"
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10 w-64"
                                        >
                                            <div
                                                class="max-h-40 overflow-y-auto mb-2"
                                            >
                                                <div
                                                    v-for="option in uniqueValues.tram"
                                                    :key="option"
                                                    class="flex items-center mb-2"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        :id="`tram-${option}`"
                                                        :value="option"
                                                        v-model="
                                                            selectedFilterValues.tram
                                                        "
                                                        class="mr-2 rounded text-green-500 focus:ring-green-500"
                                                    />
                                                    <label
                                                        :for="`tram-${option}`"
                                                        class="select-none"
                                                        >{{ option }}</label
                                                    >
                                                </div>
                                            </div>
                                            <div class="flex justify-between">
                                                <button
                                                    @click="resetFilter('tram')"
                                                    class="px-2 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-xs"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="applyFilter('tram')"
                                                    class="px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-xs"
                                                >
                                                    Áp dụng
                                                </button>
                                            </div>
                                        </div>
                                    </th>
                                    <th class="px-4 py-2">
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <span>Vụ đầu tư</span>
                                            <button
                                                @click="
                                                    toggleFilter('vu_dau_tu')
                                                "
                                                class="ml-2 text-gray-500 hover:text-gray-700"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.vu_dau_tu,
                                                    }"
                                                ></i>
                                            </button>
                                        </div>
                                        <div
                                            v-if="activeFilter === 'vu_dau_tu'"
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10 w-64"
                                        >
                                            <div
                                                class="max-h-40 overflow-y-auto mb-2"
                                            >
                                                <div
                                                    v-for="option in uniqueValues.vu_dau_tu"
                                                    :key="option"
                                                    class="flex items-center mb-2"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        :id="`vu_dau_tu-${option}`"
                                                        :value="option"
                                                        v-model="
                                                            selectedFilterValues.vu_dau_tu
                                                        "
                                                        class="mr-2 rounded text-green-500 focus:ring-green-500"
                                                    />
                                                    <label
                                                        :for="`vu_dau_tu-${option}`"
                                                        class="select-none"
                                                        >{{ option }}</label
                                                    >
                                                </div>
                                            </div>
                                            <div class="flex justify-between">
                                                <button
                                                    @click="
                                                        resetFilter('vu_dau_tu')
                                                    "
                                                    class="px-2 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-xs"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="
                                                        applyFilter('vu_dau_tu')
                                                    "
                                                    class="px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-xs"
                                                >
                                                    Áp dụng
                                                </button>
                                            </div>
                                        </div>
                                    </th>
                                    <th class="px-4 py-2">
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <span>Tiêu đề</span>
                                            <button
                                                @click="toggleFilter('tieu_de')"
                                                class="ml-2 text-gray-500 hover:text-gray-700"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.tieu_de,
                                                    }"
                                                ></i>
                                            </button>
                                        </div>
                                        <div
                                            v-if="activeFilter === 'tieu_de'"
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10 w-64"
                                        >
                                            <input
                                                v-model="columnFilters.tieu_de"
                                                type="text"
                                                placeholder="Tìm kiếm tiêu đề..."
                                                class="w-full p-2 border rounded mb-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                            />
                                            <div class="flex justify-between">
                                                <button
                                                    @click="
                                                        resetFilter('tieu_de')
                                                    "
                                                    class="px-2 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-xs"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="activeFilter = null"
                                                    class="px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-xs"
                                                >
                                                    Áp dụng
                                                </button>
                                            </div>
                                        </div>
                                    </th>
                                    <th class="px-4 py-2">
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <span>Khách hàng cá nhân</span>
                                            <button
                                                @click="
                                                    toggleFilter(
                                                        'khach_hang_ca_nhan_dt_mia'
                                                    )
                                                "
                                                class="ml-2 text-gray-500 hover:text-gray-700"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.khach_hang_ca_nhan_dt_mia,
                                                    }"
                                                ></i>
                                            </button>
                                        </div>
                                        <div
                                            v-if="
                                                activeFilter ===
                                                'khach_hang_ca_nhan_dt_mia'
                                            "
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10 w-64"
                                        >
                                            <input
                                                v-model="
                                                    columnFilters.khach_hang_ca_nhan_dt_mia
                                                "
                                                type="text"
                                                placeholder="Tìm kiếm khách hàng..."
                                                class="w-full p-2 border rounded mb-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                            />
                                            <div class="flex justify-between">
                                                <button
                                                    @click="
                                                        resetFilter(
                                                            'khach_hang_ca_nhan_dt_mia'
                                                        )
                                                    "
                                                    class="px-2 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-xs"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="activeFilter = null"
                                                    class="px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-xs"
                                                >
                                                    Áp dụng
                                                </button>
                                            </div>
                                        </div>
                                    </th>
                                    <th class="px-4 py-2">
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <span>Khách hàng doanh nghiệp</span>
                                            <button
                                                @click="
                                                    toggleFilter(
                                                        'khach_hang_doanh_nghiep_dt_mia'
                                                    )
                                                "
                                                class="ml-2 text-gray-500 hover:text-gray-700"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.khach_hang_doanh_nghiep_dt_mia,
                                                    }"
                                                ></i>
                                            </button>
                                        </div>
                                        <div
                                            v-if="
                                                activeFilter ===
                                                'khach_hang_doanh_nghiep_dt_mia'
                                            "
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10 w-64"
                                        >
                                            <input
                                                v-model="
                                                    columnFilters.khach_hang_doanh_nghiep_dt_mia
                                                "
                                                type="text"
                                                placeholder="Tìm kiếm doanh nghiệp..."
                                                class="w-full p-2 border rounded mb-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                            />
                                            <div class="flex justify-between">
                                                <button
                                                    @click="
                                                        resetFilter(
                                                            'khach_hang_doanh_nghiep_dt_mia'
                                                        )
                                                    "
                                                    class="px-2 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-xs"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="activeFilter = null"
                                                    class="px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-xs"
                                                >
                                                    Áp dụng
                                                </button>
                                            </div>
                                        </div>
                                    </th>
                                    <th class="px-4 py-2">
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <span>Hợp đồng đầu tư mía</span>
                                            <button
                                                @click="
                                                    toggleFilter(
                                                        'hop_dong_dau_tu_mia'
                                                    )
                                                "
                                                class="ml-2 text-gray-500 hover:text-gray-700"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.hop_dong_dau_tu_mia,
                                                    }"
                                                ></i>
                                            </button>
                                        </div>
                                        <div
                                            v-if="
                                                activeFilter ===
                                                'hop_dong_dau_tu_mia'
                                            "
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10 w-64"
                                        >
                                            <input
                                                v-model="
                                                    columnFilters.hop_dong_dau_tu_mia
                                                "
                                                type="text"
                                                placeholder="Tìm kiếm hợp đồng..."
                                                class="w-full p-2 border rounded mb-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                            />
                                            <div class="flex justify-between">
                                                <button
                                                    @click="
                                                        resetFilter(
                                                            'hop_dong_dau_tu_mia'
                                                        )
                                                    "
                                                    class="px-2 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-xs"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="activeFilter = null"
                                                    class="px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-xs"
                                                >
                                                    Áp dụng
                                                </button>
                                            </div>
                                        </div>
                                    </th>
                                    <th class="px-4 py-2">
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <span>Hình thức thực hiện DV</span>
                                            <button
                                                @click="
                                                    toggleFilter(
                                                        'hinh_thuc_thuc_hien_dv'
                                                    )
                                                "
                                                class="ml-2 text-gray-500 hover:text-gray-700"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.hinh_thuc_thuc_hien_dv,
                                                    }"
                                                ></i>
                                            </button>
                                        </div>
                                        <div
                                            v-if="
                                                activeFilter ===
                                                'hinh_thuc_thuc_hien_dv'
                                            "
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10 w-64"
                                        >
                                            <div
                                                class="max-h-40 overflow-y-auto mb-2"
                                            >
                                                <div
                                                    v-for="option in uniqueValues.hinh_thuc_thuc_hien_dv"
                                                    :key="option"
                                                    class="flex items-center mb-2"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        :id="`hinh_thuc_thuc_hien_dv-${option}`"
                                                        :value="option"
                                                        v-model="
                                                            selectedFilterValues.hinh_thuc_thuc_hien_dv
                                                        "
                                                        class="mr-2 rounded text-green-500 focus:ring-green-500"
                                                    />
                                                    <label
                                                        :for="`hinh_thuc_thuc_hien_dv-${option}`"
                                                        class="select-none"
                                                        >{{ option }}</label
                                                    >
                                                </div>
                                            </div>
                                            <div class="flex justify-between">
                                                <button
                                                    @click="
                                                        resetFilter(
                                                            'hinh_thuc_thuc_hien_dv'
                                                        )
                                                    "
                                                    class="px-2 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-xs"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="
                                                        applyFilter(
                                                            'hinh_thuc_thuc_hien_dv'
                                                        )
                                                    "
                                                    class="px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-xs"
                                                >
                                                    Áp dụng
                                                </button>
                                            </div>
                                        </div>
                                    </th>
                                    <th class="px-4 py-2">
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <span
                                                >Hợp đồng cung ứng dịch vụ</span
                                            >
                                            <button
                                                @click="
                                                    toggleFilter(
                                                        'hop_dong_cung_ung_dich_vu'
                                                    )
                                                "
                                                class="ml-2 text-gray-500 hover:text-gray-700"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.hop_dong_cung_ung_dich_vu,
                                                    }"
                                                ></i>
                                            </button>
                                        </div>
                                        <div
                                            v-if="
                                                activeFilter ===
                                                'hop_dong_cung_ung_dich_vu'
                                            "
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10 w-64"
                                        >
                                            <input
                                                v-model="
                                                    columnFilters.hop_dong_cung_ung_dich_vu
                                                "
                                                type="text"
                                                placeholder="Tìm kiếm hợp đồng..."
                                                class="w-full p-2 border rounded mb-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                            />
                                            <div class="flex justify-between">
                                                <button
                                                    @click="
                                                        resetFilter(
                                                            'hop_dong_cung_ung_dich_vu'
                                                        )
                                                    "
                                                    class="px-2 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-xs"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="activeFilter = null"
                                                    class="px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-xs"
                                                >
                                                    Áp dụng
                                                </button>
                                            </div>
                                        </div>
                                    </th>
                                    <th class="px-4 py-2">Tổng tiền dịch vụ</th>
                                    <th class="px-4 py-2">Tổng tiền tạm giữ</th>
                                    <th class="px-4 py-2">
                                        Tổng tiền thanh toán
                                    </th>
                                    <th class="px-4 py-2">
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <span>Người giao</span>
                                            <button
                                                @click="
                                                    toggleFilter('nguoi_giao')
                                                "
                                                class="ml-2 text-gray-500 hover:text-gray-700"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.nguoi_giao,
                                                    }"
                                                ></i>
                                            </button>
                                        </div>
                                        <div
                                            v-if="activeFilter === 'nguoi_giao'"
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10 w-64"
                                        >
                                            <input
                                                v-model="
                                                    columnFilters.nguoi_giao
                                                "
                                                type="text"
                                                placeholder="Tìm kiếm người giao..."
                                                class="w-full p-2 border rounded mb-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                            />
                                            <div class="flex justify-between">
                                                <button
                                                    @click="
                                                        resetFilter(
                                                            'nguoi_giao'
                                                        )
                                                    "
                                                    class="px-2 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-xs"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="activeFilter = null"
                                                    class="px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-xs"
                                                >
                                                    Áp dụng
                                                </button>
                                            </div>
                                        </div>
                                    </th>
                                    <th class="px-4 py-2">
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <span>Người nhận</span>
                                            <button
                                                @click="
                                                    toggleFilter('nguoi_nhan')
                                                "
                                                class="ml-2 text-gray-500 hover:text-gray-700"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.nguoi_nhan,
                                                    }"
                                                ></i>
                                            </button>
                                        </div>
                                        <div
                                            v-if="activeFilter === 'nguoi_nhan'"
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10 w-64"
                                        >
                                            <input
                                                v-model="
                                                    columnFilters.nguoi_nhan
                                                "
                                                type="text"
                                                placeholder="Tìm kiếm người nhận..."
                                                class="w-full p-2 border rounded mb-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                            />
                                            <div class="flex justify-between">
                                                <button
                                                    @click="
                                                        resetFilter(
                                                            'nguoi_nhan'
                                                        )
                                                    "
                                                    class="px-2 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-xs"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="activeFilter = null"
                                                    class="px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-xs"
                                                >
                                                    Áp dụng
                                                </button>
                                            </div>
                                        </div>
                                    </th>
                                    <th class="px-4 py-2">
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <span>Ngày nhận</span>
                                            <button
                                                @click="
                                                    toggleFilter('ngay_nhan')
                                                "
                                                class="ml-2 text-gray-500 hover:text-gray-700"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.ngay_nhan,
                                                    }"
                                                ></i>
                                            </button>
                                        </div>
                                        <div
                                            v-if="activeFilter === 'ngay_nhan'"
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10 w-64"
                                        >
                                            <input
                                                v-model="
                                                    columnFilters.ngay_nhan
                                                "
                                                type="text"
                                                placeholder="Tìm kiếm ngày nhận (DD/MM/YYYY)..."
                                                class="w-full p-2 border rounded mb-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                            />
                                            <div class="flex justify-between">
                                                <button
                                                    @click="
                                                        resetFilter('ngay_nhan')
                                                    "
                                                    class="px-2 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-xs"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="activeFilter = null"
                                                    class="px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-xs"
                                                >
                                                    Áp dụng
                                                </button>
                                            </div>
                                        </div>
                                    </th>
                                    <th class="px-4 py-2">
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <span>Trạng thái nhận HS</span>
                                            <button
                                                @click="
                                                    toggleFilter(
                                                        'trang_thai_nhan_hs'
                                                    )
                                                "
                                                class="ml-2 text-gray-500 hover:text-gray-700"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.trang_thai_nhan_hs,
                                                    }"
                                                ></i>
                                            </button>
                                        </div>
                                        <div
                                            v-if="
                                                activeFilter ===
                                                'trang_thai_nhan_hs'
                                            "
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10 w-64"
                                        >
                                            <div
                                                class="max-h-40 overflow-y-auto mb-2"
                                            >
                                                <div
                                                    v-for="option in uniqueValues.trang_thai_nhan_hs"
                                                    :key="option"
                                                    class="flex items-center mb-2"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        :id="`trang_thai_nhan_hs-${option}`"
                                                        :value="option"
                                                        v-model="
                                                            selectedFilterValues.trang_thai_nhan_hs
                                                        "
                                                        class="mr-2 rounded text-green-500 focus:ring-green-500"
                                                    />
                                                    <label
                                                        :for="`trang_thai_nhan_hs-${option}`"
                                                        class="select-none"
                                                        >{{
                                                            formatStatus(option)
                                                        }}</label
                                                    >
                                                </div>
                                            </div>
                                            <div class="flex justify-between">
                                                <button
                                                    @click="
                                                        resetFilter(
                                                            'trang_thai_nhan_hs'
                                                        )
                                                    "
                                                    class="px-2 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-xs"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="
                                                        applyFilter(
                                                            'trang_thai_nhan_hs'
                                                        )
                                                    "
                                                    class="px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-xs"
                                                >
                                                    Áp dụng
                                                </button>
                                            </div>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="item in paginatedItems.data"
                                    :key="item.id"
                                    class="desktop-row"
                                    @click="viewDetails(item)"
                                >
                                    <td class="border px-4 py-2">
                                        {{ item.ma_nghiem_thu }}
                                    </td>
                                    <td class="border px-4 py-2">
                                        {{ item.tram }}
                                    </td>
                                    <td class="border px-4 py-2">
                                        {{ item.vu_dau_tu }}
                                    </td>
                                    <td class="border px-4 py-2">
                                        {{ item.tieu_de }}
                                    </td>
                                    <td class="border px-4 py-2">
                                        {{ item.khach_hang_ca_nhan_dt_mia }}
                                    </td>
                                    <td class="border px-4 py-2">
                                        {{
                                            item.khach_hang_doanh_nghiep_dt_mia
                                        }}
                                    </td>
                                    <td class="border px-4 py-2">
                                        {{ item.hop_dong_dau_tu_mia }}
                                    </td>
                                    <td class="border px-4 py-2">
                                        {{ item.hinh_thuc_thuc_hien_dv }}
                                    </td>
                                    <td class="border px-4 py-2">
                                        {{ item.hop_dong_cung_ung_dich_vu }}
                                    </td>
                                    <td class="border px-4 py-2">
                                        {{
                                            formatCurrency(
                                                item.tong_tien_dich_vu
                                            )
                                        }}
                                    </td>
                                    <td class="border px-4 py-2">
                                        {{
                                            formatCurrency(
                                                item.tong_tien_tam_giu
                                            )
                                        }}
                                    </td>
                                    <td class="border px-4 py-2">
                                        {{
                                            formatCurrency(
                                                item.tong_tien_thanh_toan
                                            )
                                        }}
                                    </td>
                                    <td class="border px-4 py-2">
                                        <template v-if="item.nguoi_giao">
                                            <i
                                                class="fas fa-user text-blue-500"
                                            ></i>
                                            {{ item.nguoi_giao }}
                                        </template>
                                    </td>
                                    <td class="border px-4 py-2">
                                        <template v-if="item.nguoi_nhan">
                                            <i
                                                class="fas fa-user text-green-500"
                                            ></i>
                                            {{ item.nguoi_nhan }}
                                        </template>
                                    </td>
                                    <td class="border px-4 py-2">
                                        {{ formatDate(item.ngay_nhan) }}
                                    </td>
                                    <td class="border px-4 py-2">
                                        <span
                                            v-if="item.trang_thai_nhan_hs"
                                            :class="
                                                statusClass(
                                                    item.trang_thai_nhan_hs
                                                )
                                            "
                                            class="flex items-center"
                                        >
                                            <i
                                                :class="
                                                    statusIcons(
                                                        item.trang_thai_nhan_hs
                                                    )
                                                "
                                                class="mr-1"
                                            ></i>
                                            {{
                                                formatStatus(
                                                    item.trang_thai_nhan_hs
                                                )
                                            }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- pagination -->
                    <div class="flex justify-center mt-4">
                        <Bootstrap5Pagination
                            :data="paginatedItems"
                            @pagination-change-page="pageChanged"
                            :limit="5"
                            :classes="paginationClasses"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile view cards -->
        <div v-if="isMobile" class="card-container">
            <div
                v-for="item in paginatedItems.data"
                :key="item.id"
                class="card border p-4 mb-4 rounded shadow hover:shadow-green-500 transition-shadow duration-300"
                @click="viewDetails(item)"
            >
                <div class="flex-1 justify-items-start">
                    <div class="mb-2">
                        <strong>Mã nghiệm thu:</strong>
                        {{ item.ma_nghiem_thu }}
                    </div>
                    <div class="mb-2">
                        <strong>Trạm:</strong>
                        {{ item.tram }}
                    </div>
                    <div class="mb-2">
                        <strong>Vụ đầu tư:</strong>
                        {{ item.vu_dau_tu }}
                    </div>
                    <div class="mb-2">
                        <strong>Tiêu đề:</strong>
                        {{ item.tieu_de }}
                    </div>
                    <div class="mb-2">
                        <strong>Hợp đồng đầu tư mía:</strong>
                        {{ item.hop_dong_dau_tu_mia }}
                    </div>
                    <div class="mb-2">
                        <strong>Hình thức thực hiện DV:</strong>
                        {{ item.hinh_thuc_thuc_hien_dv }}
                    </div>
                    <div class="mb-2">
                        <strong>Hợp đồng cung ứng dịch vụ:</strong>
                        {{ item.hop_dong_cung_ung_dich_vu }}
                    </div>
                    <div class="mb-2">
                        <strong>Tổng tiền dịch vụ:</strong>
                        {{ formatCurrency(item.tong_tien_dich_vu) }}
                    </div>
                </div>
            </div>
            <!-- pagination -->
            <div class="flex justify-center mt-4">
                <div class="pagination-card">
                    <Bootstrap5Pagination
                        :data="paginatedItems"
                        @pagination-change-page="pageChanged"
                        :limit="5"
                        :classes="paginationClasses"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import { useStore } from "../../Store/Auth";
import { ref, computed, onMounted } from "vue";
import { Bootstrap5Pagination } from "laravel-vue-pagination"; // Add this import

export default {
    components: {
        Bootstrap5Pagination, // Register the component
    },
    setup() {
        const store = useStore();
        return {
            store,
        };
    },
    data() {
        return {
            isLoading: false,
            bienBanList: [],
            allBienBanList: [], // เพิ่มสำหรับเก็บข้อมูลทั้งหมด
            search: "",
            statusFilter: "all",
            investmentFilter: "all",
            investmentProjects: [],
            isMobile: window.innerWidth < 768,
            currentPage: 1, // เพิ่มสำหรับการจัดการหน้า
            perPage: 10, // จำนวนรายการต่อหน้า
            paginatedItems: {
                data: [],
            },
            statusOptions: [
                { code: "all", name: "Tất cả trạng thái" },
                { code: "approved", name: "Đã duyệt" },
                { code: "pending", name: "Chờ duyệt" },
                { code: "rejected", name: "Từ chối" },
            ],
            paginationClasses: {
                ul: "flex list-none pagination",
                li: "page-item mx-1",
                a: "page-link px-3 py-2 bg-white border border-gray-300 rounded hover:bg-gray-100",
                active: "bg-green-500 text-white active",
                disabled: "opacity-50 cursor-not-allowed disabled",
            },
            activeFilter: null,
            columnFilters: {
                ma_nghiem_thu: "",
                tram: "",
                vu_dau_tu: "",
                tieu_de: "",
                khach_hang_ca_nhan_dt_mia: "",
                khach_hang_doanh_nghiep_dt_mia: "",
                hop_dong_dau_tu_mia: "",
                hinh_thuc_thuc_hien_dv: "",
                hop_dong_cung_ung_dich_vu: "",
                nguoi_giao: "",
                nguoi_nhan: "",
                ngay_nhan: "",
                trang_thai_nhan_hs: "",
            },
            selectedFilterValues: {
                tram: [],
                vu_dau_tu: [],
                hinh_thuc_thuc_hien_dv: [],
                trang_thai_nhan_hs: [],
            },
            uniqueValues: {
                tram: [],
                vu_dau_tu: [],
                hinh_thuc_thuc_hien_dv: [],
                trang_thai_nhan_hs: [],
            },
        };
    },
    computed: {
        statusCounts() {
            const counts = {
                all: this.bienBanList.length,
                approved: 0,
                pending: 0,
                rejected: 0,
            };

            this.bienBanList.forEach((item) => {
                if (item.tinh_trang_duyet === 1) {
                    counts.approved++;
                } else if (item.tinh_trang_duyet === 0) {
                    counts.pending++;
                } else if (item.tinh_trang_duyet === 2) {
                    counts.rejected++;
                }
            });

            return counts;
        },
        filteredItems() {
            return this.bienBanList.filter((item) => {
                // Filter by search query
                const matchesSearch =
                    item.ma_nghiem_thu
                        ?.toLowerCase()
                        .includes(this.search.toLowerCase()) ||
                    item.tieu_de
                        ?.toLowerCase()
                        .includes(this.search.toLowerCase()) ||
                    item.hop_dong_dau_tu_mia
                        ?.toLowerCase()
                        .includes(this.search.toLowerCase()) ||
                    item.tram
                        ?.toLowerCase()
                        .includes(this.search.toLowerCase());

                // Filter by status
                let matchesStatus = true;
                if (this.statusFilter !== "all") {
                    if (this.statusFilter === "approved") {
                        matchesStatus = item.tinh_trang_duyet === 1;
                    } else if (this.statusFilter === "pending") {
                        matchesStatus = item.tinh_trang_duyet === 0;
                    } else if (this.statusFilter === "rejected") {
                        matchesStatus = item.tinh_trang_duyet === 2;
                    }
                }

                // Filter by investment project
                let matchesInvestment = true;
                if (this.investmentFilter !== "all") {
                    matchesInvestment =
                        item.vu_dau_tu === this.investmentFilter;
                }

                // Column specific filters
                const matchColumnFilters =
                    // Search text filters
                    (!this.columnFilters.ma_nghiem_thu ||
                        (item.ma_nghiem_thu &&
                            item.ma_nghiem_thu
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.ma_nghiem_thu.toLowerCase()
                                ))) &&
                    (!this.columnFilters.tieu_de ||
                        (item.tieu_de &&
                            item.tieu_de
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.tieu_de.toLowerCase()
                                ))) &&
                    (!this.columnFilters.khach_hang_ca_nhan_dt_mia ||
                        (item.khach_hang_ca_nhan_dt_mia &&
                            item.khach_hang_ca_nhan_dt_mia
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.khach_hang_ca_nhan_dt_mia.toLowerCase()
                                ))) &&
                    (!this.columnFilters.khach_hang_doanh_nghiep_dt_mia ||
                        (item.khach_hang_doanh_nghiep_dt_mia &&
                            item.khach_hang_doanh_nghiep_dt_mia
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.khach_hang_doanh_nghiep_dt_mia.toLowerCase()
                                ))) &&
                    (!this.columnFilters.hop_dong_dau_tu_mia ||
                        (item.hop_dong_dau_tu_mia &&
                            item.hop_dong_dau_tu_mia
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.hop_dong_dau_tu_mia.toLowerCase()
                                ))) &&
                    (!this.columnFilters.hop_dong_cung_ung_dich_vu ||
                        (item.hop_dong_cung_ung_dich_vu &&
                            item.hop_dong_cung_ung_dich_vu
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.hop_dong_cung_ung_dich_vu.toLowerCase()
                                ))) &&
                    (!this.columnFilters.nguoi_giao ||
                        (item.nguoi_giao &&
                            item.nguoi_giao
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.nguoi_giao.toLowerCase()
                                ))) &&
                    (!this.columnFilters.nguoi_nhan ||
                        (item.nguoi_nhan &&
                            item.nguoi_nhan
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.nguoi_nhan.toLowerCase()
                                ))) &&
                    (!this.columnFilters.ngay_nhan ||
                        (item.ngay_nhan &&
                            this.formatDate(item.ngay_nhan).includes(
                                this.columnFilters.ngay_nhan
                            ))) &&
                    // Dropdown filters - check if empty or if value is in selected options
                    (this.selectedFilterValues.tram.length === 0 ||
                        (item.tram &&
                            this.selectedFilterValues.tram.includes(
                                item.tram
                            ))) &&
                    (this.selectedFilterValues.vu_dau_tu.length === 0 ||
                        (item.vu_dau_tu &&
                            this.selectedFilterValues.vu_dau_tu.includes(
                                item.vu_dau_tu
                            ))) &&
                    (this.selectedFilterValues.hinh_thuc_thuc_hien_dv.length ===
                        0 ||
                        (item.hinh_thuc_thuc_hien_dv &&
                            this.selectedFilterValues.hinh_thuc_thuc_hien_dv.includes(
                                item.hinh_thuc_thuc_hien_dv
                            ))) &&
                    (this.selectedFilterValues.trang_thai_nhan_hs.length ===
                        0 ||
                        (item.trang_thai_nhan_hs &&
                            this.selectedFilterValues.trang_thai_nhan_hs.includes(
                                item.trang_thai_nhan_hs
                            )));

                return (
                    matchesSearch &&
                    matchesStatus &&
                    matchesInvestment &&
                    matchColumnFilters
                );
            });
        },
        paginatedItems() {
            // Implement client-side pagination
            const page = this.currentPage || 1;
            const perPage = this.perPage || 10;
            const total = this.filteredItems.length;
            const lastPage = Math.ceil(total / perPage);
            const from = (page - 1) * perPage + 1;
            const to = Math.min(from + perPage - 1, total);

            const items = this.filteredItems.slice(
                (page - 1) * perPage,
                page * perPage
            );

            return {
                current_page: page,
                data: items,
                from: from,
                last_page: lastPage,
                per_page: perPage,
                to: to,
                total: total,
                prev_page_url: page > 1 ? `?page=${page - 1}` : null,
                next_page_url: page < lastPage ? `?page=${page + 1}` : null,
            };
        },
    },
    methods: {
        formatDate(date) {
            if (!date) return "";
            return new Date(date).toLocaleDateString("vi-VN");
        },

        formatStatus(status) {
            if (!status) return "";

            const statusMap = {
                creating: "Đang tạo",
                sending: "Đang gửi",
                received: "Đã nhận",
                cancelled: "Đã hủy",
            };

            return statusMap[status] || status;
        },
        statusClass(status) {
            if (!status) return "";

            switch (status) {
                case "creating":
                    return "text-yellow-600";
                case "sending":
                    return "text-blue-600";
                case "received":
                    return "text-green-600";
                case "cancelled":
                    return "text-red-600";
                default:
                    return "";
            }
        },

        statusIcons(status) {
            if (!status) return "";

            switch (status) {
                case "creating":
                    return "fas fa-spinner";
                case "sending":
                    return "fas fa-shipping-fast";
                case "received":
                    return "fas fa-check-circle";
                case "cancelled":
                    return "fas fa-times-circle";
                default:
                    return "fas fa-info-circle";
            }
        },
        statusIcon(status) {
            switch (status) {
                case "approved":
                    return "fas fa-check-circle text-success";
                case "pending":
                    return "fas fa-clock text-warning";
                case "rejected":
                    return "fas fa-times-circle text-danger";
                default:
                    return "";
            }
        },
        formatCurrency(value) {
            if (!value) return "0";
            return new Intl.NumberFormat("vi-VN", {
                style: "currency",
                currency: "VND",
                maximumFractionDigits: 0,
            }).format(value);
        },
        pageChanged(page) {
            this.currentPage = page;
        },
        checkScreenSize() {
            this.isMobile = window.innerWidth < 768;
        },
        async fetchBienBanData(page = 1) {
            this.isLoading = true;
            try {
                // โหลดข้อมูลทั้งหมดครั้งเดียว
                const response = await axios.get("/api/bien-ban-nghiem-thu", {
                    params: {
                        per_page: 1000, // โหลดทั้งหมดหรือจำนวนมากๆ
                    },
                    headers: {
                        Authorization: "Bearer " + this.store.getToken,
                    },
                });

                this.allBienBanList = response.data.data;
                this.bienBanList = this.allBienBanList;

                // Set current page
                this.currentPage = page;

                // ดึงรายการโครงการลงทุนที่มีอยู่ทั้งหมด
                this.extractInvestmentProjects();

                // Initialize unique values for dropdown filters
                this.updateUniqueValues("tram");
                this.updateUniqueValues("vu_dau_tu");
                this.updateUniqueValues("hinh_thuc_thuc_hien_dv");
                this.updateUniqueValues("trang_thai_nhan_hs");
            } catch (error) {
                console.error("Error fetching biên bản:", error);

                if (error.response?.status === 401) {
                    this.handleAuthError();
                } else {
                    alert(
                        "Có lỗi xảy ra khi tải dữ liệu. Vui lòng thử lại sau."
                    );
                }
            } finally {
                this.isLoading = false;
            }
        },
        extractInvestmentProjects() {
            // Get unique investment projects
            this.investmentProjects = [
                ...new Set(this.bienBanList.map((item) => item.vu_dau_tu)),
            ].filter(Boolean);
        },
        viewDetails(item) {
            // You can implement a detail view here
            // For example:
            // this.$router.push(`/bien-ban-nghiem-thu/${item.ma_nghiem_thu}`);

            // Or show a modal with details
            console.log("Viewing details for:", item);
        },
        handleAuthError() {
            // Clear localStorage
            localStorage.removeItem("web_token");
            localStorage.removeItem("web_user");

            // Clear store
            this.store.logout();

            // Redirect to login
            this.$router.push("/login");
        },
        toggleFilter(column) {
            if (this.activeFilter === column) {
                this.activeFilter = null;
            } else {
                this.activeFilter = column;

                // If it's a dropdown filter column, update unique values
                if (
                    [
                        "tram",
                        "vu_dau_tu",
                        "hinh_thuc_thuc_hien_dv",
                        "trang_thai_nhan_hs",
                    ].includes(column)
                ) {
                    this.updateUniqueValues(column);
                }
            }
        },

        updateUniqueValues(column) {
            // Get unique values for dropdown columns
            this.uniqueValues[column] = [
                ...new Set(
                    this.bienBanList.map((item) => item[column]).filter(Boolean) // Remove null/undefined values
                ),
            ];
        },

        resetFilter(column) {
            if (
                [
                    "tram",
                    "vu_dau_tu",
                    "hinh_thuc_thuc_hien_dv",
                    "trang_thai_nhan_hs",
                ].includes(column)
            ) {
                this.selectedFilterValues[column] = [];
            } else {
                this.columnFilters[column] = "";
            }
            this.currentPage = 1;
        },

        resetAllFilters() {
            // Reset text filters
            for (const key in this.columnFilters) {
                this.columnFilters[key] = "";
            }

            // Reset dropdown filters
            for (const key in this.selectedFilterValues) {
                this.selectedFilterValues[key] = [];
            }

            // Reset global filters
            this.search = "";
            this.statusFilter = "all";
            this.investmentFilter = "all";

            this.currentPage = 1;
        },

        applyFilter(column) {
            // Just close the filter dropdown as the filter is applied automatically
            this.activeFilter = null;
            this.currentPage = 1;
        },
    },
    watch: {
        search() {
            this.currentPage = 1; // รีเซ็ตกลับไปหน้าแรก
        },
        statusFilter() {
            this.currentPage = 1; // รีเซ็ตกลับไปหน้าแรก
        },
        investmentFilter() {
            this.currentPage = 1; // รีเซ็ตกลับไปหน้าแรก
        },
        columnFilters: {
            handler() {
                this.currentPage = 1;
            },
            deep: true,
        },
        selectedFilterValues: {
            handler() {
                this.currentPage = 1;
            },
            deep: true,
        },
    },
    mounted() {
        this.fetchBienBanData();
        window.addEventListener("resize", this.checkScreenSize);
    },
    beforeUnmount() {
        window.removeEventListener("resize", this.checkScreenSize);
    },
};
</script>

<style scoped>
/* Styling */
.text-gray-400 {
    color: #9ca3af;
}
.container {
    padding: 1rem;
}
.card-container {
    display: flex;
    flex-direction: column;
}
.card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: box-shadow 0.3s ease;
}
.card:hover {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}
.desktop-row {
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
}
.desktop-row:hover {
    background-color: #f0f9f0;
    box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);
}
.table-auto th,
.table-auto td {
    text-align: left;
    white-space: nowrap;
    overflow: visible;
    text-overflow: clip;
    word-wrap: break-word;
    font-size: 0.875rem;
}
.table-auto th {
    background-color: #e7e7e7;
    border: 1px solid #e5e7eb;
    padding: 0.75rem;
    vertical-align: top;
}
.pagination-card {
    padding: 0.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border-radius: 0.5rem;
    background-color: #fff;
    max-height: 50px;
    display: flex;
    justify-content: center;
}

.desktop-row.selected {
    background-color: #e6f4ea;
}

.form-checkbox {
    cursor: pointer;
    width: 1rem;
    height: 1rem;
    border-radius: 0.25rem;
    border: 1px solid #d1d5db;
    transition: all 0.2s ease;
}

.form-checkbox:checked {
    background-color: #10b981;
    border-color: #10b981;
}

.form-checkbox:hover {
    border-color: #10b981;
}

.status-filter {
    position: relative;
    min-width: 200px;
}

.status-select {
    appearance: none;
    background-color: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    padding: 0.5rem 2.5rem 0.5rem 1rem;
    font-size: 0.875rem;
    line-height: 1.25rem;
    color: #374151;
    cursor: pointer;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236B7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.5rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
    transition: all 0.2s ease;
}

.status-select:hover {
    border-color: #10b981;
}

.status-select:focus {
    outline: none;
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
}

.status-option {
    padding: 0.5rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.search-input {
    min-width: 250px;
}

/* Mobile responsive styles */
@media (max-width: 768px) {
    .status-filter {
        min-width: 100%;
        margin-bottom: 1rem;
    }

    .search-input {
        min-width: 100%;
    }
}

.investment-filter {
    position: relative;
    min-width: 200px;
}

.investment-select {
    appearance: none;
    background-color: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    padding: 0.5rem 2.5rem 0.5rem 1rem;
    font-size: 0.875rem;
    line-height: 1.25rem;
    color: #374151;
    cursor: pointer;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236B7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.5rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
    transition: all 0.2s ease;
}

.investment-select:hover {
    border-color: #10b981;
}

.investment-select:focus {
    outline: none;
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
}

/* Mobile responsive styles */
@media (max-width: 768px) {
    .investment-filter {
        min-width: 100%;
        margin-bottom: 1rem;
    }
}
/* จัดการการแสดงผลของไอคอนร่วมกับข้อความ */
.flex.items-center {
    display: flex;
    align-items: center;
}
.mr-1 {
    margin-right: 0.25rem;
}

/* Add new styles for filters */
th {
    position: relative;
}

.table-auto th {
    min-width: 150px;
    white-space: nowrap;
}

.desktop-row {
    cursor: pointer;
    transition: all 0.2s ease;
}

.desktop-row:hover {
    background-color: #f0fff4;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transform: translateY(-1px);
}

/* Improve table appearance */
.table-auto {
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
    border-radius: 0.5rem;
    overflow: hidden;
}

.table-auto th:first-child {
    border-top-left-radius: 0.5rem;
}

.table-auto th:last-child {
    border-top-right-radius: 0.5rem;
}

.table-auto tr:last-child td:first-child {
    border-bottom-left-radius: 0.5rem;
}

.table-auto tr:last-child td:last-child {
    border-bottom-right-radius: 0.5rem;
}

/* Filter icon styling */
.fa-filter {
    font-size: 0.75rem;
}

/* Filter dropdown positioning */
.relative {
    position: relative;
}

/* Checkbox styling */
input[type="checkbox"] {
    cursor: pointer;
}

/* Improve active filter visibility */
.text-green-500 {
    color: #10b981;
}

/* Improve dropdown shadow */
.shadow-lg {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1),
        0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

/* Ensure filter dropdowns appear on top */
.z-10 {
    z-index: 10;
}

/* Fix for filter dropdowns positioning */
.table-auto th {
    position: relative;
}

/* Prevent dropdowns from being cut off by overflow-x-auto */
.absolute.mt-1.bg-white.p-2.rounded.shadow-lg.z-10 {
    position: absolute; /* Changed from fixed to absolute */
    top: 100%; /* Position below the filter button */
    left: 0; /* Align with the left edge of the parent */
    transform: translateY(6px); /* Small offset for better appearance */
    z-index: 1050; /* Ensure dropdown appears above other elements */
    overflow: auto; /* Handle overflow for long dropdowns */
    max-height: 200px; /* Limit height to prevent excessive size */
}

/* Ensure parent elements have proper positioning */
th {
    position: relative; /* Ensure dropdowns are positioned relative to the column */
}

/* Add overflow handling for table container */
.overflow-x-auto {
    position: relative;
    overflow: visible; /* Allow dropdowns to overflow outside the container */
}

/* Improve table overflow behavior */
.overflow-x-auto {
    position: relative;
    min-width: 100%;
}

/* Add responsive table wrapper */
.table-responsive-wrapper {
    position: relative;
}
</style>
