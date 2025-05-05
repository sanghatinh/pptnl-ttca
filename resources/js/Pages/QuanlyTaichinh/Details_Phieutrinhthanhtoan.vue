<template lang="">
    <breadcrumb-vue />
    <div class="card shadow">
        <div class="card-body p-0">
            <PerfectScrollbar
                :options="{
                    wheelSpeed: 1,
                    wheelPropagation: true,
                    minScrollbarLength: 20,
                }"
                class="scroll-area"
            >
                <!-- Fixed top container -->
                <div class="sticky-wrapper">
                    <!-- Add container with padding -->
                    <div class="container-fluid px-4">
                        <div class="button-container">
                            <!-- ปรับปรุงส่วนของปุ่มการกระทำ -->
                            <div class="action-button-group">
                                <button
                                    type="button"
                                    class="button-30"
                                    @click="saveBasicInfo"
                                >
                                    <i class="bx bxs-save"></i>
                                    <span>Lưu</span>
                                </button>

                                <!-- ปุ่มนำส่งฝ่ายบัญชี (แสดงเฉพาะเมื่อสถานะเป็น 'processing') -->
                                <button
                                    v-if="document.status === 'processing'"
                                    class="button-30-text-green"
                                    @click="
                                        confirmUpdateStatus(
                                            'submitted',
                                            'Xác nhận nộp phòng kế toán?',
                                            'Bạn có chắc chắn muốn nộp phiếu này cho phòng kế toán không?'
                                        )
                                    "
                                >
                                    <i class="bx bx-calendar-check"></i>
                                    <span>Nộp phòng kế toán</span>
                                </button>

                                <!-- ปุ่มส่งคืน (แสดงเฉพาะเมื่อสถานะเป็น 'processing') -->
                                <button
                                    v-if="document.status === 'submitted'"
                                    type="button"
                                    class="button-30"
                                    @click="
                                        confirmUpdateStatus(
                                            'processing',
                                            'Xác nhận trả về?',
                                            'Bạn có chắc chắn muốn trả về phiếu này không?'
                                        )
                                    "
                                >
                                    <i class="bx bx-calendar-x"></i>
                                    <span>Trả về</span>
                                </button>

                                <!-- ปุ่มสถานะจ่ายเงินแล้ว (แสดงเฉพาะเมื่อสถานะเป็น 'submitted') -->
                                <button
                                    v-if="document.status === 'submitted'"
                                    type="button"
                                    class="button-30-text-green"
                                    @click="
                                        confirmUpdateStatus(
                                            'paid',
                                            'Xác nhận đã thanh toán?',
                                            'Bạn có chắc chắn muốn đánh dấu phiếu này là đã thanh toán không?'
                                        )
                                    "
                                >
                                    <i class="bx bx-check-square"></i>
                                    <span>Đã thanh toán</span>
                                </button>

                                <!-- ปุ่มยกเลิก (แสดงเฉพาะเมื่อสถานะเป็น 'submitted') -->
                                <button
                                    v-if="document.status === 'submitted'"
                                    type="button"
                                    class="button-30"
                                    @click="
                                        confirmUpdateStatus(
                                            'cancelled',
                                            'Xác nhận hủy?',
                                            'Bạn có chắc chắn muốn hủy phiếu này không?'
                                        )
                                    "
                                >
                                    <i class="fa-solid fa-xmark"></i>
                                    <span>Hủy</span>
                                </button>

                                <!-- ปุ่มลบ (แสดงเฉพาะเมื่อไม่ได้อยู่ในสถานะ paid) -->
                                <button
                                    v-if="document.status !== 'paid'"
                                    type="button"
                                    class="button-30-del"
                                    @click="deleteDocument"
                                >
                                    <i class="fa-solid fa-trash-can"></i>
                                    <span>Xóa</span>
                                </button>

                                <button
                                    type="button"
                                    class="button-30"
                                    @click="printDocument"
                                >
                                    <i class="bx bxs-printer"></i>
                                    <span>Print</span>
                                </button>
                            </div>
                            <div class="row align-items-center mb-2"></div>
                        </div>
                        <!-- progress-tracker-container -->
                        <div class="progress-container mt-4">
                            <div class="col-12">
                                <div
                                    class="progress-tracker"
                                    :class="document.status || 'processing'"
                                >
                                    <!-- Pending Step -->
                                    <div
                                        class="track-step"
                                        :class="{
                                            active:
                                                document.status ===
                                                    'processing' ||
                                                document.status ===
                                                    'submitted' ||
                                                document.status === 'paid',
                                        }"
                                    >
                                        <div class="step-icon status-pending">
                                            <i class="fas fa-file-invoice"></i>
                                        </div>
                                        <span class="step-label"
                                            >Đang xử lý</span
                                        >
                                    </div>

                                    <!-- Approved Step -->
                                    <div
                                        class="track-step"
                                        :class="{
                                            active:
                                                document.status ===
                                                    'submitted' ||
                                                document.status === 'paid',
                                        }"
                                    >
                                        <div class="step-icon status-approved">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                        <span class="step-label"
                                            >Đã nộp kế toán</span
                                        >
                                    </div>

                                    <!-- Paid Step -->
                                    <div
                                        class="track-step"
                                        :class="{
                                            active: document.status === 'paid',
                                        }"
                                    >
                                        <div class="step-icon status-paid">
                                            <i
                                                class="fas fa-money-bill-wave"
                                            ></i>
                                        </div>
                                        <span class="step-label"
                                            >Đã thanh toán</span
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main content with added top margin -->
                <div class="main-content-wrapper">
                    <div class="d-flex flex-column flex-md-row gap-1">
                        <!-- Thông tin phiếu trình -->
                        <div class="card col-12 col-md-6">
                            <div class="card-body">
                                <h5 class="card-title mb-3 border-bottom pb-2">
                                    Thông tin phiếu trình
                                </h5>
                                <div class="row gutters">
                                    <!-- Mã trình thanh toán -->
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="maTrinh"
                                                class="form-label"
                                            >
                                                Mã trình thanh toán
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="maTrinh"
                                                :value="document.payment_code"
                                                disabled
                                            />
                                        </div>
                                    </div>

                                    <!-- Tiêu đề -->
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="tieuDe"
                                                class="form-label"
                                            >
                                                Tiêu đề
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="tieuDe"
                                                :value="document.title"
                                                disabled
                                            />
                                        </div>
                                    </div>

                                    <!-- Vụ đầu tư -->
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="vuDauTu"
                                                class="form-label"
                                            >
                                                Vụ đầu tư
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="vuDauTu"
                                                v-model="
                                                    document.investment_project
                                                "
                                            />
                                        </div>
                                    </div>

                                    <!-- Loại thanh toán -->
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="loaiThanhToan"
                                                class="form-label"
                                            >
                                                Loại thanh toán
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="loaiThanhToan"
                                                v-model="document.payment_type"
                                            />
                                        </div>
                                    </div>
                                    <!-- Người tạo -->
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="nguoiTao"
                                                class="form-label"
                                            >
                                                Người tạo
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="nguoiTao"
                                                :value="document.creator_name"
                                                disabled
                                            />
                                        </div>
                                    </div>
                                    <!-- Số tờ trình -->
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="soToTrinh"
                                                class="form-label"
                                            >
                                                Số tờ trình
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="soToTrinh"
                                                v-model="
                                                    document.proposal_number
                                                "
                                            />
                                        </div>
                                    </div>

                                    <!-- Ngày tạo -->
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="ngayTao"
                                                class="form-label"
                                            >
                                                Ngày tạo
                                            </label>
                                            <input
                                                type="date"
                                                class="form-control"
                                                id="ngayTao"
                                                v-model="document.created_at"
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="trangThai"
                                                class="form-label"
                                            >
                                                Trạng thái thanh toán
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="trangThai"
                                                :value="
                                                    formatStatus(
                                                        document.status
                                                    )
                                                "
                                                :class="
                                                    statusClass(document.status)
                                                "
                                                disabled
                                            />
                                        </div>
                                    </div>

                                    <!-- Số đợt thanh toán -->
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="soDotTT"
                                                class="form-label"
                                            >
                                                Số đợt thanh toán
                                            </label>
                                            <input
                                                type="number"
                                                min="1"
                                                class="form-control"
                                                id="soDotTT"
                                                v-model="
                                                    document.payment_installment
                                                "
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Thông tin tài chính -->
                        <!-- filepath: f:\Webpoject\TTCA_PTNL\ttca_ptnl\resources\js\Pages\QuanlyTaichinh\Details_Phieutrinhthanhtoan.vue -->
                        <!-- Replace the existing Thông tin tài chính card with this improved version -->
                        <div class="card col-12 col-md-6">
                            <div class="card-body">
                                <!-- Financial Summary -->
                                <h6 class="mb-3 border-bottom pb-2">
                                    Tổng hợp tài chính
                                </h6>
                                <div class="row gutters">
                                    <!-- Tổng tiền thanh toán -->
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="tongTien"
                                                class="form-label"
                                            >
                                                Tổng tiền thanh toán
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control text-end fw-medium"
                                                id="tongTien"
                                                :value="
                                                    formatCurrency(
                                                        totalPaymentAmount
                                                    )
                                                "
                                                disabled
                                            />
                                        </div>
                                    </div>

                                    <!-- Tổng tiền tạm giữ -->
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="tongTienTamGiu"
                                                class="form-label"
                                            >
                                                Tổng tiền tạm giữ
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control text-end fw-medium"
                                                id="tongTienTamGiu"
                                                :value="
                                                    formatCurrency(
                                                        totalHoldAmount
                                                    )
                                                "
                                                disabled
                                            />
                                        </div>
                                    </div>

                                    <!-- Tổng tiền khấu trừ -->
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="tongTienKhauTru"
                                                class="form-label"
                                            >
                                                Tổng tiền khấu trừ
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control text-end fw-medium"
                                                id="tongTienKhauTru"
                                                :value="
                                                    formatCurrency(
                                                        totalDeductionAmount
                                                    )
                                                "
                                                disabled
                                            />
                                        </div>
                                    </div>

                                    <!-- Tổng tiền lãi suất -->
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="tongTienLaiSuat"
                                                class="form-label"
                                            >
                                                Tổng tiền lãi suất
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control text-end fw-medium"
                                                id="tongTienLaiSuat"
                                                :value="
                                                    formatCurrency(
                                                        totalInterestAmount
                                                    )
                                                "
                                                disabled
                                            />
                                        </div>
                                    </div>

                                    <!-- Tổng tiền thanh toán còn lại -->
                                    <div class="col-12">
                                        <div class="form-group mb-1">
                                            <label
                                                for="tongTienConLai"
                                                class="form-label fw-bold"
                                            >
                                                Tổng tiền thanh toán còn lại
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control text-end fw-bold bg-light"
                                                id="tongTienConLai"
                                                :value="
                                                    formatCurrency(
                                                        totalRemainingAmount
                                                    )
                                                "
                                                disabled
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bảng chi tiết -->
                    <div class="card mt-3">
                        <div
                            class="card-header border-0 bg-transparent d-flex justify-content-between align-items-center"
                        >
                            <h5 class="card-title mb-0">
                                <span
                                    @click="togglePaymentDetails"
                                    class="toggle-section cursor-pointer"
                                >
                                    <i
                                        :class="
                                            showPaymentDetails
                                                ? 'fas fa-angle-down'
                                                : 'fas fa-angle-right'
                                        "
                                        class="me-2 toggle-icon"
                                    ></i>
                                    Chi tiết hồ sơ thanh toán
                                </span>
                            </h5>
                            <div class="card-actions" v-if="showPaymentDetails">
                                <span
                                    class="import-data-btn"
                                    title="Import data"
                                    @click="openImportModal"
                                >
                                    <i class="fas fa-file-import"></i>
                                </span>
                                <span
                                    class="export-excel-btn"
                                    title="Export to Excel"
                                    @click="exportToExcel"
                                >
                                    <i class="fas fa-file-excel"></i>
                                </span>
                                <span
                                    class="reset-all-filters-btn"
                                    title="Reset all filters"
                                    @click="resetAllFilters"
                                >
                                    <i class="fas fa-redo-alt"></i>
                                </span>
                                <span
                                    class="edit-records-btn"
                                    title="Edit selected records"
                                    @click="editSelectedRecords"
                                    :class="{
                                        disabled: selectedRecords.length === 0,
                                    }"
                                >
                                    <i class="fas fa-edit"></i>
                                </span>
                                <span
                                    class="delete-records-btn"
                                    title="Delete selected records"
                                    @click="deleteSelectedRecords"
                                    :class="{
                                        disabled: selectedRecords.length === 0,
                                    }"
                                >
                                    <i class="fas fa-trash"></i>
                                </span>
                                <span
                                    class="add-records-btn"
                                    title="Add new receipt"
                                    @click="openAddReceiptModal"
                                >
                                    <i class="fas fa-plus"></i>
                                </span>
                            </div>
                        </div>
                        <div class="card-body" v-if="showPaymentDetails">
                            <div class="table-container">
                                <div class="table-responsive mt-2">
                                    <table
                                        class="table table-bordered table-hover align-middle"
                                    >
                                        <thead class="table-light text-center">
                                            <tr>
                                                <th>
                                                    <input
                                                        type="checkbox"
                                                        :checked="isAllSelected"
                                                        @change="
                                                            toggleSelectAll
                                                        "
                                                        class="form-check-input"
                                                    />
                                                </th>
                                                <th>
                                                    Mã nghiệm thu
                                                    <button
                                                        @click="
                                                            toggleFilter(
                                                                'document_code'
                                                            )
                                                        "
                                                        class="filter-btn"
                                                    >
                                                        <i
                                                            class="fas fa-filter"
                                                            :class="{
                                                                'text-green-500':
                                                                    columnFilters.document_code,
                                                            }"
                                                        ></i>
                                                    </button>
                                                    <div
                                                        v-if="
                                                            activeFilter ===
                                                            'document_code'
                                                        "
                                                        class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                                    >
                                                        <input
                                                            type="text"
                                                            v-model="
                                                                columnFilters.document_code
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
                                                                        'document_code'
                                                                    )
                                                                "
                                                                class="btn btn-sm btn-light"
                                                            >
                                                                Reset
                                                            </button>
                                                            <button
                                                                @click="
                                                                    applyFilter(
                                                                        'document_code'
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
                                                    Trạm
                                                    <button
                                                        @click="
                                                            toggleFilter('tram')
                                                        "
                                                        class="filter-btn"
                                                    >
                                                        <i
                                                            class="fas fa-filter"
                                                            :class="{
                                                                'text-green-500':
                                                                    selectedFilterValues.tram &&
                                                                    selectedFilterValues
                                                                        .tram
                                                                        .length >
                                                                        0,
                                                            }"
                                                        ></i>
                                                    </button>
                                                    <div
                                                        v-if="
                                                            activeFilter ===
                                                            'tram'
                                                        "
                                                        class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
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
                                                                    :value="
                                                                        option
                                                                    "
                                                                    v-model="
                                                                        selectedFilterValues.tram
                                                                    "
                                                                    class="mr-2 rounded text-green-500 focus:ring-green-500"
                                                                />
                                                                <label
                                                                    :for="`tram-${option}`"
                                                                    class="select-none"
                                                                    >{{
                                                                        option
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
                                                                        'tram'
                                                                    )
                                                                "
                                                                class="btn btn-sm btn-light"
                                                            >
                                                                Reset
                                                            </button>
                                                            <button
                                                                @click="
                                                                    applyFilter(
                                                                        'tram'
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
                                                        @click="
                                                            toggleFilter(
                                                                'title'
                                                            )
                                                        "
                                                        class="filter-btn"
                                                    >
                                                        <i
                                                            class="fas fa-filter"
                                                            :class="{
                                                                'text-green-500':
                                                                    columnFilters.title,
                                                            }"
                                                        ></i>
                                                    </button>
                                                    <div
                                                        v-if="
                                                            activeFilter ===
                                                            'title'
                                                        "
                                                        class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                                    >
                                                        <input
                                                            type="text"
                                                            v-model="
                                                                columnFilters.title
                                                            "
                                                            class="form-control mb-2"
                                                            placeholder="Lọc theo tiêu đề..."
                                                        />
                                                        <div
                                                            class="flex justify-between"
                                                        >
                                                            <button
                                                                @click="
                                                                    resetFilter(
                                                                        'title'
                                                                    )
                                                                "
                                                                class="btn btn-sm btn-light"
                                                            >
                                                                Reset
                                                            </button>
                                                            <button
                                                                @click="
                                                                    applyFilter(
                                                                        'title'
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
                                                        @click="
                                                            toggleFilter(
                                                                'investment_project'
                                                            )
                                                        "
                                                        class="filter-btn"
                                                    >
                                                        <i
                                                            class="fas fa-filter"
                                                            :class="{
                                                                'text-green-500':
                                                                    selectedFilterValues.investment_project &&
                                                                    selectedFilterValues
                                                                        .investment_project
                                                                        .length >
                                                                        0,
                                                            }"
                                                        ></i>
                                                    </button>
                                                    <div
                                                        v-if="
                                                            activeFilter ===
                                                            'investment_project'
                                                        "
                                                        class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                                    >
                                                        <div
                                                            class="max-h-40 overflow-y-auto mb-2"
                                                        >
                                                            <div
                                                                v-for="option in uniqueValues.investment_project"
                                                                :key="option"
                                                                class="flex items-center mb-2"
                                                            >
                                                                <input
                                                                    type="checkbox"
                                                                    :id="`investment_project-${option}`"
                                                                    :value="
                                                                        option
                                                                    "
                                                                    v-model="
                                                                        selectedFilterValues.investment_project
                                                                    "
                                                                    class="mr-2 rounded text-green-500 focus:ring-green-500"
                                                                />
                                                                <label
                                                                    :for="`investment_project-${option}`"
                                                                    class="select-none"
                                                                    >{{
                                                                        option
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
                                                                        'investment_project'
                                                                    )
                                                                "
                                                                class="btn btn-sm btn-light"
                                                            >
                                                                Reset
                                                            </button>
                                                            <button
                                                                @click="
                                                                    applyFilter(
                                                                        'investment_project'
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
                                                    KH Cá nhân
                                                    <button
                                                        @click="
                                                            toggleFilter(
                                                                'khach_hang_ca_nhan_dt_mia'
                                                            )
                                                        "
                                                        class="filter-btn"
                                                    >
                                                        <i
                                                            class="fas fa-filter"
                                                            :class="{
                                                                'text-green-500':
                                                                    columnFilters.khach_hang_ca_nhan_dt_mia,
                                                            }"
                                                        ></i>
                                                    </button>
                                                    <div
                                                        v-if="
                                                            activeFilter ===
                                                            'khach_hang_ca_nhan_dt_mia'
                                                        "
                                                        class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                                    >
                                                        <input
                                                            type="text"
                                                            v-model="
                                                                columnFilters.khach_hang_ca_nhan_dt_mia
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
                                                                        'khach_hang_ca_nhan_dt_mia'
                                                                    )
                                                                "
                                                                class="btn btn-sm btn-light"
                                                            >
                                                                Reset
                                                            </button>
                                                            <button
                                                                @click="
                                                                    applyFilter(
                                                                        'khach_hang_ca_nhan_dt_mia'
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
                                                    KH Doanh nghiệp
                                                    <button
                                                        @click="
                                                            toggleFilter(
                                                                'khach_hang_doanh_nghiep_dt_mia'
                                                            )
                                                        "
                                                        class="filter-btn"
                                                    >
                                                        <i
                                                            class="fas fa-filter"
                                                            :class="{
                                                                'text-green-500':
                                                                    columnFilters.khach_hang_doanh_nghiep_dt_mia,
                                                            }"
                                                        ></i>
                                                    </button>
                                                    <div
                                                        v-if="
                                                            activeFilter ===
                                                            'khach_hang_doanh_nghiep_dt_mia'
                                                        "
                                                        class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                                    >
                                                        <input
                                                            type="text"
                                                            v-model="
                                                                columnFilters.khach_hang_doanh_nghiep_dt_mia
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
                                                                        'khach_hang_doanh_nghiep_dt_mia'
                                                                    )
                                                                "
                                                                class="btn btn-sm btn-light"
                                                            >
                                                                Reset
                                                            </button>
                                                            <button
                                                                @click="
                                                                    applyFilter(
                                                                        'khach_hang_doanh_nghiep_dt_mia'
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
                                                    Hợp đồng đầu tư
                                                    <button
                                                        @click="
                                                            toggleFilter(
                                                                'contract_number'
                                                            )
                                                        "
                                                        class="filter-btn"
                                                    >
                                                        <i
                                                            class="fas fa-filter"
                                                            :class="{
                                                                'text-green-500':
                                                                    columnFilters.contract_number,
                                                            }"
                                                        ></i>
                                                    </button>
                                                    <div
                                                        v-if="
                                                            activeFilter ===
                                                            'contract_number'
                                                        "
                                                        class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                                    >
                                                        <input
                                                            type="text"
                                                            v-model="
                                                                columnFilters.contract_number
                                                            "
                                                            class="form-control mb-2"
                                                            placeholder="Lọc theo hợp đồng..."
                                                        />
                                                        <div
                                                            class="flex justify-between"
                                                        >
                                                            <button
                                                                @click="
                                                                    resetFilter(
                                                                        'contract_number'
                                                                    )
                                                                "
                                                                class="btn btn-sm btn-light"
                                                            >
                                                                Reset
                                                            </button>
                                                            <button
                                                                @click="
                                                                    applyFilter(
                                                                        'contract_number'
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
                                                    Hình thức DV
                                                    <button
                                                        @click="
                                                            toggleFilter(
                                                                'service_type'
                                                            )
                                                        "
                                                        class="filter-btn"
                                                    >
                                                        <i
                                                            class="fas fa-filter"
                                                            :class="{
                                                                'text-green-500':
                                                                    selectedFilterValues.service_type &&
                                                                    selectedFilterValues
                                                                        .service_type
                                                                        .length >
                                                                        0,
                                                            }"
                                                        ></i>
                                                    </button>
                                                    <div
                                                        v-if="
                                                            activeFilter ===
                                                            'service_type'
                                                        "
                                                        class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                                    >
                                                        <div
                                                            class="max-h-40 overflow-y-auto mb-2"
                                                        >
                                                            <div
                                                                v-for="option in uniqueValues.service_type"
                                                                :key="option"
                                                                class="flex items-center mb-2"
                                                            >
                                                                <input
                                                                    type="checkbox"
                                                                    :id="`service_type-${option}`"
                                                                    :value="
                                                                        option
                                                                    "
                                                                    v-model="
                                                                        selectedFilterValues.service_type
                                                                    "
                                                                    class="mr-2 rounded text-green-500 focus:ring-green-500"
                                                                />
                                                                <label
                                                                    :for="`service_type-${option}`"
                                                                    class="select-none"
                                                                    >{{
                                                                        option
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
                                                                        'service_type'
                                                                    )
                                                                "
                                                                class="btn btn-sm btn-light"
                                                            >
                                                                Reset
                                                            </button>
                                                            <button
                                                                @click="
                                                                    applyFilter(
                                                                        'service_type'
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
                                                    Hợp đồng cung ứng DV
                                                    <button
                                                        @click="
                                                            toggleFilter(
                                                                'hop_dong_cung_ung_dich_vu'
                                                            )
                                                        "
                                                        class="filter-btn"
                                                    >
                                                        <i
                                                            class="fas fa-filter"
                                                            :class="{
                                                                'text-green-500':
                                                                    columnFilters.hop_dong_cung_ung_dich_vu,
                                                            }"
                                                        ></i>
                                                    </button>
                                                    <div
                                                        v-if="
                                                            activeFilter ===
                                                            'hop_dong_cung_ung_dich_vu'
                                                        "
                                                        class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                                    >
                                                        <input
                                                            type="text"
                                                            v-model="
                                                                columnFilters.hop_dong_cung_ung_dich_vu
                                                            "
                                                            class="form-control mb-2"
                                                            placeholder="Lọc theo hợp đồng..."
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
                                                                class="btn btn-sm btn-light"
                                                            >
                                                                Reset
                                                            </button>
                                                            <button
                                                                @click="
                                                                    applyFilter(
                                                                        'hop_dong_cung_ung_dich_vu'
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
                                                        <div
                                                            class="flex justify-between"
                                                        >
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
                                                    Đợt TT
                                                    <button
                                                        @click="
                                                            toggleFilter(
                                                                'installment'
                                                            )
                                                        "
                                                        class="filter-btn"
                                                    >
                                                        <i
                                                            class="fas fa-filter"
                                                            :class="{
                                                                'text-green-500':
                                                                    columnFilters.installment,
                                                            }"
                                                        ></i>
                                                    </button>
                                                    <div
                                                        v-if="
                                                            activeFilter ===
                                                            'installment'
                                                        "
                                                        class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                                    >
                                                        <input
                                                            type="text"
                                                            v-model="
                                                                columnFilters.installment
                                                            "
                                                            class="form-control mb-2"
                                                            placeholder="Lọc theo đợt..."
                                                        />
                                                        <div
                                                            class="flex justify-between"
                                                        >
                                                            <button
                                                                @click="
                                                                    resetFilter(
                                                                        'installment'
                                                                    )
                                                                "
                                                                class="btn btn-sm btn-light"
                                                            >
                                                                Reset
                                                            </button>
                                                            <button
                                                                @click="
                                                                    applyFilter(
                                                                        'installment'
                                                                    )
                                                                "
                                                                class="btn btn-sm btn-success"
                                                            >
                                                                Apply
                                                            </button>
                                                        </div>
                                                    </div>
                                                </th>

                                                <th>Số tiền</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-if="
                                                    paginatedPaymentDetails.data
                                                        .length === 0
                                                "
                                            >
                                                <td
                                                    colspan="13"
                                                    class="text-center py-4"
                                                >
                                                    <div class="empty-state">
                                                        <i
                                                            class="fas fa-file-invoice empty-icon"
                                                        ></i>
                                                        <p>
                                                            Chưa có dữ liệu chi
                                                            tiết
                                                        </p>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr
                                                v-for="(
                                                    item, index
                                                ) in paginatedPaymentDetails.data"
                                                :key="index"
                                            >
                                                <td class="text-center">
                                                    <input
                                                        type="checkbox"
                                                        :value="
                                                            item.document_code
                                                        "
                                                        v-model="
                                                            selectedRecords
                                                        "
                                                        class="form-check-input"
                                                    />
                                                </td>
                                                <td>
                                                    {{ item.document_code }}
                                                </td>
                                                <td>
                                                    {{ item.tram || "N/A" }}
                                                </td>
                                                <td>{{ item.title }}</td>
                                                <td>
                                                    {{
                                                        item.investment_project
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        item.khach_hang_ca_nhan_dt_mia ||
                                                        "N/A"
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        item.khach_hang_doanh_nghiep_dt_mia ||
                                                        "N/A"
                                                    }}
                                                </td>
                                                <td>
                                                    {{ item.contract_number }}
                                                </td>
                                                <td>{{ item.service_type }}</td>
                                                <td>
                                                    {{
                                                        item.hop_dong_cung_ung_dich_vu ||
                                                        "N/A"
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        item.disbursement_code ||
                                                        "N/A"
                                                    }}
                                                </td>
                                                <td class="text-center">
                                                    {{ item.installment }}
                                                </td>
                                                <td class="text-end fw-medium">
                                                    {{
                                                        formatCurrency(
                                                            item.amount
                                                        )
                                                    }}
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot v-if="paymentDetails.length > 0">
                                            <tr>
                                                <td
                                                    colspan="12"
                                                    class="text-end fw-bold"
                                                >
                                                    Tổng tiền:
                                                </td>
                                                <td class="text-end fw-bold">
                                                    {{
                                                        formatCurrency(
                                                            totalAmount
                                                        )
                                                    }}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                    <!-- Pagination -->
                                    <div
                                        class="pagination-wrapper mt-4 d-flex justify-content-between align-items-center"
                                    >
                                        <div class="pagination-info text-muted">
                                            <small
                                                >Hiển thị
                                                {{
                                                    paginatedPaymentDetails.from
                                                }}-{{
                                                    paginatedPaymentDetails.to
                                                }}
                                                trên tổng số
                                                {{
                                                    filteredPaymentDetails.length
                                                }}
                                                bản ghi</small
                                            >
                                        </div>
                                        <Bootstrap5Pagination
                                            :data="paginatedPaymentDetails"
                                            @pagination-change-page="
                                                pageChanged
                                            "
                                            :limit="3"
                                            :classes="paginationClasses"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body text-center py-4" v-else>
                            <button
                                class="btn btn-outline-secondary"
                                @click="togglePaymentDetails"
                            >
                                <i class="fas fa-eye me-1"></i> Hiển thị chi
                                tiết hồ sơ thanh toán
                            </button>
                        </div>
                    </div>

                    <!-- Phiếu đề nghị thanh toán table -->
                    <div class="card mt-3">
                        <div
                            class="card-header border-0 bg-transparent d-flex justify-content-between align-items-center"
                        >
                            <h5 class="card-title mb-0">
                                <span
                                    @click="togglePaymentRequests"
                                    class="toggle-section cursor-pointer"
                                >
                                    <i
                                        :class="
                                            showPaymentRequests
                                                ? 'fas fa-angle-down'
                                                : 'fas fa-angle-right'
                                        "
                                        class="me-2 toggle-icon"
                                    ></i>
                                    Phiếu đề nghị thanh toán
                                </span>
                            </h5>
                            <div
                                class="card-actions"
                                v-if="showPaymentRequests"
                            >
                                <span
                                    class="import-data-btn"
                                    title="Import data"
                                    @click="openPaymentImportModal"
                                >
                                    <i class="fas fa-file-import"></i>
                                </span>
                                <span
                                    class="export-excel-btn"
                                    title="Export to Excel"
                                    @click="exportPaymentRequestsToExcel"
                                >
                                    <i class="fas fa-file-excel"></i>
                                </span>
                                <span
                                    class="reset-all-filters-btn"
                                    title="Reset all filters"
                                    @click="resetPaymentRequestFilters"
                                >
                                    <i class="fas fa-redo-alt"></i>
                                </span>
                                <span
                                    class="edit-records-btn"
                                    title="Edit selected records"
                                    @click="editSelectedPaymentRecords"
                                    :class="{
                                        disabled:
                                            selectedPaymentRequests.length ===
                                            0,
                                    }"
                                >
                                    <i class="fas fa-edit"></i>
                                </span>
                                <span
                                    class="delete-records-btn"
                                    title="Delete selected records"
                                    @click="deleteSelectedPaymentRecords"
                                    :class="{
                                        disabled:
                                            selectedPaymentRequests.length ===
                                            0,
                                    }"
                                >
                                    <i class="fas fa-trash"></i>
                                </span>
                                <span
                                    class="add-records-btn"
                                    title="Add new payment request"
                                    @click="openAddPaymentRequestModal"
                                >
                                    <i class="fas fa-plus"></i>
                                </span>
                            </div>
                        </div>
                        <div class="card-body" v-if="showPaymentRequests">
                            <div class="table-container">
                                <div class="table-responsive mt-2">
                                    <table
                                        class="table table-bordered table-hover align-middle"
                                    >
                                        <thead class="table-light text-center">
                                            <tr>
                                                <th>
                                                    <input
                                                        type="checkbox"
                                                        :checked="
                                                            isAllPaymentRequestsSelected
                                                        "
                                                        @change="
                                                            toggleSelectAllPaymentRequests
                                                        "
                                                        class="form-check-input"
                                                    />
                                                </th>
                                                <th>
                                                    Mã giải ngân
                                                    <button
                                                        @click="
                                                            togglePaymentFilter(
                                                                'disbursement_code'
                                                            )
                                                        "
                                                        class="filter-btn"
                                                    >
                                                        <i
                                                            class="fas fa-filter"
                                                            :class="{
                                                                'text-green-500':
                                                                    paymentFilters.disbursement_code,
                                                            }"
                                                        ></i>
                                                    </button>
                                                    <div
                                                        v-if="
                                                            activePaymentFilter ===
                                                            'disbursement_code'
                                                        "
                                                        class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                                    >
                                                        <input
                                                            type="text"
                                                            v-model="
                                                                paymentFilters.disbursement_code
                                                            "
                                                            class="form-control mb-2"
                                                            placeholder="Lọc theo mã..."
                                                        />
                                                        <div
                                                            class="flex justify-between"
                                                        >
                                                            <button
                                                                @click="
                                                                    resetPaymentFilter(
                                                                        'disbursement_code'
                                                                    )
                                                                "
                                                                class="btn btn-sm btn-light"
                                                            >
                                                                Reset
                                                            </button>
                                                            <button
                                                                @click="
                                                                    applyPaymentFilter(
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
                                                    Vụ đầu tư
                                                    <button
                                                        @click="
                                                            togglePaymentFilter(
                                                                'investment_project'
                                                            )
                                                        "
                                                        class="filter-btn"
                                                    >
                                                        <i
                                                            class="fas fa-filter"
                                                            :class="{
                                                                'text-green-500':
                                                                    selectedPaymentFilterValues.investment_project &&
                                                                    selectedPaymentFilterValues
                                                                        .investment_project
                                                                        .length >
                                                                        0,
                                                            }"
                                                        ></i>
                                                    </button>
                                                    <div
                                                        v-if="
                                                            activePaymentFilter ===
                                                            'investment_project'
                                                        "
                                                        class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                                    >
                                                        <div
                                                            class="max-h-40 overflow-y-auto mb-2"
                                                        >
                                                            <div
                                                                v-for="option in uniquePaymentValues.investment_project"
                                                                :key="option"
                                                                class="flex items-center mb-2"
                                                            >
                                                                <input
                                                                    type="checkbox"
                                                                    :id="`payment-investment-${option}`"
                                                                    :value="
                                                                        option
                                                                    "
                                                                    v-model="
                                                                        selectedPaymentFilterValues.investment_project
                                                                    "
                                                                    class="mr-2 rounded text-green-500 focus:ring-green-500"
                                                                />
                                                                <label
                                                                    :for="`payment-investment-${option}`"
                                                                    class="select-none"
                                                                    >{{
                                                                        option
                                                                    }}</label
                                                                >
                                                            </div>
                                                        </div>
                                                        <div
                                                            class="flex justify-between"
                                                        >
                                                            <button
                                                                @click="
                                                                    resetPaymentFilter(
                                                                        'investment_project'
                                                                    )
                                                                "
                                                                class="btn btn-sm btn-light"
                                                            >
                                                                Reset
                                                            </button>
                                                            <button
                                                                @click="
                                                                    applyPaymentFilter(
                                                                        'investment_project'
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
                                                    Loại thanh toán
                                                    <button
                                                        @click="
                                                            togglePaymentFilter(
                                                                'payment_type'
                                                            )
                                                        "
                                                        class="filter-btn"
                                                    >
                                                        <i
                                                            class="fas fa-filter"
                                                            :class="{
                                                                'text-green-500':
                                                                    selectedPaymentFilterValues.payment_type &&
                                                                    selectedPaymentFilterValues
                                                                        .payment_type
                                                                        .length >
                                                                        0,
                                                            }"
                                                        ></i>
                                                    </button>
                                                    <div
                                                        v-if="
                                                            activePaymentFilter ===
                                                            'payment_type'
                                                        "
                                                        class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                                    >
                                                        <div
                                                            class="max-h-40 overflow-y-auto mb-2"
                                                        >
                                                            <div
                                                                v-for="option in uniquePaymentValues.payment_type"
                                                                :key="option"
                                                                class="flex items-center mb-2"
                                                            >
                                                                <input
                                                                    type="checkbox"
                                                                    :id="`payment-type-${option}`"
                                                                    :value="
                                                                        option
                                                                    "
                                                                    v-model="
                                                                        selectedPaymentFilterValues.payment_type
                                                                    "
                                                                    class="mr-2 rounded text-green-500 focus:ring-green-500"
                                                                />
                                                                <label
                                                                    :for="`payment-type-${option}`"
                                                                    class="select-none"
                                                                    >{{
                                                                        option
                                                                    }}</label
                                                                >
                                                            </div>
                                                        </div>
                                                        <div
                                                            class="flex justify-between"
                                                        >
                                                            <button
                                                                @click="
                                                                    resetPaymentFilter(
                                                                        'payment_type'
                                                                    )
                                                                "
                                                                class="btn btn-sm btn-light"
                                                            >
                                                                Reset
                                                            </button>
                                                            <button
                                                                @click="
                                                                    applyPaymentFilter(
                                                                        'payment_type'
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
                                                            togglePaymentFilter(
                                                                'individual_customer'
                                                            )
                                                        "
                                                        class="filter-btn"
                                                    >
                                                        <i
                                                            class="fas fa-filter"
                                                            :class="{
                                                                'text-green-500':
                                                                    paymentFilters.individual_customer,
                                                            }"
                                                        ></i>
                                                    </button>
                                                    <div
                                                        v-if="
                                                            activePaymentFilter ===
                                                            'individual_customer'
                                                        "
                                                        class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                                    >
                                                        <input
                                                            type="text"
                                                            v-model="
                                                                paymentFilters.individual_customer
                                                            "
                                                            class="form-control mb-2"
                                                            placeholder="Lọc theo khách hàng..."
                                                        />
                                                        <div
                                                            class="flex justify-between"
                                                        >
                                                            <button
                                                                @click="
                                                                    resetPaymentFilter(
                                                                        'individual_customer'
                                                                    )
                                                                "
                                                                class="btn btn-sm btn-light"
                                                            >
                                                                Reset
                                                            </button>
                                                            <button
                                                                @click="
                                                                    applyPaymentFilter(
                                                                        'individual_customer'
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
                                                            togglePaymentFilter(
                                                                'individual_customer_code'
                                                            )
                                                        "
                                                        class="filter-btn"
                                                    >
                                                        <i
                                                            class="fas fa-filter"
                                                            :class="{
                                                                'text-green-500':
                                                                    paymentFilters.individual_customer_code,
                                                            }"
                                                        ></i>
                                                    </button>
                                                    <div
                                                        v-if="
                                                            activePaymentFilter ===
                                                            'individual_customer_code'
                                                        "
                                                        class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                                    >
                                                        <input
                                                            type="text"
                                                            v-model="
                                                                paymentFilters.individual_customer_code
                                                            "
                                                            class="form-control mb-2"
                                                            placeholder="Lọc theo mã khách hàng..."
                                                        />
                                                        <div
                                                            class="flex justify-between"
                                                        >
                                                            <button
                                                                @click="
                                                                    resetPaymentFilter(
                                                                        'individual_customer_code'
                                                                    )
                                                                "
                                                                class="btn btn-sm btn-light"
                                                            >
                                                                Reset
                                                            </button>
                                                            <button
                                                                @click="
                                                                    applyPaymentFilter(
                                                                        'individual_customer_code'
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
                                                            togglePaymentFilter(
                                                                'corporate_customer'
                                                            )
                                                        "
                                                        class="filter-btn"
                                                    >
                                                        <i
                                                            class="fas fa-filter"
                                                            :class="{
                                                                'text-green-500':
                                                                    paymentFilters.corporate_customer,
                                                            }"
                                                        ></i>
                                                    </button>
                                                    <div
                                                        v-if="
                                                            activePaymentFilter ===
                                                            'corporate_customer'
                                                        "
                                                        class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                                    >
                                                        <input
                                                            type="text"
                                                            v-model="
                                                                paymentFilters.corporate_customer
                                                            "
                                                            class="form-control mb-2"
                                                            placeholder="Lọc theo doanh nghiệp..."
                                                        />
                                                        <div
                                                            class="flex justify-between"
                                                        >
                                                            <button
                                                                @click="
                                                                    resetPaymentFilter(
                                                                        'corporate_customer'
                                                                    )
                                                                "
                                                                class="btn btn-sm btn-light"
                                                            >
                                                                Reset
                                                            </button>
                                                            <button
                                                                @click="
                                                                    applyPaymentFilter(
                                                                        'corporate_customer'
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
                                                            togglePaymentFilter(
                                                                'corporate_customer_code'
                                                            )
                                                        "
                                                        class="filter-btn"
                                                    >
                                                        <i
                                                            class="fas fa-filter"
                                                            :class="{
                                                                'text-green-500':
                                                                    paymentFilters.corporate_customer_code,
                                                            }"
                                                        ></i>
                                                    </button>
                                                    <div
                                                        v-if="
                                                            activePaymentFilter ===
                                                            'corporate_customer_code'
                                                        "
                                                        class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                                    >
                                                        <input
                                                            type="text"
                                                            v-model="
                                                                paymentFilters.corporate_customer_code
                                                            "
                                                            class="form-control mb-2"
                                                            placeholder="Lọc theo mã doanh nghiệp..."
                                                        />
                                                        <div
                                                            class="flex justify-between"
                                                        >
                                                            <button
                                                                @click="
                                                                    resetPaymentFilter(
                                                                        'corporate_customer_code'
                                                                    )
                                                                "
                                                                class="btn btn-sm btn-light"
                                                            >
                                                                Reset
                                                            </button>
                                                            <button
                                                                @click="
                                                                    applyPaymentFilter(
                                                                        'corporate_customer_code'
                                                                    )
                                                                "
                                                                class="btn btn-sm btn-success"
                                                            >
                                                                Apply
                                                            </button>
                                                        </div>
                                                    </div>
                                                </th>
                                                <th>Tổng tiền</th>
                                                <th>Tổng tiền tạm giữ</th>
                                                <th>Tổng tiền khấu trừ</th>
                                                <th>Tổng tiền lãi suất</th>
                                                <th>
                                                    Tổng tiền thanh toán còn lại
                                                </th>
                                                <th>
                                                    Ngày thanh toán
                                                    <button
                                                        @click="
                                                            togglePaymentFilter(
                                                                'payment_date'
                                                            )
                                                        "
                                                        class="filter-btn"
                                                    >
                                                        <i
                                                            class="fas fa-filter"
                                                            :class="{
                                                                'text-green-500':
                                                                    paymentFilters.payment_date,
                                                            }"
                                                        ></i>
                                                    </button>
                                                    <div
                                                        v-if="
                                                            activePaymentFilter ===
                                                            'payment_date'
                                                        "
                                                        class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                                    >
                                                        <input
                                                            type="date"
                                                            v-model="
                                                                paymentFilters.payment_date
                                                            "
                                                            class="form-control mb-2"
                                                        />
                                                        <div
                                                            class="flex justify-between"
                                                        >
                                                            <button
                                                                @click="
                                                                    resetPaymentFilter(
                                                                        'payment_date'
                                                                    )
                                                                "
                                                                class="btn btn-sm btn-light"
                                                            >
                                                                Reset
                                                            </button>
                                                            <button
                                                                @click="
                                                                    applyPaymentFilter(
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
                                                    Số tờ trình
                                                    <button
                                                        @click="
                                                            togglePaymentFilter(
                                                                'proposal_number'
                                                            )
                                                        "
                                                        class="filter-btn"
                                                    >
                                                        <i
                                                            class="fas fa-filter"
                                                            :class="{
                                                                'text-green-500':
                                                                    paymentFilters.proposal_number,
                                                            }"
                                                        ></i>
                                                    </button>
                                                    <div
                                                        v-if="
                                                            activePaymentFilter ===
                                                            'proposal_number'
                                                        "
                                                        class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                                    >
                                                        <input
                                                            type="text"
                                                            v-model="
                                                                paymentFilters.proposal_number
                                                            "
                                                            class="form-control mb-2"
                                                            placeholder="Lọc theo số tờ trình..."
                                                        />
                                                        <div
                                                            class="flex justify-between"
                                                        >
                                                            <button
                                                                @click="
                                                                    resetPaymentFilter(
                                                                        'proposal_number'
                                                                    )
                                                                "
                                                                class="btn btn-sm btn-light"
                                                            >
                                                                Reset
                                                            </button>
                                                            <button
                                                                @click="
                                                                    applyPaymentFilter(
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
                                                    Đợt thanh toán
                                                    <button
                                                        @click="
                                                            togglePaymentFilter(
                                                                'installment'
                                                            )
                                                        "
                                                        class="filter-btn"
                                                    >
                                                        <i
                                                            class="fas fa-filter"
                                                            :class="{
                                                                'text-green-500':
                                                                    paymentFilters.installment,
                                                            }"
                                                        ></i>
                                                    </button>
                                                    <div
                                                        v-if="
                                                            activePaymentFilter ===
                                                            'installment'
                                                        "
                                                        class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                                    >
                                                        <input
                                                            type="text"
                                                            v-model="
                                                                paymentFilters.installment
                                                            "
                                                            class="form-control mb-2"
                                                            placeholder="Lọc theo đợt..."
                                                        />
                                                        <div
                                                            class="flex justify-between"
                                                        >
                                                            <button
                                                                @click="
                                                                    resetPaymentFilter(
                                                                        'installment'
                                                                    )
                                                                "
                                                                class="btn btn-sm btn-light"
                                                            >
                                                                Reset
                                                            </button>
                                                            <button
                                                                @click="
                                                                    applyPaymentFilter(
                                                                        'installment'
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
                                                v-if="
                                                    paginatedPaymentRequests
                                                        .data.length === 0
                                                "
                                            >
                                                <td
                                                    colspan="16"
                                                    class="text-center py-4"
                                                >
                                                    <div class="empty-state">
                                                        <i
                                                            class="fas fa-file-invoice empty-icon"
                                                        ></i>
                                                        <p>
                                                            Chưa có dữ liệu
                                                            phiếu đề nghị thanh
                                                            toán
                                                        </p>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr
                                                v-for="(
                                                    item, index
                                                ) in paginatedPaymentRequests.data"
                                                :key="index"
                                            >
                                                <td class="text-center">
                                                    <input
                                                        type="checkbox"
                                                        :value="
                                                            item.disbursement_code
                                                        "
                                                        v-model="
                                                            selectedPaymentRequests
                                                        "
                                                        class="form-check-input"
                                                    />
                                                </td>
                                                <td>
                                                    {{ item.disbursement_code }}
                                                </td>

                                                <td>
                                                    {{
                                                        item.investment_project
                                                    }}
                                                </td>
                                                <td>{{ item.payment_type }}</td>
                                                <td>
                                                    {{
                                                        item.individual_customer ||
                                                        "N/A"
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        item.individual_customer_code ||
                                                        "N/A"
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        item.corporate_customer ||
                                                        "N/A"
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        item.corporate_customer_code ||
                                                        "N/A"
                                                    }}
                                                </td>
                                                <td class="text-end fw-medium">
                                                    {{
                                                        formatCurrency(
                                                            item.total_amount
                                                        )
                                                    }}
                                                </td>
                                                <td class="text-end fw-medium">
                                                    {{
                                                        formatCurrency(
                                                            item.total_hold_amount
                                                        )
                                                    }}
                                                </td>
                                                <td class="text-end fw-medium">
                                                    {{
                                                        formatCurrency(
                                                            item.total_deduction
                                                        )
                                                    }}
                                                </td>
                                                <td class="text-end fw-medium">
                                                    {{
                                                        formatCurrency(
                                                            item.total_interest
                                                        )
                                                    }}
                                                </td>
                                                <td class="text-end fw-medium">
                                                    {{
                                                        formatCurrency(
                                                            item.total_remaining
                                                        )
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        formatDate(
                                                            item.payment_date
                                                        )
                                                    }}
                                                </td>
                                                <td>
                                                    {{ item.proposal_number }}
                                                </td>
                                                <td class="text-center">
                                                    {{ item.installment }}
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot
                                            v-if="paymentRequests.length > 0"
                                        >
                                            <tr>
                                                <td
                                                    colspan="8"
                                                    class="text-end fw-bold"
                                                >
                                                    Tổng cộng:
                                                </td>
                                                <td class="text-end fw-bold">
                                                    {{
                                                        formatCurrency(
                                                            totalPaymentAmount
                                                        )
                                                    }}
                                                </td>
                                                <td class="text-end fw-bold">
                                                    {{
                                                        formatCurrency(
                                                            totalHoldAmount
                                                        )
                                                    }}
                                                </td>
                                                <td class="text-end fw-bold">
                                                    {{
                                                        formatCurrency(
                                                            totalDeductionAmount
                                                        )
                                                    }}
                                                </td>
                                                <td class="text-end fw-bold">
                                                    {{
                                                        formatCurrency(
                                                            totalInterestAmount
                                                        )
                                                    }}
                                                </td>
                                                <td class="text-end fw-bold">
                                                    {{
                                                        formatCurrency(
                                                            totalRemainingAmount
                                                        )
                                                    }}
                                                </td>
                                                <td colspan="3"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                    <!-- Pagination for payment requests -->
                                    <div
                                        class="pagination-wrapper mt-4 d-flex justify-content-between align-items-center"
                                    >
                                        <div class="pagination-info text-muted">
                                            <small
                                                >Hiển thị
                                                {{
                                                    paginatedPaymentRequests.from
                                                }}-{{
                                                    paginatedPaymentRequests.to
                                                }}
                                                trên tổng số
                                                {{
                                                    filteredPaymentRequests.length
                                                }}
                                                bản ghi</small
                                            >
                                        </div>
                                        <Bootstrap5Pagination
                                            :data="paginatedPaymentRequests"
                                            @pagination-change-page="
                                                paymentPageChanged
                                            "
                                            :limit="3"
                                            :classes="paginationClasses"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body text-center py-4" v-else>
                            <button
                                class="btn btn-outline-secondary"
                                @click="togglePaymentRequests"
                            >
                                <i class="fas fa-eye me-1"></i> Hiển thị phiếu
                                đề nghị thanh toán
                            </button>
                        </div>
                    </div>

                    <!-- Ghi chú -->
                    <div class="card mt-3">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-sticky-note me-2"></i>Ghi chú
                            </h5>
                            <div class="form-group">
                                <div v-if="!isEditingNote">
                                    <div
                                        class="note-content p-3 border rounded bg-light"
                                    >
                                        {{
                                            document.notes || "Chưa có ghi chú"
                                        }}
                                    </div>
                                    <div
                                        class="d-flex justify-content-end mt-2"
                                        v-if="canEditNote"
                                    >
                                        <button
                                            @click="isEditingNote = true"
                                            class="button-30"
                                        >
                                            <i class="fas fa-edit me-1"></i>Sửa
                                            ghi chú
                                        </button>
                                    </div>
                                </div>
                                <div v-else>
                                    <textarea
                                        class="form-control"
                                        rows="4"
                                        v-model="noteText"
                                        placeholder="Nhập ghi chú..."
                                    ></textarea>
                                    <div
                                        class="d-flex justify-content-end mt-2"
                                        v-if="showEditNoteButtons"
                                    >
                                        <button
                                            @click="cancelEditNote"
                                            class="button-30 me-2"
                                        >
                                            <i class="fas fa-times me-1"></i>Hủy
                                        </button>
                                        <button
                                            @click="saveNote"
                                            class="button-30-text-green"
                                        >
                                            <i class="fas fa-save me-1"></i>Lưu
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </PerfectScrollbar>
        </div>
    </div>

    <!-- Add this modal at the end of the template -->
    <!-- Edit Modal -->
    <div
        class="modal fade"
        id="editModal"
        tabindex="-1"
        aria-labelledby="editModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">
                        <i class="fas fa-edit me-2"></i>
                        Chỉnh sửa chi tiết nghiệm thu
                    </h5>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info text-white mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        Đang chỉnh sửa
                        <strong>{{ selectedRecords.length }}</strong> bản ghi
                    </div>

                    <div class="mb-3">
                        <label for="disbursementCode" class="form-label"
                            >Mã đề nghị giải ngân</label
                        >
                        <input
                            type="text"
                            class="form-control"
                            id="disbursementCode"
                            v-model="editForm.disbursementCode"
                            placeholder="Nhập mã đề nghị giải ngân"
                        />
                    </div>
                </div>
                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Hủy
                    </button>
                    <button
                        type="button"
                        class="btn btn-success"
                        @click="updateRecords"
                        :disabled="isUpdating"
                    >
                        <i class="fas fa-save me-1"></i>
                        <span v-if="isUpdating">Đang lưu...</span>
                        <span v-else>Lưu thay đổi</span>
                    </button>
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
                        <i class="fas fa-file-import text-primary me-2"></i>
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
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <small
                            >Import data to update payment details. The file
                            should contain columns for Mã nghiệm thu and Mã đề
                            nghị giải ngân.</small
                        >
                    </div>

                    <!-- Add this inside the modal-body, before the file input -->
                    <div class="d-flex justify-content-end mb-2">
                        <button
                            @click.prevent="downloadImportTemplate"
                            class="btn btn-sm btn-outline-secondary"
                        >
                            <i class="fas fa-download me-1"></i>
                            Download Template
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
                                class="progress-bar progress-bar-striped progress-bar-animated bg-success"
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

                    <div v-if="importErrors.length > 0" class="mt-3">
                        <div class="alert alert-danger">
                            <h6 class="alert-heading">Import errors</h6>
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
                                <small
                                    >And
                                    {{ importErrors.length - 5 }}
                                    more errors...</small
                                >
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

    <!-- Payment Request Edit Modal -->
    <div
        class="modal fade"
        id="paymentEditModal"
        tabindex="-1"
        aria-labelledby="paymentEditModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentEditModalLabel">
                        <i class="fas fa-edit me-2"></i>
                        Chỉnh sửa phiếu đề nghị thanh toán
                    </h5>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>
                </div>
                <!-- ในส่วนของ Payment Request Edit Modal -->
                <div class="modal-body">
                    <div
                        class="alert text-white"
                        :class="{
                            'alert-info text-white':
                                selectedPaymentRequests.length > 1,
                            'alert-success':
                                selectedPaymentRequests.length === 1,
                        }"
                    >
                        <i
                            class="fas"
                            :class="{
                                'fa-info-circle me-2':
                                    selectedPaymentRequests.length > 1,
                                'fa-edit me-2':
                                    selectedPaymentRequests.length === 1,
                            }"
                        ></i>
                        <span v-if="selectedPaymentRequests.length === 1">
                            Đang chỉnh sửa phiếu đề nghị:
                            <strong>{{ selectedPaymentRequests[0] }}</strong>
                        </span>
                        <span v-else>
                            Đang chỉnh sửa
                            <strong>{{
                                selectedPaymentRequests.length
                            }}</strong>
                            phiếu đề nghị
                            <div class="mt-2 small">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Lưu ý: Khi chỉnh sửa nhiều phiếu cùng lúc, tất
                                cả phiếu sẽ được cập nhật với cùng giá trị mới.
                            </div>
                        </span>
                    </div>

                    <!-- เพิ่มฟิลด์ Mã giải ngân ตรงนี้ -->
                    <div class="mb-3">
                        <label for="disbursementCode" class="form-label"
                            >Mã giải ngân</label
                        >
                        <input
                            type="text"
                            class="form-control"
                            id="disbursementCode"
                            v-model="paymentEditForm.ma_giai_ngan"
                            placeholder="Nhập mã giải ngân"
                            :disabled="selectedPaymentRequests.length > 1"
                        />

                        <div
                            class="form-text text-warning"
                            v-if="selectedPaymentRequests.length > 1"
                        >
                            Không thể chỉnh sửa mã giải ngân khi chọn nhiều
                            phiếu
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="individualCustomer" class="form-label"
                            >Khách hàng cá nhân</label
                        >
                        <input
                            type="text"
                            class="form-control"
                            id="individualCustomer"
                            v-model="paymentEditForm.khach_hang_ca_nhan"
                            placeholder="Nhập tên khách hàng cá nhân"
                        />
                    </div>

                    <div class="mb-3">
                        <label for="individualCustomerCode" class="form-label"
                            >Mã khách hàng cá nhân</label
                        >
                        <input
                            type="text"
                            class="form-control"
                            id="individualCustomerCode"
                            v-model="paymentEditForm.ma_khach_hang_ca_nhan"
                            placeholder="Nhập mã khách hàng cá nhân"
                        />
                    </div>

                    <div class="mb-3">
                        <label for="corporateCustomer" class="form-label"
                            >Khách hàng doanh nghiệp</label
                        >
                        <input
                            type="text"
                            class="form-control"
                            id="corporateCustomer"
                            v-model="paymentEditForm.khach_hang_doanh_nghiep"
                            placeholder="Nhập tên khách hàng doanh nghiệp"
                        />
                    </div>

                    <div class="mb-3">
                        <label for="corporateCustomerCode" class="form-label"
                            >Mã khách hàng doanh nghiệp</label
                        >
                        <input
                            type="text"
                            class="form-control"
                            id="corporateCustomerCode"
                            v-model="paymentEditForm.ma_khach_hang_doanh_nghiep"
                            placeholder="Nhập mã khách hàng doanh nghiệp"
                        />
                    </div>
                </div>
                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Hủy
                    </button>
                    <button
                        type="button"
                        class="btn btn-success"
                        @click="updatePaymentRecords"
                        :disabled="isPaymentUpdating"
                    >
                        <i class="fas fa-save me-1"></i>
                        <span v-if="isPaymentUpdating">Đang lưu...</span>
                        <span v-else>Lưu thay đổi</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Receipt Modal -->
    <div
        class="modal fade"
        id="addReceiptModal"
        tabindex="-1"
        aria-labelledby="addReceiptModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addReceiptModalLabel">
                        <i class="fas fa-plus me-2"></i>
                        Thêm biên bản nghiệm thu dịch vụ
                    </h5>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>
                </div>
                <div class="modal-body">
                    <div class="search-container mb-4">
                        <label for="receiptSearch" class="form-label"
                            >Tìm kiếm mã nghiệm thu</label
                        >
                        <div class="search-input-wrapper">
                            <input
                                type="search"
                                class="form-control"
                                v-model="searchQuery"
                                @input="searchReceipts"
                                placeholder="Nhập mã nghiệm thu..."
                            />
                            <i class="search-icon bx bx-search"></i>
                        </div>
                    </div>

                    <!-- Update the table in the addReceiptModal to show more details -->
                    <div
                        class="search-results"
                        v-if="receiptResults.length > 0"
                    >
                        <h6 class="results-title mb-2">Kết quả tìm kiếm</h6>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Mã nghiệm thu</th>
                                        <th>Trạm</th>
                                        <th>Cán bộ nông vụ</th>
                                        <th>Vụ đầu tư</th>
                                        <th>Tiêu đề</th>
                                        <th>Hợp đồng đầu tư mía</th>
                                        <th>Hình thức DV</th>
                                        <th>Hợp đồng cung ứng DV</th>
                                        <th>Tổng tiền</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="item in receiptResults"
                                        :key="item.ma_nghiem_thu"
                                        :class="{
                                            'table-warning': isDuplicate(
                                                item.ma_nghiem_thu
                                            ),
                                        }"
                                    >
                                        <td>{{ item.ma_nghiem_thu }}</td>
                                        <td>{{ item.tram }}</td>
                                        <td>{{ item.can_bo_nong_vu }}</td>
                                        <td>{{ item.vu_dau_tu }}</td>
                                        <td>{{ item.tieu_de }}</td>
                                        <td>{{ item.hop_dong_dau_tu_mia }}</td>
                                        <td>
                                            {{ item.hinh_thuc_thuc_hien_dv }}
                                        </td>
                                        <td>
                                            {{ item.hop_dong_cung_ung_dich_vu }}
                                        </td>
                                        <td>
                                            {{ formatCurrency(item.tong_tien) }}
                                        </td>
                                        <td>
                                            <button
                                                @click="selectReceipt(item)"
                                                class="btn btn-sm btn-success"
                                                :disabled="
                                                    isDuplicate(
                                                        item.ma_nghiem_thu
                                                    )
                                                "
                                                title="Thêm biên bản này vào phiếu trình thanh toán"
                                            >
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div
                        v-else-if="searchQuery && searchQuery.length > 0"
                        class="text-center py-4"
                    >
                        <i class="bx bx-search-alt empty-icon d-block mb-2"></i>
                        <p class="text-muted">Không tìm thấy kết quả phù hợp</p>
                    </div>

                    <div
                        class="selected-receipts mt-4"
                        v-if="selectedReceipts.length > 0"
                    >
                        <h6 class="mb-2">
                            Biên bản đã chọn ({{ selectedReceipts.length }})
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Mã nghiệm thu</th>
                                        <th>Tiêu đề</th>
                                        <th>Hợp đồng đầu tư mía</th>
                                        <th>Hình thức DV</th>
                                        <th>Hợp đồng cung ứng DV</th>
                                        <th>Số tiền</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(
                                            item, index
                                        ) in selectedReceipts"
                                        :key="index"
                                    >
                                        <td>{{ item.ma_nghiem_thu }}</td>
                                        <td>{{ item.tieu_de }}</td>
                                        <td>{{ item.hop_dong_dau_tu_mia }}</td>
                                        <td>
                                            {{ item.hinh_thuc_thuc_hien_dv }}
                                        </td>
                                        <td>
                                            {{ item.hop_dong_cung_ung_dich_vu }}
                                        </td>
                                        <td>
                                            {{ formatCurrency(item.tong_tien) }}
                                        </td>
                                        <td>
                                            <button
                                                @click="
                                                    removeSelectedReceipt(index)
                                                "
                                                class="btn btn-sm btn-danger"
                                                title="Xóa khỏi danh sách"
                                            >
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="5" class="text-end">
                                            Tổng tiền:
                                        </td>
                                        <td colspan="2">
                                            <strong>{{
                                                formatCurrency(
                                                    calculateTotalSelected()
                                                )
                                            }}</strong>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Hủy
                    </button>
                    <button
                        type="button"
                        class="btn btn-success"
                        @click="addSelectedReceipts"
                        :disabled="selectedReceipts.length === 0 || isAdding"
                    >
                        <i class="fas fa-save me-1"></i>
                        <span v-if="isAdding">Đang thêm...</span>
                        <span v-else
                            >Thêm {{ selectedReceipts.length }} biên bản</span
                        >
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Payment Import Modal -->
    <div
        class="modal fade"
        id="paymentImportModal"
        tabindex="-1"
        aria-labelledby="paymentImportModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5
                        class="modal-title text-primary"
                        id="paymentImportModalLabel"
                    >
                        <i class="fas fa-file-import text-primary me-2"></i>
                        Import Phiếu đề nghị thanh toán
                    </h5>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                        @click="closePaymentImportModal"
                    ></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info text-white mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <small>
                            Import dữ liệu đề nghị thanh toán. File cần chứa các
                            cột: ma_giai_ngan, vu_dau_tu, loai_thanh_toan và các
                            thông tin khách hàng nếu có.
                        </small>
                    </div>

                    <!-- Template download button -->
                    <div class="d-flex justify-content-end mb-2">
                        <button
                            @click.prevent="downloadPaymentImportTemplate"
                            class="btn btn-sm btn-outline-secondary"
                        >
                            <i class="fas fa-download me-1"></i>
                            Download Template
                        </button>
                    </div>

                    <div class="mb-3">
                        <label for="paymentImportFile" class="form-label"
                            >Select file</label
                        >
                        <input
                            class="form-control"
                            type="file"
                            id="paymentImportFile"
                            @change="handlePaymentFileSelected"
                            accept=".csv,.xlsx"
                        />
                        <div class="form-text">Support files: .csv, .xlsx</div>
                    </div>

                    <div v-if="paymentUploadProgress > 0" class="mb-3">
                        <label class="form-label">Upload progress</label>
                        <div class="progress">
                            <div
                                class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                role="progressbar"
                                :style="`width: ${paymentUploadProgress}%`"
                                :aria-valuenow="paymentUploadProgress"
                                aria-valuemin="0"
                                aria-valuemax="100"
                            >
                                {{ paymentUploadProgress }}%
                            </div>
                        </div>
                    </div>

                    <div v-if="paymentImportErrors.length > 0" class="mt-3">
                        <div class="alert alert-danger">
                            <h6 class="alert-heading">Import errors</h6>
                            <ul class="mb-0 ps-3">
                                <li
                                    v-for="(
                                        error, index
                                    ) in paymentImportErrors.slice(0, 5)"
                                    :key="index"
                                >
                                    {{ error }}
                                </li>
                            </ul>
                            <div
                                v-if="paymentImportErrors.length > 5"
                                class="mt-2 text-center"
                            >
                                <small
                                    >And
                                    {{ paymentImportErrors.length - 5 }}
                                    more errors...</small
                                >
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                        @click="closePaymentImportModal"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="btn btn-primary"
                        @click="startPaymentImport"
                        :disabled="!selectedPaymentFile || isPaymentImporting"
                    >
                        <i class="fas fa-upload me-2"></i>
                        <span v-if="isPaymentImporting">Importing...</span>
                        <span v-else>Import</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Add Payment Request Modal -->
    <div
        class="modal fade"
        id="addPaymentModal"
        tabindex="-1"
        aria-labelledby="addPaymentModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPaymentModalLabel">
                        <i class="fas fa-plus me-2"></i>
                        Thêm phiếu đề nghị thanh toán
                    </h5>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="disbursementCode" class="form-label"
                            >Mã giải ngân
                            <span class="text-danger">*</span></label
                        >
                        <input
                            type="text"
                            class="form-control"
                            id="disbursementCode"
                            v-model="newPaymentRequest.ma_giai_ngan"
                            placeholder="Nhập mã giải ngân"
                            required
                        />
                    </div>

                    <div class="mb-3">
                        <label for="investmentProject" class="form-label"
                            >Vụ đầu tư <span class="text-danger">*</span></label
                        >
                        <input
                            type="text"
                            class="form-control"
                            id="investmentProject"
                            v-model="newPaymentRequest.vu_dau_tu"
                            placeholder="Nhập vụ đầu tư"
                            required
                        />
                    </div>

                    <div class="mb-3">
                        <label for="paymentType" class="form-label"
                            >Loại thanh toán
                            <span class="text-danger">*</span></label
                        >
                        <input
                            type="text"
                            class="form-control"
                            id="paymentType"
                            v-model="newPaymentRequest.loai_thanh_toan"
                            placeholder="Nhập loại thanh toán"
                            required
                        />
                    </div>

                    <div class="mb-3">
                        <label for="individualCustomer" class="form-label"
                            >Khách hàng cá nhân</label
                        >
                        <input
                            type="text"
                            class="form-control"
                            id="individualCustomer"
                            v-model="newPaymentRequest.khach_hang_ca_nhan"
                            placeholder="Nhập tên khách hàng cá nhân"
                        />
                    </div>

                    <div class="mb-3">
                        <label for="individualCustomerCode" class="form-label"
                            >Mã khách hàng cá nhân</label
                        >
                        <input
                            type="text"
                            class="form-control"
                            id="individualCustomerCode"
                            v-model="newPaymentRequest.ma_khach_hang_ca_nhan"
                            placeholder="Nhập mã khách hàng cá nhân"
                        />
                    </div>

                    <div class="mb-3">
                        <label for="corporateCustomer" class="form-label"
                            >Khách hàng doanh nghiệp</label
                        >
                        <input
                            type="text"
                            class="form-control"
                            id="corporateCustomer"
                            v-model="newPaymentRequest.khach_hang_doanh_nghiep"
                            placeholder="Nhập tên khách hàng doanh nghiệp"
                        />
                    </div>

                    <div class="mb-3">
                        <label for="corporateCustomerCode" class="form-label"
                            >Mã khách hàng doanh nghiệp</label
                        >
                        <input
                            type="text"
                            class="form-control"
                            id="corporateCustomerCode"
                            v-model="
                                newPaymentRequest.ma_khach_hang_doanh_nghiep
                            "
                            placeholder="Nhập mã khách hàng doanh nghiệp"
                        />
                    </div>

                    <div
                        class="alert alert-info text-white"
                        v-if="paymentReceiptIds.length > 0"
                    >
                        <i class="fas fa-info-circle me-2"></i>
                        Phiếu đề nghị sẽ được liên kết với
                        <strong>{{ paymentReceiptIds.length }}</strong> biên bản
                        nghiệm thu đã chọn
                    </div>
                    <div class="alert alert-warning" v-else>
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Vui lòng chọn ít nhất một biên bản nghiệm thu trong bảng
                        chi tiết
                    </div>
                </div>
                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Hủy
                    </button>
                    <button
                        type="button"
                        class="btn btn-success"
                        @click="addPaymentRequest"
                        :disabled="
                            isAddingPaymentRequest ||
                            paymentReceiptIds.length === 0
                        "
                    >
                        <i class="fas fa-save me-1"></i>
                        <span v-if="isAddingPaymentRequest">Đang lưu...</span>
                        <span v-else>Tạo phiếu đề nghị</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import * as XLSX from "xlsx";
import Swal from "sweetalert2";
import axios from "axios";
import { useStore } from "../../Store/Auth";
import { Bootstrap5Pagination } from "laravel-vue-pagination";

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
            document: {
                payment_code: "", // Mã trình thanh toán
                title: "", // Tiêu đề
                investment_project: "", // Vụ đầu tư
                payment_type: "", // Loại thanh toán
                status: "pending", // Trạng thái thanh toán
                payment_installment: 0, // Số đợt thanh toán
                proposal_number: "", // Số tờ trình
                created_at: null, // Ngày tạo
                total_amount: 0, // Tổng tiền thanh toán
                creator_name: "", // Người tạo
                notes: "",
            },
            // Add to data() function in your component
            newPaymentRequest: {
                ma_giai_ngan: "",
                ma_trinh_thanh_toan: this.$route.params.id, // Set the parent payment request ID
                vu_dau_tu: "",
                loai_thanh_toan: "",
                khach_hang_ca_nhan: "",
                ma_khach_hang_ca_nhan: "", // Added field for customer code
                khach_hang_doanh_nghiep: "",
                ma_khach_hang_doanh_nghiep: "", // Added field for corporate code
                tram: "",
            },
            isAddingPaymentRequest: false,
            addPaymentModal: null,
            paymentReceiptIds: [],
            investmentProjects: [],

            // Add to data() function in your component
            paymentEditForm: {
                ma_giai_ngan: "",
                khach_hang_ca_nhan: "",
                ma_khach_hang_ca_nhan: "", // Added field for customer code
                khach_hang_doanh_nghiep: "",
                ma_khach_hang_doanh_nghiep: "", // Added field for corporate code
            },
            isPaymentUpdating: false,
            paymentEditModal: null,

            // Payment import related properties
            selectedPaymentFile: null,
            isPaymentImporting: false,
            paymentUploadProgress: 0,
            paymentImportErrors: [],
            paymentImportModal: null,
            paymentDetails: [
                {
                    document_code: "", // ma_nghiem_thu (Mã nghiệm thu)

                    document_type: "Nghiệm thu",
                    // Fixed value since we're dealing with nghiệm thu records
                    title: "", // Tiêu đề
                    contract_number: "", // hop_dong_dau_tu_mia (Hợp đồng đầu tư mía)
                    installment: 1, // Default value or from request
                    amount: 0, // tong_tien (Tổng tiền)
                    // Additional fields from requirements:
                    investment_project: "", // vu_dau_tu (Vụ đầu tư)
                    individual_customer: "", // khach_hang_ca_nhan_dt_mia
                    corporate_customer: "", // khach_hang_doanh_nghiep_dt_mia
                    service_type: "", // hinh_thuc_thuc_hien_dv
                    service_contract: "", // hop_dong_cung_ung_dich_vu
                    disbursement_code: "", // ma_de_nghi_giai_ngan
                    tram: "",
                },
            ],

            user: null,
            isEditingNote: false,
            noteText: "",
            isLoading: false,
            // Filter-related properties
            activeFilter: null,
            columnFilters: {
                document_code: "", // Mã nghiệm thu
                title: "", // Tiêu đề
                khach_hang_ca_nhan_dt_mia: "", // KH Cá nhân
                khach_hang_doanh_nghiep_dt_mia: "", // KH Doanh nghiệp
                contract_number: "", // Hợp đồng đầu tư
                hop_dong_cung_ung_dich_vu: "", // Hợp đồng cung ứng DV
                disbursement_code: "", // Mã giải ngân
                installment: "", // Đợt TT
            },
            // For unique value dropdown filters
            uniqueValues: {
                tram: [], // Trạm
                investment_project: [], // Vụ đầu tư
                service_type: [], // Hình thức DV
            },
            selectedFilterValues: {
                tram: [], // Trạm
                investment_project: [], // Vụ đầu tư
                service_type: [], // Hình thức DV
            },
            selectedRecords: [],
            editForm: {
                disbursementCode: "",
            },
            isUpdating: false,
            editModal: null,
            // Properties for adding receipts
            searchQuery: "",
            receiptResults: [],
            selectedReceipts: [],
            isAdding: false,
            addReceiptModal: null,
            existingReceiptIds: [], // Will store already mapped receipt IDs
            // Import related properties
            selectedFile: null,
            isImporting: false,
            uploadProgress: 0,
            importErrors: [],
            importModal: null,
            currentPage: 1,
            perPage: 15, // Set to 15 rows per page
            paginationClasses: {
                ul: "pagination rounded justify-content-center",
                li: "page-item mx-1",
                a: "page-link px-3 py-2 rounded border",
                active: "active bg-success border-success",
                disabled: "disabled opacity-50",
            },
            // Payment request related properties
            paymentRequests: [],
            activePaymentFilter: null,
            paymentFilters: {
                disbursement_code: "",
                individual_customer: "",
                individual_customer_code: "",
                corporate_customer: "",
                corporate_customer_code: "",
                payment_date: "",
                proposal_number: "",
                installment: "",
            },
            uniquePaymentValues: {
                tram: [],
                investment_project: [],
                payment_type: [],
            },
            selectedPaymentFilterValues: {
                tram: [],
                investment_project: [],
                payment_type: [],
            },
            selectedPaymentRequests: [],
            currentPaymentPage: 1,
            perPaymentPage: 15,
            showPaymentDetails: true,
            showPaymentRequests: true,
        };
    },
    computed: {
        totalAmount() {
            return this.paymentDetails.reduce(
                (sum, item) => sum + (parseFloat(item.amount) || 0),
                0
            );
        },
        showEditNoteButtons() {
            return this.isEditingNote;
        },
        canEditNote() {
            return true; // Can be modified based on user permissions or document status
        },
        filteredPaymentDetails() {
            return this.paymentDetails.filter((item) => {
                // Apply text search filters
                const matchesTextFilters =
                    (!this.columnFilters.document_code ||
                        (item.document_code &&
                            item.document_code
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.document_code.toLowerCase()
                                ))) &&
                    (!this.columnFilters.title ||
                        (item.title &&
                            item.title
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.title.toLowerCase()
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
                    (!this.columnFilters.contract_number ||
                        (item.contract_number &&
                            item.contract_number
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.contract_number.toLowerCase()
                                ))) &&
                    (!this.columnFilters.hop_dong_cung_ung_dich_vu ||
                        (item.hop_dong_cung_ung_dich_vu &&
                            item.hop_dong_cung_ung_dich_vu
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.hop_dong_cung_ung_dich_vu.toLowerCase()
                                ))) &&
                    (!this.columnFilters.disbursement_code ||
                        (item.disbursement_code &&
                            item.disbursement_code
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.disbursement_code.toLowerCase()
                                ))) &&
                    (!this.columnFilters.installment ||
                        (item.installment &&
                            item.installment
                                .toString()
                                .includes(this.columnFilters.installment)));

                // Apply dropdown filters
                const matchesDropdownFilters =
                    (this.selectedFilterValues.tram.length === 0 ||
                        (item.tram &&
                            this.selectedFilterValues.tram.includes(
                                item.tram
                            ))) &&
                    (this.selectedFilterValues.investment_project.length ===
                        0 ||
                        (item.investment_project &&
                            this.selectedFilterValues.investment_project.includes(
                                item.investment_project
                            ))) &&
                    (this.selectedFilterValues.service_type.length === 0 ||
                        (item.service_type &&
                            this.selectedFilterValues.service_type.includes(
                                item.service_type
                            )));

                return matchesTextFilters && matchesDropdownFilters;
            });
        },
        isAllSelected() {
            return (
                this.filteredPaymentDetails.length > 0 &&
                this.selectedRecords.length ===
                    this.filteredPaymentDetails.length
            );
        },
        // Paginated payment details
        paginatedPaymentDetails() {
            const startIndex = (this.currentPage - 1) * this.perPage;
            const endIndex = startIndex + this.perPage;

            return {
                data: this.filteredPaymentDetails.slice(startIndex, endIndex),
                current_page: this.currentPage,
                from:
                    this.filteredPaymentDetails.length > 0 ? startIndex + 1 : 0,
                to: Math.min(endIndex, this.filteredPaymentDetails.length),
                total: this.filteredPaymentDetails.length,
                per_page: this.perPage,
                last_page: Math.ceil(
                    this.filteredPaymentDetails.length / this.perPage
                ),
                prev_page_url:
                    this.currentPage > 1
                        ? `?page=${this.currentPage - 1}`
                        : null,
                next_page_url:
                    this.currentPage <
                    Math.ceil(this.filteredPaymentDetails.length / this.perPage)
                        ? `?page=${this.currentPage + 1}`
                        : null,
            };
        },
        // Filtered payment requests
        filteredPaymentRequests() {
            return this.paymentRequests.filter((item) => {
                // Apply text search filters
                const matchesTextFilters =
                    (!this.paymentFilters.disbursement_code ||
                        (item.disbursement_code &&
                            item.disbursement_code
                                .toLowerCase()
                                .includes(
                                    this.paymentFilters.disbursement_code.toLowerCase()
                                ))) &&
                    (!this.paymentFilters.individual_customer ||
                        (item.individual_customer &&
                            item.individual_customer
                                .toLowerCase()
                                .includes(
                                    this.paymentFilters.individual_customer.toLowerCase()
                                ))) &&
                    (!this.paymentFilters.individual_customer_code ||
                        (item.individual_customer_code &&
                            item.individual_customer_code
                                .toLowerCase()
                                .includes(
                                    this.paymentFilters.individual_customer_code.toLowerCase()
                                ))) &&
                    (!this.paymentFilters.corporate_customer ||
                        (item.corporate_customer &&
                            item.corporate_customer
                                .toLowerCase()
                                .includes(
                                    this.paymentFilters.corporate_customer.toLowerCase()
                                ))) &&
                    (!this.paymentFilters.corporate_customer_code ||
                        (item.corporate_customer_code &&
                            item.corporate_customer_code
                                .toLowerCase()
                                .includes(
                                    this.paymentFilters.corporate_customer_code.toLowerCase()
                                ))) &&
                    (!this.paymentFilters.payment_date ||
                        (item.payment_date &&
                            item.payment_date
                                .toLowerCase()
                                .includes(
                                    this.paymentFilters.payment_date.toLowerCase()
                                ))) &&
                    (!this.paymentFilters.proposal_number ||
                        (item.proposal_number &&
                            item.proposal_number
                                .toLowerCase()
                                .includes(
                                    this.paymentFilters.proposal_number.toLowerCase()
                                ))) &&
                    (!this.paymentFilters.installment ||
                        (item.installment &&
                            item.installment
                                .toString()
                                .includes(this.paymentFilters.installment)));

                // Apply dropdown filters
                const matchesDropdownFilters =
                    (this.selectedPaymentFilterValues.tram.length === 0 ||
                        (item.tram &&
                            this.selectedPaymentFilterValues.tram.includes(
                                item.tram
                            ))) &&
                    (this.selectedPaymentFilterValues.investment_project
                        .length === 0 ||
                        (item.investment_project &&
                            this.selectedPaymentFilterValues.investment_project.includes(
                                item.investment_project
                            ))) &&
                    (this.selectedPaymentFilterValues.payment_type.length ===
                        0 ||
                        (item.payment_type &&
                            this.selectedPaymentFilterValues.payment_type.includes(
                                item.payment_type
                            )));

                return matchesTextFilters && matchesDropdownFilters;
            });
        },
        isAllPaymentRequestsSelected() {
            return (
                this.filteredPaymentRequests.length > 0 &&
                this.selectedPaymentRequests.length ===
                    this.filteredPaymentRequests.length
            );
        },
        // Paginated payment requests
        paginatedPaymentRequests() {
            const startIndex =
                (this.currentPaymentPage - 1) * this.perPaymentPage;
            const endIndex = startIndex + this.perPaymentPage;

            return {
                data: this.filteredPaymentRequests.slice(startIndex, endIndex),
                current_page: this.currentPaymentPage,
                from:
                    this.filteredPaymentRequests.length > 0
                        ? startIndex + 1
                        : 0,
                to: Math.min(endIndex, this.filteredPaymentRequests.length),
                total: this.filteredPaymentRequests.length,
                per_page: this.perPaymentPage,
                last_page: Math.ceil(
                    this.filteredPaymentRequests.length / this.perPaymentPage
                ),
                prev_page_url:
                    this.currentPaymentPage > 1
                        ? `?page=${this.currentPaymentPage - 1}`
                        : null,
                next_page_url:
                    this.currentPaymentPage <
                    Math.ceil(
                        this.filteredPaymentRequests.length /
                            this.perPaymentPage
                    )
                        ? `?page=${this.currentPaymentPage + 1}`
                        : null,
            };
        },
        totalPaymentAmount() {
            return this.filteredPaymentRequests.reduce(
                (sum, item) => sum + (parseFloat(item.total_amount) || 0),
                0
            );
        },
        totalHoldAmount() {
            return this.filteredPaymentRequests.reduce(
                (sum, item) => sum + (parseFloat(item.total_hold_amount) || 0),
                0
            );
        },
        totalDeductionAmount() {
            return this.filteredPaymentRequests.reduce(
                (sum, item) => sum + (parseFloat(item.total_deduction) || 0),
                0
            );
        },
        totalInterestAmount() {
            return this.filteredPaymentRequests.reduce(
                (sum, item) => sum + (parseFloat(item.total_interest) || 0),
                0
            );
        },
        totalRemainingAmount() {
            return this.filteredPaymentRequests.reduce(
                (sum, item) => sum + (parseFloat(item.total_remaining) || 0),
                0
            );
        },
    },
    mounted() {
        this.fetchUserData();
        this.fetchDocument();
        this.fetchInvestmentProjects(); // Add this line

        // Restore visibility state from localStorage
        const savedShowPaymentDetails =
            localStorage.getItem("showPaymentDetails");
        const savedShowPaymentRequests = localStorage.getItem(
            "showPaymentRequests"
        );

        if (savedShowPaymentDetails !== null) {
            this.showPaymentDetails = savedShowPaymentDetails === "true";
        }

        if (savedShowPaymentRequests !== null) {
            this.showPaymentRequests = savedShowPaymentRequests === "true";
        }
    },
    methods: {
        // ... existing methods
        // Add this to your Vue component's methods section
        async deleteDocument() {
    // Confirm deletion with the user
    const result = await Swal.fire({
        title: "Xác nhận xóa",
        text: "Bạn có chắc chắn muốn xóa phiếu trình thanh toán này không? Hành động này không thể hoàn tác.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Xóa",
        cancelButtonText: "Hủy",
        buttonsStyling: false,
        customClass: {
            confirmButton: "btn btn-danger me-2",
            cancelButton: "btn btn-outline-secondary",
        },
    });

    if (!result.isConfirmed) {
        return;
    }
    
    try {
        // Show loading indicator
        Swal.fire({
            title: "Đang xử lý",
            text: "Vui lòng đợi...",
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });

        // Call API to delete the document
        const response = await axios.delete(
            `/api/payment-requests/${this.document.payment_code}`,
            {
                headers: {
                    Authorization: "Bearer " + this.store.getToken,
                },
            }
        );

        if (response.data.success) {
            Swal.fire({
                title: "Thành công!",
                text: "Phiếu trình thanh toán đã được xóa",
                icon: "success",
                confirmButtonText: "OK",
                buttonsStyling: false,
                customClass: {
                    confirmButton: "btn btn-success",
                },
            });
            
            // Redirect to the payment request list page
            this.$router.push('/phieutrinhthanhtoan');
        } else {
            throw new Error(response.data.message || "Không thể xóa phiếu trình thanh toán");
        }
    } catch (error) {
        console.error("Error deleting document:", error);
        
        Swal.fire({
            title: "Lỗi!",
            text: error.response?.data?.message || "Đã xảy ra lỗi khi xóa phiếu trình thanh toán",
            icon: "error",
            confirmButtonText: "OK",
            buttonsStyling: false,
            customClass: {
                confirmButton: "btn btn-success",
            },
        });

        if (error.response?.status === 401) {
            this.handleAuthError();
        }
    }
},
        confirmUpdateStatus(status, title, text) {
            Swal.fire({
                title: title,
                text: text,
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Xác nhận",
                cancelButtonText: "Hủy",
                buttonsStyling: false,
                customClass: {
                    confirmButton: "btn btn-success me-2",
                    cancelButton: "btn btn-outline-secondary",
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    this.updateStatus(status);
                }
            });
        },

        /**
         * Update payment request status
         */
        async updateStatus(status) {
            try {
                // Optional: Show loading indicator
                Swal.fire({
                    title: "Đang xử lý",
                    text: "Vui lòng đợi...",
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });

                // Send status update request
                const response = await axios.put(
                    `/api/payment-requests/${this.document.payment_code}/status`,
                    {
                        status: status,
                        action_notes: "", // Có thể thêm ghi chú nếu cần
                    },
                    {
                        headers: {
                            Authorization: "Bearer " + this.store.getToken,
                        },
                    }
                );

                if (response.data.success) {
                    // Update local document status จากค่า action ที่ได้อัพเดทไป
                    this.document.status = status;

                    // Show success message
                    Swal.fire({
                        title: "Thành công",
                        text: "Cập nhật trạng thái thành công",
                        icon: "success",
                        confirmButtonText: "OK",
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: "btn btn-success",
                        },
                    });

                    // Refresh document data to get updated info and latest processing history
                    this.fetchDocument();
                } else {
                    throw new Error(
                        response.data.message || "Lỗi không xác định"
                    );
                }
            } catch (error) {
                console.error("Error updating status:", error);

                // Show error message
                Swal.fire({
                    title: "Lỗi",
                    text: `Đã xảy ra lỗi khi cập nhật trạng thái: ${error.message}`,
                    icon: "error",
                    confirmButtonText: "OK",
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: "btn btn-success",
                    },
                });

                if (error.response?.status === 401) {
                    this.handleAuthError();
                }
            }
        },
        saveBasicInfo() {
            const data = {
                so_to_trinh: this.document.proposal_number,
                ngay_tao: this.document.created_at,
                so_dot_thanh_toan: this.document.payment_installment,
                loai_thanh_toan: this.document.payment_type,
                vu_dau_tu: this.document.investment_project,
            };

            axios
                .put(
                    `/api/payment-requests/${this.document.payment_code}/basic-info`,
                    data,
                    {
                        headers: {
                            Authorization: "Bearer " + this.store.getToken,
                        },
                    }
                )
                .then((response) => {
                    if (response.data.success) {
                        // Show success message
                        Swal.fire({
                            title: "Thành công",
                            text: "Cập nhật thông tin phiếu trình thanh toán thành công",
                            icon: "success",
                            confirmButtonText: "OK",
                            buttonsStyling: false,
                            customClass: {
                                confirmButton: "btn btn-success",
                            },
                        });

                        // Update local data if needed
                        // this.document = { ...this.document, ...response.data.data };
                    } else {
                        Swal.fire({
                            title: "Lỗi",
                            text:
                                response.data.message ||
                                "Đã xảy ra lỗi khi cập nhật",
                            icon: "error",
                            confirmButtonText: "OK",
                            buttonsStyling: false,
                            customClass: {
                                confirmButton: "btn btn-danger",
                            },
                        });
                    }
                })
                .catch((error) => {
                    console.error("Error updating basic info:", error);
                    Swal.fire({
                        title: "Lỗi",
                        text:
                            "Đã xảy ra lỗi khi cập nhật: " +
                            (error.response?.data?.message || error.message),
                        icon: "error",
                        confirmButtonText: "OK",
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: "btn btn-danger",
                        },
                    });
                });
        },

        async fetchInvestmentProjects() {
            try {
                const response = await axios.get("/api/investment-projects", {
                    headers: {
                        Authorization: "Bearer " + this.store.getToken,
                    },
                });

                if (response.data.success) {
                    this.investmentProjects = response.data.data || [];
                } else {
                    console.error("Failed to fetch investment projects");
                }
            } catch (error) {
                console.error("Error fetching investment projects:", error);
                if (error.response?.status === 401) {
                    this.handleAuthError();
                }
            }
        },
        // Add a helper method to get investment project name from code
        getInvestmentProjectName(code) {
            if (!code) return "N/A";

            const project = this.investmentProjects.find(
                (p) => p.Ma_Vudautu === code
            );
            return project ? project.Ten_Vudautu : code;
        },

        togglePaymentDetails() {
            this.showPaymentDetails = !this.showPaymentDetails;
            localStorage.setItem("showPaymentDetails", this.showPaymentDetails);
        },

        togglePaymentRequests() {
            this.showPaymentRequests = !this.showPaymentRequests;
            localStorage.setItem(
                "showPaymentRequests",
                this.showPaymentRequests
            );
        },

        openAddPaymentRequestModal() {
            // Reset form
            this.newPaymentRequest = {
                ma_giai_ngan: "",
                ma_trinh_thanh_toan: this.document.payment_code, // Set parent payment request ID
                vu_dau_tu: this.document.investment_project,
                loai_thanh_toan: this.document.payment_type,
                khach_hang_ca_nhan: "",
                ma_khach_hang_ca_nhan: "",
                khach_hang_doanh_nghiep: "",
                ma_khach_hang_doanh_nghiep: "",
            };

            // Get selected receipt IDs (if any)
            this.paymentReceiptIds = this.selectedRecords;

            // Show the modal using Bootstrap's modal
            if (!this.addPaymentModal) {
                import("bootstrap/dist/js/bootstrap.bundle.min.js").then(
                    (bootstrap) => {
                        const modalElement =
                            document.getElementById("addPaymentModal");
                        if (modalElement) {
                            this.addPaymentModal = new bootstrap.Modal(
                                modalElement
                            );
                            this.addPaymentModal.show();
                        }
                    }
                );
            } else {
                this.addPaymentModal.show();
            }
        },

        async addPaymentRequest() {
            // Validate required fields
            if (
                !this.newPaymentRequest.ma_giai_ngan ||
                !this.newPaymentRequest.vu_dau_tu ||
                !this.newPaymentRequest.loai_thanh_toan
            ) {
                this.showError("Vui lòng nhập đầy đủ thông tin bắt buộc");
                return;
            }

            // Ensure we have receipt IDs to associate
            if (this.paymentReceiptIds.length === 0) {
                this.showError("Vui lòng chọn ít nhất một biên bản nghiệm thu");
                return;
            }

            this.isAddingPaymentRequest = true;

            try {
                const response = await axios.post(
                    `/api/disbursements/with-receipts`,
                    {
                        payment_request_data: this.newPaymentRequest,
                        receipt_ids: this.paymentReceiptIds,
                    },
                    {
                        headers: {
                            Authorization: "Bearer " + this.store.getToken,
                        },
                    }
                );

                if (response.data.success) {
                    // Close the modal
                    this.addPaymentModal.hide();

                    // Clear selection
                    this.selectedRecords = [];
                    this.paymentReceiptIds = [];

                    this.showSuccess(
                        "Đã tạo phiếu đề nghị thanh toán thành công"
                    );

                    // Refresh data
                    this.fetchPaymentRequests();
                    this.fetchDocument(); // Also refresh receipt details as we've changed their associations
                } else {
                    throw new Error(response.data.message || "Unknown error");
                }
            } catch (error) {
                console.error("Error adding payment request:", error);
                this.showError(
                    "Đã xảy ra lỗi khi tạo phiếu đề nghị thanh toán"
                );

                if (error.response?.status === 401) {
                    this.handleAuthError();
                }
            } finally {
                this.isAddingPaymentRequest = false;
            }
        },

        /**
         * Edit selected payment records
         */
        editSelectedPaymentRecords() {
            if (this.selectedPaymentRequests.length === 0) {
                this.showError(
                    "Vui lòng chọn ít nhất một phiếu đề nghị để chỉnh sửa"
                );
                return;
            }

            // Reset form first
            this.paymentEditForm = {
                ma_giai_ngan: "",
                khach_hang_ca_nhan: "",
                ma_khach_hang_ca_nhan: "",
                khach_hang_doanh_nghiep: "",
                ma_khach_hang_doanh_nghiep: "",
            };

            // If only one record is selected, populate the form with its data
            if (this.selectedPaymentRequests.length === 1) {
                const selectedCode = this.selectedPaymentRequests[0];
                const selectedRecord = this.paymentRequests.find(
                    (item) => item.disbursement_code === selectedCode
                );

                if (selectedRecord) {
                    // Populate form with existing data
                    this.paymentEditForm = {
                        ma_giai_ngan: selectedRecord.disbursement_code || "", // เติมข้อมูลรหัสเบิกจ่ายเดิม
                        khach_hang_ca_nhan:
                            selectedRecord.individual_customer || "",
                        ma_khach_hang_ca_nhan:
                            selectedRecord.individual_customer_code || "",
                        khach_hang_doanh_nghiep:
                            selectedRecord.corporate_customer || "",
                        ma_khach_hang_doanh_nghiep:
                            selectedRecord.corporate_customer_code || "",
                    };
                }
            } else {
                // If multiple records selected, show a message in the modal
                // This will be handled in the modal template
            }

            // Show the modal using Bootstrap's modal
            if (!this.paymentEditModal) {
                import("bootstrap/dist/js/bootstrap.bundle.min.js").then(
                    (bootstrap) => {
                        const modalElement =
                            document.getElementById("paymentEditModal");
                        if (modalElement) {
                            this.paymentEditModal = new bootstrap.Modal(
                                modalElement
                            );
                            this.paymentEditModal.show();
                        }
                    }
                );
            } else {
                this.paymentEditModal.show();
            }
        },

        /**
         * Update payment records
         */
        async updatePaymentRecords() {
            if (this.selectedPaymentRequests.length === 0) return;

            // Check if at least one field is filled
            const hasValue = Object.values(this.paymentEditForm).some(
                (value) => value !== null && value !== ""
            );

            if (!hasValue) {
                this.showError(
                    "Vui lòng nhập ít nhất một thông tin để cập nhật"
                );
                return;
            }

            this.isPaymentUpdating = true;

            try {
                const response = await axios.put(
                    `/api/disbursements/bulk`,
                    {
                        disbursement_codes: this.selectedPaymentRequests,
                        data: this.paymentEditForm,
                        original_code:
                            this.selectedPaymentRequests.length === 1
                                ? this.selectedPaymentRequests[0]
                                : null,
                    },
                    {
                        headers: {
                            Authorization: "Bearer " + this.store.getToken,
                        },
                    }
                );

                if (response.data.success) {
                    // Close the modal
                    this.paymentEditModal.hide();

                    // Show success message
                    this.showSuccess(
                        `Đã cập nhật thành công ${response.data.updated_count} phiếu đề nghị`
                    );

                    // Clear selection and refresh data
                    this.selectedPaymentRequests = [];
                    this.fetchPaymentRequests();
                    this.fetchDocument(); // เพิ่มการเรียก fetchDocument() เพื่อรีเฟรชข้อมูล Chi tiết hồ sơ thanh toán ด้วย
                } else {
                    throw new Error(response.data.message || "Unknown error");
                }
            } catch (error) {
                console.error("Error updating payment requests:", error);
                this.showError(
                    "Đã xảy ra lỗi khi cập nhật phiếu đề nghị thanh toán"
                );

                if (error.response?.status === 401) {
                    this.handleAuthError();
                }
            } finally {
                this.isPaymentUpdating = false;
            }
        },

        /**
         * Delete selected payment records
         */
        async deleteSelectedPaymentRecords() {
            if (this.selectedPaymentRequests.length === 0) {
                this.showError(
                    "Vui lòng chọn ít nhất một phiếu đề nghị để xóa"
                );
                return;
            }

            const result = await Swal.fire({
                title: "Xác nhận xóa?",
                html: `Bạn có chắc chắn muốn xóa <b>${this.selectedPaymentRequests.length}</b> phiếu đề nghị thanh toán đã chọn?`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Xóa",
                cancelButtonText: "Hủy",
                buttonsStyling: false,
                customClass: {
                    confirmButton: "btn btn-danger me-2",
                    cancelButton: "btn btn-outline-secondary",
                },
            });

            if (result.isConfirmed) {
                try {
                    const response = await axios.delete(
                        `/api/disbursements/bulk`,
                        {
                            data: {
                                disbursement_codes:
                                    this.selectedPaymentRequests,
                            },
                            headers: {
                                Authorization: "Bearer " + this.store.getToken,
                            },
                        }
                    );

                    if (response.data.success) {
                        // Remove deleted records from the local array
                        this.paymentRequests = this.paymentRequests.filter(
                            (item) =>
                                !this.selectedPaymentRequests.includes(
                                    item.disbursement_code
                                )
                        );

                        // Clear selection
                        this.selectedPaymentRequests = [];

                        this.showSuccess(
                            `Đã xóa thành công ${response.data.deleted_count} phiếu đề nghị`
                        );
                        // Refresh data
                        this.fetchPaymentRequests();
                    } else {
                        throw new Error(
                            response.data.message || "Unknown error"
                        );
                    }
                } catch (error) {
                    console.error("Error deleting payment requests:", error);
                    this.showError(
                        "Đã xảy ra lỗi khi xóa phiếu đề nghị thanh toán"
                    );

                    if (error.response?.status === 401) {
                        this.handleAuthError();
                    }
                }
            }
        },

        /**
         * Open the payment import modal
         */
        openPaymentImportModal() {
            // Reset state

            this.selectedPaymentFile = null;
            this.paymentUploadProgress = 0;
            this.paymentImportErrors = [];

            // Show the modal
            if (!this.paymentImportModal) {
                import("bootstrap/dist/js/bootstrap.bundle.min.js").then(
                    (bootstrap) => {
                        const modalElement =
                            document.getElementById("paymentImportModal");
                        if (modalElement) {
                            this.paymentImportModal = new bootstrap.Modal(
                                modalElement
                            );
                            this.paymentImportModal.show();
                        }
                    }
                );
            } else {
                this.paymentImportModal.show();
            }
        },

        /**
         * Close the payment import modal
         */

        /**
         * Handle file selection for payment import
         */
        handlePaymentFileSelected(event) {
            const file = event.target.files[0];
            if (file) {
                // Validate file type
                const fileType = file.name.split(".").pop().toLowerCase();
                if (!["csv", "xlsx", "xls"].includes(fileType)) {
                    this.showError("Please select a valid file (CSV or Excel)");
                    event.target.value = ""; // Clear the file input
                    this.selectedPaymentFile = null;
                    return;
                }

                this.selectedPaymentFile = file;
                this.paymentImportErrors = [];
            } else {
                this.selectedPaymentFile = null;
            }
        },
        closePaymentImportModal() {
            if (this.paymentImportModal) {
                this.paymentImportModal.hide();
            }

            // Reset import state
            this.selectedPaymentFile = null;
            this.paymentUploadProgress = 0;
            this.paymentImportErrors = [];
        },

        /**
         * Start the payment import process
         */
        async startPaymentImport() {
            if (!this.selectedPaymentFile) {
                this.showError("Please select a file to upload");
                return;
            }

            // Confirm before proceeding
            const result = await Swal.fire({
                title: "Confirm Import",
                text: "This will import payment requests based on the file. Continue?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, import",
                cancelButtonText: "Cancel",
                buttonsStyling: false,
                customClass: {
                    confirmButton: "btn btn-success me-2",
                    cancelButton: "btn btn-outline-secondary",
                },
            });

            if (!result.isConfirmed) {
                return;
            }

            this.isPaymentImporting = true;

            // Create form data for the file upload
            const formData = new FormData();
            formData.append("file", this.selectedPaymentFile);
            formData.append("payment_code", this.document.payment_code);

            try {
                const response = await axios.post(
                    `/api/disbursements/import`,
                    formData,
                    {
                        headers: {
                            "Content-Type": "multipart/form-data",
                            Authorization: "Bearer " + this.store.getToken,
                        },
                        onUploadProgress: (progressEvent) => {
                            // Calculate the upload progress
                            if (progressEvent.total) {
                                this.paymentUploadProgress = Math.round(
                                    (progressEvent.loaded * 100) /
                                        progressEvent.total
                                );
                            } else {
                                this.paymentUploadProgress = 50; // Show 50% if total is unknown
                            }
                        },
                    }
                );

                if (response.data.success) {
                    this.closePaymentImportModal();

                    // Show success notification
                    Swal.fire({
                        title: "Import Successful",
                        text:
                            response.data.message ||
                            "Payment requests imported successfully",
                        icon: "success",
                        confirmButtonText: "OK",
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: "btn btn-success",
                        },
                    });

                    // Refresh the payment requests data
                    this.fetchPaymentRequests();
                } else {
                    this.paymentImportErrors = response.data.errors || [
                        "Unknown error occurred during import.",
                    ];
                }
            } catch (error) {
                console.error("Import error:", error);

                if (error.response?.status === 401) {
                    this.handleAuthError();
                } else {
                    this.paymentImportErrors = error.response?.data?.errors || [
                        error.response?.data?.message ||
                            "Error occurred during import.",
                    ];
                }
            } finally {
                this.isPaymentImporting = false;
            }
        },

        /**
         * Download template for payment import
         */
        downloadPaymentImportTemplate() {
            // Create workbook with headers
            const wb = XLSX.utils.book_new();
            const headers = [
                "ma_giai_ngan",
                "vu_dau_tu",
                "loai_thanh_toan",
                "khach_hang_ca_nhan",
                "ma_khach_hang_ca_nhan",
                "khach_hang_doanh_nghiep",
                "ma_khach_hang_doanh_nghiep",
            ];

            // Add example row
            const exampleData = [
                [
                    "GN-001",
                    this.document.investment_project,
                    this.document.payment_type,
                    "Nguyen Van A",
                    "KH001",
                    "Cong ty ABC",
                    "DN001",
                ],
            ];

            // Create worksheet with headers and example data
            const ws = XLSX.utils.aoa_to_sheet([headers, ...exampleData]);

            // Add worksheet to workbook
            XLSX.utils.book_append_sheet(wb, ws, "Template");

            // Save file
            XLSX.writeFile(wb, "payment_request_import_template.xlsx");
        },

        /**
         * Fetch payment requests data
         */
        async fetchPaymentRequests() {
            try {
                const response = await axios.get(
                    `/api/payment-requests/${this.document.payment_code}/disbursements`,
                    {
                        headers: {
                            Authorization: "Bearer " + this.store.getToken,
                        },
                    }
                );

                if (response.data.success) {
                    this.paymentRequests = response.data.data;
                } else {
                    this.showError("Failed to fetch payment requests data");
                }
            } catch (error) {
                console.error("Error fetching payment requests:", error);
                if (error.response?.status === 401) {
                    this.handleAuthError();
                } else {
                    this.showError("Error loading payment requests data");
                }
            }
        },

        fetchUserData() {
            const user = localStorage.getItem("web_user");
            if (user) {
                this.user = JSON.parse(user);
            }
        },
        fetchDocument() {
            const id = this.$route.params.id;
            if (!id) {
                this.showError("Không tìm thấy mã phiếu trình");
                return;
            }

            this.isLoading = true;
            axios
                .get(`/api/payment-requests/${id}`, {
                    headers: {
                        Authorization: "Bearer " + this.store.getToken,
                    },
                })
                .then((response) => {
                    if (response.data.success) {
                        // Map main document data
                        // ดึงข้อมูล action ล่าสุดจาก processingHistory (ซึ่งมาจากตาราง Action_phieu_trinh_thanh_toan)
                        let latestAction = "";
                        if (
                            response.data.processingHistory &&
                            response.data.processingHistory.length > 0
                        ) {
                            latestAction =
                                response.data.processingHistory[0].action;
                        }
                        this.document = {
                            payment_code:
                                response.data.document.payment_code || "",
                            title: response.data.document.title || "",
                            investment_project:
                                response.data.document.investment_project || "",
                            payment_type:
                                response.data.document.payment_type || "",
                            // ใช้ action ล่าสุดเป็น status ถ้ามี แต่ถ้าไม่มีให้ใช้ค่า trang_thai_thanh_toan เหมือนเดิม
                            status:
                                latestAction ||
                                response.data.document.trang_thai_thanh_toan ||
                                "processing",
                            payment_installment:
                                response.data.document.payment_installment || 0,
                            proposal_number:
                                response.data.document.proposal_number || "",
                            created_at:
                                response.data.document.created_at || null,
                            total_amount:
                                response.data.document.total_amount || 0,
                            creator_name:
                                response.data.document.creator_name || "",
                            notes: response.data.document.notes || "",
                        };
                        this.noteText = this.document.notes || "";

                        // Map payment details
                        this.paymentDetails = Array.isArray(
                            response.data.paymentDetails
                        )
                            ? response.data.paymentDetails.map((item) => ({
                                  document_code: item.document_code || "",
                                  document_type: "Biên bản nghiệm thu DV",
                                  tram: item.tram || "",
                                  title: item.title || "",
                                  investment_project:
                                      item.investment_project || "",
                                  khach_hang_ca_nhan_dt_mia:
                                      item.khach_hang_ca_nhan_dt_mia || "",
                                  khach_hang_doanh_nghiep_dt_mia:
                                      item.khach_hang_doanh_nghiep_dt_mia || "",
                                  contract_number: item.contract_number || "",
                                  service_type: item.service_type || "",
                                  hop_dong_cung_ung_dich_vu:
                                      item.hop_dong_cung_ung_dich_vu || "",
                                  disbursement_code:
                                      item.disbursement_code || "",
                                  installment: item.installment || 1,
                                  amount: item.amount || 0,
                              }))
                            : [];

                        // Now fetch the payment requests (disbursements)
                        return axios.get(
                            `/api/payment-requests/${id}/disbursements`,
                            {
                                headers: {
                                    Authorization:
                                        "Bearer " + this.store.getToken,
                                },
                            }
                        );
                    } else {
                        throw new Error(
                            response.data.message ||
                                "Không tìm thấy thông tin phiếu trình thanh toán"
                        );
                    }
                })
                .then((disbResponse) => {
                    if (disbResponse && disbResponse.data.success) {
                        // Map the payment requests data
                        this.paymentRequests = Array.isArray(
                            disbResponse.data.data
                        )
                            ? disbResponse.data.data.map((item) => ({
                                  disbursement_code:
                                      item.disbursement_code || "",
                                  tram: item.tram || "",
                                  investment_project:
                                      item.investment_project || "",
                                  payment_type: item.payment_type || "",
                                  individual_customer:
                                      item.individual_customer || "",
                                  individual_customer_code:
                                      item.individual_customer_code || "",
                                  corporate_customer:
                                      item.corporate_customer || "",
                                  corporate_customer_code:
                                      item.corporate_customer_code || "",
                                  total_amount: item.total_amount || 0,
                                  total_hold_amount:
                                      item.total_hold_amount || 0,
                                  total_deduction: item.total_deduction || 0,
                                  total_interest: item.total_interest || 0,
                                  total_remaining: item.total_remaining || 0,
                                  payment_date: item.payment_date || null,
                                  proposal_number: item.proposal_number || "",
                                  installment: item.installment || 1,
                              }))
                            : [];

                        // Update unique values for payment filters
                        this.updatePaymentFilterValues();
                    }
                })
                .catch((error) => {
                    console.error("Error fetching document:", error);
                    this.showError(
                        "Lỗi khi tải thông tin phiếu trình thanh toán"
                    );
                    if (error.response?.status === 401) {
                        this.handleAuthError();
                    }
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },

        // Add this helper method to update payment filter values
        updatePaymentFilterValues() {
            // Extract unique values for dropdown filters
            this.uniquePaymentValues = {
                tram: [
                    ...new Set(
                        this.paymentRequests
                            .map((item) => item.tram)
                            .filter(Boolean)
                    ),
                ],
                investment_project: [
                    ...new Set(
                        this.paymentRequests
                            .map((item) => item.investment_project)
                            .filter(Boolean)
                    ),
                ],
                payment_type: [
                    ...new Set(
                        this.paymentRequests
                            .map((item) => item.payment_type)
                            .filter(Boolean)
                    ),
                ],
            };
        },
        formatCurrency(value) {
            if (!value) return "0 KIP";
            return new Intl.NumberFormat("vi-VN", {
                style: "currency",
                currency: "KIP",
                maximumFractionDigits: 0,
            }).format(value);
        },
        formatNumber(value) {
            if (!value) return "0";
            return new Intl.NumberFormat("vi-VN").format(value);
        },
        formatDate(date) {
            if (!date) return "N/A";
            return new Date(date).toLocaleDateString("vi-VN");
        },
        formatDateTime(date) {
            if (!date) return "N/A";
            return new Date(date).toLocaleString("vi-VN");
        },
        formatStatus(status) {
            if (status === "paid") return "Đã thanh toán";
            if (status === "submitted") return "Đã nộp kế toán";
            if (status === "processing") return "Đang xử lý";
            if (status === "Cancell") return "Hủy";
            return status || "N/A";
        },
        statusClass(status) {
            if (status === "paid") return "text-success";
            if (status === "submitted") return "text-primary";
            if (status === "processing") return "text-warning";
            if (status === "Cancell") return "text-danger";
            return "";
        },

        saveNote() {
            axios
                .post(
                    `/api/payment-requests/${this.$route.params.id}/notes`,
                    { note: this.noteText },
                    {
                        headers: {
                            Authorization: "Bearer " + this.store.getToken,
                        },
                    }
                )
                .then((response) => {
                    if (response.data.success) {
                        this.document.notes = this.noteText;
                        this.isEditingNote = false;
                        this.showSuccess("Ghi chú đã được cập nhật");
                        this.fetchDocument(); // Refresh data to get updated history
                    } else {
                        this.showError(
                            response.data.message || "Không thể lưu ghi chú"
                        );
                    }
                })
                .catch((error) => {
                    console.error("Error saving note:", error);
                    this.showError("Lỗi khi lưu ghi chú");
                    if (error.response?.status === 401) {
                        this.handleAuthError();
                    }
                });
        },
        cancelEditNote() {
            this.noteText = this.document.notes || "";
            this.isEditingNote = false;
        },
        confirmAction(title, text, icon) {
            return Swal.fire({
                title,
                text,
                icon,
                showCancelButton: true,
                confirmButtonText: "Xác nhận",
                cancelButtonText: "Hủy",
                buttonsStyling: false,
                customClass: {
                    confirmButton: "btn btn-success me-2",
                    cancelButton: "btn btn-outline-secondary",
                },
            });
        },
        showSuccess(message) {
            Swal.fire({
                toast: true,
                position: "top-end",
                title: message,
                icon: "success",
                showConfirmButton: false,
                timer: 3000,
            });
        },
        showError(message) {
            Swal.fire({
                title: "Lỗi!",
                text: message,
                icon: "error",
                confirmButtonText: "OK",
                buttonsStyling: false,
                customClass: {
                    confirmButton: "btn btn-success",
                },
            });
        },
        handleAuthError() {
            localStorage.removeItem("web_token");
            localStorage.removeItem("web_user");
            this.store.logout();
            this.$router.push("/login");
        },
        // Filter management
        toggleFilter(column) {
            this.activeFilter = this.activeFilter === column ? null : column;

            // If opening a dropdown filter, ensure unique values are populated
            if (
                (column === "tram" ||
                    column === "investment_project" ||
                    column === "service_type") &&
                this.activeFilter === column
            ) {
                this.updateUniqueValues(column);
            }
        },

        updateUniqueValues(column) {
            if (
                column === "tram" ||
                column === "investment_project" ||
                column === "service_type"
            ) {
                this.uniqueValues[column] = [
                    ...new Set(
                        this.paymentDetails
                            .map((item) => item[column])
                            .filter(Boolean) // Remove null/undefined values
                    ),
                ];
            }
        },

        resetFilter(column) {
            if (
                column === "tram" ||
                column === "investment_project" ||
                column === "service_type"
            ) {
                this.selectedFilterValues[column] = [];
            } else {
                this.columnFilters[column] = "";
            }
        },

        applyFilter(column) {
            this.activeFilter = null; // Close the filter dropdown
        },

        resetAllFilters() {
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
        },
        toggleSelectAll() {
            if (this.isAllSelected) {
                this.selectedRecords = [];
            } else {
                this.selectedRecords = this.filteredPaymentDetails.map(
                    (item) => item.document_code
                );
            }
        },

        editSelectedRecords() {
            if (this.selectedRecords.length === 0) {
                this.showError(
                    "Vui lòng chọn ít nhất một bản ghi để chỉnh sửa"
                );
                return;
            }

            this.editForm.disbursementCode = "";

            // Show the modal using Bootstrap's modal
            if (!this.editModal) {
                import("bootstrap/dist/js/bootstrap.bundle.min.js").then(
                    (bootstrap) => {
                        const modalElement =
                            document.getElementById("editModal");
                        if (modalElement) {
                            this.editModal = new bootstrap.Modal(modalElement);
                            this.editModal.show();
                        }
                    }
                );
            } else {
                this.editModal.show();
            }
        },

        async updateRecords() {
            if (this.selectedRecords.length === 0) return;

            this.isUpdating = true;

            try {
                const response = await axios.post(
                    `/api/payment-requests/${this.document.payment_code}/update-records`,
                    {
                        receipt_ids: this.selectedRecords,
                        disbursement_code: this.editForm.disbursementCode,
                    },
                    {
                        headers: {
                            Authorization: "Bearer " + this.store.getToken,
                        },
                    }
                );

                if (response.data.success) {
                    // Close the modal
                    this.editModal.hide();

                    // Update local data
                    this.selectedRecords.forEach((receiptId) => {
                        const record = this.paymentDetails.find(
                            (item) => item.document_code === receiptId
                        );
                        if (record) {
                            record.disbursement_code =
                                this.editForm.disbursementCode;
                        }
                    });

                    // Clear selection
                    this.selectedRecords = [];

                    this.showSuccess("Cập nhật thành công");
                    this.fetchDocument(); // Refresh data
                } else {
                    throw new Error(response.data.message || "Unknown error");
                }
            } catch (error) {
                console.error("Error updating records:", error);
                this.showError("Đã xảy ra lỗi khi cập nhật bản ghi");

                if (error.response?.status === 401) {
                    this.handleAuthError();
                }
            } finally {
                this.isUpdating = false;
            }
        },

        async deleteSelectedRecords() {
            if (this.selectedRecords.length === 0) {
                this.showError("Vui lòng chọn ít nhất một bản ghi để xóa");
                return;
            }

            const result = await Swal.fire({
                title: "Xác nhận xóa?",
                html: `Bạn có chắc chắn muốn xóa <b>${this.selectedRecords.length}</b> bản ghi đã chọn?`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Xóa",
                cancelButtonText: "Hủy",
                buttonsStyling: false,
                customClass: {
                    confirmButton: "btn btn-danger me-2",
                    cancelButton: "btn btn-outline-secondary",
                },
            });

            if (result.isConfirmed) {
                try {
                    const response = await axios.post(
                        `/api/payment-requests/${this.document.payment_code}/delete-records`,
                        {
                            receipt_ids: this.selectedRecords,
                        },
                        {
                            headers: {
                                Authorization: "Bearer " + this.store.getToken,
                            },
                        }
                    );

                    if (response.data.success) {
                        // Remove deleted records from the local array
                        this.paymentDetails = this.paymentDetails.filter(
                            (item) =>
                                !this.selectedRecords.includes(
                                    item.document_code
                                )
                        );

                        // Clear selection
                        this.selectedRecords = [];

                        this.showSuccess("Xóa thành công");
                        this.fetchDocument(); // Refresh data
                    } else {
                        throw new Error(
                            response.data.message || "Unknown error"
                        );
                    }
                } catch (error) {
                    console.error("Error deleting records:", error);
                    this.showError("Đã xảy ra lỗi khi xóa bản ghi");

                    if (error.response?.status === 401) {
                        this.handleAuthError();
                    }
                }
            }
        },
        openAddReceiptModal() {
            // Clear previous data
            this.searchQuery = "";
            this.receiptResults = [];
            this.selectedReceipts = [];

            // Store existing receipt IDs to prevent duplicates
            this.existingReceiptIds = this.paymentDetails.map(
                (item) => item.document_code
            );

            // Show modal using Bootstrap
            if (!this.addReceiptModal) {
                import("bootstrap/dist/js/bootstrap.bundle.min.js").then(
                    (bootstrap) => {
                        const modalElement =
                            document.getElementById("addReceiptModal");
                        if (modalElement) {
                            this.addReceiptModal = new bootstrap.Modal(
                                modalElement
                            );
                            this.addReceiptModal.show();
                        }
                    }
                );
            } else {
                this.addReceiptModal.show();
            }
        },

        async searchReceipts() {
            if (!this.searchQuery || this.searchQuery.length < 2) {
                this.receiptResults = [];
                return;
            }

            try {
                this.isSearching = true; // Add a loading state

                const response = await axios.get(
                    "/api/bienban-nghiemthu-search-pttt",
                    {
                        params: {
                            search: this.searchQuery,
                            investment_project:
                                this.document.investment_project,
                        },
                        headers: {
                            Authorization: "Bearer " + this.store.getToken,
                        },
                    }
                );

                console.log("Search response:", response.data); // Debug log

                if (response.data && Array.isArray(response.data)) {
                    // Map the response to include all required fields
                    this.receiptResults = response.data.map((item) => ({
                        ma_nghiem_thu: item.ma_nghiem_thu,
                        tieu_de: item.tieu_de,
                        tram: item.tram,
                        can_bo_nong_vu: item.can_bo_nong_vu,
                        vu_dau_tu: item.vu_dau_tu,
                        hop_dong_dau_tu_mia: item.hop_dong_dau_tu_mia,
                        hinh_thuc_thuc_hien_dv: item.hinh_thuc_thuc_hien_dv,
                        hop_dong_cung_ung_dich_vu:
                            item.hop_dong_cung_ung_dich_vu,
                        tong_tien: item.tong_tien || 0,
                    }));
                } else {
                    this.receiptResults = [];
                }
            } catch (error) {
                console.error("Error searching receipts:", error);
                if (error.response?.status === 401) {
                    this.handleAuthError();
                }
            } finally {
                this.isSearching = false; // Clear loading state
            }
        },

        isDuplicate(receiptId) {
            // Check if receipt is already in the payment request
            return this.existingReceiptIds.includes(receiptId);
        },

        selectReceipt(item) {
            // Check if receipt is not already selected
            const isAlreadySelected = this.selectedReceipts.some(
                (selected) => selected.ma_nghiem_thu === item.ma_nghiem_thu
            );

            if (!isAlreadySelected) {
                this.selectedReceipts.push(item);
            }
        },

        removeSelectedReceipt(index) {
            this.selectedReceipts.splice(index, 1);
        },

        calculateTotalSelected() {
            return this.selectedReceipts.reduce(
                (sum, item) => sum + (parseFloat(item.tong_tien) || 0),
                0
            );
        },

        async addSelectedReceipts() {
            if (this.selectedReceipts.length === 0) return;

            this.isAdding = true;

            try {
                const receiptIds = this.selectedReceipts.map(
                    (item) => item.ma_nghiem_thu
                );

                const response = await axios.post(
                    `/api/payment-requests/${this.document.payment_code}/add-receipts`,
                    {
                        receipt_ids: receiptIds,
                    },
                    {
                        headers: {
                            Authorization: "Bearer " + this.store.getToken,
                        },
                    }
                );

                if (response.data.success) {
                    // Close the modal
                    this.addReceiptModal.hide();

                    // Show success message
                    this.showSuccess(
                        `Đã thêm ${this.selectedReceipts.length} biên bản thành công`
                    );

                    // Refresh data
                    this.fetchDocument();
                } else {
                    throw new Error(response.data.message || "Unknown error");
                }
            } catch (error) {
                console.error("Error adding receipts:", error);
                this.showError("Đã xảy ra lỗi khi thêm biên bản");

                if (error.response?.status === 401) {
                    this.handleAuthError();
                }
            } finally {
                this.isAdding = false;
            }
        },
        exportToExcel() {
            // Show loading indicator
            Swal.fire({
                title: "Đang xuất dữ liệu",
                html: "Vui lòng đợi trong giây lát...",
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            try {
                // Create a workbook with a worksheet
                const wb = XLSX.utils.book_new();

                // Format the data for export
                const paymentDetailsForExport = this.prepareDataForExport();

                // Create worksheet from data
                const ws = XLSX.utils.json_to_sheet(paymentDetailsForExport);

                // Set column widths
                const columnWidths = this.calculateColumnWidths(
                    paymentDetailsForExport
                );
                ws["!cols"] = columnWidths;

                // Add header styling (bold)
                this.styleWorksheet(ws);

                // Add the worksheet to the workbook
                XLSX.utils.book_append_sheet(wb, ws, "Chi tiết thanh toán");

                // Generate filename with document code and date
                const fileName = `PhieuTrinhTT_${
                    this.document.payment_code
                }_${new Date().toISOString().slice(0, 10)}.xlsx`;

                // Export the file
                XLSX.writeFile(wb, fileName);

                // Close loading and show success
                Swal.close();
                this.showSuccess("Xuất Excel thành công!");
            } catch (error) {
                console.error("Error exporting to Excel:", error);
                Swal.fire({
                    icon: "error",
                    title: "Lỗi xuất Excel",
                    text: "Đã xảy ra lỗi khi xuất dữ liệu sang Excel.",
                });
            }
        },

        prepareDataForExport() {
            // Get filtered data or all data if no filters applied
            const dataToExport = this.filteredPaymentDetails;

            // Create translated headers and mapped data
            return dataToExport.map((item) => {
                return {
                    "Mã nghiệm thu": item.document_code,
                    Trạm: item.tram || "N/A",
                    "Tiêu đề": item.title,
                    "Vụ đầu tư": item.investment_project,
                    "KH Cá nhân": item.khach_hang_ca_nhan_dt_mia || "N/A",
                    "KH Doanh nghiệp":
                        item.khach_hang_doanh_nghiep_dt_mia || "N/A",
                    "Hợp đồng đầu tư": item.contract_number,
                    "Hình thức DV": item.service_type,
                    "Hợp đồng cung ứng DV":
                        item.hop_dong_cung_ung_dich_vu || "N/A",
                    "Mã đề nghị giải ngân": item.disbursement_code || "N/A",
                    "Đợt TT": item.installment,
                    "Số tiền": this.formatCurrencyRaw(item.amount),
                };
            });
        },

        calculateColumnWidths(data) {
            // Base column widths
            const baseWidths = {
                "Mã nghiệm thu": 15,
                Trạm: 10,
                "Tiêu đề": 30,
                "Vụ đầu tư": 15,
                "KH Cá nhân": 25,
                "KH Doanh nghiệp": 25,
                "Hợp đồng đầu tư": 20,
                "Hình thức DV": 15,
                "Hợp đồng cung ứng DV": 20,
                "Mã đề nghị giải ngân": 20,
                "Đợt TT": 8,
                "Số tiền": 15,
            };

            // Convert to column width objects
            return Object.keys(baseWidths).map((key) => ({
                wch: baseWidths[key],
            }));
        },

        styleWorksheet(worksheet) {
            // Add header styling
            if (!worksheet["!rows"]) worksheet["!rows"] = [];

            // Make first row (header) bold
            worksheet["!rows"][0] = { hpt: 18, hpx: 18 }; // Height

            // Get the range of all cells
            const range = XLSX.utils.decode_range(worksheet["!ref"]);

            // Create styles for header row
            for (let col = range.s.c; col <= range.e.c; col++) {
                const cellAddress = XLSX.utils.encode_cell({ r: 0, c: col });
                if (!worksheet[cellAddress]) continue;

                // Create cell object if it doesn't exist
                if (typeof worksheet[cellAddress] !== "object") {
                    worksheet[cellAddress] = {
                        t: "s",
                        v: worksheet[cellAddress],
                    };
                }

                // Add styles to cell
                worksheet[cellAddress].s = {
                    font: { bold: true },
                    fill: { fgColor: { rgb: "EEEEEE" } },
                    alignment: { horizontal: "center", vertical: "center" },
                };
            }
        },

        formatCurrencyRaw(value) {
            if (!value) return "0";
            return new Intl.NumberFormat("vi-VN").format(value);
        },
        // Import related methods
        openImportModal() {
            // Reset import state
            this.selectedFile = null;
            this.uploadProgress = 0;
            this.importErrors = [];

            // Show modal using Bootstrap
            if (!this.importModal) {
                import("bootstrap/dist/js/bootstrap.bundle.min.js").then(
                    (bootstrap) => {
                        const modalElement =
                            document.getElementById("importModal");
                        if (modalElement) {
                            this.importModal = new bootstrap.Modal(
                                modalElement
                            );
                            this.importModal.show();
                        }
                    }
                );
            } else {
                this.importModal.show();
            }
        },

        handleFileSelected(event) {
            const file = event.target.files[0];
            if (file) {
                // Validate file type
                const fileType = file.name.split(".").pop().toLowerCase();
                if (!["csv", "xlsx", "xls"].includes(fileType)) {
                    this.showError("Please select a valid file (CSV or Excel)");
                    event.target.value = ""; // Clear the file input
                    this.selectedFile = null;
                    return;
                }

                this.selectedFile = file;
                this.importErrors = [];
            } else {
                this.selectedFile = null;
            }
        },

        closeImportModal() {
            if (this.importModal) {
                this.importModal.hide();
            }

            // Reset import state
            this.selectedFile = null;
            this.uploadProgress = 0;
            this.importErrors = [];
        },

        async startImport() {
            if (!this.selectedFile) {
                this.showError("Please select a file to upload");
                return;
            }

            // Confirm before proceeding
            const result = await Swal.fire({
                title: "Confirm Import",
                text: "This will update payment details based on the import file. Continue?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, import",
                cancelButtonText: "Cancel",
                buttonsStyling: false,
                customClass: {
                    confirmButton: "btn btn-success me-2",
                    cancelButton: "btn btn-outline-secondary",
                },
            });

            if (!result.isConfirmed) {
                return;
            }

            this.isImporting = true;

            // Create form data for the file upload
            const formData = new FormData();
            formData.append("file", this.selectedFile);
            formData.append("payment_code", this.document.payment_code);

            try {
                const response = await axios.post(
                    `/api/payment-requests/${this.document.payment_code}/import-data`,
                    formData,
                    {
                        headers: {
                            "Content-Type": "multipart/form-data",
                            Authorization: "Bearer " + this.store.getToken,
                        },
                        onUploadProgress: (progressEvent) => {
                            // Calculate the upload progress
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
                    this.closeImportModal();

                    // Show success notification
                    Swal.fire({
                        title: "Import Successful",
                        text:
                            response.data.message ||
                            "Data imported successfully",
                        icon: "success",
                        confirmButtonText: "OK",
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: "btn btn-success",
                        },
                    });

                    // Refresh the data to show updated values
                    this.fetchDocument();
                } else {
                    this.importErrors = response.data.errors || [
                        "Unknown error occurred during import.",
                    ];
                }
            } catch (error) {
                console.error("Import error:", error);

                if (error.response?.status === 401) {
                    this.handleAuthError();
                } else {
                    this.importErrors = error.response?.data?.errors || [
                        error.response?.data?.message ||
                            "Error occurred during import.",
                    ];
                }
            } finally {
                this.isImporting = false;
            }
        },
        downloadImportTemplate() {
            try {
                // Create a workbook with a worksheet
                const wb = XLSX.utils.book_new();

                // Create sample data
                const data = [
                    {
                        ma_nghiem_thu: "Example-001",
                        ma_de_nghi_giai_ngan: "DN-001",
                    },
                    {
                        ma_nghiem_thu: "Example-002",
                        ma_de_nghi_giai_ngan: "DN-002",
                    },
                ];

                // Create worksheet from data
                const ws = XLSX.utils.json_to_sheet(data);

                // Add the worksheet to the workbook
                XLSX.utils.book_append_sheet(wb, ws, "Import Template");

                // Generate filename
                const fileName = `import_template.xlsx`;

                // Export the file
                XLSX.writeFile(wb, fileName);

                this.showSuccess("Template downloaded successfully!");
            } catch (error) {
                console.error("Error creating template:", error);
                this.showError("Could not create template file");
            }
        },
        pageChanged(page) {
            this.currentPage = page;
            // Scroll to the top of the table for better UX
            const tableElement = document.querySelector(".table-container");
            if (tableElement) {
                tableElement.scrollIntoView({
                    behavior: "smooth",
                    block: "start",
                });
            }
        },
        // Payment request filter management
        togglePaymentFilter(column) {
            this.activePaymentFilter =
                this.activePaymentFilter === column ? null : column;

            // If opening a dropdown filter, ensure unique values are populated
            if (
                (column === "tram" ||
                    column === "investment_project" ||
                    column === "payment_type") &&
                this.activePaymentFilter === column
            ) {
                this.updateUniquePaymentValues(column);
            }
        },

        updateUniquePaymentValues(column) {
            if (
                column === "tram" ||
                column === "investment_project" ||
                column === "payment_type"
            ) {
                this.uniquePaymentValues[column] = [
                    ...new Set(
                        this.paymentRequests
                            .map((item) => item[column])
                            .filter(Boolean) // Remove null/undefined values
                    ),
                ];
            }
        },

        resetPaymentFilter(column) {
            if (
                column === "tram" ||
                column === "investment_project" ||
                column === "payment_type"
            ) {
                this.selectedPaymentFilterValues[column] = [];
            } else {
                this.paymentFilters[column] = "";
            }
        },

        applyPaymentFilter(column) {
            this.activePaymentFilter = null; // Close the filter dropdown
        },

        resetPaymentRequestFilters() {
            // Reset all column filters
            for (const key in this.paymentFilters) {
                this.paymentFilters[key] = "";
            }

            // Reset dropdown filters
            for (const key in this.selectedPaymentFilterValues) {
                this.selectedPaymentFilterValues[key] = [];
            }

            // Reset active filter (close any open filter dropdown)
            this.activePaymentFilter = null;
        },
        toggleSelectAllPaymentRequests() {
            if (this.isAllPaymentRequestsSelected) {
                this.selectedPaymentRequests = [];
            } else {
                this.selectedPaymentRequests = this.filteredPaymentRequests.map(
                    (item) => item.disbursement_code
                );
            }
        },
        paymentPageChanged(page) {
            this.currentPaymentPage = page;
            // Scroll to the top of the table for better UX
            const tableElement = document.querySelector(".table-container");
            if (tableElement) {
                tableElement.scrollIntoView({
                    behavior: "smooth",
                    block: "start",
                });
            }
        },
    },
};
</script>

<style scoped>
/* Sticky container */
.sticky-wrapper {
    position: sticky;
    top: 0px;
    left: 230px;
    right: 0;
    z-index: 999;
    background: white;
    padding: 1rem 0;
    border-bottom: 1px solid #e0e3e8;
    transition: box-shadow 0.3s ease;
}

/* Toggle section styles */
.toggle-section {
    display: flex;
    align-items: center;
    cursor: pointer;
    user-select: none;
}

.toggle-icon {
    transition: transform 0.3s ease;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 8px;
}

.cursor-pointer {
    cursor: pointer;
}

.card-header {
    padding: 1rem 1.25rem;
    transition: background-color 0.2s ease;
}

.card-header:hover {
    background-color: rgba(0, 0, 0, 0.01);
}

/* Transition effects for card body */
.card-body {
    transition: all 0.3s ease;
}

.card-actions {
    display: flex;
    gap: 8px;
    align-items: center;
    position: relative;
    right: 0;
    top: 0;
}

.card-actions > span {
    position: relative;
    right: auto;
    top: auto;
}

/* Better positioning for action buttons */
.import-data-btn,
.export-excel-btn,
.reset-all-filters-btn,
.edit-records-btn,
.delete-records-btn,
.add-records-btn {
    position: relative;
    right: auto;
    top: auto;
}

/* Container for buttons and progress */
.container-fluid {
    max-width: inherit;
    margin: 0 auto;
    background: white;
}

/* Main content wrapper */
.main-content-wrapper {
    margin-top: 10px;
    padding: 1rem;
}

/* Hide scrollbar for Chrome, Safari and Opera */
.scroll-area::-webkit-scrollbar {
    display: none;
}

/* Hide scrollbar for IE, Edge */
.scroll-area {
    -ms-overflow-style: none;
    scrollbar-width: none;
    height: calc(90vh - 60px);
    -webkit-overflow-scrolling: touch;
}

/* Customize scrollbar styling */
.ps__rail-y {
    width: 9px;
    background-color: transparent !important;
}

.ps__thumb-y {
    width: 6px;
    background-color: rgba(0, 0, 0, 0.2);
    border-radius: 6px;
}

/* Hover styling for scrollbar */
.ps__rail-y:hover > .ps__thumb-y,
.ps__rail-y:focus > .ps__thumb-y,
.ps__rail-y.ps--clicking .ps__thumb-y {
    width: 6px;
    background-color: rgba(0, 0, 0, 0.3);
}

/* Progress tracker styles */
.progress-tracker {
    position: relative;
    display: flex;
    justify-content: space-between;
    margin: 0.5rem 0;
}
.progress-tracker::before {
    content: "";
    position: absolute;
    top: 20px;
    width: 98%;
    height: 3px;
    background-color: #e9ecef;
    z-index: 0;
    transition: background-color 0.3s;
}
.progress-tracker.pending::before {
    background: linear-gradient(to right, #ffc107 0%, #e9ecef 0%);
}
.progress-tracker.approved::before {
    background: linear-gradient(
        to right,
        #ffc107 33%,
        #1e88e5 33%,
        #1e88e5 66%,
        #e9ecef 66%
    );
}
.progress-tracker.paid::before {
    background: linear-gradient(
        to right,
        #ffc107 33%,
        #1e88e5 33%,
        #1e88e5 66%,
        #198754 66%,
        #198754 100%
    );
}
.track-step {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    z-index: 1;
}
.step-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: white;
    font-size: 1.2rem;
    margin-bottom: 0.5rem;
    border: 2px solid #dee2e6;
    transition: all 0.3s;
}
.track-step.active .step-icon {
    width: 50px;
    height: 50px;
    font-size: 1.5rem;
    border: none;
}
.status-pending {
    color: #ffc107;
}
.status-approved {
    color: #1e88e5;
}
.status-paid {
    color: #198754;
}
.track-step.active .status-pending {
    background-color: #ffc107;
    color: white;
}
.track-step.active .status-approved {
    background-color: #1e88e5;
    color: white;
}
.track-step.active .status-paid {
    background-color: #198754;
    color: white;
}
.step-label {
    text-align: center;
    font-size: 0.9rem;
    max-width: 100px;
}

/* Timeline styles */
.timeline-wrapper {
    padding: 0;
    margin: 0;
}
.timeline {
    list-style: none;
    padding: 20px 0;
    position: relative;
    margin: 0;
}
.timeline:before {
    top: 0;
    bottom: 0;
    position: absolute;
    content: " ";
    width: 3px;
    background-color: #e9ecef;
    left: 50px;
}
.timeline-item {
    margin-bottom: 20px;
    position: relative;
}
.timeline-badge {
    width: 36px;
    height: 36px;
    left: 32px;
    margin-left: -18px;
    z-index: 1;
    color: #fff;
    border-radius: 50%;
    position: absolute;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}
.timeline-panel {
    width: calc(100% - 90px);
    float: right;
    border: 1px solid #e9ecef;
    border-radius: 0.25rem;
    padding: 15px;
    position: relative;
    background-color: #fff;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}
.timeline-panel:before {
    position: absolute;
    top: 12px;
    left: -10px;
    display: inline-block;
    border-top: 10px solid transparent;
    border-right: 10px solid #e9ecef;
    border-bottom: 10px solid transparent;
    content: " ";
}
.timeline-panel:after {
    position: absolute;
    top: 13px;
    left: -9px;
    display: inline-block;
    border-top: 9px solid transparent;
    border-right: 9px solid #fff;
    border-bottom: 9px solid transparent;
    content: " ";
}
.timeline-title {
    margin-top: 0;
    color: inherit;
    font-size: 1rem;
    margin-bottom: 5px;
}
.timeline-body > p,
.timeline-body > ul {
    margin-bottom: 0;
}
.bg-success {
    background-color: #198754 !important;
}
.bg-primary {
    background-color: #1e88e5 !important;
}
.bg-info {
    background-color: #17a2b8 !important;
}
.bg-danger {
    background-color: #dc3545 !important;
}
.bg-secondary {
    background-color: #6c757d !important;
}

.button-30:focus {
    box-shadow: #d6d6e7 0 0 0 1.5px inset, rgba(45, 35, 66, 0.4) 0 2px 4px,
        rgba(45, 35, 66, 0.3) 0 7px 13px -3px, #d6d6e7 0 -3px 0 inset;
}

.button-30:hover {
    box-shadow: rgba(45, 35, 66, 0.4) 0 4px 8px,
        rgba(45, 35, 66, 0.3) 0 7px 13px -3px, #d6d6e7 0 -3px 0 inset;
    transform: translateY(-2px);
}

.button-30:active {
    box-shadow: #d6d6e7 0 3px 7px inset;
    transform: translateY(2px);
}

.button-30-text-green:focus {
    box-shadow: #92d9a7 0 0 0 1.5px inset, rgba(45, 35, 66, 0.4) 0 2px 4px,
        rgba(45, 35, 66, 0.3) 0 7px 13px -3px, #92d9a7 0 -3px 0 inset;
}

.button-30-text-green:hover {
    box-shadow: rgba(45, 35, 66, 0.4) 0 4px 8px,
        rgba(45, 35, 66, 0.3) 0 7px 13px -3px, #92d9a7 0 -3px 0 inset;
    transform: translateY(-2px);
}

/* Responsive styles */
@media (max-width: 768px) {
    .sticky-wrapper {
        top: 0px;
        left: 0;
        padding: 0.5rem 0;
        z-index: 100;
    }

    .main-content-wrapper {
        margin-top: 10px;
    }

    .d-flex.flex-md-row {
        flex-direction: column !important;
    }

    .col-md-6 {
        width: 100% !important;
    }
}

/* Enhanced table styles */
.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.table {
    width: 100%;
    margin-bottom: 1rem;
    border-collapse: collapse;
}

.table th {
    font-weight: 600;
    white-space: nowrap;
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
}

.table td,
.table th {
    padding: 0.75rem;
    vertical-align: middle;
    border: 1px solid #dee2e6;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.03);
}

/* Empty state styling */
.empty-state {
    text-align: center;
    padding: 30px 0;
    color: #6c757d;
}

.empty-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: #dee2e6;
}

.note-content {
    min-height: 80px;
    white-space: pre-line;
}

/* Card styling */
.card {
    border: none;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    border-radius: 5px;
    overflow: hidden;
}

/* Filter button styling */
.filter-btn {
    background: none;
    border: none;
    font-size: 0.75rem;
    color: #6c757d;
    padding: 0 0.25rem;
    cursor: pointer;
    transition: color 0.2s ease;
}
.filter-btn:hover {
    color: #10b981;
}

/* Reset all filters button styling */
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

/* Pointer for filter dropdown */
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

/* Ensure filter icons look professional */
.fas.fa-filter {
    transition: color 0.2s;
}

button:hover .fas.fa-filter:not(.text-green-500) {
    color: #10b981;
}

/* Active filter styling */
.text-green-500 {
    color: #10b981;
}

/* Ensure the table headers have proper positioning */
.table-light th {
    position: relative;
    vertical-align: middle;
    text-align: center;
    white-space: nowrap;
}

/* Add hover effect to table rows */
.table-hover tbody tr:hover {
    background-color: rgba(16, 185, 129, 0.05);
}

/* Button styling for action buttons */
.edit-records-btn,
.delete-records-btn,
.add-records-btn {
    position: absolute;
    z-index: 99;
    font-size: 1rem;
    cursor: pointer;
    color: #fff;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease;
}

.edit-records-btn {
    right: 40px;
    top: 25px;
    background: #1e88e5;
}

.delete-records-btn {
    right: 75px;
    top: 25px;
    background: #dc3545;
}

.add-records-btn {
    right: 110px;
    top: 25px;
    background: #198754;
}

.edit-records-btn:hover {
    background: #0d6efd;
    transform: scale(1.1);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.delete-records-btn:hover {
    background: #c82333;
    transform: scale(1.1);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.add-records-btn:hover {
    background: #157347;
    transform: scale(1.1);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.edit-records-btn.disabled,
.delete-records-btn.disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Checkbox styling */
.form-check-input {
    cursor: pointer;
    width: 18px;
    height: 18px;
}

/* Search input styling */
.search-input-wrapper {
    position: relative;
}

.search-icon {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    font-size: 1.2rem;
}

/* Search results styling */
.results-title {
    font-weight: 500;
    color: #495057;
    margin-bottom: 0.5rem;
}

.table-warning {
    background-color: rgba(255, 243, 205, 0.5);
}
/* Table Professional Enhancement */
.table {
    font-size: 0.8125rem; /* 13px - เพิ่มขนาดตัวอักษรเล็กน้อยเพื่อการอ่านง่าย */
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #e6e6e6;
    margin-bottom: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

/* Table Header Styling */
.table thead th {
    background-color: #f8f9fa;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.03em;
    padding: 12px 10px;
    border-bottom: 2px solid #e9ecef;
    position: sticky;
    top: 0;
    z-index: 5;
}

/* Improved cell padding for better readability */
.table td {
    padding: 10px;
    max-width: 250px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    vertical-align: middle;
    border-top: 1px solid #f0f0f0;
    transition: all 0.2s;
}

/* Tooltip for truncated content */
.table td[title] {
    cursor: help;
}

/* Add hover style to see more of truncated content */
.table td:hover {
    overflow: visible;
    white-space: normal;
    max-width: none;
    position: relative;
    z-index: 1;
    box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
    background-color: #fff;
}

/* Zebra striping for easier row scanning */
.table tbody tr:nth-of-type(odd) {
    background-color: rgba(0, 0, 0, 0.01);
}

/* Row hover effect */
.table-hover tbody tr:hover {
    background-color: rgba(16, 185, 129, 0.05);
}

/* Warning state with softer color */
.table-warning {
    background-color: rgba(255, 243, 205, 0.5) !important;
}

/* Align numerical data right */
.table td.text-end,
.table th.text-end {
    text-align: right;
}

/* Highlight active row - add .active class to tr when needed */
.table tr.active {
    background-color: rgba(16, 185, 129, 0.1) !important;
}

/* Highlight selected row - add .selected class to tr when needed */
.table tr.selected td {
    background-color: rgba(30, 136, 229, 0.08);
    border-left: 3px solid #1e88e5;
}

/* Footer styling */
.table tfoot {
    font-weight: 600;
    background-color: #f8f9fa;
}

.table tfoot td {
    border-top: 2px solid #e9ecef;
}
.export-excel-btn {
    position: absolute;
    right: 145px;
    top: 25px;
    z-index: 99;
    font-size: 1rem;
    cursor: pointer;
    color: #fff;
    background: #217346;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease;
}

.export-excel-btn:hover {
    background: #105325;
    transform: scale(1.1);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}
.import-data-btn {
    position: absolute;
    right: 180px;
    top: 25px;
    z-index: 99;
    font-size: 1rem;
    cursor: pointer;
    color: #fff;
    background: #6366f1;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease;
}

.import-data-btn:hover {
    background: #4f46e5;
    transform: scale(1.1);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

/* Enhanced pagination styling */
.pagination-wrapper {
    background: white;
    padding: 10px;
    border-radius: 5px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.pagination {
    margin-bottom: 0;
}

.page-link {
    border-radius: 4px !important;
    color: #198754;
    font-weight: 500;
    min-width: 38px;
    text-align: center;
    transition: all 0.2s;
}

.page-link:hover {
    background-color: #f0fff4;
    color: #198754;
    z-index: 2;
}

.page-item.active .page-link {
    background-color: #198754;
    border-color: #198754;
    color: white;
    box-shadow: 0 2px 5px rgba(25, 135, 84, 0.2);
    z-index: 3;
}

.page-item.disabled .page-link {
    color: #6c757d;
    pointer-events: none;
    background-color: #f8f9fa;
    border-color: #e9ecef;
}

@media (max-width: 576px) {
    .pagination-wrapper {
        flex-direction: column;
        gap: 0.75rem;
    }

    .pagination-info {
        text-align: center;
    }

    .page-link {
        padding: 0.375rem 0.75rem;
        min-width: 32px;
    }
}
.export-excel-btn-payment {
    position: absolute;
    right: 40px;
    top: 25px;
    z-index: 99;
    font-size: 1rem;
    cursor: pointer;
    color: #fff;
    background: #217346;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease;
}

.export-excel-btn-payment:hover {
    background: #105325;
    transform: scale(1.1);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}
.reset-filters-btn-payment {
    position: absolute;
    right: 75px;
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

.reset-filters-btn-payment:hover {
    background: #10b981;
    transform: rotate(30deg);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}
.action-button-group {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.progress-tracker::before {
    content: "";
    position: absolute;
    top: 20px;
    width: 98%;
    height: 4px;
    background-color: #e9ecef;
    border-radius: 2px;
    z-index: 0;
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.progress-tracker.processing::before {
    background: linear-gradient(to right, #ffc107 50%, #e9ecef 50%);
}

.progress-tracker.submitted::before {
    background: linear-gradient(
        to right,
        #ffc107 33%,
        #1e88e5 33%,
        #1e88e5 66%,
        #e9ecef 66%
    );
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1),
        0 0 6px rgba(30, 136, 229, 0.3);
}

.progress-tracker.paid::before {
    background: linear-gradient(
        to right,
        #ffc107 33%,
        #1e88e5 33%,
        #1e88e5 66%,
        #198754 66%
    );
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1),
        0 0 8px rgba(25, 135, 84, 0.35);
}

.progress-tracker.cancelled::before {
    background: linear-gradient(
        to right,
        #ffc107 33%,
        #dc3545 33%,
        #dc3545 100%
    );
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1),
        0 0 8px rgba(220, 53, 69, 0.35);
}
</style>
