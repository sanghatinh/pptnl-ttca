<template>
    <div class="debt-details-container">
        <breadcrumb-vue />

        <!-- Loading indicator -->
        <div v-if="isLoading" class="text-center my-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Đang tải dữ liệu...</p>
        </div>

        <!-- Error message display -->
        <div v-else-if="error" class="alert alert-danger" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ error }}
        </div>

        <!-- Main content when loaded -->
        <div v-else>
            <div class="row">
                <!-- Customer Information Card -->
                <div class="col-12 col-lg-4 mb-4">
                    <div class="card h-100 info-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i
                                    class="fas fa-user-tie me-2 text-primary"
                                ></i>
                                Thông tin khách hàng
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="info-item">
                                <div class="info-label">Khách hàng cá nhân</div>
                                <div class="info-value">
                                    {{ document.khach_hang_ca_nhan || "" }}
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Mã KH cá nhân</div>
                                <div class="info-value">
                                    {{ document.ma_khach_hang_ca_nhan || "" }}
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">KH doanh nghiệp</div>
                                <div class="info-value">
                                    {{ document.khach_hang_doanh_nghiep || "" }}
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Mã KH doanh nghiệp</div>
                                <div class="info-value">
                                    {{
                                        document.ma_khach_hang_doanh_nghiep ||
                                        ""
                                    }}
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Trạm</div>
                                <div class="info-value">
                                    {{ document.tram || "" }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Debt Information Card -->
                <div class="col-12 col-lg-4 mb-4">
                    <div class="card h-100 info-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i
                                    class="fas fa-file-invoice me-2 text-success"
                                ></i>
                                Thông tin Công nợ
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="info-item">
                                <div class="info-label">Invoice Number</div>
                                <div class="info-value">
                                    {{ document.invoicenumber || "" }}
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Vụ đầu tư</div>
                                <div class="info-value">
                                    {{ document.vu_dau_tu || "" }}
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Category Debt</div>
                                <div class="info-value">
                                    <span
                                        :class="
                                            getCategoryBadgeClass(
                                                document.category_debt
                                            )
                                        "
                                    >
                                        {{ document.category_debt || "" }}
                                    </span>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Description</div>
                                <div class="info-value description-text">
                                    {{ document.description || "" }}
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Ngày phát sinh</div>
                                <div class="info-value">
                                    {{ formatDate(document.ngay_phat_sinh) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financial Information Card -->
                <div class="col-12 col-lg-4 mb-4">
                    <div class="card h-100 info-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i
                                    class="fas fa-money-bill-wave me-2 text-danger"
                                ></i>
                                Thông tin tài chính
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <div class="info-label">
                                        Số tiền theo giá trị đầu tư
                                    </div>
                                    <div class="info-value fw-bold">
                                        {{
                                            formatCurrency(
                                                document.so_tien_theo_gia_tri_dau_tu
                                            )
                                        }}
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="info-label">Lãi suất</div>
                                    <div class="info-value">
                                        {{
                                            document.lai_suat
                                                ? document.lai_suat + "%"
                                                : ""
                                        }}
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="info-label">Loại tiền</div>
                                    <div class="info-value">
                                        {{ document.loai_tien || "" }}
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="info-label">Tỷ giá quy đổi</div>
                                    <div class="info-value">
                                        {{
                                            formatCurrency(
                                                document.ty_gia_quy_doi
                                            )
                                        }}
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="financial-summary">
                                        <div class="financial-row bg-blue-50">
                                            <span class="financial-label"
                                                >Tổng nợ gốc</span
                                            >
                                            <span
                                                class="financial-value text-blue-700"
                                            >
                                                {{
                                                    formatCurrency(
                                                        document.so_tien_no_goc_da_quy
                                                    )
                                                }}
                                            </span>
                                        </div>
                                        <div class="financial-row bg-green-50">
                                            <span class="financial-label"
                                                >Đã trả gốc</span
                                            >
                                            <span
                                                class="financial-value text-green-700"
                                            >
                                                {{
                                                    formatCurrency(
                                                        document.da_tra_goc
                                                    )
                                                }}
                                            </span>
                                        </div>
                                        <div class="financial-row bg-red-50">
                                            <span class="financial-label"
                                                >Số tiền còn lại</span
                                            >
                                            <span
                                                class="financial-value text-red-700"
                                            >
                                                {{
                                                    formatCurrency(
                                                        document.so_tien_con_lai
                                                    )
                                                }}
                                            </span>
                                        </div>
                                        <div class="financial-row bg-amber-50">
                                            <span class="financial-label"
                                                >Tiền lãi</span
                                            >
                                            <span
                                                class="financial-value text-amber-700"
                                            >
                                                {{
                                                    formatCurrency(
                                                        document.tien_lai
                                                    )
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Debt Recovery Details Table -->
            <div class="card mb-4">
                <div
                    class="card-header d-flex justify-content-between align-items-center"
                >
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2 text-indigo-600"></i>
                        Chi tiết thu hồi nợ
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover payment-history-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã số phiếu thu</th>
                                    <th>Invoice Number</th>
                                    <th class="text-end">Đã trả gốc</th>
                                    <th class="text-end">Tiền lãi</th>
                                    <th>Ngày trả</th>
                                    <th>Category Debt</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="paymentHistory.length === 0">
                                    <td colspan="7" class="text-center py-4">
                                        Không có dữ liệu thu hồi nợ
                                    </td>
                                </tr>
                                <tr
                                    v-for="(payment, index) in paginatedHistory"
                                    :key="index"
                                >
                                    <td>
                                        <span class="fw-medium">{{
                                            payment.receipt_code
                                        }}</span>
                                    </td>
                                    <td>{{ payment.invoice_number }}</td>
                                    <td class="text-end text-success fw-medium">
                                        {{
                                            formatCurrency(
                                                payment.principal_paid
                                            )
                                        }}
                                    </td>
                                    <td class="text-end text-warning fw-medium">
                                        {{
                                            formatCurrency(
                                                payment.interest_paid
                                            )
                                        }}
                                    </td>
                                    <td>
                                        {{ formatDate(payment.payment_date) }}
                                    </td>
                                    <td>
                                        <span
                                            :class="
                                                getCategoryBadgeClass(
                                                    payment.category_debt
                                                )
                                            "
                                        >
                                            {{ payment.category_debt }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div
                    class="card-footer bg-light d-flex justify-content-between align-items-center"
                >
                    <div class="text-muted small">
                        Tổng số: {{ paymentHistory.length }} phiếu
                    </div>
                    <nav v-if="showPagination" aria-label="Page navigation">
                        <ul class="pagination pagination-sm mb-0">
                            <li
                                :class="[
                                    'page-item',
                                    { disabled: currentPage === 1 },
                                ]"
                            >
                                <a
                                    class="page-link"
                                    href="#"
                                    @click.prevent="prevPage"
                                >
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                            <li
                                v-for="page in totalPages"
                                :key="page"
                                :class="[
                                    'page-item',
                                    { active: currentPage === page },
                                ]"
                            >
                                <a
                                    class="page-link"
                                    href="#"
                                    @click.prevent="goToPage(page)"
                                >
                                    {{ page }}
                                </a>
                            </li>
                            <li
                                :class="[
                                    'page-item',
                                    { disabled: currentPage === totalPages },
                                ]"
                            >
                                <a
                                    class="page-link"
                                    href="#"
                                    @click.prevent="nextPage"
                                >
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="d-flex justify-content-end mb-4 gap-2">
                <button @click="goBack" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import { useStore } from "../../Store/Auth";
// import { ref, computed, onMounted } from "vue";
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
    name: "Details_CongnoDichvuKhautru",
    data() {
        return {
            isLoading: true,
            error: null,
            document: {},
            canEdit: false,
            paymentHistory: [],
            currentPage: 1,
            itemsPerPage: 10,
        };
    },
    computed: {
        paginatedHistory() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            return this.paymentHistory.slice(start, end);
        },
        totalPages() {
            return Math.ceil(this.paymentHistory.length / this.itemsPerPage);
        },
        showPagination() {
            return this.totalPages > 1;
        },
    },
    created() {
        this.fetchDebtDetails();
    },
    methods: {
        fetchDebtDetails() {
            const invoicenumber = this.$route.params.id;
            this.isLoading = true;
            this.error = null;

            axios
                .get(`/api/congno-dichvu-khautru/${invoicenumber}`, {
                    headers: {
                        Authorization: "Bearer " + this.store.getToken,
                    },
                })
                .then((response) => {
                    if (response.data.success) {
                        this.document = response.data.document;
                        this.paymentHistory =
                            response.data.paymentHistory || [];

                        // Make sure numeric values are properly formatted for display
                        this.formatNumericValues();
                    } else {
                        this.error =
                            response.data.message ||
                            "Không thể tải dữ liệu công nợ";
                    }
                })
                .catch((error) => {
                    console.error("Error fetching debt details:", error);
                    this.error =
                        error.response?.data?.message ||
                        "Đã xảy ra lỗi khi tải dữ liệu công nợ";

                    if (error.response?.status === 401) {
                        this.handleAuthError();
                    }
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },
        handleAuthError() {
            // Clear authentication data
            localStorage.removeItem("web_token");
            localStorage.removeItem("web_user");

            // Navigate to login page
            this.$router.push("/login");
        },
        formatDate(dateString) {
            if (!dateString) return "N/A";
            const date = new Date(dateString);
            return new Intl.DateTimeFormat("vi-VN", {
                day: "2-digit",
                month: "2-digit",
                year: "numeric",
            }).format(date);
        },
        formatNumericValues() {
            // Ensure numeric fields are properly parsed as numbers
            const numericFields = [
                "so_tien_theo_gia_tri_dau_tu",
                "ty_gia_quy_doi",
                "so_tien_no_goc_da_quy",
                "da_tra_goc",
                "so_tien_con_lai",
                "tien_lai",
                "lai_suat",
            ];

            numericFields.forEach((field) => {
                if (
                    this.document[field] !== undefined &&
                    this.document[field] !== null
                ) {
                    this.document[field] = parseFloat(this.document[field]);
                }
            });

            // Also ensure payment history numeric values are parsed
            this.paymentHistory.forEach((payment) => {
                if (payment.principal_paid !== undefined) {
                    payment.principal_paid = parseFloat(payment.principal_paid);
                }
                if (payment.interest_paid !== undefined) {
                    payment.interest_paid = parseFloat(payment.interest_paid);
                }
            });
        },
        goToPage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
            }
        },
        prevPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
            }
        },
        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
            }
        },
        formatCurrency(value, showCurrency = true) {
            if (value === undefined || value === null) return "N/A";
            const formatter = new Intl.NumberFormat("vi-VN", {
                style: showCurrency ? "currency" : "decimal",
                currency: "VND",
                minimumFractionDigits: 0,
            });
            return formatter.format(value);
        },
        getCategoryBadgeClass(category) {
            if (!category) return "badge bg-secondary text-white";

            // Map different categories to different colors
            const categoryColors = {
                "Ứng dầu": "badge bg-blue-100 text-blue-800",
                MMTB: "badge bg-red-100 text-red-800",
                "Sữa chữa": "badge bg-purple-100 text-purple-800",
                "Ứng tiền trồng": "badge bg-amber-100 text-amber-800",
                "Ứng tiền chăm sóc": "badge bg-green-100 text-green-700",
            };

            // Return the corresponding class or a default one
            return categoryColors[category] || "badge bg-secondary text-white";
        },
        goBack() {
            this.$router.push("/CongnoDichvuKhautru");
        },
    },
};
</script>

<style scoped>
/* Base styling */
.debt-details-container {
    padding: 0.5rem;
}

/* Summary card styling */
.summary-card {
    border-radius: 0.75rem;
    border: none;
    background-color: #fff;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1),
        0 2px 4px -1px rgba(0, 0, 0, 0.06);
    transition: box-shadow 0.3s ease;
    overflow: hidden;
}

.summary-card:hover {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1),
        0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.summary-description {
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.summary-financial-data {
    background-color: #f9fafb;
    padding: 0.75rem;
    border-radius: 0.5rem;
}

.financial-summary-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.financial-summary-item:last-child {
    margin-bottom: 0;
}

.important-value {
    padding-top: 0.5rem;
    margin-top: 0.5rem;
    border-top: 1px dashed #e5e7eb;
}

/* Card styling */
.info-card {
    border-radius: 0.75rem;
    border: none;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
}

.info-card:hover {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 0 1px 3px rgba(0, 0, 0, 0.08);
    transform: translateY(-2px);
}

.card-header {
    background-color: #f9fafb;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    border-top-left-radius: 0.75rem !important;
    border-top-right-radius: 0.75rem !important;
}

.card-title {
    font-weight: 600;
    font-size: 1rem;
    color: #111827;
    margin: 0;
}

/* Info item styling */
.info-item {
    margin-bottom: 1.25rem;
}

.info-item:last-child {
    margin-bottom: 0;
}

.info-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6b7280;
    margin-bottom: 0.25rem;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.info-value {
    font-size: 0.95rem;
    color: #374151;
}

.description-text {
    white-space: pre-line;
    max-height: 100px;
    overflow-y: auto;
}

/* Financial summary section */
.financial-summary {
    margin-top: 0.5rem;
    border-radius: 0.5rem;
    overflow: hidden;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.financial-row {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 1rem;
    align-items: center;
    border-bottom: 1px solid rgba(0, 0, 0, 0.04);
}

.financial-row:last-child {
    border-bottom: none;
}

.financial-label {
    font-size: 0.875rem;
    font-weight: 500;
}

.financial-value {
    font-size: 0.925rem;
    font-weight: 600;
}

/* Background colors */
.bg-blue-50 {
    background-color: rgba(239, 246, 255, 0.7);
}

.text-blue-700 {
    color: #1d4ed8;
}

.bg-green-50 {
    background-color: rgba(236, 253, 245, 0.7);
}

.text-green-700 {
    color: #047857;
}

.bg-red-50 {
    background-color: rgba(254, 242, 242, 0.7);
}

.text-red-700 {
    color: #b91c1c;
}

.bg-amber-50 {
    background-color: rgba(255, 251, 235, 0.7);
}

.text-amber-700 {
    color: #b45309;
}

.text-indigo-600 {
    color: #4f46e5;
}

/* Badge styling */
.badge {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.35em 0.65em;
    border-radius: 0.25rem;
    display: inline-block;
}

/* Payment history table */
.payment-history-table {
    margin-bottom: 0;
}

.payment-history-table th {
    font-weight: 600;
    font-size: 0.875rem;
    color: #4b5563;
}

.payment-history-table td {
    vertical-align: middle;
    font-size: 0.925rem;
    padding: 0.75rem 1rem;
}

/* Button styling */
.btn-icon {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.375rem;
}

/* Responsive adjustments */
@media (max-width: 767.98px) {
    .summary-financial-data {
        margin-top: 1rem;
        text-align: left;
    }

    .info-card {
        margin-bottom: 1rem;
    }

    .payment-history-table {
        white-space: nowrap;
    }
}
</style>
