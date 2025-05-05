<template>
    <!-- Loading starts -->
    <div id="loading-wrapper" v-if="loading">
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
                        <option value="all">Tất cả trạng thái</option>
                        <option value="processing">Đang xử lý</option>
                        <option value="submitted">Đã nộp</option>
                        <option value="paid">Đã thanh toán</option>
                        <option value="cancelled">Đã hủy</option>
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
                                    @click.prevent="createNewRecord"
                                >
                                    <i
                                        class="fas fa-plus text-success me-2"
                                    ></i>
                                    Tạo mới
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="investment-filter">
                    <select
                        v-model="investmentFilter"
                        class="form-select investment-select"
                    >
                        <option value="all">Tất cả vụ đầu tư</option>
                        <option
                            v-for="item in uniqueInvestments"
                            :key="item"
                            :value="item"
                        >
                            {{ item }}
                        </option>
                    </select>
                </div>
            </div>
            <div
                class="col d-flex justify-content-end gap-3 align-items-center"
            >
                <input
                    v-model="search"
                    type="text"
                    placeholder="Tìm kiếm phiếu trình..."
                    class="search-input px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500"
                />
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
                                        Mã trình thanh toán
                                        <button
                                            @click="
                                                toggleFilter('maTrinhThanhToan')
                                            "
                                            class="filter-btn"
                                        >
                                            <i
                                                class="fas fa-filter"
                                                :class="{
                                                    'text-green-500':
                                                        columnFilters.maTrinhThanhToan,
                                                }"
                                            ></i>
                                        </button>
                                        <div
                                            v-if="
                                                activeFilter ===
                                                'maTrinhThanhToan'
                                            "
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                        >
                                            <input
                                                type="text"
                                                v-model="
                                                    columnFilters.maTrinhThanhToan
                                                "
                                                class="form-control mb-2"
                                                placeholder="Lọc theo mã số..."
                                            />
                                            <div class="flex justify-between">
                                                <button
                                                    @click="
                                                        resetFilter(
                                                            'maTrinhThanhToan'
                                                        )
                                                    "
                                                    class="btn btn-sm btn-light"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="
                                                        applyFilter(
                                                            'maTrinhThanhToan'
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
                                        Tiêu đề
                                        <button
                                            @click="toggleFilter('tieuDe')"
                                            class="filter-btn"
                                        >
                                            <i
                                                class="fas fa-filter"
                                                :class="{
                                                    'text-green-500':
                                                        columnFilters.tieuDe,
                                                }"
                                            ></i>
                                        </button>
                                        <div
                                            v-if="activeFilter === 'tieuDe'"
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                        >
                                            <input
                                                type="text"
                                                v-model="columnFilters.tieuDe"
                                                class="form-control mb-2"
                                                placeholder="Lọc theo tiêu đề..."
                                            />
                                            <div class="flex justify-between">
                                                <button
                                                    @click="
                                                        resetFilter('tieuDe')
                                                    "
                                                    class="btn btn-sm btn-light"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="
                                                        applyFilter('tieuDe')
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
                                            @click="toggleFilter('vuDauTu')"
                                            class="filter-btn"
                                        >
                                            <i
                                                class="fas fa-filter"
                                                :class="{
                                                    'text-green-500':
                                                        selectedFilterValues.vuDauTu &&
                                                        selectedFilterValues
                                                            .vuDauTu.length > 0,
                                                }"
                                            ></i>
                                        </button>
                                        <div
                                            v-if="activeFilter === 'vuDauTu'"
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                        >
                                            <div
                                                class="max-h-40 overflow-y-auto"
                                            >
                                                <div
                                                    v-for="(
                                                        investment, idx
                                                    ) in uniqueInvestments"
                                                    :key="idx"
                                                    class="form-check mb-1"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        class="form-check-input"
                                                        :id="`investment-${idx}`"
                                                        :value="investment"
                                                        v-model="
                                                            selectedFilterValues.vuDauTu
                                                        "
                                                    />
                                                    <label
                                                        class="form-check-label"
                                                        :for="`investment-${idx}`"
                                                    >
                                                        {{ investment }}
                                                    </label>
                                                </div>
                                            </div>
                                            <div
                                                class="flex justify-between mt-2"
                                            >
                                                <button
                                                    @click="
                                                        resetFilter('vuDauTu')
                                                    "
                                                    class="btn btn-sm btn-light"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="
                                                        applyFilter('vuDauTu')
                                                    "
                                                    class="btn btn-sm btn-success"
                                                >
                                                    Apply
                                                </button>
                                            </div>
                                        </div>
                                    </th>
                                    <th>
                                        Loại thanh toán
                                        <button
                                            @click="
                                                toggleFilter('loaiThanhToan')
                                            "
                                            class="filter-btn"
                                        >
                                            <i
                                                class="fas fa-filter"
                                                :class="{
                                                    'text-green-500':
                                                        columnFilters.loaiThanhToan,
                                                }"
                                            ></i>
                                        </button>
                                        <div
                                            v-if="
                                                activeFilter === 'loaiThanhToan'
                                            "
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                        >
                                            <input
                                                type="text"
                                                v-model="
                                                    columnFilters.loaiThanhToan
                                                "
                                                class="form-control mb-2"
                                                placeholder="Lọc theo loại thanh toán..."
                                            />
                                            <div class="flex justify-between">
                                                <button
                                                    @click="
                                                        resetFilter(
                                                            'loaiThanhToan'
                                                        )
                                                    "
                                                    class="btn btn-sm btn-light"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="
                                                        applyFilter(
                                                            'loaiThanhToan'
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
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <span>Trạng thái</span>
                                            <button
                                                @click="
                                                    toggleFilter(
                                                        'trangThaiThanhToan'
                                                    )
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            selectedFilterValues.trangThaiThanhToan &&
                                                            selectedFilterValues
                                                                .trangThaiThanhToan
                                                                .length > 0,
                                                    }"
                                                ></i>
                                            </button>
                                        </div>
                                        <div
                                            v-if="
                                                activeFilter ===
                                                'trangThaiThanhToan'
                                            "
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                        >
                                            <div
                                                class="max-h-40 overflow-y-auto"
                                            >
                                                <div
                                                    v-for="(status, idx) in [
                                                        'processing',
                                                        'submitted',
                                                        'paid',
                                                        'cancelled',
                                                    ]"
                                                    :key="idx"
                                                    class="form-check mb-1"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        class="form-check-input"
                                                        :id="`status-${idx}`"
                                                        :value="status"
                                                        v-model="
                                                            selectedFilterValues.trangThaiThanhToan
                                                        "
                                                    />
                                                    <label
                                                        class="form-check-label"
                                                        :for="`status-${idx}`"
                                                    >
                                                        {{
                                                            formatStatus(status)
                                                        }}
                                                    </label>
                                                </div>
                                            </div>
                                            <div
                                                class="flex justify-between mt-2"
                                            >
                                                <button
                                                    @click="
                                                        resetFilter(
                                                            'trangThaiThanhToan'
                                                        )
                                                    "
                                                    class="btn btn-sm btn-light"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="
                                                        applyFilter(
                                                            'trangThaiThanhToan'
                                                        )
                                                    "
                                                    class="btn btn-sm btn-success"
                                                >
                                                    Apply
                                                </button>
                                            </div>
                                        </div>
                                    </th>
                                    <th>Số đợt thanh toán</th>
                                    <th>Số tờ trình</th>
                                    <th>Ngày tạo</th>
                                    <th>Tổng tiền thanh toán</th>
                                    <th>Người tạo</th>
                                    <th class="text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="item in paginatedItems"
                                    :key="item.id"
                                    @click="viewDetails(item)"
                                    class="desktop-row cursor-pointer"
                                >
                                    <td>{{ item.maTrinhThanhToan }}</td>
                                    <td>{{ item.tieuDe }}</td>
                                    <td>{{ item.vuDauTu }}</td>
                                    <td>{{ item.loaiThanhToan }}</td>
                                    <td>
                                        <span
                                            :class="
                                                statusClass(
                                                    item.trangThaiThanhToan
                                                )
                                            "
                                            class="flex items-center"
                                        >
                                            <i
                                                :class="
                                                    statusIcons(
                                                        item.trangThaiThanhToan
                                                    )
                                                "
                                                class="mr-1"
                                            ></i>
                                            {{
                                                formatStatus(
                                                    item.trangThaiThanhToan
                                                )
                                            }}
                                        </span>
                                    </td>
                                    <td>{{ item.soDotThanhToan }}</td>
                                    <td>{{ item.soToTrinh }}</td>
                                    <td>{{ formatDate(item.ngayTao) }}</td>
                                    <td>
                                        {{
                                            formatCurrency(
                                                item.tongTienThanhToan
                                            )
                                        }}
                                    </td>
                                    <td>{{ item.nguoiTao }}</td>
                                    <td>
                                        <div class="flex justify-center gap-2">
                                            <button
                                                @click.stop="editItem(item)"
                                                class="btn btn-sm btn-outline-primary"
                                                title="Chỉnh sửa"
                                            >
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button
                                                @click.stop="deleteItem(item)"
                                                class="btn btn-sm btn-outline-danger"
                                                title="Xóa"
                                            >
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="paginatedItems.length === 0">
                                    <td colspan="12" class="text-center py-4">
                                        <div class="empty-state">
                                            <i
                                                class="fas fa-search empty-icon"
                                            ></i>
                                            <p>
                                                Không có dữ liệu phiếu trình
                                                thanh toán
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="flex justify-center mt-4">
                        <div class="pagination-card">
                            <Bootstrap5Pagination
                                :data="paginationData"
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
                    v-for="item in paginatedItems"
                    :key="item.id"
                    class="card border p-4 mb-4 rounded shadow hover:shadow-green-500 transition-shadow duration-300"
                    @click="viewDetails(item)"
                >
                    <div class="flex-1 justify-items-start">
                        <div class="mb-2">
                            <strong>Mã trình thanh toán:</strong>
                            {{ item.maTrinhThanhToan }}
                        </div>
                        <div class="mb-2">
                            <strong>Tiêu đề:</strong>
                            {{ item.tieuDe }}
                        </div>
                        <div class="mb-2">
                            <strong>Vụ đầu tư:</strong>
                            {{ item.vuDauTu }}
                        </div>
                        <div class="mb-2">
                            <strong>Loại thanh toán:</strong>
                            {{ item.loaiThanhToan }}
                        </div>
                        <div class="mb-2">
                            <strong>Trạng thái:</strong>
                            <span
                                :class="statusClass(item.trangThaiThanhToan)"
                                class="flex items-center"
                            >
                                <i
                                    :class="
                                        statusIcons(item.trangThaiThanhToan)
                                    "
                                    class="mr-1"
                                ></i>
                                {{ formatStatus(item.trangThaiThanhToan) }}
                            </span>
                        </div>
                        <div class="mb-2">
                            <strong>Số đợt thanh toán:</strong>
                            {{ item.soDotThanhToan }}
                        </div>
                        <div class="mb-2">
                            <strong>Số tờ trình:</strong>
                            {{ item.soToTrinh }}
                        </div>
                        <div class="mb-2">
                            <strong>Ngày tạo:</strong>
                            {{ formatDate(item.ngayTao) }}
                        </div>
                        <div class="mb-2">
                            <strong>Tổng tiền thanh toán:</strong>
                            {{ formatCurrency(item.tongTienThanhToan) }}
                        </div>
                        <div class="mb-2">
                            <strong>Người tạo:</strong>
                            {{ item.nguoiTao }}
                        </div>
                    </div>
                    <div class="flex justify-end mt-3">
                        <button
                            @click.stop="editItem(item)"
                            class="btn btn-sm btn-outline-primary me-2"
                        >
                            <i class="fas fa-edit me-1"></i> Sửa
                        </button>
                        <button
                            @click.stop="deleteItem(item)"
                            class="btn btn-sm btn-outline-danger"
                        >
                            <i class="fas fa-trash-alt me-1"></i> Xóa
                        </button>
                    </div>
                </div>
                <div
                    v-if="paginatedItems.length === 0"
                    class="text-center py-4"
                >
                    <div class="empty-state">
                        <i class="fas fa-search empty-icon"></i>
                        <p>Không có dữ liệu phiếu trình thanh toán</p>
                    </div>
                </div>
            </div>
            <div class="flex justify-center mt-4">
                <div class="pagination-card">
                    <Bootstrap5Pagination
                        :data="paginationData"
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
                                <small>
                                    And {{ importErrors.length - 5 }} more
                                    errors...
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal"
                        :disabled="isImporting"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="btn btn-primary"
                        @click="importFile"
                        :disabled="!selectedFile || isImporting"
                    >
                        <span v-if="isImporting">
                            <i
                                class="fas fa-spinner fa-spin me-2"
                                aria-hidden="true"
                            ></i>
                            Processing...
                        </span>
                        <span v-else>Import</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { Bootstrap5Pagination } from "laravel-vue-pagination";
import axios from "axios";
import { useStore } from "../../Store/Auth";
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
            search: "",
            statusFilter: "all",
            investmentFilter: "all",
            items: [],
            selectedItems: [],
            currentPage: 1,
            perPage: 10,
            loading: false,
            error: null,
            activeFilter: null,
            columnFilters: {
                maTrinhThanhToan: "",
                tieuDe: "",
                vuDauTu: "",
                loaiThanhToan: "",
                trangThaiThanhToan: "",
            },
            selectedFilterValues: {
                vuDauTu: [],
                trangThaiThanhToan: [],
            },

            // Add these new properties
            isMobile: window.innerWidth < 768,
            selectedFile: null,
            uploadProgress: 0,
            processingRecords: false,
            processingProgress: 0,
            processedRecords: 0,
            totalRecords: 0,
            importErrors: [],
            isImporting: false,
            paginationClasses: {
                ul: "pagination",
                li: "page-item",
                liActive: "active",
                liDisabled: "disabled",
                button: "page-link",
                buttonActive: "active",
            },
        };
    },
    computed: {
        uniqueInvestments() {
            // Get unique investment projects
            return [...new Set(this.items.map((item) => item.vuDauTu))].filter(
                Boolean
            );
        },
        filteredItems() {
            return this.items.filter((item) => {
                // Global search
                const matchesSearch =
                    this.search === "" ||
                    item.tieuDe
                        .toLowerCase()
                        .includes(this.search.toLowerCase()) ||
                    item.maTrinhThanhToan
                        .toLowerCase()
                        .includes(this.search.toLowerCase()) ||
                    item.soToTrinh
                        ?.toLowerCase()
                        .includes(this.search.toLowerCase());

                // Status filter
                const matchesStatus =
                    this.statusFilter === "all" ||
                    item.trangThaiThanhToan === this.statusFilter;

                // Investment filter
                const matchesInvestment =
                    this.investmentFilter === "all" ||
                    item.vuDauTu === this.investmentFilter;

                // Column filters
                const matchesColumnFilters =
                    (!this.columnFilters.maTrinhThanhToan ||
                        item.maTrinhThanhToan
                            .toLowerCase()
                            .includes(
                                this.columnFilters.maTrinhThanhToan.toLowerCase()
                            )) &&
                    (!this.columnFilters.tieuDe ||
                        item.tieuDe
                            .toLowerCase()
                            .includes(
                                this.columnFilters.tieuDe.toLowerCase()
                            )) &&
                    (!this.columnFilters.vuDauTu ||
                        item.vuDauTu
                            .toLowerCase()
                            .includes(
                                this.columnFilters.vuDauTu.toLowerCase()
                            )) &&
                    (!this.columnFilters.loaiThanhToan ||
                        item.loaiThanhToan
                            .toLowerCase()
                            .includes(
                                this.columnFilters.loaiThanhToan.toLowerCase()
                            )) &&
                    (!this.columnFilters.trangThaiThanhToan ||
                        item.trangThaiThanhToan ===
                            this.columnFilters.trangThaiThanhToan);

                return (
                    matchesSearch &&
                    matchesStatus &&
                    matchesInvestment &&
                    matchesColumnFilters
                );
            });
        },
        paginatedItems() {
            const start = (this.currentPage - 1) * this.perPage;
            const end = start + this.perPage;
            return this.filteredItems.slice(start, end);
        },
        isAllSelected() {
            return (
                this.filteredItems.length > 0 &&
                this.selectedItems.length === this.filteredItems.length
            );
        },
        paginationInfo() {
            const from =
                this.filteredItems.length === 0
                    ? 0
                    : (this.currentPage - 1) * this.perPage + 1;
            const to = Math.min(
                this.currentPage * this.perPage,
                this.filteredItems.length
            );
            return { from, to };
        },
        paginationData() {
            return {
                current_page: this.currentPage,
                data: this.paginatedItems,
                from: this.paginationInfo.from,
                last_page: Math.ceil(this.filteredItems.length / this.perPage),
                per_page: this.perPage,
                to: this.paginationInfo.to,
                total: this.filteredItems.length,
            };
        },
    },
    created() {
        this.fetchPaymentRequests();
    },
    methods: {
        async fetchPaymentRequests() {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.get("/api/payment-requests", {
                    headers: {
                        Authorization: "Bearer " + this.store.getToken,
                    },
                });

                if (response.data.success) {
                    // Map backend data fields to frontend format
                    this.items = response.data.data.map((item) => ({
                        id: item.id,
                        maTrinhThanhToan:
                            item.payment_code || item.ma_trinh_thanh_toan,
                        tieuDe: item.title || item.tieu_de,
                        vuDauTu: item.investment_project || item.vu_dau_tu,
                        loaiThanhToan:
                            item.payment_type || item.loai_thanh_toan,
                        trangThaiThanhToan:
                            item.status || item.trang_thai_thanh_toan,
                        soDotThanhToan:
                            item.payment_installment || item.so_dot_thanh_toan,
                        soToTrinh: item.proposal_number || item.so_to_trinh,
                        ngayTao: item.created_at || item.ngay_tao,
                        tongTienThanhToan:
                            item.total_amount || item.tong_tien_thanh_toan,
                        nguoiTao: item.creator_name || item.nguoi_tao,
                    }));
                } else {
                    this.error = "Failed to fetch payment requests";
                }
            } catch (error) {
                console.error("Error fetching payment requests:", error);
                if (error.response && error.response.status === 401) {
                    this.handleAuthError();
                } else {
                    this.error =
                        error.response?.data?.message || "Error fetching data";
                }
            } finally {
                this.loading = false;
            }
        },

        toggleFilter(column) {
            this.activeFilter = this.activeFilter === column ? null : column;
        },

        applyFilter(column) {
            // Apply the filter
            this.activeFilter = null;
            this.currentPage = 1;
        },

        resetFilter(column) {
            this.columnFilters[column] = "";
            this.currentPage = 1;
        },

        // Add authentication error handler
        handleAuthError() {
            // Clear localStorage
            localStorage.removeItem("web_token");
            localStorage.removeItem("web_user");

            // Clear store
            this.store.logout();

            // Redirect to login
            this.$router.push("/login");
        },

        formatDate(date) {
            if (!date) return "";
            const d = new Date(date);
            return d.toLocaleDateString("vi-VN");
        },

        formatStatus(status) {
            const statuses = {
                processing: "Đang xử lý",
                submitted: "Đã nộp",
                paid: "Đã thanh toán",
                cancelled: "Đã hủy",
            };
            return statuses[status] || status;
        },

        statusBadgeClass(status) {
            return {
                "status-processing": status === "processing",
                "status-submitted": status === "submitted",
                "status-paid": status === "paid",
                "status-cancelled": status === "cancelled",
            };
        },

        toggleSelectAll() {
            if (this.isAllSelected) {
                this.selectedItems = [];
            } else {
                this.selectedItems = this.filteredItems.map((item) => item.id);
            }
        },

        pageChanged(page) {
            this.currentPage = page;
        },

        viewDetails(item) {
            // Navigate to details page
            this.$router.push(`/payment-requests/${item.maTrinhThanhToan}`);
        },

        createNewRecord() {
            // Navigate to create page or show modal
            this.$router.push("/payment-requests/create");
        },

        editItem(item) {
            // Navigate to edit page
            this.$router.push(
                `/payment-requests/${item.maTrinhThanhToan}/edit`
            );
        },

        deleteItem(item) {
            // Confirm before deleting
            Swal.fire({
                title: "Xác nhận xóa?",
                text: `Bạn có chắc chắn muốn xóa phiếu trình "${item.tieuDe}"?`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Xóa",
                cancelButtonText: "Hủy",
                buttonsStyling: false,
                customClass: {
                    confirmButton: "btn btn-success me-2",
                    cancelButton: "btn btn-outline-secondary",
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    this.performDelete(item.id);
                }
            });
        },

        async performDelete(id) {
            try {
                const response = await axios.delete(
                    `/api/payment-requests/${id}`,
                    {
                        headers: {
                            Authorization: "Bearer " + this.store.getToken,
                        },
                    }
                );

                if (response.data.success) {
                    // Show success notification
                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        title: "Đã xóa thành công",
                        icon: "success",
                        showConfirmButton: false,
                        timer: 3000,
                    });

                    // Refresh data
                    this.fetchPaymentRequests();
                }
            } catch (error) {
                console.error("Delete error:", error);

                if (error.response && error.response.status === 401) {
                    this.handleAuthError();
                } else {
                    // Show error notification
                    Swal.fire({
                        title: "Lỗi!",
                        text: "Đã xảy ra lỗi khi xóa phiếu trình thanh toán",
                        icon: "error",
                        confirmButtonText: "OK",
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: "btn btn-success",
                        },
                    });
                }
            }
        },

        statusClass(status) {
            switch (status) {
                case "processing":
                    return "text-yellow-600";
                case "submitted":
                    return "text-blue-600";
                case "paid":
                    return "text-green-600";
                case "cancelled":
                    return "text-red-600";
                default:
                    return "text-gray-600";
            }
        },

        statusIcons(status) {
            switch (status) {
                case "processing":
                    return "fas fa-spinner";
                case "submitted":
                    return "fas fa-paper-plane";
                case "paid":
                    return "fas fa-check-circle";
                case "cancelled":
                    return "fas fa-times-circle";
                default:
                    return "fas fa-question-circle";
            }
        },

        formatCurrency(amount) {
            if (!amount) return "0 ₫";
            return new Intl.NumberFormat("vi-VN", {
                style: "currency",
                currency: "VND",
            }).format(amount);
        },

        resetAllFilters() {
            this.search = "";
            this.statusFilter = "all";
            this.investmentFilter = "all";
            this.activeFilter = null;
            this.columnFilters = {
                maTrinhThanhToan: "",
                tieuDe: "",
                vuDauTu: "",
                loaiThanhToan: "",
                trangThaiThanhToan: "",
            };
            this.selectedFilterValues = {
                vuDauTu: [],
                trangThaiThanhToan: [],
            };
            this.currentPage = 1;
        },

        showExportModal() {
            const exportModal = new window.bootstrap.Modal(
                document.getElementById("exportModal")
            );
            exportModal.show();
        },

        closeExportModal() {
            const exportModal = window.bootstrap.Modal.getInstance(
                document.getElementById("exportModal")
            );
            if (exportModal) {
                exportModal.hide();
            }
        },

        exportToExcelCurrentPage() {
            // Implementation for exporting current page
            console.log("Exporting current page to Excel");
            // Close modal after action
            this.closeExportModal();
        },

        exportToExcelAllPages() {
            // Implementation for exporting all data
            console.log("Exporting all data to Excel");
            // Close modal after action
            this.closeExportModal();
        },

        importData() {
            const importModal = new window.bootstrap.Modal(
                document.getElementById("importModal")
            );
            importModal.show();
        },

        closeImportModal() {
            const importModal = window.bootstrap.Modal.getInstance(
                document.getElementById("importModal")
            );
            if (importModal) {
                importModal.hide();
            }
        },

        handleFileSelected(event) {
            this.selectedFile = event.target.files[0];
            // Reset any previous import errors
            this.importErrors = [];
        },

        importFile() {
            // Implementation for importing file
            console.log("Importing file:", this.selectedFile);
            // Close modal after action
            this.closeImportModal();
        },
    },
};
</script>

<style scoped>
/* General Layout */
.page-container {
    padding: 1.5rem;
}

/* Toolbar Styling */
.toolbar {
    border-radius: 8px;
}

.flex-row-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
}

/* Filters Styling */
.filters-container {
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.filter-item {
    margin-bottom: 0.5rem;
}

/* Table Styling */
.data-table-container {
    margin-bottom: 2rem;
}

.table-container {
    overflow: hidden;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}

.table {
    margin-bottom: 0;
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.table th {
    border-top: none;
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    vertical-align: middle;
    border-bottom: 2px solid #e9ecef;
    background-color: #f8f9fa;
    position: relative;
}

.table td {
    vertical-align: middle;
}

.table-hover tbody tr:hover {
    background-color: #f8f9fa;
}

/* Empty State */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 2rem 0;
    color: #6c757d;
}

.empty-icon {
    font-size: 2.5rem;
    margin-bottom: 0.75rem;
    opacity: 0.5;
}

/* Filter Button */
.filter-btn {
    background: none;
    border: none;
    padding: 0;
    margin-left: 0.5rem;
    color: #adb5bd;
    cursor: pointer;
}

.filter-btn:hover {
    color: #495057;
}

.text-green-500 {
    color: #198754 !important;
}

/* Status Badges */
.status-badge {
    display: inline-block;
    padding: 0.25em 0.625em;
    font-size: 0.75em;
    font-weight: 600;
    border-radius: 0.25rem;
    text-align: center;
}

.status-processing {
    background-color: #e9f5fe;
    color: #1e88e5;
}

.status-submitted {
    background-color: #e7f9fc;
    color: #17a2b8;
}

.status-paid {
    background-color: #e9f9ee;
    color: #198754;
}

.status-cancelled {
    background-color: #feecef;
    color: #dc3545;
}

/* Loading State */
.loading-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem 0;
}

/* Search Input */
.search-input {
    min-width: 300px;
    border-color: #ced4da;
    transition: all 0.2s;
}

.search-input:focus {
    border-color: #198754;
    box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
}

/* Button Styling */
.btn-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    padding: 0;
}

.btn-success {
    background-color: #198754;
    border-color: #198754;
}

.btn-success:hover {
    background-color: #157347;
    border-color: #146c43;
}

.btn-outline-secondary {
    border-color: #ced4da;
    color: #6c757d;
}

.btn-outline-secondary:hover {
    background-color: #f8f9fa;
    color: #343a40;
}

/* Cursor for clickable rows */
.cursor-pointer {
    cursor: pointer;
}

/* Pagination */
.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.pagination-info {
    font-size: 0.875rem;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .toolbar .flex-row-container {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
    }

    .toolbar .actions-menu,
    .toolbar .col {
        width: 100%;
    }

    .search-input {
        width: 100%;
        min-width: auto;
    }

    .pagination-container {
        flex-direction: column;
        align-items: center;
    }
}

/* Table & Pagination Styling */
.text-success {
    color: #198754;
}
.text-gray-400 {
    color: #9ca3af;
}
.container-fluid {
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
    overflow-x: auto;
    overflow-y: auto;
    max-height: calc(100vh - 250px);
    width: 100%;
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
    padding: 0.75rem;
    border: 1px solid #e5e7eb;
    text-align: left;
}
.table-auto th {
    background-color: #e7e7e7;
    border: 1px solid #e5e7eb;
    padding: 0.75rem;
    vertical-align: top;
}
.table-auto td {
    text-align: left;
    white-space: nowrap;
    overflow: visible;
    text-overflow: clip;
    word-wrap: break-word;
    font-size: 0.875rem;
}
.table-auto {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

/* Filter button styling */
.filter-btn {
    background: none;
    border: none;
    padding: 0;
    margin-left: 4px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #888;
    transition: color 0.2s ease;
}
.filter-btn:hover {
    color: #198754;
}
.text-green-500 {
    color: #10b981;
}

/* Status styling */
.text-yellow-600 {
    color: #d97706;
}
.text-blue-600 {
    color: #2563eb;
}
.text-green-600 {
    color: #059669;
}
.text-red-600 {
    color: #dc2626;
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
}

/* Style for form inputs inside filters */
.form-control {
    display: block;
    width: 100%;
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    font-weight: 400;
    line-height: 1.5;
    color: #212529;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}
.form-control:focus {
    border-color: #198754;
    outline: 0;
    box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
}

/* Status filters */
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

/* Investment filter */
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

/* Search styles */
.search-input {
    width: 100%;
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    line-height: 1.25rem;
    color: #374151;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid #e5e7eb;
    appearance: none;
    border-radius: 0.375rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    min-width: 250px;
}

.search-input:focus {
    border-color: #10b981;
    outline: 0;
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

/* Table row styling */
.table-auto tbody tr {
    border-bottom: 1px solid #e5e7eb;
    transition: all 0.2s ease;
}

.table-auto tbody tr:hover {
    background-color: rgba(16, 185, 129, 0.05);
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
    white-space: nowrap;
    text-align: center;
    position: sticky;
    top: 0;
    background-color: #e7e7e7;
    z-index: 10;
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

/* Modal styling */
.modal-header {
    border-bottom: 1px solid #e5e7eb;
}

.modal-footer {
    border-top: 1px solid #e5e7eb;
}

.alert-warning {
    color: #856404;
    background-color: #fff3cd;
    border-color: #ffeeba;
}

.alert-danger {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}

.progress {
    height: 1rem;
    overflow: hidden;
    background-color: #e9ecef;
    border-radius: 0.25rem;
}

.progress-bar {
    display: flex;
    flex-direction: column;
    justify-content: center;
    color: #fff;
    text-align: center;
    white-space: nowrap;
    background-color: #198754;
    transition: width 0.6s ease;
}

.progress-bar-striped {
    background-image: linear-gradient(
        45deg,
        rgba(255, 255, 255, 0.15) 25%,
        transparent 25%,
        transparent 50%,
        rgba(255, 255, 255, 0.15) 50%,
        rgba(255, 255, 255, 0.15) 75%,
        transparent 75%,
        transparent
    );
    background-size: 1rem 1rem;
}

.progress-bar-animated {
    animation: progress-bar-stripes 1s linear infinite;
}

@keyframes progress-bar-stripes {
    from {
        background-position: 1rem 0;
    }
    to {
        background-position: 0 0;
    }
}

/* Empty state styling */
.empty-state {
    padding: 2rem;
    text-align: center;
    color: #6c757d;
}

.empty-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}
</style>
