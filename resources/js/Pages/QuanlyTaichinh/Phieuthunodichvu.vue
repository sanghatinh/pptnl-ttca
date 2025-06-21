<template>
    <!-- Loading overlay -->
    <div id="loading-wrapper" v-if="isLoading">
        <div class="spinner-border" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>

    <div class="container-fluid mx-auto p-2">
        <!-- Top toolbar with search and actions -->
        <div class="row align-items-center mb-2" v-if="!isMobile">
            <div class="col d-flex justify-content-start gap-3">
                <div class="status-filter">
                    <select
                        v-model="statusFilter"
                        class="form-select status-select"
                    >
                        <option
                            v-for="option in statusOptionsWithCount"
                            :key="option.code"
                            :value="option.code"
                        >
                            {{ option.name }} ({{ option.count }})
                        </option>
                    </select>
                </div>
                <div class="filter-section">
                    <div class="relative">
                        <select
                            v-model="selectedFilterValues.vu_dau_tu"
                            class="form-select status-select flex-1"
                            @change="applyFilter('vu_dau_tu')"
                        >
                            <option value="">
                                {{
                                    uniqueValues.vu_dau_tu &&
                                    uniqueValues.vu_dau_tu.length > 0
                                        ? "Vụ đầu tư"
                                        : "Đang tải..."
                                }}
                            </option>
                            <option
                                v-for="(
                                    option, index
                                ) in uniqueValues.vu_dau_tu"
                                :key="index"
                                :value="option"
                            >
                                {{ option }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Actions dropdown menu -->
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
                                    Export to Excel
                                </a>
                            </li>
                            <!-- เพิ่มปุ่ม Report PDF ใหม่ -->
                            <li>
                                <a
                                    class="dropdown-item"
                                    href="#"
                                    @click.prevent="showReportModal"
                                >
                                    <i
                                        class="fas fa-file-pdf text-danger me-2"
                                    ></i>
                                    Report PDF
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
                            <li>
                                <a
                                    class="dropdown-item"
                                    href="#"
                                    @click.prevent="downloadImportTemplate"
                                >
                                    <i
                                        class="fas fa-file-download text-info me-2"
                                    ></i>
                                    Download Import Template
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div
                class="col d-flex justify-content-end gap-3 align-items-center"
            >
                <input
                    v-model="search"
                    type="text"
                    placeholder="Tìm kiếm phiếu..."
                    class="search-input px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500"
                />
            </div>
        </div>

        <!-- Add this after the top toolbar with search and actions -->
        <div class="row mb-0">
            <div class="col-12">
                <div class="totals-container">
                    <div class="total-card total-count">
                        <div class="total-icon">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <div class="total-content">
                            <div class="total-label">Tổng số phiếu</div>
                            <div class="total-value">
                                {{ totals.total_count }}
                            </div>
                        </div>
                    </div>
                    <div class="total-card total-debt">
                        <div class="total-icon">
                            <i class="fas fa-money-bill"></i>
                        </div>
                        <div class="total-content">
                            <div class="total-label">Tổng tiền đã trả gốc</div>
                            <div class="total-value">
                                {{ formatCurrency(totals.total_paid) }}
                            </div>
                        </div>
                    </div>

                    <div class="total-card total-interest">
                        <div class="total-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="total-content">
                            <div class="total-label">Tổng tiền lãi</div>
                            <div class="total-value">
                                {{ formatCurrency(totals.total_interest) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Controls -->
        <div
            class="mobile-controls p-3 bg-white rounded-lg shadow-sm mb-3"
            v-if="isMobile"
        >
            <div class="flex flex-col gap-3">
                <!-- Search bar with icon and filter icon in the same row -->
                <div class="search-filter-container">
                    <div class="search-container relative">
                        <span
                            class="search-icon absolute left-3 top-1/2 transform -translate-y-1/2"
                        >
                            <i class="fas fa-search text-gray-400"></i>
                        </span>

                        <input
                            v-model="search"
                            type="text"
                            placeholder="Tìm kiếm phiếu..."
                            class="w-full pl-10 pr-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm"
                        />
                    </div>

                    <button
                        class="date-filter-toggle-btn"
                        @click="toggleDateFilter"
                        :class="{ active: dateRange.active }"
                        title="Lọc theo ngày trả"
                    >
                        <i class="fas fa-calendar-alt"></i>
                        <span
                            class="filter-indicator"
                            v-if="dateRange.active"
                        ></span>
                    </button>

                    <span
                        class="reset-all-filters-btn-mobile"
                        title="Reset all filters"
                        @click="resetAllFilters"
                    >
                        <i class="fas fa-redo-alt"></i>
                    </span>
                </div>

                <!-- Filters and other controls -->
                <div class="flex gap-2">
                    <select
                        v-model="statusFilter"
                        class="form-select status-select flex-1"
                    >
                        <option
                            v-for="option in statusOptionsWithCount"
                            :key="option.code"
                            :value="option.code"
                        >
                            {{ option.name }} ({{ option.count }})
                        </option>
                    </select>

                    <select
                        v-model="selectedFilterValues.vu_dau_tu"
                        class="form-select status-select flex-1"
                        @change="applyFilter('vu_dau_tu')"
                    >
                        <option value="">Vụ đầu tư</option>
                        <option
                            v-for="(option, index) in uniqueValues.vu_dau_tu"
                            :key="index"
                            :value="option"
                        >
                            {{ option }}
                        </option>
                    </select>

                    <button
                        class="btn-icon bg-light rounded-circle p-2"
                        @click="showActionsMenu"
                    >
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Main data table -->
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <!-- Desktop view table -->
            <div v-if="!isMobile" class="desktop-controls-container card">
                <div class="card-body">
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
                                        <!-- เพิ่มคอลัมน์ checkbox ที่แรก -->
                                        <th
                                            class="text-center"
                                            style="width: 40px"
                                        >
                                            <input
                                                type="checkbox"
                                                :checked="isAllSelected"
                                                @change="toggleSelectAll"
                                                class="form-check-input"
                                            />
                                        </th>
                                        <!-- Mã số phiếu -->
                                        <th>
                                            Mã số phiếu
                                            <button
                                                @click="
                                                    toggleFilter('ma_so_phieu')
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.ma_so_phieu,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'ma_so_phieu'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.ma_so_phieu
                                                    "
                                                    class="form-control mb-2"
                                                    placeholder="Lọc theo mã số..."
                                                />
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'ma_so_phieu'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'ma_so_phieu'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Phân bổ đầu tư -->
                                        <th>
                                            Phân bổ đầu tư
                                            <button
                                                @click="
                                                    toggleFilter(
                                                        'phan_bo_dau_tu'
                                                    )
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.phan_bo_dau_tu,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'phan_bo_dau_tu'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.phan_bo_dau_tu
                                                    "
                                                    class="form-control mb-2"
                                                    placeholder="Lọc theo phân bổ..."
                                                />
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'phan_bo_dau_tu'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'phan_bo_dau_tu'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>
                                        <!-- Add this column in the table header after the "Mã số phiếu" column -->
                                        <th>
                                            Mã giải ngân
                                            <button
                                                @click="
                                                    toggleFilter('ma_giai_ngan')
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.ma_giai_ngan,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'ma_giai_ngan'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.ma_giai_ngan
                                                    "
                                                    class="form-control mb-2"
                                                    placeholder="Lọc theo mã giải ngân..."
                                                />
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'ma_giai_ngan'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'ma_giai_ngan'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Invoice Number -->
                                        <th>
                                            Invoice Number
                                            <button
                                                @click="
                                                    toggleFilter(
                                                        'invoice_number'
                                                    )
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.invoice_number,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'invoice_number'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.invoice_number
                                                    "
                                                    class="form-control mb-2"
                                                    placeholder="Lọc theo invoice..."
                                                />
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'invoice_number'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'invoice_number'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Category Debt -->
                                        <th>
                                            Category Debt
                                            <button
                                                @click="
                                                    toggleFilter(
                                                        'category_debt'
                                                    )
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            selectedFilterValues.category_debt &&
                                                            selectedFilterValues
                                                                .category_debt
                                                                .length > 0,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'category_debt'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <div
                                                    class="max-h-40 overflow-y-auto"
                                                >
                                                    <div
                                                        v-for="(
                                                            option, index
                                                        ) in uniqueValues.category_debt"
                                                        :key="index"
                                                        class="flex items-center mb-2"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            :id="`category_debt-${index}`"
                                                            :value="option"
                                                            v-model="
                                                                selectedFilterValues.category_debt
                                                            "
                                                            class="form-checkbox h-4 w-4 text-green-600"
                                                        />
                                                        <label
                                                            :for="`category_debt-${index}`"
                                                            class="ml-2 text-gray-700"
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
                                                                'category_debt'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'category_debt'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Đã trả gốc -->
                                        <th>
                                            Đã trả gốc
                                            <button
                                                @click="
                                                    toggleFilter('da_tra_goc')
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.da_tra_goc,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'da_tra_goc'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.da_tra_goc
                                                    "
                                                    class="form-control mb-2"
                                                    placeholder="Lọc theo số tiền..."
                                                />
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'da_tra_goc'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'da_tra_goc'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Ngày vay -->
                                        <th>
                                            Ngày vay
                                            <button
                                                @click="
                                                    toggleFilter('ngay_vay')
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.ngay_vay,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter === 'ngay_vay'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="date"
                                                    v-model="
                                                        columnFilters.ngay_vay
                                                    "
                                                    class="form-control mb-2"
                                                />
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'ngay_vay'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'ngay_vay'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Ngày trả -->
                                        <th>
                                            Ngày trả
                                            <button
                                                @click="
                                                    toggleFilter('ngay_tra')
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.ngay_tra,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter === 'ngay_tra'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="date"
                                                    v-model="
                                                        columnFilters.ngay_tra
                                                    "
                                                    class="form-control mb-2"
                                                />
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'ngay_tra'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'ngay_tra'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Lãi suất -->
                                        <th>
                                            Lãi suất
                                            <button
                                                @click="
                                                    toggleFilter('lai_suat')
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.lai_suat,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter === 'lai_suat'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.lai_suat
                                                    "
                                                    class="form-control mb-2"
                                                    placeholder="Lọc theo lãi suất..."
                                                />
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'lai_suat'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'lai_suat'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Tiền lãi -->
                                        <th>
                                            Tiền lãi
                                            <button
                                                @click="
                                                    toggleFilter('tien_lai')
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.tien_lai,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter === 'tien_lai'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.tien_lai
                                                    "
                                                    class="form-control mb-2"
                                                    placeholder="Lọc theo tiền lãi..."
                                                />
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'tien_lai'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'tien_lai'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Vụ đầu tư -->
                                        <th>
                                            Vụ đầu tư
                                            <button
                                                @click="
                                                    toggleFilter('vu_dau_tu')
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            selectedFilterValues.vu_dau_tu &&
                                                            selectedFilterValues
                                                                .vu_dau_tu
                                                                .length > 0,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter === 'vu_dau_tu'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <div
                                                    class="max-h-40 overflow-y-auto"
                                                >
                                                    <div
                                                        v-for="(
                                                            option, index
                                                        ) in uniqueValues.vu_dau_tu"
                                                        :key="index"
                                                        class="flex items-center mb-2"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            :id="`vu_dau_tu-${index}`"
                                                            :value="option"
                                                            v-model="
                                                                selectedFilterValues.vu_dau_tu
                                                            "
                                                            class="form-checkbox h-4 w-4 text-green-600"
                                                        />
                                                        <label
                                                            :for="`vu_dau_tu-${index}`"
                                                            class="ml-2 text-gray-700"
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
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'vu_dau_tu'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Vụ thanh toán -->
                                        <th>
                                            Vụ thanh toán
                                            <button
                                                @click="
                                                    toggleFilter(
                                                        'vu_thanh_toan'
                                                    )
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            selectedFilterValues.vu_thanh_toan &&
                                                            selectedFilterValues
                                                                .vu_thanh_toan
                                                                .length > 0,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'vu_thanh_toan'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <div
                                                    class="max-h-40 overflow-y-auto"
                                                >
                                                    <div
                                                        v-for="(
                                                            option, index
                                                        ) in uniqueValues.vu_thanh_toan"
                                                        :key="index"
                                                        class="flex items-center mb-2"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            :id="`vu_thanh_toan-${index}`"
                                                            :value="option"
                                                            v-model="
                                                                selectedFilterValues.vu_thanh_toan
                                                            "
                                                            class="form-checkbox h-4 w-4 text-green-600"
                                                        />
                                                        <label
                                                            :for="`vu_thanh_toan-${index}`"
                                                            class="ml-2 text-gray-700"
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
                                                                'vu_thanh_toan'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'vu_thanh_toan'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Khách hàng cá nhân -->
                                        <th>
                                            Khách hàng cá nhân
                                            <button
                                                @click="
                                                    toggleFilter(
                                                        'khach_hang_ca_nhan'
                                                    )
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.khach_hang_ca_nhan,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'khach_hang_ca_nhan'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.khach_hang_ca_nhan
                                                    "
                                                    class="form-control mb-2"
                                                    placeholder="Lọc theo tên khách hàng..."
                                                />
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'khach_hang_ca_nhan'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'khach_hang_ca_nhan'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Mã khách hàng cá nhân -->
                                        <th>
                                            Mã KH cá nhân
                                            <button
                                                @click="
                                                    toggleFilter(
                                                        'ma_khach_hang_ca_nhan'
                                                    )
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.ma_khach_hang_ca_nhan,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'ma_khach_hang_ca_nhan'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.ma_khach_hang_ca_nhan
                                                    "
                                                    class="form-control mb-2"
                                                    placeholder="Lọc theo mã KH..."
                                                />
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'ma_khach_hang_ca_nhan'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'ma_khach_hang_ca_nhan'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Khách hàng doanh nghiệp -->
                                        <th>
                                            KH doanh nghiệp
                                            <button
                                                @click="
                                                    toggleFilter(
                                                        'khach_hang_doanh_nghiep'
                                                    )
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.khach_hang_doanh_nghiep,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'khach_hang_doanh_nghiep'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.khach_hang_doanh_nghiep
                                                    "
                                                    class="form-control mb-2"
                                                    placeholder="Lọc theo tên doanh nghiệp..."
                                                />
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'khach_hang_doanh_nghiep'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'khach_hang_doanh_nghiep'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Mã khách hàng doanh nghiệp -->
                                        <th>
                                            Mã KH DN
                                            <button
                                                @click="
                                                    toggleFilter(
                                                        'ma_khach_hang_doanh_nghiep'
                                                    )
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.ma_khach_hang_doanh_nghiep,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'ma_khach_hang_doanh_nghiep'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.ma_khach_hang_doanh_nghiep
                                                    "
                                                    class="form-control mb-2"
                                                    placeholder="Lọc theo mã KH..."
                                                />
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'ma_khach_hang_doanh_nghiep'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'ma_khach_hang_doanh_nghiep'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Số trờ trình -->
                                        <th>
                                            Số trờ trình
                                            <button
                                                @click="
                                                    toggleFilter('so_tro_trinh')
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.so_tro_trinh,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'so_tro_trinh'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.so_tro_trinh
                                                    "
                                                    class="form-control mb-2"
                                                    placeholder="Lọc theo số trờ trình..."
                                                />
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'so_tro_trinh'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'so_tro_trinh'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Description -->
                                        <th>
                                            Description
                                            <button
                                                @click="
                                                    toggleFilter('description')
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.description,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'description'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.description
                                                    "
                                                    class="form-control mb-2"
                                                    placeholder="Lọc theo mô tả..."
                                                />
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'description'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'description'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Tình trạng -->
                                        <th>
                                            <div
                                                class="flex items-center justify-between"
                                            >
                                                <span>Tình trạng</span>
                                                <button
                                                    @click="
                                                        toggleFilter(
                                                            'tinh_trang'
                                                        )
                                                    "
                                                    class="filter-btn"
                                                >
                                                    <i
                                                        class="fas fa-filter"
                                                        :class="{
                                                            'text-green-500':
                                                                selectedFilterValues.tinh_trang &&
                                                                selectedFilterValues
                                                                    .tinh_trang
                                                                    .length > 0,
                                                        }"
                                                    ></i>
                                                </button>
                                            </div>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'tinh_trang'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <div
                                                    class="max-h-40 overflow-y-auto"
                                                >
                                                    <div
                                                        v-for="(
                                                            option, index
                                                        ) in uniqueValues.tinh_trang"
                                                        :key="index"
                                                        class="flex items-center mb-2"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            :id="`tinh_trang-${index}`"
                                                            :value="option"
                                                            v-model="
                                                                selectedFilterValues.tinh_trang
                                                            "
                                                            class="form-checkbox h-4 w-4 text-green-600"
                                                        />
                                                        <label
                                                            :for="`tinh_trang-${index}`"
                                                            class="ml-2 text-gray-700"
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
                                                                'tinh_trang'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'tinh_trang'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
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
                                        class="desktop-row cursor-pointer"
                                    >
                                        <!-- เพิ่มคอลัมน์ checkbox -->
                                        <td class="text-center">
                                            <input
                                                type="checkbox"
                                                :checked="isItemSelected(item)"
                                                @change="
                                                    toggleItemSelection(item)
                                                "
                                                class="form-check-input"
                                            />
                                        </td>
                                        <td>{{ item.ma_so_phieu }}</td>
                                        <td>{{ item.phan_bo_dau_tu }}</td>
                                        <td>
                                            <router-link
                                                v-if="item.ma_giai_ngan"
                                                :to="`/Details_Phieudenghithanhtoandichvu/${item.ma_giai_ngan}`"
                                                class="text-blue-600 hover:text-blue-800 hover:underline font-medium transition-colors"
                                            >
                                                {{ item.ma_giai_ngan }}
                                            </router-link>
                                            <span v-else>N/A</span>
                                        </td>
                                        <td>{{ item.invoice_number }}</td>
                                        <td>
                                            <span
                                                :class="
                                                    getCategoryBadgeClass(
                                                        item.category_debt
                                                    ).class
                                                "
                                            >
                                                <i
                                                    :class="
                                                        getCategoryBadgeClass(
                                                            item.category_debt
                                                        ).icon
                                                    "
                                                    class="mr-1"
                                                ></i>
                                                {{ item.category_debt }}
                                            </span>
                                        </td>
                                        <td>
                                            {{
                                                formatCurrency(item.da_tra_goc)
                                            }}
                                        </td>
                                        <td>{{ formatDate(item.ngay_vay) }}</td>
                                        <td>{{ formatDate(item.ngay_tra) }}</td>
                                        <td>{{ item.lai_suat }}%</td>
                                        <td>
                                            {{ formatCurrency(item.tien_lai) }}
                                        </td>
                                        <td>{{ item.vu_dau_tu }}</td>
                                        <td>{{ item.vu_thanh_toan }}</td>
                                        <td>{{ item.khach_hang_ca_nhan }}</td>
                                        <td>
                                            {{ item.ma_khach_hang_ca_nhan }}
                                        </td>
                                        <td>
                                            {{ item.khach_hang_doanh_nghiep }}
                                        </td>
                                        <td>
                                            {{
                                                item.ma_khach_hang_doanh_nghiep
                                            }}
                                        </td>
                                        <td>{{ item.so_tro_trinh }}</td>

                                        <td>{{ item.description }}</td>
                                        <td>
                                            <span
                                                :class="
                                                    statusClass(item.tinh_trang)
                                                "
                                                class="flex items-center"
                                            >
                                                <i
                                                    :class="
                                                        statusIcons(
                                                            item.tinh_trang
                                                        )
                                                    "
                                                    class="mr-1"
                                                ></i>
                                                {{
                                                    formatStatus(
                                                        item.tinh_trang
                                                    )
                                                }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="flex justify-center mt-4 pagination-wrapper">
                        <div class="">
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
        </div>

        <!-- Mobile view cards -->
        <div v-if="isMobile" class="card-container">
            <div
                v-for="item in paginatedItems.data"
                :key="item.id"
                class="card border p-4 mb-4 rounded shadow hover:shadow-green-500 transition-shadow duration-300"
            >
                <div
                    class="card-header-mobile mb-3 d-flex justify-content-between align-items-center"
                >
                    <h6 class="mb-0 font-weight-bold text-truncate">
                        {{ item.invoice_number }}
                    </h6>
                    <span
                        :class="getCategoryBadgeClass(item.category_debt).class"
                        class="d-inline-flex align-items-center"
                    >
                        <i
                            :class="
                                getCategoryBadgeClass(item.category_debt).icon
                            "
                            class="mr-1"
                        ></i>
                        {{ item.category_debt }}
                    </span>
                </div>

                <div class="customer-info p-2 rounded mb-3">
                    <div class="mb-1">
                        <strong>Khách hàng:</strong>
                        <span v-if="item.khach_hang_ca_nhan">{{
                            item.khach_hang_ca_nhan
                        }}</span>
                        <span v-else>{{
                            item.khach_hang_doanh_nghiep || "N/A"
                        }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span
                            ><strong>Mã KH:</strong>
                            {{
                                item.ma_khach_hang_ca_nhan ||
                                item.ma_khach_hang_doanh_nghiep ||
                                "N/A"
                            }}</span
                        >
                    </div>
                    <div class="mb-1">
                        <strong>Mã giải ngân:</strong>
                        <router-link
                            v-if="item.ma_giai_ngan"
                            :to="`/Details_Phieudenghithanhtoandichvu/${item.ma_giai_ngan}`"
                            class="text-blue-600 hover:text-blue-800 hover:underline font-medium transition-colors"
                        >
                            {{ item.ma_giai_ngan }}
                        </router-link>
                        <span v-else>N/A</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span
                            ><strong>Description:</strong>
                            {{ item.description }}</span
                        >
                    </div>
                </div>

                <div class="d-flex justify-content-between mb-3">
                    <div class="financial-item">
                        <div class="financial-label">Đã trả gốc</div>
                        <div class="financial-value text-success">
                            {{ formatCurrency(item.da_tra_goc) }}
                        </div>
                    </div>
                    <div class="financial-item">
                        <div class="financial-label">Tiền lãi</div>
                        <div class="financial-value text-warning">
                            {{ formatCurrency(item.tien_lai) }}
                        </div>
                    </div>
                </div>

                <div class="dates-section d-flex justify-content-between mb-3">
                    <div class="date-item">
                        <div class="date-label">
                            <i class="fas fa-calendar-plus mr-1"></i> Ngày vay
                        </div>
                        <div class="date-value">
                            {{ formatDate(item.ngay_vay) }}
                        </div>
                    </div>
                    <div class="date-item">
                        <div class="date-label">
                            <i class="fas fa-calendar-check mr-1"></i> Ngày trả
                        </div>
                        <div class="date-value">
                            {{ formatDate(item.ngay_tra) }}
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <span class="badge bg-light text-dark">
                        <i class="fas fa-percentage mr-1"></i>
                        {{ item.lai_suat }}% ({{ item.vu_dau_tu || "N/A" }})
                    </span>
                    <span class="card-btn">
                        <i class="fas fa-chevron-right"></i>
                    </span>
                </div>
            </div>

            <div class="mobile-pagination-card" v-if="isMobile">
                <div class="pagination-info">
                    <span class="page-info"
                        >Trang {{ paginatedItems.current_page }} của
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
                        :disabled="paginatedItems.current_page === 1"
                    >
                        <i class="fas fa-angle-double-left"></i>
                    </button>

                    <button
                        class="page-btn"
                        @click="pageChanged(paginatedItems.current_page - 1)"
                        :disabled="paginatedItems.current_page === 1"
                    >
                        <i class="fas fa-angle-left"></i>
                    </button>

                    <div class="current-page">
                        {{ paginatedItems.current_page }}
                    </div>

                    <button
                        class="page-btn"
                        @click="pageChanged(paginatedItems.current_page + 1)"
                        :disabled="
                            paginatedItems.current_page ===
                            paginatedItems.last_page
                        "
                    >
                        <i class="fas fa-angle-right"></i>
                    </button>

                    <button
                        class="page-btn"
                        @click="pageChanged(paginatedItems.last_page)"
                        :disabled="
                            paginatedItems.current_page ===
                            paginatedItems.last_page
                        "
                    >
                        <i class="fas fa-angle-double-right"></i>
                    </button>
                </div>

                <!-- Quick jump for many pages -->
                <div class="quick-jump" v-if="paginatedItems.last_page > 5">
                    <span>Đi đến trang:</span>
                    <select
                        :value="paginatedItems.current_page"
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

    <!-- Add Export Modal -->
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
                    <div class="d-flex flex-column">
                        <button
                            class="btn btn-outline-success mb-2"
                            @click="exportToExcelCurrentPage"
                        >
                            <i class="fas fa-file-export me-2"></i>
                            Export current page
                        </button>
                        <button
                            class="btn btn-outline-success"
                            @click="exportToExcelAllPages"
                        >
                            <i class="fas fa-file-export me-2"></i>
                            Export all data
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
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
                        <button
                            class="btn btn-outline-secondary mb-3 w-100"
                            @click="downloadImportTemplate"
                        >
                            <i class="fas fa-download me-2"></i> Tải mẫu nhập
                            liệu
                        </button>
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
    <!-- เพิ่ม Report PDF Modal หลังจาก Import Modal -->
    <div
        class="modal fade"
        id="reportPdfModal"
        tabindex="-1"
        aria-labelledby="reportPdfModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5
                        class="modal-title text-danger"
                        id="reportPdfModalLabel"
                    >
                        <i class="fas fa-file-pdf me-2"></i>Tạo báo cáo PDF
                    </h5>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                        @click="closeReportModal"
                    ></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">
                        <i class="fas fa-info-circle me-1"></i>
                        Chọn loại báo cáo bạn muốn tạo:
                    </p>
                    <div class="d-grid gap-2">
                        <!-- Report Selected Items Button -->
                        <button
                            @click="generateReportSelectedItems"
                            class="btn btn-outline-danger"
                            :disabled="
                                isGeneratingReport || selectedItems.length === 0
                            "
                        >
                            <div
                                v-if="
                                    isGeneratingReport &&
                                    reportType === 'selected'
                                "
                                class="d-flex align-items-center"
                            >
                                <div
                                    class="spinner-border spinner-border-sm me-2"
                                    role="status"
                                >
                                    <span class="visually-hidden"
                                        >Đang tạo...</span
                                    >
                                </div>
                                Đang tạo báo cáo...
                            </div>
                            <div v-else class="d-flex align-items-center">
                                <i class="fas fa-file-pdf me-2"></i>
                                Tạo báo cáo từ mục đã chọn ({{
                                    selectedItems.length
                                }})
                            </div>
                        </button>

                        <!-- Report All Data Button -->
                        <button
                            @click="generateReportAllData"
                            class="btn btn-danger"
                            :disabled="
                                isGeneratingReport || filteredItems.length === 0
                            "
                        >
                            <div
                                v-if="
                                    isGeneratingReport && reportType === 'all'
                                "
                                class="d-flex align-items-center"
                            >
                                <div
                                    class="spinner-border spinner-border-sm me-2"
                                    role="status"
                                >
                                    <span class="visually-hidden"
                                        >Đang tạo...</span
                                    >
                                </div>
                                Đang tạo báo cáo...
                            </div>
                            <div v-else class="d-flex align-items-center">
                                <i class="fas fa-table me-2"></i>
                                Báo cáo tất cả dữ liệu ({{
                                    filteredItems.length
                                }})
                            </div>
                        </button>
                    </div>

                    <!-- Info Messages -->
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Báo cáo sẽ bao gồm dữ liệu theo bộ lọc hiện tại
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import { useStore } from "../../Store/Auth";
import { Bootstrap5Pagination } from "laravel-vue-pagination";
import "bootstrap/dist/js/bootstrap.bundle.min.js";
import "bootstrap/dist/css/bootstrap.min.css";
import Swal from "sweetalert2";
import PerfectScrollbar from "perfect-scrollbar";
import "perfect-scrollbar/css/perfect-scrollbar.css";

export default {
    components: {
        Bootstrap5Pagination,
    },
    setup() {
        const store = useStore();
        return {
            store,
        };
    },
    data() {
        return {
            isGeneratingReport: false,
            selectedItems: [], // Array to store selected items for report
            reportType: "",
            isLoading: false,
            phieuList: [],
            allPhieuList: [],
            search: "",
            statusFilter: "all",
            isMobile: window.innerWidth < 768,
            currentPage: 1,
            perPage: 50,
            ps: null,
            statusOptions: [
                { code: "all", name: "Tất cả" }, // Keep "All" option as default
            ],
            paginationClasses: {
                ul: "flex list-none pagination",
                li: "page-item mx-1",
                a: "page-link px-3 py-2 bg-white border border-gray-300 rounded hover:bg-gray-100",
                active: "bg-green-500 text-white active",
                disabled: "opacity-50 cursor-not-allowed disabled",
            },
            columnFilters: {
                ma_so_phieu: "",
                ma_so_phieu_phan_bo: "",
                phan_bo_dau_tu: "",
                so_phieu_phan_bo_dau_tu: "",
                invoice_number: "",
                da_tra_goc: "",
                ngay_vay: "",
                ngay_tra: "",
                lai_suat: "",
                tien_lai: "",
                tinh_trang: "",
                tinh_trang_duyet: "",
                da_ho_tro_lai: "",
                phieu_tinh_tien_mia: "",
                created_on: "",
                vu_thanh_toan: "",
                khach_hang: "",
                khach_hang_ca_nhan: "",
                khach_hang_doanh_nghiep: "",
                xoa_no: "",
                vu_dau_tu: "",
                owner: "",
                so_tro_trinh: "",
                category_debt: "",
                description: "",
                ma_khach_hang_ca_nhan: "",
                ma_khach_hang_doanh_nghiep: "",
                ma_giai_ngan: "", // Add this line
            },
            dateRange: {
                startDate: "",
                endDate: "",
                active: false,
            },
            totals: {
                total_paid: 0,
                total_interest: 0,
                total_count: 0,
            },
            showDateFilter: false,
            activeFilter: null,
            selectedFile: null,
            uploadProgress: 0,
            importErrors: [],
            isImporting: false,
            processingRecords: false,
            processingProgress: 0,
            processedRecords: 0,
            totalRecords: 0,
            exportModal: null,
            importModal: null,
            uniqueValues: {
                tinh_trang: [],
                tinh_trang_duyet: [],
                da_ho_tro_lai: [],
                vu_thanh_toan: [],
                vu_dau_tu: [],
                category_debt: [],
                xoa_no: [],
            },
            selectedFilterValues: {
                tinh_trang: [],
                tinh_trang_duyet: [],
                da_ho_tro_lai: [],
                vu_thanh_toan: [],
                vu_dau_tu: [],
                category_debt: [],
                xoa_no: [],
            },
        };
    },
    computed: {
        isAllSelected() {
            return (
                this.paginatedItems.data.length > 0 &&
                this.selectedItems.length === this.paginatedItems.data.length &&
                this.paginatedItems.data.every((item) =>
                    this.selectedItems.some(
                        (selectedItem) => selectedItem.id === item.id
                    )
                )
            );
        },
        statusOptionsWithCount() {
            // กรองรายการตามการค้นหาและฟิลเตอร์คอลัมน์อื่นๆ ทั้งหมด ยกเว้นฟิลเตอร์ category_debt
            const baseFilteredItems = this.phieuList.filter((item) => {
                // Apply global search
                const matchesSearch =
                    (item.ma_so_phieu &&
                        item.ma_so_phieu
                            .toLowerCase()
                            .includes(this.search.toLowerCase())) ||
                    (item.invoice_number &&
                        item.invoice_number
                            .toLowerCase()
                            .includes(this.search.toLowerCase())) ||
                    (item.khach_hang &&
                        item.khach_hang
                            .toLowerCase()
                            .includes(this.search.toLowerCase())) ||
                    (item.khach_hang_ca_nhan &&
                        item.khach_hang_ca_nhan
                            .toLowerCase()
                            .includes(this.search.toLowerCase())) ||
                    (item.khach_hang_doanh_nghiep &&
                        item.khach_hang_doanh_nghiep
                            .toLowerCase()
                            .includes(this.search.toLowerCase())) ||
                    (item.ma_khach_hang_ca_nhan &&
                        item.ma_khach_hang_ca_nhan
                            .toLowerCase()
                            .includes(this.search.toLowerCase())) ||
                    (item.ma_khach_hang_doanh_nghiep &&
                        item.ma_khach_hang_doanh_nghiep
                            .toLowerCase()
                            .includes(this.search.toLowerCase())) ||
                    (item.so_tro_trinh &&
                        item.so_tro_trinh
                            .toLowerCase()
                            .includes(this.search.toLowerCase())) ||
                    (item.ma_giai_ngan && // Add this condition
                        item.ma_giai_ngan
                            .toLowerCase()
                            .includes(this.search.toLowerCase()));

                // Apply all column filters except category_debt (which is our status filter)
                const matchesColumnFilters =
                    // Mã số phiếu
                    (!this.columnFilters.ma_so_phieu ||
                        (item.ma_so_phieu &&
                            item.ma_so_phieu
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.ma_so_phieu.toLowerCase()
                                ))) &&
                    // Mã số phiếu phân bổ
                    (!this.columnFilters.ma_so_phieu_phan_bo ||
                        (item.ma_so_phieu_phan_bo &&
                            item.ma_so_phieu_phan_bo
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.ma_so_phieu_phan_bo.toLowerCase()
                                ))) &&
                    // Phân bổ đầu tư
                    (!this.columnFilters.phan_bo_dau_tu ||
                        (item.phan_bo_dau_tu &&
                            item.phan_bo_dau_tu
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.phan_bo_dau_tu.toLowerCase()
                                ))) &&
                    // Số phiếu phân bổ
                    (!this.columnFilters.so_phieu_phan_bo_dau_tu ||
                        (item.so_phieu_phan_bo_dau_tu &&
                            item.so_phieu_phan_bo_dau_tu
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.so_phieu_phan_bo_dau_tu.toLowerCase()
                                ))) &&
                    // Invoice Number
                    (!this.columnFilters.invoice_number ||
                        (item.invoice_number &&
                            item.invoice_number
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.invoice_number.toLowerCase()
                                ))) &&
                    // Đã trả gốc
                    (!this.columnFilters.da_tra_goc ||
                        (item.da_tra_goc !== undefined &&
                            item.da_tra_goc
                                .toString()
                                .includes(this.columnFilters.da_tra_goc))) &&
                    // Ngày vay
                    (!this.columnFilters.ngay_vay ||
                        (item.ngay_vay &&
                            this.formatDateForComparison(item.ngay_vay) ===
                                this.formatDateForComparison(
                                    this.columnFilters.ngay_vay
                                ))) &&
                    // Ngày trả
                    (!this.columnFilters.ngay_tra ||
                        (item.ngay_tra &&
                            this.formatDateForComparison(item.ngay_tra) ===
                                this.formatDateForComparison(
                                    this.columnFilters.ngay_tra
                                ))) &&
                    // Lãi suất
                    (!this.columnFilters.lai_suat ||
                        (item.lai_suat !== undefined &&
                            item.lai_suat
                                .toString()
                                .includes(this.columnFilters.lai_suat))) &&
                    // Tiền lãi
                    (!this.columnFilters.tien_lai ||
                        (item.tien_lai !== undefined &&
                            item.tien_lai
                                .toString()
                                .includes(this.columnFilters.tien_lai))) &&
                    // Tình trạng (dropdown filter)
                    (this.selectedFilterValues.tinh_trang.length === 0 ||
                        (item.tinh_trang &&
                            this.selectedFilterValues.tinh_trang.includes(
                                item.tinh_trang
                            ))) &&
                    // Tình trạng duyệt (dropdown filter)
                    (this.selectedFilterValues.tinh_trang_duyet.length === 0 ||
                        (item.tinh_trang_duyet &&
                            this.selectedFilterValues.tinh_trang_duyet.includes(
                                item.tinh_trang_duyet
                            ))) &&
                    // Đã hỗ trợ lãi (dropdown filter)
                    (this.selectedFilterValues.da_ho_tro_lai.length === 0 ||
                        (item.da_ho_tro_lai !== undefined &&
                            this.selectedFilterValues.da_ho_tro_lai.includes(
                                item.da_ho_tro_lai
                            ))) &&
                    // Phiếu tính tiền mía
                    (!this.columnFilters.phieu_tinh_tien_mia ||
                        (item.phieu_tinh_tien_mia &&
                            item.phieu_tinh_tien_mia
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.phieu_tinh_tien_mia.toLowerCase()
                                ))) &&
                    // Created On
                    (!this.columnFilters.created_on ||
                        (item.created_on &&
                            this.formatDateForComparison(item.created_on) ===
                                this.formatDateForComparison(
                                    this.columnFilters.created_on
                                ))) &&
                    // Vụ thanh toán (dropdown filter)
                    (this.selectedFilterValues.vu_thanh_toan.length === 0 ||
                        (item.vu_thanh_toan &&
                            this.selectedFilterValues.vu_thanh_toan.includes(
                                item.vu_thanh_toan
                            ))) &&
                    // Khách hàng
                    (!this.columnFilters.khach_hang ||
                        (item.khach_hang &&
                            item.khach_hang
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.khach_hang.toLowerCase()
                                ))) &&
                    // Khách hàng cá nhân
                    (!this.columnFilters.khach_hang_ca_nhan ||
                        (item.khach_hang_ca_nhan &&
                            item.khach_hang_ca_nhan
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.khach_hang_ca_nhan.toLowerCase()
                                ))) &&
                    // Khách hàng doanh nghiệp
                    (!this.columnFilters.khach_hang_doanh_nghiep ||
                        (item.khach_hang_doanh_nghiep &&
                            item.khach_hang_doanh_nghiep
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.khach_hang_doanh_nghiep.toLowerCase()
                                ))) &&
                    // Xóa nợ (dropdown filter)
                    (this.selectedFilterValues.xoa_no.length === 0 ||
                        (item.xoa_no !== undefined &&
                            this.selectedFilterValues.xoa_no.includes(
                                item.xoa_no
                            ))) &&
                    // Vụ đầu tư (dropdown filter)
                    (this.selectedFilterValues.vu_dau_tu.length === 0 ||
                        (item.vu_dau_tu &&
                            this.selectedFilterValues.vu_dau_tu.includes(
                                item.vu_dau_tu
                            ))) &&
                    // Owner
                    (!this.columnFilters.owner ||
                        (item.owner &&
                            item.owner
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.owner.toLowerCase()
                                ))) &&
                    // Số trờ trình
                    (!this.columnFilters.so_tro_trinh ||
                        (item.so_tro_trinh &&
                            item.so_tro_trinh
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.so_tro_trinh.toLowerCase()
                                ))) &&
                    // Description
                    (!this.columnFilters.description ||
                        (item.description &&
                            item.description
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.description.toLowerCase()
                                ))) &&
                    // Mã khách hàng cá nhân
                    (!this.columnFilters.ma_khach_hang_ca_nhan ||
                        (item.ma_khach_hang_ca_nhan &&
                            item.ma_khach_hang_ca_nhan
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.ma_khach_hang_ca_nhan.toLowerCase()
                                ))) &&
                    // Mã khách hàng doanh nghiệp
                    (!this.columnFilters.ma_khach_hang_doanh_nghiep ||
                        (item.ma_khach_hang_doanh_nghiep &&
                            item.ma_khach_hang_doanh_nghiep
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.ma_khach_hang_doanh_nghiep.toLowerCase()
                                )));

                // Return true if item matches search and all other column filters (except category_debt)
                return matchesSearch && matchesColumnFilters;
            });

            // For "all" option, count is the total number of filtered items
            const all = {
                code: "all",
                name: "Tất cả",
                count: baseFilteredItems.length,
            };

            // For other options, count the matching items for each category
            const options = this.statusOptions
                .filter((option) => option.code !== "all")
                .map((option) => {
                    const count = baseFilteredItems.filter(
                        (item) => item.category_debt === option.code
                    ).length;

                    return {
                        ...option,
                        count: count,
                    };
                });

            // Return combined array with "all" as first option
            return [all, ...options];
        },
        filteredItems() {
            return this.phieuList.filter((item) => {
                // Apply global search
                const matchesSearch =
                    (item.ma_so_phieu &&
                        item.ma_so_phieu
                            .toLowerCase()
                            .includes(this.search.toLowerCase())) ||
                    (item.invoice_number &&
                        item.invoice_number
                            .toLowerCase()
                            .includes(this.search.toLowerCase())) ||
                    (item.khach_hang &&
                        item.khach_hang
                            .toLowerCase()
                            .includes(this.search.toLowerCase())) ||
                    (item.khach_hang_ca_nhan &&
                        item.khach_hang_ca_nhan
                            .toLowerCase()
                            .includes(this.search.toLowerCase())) ||
                    (item.khach_hang_doanh_nghiep &&
                        item.khach_hang_doanh_nghiep
                            .toLowerCase()
                            .includes(this.search.toLowerCase())) ||
                    (item.ma_khach_hang_ca_nhan &&
                        item.ma_khach_hang_ca_nhan
                            .toLowerCase()
                            .includes(this.search.toLowerCase())) ||
                    (item.ma_khach_hang_doanh_nghiep &&
                        item.ma_khach_hang_doanh_nghiep
                            .toLowerCase()
                            .includes(this.search.toLowerCase())) ||
                    (item.so_tro_trinh &&
                        item.so_tro_trinh
                            .toLowerCase()
                            .includes(this.search.toLowerCase())) ||
                    (item.ma_giai_ngan && // Add this condition
                        item.ma_giai_ngan
                            .toLowerCase()
                            .includes(this.search.toLowerCase()));

                // Apply date range filter for ngay_tra
                const passesDateRangeFilter =
                    !this.dateRange.active ||
                    (item.ngay_tra &&
                        new Date(this.formatDateForComparison(item.ngay_tra)) >=
                            new Date(this.dateRange.startDate) &&
                        new Date(this.formatDateForComparison(item.ngay_tra)) <=
                            new Date(this.dateRange.endDate));

                // Apply Vụ đầu tư filter
                const matchesVuDauTu =
                    !this.selectedFilterValues.vu_dau_tu ||
                    item.vu_dau_tu === this.selectedFilterValues.vu_dau_tu;

                // Apply status filter - now filtering by category_debt instead of tinh_trang
                let matchesStatus = true;
                if (this.statusFilter !== "all") {
                    matchesStatus = item.category_debt === this.statusFilter;
                }

                // Apply column filters
                const matchesColumnFilters =
                    // Mã số phiếu
                    (!this.columnFilters.ma_so_phieu ||
                        (item.ma_so_phieu &&
                            item.ma_so_phieu
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.ma_so_phieu.toLowerCase()
                                ))) &&
                    // Mã số phiếu phân bổ
                    (!this.columnFilters.ma_so_phieu_phan_bo ||
                        (item.ma_so_phieu_phan_bo &&
                            item.ma_so_phieu_phan_bo
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.ma_so_phieu_phan_bo.toLowerCase()
                                ))) &&
                    // Phân bổ đầu tư
                    (!this.columnFilters.phan_bo_dau_tu ||
                        (item.phan_bo_dau_tu &&
                            item.phan_bo_dau_tu
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.phan_bo_dau_tu.toLowerCase()
                                ))) &&
                    // Số phiếu phân bổ
                    (!this.columnFilters.so_phieu_phan_bo_dau_tu ||
                        (item.so_phieu_phan_bo_dau_tu &&
                            item.so_phieu_phan_bo_dau_tu
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.so_phieu_phan_bo_dau_tu.toLowerCase()
                                ))) &&
                    // Invoice Number
                    (!this.columnFilters.invoice_number ||
                        (item.invoice_number &&
                            item.invoice_number
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.invoice_number.toLowerCase()
                                ))) &&
                    // Đã trả gốc
                    (!this.columnFilters.da_tra_goc ||
                        (item.da_tra_goc !== undefined &&
                            item.da_tra_goc
                                .toString()
                                .includes(this.columnFilters.da_tra_goc))) &&
                    // Ngày vay
                    (!this.columnFilters.ngay_vay ||
                        (item.ngay_vay &&
                            this.formatDateForComparison(item.ngay_vay) ===
                                this.formatDateForComparison(
                                    this.columnFilters.ngay_vay
                                ))) &&
                    // Ngày trả
                    (!this.columnFilters.ngay_tra ||
                        (item.ngay_tra &&
                            this.formatDateForComparison(item.ngay_tra) ===
                                this.formatDateForComparison(
                                    this.columnFilters.ngay_tra
                                ))) &&
                    // Lãi suất
                    (!this.columnFilters.lai_suat ||
                        (item.lai_suat !== undefined &&
                            item.lai_suat
                                .toString()
                                .includes(this.columnFilters.lai_suat))) &&
                    // Tiền lãi
                    (!this.columnFilters.tien_lai ||
                        (item.tien_lai !== undefined &&
                            item.tien_lai
                                .toString()
                                .includes(this.columnFilters.tien_lai))) &&
                    // Mã giải ngân
                    (!this.columnFilters.ma_giai_ngan ||
                        (item.ma_giai_ngan &&
                            item.ma_giai_ngan
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.ma_giai_ngan.toLowerCase()
                                ))) &&
                    // Tình trạng (dropdown filter)
                    (this.selectedFilterValues.tinh_trang.length === 0 ||
                        (item.tinh_trang &&
                            this.selectedFilterValues.tinh_trang.includes(
                                item.tinh_trang
                            ))) &&
                    // Tình trạng duyệt (dropdown filter)
                    (this.selectedFilterValues.tinh_trang_duyet.length === 0 ||
                        (item.tinh_trang_duyet &&
                            this.selectedFilterValues.tinh_trang_duyet.includes(
                                item.tinh_trang_duyet
                            ))) &&
                    // Đã hỗ trợ lãi (dropdown filter)
                    (this.selectedFilterValues.da_ho_tro_lai.length === 0 ||
                        (item.da_ho_tro_lai !== undefined &&
                            this.selectedFilterValues.da_ho_tro_lai.includes(
                                item.da_ho_tro_lai
                            ))) &&
                    // Phiếu tính tiền mía
                    (!this.columnFilters.phieu_tinh_tien_mia ||
                        (item.phieu_tinh_tien_mia &&
                            item.phieu_tinh_tien_mia
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.phieu_tinh_tien_mia.toLowerCase()
                                ))) &&
                    // Created On
                    (!this.columnFilters.created_on ||
                        (item.created_on &&
                            this.formatDateForComparison(item.created_on) ===
                                this.formatDateForComparison(
                                    this.columnFilters.created_on
                                ))) &&
                    // Vụ thanh toán (dropdown filter)
                    (this.selectedFilterValues.vu_thanh_toan.length === 0 ||
                        (item.vu_thanh_toan &&
                            this.selectedFilterValues.vu_thanh_toan.includes(
                                item.vu_thanh_toan
                            ))) &&
                    // Khách hàng
                    (!this.columnFilters.khach_hang ||
                        (item.khach_hang &&
                            item.khach_hang
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.khach_hang.toLowerCase()
                                ))) &&
                    // Khách hàng cá nhân
                    (!this.columnFilters.khach_hang_ca_nhan ||
                        (item.khach_hang_ca_nhan &&
                            item.khach_hang_ca_nhan
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.khach_hang_ca_nhan.toLowerCase()
                                ))) &&
                    // Khách hàng doanh nghiệp
                    (!this.columnFilters.khach_hang_doanh_nghiep ||
                        (item.khach_hang_doanh_nghiep &&
                            item.khach_hang_doanh_nghiep
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.khach_hang_doanh_nghiep.toLowerCase()
                                ))) &&
                    // Xóa nợ (dropdown filter)
                    (this.selectedFilterValues.xoa_no.length === 0 ||
                        (item.xoa_no !== undefined &&
                            this.selectedFilterValues.xoa_no.includes(
                                item.xoa_no
                            ))) &&
                    // Vụ đầu tư (dropdown filter)
                    (this.selectedFilterValues.vu_dau_tu.length === 0 ||
                        (item.vu_dau_tu &&
                            this.selectedFilterValues.vu_dau_tu.includes(
                                item.vu_dau_tu
                            ))) &&
                    // Owner
                    (!this.columnFilters.owner ||
                        (item.owner &&
                            item.owner
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.owner.toLowerCase()
                                ))) &&
                    // Số trờ trình
                    (!this.columnFilters.so_tro_trinh ||
                        (item.so_tro_trinh &&
                            item.so_tro_trinh
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.so_tro_trinh.toLowerCase()
                                ))) &&
                    // Category Debt (dropdown filter)
                    (this.selectedFilterValues.category_debt.length === 0 ||
                        (item.category_debt &&
                            this.selectedFilterValues.category_debt.includes(
                                item.category_debt
                            ))) &&
                    // Description
                    (!this.columnFilters.description ||
                        (item.description &&
                            item.description
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.description.toLowerCase()
                                ))) &&
                    // Mã khách hàng cá nhân
                    (!this.columnFilters.ma_khach_hang_ca_nhan ||
                        (item.ma_khach_hang_ca_nhan &&
                            item.ma_khach_hang_ca_nhan
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.ma_khach_hang_ca_nhan.toLowerCase()
                                ))) &&
                    // Mã khách hàng doanh nghiệp
                    (!this.columnFilters.ma_khach_hang_doanh_nghiep ||
                        (item.ma_khach_hang_doanh_nghiep &&
                            item.ma_khach_hang_doanh_nghiep
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.ma_khach_hang_doanh_nghiep.toLowerCase()
                                )));

                return (
                    matchesSearch &&
                    matchesStatus &&
                    matchesColumnFilters &&
                    passesDateRangeFilter
                );
            });
        },
        paginatedItems() {
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
        /**
         * Apply filters and update pagination after data is loaded
         */
        applyFiltersAndPagination(page) {
            // Update current page
            this.currentPage = page || 1;

            // If any filter was previously set, it will be automatically applied
            // through the computed properties (filteredItems, paginatedItems)

            // Recalculate totals based on filtered items
            this.calculateTotals();

            // Update the PerfectScrollbar if it exists
            this.$nextTick(() => {
                if (this.ps) {
                    this.ps.update();
                }
            });
        },
        // Add to methods section
        extractUniqueValues() {
            // Reset uniqueValues
            this.uniqueValues = {
                tinh_trang: [],
                tinh_trang_duyet: [],
                da_ho_tro_lai: [],
                vu_thanh_toan: [],
                vu_dau_tu: [],
                category_debt: [],
                xoa_no: [],
            };

            // Populate all uniqueValues fields at once
            if (this.phieuList && this.phieuList.length > 0) {
                // Extract Vụ đầu tư values
                this.uniqueValues.vu_dau_tu = [
                    ...new Set(
                        this.phieuList
                            .map((item) => item.vu_dau_tu)
                            .filter(Boolean) // Remove null/undefined values
                    ),
                ].sort();

                // Extract other unique values
                this.uniqueValues.tinh_trang = [
                    ...new Set(
                        this.phieuList
                            .map((item) => item.tinh_trang)
                            .filter(Boolean)
                    ),
                ].sort();

                this.uniqueValues.vu_thanh_toan = [
                    ...new Set(
                        this.phieuList
                            .map((item) => item.vu_thanh_toan)
                            .filter(Boolean)
                    ),
                ].sort();

                this.uniqueValues.category_debt = [
                    ...new Set(
                        this.phieuList
                            .map((item) => item.category_debt)
                            .filter(Boolean)
                    ),
                ].sort();

                // Continue for other fields...
            }
        },
        extractCategoryOptions() {
            // Get unique category_debt values
            const uniqueCategories = [
                ...new Set(
                    this.phieuList
                        .map((item) => item.category_debt)
                        .filter(Boolean) // Remove null/undefined
                ),
            ];

            // Clear existing options except "all"
            this.statusOptions = [{ code: "all", name: "Tất cả" }];

            // Add each unique category as an option
            uniqueCategories.forEach((category) => {
                this.statusOptions.push({
                    code: category,
                    name: category,
                });
            });
        },
        /**
         * Toggle Select All Checkbox
         */
        toggleSelectAll(event) {
            if (event.target.checked) {
                // Select all items on current page
                this.paginatedItems.data.forEach((item) => {
                    if (!this.isItemSelected(item)) {
                        this.selectedItems.push(item);
                    }
                });
            } else {
                // Deselect all items on current page
                this.paginatedItems.data.forEach((item) => {
                    const index = this.selectedItems.findIndex(
                        (selectedItem) => selectedItem.id === item.id
                    );
                    if (index > -1) {
                        this.selectedItems.splice(index, 1);
                    }
                });
            }
        },

        /**
         * Clear all selections
         */
        clearSelection() {
            this.selectedItems = [];
        },

        /**
         * Check if item is selected
         */
        isItemSelected(item) {
            return this.selectedItems.some(
                (selectedItem) => selectedItem.id === item.id
            );
        },

        /**
         * Toggle single item selection
         */
        toggleItemSelection(item) {
            const index = this.selectedItems.findIndex(
                (selectedItem) => selectedItem.id === item.id
            );

            if (index > -1) {
                // Item is selected, remove it
                this.selectedItems.splice(index, 1);
            } else {
                // Item is not selected, add it
                this.selectedItems.push(item);
            }
        },

        /**
         * Show Report Modal
         */
        showReportModal() {
            // Show the report modal using Bootstrap
            const modalElement = document.getElementById("reportPdfModal");
            if (modalElement) {
                // Try jQuery first
                if (window.$ && window.$.fn.modal) {
                    window.$("#reportPdfModal").modal("show");
                } else {
                    // Try Bootstrap 5 Modal API
                    try {
                        import(
                            "bootstrap/dist/js/bootstrap.bundle.min.js"
                        ).then((bootstrap) => {
                            const modal = new bootstrap.Modal(modalElement);
                            modal.show();
                        });
                    } catch (error) {
                        console.error("Error showing modal:", error);
                    }
                }
            }
        },

        /**
         * Close Report Modal
         */
        closeReportModal() {
            const modalElement = document.getElementById("reportPdfModal");
            if (modalElement) {
                // Try jQuery first
                if (window.$ && window.$.fn.modal) {
                    window.$("#reportPdfModal").modal("hide");
                } else {
                    // Try Bootstrap 5 Modal API
                    try {
                        const bsModal =
                            bootstrap.Modal.getInstance(modalElement);
                        if (bsModal) {
                            bsModal.hide();
                        }
                    } catch (error) {
                        console.error("Error hiding modal:", error);
                    }
                }
            }
            this.isGeneratingReport = false;
            this.reportType = "";
        },

        /**
         * Generate Report for Selected Items
         */
        async generateReportSelectedItems() {
            try {
                this.isGeneratingReport = true;
                this.reportType = "selected";

                // Get selected items for report
                const selectedIds = this.selectedItems.map(
                    (item) => item.ma_so_phieu
                );

                if (selectedIds.length === 0) {
                    Swal.fire({
                        icon: "warning",
                        title: "Chưa chọn mục nào",
                        text: "Vui lòng chọn ít nhất một mục để tạo báo cáo",
                        confirmButtonText: "Đồng ý",
                    });
                    return;
                }

                await this.generateReport(
                    selectedIds,
                    "selected_items",
                    selectedIds.length
                );
            } catch (error) {
                console.error("Error generating selected items report:", error);
                this.handleReportError(error);
            } finally {
                this.isGeneratingReport = false;
                this.reportType = "";
            }
        },

        /**
         * Generate Report for All Data
         */
        async generateReportAllData() {
            try {
                this.isGeneratingReport = true;
                this.reportType = "all";

                if (this.filteredItems.length === 0) {
                    Swal.fire({
                        icon: "warning",
                        title: "Không có dữ liệu",
                        text: "Không có dữ liệu để tạo báo cáo với bộ lọc hiện tại",
                        confirmButtonText: "Đồng ý",
                    });
                    return;
                }

                // Show confirmation for large datasets
                if (this.filteredItems.length > 100) {
                    const result = await Swal.fire({
                        icon: "question",
                        title: "Xác nhận tạo báo cáo",
                        text: `Bạn sắp tạo báo cáo cho ${this.filteredItems.length} bản ghi. Điều này có thể mất một chút thời gian. Bạn có muốn tiếp tục?`,
                        showCancelButton: true,
                        confirmButtonText: "Tiếp tục",
                        cancelButtonText: "Hủy",
                    });

                    if (!result.isConfirmed) {
                        return;
                    }
                }

                // Get all filtered items
                const allIds = this.filteredItems.map(
                    (item) => item.ma_so_phieu
                );

                await this.generateReport(allIds, "all_data", allIds.length);
            } catch (error) {
                console.error("Error generating all data report:", error);
                this.handleReportError(error);
            } finally {
                this.isGeneratingReport = false;
                this.reportType = "";
            }
        },

        /**
         * Generate Report - Main function
         */
        async generateReport(recordIds, reportType, totalCount) {
            // Prepare filter parameters
            const filterParams = {
                record_ids: recordIds, // ใช้ record_ids แทน invoice_numbers
                report_type: reportType,
                generated_by: this.store.user?.full_name || "Hệ thống",
                generated_date: new Date().toISOString(),
                total_records: totalCount,
                // Include current filters for context
                current_filters: {
                    search: this.search || "",
                    status_filter: this.statusFilter || "all",
                    selected_filter_values: this.selectedFilterValues,
                },
            };

            // Create form data
            const formData = new FormData();
            formData.append("filter_params", JSON.stringify(filterParams));

            // Make API request to generate report
            const headers = this.store.getAuthHeaders();
            const response = await axios.post(
                "/api/generate-report-table-phieuthuno", // ใช้ endpoint ใหม่
                formData,
                { headers: headers }
            );

            if (response.status === 200) {
                // Open the HTML report in a new window
                const newWindow = window.open("", "_blank");
                if (newWindow) {
                    newWindow.document.write(response.data);
                    newWindow.document.close();
                    newWindow.focus();
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Không thể mở cửa sổ mới",
                        text: "Vui lòng cho phép popup trong trình duyệt và thử lại",
                        confirmButtonText: "Đồng ý",
                    });
                }

                // Close modal after successful generation
                this.closeReportModal();
            }
        },

        /**
         * Handle Report Generation Errors
         */
        handleReportError(error) {
            let errorMessage = "Đã xảy ra lỗi khi tạo báo cáo";

            if (error.response) {
                if (error.response.status === 401) {
                    this.handleAuthError();
                    return;
                } else if (error.response.data?.message) {
                    errorMessage = error.response.data.message;
                }
            } else if (error.message) {
                errorMessage = error.message;
            }

            Swal.fire({
                icon: "error",
                title: "Lỗi tạo báo cáo",
                text: errorMessage,
                confirmButtonText: "Đồng ý",
            });
        },
        toggleDateFilter() {
            // Instead of toggling a local element, show a modal dialog
            Swal.fire({
                title: '<i class="fas fa-calendar-alt text-success me-2"></i> Lọc theo ngày trả',
                html: `
            <div class="date-modal-content">
                <div class="date-input-groups mb-3">
                    <div class="date-input-group mb-3">
                        <label class="date-label text-start d-block mb-1">Từ ngày</label>
                        <input
                            type="date"
                            id="swal-date-from"
                            class="form-control date-input"
                            value="${this.dateRange.startDate}"
                        />
                    </div>
                    <div class="date-input-group">
                        <label class="date-label text-start d-block mb-1">Đến ngày</label>
                        <input
                            type="date"
                            id="swal-date-to"
                            class="form-control date-input"
                            value="${this.dateRange.endDate}"
                        />
                    </div>
                </div>
                <div class="invalid-feedback" id="date-error" style="display: none;">
                    Ngày bắt đầu phải nhỏ hơn hoặc bằng ngày kết thúc
                </div>
            </div>
        `,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-check me-1"></i> Áp dụng',
                cancelButtonText: '<i class="fas fa-times me-1"></i> Hủy',
                customClass: {
                    container: "date-filter-modal",
                    popup: "date-filter-popup",
                    confirmButton: "btn btn-success",
                    cancelButton: "btn btn-outline-secondary",
                    actions: "swal2-actions-custom-gap",
                },
                buttonsStyling: false,
                showCloseButton: true,
                preConfirm: () => {
                    const startDate =
                        document.getElementById("swal-date-from").value;
                    const endDate =
                        document.getElementById("swal-date-to").value;

                    // Validate dates
                    if (
                        startDate &&
                        endDate &&
                        new Date(startDate) > new Date(endDate)
                    ) {
                        document.getElementById("date-error").style.display =
                            "block";
                        return false;
                    }

                    return {
                        startDate: startDate,
                        endDate: endDate,
                    };
                },
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    // Update the date range and apply filter
                    this.dateRange.startDate = result.value.startDate;
                    this.dateRange.endDate = result.value.endDate;

                    if (this.dateRange.startDate && this.dateRange.endDate) {
                        this.dateRange.active = true;
                        this.currentPage = 1;
                    } else {
                        // If both dates are empty, reset the filter
                        this.resetDateRange();
                    }
                }
            });
        },
        // Apply date range filter for ngay_tra
        applyDateRangeFilter() {
            if (this.dateRange.startDate && this.dateRange.endDate) {
                // Check if start date is before end date
                if (
                    new Date(this.dateRange.startDate) >
                    new Date(this.dateRange.endDate)
                ) {
                    // Swap dates if start date is later than end date
                    const temp = this.dateRange.startDate;
                    this.dateRange.startDate = this.dateRange.endDate;
                    this.dateRange.endDate = temp;
                }

                this.dateRange.active = true;
                this.currentPage = 1;

                // Optionally hide the date filter panel after applying
                // this.showDateFilter = false;
            }
        },

        // Reset date range filter
        resetDateRange() {
            this.dateRange.startDate = "";
            this.dateRange.endDate = "";
            this.dateRange.active = false;
            this.currentPage = 1;

            // Show success toast
            const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });

            Toast.fire({
                icon: "success",
                title: "Đã xóa bộ lọc ngày trả",
            });
        },
        getActiveDateFilterText() {
            if (!this.dateRange.active) return "";

            const formatDisplayDate = (dateStr) => {
                if (!dateStr) return "";
                const date = new Date(dateStr);
                return date.toLocaleDateString("vi-VN");
            };

            return `${formatDisplayDate(
                this.dateRange.startDate
            )} - ${formatDisplayDate(this.dateRange.endDate)}`;
        },
        // Add this method to the methods section of your component
        getCategoryBadgeClass(category) {
            if (!category) return "badge bg-secondary text-white";

            // Map different categories to different colors and icons
            const categoryColors = {
                "Ứng dầu": {
                    class: "badge bg-blue-600 text-white",
                    icon: "fas fa-oil-can",
                },
                MMTB: {
                    class: "badge bg-red-600 text-white",
                    icon: "fas fa-tools",
                },
                "Sữa chữa": {
                    class: "badge bg-purple-600 text-white",
                    icon: "fas fa-wrench",
                },
                "Ứng tiền trồng": {
                    class: "badge bg-amber-600 text-white",
                    icon: "fas fa-seedling",
                },
                "Ứng tiền chăm sóc": {
                    class: "badge bg-green-600 text-white",
                    icon: "fas fa-hand-holding-heart",
                },
            };

            // Return just the class if category doesn't exist in our mapping
            if (!categoryColors[category]) {
                return "badge bg-secondary text-white";
            }

            // Return the badge class and icon info
            return categoryColors[category];
        },
        showActionsMenu() {
            Swal.fire({
                title: "Actions",
                html: `
        <div class="d-grid gap-2">
            <button onclick="exportToExcel()" class="btn btn-outline-success mb-2">
                <i class="fas fa-file-excel text-success me-2"></i> Export to Excel
            </button>
            <button onclick="showReportModal()" class="btn btn-outline-danger mb-2">
                <i class="fas fa-file-pdf text-danger me-2"></i> Report Data
            </button>
            <button onclick="importData()" class="btn btn-outline-primary mb-2">
                <i class="fas fa-upload text-primary me-2"></i> Import Data
            </button>
            <button onclick="downloadTemplate()" class="btn btn-outline-info mb-2">
                <i class="fas fa-file-download text-info me-2"></i> Download Template
            </button>
        </div>
        `,
                showConfirmButton: false,
                showCloseButton: true,
                didOpen: () => {
                    // Define global functions for the buttons
                    window.exportToExcel = () => {
                        Swal.close();
                        this.showExportModal();
                    };
                    window.showReportModal = () => {
                        Swal.close();
                        this.showReportModal();
                    };
                    window.importData = () => {
                        Swal.close();
                        this.importData();
                    };
                    window.downloadTemplate = () => {
                        Swal.close();
                        this.downloadImportTemplate();
                    };
                },
                willClose: () => {
                    // Clean up global functions
                    delete window.exportToExcel;
                    delete window.showReportModal;
                    delete window.importData;
                    delete window.downloadTemplate;
                },
            });
        },
        calculateTotals() {
            const filteredData = this.filteredItems;

            this.totals = {
                total_paid: filteredData.reduce(
                    (sum, item) => sum + (parseFloat(item.da_tra_goc) || 0),
                    0
                ),
                total_interest: filteredData.reduce(
                    (sum, item) => sum + (parseFloat(item.tien_lai) || 0),
                    0
                ),
                total_count: filteredData.length,
            };
        },
        formatDate(date) {
            if (!date) return "";
            const d = new Date(date);
            const day = d.getDate().toString().padStart(2, "0");
            const month = (d.getMonth() + 1).toString().padStart(2, "0");
            const year = d.getFullYear();
            return `${day}/${month}/${year}`;
        },
        formatDateForComparison(date) {
            if (!date) return "";
            const d = new Date(date);
            const year = d.getFullYear();
            const month = (d.getMonth() + 1).toString().padStart(2, "0");
            const day = d.getDate().toString().padStart(2, "0");
            return `${year}-${month}-${day}`;
        },
        formatCurrency(value) {
            if (!value) return "0";
            return new Intl.NumberFormat("vi-VN", {
                style: "currency",
                currency: "KIP",
                maximumFractionDigits: 0,
            }).format(value);
        },
        formatStatus(status) {
            if (status === undefined || status === null) return "";

            const statusMap = {
                active: "Đang nợ",
                paid: "Đã thanh toán",
                overdue: "Quá hạn",
                cancelled: "Đã hủy",
                approved: "Đã duyệt",
                pending: "Chờ duyệt",
                rejected: "Từ chối",
            };

            return statusMap[status] || status;
        },
        statusClass(status) {
            if (status === undefined || status === null) return "";

            switch (status) {
                case "active":
                case "pending":
                    return "text-yellow-600";
                case "paid":
                case "approved":
                    return "text-green-500";
                case "overdue":
                case "rejected":
                    return "text-red-500";
                case "cancelled":
                    return "text-purple-500";
                default:
                    return "text-blue-600";
            }
        },
        statusIcons(status) {
            if (status === undefined || status === null) return "";

            switch (status) {
                case "active":
                    return "fas fa-spinner";
                case "paid":
                    return "fas fa-check-circle";
                case "overdue":
                    return "fas fa-exclamation-circle";
                case "cancelled":
                    return "fas fa-times-circle";
                case "pending":
                    return "fas fa-clock";
                case "approved":
                    return "fas fa-check-circle";
                case "rejected":
                    return "fas fa-times-circle";
                default:
                    return "fas fa-info-circle";
            }
        },
        pageChanged(page) {
            this.currentPage = page;
        },
        async fetchData(page = 1, initialFetch = false, callback = null) {
            this.isLoading = true;
            try {
                // Only request all data on initial fetch
                const params = initialFetch ? { all: true } : { page: page };

                // Use the store's getAuthHeaders method
                const headers = this.store.getAuthHeaders();

                const response = await axios.get("/api/phieu-thu-no-dich-vu", {
                    params: params,
                    headers: headers, // ใช้ headers จาก store ที่มี JWT token และ user info
                });

                // Process successful response
                const responseData = response.data;

                if (responseData && responseData.success) {
                    if (initialFetch) {
                        // Store all data for client-side filtering
                        this.allPhieuList = responseData.data || [];
                        this.phieuList = [...this.allPhieuList];
                        this.dataInitialized = true;
                        // Add this line right after:
                        this.extractCategoryOptions();
                        // Extract unique values after data is loaded
                        this.extractUniqueValues();

                        // Apply any restored filters
                        this.applyFiltersAndPagination(this.currentPage);

                        // Execute callback if provided
                        if (callback) {
                            callback();
                        }
                    } else {
                        // Handle paginated response if needed
                        this.phieuList = responseData.data || [];
                    }
                } else {
                    console.error("API response error:", responseData);
                    throw new Error(
                        responseData.message || "Unknown error occurred"
                    );
                }
            } catch (error) {
                console.error("Error fetching data:", error);
                if (error.response?.status === 401) {
                    this.handleAuthError();
                } else {
                    // Show user-friendly error message
                    Swal.fire({
                        icon: "error",
                        title: "Lỗi tải dữ liệu",
                        text: "Không thể tải dữ liệu. Vui lòng thử lại sau.",
                        confirmButtonText: "Đồng ý",
                    });
                }
            } finally {
                this.isLoading = false;
            }
        },
        resetAllFilters() {
            // Also reset date range
            this.dateRange.startDate = "";
            this.dateRange.endDate = "";
            this.dateRange.active = false;
            this.showDateFilter = false;
            // Reset global search
            this.search = "";

            // Reset status filter
            this.statusFilter = "all";

            // Reset all column filters
            for (const key in this.columnFilters) {
                this.columnFilters[key] = "";
            }

            // Reset dropdown filters
            for (const key in this.selectedFilterValues) {
                this.selectedFilterValues[key] = [];
            }

            // Reset active filter (close any open filter dropdown)
            this.activeFilter = null;

            // Reset to first page
            this.currentPage = 1;
        },

        handleAuthError() {
            localStorage.removeItem("web_token");
            localStorage.removeItem("web_user");
            this.store.logout();
            this.$router.push("/login");
        },
        // Column filter methods
        toggleFilter(column) {
            this.activeFilter = this.activeFilter === column ? null : column;

            // If opening filter for dropdown columns, ensure unique values are populated
            if (
                [
                    "tinh_trang",
                    "tinh_trang_duyet",
                    "da_ho_tro_lai",
                    "vu_thanh_toan",
                    "vu_dau_tu",
                    "category_debt",
                    "xoa_no",
                ].includes(column) &&
                this.activeFilter === column
            ) {
                this.updateUniqueValues(column);
            }
        },
        updateUniqueValues(column) {
            if (
                [
                    "tinh_trang",
                    "tinh_trang_duyet",
                    "da_ho_tro_lai",
                    "vu_thanh_toan",
                    "vu_dau_tu",
                    "category_debt",
                    "xoa_no",
                ].includes(column)
            ) {
                this.uniqueValues[column] = [
                    ...new Set(
                        this.phieuList
                            .map((item) => item[column])
                            .filter(Boolean) // Remove null/undefined values
                    ),
                ];
            }
        },
        resetFilter(column) {
            if (
                [
                    "tinh_trang",
                    "tinh_trang_duyet",
                    "da_ho_tro_lai",
                    "vu_thanh_toan",
                    "vu_dau_tu",
                    "category_debt",
                    "xoa_no",
                ].includes(column)
            ) {
                this.selectedFilterValues[column] = [];
            } else {
                this.columnFilters[column] = "";
            }
            this.currentPage = 1;
        },
        applyFilter(column) {
            this.activeFilter = null;
            this.currentPage = 1;
        },
        // Export methods
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
            if (
                this.paginatedItems.data &&
                this.paginatedItems.data.length > 0
            ) {
                this.generateExcel(
                    this.paginatedItems.data,
                    "phieu_thu_no_dich_vu_current_page"
                );
                this.closeExportModal();
            } else {
                alert("Không có dữ liệu để xuất");
            }
        },
        exportToExcelAllPages() {
            if (this.filteredItems && this.filteredItems.length > 0) {
                this.generateExcel(
                    this.filteredItems,
                    "phieu_thu_no_dich_vu_all_data"
                );
                this.closeExportModal();
            } else {
                alert("Không có dữ liệu để xuất");
            }
        },
        generateExcel(data, filename) {
            try {
                // Import xlsx and FileSaver dynamically
                import("xlsx").then((XLSX) => {
                    import("file-saver").then((module) => {
                        const FileSaver = module.default;

                        if (!data || data.length === 0) {
                            alert("Không có dữ liệu để xuất");
                            return;
                        }

                        // Format data for export
                        const exportData = data.map((item) => ({
                            "Mã số phiếu": item.ma_so_phieu || "",
                            "Mã PB đầu tư": item.ma_so_phieu_phan_bo || "",
                            "Phân bổ đầu tư": item.phan_bo_dau_tu || "",
                            "Số phiếu PB đầu tư":
                                item.so_phieu_phan_bo_dau_tu || "",
                            "Mã giải ngân": item.ma_giai_ngan || "", // Add this line
                            "Invoice Number": item.invoice_number || "",
                            "Đã trả gốc": item.da_tra_goc || 0,
                            "Ngày vay": item.ngay_vay
                                ? new Date(item.ngay_vay).toLocaleDateString(
                                      "vi-VN"
                                  )
                                : "",
                            "Ngày trả": item.ngay_tra
                                ? new Date(item.ngay_tra).toLocaleDateString(
                                      "vi-VN"
                                  )
                                : "",
                            "Lãi suất": item.lai_suat || 0,
                            "Tiền lãi": item.tien_lai || 0,
                            "Tình trạng":
                                this.formatStatus(item.tinh_trang) || "",
                            "Tình trạng duyệt":
                                this.formatStatus(item.tinh_trang_duyet) || "",
                            "Đã hỗ trợ lãi": item.da_ho_tro_lai
                                ? "Có"
                                : "Không",
                            "Phiếu tính tiền mía":
                                item.phieu_tinh_tien_mia || "",
                            "Created On": item.created_on
                                ? new Date(item.created_on).toLocaleDateString(
                                      "vi-VN"
                                  )
                                : "",
                            "Vụ thanh toán": item.vu_thanh_toan || "",
                            "Khách hàng": item.khach_hang || "",
                            "Khách hàng cá nhân": item.khach_hang_ca_nhan || "",
                            "Khách hàng doanh nghiệp":
                                item.khach_hang_doanh_nghiep || "",
                            "Xóa nợ": item.xoa_no ? "Có" : "Không",
                            "Vụ đầu tư": item.vu_dau_tu || "",
                            Owner: item.owner || "",
                            "Số trờ trình": item.so_tro_trinh || "",
                            "Category Debt": item.category_debt || "",
                            Description: item.description || "",
                            "Mã KH cá nhân": item.ma_khach_hang_ca_nhan || "",
                            "Mã KH doanh nghiệp":
                                item.ma_khach_hang_doanh_nghiep || "",
                        }));

                        // Create a worksheet
                        const worksheet = XLSX.utils.json_to_sheet(exportData);

                        // Set column widths
                        const columnWidths = [
                            { wch: 15 }, // Mã số phiếu
                            { wch: 15 }, // Mã PB đầu tư
                            { wch: 20 }, // Phân bổ đầu tư
                            { wch: 20 }, // Số phiếu PB đầu tư
                            { wch: 15 }, // Mã giải ngân
                            { wch: 15 }, // Invoice Number
                            { wch: 15 }, // Đã trả gốc
                            { wch: 15 }, // Ngày vay
                            { wch: 15 }, // Ngày trả
                            { wch: 10 }, // Lãi suất
                            { wch: 15 }, // Tiền lãi
                            { wch: 15 }, // Tình trạng
                            { wch: 15 }, // Tình trạng duyệt
                            { wch: 15 }, // Đã hỗ trợ lãi
                            { wch: 20 }, // Phiếu tính tiền mía
                            { wch: 15 }, // Created On
                            { wch: 15 }, // Vụ thanh toán
                            { wch: 20 }, // Khách hàng
                            { wch: 25 }, // Khách hàng cá nhân
                            { wch: 25 }, // Khách hàng doanh nghiệp
                            { wch: 10 }, // Xóa nợ
                            { wch: 15 }, // Vụ đầu tư
                            { wch: 15 }, // Owner
                            { wch: 15 }, // Số trờ trình
                            { wch: 15 }, // Category Debt
                            { wch: 30 }, // Description
                            { wch: 20 }, // Mã KH cá nhân
                            { wch: 20 }, // Mã KH doanh nghiệp
                        ];
                        worksheet["!cols"] = columnWidths;

                        // Create a workbook
                        const workbook = XLSX.utils.book_new();
                        XLSX.utils.book_append_sheet(
                            workbook,
                            worksheet,
                            "Phiếu thu nợ dịch vụ"
                        );

                        // Create an Excel file
                        const excelBuffer = XLSX.write(workbook, {
                            bookType: "xlsx",
                            type: "array",
                        });
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
                        Swal.fire({
                            title: "Thành công!",
                            text: "Xuất Excel thành công",
                            icon: "success",
                            confirmButtonText: "OK",
                            buttonsStyling: false,
                            customClass: {
                                confirmButton: "btn btn-success",
                            },
                        });
                    });
                });
            } catch (error) {
                console.error("Export error:", error);
                Swal.fire({
                    title: "Lỗi!",
                    text: "Đã xảy ra lỗi khi xuất dữ liệu. Vui lòng thử lại.",
                    icon: "error",
                    confirmButtonText: "OK",
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: "btn btn-success",
                    },
                });
            }
        },
        closeExportModal() {
            const modalElement = document.getElementById("exportModal");
            if (modalElement) {
                // First move focus to a safe element outside the modal
                document.body.focus();
                // Then hide the modal
                const bsModal = bootstrap.Modal.getInstance(modalElement);
                if (bsModal) {
                    bsModal.hide();
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
                    "/api/import-phieu-thu-no-dich-vu",
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
                            (Array.isArray(error.response.data.message)
                                ? error.response.data.message
                                : [
                                      error.response.data.message ||
                                          "Lỗi máy chủ",
                                  ]);
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
                    `/api/import-thu-no-dich-vu-progress/${importId}`,
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
                            this.fetchData();
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
        /**
         * Download template for import
         */
        downloadImportTemplate() {
            try {
                // Create a workbook with headers
                const wb = XLSX.utils.book_new();
                const headers = this.getDbColumns();

                // Create example data row
                const exampleRow = {};
                headers.forEach((header) => {
                    // Add sample data for each column
                    switch (header) {
                        case "Ma_So_Phieu_PDN_Thu_No":
                            exampleRow[header] = "PDN001";
                            break;
                        case "Da_Tra_Goc":
                        case "Tien_Lai":
                        case "Xoa_No_Phan_Bo_Dau_Tu":
                            exampleRow[header] = "1000000";
                            break;
                        case "Lai_Suat_Phan_Bo_Dau_Tu":
                            exampleRow[header] = "5.5";
                            break;
                        case "Ngay_Vay":
                        case "Ngay_Tra":
                            exampleRow[header] = new Date()
                                .toISOString()
                                .split("T")[0];
                            break;
                        case "Created_On":
                            exampleRow[header] = new Date()
                                .toISOString()
                                .slice(0, 19)
                                .replace("T", " ");
                            break;
                        case "Tinh_Trang_PDN_Thu_No":
                            exampleRow[header] = "active";
                            break;
                        case "Tinh_Trang_Duyet_PDN_Thu_No":
                            exampleRow[header] = "pending";
                            break;
                        case "Da_Ho_Tro_Lai":
                            exampleRow[header] = "0";
                            break;
                        default:
                            exampleRow[header] = header + " example";
                    }
                });

                // Create worksheet with headers and example data
                const ws = XLSX.utils.json_to_sheet([exampleRow], {
                    header: headers,
                });

                // Set column widths
                const columnWidths = headers.map(() => ({ wch: 20 }));
                ws["!cols"] = columnWidths;

                // Add worksheet to workbook
                XLSX.utils.book_append_sheet(wb, ws, "Template");

                // Save file
                XLSX.writeFile(wb, "phieu_thu_no_dich_vu_import_template.xlsx");
            } catch (error) {
                console.error("Error generating template:", error);
                Swal.fire({
                    title: "Lỗi",
                    text: "Không thể tạo tệp mẫu nhập liệu",
                    icon: "error",
                    confirmButtonText: "OK",
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: "btn btn-success",
                    },
                });
            }
        },

        /**
         * Get the list of database columns
         */
        getDbColumns() {
            return [
                "Ma_So_Phieu_PDN_Thu_No",
                "Ma_So_Phieu_Phan_Bo_Dau_Tu",
                "Phan_Bo_Dau_Tu",
                "So_Phieu_Phan_Bo_Dau_Tu",
                "Invoice_Number_Phan_Bo_Dau_Tu",
                "Da_Tra_Goc",
                "Ngay_Vay",
                "Ngay_Tra",
                "Lai_Suat_Phan_Bo_Dau_Tu",
                "Tien_Lai",
                "Tinh_Trang_PDN_Thu_No",
                "Tinh_Trang_Duyet_PDN_Thu_No",
                "Da_Ho_Tro_Lai",
                "Phieu_Tinh_Tien_Mia_PDN_Thu_No",
                "Created_On",
                "Vu_Thanh_Toan_Phan_Bo_Dau_Tu",
                "Khach_Hang_PDN_Thu_No",
                "Khach_Hang_Ca_Nhan_PDN_Thu_No",
                "Khach_Hang_Doanh_Nghiep_PDN_Thu_No",
                "Xoa_No_Phan_Bo_Dau_Tu",
                "Vu_Dau_Tu_Phan_Bo_Dau_Tu",
                "Owner_PDN_Thu_No",
                "So_Tro_Trinh",
                "Category_Debt",
                "Description",
                "Ma_Khach_Hang_Ca_Nhan",
                "Ma_Khach_Hang_Doanh_Nghiep",
            ];
        },
        initPerfectScrollbar() {
            // Initialize PerfectScrollbar after the DOM is updated
            this.$nextTick(() => {
                if (this.$refs.tableScrollContainer) {
                    // Destroy existing instance if it exists
                    if (this.ps) {
                        this.ps.destroy();
                    }
                    // Create new PerfectScrollbar instance
                    this.ps = new PerfectScrollbar(
                        this.$refs.tableScrollContainer,
                        {
                            suppressScrollX: false,
                            wheelPropagation: false,
                        }
                    );
                }
            });
        },

        updateScrollbar() {
            // Update the scrollbar when data changes
            this.$nextTick(() => {
                if (this.ps) {
                    this.ps.update();
                }
            });
        },
    },
    watch: {
        phieuList: {
            handler() {
                this.extractUniqueValues();
                this.extractCategoryOptions();
                this.updateScrollbar();
            },
            deep: true,
        },
        // เพิ่ม watcher สำหรับ selectedItems
        selectedItems: {
            handler(newVal) {
                console.log("Selected items changed:", newVal.length);
            },
            deep: true,
        },

        // Clear selections when page changes
        currentPage() {
            // เคลียร์การเลือกเมื่อเปลี่ยนหน้า (ตัวเลือก)
            // this.selectedItems = [];
        },
        search() {
            this.currentPage = 1;
            this.updateScrollbar();
        },
        statusFilter() {
            this.currentPage = 1;
            this.updateScrollbar();
        },
        paginatedItems: {
            handler() {
                this.updateScrollbar();
            },
            deep: true,
        },
        filteredItems: {
            handler() {
                this.calculateTotals();
                this.updateScrollbar();
            },
            deep: true,
        },
    },
    mounted() {
        this.fetchData(1, true, () => {
            // This will run after data is fetched
            this.extractUniqueValues();
        });

        // Initialize unique values for dropdown filters
        [
            "tinh_trang",
            "tinh_trang_duyet",
            "da_ho_tro_lai",
            "vu_thanh_toan",
            "vu_dau_tu",
            "category_debt",
            "xoa_no",
        ].forEach((column) => {
            this.updateUniqueValues(column);
        });

        // Watch for screen size changes to toggle mobile view
        window.addEventListener("resize", () => {
            this.isMobile = window.innerWidth < 768;
            this.updateScrollbar();
        });

        // Initialize PerfectScrollbar
        this.initPerfectScrollbar();
    },
    updated() {
        // Update scrollbar after component updates
        this.updateScrollbar();
    },
    beforeUnmount() {
        window.removeEventListener("resize", () => {
            this.isMobile = window.innerWidth < 768;
        });

        // Destroy PerfectScrollbar instance when component is unmounted
        if (this.ps) {
            this.ps.destroy();
            this.ps = null;
        }
    },
};
</script>

<style scoped>
/* Table & Pagination Styling */
.text-success {
    color: #198754;
}
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
    position: relative;
}
.card:hover {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.desktop-controls-container.card {
    /* ให้ card มีความสูงเต็ม */
    height: calc(100vh - 270px);
    /* height: auto; */
}

/* Table container with fixed header */
.table-container-wrapper {
    position: relative;
    width: 100%;
}

.table-scroll-container {
    position: relative;
    max-height: calc(100vh - 385px);
    height: calc(100vh - 270px);
    overflow: auto;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
}

.table-auto {
    border-collapse: separate;
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
    font-size: 12px;
    position: sticky;
    top: 0;
    background-color: #f3f4f6;
    z-index: 20;
    padding: 0.75rem;
    border-bottom: 2px solid #e5e7eb;
    font-weight: 600;
    text-align: left;
    color: #374151;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
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

/* Ensure proper spacing in pagination */
.pagination-wrapper {
    position: relative;
    margin-top: 1rem;
    width: 100%;
}

/* Desktop row styling */
.desktop-row {
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
}

.desktop-row:hover {
    background-color: #f0f9f0;
    box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);
}

.table-auto td {
    padding: 0.75rem;
    border-bottom: 1px solid #e5e7eb;
    vertical-align: middle;
    font-size: 0.7rem;
    white-space: nowrap;
}

/* Status select styling */
.status-select {
    border: 1px solid #e5e7eb;
    padding: 0.5rem 2rem 0.5rem 1rem;
    border-radius: 0.375rem;
    background-color: #fff;
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

/* Improved Pagination Styling */
.pagination {
    display: flex;
    padding-left: 0;
    list-style: none;
    border-radius: 0.25rem;
    margin: 0;
}

.page-item {
    margin: 0 2px;
}

.page-link {
    position: relative;
    display: block;
    padding: 0.5rem 0.75rem;
    margin-left: -1px;
    line-height: 1.25;
    color: #198754;
    background-color: #fff;
    border: 1px solid #dee2e6;
    transition: all 0.2s ease;
}

.page-link:hover {
    z-index: 2;
    color: #0d6efd;
    text-decoration: none;
    background-color: #e9ecef;
    border-color: #dee2e6;
}

.page-link:focus {
    z-index: 3;
    outline: 0;
    box-shadow: 0 0 0 0.25rem rgba(16, 185, 129, 0.25);
}

.page-item.active .page-link {
    z-index: 3;
    color: #fff;
    background-color: #198754;
    border-color: #198754;
}

.page-item.disabled .page-link {
    color: #6c757d;
    pointer-events: none;
    cursor: auto;
    background-color: #fff;
    border-color: #dee2e6;
}

/* Loading indicator */
#loading-wrapper {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: rgba(255, 255, 255, 0.7);
    z-index: 9999;
}

.spinner-border {
    width: 3rem;
    height: 3rem;
    color: #198754;
}

/* Reset filters button styling */
.reset-all-filters-btn {
    position: absolute;
    right: 15px;
    top: 35px;
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

/* Filter button styling */
.filter-btn {
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 0.25rem;
    margin-left: 0.25rem;
    color: #6c757d;
    transition: all 0.2s ease;
}

.filter-btn:hover {
    color: #10b981;
}

.filter-btn:focus {
    outline: none;
}

/* Status color classes */
.text-yellow-600 {
    color: #d97706;
}

.text-blue-600 {
    color: #2563eb;
}

.text-green-500 {
    color: #10b981;
}

.text-red-500 {
    color: #ef4444;
}

.text-purple-500 {
    color: #8b5cf6;
}

/* Pagination card styling */
.pagination-card {
    padding: 0.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border-radius: 0.5rem;
    background-color: #fff;
    max-height: 50px;
    display: flex;
    justify-content: center;
}

/* Filter dropdown positioning */
.absolute.mt-1.bg-white.p-2.rounded.shadow-lg.z-10 {
    position: absolute;
    top: calc(100% + 5px);
    left: 0;
    min-width: 250px;
    max-width: 300px;
    z-index: 1050;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1),
        0 4px 6px -2px rgba(0, 0, 0, 0.05);
    padding: 12px;
    border: 1px solid #e2e8f0;
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

    .status-filter {
        min-width: 100%;
        margin-bottom: 1rem;
    }

    .search-input {
        min-width: 100%;
    }

    .table-auto thead th {
        font-size: 0.75rem;
        padding: 0.5rem;
    }

    .table-auto td {
        font-size: 0.75rem;
        padding: 0.5rem;
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

/* Totals cards styling */
.totals-container {
    display: flex;
    flex-wrap: wrap;
    gap: 1.25rem;
    margin-bottom: 0.5rem;
    justify-content: space-between;
    background-color: white;
    padding: 1rem;
    border-radius: 1rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
}

.total-card {
    background-color: #ffffff;
    flex: 1;
    min-width: 200px;
    display: flex;
    align-items: center;
    padding: 1.25rem;
    border-radius: 0.75rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1),
        0 2px 4px -1px rgba(0, 0, 0, 0.06);
    transition: transform 0.2s, box-shadow 0.2s;
    overflow: hidden;
    position: relative;
}

.total-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1),
        0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.total-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    margin-right: 1rem;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.total-content {
    flex-grow: 1;
}

.total-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #6b7280;
    margin-bottom: 0.25rem;
    white-space: nowrap;
}

.total-value {
    font-size: 1rem;
    font-weight: 700;
    line-height: 1.2;
}

/* Card specific colors */
.total-debt {
    background-color: rgba(59, 130, 246, 0.1);
    border-left: 4px solid #3b82f6;
}

.total-debt .total-icon {
    color: #3b82f6;
    background-color: rgba(59, 130, 246, 0.2);
}

.total-debt .total-value {
    color: #1e40af;
}

.total-interest {
    background-color: rgba(245, 158, 11, 0.1);
    border-left: 4px solid #f59e0b;
}

.total-interest .total-icon {
    color: #f59e0b;
    background-color: rgba(245, 158, 11, 0.2);
}

.total-interest .total-value {
    color: #b45309;
}

.total-count {
    background-color: rgba(16, 185, 129, 0.1);
    border-left: 4px solid #10b981;
}

.total-count .total-icon {
    color: #10b981;
    background-color: rgba(16, 185, 129, 0.2);
}

.total-count .total-value {
    color: #065f46;
}

.total-custom {
    background-color: rgba(139, 92, 246, 0.1);
    border-left: 4px solid #8b5cf6;
}

.total-custom .total-icon {
    color: #8b5cf6;
    background-color: rgba(139, 92, 246, 0.2);
}

.total-custom .total-value {
    color: #5b21b6;
}

/* Mobile card improvements */
.card-header-mobile {
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    padding-bottom: 0.75rem;
}

.status-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 1rem;
    display: inline-flex;
    align-items: center;
    font-weight: 500;
}

.customer-info {
    background-color: rgba(243, 244, 246, 0.7);
}

.financial-item {
    text-align: center;
    flex: 1;
}

.financial-label {
    font-size: 0.75rem;
    color: #6b7280;
    margin-bottom: 0.25rem;
}

.financial-value {
    font-weight: 600;
    font-size: 1rem;
}

.date-item {
    flex: 1;
    text-align: center;
}

.date-label {
    font-size: 0.75rem;
    color: #6b7280;
    margin-bottom: 0.25rem;
}

.date-value {
    font-weight: 500;
}

.card-btn {
    color: #10b981;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background-color: rgba(16, 185, 129, 0.1);
    transition: all 0.2s;
}

.reset-all-filters-btn-mobile {
    /* position: absolute; */
    right: 15px;
    top: 0px;
    z-index: 99;
    font-size: 0.875rem;
    cursor: pointer;
    color: #fff;
    background: #198754;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .totals-container {
        gap: 0.75rem;
    }

    .total-card {
        min-width: calc(50% - 0.5rem);
        flex: 0 0 calc(50% - 0.5rem);
        padding: 0.75rem;
    }

    .total-icon {
        width: 40px;
        height: 40px;
        font-size: 1.25rem;
        margin-right: 0.75rem;
    }

    .total-label {
        font-size: 0.75rem;
    }

    .total-value {
        font-size: 1rem;
    }
}

@media (max-width: 480px) {
    .totals-container {
        flex-direction: column;
    }

    .total-card {
        width: 100%;
        min-width: 100%;
    }
}

/* Add these styles to your <style> section */

/* Search and Filter Container Layout */
.search-filter-container {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    width: 100%;
}

.search-container {
    flex: 0.9; /* Takes 90% of the space */
}

/* Date Filter Toggle Button */
.date-filter-toggle-btn {
    flex: 0.1; /* Takes 10% of the space */
    min-width: 40px;
    max-width: 45px;
    height: 40px;
    background-color: #f3f4f6;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6b7280;
    font-size: 1rem;
    position: relative;
    transition: all 0.2s ease;
}

.date-filter-toggle-btn:hover {
    background-color: #e5e7eb;
    color: #10b981;
}

.date-filter-toggle-btn.active {
    background-color: #dcfce7;
    color: #10b981;
    border-color: #10b981;
}

/* Filter active indicator (small dot) */
.filter-indicator {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 8px;
    height: 8px;
    background-color: #ef4444;
    border-radius: 50%;
}

/* Date Range Filter Styling */
.date-range-filter {
    background-color: #ffffff;
    border-radius: 0.75rem;
    padding: 1rem;
    margin-top: 0.25rem;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(226, 232, 240, 0.8);
    overflow: hidden;
    max-height: 0;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.date-range-filter.active {
    border-color: #10b981;
    background-color: rgba(16, 185, 129, 0.05);
}

.date-range-filter[style*="display: block"] {
    max-height: 250px;
    opacity: 1;
    visibility: visible;
    margin-bottom: 0.75rem;
}

.date-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.date-title {
    font-size: 0.9rem;
    font-weight: 500;
    color: #374151;
    display: flex;
    align-items: center;
}

.date-close-btn {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    border: none;
    background-color: #f3f4f6;
    color: #6b7280;
    transition: all 0.2s;
}

.date-close-btn:hover {
    background-color: #e5e7eb;
    color: #ef4444;
}

.date-range-inputs {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.date-input-group {
    display: flex;
    flex-direction: column;
}

.date-label {
    font-size: 0.75rem;
    color: #6b7280;
    margin-bottom: 0.25rem;
}

.date-input {
    padding: 0.5rem;
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
    font-size: 0.875rem;
    background-color: white;
    transition: all 0.2s ease;
}

.date-input:focus {
    outline: none;
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
}

.date-actions {
    display: flex;
    justify-content: space-between;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.date-reset-btn {
    padding: 0.5rem 0.75rem;
    border-radius: 0.375rem;
    background-color: #f3f4f6;
    border: 1px solid #e5e7eb;
    color: #4b5563;
    font-size: 0.8rem;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.date-reset-btn:hover {
    background-color: #fee2e2;
    color: #ef4444;
    border-color: #fca5a5;
}

.date-apply-btn {
    padding: 0.5rem 0.75rem;
    border-radius: 0.375rem;
    background-color: #10b981;
    border: 1px solid #059669;
    color: white;
    font-size: 0.8rem;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.date-apply-btn:hover {
    background-color: #059669;
}

.date-apply-btn:disabled {
    background-color: #9ca3af;
    border-color: #6b7280;
    cursor: not-allowed;
    opacity: 0.6;
}

/* Additional CSS for the date filter modal */
.date-filter-popup {
    border-radius: 1rem;
    padding: 1rem;
}

.date-input-groups {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.swal2-title {
    font-size: 1.25rem !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.date-filter-modal .swal2-html-container {
    margin-top: 0.5rem;
}

/* Mobile optimizations */
@media (max-width: 480px) {
    .date-filter-modal .swal2-popup {
        padding: 1rem;
        width: 90% !important;
    }
    .swal2-actions-custom-gap {
        gap: 10px !important;
    }

    .date-filter-modal .swal2-title {
        font-size: 1rem !important;
    }
}

/* Add styling for the hint */
.active-filter-hint {
    position: absolute;
    top: -30px;
    right: 0;
    background-color: #ffffff;
    padding: 0.3rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    display: none;
    z-index: 10;
    white-space: nowrap;
}

.date-filter-toggle-btn:hover .active-filter-hint {
    display: block;
}

/* Router link styling */
.text-blue-600 {
    color: #2563eb;
}
.hover\:text-blue-800:hover {
    color: #1e40af;
}
.hover\:underline:hover {
    text-decoration: underline;
}
.font-medium {
    font-weight: 500;
}
.transition-colors {
    transition: color 0.2s ease;
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
