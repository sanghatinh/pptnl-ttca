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
                        <option value="all">
                            Tất cả trạng thái ({{ statusCounts.total }})
                        </option>
                        <option value="processing">
                            Đang xử lý ({{ statusCounts.processing }})
                        </option>
                        <option value="submitted">
                            Đã nộp kế toán ({{ statusCounts.submitted }})
                        </option>

                        <option value="paid">
                            Đã thanh toán ({{ statusCounts.paid }})
                        </option>
                        <option value="rejected">
                            Từ chối ({{ statusCounts.rejected }})
                        </option>
                    </select>
                </div>
                <!-- New filter section for Vụ đầu tư -->
                <div class="filter-section">
                    <select
                        v-model="vuDauTuFilter"
                        class="form-select filter-select"
                        @change="applyVuDauTuFilter"
                    >
                        <option value="">Tất cả vụ đầu tư</option>
                        <option
                            v-for="option in uniqueValues.vu_dau_tu"
                            :key="option"
                            :value="option"
                        >
                            {{ option }}
                        </option>
                    </select>
                </div>
                <!-- Add Export/Import buttons to the dropdown menu -->
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
                        </ul>
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
        </div>
        <!-- Totals Cards -->
        <div class="row mb-0">
            <div class="col-12">
                <div class="totals-container">
                    <!-- Total Amount Card -->
                    <div class="total-card total-amount">
                        <div class="total-icon">
                            <i class="fas fa-coins"></i>
                        </div>
                        <div class="total-content">
                            <div class="total-label">Tổng tiền</div>
                            <div class="total-value">
                                {{
                                    formatCurrency(financialTotals.total_amount)
                                }}
                            </div>
                        </div>
                    </div>

                    <!-- Total Hold Amount Card -->
                    <div class="total-card total-hold">
                        <div class="total-icon">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <div class="total-content">
                            <div class="total-label">Tổng tiền tạm giữ</div>
                            <div class="total-value">
                                {{
                                    formatCurrency(
                                        financialTotals.total_hold_amount
                                    )
                                }}
                            </div>
                        </div>
                    </div>

                    <!-- Total Deduction Card -->
                    <div class="total-card total-deduction">
                        <div class="total-icon">
                            <i class="fas fa-minus-circle"></i>
                        </div>
                        <div class="total-content">
                            <div class="total-label">Tổng tiền khấu trừ</div>
                            <div class="total-value">
                                {{
                                    formatCurrency(
                                        financialTotals.total_deduction_amount
                                    )
                                }}
                            </div>
                        </div>
                    </div>

                    <!-- Total Interest Card -->
                    <div class="total-card total-interest">
                        <div class="total-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="total-content">
                            <div class="total-label">Tổng tiền lãi suất</div>
                            <div class="total-value">
                                {{
                                    formatCurrency(
                                        financialTotals.total_interest_amount
                                    )
                                }}
                            </div>
                        </div>
                    </div>

                    <!-- Total Remaining Card -->
                    <div class="total-card total-remaining">
                        <div class="total-icon">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div class="total-content">
                            <div class="total-label">Tổng tiền thanh toán</div>
                            <div class="total-value">
                                {{
                                    formatCurrency(
                                        financialTotals.total_remaining_amount
                                    )
                                }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Mobile Controls -->
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
                        :class="{ active: dateRangeActive }"
                        title="Lọc theo ngày thanh toán"
                    >
                        <i class="fas fa-calendar-alt"></i>
                        <span
                            class="filter-indicator"
                            v-if="dateRangeActive"
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
                        <option value="all">
                            Tất cả trạng thái ({{ statusCounts.total }})
                        </option>
                        <option value="processing">
                            Đang xử lý ({{ statusCounts.processing }})
                        </option>
                        <option value="submitted">
                            Đã nộp kế toán ({{ statusCounts.submitted }})
                        </option>
                        <option value="paid">
                            Đã thanh toán ({{ statusCounts.paid }})
                        </option>
                        <option value="rejected">
                            Từ chối ({{ statusCounts.rejected }})
                        </option>
                    </select>

                    <select
                        v-model="vuDauTuFilter"
                        class="form-select status-select flex-1"
                        @change="applyVuDauTuFilter"
                    >
                        <option value="">Vụ đầu tư</option>
                        <option
                            v-for="option in uniqueValues.vu_dau_tu"
                            :key="option"
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

        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <!-- Desktop view table -->
            <div v-if="!isMobile" class="card">
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
                                        <!-- Add checkbox column -->
                                        <th
                                            class="text-center"
                                            style="width: 50px"
                                        >
                                            <input
                                                type="checkbox"
                                                :checked="isAllSelected"
                                                @change="toggleSelectAll"
                                                class="form-check-input"
                                            />
                                        </th>
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
                                                    placeholder="Lọc theo mã..."
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
                                        <th>
                                            <div class="th-content">
                                                <div
                                                    class="th-title"
                                                    @click="
                                                        toggleSort('vu_dau_tu')
                                                    "
                                                >
                                                    Vụ đầu tư
                                                    <span
                                                        class="sort-indicator"
                                                        >{{
                                                            getSortIndicator(
                                                                "vu_dau_tu"
                                                            )
                                                        }}</span
                                                    >
                                                </div>
                                                <button
                                                    @click.stop="
                                                        toggleFilter(
                                                            'vu_dau_tu'
                                                        )
                                                    "
                                                    class="filter-btn"
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
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
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
                                        <th>
                                            <div class="th-content">
                                                <div
                                                    class="th-title"
                                                    @click="
                                                        toggleSort(
                                                            'loai_thanh_toan'
                                                        )
                                                    "
                                                >
                                                    Loại thanh toán
                                                    <span
                                                        class="sort-indicator"
                                                        >{{
                                                            getSortIndicator(
                                                                "loai_thanh_toan"
                                                            )
                                                        }}</span
                                                    >
                                                </div>
                                                <button
                                                    @click.stop="
                                                        toggleFilter(
                                                            'loai_thanh_toan'
                                                        )
                                                    "
                                                    class="filter-btn"
                                                >
                                                    <i
                                                        class="fas fa-filter"
                                                        :class="{
                                                            'text-green-500':
                                                                columnFilters.loai_thanh_toan,
                                                        }"
                                                    ></i>
                                                </button>
                                            </div>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'loai_thanh_toan'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <div
                                                    class="max-h-40 overflow-y-auto mb-2"
                                                >
                                                    <div
                                                        v-for="option in uniqueValues.loai_thanh_toan"
                                                        :key="option"
                                                        class="flex items-center mb-2"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            :id="`loai_thanh_toan-${option}`"
                                                            :value="option"
                                                            v-model="
                                                                selectedFilterValues.loai_thanh_toan
                                                            "
                                                            class="mr-2 rounded text-green-500 focus:ring-green-500"
                                                        />
                                                        <label
                                                            :for="`loai_thanh_toan-${option}`"
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
                                                                'loai_thanh_toan'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'loai_thanh_toan'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>
                                        <th>
                                            <div class="th-content">
                                                <div
                                                    class="th-title"
                                                    @click="
                                                        toggleSort(
                                                            'trang_thai_thanh_toan'
                                                        )
                                                    "
                                                >
                                                    Trạng thái thanh toán
                                                    <span
                                                        class="sort-indicator"
                                                        >{{
                                                            getSortIndicator(
                                                                "trang_thai_thanh_toan"
                                                            )
                                                        }}</span
                                                    >
                                                </div>
                                                <button
                                                    @click.stop="
                                                        toggleFilter(
                                                            'trang_thai_thanh_toan'
                                                        )
                                                    "
                                                    class="filter-btn"
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
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <div
                                                    class="max-h-40 overflow-y-auto mb-2"
                                                >
                                                    <div
                                                        v-for="option in uniqueValues.trang_thai_thanh_toan"
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
                                                                'trang_thai_thanh_toan'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'trang_thai_thanh_toan'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>
                                        <th>
                                            <div class="th-content">
                                                <div
                                                    class="th-title"
                                                    @click="
                                                        toggleSort(
                                                            'ngay_thanh_toan'
                                                        )
                                                    "
                                                >
                                                    Ngày thanh toán
                                                    <span
                                                        class="sort-indicator"
                                                        >{{
                                                            getSortIndicator(
                                                                "ngay_thanh_toan"
                                                            )
                                                        }}</span
                                                    >
                                                </div>
                                                <button
                                                    @click.stop="
                                                        toggleFilter(
                                                            'ngay_thanh_toan'
                                                        )
                                                    "
                                                    class="filter-btn"
                                                >
                                                    <i
                                                        class="fas fa-filter"
                                                        :class="{
                                                            'text-green-500':
                                                                columnFilters.ngay_thanh_toan,
                                                        }"
                                                    ></i>
                                                </button>
                                            </div>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'ngay_thanh_toan'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="date"
                                                    v-model="
                                                        columnFilters.ngay_thanh_toan
                                                    "
                                                    class="form-control mb-2"
                                                />
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'ngay_thanh_toan'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'ngay_thanh_toan'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>
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
                                                    placeholder="Lọc theo khách hàng..."
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
                                                    placeholder="Lọc theo mã..."
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
                                        <th>
                                            Khách hàng doanh nghiệp
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
                                                    placeholder="Lọc theo doanh nghiệp..."
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
                                        <th>
                                            Mã KH doanh nghiệp
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
                                                    placeholder="Lọc theo mã..."
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
                                        <th>
                                            <div class="th-content">
                                                <div
                                                    class="th-title"
                                                    @click="
                                                        toggleSort('tong_tien')
                                                    "
                                                >
                                                    Tổng tiền
                                                    <span
                                                        class="sort-indicator"
                                                        >{{
                                                            getSortIndicator(
                                                                "tong_tien"
                                                            )
                                                        }}</span
                                                    >
                                                </div>
                                            </div>
                                        </th>
                                        <th>
                                            <div class="th-content">
                                                <div
                                                    class="th-title"
                                                    @click="
                                                        toggleSort(
                                                            'tong_tien_tam_giu'
                                                        )
                                                    "
                                                >
                                                    Tổng tiền tạm giữ
                                                    <span
                                                        class="sort-indicator"
                                                        >{{
                                                            getSortIndicator(
                                                                "tong_tien_tam_giu"
                                                            )
                                                        }}</span
                                                    >
                                                </div>
                                            </div>
                                        </th>
                                        <th>
                                            <div class="th-content">
                                                <div
                                                    class="th-title"
                                                    @click="
                                                        toggleSort(
                                                            'tong_tien_khau_tru'
                                                        )
                                                    "
                                                >
                                                    Tổng tiền khấu trừ
                                                    <span
                                                        class="sort-indicator"
                                                        >{{
                                                            getSortIndicator(
                                                                "tong_tien_khau_tru"
                                                            )
                                                        }}</span
                                                    >
                                                </div>
                                            </div>
                                        </th>
                                        <th>
                                            <div class="th-content">
                                                <div
                                                    class="th-title"
                                                    @click="
                                                        toggleSort(
                                                            'tong_tien_lai_suat'
                                                        )
                                                    "
                                                >
                                                    Tổng tiền lãi suất
                                                    <span
                                                        class="sort-indicator"
                                                        >{{
                                                            getSortIndicator(
                                                                "tong_tien_lai_suat"
                                                            )
                                                        }}</span
                                                    >
                                                </div>
                                            </div>
                                        </th>
                                        <th>
                                            <div class="th-content">
                                                <div
                                                    class="th-title"
                                                    @click="
                                                        toggleSort(
                                                            'tong_tien_thanh_toan_con_lai'
                                                        )
                                                    "
                                                >
                                                    Tổng tiền thanh toán còn lại
                                                    <span
                                                        class="sort-indicator"
                                                        >{{
                                                            getSortIndicator(
                                                                "tong_tien_thanh_toan_con_lai"
                                                            )
                                                        }}</span
                                                    >
                                                </div>
                                            </div>
                                        </th>
                                        <th>
                                            <div class="th-content">
                                                <div
                                                    class="th-title"
                                                    @click="
                                                        toggleSort(
                                                            'proposal_number'
                                                        )
                                                    "
                                                >
                                                    Số tờ trình
                                                    <span
                                                        class="sort-indicator"
                                                        >{{
                                                            getSortIndicator(
                                                                "proposal_number"
                                                            )
                                                        }}</span
                                                    >
                                                </div>
                                                <button
                                                    @click.stop="
                                                        toggleFilter(
                                                            'proposal_number'
                                                        )
                                                    "
                                                    class="filter-btn"
                                                >
                                                    <i
                                                        class="fas fa-filter"
                                                        :class="{
                                                            'text-green-500':
                                                                columnFilters.proposal_number,
                                                        }"
                                                    ></i>
                                                </button>
                                            </div>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'proposal_number'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.proposal_number
                                                    "
                                                    class="form-control mb-2"
                                                    placeholder="Lọc theo số tờ trình..."
                                                />
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'proposal_number'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'proposal_number'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>
                                        <th>
                                            <div class="th-content">
                                                <div
                                                    class="th-title"
                                                    @click="
                                                        toggleSort(
                                                            'payment_installment'
                                                        )
                                                    "
                                                >
                                                    Đợt thanh toán
                                                    <span
                                                        class="sort-indicator"
                                                        >{{
                                                            getSortIndicator(
                                                                "payment_installment"
                                                            )
                                                        }}</span
                                                    >
                                                </div>
                                                <button
                                                    @click.stop="
                                                        toggleFilter(
                                                            'payment_installment'
                                                        )
                                                    "
                                                    class="filter-btn"
                                                >
                                                    <i
                                                        class="fas fa-filter"
                                                        :class="{
                                                            'text-green-500':
                                                                columnFilters.payment_installment,
                                                        }"
                                                    ></i>
                                                </button>
                                            </div>

                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'payment_installment'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.payment_installment
                                                    "
                                                    class="form-control mb-2"
                                                    placeholder="Lọc theo đợt thanh toán..."
                                                />
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'payment_installment'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'payment_installment'
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
                                        @click="viewDetails(item)"
                                        class="cursor-pointer"
                                    >
                                        <!-- Add checkbox column -->
                                        <td class="text-center" @click.stop>
                                            <input
                                                type="checkbox"
                                                :value="item"
                                                v-model="selectedItems"
                                                class="form-check-input"
                                            />
                                        </td>
                                        <td>{{ item.ma_giai_ngan }}</td>
                                        <td>{{ item.vu_dau_tu }}</td>
                                        <td>
                                            <span
                                                class="badge bg-info text-white"
                                            >
                                                {{ item.loai_thanh_toan }}
                                            </span>
                                        </td>
                                        <td>
                                            <span
                                                v-if="
                                                    item.trang_thai_thanh_toan !==
                                                    undefined
                                                "
                                                :class="
                                                    statusClass(
                                                        item.trang_thai_thanh_toan
                                                    )
                                                "
                                                class="status-badge flex items-center"
                                            >
                                                <i
                                                    :class="
                                                        statusIcons(
                                                            item.trang_thai_thanh_toan
                                                        )
                                                    "
                                                    class="mr-1"
                                                ></i>
                                                {{
                                                    formatStatus(
                                                        item.trang_thai_thanh_toan
                                                    )
                                                }}
                                            </span>
                                        </td>
                                        <td>
                                            {{
                                                formatDate(item.ngay_thanh_toan)
                                            }}
                                        </td>
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
                                        <td>
                                            {{ formatCurrency(item.tong_tien) }}
                                        </td>
                                        <td>
                                            {{
                                                formatCurrency(
                                                    item.tong_tien_tam_giu
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                formatCurrency(
                                                    item.tong_tien_khau_tru
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                formatCurrency(
                                                    item.tong_tien_lai_suat
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                formatCurrency(
                                                    item.tong_tien_thanh_toan_con_lai
                                                )
                                            }}
                                        </td>
                                        <td>
                                            <span
                                                class="badge bg-warning text-dark"
                                            >
                                                {{ item.proposal_number }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="badge bg-danger text-white"
                                                >{{
                                                    item.payment_installment
                                                }}</span
                                            >
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
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
        </div>

        <!-- Mobile view cards -->
        <div v-if="isMobile" class="card-container">
            <div
                v-for="item in paginatedItems.data"
                :key="item.id"
                class="card border-0 p-0 mb-4 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300"
                @click="viewDetails(item)"
            >
                <!-- Card header -->
                <div
                    class="card-header p-3 bg-green-50 flex justify-between items-center rounded-t-lg border-b border-gray-100"
                >
                    <div class="flex items-center">
                        <div class="badge-container mr-2">
                            <span
                                :class="statusClass(item.trang_thai_thanh_toan)"
                                class="status-badge flex items-center text-xs px-2 py-1 rounded"
                            >
                                <i
                                    :class="
                                        statusIcons(item.trang_thai_thanh_toan)
                                    "
                                    class="mr-1"
                                ></i>
                                {{ formatStatus(item.trang_thai_thanh_toan) }}
                            </span>
                        </div>
                    </div>
                    <span class="text-xs text-gray-500">{{
                        formatDate(item.ngay_thanh_toan)
                    }}</span>
                </div>

                <!-- Card body -->
                <div class="card-body p-3">
                    <!-- Vụ đầu tư and loại thanh toán -->
                    <div class="mb-3">
                        <h5 class="text-green-900 font-medium mb-0 text-sm">
                            {{ item.ma_giai_ngan || "Không xác định" }}
                        </h5>
                        <p class="font-medium mb-1">
                            {{ item.vu_dau_tu || "Không có vụ đầu tư" }}
                        </p>
                        <p class="text-sm text-gray-600 mb-0">
                            Loại thanh toán: {{ item.loai_thanh_toan || "N/A" }}
                        </p>
                    </div>

                    <!-- Customer info -->
                    <div class="customer-info bg-gray-50 p-2 rounded-md mb-3">
                        <p class="text-sm mb-1">
                            <i class="fas fa-user-tie text-gray-500 mr-2"></i>
                            {{
                                item.khach_hang_ca_nhan ||
                                item.khach_hang_doanh_nghiep ||
                                "Không có thông tin"
                            }}
                        </p>
                        <p class="text-xs text-gray-500 mb-0">
                            <i class="fas fa-tag mr-1"></i>
                            Mã KH:
                            {{
                                item.ma_khach_hang_ca_nhan ||
                                item.ma_khach_hang_doanh_nghiep ||
                                "N/A"
                            }}
                            <span v-if="item.proposal_number" class="mx-1"
                                >•</span
                            >
                            {{
                                item.proposal_number
                                    ? "Số tờ trình: " + item.proposal_number
                                    : ""
                            }}
                        </p>
                    </div>

                    <!-- Financial details grid -->
                    <div class="grid grid-cols-2 gap-2 mb-1">
                        <div class="bg-blue-50 p-2 rounded">
                            <p class="text-xs text-blue-700 mb-0">Tổng tiền</p>
                            <p class="text-sm font-medium mb-0">
                                {{ formatCurrency(item.tong_tien) }}
                            </p>
                        </div>
                        <div class="bg-green-50 p-2 rounded">
                            <p class="text-xs text-green-700 mb-0">
                                Tiền lãi suất
                            </p>
                            <p class="text-sm font-medium mb-0">
                                {{ formatCurrency(item.tong_tien_lai_suat) }}
                            </p>
                        </div>
                        <div class="bg-red-50 p-2 rounded">
                            <p class="text-xs text-red-700 mb-0">
                                Tiền tạm giữ
                            </p>
                            <p class="text-sm font-medium mb-0">
                                {{ formatCurrency(item.tong_tien_tam_giu) }}
                            </p>
                        </div>
                        <div class="bg-amber-50 p-2 rounded">
                            <p class="text-xs text-amber-700 mb-0">
                                Tiền khấu trừ
                            </p>
                            <p class="text-sm font-medium mb-0">
                                {{ formatCurrency(item.tong_tien_khau_tru) }}
                            </p>
                        </div>
                    </div>

                    <!-- Bottom info -->
                    <div
                        class="mt-3 bg-gray-50 p-2 rounded-md flex justify-between items-center"
                    >
                        <span class="text-sm font-medium text-green-700">
                            Thanh toán:
                            {{
                                formatCurrency(
                                    item.tong_tien_thanh_toan_con_lai
                                )
                            }}
                        </span>
                        <span class="card-btn">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Pagination for mobile -->
            <div class="flex justify-center mt-0">
                <div class="mobile-pagination-card" v-if="isMobile">
                    <div class="pagination-info">
                        <span class="page-info"
                            >Trang {{ currentPage }} của
                            {{ paginatedItems.last_page }}</span
                        >
                        <span class="record-info"
                            >{{ paginatedItems.from }}-{{
                                paginatedItems.to
                            }}
                            của {{ paginatedItems.total }} bản ghi</span
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
    <!-- Report PDF Modal -->
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
                        @click="closeReportModal"
                    ></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        Chọn loại báo cáo cần tạo:
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
                        <small
                            class="text-muted"
                            v-if="selectedItems.length === 0"
                        >
                            <i class="fas fa-info-circle me-1"></i>
                            Vui lòng chọn ít nhất một mục để tạo báo cáo từ mục
                            đã chọn
                        </small>
                        <small
                            class="text-muted"
                            v-if="filteredItems.length === 0"
                        >
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Không có dữ liệu để tạo báo cáo
                        </small>
                    </div>

                    <!-- Progress bar for report generation -->
                    <div v-if="isGeneratingReport" class="mt-3">
                        <div class="progress" style="height: 6px">
                            <div
                                class="progress-bar progress-bar-striped progress-bar-animated bg-danger"
                                role="progressbar"
                                style="width: 100%"
                            ></div>
                        </div>
                        <small class="text-muted d-block mt-2">
                            <i class="fas fa-clock me-1"></i>
                            {{
                                reportType === "all"
                                    ? "Đang xử lý tất cả dữ liệu..."
                                    : "Đang xử lý mục đã chọn..."
                            }}
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
import * as XLSX from "xlsx";
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
            // เพิ่ม properties สำหรับ Report
            isGeneratingReport: false,
            selectedItems: [], // Array to store selected items for report
            isLoading: false,
            reportType: "",
            phieuList: [],
            allPhieuList: [],
            search: "",
            statusFilter: "all",
            isMobile: window.innerWidth < 768,
            currentPage: 1,
            perPage: 15,
            statusOptions: [
                { code: "all", name: "Tất cả trạng thái" },
                { code: "processing", name: "Đang xử lý" },
                { code: "submitted", name: "Đã nộp kế toán" },
                { code: "paid", name: "Đã thanh toán" },
                { code: "rejected", name: "Từ chối" },
            ],
            paginationClasses: {
                disabled: "opacity-50 cursor-not-allowed disabled",
            },
            columnFilters: {
                ma_giai_ngan: "",
                ngay_thanh_toan: "",
                khach_hang_ca_nhan: "",
                ma_khach_hang_ca_nhan: "",
                khach_hang_doanh_nghiep: "",
                ma_khach_hang_doanh_nghiep: "",
                proposal_number: "",
                payment_installment: "",
            },
            dateRangeActive: false,
            dateRange: {
                startDate: "",
                endDate: "",
                active: false,
            },
            vuDauTuFilter: "",
            sortColumn: null,
            sortDirection: "asc", // 'asc' or 'desc'
            activeFilter: null,
            selectedFile: null,
            uploadProgress: 0,
            importErrors: [],
            isImporting: false,
            processingRecords: false,
            processingProgress: 0,
            processedRecords: 0,
            totalRecords: 0,
            ps: null, // Add this line for PerfectScrollbar instance
            // Export/import related data
            exportModal: null,
            importModal: null,
            // Unique values and selected filter values
            uniqueValues: {
                vu_dau_tu: [],
                loai_thanh_toan: [],
                trang_thai_thanh_toan: [],
            },
            selectedFilterValues: {
                vu_dau_tu: [],
                loai_thanh_toan: [],
                trang_thai_thanh_toan: [],
            },
        };
    },
    computed: {
        isAllSelected() {
            return (
                this.paginatedItems.data.length > 0 &&
                this.selectedItems.length === this.paginatedItems.data.length
            );
        },
        financialTotals() {
            return {
                total_amount: this.filteredItems.reduce(
                    (sum, item) => sum + (parseFloat(item.tong_tien) || 0),
                    0
                ),
                total_hold_amount: this.filteredItems.reduce(
                    (sum, item) =>
                        sum + (parseFloat(item.tong_tien_tam_giu) || 0),
                    0
                ),
                total_deduction_amount: this.filteredItems.reduce(
                    (sum, item) =>
                        sum + (parseFloat(item.tong_tien_khau_tru) || 0),
                    0
                ),
                total_interest_amount: this.filteredItems.reduce(
                    (sum, item) =>
                        sum + (parseFloat(item.tong_tien_lai_suat) || 0),
                    0
                ),
                total_remaining_amount: this.filteredItems.reduce(
                    (sum, item) =>
                        sum +
                        (parseFloat(item.tong_tien_thanh_toan_con_lai) || 0),
                    0
                ),
            };
        },

        statusCounts() {
            // นับจำนวนรายการทั้งหมด
            const total = this.phieuList.length;

            // นับจำนวนรายการตามแต่ละสถานะ
            const counts = {
                submitted: 0,
                processing: 0,
                paid: 0,
                rejected: 0,
            };

            this.phieuList.forEach((item) => {
                if (
                    item.trang_thai_thanh_toan &&
                    counts.hasOwnProperty(item.trang_thai_thanh_toan)
                ) {
                    counts[item.trang_thai_thanh_toan]++;
                }
            });

            return {
                total,
                ...counts,
            };
        },
        filteredItems() {
            let items = this.phieuList.filter((item) => {
                // Global search
                const matchesSearch =
                    this.search === "" ||
                    Object.values(item).some(
                        (val) =>
                            val &&
                            val
                                .toString()
                                .toLowerCase()
                                .includes(this.search.toLowerCase())
                    );

                // Status filter
                const matchesStatus =
                    this.statusFilter === "all" ||
                    item.trang_thai_thanh_toan === this.statusFilter;

                // Date Range filter
                let matchesDateRange = true;
                if (
                    this.dateRange.active &&
                    this.dateRange.startDate &&
                    this.dateRange.endDate
                ) {
                    const itemDate = item.ngay_thanh_toan
                        ? new Date(item.ngay_thanh_toan)
                        : null;
                    const startDate = new Date(this.dateRange.startDate);
                    const endDate = new Date(this.dateRange.endDate);

                    // Set time to end of day for end date for inclusive comparison
                    endDate.setHours(23, 59, 59, 999);

                    if (!itemDate) {
                        matchesDateRange = false;
                    } else {
                        matchesDateRange =
                            itemDate >= startDate && itemDate <= endDate;
                    }
                }
                // Column filters - text inputs
                const matchesColumnFilters = Object.keys(
                    this.columnFilters
                ).every((key) => {
                    if (!this.columnFilters[key]) return true;

                    if (key === "ngay_thanh_toan" && this.columnFilters[key]) {
                        // Date comparison
                        const filterDate = this.formatDateForComparison(
                            this.columnFilters[key]
                        );
                        const itemDate = item[key]
                            ? this.formatDateForComparison(item[key])
                            : null;
                        return itemDate && itemDate === filterDate;
                    }

                    return (
                        item[key] &&
                        item[key]
                            .toString()
                            .toLowerCase()
                            .includes(this.columnFilters[key].toLowerCase())
                    );
                });

                // Selected filters - checkboxes - Fixed error by ensuring we're dealing with arrays
                const matchesSelectedFilters = Object.keys(
                    this.selectedFilterValues
                ).every((key) => {
                    // Make sure selectedFilterValues[key] exists and is an array
                    if (
                        !this.selectedFilterValues[key] ||
                        !Array.isArray(this.selectedFilterValues[key]) ||
                        this.selectedFilterValues[key].length === 0
                    ) {
                        return true;
                    }
                    return (
                        item[key] &&
                        this.selectedFilterValues[key].includes(item[key])
                    );
                });

                return (
                    matchesSearch &&
                    matchesStatus &&
                    matchesDateRange &&
                    matchesColumnFilters &&
                    matchesSelectedFilters
                );
            });
            // Apply sorting if a sort column is selected
            if (this.sortColumn) {
                items = [...items].sort((a, b) => {
                    const valueA = a[this.sortColumn];
                    const valueB = b[this.sortColumn];

                    // Handle numeric values
                    if (!isNaN(valueA) && !isNaN(valueB)) {
                        return this.sortDirection === "asc"
                            ? Number(valueA) - Number(valueB)
                            : Number(valueB) - Number(valueA);
                    }

                    // Handle date values
                    if (this.sortColumn === "ngay_thanh_toan") {
                        const dateA = valueA ? new Date(valueA).getTime() : 0;
                        const dateB = valueB ? new Date(valueB).getTime() : 0;
                        return this.sortDirection === "asc"
                            ? dateA - dateB
                            : dateB - dateA;
                    }

                    // Handle strings (case-insensitive)
                    const strA = valueA ? valueA.toString().toLowerCase() : "";
                    const strB = valueB ? valueB.toString().toLowerCase() : "";

                    if (this.sortDirection === "asc") {
                        return strA.localeCompare(strB);
                    } else {
                        return strB.localeCompare(strA);
                    }
                });
            }

            return items;
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
                data: items,
                current_page: page,
                from,
                last_page: lastPage,
                per_page: perPage,
                to,
                total,
                links: this.generatePaginationLinks(page, lastPage),
            };
        },
    },
    methods: {
        async generateReportSelectedItems() {
            try {
                this.isGeneratingReport = true;
                this.reportType = "selected";

                // Get selected items for report
                const selectedMaGiaiNgan = this.selectedItems.map(
                    (item) => item.ma_giai_ngan
                );

                if (selectedMaGiaiNgan.length === 0) {
                    Swal.fire({
                        icon: "warning",
                        title: "Chưa chọn dữ liệu",
                        text: "Vui lòng chọn ít nhất một mục để tạo báo cáo",
                        confirmButtonText: "Đồng ý",
                    });
                    return;
                }

                await this.generateReport(
                    selectedMaGiaiNgan,
                    "selected_items",
                    selectedMaGiaiNgan.length
                );
            } catch (error) {
                console.error("Error generating selected items report:", error);
                this.handleReportError(error);
            } finally {
                this.isGeneratingReport = false;
                this.reportType = "";
            }
        },

        async generateReportAllData() {
            try {
                this.isGeneratingReport = true;
                this.reportType = "all";

                if (this.filteredItems.length === 0) {
                    Swal.fire({
                        icon: "warning",
                        title: "Không có dữ liệu",
                        text: "Không có dữ liệu để tạo báo cáo",
                        confirmButtonText: "Đồng ý",
                    });
                    return;
                }

                // Show confirmation for large datasets
                if (this.filteredItems.length > 100) {
                    const result = await Swal.fire({
                        icon: "question",
                        title: "Xác nhận tạo báo cáo",
                        text: `Bạn có muốn tạo báo cáo cho ${this.filteredItems.length} bản ghi không? Điều này có thể mất một chút thời gian.`,
                        showCancelButton: true,
                        confirmButtonText: "Tạo báo cáo",
                        cancelButtonText: "Hủy",
                        confirmButtonColor: "#dc3545",
                        cancelButtonColor: "#6c757d",
                    });

                    if (!result.isConfirmed) {
                        return;
                    }
                }

                // Get all filtered items
                const allMaGiaiNgan = this.filteredItems.map(
                    (item) => item.ma_giai_ngan
                );

                await this.generateReport(
                    allMaGiaiNgan,
                    "all_data",
                    this.filteredItems.length
                );
            } catch (error) {
                console.error("Error generating all data report:", error);
                this.handleReportError(error);
            } finally {
                this.isGeneratingReport = false;
                this.reportType = "";
            }
        },

        async generateReport(maGiaiNganList, reportType, totalCount) {
            // Prepare filter parameters
            const filterParams = {
                ma_giai_ngan_list: maGiaiNganList,
                report_type: reportType,
                generated_by: this.store.user?.full_name || "Hệ thống",
                generated_date: new Date().toISOString(),
                total_records: totalCount,
                // Include current filters for context
                search: this.search || "",
                status_filter: this.statusFilter || "all",
                current_filters: {
                    search: this.search,
                    status: this.statusFilter,
                    // Add other filter states as needed
                },
            };

            // Create form data
            const formData = new FormData();
            formData.append("filter_params", JSON.stringify(filterParams));

            // Make API request
            const response = await axios.post(
                "/api/generate-report-phieu-dntt-dv",
                formData,
                {
                    headers: {
                        ...this.store.getAuthHeaders(),
                        "Content-Type": "multipart/form-data",
                    },
                    responseType: "text", // Expect HTML response
                }
            );

            if (response.status === 200) {
                // Open the HTML report in a new window
                const newWindow = window.open("", "_blank");
                if (newWindow) {
                    newWindow.document.write(response.data);
                    newWindow.document.close();

                    // Show success message
                    Swal.fire({
                        icon: "success",
                        title: "Thành công",
                        text: `Đã tạo báo cáo cho ${totalCount} bản ghi`,
                        timer: 2000,
                        showConfirmButton: false,
                        position: "top-end",
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Lỗi",
                        text: "Trình duyệt đã chặn popup. Vui lòng cho phép popup để xem báo cáo",
                        confirmButtonText: "Đồng ý",
                    });
                }

                // Close modal after successful generation
                this.closeReportModal();
            }
        },

        handleReportError(error) {
            let errorMessage = "Đã xảy ra lỗi khi tạo báo cáo";

            if (error.response) {
                if (error.response.status === 401) {
                    errorMessage = "Phiên đăng nhập đã hết hạn";
                    this.handleAuthError();
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

        showReportModal() {
            // Show the report modal using Bootstrap
            const modalElement = document.getElementById("reportPdfModal");
            if (modalElement) {
                // Try jQuery first
                if (window.$ && window.$.fn.modal) {
                    window.$("#reportPdfModal").modal("show");
                } else {
                    // Fallback to Bootstrap 5 JavaScript API
                    import("bootstrap/dist/js/bootstrap.bundle.min.js")
                        .then((bootstrap) => {
                            const modal = new bootstrap.Modal(modalElement);
                            modal.show();
                        })
                        .catch((err) => {
                            console.error("Error loading Bootstrap:", err);
                        });
                }
            }
        },

        closeReportModal() {
            const modalElement = document.getElementById("reportPdfModal");
            if (modalElement) {
                // Try jQuery first
                if (window.$ && window.$.fn.modal) {
                    window.$("#reportPdfModal").modal("hide");
                } else {
                    // Fallback to Bootstrap 5 JavaScript API
                    import("bootstrap/dist/js/bootstrap.bundle.min.js")
                        .then((bootstrap) => {
                            const modal =
                                bootstrap.Modal.getInstance(modalElement);
                            if (modal) {
                                modal.hide();
                            }
                        })
                        .catch((err) => {
                            console.error("Error loading Bootstrap:", err);
                        });
                }
            }
            this.isGeneratingReport = false;
            this.reportType = "";
        },

        toggleSelectAll(event) {
            if (event.target.checked) {
                this.selectedItems = [...this.paginatedItems.data];
            } else {
                this.selectedItems = [];
            }
        },

        // Add these to your methods
        toggleDateFilter() {
            // Show a modal dialog for date filtering
            Swal.fire({
                title: '<i class="fas fa-calendar-alt text-success me-2"></i> Lọc theo ngày thanh toán',
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
                        this.dateRangeActive = true;
                        this.currentPage = 1;
                        // ตรงนี้คือจุดที่ควรจะเพิ่ม
                        this.fetchPhieuData(1); // เพิ่มบรรทัดนี้เพื่อให้ข้อมูลถูกอัพเดทหลังจากเลือกวันที่
                    } else {
                        // If both dates are empty, reset the filter
                        this.resetDateRange();
                    }
                }
            });
        },

        resetDateRange() {
            this.dateRange.startDate = "";
            this.dateRange.endDate = "";
            this.dateRange.active = false;
            this.dateRangeActive = false;
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
                title: "Đã xóa bộ lọc ngày thanh toán",
            });
        },

        showActionsMenu() {
            Swal.fire({
                title: "Actions",
                html: `
        <div class="d-grid gap-2">
            <button onclick="exportToExcel()" class="btn btn-outline-success mb-2">
                <i class="fas fa-file-excel text-success me-2"></i> Export to Excel
            </button>
            <button onclick="showReportPdf()" class="btn btn-outline-danger mb-2">
                <i class="fas fa-file-pdf text-danger me-2"></i> Report PDF
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
                    window.showReportPdf = () => {
                        Swal.close();
                        this.showReportModal();
                    };
                },
                willClose: () => {
                    // Clean up global functions
                    delete window.exportToExcel;
                    delete window.showReportPdf;
                },
            });
        },
        applyVuDauTuFilter() {
            this.currentPage = 1;
            // Ensure we're always working with arrays
            if (this.vuDauTuFilter) {
                // Make sure this is an array
                this.selectedFilterValues.vu_dau_tu = [this.vuDauTuFilter];
            } else {
                // Initialize as empty array
                this.selectedFilterValues.vu_dau_tu = [];
            }
            this.fetchPhieuData(1);
        },
        toggleSort(column) {
            // If clicking on the same column, toggle direction
            if (this.sortColumn === column) {
                this.sortDirection =
                    this.sortDirection === "asc" ? "desc" : "asc";
            } else {
                // New column, set to ascending by default
                this.sortColumn = column;
                this.sortDirection = "asc";
            }
        },
        getSortIndicator(column) {
            if (this.sortColumn !== column) return "";
            return this.sortDirection === "asc" ? "↑" : "↓";
        },
        // Save state to localStorage before navigating to detail
        saveFilterState() {
            const filterState = {
                search: this.search,
                statusFilter: this.statusFilter,
                columnFilters: this.columnFilters,
                selectedFilterValues: this.selectedFilterValues,
                currentPage: this.currentPage,
            };
            localStorage.setItem(
                "phieuDichvu_filterState",
                JSON.stringify(filterState)
            );
        },

        // Load state from localStorage when mounting the component
        loadFilterState() {
            const savedState = localStorage.getItem("phieuDichvu_filterState");
            if (savedState) {
                const filterState = JSON.parse(savedState);
                this.search = filterState.search;
                this.statusFilter = filterState.statusFilter;
                this.columnFilters = filterState.columnFilters;
                this.selectedFilterValues = filterState.selectedFilterValues;
                this.currentPage = filterState.currentPage;
            }
        },
        formatDate(date) {
            if (!date) return "";
            const d = new Date(date);
            const day = d.getDate().toString().padStart(2, "0");
            const month = (d.getMonth() + 1).toString().padStart(2, "0");
            const year = d.getFullYear();
            return `${day}/${month}/${year}`;
        },
        formatStatus(status) {
            if (!status) return "";

            const statusMap = {
                processing: "Đang xử lý",
                submitted: "Đã nộp kế toán",

                paid: "Đã thanh toán",
                rejected: "Từ chối",
            };

            return statusMap[status] || status;
        },
        formatCurrency(value) {
            if (!value) return "";
            return new Intl.NumberFormat("vi-VN", {
                maximumFractionDigits: 0,
            }).format(value);
        },
        pageChanged(page) {
            this.currentPage = page;
            // this.fetchPhieuData(page);
        },
        generatePaginationLinks(currentPage, lastPage) {
            // Generate pagination links for Bootstrap5Pagination
            const links = [];

            // Previous link
            links.push({
                url: currentPage > 1 ? "#" : null,
                label: "&laquo; Previous",
                active: false,
            });

            // Page links
            for (let i = 1; i <= lastPage; i++) {
                links.push({
                    url: "#",
                    label: i.toString(),
                    active: i === currentPage,
                });
            }

            // Next link
            links.push({
                url: currentPage < lastPage ? "#" : null,
                label: "Next &raquo;",
                active: false,
            });

            return links;
        },
        async fetchPhieuData(page = null) {
            // If page is not provided, use the current page (for when returning from details)
            if (page === null) {
                page = this.currentPage || 1;
            }

            this.isLoading = true;
            try {
                const params = {
                    page: page,
                    per_page: this.perPage,
                    search: this.search || null,
                    status:
                        this.statusFilter !== "all" ? this.statusFilter : null,
                };

                // Add date range filter params
                if (
                    this.dateRange.active &&
                    this.dateRange.startDate &&
                    this.dateRange.endDate
                ) {
                    params.start_date = this.dateRange.startDate;
                    params.end_date = this.dateRange.endDate;
                }

                // Add column filters to params
                Object.keys(this.columnFilters).forEach((key) => {
                    if (this.columnFilters[key]) {
                        params[`filter_${key}`] = this.columnFilters[key];
                    }
                });

                // Add selected filter values for dropdown filters
                Object.keys(this.selectedFilterValues).forEach((key) => {
                    if (
                        this.selectedFilterValues[key] &&
                        this.selectedFilterValues[key].length > 0
                    ) {
                        params[`filter_${key}`] =
                            this.selectedFilterValues[key].join(",");
                    }
                });

                const headers = this.store.getAuthHeaders();
                const response = await axios.get(
                    "/api/tai-chinh/phieu-de-nghi-thanh-toan-dv",
                    {
                        headers: headers,
                        params: params,
                    }
                );

                if (response.data && response.data.success) {
                    this.phieuList = response.data.data;

                    // Handle pagination data safely
                    if (response.data.pagination) {
                        // If pagination object exists, use it
                        if (page !== null) {
                            this.currentPage =
                                response.data.pagination.current_page || page;
                        }
                        this.totalPages =
                            response.data.pagination.last_page || 1;
                        this.totalRecords =
                            response.data.pagination.total ||
                            this.phieuList.length;
                    } else {
                        // If no pagination object, handle as single page
                        if (page !== null) {
                            this.currentPage = page;
                        }
                        this.totalPages = 1;
                        this.totalRecords = this.phieuList.length;
                    }

                    // Store the entire collection for operations that need all data
                    if (page === 1 || this.allPhieuList.length === 0) {
                        this.allPhieuList = [...this.phieuList];
                    }

                    // Set unique values for dropdown filters if available
                    if (response.data.unique_filters) {
                        this.uniqueValues = {
                            ...this.uniqueValues,
                            ...response.data.unique_filters,
                        };
                    } else {
                        // If server doesn't provide unique values, extract them from data
                        this.updateAllUniqueValues();
                    }

                    // Update scrollbar after data is loaded
                    this.$nextTick(() => {
                        this.updateScrollbar();
                    });
                } else {
                    console.error("Failed to fetch payment request data");
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Failed to fetch payment request data",
                    });
                }
            } catch (error) {
                console.error("Error fetching payment request data:", error);
                if (error.response?.status === 401) {
                    this.handleAuthError();
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "An error occurred while fetching data",
                    });
                }
            } finally {
                this.isLoading = false;
            }
        },

        // Add helper method to update all unique values
        updateAllUniqueValues() {
            // Update unique values for all dropdown filters
            const dropdownColumns = [
                "vu_dau_tu",
                "loai_thanh_toan",
                "trang_thai_thanh_toan",
            ];

            dropdownColumns.forEach((column) => {
                const uniqueSet = new Set();
                this.phieuList.forEach((item) => {
                    if (item[column]) {
                        uniqueSet.add(item[column]);
                    }
                });
                this.uniqueValues[column] = Array.from(uniqueSet).sort();
            });
        },
        resetAllFilters() {
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

            // Reset date range filter
            this.dateRange.startDate = "";
            this.dateRange.endDate = "";
            this.dateRange.active = false;
            this.dateRangeActive = false;

            // Reset active filter (close any open filter dropdown)
            this.activeFilter = null;

            // Reset to first page
            this.currentPage = 1;
            // Clear saved filter state in localStorage
            localStorage.removeItem("phieuDichvu_filterState");
            this.fetchPhieuData(1);
        },
        async viewDetails(item) {
            try {
                this.isLoading = true;
                // Save filter state before navigating
                this.saveFilterState();
                // Navigate to detail page
                this.$router.push({
                    name: "Details_Phieudenghithanhtoandichvu",
                    params: { id: item.ma_giai_ngan },
                });
            } catch (error) {
                console.error("Error navigating to details:", error);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Could not open details page",
                });
            } finally {
                this.isLoading = false;
            }
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

            // If it's one of our dropdown columns and we're opening the filter, ensure unique values are populated
            if (
                (column === "vu_dau_tu" ||
                    column === "loai_thanh_toan" ||
                    column === "trang_thai_thanh_toan") &&
                this.activeFilter === column
            ) {
                this.updateUniqueValues(column);
            }
        },

        updateUniqueValues(column) {
            // Get unique values for dropdown columns
            if (
                column === "vu_dau_tu" ||
                column === "loai_thanh_toan" ||
                column === "trang_thai_thanh_toan"
            ) {
                // Extract unique values from all items
                const uniqueSet = new Set();
                this.allPhieuList.forEach((item) => {
                    if (item[column]) {
                        uniqueSet.add(item[column]);
                    }
                });
                this.uniqueValues[column] = Array.from(uniqueSet).sort();
            }
        },

        resetFilter(column) {
            if (
                column === "vu_dau_tu" ||
                column === "loai_thanh_toan" ||
                column === "trang_thai_thanh_toan"
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
            this.fetchPhieuData(1); // Fetch data with the new filter applied
        },

        // Export methods
        showExportModal() {
            try {
                // Initialize and show Bootstrap modal
                import("bootstrap/dist/js/bootstrap.bundle.min.js")
                    .then((bootstrap) => {
                        const Modal = bootstrap.Modal;
                        const modalElement =
                            document.getElementById("exportModal");

                        if (modalElement) {
                            this.exportModal = new Modal(modalElement);
                            this.exportModal.show();
                        } else {
                            console.error("Export modal element not found");
                        }
                    })
                    .catch((err) => {
                        console.error("Failed to load Bootstrap:", err);

                        // Fallback with direct DOM manipulation
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
                console.error("Error showing export modal:", error);
            }
        },

        exportToExcelCurrentPage() {
            if (this.paginatedItems.data.length > 0) {
                this.generateExcel(
                    this.paginatedItems.data,
                    "payment_requests_current_page"
                );
                this.closeExportModal();
            } else {
                Swal.fire({
                    icon: "info",
                    title: "No Data",
                    text: "No data available to export",
                });
            }
        },

        exportToExcelAllPages() {
            if (this.filteredItems && this.filteredItems.length > 0) {
                this.generateExcel(this.filteredItems, "payment_requests_all");
                this.closeExportModal();
            } else {
                Swal.fire({
                    icon: "info",
                    title: "No Data",
                    text: "No data available to export",
                });
            }
        },

        generateExcel(data, filename) {
            try {
                // Process data for export - format dates and statuses
                const exportData = data.map((item) => {
                    return {
                        "Mã giải ngân": item.ma_giai_ngan || "",
                        "Vụ đầu tư": item.vu_dau_tu || "",
                        "Loại thanh toán": item.loai_thanh_toan || "",
                        "Trạng thái thanh toán":
                            this.formatStatus(item.trang_thai_thanh_toan) || "",
                        "Ngày thanh toán": item.ngay_thanh_toan
                            ? this.formatDate(item.ngay_thanh_toan)
                            : "",
                        "Khách hàng cá nhân": item.khach_hang_ca_nhan || "",
                        "Mã KH cá nhân": item.ma_khach_hang_ca_nhan || "",
                        "Khách hàng doanh nghiệp":
                            item.khach_hang_doanh_nghiep || "",
                        "Mã KH doanh nghiệp":
                            item.ma_khach_hang_doanh_nghiep || "",
                        "Tổng tiền": item.tong_tien || 0,
                        "Tổng tiền tạm giữ": item.tong_tien_tam_giu || 0,
                        "Tổng tiền khấu trừ": item.tong_tien_khau_tru || 0,
                        "Tổng tiền lãi suất": item.tong_tien_lai_suat || 0,
                        "Tổng tiền thanh toán còn lại":
                            item.tong_tien_thanh_toan_con_lai || 0,
                        "Số tờ trình": item.proposal_number || "",
                        "Đợt thanh toán": item.payment_installment || "",
                    };
                });

                // Create worksheet
                const ws = XLSX.utils.json_to_sheet(exportData);

                // Set column widths
                const wscols = [
                    { wch: 15 }, // Mã giải ngân
                    { wch: 15 }, // Vụ đầu tư
                    { wch: 15 }, // Loại thanh toán
                    { wch: 20 }, // Trạng thái thanh toán
                    { wch: 15 }, // Ngày thanh toán
                    { wch: 25 }, // Khách hàng cá nhân
                    { wch: 15 }, // Mã KH cá nhân
                    { wch: 25 }, // Khách hàng doanh nghiệp
                    { wch: 15 }, // Mã KH doanh nghiệp
                    { wch: 15 }, // Tổng tiền
                    { wch: 15 }, // Tổng tiền tạm giữ
                    { wch: 15 }, // Tổng tiền khấu trừ
                    { wch: 15 }, // Tổng tiền lãi suất
                    { wch: 15 }, // Tổng tiền thanh toán còn lại
                    { wch: 15 }, // Số tờ trình
                    { wch: 15 }, // Đợt thanh toán
                ];
                ws["!cols"] = wscols;

                // Create workbook and add worksheet
                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, "Payment Requests");

                // Generate Excel file
                XLSX.writeFile(
                    wb,
                    `${filename}_${new Date().toISOString().split("T")[0]}.xlsx`
                );
            } catch (error) {
                console.error("Error generating Excel:", error);
                Swal.fire({
                    icon: "error",
                    title: "Export Error",
                    text: "An error occurred while generating the Excel file",
                });
            }
        },

        closeExportModal() {
            try {
                if (this.exportModal) {
                    this.exportModal.hide();
                } else {
                    // Fallback if Bootstrap modal instance is not available
                    const modalElement = document.getElementById("exportModal");
                    if (modalElement) {
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
            } catch (error) {
                console.error("Error closing export modal:", error);
            }
        },

        statusClass(status) {
            switch (status) {
                case "submitted":
                    return "text-warning";
                case "processing":
                    return "text-primary";
                case "paid":
                    return "text-success";
                case "rejected":
                    return "text-danger";
                default:
                    return "text-secondary";
            }
        },

        statusIcons(status) {
            switch (status) {
                case "submitted":
                    return "fas fa-clock text-warning";
                case "processing":
                    return "fas fa-spinner text-primary";
                case "paid":
                    return "fas fa-check-circle text-success";
                case "rejected":
                    return "fas fa-times-circle text-danger";
                default:
                    return "";
            }
        },
        formatDateForComparison(date) {
            if (!date) return null;

            const d = new Date(date);
            const year = d.getFullYear();
            const month = (d.getMonth() + 1).toString().padStart(2, "0");
            const day = d.getDate().toString().padStart(2, "0");

            return `${year}-${month}-${day}`;
        },
        // Add these new methods for PerfectScrollbar
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
                this.updateScrollbar();
            },
            deep: true,
        },
    },
    mounted() {
        // Add event listener for modal close button
        const closeBtn = document.querySelector("#reportPdfModal .btn-close");
        if (closeBtn) {
            closeBtn.addEventListener("click", this.closeReportModal);
        }

        // Add event listener for backdrop click
        const modal = document.getElementById("reportPdfModal");
        if (modal) {
            modal.addEventListener("click", (e) => {
                if (e.target === modal) {
                    this.closeReportModal();
                }
            });
        }
        // Load saved filter state if available
        this.loadFilterState();
        this.fetchPhieuData();

        // Add PerfectScrollbar initialization
        this.initPerfectScrollbar();

        // Initialize responsive behavior
        window.addEventListener("resize", () => {
            this.isMobile = window.innerWidth < 768;
        });
    },
    updated() {
        // Update scrollbar after component updates
        this.updateScrollbar();
    },
    beforeUnmount() {
        // Clean up event listeners
        const closeBtn = document.querySelector("#reportPdfModal .btn-close");
        if (closeBtn) {
            closeBtn.removeEventListener("click", this.closeReportModal);
        }
        window.removeEventListener("resize", () => {
            this.isMobile = window.innerWidth < 768;
        });

        // Add PerfectScrollbar cleanup
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

/* Table container with fixed header */
.table-container-wrapper {
    position: relative;
    width: 100%;
}

.table-scroll-container {
    position: relative;
    max-height: calc(100vh - 350px);
    min-height: 280px; /* Add this line to set the minimum height */
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
.desktop-row,
.cursor-pointer {
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
}

.desktop-row:hover,
.cursor-pointer:hover {
    background-color: #f0f9f0;
    box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);
}

.table-auto td {
    padding: 0.75rem;
    border-bottom: 1px solid #e5e7eb;
    vertical-align: middle;
    font-size: 14px;
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

/* Filter dropdown styling */
.absolute.mt-1.bg-white.p-2.rounded.shadow-lg.z-10 {
    position: absolute;
    top: calc(100% + 5px);
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

    .absolute.mt-1.bg-white.p-2.rounded.shadow-lg.z-10 {
        width: 90vw;
        max-width: 90vw;
        left: 0;
        right: 0;
        margin-left: auto;
        margin-right: auto;
    }
}

/* Status badge styling */
.status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
    white-space: nowrap;
}

/* Center align status column Trạng thái thanh toán */
td:nth-child(4) {
    text-align: center;
}

/* Status colors with soft backgrounds */
.text-warning {
    background-color: rgba(255, 193, 7, 0.15);
    color: #8a6d3b;
}

.text-primary {
    background-color: rgba(13, 110, 253, 0.15);
    color: #0d6efd;
}

.text-success {
    background-color: rgba(25, 135, 84, 0.15);
    color: #198754;
}

.text-danger {
    background-color: rgba(220, 53, 69, 0.15);
    color: #dc3545;
}

.text-secondary {
    background-color: rgba(108, 117, 125, 0.15);
    color: #6c757d;
}
/* Keep all your existing styles below this line */
/* ... */

/* Status display styling for mobile */
.status-display {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-width: 85px;
    min-height: 100%;
    padding: 10px;
    border-radius: 8px;
    margin-right: 15px;
}

/* Center the icon and text vertically */
.flex-shrink-0.flex.items-center.justify-center.mr-4.me-4 {
    display: flex;
    align-items: center;
    justify-content: center;
    align-self: stretch;
}

/* Status colors for mobile display */
.status-display i.text-warning {
    color: #ffc107;
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.status-display i.text-primary {
    color: #0d6efd;
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.status-display i.text-success {
    color: #198754;
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.status-display i.text-danger {
    color: #dc3545;
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

/* Mobile card optimization */
@media (max-width: 768px) {
    .card {
        padding: 1rem;
    }

    .flex {
        align-items: stretch;
    }

    .status-display {
        padding: 8px;
        min-width: 70px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
    }

    .text-3xl {
        font-size: 1.5rem;
        line-height: 1.2;
    }

    /* Make the status text centered and properly sized */
    .status-display span {
        text-align: center;
        font-size: 0.85rem;
        font-weight: 500;
        white-space: nowrap;
    }
    .flex-1.justify-items-start {
        align-items: center;
        justify-content: center;
        font-size: 12px;
    }
}
/* Table header styling for sorting */
.th-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
}

.th-title {
    display: flex;
    align-items: center;
    cursor: pointer;
    user-select: none;
    flex-grow: 1;
    padding-right: 8px;
}

.th-title:hover {
    color: #198754;
}

.sort-indicator {
    margin-left: 4px;
    font-weight: bold;
}

/* Ensure filter button remains separate */
/* .filter-btn {
    background: none;
    border: none;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
} */

.filter-btn:hover {
    color: #198754;
}

/* Totals cards styling - Updated for single row layout */
.totals-container {
    display: flex;
    flex-wrap: nowrap; /* Changed from wrap to nowrap to force single row */
    gap: 0.75rem; /* Reduced gap for tighter spacing */
    margin-bottom: 0.5rem;
    justify-content: space-between;
    background-color: white;
    padding: 0.75rem; /* Reduced padding */
    border-radius: 1rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    overflow-x: auto; /* Allow horizontal scrolling if needed */
}

.total-card {
    background-color: #ffffff;
    flex: 1;
    min-width: 170px; /* Reduced from 210px to fit better */
    max-width: 20%; /* Limit maximum width to force equal sizing */
    display: flex;
    align-items: center;
    padding: 0.75rem; /* Reduced padding */
    border-radius: 0.75rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1),
        0 2px 4px -1px rgba(0, 0, 0, 0.06);
    transition: transform 0.2s, box-shadow 0.2s;
    overflow: hidden;
    position: relative;
}

.total-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px; /* Reduced from 48px */
    height: 40px; /* Reduced from 48px */
    border-radius: 50%;
    margin-right: 0.75rem; /* Reduced spacing */
    font-size: 1.25rem; /* Smaller icon */
    flex-shrink: 0;
}

.total-label {
    font-size: 0.75rem; /* Smaller text */
    font-weight: 500;
    color: #6b7280;
    margin-bottom: 0.15rem; /* Reduced margin */
    white-space: nowrap;
}

.total-value {
    font-size: 0.9rem; /* Smaller font */
    font-weight: 700;
    line-height: 1.2;
}
/* Card specific colors */
.total-amount {
    background-color: rgba(59, 130, 246, 0.1);
    border-left: 4px solid #3b82f6;
}

.total-amount .total-icon {
    color: #3b82f6;
    background-color: rgba(59, 130, 246, 0.2);
}

.total-amount .total-value {
    color: #1e40af;
}

.total-hold {
    background-color: rgba(16, 185, 129, 0.1);
    border-left: 4px solid #10b981;
}

.total-hold .total-icon {
    color: #10b981;
    background-color: rgba(16, 185, 129, 0.2);
}

.total-hold .total-value {
    color: #065f46;
}

.total-deduction {
    background-color: rgba(239, 68, 68, 0.1);
    border-left: 4px solid #ef4444;
}

.total-deduction .total-icon {
    color: #ef4444;
    background-color: rgba(239, 68, 68, 0.2);
}

.total-deduction .total-value {
    color: #b91c1c;
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

.total-remaining {
    background-color: rgba(139, 92, 246, 0.1);
    border-left: 4px solid #8b5cf6;
}

.total-remaining .total-icon {
    color: #8b5cf6;
    background-color: rgba(139, 92, 246, 0.2);
}

.total-remaining .total-value {
    color: #5b21b6;
}

/* Adjusted mobile responsive styles */
@media (max-width: 992px) {
    .totals-container {
        flex-wrap: wrap; /* Allow wrapping on medium screens */
    }

    .total-card {
        min-width: calc(33.33% - 0.5rem); /* Show 3 cards per row */
        flex: 0 0 calc(33.33% - 0.5rem);
        max-width: calc(33.33% - 0.5rem);
    }
}

@media (max-width: 768px) {
    .total-card {
        min-width: calc(50% - 0.5rem); /* Show 2 cards per row */
        flex: 0 0 calc(50% - 0.5rem);
        max-width: calc(50% - 0.5rem);
    }
}

@media (max-width: 480px) {
    .totals-container {
        flex-direction: column;
    }

    .total-card {
        width: 100%;
        min-width: 100%;
        max-width: 100%;
    }
}

/* Card styling enhancements */
.card-container .card {
    overflow: hidden;
    border: 1px solid #f0f0f0;
    transition: all 0.3s ease;
}

.card-container .card:active {
    transform: scale(0.98);
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}

.card-btn {
    color: #10b981;
    font-size: 0.75rem;
}

.customer-info {
    background-color: rgba(243, 244, 246, 0.7);
}

/* Background colors for financial details */
.bg-blue-50 {
    background-color: #eff6ff;
}
.text-blue-700 {
    color: #1d4ed8;
}

.bg-green-50 {
    background-color: #ecfdf5;
}
.text-green-700 {
    color: #047857;
}

.bg-red-50 {
    background-color: #fef2f2;
}
.text-red-700 {
    color: #b91c1c;
}

.bg-amber-50 {
    background-color: #fffbeb;
}
.text-amber-700 {
    color: #b45309;
}

/* Mobile pagination adjustments */
@media (max-width: 768px) {
    .pagination-card
        .page-item:nth-child(n + 5):not(:last-child):not(:nth-last-child(2)) {
        display: none;
    }
    .page-item:nth-child(n + 5):not(:last-child):not(:nth-last-child(2)) {
        display: none;
    }
    /* Fix reset filter button position for mobile */
    .mobile-controls .reset-all-filters-btn {
        position: relative;
        right: auto;
        top: auto;
        z-index: 5;
        width: 28px;
        height: 28px;
    }
}

/* Status select styling */
.status-select,
.filter-select {
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

.status-select:hover,
.filter-select:hover {
    border-color: #10b981;
}

.status-select:focus,
.filter-select:focus {
    outline: none;
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
}

/* Filter section styling */
.filter-section {
    min-width: 200px;
}

/* Mobile responsive styles */
@media (max-width: 768px) {
    .status-filter,
    .filter-section {
        min-width: 100%;
        margin-bottom: 1rem;
    }

    /* ...existing mobile styles... */
}

/* Search and Filter Container Layout */
.search-filter-container {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    width: 100%;
}

.search-container {
    flex: 0.9; /* Takes 90% of the space */
    position: relative;
}

.search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
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

.reset-all-filters-btn-mobile {
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

.reset-all-filters-btn-mobile:hover {
    background: #10b981;
    transform: rotate(30deg);
}

/* Mobile controls styling */
.mobile-controls {
    background-color: #fff;
    border-radius: 0.75rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-bottom: 1rem;
    padding: 0.75rem;
}

.btn-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #f3f4f6;
    border: 1px solid #e5e7eb;
    color: #6b7280;
    transition: all 0.2s ease;
}

.btn-icon:hover {
    background-color: #e5e7eb;
    color: #10b981;
}

/* Date filter modal customization */
.date-filter-popup {
    border-radius: 1rem;
    padding: 1rem;
}

.date-input-groups {
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

.swal2-actions-custom-gap {
    gap: 10px !important;
}

/* Mobile optimizations */
@media (max-width: 480px) {
    .date-filter-modal .swal2-popup {
        padding: 1rem;
        width: 90% !important;
    }
}

/* Add decorative top border to table card */
.card::before {
    border-radius: 1.5rem 1.5rem 0 0;
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #10b981 0%, #059669 50%, #10b981 100%);
    z-index: 1;
}

/* Mobile Pagination - Simple & Clean */
.mobile-pagination-card {
    background: white;
    border-radius: 12px;
    padding: 16px;
    margin-top: 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border: 1px solid #e5e7eb;
    width: 100%;
    max-width: 400px;
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

.record-info {
    font-size: 12px;
    color: #9ca3af;
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
    font-size: 14px;
}

.page-btn:hover:not(:disabled) {
    border-color: #10b981;
    color: #10b981;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
}

.page-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.page-btn:active:not(:disabled) {
    transform: translateY(0);
}

.current-page {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    padding: 8px 16px;
    border-radius: 8px;
    font-weight: 600;
    margin: 0 8px;
    min-width: 40px;
    text-align: center;
    box-shadow: 0 2px 4px rgba(16, 185, 129, 0.25);
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
    min-width: 60px;
    text-align: center;
    font-size: 14px;
}

.quick-jump select:focus {
    outline: none;
    border-color: #10b981;
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.1);
}

/* Responsive adjustments */
@media (max-width: 480px) {
    .mobile-pagination-card {
        padding: 12px;
        margin-top: 5px;
    }

    .pagination-info {
        flex-direction: column;
        gap: 4px;
        text-align: center;
        margin-bottom: 12px;
    }

    .page-info {
        font-size: 13px;
    }

    .record-info {
        font-size: 11px;
    }

    .page-btn {
        width: 36px;
        height: 36px;
        font-size: 12px;
    }

    .current-page {
        padding: 6px 12px;
        margin: 0 4px;
        font-size: 14px;
    }

    .quick-jump {
        padding-top: 8px;
        font-size: 12px;
    }

    .quick-jump select {
        padding: 3px 6px;
        font-size: 12px;
        min-width: 50px;
    }
}

/* Animation for page transitions */
@keyframes pageTransition {
    0% {
        opacity: 0;
        transform: translateY(10px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

.mobile-pagination-card {
    animation: pageTransition 0.3s ease-out;
}

/* Ensure desktop pagination is hidden on mobile */
@media (max-width: 768px) {
    .pagination-card:not(.mobile-pagination-card) {
        display: none !important;
    }
}

/* Ensure mobile pagination is hidden on desktop */
@media (min-width: 769px) {
    .mobile-pagination-card {
        display: none !important;
    }
}
</style>
