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
                    class="modern-create-payment-btn"
                    @click="showCreatePaymentRequestModal"
                    :disabled="selectedItems.length === 0"
                    title="Tạo phiếu trình thanh toán"
                >
                    <i class="fa-solid fa-folder-plus"></i>
                    <span class="btn-label">Add PTTT</span>
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
        <div class="mobile-controls-container" v-if="isMobile">
            <!-- Search and Actions Row -->
            <div class="mobile-header-row">
                <div class="mobile-search-section">
                    <div class="modern-search-wrapper">
                        <div class="search-input-container">
                            <div class="search-icon-wrapper">
                                <i class="fas fa-search"></i>
                            </div>
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Tìm kiếm hồ sơ..."
                                class="modern-search-input"
                                aria-label="Search"
                            />
                            <button
                                v-if="search"
                                @click="search = ''"
                                class="clear-search-btn"
                                type="button"
                                aria-label="Clear search"
                            >
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <!-- Search results indicator -->
                        <div
                            v-if="search && filteredItems.length === 0"
                            class="search-no-results"
                        >
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Không tìm thấy kết quả nào
                        </div>
                        <div
                            v-else-if="search && filteredItems.length > 0"
                            class="search-results-count"
                        >
                            <i class="fas fa-check-circle me-2"></i>
                            Tìm thấy {{ filteredItems.length }} kết quả
                        </div>
                    </div>
                </div>
                <div class="mobile-action-section">
                    <div class="actions-menu">
                        <div class="dropdown">
                            <button
                                class="btn-mobile-menu"
                                type="button"
                                id="mobileActionMenuButton"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                            >
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul
                                class="dropdown-menu shadow-sm"
                                aria-labelledby="mobileActionMenuButton"
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
                                        Export to Excel
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
                                        Import Data
                                    </a>
                                </li>
                                <li v-if="selectedItems.length > 0">
                                    <a
                                        class="dropdown-item"
                                        href="#"
                                        @click.prevent="
                                            showCreatePaymentRequestModal
                                        "
                                    >
                                        <i
                                            class="fas fa-file-invoice-dollar text-warning me-2"
                                        ></i>
                                        Tạo phiếu trình
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Row -->
            <div class="mobile-filter-row">
                <div class="mobile-filter-left">
                    <select v-model="statusFilter" class="mobile-status-select">
                        <option value="all">
                            Tất cả trạng thái thanh toán
                        </option>
                        <option value="null">Chưa thanh toán</option>
                        <option value="processing">Đang xử lý</option>
                        <option value="submitted">Đã nộp kế toán</option>
                        <option value="paid">Đã thanh toán</option>
                        <option value="cancelled">Đã hủy</option>
                        <option value="rejected">Từ chối</option>
                    </select>
                </div>
                <div class="mobile-filter-right">
                    <button
                        @click="showMobileFilterModal = true"
                        class="mobile-filter-btn"
                    >
                        <i class="fas fa-filter"></i>
                    </button>
                    <button @click="resetAllFilters" class="mobile-filter-btn">
                        <i class="fas fa-redo-alt"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Filter Modal -->
        <div
            v-if="showMobileFilterModal"
            class="mobile-filter-modal-overlay"
            @click="showMobileFilterModal = false"
        >
            <div class="mobile-filter-modal" @click.stop>
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-filter me-2"></i>
                        Bộ lọc nâng cao
                    </h5>
                    <button
                        @click="showMobileFilterModal = false"
                        class="btn-close"
                    >
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="modal-body">
                    <!-- Trạm Filter -->
                    <div class="filter-section">
                        <label class="filter-label">
                            <i class="fas fa-building me-2"></i>
                            Trạm
                        </label>
                        <div class="checkbox-container">
                            <div
                                v-for="tram in uniqueValues.tram"
                                :key="tram"
                                class="form-check"
                            >
                                <input
                                    type="checkbox"
                                    class="form-check-input"
                                    :id="'mobile-tram-' + tram"
                                    :value="tram"
                                    v-model="selectedFilterValues.tram"
                                />
                                <label
                                    class="form-check-label"
                                    :for="'mobile-tram-' + tram"
                                >
                                    {{ tram }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Tình trạng giao nhận hồ sơ Filter -->
                    <div class="filter-section">
                        <label class="filter-label">
                            <i class="fas fa-exchange-alt me-2"></i>
                            Tình trạng giao nhận hồ sơ
                        </label>
                        <div class="checkbox-container">
                            <div
                                v-for="status in uniqueValues.trang_thai_nhan_hs"
                                :key="status"
                                class="form-check"
                            >
                                <input
                                    type="checkbox"
                                    class="form-check-input"
                                    :id="'mobile-delivery-' + status"
                                    :value="status"
                                    v-model="
                                        selectedFilterValues.trang_thai_nhan_hs
                                    "
                                />
                                <label
                                    class="form-check-label"
                                    :for="'mobile-delivery-' + status"
                                >
                                    {{ formatStatus(status) }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Vụ đầu tư Filter -->
                    <div class="filter-section">
                        <label class="filter-label">
                            <i class="fas fa-seedling me-2"></i>
                            Vụ đầu tư
                        </label>
                        <div class="checkbox-container">
                            <div
                                v-for="vuDauTu in uniqueValues.vu_dau_tu"
                                :key="vuDauTu"
                                class="form-check"
                            >
                                <input
                                    type="checkbox"
                                    class="form-check-input"
                                    :id="'mobile-vu-' + vuDauTu"
                                    :value="vuDauTu"
                                    v-model="selectedFilterValues.vu_dau_tu"
                                />
                                <label
                                    class="form-check-label"
                                    :for="'mobile-vu-' + vuDauTu"
                                >
                                    {{ vuDauTu }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Hình thức thực hiện DV Filter -->
                    <div class="filter-section">
                        <label class="filter-label">
                            <i class="fas fa-cogs me-2"></i>
                            Hình thức thực hiện DV
                        </label>
                        <div class="checkbox-container">
                            <div
                                v-for="hinhThuc in uniqueValues.hinh_thuc_thuc_hien_dv"
                                :key="hinhThuc"
                                class="form-check"
                            >
                                <input
                                    type="checkbox"
                                    class="form-check-input"
                                    :id="'mobile-hinh-thuc-' + hinhThuc"
                                    :value="hinhThuc"
                                    v-model="
                                        selectedFilterValues.hinh_thuc_thuc_hien_dv
                                    "
                                />
                                <label
                                    class="form-check-label"
                                    :for="'mobile-hinh-thuc-' + hinhThuc"
                                >
                                    {{ hinhThuc }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Trạng thái thanh toán Filter -->
                    <div class="filter-section">
                        <label class="filter-label">
                            <i class="fas fa-credit-card me-2"></i>
                            Trạng thái thanh toán
                        </label>
                        <div class="checkbox-container">
                            <div class="form-check">
                                <input
                                    type="checkbox"
                                    class="form-check-input"
                                    id="mobile-payment-null"
                                    value="null"
                                    v-model="
                                        selectedFilterValues.trang_thai_thanh_toan
                                    "
                                />
                                <label
                                    class="form-check-label"
                                    for="mobile-payment-null"
                                >
                                    Chưa thanh toán
                                </label>
                            </div>
                            <div
                                v-for="status in [
                                    'processing',
                                    'submitted',
                                    'paid',
                                    'cancelled',
                                    'rejected',
                                ]"
                                :key="status"
                                class="form-check"
                            >
                                <input
                                    type="checkbox"
                                    class="form-check-input"
                                    :id="'mobile-payment-' + status"
                                    :value="status"
                                    v-model="
                                        selectedFilterValues.trang_thai_thanh_toan
                                    "
                                />
                                <label
                                    class="form-check-label"
                                    :for="'mobile-payment-' + status"
                                >
                                    {{ formatPaymentStatus(status) }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button
                        @click="resetMobileFilters"
                        class="btn btn-outline-secondary"
                    >
                        <i class="fas fa-undo me-1"></i>
                        Reset
                    </button>
                    <button @click="applyMobileFilters" class="btn btn-primary">
                        <i class="fas fa-check me-1"></i>
                        Áp dụng
                    </button>
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
                    <div class="table-container-wrapper">
                        <div
                            class="table-scroll-container"
                            ref="tableScrollContainer"
                        >
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
                                                    activeFilter ===
                                                    'ma_nghiem_thu'
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
                                                <div
                                                    class="flex justify-between"
                                                >
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
                                                        @click="
                                                            activeFilter = null
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
                                                <span>Trạm</span>
                                                <button
                                                    @click="
                                                        toggleFilter('tram')
                                                    "
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
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter('tram')
                                                        "
                                                        class="px-2 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-xs"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter('tram')
                                                        "
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
                                                <div
                                                    class="flex justify-between"
                                                >
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
                                                        @click="
                                                            activeFilter = null
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
                                                    >Trạng thái thanh toán</span
                                                >
                                                <button
                                                    @click="
                                                        toggleFilter(
                                                            'trang_thai_thanh_toan'
                                                        )
                                                    "
                                                    class="ml-2 text-gray-500 hover:text-gray-700"
                                                >
                                                    <i
                                                        class="fas fa-filter"
                                                        :class="{
                                                            'text-green-500':
                                                                columnFilters.trang_thai_thanh_toan,
                                                        }"
                                                    ></i>
                                                </button>
                                            </div>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'trang_thai_thanh_toan'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10 w-64"
                                            >
                                                <div
                                                    class="max-h-40 overflow-y-auto mb-2"
                                                >
                                                    <!-- Add null option first if there are items without payment status -->
                                                    <div
                                                        v-if="
                                                            uniqueValues.trang_thai_thanh_toan.includes(
                                                                'null'
                                                            )
                                                        "
                                                        class="flex items-center mb-2"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            id="trang_thai_thanh_toan-null"
                                                            value="null"
                                                            v-model="
                                                                selectedFilterValues.trang_thai_thanh_toan
                                                            "
                                                            class="mr-2 rounded text-green-500 focus:ring-green-500"
                                                        />
                                                        <label
                                                            for="trang_thai_thanh_toan-null"
                                                            class="select-none"
                                                        >
                                                            Chưa thanh toán
                                                        </label>
                                                    </div>
                                                    <div
                                                        v-for="option in uniqueValues.trang_thai_thanh_toan.filter(
                                                            (opt) =>
                                                                opt !== 'null'
                                                        )"
                                                        :key="option"
                                                        class="flex items-center mb-2"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            :id="`trang_thai_thanh_toan-${option}`"
                                                            :value="option"
                                                            v-model="
                                                                selectedFilterValues.trang_thai_thanh_toan
                                                            "
                                                            class="mr-2 rounded text-green-500 focus:ring-green-500"
                                                        />
                                                        <label
                                                            :for="`trang_thai_thanh_toan-${option}`"
                                                            class="select-none"
                                                        >
                                                            {{
                                                                formatPaymentStatus(
                                                                    option
                                                                )
                                                            }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'trang_thai_thanh_toan'
                                                            )
                                                        "
                                                        class="px-2 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-xs"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'trang_thai_thanh_toan'
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
                                                <span>Vụ đầu tư</span>
                                                <button
                                                    @click="
                                                        toggleFilter(
                                                            'vu_dau_tu'
                                                        )
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
                                                v-if="
                                                    activeFilter === 'vu_dau_tu'
                                                "
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
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'vu_dau_tu'
                                                            )
                                                        "
                                                        class="px-2 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-xs"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'vu_dau_tu'
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
                                                <span>Tiêu đề</span>
                                                <button
                                                    @click="
                                                        toggleFilter('tieu_de')
                                                    "
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
                                                v-if="
                                                    activeFilter === 'tieu_de'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10 w-64"
                                            >
                                                <input
                                                    v-model="
                                                        columnFilters.tieu_de
                                                    "
                                                    type="text"
                                                    placeholder="Tìm kiếm Tiêu đề..."
                                                    class="w-full p-2 border rounded mb-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                                />
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'tieu_de'
                                                            )
                                                        "
                                                        class="px-2 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-xs"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            activeFilter = null
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
                                                    >Khách hàng cá nhân ĐT
                                                    mía</span
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
                                                <div
                                                    class="flex justify-between"
                                                >
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
                                                        @click="
                                                            activeFilter = null
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
                                                <div
                                                    class="flex justify-between"
                                                >
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
                                                        @click="
                                                            activeFilter = null
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
                                                <div
                                                    class="flex justify-between"
                                                >
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
                                                        @click="
                                                            activeFilter = null
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
                                                    >Hình thức thực hiện
                                                    DV</span
                                                >
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
                                                <div
                                                    class="flex justify-between"
                                                >
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
                                                    >Hợp đồng cung ứng dịch
                                                    vụ</span
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
                                                <div
                                                    class="flex justify-between"
                                                >
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
                                                        @click="
                                                            activeFilter = null
                                                        "
                                                        class="px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-xs"
                                                    >
                                                        Áp dụng
                                                    </button>
                                                </div>
                                            </div>
                                        </th>
                                        <th class="px-4 py-2">Tổng tiền</th>
                                        <th class="px-4 py-2">
                                            Tổng tiền tạm giữ
                                        </th>
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
                                                        toggleFilter(
                                                            'nguoi_giao'
                                                        )
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
                                                v-if="
                                                    activeFilter ===
                                                    'nguoi_giao'
                                                "
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
                                                <div
                                                    class="flex justify-between"
                                                >
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
                                                        @click="
                                                            activeFilter = null
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
                                                <span>Người nhận hồ sơ</span>
                                                <button
                                                    @click="
                                                        toggleFilter(
                                                            'nguoi_nhan'
                                                        )
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
                                                v-if="
                                                    activeFilter ===
                                                    'nguoi_nhan'
                                                "
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
                                                <div
                                                    class="flex justify-between"
                                                >
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
                                                        @click="
                                                            activeFilter = null
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
                                                <span>Ngày nhận hồ sơ</span>
                                                <button
                                                    @click="
                                                        toggleFilter(
                                                            'ngay_nhan'
                                                        )
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
                                                v-if="
                                                    activeFilter === 'ngay_nhan'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10 w-64"
                                            >
                                                <input
                                                    v-model="
                                                        columnFilters.ngay_nhan
                                                    "
                                                    type="date"
                                                    class="w-full p-2 border rounded mb-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                                />
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'ngay_nhan'
                                                            )
                                                        "
                                                        class="px-2 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-xs"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            activeFilter = null
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
                                                                formatStatus(
                                                                    option
                                                                )
                                                            }}</label
                                                        >
                                                    </div>
                                                </div>
                                                <div
                                                    class="flex justify-between"
                                                >
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
                                        @dblclick="viewDetails(item)"
                                    >
                                        <td class="px-4 py-2" @click.stop>
                                            <input
                                                type="checkbox"
                                                :value="item.ma_nghiem_thu"
                                                v-model="selectedItems"
                                                :disabled="!canBeSelected(item)"
                                                class="form-checkbox h-4 w-4 text-green-600"
                                                @click.stop
                                            />
                                        </td>
                                        <td class="px-4 py-2">
                                            {{ item.ma_nghiem_thu }}
                                        </td>
                                        <td class="px-4 py-2">
                                            {{ item.tram }}
                                        </td>
                                        <td class="px-4 py-2">
                                            <template
                                                v-if="item.can_bo_nong_vu"
                                            >
                                                <i
                                                    class="fas fa-user-tie text-purple-500"
                                                ></i>
                                                {{ item.can_bo_nong_vu }}
                                            </template>
                                        </td>
                                        <td class="px-4 py-2">
                                            <span
                                                v-if="
                                                    item.trang_thai_thanh_toan
                                                "
                                                :class="
                                                    paymentStatusClass(
                                                        item.trang_thai_thanh_toan
                                                    )
                                                "
                                                class="px-2 py-1 rounded-full text-xs font-medium flex items-center w-fit"
                                            >
                                                <i
                                                    :class="
                                                        paymentStatusIcon(
                                                            item.trang_thai_thanh_toan
                                                        )
                                                    "
                                                    class="mr-1"
                                                ></i>
                                                {{
                                                    formatPaymentStatus(
                                                        item.trang_thai_thanh_toan
                                                    )
                                                }}
                                            </span>
                                            <span
                                                v-else
                                                class="text-gray-400 italic text-xs"
                                            >
                                                Chưa thanh toán
                                            </span>
                                        </td>
                                        <td class="px-4 py-2">
                                            {{ item.vu_dau_tu }}
                                        </td>
                                        <td class="px-4 py-2">
                                            {{ item.tieu_de }}
                                        </td>
                                        <td class="px-4 py-2">
                                            {{ item.khach_hang_ca_nhan_dt_mia }}
                                        </td>
                                        <td class="px-4 py-2">
                                            {{
                                                item.khach_hang_doanh_nghiep_dt_mia
                                            }}
                                        </td>
                                        <td class="px-4 py-2">
                                            {{ item.hop_dong_dau_tu_mia }}
                                        </td>
                                        <td class="px-4 py-2">
                                            {{ item.hinh_thuc_thuc_hien_dv }}
                                        </td>
                                        <td class="px-4 py-2">
                                            {{ item.hop_dong_cung_ung_dich_vu }}
                                        </td>
                                        <td class="px-4 py-2">
                                            {{
                                                formatCurrency(
                                                    item.tong_tien_dich_vu
                                                )
                                            }}
                                        </td>
                                        <td class="px-4 py-2">
                                            {{
                                                formatCurrency(
                                                    item.tong_tien_tam_giu
                                                )
                                            }}
                                        </td>
                                        <td class="px-4 py-2">
                                            {{
                                                formatCurrency(
                                                    item.tong_tien_thanh_toan
                                                )
                                            }}
                                        </td>
                                        <td class="px-4 py-2">
                                            <template v-if="item.nguoi_giao">
                                                <i
                                                    class="fas fa-user text-blue-500"
                                                ></i>
                                                {{ item.nguoi_giao }}
                                            </template>
                                        </td>
                                        <td class="px-4 py-2">
                                            <template v-if="item.nguoi_nhan">
                                                <i
                                                    class="fas fa-user text-green-500"
                                                ></i>
                                                {{ item.nguoi_nhan }}
                                            </template>
                                        </td>
                                        <td class="px-4 py-2">
                                            <span
                                                v-if="item.ngay_nhan"
                                                class="badge bg-warning text-dark px-2 py-1 rounded-pill"
                                            >
                                                {{ formatDate(item.ngay_nhan) }}
                                            </span>
                                            <span
                                                v-else
                                                class="text-gray-400 italic text-xs"
                                            >
                                                Chưa có ngày nhận
                                            </span>
                                        </td>
                                        <td class="px-4 py-2p">
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
                <div class="flex">
                    <!-- First section: Show only status with icon -->
                    <div
                        class="flex-shrink-0 flex items-center justify-center mr-4 me-4"
                    >
                        <div
                            class="status-display flex flex-column items-center"
                        >
                            <i
                                v-if="item.trang_thai_thanh_toan"
                                :class="[
                                    paymentStatusIcon(
                                        item.trang_thai_thanh_toan
                                    ),
                                    'text-3xl mb-1',
                                    {
                                        'text-warning':
                                            item.trang_thai_thanh_toan ===
                                            'processing',
                                        'text-primary':
                                            item.trang_thai_thanh_toan ===
                                            'submitted',
                                        'text-success':
                                            item.trang_thai_thanh_toan ===
                                            'paid',
                                        'text-danger':
                                            item.trang_thai_thanh_toan ===
                                            'cancelled',
                                    },
                                ]"
                            ></i>
                            <i
                                v-else
                                class="fas fa-money-bill-slash text-3xl mb-1 text-gray-400"
                            ></i>
                            <span
                                v-if="item.trang_thai_thanh_toan"
                                class="status-badge text-xs"
                                :class="
                                    paymentStatusClass(
                                        item.trang_thai_thanh_toan
                                    )
                                "
                            >
                                {{
                                    formatPaymentStatus(
                                        item.trang_thai_thanh_toan
                                    )
                                }}
                            </span>
                            <span
                                v-else
                                class="status-badge text-xs text-gray-400"
                                >Chưa thanh toán</span
                            >
                        </div>
                    </div>
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
                        <div class="mb-2">
                            <strong>Tình trạng giao nhận hồ sơ:</strong>
                            <span
                                v-if="item.trang_thai_nhan_hs !== undefined"
                                :class="statusClass(item.trang_thai_nhan_hs)"
                                class="flex items-center"
                            >
                                <i
                                    :class="
                                        statusIcons(item.trang_thai_nhan_hs)
                                    "
                                    class="mr-1"
                                ></i>
                                {{ formatStatus(item.trang_thai_nhan_hs) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Mobile Pagination Card - ปรับปรุงใหม่ -->
            <div class="mobile-pagination-card" v-if="isMobile">
                <div class="pagination-info">
                    <span class="page-info"
                        >Trang {{ currentPage }} của
                        {{ paginatedItems.last_page }}</span
                    >
                    <span class="record-info"
                        >{{ paginatedItems.from }}-{{ paginatedItems.to }} của
                        {{ paginatedItems.total }} bản ghi</span
                    >
                </div>

                <div class="pagination-controls">
                    <button
                        class="page-btn"
                        @click="pageChanged(1)"
                        :disabled="currentPage === 1"
                    >
                        <i class="fas fa-angle-double-left"></i>
                    </button>

                    <button
                        class="page-btn"
                        @click="pageChanged(currentPage - 1)"
                        :disabled="currentPage === 1"
                    >
                        <i class="fas fa-angle-left"></i>
                    </button>

                    <div class="current-page">{{ currentPage }}</div>

                    <button
                        class="page-btn"
                        @click="pageChanged(currentPage + 1)"
                        :disabled="currentPage === paginatedItems.last_page"
                    >
                        <i class="fas fa-angle-right"></i>
                    </button>

                    <button
                        class="page-btn"
                        @click="pageChanged(paginatedItems.last_page)"
                        :disabled="currentPage === paginatedItems.last_page"
                    >
                        <i class="fas fa-angle-double-right"></i>
                    </button>
                </div>

                <!-- Quick jump สำหรับหน้าเยอะ -->
                <div class="quick-jump" v-if="paginatedItems.last_page > 5">
                    <span>Đi đến trang:</span>
                    <select
                        :value="currentPage"
                        @change="pageChanged(parseInt($event.target.value))"
                    >
                        <option
                            v-for="page in paginatedItems.last_page"
                            :key="page"
                            :value="page"
                        >
                            {{ page }}
                        </option>
                    </select>
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
            <div class="modal-content modern-modal">
                <!-- Enhanced Header -->
                <div class="modal-header modern-header">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <div class="header-text">
                            <h5
                                class="modal-title"
                                id="paymentRequestModalLabel"
                            >
                                Tạo phiếu trình thanh toán
                            </h5>
                            <p class="modal-subtitle">
                                Tạo phiếu trình thanh toán cho các biên bản
                                nghiệm thu đã chọn
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="btn-close modern-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                        @click="closePaymentRequestModal"
                    ></button>
                </div>

                <div class="modal-body modern-body">
                    <!-- Progress Steps -->
                    <div class="progress-steps mb-4">
                        <div class="step active">
                            <div class="step-circle">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <span class="step-label">Thông tin cơ bản</span>
                        </div>
                        <div class="step-connector"></div>
                        <div class="step">
                            <div class="step-circle">
                                <i class="fas fa-list-check"></i>
                            </div>
                            <span class="step-label">Xác nhận</span>
                        </div>
                    </div>

                    <!-- Selected Items Summary Card -->
                    <div class="summary-card mb-4">
                        <div class="summary-header">
                            <div class="summary-icon">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                            <div class="summary-info">
                                <h6 class="summary-title">Biên bản đã chọn</h6>
                                <p class="summary-count">
                                    <span class="count-badge">{{
                                        selectedItems.length
                                    }}</span>
                                    biên bản nghiệm thu
                                </p>
                            </div>
                        </div>

                        <!-- Duplicate Warning (if any) -->
                        <div
                            v-if="duplicateRecords.length > 0"
                            class="alert alert-warning modern-alert"
                        >
                            <div class="alert-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="alert-content">
                                <strong>Cảnh báo:</strong> Một số biên bản đã
                                được sử dụng
                                <div class="mt-2">
                                    <div
                                        v-for="(
                                            item, index
                                        ) in duplicateRecords.slice(0, 3)"
                                        :key="index"
                                        class="duplicate-item"
                                    >
                                        <i class="fas fa-file-alt"></i>
                                        {{ item.ma_nghiem_thu }} →
                                        {{ item.ma_trinh_thanh_toan }}
                                    </div>
                                    <div
                                        v-if="duplicateRecords.length > 3"
                                        class="more-items"
                                    >
                                        +{{ duplicateRecords.length - 3 }} mục
                                        khác...
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Content -->
                    <form
                        @submit.prevent="submitPaymentRequest"
                        class="modern-form"
                    >
                        <!-- Auto-generated Title Display -->
                        <div class="form-group mb-4">
                            <label class="form-label modern-label">
                                <i class="fas fa-tag me-2"></i>
                                Tiêu đề phiếu trình
                            </label>
                            <div class="auto-title-display">
                                <i class="fas fa-magic me-2"></i>
                                <span class="auto-text">Tự động tạo:</span>
                                <span class="title-preview">{{
                                    paymentRequest.title ||
                                    "TIEUDE-MTTT-TTCA-[VỤ ĐẦU TƯ]-[ID]"
                                }}</span>
                            </div>
                        </div>

                        <!-- Date and Investment Project Row -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label
                                    for="requestDate"
                                    class="form-label modern-label"
                                >
                                    <i class="fas fa-calendar-alt me-2"></i>
                                    Ngày tạo
                                </label>
                                <div class="input-wrapper">
                                    <input
                                        type="date"
                                        class="form-control modern-input"
                                        id="requestDate"
                                        v-model="paymentRequest.created_date"
                                        required
                                    />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label
                                    for="investmentProject"
                                    class="form-label modern-label"
                                >
                                    <i class="fas fa-seedling me-2"></i>
                                    Vụ đầu tư
                                </label>
                                <div class="input-wrapper">
                                    <select
                                        class="form-select modern-select"
                                        id="investmentProject"
                                        v-model="
                                            paymentRequest.investment_project
                                        "
                                        required
                                    >
                                        <option value="" disabled>
                                            Chọn vụ đầu tư...
                                        </option>
                                        <option
                                            v-for="project in investmentProjectsList"
                                            :key="project.Ma_Vudautu"
                                            :value="project.Ma_Vudautu"
                                        >
                                            {{ project.Ten_Vudautu }}
                                        </option>
                                    </select>
                                    <i
                                        class="fas fa-chevron-down select-arrow"
                                    ></i>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Type and Installment Row -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-8">
                                <label
                                    for="paymentType"
                                    class="form-label modern-label"
                                >
                                    <i class="fas fa-money-check-alt me-2"></i>
                                    Loại thanh toán
                                </label>
                                <div class="input-wrapper">
                                    <select
                                        class="form-select modern-select"
                                        id="paymentType"
                                        v-model="paymentRequest.payment_type"
                                        required
                                    >
                                        <option value="" disabled>
                                            Chọn loại thanh toán...
                                        </option>
                                        <option value="Nghiệm thu dịch vụ">
                                            <i
                                                class="fas fa-clipboard-check me-2"
                                            ></i>
                                            Nghiệm thu dịch vụ
                                        </option>
                                        <option
                                            value="Phiếu giao nhận hom giống"
                                        >
                                            <i
                                                class="fas fa-exchange-alt me-2"
                                            ></i>
                                            Phiếu giao nhận hom giống
                                        </option>
                                    </select>
                                    <i
                                        class="fas fa-chevron-down select-arrow"
                                    ></i>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label
                                    for="paymentInstallment"
                                    class="form-label modern-label"
                                >
                                    <i class="fas fa-sort-numeric-up me-2"></i>
                                    Số đợt
                                </label>
                                <div class="input-wrapper installment-wrapper">
                                    <input
                                        type="number"
                                        class="form-control modern-input"
                                        id="paymentInstallment"
                                        v-model="
                                            paymentRequest.payment_installment
                                        "
                                        min="1"
                                        max="99"
                                        required
                                    />
                                    <div class="installment-controls">
                                        <button
                                            type="button"
                                            class="installment-btn"
                                            @click="
                                                paymentRequest.payment_installment =
                                                    Math.min(
                                                        99,
                                                        parseInt(
                                                            paymentRequest.payment_installment ||
                                                                1
                                                        ) + 1
                                                    )
                                            "
                                        >
                                            <i class="fas fa-plus"></i>
                                        </button>
                                        <button
                                            type="button"
                                            class="installment-btn"
                                            @click="
                                                paymentRequest.payment_installment =
                                                    Math.max(
                                                        1,
                                                        parseInt(
                                                            paymentRequest.payment_installment ||
                                                                1
                                                        ) - 1
                                                    )
                                            "
                                        >
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Proposal Number -->
                        <div class="form-group mb-4">
                            <label
                                for="proposalNumber"
                                class="form-label modern-label"
                            >
                                <i class="fas fa-file-signature me-2"></i>
                                Số tờ trình
                            </label>
                            <div class="input-wrapper">
                                <input
                                    type="text"
                                    class="form-control modern-input"
                                    id="proposalNumber"
                                    v-model="paymentRequest.proposal_number"
                                    placeholder="Nhập số tờ trình..."
                                    maxlength="50"
                                    required
                                />
                                <div class="input-feedback">
                                    <span class="char-count"
                                        >{{
                                            paymentRequest.proposal_number
                                                ?.length || 0
                                        }}/50</span
                                    >
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Enhanced Footer -->
                <div class="modal-footer modern-footer">
                    <div class="footer-actions">
                        <button
                            type="button"
                            class="btn btn-secondary modern-btn-secondary"
                            data-bs-dismiss="modal"
                            @click="closePaymentRequestModal"
                        >
                            <i class="fas fa-times me-2"></i>
                            Hủy bỏ
                        </button>
                        <button
                            type="button"
                            class="btn btn-success modern-btn-primary"
                            @click="submitPaymentRequest"
                            :disabled="
                                isSubmitting || duplicateRecords.length > 0
                            "
                        >
                            <div v-if="isSubmitting" class="btn-loading">
                                <div class="spinner"></div>
                                <span>Đang xử lý...</span>
                            </div>
                            <div v-else class="btn-content">
                                <i class="fas fa-save me-2"></i>
                                <span>Tạo phiếu trình</span>
                            </div>
                        </button>
                    </div>
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

import PerfectScrollbar from "perfect-scrollbar"; // Add this import
import "perfect-scrollbar/css/perfect-scrollbar.css"; // Add this import
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
            showMobileFilterModal: false,
            ps: null, // Add this for PerfectScrollbar instance
            isLoading: false,
            bienBanList: [],
            allBienBanList: [], // เพิ่มสำหรับเก็บข้อมูลทั้งหมด
            search: "",
            statusFilter: "all",
            investmentFilter: "all",
            investmentProjects: [],
            isMobile: window.innerWidth < 768,
            currentPage: 1, // เพิ่มสำหรับการจัดการหน้า
            perPage: 15, // จำนวนรายการต่อหน้า
            userPosition: null,
            userStation: null,
            userEmployeeCode: null,
            statusOptions: [
                { code: "all", name: "Tất cả trạng thái" },
                { code: "processing", name: "Đang xử lý" },
                { code: "submitted", name: "Đã nộp kế toán" },
                { code: "paid", name: "Đã thanh toán" },
            ],
            deliveryStatusFilter: "all",
            deliveryStatusOptions: [
                { code: "all", name: "Tất cả tình trạng giao nhận" },
                { code: "creating", name: "Nháp" },
                { code: "sending", name: "Đang nộp" },
                { code: "received", name: "Đã nhận" },
                { code: "cancelled", name: "Hủy" },
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
                trang_thai_thanh_toan: "",
            },
            selectedFilterValues: {
                tram: [],
                vu_dau_tu: [],
                hinh_thuc_thuc_hien_dv: [],
                trang_thai_nhan_hs: [],
                trang_thai_thanh_toan: [],
            },
            uniqueValues: {
                tram: [],
                vu_dau_tu: [],
                hinh_thuc_thuc_hien_dv: [],
                trang_thai_nhan_hs: [],
                trang_thai_thanh_toan: [],
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
            // Initialize counts of each status type
            const counts = {
                all: 0, // This will be calculated as the sum of all payment statuses
                processing: 0,
                submitted: 0,
                paid: 0,
                cancelled: 0,
                rejected: 0,
                null: 0, // For "Chưa thanh toán"
            };

            // Count each status type in the bienBanList
            this.bienBanList.forEach((item) => {
                if (!item.trang_thai_thanh_toan) {
                    counts.null++;
                } else if (counts[item.trang_thai_thanh_toan] !== undefined) {
                    counts[item.trang_thai_thanh_toan]++;
                }
            });

            // Calculate 'all' as the sum of all status counts
            counts.all =
                counts.processing +
                counts.submitted +
                counts.paid +
                counts.cancelled +
                counts.rejected +
                counts.null;

            return counts;
        },
        deliveryStatusCounts() {
            // Initialize counts of each status type
            const counts = {
                all: 0,
                creating: 0,
                sending: 0,
                received: 0,
                cancelled: 0,
            };

            // Count each status type in the bienBanList
            this.bienBanList.forEach((item) => {
                if (!item.trang_thai_nhan_hs) {
                    counts.all++;
                } else if (counts[item.trang_thai_nhan_hs] !== undefined) {
                    counts[item.trang_thai_nhan_hs]++;
                }
            });

            // Calculate 'all' as the sum of all status counts
            counts.all = this.bienBanList.length;

            return counts;
        },
        filteredItems() {
            return this.bienBanList.filter((item) => {
                // Filter by search query
                const matchesSearch =
                    !this.search ||
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
                        .includes(this.search.toLowerCase()) ||
                    item.can_bo_nong_vu
                        ?.toLowerCase()
                        .includes(this.search.toLowerCase()) ||
                    item.khach_hang_ca_nhan_dt_mia
                        ?.toLowerCase()
                        .includes(this.search.toLowerCase()) ||
                    item.khach_hang_doanh_nghiep_dt_mia
                        ?.toLowerCase()
                        .includes(this.search.toLowerCase()) ||
                    item.nguoi_giao
                        ?.toLowerCase()
                        .includes(this.search.toLowerCase()) ||
                    item.nguoi_nhan
                        ?.toLowerCase()
                        .includes(this.search.toLowerCase());

                // Filter by payment status
                let matchesStatus = true;
                if (this.statusFilter !== "all") {
                    if (this.statusFilter === "null") {
                        // For "Chưa thanh toán" - null value
                        matchesStatus = !item.trang_thai_thanh_toan;
                    } else {
                        // For other payment statuses
                        matchesStatus =
                            item.trang_thai_thanh_toan === this.statusFilter;
                    }
                }

                // Filter by investment project
                let matchesInvestment = true;
                if (this.investmentFilter !== "all") {
                    matchesInvestment =
                        item.vu_dau_tu === this.investmentFilter;
                }

                // Filter by delivery status
                let matchesDeliveryStatus = true;
                if (this.deliveryStatusFilter !== "all") {
                    matchesDeliveryStatus =
                        item.trang_thai_nhan_hs === this.deliveryStatusFilter;
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
                            ))) &&
                    // Trang thai thanh toan filter
                    (this.selectedFilterValues.trang_thai_thanh_toan.length ===
                        0 ||
                        (this.selectedFilterValues.trang_thai_thanh_toan.includes(
                            "null"
                        ) &&
                            !item.trang_thai_thanh_toan) ||
                        (item.trang_thai_thanh_toan &&
                            this.selectedFilterValues.trang_thai_thanh_toan.includes(
                                item.trang_thai_thanh_toan
                            )));

                return (
                    matchesSearch &&
                    matchesStatus &&
                    matchesInvestment &&
                    matchesDeliveryStatus &&
                    matchColumnFilters
                );
            });
        },
        paginatedItems() {
            // Implement client-side pagination
            const page = this.currentPage || 1;
            const perPage = this.perPage || 15;
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
        saveFilterState() {
            // เก็บสถานะทั้งหมดของฟิลเตอร์ใน Local Storage
            const filterState = {
                search: this.search,
                statusFilter: this.statusFilter,
                investmentFilter: this.investmentFilter,
                deliveryStatusFilter: this.deliveryStatusFilter,
                columnFilters: this.columnFilters,
                selectedFilterValues: this.selectedFilterValues,
                currentPage: this.currentPage,
                activeFilter: this.activeFilter,
            };
            localStorage.setItem(
                "bienban_filter_state",
                JSON.stringify(filterState)
            );
        },

        loadFilterState() {
            // โหลดสถานะฟิลเตอร์จาก Local Storage
            const filterState = localStorage.getItem("bienban_filter_state");
            if (filterState) {
                const parsedState = JSON.parse(filterState);
                this.search = parsedState.search || "";
                this.statusFilter = parsedState.statusFilter || "all";
                this.investmentFilter = parsedState.investmentFilter || "all";
                this.deliveryStatusFilter =
                    parsedState.deliveryStatusFilter || "all";
                this.columnFilters =
                    parsedState.columnFilters || this.columnFilters;
                this.selectedFilterValues =
                    parsedState.selectedFilterValues ||
                    this.selectedFilterValues;
                this.currentPage = parsedState.currentPage || 1;
                this.activeFilter = null; // ไม่โหลด activeFilter เพื่อป้องกันปัญหากับ dropdown
            }
        },

        // Add these new methods for PerfectScrollbar
        initPerfectScrollbar() {
            this.$nextTick(() => {
                if (this.$refs.tableScrollContainer) {
                    try {
                        // Import PerfectScrollbar dynamically to ensure it's loaded
                        import("perfect-scrollbar")
                            .then((module) => {
                                const PerfectScrollbar = module.default;

                                // Destroy existing instance if it exists
                                if (this.ps) {
                                    this.ps.destroy();
                                    this.ps = null;
                                }

                                // Create new PerfectScrollbar instance
                                this.ps = new PerfectScrollbar(
                                    this.$refs.tableScrollContainer,
                                    {
                                        suppressScrollX: false,
                                        wheelPropagation: false,
                                    }
                                );
                            })
                            .catch((error) => {
                                console.error(
                                    "Failed to load PerfectScrollbar:",
                                    error
                                );
                            });
                    } catch (error) {
                        console.error(
                            "Error initializing PerfectScrollbar:",
                            error
                        );
                    }
                }
            });
        },

        updateScrollbar() {
            this.$nextTick(() => {
                if (this.ps) {
                    this.ps.update();
                }
            });
        },
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
        formatPaymentStatus(status) {
            if (!status) return "Chưa thanh toán";

            const statusMap = {
                processing: "Đang xử lý",
                submitted: "Đã nộp kế toán",
                paid: "Đã thanh toán",
                cancelled: "Đã hủy",
                rejected: "Từ chối",
            };

            return statusMap[status] || status;
        },

        paymentStatusClass(status) {
            if (!status) return "";

            const classMap = {
                processing: "bg-blue-100 text-blue-800",
                submitted: "bg-orange-100 text-orange-800",
                paid: "bg-green-100 text-green-800",
                cancelled: "bg-red-100 text-red-800",
                rejected: "bg-gray-100 text-gray-800",
            };

            return classMap[status] || "";
        },

        paymentStatusIcon(status) {
            if (!status) return "";

            const iconMap = {
                processing: "fas fa-cog",
                submitted: "fas fa-paper-plane",
                paid: "fas fa-check-circle",
                cancelled: "fas fa-times-circle",
                rejected: "fas fa-exclamation-circle",
            };

            return iconMap[status] || "fas fa-question-circle";
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
                    headers: this.store.getAuthHeaders(),
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
                    headers: this.store.getAuthHeaders(),
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
                    this.updateUniqueValues("trang_thai_thanh_toan"); // Add this line
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
                // บันทึกสถานะฟิลเตอร์ก่อนนำทางไปหน้ารายละเอียด
                this.saveFilterState();

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
            if (column === "trang_thai_thanh_toan") {
                // For payment status, include null values and add specific statuses
                const existingStatuses = [
                    ...new Set(
                        this.bienBanList
                            .map((item) => item[column])
                            .filter(Boolean)
                    ),
                ];
                // Add null option for "Chưa thanh toán" if there are any null values
                const hasNullValues = this.bienBanList.some(
                    (item) => !item[column]
                );
                if (hasNullValues) {
                    existingStatuses.unshift("null"); // Add null as first option
                }
                this.uniqueValues[column] = existingStatuses;
            } else {
                this.uniqueValues[column] = [
                    ...new Set(
                        this.bienBanList
                            .map((item) => item[column])
                            .filter(Boolean)
                    ),
                ];
            }
        },

        resetFilter(column) {
            if (
                [
                    "tram",
                    "vu_dau_tu",
                    "hinh_thuc_thuc_hien_dv",
                    "trang_thai_nhan_hs",
                    "trang_thai_thanh_toan", // เพิ่มบรรทัดนี้
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
            this.deliveryStatusFilter = "all";

            this.currentPage = 1;
            // ลบสถานะฟิลเตอร์จาก Local Storage
            localStorage.removeItem("bienban_filter_state");
        },

        applyFilter(column) {
            // Just close the filter dropdown as the filter is applied automatically
            this.activeFilter = null;
            this.currentPage = 1;
        },

        // Mobile filter methods
        applyMobileFilters() {
            this.showMobileFilterModal = false;
            this.currentPage = 1;
            this.saveFilterState();
        },

        resetMobileFilters() {
            // Reset dropdown filters for mobile
            Object.keys(this.selectedFilterValues).forEach((key) => {
                this.selectedFilterValues[key] = [];
            });
            this.statusFilter = "all";
            this.deliveryStatusFilter = "all";
            this.investmentFilter = "all";
            this.currentPage = 1;
            // Clear from localStorage
            localStorage.removeItem("bienban_filter_state");
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
            this.isLoading = true;

            // Get the current page data from paginatedItems
            setTimeout(() => {
                if (
                    this.paginatedItems.data &&
                    this.paginatedItems.data.length > 0
                ) {
                    try {
                        // Import xlsx and FileSaver dynamically
                        import("xlsx").then((XLSX) => {
                            import("file-saver").then((module) => {
                                const FileSaver = module.default;

                                // Format data for export
                                const exportData = this.paginatedItems.data.map(
                                    (item) => {
                                        // Format all numeric values as integers (without decimal places)
                                        const tong_tien_dich_vu =
                                            item.tong_tien_dich_vu
                                                ? parseInt(
                                                      item.tong_tien_dich_vu
                                                  )
                                                : 0;

                                        const tong_tien_tam_giu =
                                            item.tong_tien_tam_giu
                                                ? parseInt(
                                                      item.tong_tien_tam_giu
                                                  )
                                                : 0;

                                        const tong_tien_thanh_toan =
                                            item.tong_tien_thanh_toan
                                                ? parseInt(
                                                      item.tong_tien_thanh_toan
                                                  )
                                                : 0;

                                        return {
                                            "Mã nghiệm thu":
                                                item.ma_nghiem_thu || "",
                                            Trạm: item.tram || "",
                                            "Cán bộ nông vụ":
                                                item.can_bo_nong_vu || "",
                                            "Trạng thái thanh toán":
                                                item.trang_thai_thanh_toan
                                                    ? this.formatPaymentStatus(
                                                          item.trang_thai_thanh_toan
                                                      )
                                                    : "Chưa thanh toán",
                                            "Vụ đầu tư": item.vu_dau_tu || "",
                                            "Tiêu đề": item.tieu_de || "",
                                            "Khách hàng cá nhân":
                                                item.khach_hang_ca_nhan_dt_mia ||
                                                "",
                                            "Khách hàng doanh nghiệp":
                                                item.khach_hang_doanh_nghiep_dt_mia ||
                                                "",
                                            "Hợp đồng đầu tư mía":
                                                item.hop_dong_dau_tu_mia || "",
                                            "Hình thức thực hiện DV":
                                                item.hinh_thuc_thuc_hien_dv ||
                                                "",
                                            "Hợp đồng cung ứng DV":
                                                item.hop_dong_cung_ung_dich_vu ||
                                                "",
                                            "Tổng tiền dịch vụ":
                                                tong_tien_dich_vu,
                                            "Tổng tiền tạm giữ":
                                                tong_tien_tam_giu,
                                            "Tổng tiền thanh toán":
                                                tong_tien_thanh_toan,
                                            "Người giao": item.nguoi_giao || "",
                                            "Người nhận": item.nguoi_nhan || "",
                                            "Ngày nhận": item.ngay_nhan
                                                ? this.formatDate(
                                                      item.ngay_nhan
                                                  )
                                                : "",
                                            "Trạng thái":
                                                item.trang_thai_nhan_hs
                                                    ? this.formatStatus(
                                                          item.trang_thai_nhan_hs
                                                      )
                                                    : "",
                                        };
                                    }
                                );

                                // Create a worksheet
                                const worksheet =
                                    XLSX.utils.json_to_sheet(exportData);

                                // Set column widths
                                const columnWidths = [
                                    { wpx: 120 }, // Mã nghiệm thu
                                    { wpx: 100 }, // Trạm
                                    { wpx: 120 }, // Cán bộ nông vụ
                                    { wpx: 120 }, // Trạng thái thanh toán
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
                                    `bien_ban_current_page_${
                                        new Date().toISOString().split("T")[0]
                                    }.xlsx`
                                );

                                // Show success notification using SweetAlert2 toast
                                Swal.fire({
                                    title: "Xuất thành công!",
                                    text: `Đã xuất ${exportData.length} bản ghi ra tệp Excel`,
                                    icon: "success",
                                    toast: true,
                                    position: "top-end",
                                    showConfirmButton: false,
                                    timer: 3000,
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
                            });
                        });
                    } catch (error) {
                        console.error("Export error:", error);

                        // Show error toast
                        Swal.fire({
                            title: "Xuất không thành công!",
                            text: "Đã xảy ra lỗi khi xuất dữ liệu. Vui lòng thử lại.",
                            icon: "error",
                            toast: true,
                            position: "top-end",
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                        });
                    }
                } else {
                    // Show warning toast for no data
                    Swal.fire({
                        title: "Không có dữ liệu!",
                        text: "Không tìm thấy dữ liệu cần xuất.",
                        icon: "warning",
                        toast: true,
                        position: "top-end",
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                    });
                }
                this.isLoading = false;

                // Close modal after export
                this.closeExportModal();
            }, 100);
        },

        exportToExcelAllPages() {
            this.isLoading = true;
            // Use all filtered items instead of just the current page
            setTimeout(() => {
                if (this.filteredItems && this.filteredItems.length > 0) {
                    try {
                        // Import xlsx and FileSaver dynamically
                        import("xlsx").then((XLSX) => {
                            import("file-saver").then((module) => {
                                const FileSaver = module.default;

                                // Format data for export
                                const exportData = this.filteredItems.map(
                                    (item) => {
                                        // Format all numeric values as integers (without decimal places)
                                        const tong_tien_dich_vu =
                                            item.tong_tien_dich_vu
                                                ? parseInt(
                                                      item.tong_tien_dich_vu
                                                  )
                                                : 0;

                                        const tong_tien_tam_giu =
                                            item.tong_tien_tam_giu
                                                ? parseInt(
                                                      item.tong_tien_tam_giu
                                                  )
                                                : 0;

                                        const tong_tien_thanh_toan =
                                            item.tong_tien_thanh_toan
                                                ? parseInt(
                                                      item.tong_tien_thanh_toan
                                                  )
                                                : 0;

                                        return {
                                            "Mã nghiệm thu":
                                                item.ma_nghiem_thu || "",
                                            Trạm: item.tram || "",
                                            "Cán bộ nông vụ":
                                                item.can_bo_nong_vu || "",
                                            "Trạng thái thanh toán":
                                                item.trang_thai_thanh_toan
                                                    ? this.formatPaymentStatus(
                                                          item.trang_thai_thanh_toan
                                                      )
                                                    : "Chưa thanh toán",
                                            "Vụ đầu tư": item.vu_dau_tu || "",
                                            "Tiêu đề": item.tieu_de || "",
                                            "Khách hàng cá nhân":
                                                item.khach_hang_ca_nhan_dt_mia ||
                                                "",
                                            "Khách hàng doanh nghiệp":
                                                item.khach_hang_doanh_nghiep_dt_mia ||
                                                "",
                                            "Hợp đồng đầu tư mía":
                                                item.hop_dong_dau_tu_mia || "",
                                            "Hình thức thực hiện DV":
                                                item.hinh_thuc_thuc_hien_dv ||
                                                "",
                                            "Hợp đồng cung ứng DV":
                                                item.hop_dong_cung_ung_dich_vu ||
                                                "",
                                            "Tổng tiền dịch vụ":
                                                tong_tien_dich_vu,
                                            "Tổng tiền tạm giữ":
                                                tong_tien_tam_giu,
                                            "Tổng tiền thanh toán":
                                                tong_tien_thanh_toan,
                                            "Người giao": item.nguoi_giao || "",
                                            "Người nhận": item.nguoi_nhan || "",
                                            "Ngày nhận": item.ngay_nhan
                                                ? this.formatDate(
                                                      item.ngay_nhan
                                                  )
                                                : "",
                                            "Trạng thái":
                                                item.trang_thai_nhan_hs
                                                    ? this.formatStatus(
                                                          item.trang_thai_nhan_hs
                                                      )
                                                    : "",
                                        };
                                    }
                                );

                                // Create a worksheet
                                const worksheet =
                                    XLSX.utils.json_to_sheet(exportData);

                                // Set column widths
                                const columnWidths = [
                                    { wpx: 120 }, // Mã nghiệm thu
                                    { wpx: 100 }, // Trạm
                                    { wpx: 120 }, // Cán bộ nông vụ
                                    { wpx: 120 }, // Trạng thái thanh toán (new column)
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
                                    `bien_ban_all_data_${
                                        new Date().toISOString().split("T")[0]
                                    }.xlsx`
                                );

                                // Show success notification using SweetAlert2 toast
                                Swal.fire({
                                    title: "Xuất thành công!",
                                    text: `Đã xuất ${exportData.length} bản ghi ra tệp Excel`,
                                    icon: "success",
                                    toast: true,
                                    position: "top-end",
                                    showConfirmButton: false,
                                    timer: 3000,
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
                            });
                        });
                    } catch (error) {
                        console.error("Export error:", error);

                        // Show error toast
                        Swal.fire({
                            title: "Xuất không thành công!",
                            text: "Đã xảy ra lỗi khi xuất dữ liệu. Vui lòng thử lại.",
                            icon: "error",
                            toast: true,
                            position: "top-end",
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                        });
                    }
                } else {
                    // Show warning toast for no data
                    Swal.fire({
                        title: "Không có dữ liệu!",
                        text: "Không tìm thấy dữ liệu cần xuất.",
                        icon: "warning",
                        toast: true,
                        position: "top-end",
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                    });
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
                            "Trạng thái thanh toán": item.trang_thai_thanh_toan
                                ? this.formatPaymentStatus(
                                      item.trang_thai_thanh_toan
                                  )
                                : "Chưa thanh toán", // Add payment status
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
                            { wpx: 120 }, // Trạng thái thanh toán (new column)
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
                    headers: this.store.getAuthHeaders(),
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

                    // Navigate to the payment request detail page
                    if (response.data.payment_request_id) {
                        this.$router.push(
                            `/Details_Phieutrinhthanhtoandv/${response.data.payment_request_id}`
                        );
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
        paginatedItems: {
            handler() {
                this.updateScrollbar();
            },
            deep: true,
        },
        filteredItems: {
            handler() {
                this.updateScrollbar();
            },
            deep: true,
        },
        search() {
            this.currentPage = 1; // Resetกลับไปหน้าแรก
        },
        deliveryStatusFilter() {
            this.currentPage = 1;
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
        // โหลดสถานะฟิลเตอร์จาก Local Storage ก่อน
        this.loadFilterState();
        this.fetchUserInfo();
        // this.fetchBienBanData();
        window.addEventListener("resize", this.checkScreenSize);
        // Initialize PerfectScrollbar after a short delay to ensure DOM is fully rendered
        setTimeout(() => {
            this.initPerfectScrollbar();
        }, 100);

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

    created() {
        // ตรวจสอบ route ที่มา (เฉพาะในกรณีที่มีการใช้ route meta)
        const fromRoute = this.$route.query.from;
        if (fromRoute === "detail") {
            this.loadFilterState();
        }
    },
    beforeUnmount() {
        if (this.ps) {
            this.ps.destroy();
            this.ps = null;
        }
    },

    updated() {
        // Update scrollbar after component updates
        this.updateScrollbar();
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
    cursor: pointer;
}
.desktop-row:hover {
    background-color: #f0f9f0;
    box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);
}

.desktop-row::after {
    content: attr(data-tooltip);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    padding: 5px;
    background-color: rgba(0, 0, 0, 0.8);
    color: white;
    border-radius: 4px;
    font-size: 12px;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s, visibility 0.3s;
}

.desktop-row:hover::after {
    opacity: 1;
    visibility: visible;
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

input[type="checkbox"].form-checkbox,
.form-checkbox {
    width: 20px !important;
    height: 20px !important;
    min-width: 20px !important;
    max-width: 20px !important;
    min-height: 20px !important;
    max-height: 20px !important;
    margin: 0 !important;
    display: inline-block !important;
    position: relative !important;
    vertical-align: middle !important;
    cursor: pointer !important;
    appearance: none !important;
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    border: 2px solid #d1d5db !important;
    border-radius: 4px !important;
    background-color: white !important;
    transition: all 0.2s ease !important;
    box-sizing: border-box !important;
    flex: 0 0 20px !important;
}

/* Checked state */
input[type="checkbox"].form-checkbox:checked,
.form-checkbox:checked {
    background-color: #10b981 !important;
    border-color: #10b981 !important;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='white'%3e%3cpath fill-rule='evenodd' d='M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z' clip-rule='evenodd'/%3e%3c/svg%3e") !important;
    background-size: 75% 75% !important;
    background-position: center !important;
    background-repeat: no-repeat !important;
}

/* Address the column width */
.table-auto td:first-child {
    width: 40px !important;
    max-width: 40px !important;
    min-width: 40px !important;
    padding: 4px !important;
    text-align: center !important;
}

.table-auto th:first-child {
    width: 40px !important;
    max-width: 40px !important;
    min-width: 40px !important;
    padding: 4px !important;
    text-align: center !important;
}

/* Add disabled checkbox styling */
input[type="checkbox"].form-checkbox:disabled,
.form-checkbox:disabled {
    opacity: 0.5 !important;
    cursor: not-allowed !important;
    background-color: #e5e7eb !important;
    border-color: #9ca3af !important;
}

/* Add a distinct style for rows that can't be selected */
.table-auto tbody tr.disabled-row {
    background-color: #f9f9f9;
    opacity: 0.7;
}

/* Add style for disabled checkbox cells */
.table-auto td.disabled-checkbox {
    background-color: #f5f5f5;
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
/* .table-auto {
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
    border-radius: 0.5rem;
    overflow: hidden;
} */

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
    border: none;
    vertical-align: middle;
    border-bottom: 1px solid #e5e7eb;
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

/* Add these styles to your existing <style> section */
/* Mobile card view styling */
.status-display {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 0.75rem;
    min-width: 80px;
}

.status-display i {
    font-size: 1.75rem;
    margin-bottom: 0.25rem;
}

/* Status badge styling */
.status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 4px 8px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 500;
    white-space: nowrap;
}

/* Status colors with soft backgrounds */
.text-warning {
    color: #ffc107;
}

.text-primary {
    color: #0d6efd;
}

.text-success {
    color: #198754;
}

.text-danger {
    color: #dc3545;
}

.text-gray-400 {
    color: #9ca3af;
}

/* Status badge background colors */
.bg-blue-100 {
    background-color: rgba(13, 110, 253, 0.15);
    color: #0d6efd;
}

.bg-orange-100 {
    background-color: rgba(255, 193, 7, 0.15);
    color: #fd7e14;
}

.bg-green-100 {
    background-color: rgba(25, 135, 84, 0.15);
    color: #198754;
}

.bg-red-100 {
    background-color: rgba(220, 53, 69, 0.15);
    color: #dc3545;
}

/* Flex helpers for mobile layout */
.flex {
    display: flex;
}

.flex-column {
    flex-direction: column;
}

.flex-1 {
    flex: 1;
}

.flex-shrink-0 {
    flex-shrink: 0;
}

.items-center {
    align-items: center;
}

.justify-center {
    justify-content: center;
}

.mr-4 {
    margin-right: 1rem;
}

/* Mobile card optimization */
@media (max-width: 768px) {
    .card {
        padding: 0.75rem;
    }

    .status-display {
        padding: 0.5rem;
        min-width: 70px;
    }

    .status-display i {
        font-size: 1.5rem;
    }
    .flex-1.justify-items-start {
        font-size: 10px;
    }
}
/* Table container with fixed header */
.table-container-wrapper {
    position: relative;
    width: 100%;
}

.table-scroll-container {
    position: relative;
    max-height: calc(100vh - 240px);
    overflow: auto;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    min-height: 410px; /* Add minimum height */
}

.table-auto {
    border-collapse: separate !important; /* Must be separate for sticky to work */
    border-spacing: 0;
    width: 100%;
}

/* Fixed header styling */
.table-auto thead {
    position: sticky;
    top: 0;
    z-index: 20;
    background-color: #f9fafb;
    text-align: center;
    white-space: nowrap;
}

.table-auto thead th {
    font-size: 14px;
    position: sticky;
    top: 0;
    background-color: #f3f4f6;
    z-index: 20;
    padding: 0.75rem;
    border-bottom: 2px solid #e5e7eb;
    font-weight: 600;
    text-align: left;
    color: #374151;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Add stronger shadow for better visibility */
}
.table-scroll-container {
    max-height: calc(100vh - 240px);
    overflow: auto;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    position: relative; /* Ensure proper positioning context */
}
/* Custom scrollbar styling to match perfect-scrollbar */
.table-scroll-container::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

.table-scroll-container::-webkit-scrollbar-track {
    background: transparent;
}

.table-scroll-container::-webkit-scrollbar-thumb {
    background-color: rgba(0, 0, 0, 0.2);
    border-radius: 3px;
}

.table-scroll-container::-webkit-scrollbar-thumb:hover {
    background-color: rgba(0, 0, 0, 0.3);
}

/* Perfect scrollbar customization */
.ps__rail-y {
    width: 9px;
    background-color: transparent !important;
}

.ps__thumb-y {
    width: 6px;
    background-color: rgba(0, 0, 0, 0.2);
    border-radius: 6px;
}

.ps__rail-y:hover > .ps__thumb-y,
.ps__rail-y:focus > .ps__thumb-y,
.ps__rail-y.ps--clicking .ps__thumb-y {
    width: 6px;
    background-color: rgba(0, 0, 0, 0.3);
}

.ps__rail-x {
    height: 9px;
    background-color: transparent !important;
}

.ps__thumb-x {
    height: 6px;
    background-color: rgba(0, 0, 0, 0.2);
    border-radius: 6px;
}

.ps__rail-x:hover > .ps__thumb-x,
.ps__rail-x:focus > .ps__thumb-x,
.ps__rail-x.ps--clicking .ps__thumb-x {
    height: 6px;
    background-color: rgba(0, 0, 0, 0.3);
}

/* Mobile responsive styles */
@media (max-width: 768px) {
    .table-scroll-container {
        max-height: calc(100vh - 300px);
    }

    .absolute.mt-1.bg-white.p-2.rounded.shadow-lg.z-10 {
        width: 90vw;
        max-width: 90vw;
        left: 0;
        right: 0;
        margin-left: auto;
        margin-right: auto;
    }
}

/* Modern Payment Request Modal Styles */
.modern-modal {
    border: none;
    border-radius: 20px;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
    overflow: hidden;
}

/* Enhanced Header */
.modern-header {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    padding: 1.5rem;
    border-bottom: none;
}

.header-content {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.header-icon {
    width: 50px;
    height: 50px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.header-text .modal-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0;
}

.modal-subtitle {
    margin: 0;
    opacity: 0.9;
    font-size: 0.875rem;
    font-weight: 400;
}

.modern-close {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 10px;
    opacity: 1;
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.modern-close::before {
    content: "×";
    font-size: 26px;
    font-weight: 300;
    line-height: 1;
}
.modern-close:hover {
    background: rgba(255, 255, 255, 0.3);
}

/* Progress Steps */
.progress-steps {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem 0;
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.step-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e5e7eb;
    color: #9ca3af;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
    transition: all 0.3s ease;
}

.step.active .step-circle {
    background: #10b981;
    color: white;
}

.step-label {
    font-size: 0.75rem;
    color: #6b7280;
    font-weight: 500;
    text-align: center;
}

.step.active .step-label {
    color: #10b981;
    font-weight: 600;
}

.step-connector {
    width: 60px;
    height: 2px;
    background: #e5e7eb;
    margin: 0 1rem;
}

/* Summary Card */
.summary-card {
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    border: 1px solid #bae6fd;
    border-radius: 15px;
    padding: 1.25rem;
}

.summary-header {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.summary-icon {
    width: 45px;
    height: 45px;
    background: #0ea5e9;
    color: white;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.125rem;
}

.summary-title {
    margin: 0;
    color: #0c4a6e;
    font-weight: 600;
    font-size: 1rem;
}

.summary-count {
    margin: 0;
    color: #075985;
    font-size: 0.875rem;
}

.count-badge {
    background: #0ea5e9;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.75rem;
}

/* Modern Alert */
.modern-alert {
    border: none;
    border-radius: 12px;
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border-left: 4px solid #f59e0b;
    margin-top: 1rem;
    padding: 1rem;
    display: flex;
    gap: 1rem;
}

.alert-icon {
    color: #d97706;
    font-size: 1.125rem;
    margin-top: 0.125rem;
}

.alert-content {
    flex: 1;
}

.duplicate-item {
    background: rgba(251, 191, 36, 0.2);
    padding: 0.5rem;
    border-radius: 8px;
    margin: 0.25rem 0;
    font-family: "Monaco", "Menlo", monospace;
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.more-items {
    font-style: italic;
    color: #92400e;
    margin-top: 0.5rem;
}

/* Modern Form */
.modern-body {
    padding: 1.5rem;
}

.modern-label {
    display: flex;
    align-items: center;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.input-wrapper {
    position: relative;
}

.modern-input,
.modern-select {
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 0.75rem 1rem;
    font-size: 0.925rem;
    transition: all 0.3s ease;
    background: white;
}

.modern-input:focus,
.modern-select:focus {
    border-color: #10b981;
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
    outline: none;
}

.select-arrow {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    pointer-events: none;
    font-size: 0.75rem;
}

/* Auto Title Display */
.auto-title-display {
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
    border: 2px dashed #d1d5db;
    border-radius: 12px;
    padding: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-family: "Monaco", "Menlo", monospace;
}

.auto-text {
    color: #6b7280;
    font-size: 0.8rem;
    font-style: italic;
}

.title-preview {
    color: #374151;
    font-weight: 600;
    font-size: 0.9rem;
}

/* Installment Controls */
.installment-wrapper {
    position: relative;
}

.installment-controls {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.installment-btn {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    width: 20px;
    height: 16px;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.6rem;
    color: #6b7280;
    transition: all 0.2s ease;
    cursor: pointer;
}

.installment-btn:hover {
    background: #10b981;
    color: white;
    border-color: #10b981;
}

/* Input Feedback */
.input-feedback {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
}

.char-count {
    font-size: 0.7rem;
    color: #9ca3af;
    background: #f9fafb;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
}

/* Enhanced Footer */
.modern-footer {
    background: #f9fafb;
    border-top: 1px solid #e5e7eb;
    padding: 1.25rem 1.5rem;
}

.footer-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}

.modern-btn-secondary {
    background: white;
    border: 2px solid #e5e7eb;
    color: #6b7280;
    padding: 0.75rem 1.5rem;
    border-radius: 12px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.modern-btn-secondary:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
    color: #374151;
}

.modern-btn-primary {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border: none;
    color: white;
    padding: 0.75rem 2rem;
    border-radius: 12px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
}

.modern-btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5);
}

.modern-btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

/* Loading Button */
.btn-loading,
.btn-content {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.spinner {
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top: 2px solid white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .modern-header {
        padding: 1rem;
    }

    .header-content {
        gap: 0.75rem;
    }

    .header-icon {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }

    .modal-title {
        font-size: 1.125rem;
    }

    .progress-steps {
        padding: 0.75rem 0;
    }

    .step-circle {
        width: 35px;
        height: 35px;
        font-size: 0.8rem;
    }

    .step-connector {
        width: 40px;
        margin: 0 0.5rem;
    }

    .modern-body {
        padding: 1rem;
    }

    .summary-card {
        padding: 1rem;
    }

    .footer-actions {
        flex-direction: column;
        gap: 0.75rem;
    }

    .modern-btn-secondary,
    .modern-btn-primary {
        width: 100%;
        justify-content: center;
    }
}
/* ปุ่มสร้าง phiếu trình thanh toán แบบทันสมัย */
.modern-create-payment-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: linear-gradient(90deg, #10b981 0%, #059669 100%);
    color: #fff;
    border: none;
    border-radius: 0.5rem;
    padding: 0.5rem 1.5rem;
    font-size: 1rem;
    font-weight: 600;
    box-shadow: 0 4px 16px rgba(16, 185, 129, 0.15);
    transition: background 0.2s, box-shadow 0.2s, transform 0.1s;
    cursor: pointer;
    outline: none;
}

.modern-create-payment-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    box-shadow: none;
}

.modern-create-payment-btn:hover:not(:disabled) {
    background: linear-gradient(90deg, #059669 0%, #10b981 100%);
    box-shadow: 0 6px 24px rgba(16, 185, 129, 0.25);
    transform: translateY(-2px) scale(1.03);
}

.modern-create-payment-btn i {
    font-size: 1.25rem;
}

.btn-label {
    display: inline-block;
}

/* ==================== Mobile Controls Styling ==================== */
/* Responsive */
@media (max-width: 768px) {
    .mobile-controls-container {
        background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 1rem;
        padding: 1.25rem;
        margin-bottom: 1rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1),
            0 2px 4px -1px rgba(0, 0, 0, 0.06),
            0 0 0 1px rgba(226, 232, 240, 0.7);
        border: 1px solid rgba(226, 232, 240, 0.7);
        position: relative;
        overflow: hidden;
    }

    .mobile-controls-container::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(
            90deg,
            #10b981 0%,
            #059669 50%,
            #10b981 100%
        );
    }

    .mobile-header-row {
        display: flex;

        margin-bottom: 1rem;
        align-items: center;
    }

    .mobile-search-section {
        flex: 0 0 80%;
    }

    /* Modern Search Wrapper */
    .modern-search-wrapper {
        position: relative;
        width: 100%;
    }

    .search-input-container {
        position: relative;
        display: flex;
        align-items: center;
        background: linear-gradient(145deg, #ffffff 0%, #f9fafb 100%);
        border: 2px solid #e5e7eb;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .search-input-container:focus-within {
        border-color: #10b981;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1),
            0 4px 8px rgba(0, 0, 0, 0.1);
        background: #ffffff;
        transform: translateY(-1px);
    }

    .search-icon-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 1rem;
        color: #9ca3af;
        font-size: 1rem;
        transition: color 0.3s ease;
    }

    .search-input-container:focus-within .search-icon-wrapper {
        color: #10b981;
    }

    .modern-search-input {
        flex: 1;
        height: 48px;
        border: none;
        background: transparent;
        padding: 0 1rem 0 0;
        font-size: 0.95rem;
        font-weight: 500;
        color: #374151;
        outline: none;
        transition: all 0.3s ease;
    }

    .modern-search-input::placeholder {
        color: #9ca3af;
        font-weight: 400;
        transition: color 0.3s ease;
    }

    .modern-search-input:focus::placeholder {
        color: #d1d5db;
    }

    .clear-search-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        margin-right: 0.5rem;
        background: rgba(156, 163, 175, 0.1);
        border-radius: 50%;
        color: #6b7280;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.875rem;
    }

    .clear-search-btn:hover {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
        transform: scale(1.1);
    }

    .search-no-results {
        position: absolute;
        top: calc(100% + 0.5rem);
        left: 0;
        right: 0;
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        border: 1px solid #fecaca;
        border-radius: 0.75rem;
        padding: 0.75rem 1rem;
        color: #dc2626;
        font-size: 0.875rem;
        font-weight: 500;
        z-index: 10;
        box-shadow: 0 4px 6px rgba(220, 38, 38, 0.1);
        animation: slideInDown 0.3s ease-out;
    }

    .search-results-count {
        position: absolute;
        top: calc(100% + 0.5rem);
        left: 0;
        right: 0;
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        border: 1px solid #bae6fd;
        border-radius: 0.75rem;
        padding: 0.75rem 1rem;
        color: #0369a1;
        font-size: 0.875rem;
        font-weight: 500;
        z-index: 10;
        box-shadow: 0 4px 6px rgba(3, 105, 161, 0.1);
        animation: slideInDown 0.3s ease-out;
    }

    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .mobile-action-section {
        flex: 0 0 15%;
        display: flex;
        justify-content: flex-end;
    }

    .btn-mobile-menu {
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        border: 2px solid #d1d5db;
        border-radius: 0.75rem;
        color: #6b7280;
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .btn-mobile-menu::before {
        content: "";
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        background: rgba(16, 185, 129, 0.2);
        border-radius: 50%;
        transition: all 0.3s ease;
        transform: translate(-50%, -50%);
    }

    .btn-mobile-menu:hover::before {
        width: 100px;
        height: 100px;
    }

    .btn-mobile-menu:hover {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-color: #10b981;
        color: white;
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 8px 12px rgba(16, 185, 129, 0.25);
    }

    .mobile-filter-row {
        display: flex;
        gap: 0.75rem;
        align-items: center;
    }

    .mobile-filter-left {
        flex: 0 0 60%;
    }

    .mobile-filter-right {
        flex: 0 0 40%;
        display: flex;
        justify-content: flex-start;
        gap: 0.5rem;
    }

    .mobile-status-select {
        width: 100%;
        height: 48px;
        border: 2px solid #e5e7eb;
        border-radius: 0.75rem;
        padding: 0 1rem;
        background: linear-gradient(145deg, #ffffff 0%, #f9fafb 100%);
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        transition: all 0.3s ease;
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.75rem center;
        background-repeat: no-repeat;
        background-size: 1.5em 1.5em;
        padding-right: 3rem;
    }

    .mobile-status-select:focus {
        outline: none;
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        background: #ffffff;
    }

    .mobile-filter-btn {
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        border: 2px solid #d1d5db;
        border-radius: 0.75rem;
        color: #6b7280;
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .mobile-filter-btn::before {
        content: "";
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        background: rgba(16, 185, 129, 0.2);
        border-radius: 50%;
        transition: all 0.3s ease;
        transform: translate(-50%, -50%);
    }

    .mobile-filter-btn:hover::before {
        width: 100px;
        height: 100px;
    }

    .mobile-filter-btn:hover {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-color: #10b981;
        color: white;
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 8px 12px rgba(16, 185, 129, 0.25);
    }

    /* Mobile Filter Modal */
    .mobile-filter-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
        z-index: 1050;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .mobile-filter-modal {
        background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 1rem;
        width: 100%;
        max-width: 400px;
        max-height: 85vh;
        overflow: hidden;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1),
            0 10px 10px -5px rgba(0, 0, 0, 0.04);
        border: 1px solid rgba(226, 232, 240, 0.8);
        animation: modalSlideIn 0.3s ease-out;
    }

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: scale(0.9) translateY(20px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    .modal-header {
        padding: 1.5rem 1.5rem 1rem;
        border-bottom: 1px solid rgba(226, 232, 240, 0.7);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    }

    .modal-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
        flex: 1;
        display: flex;
        align-items: center;
    }

    .btn-close {
        background: transparent;
        border: none;
        color: #6b7280;
        font-size: 1.25rem;
        padding: 0.5rem;
        border-radius: 0.375rem;
        transition: all 0.2s ease;
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-close:hover {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
        transform: scale(1.1);
    }

    .modal-body {
        padding: 1rem 1.5rem;
        max-height: 55vh;
        overflow-y: auto;
    }

    .filter-section {
        margin-bottom: 1.5rem;
        padding: 1rem;
        background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
        border-radius: 0.75rem;
        border: 1px solid rgba(226, 232, 240, 0.5);
    }

    .filter-label {
        display: flex;
        align-items: center;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.75rem;
        font-size: 0.95rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid rgba(16, 185, 129, 0.2);
    }

    .checkbox-container {
        max-height: 200px;
        overflow-y: auto;
        padding: 0.25rem;
    }

    .form-check {
        margin-bottom: 0.75rem;
        /* padding: 0.5rem; */
        border-radius: 0.5rem;
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }

    .form-check:hover {
        background: rgba(16, 185, 129, 0.05);
        border-color: rgba(16, 185, 129, 0.2);
    }

    .form-check-input {
        width: 18px;
        height: 18px;
        margin-top: 0.125rem;
        margin-right: 0.75rem;
        border: 2px solid #d1d5db;
        border-radius: 0.375rem;
        transition: all 0.2s ease;
    }

    .form-check-input:checked {
        background-color: #10b981;
        border-color: #10b981;
    }

    .form-check-input:focus {
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
    }

    .form-check-label {
        font-size: 0.875rem;
        color: #4b5563;
        font-weight: 500;
        cursor: pointer;
        flex: 1;
    }

    .modal-footer {
        padding: 1rem 1.5rem 1.5rem;
        border-top: 1px solid rgba(226, 232, 240, 0.7);
        display: flex;
        gap: 0.75rem;
        justify-content: flex-end;
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        transition: all 0.3s ease;
        border: 1px solid transparent;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        text-decoration: none;
        position: relative;
        overflow: hidden;
    }

    .btn::before {
        content: "";
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        transition: all 0.3s ease;
        transform: translate(-50%, -50%);
    }

    .btn:hover::before {
        width: 100px;
        height: 100px;
    }

    .btn-outline-secondary {
        color: #6b7280;
        border-color: #d1d5db;
        background: transparent;
    }

    .btn-outline-secondary:hover {
        background: #f3f4f6;
        border-color: #9ca3af;
        color: #4b5563;
        transform: translateY(-1px);
    }

    .btn-primary {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border: none;
        box-shadow: 0 4px 6px rgba(16, 185, 129, 0.25);
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 12px rgba(16, 185, 129, 0.3),
            0 3px 6px rgba(0, 0, 0, 0.15);
    }

    .btn-primary:active {
        transform: translateY(0);
        box-shadow: 0 2px 4px rgba(16, 185, 129, 0.25);
    }

    /* Enhanced dropdown menu for mobile */
    .dropdown-menu {
        min-width: 200px;
        padding: 0.5rem 0;
        margin: 0.125rem 0 0;
        border-radius: 0.75rem;
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1),
            0 4px 6px -2px rgba(0, 0, 0, 0.05);
        background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
    }

    .dropdown-item {
        padding: 0.75rem 1rem;
        display: flex;
        align-items: center;
        color: #374151;
        transition: all 0.2s ease;
        font-size: 0.875rem;
        font-weight: 500;
        border-radius: 0.5rem;
        margin: 0.125rem 0.5rem;
    }

    .dropdown-item:hover {
        background: linear-gradient(
            135deg,
            rgba(16, 185, 129, 0.1) 0%,
            rgba(16, 185, 129, 0.05) 100%
        );
        color: #059669;
        transform: translateX(4px);
    }

    .dropdown-item i {
        font-size: 1rem;
        width: 20px;
        text-align: center;
    }
}
/* Responsive adjustments */
@media (max-width: 480px) {
    .mobile-controls-container {
        padding: 1rem;
        margin: 0.5rem;
    }

    .search-input-container {
        width: 13rem;
    }

    .mobile-header-row {
        gap: 0.5rem;
    }

    .mobile-filter-row {
        gap: 0.5rem;
    }

    .mobile-filter-modal {
        margin: 0.5rem;
        width: calc(100% - 1rem);
    }

    .btn-mobile-menu,
    .mobile-filter-btn {
        width: 42px;
        height: 42px;
        font-size: 1rem;
    }

    .mobile-status-select {
        height: 42px;
        font-size: 0.8rem;
    }
}

/* Mobile Pagination - Simple & Clean */
.mobile-pagination-card {
    background: white;
    border-radius: 12px;
    padding: 16px;
    margin-top: 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border: 1px solid #e5e7eb;
}

.pagination-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    font-size: 14px;
    color: #6b7280;
}

.page-info {
    font-weight: 600;
    color: #374151;
}

.pagination-controls {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
}

.page-btn {
    width: 40px;
    height: 40px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: white;
    color: #6b7280;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    cursor: pointer;
}

.page-btn:hover:not(:disabled) {
    border-color: #10b981;
    color: #10b981;
    transform: translateY(-1px);
}

.page-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.current-page {
    background: #10b981;
    color: white;
    padding: 8px 16px;
    border-radius: 8px;
    font-weight: 600;
    margin: 0 8px;
    min-width: 40px;
    text-align: center;
}

.quick-jump {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding-top: 12px;
    border-top: 1px solid #e5e7eb;
    font-size: 14px;
    color: #6b7280;
}

.quick-jump select {
    border: 1px solid #d1d5db;
    border-radius: 6px;
    padding: 4px 8px;
    background: white;
    color: #374151;
    cursor: pointer;
}

.quick-jump select:focus {
    outline: none;
    border-color: #10b981;
}

@media (max-width: 480px) {
    .pagination-info {
        flex-direction: column;
        gap: 4px;
        text-align: center;
    }

    .page-btn {
        width: 36px;
        height: 36px;
    }

    .current-page {
        padding: 6px 12px;
        margin: 0 4px;
    }
}
</style>
