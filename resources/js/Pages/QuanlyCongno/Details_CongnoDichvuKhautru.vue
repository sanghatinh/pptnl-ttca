<template>
    <div class="debt-details-container">
        <breadcrumb-vue />
        <!-- Loading starts -->
        <div id="loading-wrapper" v-if="isLoading">
            <div class="spinner-border" role="status">
                <span class="sr-only">Loading...</span>
            </div>
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
                                            ).class
                                        "
                                    >
                                        <i
                                            :class="
                                                getCategoryBadgeClass(
                                                    document.category_debt
                                                ).icon + ' me-1'
                                            "
                                        ></i>
                                        {{ document.category_debt }}
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
                                                document.so_tien_theo_gia_tri_dau_tu,
                                                false
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
                    <button
                        v-if="
                            Object.values(columnFilters).some(Boolean) ||
                            Object.values(selectedFilterValues).some(
                                (arr) => arr.length > 0
                            )
                        "
                        @click="resetAllFilters"
                        class="btn btn-sm btn-outline-success"
                        title="Đặt lại tất cả bộ lọc"
                    >
                        <i class="fas fa-redo-alt me-1"></i> Đặt lại bộ lọc
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover payment-history-table">
                            <thead class="table-light">
                                <tr>
                                    <th>
                                        Mã số phiếu thu
                                        <button
                                            @click="
                                                toggleFilter('receipt_code')
                                            "
                                            class="filter-btn"
                                        >
                                            <i
                                                class="fas fa-filter"
                                                :class="{
                                                    'text-green-500':
                                                        columnFilters.receipt_code,
                                                }"
                                            ></i>
                                        </button>
                                        <div
                                            v-if="
                                                activeFilter === 'receipt_code'
                                            "
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                        >
                                            <input
                                                type="text"
                                                v-model="
                                                    columnFilters.receipt_code
                                                "
                                                class="form-control mb-2"
                                                placeholder="Lọc theo mã phiếu..."
                                            />
                                            <div class="flex justify-between">
                                                <button
                                                    @click="
                                                        resetFilter(
                                                            'receipt_code'
                                                        )
                                                    "
                                                    class="btn btn-sm btn-light"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="
                                                        applyFilter(
                                                            'receipt_code'
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
                                        Invoice Number
                                        <button
                                            @click="
                                                toggleFilter('invoice_number')
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
                                            <div class="flex justify-between">
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
                                    <th>
                                        Mã giải ngân
                                        <button
                                            @click="
                                                toggleFilter(
                                                    'disbursement_code'
                                                )
                                            "
                                            class="filter-btn"
                                        >
                                            <i
                                                class="fas fa-filter"
                                                :class="{
                                                    'text-green-500':
                                                        columnFilters.disbursement_code,
                                                }"
                                            ></i>
                                        </button>
                                        <div
                                            v-if="
                                                activeFilter ===
                                                'disbursement_code'
                                            "
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                        >
                                            <input
                                                type="text"
                                                v-model="
                                                    columnFilters.disbursement_code
                                                "
                                                class="form-control mb-2"
                                                placeholder="Lọc theo mã giải ngân..."
                                            />
                                            <div class="flex justify-between">
                                                <button
                                                    @click="
                                                        resetFilter(
                                                            'disbursement_code'
                                                        )
                                                    "
                                                    class="btn btn-sm btn-light"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="
                                                        applyFilter(
                                                            'disbursement_code'
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
                                        Số tờ trình
                                        <button
                                            @click="
                                                toggleFilter('proposal_number')
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
                                            <div class="flex justify-between">
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
                                    <th class="text-end">
                                        Đã trả gốc
                                        <button
                                            @click="
                                                toggleFilter('principal_paid')
                                            "
                                            class="filter-btn"
                                        >
                                            <i
                                                class="fas fa-filter"
                                                :class="{
                                                    'text-green-500':
                                                        columnFilters.principal_paid,
                                                }"
                                            ></i>
                                        </button>
                                        <div
                                            v-if="
                                                activeFilter ===
                                                'principal_paid'
                                            "
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                        >
                                            <div class="mb-2">
                                                <label class="form-label"
                                                    >Từ số tiền:</label
                                                >
                                                <input
                                                    type="number"
                                                    v-model="
                                                        columnFilters.principal_paid_from
                                                    "
                                                    class="form-control"
                                                    placeholder="KIP"
                                                />
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label"
                                                    >Đến số tiền:</label
                                                >
                                                <input
                                                    type="number"
                                                    v-model="
                                                        columnFilters.principal_paid_to
                                                    "
                                                    class="form-control"
                                                    placeholder="KIP"
                                                />
                                            </div>
                                            <div class="flex justify-between">
                                                <button
                                                    @click="
                                                        resetFilter(
                                                            'principal_paid'
                                                        )
                                                    "
                                                    class="btn btn-sm btn-light"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="
                                                        applyFilter(
                                                            'principal_paid'
                                                        )
                                                    "
                                                    class="btn btn-sm btn-success"
                                                >
                                                    Apply
                                                </button>
                                            </div>
                                        </div>
                                    </th>
                                    <th class="text-end">
                                        Tiền lãi
                                        <button
                                            @click="
                                                toggleFilter('interest_paid')
                                            "
                                            class="filter-btn"
                                        >
                                            <i
                                                class="fas fa-filter"
                                                :class="{
                                                    'text-green-500':
                                                        columnFilters.interest_paid,
                                                }"
                                            ></i>
                                        </button>
                                        <div
                                            v-if="
                                                activeFilter === 'interest_paid'
                                            "
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                        >
                                            <div class="mb-2">
                                                <label class="form-label"
                                                    >Từ số tiền:</label
                                                >
                                                <input
                                                    type="number"
                                                    v-model="
                                                        columnFilters.interest_paid_from
                                                    "
                                                    class="form-control"
                                                    placeholder="KIP"
                                                />
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label"
                                                    >Đến số tiền:</label
                                                >
                                                <input
                                                    type="number"
                                                    v-model="
                                                        columnFilters.interest_paid_to
                                                    "
                                                    class="form-control"
                                                    placeholder="KIP"
                                                />
                                            </div>
                                            <div class="flex justify-between">
                                                <button
                                                    @click="
                                                        resetFilter(
                                                            'interest_paid'
                                                        )
                                                    "
                                                    class="btn btn-sm btn-light"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="
                                                        applyFilter(
                                                            'interest_paid'
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
                                        Ngày trả
                                        <button
                                            @click="
                                                toggleFilter('payment_date')
                                            "
                                            class="filter-btn"
                                        >
                                            <i
                                                class="fas fa-filter"
                                                :class="{
                                                    'text-green-500':
                                                        columnFilters.payment_date,
                                                }"
                                            ></i>
                                        </button>
                                        <div
                                            v-if="
                                                activeFilter === 'payment_date'
                                            "
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                        >
                                            <div class="mb-2">
                                                <label class="form-label"
                                                    >Từ ngày:</label
                                                >
                                                <input
                                                    type="date"
                                                    v-model="
                                                        columnFilters.payment_date_from
                                                    "
                                                    class="form-control"
                                                />
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label"
                                                    >Đến ngày:</label
                                                >
                                                <input
                                                    type="date"
                                                    v-model="
                                                        columnFilters.payment_date_to
                                                    "
                                                    class="form-control"
                                                />
                                            </div>
                                            <div class="flex justify-between">
                                                <button
                                                    @click="
                                                        resetFilter(
                                                            'payment_date'
                                                        )
                                                    "
                                                    class="btn btn-sm btn-light"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="
                                                        applyFilter(
                                                            'payment_date'
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
                                        Category Debt
                                        <button
                                            @click="
                                                toggleFilter('category_debt')
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
                                                activeFilter === 'category_debt'
                                            "
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                        >
                                            <div
                                                class="max-h-40 overflow-y-auto"
                                            >
                                                <div
                                                    v-for="category in uniqueCategories"
                                                    :key="category"
                                                    class="form-check mb-1"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        class="form-check-input"
                                                        :id="`category-${category}`"
                                                        :value="category"
                                                        v-model="
                                                            selectedFilterValues.category_debt
                                                        "
                                                    />
                                                    <label
                                                        class="form-check-label"
                                                        :for="`category-${category}`"
                                                        >{{ category }}</label
                                                    >
                                                </div>
                                            </div>
                                            <div
                                                class="flex justify-between mt-2"
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
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="paymentHistory.length === 0">
                                    <td colspan="8" class="text-center py-4">
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
                                    <td>
                                        <router-link
                                            v-if="payment.disbursement_code"
                                            :to="`/Details_Phieudenghithanhtoandichvu/${payment.disbursement_code}`"
                                            class="disbursement-code-link"
                                        >
                                            {{ payment.disbursement_code }}
                                        </router-link>
                                        <span v-else>N/A</span>
                                    </td>
                                    <!-- New column -->
                                    <td>
                                        {{ payment.proposal_number || "N/A" }}
                                    </td>
                                    <!-- New column -->
                                    <td class="text-end text-success fw-medium">
                                        {{
                                            formatCurrency(
                                                payment.principal_paid
                                            )
                                        }}
                                    </td>
                                    <td
                                        class="text-end text-amber-700 fw-medium"
                                    >
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
                                                ).class
                                            "
                                        >
                                            <i
                                                :class="
                                                    getCategoryBadgeClass(
                                                        payment.category_debt
                                                    ).icon + ' me-1'
                                                "
                                            ></i>
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
            activeFilter: null,
            columnFilters: {
                receipt_code: "",
                invoice_number: "",
                disbursement_code: "",
                proposal_number: "",
                principal_paid: "",
                principal_paid_from: "",
                principal_paid_to: "",
                interest_paid: "",
                interest_paid_from: "",
                interest_paid_to: "",
                payment_date: "",
                payment_date_from: "",
                payment_date_to: "",
                category_debt: "",
            },
            selectedFilterValues: {
                category_debt: [],
            },
        };
    },
    computed: {
        uniqueCategories() {
            // Get all unique category values that exist in the data
            return [
                ...new Set(
                    this.paymentHistory.map((payment) => payment.category_debt)
                ),
            ].filter(Boolean);
        },

        filteredPaymentHistory() {
            return this.paymentHistory.filter((payment) => {
                // Apply filters for each column
                const matchesReceiptCode =
                    !this.columnFilters.receipt_code ||
                    payment.receipt_code
                        ?.toLowerCase()
                        .includes(
                            this.columnFilters.receipt_code.toLowerCase()
                        );

                const matchesInvoiceNumber =
                    !this.columnFilters.invoice_number ||
                    payment.invoice_number
                        ?.toLowerCase()
                        .includes(
                            this.columnFilters.invoice_number.toLowerCase()
                        );

                const matchesDisbursementCode =
                    !this.columnFilters.disbursement_code ||
                    payment.disbursement_code
                        ?.toLowerCase()
                        .includes(
                            this.columnFilters.disbursement_code.toLowerCase()
                        );

                const matchesProposalNumber =
                    !this.columnFilters.proposal_number ||
                    payment.proposal_number
                        ?.toLowerCase()
                        .includes(
                            this.columnFilters.proposal_number.toLowerCase()
                        );

                // Number range filters
                const matchesPrincipalPaid =
                    (!this.columnFilters.principal_paid_from ||
                        payment.principal_paid >=
                            parseFloat(
                                this.columnFilters.principal_paid_from
                            )) &&
                    (!this.columnFilters.principal_paid_to ||
                        payment.principal_paid <=
                            parseFloat(this.columnFilters.principal_paid_to));

                const matchesInterestPaid =
                    (!this.columnFilters.interest_paid_from ||
                        payment.interest_paid >=
                            parseFloat(
                                this.columnFilters.interest_paid_from
                            )) &&
                    (!this.columnFilters.interest_paid_to ||
                        payment.interest_paid <=
                            parseFloat(this.columnFilters.interest_paid_to));

                // Date range filters
                const paymentDate = payment.payment_date
                    ? new Date(payment.payment_date)
                    : null;
                const matchesPaymentDate =
                    (!this.columnFilters.payment_date_from ||
                        !paymentDate ||
                        paymentDate >=
                            new Date(this.columnFilters.payment_date_from)) &&
                    (!this.columnFilters.payment_date_to ||
                        !paymentDate ||
                        paymentDate <=
                            new Date(this.columnFilters.payment_date_to));

                // Category filter (checkbox-based)
                const matchesCategory =
                    this.selectedFilterValues.category_debt.length === 0 ||
                    this.selectedFilterValues.category_debt.includes(
                        payment.category_debt
                    );

                return (
                    matchesReceiptCode &&
                    matchesInvoiceNumber &&
                    matchesDisbursementCode &&
                    matchesProposalNumber &&
                    matchesPrincipalPaid &&
                    matchesInterestPaid &&
                    matchesPaymentDate &&
                    matchesCategory
                );
            });
        },

        paginatedHistory() {
            // Update to use filteredPaymentHistory instead of paymentHistory
            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            return this.filteredPaymentHistory.slice(start, end);
        },

        totalPages() {
            // Update to use filteredPaymentHistory
            return Math.ceil(
                this.filteredPaymentHistory.length / this.itemsPerPage
            );
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

            // ตรวจสอบ token ก่อนเรียก API
            const token =
                localStorage.getItem("web_token") || this.store.getToken;
            if (!token) {
                // ถ้าไม่มี token ให้ไปหน้า login ทันที
                this.$router.replace("/login");
                return;
            }

            axios
                .get(`/api/congno-dichvu-khautru/${invoicenumber}`, {
                    headers: {
                        Authorization: "Bearer " + this.store.getToken,
                        Accept: "application/json",
                        "Content-Type": "application/json",
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

                    if (error.response?.status === 401) {
                        this.handleAuthError();
                    } else if (error.response?.status === 403) {
                        this.error =
                            "Bạn không có quyền truy cập thông tin công nợ này.";
                        // Optionally redirect to unauthorized page
                        // this.$router.push("/unauthorized");
                    } else if (error.response?.status === 404) {
                        this.error = "Không tìm thấy thông tin công nợ.";
                    } else {
                        this.error =
                            error.response?.data?.message ||
                            "Đã xảy ra lỗi khi tải dữ liệu công nợ";
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
            this.store.logout();

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
        toggleFilter(column) {
            this.activeFilter = this.activeFilter === column ? null : column;
        },

        applyFilter(column) {
            // Apply the filter and hide the dropdown
            this.activeFilter = null;
            this.currentPage = 1;
        },

        resetFilter(column) {
            if (column === "category_debt") {
                this.selectedFilterValues.category_debt = [];
            } else if (column === "principal_paid") {
                this.columnFilters.principal_paid_from = "";
                this.columnFilters.principal_paid_to = "";
            } else if (column === "interest_paid") {
                this.columnFilters.interest_paid_from = "";
                this.columnFilters.interest_paid_to = "";
            } else if (column === "payment_date") {
                this.columnFilters.payment_date_from = "";
                this.columnFilters.payment_date_to = "";
            } else {
                this.columnFilters[column] = "";
            }

            // Reset to first page
            this.currentPage = 1;
        },

        resetAllFilters() {
            this.activeFilter = null;
            this.columnFilters = {
                receipt_code: "",
                invoice_number: "",
                disbursement_code: "",
                proposal_number: "",
                principal_paid: "",
                principal_paid_from: "",
                principal_paid_to: "",
                interest_paid: "",
                interest_paid_from: "",
                interest_paid_to: "",
                payment_date: "",
                payment_date_from: "",
                payment_date_to: "",
                category_debt: "",
            };
            this.selectedFilterValues = {
                category_debt: [],
            };
            this.currentPage = 1;
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
                currency: "KIP",
                minimumFractionDigits: 0,
            });
            return formatter.format(value);
        },
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

/* Filter dropdown positioning */

/* Add a subtle pointer indicator for dropdown */
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

/* Reset all filters button */
.reset-all-filters-btn {
    position: absolute;
    right: 5px;
    top: 5px;
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

/* Checkbox styling */
.form-check {
    display: block;
    min-height: 1.5rem;
    padding-left: 1.5em;
    margin-bottom: 0.125rem;
}
.form-check-input {
    width: 1em;
    height: 1em;
    margin-top: 0.25em;
    vertical-align: top;
    background-color: #fff;
    background-repeat: no-repeat;
    background-position: center;
    background-size: contain;
    border: 1px solid rgba(0, 0, 0, 0.25);
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    -webkit-print-color-adjust: exact;
    color-adjust: exact;
    margin-left: -1.5em;
}
.form-check-input[type="checkbox"] {
    border-radius: 0.25em;
}
.form-check-input:checked {
    background-color: #198754;
    border-color: #198754;
}
.form-check-label {
    margin-left: 0.5rem;
}

/* Overflow for category list */
.max-h-40.overflow-y-auto {
    max-height: 160px;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: #cbd5e0 #f7fafc;
}

/* Handle overflow for long content in dropdown filters */
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

/* Flexbox for button layout */
.flex.justify-between {
    display: flex;
    justify-content: space-between;
}

/* Button styling */
.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    border-radius: 0.2rem;
}
.btn-light {
    color: #000;
    background-color: #f8f9fa;
    border-color: #f8f9fa;
}
.btn-success {
    color: #fff;
    background-color: #198754;
    border-color: #198754;
}

/* Keep table header in proper position */
.table thead th {
    position: relative;
}

/* Position card body properly */
.card-body.p-0 {
    position: relative;
}

/* Make table responsive */
.table-responsive {
    min-height: 350px;
    position: relative;
    width: 100%;
    white-space: nowrap;
    overflow-x: auto; /* Enable horizontal scrolling if needed */
    -webkit-overflow-scrolling: touch; /* Smooth scrolling on touch devices */
}

table {
    width: 100%;
    border-collapse: collapse;
    table-layout: auto; /* Change from fixed to auto layout */
}

/* Ensure table headers have proper spacing */
.payment-history-table th {
    font-weight: 600;
    font-size: 0.875rem;
    color: #4b5563;
    white-space: nowrap;
    min-width: 120px; /* Minimum width for columns */
    padding: 0.75rem 1rem;
    position: relative;
}

/* Adjust filter dropdowns position */
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
/* Add this to the style section */
.disbursement-code-link {
    color: #0d6efd;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s ease;
}

.disbursement-code-link:hover {
    color: #0a58ca;
    text-decoration: underline;
}
</style>
