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
                        >
                            {{ option.name }}
                        </option>
                    </select>
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
                                    @click.prevent="importData"
                                >
                                    <i
                                        class="fas fa-upload text-primary me-2"
                                    ></i>
                                    Import Data
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Controls -->
        <div
            class="mobile-controls p-3 bg-white rounded-lg shadow-sm mb-3"
            v-if="isMobile"
        >
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
                        placeholder="Tìm kiếm phiếu..."
                        class="w-full pl-10 pr-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm"
                    />
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
                    <div class="overflow-x-auto">
                        <table class="table-auto w-full">
                            <thead>
                                <tr>
                                    <th>
                                        Mã số phiếu
                                        <button
                                            @click="toggleFilter('ma_so_phieu')"
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
                                                activeFilter === 'ma_so_phieu'
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
                                            <div class="flex justify-between">
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
                                    <th>
                                        Cán bộ nông vụ
                                        <button
                                            @click="
                                                toggleFilter('can_bo_nong_vu')
                                            "
                                            class="filter-btn"
                                        >
                                            <i
                                                class="fas fa-filter"
                                                :class="{
                                                    'text-green-500':
                                                        columnFilters.can_bo_nong_vu,
                                                }"
                                            ></i>
                                        </button>
                                        <div
                                            v-if="
                                                activeFilter ===
                                                'can_bo_nong_vu'
                                            "
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                        >
                                            <input
                                                type="text"
                                                v-model="
                                                    columnFilters.can_bo_nong_vu
                                                "
                                                class="form-control mb-2"
                                                placeholder="Lọc theo cán bộ..."
                                            />
                                            <div class="flex justify-between">
                                                <button
                                                    @click="
                                                        resetFilter(
                                                            'can_bo_nong_vu'
                                                        )
                                                    "
                                                    class="btn btn-sm btn-light"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="
                                                        applyFilter(
                                                            'can_bo_nong_vu'
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
                                        Vụ đầu tư
                                        <button
                                            @click="toggleFilter('vu_dau_tu')"
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
                                        <div
                                            v-if="activeFilter === 'vu_dau_tu'"
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                        >
                                            <input
                                                type="text"
                                                v-model="
                                                    columnFilters.vu_dau_tu
                                                "
                                                class="form-control mb-2"
                                                placeholder="Lọc theo vụ..."
                                            />
                                            <div class="flex justify-between">
                                                <button
                                                    @click="
                                                        resetFilter('vu_dau_tu')
                                                    "
                                                    class="btn btn-sm btn-light"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="
                                                        applyFilter('vu_dau_tu')
                                                    "
                                                    class="btn btn-sm btn-success"
                                                >
                                                    Apply
                                                </button>
                                            </div>
                                        </div>
                                    </th>
                                    <th>
                                        Tên phiếu
                                        <button
                                            @click="toggleFilter('ten_phieu')"
                                            class="filter-btn"
                                        >
                                            <i
                                                class="fas fa-filter"
                                                :class="{
                                                    'text-green-500':
                                                        columnFilters.ten_phieu,
                                                }"
                                            ></i>
                                        </button>
                                        <div
                                            v-if="activeFilter === 'ten_phieu'"
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                        >
                                            <input
                                                type="text"
                                                v-model="
                                                    columnFilters.ten_phieu
                                                "
                                                class="form-control mb-2"
                                                placeholder="Lọc theo tên..."
                                            />
                                            <div class="flex justify-between">
                                                <button
                                                    @click="
                                                        resetFilter('ten_phieu')
                                                    "
                                                    class="btn btn-sm btn-light"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="
                                                        applyFilter('ten_phieu')
                                                    "
                                                    class="btn btn-sm btn-success"
                                                >
                                                    Apply
                                                </button>
                                            </div>
                                        </div>
                                    </th>
                                    <th>
                                        Hợp đồng đầu tư mía bên giao
                                        <button
                                            @click="
                                                toggleFilter(
                                                    'hop_dong_dau_tu_mia_ben_giao'
                                                )
                                            "
                                            class="filter-btn"
                                        >
                                            <i
                                                class="fas fa-filter"
                                                :class="{
                                                    'text-green-500':
                                                        columnFilters.hop_dong_dau_tu_mia_ben_giao,
                                                }"
                                            ></i>
                                        </button>
                                        <div
                                            v-if="
                                                activeFilter ===
                                                'hop_dong_dau_tu_mia_ben_giao'
                                            "
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                        >
                                            <input
                                                type="text"
                                                v-model="
                                                    columnFilters.hop_dong_dau_tu_mia_ben_giao
                                                "
                                                class="form-control mb-2"
                                                placeholder="Lọc theo hợp đồng..."
                                            />
                                            <div class="flex justify-between">
                                                <button
                                                    @click="
                                                        resetFilter(
                                                            'hop_dong_dau_tu_mia_ben_giao'
                                                        )
                                                    "
                                                    class="btn btn-sm btn-light"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="
                                                        applyFilter(
                                                            'hop_dong_dau_tu_mia_ben_giao'
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
                                        Hợp đồng đầu tư mía bên nhận
                                        <button
                                            @click="
                                                toggleFilter(
                                                    'hop_dong_dau_tu_mia_ben_nhan'
                                                )
                                            "
                                            class="filter-btn"
                                        >
                                            <i
                                                class="fas fa-filter"
                                                :class="{
                                                    'text-green-500':
                                                        columnFilters.hop_dong_dau_tu_mia_ben_nhan,
                                                }"
                                            ></i>
                                        </button>
                                        <div
                                            v-if="
                                                activeFilter ===
                                                'hop_dong_dau_tu_mia_ben_nhan'
                                            "
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                        >
                                            <input
                                                type="text"
                                                v-model="
                                                    columnFilters.hop_dong_dau_tu_mia_ben_nhan
                                                "
                                                class="form-control mb-2"
                                                placeholder="Lọc theo hợp đồng..."
                                            />
                                            <div class="flex justify-between">
                                                <button
                                                    @click="
                                                        resetFilter(
                                                            'hop_dong_dau_tu_mia_ben_nhan'
                                                        )
                                                    "
                                                    class="btn btn-sm btn-light"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="
                                                        applyFilter(
                                                            'hop_dong_dau_tu_mia_ben_nhan'
                                                        )
                                                    "
                                                    class="btn btn-sm btn-success"
                                                >
                                                    Apply
                                                </button>
                                            </div>
                                        </div>
                                    </th>
                                    <th>Tổng thực nhận</th>
                                    <th>Tổng tiền</th>
                                    <th>
                                        Người giao hồ sơ
                                        <button
                                            @click="
                                                toggleFilter('nguoi_giao_ho_so')
                                            "
                                            class="filter-btn"
                                        >
                                            <i
                                                class="fas fa-filter"
                                                :class="{
                                                    'text-green-500':
                                                        columnFilters.nguoi_giao_ho_so,
                                                }"
                                            ></i>
                                        </button>
                                        <div
                                            v-if="
                                                activeFilter ===
                                                'nguoi_giao_ho_so'
                                            "
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                        >
                                            <input
                                                type="text"
                                                v-model="
                                                    columnFilters.nguoi_giao_ho_so
                                                "
                                                class="form-control mb-2"
                                                placeholder="Lọc theo người giao..."
                                            />
                                            <div class="flex justify-between">
                                                <button
                                                    @click="
                                                        resetFilter(
                                                            'nguoi_giao_ho_so'
                                                        )
                                                    "
                                                    class="btn btn-sm btn-light"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="
                                                        applyFilter(
                                                            'nguoi_giao_ho_so'
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
                                        Người nhận hồ sơ
                                        <button
                                            @click="
                                                toggleFilter('nguoi_nhan_ho_so')
                                            "
                                            class="filter-btn"
                                        >
                                            <i
                                                class="fas fa-filter"
                                                :class="{
                                                    'text-green-500':
                                                        columnFilters.nguoi_nhan_ho_so,
                                                }"
                                            ></i>
                                        </button>
                                        <div
                                            v-if="
                                                activeFilter ===
                                                'nguoi_nhan_ho_so'
                                            "
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                        >
                                            <input
                                                type="text"
                                                v-model="
                                                    columnFilters.nguoi_nhan_ho_so
                                                "
                                                class="form-control mb-2"
                                                placeholder="Lọc theo người nhận..."
                                            />
                                            <div class="flex justify-between">
                                                <button
                                                    @click="
                                                        resetFilter(
                                                            'nguoi_nhan_ho_so'
                                                        )
                                                    "
                                                    class="btn btn-sm btn-light"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="
                                                        applyFilter(
                                                            'nguoi_nhan_ho_so'
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
                                        Ngày nhận hồ sơ
                                        <button
                                            @click="
                                                toggleFilter('ngay_nhan_ho_so')
                                            "
                                            class="filter-btn"
                                        >
                                            <i
                                                class="fas fa-filter"
                                                :class="{
                                                    'text-green-500':
                                                        columnFilters.ngay_nhan_ho_so,
                                                }"
                                            ></i>
                                        </button>
                                        <div
                                            v-if="
                                                activeFilter ===
                                                'ngay_nhan_ho_so'
                                            "
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                        >
                                            <input
                                                type="date"
                                                v-model="
                                                    columnFilters.ngay_nhan_ho_so
                                                "
                                                class="form-control mb-2"
                                            />
                                            <div class="flex justify-between">
                                                <button
                                                    @click="
                                                        resetFilter(
                                                            'ngay_nhan_ho_so'
                                                        )
                                                    "
                                                    class="btn btn-sm btn-light"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="
                                                        applyFilter(
                                                            'ngay_nhan_ho_so'
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
                                        Tình trạng giao nhận hồ sơ
                                        <button
                                            @click="
                                                toggleFilter(
                                                    'tinh_trang_giao_nhan_ho_so'
                                                )
                                            "
                                            class="filter-btn"
                                        >
                                            <i
                                                class="fas fa-filter"
                                                :class="{
                                                    'text-green-500':
                                                        columnFilters.tinh_trang_giao_nhan_ho_so,
                                                }"
                                            ></i>
                                        </button>
                                        <div
                                            v-if="
                                                activeFilter ===
                                                'tinh_trang_giao_nhan_ho_so'
                                            "
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                        >
                                            <select
                                                v-model="
                                                    columnFilters.tinh_trang_giao_nhan_ho_so
                                                "
                                                class="form-select mb-2"
                                            >
                                                <option value="">Tất cả</option>
                                                <option value="0">
                                                    Chờ duyệt
                                                </option>
                                                <option value="1">
                                                    Đã duyệt
                                                </option>
                                                <option value="2">
                                                    Từ chối
                                                </option>
                                            </select>
                                            <div class="flex justify-between">
                                                <button
                                                    @click="
                                                        resetFilter(
                                                            'tinh_trang_giao_nhan_ho_so'
                                                        )
                                                    "
                                                    class="btn btn-sm btn-light"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="
                                                        applyFilter(
                                                            'tinh_trang_giao_nhan_ho_so'
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
                                >
                                    <td>{{ item.ma_so_phieu }}</td>
                                    <td>{{ item.can_bo_nong_vu }}</td>
                                    <td>{{ item.vu_dau_tu }}</td>
                                    <td>{{ item.ten_phieu }}</td>
                                    <td>
                                        {{ item.hop_dong_dau_tu_mia_ben_giao }}
                                    </td>
                                    <td>
                                        {{ item.hop_dong_dau_tu_mia_ben_nhan }}
                                    </td>
                                    <td>
                                        {{
                                            formatCurrency(item.tong_thuc_nhan)
                                        }}
                                    </td>
                                    <td>
                                        {{ formatCurrency(item.tong_tien) }}
                                    </td>
                                    <td>{{ item.nguoi_giao_ho_so }}</td>
                                    <td>{{ item.nguoi_nhan_ho_so }}</td>
                                    <td>
                                        {{ formatDate(item.ngay_nhan_ho_so) }}
                                    </td>
                                    <td>
                                        {{
                                            formatStatus(
                                                item.tinh_trang_giao_nhan_ho_so
                                            )
                                        }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="flex justify-center mt-4">
                        <Bootstrap5Pagination
                            :data="paginatedItems"
                            @pagination-change-page="pageChanged"
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
                        <strong>Mã số phiếu:</strong>
                        {{ item.ma_so_phieu }}
                    </div>
                    <div class="mb-2">
                        <strong>Cán bộ nông vụ:</strong>
                        {{ item.can_bo_nong_vu }}
                    </div>
                    <div class="mb-2">
                        <strong>Vụ đầu tư:</strong>
                        {{ item.vu_dau_tu }}
                    </div>
                    <div class="mb-2">
                        <strong>Tên phiếu:</strong>
                        {{ item.ten_phieu }}
                    </div>
                    <div class="mb-2">
                        <strong>Hợp đồng đầu tư mía bên giao:</strong>
                        {{ item.hop_dong_dau_tu_mia_ben_giao }}
                    </div>
                    <div class="mb-2">
                        <strong>Hợp đồng đầu tư mía bên nhận:</strong>
                        {{ item.hop_dong_dau_tu_mia_ben_nhan }}
                    </div>
                    <div class="mb-2">
                        <strong>Tổng thực nhận:</strong>
                        {{ formatCurrency(item.tong_thuc_nhan) }}
                    </div>
                    <div class="mb-2">
                        <strong>Tổng tiền:</strong>
                        {{ formatCurrency(item.tong_tien) }}
                    </div>
                    <div class="mb-2">
                        <strong>Người giao hồ sơ:</strong>
                        {{ item.nguoi_giao_ho_so }}
                    </div>
                    <div class="mb-2">
                        <strong>Người nhận hồ sơ:</strong>
                        {{ item.nguoi_nhan_ho_so }}
                    </div>
                    <div class="mb-2">
                        <strong>Ngày nhận hồ sơ:</strong>
                        {{ formatDate(item.ngay_nhan_ho_so) }}
                    </div>
                    <div class="mb-2">
                        <strong>Tình trạng giao nhận hồ sơ:</strong>
                        {{ formatStatus(item.tinh_trang_giao_nhan_ho_so) }}
                    </div>
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
                        Xuất Excel
                    </h5>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
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

    <!-- Add Import Modal -->
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
                        Import Data
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
                            >Import data sẽ cập nhật dữ liệu theo mã phiếu. Vui
                            lòng kiểm tra kỹ thông tin trước khi tiếp
                            tục.</small
                        >
                    </div>

                    <div class="mb-3">
                        <label for="importFile" class="form-label"
                            >Chọn file</label
                        >
                        <input
                            class="form-control"
                            type="file"
                            id="importFile"
                            @change="handleFileSelected"
                            accept=".csv,.xlsx"
                        />
                        <div class="form-text">Hỗ trợ file: .csv, .xlsx</div>
                    </div>

                    <div v-if="uploadProgress > 0" class="mb-3">
                        <label class="form-label">Tiến trình tải lên</label>
                        <div class="progress">
                            <div
                                class="progress-bar bg-success"
                                role="progressbar"
                                :style="{ width: uploadProgress + '%' }"
                                :aria-valuenow="uploadProgress"
                                aria-valuemin="0"
                                aria-valuemax="100"
                            >
                                {{ uploadProgress }}%
                            </div>
                        </div>
                    </div>

                    <div v-if="importErrors.length > 0" class="mt-3">
                        <div class="alert alert-danger">
                            <h6 class="alert-heading">Lỗi import</h6>
                            <ul class="mb-0 ps-3">
                                <li
                                    v-for="(error, index) in importErrors"
                                    :key="index"
                                >
                                    {{ error }}
                                </li>
                            </ul>
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
                        Hủy
                    </button>
                    <button
                        type="button"
                        class="btn btn-primary"
                        @click="startImport"
                        :disabled="!selectedFile || isImporting"
                    >
                        <i class="fas fa-upload me-2"></i>
                        <span v-if="isImporting">Đang import...</span>
                        <span v-else>Import</span>
                    </button>
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
            isLoading: false,
            phieuList: [],
            allPhieuList: [],
            search: "",
            statusFilter: "all",
            isMobile: window.innerWidth < 768,
            currentPage: 1,
            perPage: 10,
            statusOptions: [
                { code: "all", name: "Tất cả" },
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
            columnFilters: {
                ma_so_phieu: "",
                can_bo_nong_vu: "",
                vu_dau_tu: "",
                ten_phieu: "",
                hop_dong_dau_tu_mia_ben_giao: "",
                hop_dong_dau_tu_mia_ben_nhan: "",
                nguoi_giao_ho_so: "",
                nguoi_nhan_ho_so: "",
                ngay_nhan_ho_so: "",
                tinh_trang_giao_nhan_ho_so: "",
            },
            activeFilter: null,
            selectedFile: null,
            uploadProgress: 0,
            importErrors: [],
            isImporting: false,
            // Add export/import related data
            exportModal: null,
            importModal: null,
        };
    },
    computed: {
        filteredItems() {
            return this.phieuList.filter((item) => {
                const matchesSearch =
                    item.ma_so_phieu
                        .toLowerCase()
                        .includes(this.search.toLowerCase()) ||
                    item.ten_phieu
                        .toLowerCase()
                        .includes(this.search.toLowerCase());

                let matchesStatus = true;
                if (this.statusFilter !== "all") {
                    if (this.statusFilter === "approved") {
                        matchesStatus = item.tinh_trang_giao_nhan_ho_so === 1;
                    } else if (this.statusFilter === "pending") {
                        matchesStatus = item.tinh_trang_giao_nhan_ho_so === 0;
                    } else if (this.statusFilter === "rejected") {
                        matchesStatus = item.tinh_trang_giao_nhan_ho_so === 2;
                    }
                }

                return matchesSearch && matchesStatus;
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
        formatDate(date) {
            if (!date) return "";
            return new Date(date).toLocaleDateString("vi-VN");
        },
        formatStatus(status) {
            if (!status) return "";

            const statusMap = {
                0: "Chờ duyệt",
                1: "Đã duyệt",
                2: "Từ chối",
            };

            return statusMap[status] || status;
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
        async fetchPhieuData(page = 1) {
            this.isLoading = true;
            try {
                const response = await axios.get(
                    "/api/phieu-giao-nhan-hom-giong",
                    {
                        params: {
                            page,
                        },
                        headers: {
                            Authorization: "Bearer " + this.store.getToken,
                        },
                    }
                );

                if (response.data.success) {
                    this.allPhieuList = response.data.data;
                    this.phieuList = this.allPhieuList;
                    this.currentPage = page;
                } else {
                    throw new Error(response.data.message);
                }
            } catch (error) {
                console.error("Error fetching phiếu:", error);
                if (error.response?.status === 401) {
                    this.handleAuthError();
                } else {
                    Swal.fire({
                        confirmButtonText: "OK",
                    });
                }
            } finally {
                this.isLoading = false;
            }
        },
        resetAllFilters() {
            this.search = "";
            this.statusFilter = "all";
            this.currentPage = 1;
        },
        viewDetails(item) {
            console.log("Viewing details for:", item);
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
        },

        resetFilter(column) {
            this.columnFilters[column] = "";
            this.currentPage = 1;
        },

        applyFilter(column) {
            this.activeFilter = null;
            this.currentPage = 1;
        },

        // Export methods
        showExportModal() {
            const modalElement = document.getElementById("exportModal");
            if (modalElement) {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            }
        },

        exportToExcelCurrentPage() {
            if (
                this.paginatedItems.data &&
                this.paginatedItems.data.length > 0
            ) {
                this.generateExcel(
                    this.paginatedItems.data,
                    "phieu_giao_nhan_current_page"
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
                    "phieu_giao_nhan_all_data"
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
                            "Cán bộ nông vụ": item.can_bo_nong_vu || "",
                            "Vụ đầu tư": item.vu_dau_tu || "",
                            "Tên phiếu": item.ten_phieu || "",
                            "Hợp đồng đầu tư mía bên giao":
                                item.hop_dong_dau_tu_mia_ben_giao || "",
                            "Hợp đồng đầu tư mía bên nhận":
                                item.hop_dong_dau_tu_mia_ben_nhan || "",
                            "Tổng thực nhận": item.tong_thuc_nhan || 0,
                            "Tổng tiền": item.tong_tien || 0,
                            "Người giao hồ sơ": item.nguoi_giao_ho_so || "",
                            "Người nhận hồ sơ": item.nguoi_nhan_ho_so || "",
                            "Ngày nhận hồ sơ": item.ngay_nhan_ho_so
                                ? new Date(
                                      item.ngay_nhan_ho_so
                                  ).toLocaleDateString("vi-VN")
                                : "",
                            "Tình trạng giao nhận hồ sơ":
                                this.formatStatus(
                                    item.tinh_trang_giao_nhan_ho_so
                                ) || "",
                        }));

                        // Create a worksheet
                        const worksheet = XLSX.utils.json_to_sheet(exportData);

                        // Set column widths
                        const columnWidths = [
                            { wch: 15 }, // Mã số phiếu
                            { wch: 20 }, // Cán bộ nông vụ
                            { wch: 15 }, // Vụ đầu tư
                            { wch: 30 }, // Tên phiếu
                            { wch: 25 }, // Hợp đồng đầu tư mía bên giao
                            { wch: 25 }, // Hợp đồng đầu tư mía bên nhận
                            { wch: 15 }, // Tổng thực nhận
                            { wch: 15 }, // Tổng tiền
                            { wch: 20 }, // Người giao hồ sơ
                            { wch: 20 }, // Người nhận hồ sơ
                            { wch: 15 }, // Ngày nhận hồ sơ
                            { wch: 20 }, // Tình trạng giao nhận hồ sơ
                        ];
                        worksheet["!cols"] = columnWidths;

                        // Create a workbook
                        const workbook = XLSX.utils.book_new();
                        XLSX.utils.book_append_sheet(
                            workbook,
                            worksheet,
                            "Phiếu giao nhận"
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

        // Import methods
        importData() {
            const modalElement = document.getElementById("importModal");
            if (modalElement) {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            }
        },

        handleFileSelected(event) {
            const file = event.target.files[0];
            if (file) {
                const fileType = file.name.split(".").pop().toLowerCase();
                if (!["csv", "xlsx"].includes(fileType)) {
                    alert("Vui lòng chọn file CSV hoặc Excel");
                    event.target.value = "";
                    this.selectedFile = null;
                    return;
                }
                this.selectedFile = file;
                this.importErrors = [];
            }
        },

        async startImport() {
            if (!this.selectedFile) {
                alert("Vui lòng chọn file để import");
                return;
            }

            this.isImporting = true;
            const formData = new FormData();
            formData.append("file", this.selectedFile);

            try {
                const response = await axios.post(
                    "/api/import-phieu-giao-nhan",
                    formData,
                    {
                        headers: {
                            "Content-Type": "multipart/form-data",
                            Authorization: "Bearer " + this.store.getToken,
                        },
                        onUploadProgress: (progressEvent) => {
                            this.uploadProgress = Math.round(
                                (progressEvent.loaded * 100) /
                                    progressEvent.total
                            );
                        },
                    }
                );

                if (response.data.success) {
                    alert("Import thành công");
                    this.closeImportModal();
                    this.fetchPhieuData(); // Refresh data
                } else {
                    this.importErrors = response.data.errors || [
                        "Có lỗi xảy ra khi import",
                    ];
                }
            } catch (error) {
                console.error("Import error:", error);
                this.importErrors = [
                    "Có lỗi xảy ra khi import: " + error.message,
                ];
            } finally {
                this.isImporting = false;
            }
        },

        closeImportModal() {
            const modalElement = document.getElementById("importModal");
            if (modalElement) {
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.hide();
                }
            }
            this.selectedFile = null;
            this.uploadProgress = 0;
            this.importErrors = [];
        },
    },
    watch: {
        search() {
            this.currentPage = 1;
        },
        statusFilter() {
            this.currentPage = 1;
        },
    },
    mounted() {
        this.fetchPhieuData();
        window.addEventListener("resize", () => {
            this.isMobile = window.innerWidth < 768;
        });
    },
    beforeUnmount() {
        window.removeEventListener("resize", () => {
            this.isMobile = window.innerWidth < 768;
        });
    },
};
</script>

<style scoped>
/* ...existing styles... */
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

/* Add to <style> section */

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

/* ... existing styles ... */

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

/* Add styles for column filters */
.filter-btn {
    background: none;
    border: none;
    padding: 2px 6px;
    cursor: pointer;
    color: #6b7280;
}

.filter-btn:hover {
    color: #10b981;
}

.filter-btn.active {
    color: #10b981;
}

/* Position the filter dropdown */
.absolute.mt-1.bg-white {
    min-width: 200px;
    z-index: 1000;
}
</style>
