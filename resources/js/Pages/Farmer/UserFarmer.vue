<template>
    <!-- Loading starts -->
    <div id="loading-wrapper" v-if="isLoading">
        <div class="spinner-border" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>

    <div>
        <!-- Desktop Controls -->
        <div class="desktop-controls-container mt-3" v-if="!isMobile">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-users-cog me-2"></i>
                        Quản lý người dùng nông dân
                        <span class="badge bg-primary ms-2"
                            >{{ filteredUsers.length }} users</span
                        >
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Search and Actions -->
                    <div class="row mb-3 align-items-center">
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <button
                                    @click="showExportModal"
                                    class="btn btn-outline-success"
                                >
                                    <i class="fas fa-file-export me-2"></i>Xuất
                                    Excel
                                </button>
                                <button
                                    @click="Adduser"
                                    class="btn btn-success"
                                >
                                    <i class="fas fa-plus me-2"></i>Thêm người
                                    dùng
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex justify-content-end">
                            <div class="search-container">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input
                                        type="text"
                                        class="form-control search-input"
                                        placeholder="Tìm kiếm theo tên khách hàng, mã KH, số điện thoại..."
                                        v-model="search"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Buttons -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="d-flex gap-2 flex-wrap">
                                <button
                                    @click="resetAllFilters"
                                    class="btn btn-sm btn-outline-secondary"
                                    v-if="hasActiveFilters"
                                >
                                    <i class="fas fa-times me-1"></i>Xóa tất cả
                                    bộ lọc
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Table Container -->
                    <div class="table-container">
                        <div
                            class="table-scroll-container"
                            ref="tableScrollContainer"
                        >
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>
                                            Trạm
                                            <button
                                                @click="toggleFilter('tram')"
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.tram,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="activeFilter === 'tram'"
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="columnFilters.tram"
                                                    class="form-control form-control-sm mb-2"
                                                    placeholder="Filter by trạm..."
                                                />
                                                <div
                                                    class="d-flex justify-content-between mt-2"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter('tram')
                                                        "
                                                        class="btn btn-sm btn-outline-secondary"
                                                    >
                                                        Clear
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter('tram')
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>
                                        <th>
                                            Supplier Number
                                            <button
                                                @click="
                                                    toggleFilter(
                                                        'supplier_number'
                                                    )
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.supplier_number,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'supplier_number'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.supplier_number
                                                    "
                                                    class="form-control form-control-sm mb-2"
                                                    placeholder="Filter by supplier number..."
                                                />
                                                <div
                                                    class="d-flex justify-content-between mt-2"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'supplier_number'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-outline-secondary"
                                                    >
                                                        Clear
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'supplier_number'
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
                                                        'ma_kh_ca_nhan'
                                                    )
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.ma_kh_ca_nhan,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'ma_kh_ca_nhan'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.ma_kh_ca_nhan
                                                    "
                                                    class="form-control form-control-sm mb-2"
                                                    placeholder="Filter by mã KH cá nhân..."
                                                />
                                                <div
                                                    class="d-flex justify-content-between mt-2"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'ma_kh_ca_nhan'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-outline-secondary"
                                                    >
                                                        Clear
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'ma_kh_ca_nhan'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>
                                        <th>Khách hàng cá nhân</th>
                                        <th>
                                            Mã KH doanh nghiệp
                                            <button
                                                @click="
                                                    toggleFilter(
                                                        'ma_kh_doanh_nghiep'
                                                    )
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.ma_kh_doanh_nghiep,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'ma_kh_doanh_nghiep'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.ma_kh_doanh_nghiep
                                                    "
                                                    class="form-control form-control-sm mb-2"
                                                    placeholder="Filter by mã KH doanh nghiệp..."
                                                />
                                                <div
                                                    class="d-flex justify-content-between mt-2"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'ma_kh_doanh_nghiep'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-outline-secondary"
                                                    >
                                                        Clear
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'ma_kh_doanh_nghiep'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>
                                        <th>Khách hàng doanh nghiệp</th>
                                        <th>
                                            Số điện thoại
                                            <button
                                                @click="toggleFilter('phone')"
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.phone,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="activeFilter === 'phone'"
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.phone
                                                    "
                                                    class="form-control form-control-sm mb-2"
                                                    placeholder="Filter by phone..."
                                                />
                                                <div
                                                    class="d-flex justify-content-between mt-2"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter('phone')
                                                        "
                                                        class="btn btn-sm btn-outline-secondary"
                                                    >
                                                        Clear
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter('phone')
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>
                                        <th>Địa chỉ thường trú</th>
                                        <th>Chủ tài khoản</th>
                                        <th>Ngân hàng</th>
                                        <th>Số tài khoản</th>
                                        <th>
                                            Vai trò
                                            <button
                                                @click="toggleFilter('role_id')"
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            selectedFilterValues
                                                                .role_id
                                                                .length > 0,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter === 'role_id'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <div
                                                    class="max-h-40 overflow-y-auto"
                                                >
                                                    <div
                                                        v-for="role in roles"
                                                        :key="role.id"
                                                        class="form-check"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            class="form-check-input"
                                                            :id="
                                                                'role-' +
                                                                role.id
                                                            "
                                                            :value="role.id"
                                                            v-model="
                                                                selectedFilterValues.role_id
                                                            "
                                                        />
                                                        <label
                                                            class="form-check-label"
                                                            :for="
                                                                'role-' +
                                                                role.id
                                                            "
                                                        >
                                                            {{ role.name }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <div
                                                    class="d-flex justify-content-between mt-2"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'role_id'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-outline-secondary"
                                                    >
                                                        Clear
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'role_id'
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
                                            Trạng thái
                                            <button
                                                @click="toggleFilter('status')"
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            selectedFilterValues
                                                                .status.length >
                                                            0,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="activeFilter === 'status'"
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <div
                                                    class="max-h-40 overflow-y-auto"
                                                >
                                                    <div
                                                        v-for="status in uniqueValues.status"
                                                        :key="status"
                                                        class="form-check"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            class="form-check-input"
                                                            :id="
                                                                'status-' +
                                                                status
                                                            "
                                                            :value="status"
                                                            v-model="
                                                                selectedFilterValues.status
                                                            "
                                                        />
                                                        <label
                                                            class="form-check-label"
                                                            :for="
                                                                'status-' +
                                                                status
                                                            "
                                                        >
                                                            {{ status }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <div
                                                    class="d-flex justify-content-between mt-2"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'status'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-outline-secondary"
                                                    >
                                                        Clear
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'status'
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
                                        v-for="(
                                            user, index
                                        ) in paginatedUsers.data"
                                        :key="user.id"
                                        class="clickable-row"
                                        @click="navigateToEditUser(user.id)"
                                    >
                                        <td>
                                            {{
                                                (currentPage - 1) * perPage +
                                                index +
                                                1
                                            }}
                                        </td>
                                        <td>{{ user.tram }}</td>
                                        <td>{{ user.supplier_number }}</td>
                                        <td>{{ user.ma_kh_ca_nhan }}</td>
                                        <td>{{ user.khach_hang_ca_nhan }}</td>
                                        <td>{{ user.ma_kh_doanh_nghiep }}</td>
                                        <td>
                                            {{ user.khach_hang_doanh_nghiep }}
                                        </td>
                                        <td>{{ user.phone }}</td>
                                        <td>{{ user.dia_chi_thuong_tru }}</td>
                                        <td>{{ user.chu_tai_khoan }}</td>
                                        <td>
                                            {{
                                                user.bank_name || user.bank_code
                                            }}
                                        </td>
                                        <td>{{ user.so_tai_khoan }}</td>
                                        <td>
                                            <span
                                                :class="
                                                    getRoleBadgeClass(
                                                        user.role_id
                                                    )
                                                "
                                            >
                                                <i
                                                    :class="
                                                        getRoleIcon(
                                                            user.role_id
                                                        )
                                                    "
                                                ></i>
                                                {{
                                                    user.role_name ||
                                                    "Chưa phân quyền"
                                                }}
                                            </span>
                                        </td>
                                        <td>
                                            <span
                                                :class="{
                                                    'badge bg-success':
                                                        user.status ===
                                                        'active',
                                                    'badge bg-danger':
                                                        user.status ===
                                                        'inactive',
                                                }"
                                            >
                                                {{
                                                    user.status === "active"
                                                        ? "Hoạt động"
                                                        : "Không hoạt động"
                                                }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="pagination-card">
                            <pagination
                                :data="paginatedUsers"
                                @pagination-change-page="pageChanged"
                                :limit="5"
                                :classes="paginationClasses"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Controls -->
        <div v-else class="mobile-controls-container">
            <div class="card-container">
                <div
                    v-for="user in paginatedUsers.data"
                    :key="user.id"
                    class="user-card card mb-3 clickable-card"
                    @click="navigateToEditUser(user.id)"
                >
                    <div class="card-body">
                        <div class="card-header">
                            <div
                                class="d-flex justify-content-between align-items-start"
                            >
                                <h6 class="mb-1 fw-bold">
                                    {{
                                        user.khach_hang_ca_nhan ||
                                        user.khach_hang_doanh_nghiep ||
                                        "N/A"
                                    }}
                                </h6>
                                <span
                                    :class="{
                                        'badge bg-success':
                                            user.status === 'active',
                                        'badge bg-danger':
                                            user.status === 'inactive',
                                    }"
                                >
                                    {{
                                        user.status === "active"
                                            ? "Hoạt động"
                                            : "Không hoạt động"
                                    }}
                                </span>
                            </div>
                        </div>

                        <div class="user-info">
                            <div class="info-item">
                                <strong>Trạm:</strong> {{ user.tram }}
                            </div>
                            <div class="info-item">
                                <strong>Supplier:</strong>
                                {{ user.supplier_number }}
                            </div>
                            <div class="info-item">
                                <strong>KH cá nhân:</strong>
                                {{ user.ma_kh_ca_nhan }}
                            </div>
                            <div class="info-item">
                                <strong>KH doanh nghiệp:</strong>
                                {{ user.ma_kh_doanh_nghiep }}
                            </div>
                            <div class="info-item">
                                <strong>Phone:</strong> {{ user.phone }}
                            </div>
                            <div class="info-item">
                                <strong>Địa chỉ:</strong>
                                {{ user.dia_chi_thuong_tru }}
                            </div>
                            <div class="info-item">
                                <strong>Chủ TK:</strong>
                                {{ user.chu_tai_khoan }}
                            </div>
                            <div class="info-item">
                                <strong>Ngân hàng:</strong> {{ user.bank_name }}
                            </div>
                        </div>

                        <div class="status-section">
                            <div
                                class="d-flex justify-content-between align-items-center"
                            >
                                <span
                                    class="role-badge"
                                    :class="getRoleBadgeClass(user.role_id)"
                                >
                                    <i :class="getRoleIcon(user.role_id)"></i>
                                    {{ user.role_name || "Chưa phân quyền" }}
                                </span>
                                <small class="text-muted">{{
                                    user.so_tai_khoan
                                }}</small>
                            </div>
                        </div>
                    </div>
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
                    <h5 class="modal-title">Filter Options</h5>
                    <button
                        @click="showMobileFilterModal = false"
                        class="btn-close"
                    >
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="modal-body">
                    <!-- Station Filter -->
                    <div class="filter-section">
                        <label class="filter-label">Trạm</label>
                        <div class="checkbox-container">
                            <div
                                v-for="station in stations"
                                :key="station.ma_don_vi"
                                class="form-check"
                            >
                                <input
                                    type="checkbox"
                                    class="form-check-input"
                                    :id="'mobile-station-' + station.ma_don_vi"
                                    :value="station.ma_don_vi"
                                    v-model="selectedFilterValues.station"
                                />
                                <label
                                    class="form-check-label"
                                    :for="'mobile-station-' + station.ma_don_vi"
                                >
                                    {{ station.Name }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Position Filter -->
                    <div class="filter-section">
                        <label class="filter-label">Chức vụ</label>
                        <div class="checkbox-container">
                            <div
                                v-for="position in positions"
                                :key="position.id_position"
                                class="form-check"
                            >
                                <input
                                    type="checkbox"
                                    class="form-check-input"
                                    :id="'mobile-pos-' + position.id_position"
                                    :value="position.id_position"
                                    v-model="selectedFilterValues.position"
                                />
                                <label
                                    class="form-check-label"
                                    :for="'mobile-pos-' + position.id_position"
                                >
                                    {{ position.position }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div class="filter-section">
                        <label class="filter-label">Status</label>
                        <div class="checkbox-container">
                            <div
                                v-for="status in uniqueValues.status"
                                :key="status"
                                class="form-check"
                            >
                                <input
                                    type="checkbox"
                                    class="form-check-input"
                                    :id="'mobile-status-' + status"
                                    :value="status"
                                    v-model="selectedFilterValues.status"
                                />
                                <label
                                    class="form-check-label"
                                    :for="'mobile-status-' + status"
                                >
                                    {{ status }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button
                        @click="showMobileFilterModal = false"
                        class="btn btn-primary"
                    >
                        <i class="fas fa-check me-1"></i>
                        Apply Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Export Modal -->
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
                        <h5
                            class="modal-title text-success"
                            id="exportModalLabel"
                        >
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
                        <p class="text-muted small mb-3">
                            Chọn định dạng xuất:
                        </p>
                        <div class="d-grid gap-2">
                            <button
                                @click="exportToExcelCurrentPage"
                                class="btn btn-outline-success"
                            >
                                <i class="fas fa-file-export me-2"></i>
                                Chỉ xuất trang hiện tại ({{
                                    paginatedUsers.data.length
                                }}
                                users)
                            </button>
                            <button
                                @click="exportToExcelAllPages"
                                class="btn btn-success"
                            >
                                <i class="fas fa-table me-2"></i>
                                Xuất tất cả ({{ filteredUsers.length }}
                                users)
                            </button>
                        </div>
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

import * as XLSX from "xlsx";
import Swal from "sweetalert2";
import "bootstrap/dist/js/bootstrap.bundle.min.js";
import "bootstrap/dist/css/bootstrap.min.css";

import PerfectScrollbar from "perfect-scrollbar";
import "perfect-scrollbar/css/perfect-scrollbar.css";
import { usePermissions } from "../../Composables/usePermissions";

export default {
    setup() {
        const store = useStore();
        const { hasPermission, canViewComponent } = usePermissions();
        return {
            store,
            exportModal: null,
            hasPermission,
            canViewComponent,
        };
    },
    components: {
        Pagination: Bootstrap5Pagination,
    },

    data() {
        return {
            search: "",
            currentPage: 1,
            perPage: 10,
            users: [],
            roles: [],
            userPermissions: [],
            paginationClasses: {
                ul: "pagination rounded-pill shadow-sm justify-content-center align-items-center",
                li: "page-item mx-1",
                a: "page-link modern-page-link",
                active: "active modern-active",
                disabled: "disabled modern-disabled",
            },
            ps: null,
            isLoading: false,
            // Filter properties updated for new columns
            activeFilter: null,
            columnFilters: {
                tram: "",
                supplier_number: "",
                ma_kh_ca_nhan: "",
                ma_kh_doanh_nghiep: "",
                phone: "",
            },
            uniqueValues: {
                tram: [],
                role_id: [],
                status: ["active", "inactive"],
            },
            selectedFilterValues: {
                tram: [],
                role_id: [],
                status: [],
            },
            isMobile: window.innerWidth < 768,
            showMobileFilterModal: false,
        };
    },

    computed: {
        filteredUsers() {
            return this.users.filter((user) => {
                // Text search
                const searchLower = this.search.toLowerCase();
                const matchesSearch =
                    !this.search ||
                    (user.khach_hang_ca_nhan &&
                        user.khach_hang_ca_nhan
                            .toLowerCase()
                            .includes(searchLower)) ||
                    (user.khach_hang_doanh_nghiep &&
                        user.khach_hang_doanh_nghiep
                            .toLowerCase()
                            .includes(searchLower)) ||
                    (user.supplier_number &&
                        user.supplier_number
                            .toLowerCase()
                            .includes(searchLower)) ||
                    (user.ma_kh_ca_nhan &&
                        user.ma_kh_ca_nhan
                            .toLowerCase()
                            .includes(searchLower)) ||
                    (user.ma_kh_doanh_nghiep &&
                        user.ma_kh_doanh_nghiep
                            .toLowerCase()
                            .includes(searchLower)) ||
                    (user.phone &&
                        user.phone.toLowerCase().includes(searchLower));

                // Column filters
                const matchesTram =
                    !this.columnFilters.tram ||
                    (user.tram &&
                        user.tram
                            .toLowerCase()
                            .includes(this.columnFilters.tram.toLowerCase()));
                const matchesSupplier =
                    !this.columnFilters.supplier_number ||
                    (user.supplier_number &&
                        user.supplier_number
                            .toLowerCase()
                            .includes(
                                this.columnFilters.supplier_number.toLowerCase()
                            ));
                const matchesMaKhCaNhan =
                    !this.columnFilters.ma_kh_ca_nhan ||
                    (user.ma_kh_ca_nhan &&
                        user.ma_kh_ca_nhan
                            .toLowerCase()
                            .includes(
                                this.columnFilters.ma_kh_ca_nhan.toLowerCase()
                            ));
                const matchesMaKhDoanhNghiep =
                    !this.columnFilters.ma_kh_doanh_nghiep ||
                    (user.ma_kh_doanh_nghiep &&
                        user.ma_kh_doanh_nghiep
                            .toLowerCase()
                            .includes(
                                this.columnFilters.ma_kh_doanh_nghiep.toLowerCase()
                            ));
                const matchesPhone =
                    !this.columnFilters.phone ||
                    (user.phone &&
                        user.phone
                            .toLowerCase()
                            .includes(this.columnFilters.phone.toLowerCase()));

                // Dropdown filters
                const matchesRole =
                    this.selectedFilterValues.role_id.length === 0 ||
                    this.selectedFilterValues.role_id.includes(user.role_id);
                const matchesStatus =
                    this.selectedFilterValues.status.length === 0 ||
                    this.selectedFilterValues.status.includes(user.status);

                return (
                    matchesSearch &&
                    matchesTram &&
                    matchesSupplier &&
                    matchesMaKhCaNhan &&
                    matchesMaKhDoanhNghiep &&
                    matchesPhone &&
                    matchesRole &&
                    matchesStatus
                );
            });
        },

        paginatedUsers() {
            const data = this.filteredUsers;
            const total = data.length;
            const start = (this.currentPage - 1) * this.perPage;
            const end = start + this.perPage;

            return {
                data: data.slice(start, end),
                current_page: this.currentPage,
                per_page: this.perPage,
                total: total,
                last_page: Math.ceil(total / this.perPage),
                from: total > 0 ? start + 1 : 0,
                to: Math.min(end, total),
                prev_page_url:
                    this.currentPage > 1
                        ? `?page=${this.currentPage - 1}`
                        : null,
                next_page_url:
                    this.currentPage < Math.ceil(total / this.perPage)
                        ? `?page=${this.currentPage + 1}`
                        : null,
            };
        },

        hasActiveFilters() {
            return (
                this.search ||
                this.columnFilters.tram ||
                this.columnFilters.supplier_number ||
                this.columnFilters.ma_kh_ca_nhan ||
                this.columnFilters.ma_kh_doanh_nghiep ||
                this.columnFilters.phone ||
                this.selectedFilterValues.role_id.length > 0 ||
                this.selectedFilterValues.status.length > 0
            );
        },
    },

    mounted() {
        this.fetchUsers();
        this.fetchFilterOptions();
        window.addEventListener("resize", this.handleResize);
        this.initPerfectScrollbar();
    },

    updated() {
        this.updateScrollbar();
    },

    beforeUnmount() {
        window.removeEventListener("resize", this.handleResize);
        if (this.ps) {
            this.ps.destroy();
            this.ps = null;
        }
    },

    methods: {
        getRoleBadgeClass(roleId) {
            const role = this.roles.find((r) => r.id === roleId);
            if (!role) return "badge bg-label-secondary";

            const roleName = role.name.toLowerCase();
            if (roleName.includes("admin")) {
                return "badge bg-label-danger";
            } else if (roleName.includes("manager")) {
                return "badge bg-label-warning";
            } else {
                return "badge bg-label-primary";
            }
        },

        getRoleIcon(roleId) {
            const role = this.roles.find((r) => r.id === roleId);
            if (!role) return "bx bx-user";

            const roleName = role.name.toLowerCase();
            if (roleName.includes("admin")) {
                return "bx bx-crown";
            } else if (roleName.includes("manager")) {
                return "bx bx-briefcase-alt";
            } else {
                return "bx bx-user";
            }
        },

        initPerfectScrollbar() {
            this.$nextTick(() => {
                if (this.$refs.tableScrollContainer && !this.isMobile) {
                    if (this.ps) {
                        this.ps.destroy();
                    }
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
            this.$nextTick(() => {
                if (this.ps) {
                    this.ps.update();
                }
            });
        },

        pageChanged(page) {
            this.currentPage = page;
        },

        Adduser() {
            this.$router.push("/AddUser");
        },

        navigateToEditUser(userId) {
            this.$router.push(`/EditUser/${userId}`);
        },

        fetchUsers() {
            this.isLoading = true;
            axios
                .get("/api/farmer-users", {
                    headers: { Authorization: "Bearer " + this.store.getToken },
                })
                .then((res) => {
                    this.users = res.data.data;
                    this.updateUniqueValues();
                })
                .catch((err) => {
                    console.error("Error fetching farmer users:", err);
                    if (err.response?.status === 401) {
                        this.handleAuthError();
                    }
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },

        fetchFilterOptions() {
            axios
                .get("/api/farmer-users/filter-options", {
                    headers: { Authorization: "Bearer " + this.store.getToken },
                })
                .then((response) => {
                    const data = response.data.data;
                    this.uniqueValues.tram = data.stations;
                    this.roles = data.roles;
                })
                .catch((error) => {
                    console.error("Error fetching filter options:", error);
                });
        },

        updateUniqueValues() {
            this.uniqueValues.role_id = [
                ...new Set(
                    this.users.map((user) => user.role_id).filter(Boolean)
                ),
            ];
        },

        toggleFilter(filterName) {
            if (this.activeFilter === filterName) {
                this.activeFilter = null;
            } else {
                this.activeFilter = filterName;
            }
        },

        applyFilter(filterName) {
            this.activeFilter = null;
        },

        resetFilter(filterName) {
            if (this.columnFilters.hasOwnProperty(filterName)) {
                this.columnFilters[filterName] = "";
            } else {
                this.selectedFilterValues[filterName] = [];
            }
            this.activeFilter = null;
        },

        resetAllFilters() {
            this.search = "";
            this.columnFilters = {
                tram: "",
                supplier_number: "",
                ma_kh_ca_nhan: "",
                ma_kh_doanh_nghiep: "",
                phone: "",
            };
            Object.keys(this.selectedFilterValues).forEach((key) => {
                this.selectedFilterValues[key] = [];
            });
            this.activeFilter = null;
            this.currentPage = 1;
        },

        handleResize() {
            this.isMobile = window.innerWidth < 768;
        },

        showExportModal() {
            this.exportModal = new bootstrap.Modal(
                document.getElementById("exportModal")
            );
            this.exportModal.show();
        },

        closeExportModal() {
            if (this.exportModal) {
                this.exportModal.hide();
            }
        },

        exportToExcelCurrentPage() {
            this.exportToExcel(
                this.paginatedUsers.data,
                "farmer_users_current_page"
            );
        },

        exportToExcelAllPages() {
            this.exportToExcel(this.filteredUsers, "farmer_users_all");
        },

        exportToExcel(data, filename) {
            const exportData = data.map((user) => ({
                Trạm: user.tram,
                "Supplier Number": user.supplier_number,
                "Mã KH cá nhân": user.ma_kh_ca_nhan,
                "Khách hàng cá nhân": user.khach_hang_ca_nhan,
                "Mã KH doanh nghiệp": user.ma_kh_doanh_nghiep,
                "Khách hàng doanh nghiệp": user.khach_hang_doanh_nghiep,
                "Số điện thoại": user.phone,
                "Địa chỉ thường trú": user.dia_chi_thuong_tru,
                "Chủ tài khoản": user.chu_tai_khoan,
                "Ngân hàng": user.bank_name || user.bank_code,
                "Số tài khoản": user.so_tai_khoan,
                "Vai trò": user.role_name,
                "Trạng thái":
                    user.status === "active" ? "Hoạt động" : "Không hoạt động",
            }));

            const worksheet = XLSX.utils.json_to_sheet(exportData);
            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, "Farmer Users");

            const colWidths = [
                { wch: 15 }, // Trạm
                { wch: 20 }, // Supplier Number
                { wch: 20 }, // Mã KH cá nhân
                { wch: 30 }, // Khách hàng cá nhân
                { wch: 25 }, // Mã KH doanh nghiệp
                { wch: 35 }, // Khách hàng doanh nghiệp
                { wch: 15 }, // Số điện thoại
                { wch: 40 }, // Địa chỉ thường trú
                { wch: 25 }, // Chủ tài khoản
                { wch: 20 }, // Ngân hàng
                { wch: 20 }, // Số tài khoản
                { wch: 15 }, // Vai trò
                { wch: 15 }, // Trạng thái
            ];
            worksheet["!cols"] = colWidths;

            XLSX.writeFile(workbook, `${filename}.xlsx`);
            this.closeExportModal();
        },

        deleteUser(userId) {
            Swal.fire({
                title: "Bạn có chắc chắn?",
                text: "Bạn sẽ không thể khôi phục lại dữ liệu này!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Có, xóa!",
                cancelButtonText: "Hủy",
            }).then((result) => {
                if (result.isConfirmed) {
                    this.performDeleteUser(userId);
                }
            });
        },

        performDeleteUser(userId) {
            axios
                .delete(`/api/farmer-users/${userId}`, {
                    headers: { Authorization: "Bearer " + this.store.getToken },
                })
                .then(() => {
                    Swal.fire(
                        "Đã xóa!",
                        "Người dùng đã được xóa thành công.",
                        "success"
                    );
                    this.fetchUsers();
                })
                .catch((error) => {
                    console.error("Error deleting user:", error);
                    Swal.fire(
                        "Lỗi!",
                        "Có lỗi xảy ra khi xóa người dùng.",
                        "error"
                    );
                });
        },

        handleAuthError() {
            localStorage.removeItem("web_token");
            localStorage.removeItem("web_user");
            this.store.logout();
            this.$router.push("/login");
        },
    },

    watch: {
        search() {
            this.currentPage = 1;
        },
        selectedFilterValues: {
            handler() {
                this.currentPage = 1;
            },
            deep: true,
        },
        columnFilters: {
            handler() {
                this.currentPage = 1;
            },
            deep: true,
        },
    },
};
</script>

<style scoped>
/* Enhanced table styling for modern look */
.table-container {
    position: relative;
    width: 100%;
    background-color: #fff;
    border-radius: 0.75rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1),
        0 2px 4px -1px rgba(0, 0, 0, 0.06);
    padding: 1.5rem;
    margin-bottom: 1rem;
    overflow: hidden;
}

.table-container::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #10b981 0%, #059669 50%, #10b981 100%);
    z-index: 1;
}

.table-scroll-container {
    min-height: 410px;
    position: relative;
    max-height: calc(100vh - 240px);
    overflow: auto;
    border-radius: 0.75rem;
    background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
}

/* Modern table styling without borders */
.table {
    margin-bottom: 0;
    width: 100%;
    min-width: 1200px;
    border-collapse: separate;
    border-spacing: 0;
    border: none;
    background-color: transparent;
}

/* Enhanced header styling */
.table thead {
    position: sticky;
    top: 0;
    z-index: 20;
}

.table thead th {
    position: sticky;
    top: 0;
    background: #f8fafc;
    z-index: 20;
    padding: 1.25rem 1rem;
    vertical-align: middle;
    border: none;
    border-bottom: 2px solid #e2e8f0;
    font-weight: 600;
    font-size: 0.875rem;
    color: #059669;
    text-transform: none;
    letter-spacing: normal;
    white-space: nowrap;
    min-width: 120px;
    position: relative;
    transition: all 0.2s ease;
}

/* Add subtle separator between headers */
.table thead th:not(:last-child):after {
    content: "";
    position: absolute;
    right: 0;
    top: 25%;
    height: 50%;
    width: 1px;
    background: linear-gradient(
        180deg,
        transparent 0%,
        #d1d5db 50%,
        transparent 100%
    );
}

/* Modern table body styling */
.table tbody {
    background-color: transparent;
}

.table td {
    padding: 0.5rem 0.5rem;
    vertical-align: middle;
    border: none;
    transition: all 0.3s ease;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 0.875rem;
    color: #374151;
    background-color: transparent;
}

/* Enhanced row styling with subtle separation */
.table tbody tr {
    position: relative;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 0.5rem;
    margin-bottom: 0.25rem;
}

/* Add subtle row separator */
.table tbody tr:not(:last-child):after {
    content: "";
    position: absolute;
    bottom: 0;
    left: 1rem;
    right: 1rem;
    height: 1px;
    background: linear-gradient(
        90deg,
        transparent 0%,
        rgba(229, 231, 235, 0.6) 10%,
        rgba(229, 231, 235, 0.6) 90%,
        transparent 100%
    );
}

/* Modern hover effect */
.table tbody tr:hover {
    background: linear-gradient(
        135deg,
        rgba(16, 185, 129, 0.05) 0%,
        rgba(16, 185, 129, 0.02) 100%
    );
    transform: translateY(-2px) scale(1.01);
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.15);
    border-radius: 0.75rem;
}

.table tbody tr:hover:after {
    opacity: 0;
}

.table tbody tr:hover td {
    color: #065f46;
}

/* Enhanced zebra striping */
.table tbody tr:nth-child(even) {
    background: linear-gradient(
        135deg,
        rgba(248, 250, 252, 0.7) 0%,
        rgba(241, 245, 249, 0.3) 100%
    );
}

.table tbody tr:nth-child(odd) {
    background: linear-gradient(
        135deg,
        rgba(255, 255, 255, 0.9) 0%,
        rgba(248, 250, 252, 0.5) 100%
    );
}

/* Enhanced badges and status styling */
.badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375em 0.75em;
    font-size: 0.75rem;
    font-weight: 600;
    line-height: 1;
    text-align: center;
    white-space: nowrap;
    vertical-align: baseline;
    border-radius: 0.5rem;
    margin: auto;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease;
}

.badge:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
}

.bg-label-primary {
    color: #1e40af;
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    border: 1px solid rgba(30, 64, 175, 0.2);
}

.bg-label-success {
    color: #065f46;
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    border: 1px solid rgba(6, 95, 70, 0.2);
}

.bg-label-warning {
    color: #92400e;
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border: 1px solid rgba(146, 64, 14, 0.2);
}

.bg-label-danger {
    color: #991b1b;
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    border: 1px solid rgba(153, 27, 27, 0.2);
}

/* Enhanced status styling */
.text-success {
    color: #059669;
    font-weight: 600;
}

.text-danger {
    color: #dc2626;
    font-weight: 600;
}

/* Enhanced action button styling */
.actions-column {
    white-space: normal;
    overflow: visible;
    text-overflow: clip;
}

.dropdown-toggle {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    border: 1px solid rgba(148, 163, 184, 0.3);
    border-radius: 0.5rem;
    padding: 0.5rem;
    transition: all 0.2s ease;
    color: #64748b;
}

.dropdown-toggle:hover {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(16, 185, 129, 0.25);
}

/* Enhanced filter button */
.filter-btn {
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 0.375rem;
    margin-left: 0.375rem;
    color: #9ca3af;
    transition: all 0.2s ease;
    border-radius: 0.375rem;
}

.filter-btn:hover {
    color: #10b981;
    background: rgba(16, 185, 129, 0.1);
    transform: scale(1.1);
}

.text-green-500 {
    color: #10b981;
}

/* Enhanced reset filters button */
.reset-all-filters-btn {
    position: absolute;
    right: 15px;
    top: 30px;
    z-index: 99;
    font-size: 1rem;
    cursor: pointer;
    color: #fff;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border-radius: 50%;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 6px rgba(16, 185, 129, 0.25);
    transition: all 0.3s ease;
    border: 2px solid rgba(255, 255, 255, 0.2);
}

.reset-all-filters-btn:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    transform: rotate(180deg) scale(1.1);
    box-shadow: 0 6px 12px rgba(16, 185, 129, 0.4);
}

/* Enhanced filter dropdown */
.absolute.mt-1.bg-white.p-2.rounded.shadow-lg.z-10 {
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    min-width: 280px;
    max-width: 320px;
    z-index: 1050;
    background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
    padding: 1rem;
    border-radius: 0.75rem;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1),
        0 10px 10px -5px rgba(0, 0, 0, 0.04);
    border: 1px solid rgba(226, 232, 240, 0.8);
    backdrop-filter: blur(8px);
}

.absolute.mt-1.bg-white.p-2.rounded.shadow-lg.z-10:before {
    content: "";
    position: absolute;
    top: -8px;
    left: 15px;
    width: 16px;
    height: 16px;
    background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
    transform: rotate(45deg);
    border-left: 1px solid rgba(226, 232, 240, 0.8);
    border-top: 1px solid rgba(226, 232, 240, 0.8);
    z-index: -1;
}

/* Enhanced row number column */
.table th:first-child,
.table td:first-child {
    width: 60px;
    min-width: 60px;
    max-width: 60px;
    text-align: center;
    font-weight: 600;
    color: #6b7280;
}

/* Perfect scrollbar modern styling */
.ps__rail-y {
    width: 8px;
    background-color: transparent !important;
    border-radius: 4px;
}

.ps__thumb-y {
    width: 6px;
    background: linear-gradient(180deg, #cbd5e1 0%, #94a3b8 100%);
    border-radius: 4px;
    transition: all 0.2s ease;
}

.ps__rail-y:hover > .ps__thumb-y,
.ps__rail-y:focus > .ps__thumb-y {
    width: 8px;
    background: linear-gradient(180deg, #10b981 0%, #059669 100%);
}

.ps__rail-x {
    height: 8px;
    background-color: transparent !important;
    border-radius: 4px;
}

.ps__thumb-x {
    height: 6px;
    background: linear-gradient(90deg, #cbd5e1 0%, #94a3b8 100%);
    border-radius: 4px;
    transition: all 0.2s ease;
}

.ps__rail-x:hover > .ps__thumb-x,
.ps__rail-x:focus > .ps__thumb-x {
    height: 8px;
    background: linear-gradient(90deg, #10b981 0%, #059669 100%);
}

/* Enhanced pagination styling */
.pagination-wrapper {
    position: relative;
    margin-top: 1.5rem;
    width: 100%;
    display: flex;
    justify-content: center;
}

.pagination-card {
    padding: 1rem;

    display: flex;
    justify-content: center;
}

.pagination {
    display: flex;
    list-style: none;
    padding-left: 0;
    margin: 0;
    border-radius: 0.5rem;
    gap: 0.25rem;
}

.pagination li {
    margin: 0;
}

.pagination li a {
    color: #6b7280;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.625rem 0.875rem;
    line-height: 1.25;
    border: 1px solid transparent;
    border-radius: 0.5rem;
    transition: all 0.2s ease;
    font-weight: 500;
    min-width: 40px;
    background: transparent;
}

.pagination li.active a {
    z-index: 1;
    color: #fff;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border-color: transparent;
    box-shadow: 0 4px 6px rgba(16, 185, 129, 0.25);
    transform: translateY(-1px);
}

.pagination li a:hover:not(.active) {
    color: #10b981;
    background: linear-gradient(
        135deg,
        rgba(16, 185, 129, 0.1) 0%,
        rgba(16, 185, 129, 0.05) 100%
    );
    border-color: rgba(16, 185, 129, 0.2);
    transform: translateY(-1px);
}

/* Enhanced search container */
.search-container {
    max-width: 350px;
    width: 100%;
}

.search-input {
    height: 42px;
    transition: all 0.3s ease;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    border: 1px solid #d1d5db;
    background: linear-gradient(145deg, #ffffff 0%, #f9fafb 100%);
}

.search-input:focus {
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1), 0 1px 3px rgba(0, 0, 0, 0.1);
    border-color: #10b981;
    background: #ffffff;
}

.input-group-text {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    background: linear-gradient(145deg, #f3f4f6 0%, #e5e7eb 100%);
    border: 1px solid #d1d5db;
    border-right: none;
}

/* Mobile optimizations */
@media (max-width: 768px) {
    .table-scroll-container {
        max-height: calc(100vh - 300px);
        border-radius: 0.5rem;
    }

    .table thead th {
        font-size: 0.75rem;
        padding: 0.75rem 0.5rem;
    }

    .table td {
        font-size: 0.75rem;
        padding: 0.75rem 0.5rem;
    }

    .absolute.mt-1.bg-white.p-2.rounded.shadow-lg.z-10 {
        width: 90vw;
        max-width: 90vw;
        left: 50%;
        transform: translateX(-50%);
        margin-left: 0;
        margin-right: 0;
    }
}

/* Loading animation enhancement */
#loading-wrapper {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(4px);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.spinner-border {
    width: 3rem;
    height: 3rem;
    color: #10b981;
}

/* Add clickable row styles */
.clickable-row {
    cursor: pointer;
    transition: all 0.3s ease;
}

.clickable-row:hover {
    background: linear-gradient(
        135deg,
        rgba(16, 185, 129, 0.1) 0%,
        rgba(16, 185, 129, 0.05) 100%
    ) !important;
    transform: translateY(-2px) scale(1.01);
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.15);
}

.clickable-card {
    cursor: pointer;
    transition: all 0.3s ease;
}

.clickable-card:hover {
    background: linear-gradient(
        135deg,
        rgba(16, 185, 129, 0.1) 0%,
        rgba(16, 185, 129, 0.05) 100%
    ) !important;
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(16, 185, 129, 0.2);
}

/* Mobile Card View Improvements */
.card-container {
    padding: 0.5rem;
}

.user-card {
    position: relative;
    background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05),
        0 10px 15px -5px rgba(16, 185, 129, 0.07);
    border: 1px solid rgba(226, 232, 240, 0.7);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
}

.user-card:before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 6px;
    height: 100%;
    background: linear-gradient(180deg, #10b981 0%, #059669 100%);
    border-top-left-radius: 4px;
    border-bottom-left-radius: 4px;
}

.card-header {
    border-bottom: 1px solid rgba(226, 232, 240, 0.7);
    padding-bottom: 0.75rem;
}

.user-info {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
    padding: 0.75rem 0;
}

.info-item {
    font-size: 0.875rem;
    line-height: 1.25rem;
}

.info-item strong {
    color: #4b5563;
    font-weight: 600;
    margin-right: 0.25rem;
}

.status-section {
    padding-top: 0.75rem;
    border-top: 1px solid rgba(226, 232, 240, 0.7);
}

.role-badge,
.status-badge {
    padding: 0.375rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.active-status {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    color: #065f46;
    border: 1px solid rgba(6, 95, 70, 0.2);
}

.inactive-status {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    color: #991b1b;
    border: 1px solid rgba(153, 27, 27, 0.2);
}

/* Enhanced Mobile Card Hover Effects */
.clickable-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 25px rgba(16, 185, 129, 0.15),
        0 5px 10px rgba(0, 0, 0, 0.05);
    border-color: rgba(16, 185, 129, 0.3);
}

.clickable-card:active {
    transform: translateY(0);
    box-shadow: 0 5px 15px rgba(16, 185, 129, 0.1);
}
/* Mobile Controls Styling */

@media (max-width: 768px) {
    .mobile-header-row {
        display: flex;
        gap: 0.75rem;
        margin-bottom: 1rem;
        align-items: center;
    }

    .mobile-search-section {
        flex: 0 0 80%;
    }

    .mobile-action-section {
        flex: 0 0 10%;
        display: flex;
        justify-content: flex-end;
    }

    .btn-mobile-add {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border: none;
        border-radius: 0.75rem;
        color: white;
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        box-shadow: 0 4px 6px rgba(16, 185, 129, 0.25),
            0 1px 3px rgba(0, 0, 0, 0.1);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .btn-mobile-add:hover {
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 8px 12px rgba(16, 185, 129, 0.4);
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
    }

    .mobile-filter-row {
        display: flex;
        gap: 0.75rem;
        align-items: center;
    }

    .mobile-role-select {
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

    .mobile-role-select:focus {
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
        max-height: 80vh;
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
        justify-content: between;
        align-items: center;
    }

    .modal-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
        flex: 1;
    }

    .btn-close {
        background: transparent;
        border: none;
        color: #6b7280;
        font-size: 1.25rem;
        padding: 0.5rem;
        border-radius: 0.375rem;
        transition: all 0.2s ease;
    }

    .btn-close:hover {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }

    .modal-body {
        padding: 1rem 1.5rem;
        max-height: 50vh;
        overflow-y: auto;
    }

    /* ==================== Mobile Controls Styling ==================== */
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
        height: 2px;
        background: linear-gradient(
            90deg,
            #10b981 0%,
            #059669 50%,
            #10b981 100%
        );
    }

    .mobile-header-row {
        display: flex;
        gap: 0.75rem;
        margin-bottom: 1rem;
        align-items: center;
    }

    .btn-mobile-add {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border: none;
        border-radius: 0.75rem;
        color: white;
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        box-shadow: 0 4px 6px rgba(16, 185, 129, 0.25),
            0 1px 3px rgba(0, 0, 0, 0.1);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .btn-mobile-add::before {
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

    .btn-mobile-add:hover::before {
        width: 100px;
        height: 100px;
    }

    .btn-mobile-add:hover {
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 8px 12px rgba(16, 185, 129, 0.4),
            0 3px 6px rgba(0, 0, 0, 0.15);
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
    }

    .btn-mobile-add:active {
        transform: translateY(0);
        box-shadow: 0 5px 15px rgba(16, 185, 129, 0.1);
    }

    .mobile-filter-row {
        display: flex;
        gap: 0.75rem;
        align-items: center;
    }

    .mobile-filter-left {
        flex: 0 0 65%;
    }

    .mobile-filter-right {
        flex: 0 0 35%;
        display: flex;
        justify-content: flex-start;
        gap: 0.5rem;
    }

    /* ==================== Modal Footer ==================== */
    .modal-footer {
        padding: 1rem 1.5rem 1.5rem;
        border-top: 1px solid rgba(226, 232, 240, 0.7);
        display: flex;
        gap: 0.75rem;
        justify-content: flex-end;
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
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
}

/* เพิ่ม Modal styling */
.modal-content {
    border-radius: 1rem;
    border: none;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.modal-header {
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    border-radius: 1rem 1rem 0 0;
}

.modal-body {
    padding: 1.5rem;
}

.btn {
    border-radius: 0.5rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-outline-success {
    border-color: #10b981;
    color: #10b981;
}

.btn-outline-success:hover {
    background-color: #10b981;
    border-color: #10b981;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);
}

.btn-success {
    background-color: #10b981;
    border-color: #10b981;
    color: white;
}

.btn-success:hover {
    background-color: #059669;
    border-color: #059669;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(5, 150, 105, 0.4);
}

.btn-close {
    background-size: 1.2em;
}

.d-grid {
    display: grid;
}

.gap-2 {
    gap: 0.5rem;
}
/* Position Badge Styling */
.position-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 600;
    line-height: 1;
    border-radius: 0.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    border: 1px solid transparent;
    min-width: 80px;
    justify-content: center;
}

.position-badge:hover {
    transform: translateY(-1px) scale(1.02);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.position-badge i {
    font-size: 0.8rem;
    margin-right: 0.25rem;
}

/* Station Badge Styling */
.station-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 600;
    line-height: 1;
    border-radius: 0.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    border: 1px solid transparent;
    min-width: 90px;
    justify-content: center;
}

.station-badge:hover {
    transform: translateY(-1px) scale(1.02);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.station-badge i {
    font-size: 0.8rem;
    margin-right: 0.25rem;
}

/* Enhanced Badge Color Schemes */
.bg-label-primary {
    color: #1e40af;
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    border: 1px solid rgba(30, 64, 175, 0.2);
}

.bg-label-success {
    color: #065f46;
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    border: 1px solid rgba(6, 95, 70, 0.2);
}

.bg-label-warning {
    color: #92400e;
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border: 1px solid rgba(146, 64, 14, 0.2);
}

.bg-label-danger {
    color: #991b1b;
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    border: 1px solid rgba(153, 27, 27, 0.2);
}

.bg-label-info {
    color: #0369a1;
    background: linear-gradient(135deg, #e0f2fe 0%, #b3e5fc 100%);
    border: 1px solid rgba(3, 105, 161, 0.2);
}

.bg-label-secondary {
    color: #4b5563;
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
    border: 1px solid rgba(75, 85, 99, 0.2);
}

.bg-label-light {
    color: #6b7280;
    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
    border: 1px solid rgba(107, 114, 128, 0.2);
}

/* Position Badge Specific Hover Effects */
.bg-label-danger.position-badge:hover {
    background: linear-gradient(135deg, #fca5a5 0%, #f87171 100%);
    color: #7f1d1d;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.bg-label-warning.position-badge:hover {
    background: linear-gradient(135deg, #fed7aa 0%, #fdba74 100%);
    color: #78350f;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
}

.bg-label-info.position-badge:hover {
    background: linear-gradient(135deg, #7dd3fc 0%, #38bdf8 100%);
    color: #0c4a6e;
    box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);
}

.bg-label-success.position-badge:hover {
    background: linear-gradient(135deg, #86efac 0%, #4ade80 100%);
    color: #14532d;
    box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
}

.bg-label-primary.position-badge:hover {
    background: linear-gradient(135deg, #93c5fd 0%, #60a5fa 100%);
    color: #1e3a8a;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

/* Station Badge Specific Hover Effects */
.bg-label-primary.station-badge:hover {
    background: linear-gradient(135deg, #93c5fd 0%, #60a5fa 100%);
    color: #1e3a8a;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.bg-label-success.station-badge:hover {
    background: linear-gradient(135deg, #86efac 0%, #4ade80 100%);
    color: #14532d;
    box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
}

.bg-label-info.station-badge:hover {
    background: linear-gradient(135deg, #7dd3fc 0%, #38bdf8 100%);
    color: #0c4a6e;
    box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);
}

.bg-label-warning.station-badge:hover {
    background: linear-gradient(135deg, #fed7aa 0%, #fdba74 100%);
    color: #78350f;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
}

.bg-label-secondary.station-badge:hover {
    background: linear-gradient(135deg, #d1d5db 0%, #9ca3af 100%);
    color: #1f2937;
    box-shadow: 0 4px 12px rgba(107, 114, 128, 0.3);
}

.bg-label-light.station-badge:hover {
    background: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%);
    color: #374151;
    box-shadow: 0 4px 12px rgba(156, 163, 175, 0.3);
}

/* Mobile card view badge adjustments */
@media (max-width: 768px) {
    .position-badge,
    .station-badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
        min-width: 70px;
    }

    .position-badge i,
    .station-badge i {
        font-size: 0.7rem;
        margin-right: 0.2rem;
    }
}

/* Badge animation on page load */
@keyframes badgeSlideIn {
    from {
        opacity: 0;
        transform: translateX(-10px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.position-badge,
.station-badge {
    animation: badgeSlideIn 0.5s ease-out;
}

/* Enhance table cell alignment for badges */

.table td:nth-child(5), /* Position column */
.table td:nth-child(6) {
    /* Station column */
    text-align: center;
    vertical-align: middle;
    padding: 0.75rem 0.5rem;
}
</style>
