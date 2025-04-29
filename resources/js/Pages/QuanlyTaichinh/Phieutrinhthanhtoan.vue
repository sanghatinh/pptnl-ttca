<template>
    <div class="page-container">
        <!-- Toolbar -->
        <div class="toolbar bg-white rounded-lg shadow-sm mb-4">
            <div class="flex-row-container py-3 px-4">
                <div class="actions-menu d-flex align-items-center gap-3">
                    <button
                        class="btn btn-success d-flex align-items-center gap-2"
                        @click="createNewRecord"
                    >
                        <i class="fas fa-plus"></i>
                        <span>Tạo mới</span>
                    </button>
                    <button
                        class="btn btn-outline-secondary d-flex align-items-center gap-2"
                    >
                        <i class="fas fa-file-export"></i>
                        <span>Xuất Excel</span>
                    </button>
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
        </div>

        <!-- Filters -->
        <div class="filters-container mb-4">
            <div class="row g-3 align-items-center">
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="filter-item">
                        <label class="form-label mb-1"
                            >Trạng thái thanh toán</label
                        >
                        <select class="form-select" v-model="statusFilter">
                            <option value="all">Tất cả trạng thái</option>
                            <option value="processing">Đang xử lý</option>
                            <option value="submitted">Đã nộp</option>
                            <option value="paid">Đã thanh toán</option>
                            <option value="cancelled">Đã hủy</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="filter-item">
                        <label class="form-label mb-1">Vụ đầu tư</label>
                        <select class="form-select" v-model="investmentFilter">
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
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="loading-container">
            <div class="spinner-border text-success" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Đang tải dữ liệu...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ error }}
        </div>

        <!-- Data Table -->
        <div v-else class="data-table-container">
            <!-- Table Container with Shadow and Rounded Corners -->
            <div class="table-container bg-white rounded-lg shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light text-nowrap">
                            <tr>
                                <th class="px-4 py-3" style="width: 40px">
                                    <input
                                        type="checkbox"
                                        class="form-check-input"
                                        @click="toggleSelectAll"
                                        :checked="isAllSelected"
                                    />
                                </th>
                                <th class="px-4 py-3">
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
                                </th>
                                <th class="px-4 py-3">
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
                                </th>
                                <th class="px-4 py-3">
                                    Vụ đầu tư
                                    <button
                                        @click="toggleFilter('vuDauTu')"
                                        class="filter-btn"
                                    >
                                        <i
                                            class="fas fa-filter"
                                            :class="{
                                                'text-green-500':
                                                    columnFilters.vuDauTu,
                                            }"
                                        ></i>
                                    </button>
                                </th>
                                <th class="px-4 py-3">
                                    Loại thanh toán
                                    <button
                                        @click="toggleFilter('loaiThanhToan')"
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
                                </th>
                                <th class="px-4 py-3">
                                    Trạng thái
                                    <button
                                        @click="
                                            toggleFilter('trangThaiThanhToan')
                                        "
                                        class="filter-btn"
                                    >
                                        <i
                                            class="fas fa-filter"
                                            :class="{
                                                'text-green-500':
                                                    columnFilters.trangThaiThanhToan,
                                            }"
                                        ></i>
                                    </button>
                                </th>
                                <th class="px-4 py-3">Số đợt thanh toán</th>
                                <th class="px-4 py-3">Số tờ trình</th>
                                <th class="px-4 py-3">Ngày tạo</th>
                                <th class="px-4 py-3">Tổng tiền thanh toán</th>
                                <th class="px-4 py-3">Người tạo</th>
                                <th
                                    class="px-4 py-3 text-center"
                                    style="width: 100px"
                                >
                                    Thao tác
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="filteredItems.length === 0">
                                <td colspan="10" class="text-center py-4">
                                    <div class="empty-state">
                                        <i
                                            class="bx bx-file-find empty-icon"
                                        ></i>
                                        <p>
                                            Không có dữ liệu phiếu trình thanh
                                            toán
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            <tr
                                v-for="item in paginatedItems"
                                :key="item.id"
                                @click="viewDetails(item)"
                                class="cursor-pointer"
                            >
                                <td class="px-4 py-3" @click.stop>
                                    <input
                                        type="checkbox"
                                        class="form-check-input"
                                        v-model="selectedItems"
                                        :value="item.id"
                                    />
                                </td>
                                <td class="px-4 py-3">
                                    {{ item.maTrinhThanhToan }}
                                </td>
                                <td class="px-4 py-3">{{ item.tieuDe }}</td>
                                <td class="px-4 py-3">{{ item.vuDauTu }}</td>
                                <td class="px-4 py-3">
                                    {{ item.loaiThanhToan }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="status-badge"
                                        :class="
                                            statusBadgeClass(
                                                item.trangThaiThanhToan
                                            )
                                        "
                                    >
                                        {{
                                            formatStatus(
                                                item.trangThaiThanhToan
                                            )
                                        }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    {{ item.soDotThanhToan }}
                                </td>
                                <td class="px-4 py-3">{{ item.soToTrinh }}</td>
                                <td class="px-4 py-3">
                                    {{ formatDate(item.ngayTao) }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ item.tongTienThanhToan }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ item.nguoiTao }}
                                </td>
                                <td class="px-4 py-3 text-center" @click.stop>
                                    <div
                                        class="d-flex justify-content-center gap-2"
                                    >
                                        <button
                                            class="btn btn-icon btn-sm btn-outline-primary"
                                            @click.stop="editItem(item)"
                                            data-bs-toggle="tooltip"
                                            title="Chỉnh sửa"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button
                                            class="btn btn-icon btn-sm btn-outline-danger"
                                            @click.stop="deleteItem(item)"
                                            data-bs-toggle="tooltip"
                                            title="Xóa"
                                        >
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div
                class="pagination-container mt-4 d-flex justify-content-between align-items-center"
            >
                <div class="pagination-info">
                    <span class="text-muted">
                        Hiển thị {{ paginationInfo.from }}-{{
                            paginationInfo.to
                        }}
                        trên {{ filteredItems.length }} bản ghi
                    </span>
                </div>
                <div class="pagination-controls">
                    <Bootstrap5Pagination
                        :data="paginationData"
                        @pagination-change-page="pageChanged"
                        :limit="3"
                        :classes="{
                            list: 'pagination',
                            item: 'page-item',
                            link: 'page-link px-3 py-2 rounded mx-1 border',
                            next: 'page-item',
                            prev: 'page-item',
                            active: 'active bg-success border-success',
                            disabled: 'disabled',
                        }"
                    />
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
</style>
