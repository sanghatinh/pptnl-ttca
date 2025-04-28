<template>
    <!-- Loading starts -->
    <div id="loading-wrapper" v-if="isLoading">
        <div class="spinner-border" role="status">
            <span class="sr-only">Loadding...</span>
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
                <!-- Vertical ellipsis dropdown menu -->
                <div class="actions-menu">
                    <div class="dropdown">
                        <button
                            class="btn btn-light btn-icon"
                            type="button"
                            id="actionMenuButton"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                        >
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul
                            class="dropdown-menu shadow-sm"
                            aria-labelledby="actionMenuButton"
                        >
                            <li>
                                <a
                                    class="dropdown-item"
                                    href="#"
                                    @click.prevent="showExportModal"
                                >
                                    <i
                                        class="fas fa-file-excel text-success me-2"
                                    ></i>
                                    Export to excel
                                </a>
                            </li>
                            <li>
                                <a
                                    class="dropdown-item"
                                    href="#"
                                    @click.prevent="importData"
                                >
                                    <i
                                        class="fas fa-upload text-primary me-2"
                                    ></i>
                                    Import data
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <!-- Add this button next to actions-menu in the toolbar -->
                <button
                    class="btn btn-success d-flex align-items-center gap-2"
                    @click="showCreatePaymentRequestModal"
                    :disabled="selectedItems.length === 0"
                >
                    <i class="fas fa-file-invoice"></i>
                    <span>Tạo phiếu trình thanh toán</span>
                </button>
                <div
                    class="col d-flex justify-content-end gap-3 align-items-center"
                >
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Tìm kiếm hồ sơ..."
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
                        placeholder="Tìm kiếm hồ sơ..."
                        class="w-full pl-10 pr-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm"
                    />
                </div>
            </div>

            <!-- Filters Section -->
            <div class="filter-section">
                <!-- Status Filter -->
                <div class="mb-2.5">
                    <label class="text-sm font-medium text-gray-700 mb-1 block"
                        >Tình trạng thanh toán</label
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
                            <option value="all">Chọn tất cả</option>
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
                    <!-- Reset filters button -->
                    <span
                        class="reset-all-filters-btn"
                        title="Reset all filters"
                        @click="resetAllFilters"
                    >
                        <i class="fas fa-redo-alt"></i>
                    </span>
                    <div class="overflow-x-auto">
                        <table class="table-auto w-full">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2">
                                        <input
                                            type="checkbox"
                                            @click="toggleSelectAll($event)"
                                            :checked="isAllSelected"
                                            class="form-checkbox h-4 w-4 text-green-600"
                                        />
                                    </th>
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
                                                placeholder="Tìm kiếm Mã nghiệm thu..."
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
                                    <!-- Add this column header after "Ngày nhận hồ sơ" and before "Tình trạng giao nhận hồ sơ" -->
                                    <th class="px-4 py-2">
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <span>Cán bộ nông vụ</span>
                                            <button
                                                @click="
                                                    toggleFilter(
                                                        'can_bo_nong_vu'
                                                    )
                                                "
                                                class="ml-2 text-gray-500 hover:text-gray-700"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.can_bo_nong_vu,
                                                    }"
                                                ></i>
                                            </button>
                                        </div>
                                        <div
                                            v-if="
                                                activeFilter ===
                                                'can_bo_nong_vu'
                                            "
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10 w-64"
                                        >
                                            <input
                                                v-model="
                                                    columnFilters.can_bo_nong_vu
                                                "
                                                type="text"
                                                placeholder="Tìm kiếm Cán bộ nông vụ..."
                                                class="w-full p-2 border rounded mb-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                            />
                                            <div class="flex justify-between">
                                                <button
                                                    @click="
                                                        resetFilter(
                                                            'can_bo_nong_vu'
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
                                                placeholder="Tìm kiếm Tiêu đề..."
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
                                            <span
                                                >Khách hàng cá nhân ĐT mía</span
                                            >
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
                                            <span
                                                >Khách hàng doanh nghiệp ĐT
                                                mía</span
                                            >
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
                                                placeholder="Tìm kiếm khách hàng DN..."
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
                                    <th class="px-4 py-2">Tổng tiền</th>
                                    <th class="px-4 py-2">Tổng tiền tạm giữ</th>
                                    <th class="px-4 py-2">
                                        Tổng tiền thanh toán
                                    </th>
                                    <th class="px-4 py-2">
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <span>Người giao hồ sơ</span>
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
                                                placeholder="Tìm kiếm Người giao hồ sơ..."
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
                                            <span>Người nhận hồ sơ</span>
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
                                                placeholder="Tìm kiếm Người nhận hồ sơ..."
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
                                            <span>Ngày nhận hồ sơ</span>
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
                                                type="date"
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
                                            <span
                                                >Tình trạng giao nhận hồ
                                                sơ</span
                                            >
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
                                    <td class="border px-4 py-2" @click.stop>
                                        <input
                                            type="checkbox"
                                            :value="item.ma_nghiem_thu"
                                            v-model="selectedItems"
                                            :disabled="!canBeSelected(item)"
                                            class="form-checkbox h-4 w-4 text-green-600"
                                            @click.stop
                                        />
                                    </td>
                                    <td class="border px-4 py-2">
                                        {{ item.ma_nghiem_thu }}
                                    </td>
                                    <td class="border px-4 py-2">
                                        {{ item.tram }}
                                    </td>
                                    <td class="border px-4 py-2">
                                        <template v-if="item.can_bo_nong_vu">
                                            <i
                                                class="fas fa-user-tie text-purple-500"
                                            ></i>
                                            {{ item.can_bo_nong_vu }}
                                        </template>
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
                        <strong>Tổng tiền:</strong>
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
    <!-- Export to Excel Modal -->
    <div
        class="modal fade"
        id="exportModal"
        tabindex="-1"
        aria-labelledby="exportModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title text-success" id="exportModalLabel">
                        <i class="fas fa-file-excel text-success me-2"></i>
                        Export to Excel
                    </h5>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                        @click="closeExportModal"
                    ></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">Chọn định dạng xuất:</p>
                    <div class="d-grid gap-2">
                        <button
                            @click="exportToExcelCurrentPage"
                            class="btn btn-outline-success"
                        >
                            <i class="fas fa-file-export me-2"></i>
                            Chỉ xuất trang hiện tại
                        </button>
                        <button
                            @click="exportToExcelAllPages"
                            class="btn btn-success"
                        >
                            <i class="fas fa-table me-2"></i>
                            Xuất tất cả
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Data Modal -->
    <div
        class="modal fade"
        id="importModal"
        tabindex="-1"
        aria-labelledby="importModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title text-primary" id="importModalLabel">
                        <i class="fas fa-upload text-primary me-2"></i>
                        Import data
                    </h5>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                        @click="closeImportModal"
                    ></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning mb-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <small
                            >Import data chỉ thay thế dữ liệu hiện có. Mã nhập
                            mới Không xóa dữ liệu tham chiếu gốc Vui lòng xác
                            minh thông tin là chính xác trước khi tiếp
                            tục.</small
                        >
                    </div>

                    <div class="mb-3">
                        <label for="importFile" class="form-label"
                            >Select file</label
                        >
                        <input
                            class="form-control"
                            type="file"
                            id="importFile"
                            @change="handleFileSelected"
                            accept=".csv,.xlsx"
                        />
                        <div class="form-text">Support files: .csv, .xlsx</div>
                    </div>

                    <div v-if="uploadProgress > 0" class="mb-3">
                        <label class="form-label">Upload progress</label>
                        <div class="progress">
                            <div
                                class="progress-bar progress-bar-striped progress-bar-animated"
                                role="progressbar"
                                :style="`width: ${uploadProgress}%`"
                                :aria-valuenow="uploadProgress"
                                aria-valuemin="0"
                                aria-valuemax="100"
                            >
                                {{ uploadProgress }}%
                            </div>
                        </div>
                    </div>

                    <div v-if="processingRecords" class="mb-3">
                        <label class="form-label">Processing data</label>
                        <div class="progress">
                            <div
                                class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                role="progressbar"
                                :style="`width: ${processingProgress}%`"
                                :aria-valuenow="processingProgress"
                                aria-valuemin="0"
                                aria-valuemax="100"
                            >
                                {{ processedRecords }} / {{ totalRecords }}
                            </div>
                        </div>
                    </div>

                    <div v-if="importErrors.length > 0" class="mt-3">
                        <div class="alert alert-danger">
                            <h6 class="alert-heading">Import error</h6>
                            <ul class="mb-0 ps-3">
                                <li
                                    v-for="(error, index) in importErrors.slice(
                                        0,
                                        5
                                    )"
                                    :key="index"
                                >
                                    {{ error }}
                                </li>
                            </ul>
                            <div
                                v-if="importErrors.length > 5"
                                class="mt-2 text-center"
                            >
                                <small>Other errors...</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                        @click="closeImportModal"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="btn btn-primary"
                        @click="startImport"
                        :disabled="!selectedFile || isImporting"
                    >
                        <i class="fas fa-upload me-2"></i>
                        <span v-if="isImporting">Importing...</span>
                        <span v-else>Import</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Request Modal -->
    <div
        class="modal fade"
        id="paymentRequestModal"
        tabindex="-1"
        aria-labelledby="paymentRequestModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="paymentRequestModalLabel">
                        <i class="fas fa-file-invoice me-2 text-success"></i>
                        Tạo phiếu trình thanh toán
                    </h5>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                        @click="closePaymentRequestModal"
                    ></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="submitPaymentRequest">
                        <div class="mb-3">
                            <label for="requestTitle" class="form-label"
                                >Tiêu đề</label
                            >
                            <input
                                type="text"
                                class="form-control"
                                id="requestTitle"
                                v-model="paymentRequest.title"
                                readonly
                            />
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="requestDate" class="form-label"
                                    >Ngày tạo</label
                                >
                                <input
                                    type="date"
                                    class="form-control"
                                    id="requestDate"
                                    v-model="paymentRequest.created_date"
                                    required
                                />
                            </div>
                            <div class="col-md-6">
                                <label
                                    for="investmentProject"
                                    class="form-label"
                                    >Vụ đầu tư</label
                                >
                                <select
                                    class="form-select"
                                    id="investmentProject"
                                    v-model="paymentRequest.investment_project"
                                    required
                                >
                                    <option value="" disabled selected>
                                        Chọn vụ đầu tư
                                    </option>
                                    <option
                                        v-for="project in investmentProjectsList"
                                        :key="project.Ma_Vudautu"
                                        :value="project.Ma_Vudautu"
                                    >
                                        {{ project.Ten_Vudautu }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="paymentType" class="form-label"
                                    >Loại thanh toán</label
                                >
                                <select
                                    class="form-select"
                                    id="paymentType"
                                    v-model="paymentRequest.payment_type"
                                    required
                                >
                                    <option value="" disabled selected>
                                        Chọn loại thanh toán
                                    </option>
                                    <option value="Nghiệm thu dịch vụ">
                                        Nghiệm thu dịch vụ
                                    </option>
                                    <option value="Phiếu giao nhận hom giống">
                                        Phiếu giao nhận hom giống
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label
                                    for="paymentInstallment"
                                    class="form-label"
                                    >Số đợt thanh toán</label
                                >
                                <input
                                    type="number"
                                    class="form-control"
                                    id="paymentInstallment"
                                    v-model="paymentRequest.payment_installment"
                                    min="1"
                                    required
                                />
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="proposalNumber" class="form-label"
                                >Số tờ trình</label
                            >
                            <input
                                type="text"
                                class="form-control"
                                id="proposalNumber"
                                v-model="paymentRequest.proposal_number"
                                maxlength="50"
                                required
                            />
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Đã chọn
                            <strong>{{ selectedItems.length }}</strong> biên bản
                            nghiệm thu để tạo phiếu trình thanh toán
                        </div>

                        <div
                            v-if="duplicateRecords.length > 0"
                            class="alert alert-warning"
                        >
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Lưu ý:</strong> Một số biên bản nghiệm thu
                            đã được sử dụng trong phiếu trình thanh toán khác:
                            <ul class="mb-0 mt-2">
                                <li
                                    v-for="(item, index) in duplicateRecords"
                                    :key="index"
                                >
                                    {{ item.ma_nghiem_thu }} - Đã được sử dụng
                                    trong phiếu {{ item.ma_trinh_thanh_toan }}
                                </li>
                            </ul>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                        @click="closePaymentRequestModal"
                    >
                        Hủy
                    </button>
                    <button
                        type="button"
                        class="btn btn-success"
                        @click="submitPaymentRequest"
                        :disabled="isSubmitting || duplicateRecords.length > 0"
                    >
                        <i class="fas fa-save me-1"></i>
                        <span v-if="isSubmitting">Đang lưu...</span>
                        <span v-else>Lưu phiếu trình</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import { useStore } from "../../Store/Auth";
// import { ref, computed, onMounted } from "vue";
import { Bootstrap5Pagination } from "laravel-vue-pagination"; // Add this import
// Import Bootstrap JS explicitly
import "bootstrap/dist/js/bootstrap.bundle.min.js";
import "bootstrap/dist/css/bootstrap.min.css"; // Import Bootstrap CSS
import Swal from "sweetalert2"; // Import SweetAlert2
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
            userPosition: null,
            userStation: null,
            userEmployeeCode: null,
            statusOptions: [
                { code: "all", name: "Tất cả" },
                { code: "approved", name: "อนุมัติแล้ว" },
                { code: "pending", name: "รอการอนุมัติ" },
                { code: "rejected", name: "ปฏิเสธ" },
            ],
            paginationClasses: {
                ul: "flex list-none pagination",
                a: "page-link px-3 py-2 bg-white border border-gray-300 rounded hover:bg-gray-100",
                active: "bg-green-500 text-white active",
                disabled: "opacity-50 cursor-not-allowed disabled",
            },
            activeFilter: null,
            columnFilters: {
                ma_nghiem_thu: "",
                tram: "",
                can_bo_nong_vu: "",
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
            exportModal: null, // Add this to store modal reference
            selectedFile: null,
            uploadProgress: 0,
            processingRecords: false,
            processingProgress: 0,
            processedRecords: 0,
            totalRecords: 0,
            importErrors: [],
            isImporting: false,
            selectedItems: [],
            paymentRequestModal: null,
            paymentRequest: {
                title: "",
                created_date: new Date().toISOString().split("T")[0], // Today's date in YYYY-MM-DD format
                investment_project: "",
                payment_type: "",
                payment_installment: 1,
                proposal_number: "",
            },
            investmentProjectsList: [],
            duplicateRecords: [],
            isSubmitting: false,
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
                            this.formatDateForComparison(item.ngay_nhan) ===
                                this.formatDateForComparison(
                                    this.columnFilters.ngay_nhan
                                ))) &&
                    (!this.columnFilters.can_bo_nong_vu ||
                        (item.can_bo_nong_vu &&
                            item.can_bo_nong_vu
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.can_bo_nong_vu.toLowerCase()
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
        isAllSelected() {
            return (
                this.filteredItems.length > 0 &&
                this.selectedItems.length ===
                    this.filteredItems.filter(
                        (item) => item.trang_thai_nhan_hs === "received"
                    ).length
            );
        },
    },
    methods: {
        formatDate(date) {
            if (!date) return "";
            const d = new Date(date);
            const day = d.getDate().toString().padStart(2, "0");
            const month = (d.getMonth() + 1).toString().padStart(2, "0");
            const year = d.getFullYear(); // Use Gregorian year directly
            return `${day}/${month}/${year}`;
        },

        formatStatus(status) {
            if (!status) return "";

            const statusMap = {
                creating: "Nháp",
                sending: "Đang nộp",
                received: "Đã nhận",
                cancelled: "Hủy",
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
                currency: "KIP",
                maximumFractionDigits: 0,
            }).format(value);
        },
        formatDateForComparison(date) {
            if (!date) return "";
            const d = new Date(date);
            const year = d.getFullYear();
            const month = (d.getMonth() + 1).toString().padStart(2, "0");
            const day = d.getDate().toString().padStart(2, "0");
            return `${year}-${month}-${day}`;
        },
        pageChanged(page) {
            this.currentPage = page;
        },
        checkScreenSize() {
            this.isMobile = window.innerWidth < 768;
        },
        async fetchUserInfo() {
            try {
                const response = await axios.get("/api/user-info", {
                    headers: {
                        Authorization: "Bearer " + this.store.getToken,
                    },
                });

                const user = response.data;
                this.userPosition = user.position;
                this.userStation = user.station;
                this.userEmployeeCode = user.ma_nhan_vien;

                // After getting user info, fetch the bien ban data
                await this.fetchBienBanData();
            } catch (error) {
                console.error("Error fetching user info:", error);
                if (error.response?.status === 401) {
                    this.handleAuthError();
                }
            }
        },

        async fetchBienBanData(page = 1) {
            this.isLoading = true;
            try {
                const response = await axios.get("/api/bien-ban-nghiem-thu", {
                    params: {
                        page,
                        per_page: 1000,
                        position: this.userPosition,
                        station: this.userStation,
                        ma_nhan_vien: this.userEmployeeCode,
                    },
                    headers: {
                        Authorization: "Bearer " + this.store.getToken,
                    },
                });

                if (response.data.success) {
                    this.allBienBanList = response.data.data;
                    this.bienBanList = this.allBienBanList;
                    this.currentPage = page;
                    this.extractInvestmentProjects();
                    this.updateUniqueValues("tram");
                    this.updateUniqueValues("vu_dau_tu");
                    this.updateUniqueValues("hinh_thuc_thuc_hien_dv");
                    this.updateUniqueValues("trang_thai_nhan_hs");
                } else {
                    throw new Error(response.data.message);
                }
            } catch (error) {
                console.error("Error fetching biên bản:", error);
                if (error.response?.status === 401) {
                    this.handleAuthError();
                } else {
                    Swal.fire({
                        title: "Error!",
                        text: "Có lỗi xảy ra khi tải dữ liệu. Vui lòng thử lại sau.",
                        icon: "error",
                        confirmButtonText: "OK",
                    });
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
        async viewDetails(item) {
            this.isLoading = true;
            try {
                // ตรวจสอบสิทธิ์การเข้าถึงก่อนการนำทาง
                const response = await axios.get(
                    `/api/bien-ban-nghiemthu/${item.ma_nghiem_thu}/check-access`,
                    {
                        headers: {
                            Authorization: "Bearer " + this.store.getToken,
                        },
                    }
                );

                this.isLoading = false;

                if (response.data.hasAccess) {
                    // มีสิทธิ์เข้าถึง - นำทางไปยังหน้ารายละเอียด
                    this.$router.push(
                        `/Details_NghiemthuDV/${item.ma_nghiem_thu}`
                    );
                } else {
                    // ไม่มีสิทธิ์เข้าถึง - แสดงข้อความและนำทางไปยังหน้า Unauthorized
                    Swal.fire({
                        icon: "warning",
                        title: "Hạn chế truy cập",
                        text: "Bạn không có quyền truy cập tài liệu này",
                        confirmButtonText: "Đã hiểu",
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: "btn btn-primary",
                        },
                    }).then(() => {
                        this.$router.push("/unauthorized");
                    });
                }
            } catch (error) {
                this.isLoading = false;
                console.error("Error checking access:", error);

                if (error.response?.status === 401) {
                    this.handleAuthError();
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Đã xảy ra lỗi",
                        text: "Không thể kiểm tra quyền truy cập",
                        confirmButtonText: "Thử lại",
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: "btn btn-primary",
                        },
                    });
                }
            }
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

        //export to excel functions
        showExportModal() {
            try {
                // Ensure Bootstrap is properly imported and initialized
                import("bootstrap/dist/js/bootstrap.bundle.min.js")
                    .then((bootstrap) => {
                        const Modal = bootstrap.Modal;
                        const modalElement =
                            document.getElementById("exportModal");

                        if (modalElement) {
                            this.exportModal = new Modal(modalElement);
                            this.exportModal.show();
                        } else {
                            console.error("Modal element not found");
                        }
                    })
                    .catch((err) => {
                        console.error("Failed to load Bootstrap:", err);
                        // Fallback method using direct DOM manipulation
                        const modalElement =
                            document.getElementById("exportModal");
                        if (modalElement) {
                            modalElement.classList.add("show");
                            modalElement.style.display = "block";
                            document.body.classList.add("modal-open");

                            // Add backdrop
                            const backdrop = document.createElement("div");
                            backdrop.classList.add(
                                "modal-backdrop",
                                "fade",
                                "show"
                            );
                            document.body.appendChild(backdrop);
                        }
                    });
            } catch (error) {
                console.error("Error showing modal:", error);
            }
        },

        exportToExcelCurrentPage() {
            // Get the current page data from paginatedItems
            if (
                this.paginatedItems.data &&
                this.paginatedItems.data.length > 0
            ) {
                this.generateExcel(
                    this.paginatedItems.data,
                    "bien_ban_current_page"
                );

                // Close modal after export
                this.closeExportModal();
            } else {
                alert("Không tìm thấy dữ liệu cần xuất.");
                // Close modal after alert
                this.closeExportModal();
            }
        },

        exportToExcelAllPages() {
            this.isLoading = true;
            // Use all filtered items instead of just the current page
            setTimeout(() => {
                if (this.filteredItems && this.filteredItems.length > 0) {
                    this.generateExcel(this.filteredItems, "bien_ban_all_data");
                } else {
                    alert("Không tìm thấy dữ liệu cần xuất.");
                }
                this.isLoading = false;

                // Close modal after export
                this.closeExportModal();
            }, 100);
        },

        generateExcel(data, filename) {
            try {
                // Import xlsx and FileSaver dynamically
                import("xlsx").then((XLSX) => {
                    import("file-saver").then((module) => {
                        const FileSaver = module.default;

                        // Check if data exists and is not empty
                        if (!data || data.length === 0) {
                            alert("ไม่พบข้อมูลที่ต้องการส่งออก");
                            return;
                        }

                        // Format data for export
                        const exportData = data.map((item) => ({
                            "Mã nghiệm thu": item.ma_nghiem_thu || "",
                            Trạm: item.tram || "",
                            "Cán bộ nông vụ": item.can_bo_nong_vu || "", // Add new column
                            "Vụ đầu tư": item.vu_dau_tu || "",
                            "Tiêu đề": item.tieu_de || "",
                            "Khách hàng cá nhân":
                                item.khach_hang_ca_nhan_dt_mia || "",
                            "Khách hàng doanh nghiệp":
                                item.khach_hang_doanh_nghiep_dt_mia || "",
                            "Hợp đồng đầu tư mía":
                                item.hop_dong_dau_tu_mia || "",
                            "Hình thức thực hiện DV":
                                item.hinh_thuc_thuc_hien_dv || "",
                            "Hợp đồng cung ứng DV":
                                item.hop_dong_cung_ung_dich_vu || "",
                            "Tổng tiền dịch vụ": item.tong_tien_dich_vu || 0,
                            "Tổng tiền tạm giữ": item.tong_tien_tam_giu || 0,
                            "Tổng tiền thanh toán":
                                item.tong_tien_thanh_toan || 0,
                            "Người giao": item.nguoi_giao || "",
                            "Người nhận": item.nguoi_nhan || "",
                            "Ngày nhận": item.ngay_nhan
                                ? this.formatDate(item.ngay_nhan)
                                : "",
                            "Trạng thái": item.trang_thai_nhan_hs
                                ? this.formatStatus(item.trang_thai_nhan_hs)
                                : "",
                        }));

                        // Only proceed if we have data after formatting
                        if (exportData.length === 0) {
                            alert("ไม่พบข้อมูลที่ต้องการส่งออก");
                            return;
                        }

                        // Create a worksheet
                        const worksheet = XLSX.utils.json_to_sheet(exportData);

                        // Set column widths
                        const columnWidths = [
                            { wpx: 120 }, // Mã nghiệm thu
                            { wpx: 100 }, // Trạm
                            { wpx: 120 }, // Cán bộ nông vụ
                            { wpx: 120 }, // Vụ đầu tư
                            { wpx: 150 }, // Tiêu đề
                            { wpx: 150 }, // Khách hàng cá nhân ĐT mía
                            { wpx: 150 }, // Khách hàng doanh nghiệp ĐT mía
                            { wpx: 150 }, // Hợp đồng đầu tư mía
                            { wpx: 150 }, // Hình thức thực hiện DV
                            { wpx: 150 }, // Hợp đồng cung ứng dịch vụ
                            { wpx: 120 }, // Tổng tiền
                            { wpx: 120 }, // Tổng tiền tạm giữ
                            { wpx: 120 }, // Tổng tiền thanh toán
                            { wpx: 120 }, // Người giao hồ sơ
                            { wpx: 120 }, // Người nhận hồ sơ
                            { wpx: 100 }, // Ngày nhận hồ sơ
                            { wpx: 120 }, // สถานะ
                        ];
                        worksheet["!cols"] = columnWidths;

                        // Create a workbook
                        const workbook = XLSX.utils.book_new();
                        XLSX.utils.book_append_sheet(
                            workbook,
                            worksheet,
                            "Biên bản nghiệm thu"
                        );

                        // Create an Excel file
                        const excelBuffer = XLSX.write(workbook, {
                            bookType: "xlsx",
                            type: "array",
                        });

                        // Save the file
                        const blob = new Blob([excelBuffer], {
                            type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                        });
                        FileSaver.saveAs(
                            blob,
                            `${filename}_${
                                new Date().toISOString().split("T")[0]
                            }.xlsx`
                        );

                        // Show success notification
                        this.$toast?.success("Export to Excel succeed") ||
                            alert("Export to Excel succeed");
                    });
                });
            } catch (error) {
                console.error("Export error:", error);
                alert("Đã xảy ra lỗi khi xuất dữ liệu. Vui lòng thử lại.");
                this.isLoading = false;
            }
        },
        closeExportModal() {
            // Check if we have stored a modal reference and use it
            if (this.exportModal) {
                this.exportModal.hide();
            } else {
                // Try to close using direct DOM manipulation if Bootstrap isn't working properly
                const modalElement = document.getElementById("exportModal");
                if (modalElement) {
                    modalElement.classList.remove("show");
                    modalElement.style.display = "none";
                    document.body.classList.remove("modal-open");

                    // Remove backdrop
                    const backdrop = document.querySelector(".modal-backdrop");
                    if (backdrop) {
                        backdrop.remove();
                    }
                }
            }
        },
        importData() {
            // Show the import modal
            try {
                import("bootstrap/dist/js/bootstrap.bundle.min.js")
                    .then((bootstrap) => {
                        const Modal = bootstrap.Modal;
                        const modalElement =
                            document.getElementById("importModal");

                        if (modalElement) {
                            const importModal = new Modal(modalElement);
                            importModal.show();
                        } else {
                            console.error("Import modal element not found");
                        }
                    })
                    .catch((err) => {
                        console.error("Failed to load Bootstrap:", err);

                        // Fallback method using direct DOM manipulation
                        const modalElement =
                            document.getElementById("importModal");
                        if (modalElement) {
                            modalElement.classList.add("show");
                            modalElement.style.display = "block";
                            document.body.classList.add("modal-open");

                            // Add backdrop
                            const backdrop = document.createElement("div");
                            backdrop.classList.add(
                                "modal-backdrop",
                                "fade",
                                "show"
                            );
                            document.body.appendChild(backdrop);
                        }
                    });
            } catch (error) {
                console.error("Error showing import modal:", error);
            }
        },

        handleFileSelected(event) {
            const file = event.target.files[0];
            if (file) {
                // Validate file type
                const fileType = file.name.split(".").pop().toLowerCase();
                if (!["csv", "xlsx"].includes(fileType)) {
                    alert("Vui lòng chọn đúng tập tin. (CSV hoặc Excel)");
                    event.target.value = ""; // Clear the file input
                    this.selectedFile = null;
                    return;
                }

                this.selectedFile = file;
                this.importErrors = [];
                console.log("File selected:", file.name);
            } else {
                this.selectedFile = null;
            }
        },

        closeImportModal() {
            try {
                const modalElement = document.getElementById("importModal");
                if (modalElement) {
                    if (window.bootstrap) {
                        const modalInstance =
                            window.bootstrap.Modal.getInstance(modalElement);
                        if (modalInstance) {
                            modalInstance.hide();
                        }
                    } else {
                        // Fallback if bootstrap is not available
                        modalElement.classList.remove("show");
                        modalElement.style.display = "none";
                        document.body.classList.remove("modal-open");

                        // Remove backdrop
                        const backdrop =
                            document.querySelector(".modal-backdrop");
                        if (backdrop) {
                            backdrop.remove();
                        }
                    }
                }

                // Reset import state
                this.selectedFile = null;
                this.uploadProgress = 0;
                this.processingRecords = false;
                this.processingProgress = 0;
                this.processedRecords = 0;
                this.totalRecords = 0;
                this.importErrors = [];
            } catch (error) {
                console.error("Error closing import modal:", error);
            }
        },

        async startImport() {
            if (!this.selectedFile) {
                Swal.fire({
                    title: "Cảnh báo",
                    text: "Vui lòng chọn tệp để tải lên",
                    icon: "warning",
                    confirmButtonText: "OK",
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: "btn btn-success",
                    },
                });
                return;
            }

            // Confirm before proceeding with SweetAlert2
            const result = await Swal.fire({
                title: "Xác nhận tải lên",
                text: "Cảnh báo: Việc nhập dữ liệu sẽ thay đổi dữ liệu theo mã tài liệu được nhập. Bạn có chắc chắn muốn tiếp tục không?",
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "OK",
                cancelButtonText: "Hủy",
                buttonsStyling: false,
                customClass: {
                    confirmButton: "btn btn-success me-2",
                    cancelButton: "btn btn-outline-success",
                },
            });

            if (!result.isConfirmed) {
                return;
            }

            this.isImporting = true;
            this.uploadProgress = 0;
            this.processingRecords = false;
            this.processingProgress = 0;
            this.processedRecords = 0;
            this.totalRecords = 0;
            this.importErrors = [];

            try {
                const formData = new FormData();
                formData.append("file", this.selectedFile);

                // Start file upload with progress tracking
                const response = await axios.post(
                    "/api/import-bienban-nghiemthu",
                    formData,
                    {
                        headers: {
                            "Content-Type": "multipart/form-data",
                            Authorization: "Bearer " + this.store.getToken,
                        },
                        onUploadProgress: (progressEvent) => {
                            if (progressEvent.total) {
                                this.uploadProgress = Math.round(
                                    (progressEvent.loaded * 100) /
                                        progressEvent.total
                                );
                            } else {
                                this.uploadProgress = 50; // Show 50% if total is unknown
                            }
                        },
                    }
                );

                if (response.data.success) {
                    this.processingRecords = true;
                    this.totalRecords = response.data.totalRecords || 0;

                    // Start tracking processing status
                    this.checkImportProgress(response.data.importId);
                } else {
                    this.isImporting = false;
                    this.importErrors = response.data.errors || [
                        "Đã xảy ra lỗi không xác định trong quá trình nhập.",
                    ];
                    console.error("Import failed:", response.data);

                    // Use SweetAlert2 to show failure
                    Swal.fire({
                        title: "Thất bại",
                        text: "Nhập dữ liệu không thành công. Vui lòng kiểm tra lỗi và thử lại.",
                        icon: "error",
                        confirmButtonText: "OK",
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: "btn btn-success",
                        },
                    });
                }
            } catch (error) {
                this.isImporting = false;
                console.error("Import error:", error);

                if (error.response) {
                    if (error.response.status === 401) {
                        this.handleAuthError();
                    } else {
                        this.importErrors =
                            error.response.data.errors ||
                            Array.isArray(error.response.data.message)
                                ? error.response.data.message
                                : [
                                      error.response.data.message ||
                                          "Lỗi máy chủ",
                                  ];
                    }
                } else if (error.request) {
                    // Request made but no response received
                    this.importErrors = [
                        "Không có phản hồi từ máy chủ. Vui lòng kiểm tra kết nối mạng và thử lại.",
                    ];
                } else {
                    // Error setting up the request
                    this.importErrors = [
                        "Lỗi mạng. Vui lòng kiểm tra kết nối của bạn và thử lại: " +
                            error.message,
                    ];
                }

                // Use SweetAlert2 to show error
                Swal.fire({
                    title: "Lỗi",
                    text: "Lỗi khi nhập dữ liệu. Vui lòng kiểm tra chi tiết lỗi hiển thị bên dưới.",
                    icon: "error",
                    confirmButtonText: "OK",
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: "btn btn-success",
                    },
                });
            }
        },

        async checkImportProgress(importId) {
            if (!importId) {
                this.importErrors = ["Invalid import ID. Please try again."];
                return;
            }

            try {
                const response = await axios.get(
                    `/api/import-progress/${importId}`,
                    {
                        headers: {
                            Authorization: "Bearer " + this.store.getToken,
                        },
                    }
                );

                const data = response.data;

                if (data.finished) {
                    this.processedRecords = data.processed || 0;
                    this.totalRecords = data.total || 0;
                    this.processingProgress = 100;

                    if (data.success) {
                        // Import completed successfully
                        setTimeout(() => {
                            this.isImporting = false;
                            this.closeImportModal();

                            // Show success toast notification with SweetAlert2
                            Swal.fire({
                                title: "Thành công",
                                text:
                                    `Đã tải lên thành công ${this.processedRecords} bản ghi` +
                                    (this.importErrors.length > 0
                                        ? ` với ${this.importErrors.length} lỗi`
                                        : ""),
                                icon: "success",
                                toast: true,
                                position: "top-end",
                                showConfirmButton: false,
                                timer: 5000,
                                timerProgressBar: true,
                                didOpen: (toast) => {
                                    toast.addEventListener(
                                        "mouseenter",
                                        Swal.stopTimer
                                    );
                                    toast.addEventListener(
                                        "mouseleave",
                                        Swal.resumeTimer
                                    );
                                },
                            });

                            // Refresh data to get the latest changes
                            this.fetchBienBanData();
                        }, 1000);
                    } else {
                        // Import failed
                        this.isImporting = false;

                        // Make sure errors are properly presented
                        if (data.errors && data.errors.length > 0) {
                            this.importErrors = data.errors;
                        } else {
                            this.importErrors = [
                                "Đã xảy ra lỗi không xác định trong quá trình xử lý.",
                            ];
                        }

                        console.error("Import failed:", data);

                        // Show error notification with SweetAlert2
                        Swal.fire({
                            title: "Thất bại",
                            text: "Tải lên không thành công. Vui lòng kiểm tra thông báo lỗi bên dưới.",
                            icon: "error",
                            confirmButtonText: "OK",
                            buttonsStyling: false,
                            customClass: {
                                confirmButton: "btn btn-success",
                            },
                        });
                    }
                } else {
                    // Still processing, update progress
                    this.processedRecords = data.processed || 0;
                    this.totalRecords = data.total || this.totalRecords || 1;

                    // Avoid division by zero
                    if (this.totalRecords > 0) {
                        this.processingProgress = Math.min(
                            99, // Cap at 99% until finished
                            Math.round(
                                (this.processedRecords * 100) /
                                    this.totalRecords
                            )
                        );
                    } else {
                        this.processingProgress = 50; // Show 50% if total is unknown
                    }

                    // Update import errors if any are available during processing
                    if (data.errors && data.errors.length > 0) {
                        this.importErrors = data.errors;
                    }

                    // Check again after a delay
                    setTimeout(() => this.checkImportProgress(importId), 1000);
                }
            } catch (error) {
                console.error("Error checking import progress:", error);

                if (error.response && error.response.status === 401) {
                    this.handleAuthError();
                    return;
                }

                this.isImporting = false;
                this.importErrors = [
                    "Lỗi khi theo dõi tiến trình tải lên. Quá trình tải lên có thể vẫn đang được xử lý trong nền.",
                    "Vui lòng làm mới trang sau vài phút để xem dữ liệu đã được tải lên hay chưa.",
                ];

                if (error.response && error.response.data) {
                    if (typeof error.response.data === "string") {
                        this.importErrors.push(error.response.data);
                    } else if (error.response.data.message) {
                        this.importErrors.push(error.response.data.message);
                    }
                }
            }
        },
        toggleSelectAll(event) {
            if (event.target.checked) {
                // Select all eligible items
                this.selectedItems = this.filteredItems
                    .filter((item) => item.trang_thai_nhan_hs === "received")
                    .map((item) => item.ma_nghiem_thu);
            } else {
                // Deselect all
                this.selectedItems = [];
            }

            // Check for duplicates after selection changes
            this.checkForDuplicates();
        },

        canBeSelected(item) {
            return item.trang_thai_nhan_hs === "received";
        },

        async checkForDuplicates() {
            if (this.selectedItems.length === 0) {
                this.duplicateRecords = [];
                return;
            }

            try {
                const response = await axios.post(
                    "/api/check-payment-request-duplicates",
                    {
                        receipt_ids: this.selectedItems,
                    },
                    {
                        headers: {
                            Authorization: `Bearer ${this.store.getToken}`,
                        },
                    }
                );

                if (response.data.success) {
                    this.duplicateRecords = response.data.duplicates || [];
                }
            } catch (error) {
                console.error("Error checking for duplicates:", error);
                if (error.response && error.response.status === 401) {
                    this.handleAuthError();
                }
            }
        },

        async fetchInvestmentProjects() {
            try {
                const response = await axios.get("/api/investment-projects", {
                    headers: {
                        Authorization: `Bearer ${this.store.getToken}`,
                    },
                });

                if (response.data.success) {
                    this.investmentProjectsList = response.data.data;
                }
            } catch (error) {
                console.error("Error fetching investment projects:", error);
                if (error.response && error.response.status === 401) {
                    this.handleAuthError();
                }
            }
        },

        showCreatePaymentRequestModal() {
            if (this.selectedItems.length === 0) {
                Swal.fire({
                    title: "Thông báo",
                    text: "Vui lòng chọn ít nhất một biên bản nghiệm thu để tạo phiếu trình thanh toán",
                    icon: "info",
                    confirmButtonText: "Đồng ý",
                });
                return;
            }

            // Check if we have any duplicates
            this.checkForDuplicates();

            // Reset the form
            this.paymentRequest = {
                title: "",
                created_date: new Date().toISOString().split("T")[0],
                investment_project: "",
                payment_type: "",
                payment_installment: 1,
                proposal_number: "",
            };

            // Show the modal
            try {
                import("bootstrap/dist/js/bootstrap.bundle.min.js")
                    .then((bootstrap) => {
                        const Modal = bootstrap.Modal;
                        const modalElement = document.getElementById(
                            "paymentRequestModal"
                        );

                        if (modalElement) {
                            this.paymentRequestModal = new Modal(modalElement);
                            this.paymentRequestModal.show();
                        } else {
                            console.error("Modal element not found");
                        }
                    })
                    .catch((err) => {
                        console.error("Failed to load Bootstrap:", err);
                        // Fallback method
                        const modalElement = document.getElementById(
                            "paymentRequestModal"
                        );
                        if (modalElement) {
                            modalElement.classList.add("show");
                            modalElement.style.display = "block";
                            document.body.classList.add("modal-open");

                            // Add backdrop
                            const backdrop = document.createElement("div");
                            backdrop.classList.add(
                                "modal-backdrop",
                                "fade",
                                "show"
                            );
                            document.body.appendChild(backdrop);
                        }
                    });
            } catch (error) {
                console.error("Error showing payment request modal:", error);
            }
        },

        closePaymentRequestModal() {
            if (this.paymentRequestModal) {
                this.paymentRequestModal.hide();
            } else {
                const modalElement = document.getElementById(
                    "paymentRequestModal"
                );
                if (modalElement) {
                    modalElement.classList.remove("show");
                    modalElement.style.display = "none";
                    document.body.classList.remove("modal-open");

                    // Remove backdrop
                    const backdrop = document.querySelector(".modal-backdrop");
                    if (backdrop) {
                        backdrop.remove();
                    }
                }
            }

            // Clear form and selections
            this.paymentRequest = {
                title: "",
                created_date: new Date().toISOString().split("T")[0],
                investment_project: "",
                payment_type: "",
                payment_installment: 1,
                proposal_number: "",
            };
            this.duplicateRecords = [];
        },

        async submitPaymentRequest() {
            // Validate form
            if (
                !this.paymentRequest.created_date ||
                !this.paymentRequest.investment_project ||
                !this.paymentRequest.payment_type ||
                !this.paymentRequest.payment_installment ||
                !this.paymentRequest.proposal_number
            ) {
                Swal.fire({
                    title: "Lỗi!",
                    text: "Vui lòng điền đầy đủ thông tin",
                    icon: "error",
                    confirmButtonText: "Đồng ý",
                });
                return;
            }

            // Check if we have any duplicates
            if (this.duplicateRecords.length > 0) {
                Swal.fire({
                    title: "Không thể tiếp tục",
                    text: "Có biên bản nghiệm thu đã được sử dụng trong phiếu trình thanh toán khác",
                    icon: "warning",
                    confirmButtonText: "Đồng ý",
                });
                return;
            }

            this.isSubmitting = true;

            try {
                // Generate the auto ID format will be handled by the backend
                // The title will also be generated on the backend based on the format

                const response = await axios.post(
                    "/api/create-payment-request",
                    {
                        investment_project:
                            this.paymentRequest.investment_project,
                        payment_type: this.paymentRequest.payment_type,
                        payment_installment:
                            this.paymentRequest.payment_installment,
                        proposal_number: this.paymentRequest.proposal_number,
                        created_date: this.paymentRequest.created_date,
                        receipt_ids: this.selectedItems,
                    },
                    {
                        headers: {
                            Authorization: `Bearer ${this.store.getToken}`,
                        },
                    }
                );

                if (response.data.success) {
                    Swal.fire({
                        title: "Thành công!",
                        text: "Đã tạo phiếu trình thanh toán mới",
                        icon: "success",
                        confirmButtonText: "Đồng ý",
                    });

                    // Close the modal
                    this.closePaymentRequestModal();

                    // Reset selections
                    this.selectedItems = [];

                    // Option: Navigate to the payment request detail page
                    if (response.data.payment_request_id) {
                        // this.$router.push(`/payment-request/${response.data.payment_request_id}`);
                    }
                } else {
                    throw new Error(response.data.message || "Unknown error");
                }
            } catch (error) {
                console.error("Error creating payment request:", error);
                Swal.fire({
                    title: "Lỗi!",
                    text:
                        error.response?.data?.message ||
                        "Đã xảy ra lỗi khi tạo phiếu trình thanh toán",
                    icon: "error",
                    confirmButtonText: "Đồng ý",
                });

                if (error.response && error.response.status === 401) {
                    this.handleAuthError();
                }
            } finally {
                this.isSubmitting = false;
            }
        },
    },
    watch: {
        search() {
            this.currentPage = 1; // Resetกลับไปหน้าแรก
        },
        statusFilter() {
            this.currentPage = 1; // Resetกลับไปหน้าแรก
        },
        investmentFilter() {
            this.currentPage = 1; // Resetกลับไปหน้าแรก
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
        selectedItems: {
            handler() {
                // Auto-generate title when ID is set
                if (this.paymentRequest.investment_project) {
                    // The actual title will be generated on the server, this is just for preview
                    this.paymentRequest.title = `TIEUDE-MTTT-TTCA-${this.paymentRequest.investment_project}-[ID]`;
                }

                // Check for duplicates
                this.checkForDuplicates();
            },
            deep: true,
        },
    },
    mounted() {
        this.fetchUserInfo();
        this.fetchBienBanData();
        window.addEventListener("resize", this.checkScreenSize);

        // Properly initialize Bootstrap components
        import("bootstrap/dist/js/bootstrap.bundle.min.js")
            .then((bootstrap) => {
                // Store the Bootstrap module for later use
                window.bootstrap = bootstrap;

                // Initialize the export modal
                const exportModalElement =
                    document.getElementById("exportModal");
                if (exportModalElement) {
                    this.exportModal = new bootstrap.Modal(exportModalElement);
                }
            })
            .catch((err) => {
                console.error("Failed to load Bootstrap:", err);
            });
        this.fetchInvestmentProjects();
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

.form-checkbox:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background-color: #e5e7eb;
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

/* Reset filters button styling */
.reset-all-filters-btn {
    position: absolute;
    right: 5px;
    top: 25px;
    z-index: 99;
    font-size: 1rem;
    cursor: pointer;
    color: #fff;
    background: #198754;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease;
}

.reset-all-filters-btn:hover {
    background: #10b981;
    transform: rotate(30deg);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
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

/* Improved dropdown positioning to prevent overlap with table headers */
.absolute.mt-1.bg-white.p-2.rounded.shadow-lg.z-10 {
    position: absolute;
    top: calc(100% + 5px); /* Position below the header with some spacing */
    left: 0;
    min-width: 250px;
    max-width: 300px;
    z-index: 1050;
    overflow: visible;
    max-height: 300px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1),
        0 4px 6px -4px rgba(0, 0, 0, 0.1);
}

/* Handle overflow for long content in dropdown filters */
.max-h-40.overflow-y-auto {
    max-height: 160px;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: #cbd5e0 #f7fafc;
}

/* Prettier scrollbars for Webkit browsers */
.max-h-40.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.max-h-40.overflow-y-auto::-webkit-scrollbar-track {
    background: #f7fafc;
    border-radius: 3px;
}

.max-h-40.overflow-y-auto::-webkit-scrollbar-thumb {
    background-color: #cbd5e0;
    border-radius: 3px;
}

/* Ensure the table container handles overlays properly */
.overflow-x-auto {
    position: relative;
    overflow: visible; /* Allow dropdowns to overflow outside the container */
    width: 100%;
}

/* Make sure the card has proper overflow handling */
.card-body {
    position: relative;
    overflow: visible;
}

/* Ensure table wrapper handles the overflow context properly */
.table-responsive-wrapper {
    position: relative;
    overflow: visible;
    width: 100%;
}

/* Make the filter dropdowns look more professional */
.absolute.mt-1.bg-white.p-2.rounded.shadow-lg.z-10 {
    padding: 12px;
    background-color: white;
    border-radius: 8px;
}

/* Improve filter input styling */
.absolute.mt-1.bg-white.p-2.rounded.shadow-lg.z-10 input[type="text"] {
    border: 1px solid #e2e8f0;
    transition: all 0.2s;
    font-size: 0.875rem;
}

.absolute.mt-1.bg-white.p-2.rounded.shadow-lg.z-10 input[type="text"]:focus {
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
}

/* Style filter buttons */
.flex.justify-between button {
    transition: all 0.2s;
    font-weight: 500;
}

.flex.justify-between button:hover {
    transform: translateY(-1px);
}

/* Add a subtle pointer indicator to make it clearer the dropdown is tied to a specific column */
.absolute.mt-1.bg-white.p-2.rounded.shadow-lg.z-10:before {
    content: "";
    position: absolute;
    top: -6px;
    left: 10px;
    width: 12px;
    height: 12px;
    background: white;
    transform: rotate(45deg);
    border-left: 1px solid #e2e8f0;
    border-top: 1px solid #e2e8f0;
    z-index: -1;
}

/* Fix table header sticky positioning for better scrolling */
.table-auto thead th {
    position: sticky;
    top: 0;
    background-color: #e7e7e7;
    z-index: 10;
}

/* Table row styling */
.table-auto tbody tr {
    border-bottom: 1px solid #e5e7eb;
    transition: all 0.2s ease;
}

.table-auto tbody tr:hover {
    background-color: rgba(16, 185, 129, 0.05);
}

.table-auto td {
    padding: 0.75rem;
    border: 1px solid #e5e7eb;
    vertical-align: middle;
}

/* Ensure filter icons look professional */
.fas.fa-filter {
    transition: color 0.2s;
}

button:hover .fas.fa-filter:not(.text-green-500) {
    color: #10b981;
}

/* Improve mobile view for filter dropdowns */
@media (max-width: 768px) {
    .absolute.mt-1.bg-white.p-2.rounded.shadow-lg.z-10 {
        width: 90vw;
        max-width: 90vw;
        left: 0;
        right: 0;
        margin-left: auto;
        margin-right: auto;
    }
}

/* Vertical ellipsis button styling */
.actions-menu {
    position: relative;
}

.btn-icon {
    width: 38px;
    height: 38px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.375rem;
    transition: all 0.2s;
    background-color: #f8f9fa;
    color: #6c757d;
    border: 1px solid #e5e7eb;
}

.btn-icon:hover {
    background-color: #e9ecef;
    color: #495057;
    border-color: #ddd;
}

.btn-icon:focus {
    box-shadow: 0 0 0 0.25rem rgba(16, 185, 129, 0.25);
    border-color: #10b981;
}

.dropdown-menu {
    min-width: 200px;
    padding: 0.5rem 0;
    margin: 0.125rem 0 0;
    border-radius: 0.375rem;
    border: 1px solid rgba(0, 0, 0, 0.1);
}

.dropdown-item {
    padding: 0.6rem 1rem;
    display: flex;
    align-items: center;
    color: #212529;
    transition: all 0.2s;
}

.dropdown-item:hover {
    background-color: #f0fff4;
    color: #10b981;
}

.dropdown-item i {
    font-size: 1rem;
    width: 20px;
    text-align: center;
}
</style>
