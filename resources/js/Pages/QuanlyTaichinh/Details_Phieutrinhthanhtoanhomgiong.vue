<template lang="">
    <breadcrumb-vue v-if="!showTimeline" />

    <!-- Timeline View (this will replace the main content when active) -->
    <div class="text-center py-3" v-if="showTimeline">
        <div class="card timeline-card">
            <div
                class="card-header timeline-header d-flex justify-content-between align-items-center bg-light"
            >
                <h5 class="card-title mb-0">
                    <i class="fas fa-history me-2 text-primary"></i>
                    Lịch sử thanh toán
                </h5>
                <button
                    class="btn btn-sm btn-outline-secondary"
                    @click="toggleTimelineView"
                >
                    <i class="fas fa-times"></i>
                    <span class="ms-1">Đóng</span>
                </button>
            </div>
            <div class="card-body p-0">
                <PerfectScrollbar
                    class="timeline-scrollarea"
                    :options="{
                        wheelSpeed: 1,
                        wheelPropagation: false,
                        minScrollbarLength: 20,
                        suppressScrollX: true,
                    }"
                >
                    <div class="timeline-body p-4">
                        <div class="timeline-container">
                            <!-- Processing Stage -->
                            <div
                                class="timeline-item appear"
                                v-if="processingAction"
                            >
                                <div class="timeline-badge bg-primary">
                                    <i class="fas fa-spinner"></i>
                                </div>
                                <div class="timeline-content">
                                    <h5 class="timeline-title">
                                        <span class="badge bg-primary"
                                            >Đang xử lý</span
                                        >
                                    </h5>
                                    <p class="mb-1">
                                        <i class="far fa-calendar me-2"></i>
                                        <span class="fw-medium">{{
                                            formatDate(
                                                processingAction.created_at
                                            )
                                        }}</span>
                                    </p>
                                    <p class="mb-1">
                                        <i class="far fa-user me-2"></i>
                                        <span class="fw-medium">{{
                                            getUserName(
                                                processingAction.action_by
                                            )
                                        }}</span>
                                    </p>
                                    <div
                                        class="timeline-note"
                                        v-if="processingAction.note"
                                    >
                                        <i class="far fa-comment me-2"></i>
                                        <span>{{ processingAction.note }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Submitted Stage -->
                            <div
                                class="timeline-item appear"
                                v-if="submittedAction"
                            >
                                <div class="timeline-badge bg-info">
                                    <i class="fas fa-paper-plane"></i>
                                </div>
                                <div class="timeline-content">
                                    <h5 class="timeline-title">
                                        <span class="badge bg-info"
                                            >Đã nộp kế toán</span
                                        >
                                    </h5>
                                    <p class="mb-1">
                                        <i class="far fa-calendar me-2"></i>
                                        <span class="fw-medium">{{
                                            formatDate(
                                                submittedAction.created_at
                                            )
                                        }}</span>
                                    </p>
                                    <p class="mb-1">
                                        <i class="far fa-user me-2"></i>
                                        <span class="fw-medium">{{
                                            getUserName(
                                                submittedAction.action_by
                                            )
                                        }}</span>
                                    </p>
                                    <p
                                        class="mb-1"
                                        v-if="
                                            daysBetweenProcessingAndSubmitted !==
                                            null
                                        "
                                    >
                                        <i class="far fa-clock me-2"></i>
                                        <span
                                            >{{
                                                daysBetweenProcessingAndSubmitted
                                            }}
                                            ngày sau khi xử lý</span
                                        >
                                    </p>
                                    <div
                                        class="timeline-note"
                                        v-if="submittedAction.note"
                                    >
                                        <i class="far fa-comment me-2"></i>
                                        <span>{{ submittedAction.note }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Paid Stage -->
                            <div class="timeline-item appear" v-if="paidAction">
                                <div class="timeline-badge bg-success">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="timeline-content">
                                    <h5 class="timeline-title">
                                        <span class="badge bg-success"
                                            >Đã thanh toán</span
                                        >
                                    </h5>
                                    <p class="mb-1">
                                        <i class="far fa-calendar me-2"></i>
                                        <span class="fw-medium">
                                            {{
                                                formatDate(
                                                    document.payment_date ||
                                                        paidAction.created_at
                                                )
                                            }}
                                            <span
                                                v-if="document.payment_date"
                                                class="badge bg-light text-dark ms-2"
                                            ></span>
                                        </span>
                                    </p>
                                    <p
                                        class="mb-1"
                                        v-if="
                                            document.payment_date &&
                                            document.payment_date !==
                                                formatDate(
                                                    paidAction.created_at
                                                )
                                        "
                                    >
                                        <i
                                            class="fas fa-history me-2 text-muted"
                                        ></i>
                                        <span class="text-muted"
                                            >Ngày chuyển trạng thái:
                                            {{
                                                formatDate(
                                                    paidAction.created_at
                                                )
                                            }}</span
                                        >
                                    </p>
                                    <p class="mb-1">
                                        <i class="far fa-user me-2"></i>
                                        <span class="fw-medium">{{
                                            getUserName(paidAction.action_by)
                                        }}</span>
                                    </p>
                                    <p
                                        class="mb-1"
                                        v-if="
                                            daysBetweenSubmittedAndPaid !== null
                                        "
                                    >
                                        <i class="far fa-clock me-2"></i>
                                        <span>
                                            {{ daysBetweenSubmittedAndPaid }}
                                            ngày sau khi nộp kế toán
                                            <span
                                                v-if="document.payment_date"
                                                class="badge bg-light text-dark ms-2"
                                            ></span>
                                        </span>
                                    </p>
                                    <div
                                        class="timeline-note"
                                        v-if="paidAction.note"
                                    >
                                        <i class="far fa-comment me-2"></i>
                                        <span>{{ paidAction.note }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Cancelled Stage (if applicable) -->
                            <div
                                class="timeline-item appear"
                                v-if="cancelledAction"
                            >
                                <div class="timeline-badge bg-danger">
                                    <i class="fas fa-times-circle"></i>
                                </div>
                                <div class="timeline-content">
                                    <h5 class="timeline-title">
                                        <span class="badge bg-danger"
                                            >Đã hủy</span
                                        >
                                    </h5>
                                    <p class="mb-1">
                                        <i class="far fa-calendar me-2"></i>
                                        <span class="fw-medium">{{
                                            formatDate(
                                                cancelledAction.created_at
                                            )
                                        }}</span>
                                    </p>
                                    <p class="mb-1">
                                        <i class="far fa-user me-2"></i>
                                        <span class="fw-medium">{{
                                            getUserName(
                                                cancelledAction.action_by
                                            )
                                        }}</span>
                                    </p>
                                    <div
                                        class="timeline-note"
                                        v-if="cancelledAction.note"
                                    >
                                        <i class="far fa-comment me-2"></i>
                                        <span>{{ cancelledAction.note }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Empty state if no timeline data -->
                            <div
                                class="empty-timeline text-center py-0"
                                v-if="!processingAction"
                            >
                                <i
                                    class="fas fa-calendar-times fa-4x text-muted mb-3 opacity-50"
                                ></i>
                                <p class="lead text-muted">
                                    Không tìm thấy dữ liệu lịch sử
                                </p>
                                <button
                                    class="btn btn-outline-secondary mt-3"
                                    @click="toggleTimelineView"
                                >
                                    <i class="fas fa-arrow-left me-2"></i> Quay
                                    lại
                                </button>
                            </div>
                        </div>
                    </div>
                </PerfectScrollbar>
            </div>
        </div>
    </div>

    <!-- Original content (hidden when timeline is shown) -->
    <div class="card shadow" v-else>
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
                                    v-if="hasPermission('save')"
                                >
                                    <i class="bx bxs-save"></i>
                                    <span>Lưu</span>
                                </button>

                                <!-- ปุ่มนำส่งฝ่ายบัญชี (แสดงเฉพาะเมื่อสถานะเป็น 'processing') -->
                                <button
                                    v-if="
                                        document.status === 'processing' &&
                                        hasPermission(
                                            'nộp phiếu trình thanh toán'
                                        )
                                    "
                                    class="button-30-blue"
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
                                    v-if="
                                        document.status === 'submitted' &&
                                        hasPermission(
                                            'nộp phiếu trình thanh toán'
                                        )
                                    "
                                    type="button"
                                    class="button-30-yellow"
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
                                    v-if="
                                        document.status === 'submitted' &&
                                        hasPermission('đã thanh toán')
                                    "
                                    type="button"
                                    class="button-30-save"
                                    @click="confirmPaymentStatus"
                                >
                                    <i class="bx bx-check-square"></i>
                                    <span>Đã thanh toán</span>
                                </button>

                                <!-- ปุ่มยกเลิก (แสดงเฉพาะเมื่อสถานะเป็น 'submitted') -->
                                <button
                                    v-if="
                                        document.status === 'paid' &&
                                        hasPermission('cancel')
                                    "
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
                                    v-if="
                                        document.status !== 'paid' &&
                                        hasPermission(
                                            'delete_phieu_trinh_thanhtoan_dv'
                                        )
                                    "
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
                                    @click="toggleTimelineView"
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
                                            <select
                                                class="form-select"
                                                id="vuDauTu"
                                                v-model="
                                                    document.investment_project
                                                "
                                            >
                                                <option value="" disabled>
                                                    -- Chọn vụ đầu tư --
                                                </option>
                                                <option
                                                    v-for="project in investmentProjects"
                                                    :key="project.Ma_Vudautu"
                                                    :value="project.Ma_Vudautu"
                                                >
                                                    {{ project.Ten_Vudautu }}
                                                </option>
                                            </select>
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
                                            <select
                                                class="form-select"
                                                id="loaiThanhToan"
                                                v-model="document.payment_type"
                                            >
                                                <option value="" disabled>
                                                    -- Chọn loại thanh toán --
                                                </option>

                                                <option
                                                    value="Phiếu giao nhận hom giống"
                                                >
                                                    Phiếu giao nhận hom giống
                                                </option>
                                            </select>
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
                                            <div
                                                class="status-display"
                                                :class="
                                                    statusClass(document.status)
                                                "
                                            >
                                                <i
                                                    class="fas"
                                                    :class="
                                                        getStatusIcon(
                                                            document.status
                                                        )
                                                    "
                                                ></i>
                                                <span>{{
                                                    formatStatus(
                                                        document.status
                                                    )
                                                }}</span>
                                            </div>
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
                                    <!-- Ngày thanh toánoán -->
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="ngaythanhtoan"
                                                class="form-label"
                                            >
                                                Ngày thanh toán
                                                <span class="text-danger"
                                                    >*</span
                                                >
                                            </label>
                                            <input
                                                type="date"
                                                class="form-control"
                                                id="ngaythanhtoan"
                                                v-model="document.payment_date"
                                                ref="paymentDateInput"
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
                    <div class="card mt-3 payment-details-summary-card">
                        <div
                            class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center"
                        >
                            <h5 class="card-title mb-0 text-white">
                                <i class="fas fa-file-invoice-dollar me-2"></i>
                                Chi tiết hồ sơ thanh toán
                            </h5>
                            <div class="summary-stats d-flex gap-3">
                                <div class="stat-item">
                                    <small class="text-white-50"
                                        >Tổng số biên bản</small
                                    >
                                    <div class="fw-bold">
                                        {{ paymentDetails.length }}
                                    </div>
                                </div>
                                <div class="stat-item">
                                    <small class="text-white-50"
                                        >Tổng tiền</small
                                    >
                                    <div class="fw-bold">
                                        {{ formatCurrency(totalAmount) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body text-center py-5">
                            <div class="payment-summary-content">
                                <div class="summary-icon mb-3">
                                    <i
                                        class="fas fa-table fa-3x text-primary opacity-75"
                                    ></i>
                                </div>
                                <h6 class="mb-3">
                                    Quản lý chi tiết hồ sơ thanh toán
                                </h6>
                                <p class="text-muted mb-4">
                                    Xem và quản lý danh sách biên bản nghiệm thu
                                    dịch vụ trong phiếu trình thanh toán này
                                </p>
                                <button
                                    class="btn btn-primary btn-lg px-4 py-2"
                                    @click="openPaymentDetailsModal"
                                >
                                    <i class="fas fa-eye me-2"></i>
                                    Hiển thị chi tiết hồ sơ thanh toán
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Phiếu đề nghị thanh toán table -->
                    <!-- Phiếu đề nghị thanh toán - Summary Card -->
                    <div class="card mt-3 payment-requests-summary-card">
                        <div
                            class="card-header bg-gradient-success text-white d-flex justify-content-between align-items-center"
                        >
                            <h5 class="card-title mb-0 text-white">
                                <i class="fas fa-file-contract me-2"></i>
                                Phiếu đề nghị thanh toán
                            </h5>
                            <div class="summary-stats d-flex gap-3">
                                <div class="stat-item">
                                    <small class="text-white-50"
                                        >Tổng số phiếu</small
                                    >
                                    <div class="fw-bold">
                                        {{ paymentRequests.length }}
                                    </div>
                                </div>
                                <div class="stat-item">
                                    <small class="text-white-50"
                                        >Tổng tiền thanh toán</small
                                    >
                                    <div class="fw-bold">
                                        {{ formatCurrency(totalPaymentAmount) }}
                                    </div>
                                </div>
                                <div class="stat-item">
                                    <small class="text-white-50"
                                        >Tổng tiền còn lại</small
                                    >
                                    <div class="fw-bold">
                                        {{
                                            formatCurrency(totalRemainingAmount)
                                        }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body text-center py-5">
                            <div class="payment-summary-content">
                                <div class="summary-icon mb-3">
                                    <i
                                        class="fas fa-file-contract fa-3x text-success opacity-75"
                                    ></i>
                                </div>
                                <h6 class="mb-3">
                                    Quản lý phiếu đề nghị thanh toán
                                </h6>
                                <p class="text-muted mb-4">
                                    Xem và quản lý danh sách phiếu đề nghị thanh
                                    toán trong phiếu trình này
                                </p>
                                <button
                                    class="btn btn-success btn-lg px-4 py-2"
                                    @click="openPaymentRequestsModal"
                                >
                                    <i class="fas fa-eye me-2"></i>
                                    Hiển thị phiếu đề nghị thanh toán
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Note Section -->
                    <div class="col-md-12 mt-3">
                        <div class="card">
                            <div
                                class="card-header d-flex justify-content-between align-items-center"
                            >
                                <h6 class="mb-0">
                                    <i class="fas fa-sticky-note me-2"></i>Ghi
                                    chú
                                </h6>
                                <div v-if="!isEditingNote">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary"
                                        @click="isEditingNote = true"
                                        :disabled="isSavingNote"
                                    >
                                        <i class="fas fa-edit me-1"></i>Chỉnh
                                        sửa
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div v-if="!isEditingNote">
                                    <div
                                        class="note-content"
                                        v-html="
                                            document.notes
                                                ? document.notes.replace(
                                                      /\n/g,
                                                      '<br>'
                                                  )
                                                : '<em class=\'text-muted\'>Chưa có ghi chú nào</em>'
                                        "
                                    ></div>
                                </div>
                                <div v-else>
                                    <textarea
                                        v-model="noteText"
                                        class="form-control mb-3"
                                        rows="4"
                                        placeholder="Nhập ghi chú..."
                                        :disabled="isSavingNote"
                                    ></textarea>
                                    <div class="d-flex gap-2">
                                        <button
                                            type="button"
                                            class="btn btn-success"
                                            @click="saveNote"
                                            :disabled="isSavingNote"
                                        >
                                            <span
                                                v-if="isSavingNote"
                                                class="spinner-border spinner-border-sm me-1"
                                            ></span>
                                            <i
                                                v-else
                                                class="fas fa-save me-1"
                                            ></i>
                                            {{
                                                isSavingNote
                                                    ? "Đang lưu..."
                                                    : "Lưu"
                                            }}
                                        </button>
                                        <button
                                            type="button"
                                            class="btn btn-outline-secondary"
                                            @click="cancelEditNote"
                                            :disabled="isSavingNote"
                                        >
                                            <i class="fas fa-times me-1"></i>Hủy
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
    <!-- Payment Requests Modal -->
    <div
        class="modal fade"
        id="paymentRequestsModal"
        tabindex="-1"
        aria-labelledby="paymentRequestsModalLabel"
        aria-hidden="true"
        data-bs-backdrop="static"
        data-bs-keyboard="false"
    >
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5
                        class="modal-title text-white"
                        id="paymentRequestsModalLabel"
                    >
                        <i class="fas fa-file-contract me-2"></i>
                        Phiếu đề nghị thanh toán - {{ document.payment_code }}
                    </h5>
                    <div class="modal-header-stats d-flex gap-4 me-3">
                        <div class="stat-badge">
                            <i class="fas fa-list-ol me-1"></i>
                            <span
                                >{{
                                    filteredPaymentRequests.length
                                }}
                                phiếu</span
                            >
                        </div>
                        <div class="stat-badge">
                            <i class="fas fa-money-bill-wave me-1"></i>
                            <span>{{
                                formatCurrency(totalPaymentAmount)
                            }}</span>
                        </div>
                        <div class="stat-badge">
                            <i class="fas fa-calculator me-1"></i>
                            <span>{{
                                formatCurrency(totalRemainingAmount)
                            }}</span>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="btn-close btn-close-white"
                        @click="closePaymentRequestsModal"
                        aria-label="Close"
                    ></button>
                </div>

                <div class="modal-body p-0">
                    <!-- Enhanced Action Toolbar with Search -->
                    <div class="action-toolbar bg-light border-bottom p-3">
                        <div class="row align-items-center">
                            <!-- Search Section -->
                            <div class="col-md-4 mb-2 mb-md-0">
                                <div class="search-container">
                                    <div
                                        class="search-input-wrapper position-relative"
                                    >
                                        <input
                                            type="text"
                                            class="form-control search-input"
                                            v-model="paymentSearchQuery"
                                            @input="performPaymentSearch"
                                            placeholder="Tìm kiếm mã giải ngân, khách hàng..."
                                            autocomplete="off"
                                        />
                                        <i
                                            class="fas fa-search search-icon"
                                        ></i>
                                        <button
                                            v-if="paymentSearchQuery"
                                            @click="clearPaymentSearch"
                                            class="btn btn-sm btn-link search-clear"
                                            type="button"
                                        >
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <div
                                        v-if="paymentSearchQuery"
                                        class="search-info mt-1"
                                    >
                                        <small class="text-muted">
                                            Tìm thấy
                                            {{ filteredPaymentRequests.length }}
                                            kết quả cho "{{
                                                paymentSearchQuery
                                            }}"
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="col-md-4 mb-2 mb-md-0">
                                <div
                                    class="d-flex gap-2 justify-content-center"
                                >
                                    <button
                                        v-if="
                                            hasPermission(
                                                'add_phieu_trinh_thanhtoan_dv'
                                            )
                                        "
                                        class="btn btn-success btn-sm"
                                        title="Thêm phiếu đề nghị"
                                        @click="openAddPaymentRequestModal"
                                    >
                                        <i class="fas fa-plus me-1"></i>
                                        Thêm
                                    </button>
                                    <button
                                        v-if="
                                            hasPermission(
                                                'import_phieu_trinh_thanhtoan_dv'
                                            )
                                        "
                                        class="btn btn-primary btn-sm"
                                        title="Import dữ liệu"
                                        @click="openPaymentImportModal"
                                    >
                                        <i class="fas fa-file-import me-1"></i>
                                        Import
                                    </button>
                                    <button
                                        v-if="
                                            hasPermission(
                                                'export_phieu_trinh_thanhtoan_dv'
                                            )
                                        "
                                        class="btn btn-success btn-sm"
                                        title="Export Excel"
                                        @click="exportPaymentRequestsToExcel"
                                    >
                                        <i class="fas fa-file-excel me-1"></i>
                                        Excel
                                    </button>
                                </div>
                            </div>

                            <!-- Selection Actions -->
                            <div class="col-md-4">
                                <div class="d-flex gap-2 justify-content-end">
                                    <button
                                        class="btn btn-warning btn-sm"
                                        :disabled="
                                            selectedPaymentRequests.length === 0
                                        "
                                        @click="openUpdateHoldAmountModal"
                                        title="Điều chỉnh Amount cho các phiếu đã chọn"
                                    >
                                        <i class="fas fa-calculator me-1"></i>
                                        Điều chỉnh
                                        <span
                                            class="badge bg-light text-dark ms-1"
                                            v-if="
                                                selectedPaymentRequests.length >
                                                0
                                            "
                                        >
                                            {{ selectedPaymentRequests.length }}
                                        </span>
                                    </button>
                                    <button
                                        v-if="
                                            hasPermission(
                                                'edit_phieu_trinh_thanhtoan_dv'
                                            )
                                        "
                                        class="btn btn-warning btn-sm"
                                        title="Chỉnh sửa phiếu đã chọn"
                                        @click="editSelectedPaymentRecords"
                                        :disabled="
                                            selectedPaymentRequests.length === 0
                                        "
                                    >
                                        <i class="fas fa-edit me-1"></i>
                                        Sửa ({{
                                            selectedPaymentRequests.length
                                        }})
                                    </button>
                                    <button
                                        v-if="
                                            hasPermission(
                                                'delete_phieu_trinh_thanhtoan_dv'
                                            )
                                        "
                                        class="btn btn-danger btn-sm"
                                        title="Xóa phiếu đã chọn"
                                        @click="deleteSelectedPaymentRecords"
                                        :disabled="
                                            selectedPaymentRequests.length === 0
                                        "
                                    >
                                        <i class="fas fa-trash me-1"></i>
                                        Xóa ({{
                                            selectedPaymentRequests.length
                                        }})
                                    </button>
                                    <button
                                        class="btn btn-secondary btn-sm"
                                        title="Reset tất cả"
                                        @click="resetAllPaymentFiltersAndSearch"
                                    >
                                        <i class="fas fa-redo-alt me-1"></i>
                                        Reset
                                    </button>
                                    <button
                                        class="btn btn-info btn-sm"
                                        title="In phiếu đã chọn"
                                        @click="openPrintModal"
                                        :disabled="
                                            selectedPaymentRequests.length === 0
                                        "
                                    >
                                        <i class="fas fa-print me-1"></i>
                                        In ({{
                                            selectedPaymentRequests.length
                                        }})
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Advanced Filters Row -->
                        <div class="row mt-2" v-if="showPaymentAdvancedFilters">
                            <div class="col-12">
                                <div
                                    class="advanced-filters d-flex gap-2 flex-wrap"
                                >
                                    <!-- Quick Filter Buttons -->
                                    <button
                                        class="btn btn-outline-primary btn-sm"
                                        :class="{
                                            active:
                                                paymentQuickFilter ===
                                                'individual_customers',
                                        }"
                                        @click="
                                            applyPaymentQuickFilter(
                                                'individual_customers'
                                            )
                                        "
                                    >
                                        <i class="fas fa-user me-1"></i>
                                        Khách hàng cá nhân
                                    </button>
                                    <button
                                        class="btn btn-outline-warning btn-sm"
                                        :class="{
                                            active:
                                                paymentQuickFilter ===
                                                'corporate_customers',
                                        }"
                                        @click="
                                            applyPaymentQuickFilter(
                                                'corporate_customers'
                                            )
                                        "
                                    >
                                        <i class="fas fa-building me-1"></i>
                                        Khách hàng doanh nghiệp
                                    </button>
                                    <button
                                        class="btn btn-outline-info btn-sm"
                                        :class="{
                                            active:
                                                paymentQuickFilter ===
                                                'high_value',
                                        }"
                                        @click="
                                            applyPaymentQuickFilter(
                                                'high_value'
                                            )
                                        "
                                    >
                                        <i class="fas fa-dollar-sign me-1"></i>
                                        Giá trị cao (&gt; 1 tỷ)
                                    </button>
                                    <button
                                        class="btn btn-outline-secondary btn-sm"
                                        @click="clearPaymentQuickFilters"
                                    >
                                        <i class="fas fa-times me-1"></i>
                                        Xóa bộ lọc
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Toggle Advanced Filters -->
                        <div class="row mt-2">
                            <div class="col-12 text-center">
                                <button
                                    class="btn btn-link btn-sm text-muted"
                                    @click="
                                        showPaymentAdvancedFilters =
                                            !showPaymentAdvancedFilters
                                    "
                                >
                                    <i
                                        class="fas"
                                        :class="
                                            showPaymentAdvancedFilters
                                                ? 'fa-chevron-up'
                                                : 'fa-chevron-down'
                                        "
                                    ></i>
                                    {{
                                        showPaymentAdvancedFilters
                                            ? "Ẩn"
                                            : "Hiện"
                                    }}
                                    bộ lọc nâng cao
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Table Container -->
                    <div class="table-container-modal">
                        <div class="table-responsive">
                            <table
                                class="table table-bordered table-hover align-middle mb-0"
                                v-resizable-columns="{
                                    minWidth: 80,
                                    saveState: true,
                                    id: 'payment-requests-table-modal',
                                    adjustTableWidth: false,
                                }"
                                style="table-layout: fixed"
                            >
                                <thead
                                    class="table-dark text-center sticky-top"
                                >
                                    <tr>
                                        <th style="width: 50px">
                                            <div class="form-check">
                                                <input
                                                    type="checkbox"
                                                    class="form-check-input"
                                                    :checked="
                                                        isAllPaymentRequestsSelected
                                                    "
                                                    @change="
                                                        toggleSelectAllPaymentRequests
                                                    "
                                                />
                                            </div>
                                        </th>
                                        <th style="width: 170px">
                                            Mã giải ngân
                                        </th>
                                        <th style="width: 140px">Vụ đầu tư</th>
                                        <th style="width: 190px">
                                            Loại thanh toán
                                        </th>
                                        <th style="width: 180px">KH Cá nhân</th>
                                        <th style="width: 140px">
                                            Mã KH cá nhân
                                        </th>
                                        <th style="width: 180px">
                                            KH Doanh nghiệp
                                        </th>
                                        <th style="width: 140px">
                                            Mã KH doanh nghiệp
                                        </th>
                                        <th
                                            class="text-center"
                                            style="width: 120px"
                                        >
                                            Thực nhận
                                        </th>
                                        <th style="width: 140px">Tổng tiền</th>
                                        <th style="width: 140px">
                                            Tổng tiền tạm giữ
                                        </th>
                                        <th style="width: 140px">
                                            Tổng tiền khấu trừ
                                        </th>
                                        <th style="width: 140px">
                                            Tổng tiền lãi suất
                                        </th>
                                        <th style="width: 140px">
                                            Tổng tiền còn lại
                                        </th>
                                        <th style="width: 120px">
                                            Ngày thanh toán
                                        </th>
                                        <th style="width: 120px">
                                            Số tờ trình
                                        </th>
                                        <th style="width: 80px">Đợt TT</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-if="
                                            paginatedPaymentRequests.data
                                                .length === 0
                                        "
                                    >
                                        <td
                                            colspan="16"
                                            class="text-center py-5"
                                        >
                                            <div class="empty-state">
                                                <i
                                                    class="fas fa-search fa-3x text-muted mb-3"
                                                    v-if="paymentSearchQuery"
                                                ></i>
                                                <i
                                                    class="fas fa-file-contract fa-3x text-muted mb-3"
                                                    v-else
                                                ></i>
                                                <h6 class="text-muted">
                                                    {{
                                                        paymentSearchQuery
                                                            ? "Không tìm thấy kết quả"
                                                            : "Không có dữ liệu"
                                                    }}
                                                </h6>
                                                <p class="text-muted mb-0">
                                                    {{
                                                        paymentSearchQuery
                                                            ? `Không có kết quả nào khớp với "${paymentSearchQuery}"`
                                                            : "Chưa có phiếu đề nghị thanh toán nào trong phiếu trình này"
                                                    }}
                                                </p>
                                                <button
                                                    v-if="paymentSearchQuery"
                                                    @click="clearPaymentSearch"
                                                    class="btn btn-outline-success mt-2"
                                                >
                                                    <i
                                                        class="fas fa-times me-1"
                                                    ></i>
                                                    Xóa tìm kiếm
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr
                                        v-for="(
                                            item, index
                                        ) in paginatedPaymentRequests.data"
                                        :key="index"
                                        :class="{
                                            'table-warning':
                                                selectedPaymentRequests.includes(
                                                    item.disbursement_code
                                                ),
                                            'search-highlight':
                                                isPaymentHighlighted(item),
                                        }"
                                    >
                                        <td class="text-center">
                                            <div class="form-check">
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
                                            </div>
                                        </td>
                                        <td>
                                            <a
                                                :href="`/Details_Phieudenghithanhtoandichvu/${item.disbursement_code}`"
                                                target="_blank"
                                                class="fw-medium text-primary text-decoration-none"
                                                style="cursor: pointer"
                                                :title="`Xem chi tiết ${item.disbursement_code}`"
                                                v-html="
                                                    highlightPaymentSearchTerm(
                                                        item.disbursement_code
                                                    )
                                                "
                                            ></a>
                                        </td>
                                        <td
                                            v-html="
                                                highlightPaymentSearchTerm(
                                                    item.investment_project_name ||
                                                        'N/A'
                                                )
                                            "
                                        ></td>
                                        <td>
                                            <span class="badge bg-info">{{
                                                item.payment_type
                                            }}</span>
                                        </td>
                                        <td
                                            v-html="
                                                highlightPaymentSearchTerm(
                                                    item.individual_customer ||
                                                        'N/A'
                                                )
                                            "
                                        ></td>
                                        <td>
                                            {{
                                                item.individual_customer_code ||
                                                "N/A"
                                            }}
                                        </td>
                                        <td
                                            v-html="
                                                highlightPaymentSearchTerm(
                                                    item.corporate_customer ||
                                                        'N/A'
                                                )
                                            "
                                        ></td>
                                        <td>
                                            {{
                                                item.corporate_customer_code ||
                                                "N/A"
                                            }}
                                        </td>
                                        <!-- เพิ่ม Column Thực nhận -->
                                        <td class="text-end">
                                            <span class="text-info fw-bold">
                                                {{ item.actual_received }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-bold text-success">
                                                {{
                                                    formatCurrency(
                                                        item.total_amount
                                                    )
                                                }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <span
                                                class="fw-medium text-warning"
                                            >
                                                {{
                                                    formatCurrency(
                                                        item.total_hold_amount
                                                    )
                                                }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-medium text-danger">
                                                {{
                                                    formatCurrency(
                                                        item.total_deduction
                                                    )
                                                }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-medium text-info">
                                                {{
                                                    formatCurrency(
                                                        item.total_interest
                                                    )
                                                }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-bold text-primary">
                                                {{
                                                    formatCurrency(
                                                        item.total_remaining
                                                    )
                                                }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary">
                                                {{
                                                    formatDate(
                                                        item.payment_date
                                                    )
                                                }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-warning">
                                                {{ item.proposal_number }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary">{{
                                                item.payment_installment
                                            }}</span>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot
                                    v-if="paymentRequests.length > 0"
                                    class="table-light"
                                >
                                    <tr>
                                        <td
                                            colspan="9"
                                            class="text-end fw-bold"
                                        >
                                            Tổng cộng ({{
                                                filteredPaymentRequests.length
                                            }}
                                            phiếu):
                                        </td>
                                        <td
                                            class="text-end fw-bold text-success"
                                        >
                                            {{
                                                formatCurrency(
                                                    totalPaymentAmount
                                                )
                                            }}
                                        </td>
                                        <td
                                            class="text-end fw-bold text-warning"
                                        >
                                            {{
                                                formatCurrency(totalHoldAmount)
                                            }}
                                        </td>
                                        <td
                                            class="text-end fw-bold text-danger"
                                        >
                                            {{
                                                formatCurrency(
                                                    totalDeductionAmount
                                                )
                                            }}
                                        </td>
                                        <td class="text-end fw-bold text-info">
                                            {{
                                                formatCurrency(
                                                    totalInterestAmount
                                                )
                                            }}
                                        </td>
                                        <td
                                            class="text-end fw-bold text-primary"
                                        >
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
                        </div>
                    </div>
                </div>

                <!-- Modal Footer with Pagination -->
                <div class="modal-footer bg-light border-top">
                    <div class="row w-100 align-items-center">
                        <div class="col-md-6">
                            <div class="pagination-info text-muted">
                                <small>
                                    Hiển thị
                                    {{ paginatedPaymentRequests.from }}-{{
                                        paginatedPaymentRequests.to
                                    }}
                                    trên tổng số
                                    {{ filteredPaymentRequests.length }} phiếu
                                    {{
                                        paymentSearchQuery
                                            ? `(đã lọc từ ${paymentRequests.length} phiếu)`
                                            : ""
                                    }}
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex justify-content-end">
                            <Bootstrap5Pagination
                                :data="paginatedPaymentRequests"
                                @pagination-change-page="paymentPageChanged"
                                :limit="3"
                                :classes="paginationClasses"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Details Modal -->

    <div
        class="modal fade"
        id="paymentDetailsModal"
        tabindex="-1"
        aria-labelledby="paymentDetailsModalLabel"
        aria-hidden="true"
        data-bs-backdrop="static"
        data-bs-keyboard="false"
    >
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5
                        class="modal-title text-white"
                        id="paymentDetailsModalLabel"
                    >
                        <i class="fas fa-file-invoice-dollar me-2"></i>
                        Chi tiết hồ sơ thanh toán - {{ document.payment_code }}
                    </h5>
                    <div class="modal-header-stats d-flex gap-4 me-3">
                        <div class="stat-badge">
                            <i class="fas fa-list-ol me-1"></i>
                            <span
                                >{{ filteredPaymentDetails.length }} bản
                                ghi</span
                            >
                        </div>
                        <div class="stat-badge">
                            <i class="fas fa-money-bill-wave me-1"></i>
                            <span>{{ formatCurrency(totalAmount) }}</span>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="btn-close btn-close-white"
                        @click="closePaymentDetailsModal"
                        aria-label="Close"
                    ></button>
                </div>

                <div class="modal-body p-0">
                    <!-- Enhanced Action Toolbar with Search -->
                    <div class="action-toolbar bg-light border-bottom p-3">
                        <div class="row align-items-center">
                            <!-- Search Section -->
                            <div class="col-md-4 mb-2 mb-md-0">
                                <div class="search-container">
                                    <div
                                        class="search-input-wrapper position-relative"
                                    >
                                        <input
                                            type="text"
                                            class="form-control search-input"
                                            v-model="modalSearchQuery"
                                            @input="performModalSearch"
                                            placeholder="Tìm kiếm mã số phiếu..."
                                            autocomplete="off"
                                        />
                                        <i
                                            class="fas fa-search search-icon"
                                        ></i>
                                        <button
                                            v-if="modalSearchQuery"
                                            @click="clearModalSearch"
                                            class="btn btn-sm btn-link search-clear"
                                            type="button"
                                        >
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <div
                                        v-if="modalSearchQuery"
                                        class="search-info mt-1"
                                    >
                                        <small class="text-muted">
                                            Tìm thấy
                                            {{ filteredPaymentDetails.length }}
                                            kết quả cho "{{ modalSearchQuery }}"
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="col-md-4 mb-2 mb-md-0">
                                <div
                                    class="d-flex gap-2 justify-content-center"
                                >
                                    <button
                                        v-if="
                                            hasPermission(
                                                'add_phieu_trinh_thanhtoan_dv'
                                            )
                                        "
                                        class="btn btn-primary btn-sm"
                                        title="Thêm phiếu đề nghị"
                                        @click="openAddPaymentRequestModal"
                                    >
                                        <i class="fa-solid fa-list-check"></i>
                                        Tạo PDNTT
                                    </button>
                                    <button
                                        v-if="
                                            hasPermission(
                                                'add_phieu_trinh_thanhtoan_dv'
                                            )
                                        "
                                        class="btn btn-success btn-sm"
                                        title="Thêm biên bản mới"
                                        @click="openAddReceiptModal"
                                    >
                                        <i class="fas fa-plus me-1"></i>
                                        Thêm
                                    </button>
                                    <button
                                        v-if="
                                            hasPermission(
                                                'import_phieu_trinh_thanhtoan_dv'
                                            )
                                        "
                                        class="btn btn-primary btn-sm"
                                        title="Import dữ liệu"
                                        @click="openImportModal"
                                    >
                                        <i class="fas fa-file-import me-1"></i>
                                        Import
                                    </button>
                                    <button
                                        v-if="
                                            hasPermission(
                                                'export_phieu_trinh_thanhtoan_dv'
                                            )
                                        "
                                        class="btn btn-success btn-sm"
                                        title="Export Excel"
                                        @click="exportToExcel"
                                    >
                                        <i class="fas fa-file-excel me-1"></i>
                                        Excel
                                    </button>
                                </div>
                            </div>

                            <!-- Selection Actions -->
                            <div class="col-md-4">
                                <div class="d-flex gap-2 justify-content-end">
                                    <button
                                        v-if="
                                            hasPermission(
                                                'edit_phieu_trinh_thanhtoan_dv'
                                            )
                                        "
                                        class="btn btn-warning btn-sm"
                                        title="Chỉnh sửa bản ghi đã chọn"
                                        @click="editSelectedRecords"
                                        :disabled="selectedRecords.length === 0"
                                    >
                                        <i class="fas fa-edit me-1"></i>
                                        Sửa ({{ selectedRecords.length }})
                                    </button>
                                    <button
                                        v-if="
                                            hasPermission(
                                                'delete_phieu_trinh_thanhtoan_dv'
                                            )
                                        "
                                        class="btn btn-danger btn-sm"
                                        title="Xóa bản ghi đã chọn"
                                        @click="deleteSelectedRecords"
                                        :disabled="selectedRecords.length === 0"
                                    >
                                        <i class="fas fa-trash me-1"></i>
                                        Xóa ({{ selectedRecords.length }})
                                    </button>
                                    <button
                                        class="btn btn-secondary btn-sm"
                                        title="Reset tất cả"
                                        @click="resetAllFiltersAndSearch"
                                    >
                                        <i class="fas fa-redo-alt me-1"></i>
                                        Reset
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Advanced Filters Row (optional) -->
                        <div class="row mt-2" v-if="showAdvancedFilters">
                            <div class="col-12">
                                <div
                                    class="advanced-filters d-flex gap-2 flex-wrap"
                                >
                                    <!-- Quick Filter Buttons -->
                                    <button
                                        class="btn btn-outline-primary btn-sm"
                                        :class="{
                                            active:
                                                quickFilter ===
                                                'has_disbursement',
                                        }"
                                        @click="
                                            applyQuickFilter('has_disbursement')
                                        "
                                    >
                                        <i class="fas fa-check-circle me-1"></i>
                                        Có mã giải ngân
                                    </button>
                                    <button
                                        class="btn btn-outline-warning btn-sm"
                                        :class="{
                                            active:
                                                quickFilter ===
                                                'no_disbursement',
                                        }"
                                        @click="
                                            applyQuickFilter('no_disbursement')
                                        "
                                    >
                                        <i
                                            class="fas fa-exclamation-circle me-1"
                                        ></i>
                                        Chưa có mã giải ngân
                                    </button>
                                    <button
                                        class="btn btn-outline-info btn-sm"
                                        :class="{
                                            active:
                                                quickFilter === 'high_amount',
                                        }"
                                        @click="applyQuickFilter('high_amount')"
                                    >
                                        <i class="fas fa-dollar-sign me-1"></i>
                                        Giá trị cao (&gt; 1 tỷ)
                                    </button>
                                    <button
                                        class="btn btn-outline-secondary btn-sm"
                                        @click="clearQuickFilters"
                                    >
                                        <i class="fas fa-times me-1"></i>
                                        Xóa bộ lọc
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Toggle Advanced Filters -->
                        <div class="row mt-2">
                            <div class="col-12 text-center">
                                <button
                                    class="btn btn-link btn-sm text-muted"
                                    @click="
                                        showAdvancedFilters =
                                            !showAdvancedFilters
                                    "
                                >
                                    <i
                                        class="fas"
                                        :class="
                                            showAdvancedFilters
                                                ? 'fa-chevron-up'
                                                : 'fa-chevron-down'
                                        "
                                    ></i>
                                    {{ showAdvancedFilters ? "Ẩn" : "Hiện" }} bộ
                                    lọc nâng cao
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Table Container -->
                    <div class="table-container-modal">
                        <div class="table-responsive">
                            <table
                                class="table table-bordered table-hover align-middle mb-0"
                                v-resizable-columns="{
                                    minWidth: 80,
                                    saveState: true,
                                    id: 'phieutrinhthanhtoan-details-table-modal',
                                    adjustTableWidth: false,
                                }"
                                style="table-layout: fixed"
                            >
                                <thead
                                    class="table-dark text-center sticky-top"
                                >
                                    <tr>
                                        <th style="width: 50px">
                                            <div class="form-check">
                                                <input
                                                    type="checkbox"
                                                    class="form-check-input"
                                                    :checked="isAllSelected"
                                                    @change="toggleSelectAll"
                                                />
                                            </div>
                                        </th>
                                        <th style="width: 170px">
                                            Mã số phiếu
                                        </th>
                                        <th style="width: 130px">Trạm</th>

                                        <th style="width: 140px">Vụ đầu tư</th>
                                        <th style="width: 180px">Tên phiếu</th>
                                        <th style="width: 180px">
                                            Hợp đồng đầu tư mía bên giao
                                        </th>
                                        <th style="width: 160px">
                                            Tổng thực nhận
                                        </th>

                                        <th style="width: 160px">
                                            Mã đề nghị giải ngân
                                        </th>
                                        <th style="width: 80px">Đợt TT</th>
                                        <th style="width: 140px">Số tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-if="
                                            filteredPaymentDetails.length === 0
                                        "
                                    >
                                        <td
                                            colspan="8"
                                            class="text-center py-5"
                                        >
                                            <div class="empty-state">
                                                <i
                                                    class="fas fa-search fa-3x text-muted mb-3"
                                                    v-if="modalSearchQuery"
                                                ></i>
                                                <i
                                                    class="fas fa-inbox fa-3x text-muted mb-3"
                                                    v-else
                                                ></i>
                                                <h6 class="text-muted">
                                                    {{
                                                        modalSearchQuery
                                                            ? "Không tìm thấy kết quả"
                                                            : "Không có dữ liệu"
                                                    }}
                                                </h6>
                                                <p class="text-muted mb-0">
                                                    {{
                                                        modalSearchQuery
                                                            ? `Không có kết quả nào khớp với "${modalSearchQuery}"`
                                                            : "Chưa có biên bản nghiệm thu hom giống nào trong phiếu trình này"
                                                    }}
                                                </p>
                                                <button
                                                    v-if="modalSearchQuery"
                                                    @click="clearModalSearch"
                                                    class="btn btn-outline-primary mt-2"
                                                >
                                                    <i
                                                        class="fas fa-times me-1"
                                                    ></i>
                                                    Xóa tìm kiếm
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr
                                        v-for="(
                                            detail, index
                                        ) in paginatedPaymentDetails.data"
                                        :key="detail.document_code"
                                        :class="{
                                            'table-warning':
                                                selectedRecords.includes(
                                                    detail.document_code
                                                ),
                                            'search-highlight':
                                                isHighlighted(detail),
                                            'table-success':
                                                detail.disbursement_code,
                                        }"
                                    >
                                        <td class="text-center">
                                            <div class="form-check">
                                                <input
                                                    type="checkbox"
                                                    :value="
                                                        detail.document_code
                                                    "
                                                    v-model="selectedRecords"
                                                    class="form-check-input"
                                                />
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <a
                                                :href="`/Details_BienbanNghiemthuHomgiong/${detail.document_code}`"
                                                target="_blank"
                                                class="fw-medium text-primary text-decoration-none"
                                                style="cursor: pointer"
                                                :title="`Xem chi tiết ${detail.document_code}`"
                                                v-html="
                                                    highlightSearchTerm(
                                                        detail.document_code
                                                    )
                                                "
                                            ></a>
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="badge bg-secondary"
                                                :title="detail.tram"
                                                v-html="
                                                    highlightSearchTerm(
                                                        detail.tram || 'N/A'
                                                    )
                                                "
                                            ></span>
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="text-truncate d-inline-block"
                                                style="max-width: 100px"
                                                :title="
                                                    detail.investment_project
                                                "
                                                v-html="
                                                    highlightSearchTerm(
                                                        detail.investment_project ||
                                                            'N/A'
                                                    )
                                                "
                                            ></span>
                                        </td>
                                        <td>
                                            <div
                                                class="text-truncate"
                                                style="max-width: 200px"
                                                :title="detail.title"
                                                v-html="
                                                    highlightSearchTerm(
                                                        detail.title || 'N/A'
                                                    )
                                                "
                                            ></div>
                                        </td>
                                        <td>
                                            <div
                                                class="text-truncate"
                                                style="max-width: 200px"
                                                :title="
                                                    detail.hop_dong_dau_tu_mia_ben_giao_hom
                                                "
                                                v-html="
                                                    highlightSearchTerm(
                                                        detail.hop_dong_dau_tu_mia_ben_giao_hom ||
                                                            'N/A'
                                                    )
                                                "
                                            ></div>
                                        </td>
                                        <td class="text-end">
                                            <span
                                                class="text-success fw-medium"
                                            >
                                                {{ detail.tong_thuc_nhan || 0 }}
                                            </span>
                                        </td>
                                        <!-- เพิ่ม Column: Mã đề nghị giải ngân -->
                                        <td class="text-center">
                                            <span
                                                v-if="detail.disbursement_code"
                                                class="badge bg-success"
                                                :title="
                                                    detail.disbursement_code
                                                "
                                                v-html="
                                                    highlightSearchTerm(
                                                        detail.disbursement_code
                                                    )
                                                "
                                            ></span>
                                            <span
                                                v-else
                                                class="badge bg-warning text-dark"
                                                title="Chưa có mã giải ngân"
                                            >
                                                <i
                                                    class="fas fa-exclamation-triangle me-1"
                                                ></i>
                                                Chưa có
                                            </span>
                                        </td>
                                        <!-- เพิ่ม Column: Đợt TT -->
                                        <td class="text-center">
                                            <span
                                                class="badge bg-info"
                                                :title="`Đợt thanh toán ${detail.installment}`"
                                            >
                                                <i
                                                    class="fas fa-layer-group me-1"
                                                ></i>
                                                {{ detail.installment || 1 }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-primary fw-bold">
                                                {{
                                                    formatCurrency(
                                                        detail.amount || 0
                                                    )
                                                }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot
                                    v-if="paymentDetails.length > 0"
                                    class="table-light"
                                >
                                    <tr>
                                        <th colspan="6" class="text-end">
                                            Tổng cộng:
                                        </th>
                                        <th class="text-end">
                                            <span class="text-success fw-bold">
                                                {{
                                                    filteredPaymentDetails.reduce(
                                                        (sum, item) =>
                                                            sum +
                                                            parseFloat(
                                                                item.tong_thuc_nhan ||
                                                                    0
                                                            ),
                                                        0
                                                    )
                                                }}
                                            </span>
                                        </th>
                                        <th></th>
                                        <th></th>
                                        <th class="text-end">
                                            <span class="text-primary fw-bold">
                                                {{
                                                    formatCurrency(
                                                        totalFilteredAmount
                                                    )
                                                }}
                                            </span>
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer with Pagination -->
                <div class="modal-footer bg-light border-top">
                    <div class="row w-100 align-items-center">
                        <div class="col-md-6">
                            <div class="pagination-info text-muted">
                                <small>
                                    Hiển thị
                                    {{ paginatedPaymentDetails.from }}-{{
                                        paginatedPaymentDetails.to
                                    }}
                                    trên tổng số
                                    {{ filteredPaymentDetails.length }} bản ghi
                                    {{
                                        modalSearchQuery
                                            ? `(đã lọc từ ${paymentDetails.length} bản ghi)`
                                            : ""
                                    }}
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex justify-content-end">
                            <Bootstrap5Pagination
                                :data="paginatedPaymentDetails"
                                @pagination-change-page="pageChanged"
                                :limit="3"
                                :classes="paginationClasses"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Hold Amount Modal -->
    <div
        class="modal fade"
        id="updateHoldAmountModal"
        tabindex="-1"
        aria-labelledby="updateHoldAmountModalLabel"
        aria-hidden="true"
        data-bs-backdrop="static"
        data-bs-keyboard="false"
    >
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title" id="updateHoldAmountModalLabel">
                        <i class="fas fa-calculator me-2"></i>
                        Điều chỉnh Amount - Chi phí thu hoạch
                    </h5>
                    <button
                        type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>
                </div>
                <div class="modal-body">
                    <!-- Selected Items Summary -->
                    <div class="alert alert-info mb-4">
                        <div
                            class="d-flex justify-content-between align-items-center"
                        >
                            <div>
                                <h6 class="alert-heading mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Thông tin các phiếu đã chọn
                                </h6>
                            </div>
                            <div class="d-flex gap-4">
                                <div>
                                    <span class="badge bg-primary ms-1">{{
                                        selectedPaymentRequests.length
                                    }}</span>
                                </div>
                                <!-- <div>
                                    <strong>Tổng thực nhận:</strong>
                                    <span class="fw-bold text-success ms-1">{{
                                        totalSelectedThucNhan
                                    }}</span>
                                    <small class="text-muted">tấn</small>
                                </div> -->
                            </div>
                        </div>
                    </div>

                    <!-- Harvest Cost Input Form -->
                    <form @submit.prevent="updateHoldAmount">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="donGiaChatMia" class="form-label">
                                    <i class="fas fa-cut me-1"></i>
                                    Đơn giá chặt mía (Kip/tấn)
                                </label>
                                <input
                                    type="text"
                                    id="donGiaChatMia"
                                    v-model="holdAmountForm.don_gia_chat_mia"
                                    class="form-control"
                                    placeholder="Nhập đơn giá chặt mía"
                                    @input="
                                        formatCurrencyInput(
                                            $event,
                                            'don_gia_chat_mia'
                                        )
                                    "
                                    @focus="
                                        removeFormatting(
                                            $event,
                                            'don_gia_chat_mia'
                                        )
                                    "
                                    @blur="
                                        addFormatting(
                                            $event,
                                            'don_gia_chat_mia'
                                        )
                                    "
                                />
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Nhập số tiền không bao gồm dấu phẩy
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label for="donGiaGapMia" class="form-label">
                                    <i class="fas fa-tractor me-1"></i>
                                    Đơn giá gặp mía (Kip/tấn)
                                </label>
                                <input
                                    type="text"
                                    id="donGiaGapMia"
                                    v-model="holdAmountForm.don_gia_gap_mia"
                                    class="form-control"
                                    placeholder="Nhập đơn giá gặp mía"
                                    @input="
                                        formatCurrencyInput(
                                            $event,
                                            'don_gia_gap_mia'
                                        )
                                    "
                                    @focus="
                                        removeFormatting(
                                            $event,
                                            'don_gia_gap_mia'
                                        )
                                    "
                                    @blur="
                                        addFormatting($event, 'don_gia_gap_mia')
                                    "
                                />
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Nhập số tiền không bao gồm dấu phẩy
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label
                                    for="donGiaVanChuyenMia"
                                    class="form-label"
                                >
                                    <i class="fas fa-truck me-1"></i>
                                    Đơn giá vận chuyển mía (Kip/tấn)
                                </label>
                                <input
                                    type="text"
                                    id="donGiaVanChuyenMia"
                                    v-model="
                                        holdAmountForm.don_gia_van_chuyen_mia
                                    "
                                    class="form-control"
                                    placeholder="Nhập đơn giá vận chuyển"
                                    @input="
                                        formatCurrencyInput(
                                            $event,
                                            'don_gia_van_chuyen_mia'
                                        )
                                    "
                                    @focus="
                                        removeFormatting(
                                            $event,
                                            'don_gia_van_chuyen_mia'
                                        )
                                    "
                                    @blur="
                                        addFormatting(
                                            $event,
                                            'don_gia_van_chuyen_mia'
                                        )
                                    "
                                />
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Nhập số tiền không bao gồm dấu phẩy
                                </div>
                            </div>
                        </div>

                        <!-- Calculation Summary -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i
                                                class="fas fa-calculator me-2"
                                            ></i>
                                            Tính toán chi phí thu hoạch
                                        </h6>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div
                                                    class="d-flex justify-content-between"
                                                >
                                                    <span
                                                        >Tổng chi phí thu
                                                        hoạch/tấn:</span
                                                    >
                                                    <strong class="text-danger">
                                                        {{
                                                            formatCurrency(
                                                                totalHarvestCostPerTon
                                                            )
                                                        }}
                                                        /tấn
                                                    </strong>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div
                                                    class="d-flex justify-content-between"
                                                >
                                                    <span
                                                        >Tổng tiền tạm giữ dự
                                                        kiến:</span
                                                    >
                                                    <strong
                                                        class="text-warning"
                                                    >
                                                        {{
                                                            formatCurrency(
                                                                estimatedTotalHoldAmount
                                                            )
                                                        }}
                                                    </strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Selected Payment Requests Preview -->
                        <div class="mt-4">
                            <h6>
                                <i class="fas fa-list me-2"></i>
                                Danh sách phiếu đề nghị sẽ được cập nhật:
                            </h6>
                            <div
                                class="table-responsive"
                                style="max-height: 300px"
                            >
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Mã giải ngân</th>
                                            <th class="text-end">
                                                Thực nhận (tấn)
                                            </th>
                                            <th class="text-end">
                                                Tạm giữ hiện tại
                                            </th>
                                            <th class="text-end">
                                                Tạm giữ mới
                                            </th>
                                            <th class="text-end">
                                                Còn lại mới
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="item in selectedPaymentRequestsData"
                                            :key="item.disbursement_code"
                                        >
                                            <td>
                                                {{ item.disbursement_code }}
                                            </td>
                                            <td class="text-end">
                                                {{ item.actual_received }}
                                            </td>
                                            <td class="text-end">
                                                {{
                                                    formatCurrency(
                                                        item.total_hold_amount
                                                    )
                                                }}
                                            </td>
                                            <td
                                                class="text-end text-warning fw-bold"
                                            >
                                                {{
                                                    formatCurrency(
                                                        calculateNewHoldAmount(
                                                            item.actual_received
                                                        )
                                                    )
                                                }}
                                            </td>
                                            <td
                                                class="text-end text-success fw-bold"
                                            >
                                                {{
                                                    formatCurrency(
                                                        calculateNewRemainingAmount(
                                                            item
                                                        )
                                                    )
                                                }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal"
                    >
                        <i class="fas fa-times me-1"></i>
                        Hủy
                    </button>
                    <button
                        type="button"
                        class="btn btn-warning"
                        @click="updateHoldAmount"
                        :disabled="updateHoldAmountLoading || !isFormValid"
                        ref="updateButton"
                    >
                        <span
                            v-if="updateHoldAmountLoading"
                            class="spinner-border spinner-border-sm me-2"
                        ></span>
                        <i v-else class="fas fa-save me-1"></i>
                        {{
                            updateHoldAmountLoading
                                ? "Đang cập nhật..."
                                : "Cập nhật Amount"
                        }}
                    </button>
                </div>
            </div>
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
                    <h5 class="modal-title text-white" id="importModalLabel">
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
                    <div class="alert alert-info mb-3 text-info">
                        <i class="fas fa-info-circle me-2 text-white"></i>
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
        data-bs-backdrop="static"
        data-bs-keyboard="false"
    >
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5
                        class="modal-title text-white"
                        id="paymentEditModalLabel"
                    >
                        <i class="fas fa-edit me-2"></i>
                        Chỉnh sửa phiếu đề nghị thanh toán
                    </h5>
                    <button
                        type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>
                </div>

                <div
                    class="modal-body"
                    style="max-height: 70vh; overflow-y: auto"
                >
                    <!-- Form Container -->
                    <form @submit.prevent="updatePaymentRecords">
                        <!-- Alert Section -->
                        <div
                            class="alert text-info mb-3"
                            :class="{
                                'alert-info text-white':
                                    selectedPaymentRequests.length > 1,
                                'alert-info text-dark':
                                    selectedPaymentRequests.length === 1,
                            }"
                        >
                            <i
                                class="fas me-2"
                                :class="{
                                    'fa-info-circle':
                                        selectedPaymentRequests.length > 1,
                                    'fa-edit':
                                        selectedPaymentRequests.length === 1,
                                }"
                            ></i>
                            <span v-if="selectedPaymentRequests.length === 1">
                                Đang chỉnh sửa phiếu đề nghị:
                                <strong>{{
                                    selectedPaymentRequests[0]
                                }}</strong>
                            </span>
                            <span v-else>
                                Đang chỉnh sửa
                                <strong>{{
                                    selectedPaymentRequests.length
                                }}</strong>
                                phiếu đề nghị
                                <div class="mt-2 small">
                                    <strong>Lưu ý:</strong> Chỉ có thể chỉnh sửa
                                    thông tin khách hàng khi chọn nhiều phiếu
                                </div>
                            </span>
                        </div>

                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <!-- Mã giải ngân -->
                                <div class="mb-3">
                                    <label
                                        for="editDisbursementCode"
                                        class="form-label"
                                    >
                                        <i
                                            class="fas fa-barcode me-1 text-warning"
                                        ></i>
                                        Mã giải ngân
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="editDisbursementCode"
                                        v-model="paymentEditForm.ma_giai_ngan"
                                        placeholder="Nhập mã giải ngân"
                                        :disabled="
                                            selectedPaymentRequests.length > 1
                                        "
                                    />
                                    <div
                                        class="form-text text-warning"
                                        v-if="
                                            selectedPaymentRequests.length > 1
                                        "
                                    >
                                        <i
                                            class="fas fa-exclamation-triangle me-1"
                                        ></i>
                                        Không thể chỉnh sửa mã giải ngân khi
                                        chọn nhiều phiếu
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <!-- Spacer for alignment -->
                                <div class="mb-3">
                                    <div class="h-100 d-flex align-items-end">
                                        <div
                                            class="alert alert-light w-100 mb-0"
                                        >
                                            <i
                                                class="fas fa-lightbulb text-warning me-2"
                                            ></i>
                                            <small
                                                >Thông tin khách hàng có thể
                                                chỉnh sửa cho tất cả phiếu đã
                                                chọn</small
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Left Column - Individual Customer -->
                            <div class="col-md-6">
                                <!-- Khách hàng cá nhân Section -->
                                <div class="mb-4">
                                    <h6
                                        class="border-bottom border-primary text-primary pb-2 mb-3"
                                    >
                                        <i class="fas fa-user me-2"></i>
                                        Khách hàng cá nhân
                                    </h6>

                                    <!-- Individual Customer Name -->
                                    <div class="mb-3 customer-search-container">
                                        <label
                                            for="editIndividualCustomer"
                                            class="form-label"
                                        >
                                            <i
                                                class="fas fa-user me-1 text-primary"
                                            ></i>
                                            Tên khách hàng cá nhân
                                        </label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="editIndividualCustomer"
                                            v-model="
                                                paymentEditForm.khach_hang_ca_nhan
                                            "
                                            @input="
                                                searchEditIndividualCustomers
                                            "
                                            placeholder="Nhập tên khách hàng cá nhân"
                                            autocomplete="off"
                                        />

                                        <!-- Individual Customer Dropdown -->
                                        <div
                                            v-if="
                                                showEditIndividualDropdown &&
                                                editIndividualCustomerResults.length >
                                                    0
                                            "
                                            class="customer-dropdown"
                                        >
                                            <div
                                                v-for="customer in editIndividualCustomerResults"
                                                :key="customer.ma_kh_ca_nhan"
                                                class="customer-dropdown-item"
                                                @click="
                                                    selectEditIndividualCustomer(
                                                        customer
                                                    )
                                                "
                                            >
                                                <div class="customer-name">
                                                    {{
                                                        customer.khach_hang_ca_nhan
                                                    }}
                                                </div>
                                                <small class="customer-code">{{
                                                    customer.ma_kh_ca_nhan
                                                }}</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Individual Customer Code -->
                                    <div class="mb-3">
                                        <label
                                            for="editIndividualCustomerCode"
                                            class="form-label"
                                        >
                                            <i
                                                class="fas fa-id-card me-1 text-primary"
                                            ></i>
                                            Mã khách hàng cá nhân
                                        </label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="editIndividualCustomerCode"
                                            v-model="
                                                paymentEditForm.ma_khach_hang_ca_nhan
                                            "
                                            placeholder="Nhập mã khách hàng cá nhân"
                                            readonly
                                        />
                                        <div class="form-text">
                                            <i
                                                class="fas fa-info-circle text-primary me-1"
                                            ></i>
                                            Mã sẽ được điền tự động khi chọn tên
                                            khách hàng
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column - Corporate Customer -->
                            <div class="col-md-6">
                                <!-- Khách hàng doanh nghiệp Section -->
                                <div class="mb-4">
                                    <h6
                                        class="border-bottom border-success text-success pb-2 mb-3"
                                    >
                                        <i class="fas fa-building me-2"></i>
                                        Khách hàng doanh nghiệp
                                    </h6>

                                    <!-- Corporate Customer Name -->
                                    <div class="mb-3 customer-search-container">
                                        <label
                                            for="editCorporateCustomer"
                                            class="form-label"
                                        >
                                            <i
                                                class="fas fa-building me-1 text-success"
                                            ></i>
                                            Tên khách hàng doanh nghiệp
                                        </label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="editCorporateCustomer"
                                            v-model="
                                                paymentEditForm.khach_hang_doanh_nghiep
                                            "
                                            @input="
                                                searchEditCorporateCustomers
                                            "
                                            placeholder="Nhập tên khách hàng doanh nghiệp"
                                            autocomplete="off"
                                        />

                                        <!-- Corporate Customer Dropdown -->
                                        <div
                                            v-if="
                                                showEditCorporateDropdown &&
                                                editCorporateCustomerResults.length >
                                                    0
                                            "
                                            class="customer-dropdown"
                                        >
                                            <div
                                                v-for="customer in editCorporateCustomerResults"
                                                :key="
                                                    customer.ma_kh_doanh_nghiep
                                                "
                                                class="customer-dropdown-item"
                                                @click="
                                                    selectEditCorporateCustomer(
                                                        customer
                                                    )
                                                "
                                            >
                                                <div class="customer-name">
                                                    {{
                                                        customer.khach_hang_doanh_nghiep
                                                    }}
                                                </div>
                                                <small class="customer-code">{{
                                                    customer.ma_kh_doanh_nghiep
                                                }}</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Corporate Customer Code -->
                                    <div class="mb-3">
                                        <label
                                            for="editCorporateCustomerCode"
                                            class="form-label"
                                        >
                                            <i
                                                class="fas fa-id-card me-1 text-success"
                                            ></i>
                                            Mã khách hàng doanh nghiệp
                                        </label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="editCorporateCustomerCode"
                                            v-model="
                                                paymentEditForm.ma_khach_hang_doanh_nghiep
                                            "
                                            placeholder="Nhập mã khách hàng doanh nghiệp"
                                            readonly
                                        />
                                        <div class="form-text">
                                            <i
                                                class="fas fa-info-circle text-success me-1"
                                            ></i>
                                            Mã sẽ được điền tự động khi chọn tên
                                            khách hàng
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Selected Payment Requests Preview -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card border-warning">
                                    <div
                                        class="card-header bg-warning text-dark"
                                    >
                                        <h6 class="mb-0">
                                            <i class="fas fa-list me-2"></i>
                                            Phiếu đề nghị đang chỉnh sửa ({{
                                                selectedPaymentRequests.length
                                            }})
                                        </h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <div
                                            class="table-responsive"
                                            style="
                                                max-height: 200px;
                                                overflow-y: auto;
                                            "
                                        >
                                            <table
                                                class="table table-sm table-hover mb-0"
                                            >
                                                <thead
                                                    class="table-light sticky-top"
                                                >
                                                    <tr>
                                                        <th>STT</th>
                                                        <th>Mã giải ngân</th>
                                                        <th>
                                                            Khách hàng hiện tại
                                                        </th>
                                                        <th>Vụ đầu tư</th>
                                                        <th>Loại thanh toán</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr
                                                        v-for="(
                                                            code, index
                                                        ) in selectedPaymentRequests"
                                                        :key="code"
                                                    >
                                                        <td>{{ index + 1 }}</td>
                                                        <td>
                                                            <span
                                                                class="badge bg-primary"
                                                                >{{
                                                                    code
                                                                }}</span
                                                            >
                                                        </td>
                                                        <td>
                                                            <div
                                                                v-if="
                                                                    getPaymentRequestByCode(
                                                                        code
                                                                    )
                                                                "
                                                            >
                                                                <div
                                                                    v-if="
                                                                        getPaymentRequestByCode(
                                                                            code
                                                                        )
                                                                            .individual_customer
                                                                    "
                                                                >
                                                                    <i
                                                                        class="fas fa-user text-primary me-1"
                                                                    ></i>
                                                                    {{
                                                                        getPaymentRequestByCode(
                                                                            code
                                                                        )
                                                                            .individual_customer
                                                                    }}
                                                                </div>
                                                                <div
                                                                    v-else-if="
                                                                        getPaymentRequestByCode(
                                                                            code
                                                                        )
                                                                            .corporate_customer
                                                                    "
                                                                >
                                                                    <i
                                                                        class="fas fa-building text-success me-1"
                                                                    ></i>
                                                                    {{
                                                                        getPaymentRequestByCode(
                                                                            code
                                                                        )
                                                                            .corporate_customer
                                                                    }}
                                                                </div>
                                                                <div
                                                                    v-else
                                                                    class="text-muted"
                                                                >
                                                                    <i
                                                                        class="fas fa-minus me-1"
                                                                    ></i>
                                                                    Chưa có
                                                                    thông tin
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span
                                                                v-if="
                                                                    getPaymentRequestByCode(
                                                                        code
                                                                    )
                                                                "
                                                            >
                                                                {{
                                                                    getPaymentRequestByCode(
                                                                        code
                                                                    )
                                                                        .investment_project ||
                                                                    "N/A"
                                                                }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span
                                                                v-if="
                                                                    getPaymentRequestByCode(
                                                                        code
                                                                    )
                                                                "
                                                                class="badge bg-info"
                                                            >
                                                                {{
                                                                    getPaymentRequestByCode(
                                                                        code
                                                                    )
                                                                        .payment_type ||
                                                                    "N/A"
                                                                }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Validation Summary -->
                        <div class="row mt-3" v-if="showEditValidationSummary">
                            <div class="col-12">
                                <div class="card border-danger">
                                    <div
                                        class="card-header bg-danger text-white"
                                    >
                                        <h6 class="mb-0">
                                            <i
                                                class="fas fa-exclamation-triangle me-2"
                                            ></i>
                                            Cần kiểm tra
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="mb-0 text-danger">
                                            <li v-if="!hasEditFormChanges">
                                                Vui lòng nhập ít nhất một thông
                                                tin để cập nhật
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer bg-light border-top-0">
                    <div
                        class="d-flex justify-content-between align-items-center w-100"
                    >
                        <!-- Left side - Status info -->
                        <div class="text-muted small">
                            <i class="fas fa-info-circle me-1"></i>
                            {{
                                selectedPaymentRequests.length > 0
                                    ? `Đang chỉnh sửa ${selectedPaymentRequests.length} phiếu đề nghị`
                                    : "Chưa chọn phiếu đề nghị"
                            }}
                        </div>

                        <!-- Right side - Action buttons -->
                        <div class="d-flex gap-2">
                            <button
                                type="button"
                                class="btn btn-outline-secondary"
                                data-bs-dismiss="modal"
                                :disabled="isPaymentUpdating"
                            >
                                <i class="fas fa-times me-1"></i>
                                Hủy
                            </button>
                            <button
                                type="button"
                                class="btn btn-warning"
                                @click="updatePaymentRecords"
                                :disabled="
                                    isPaymentUpdating || !hasEditFormChanges
                                "
                            >
                                <span v-if="isPaymentUpdating">
                                    <span
                                        class="spinner-border spinner-border-sm me-2"
                                    ></span>
                                    Đang lưu...
                                </span>
                                <span v-else>
                                    <i class="fas fa-save me-1"></i>
                                    Lưu thay đổi
                                </span>
                            </button>
                        </div>
                    </div>
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
                        Thêm biên bản giao nhận hom giống
                    </h5>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>
                </div>
                <!-- กำหนด max-height และ overflow สำหรับ modal-body -->
                <div
                    class="modal-body"
                    style="max-height: 70vh; overflow-y: auto"
                >
                    <div class="search-container mb-4">
                        <label for="receiptSearch" class="form-label"
                            >Tìm kiếm mã giao nhận hom giống</label
                        >
                        <div class="search-input-wrapper">
                            <input
                                type="search"
                                class="form-control"
                                v-model="searchQuery"
                                @input="searchReceipts"
                                placeholder="Nhập mã số phiếu..."
                            />
                            <i class="search-icon bx bx-search"></i>
                        </div>
                    </div>

                    <!-- Search Results Section -->
                    <div
                        class="search-results"
                        v-if="receiptResults.length > 0"
                        style="
                            max-height: 300px;
                            overflow-y: auto;
                            border: 1px solid #dee2e6;
                            border-radius: 0.375rem;
                        "
                    >
                        <h6
                            class="results-title mb-2 p-2 bg-light border-bottom"
                        >
                            Kết quả tìm kiếm
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Mã số phiếu</th>
                                        <th>Trạm</th>
                                        <th>Vụ đầu tư</th>
                                        <th>Tên phiếu</th>
                                        <th>Hợp đồng đầu tư mía bên giao</th>
                                        <th>Tổng thực nhận</th>
                                        <th>Tổng tiền</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="item in receiptResults"
                                        :key="item.ma_so_phieu"
                                        :class="{
                                            'table-warning': isDuplicate(
                                                item.ma_so_phieu
                                            ),
                                        }"
                                    >
                                        <td>{{ item.ma_so_phieu }}</td>
                                        <td>{{ item.tram }}</td>
                                        <td>{{ item.vu_dau_tu }}</td>
                                        <td>{{ item.ten_phieu }}</td>
                                        <td>
                                            {{
                                                item.hop_dong_dau_tu_mia_ben_giao_hom
                                            }}
                                        </td>
                                        <td>
                                            {{ item.tong_thuc_nhan }}
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
                                                        item.ma_so_phieu
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

                    <!-- Selected Receipts Section -->
                    <div
                        class="selected-receipts mt-4"
                        v-if="selectedReceipts.length > 0"
                    >
                        <h6 class="mb-2">
                            Biên bản đã chọn ({{ selectedReceipts.length }})
                        </h6>
                        <div
                            class="table-responsive"
                            style="
                                max-height: 250px;
                                overflow-y: auto;
                                border: 1px solid #dee2e6;
                                border-radius: 0.375rem;
                            "
                        >
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th style="width: 5%">#</th>
                                        <th style="width: 15%">Mã số phiếu</th>
                                        <th style="width: 12%">Trạm</th>
                                        <th style="width: 15%">Vụ đầu tư</th>
                                        <th style="width: 20%">Tên phiếu</th>
                                        <th style="width: 18%">
                                            Hợp đồng đầu tư mía bên giao
                                        </th>
                                        <th style="width: 10%">
                                            Tổng thực nhận
                                        </th>
                                        <th style="width: 20%">Tổng tiền</th>
                                        <th style="width: 5%">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(
                                            item, index
                                        ) in selectedReceipts"
                                        :key="index"
                                        class="table-row-selected"
                                    >
                                        <td class="text-center fw-bold">
                                            {{ index + 1 }}
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{
                                                item.ma_so_phieu
                                            }}</span>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{
                                                item.tram || "N/A"
                                            }}</span>
                                        </td>
                                        <td>
                                            <span class="fw-medium">{{
                                                item.vu_dau_tu || "N/A"
                                            }}</span>
                                        </td>
                                        <td
                                            class="text-truncate"
                                            :title="item.ten_phieu"
                                        >
                                            {{ item.ten_phieu || "N/A" }}
                                        </td>
                                        <td
                                            class="text-truncate"
                                            :title="
                                                item.hop_dong_dau_tu_mia_ben_giao_hom
                                            "
                                        >
                                            {{
                                                item.hop_dong_dau_tu_mia_ben_giao_hom ||
                                                "N/A"
                                            }}
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-bold text-success">
                                                {{ item.tong_thuc_nhan || 0 }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-bold text-success">
                                                {{
                                                    formatCurrency(
                                                        item.tong_tien
                                                    )
                                                }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button
                                                @click="
                                                    removeSelectedReceipt(index)
                                                "
                                                class="btn btn-sm btn-outline-danger"
                                                title="Xóa biên bản này khỏi danh sách chọน"
                                            >
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light sticky-bottom">
                                    <tr>
                                        <td
                                            colspan="7"
                                            class="text-end fw-bold"
                                        >
                                            <i
                                                class="fas fa-calculator me-2"
                                            ></i>
                                            Tổng cộng:
                                        </td>
                                        <td
                                            class="text-end fw-bold text-success"
                                        >
                                            {{
                                                formatCurrency(
                                                    calculateTotalSelected()
                                                )
                                            }}
                                        </td>
                                        <td></td>
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
                        class="modal-title text-white"
                        id="paymentImportModalLabel"
                    >
                        <i class="fas fa-file-import text-white me-2"></i>
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
                    <div class="alert alert-info text-info mb-3">
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
        data-bs-backdrop="static"
        data-bs-keyboard="false"
    >
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5
                        class="modal-title text-white"
                        id="addPaymentModalLabel"
                    >
                        <i class="fas fa-plus me-2"></i>
                        Thêm phiếu đề nghị thanh toán
                    </h5>
                    <button
                        type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>
                </div>

                <div
                    class="modal-body"
                    style="max-height: 70vh; overflow-y: auto"
                >
                    <!-- Form Container -->
                    <form @submit.prevent="addPaymentRequest">
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <!-- Mã giải ngân -->
                                <div class="mb-3">
                                    <label
                                        for="disbursementCode"
                                        class="form-label"
                                    >
                                        <i
                                            class="fas fa-barcode me-1 text-primary"
                                        ></i>
                                        Mã giải ngân
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="disbursementCode"
                                        v-model="newPaymentRequest.ma_giai_ngan"
                                        placeholder="Nhập mã giải ngân"
                                        required
                                    />
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Mã định danh duy nhất cho phiếu đề nghị
                                    </div>
                                </div>

                                <!-- Vụ đầu tư -->
                                <div class="mb-3">
                                    <label
                                        for="investmentProject"
                                        class="form-label"
                                    >
                                        <i
                                            class="fas fa-project-diagram me-1 text-info"
                                        ></i>
                                        Vụ đầu tư
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select
                                        class="form-select"
                                        id="investmentProject"
                                        v-model="newPaymentRequest.vu_dau_tu"
                                        required
                                    >
                                        <option value="" disabled>
                                            -- Chọn vụ đầu tư --
                                        </option>
                                        <option
                                            v-for="project in investmentProjects"
                                            :key="project.Ma_Vudautu"
                                            :value="project.Ma_Vudautu"
                                        >
                                            {{ project.Ten_Vudautu }}
                                        </option>
                                    </select>
                                    <div class="form-text">
                                        <i class="fas fa-lightbulb me-1"></i>
                                        Chọn vụ đầu tư liên quan
                                    </div>
                                </div>

                                <!-- Loại thanh toán -->
                                <div class="mb-3">
                                    <label for="paymentType" class="form-label">
                                        <i
                                            class="fas fa-credit-card me-1 text-warning"
                                        ></i>
                                        Loại thanh toán
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select
                                        class="form-select"
                                        id="paymentType"
                                        v-model="
                                            newPaymentRequest.loai_thanh_toan
                                        "
                                        required
                                    >
                                        <option
                                            value="Phiếu giao nhận hom giống"
                                        >
                                            -- Chọn loại thanh toán --
                                        </option>

                                        <option
                                            value="Phiếu giao nhận hom giống"
                                        >
                                            Phiếu giao nhận hom giống
                                        </option>
                                    </select>
                                    <div class="form-text">
                                        <i class="fas fa-tag me-1"></i>
                                        Phân loại hình thức thanh toán
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <!-- Khách hàng cá nhân Section -->
                                <div class="mb-4">
                                    <h6 class="text-primary border-bottom pb-2">
                                        <i class="fas fa-user me-2"></i>
                                        Thông tin khách hàng cá nhân
                                    </h6>

                                    <!-- Tên khách hàng cá nhân with search -->
                                    <div class="mb-3">
                                        <label
                                            for="individualCustomer"
                                            class="form-label"
                                        >
                                            Tên khách hàng cá nhân
                                        </label>
                                        <div class="position-relative">
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="individualCustomer"
                                                v-model="
                                                    newPaymentRequest.khach_hang_ca_nhan
                                                "
                                                @input="
                                                    searchIndividualCustomers
                                                "
                                                placeholder="Nhập tên khách hàng cá nhân"
                                                autocomplete="off"
                                            />
                                            <!-- Individual customers dropdown -->
                                            <div
                                                v-if="
                                                    individualCustomerResults.length >
                                                        0 &&
                                                    showIndividualDropdown
                                                "
                                                class="dropdown-menu show w-100 mt-1"
                                                style="
                                                    max-height: 200px;
                                                    overflow-y: auto;
                                                "
                                            >
                                                <button
                                                    v-for="customer in individualCustomerResults"
                                                    :key="customer.id"
                                                    type="button"
                                                    class="dropdown-item"
                                                    @click="
                                                        selectIndividualCustomer(
                                                            customer
                                                        )
                                                    "
                                                >
                                                    <div
                                                        class="d-flex justify-content-between"
                                                    >
                                                        <span>{{
                                                            customer.khach_hang_ca_nhan
                                                        }}</span>
                                                        <small
                                                            class="text-muted"
                                                            >{{
                                                                customer.ma_kh_ca_nhan
                                                            }}</small
                                                        >
                                                    </div>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Mã khách hàng cá nhân (readonly) -->
                                    <div class="mb-3">
                                        <label
                                            for="individualCustomerCode"
                                            class="form-label"
                                        >
                                            Mã khách hàng cá nhân
                                        </label>
                                        <input
                                            type="text"
                                            class="form-control bg-light"
                                            id="individualCustomerCode"
                                            v-model="
                                                newPaymentRequest.ma_khach_hang_ca_nhan
                                            "
                                            placeholder="Mã sẽ được điền tự động"
                                            readonly
                                        />
                                    </div>
                                </div>

                                <!-- Khách hàng doanh nghiệp Section -->
                                <div class="mb-4">
                                    <h6 class="text-success border-bottom pb-2">
                                        <i class="fas fa-building me-2"></i>
                                        Thông tin khách hàng doanh nghiệp
                                    </h6>

                                    <!-- Tên khách hàng doanh nghiệp with search -->
                                    <div class="mb-3">
                                        <label
                                            for="corporateCustomer"
                                            class="form-label"
                                        >
                                            Tên khách hàng doanh nghiệp
                                        </label>
                                        <div class="position-relative">
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="corporateCustomer"
                                                v-model="
                                                    newPaymentRequest.khach_hang_doanh_nghiep
                                                "
                                                @input="
                                                    searchCorporateCustomers
                                                "
                                                placeholder="Nhập tên khách hàng doanh nghiệp"
                                                autocomplete="off"
                                            />
                                            <!-- Corporate customers dropdown -->
                                            <div
                                                v-if="
                                                    corporateCustomerResults.length >
                                                        0 &&
                                                    showCorporateDropdown
                                                "
                                                class="dropdown-menu show w-100 mt-1"
                                                style="
                                                    max-height: 200px;
                                                    overflow-y: auto;
                                                "
                                            >
                                                <button
                                                    v-for="customer in corporateCustomerResults"
                                                    :key="customer.id"
                                                    type="button"
                                                    class="dropdown-item"
                                                    @click="
                                                        selectCorporateCustomer(
                                                            customer
                                                        )
                                                    "
                                                >
                                                    <div
                                                        class="d-flex justify-content-between"
                                                    >
                                                        <span>{{
                                                            customer.khach_hang_doanh_nghiep
                                                        }}</span>
                                                        <small
                                                            class="text-muted"
                                                            >{{
                                                                customer.ma_kh_doanh_nghiep
                                                            }}</small
                                                        >
                                                    </div>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Mã khách hàng doanh nghiệp (readonly) -->
                                    <div class="mb-3">
                                        <label
                                            for="corporateCustomerCode"
                                            class="form-label"
                                        >
                                            Mã khách hàng doanh nghiệp
                                        </label>
                                        <input
                                            type="text"
                                            class="form-control bg-light"
                                            id="corporateCustomerCode"
                                            v-model="
                                                newPaymentRequest.ma_khach_hang_doanh_nghiep
                                            "
                                            placeholder="Mã sẽ được điền tự động"
                                            readonly
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Biên bản liên kết Section -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div
                                    class="alert alert-info border-0"
                                    v-if="paymentReceiptIds.length > 0"
                                >
                                    <div class="d-flex align-items-center">
                                        <i
                                            class="fas fa-link fa-2x text-info me-3"
                                        ></i>
                                        <div>
                                            <h6
                                                class="alert-heading mb-1 text-info"
                                            >
                                                Biên bản nghiệm thu đã chọn
                                            </h6>
                                            <p class="mb-0">
                                                Phiếu đề nghị sẽ được liên kết
                                                với
                                                <span
                                                    class="badge bg-info text-dark"
                                                >
                                                    {{
                                                        paymentReceiptIds.length
                                                    }}
                                                </span>
                                                biên bản nghiệm thu đã chọn
                                            </p>
                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    <i
                                                        class="fas fa-list me-1"
                                                    ></i>
                                                    Mã biên bản:
                                                    {{
                                                        paymentReceiptIds.join(
                                                            ", "
                                                        )
                                                    }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="alert alert-warning border-0"
                                    v-else
                                >
                                    <div class="d-flex align-items-center">
                                        <i
                                            class="fas fa-exclamation-triangle fa-2x text-warning me-3"
                                        ></i>
                                        <div>
                                            <h6
                                                class="alert-heading mb-1 text-warning"
                                            >
                                                Chưa chọn biên bản nghiệm thu
                                            </h6>
                                            <p class="mb-0">
                                                Vui lòng chọn ít nhất một biên
                                                bản nghiệm thu trong bảng "Chi
                                                tiết hồ sơ thanh toán" trước khi
                                                tạo phiếu đề nghị
                                            </p>
                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    <i
                                                        class="fas fa-info-circle me-1"
                                                    ></i>
                                                    Bạn có thể chọn nhiều biên
                                                    bản cùng lúc bằng cách tick
                                                    vào checkbox
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Validation Summary -->
                        <div class="row mt-3" v-if="showValidationSummary">
                            <div class="col-12">
                                <div class="card border-danger">
                                    <div class="card-body text-center py-3">
                                        <h6 class="text-danger mb-2">
                                            <i
                                                class="fas fa-exclamation-circle me-2"
                                            ></i>
                                            Vui lòng kiểm tra lại thông tin
                                        </h6>
                                        <div class="text-muted small">
                                            Các trường có dấu
                                            <span class="text-danger">*</span>
                                            là bắt buộc
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Modal Footer với thiết kế mới -->
                <div class="modal-footer bg-light border-top-0">
                    <div
                        class="d-flex justify-content-between align-items-center w-100"
                    >
                        <!-- Left side - Status info -->
                        <div class="text-muted small">
                            <i class="fas fa-info-circle me-1"></i>
                            {{
                                paymentReceiptIds.length > 0
                                    ? `Sẵn sàng tạo phiếu với ${paymentReceiptIds.length} biên bản`
                                    : "Chưa chọn biên bản nghiệm thu"
                            }}
                        </div>

                        <!-- Right side - Action buttons -->
                        <div class="d-flex gap-2">
                            <button
                                type="button"
                                class="btn btn-outline-secondary"
                                data-bs-dismiss="modal"
                                :disabled="isAddingPaymentRequest"
                            >
                                <i class="fas fa-times me-1"></i>
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
                                <span v-if="isAddingPaymentRequest">
                                    <span
                                        class="spinner-border spinner-border-sm me-2"
                                        role="status"
                                        aria-hidden="true"
                                    ></span>
                                    Đang tạo...
                                </span>
                                <span v-else>
                                    <i class="fas fa-save me-1"></i>
                                    Tạo phiếu đề nghị
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Print Modal -->
    <div
        class="modal fade"
        id="printModal"
        tabindex="-1"
        aria-labelledby="printModalLabel"
        aria-hidden="true"
        data-bs-backdrop="static"
        data-bs-keyboard="false"
    >
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="printModalLabel">
                        <i class="fas fa-print me-2"></i>
                        Xác nhận in phiếu đề nghị thanh toán
                    </h5>
                    <button
                        type="button"
                        class="btn-close btn-close-white"
                        @click="closePrintModal"
                        aria-label="Close"
                    ></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Bạn đã chọn
                        <strong>{{ selectedPaymentRequests.length }}</strong>
                        phiếu để in
                    </div>

                    <!-- Loading State -->
                    <div v-if="printPreviewLoading" class="text-center py-4">
                        <div class="spinner-border text-info" role="status">
                            <span class="visually-hidden">Đang tải...</span>
                        </div>
                        <p class="mt-2 text-muted">Đang tải danh sách in...</p>
                    </div>

                    <!-- Print Preview List -->
                    <div
                        v-else-if="printPreviewData.length > 0"
                        class="table-responsive"
                    >
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px">#</th>
                                    <th>Mã giải ngân</th>
                                    <th>Khách hàng</th>
                                    <th>Vụ đầu tư</th>
                                    <th>Loại thanh toán</th>
                                    <th>Tổng tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(item, index) in printPreviewData"
                                    :key="index"
                                >
                                    <td class="text-center">{{ index + 1 }}</td>
                                    <td>
                                        <span class="fw-medium text-primary">{{
                                            item.ma_giai_ngan
                                        }}</span>
                                    </td>
                                    <td>{{ item.customer_name }}</td>
                                    <td>{{ item.investment_project }}</td>
                                    <td>
                                        <span class="badge bg-info">{{
                                            item.payment_type
                                        }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold text-success">
                                            {{
                                                formatCurrency(
                                                    item.total_amount
                                                )
                                            }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="5" class="text-end fw-bold">
                                        Tổng cộng:
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        {{ formatCurrency(printPreviewTotal) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Error State -->
                    <div
                        v-else-if="printPreviewError"
                        class="alert alert-danger"
                    >
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        {{ printPreviewError }}
                    </div>
                </div>

                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        @click="closePrintModal"
                    >
                        <i class="fas fa-times me-1"></i>
                        Hủy
                    </button>
                    <button
                        type="button"
                        class="btn btn-info"
                        @click="executePrint"
                        :disabled="
                            printPreviewLoading ||
                            printPreviewData.length === 0 ||
                            printExecuting
                        "
                    >
                        <i
                            class="fas fa-spinner fa-spin me-1"
                            v-if="printExecuting"
                        ></i>
                        <i class="fas fa-print me-1" v-else></i>
                        {{ printExecuting ? "Đang in..." : "In ngay" }}
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
import { ResizableColumns } from "../../Directives/ResizableColumns";
import { usePermissions } from "../../Composables/usePermissions";

export default {
    components: {
        Bootstrap5Pagination,
    },
    directives: {
        "resizable-columns": ResizableColumns,
    },
    setup() {
        const store = useStore();
        const { hasPermission, canViewComponent } = usePermissions();
        return {
            store,
            hasPermission,
            canViewComponent,
        };
    },
    data() {
        return {
            isSavingNote: false,
            // เพิ่ม modalInstances ใน data()
            modalInstances: [],
            // Debounced functions
            debouncedFormatInput: null,
            // Customer search related properties
            individualCustomerResults: [],
            corporateCustomerResults: [],
            showIndividualDropdown: false,
            showCorporateDropdown: false,
            individualSearchTimeout: null,
            corporateSearchTimeout: null,
            // search related properties
            editIndividualCustomerResults: [],
            editCorporateCustomerResults: [],
            showEditIndividualDropdown: false,
            showEditCorporateDropdown: false,
            editIndividualSearchTimeout: null,
            editCorporateSearchTimeout: null,
            showEditValidationSummary: false,
            // Payment Requests Modal specific data
            paymentRequestsModal: null,
            paymentSearchQuery: "",
            showPaymentAdvancedFilters: false,
            paymentQuickFilter: null,
            paymentSearchHighlightCache: {},
            //Modal Chi tiết NTDVNTDV
            modalSearchQuery: "", // Search query for modal
            showAdvancedFilters: false, // Toggle for advanced filters
            quickFilter: null, // Current quick filter
            searchHighlightCache: {}, // Cache for search highlights
            document: {
                payment_code: "", // Mã trình thanh toán
                title: "", // Tiêu đề
                investment_project: "", // Vụ đầu tư
                payment_type: "", // Loại thanh toán
                status: "pending", // Trạng thái thanh toán
                payment_installment: 0, // Số đợt thanh toán
                proposal_number: "", // Số tờ trình
                created_at: null, // Ngày tạo
                payment_date: null, // Ngày thanh toán
                total_amount: 0, // Tổng tiền thanh toán
                creator_name: "", // Người tạo
                notes: "",
                payment_date: null,
            },
            // Update Hold Amount Modal
            holdAmountForm: {
                don_gia_chat_mia: 0,
                don_gia_gap_mia: 0,
                don_gia_van_chuyen_mia: 0,
            },
            updateHoldAmountLoading: false,
            // Print related data
            printModal: null,
            printPreviewLoading: false,
            printPreviewData: [],
            printPreviewError: null,
            printExecuting: false,
            // Timeline related properties
            processingHistory: [], // Add this property to store timeline events
            showTimeline: false,
            users: {}, // Cache for user info
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
            paymentDetailsModal: null,

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
        hasEditFormChanges() {
            return Object.values(this.paymentEditForm).some(
                (value) => value !== null && value !== ""
            );
        },
        totalSelectedThucNhan() {
            return this.selectedPaymentRequestsData.reduce((total, item) => {
                return total + parseFloat(item.actual_received || 0);
            }, 0);
        },

        totalHarvestCostPerTon() {
            const chatMia =
                parseFloat(this.holdAmountForm.don_gia_chat_mia) || 0;
            const gapMia = parseFloat(this.holdAmountForm.don_gia_gap_mia) || 0;
            const vanChuyen =
                parseFloat(this.holdAmountForm.don_gia_van_chuyen_mia) || 0;

            return chatMia + gapMia + vanChuyen;
        },

        estimatedTotalHoldAmount() {
            return this.totalSelectedThucNhan * this.totalHarvestCostPerTon;
        },
        selectedPaymentRequestsData() {
            return this.paginatedPaymentRequests.data.filter((item) =>
                this.selectedPaymentRequests.includes(item.disbursement_code)
            );
        },
        isFormValid() {
            const chatMia =
                parseFloat(this.holdAmountForm.don_gia_chat_mia) || 0;
            const gapMia = parseFloat(this.holdAmountForm.don_gia_gap_mia) || 0;
            const vanChuyen =
                parseFloat(this.holdAmountForm.don_gia_van_chuyen_mia) || 0;

            return chatMia >= 0 && gapMia >= 0 && vanChuyen >= 0;
        },
        printPreviewTotal() {
            return this.printPreviewData.reduce(
                (sum, item) => sum + (parseFloat(item.total_amount) || 0),
                0
            );
        },
        // เพิ่ม property นี้เพื่อให้สามารถอัปเดต UI ทันทีเมื่อมีการเปลี่ยนแปลงสถานะ
        // Add this new computed property to track the payment date field reference
        paymentDateRef() {
            return this.$refs.paymentDateInput;
        },
        documentStatusClass() {
            return {
                processing: this.document.status === "processing",
                submitted: this.document.status === "submitted",
                paid: this.document.status === "paid",
                cancelled: this.document.status === "cancelled",
            };
        },
        // Timeline related computed properties
        processingAction() {
            if (!this.processingHistory) return null;
            return this.processingHistory.find(
                (item) => item.action === "processing"
            );
        },

        submittedAction() {
            if (!this.processingHistory) return null;
            return this.processingHistory.find(
                (item) => item.action === "submitted"
            );
        },

        paidAction() {
            if (!this.processingHistory) return null;
            return this.processingHistory.find(
                (item) => item.action === "paid"
            );
        },

        cancelledAction() {
            if (!this.processingHistory) return null;
            return this.processingHistory.find(
                (item) => item.action === "cancelled"
            );
        },

        daysBetweenProcessingAndSubmitted() {
            if (!this.processingAction || !this.submittedAction) return null;

            const processingDate = new Date(this.processingAction.created_at);
            const submittedDate = new Date(this.submittedAction.created_at);

            const diffTime = Math.abs(submittedDate - processingDate);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            return diffDays;
        },

        daysBetweenSubmittedAndPaid() {
            if (!this.submittedAction) return null;

            // ถ้าไม่มี document.payment_date และไม่มี paidAction ให้คืนค่า null
            if (!this.document.payment_date && !this.paidAction) return null;

            const submittedDate = new Date(this.submittedAction.created_at);

            // ใช้ document.payment_date (วันที่จ่ายเงินจริง) หากมีค่า มิฉะนั้นใช้วันที่มีการเปลี่ยนสถานะเป็น paid
            const paidDate = this.document.payment_date
                ? new Date(this.document.payment_date)
                : new Date(this.paidAction.created_at);

            const diffTime = Math.abs(paidDate - submittedDate);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            return diffDays;
        },
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
            let filtered = this.paymentDetails;

            // Apply search filter first
            if (this.modalSearchQuery && this.modalSearchQuery.trim()) {
                const searchTerm = this.modalSearchQuery.trim().toLowerCase();
                filtered = filtered.filter((item) => {
                    return (
                        (item.document_code &&
                            item.document_code
                                .toLowerCase()
                                .includes(searchTerm)) ||
                        (item.title &&
                            item.title.toLowerCase().includes(searchTerm)) ||
                        (item.investment_project &&
                            item.investment_project
                                .toLowerCase()
                                .includes(searchTerm)) ||
                        (item.khach_hang_ca_nhan_dt_mia &&
                            item.khach_hang_ca_nhan_dt_mia
                                .toLowerCase()
                                .includes(searchTerm)) ||
                        (item.khach_hang_doanh_nghiep_dt_mia &&
                            item.khach_hang_doanh_nghiep_dt_mia
                                .toLowerCase()
                                .includes(searchTerm)) ||
                        (item.contract_number &&
                            item.contract_number
                                .toLowerCase()
                                .includes(searchTerm)) ||
                        (item.service_type &&
                            item.service_type
                                .toLowerCase()
                                .includes(searchTerm)) ||
                        (item.hop_dong_cung_ung_dich_vu &&
                            item.hop_dong_cung_ung_dich_vu
                                .toLowerCase()
                                .includes(searchTerm)) ||
                        (item.disbursement_code &&
                            item.disbursement_code
                                .toLowerCase()
                                .includes(searchTerm)) ||
                        (item.tram &&
                            item.tram.toLowerCase().includes(searchTerm))
                    );
                });
            }

            // Apply quick filters
            if (this.quickFilter) {
                switch (this.quickFilter) {
                    case "has_disbursement":
                        filtered = filtered.filter(
                            (item) =>
                                item.disbursement_code &&
                                item.disbursement_code.trim()
                        );
                        break;
                    case "no_disbursement":
                        filtered = filtered.filter(
                            (item) =>
                                !item.disbursement_code ||
                                !item.disbursement_code.trim()
                        );
                        break;
                    case "high_amount":
                        filtered = filtered.filter(
                            (item) => parseFloat(item.amount || 0) > 1000000000
                        );
                        break;
                }
            }

            // Apply existing column filters
            return filtered.filter((item) => {
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
        totalFilteredAmount() {
            return this.filteredPaymentDetails.reduce(
                (sum, item) => sum + (parseFloat(item.amount) || 0),
                0
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
            let filtered = this.paymentRequests;

            // Apply search filter first
            if (this.paymentSearchQuery && this.paymentSearchQuery.trim()) {
                const searchTerm = this.paymentSearchQuery.trim().toLowerCase();
                filtered = filtered.filter((item) => {
                    return (
                        (item.disbursement_code &&
                            item.disbursement_code
                                .toLowerCase()
                                .includes(searchTerm)) ||
                        (item.investment_project &&
                            item.investment_project
                                .toLowerCase()
                                .includes(searchTerm)) ||
                        (item.payment_type &&
                            item.payment_type
                                .toLowerCase()
                                .includes(searchTerm)) ||
                        (item.individual_customer &&
                            item.individual_customer
                                .toLowerCase()
                                .includes(searchTerm)) ||
                        (item.individual_customer_code &&
                            item.individual_customer_code
                                .toLowerCase()
                                .includes(searchTerm)) ||
                        (item.corporate_customer &&
                            item.corporate_customer
                                .toLowerCase()
                                .includes(searchTerm)) ||
                        (item.corporate_customer_code &&
                            item.corporate_customer_code
                                .toLowerCase()
                                .includes(searchTerm)) ||
                        (item.proposal_number &&
                            item.proposal_number
                                .toLowerCase()
                                .includes(searchTerm))
                    );
                });
            }

            // Apply quick filters
            if (this.paymentQuickFilter) {
                switch (this.paymentQuickFilter) {
                    case "individual_customers":
                        filtered = filtered.filter(
                            (item) =>
                                item.individual_customer &&
                                item.individual_customer.trim()
                        );
                        break;
                    case "corporate_customers":
                        filtered = filtered.filter(
                            (item) =>
                                item.corporate_customer &&
                                item.corporate_customer.trim()
                        );
                        break;
                    case "high_value":
                        filtered = filtered.filter(
                            (item) =>
                                parseFloat(item.total_remaining || 0) >
                                1000000000
                        );
                        break;
                }
            }

            // Apply existing column filters
            return filtered.filter((item) => {
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
                            item.payment_date.includes(
                                this.paymentFilters.payment_date
                            ))) &&
                    (!this.paymentFilters.proposal_number ||
                        (item.proposal_number &&
                            item.proposal_number
                                .toString()
                                .includes(
                                    this.paymentFilters.proposal_number
                                ))) &&
                    (!this.paymentFilters.installment ||
                        (item.installment &&
                            item.installment
                                .toString()
                                .includes(this.paymentFilters.installment)));

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

        totalFilteredPaymentAmount() {
            return this.filteredPaymentRequests.reduce(
                (sum, item) => sum + (parseFloat(item.total_amount) || 0),
                0
            );
        },

        totalFilteredHoldAmount() {
            return this.filteredPaymentRequests.reduce(
                (sum, item) => sum + (parseFloat(item.total_hold_amount) || 0),
                0
            );
        },

        totalFilteredDeductionAmount() {
            return this.filteredPaymentRequests.reduce(
                (sum, item) => sum + (parseFloat(item.total_deduction) || 0),
                0
            );
        },

        totalFilteredInterestAmount() {
            return this.filteredPaymentRequests.reduce(
                (sum, item) => sum + (parseFloat(item.total_interest) || 0),
                0
            );
        },

        totalFilteredRemainingAmount() {
            return this.filteredPaymentRequests.reduce(
                (sum, item) => sum + (parseFloat(item.total_remaining) || 0),
                0
            );
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
        // Add click outside listener to hide dropdowns
        document.addEventListener("click", this.hideDropdowns);

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
    beforeUnmount() {
        // Clean up event listener and timeouts
        document.removeEventListener("click", this.hideDropdowns);
        document.removeEventListener("click", this.hideEditDropdowns);

        if (this.individualSearchTimeout) {
            clearTimeout(this.individualSearchTimeout);
        }

        if (this.corporateSearchTimeout) {
            clearTimeout(this.corporateSearchTimeout);
        }

        // ตรวจสอบและปิด modal instances อย่างปลอดภัย
        if (Array.isArray(this.modalInstances)) {
            this.modalInstances.forEach((modalData) => {
                try {
                    if (
                        modalData &&
                        modalData.instance &&
                        typeof modalData.instance.dispose === "function"
                    ) {
                        modalData.instance.dispose();
                    }
                } catch (error) {
                    console.warn("Error disposing modal:", error);
                }
            });
        }

        // ล้าง array
        this.modalInstances = [];

        // ปิดและ dispose modal instances แบบเก่า (สำหรับ modals ที่อาจไม่ได้เก็บใน modalInstances)
        const modalIds = [
            "updateHoldAmountModal",
            "paymentRequestsModal",
            "paymentDetailsModal",
            "addPaymentModal",
            "paymentEditModal",
            "printModal",
            "importModal",
            "paymentImportModal",
            "editModal",
            "addReceiptModal",
        ];

        modalIds.forEach((modalId) => {
            try {
                const modalElement = document.getElementById(modalId);
                if (
                    modalElement &&
                    typeof bootstrap !== "undefined" &&
                    bootstrap.Modal
                ) {
                    const modal = bootstrap.Modal.getInstance
                        ? bootstrap.Modal.getInstance(modalElement)
                        : null;
                    if (modal && typeof modal.dispose === "function") {
                        modal.dispose();
                    }
                }
            } catch (error) {
                console.warn(`Error disposing modal ${modalId}:`, error);
            }
        });
    },
    // เพิ่มวิธีการปิด modal อย่างปลอดภัย
    safeDisposeModal(modalId) {
        try {
            // หาใน modalInstances array
            const modalIndex = this.modalInstances.findIndex(
                (m) => m.element && m.element.id === modalId
            );

            if (modalIndex !== -1) {
                const modalData = this.modalInstances[modalIndex];
                if (
                    modalData.instance &&
                    typeof modalData.instance.dispose === "function"
                ) {
                    modalData.instance.dispose();
                }
                this.modalInstances.splice(modalIndex, 1);
                return;
            }

            // หากไม่พบใน array ให้ลองหาจาก DOM
            const modalElement = document.getElementById(modalId);
            if (
                modalElement &&
                typeof bootstrap !== "undefined" &&
                bootstrap.Modal
            ) {
                const modal = bootstrap.Modal.getInstance
                    ? bootstrap.Modal.getInstance(modalElement)
                    : null;
                if (modal && typeof modal.dispose === "function") {
                    modal.dispose();
                }
            }
        } catch (error) {
            console.warn(`Error disposing modal ${modalId}:`, error);
        }
    },

    methods: {
        /**
         * Search individual customers for edit modal
         */
        searchEditIndividualCustomers() {
            // Clear previous timeout
            if (this.editIndividualSearchTimeout) {
                clearTimeout(this.editIndividualSearchTimeout);
            }

            const query = this.paymentEditForm.khach_hang_ca_nhan;

            if (!query || query.length < 2) {
                this.editIndividualCustomerResults = [];
                this.showEditIndividualDropdown = false;
                return;
            }

            // Debounce search
            this.editIndividualSearchTimeout = setTimeout(async () => {
                try {
                    const response = await axios.get(
                        "/api/search-individual-customers",
                        {
                            params: { query },
                            headers: {
                                Authorization: "Bearer " + this.store.getToken,
                            },
                        }
                    );

                    if (response.data.success) {
                        this.editIndividualCustomerResults = response.data.data;
                        this.showEditIndividualDropdown = true;
                    }
                } catch (error) {
                    console.error(
                        "Error searching individual customers:",
                        error
                    );
                    this.editIndividualCustomerResults = [];
                    this.showEditIndividualDropdown = false;
                }
            }, 300);
        },

        /**
         * Search corporate customers for edit modal
         */
        searchEditCorporateCustomers() {
            // Clear previous timeout
            if (this.editCorporateSearchTimeout) {
                clearTimeout(this.editCorporateSearchTimeout);
            }

            const query = this.paymentEditForm.khach_hang_doanh_nghiep;

            if (!query || query.length < 2) {
                this.editCorporateCustomerResults = [];
                this.showEditCorporateDropdown = false;
                return;
            }

            // Debounce search
            this.editCorporateSearchTimeout = setTimeout(async () => {
                try {
                    const response = await axios.get(
                        "/api/search-corporate-customers",
                        {
                            params: { query },
                            headers: {
                                Authorization: "Bearer " + this.store.getToken,
                            },
                        }
                    );

                    if (response.data.success) {
                        this.editCorporateCustomerResults = response.data.data;
                        this.showEditCorporateDropdown = true;
                    }
                } catch (error) {
                    console.error(
                        "Error searching corporate customers:",
                        error
                    );
                    this.editCorporateCustomerResults = [];
                    this.showEditCorporateDropdown = false;
                }
            }, 300);
        },

        /**
         * Select individual customer for edit modal
         */
        selectEditIndividualCustomer(customer) {
            this.paymentEditForm.khach_hang_ca_nhan =
                customer.khach_hang_ca_nhan;
            this.paymentEditForm.ma_khach_hang_ca_nhan = customer.ma_kh_ca_nhan;
            this.editIndividualCustomerResults = [];
            this.showEditIndividualDropdown = false;
        },

        /**
         * Select corporate customer for edit modal
         */
        selectEditCorporateCustomer(customer) {
            this.paymentEditForm.khach_hang_doanh_nghiep =
                customer.khach_hang_doanh_nghiep;
            this.paymentEditForm.ma_khach_hang_doanh_nghiep =
                customer.ma_kh_doanh_nghiep;
            this.editCorporateCustomerResults = [];
            this.showEditCorporateDropdown = false;
        },

        /**
         * Get payment request by disbursement code
         */
        getPaymentRequestByCode(code) {
            return this.paymentRequests.find(
                (item) => item.disbursement_code === code
            );
        },

        /**
         * Hide edit dropdowns when clicking outside
         */
        hideEditDropdowns() {
            this.showEditIndividualDropdown = false;
            this.showEditCorporateDropdown = false;
        },

        // Debounce function เพื่อป้องกัน multiple clicks
        debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },

        // แก้ไข method สำหรับ currency input
        formatCurrencyInput: function (event, fieldName) {
            // ใช้ debounce เพื่อลดการเรียกใช้
            if (!this.debouncedFormatInput) {
                this.debouncedFormatInput = this.debounce(
                    (event, fieldName) => {
                        let value = event.target.value;

                        // Remove non-numeric characters except dots
                        value = value.replace(/[^0-9.]/g, "");

                        // Ensure only one decimal point
                        const parts = value.split(".");
                        if (parts.length > 2) {
                            value = parts[0] + "." + parts.slice(1).join("");
                        }

                        // Limit decimal places to 2
                        if (parts[1] && parts[1].length > 2) {
                            value = parts[0] + "." + parts[1].substring(0, 2);
                        }

                        // Update the model value as number
                        this.holdAmountForm[fieldName] = value
                            ? parseFloat(value) || 0
                            : 0;

                        // Update the input display value
                        event.target.value = value;
                    },
                    300
                );
            }

            this.debouncedFormatInput(event, fieldName);
        },

        removeFormatting(event, fieldName) {
            // When focused, show raw number without formatting
            const value = this.holdAmountForm[fieldName];
            event.target.value = value || "";
        },

        addFormatting(event, fieldName) {
            // When blurred, format the number for display
            const value = this.holdAmountForm[fieldName];
            if (value) {
                event.target.value = this.formatNumber(value);
            }
        },

        formatNumber(number) {
            if (!number) return "";
            return new Intl.NumberFormat("vi-VN").format(number);
        },
        openUpdateHoldAmountModal() {
            if (this.selectedPaymentRequests.length === 0) {
                Swal.fire({
                    icon: "warning",
                    title: "Chưa chọn phiếu đề nghị",
                    text: "Vui lòng chọn ít nhất một phiếu đề nghị để điều chỉnh Amount",
                });
                return;
            }

            // Reset form với giá trị số
            this.holdAmountForm = {
                don_gia_chat_mia: 0,
                don_gia_gap_mia: 0,
                don_gia_van_chuyen_mia: 0,
            };

            // Import Bootstrap vàสร้าง modal
            import("bootstrap/dist/js/bootstrap.bundle.min.js").then(
                (bootstrap) => {
                    const modalElement = document.getElementById(
                        "updateHoldAmountModal"
                    );
                    if (modalElement) {
                        // ตรวจสอบและ dispose modal เก่า
                        const existingModal = bootstrap.Modal.getInstance
                            ? bootstrap.Modal.getInstance(modalElement)
                            : null;

                        if (existingModal) {
                            existingModal.dispose();
                        }

                        // สร้าง modal ใหม่
                        this.$nextTick(() => {
                            const modal = new bootstrap.Modal(modalElement, {
                                backdrop: "static",
                                keyboard: false,
                                focus: true,
                            });

                            // เก็บ modal instance
                            this.modalInstances.push({
                                element: modalElement,
                                instance: modal,
                            });

                            modal.show();

                            // Focus on first input after modal is fully shown
                            modalElement.addEventListener(
                                "shown.bs.modal",
                                () => {
                                    const firstInput =
                                        document.getElementById(
                                            "donGiaChatMia"
                                        );
                                    if (firstInput) {
                                        firstInput.focus();
                                    }
                                },
                                { once: true }
                            );
                        });
                    }
                }
            );
        },

        calculateNewHoldAmount(actualReceived) {
            return (
                this.totalHarvestCostPerTon * parseFloat(actualReceived || 0)
            );
        },

        calculateNewRemainingAmount(item) {
            const newHoldAmount = this.calculateNewHoldAmount(
                item.actual_received
            );
            return (
                item.total_amount -
                newHoldAmount -
                item.total_deduction +
                item.total_interest
            );
        },

        async updateHoldAmount() {
            if (!this.isFormValid) {
                Swal.fire({
                    icon: "error",
                    title: "Dữ liệu không hợp lệ",
                    text: "Vui lòng nhập đúng các giá trị chi phí thu hoạch",
                });
                return;
            }

            try {
                this.updateHoldAmountLoading = true;

                const response = await axios.put(
                    "/api/disbursements-homgiong/update-hold-amount",
                    {
                        disbursement_codes: this.selectedPaymentRequests,
                        don_gia_chat_mia: this.holdAmountForm.don_gia_chat_mia,
                        don_gia_gap_mia: this.holdAmountForm.don_gia_gap_mia,
                        don_gia_van_chuyen_mia:
                            this.holdAmountForm.don_gia_van_chuyen_mia,
                    }
                );

                if (response.data.success) {
                    // Import Bootstrap และปิด modal
                    import("bootstrap/dist/js/bootstrap.bundle.min.js").then(
                        (bootstrap) => {
                            const modalElement = document.getElementById(
                                "updateHoldAmountModal"
                            );
                            if (modalElement) {
                                const modal = bootstrap.Modal.getInstance
                                    ? bootstrap.Modal.getInstance(modalElement)
                                    : new bootstrap.Modal(modalElement);

                                if (modal) {
                                    modal.hide();

                                    // รอให้ modal ปิดสมบูรณ์แล้วค่อย dispose
                                    modalElement.addEventListener(
                                        "hidden.bs.modal",
                                        () => {
                                            if (modal.dispose) {
                                                modal.dispose();
                                            }
                                        },
                                        { once: true }
                                    );
                                }
                            }
                        }
                    );

                    // Clear selections
                    this.selectedPaymentRequests = [];

                    // Reload payment requests data
                    await this.fetchPaymentRequests();

                    Swal.fire({
                        icon: "success",
                        title: "Cập nhật thành công!",
                        text: `Đã cập nhật ${response.data.updated_count} phiếu đề nghị`,
                        showConfirmButton: false,
                        timer: 2000,
                    });
                }
            } catch (error) {
                console.error("Error updating hold amount:", error);
                Swal.fire({
                    icon: "error",
                    title: "Lỗi cập nhật",
                    text:
                        error.response?.data?.message ||
                        "Có lỗi xảy ra khi cập nhật Amount",
                });
            } finally {
                this.updateHoldAmountLoading = false;
            }
        },
        async openPrintModal() {
            if (this.selectedPaymentRequests.length === 0) {
                Swal.fire({
                    title: "Thông báo",
                    text: "Vui lòng chọn ít nhất một phiếu để in",
                    icon: "warning",
                    confirmButtonText: "Đồng ý",
                });
                return;
            }

            // Show modal
            const modalElement = document.getElementById("printModal");
            if (modalElement) {
                this.printModal = new bootstrap.Modal(modalElement);
                this.printModal.show();
            }

            // Load preview data
            await this.loadPrintPreview();
        },

        closePrintModal() {
            if (this.printModal) {
                this.printModal.hide();
            }

            // Reset data
            this.printPreviewData = [];
            this.printPreviewError = null;
            this.printExecuting = false;
        },

        async loadPrintPreview() {
            this.printPreviewLoading = true;
            this.printPreviewError = null;

            try {
                const response = await axios.post(
                    "/api/print-preview-phieu-dntt",
                    {
                        disbursement_codes: this.selectedPaymentRequests,
                    },
                    {
                        headers: {
                            Authorization: `Bearer ${this.store.getToken}`,
                        },
                    }
                );

                if (response.data.success) {
                    this.printPreviewData = response.data.data || [];
                } else {
                    throw new Error(response.data.message || "Unknown error");
                }
            } catch (error) {
                console.error("Error loading print preview:", error);
                this.printPreviewError =
                    error.response?.data?.message ||
                    "Đã xảy ra lỗi khi tải danh sách in";
            } finally {
                this.printPreviewLoading = false;
            }
        },

        async executePrint() {
            this.printExecuting = true;

            try {
                // สร้าง URL สำหรับ API route
                const codes = this.selectedPaymentRequests.join(",");
                const printUrl = `/api/print-phieu-dntt?codes=${encodeURIComponent(
                    codes
                )}`;

                // เปิดหน้าใหม่
                window.open(printUrl, "_blank");

                // Show success message
                Swal.fire({
                    title: "Thành công!",
                    text: "Đã mở trang in trong tab mới",
                    icon: "success",
                    timer: 2000,
                    showConfirmButton: false,
                });

                // Close modal
                this.closePrintModal();
            } catch (error) {
                console.error("Error executing print:", error);
                Swal.fire({
                    title: "Lỗi!",
                    text: "Đã xảy ra lỗi khi in",
                    icon: "error",
                    confirmButtonText: "Đồng ý",
                });
            } finally {
                this.printExecuting = false;
            }
        },
        /**
         * Search individual customers
         */
        searchIndividualCustomers() {
            // Clear previous timeout
            if (this.individualSearchTimeout) {
                clearTimeout(this.individualSearchTimeout);
            }

            const query = this.newPaymentRequest.khach_hang_ca_nhan;

            if (!query || query.length < 2) {
                this.individualCustomerResults = [];
                this.showIndividualDropdown = false;
                return;
            }

            // Debounce search
            this.individualSearchTimeout = setTimeout(async () => {
                try {
                    const response = await axios.get(
                        "/api/search-individual-customers",
                        {
                            params: { query },
                            headers: {
                                Authorization: "Bearer " + this.store.getToken,
                            },
                        }
                    );

                    if (response.data.success) {
                        this.individualCustomerResults = response.data.data;
                        this.showIndividualDropdown = true;
                    }
                } catch (error) {
                    console.error(
                        "Error searching individual customers:",
                        error
                    );
                    this.individualCustomerResults = [];
                    this.showIndividualDropdown = false;
                }
            }, 300);
        },

        /**
         * Search corporate customers
         */
        searchCorporateCustomers() {
            // Clear previous timeout
            if (this.corporateSearchTimeout) {
                clearTimeout(this.corporateSearchTimeout);
            }

            const query = this.newPaymentRequest.khach_hang_doanh_nghiep;

            if (!query || query.length < 2) {
                this.corporateCustomerResults = [];
                this.showCorporateDropdown = false;
                return;
            }

            // Debounce search
            this.corporateSearchTimeout = setTimeout(async () => {
                try {
                    const response = await axios.get(
                        "/api/search-corporate-customers",
                        {
                            params: { query },
                            headers: {
                                Authorization: "Bearer " + this.store.getToken,
                            },
                        }
                    );

                    if (response.data.success) {
                        this.corporateCustomerResults = response.data.data;
                        this.showCorporateDropdown = true;
                    }
                } catch (error) {
                    console.error(
                        "Error searching corporate customers:",
                        error
                    );
                    this.corporateCustomerResults = [];
                    this.showCorporateDropdown = false;
                }
            }, 300);
        },

        /**
         * Select individual customer
         */
        selectIndividualCustomer(customer) {
            this.newPaymentRequest.khach_hang_ca_nhan =
                customer.khach_hang_ca_nhan;
            this.newPaymentRequest.ma_khach_hang_ca_nhan =
                customer.ma_kh_ca_nhan;
            this.individualCustomerResults = [];
            this.showIndividualDropdown = false;
        },

        /**
         * Select corporate customer
         */
        selectCorporateCustomer(customer) {
            this.newPaymentRequest.khach_hang_doanh_nghiep =
                customer.khach_hang_doanh_nghiep;
            this.newPaymentRequest.ma_khach_hang_doanh_nghiep =
                customer.ma_kh_doanh_nghiep;
            this.corporateCustomerResults = [];
            this.showCorporateDropdown = false;
        },

        /**
         * Hide dropdowns when clicking outside
         */
        hideDropdowns() {
            this.showIndividualDropdown = false;
            this.showCorporateDropdown = false;
        },

        /**
         * Open Payment Requests Modal
         */
        openPaymentRequestsModal() {
            if (!this.paymentRequestsModal) {
                import("bootstrap/dist/js/bootstrap.bundle.min.js").then(
                    (bootstrap) => {
                        const modalElement = document.getElementById(
                            "paymentRequestsModal"
                        );
                        if (modalElement) {
                            this.paymentRequestsModal = new bootstrap.Modal(
                                modalElement,
                                {
                                    backdrop: "static",
                                    keyboard: false,
                                }
                            );

                            // เก็บ modal instance
                            this.modalInstances.push({
                                element: modalElement,
                                instance: this.paymentRequestsModal,
                            });

                            this.paymentRequestsModal.show();
                            this.fetchPaymentRequests();
                        }
                    }
                );
            } else {
                this.paymentRequestsModal.show();
                this.fetchPaymentRequests();
            }
        },

        /**
         * Close Payment Requests Modal
         */
        closePaymentRequestsModal() {
            if (this.paymentRequestsModal) {
                this.paymentRequestsModal.hide();
            }
        },

        /**
         * Perform search in payment requests modal
         */
        performPaymentSearch() {
            // Reset to first page when searching
            this.currentPaymentPage = 1;

            // Clear search highlight cache
            this.paymentSearchHighlightCache = {};

            // Auto-clear selection when searching
            if (this.paymentSearchQuery.trim()) {
                this.selectedPaymentRequests = [];
            }
        },

        /**
         * Clear payment search
         */
        clearPaymentSearch() {
            this.paymentSearchQuery = "";
            this.currentPaymentPage = 1;
            this.paymentSearchHighlightCache = {};
        },

        /**
         * Apply payment quick filter
         */
        applyPaymentQuickFilter(filterType) {
            if (this.paymentQuickFilter === filterType) {
                this.paymentQuickFilter = null;
            } else {
                this.paymentQuickFilter = filterType;
            }
            this.currentPaymentPage = 1;
            this.selectedPaymentRequests = [];
        },

        /**
         * Clear payment quick filters
         */
        clearPaymentQuickFilters() {
            this.paymentQuickFilter = null;
            this.currentPaymentPage = 1;
        },

        /**
         * Reset all payment filters and search
         */
        resetAllPaymentFiltersAndSearch() {
            // Clear search
            this.clearPaymentSearch();

            // Clear quick filters
            this.clearPaymentQuickFilters();

            // Clear column filters
            this.resetPaymentRequestFilters();

            // Clear selection
            this.selectedPaymentRequests = [];
        },

        /**
         * Highlight search term in payment text
         */
        highlightPaymentSearchTerm(text) {
            if (
                !text ||
                !this.paymentSearchQuery ||
                !this.paymentSearchQuery.trim()
            ) {
                return text;
            }

            const searchTerm = this.paymentSearchQuery.trim();
            const cacheKey = `${text}_${searchTerm}`;

            if (this.paymentSearchHighlightCache[cacheKey]) {
                return this.paymentSearchHighlightCache[cacheKey];
            }

            const regex = new RegExp(
                `(${this.escapeRegExp(searchTerm)})`,
                "gi"
            );
            const highlighted = text.replace(
                regex,
                '<mark class="search-highlight-text">$1</mark>'
            );

            this.paymentSearchHighlightCache[cacheKey] = highlighted;
            return highlighted;
        },

        /**
         * Check if payment item should be highlighted
         */
        isPaymentHighlighted(item) {
            if (!this.paymentSearchQuery || !this.paymentSearchQuery.trim()) {
                return false;
            }

            const searchTerm = this.paymentSearchQuery.trim().toLowerCase();
            return (
                (item.disbursement_code &&
                    item.disbursement_code
                        .toLowerCase()
                        .includes(searchTerm)) ||
                (item.individual_customer &&
                    item.individual_customer
                        .toLowerCase()
                        .includes(searchTerm)) ||
                (item.corporate_customer &&
                    item.corporate_customer
                        .toLowerCase()
                        .includes(searchTerm)) ||
                (item.investment_project &&
                    item.investment_project.toLowerCase().includes(searchTerm))
            );
        },

        /**
         * Export payment requests to Excel
         */
        exportPaymentRequestsToExcel() {
            // Create workbook and worksheet
            const wb = XLSX.utils.book_new();

            // Prepare data for export
            const exportData = this.filteredPaymentRequests.map(
                (item, index) => ({
                    STT: index + 1,
                    "Mã giải ngân": item.disbursement_code,
                    "Vụ đầu tư": item.investment_project,
                    "Loại thanh toán": item.payment_type,
                    "Khách hàng cá nhân": item.individual_customer || "",
                    "Mã KH cá nhân": item.individual_customer_code || "",
                    "Khách hàng doanh nghiệp": item.corporate_customer || "",
                    "Mã KH doanh nghiệp": item.corporate_customer_code || "",
                    "Tổng tiền": item.total_amount,
                    "Tổng tiền tạm giữ": item.total_hold_amount,
                    "Tổng tiền khấu trừ": item.total_deduction,
                    "Tổng tiền lãi suất": item.total_interest,
                    "Tổng tiền còn lại": item.total_remaining,
                    "Ngày thanh toán": this.formatDate(item.payment_date),
                    "Số tờ trình": item.proposal_number,
                    "Đợt thanh toán": item.installment,
                })
            );

            // Create worksheet
            const ws = XLSX.utils.json_to_sheet(exportData);

            // Add worksheet to workbook
            XLSX.utils.book_append_sheet(wb, ws, "Phiếu đề nghị thanh toán");

            // Generate filename
            const filename = `Phieu_de_nghi_thanh_toan_${
                this.document.payment_code
            }_${new Date().toISOString().slice(0, 10)}.xlsx`;

            // Save file
            XLSX.writeFile(wb, filename);

            // Show success message
            this.showSuccess("Xuất Excel thành công!");
        },
        /**
         * Perform search in modal
         */
        performModalSearch() {
            // Reset to first page when searching
            this.currentPage = 1;

            // Clear search highlight cache
            this.searchHighlightCache = {};

            // Auto-clear selection when searching
            if (this.modalSearchQuery.trim()) {
                this.selectedRecords = [];
            }
        },

        /**
         * Clear modal search
         */
        clearModalSearch() {
            this.modalSearchQuery = "";
            this.currentPage = 1;
            this.searchHighlightCache = {};
        },

        /**
         * Apply quick filter
         */
        applyQuickFilter(filterType) {
            if (this.quickFilter === filterType) {
                this.quickFilter = null;
            } else {
                this.quickFilter = filterType;
            }
            this.currentPage = 1;
            this.selectedRecords = [];
        },

        /**
         * Clear quick filters
         */
        clearQuickFilters() {
            this.quickFilter = null;
            this.currentPage = 1;
        },

        /**
         * Reset all filters and search
         */
        resetAllFiltersAndSearch() {
            // Clear search
            this.clearModalSearch();

            // Clear quick filters
            this.clearQuickFilters();

            // Clear column filters
            this.resetAllFilters();

            // Clear selection
            this.selectedRecords = [];
        },

        /**
         * Highlight search term in text
         */
        highlightSearchTerm(text) {
            if (
                !text ||
                !this.modalSearchQuery ||
                !this.modalSearchQuery.trim()
            ) {
                return text;
            }

            const searchTerm = this.modalSearchQuery.trim();
            const cacheKey = `${text}_${searchTerm}`;

            if (this.searchHighlightCache[cacheKey]) {
                return this.searchHighlightCache[cacheKey];
            }

            const regex = new RegExp(
                `(${this.escapeRegExp(searchTerm)})`,
                "gi"
            );
            const highlighted = text.replace(
                regex,
                '<mark class="search-highlight-text">$1</mark>'
            );

            this.searchHighlightCache[cacheKey] = highlighted;
            return highlighted;
        },

        /**
         * Escape special regex characters
         */
        escapeRegExp(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
        },

        /**
         * Check if item should be highlighted
         */
        isHighlighted(item) {
            if (!this.modalSearchQuery || !this.modalSearchQuery.trim()) {
                return false;
            }

            const searchTerm = this.modalSearchQuery.trim().toLowerCase();
            return (
                (item.document_code &&
                    item.document_code.toLowerCase().includes(searchTerm)) ||
                (item.title && item.title.toLowerCase().includes(searchTerm)) ||
                (item.khach_hang_ca_nhan_dt_mia &&
                    item.khach_hang_ca_nhan_dt_mia
                        .toLowerCase()
                        .includes(searchTerm)) ||
                (item.khach_hang_doanh_nghiep_dt_mia &&
                    item.khach_hang_doanh_nghiep_dt_mia
                        .toLowerCase()
                        .includes(searchTerm))
            );
        },
        openPaymentDetailsModal() {
            if (!this.paymentDetailsModal) {
                import("bootstrap/dist/js/bootstrap.bundle.min.js").then(
                    (bootstrap) => {
                        const modalElement = document.getElementById(
                            "paymentDetailsModal"
                        );
                        if (modalElement) {
                            this.paymentDetailsModal = new bootstrap.Modal(
                                modalElement,
                                {
                                    backdrop: "static",
                                    keyboard: false,
                                }
                            );

                            // เก็บ modal instance
                            this.modalInstances.push({
                                element: modalElement,
                                instance: this.paymentDetailsModal,
                            });

                            this.paymentDetailsModal.show();
                        }
                    }
                );
            } else {
                this.paymentDetailsModal.show();
            }
        },

        /**
         * Close Payment Details Modal
         */
        closePaymentDetailsModal() {
            if (this.paymentDetailsModal) {
                this.paymentDetailsModal.hide();
            }
        },

        // ...existing methods...

        // เพิ่ม helper method สำหรับตรวจสอบ authentication
        isAuthenticated() {
            return this.store.getToken && this.store.getToken.length > 0;
        },
        // Add this method in the methods section
        confirmPaymentStatus() {
            // Check if payment date is selected
            if (!this.document.payment_date) {
                Swal.fire({
                    title: "Cảnh báo",
                    text: "Vui lòng nhập ngày thanh toán trước khi đánh dấu là đã thanh toán",
                    icon: "warning",
                    confirmButtonText: "OK",
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: "btn btn-success",
                    },
                }).then(() => {
                    // After the user closes the alert, focus on the payment date field
                    this.$nextTick(() => {
                        // Focus on the payment date input
                        if (this.$refs.paymentDateInput) {
                            this.$refs.paymentDateInput.focus();

                            // Scroll the field into view if needed
                            this.$refs.paymentDateInput.scrollIntoView({
                                behavior: "smooth",
                                block: "center",
                            });
                        }
                    });
                });
                return;
            }

            // If payment date exists, proceed with confirmation
            this.confirmUpdateStatus(
                "paid",
                "Xác nhận đã thanh toán?",
                "Bạn có chắc chắn muốn đánh dấu phiếu này là đã thanh toán không?"
            );
        },
        // Add this method to fetch the processing history

        async fetchProcessingHistory() {
            if (!this.isAuthenticated()) return;
            try {
                const response = await axios.get(
                    `/api/payment-requests-homgiong/${this.document.payment_code}/history`,
                    {
                        headers: {
                            Authorization: "Bearer " + this.store.getToken,
                        },
                    }
                );

                if (response.data.success) {
                    this.processingHistory = response.data.history;

                    // อัปเดตสถานะของเอกสารตามข้อมูลล่าสุด
                    if (
                        this.processingHistory &&
                        this.processingHistory.length > 0
                    ) {
                        // จัดเรียงข้อมูลตามวันที่จากใหม่ไปเก่า
                        const sortedHistory = [...this.processingHistory].sort(
                            (a, b) =>
                                new Date(b.created_at) - new Date(a.created_at)
                        );

                        // นำสถานะล่าสุดมากำหนดเป็นสถานะของเอกสาร
                        const latestStatus = sortedHistory[0].action;
                        console.log(
                            "Latest status from history:",
                            latestStatus
                        );

                        // อัพเดทสถานะเฉพาะเมื่อมีค่าและเป็นค่าที่แตกต่างจากเดิม
                        if (
                            latestStatus &&
                            this.document.status !== latestStatus
                        ) {
                            this.document.status = latestStatus;
                            console.log(
                                "Document status updated to:",
                                this.document.status
                            );
                            this.$forceUpdate();
                        }
                    }

                    // If timeline is being shown, load user data
                    if (
                        this.showTimeline &&
                        this.processingHistory.length > 0
                    ) {
                        this.loadUsers();
                    }
                } else {
                    console.error("Failed to fetch processing history");
                }
            } catch (error) {
                console.error("Error fetching processing history:", error);
                if (error.response?.status === 401) {
                    this.handleAuthError();
                }
            }
        },
        // Toggle timeline view
        toggleTimelineView() {
            this.showTimeline = !this.showTimeline;

            // If showing timeline and no history data yet, fetch it
            if (
                this.showTimeline &&
                (!this.processingHistory || this.processingHistory.length === 0)
            ) {
                this.fetchProcessingHistory();
            }

            // If showing timeline and users need to be loaded, do it
            if (
                this.showTimeline &&
                this.processingHistory &&
                this.processingHistory.length > 0 &&
                Object.keys(this.users).length === 0
            ) {
                this.loadUsers();
            }
        },
        // Load user data for timeline
        async loadUsers() {
            if (!this.isAuthenticated()) return;
            const userIds = this.processingHistory
                .map((item) => item.action_by)
                .filter((id) => id && !this.users[id]);

            if (userIds.length === 0) return;

            try {
                const response = await axios.post(
                    "/api/users/get-by-ids",
                    {
                        user_ids: [...new Set(userIds)], // Remove duplicates
                    },
                    {
                        headers: {
                            Authorization: "Bearer " + this.store.getToken,
                        },
                    }
                );

                if (response.data.success) {
                    const newUsers = response.data.users;
                    // Use direct property assignment instead of this.$set
                    newUsers.forEach((user) => {
                        this.users[user.id] = user;
                    });
                }
            } catch (error) {
                console.error("Error loading user data:", error);
            }
        },

        // Get user name from cache
        getUserName(userId) {
            if (!userId) return "Unknown";

            if (this.users[userId]) {
                return this.users[userId].full_name || "User ID: " + userId;
            }

            return "Loading...";
        },

        // ... existing methods
        // Add this to your Vue component's methods section
        async deleteDocument() {
            if (!this.isAuthenticated()) return;
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
                    `/api/payment-requests-homgiong/${this.document.payment_code}`,
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
                    this.$router.push("/PhieutrinhthanhtoanHomgiong");
                } else {
                    throw new Error(
                        response.data.message ||
                            "Không thể xóa phiếu trình thanh toán"
                    );
                }
            } catch (error) {
                console.error("Error deleting document:", error);

                Swal.fire({
                    title: "Lỗi!",
                    text:
                        error.response?.data?.message ||
                        "Đã xảy ra lỗi khi xóa phiếu trình thanh toán",
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
                input: "textarea",
                inputPlaceholder: "Thêm ghi chú (không bắt buộc)",
                inputAttributes: {
                    "aria-label": "Ghi chú",
                },
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
                    this.updateStatus(status, result.value || ""); // Pass note text
                }
            });
        },

        /**
         * Update payment request status
         */
        async updateStatus(status, note = "") {
            if (!this.isAuthenticated()) return;
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
                // Create request payload
                const payload = {
                    status: status,
                    action_notes: note,
                    // Add financial fields
                    tong_tien: this.totalPaymentAmount,
                    tong_tien_tam_giu: this.totalHoldAmount,
                    tong_tien_khau_tru: this.totalDeductionAmount,
                    tong_tien_lai_suat: this.totalInterestAmount,
                    tong_tien_thanh_toan_con_lai: this.totalRemainingAmount,
                };

                // Add payment_date to payload if status is 'paid'
                if (status === "paid") {
                    payload.payment_date = this.document.payment_date;
                }

                const response = await axios.put(
                    `/api/payment-requests-homgiong/${this.document.payment_code}/status`,
                    payload,
                    {
                        headers: {
                            Authorization: "Bearer " + this.store.getToken,
                        },
                    }
                );

                if (response.data.success) {
                    // Update local document status
                    this.document.status = status;

                    // ใช้ nextTick เพื่อให้แน่ใจว่า DOM จะถูกอัปเดตหลังจากการเปลี่ยนแปลงข้อมูล
                    this.$nextTick(() => {
                        // ดึงข้อมูลประวัติใหม่จาก API
                        this.fetchProcessingHistory();
                    });

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
                    // *** อัปเดต payment date ใน payment requests modal ***
                    this.updatePaymentRequestsModalPaymentDate();

                    // ดึงข้อมูลเอกสารทั้งหมดใหม่เพื่อให้แน่ใจว่าทุกส่วนอัปเดต
                    this.fetchDocument();
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
        async saveBasicInfo() {
            if (!this.isAuthenticated()) return;
            try {
                const response = await axios.put(
                    `/api/payment-requests-homgiong/${this.document.payment_code}/basic-info`,
                    {
                        so_to_trinh: this.document.proposal_number,
                        ngay_tao: this.document.created_at,
                        so_dot_thanh_toan: this.document.payment_installment,
                        loai_thanh_toan: this.document.payment_type,
                        vu_dau_tu: this.document.investment_project,
                        ngay_thanh_toan: this.document.payment_date,
                        // Add financial fields
                        tong_tien: this.totalPaymentAmount,
                        tong_tien_tam_giu: this.totalHoldAmount,
                        tong_tien_khau_tru: this.totalDeductionAmount,
                        tong_tien_lai_suat: this.totalInterestAmount,
                        tong_tien_thanh_toan_con_lai: this.totalRemainingAmount,
                    },
                    {
                        headers: {
                            Authorization: "Bearer " + this.store.getToken,
                        },
                    }
                );

                if (response.data.success) {
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
                    // *** NEW: อัปเดต payment date ใน payment requests modal ***
                    this.updatePaymentRequestsModalPaymentDate();

                    // Update local data if needed
                    this.fetchDocument(); // Refresh data to ensure payment_date is updated
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
            } catch (error) {
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
            }
        },
        // *** NEW METHOD: อัปเดต payment date ใน payment requests modal ***
        updatePaymentRequestsModalPaymentDate() {
            if (this.paymentRequests && this.paymentRequests.length > 0) {
                this.paymentRequests.forEach((request) => {
                    if (
                        request.ma_trinh_thanh_toan ===
                        this.document.payment_code
                    ) {
                        request.ngay_thanh_toan = this.document.payment_date;
                        request.payment_date_formatted = this.document
                            .payment_date
                            ? this.formatDate(this.document.payment_date)
                            : "Chưa có";
                    }
                });
            }
        },
        async fetchInvestmentProjects() {
            if (!this.isAuthenticated()) return;
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
                ma_trinh_thanh_toan: this.document.payment_code,
                vu_dau_tu: this.document.investment_project,
                loai_thanh_toan: this.document.payment_type,
                khach_hang_ca_nhan: "",
                ma_khach_hang_ca_nhan: "",
                khach_hang_doanh_nghiep: "",
                ma_khach_hang_doanh_nghiep: "",
            };

            // Reset customer search results
            this.individualCustomerResults = [];
            this.corporateCustomerResults = [];
            this.showIndividualDropdown = false;
            this.showCorporateDropdown = false;

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
            if (!this.isAuthenticated()) return;
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
                    `/api/disbursements-homgiong/with-receipts`,
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

            // Reset search results
            this.editIndividualCustomerResults = [];
            this.editCorporateCustomerResults = [];
            this.showEditIndividualDropdown = false;
            this.showEditCorporateDropdown = false;
            this.showEditValidationSummary = false;

            // If only one record is selected, populate the form with its data
            if (this.selectedPaymentRequests.length === 1) {
                const selectedCode = this.selectedPaymentRequests[0];
                const selectedRecord = this.paymentRequests.find(
                    (item) => item.disbursement_code === selectedCode
                );

                if (selectedRecord) {
                    this.paymentEditForm = {
                        ma_giai_ngan: selectedRecord.disbursement_code || "",
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
            }

            // Show the modal
            if (!this.paymentEditModal) {
                import("bootstrap/dist/js/bootstrap.bundle.min.js").then(
                    (bootstrap) => {
                        const modalElement =
                            document.getElementById("paymentEditModal");
                        if (modalElement) {
                            this.paymentEditModal = new bootstrap.Modal(
                                modalElement,
                                {
                                    backdrop: "static",
                                    keyboard: false,
                                }
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
            if (!this.isAuthenticated()) return;
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
                // Add financial data to the request
                const financialData = {
                    tong_tien: this.document.total_amount,
                    tong_tien_tam_giu: this.document.total_hold_amount,
                    tong_tien_khau_tru: this.document.total_deduction,
                    tong_tien_lai_suat: this.document.total_interest,
                    tong_tien_thanh_toan_con_lai: this.document.total_remaining,
                };

                const response = await axios.put(
                    `/api/disbursements-homgiong/bulk`,
                    {
                        disbursement_codes: this.selectedPaymentRequests,
                        update_data: this.paymentEditForm,
                        financial_data: financialData, // Add financial data
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
            if (!this.isAuthenticated()) return;
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
                        `/api/disbursements-homgiong/bulk`,
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
                        this.fetchDocument(); // Refresh document details as well
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
            if (!this.isAuthenticated()) return;
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
                    "/api/disbursements-homgiong/import",
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
            if (!this.isAuthenticated()) return;

            this.paymentRequestsLoading = true;
            try {
                const response = await axios.get(
                    `/api/payment-requests/${this.document.payment_code}/disbursements-homgiong`
                );

                if (response.data.success) {
                    // อัปเดต payment requests และซิงค์ payment date จาก document หลัก
                    this.paymentRequests = response.data.data.map(
                        (request) => ({
                            ...request,
                            ngay_thanh_toan:
                                request.ngay_thanh_toan ||
                                this.document.payment_date, // ซิงค์จาก document หลัก
                            payment_date_formatted:
                                request.ngay_thanh_toan ||
                                this.document.payment_date
                                    ? this.formatDate(
                                          request.ngay_thanh_toan ||
                                              this.document.payment_date
                                      )
                                    : "Chưa có",
                        })
                    );

                    this.updatePaymentFilterValues();
                } else {
                    console.error(
                        "Failed to fetch payment requests:",
                        response.data.message
                    );
                    this.paymentRequests = [];
                }
            } catch (error) {
                console.error("Error fetching payment requests:", error);
                this.paymentRequests = [];

                if (error.response?.status === 401) {
                    this.handleAuthError();
                }
            } finally {
                this.paymentRequestsLoading = false;
            }
        },

        fetchUserData() {
            const user = localStorage.getItem("web_user");
            if (user) {
                this.user = JSON.parse(user);
            }
        },
        fetchDocument() {
            if (!this.isAuthenticated()) return;
            const id = this.$route.params.id;
            if (!id) {
                this.showError("Không tìm thấy mã phiếu trình");
                return;
            }

            this.isLoading = true;
            axios
                .get(`/api/payment-requests-homgiong/${id}/details`, {
                    headers: {
                        Authorization: "Bearer " + this.store.getToken,
                    },
                })
                .then((response) => {
                    if (response.data.success) {
                        // Map main document data
                        let latestAction = "";

                        // Store processing history if it exists in the response
                        if (response.data.processingHistory) {
                            this.processingHistory =
                                response.data.processingHistory;

                            // ดึง action ล่าสุดจากประวัติการดำเนินการ
                            if (this.processingHistory.length > 0) {
                                const sortedHistory = [
                                    ...this.processingHistory,
                                ].sort(
                                    (a, b) =>
                                        new Date(b.created_at) -
                                        new Date(a.created_at)
                                );
                                latestAction = sortedHistory[0].action;
                            }
                        } else {
                            this.fetchProcessingHistory();
                        }

                        this.document = {
                            payment_code:
                                response.data.document.payment_code || "",
                            title: response.data.document.title || "",
                            investment_project:
                                response.data.document.investment_project || "",
                            payment_type:
                                response.data.document.payment_type || "",
                            status:
                                latestAction ||
                                response.data.document.status ||
                                "processing",
                            payment_installment:
                                response.data.document.payment_installment || 0,
                            proposal_number:
                                response.data.document.proposal_number || "",
                            created_at:
                                response.data.document.created_at || null,
                            payment_date:
                                response.data.document.payment_date || "",
                            total_amount:
                                response.data.document.total_amount || 0,
                            creator_name:
                                response.data.document.creator_name || "",
                            notes: response.data.document.notes || "",
                            // Add financial fields
                            total_hold_amount:
                                response.data.document.total_hold_amount || 0,
                            total_deduction:
                                response.data.document.total_deduction || 0,
                            total_interest:
                                response.data.document.total_interest || 0,
                            total_remaining:
                                response.data.document.total_remaining || 0,
                        };

                        this.noteText = this.document.notes || "";

                        // Map payment details สำหรับ bien_ban_nghiem_thu_hom_giong
                        // เพิ่ม disbursement_code และ installment
                        this.paymentDetails = Array.isArray(
                            response.data.paymentDetails
                        )
                            ? response.data.paymentDetails.map((item) => ({
                                  document_code: item.document_code || "",
                                  document_type:
                                      "Biên bản nghiệm thu hom giống",
                                  tram: item.tram || "",
                                  investment_project:
                                      item.investment_project || "",
                                  title: item.title || "",
                                  hop_dong_dau_tu_mia_ben_giao_hom:
                                      item.hop_dong_dau_tu_mia_ben_giao_hom ||
                                      "",
                                  tong_thuc_nhan: item.tong_thuc_nhan || 0,
                                  amount: item.amount || 0,
                                  disbursement_code:
                                      item.disbursement_code || "",
                                  installment: item.installment || 1,
                              }))
                            : [];

                        // Fetch disbursements for homgiong (implement in next step)
                        // เพิ่มการ fetch payment requests ที่นี่
                        this.fetchPaymentRequests(); // เพิ่มบรรทัดนี้
                    } else {
                        throw new Error(
                            response.data.message ||
                                "Không tìm thấy thông tin phiếu trình thanh toán hom giống"
                        );
                    }
                })
                .catch((error) => {
                    console.error("Error fetching document:", error);
                    this.showError(
                        "Lỗi khi tải thông tin phiếu trình thanh toán hom giống"
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

        getStatusIcon(status) {
            if (status === "paid") return "fa-check-circle";
            if (status === "submitted") return "fa-paper-plane";
            if (status === "processing") return "fa-spinner fa-spin";
            if (status === "cancelled") return "fa-times-circle";
            return "fa-question-circle";
        },

        statusClass(status) {
            if (status === "paid") return "text-success status-paid";
            if (status === "submitted") return "text-primary status-submitted";
            if (status === "processing")
                return "text-warning status-processing";
            if (status === "cancelled") return "text-danger status-cancelled";
            return "";
        },

        saveNote() {
            if (!this.isAuthenticated()) return;

            // Set loading state
            this.isSavingNote = true;

            axios
                .post(
                    `/api/payment-requests-homgiong/${this.document.payment_code}/notes`,
                    {
                        note: this.noteText,
                    },
                    {
                        headers: {
                            Authorization: `Bearer ${this.store.getToken}`,
                            "Content-Type": "application/json",
                        },
                    }
                )
                .then((response) => {
                    if (response.data.success) {
                        this.showSuccess(
                            response.data.message ||
                                "Ghi chú đã được lưu thành công"
                        );
                        this.document.notes = this.noteText;
                        this.isEditingNote = false;
                    } else {
                        this.showError(
                            response.data.message || "Lỗi khi lưu ghi chú"
                        );
                    }
                })
                .catch((error) => {
                    console.error("Error saving note:", error);

                    if (error.response?.data?.message) {
                        this.showError(error.response.data.message);
                    } else if (error.response?.data?.errors) {
                        // Handle validation errors
                        const errors = Object.values(
                            error.response.data.errors
                        ).flat();
                        this.showError(errors.join(", "));
                    } else {
                        this.showError("Lỗi khi lưu ghi chú");
                    }

                    if (error.response?.status === 401) {
                        this.handleAuthError();
                    }
                })
                .finally(() => {
                    this.isSavingNote = false;
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

            // Reset form
            this.editForm = {
                disbursementCode: "",
            };

            // Show the modal
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
            if (!this.isAuthenticated()) return;
            if (this.selectedRecords.length === 0) return;

            this.isUpdating = true;

            try {
                const response = await axios.put(
                    `/api/payment-requests-homgiong/${this.document.payment_code}/records`,
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

        /**
         * Delete selected records from payment request homgiong
         */
        async deleteSelectedRecords() {
            if (!this.isAuthenticated()) return;
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

            if (!result.isConfirmed) return;

            // ตรวจสอบข้อมูลก่อนส่ง
            console.log("Selected records to delete:", this.selectedRecords);

            this.isDeleting = true;

            try {
                // แก้ไขการส่งข้อมูลใน axios.delete - ย้าย data ไปใน config object
                const response = await axios.delete(
                    `/api/payment-requests-homgiong/${this.document.payment_code}/records`,
                    {
                        data: {
                            receipt_ids: this.selectedRecords,
                        },
                        headers: {
                            Authorization: "Bearer " + this.store.getToken,
                            "Content-Type": "application/json",
                        },
                    }
                );

                if (response.data.success) {
                    // Remove deleted records from the local array
                    this.paymentDetails = this.paymentDetails.filter(
                        (item) =>
                            !this.selectedRecords.includes(item.document_code)
                    );

                    // Update total amount if provided
                    if (response.data.new_total_amount !== undefined) {
                        this.document.total_amount =
                            response.data.new_total_amount;
                    }

                    // Clear selection
                    this.selectedRecords = [];

                    this.showSuccess(
                        `Đã xóa thành công ${response.data.affected_rows} bản ghi. ` +
                            `Tổng tiền mới: ${this.formatCurrency(
                                response.data.new_total_amount || 0
                            )}`
                    );

                    this.fetchDocument(); // Refresh data
                } else {
                    throw new Error(response.data.message || "Unknown error");
                }
            } catch (error) {
                console.error("Error deleting records:", error);

                if (error.response && error.response.data) {
                    console.error("Server response:", error.response.data);

                    if (
                        error.response.data.errors &&
                        error.response.data.errors.receipt_ids
                    ) {
                        this.showError(
                            "Lỗi: " + error.response.data.errors.receipt_ids[0]
                        );
                    } else {
                        this.showError(
                            error.response.data.message ||
                                "Đã xảy ra lỗi khi xóa bản ghi"
                        );
                    }
                } else {
                    this.showError("Đã xảy ra lỗi khi xóa bản ghi");
                }

                if (error.response?.status === 401) {
                    this.handleAuthError();
                }
            } finally {
                this.isDeleting = false;
            }
        },

        /**
         * Show confirmation dialog for bulk delete
         */
        confirmBulkDelete() {
            if (this.selectedRecords.length === 0) {
                this.showError("Vui lòng chọn ít nhất một bản ghi để xóa");
                return;
            }

            // Get selected records details for confirmation
            const selectedDetails = this.paymentDetails.filter((item) =>
                this.selectedRecords.includes(item.document_code)
            );

            const totalAmount = selectedDetails.reduce(
                (sum, item) => sum + parseFloat(item.amount || 0),
                0
            );

            Swal.fire({
                title: "Xác nhận xóa bản ghi",
                html: `
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Bạn đang chuẩn bị xóa <strong>${
                            this.selectedRecords.length
                        }</strong> bản ghi
                    </div>
                    <div class="mb-3">
                        <strong>Danh sách bản ghi sẽ xóa:</strong><br>
                        <ul class="text-start">
                            ${selectedDetails
                                .map(
                                    (item) =>
                                        `<li>${
                                            item.document_code
                                        } - ${this.formatCurrency(
                                            item.amount || 0
                                        )}</li>`
                                )
                                .join("")}
                        </ul>
                    </div>
                    <div class="mb-3">
                        <strong>Tổng tiền các bản ghi sẽ xóa:</strong><br>
                        <span class="text-danger fs-5">${this.formatCurrency(
                            totalAmount
                        )}</span>
                    </div>
                    <div class="text-muted">
                        <small>Hành động này sẽ cập nhật lại tổng tiền của phiếu trình và không thể hoàn tác.</small>
                    </div>
                `,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#dc3545",
                cancelButtonColor: "#6c757d",
                confirmButtonText:
                    '<i class="fas fa-trash me-1"></i> Xóa bản ghi',
                cancelButtonText: '<i class="fas fa-times me-1"></i> Hủy',
                reverseButtons: true,
                width: "600px",
            }).then((result) => {
                if (result.isConfirmed) {
                    this.deleteSelectedRecords();
                }
            });
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
            if (!this.isAuthenticated()) return;
            if (!this.searchQuery || this.searchQuery.length < 2) {
                this.receiptResults = [];
                return;
            }

            try {
                this.isSearching = true; // Add a loading state

                const response = await axios.get(
                    "/api/bienban-nghiemthu-homgiong-search-pttt",
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
                    // Map the response to include all required fields for homgiong
                    this.receiptResults = response.data.map((item) => ({
                        ma_so_phieu: item.ma_so_phieu,
                        tram: item.tram,
                        vu_dau_tu: item.vu_dau_tu,
                        ten_phieu: item.ten_phieu,
                        hop_dong_dau_tu_mia_ben_giao_hom:
                            item.hop_dong_dau_tu_mia_ben_giao_hom,
                        tong_thuc_nhan: item.tong_thuc_nhan || 0,
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
                (selected) => selected.ma_so_phieu === item.ma_so_phieu
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
            if (!this.isAuthenticated()) return;
            if (this.selectedReceipts.length === 0) return;

            this.isAdding = true;

            try {
                const response = await axios.post(
                    `/api/payment-requests-homgiong/${this.document.payment_code}/receipts`,
                    {
                        receipt_ids: this.selectedReceipts.map(
                            (item) => item.ma_so_phieu
                        ),
                    },
                    {
                        headers: {
                            Authorization: "Bearer " + this.store.getToken,
                        },
                    }
                );

                if (response.data.success) {
                    this.showSuccess("Thêm biên bản thành công!");

                    // Update total amount
                    this.document.total_amount = response.data.new_total_amount;

                    // Clear selections
                    this.selectedReceipts = [];
                    this.searchQuery = "";
                    this.receiptResults = [];

                    // Close modal
                    if (this.addReceiptModal) {
                        this.addReceiptModal.hide();
                    }

                    await this.fetchDocument();
                } else {
                    this.showError(
                        response.data.message ||
                            "Có lỗi xảy ra khi thêm biên bản"
                    );
                }
            } catch (error) {
                console.error("Error adding receipts:", error);

                if (error.response?.status === 401) {
                    this.handleAuthError();
                } else if (error.response?.data?.message) {
                    this.showError(error.response.data.message);
                } else {
                    this.showError("Có lỗi xảy ra khi thêm biên bản");
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

                // Format the data for export - updated for homgiong payment details
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
                XLSX.utils.book_append_sheet(
                    wb,
                    ws,
                    "Chi tiết hồ sơ thanh toán"
                );

                // Generate filename with document code and date
                const fileName = `ChiTietHoSoThanhToan_${
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

            // Create translated headers and mapped data for homgiong payment details
            return dataToExport.map((item, index) => {
                return {
                    STT: index + 1,
                    "Mã số phiếu": item.document_code || "N/A",
                    Trạm: item.tram || "N/A",
                    "Vụ đầu tư": item.investment_project || "N/A",
                    "Tên phiếu": item.title || "N/A",
                    "Hợp đồng đầu tư mía bên giao":
                        item.hop_dong_dau_tu_mia_ben_giao_hom || "N/A",
                    "Tổng thực nhận": this.formatCurrencyRaw(
                        item.tong_thuc_nhan || 0
                    ),
                    "Mã đề nghị giải ngân": item.disbursement_code || "N/A",
                    "Đợt TT": item.installment || 1,
                    "Số tiền": this.formatCurrencyRaw(item.amount || 0),
                };
            });
        },

        calculateColumnWidths(data) {
            // Base column widths updated for homgiong payment details
            const baseWidths = {
                STT: 8,
                "Mã số phiếu": 20,
                Trạm: 15,
                "Vụ đầu tư": 20,
                "Tên phiếu": 35,
                "Hợp đồng đầu tư mía bên giao": 30,
                "Tổng thực nhận": 18,
                "Mã đề nghị giải ngân": 25,
                "Đợt TT": 10,
                "Số tiền": 18,
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
            if (!this.isAuthenticated()) return;
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
                    `/api/payment-requests-homgiong/${this.document.payment_code}/import`,
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
                        ma_so_phieu: "Example-001",
                        ma_de_nghi_giai_ngan: "DN-001",
                    },
                    {
                        ma_so_phieu: "Example-002",
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
/* === BASE STYLES === */
* {
    transition: all 0.2s ease;
}

/* === LAYOUT === */
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

.main-content-wrapper {
    margin-top: 10px;
    padding: 1rem;
}

.container-fluid {
    max-width: inherit;
    margin: 0 auto;
    background: white;
}

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

/* === CARD COMPONENTS === */
.card {
    border: none;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    border-radius: 5px;
    overflow: hidden;
}

.card-header {
    padding: 1rem 1.25rem;
    transition: background-color 0.2s ease;
}

.card-header:hover {
    background-color: rgba(0, 0, 0, 0.01);
}

.card-body {
    transition: all 0.3s ease;
}

/* Toggle Section */
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

/* === PROGRESS & TIMELINE === */
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
}

.progress-tracker.paid::before {
    background: linear-gradient(
        to right,
        #ffc107 33%,
        #1e88e5 33%,
        #1e88e5 66%,
        #198754 66%
    );
}

.progress-tracker.cancelled::before {
    background: linear-gradient(
        to right,
        #ffc107 33%,
        #dc3545 33%,
        #dc3545 100%
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

.step-label {
    text-align: center;
    font-size: 0.9rem;
    max-width: 100px;
}

/* Status Colors */
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

/* === TIMELINE COMPONENTS === */
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

/* Background Colors */
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

/* === BUTTON STYLES === */
.btn {
    transition: all 0.2s ease;
    border-radius: 6px;
    font-weight: 500;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.btn-success:hover {
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.2);
}

/* Button Group */
.button-30 {
    transition: all 0.2s ease;
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

/* === TABLE STYLES === */
.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.table {
    font-size: 0.8125rem;
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #e6e6e6;
    margin-bottom: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

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
    white-space: nowrap;
    text-align: center;
    vertical-align: middle;
}

.table td {
    padding: 10px;
    max-width: 250px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    vertical-align: middle;
    border-top: 1px solid #f0f0f0;
    border: 1px solid #dee2e6;
}

.table tbody tr:nth-of-type(odd) {
    background-color: rgba(0, 0, 0, 0.01);
}

.table-hover tbody tr:hover {
    background-color: rgba(16, 185, 129, 0.05);
}

.table-warning {
    background-color: rgba(255, 243, 205, 0.5) !important;
}

.table td.text-end,
.table th.text-end {
    text-align: right;
}

.table tr.active {
    background-color: rgba(16, 185, 129, 0.1) !important;
}

.table tr.selected td {
    background-color: rgba(30, 136, 229, 0.08);
    border-left: 3px solid #1e88e5;
}

.table tfoot {
    font-weight: 600;
    background-color: #f8f9fa;
}

.table tfoot td {
    border-top: 2px solid #e9ecef;
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

/* === FILTER STYLES === */
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

.filter-btn .text-primary {
    color: #ffd700 !important;
}

.text-green-500 {
    color: #10b981;
}

/* === ACTION BUTTONS === */
.action-button-group {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

/* Base Action Button Styles */
.action-btn-base {
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
    top: 25px;
}

.action-btn-base:hover {
    transform: scale(1.1);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.action-btn-base.disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Specific Action Buttons */
.reset-all-filters-btn {
    right: 5px;
    background: #198754;
}

.reset-all-filters-btn:hover {
    background: #10b981;
    transform: rotate(30deg) scale(1.1);
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

/* Filter Dropdown */
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

/* === CHECKBOX & FORM ELEMENTS === */
.form-check-input {
    cursor: pointer;
    width: 18px;
    height: 18px;
}

.form-control:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.1);
}

.form-select:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.1);
}

.edit-records-btn {
    right: 40px;
    background: #1e88e5;
}

.edit-records-btn:hover {
    background: #0d6efd;
}

.delete-records-btn {
    right: 75px;
    background: #dc3545;
}

.delete-records-btn:hover {
    background: #c82333;
}

.add-records-btn {
    right: 110px;
    background: #198754;
}

.add-records-btn:hover {
    background: #157347;
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
.export-excel-btn {
    right: 145px;
    background: #217346;
}

.export-excel-btn:hover {
    background: #105325;
}

.import-data-btn {
    right: 180px;
    background: #6366f1;
}

.import-data-btn:hover {
    background: #4f46e5;
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
/* Payment Request Action Buttons */
.export-excel-btn-payment {
    right: 40px;
    background: #217346;
}

.export-excel-btn-payment:hover {
    background: #105325;
}

.reset-filters-btn-payment {
    right: 75px;
    background: #198754;
}

.reset-filters-btn-payment:hover {
    background: #10b981;
    transform: rotate(30deg) scale(1.1);
}
/* Timeline Styles */
.timeline-container {
    position: relative;
    padding: 2rem 1rem;
    max-width: 900px;
    margin: 0 auto;
}

.timeline-container::before {
    content: "";
    position: absolute;
    top: 0;
    bottom: 0;
    left: 24px;
    width: 4px;
    background: linear-gradient(
        to bottom,
        rgba(0, 0, 0, 0.05) 0%,
        #007bff 20%,
        #17a2b8 50%,
        #28a745 80%,
        rgba(0, 0, 0, 0.05) 100%
    );
    border-radius: 4px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.timeline-item {
    position: relative;
    margin-bottom: 2.5rem;
    padding-left: 45px;
    opacity: 1;
    transform: translateY(0);
    transition: all 0.4s ease;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-badge {
    position: absolute;
    left: -18px;
    top: 0;
    width: 46px;
    height: 46px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
    z-index: 2;
    border: 3px solid white;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.timeline-item:hover .timeline-badge {
    transform: scale(1.1);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
}

.timeline-badge i {
    font-size: 1.25rem;
}

.timeline-content {
    background-color: #fff;
    border-radius: 0.75rem;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    padding: 1.5rem;
    position: relative;
    transition: all 0.3s ease;
    border-left: 5px solid transparent;
}

.timeline-item:hover .timeline-content {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

/* Status-specific styling */
.timeline-item:nth-child(1) .timeline-content {
    border-color: #007bff;
}

.timeline-item:nth-child(2) .timeline-content {
    border-color: #17a2b8;
}

.timeline-item:nth-child(3) .timeline-content {
    border-color: #28a745;
}

.timeline-item:nth-child(4) .timeline-content {
    border-color: #dc3545;
}

.timeline-content::before {
    content: "";
    position: absolute;
    left: -13px;
    top: 15px;
    width: 0;
    height: 0;
    border-top: 8px solid transparent;
    border-right: 8px solid #fff;
    border-bottom: 8px solid transparent;
    z-index: 1;
}

.timeline-title {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
}

.timeline-title .badge {
    font-size: 0.85rem;
    padding: 0.5rem 0.75rem;
    border-radius: 30px;
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
}

.timeline-note {
    background: rgba(248, 249, 250, 0.7);
    padding: 1rem;
    margin-top: 1rem;
    border-radius: 0.5rem;
    border-left: 4px solid #dee2e6;
    font-style: italic;
    color: #495057;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.03);
}

.fw-medium {
    font-weight: 500;
}

@media (min-width: 992px) {
    .timeline-container::before {
        left: 50%;
        margin-left: -2px;
    }

    .timeline-item {
        padding-left: 0;
        width: 50%;
        animation: none;
    }

    .timeline-item:nth-child(odd) {
        margin-right: 50%;
        padding-right: 45px;
        padding-left: 0;
        text-align: right;
    }

    .timeline-item:nth-child(even) {
        margin-left: 50%;
        padding-left: 45px;
    }

    .timeline-badge {
        left: 50%;
        margin-left: -23px;
    }

    .timeline-item:nth-child(odd) .timeline-content {
        border-left: none;
        border-right: 5px solid transparent;
    }

    .timeline-item:nth-child(odd) .timeline-content::before {
        left: auto;
        right: -13px;
        border-right: none;
        border-left: 8px solid #fff;
    }

    .timeline-item:nth-child(odd) .timeline-title {
        justify-content: flex-end;
    }

    .timeline-item:nth-child(odd) .timeline-note {
        border-left: none;
        border-right: 4px solid #dee2e6;
    }

    .timeline-item:nth-child(odd) p i,
    .timeline-item:nth-child(odd) .timeline-note i {
        margin-right: 0;
        margin-left: 0.5rem;
        order: 2;
    }
}

/* Enhanced animation for timeline */
@keyframes slide-in-left {
    from {
        transform: translateX(-50px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slide-in-right {
    from {
        transform: translateX(50px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@media (min-width: 992px) {
    .timeline-item:nth-child(odd) {
        animation: slide-in-left 0.6s ease forwards;
    }

    .timeline-item:nth-child(even) {
        animation: slide-in-right 0.6s ease forwards;
    }

    .timeline-item:nth-child(1) {
        animation-delay: 0.1s;
    }

    .timeline-item:nth-child(2) {
        animation-delay: 0.3s;
    }

    .timeline-item:nth-child(3) {
        animation-delay: 0.5s;
    }

    .timeline-item:nth-child(4) {
        animation-delay: 0.7s;
    }
}
/* Timeline animations */
.appear {
    opacity: 0;
    animation: fade-in 0.5s ease forwards;
}

@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.empty-timeline {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
    100% {
        opacity: 1;
    }
}
/* Timeline Card Enhancements */
.timeline-card {
    display: flex;
    flex-direction: column;
    height: 90vh;
    max-height: 800px;
    margin: 0 auto;
    width: 100%;
    max-width: 1000px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    border-radius: 12px;
    overflow: hidden;
    position: relative;
    border: none;
}

.timeline-header {
    position: sticky;
    top: 0;
    z-index: 10;
    padding: 1rem 1.5rem;
    background: linear-gradient(to right, #f8f9fa, #ffffff);
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
}

.timeline-scrollarea {
    height: calc(90vh - 60px);
    max-height: 740px;
    overflow-y: auto;
    overflow-x: hidden;
}

/* Enhanced Timeline Container Styles */
.timeline-body {
    padding: 1.5rem !important;
}

.timeline-container {
    position: relative;
    padding: 1rem 0.5rem;
}

.timeline-container::before {
    content: "";
    position: absolute;
    top: 0;
    bottom: 0;
    left: 24px;
    width: 4px;
    background: linear-gradient(
        to bottom,
        rgba(0, 0, 0, 0.05) 0%,
        #007bff 20%,
        #17a2b8 50%,
        #28a745 80%,
        rgba(0, 0, 0, 0.05) 100%
    );
    border-radius: 4px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

/* Responsive Timeline Adjustments */
@media (max-width: 767px) {
    .timeline-card {
        height: 80vh;
        max-height: none;
        margin: 0.5rem;
        border-radius: 8px;
    }

    .timeline-header {
        padding: 0.75rem 1rem;
    }

    .timeline-scrollarea {
        height: calc(80vh - 60px);
    }

    .timeline-container::before {
        left: 20px;
    }

    .timeline-badge {
        width: 40px;
        height: 40px;
        left: 0;
    }

    .timeline-content {
        padding: 1rem;
        margin-left: 15px;
    }

    .timeline-item {
        padding-left: 35px;
    }
}

/* Tablet Optimization */
@media (min-width: 768px) and (max-width: 991px) {
    .timeline-card {
        width: 90%;
        max-width: 700px;
    }
}

/* Desktop Enhancement */
@media (min-width: 992px) {
    .timeline-container::before {
        left: 50%;
        margin-left: -2px;
    }

    .timeline-item {
        width: 50%;
        animation: none;
        padding-left: 0;
        margin-bottom: 3rem;
    }

    .timeline-item:nth-child(odd) {
        margin-right: 50%;
        padding-right: 45px;
        text-align: right;
    }

    .timeline-item:nth-child(even) {
        margin-left: 50%;
        padding-left: 45px;
    }

    .timeline-badge {
        left: 50%;
        margin-left: -23px;
    }

    .timeline-item:nth-child(odd) .timeline-content {
        border-left: none;
        border-right: 5px solid transparent;
    }

    .timeline-item:nth-child(odd) .timeline-content::before {
        left: auto;
        right: -13px;
        transform: rotate(180deg);
        border-right: none;
        border-left: 8px solid #fff;
    }

    .timeline-item:nth-child(odd) .timeline-note {
        border-left: none;
        border-right: 4px solid #dee2e6;
        text-align: right;
    }

    .timeline-item:nth-child(odd) p {
        text-align: right;
    }

    .timeline-item:nth-child(odd) p i,
    .timeline-item:nth-child(odd) .timeline-note i {
        margin-right: 0;
        margin-left: 0.5rem;
        float: right;
    }
}

/* Improved Animation */
.timeline-badge {
    box-shadow: 0 0 0 5px rgba(255, 255, 255, 0.5),
        0 5px 15px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
}

.timeline-item:hover .timeline-badge {
    transform: scale(1.1) rotate(10deg);
    box-shadow: 0 0 0 8px rgba(255, 255, 255, 0.5),
        0 8px 25px rgba(0, 0, 0, 0.3);
}

.timeline-content {
    margin-top: 10px;
    border-radius: 12px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.timeline-content:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
}

/* Status-specific styling */
.timeline-item:nth-child(1) .timeline-content {
    border-left: 5px solid #007bff;
}

.timeline-item:nth-child(2) .timeline-content {
    border-left: 5px solid #17a2b8;
}

.timeline-item:nth-child(3) .timeline-content {
    border-left: 5px solid #28a745;
}

.timeline-item:nth-child(4) .timeline-content {
    border-left: 5px solid #dc3545;
}

/* Enhanced animation for timeline items */
@keyframes slide-in-left {
    from {
        transform: translateX(-50px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slide-in-right {
    from {
        transform: translateX(50px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@media (min-width: 992px) {
    .timeline-item:nth-child(odd).appear {
        animation: slide-in-left 0.6s ease forwards;
    }

    .timeline-item:nth-child(even).appear {
        animation: slide-in-right 0.6s ease forwards;
    }

    .timeline-item:nth-child(1) {
        animation-delay: 0.1s !important;
    }

    .timeline-item:nth-child(2) {
        animation-delay: 0.3s !important;
    }

    .timeline-item:nth-child(3) {
        animation-delay: 0.5s !important;
    }

    .timeline-item:nth-child(4) {
        animation-delay: 0.7s !important;
    }
}

/* Mobile Scroll Indicators */
@media (max-width: 991px) {
    .timeline-scrollarea::after {
        content: "";
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        height: 30px;
        background: linear-gradient(
            to top,
            rgba(255, 255, 255, 1) 0%,
            rgba(255, 255, 255, 0) 100%
        );
        pointer-events: none;
    }
}

/* Badge Enhancements */
.timeline-badge i {
    font-size: 1.25rem;
    transform: scale(1);
    transition: transform 0.3s ease;
}

.timeline-item:hover .timeline-badge i {
    transform: scale(1.2);
}

/* Timeline Note Styling */
.timeline-note {
    position: relative;
    background: rgba(248, 249, 250, 0.7);
    padding: 1rem;
    margin-top: 1rem;
    border-radius: 8px;
    border-left: 4px solid #dee2e6;
    font-style: italic;
    color: #495057;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.03);
    transition: all 0.3s ease;
}

.timeline-note:hover {
    background: rgba(248, 249, 250, 0.9);
    border-left: 4px solid #007bff;
}

/* Empty Timeline Animation */
@keyframes pulse-glow {
    0% {
        opacity: 0.7;
        text-shadow: 0 0 5px rgba(108, 117, 125, 0.3);
    }
    50% {
        opacity: 1;
        text-shadow: 0 0 20px rgba(108, 117, 125, 0.5);
    }
    100% {
        opacity: 0.7;
        text-shadow: 0 0 5px rgba(108, 117, 125, 0.3);
    }
}

.empty-timeline {
    animation: pulse-glow 2s infinite;
}

.empty-timeline i {
    filter: drop-shadow(0 0 8px rgba(0, 0, 0, 0.1));
}

/* Trạng thái thanh toán
ให้ขื้น icon แล้วชื่อ status */
.status-display {
    display: flex;
    align-items: center;
    padding: 0.4rem 0.75rem;
    border-radius: 0.375rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.status-display i {
    margin-right: 8px;
    font-size: 1.1rem;
}

.status-display span {
    color: white !important;
}

.status-paid {
    background-color: #28a745;
    color: white !important;
}

.status-paid i,
.status-paid span {
    color: white !important;
}

.status-submitted {
    background-color: #1e88e5;
    color: white !important;
}

.status-submitted i,
.status-submitted span {
    color: white !important;
}

.status-processing {
    background-color: #ffc107;
    color: #212529 !important;
}

.status-processing i,
.status-processing span {
    color: #212529 !important;
}

.status-cancelled {
    background-color: #dc3545;
    color: white !important;
}

.status-cancelled i,
.status-cancelled span {
    color: white !important;
}

/* Payment Details Summary Card */
.payment-details-summary-card {
    border: none;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.payment-details-summary-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    border: none;
}

.summary-stats .stat-item {
    text-align: center;
    min-width: 80px;
}

.summary-stats .stat-item small {
    display: block;
    font-size: 0.75rem;
    opacity: 0.8;
}

.payment-summary-content {
    padding: 2rem 1rem;
}

.summary-icon {
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%,
    100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-10px);
    }
}

/* Modal Enhancements */
.modal-fullscreen {
    margin: 0;
    padding: 0;
}

.modal-fullscreen .modal-content {
    height: 100vh;
    border: none;
    border-radius: 0;
}

.modal-header {
    padding: 1rem 1.5rem;
    border-bottom: 2px solid rgba(255, 255, 255, 0.1);
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
}

.modal-header-stats {
    display: flex;
    gap: 1rem;
}

.stat-badge {
    background: rgba(255, 255, 255, 0.2);
    padding: 0.375rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    color: white;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.modal-body {
    height: calc(100vh - 140px);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

/* Action Toolbar */
.action-toolbar {
    flex-shrink: 0;
    border-bottom: 2px solid #e9ecef;
    background: linear-gradient(to right, #f8f9fa, #ffffff);
}

.action-toolbar .btn {
    border-radius: 6px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.action-toolbar .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Table Container */
.table-container-modal {
    flex: 1;
    overflow: auto;
    background: white;
}

.table-container-modal .table {
    margin-bottom: 0;
    font-size: 0.875rem;
}

/* Enhanced Table Header */
.table thead th {
    border-bottom: 2px solid #e2e8f0;
    background-color: #f8fafc;
    color: #059669;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    padding: 1rem 0.75rem;
    border: none;
    position: sticky;
    top: 0;
    z-index: 10;
}

.header-with-filter {
    display: flex;
    align-items: center;
    justify-content: space-between;
    white-space: nowrap;
}

.filter-btn {
    background: none;
    border: none;
    color: rgba(255, 255, 255, 0.7);
    padding: 0.25rem;
    border-radius: 3px;
    transition: all 0.2s ease;
}

.filter-btn:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white;
}

.filter-btn .text-primary {
    color: #ffd700 !important;
}

/* Filter Dropdown */
.filter-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 0.75rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    margin-top: 0.25rem;
}

.filter-actions {
    display: flex;
    gap: 0.5rem;
}

/* Enhanced Table Rows */
.table tbody tr {
    transition: all 0.2s ease;
}

.table tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
    transform: scale(1.001);
}

.table tbody tr.table-warning {
    background-color: rgba(255, 193, 7, 0.15);
    border-left: 4px solid #ffc107;
}

/* Enhanced Table Cells */
.table td {
    padding: 0.875rem 0.75rem;
    vertical-align: middle;
    border-color: #e9ecef;
}

.text-truncate {
    max-width: 200px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Badge Enhancements */
.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
    font-weight: 500;
}

/* Empty State */
.empty-state {
    padding: 3rem 1rem;
    text-align: center;
}

.empty-state i {
    opacity: 0.3;
}

/* Modal Footer */
.modal-footer {
    padding: 1rem 1.5rem;
    border-top: 2px solid #e9ecef;
    background: linear-gradient(to right, #f8f9fa, #ffffff);
}

/* Pagination Enhancements */
.pagination {
    margin-bottom: 0;
}

.page-link {
    border-radius: 6px;
    color: #007bff;
    font-weight: 500;
    padding: 0.5rem 0.75rem;
    border: 1px solid #dee2e6;
    transition: all 0.2s ease;
}

.page-link:hover {
    background-color: #e9ecef;
    border-color: #adb5bd;
    transform: translateY(-1px);
}

.page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
    box-shadow: 0 4px 8px rgba(0, 123, 255, 0.2);
}

/* Responsive Design */
@media (max-width: 768px) {
    .modal-fullscreen {
        margin: 0;
    }

    .action-toolbar .row {
        flex-direction: column;
        gap: 1rem;
    }

    .action-toolbar .col-md-6 {
        text-align: center !important;
    }

    .modal-header-stats {
        display: none;
    }

    .table {
        font-size: 0.75rem;
    }

    .table th,
    .table td {
        padding: 0.5rem 0.25rem;
    }
}

/* Smooth Transitions */
* {
    transition: all 0.2s ease;
}

/* Custom Scrollbar for Modal */
.table-container-modal::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

.table-container-modal::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.table-container-modal::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.table-container-modal::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Loading State */
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.loading-spinner {
    width: 3rem;
    height: 3rem;
    border: 0.25rem solid #f3f3f3;
    border-top: 0.25rem solid #007bff;
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

/* Payment Requests Summary Card */
.payment-requests-summary-card {
    border: none;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.payment-requests-summary-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.bg-gradient-success {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
    border: none;
}

/* Modal Header Enhancement for Payment Requests */
.modal-header.bg-success {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
}

/* Enhanced Empty State for Payment Requests */
.empty-state .btn-outline-success {
    border-color: #28a745;
    color: #28a745;
}

.empty-state .btn-outline-success:hover {
    background-color: #28a745;
    border-color: #28a745;
    color: white;
}

/* Success-themed highlighting */
.table tbody tr.search-highlight {
    background-color: rgba(40, 167, 69, 0.1);
    border-left: 3px solid #28a745;
}

.table tbody tr.search-highlight:hover {
    background-color: rgba(40, 167, 69, 0.2);
}

/* Enhanced badge colors for payment requests */
.badge.bg-info {
    background-color: #17a2b8 !important;
}

.badge.bg-secondary {
    background-color: #6c757d !important;
}

.badge.bg-primary {
    background-color: #007bff !important;
}

/* Financial amount styling */
.text-success {
    color: #28a745 !important;
}

.text-warning {
    color: #ffc107 !important;
}

.text-danger {
    color: #dc3545 !important;
}

.text-info {
    color: #17a2b8 !important;
}

.text-primary {
    color: #007bff !important;
}

/* Enhanced Quick Filter Buttons for Payment Requests */
.btn-outline-primary.active {
    background-color: #007bff;
    border-color: #007bff;
    color: white;
}

.btn-outline-warning.active {
    background-color: #ffc107;
    border-color: #ffc107;
    color: #212529;
}

.btn-outline-info.active {
    background-color: #17a2b8;
    border-color: #17a2b8;
    color: white;
}

/* Responsive Design for Payment Requests Modal */
@media (max-width: 768px) {
    .payment-requests-summary-card .summary-stats {
        display: none;
    }

    .modal-header.bg-success .modal-header-stats {
        display: none;
    }
}

/* Enhanced Animation for Payment Request Cards */
.payment-requests-summary-card .summary-icon {
    animation: float 3s ease-in-out infinite;
}

/* Custom scrollbar for payment requests table */

.table-container-modal::-webkit-scrollbar {
    height: 8px;
    width: 8px;
}

.table-container-modal::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.table-container-modal::-webkit-scrollbar-thumb {
    background: #28a745;
    border-radius: 4px;
}

.table-container-modal::-webkit-scrollbar-thumb:hover {
    background: #1e7e34;
}
/* Enhanced Modal Styles */
.modal-dialog.modal-lg {
    max-width: 900px;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    padding: 1rem 1.5rem;
    background: linear-gradient(to right, #f8f9fa, #ffffff);
}

/* Form Section Headers */
.border-bottom {
    border-bottom: 2px solid currentColor !important;
    padding-bottom: 0.5rem;
    margin-bottom: 1rem;
}

/* Alert Enhancements */
.alert {
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.alert-info {
    background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
    border-color: #bee5eb;
}

.alert-warning {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    border-color: #ffeaa7;
}

/* Form Input Enhancements */
.form-control:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.1);
}

.form-select:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.1);
}

/* Badge Styling */
.badge.bg-info {
    background-color: #17a2b8 !important;
    color: white !important;
}

/* Loading Spinner */
.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

/* Button Enhancements */
.btn-success:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.2);
}

.btn-outline-secondary:hover {
    transform: translateY(-1px);
}

/* Form Text Styling */
.form-text {
    color: #6c757d;
    font-size: 0.875rem;
}

.form-text i {
    color: #28a745;
}

/* Validation Card */
.card.border-danger {
    border-width: 2px;
    border-style: dashed;
}

/* Responsive Design */
@media (max-width: 768px) {
    .modal-dialog.modal-lg {
        margin: 0.5rem;
        max-width: calc(100% - 1rem);
    }

    .modal-body {
        padding: 1rem;
        max-height: 60vh;
    }

    .row > .col-md-6:first-child {
        margin-bottom: 1.5rem;
    }

    .d-flex.gap-2 {
        flex-direction: column;
        gap: 0.5rem !important;
    }

    .modal-footer .d-flex {
        flex-direction: column;
        align-items: stretch !important;
    }

    .modal-footer .text-muted {
        text-align: center;
        margin-bottom: 1rem;
    }
}

/* Smooth Transitions */
.modal-content {
    transition: all 0.3s ease;
}

.form-control,
.form-select,
.btn {
    transition: all 0.2s ease;
}

/* Icon Styling */
.fa-2x {
    font-size: 1.5em;
}

/* Alert Icon Animations */
.alert .fas {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
    100% {
        opacity: 1;
    }
}

/* Customer search dropdown styles */
.dropdown-menu {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    z-index: 1050;
}

.dropdown-item {
    padding: 0.5rem 1rem;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
    transition: background-color 0.15s ease-in-out;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
}

.dropdown-item:focus {
    background-color: #e9ecef;
    outline: none;
}

/* Custom styling for better UX */
.form-control:focus + .dropdown-menu {
    display: block;
}

.customer-search-container {
    position: relative;
}

.customer-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #dee2e6;
    border-top: none;
    border-radius: 0 0 0.375rem 0.375rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    z-index: 1000;
    max-height: 200px;
    overflow-y: auto;
}

.customer-dropdown-item {
    padding: 0.75rem 1rem;
    cursor: pointer;
    border-bottom: 1px solid #f8f9fa;
    transition: background-color 0.15s ease-in-out;
}

.customer-dropdown-item:hover {
    background-color: #f8f9fa;
}

.customer-dropdown-item:last-child {
    border-bottom: none;
}

.customer-name {
    font-weight: 500;
    color: #495057;
}

.customer-code {
    font-size: 0.875rem;
    color: #6c757d;
}

/* === ANIMATION KEYFRAMES === */
@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

@keyframes float {
    0%,
    100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-10px);
    }
}

@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slide-in-left {
    from {
        transform: translateX(-50px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slide-in-right {
    from {
        transform: translateX(50px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes highlight-pulse {
    0% {
        background-color: rgba(255, 235, 59, 0.4);
    }
    50% {
        background-color: rgba(255, 235, 59, 0.1);
    }
    100% {
        background-color: rgba(255, 235, 59, 0.2);
    }
}

@keyframes pulse {
    0% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
    100% {
        opacity: 1;
    }
}

@keyframes pulse-glow {
    0% {
        opacity: 0.7;
        text-shadow: 0 0 5px rgba(108, 117, 125, 0.3);
    }
    50% {
        opacity: 1;
        text-shadow: 0 0 20px rgba(108, 117, 125, 0.5);
    }
    100% {
        opacity: 0.7;
        text-shadow: 0 0 5px rgba(108, 117, 125, 0.3);
    }
}

/* === UTILITY CLASSES === */
.text-truncate {
    max-width: 200px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.cursor-pointer {
    cursor: pointer;
}

.fw-medium {
    font-weight: 500;
}

.position-relative {
    position: relative;
}

.bg-light {
    background-color: #f8f9fa !important;
}

/* Status Colors */
.text-success {
    color: #28a745 !important;
}

.text-warning {
    color: #ffc107 !important;
}

.text-danger {
    color: #dc3545 !important;
}

.text-info {
    color: #17a2b8 !important;
}

.text-primary {
    color: #007bff !important;
}

.text-green-500 {
    color: #10b981;
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

/* Background Gradients */
.bg-gradient-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    border: none;
}

.bg-gradient-success {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
    border: none;
}

/* === ENHANCED SCROLLBAR STYLES === */
.scroll-area::-webkit-scrollbar {
    display: none;
}

.scroll-area {
    -ms-overflow-style: none;
    scrollbar-width: none;
    height: calc(90vh - 60px);
    -webkit-overflow-scrolling: touch;
}

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

.table-container-modal::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

.table-container-modal::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.table-container-modal::-webkit-scrollbar-thumb {
    background: #28a745;
    border-radius: 4px;
}

.table-container-modal::-webkit-scrollbar-thumb:hover {
    background: #1e7e34;
}

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

/* === ENHANCED RESPONSIVE DESIGN === */
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

    .stat-badge {
        display: none;
    }

    .search-input {
        padding: 0.5rem 0.75rem;
        padding-right: 65px;
        font-size: 0.875rem;
    }
}

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

    .modal-fullscreen {
        margin: 0;
    }

    .modal-dialog.modal-lg {
        margin: 0.5rem;
        max-width: calc(100% - 1rem);
    }

    .modal-body {
        padding: 1rem;
        max-height: 60vh;
    }

    .action-toolbar .row {
        flex-direction: column;
        gap: 1rem;
    }

    .action-toolbar .col-md-4,
    .action-toolbar .col-md-6,
    .action-toolbar .col-md-8 {
        width: 100%;
        max-width: 100%;
        text-align: center !important;
    }

    .modal-header-stats {
        display: none;
    }

    .table {
        font-size: 0.75rem;
    }

    .table th,
    .table td {
        padding: 0.5rem 0.25rem;
    }

    .advanced-filters {
        flex-direction: column;
        gap: 0.5rem;
    }

    .advanced-filters .btn {
        width: 100%;
    }

    .modal-footer .d-flex {
        flex-direction: column;
        align-items: stretch !important;
    }

    .modal-footer .text-muted {
        text-align: center;
        margin-bottom: 1rem;
    }

    .payment-requests-summary-card .summary-stats {
        display: none;
    }

    .modal-header.bg-success .modal-header-stats {
        display: none;
    }
}

@media (min-width: 768px) and (max-width: 991px) {
    .timeline-card {
        width: 90%;
        max-width: 700px;
    }
}

@media (max-width: 767px) {
    .timeline-card {
        height: 80vh;
        max-height: none;
        margin: 0.5rem;
        border-radius: 8px;
    }

    .timeline-header {
        padding: 0.75rem 1rem;
    }

    .timeline-scrollarea {
        height: calc(80vh - 60px);
    }

    .timeline-container::before {
        left: 20px;
    }

    .timeline-badge {
        width: 40px;
        height: 40px;
        left: 0;
    }

    .timeline-content {
        padding: 1rem;
        margin-left: 15px;
    }

    .timeline-item {
        padding-left: 35px;
    }
}

@media (max-width: 991px) {
    .timeline-scrollarea::after {
        content: "";
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        height: 30px;
        background: linear-gradient(
            to top,
            rgba(255, 255, 255, 1) 0%,
            rgba(255, 255, 255, 0) 100%
        );
        pointer-events: none;
    }
}

@media (min-width: 992px) {
    .timeline-container::before {
        left: 50%;
        margin-left: -2px;
    }

    .timeline-item {
        width: 50%;
        animation: none;
        padding-left: 0;
        margin-bottom: 3rem;
    }

    .timeline-item:nth-child(odd) {
        margin-right: 50%;
        padding-right: 45px;
        text-align: right;
    }

    .timeline-item:nth-child(even) {
        margin-left: 50%;
        padding-left: 45px;
    }

    .timeline-badge {
        left: 50%;
        margin-left: -23px;
    }

    .timeline-item:nth-child(odd) .timeline-content {
        border-left: none;
        border-right: 5px solid transparent;
    }

    .timeline-item:nth-child(odd) .timeline-content::before {
        left: auto;
        right: -13px;
        transform: rotate(180deg);
        border-right: none;
        border-left: 8px solid #fff;
    }

    .timeline-item:nth-child(odd) .timeline-note {
        border-left: none;
        border-right: 4px solid #dee2e6;
        text-align: right;
    }

    .timeline-item:nth-child(odd) p {
        text-align: right;
    }

    .timeline-item:nth-child(odd) p i,
    .timeline-item:nth-child(odd) .timeline-note i {
        margin-right: 0;
        margin-left: 0.5rem;
        float: right;
    }

    .timeline-item:nth-child(odd).appear {
        animation: slide-in-left 0.6s ease forwards;
    }

    .timeline-item:nth-child(even).appear {
        animation: slide-in-right 0.6s ease forwards;
    }

    .timeline-item:nth-child(1) {
        animation-delay: 0.1s !important;
    }

    .timeline-item:nth-child(2) {
        animation-delay: 0.3s !important;
    }

    .timeline-item:nth-child(3) {
        animation-delay: 0.5s !important;
    }

    .timeline-item:nth-child(4) {
        animation-delay: 0.7s !important;
    }
}

/* === FINAL CLEANUP AND OPTIMIZATION === */
/* Remove duplicate styles and consolidate similar selectors */

/* Hover Effects Consolidation */
.btn:hover,
.action-toolbar .btn:hover,
.btn-success:hover,
.btn-outline-secondary:hover {
    transform: translateY(-1px);
}

.btn-success:hover {
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.2);
}

.page-link:hover {
    background-color: #e9ecef;
    border-color: #adb5bd;
    transform: translateY(-1px);
}

/* Active States Consolidation */
.btn-outline-primary.active {
    background-color: #007bff;
    border-color: #007bff;
    color: white;
}

.btn-outline-warning.active {
    background-color: #ffc107;
    border-color: #ffc107;
    color: #212529;
}

.btn-outline-info.active {
    background-color: #17a2b8;
    border-color: #17a2b8;
    color: white;
}

/* Focus States Consolidation */
.form-control:focus,
.form-select:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.1);
}

/* Enhanced Badge Styling */
.badge.bg-info {
    background-color: #17a2b8 !important;
    color: white !important;
}

.badge.bg-secondary {
    background-color: #6c757d !important;
}

.badge.bg-primary {
    background-color: #007bff !important;
}

/* Loading and Animation States */
.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

.appear {
    opacity: 0;
    animation: fade-in 0.5s ease forwards;
}

.empty-timeline {
    animation: pulse-glow 2s infinite;
}

.empty-timeline i {
    filter: drop-shadow(0 0 8px rgba(0, 0, 0, 0.1));
}

.summary-icon {
    animation: float 3s ease-in-out infinite;
}

/* Search and Highlight Effects */
.search-highlight {
    background-color: rgba(255, 235, 59, 0.2);
    animation: highlight-pulse 1s ease-in-out;
}

.search-highlight-text {
    background-color: #ffeb3b;
    color: #333;
    padding: 1px 2px;
    border-radius: 2px;
    font-weight: 600;
}

/* Final Performance Optimizations */
* {
    transition: all 0.2s ease;
}

.modal-content,
.form-control,
.form-select,
.btn {
    transition: all 0.2s ease;
}

/* Print Styles */
@media print {
    .sticky-wrapper,
    .action-toolbar,
    .pagination-wrapper,
    .modal-header,
    .modal-footer {
        display: none !important;
    }

    .table {
        font-size: 12px;
    }

    .card {
        box-shadow: none;
        border: 1px solid #000;
    }
}
</style>
