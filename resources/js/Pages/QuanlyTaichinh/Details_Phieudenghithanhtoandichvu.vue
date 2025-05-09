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
                                        v-if="processingAction.comments"
                                    >
                                        <i class="far fa-comment me-2"></i>
                                        <span>{{
                                            processingAction.comments
                                        }}</span>
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
                                        v-if="submittedAction.comments"
                                    >
                                        <i class="far fa-comment me-2"></i>
                                        <span>{{
                                            submittedAction.comments
                                        }}</span>
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
                                                    document.ngay_thanh_toan ||
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
                                            document.ngay_thanh_toan &&
                                            document.ngay_thanh_toan !==
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
                                                v-if="document.ngay_thanh_toan"
                                                class="badge bg-light text-dark ms-2"
                                            ></span>
                                        </span>
                                    </p>
                                    <div
                                        class="timeline-note"
                                        v-if="paidAction.comments"
                                    >
                                        <i class="far fa-comment me-2"></i>
                                        <span>{{ paidAction.comments }}</span>
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

    <!-- Original content -->
    <div class="card shadow" v-if="!showTimeline">
        <div class="card-body p-0">
            <PerfectScrollbar
                :options="{
                    wheelSpeed: 1,
                    wheelPropagation: true,
                    minScrollbarLength: 20,
                }"
                class="scroll-area"
            >
                <div class="sticky-wrapper">
                    <!-- Add container with padding -->
                    <div class="container-fluid px-4">
                        <div class="button-container">
                            <div class="row align-items-center mb-2">
                                <div class="action-button-group">
                                    <button
                                        type="button"
                                        class="button-30"
                                        @click="UpdateDocument"
                                        v-if="isEditing"
                                    >
                                        <i class="bx bxs-save"></i>
                                        <span>Lưu</span>
                                    </button>
                                    <button
                                        type="button"
                                        class="button-30-yellow"
                                        @click="toggleEditing"
                                    >
                                        <i
                                            class="fa-solid fa-pen-to-square"
                                        ></i>
                                        <span>{{
                                            isEditing ? "Hủy" : "Điều chỉnh"
                                        }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- progress-tracker-container -->
                        <div class="progress-container mt-4">
                            <div class="col-12">
                                <div
                                    class="progress-tracker"
                                    :class="
                                        document.trang_thai_thanh_toan ||
                                        'processing'
                                    "
                                    @click="toggleTimelineView"
                                >
                                    <!-- Processing Step -->
                                    <div
                                        class="track-step"
                                        :class="{
                                            active:
                                                document.trang_thai_thanh_toan ===
                                                    'processing' ||
                                                document.trang_thai_thanh_toan ===
                                                    'submitted' ||
                                                document.trang_thai_thanh_toan ===
                                                    'paid',
                                        }"
                                    >
                                        <div class="step-icon status-pending">
                                            <i class="fas fa-spinner"></i>
                                        </div>
                                        <span class="step-label"
                                            >Đang xử lý</span
                                        >
                                    </div>

                                    <!-- Submitted Step -->
                                    <div
                                        class="track-step"
                                        :class="{
                                            active:
                                                document.trang_thai_thanh_toan ===
                                                    'submitted' ||
                                                document.trang_thai_thanh_toan ===
                                                    'paid',
                                        }"
                                    >
                                        <div class="step-icon status-approved">
                                            <i class="fas fa-paper-plane"></i>
                                        </div>
                                        <span class="step-label"
                                            >Đã nộp kế toán</span
                                        >
                                    </div>

                                    <!-- Paid Step -->
                                    <div
                                        class="track-step"
                                        :class="{
                                            active:
                                                document.trang_thai_thanh_toan ===
                                                'paid',
                                        }"
                                    >
                                        <div class="step-icon status-paid">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                        <span class="step-label"
                                            >Đã thanh toán</span
                                        >
                                    </div>

                                    <!-- Cancelled Step (if applicable) -->
                                    <div
                                        v-if="
                                            document.trang_thai_thanh_toan ===
                                            'rejected'
                                        "
                                        class="track-step"
                                        :class="{
                                            active:
                                                document.trang_thai_thanh_toan ===
                                                'rejected',
                                        }"
                                    >
                                        <div class="step-icon status-cancelled">
                                            <i class="fas fa-times-circle"></i>
                                        </div>
                                        <span class="step-label">Từ chối</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Main content with added top margin -->
                <div class="main-content-wrapper">
                    <div class="d-flex flex-column flex-md-row gap-1">
                        <!-- Thông tin phiếu đề nghị -->
                        <div class="card col-12 col-md-6">
                            <div class="card-body">
                                <h5 class="card-title mb-3 border-bottom pb-2">
                                    Thông tin phiếu đề nghị
                                </h5>
                                <div class="row gutters">
                                    <!-- Mã đề nghị giải ngân -->
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="maGiaiNgan"
                                                class="form-label"
                                            >
                                                Mã đề nghị giải ngân
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="maGiaiNgan"
                                                v-model="document.ma_giai_ngan"
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
                                                disabled
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
                                                disabled
                                            />
                                        </div>
                                    </div>

                                    <!-- Trạng thái thanh toán -->
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
                                                :class="documentStatusClass"
                                            >
                                                <i
                                                    class="fas"
                                                    :class="statusIcon"
                                                ></i>
                                                <span>{{
                                                    formatStatus(
                                                        document.trang_thai_thanh_toan
                                                    )
                                                }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Khách hàng cá nhân -->
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="khachHangCaNhan"
                                                class="form-label"
                                            >
                                                Khách hàng cá nhân
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="khachHangCaNhan"
                                                disabled
                                            />
                                        </div>
                                    </div>

                                    <!-- Mã khách hàng cá nhân -->
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="maKhachHangCaNhan"
                                                class="form-label"
                                            >
                                                Mã khách hàng cá nhân
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="maKhachHangCaNhan"
                                                disabled
                                            />
                                        </div>
                                    </div>

                                    <!-- Khách hàng doanh nghiệp -->
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="khachHangDoanhNghiep"
                                                class="form-label"
                                            >
                                                Khách hàng doanh nghiệp
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="khachHangDoanhNghiep"
                                                disabled
                                            />
                                        </div>
                                    </div>

                                    <!-- Mã khách hàng doanh nghiệp -->
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="maKhachHangDoanhNghiep"
                                                class="form-label"
                                            >
                                                Mã khách hàng doanh nghiệp
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="maKhachHangDoanhNghiep"
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
                                                for="soDotThanhToan"
                                                class="form-label"
                                            >
                                                Số đợt thanh toán
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="soDotThanhToan"
                                                disabled
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Thông tin tài chính -->
                        <div class="card col-12 col-md-6">
                            <div class="card-body">
                                <h5 class="card-title mb-3 border-bottom pb-2">
                                    Thông tin tài chính
                                </h5>
                                <div class="row gutters">
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="tongTien"
                                                class="form-label fw-bold"
                                            >
                                                Tổng tiền
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control text-end fw-bold bg-light"
                                                id="tongTien"
                                                disabled
                                            />
                                        </div>
                                    </div>

                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="tongTienTamGiu"
                                                class="form-label fw-bold"
                                            >
                                                Tổng tiền tạm giữ
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control text-end fw-bold bg-light"
                                                id="tongTienTamGiu"
                                                disabled
                                            />
                                        </div>
                                    </div>

                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="tongTienKhauTru"
                                                class="form-label fw-bold"
                                            >
                                                Tổng tiền khấu trừ
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control text-end fw-bold bg-light"
                                                id="tongTienKhauTru"
                                                disabled
                                            />
                                        </div>
                                    </div>

                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="tongTienLaiSuat"
                                                class="form-label fw-bold"
                                            >
                                                Tổng tiền lãi suất
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control text-end fw-bold bg-light"
                                                id="tongTienLaiSuat"
                                                disabled
                                            />
                                        </div>
                                    </div>

                                    <div
                                        class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12"
                                    >
                                        <div class="form-group mb-0">
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
                                                disabled
                                            />
                                        </div>
                                    </div>

                                    <!-- Ngày thanh toán -->
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="ngayThanhToan"
                                                class="form-label"
                                            >
                                                Ngày thanh toán
                                            </label>
                                            <input
                                                type="date"
                                                class="form-control"
                                                id="ngayThanhToan"
                                                v-model="
                                                    editableDocument.ngay_thanh_toan
                                                "
                                                :disabled="!isEditing"
                                            />
                                        </div>
                                    </div>
                                    <!-- Hồ sơ đính kèm -->
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="hoSoDinhKem"
                                                class="form-label"
                                            >
                                                Hồ sơ đính kèm
                                            </label>
                                            <div
                                                class="d-flex align-items-center"
                                            >
                                                <a
                                                    href="#"
                                                    @click.prevent="
                                                        openAttachment
                                                    "
                                                    class="text-decoration-none"
                                                >
                                                    <div
                                                        class="d-flex align-items-center"
                                                    >
                                                        <i
                                                            class="far fa-file-pdf text-danger fs-4 me-2"
                                                        ></i>
                                                        <span
                                                            >Xem hồ sơ đính
                                                            kèm</span
                                                        >
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Ghi chú -->
                                    <div
                                        class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="ghiChu"
                                                class="form-label"
                                            >
                                                Ghi chú
                                            </label>
                                            <div class="note-container">
                                                <textarea
                                                    v-if="isEditing"
                                                    class="form-control"
                                                    id="ghiChu"
                                                    rows="3"
                                                    v-model="
                                                        editableDocument.comment
                                                    "
                                                ></textarea>
                                                <div
                                                    v-else
                                                    class="note-content"
                                                    id="ghiChu"
                                                >
                                                    <p
                                                        v-if="document.comment"
                                                        class="mb-0"
                                                    >
                                                        {{ document.comment }}
                                                    </p>
                                                    <p
                                                        v-else
                                                        class="text-muted fst-italic mb-0"
                                                    >
                                                        Không có ghi chú
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Nghiệm thu dịch vụ -->
                    <!-- First, update the table in the template to loop through the bienbans data -->
                    <div class="card mt-3">
                        <div
                            class="card-header border-0 bg-transparent d-flex justify-content-between align-items-center"
                        >
                            <h5 class="card-title mb-0">
                                <span class="toggle-section cursor-pointer">
                                    <i
                                        class="fas fa-angle-down me-2 toggle-icon"
                                    ></i>
                                    Biên bản nghiệm thu dịch vụ
                                </span>
                            </h5>
                        </div>
                        <div class="card-body" style="display: none">
                            <div class="table-container">
                                <div class="table-responsive mt-2">
                                    <table
                                        class="table table-bordered table-hover align-middle"
                                    >
                                        <thead class="table-light text-center">
                                            <tr>
                                                <th>STT</th>
                                                <th>Mã nghiệm thu</th>
                                                <th>Trạm</th>
                                                <th>Vụ đầu tư</th>
                                                <th>
                                                    Khách hàng cá nhân ĐT mía
                                                </th>
                                                <th>
                                                    Khách hàng doanh nghiệp ĐT
                                                    mía
                                                </th>
                                                <th>Hợp đồng đầu tư</th>
                                                <th>Hình thức DV</th>
                                                <th>Hợp đồng cung ứng DV</th>
                                                <th>Thành tiền</th>
                                                <th>Số tiền tạm giữ</th>
                                                <th>Tiền thanh toán</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-if="bienbans.length === 0">
                                                <td
                                                    colspan="12"
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
                                                ) in bienbans"
                                                :key="item.ma_nghiem_thu"
                                            >
                                                <td class="text-center">
                                                    {{ index + 1 }}
                                                </td>
                                                <td>
                                                    <router-link
                                                        :to="`/Details_NghiemthuDV/${item.ma_nghiem_thu}`"
                                                        class="receipt-link"
                                                    >
                                                        {{ item.ma_nghiem_thu }}
                                                    </router-link>
                                                </td>
                                                <td>{{ item.tram }}</td>
                                                <td>{{ item.vu_dau_tu }}</td>
                                                <td>
                                                    {{
                                                        item.khach_hang_ca_nhan_dt_mia
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        item.khach_hang_doanh_nghiep_dt_mia
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        item.hop_dong_dau_tu_mia
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        item.hinh_thuc_thuc_hien_dv
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        item.hop_dong_cung_ung_dich_vu
                                                    }}
                                                </td>
                                                <td class="text-end">
                                                    {{
                                                        formatCurrency(
                                                            item.tong_tien
                                                        )
                                                    }}
                                                </td>
                                                <td class="text-end">
                                                    {{
                                                        formatCurrency(
                                                            item.tong_tien_tam_giu
                                                        )
                                                    }}
                                                </td>
                                                <td class="text-end">
                                                    {{
                                                        formatCurrency(
                                                            item.tong_tien_thanh_toan
                                                        )
                                                    }}
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td
                                                    colspan="9"
                                                    class="text-end fw-bold"
                                                >
                                                    Tổng cộng:
                                                </td>
                                                <td class="text-end fw-bold">
                                                    {{
                                                        formatCurrency(
                                                            totals.total_amount
                                                        )
                                                    }}
                                                </td>
                                                <td class="text-end fw-bold">
                                                    {{
                                                        formatCurrency(
                                                            totals.total_hold_amount
                                                        )
                                                    }}
                                                </td>
                                                <td class="text-end fw-bold">
                                                    {{
                                                        formatCurrency(
                                                            totals.total_payment_amount
                                                        )
                                                    }}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chi tiết nghiệm thu dịch vụ -->
                    <!-- Chi tiết nghiệm thu dịch vụ -->
                    <div class="card mt-3">
                        <div
                            class="card-header border-0 bg-transparent d-flex justify-content-between align-items-center"
                        >
                            <h5 class="card-title mb-0">
                                <span class="toggle-section cursor-pointer">
                                    <i
                                        class="fas fa-angle-down me-2 toggle-icon"
                                    ></i>
                                    Chi tiết nghiệm thu dịch vụ
                                </span>
                            </h5>
                        </div>
                        <div class="card-body" style="display: none">
                            <div class="table-container">
                                <div class="table-responsive mt-2">
                                    <table
                                        class="table table-bordered table-hover align-middle"
                                    >
                                        <thead class="table-light text-center">
                                            <tr>
                                                <th>STT</th>
                                                <th>Mã nghiệm thu</th>
                                                <th>Trạm</th>
                                                <th>Dịch vụ</th>
                                                <th>Mã số thửa</th>
                                                <th>Đơn vị tính</th>
                                                <th>Số lần thực hiện</th>
                                                <th>Khối lượng thực hiện</th>
                                                <th>Đơn giá</th>
                                                <th>Thành tiền</th>
                                                <th>Số tiền tạm giữ</th>
                                                <th>Tiền thanh toán</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-if="
                                                    chitietDichVu.length === 0
                                                "
                                            >
                                                <td
                                                    colspan="12"
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
                                                ) in chitietDichVu"
                                                :key="index"
                                            >
                                                <td class="text-center">
                                                    {{ index + 1 }}
                                                </td>
                                                <td>
                                                    <router-link
                                                        :to="`/Details_NghiemthuDV/${item.ma_nghiem_thu}`"
                                                        class="receipt-link"
                                                    >
                                                        {{ item.ma_nghiem_thu }}
                                                    </router-link>
                                                </td>
                                                <td>{{ item.tram }}</td>
                                                <td>{{ item.dich_vu }}</td>
                                                <td>{{ item.ma_so_thua }}</td>
                                                <td>{{ item.don_vi_tinh }}</td>
                                                <td class="text-center">
                                                    {{ item.so_lan_thuc_hien }}
                                                </td>
                                                <td class="text-end">
                                                    {{
                                                        formatNumber(
                                                            item.khoi_luong_thuc_hien
                                                        )
                                                    }}
                                                </td>
                                                <td class="text-end">
                                                    {{
                                                        formatCurrency(
                                                            item.don_gia
                                                        )
                                                    }}
                                                </td>
                                                <td class="text-end">
                                                    {{
                                                        formatCurrency(
                                                            item.thanh_tien
                                                        )
                                                    }}
                                                </td>
                                                <td class="text-end">
                                                    {{
                                                        formatCurrency(
                                                            item.tien_tam_giu
                                                        )
                                                    }}
                                                </td>
                                                <td class="text-end">
                                                    {{
                                                        formatCurrency(
                                                            item.tien_thanh_toan
                                                        )
                                                    }}
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td
                                                    colspan="9"
                                                    class="text-end fw-bold"
                                                >
                                                    Tổng cộng:
                                                </td>
                                                <td class="text-end fw-bold">
                                                    {{
                                                        formatCurrency(
                                                            chitietTotals.total_amount
                                                        )
                                                    }}
                                                </td>
                                                <td class="text-end fw-bold">
                                                    {{
                                                        formatCurrency(
                                                            chitietTotals.total_hold_amount
                                                        )
                                                    }}
                                                </td>
                                                <td class="text-end fw-bold">
                                                    {{
                                                        formatCurrency(
                                                            chitietTotals.total_payment_amount
                                                        )
                                                    }}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Phiếu thu hồi nợ -->
                    <div class="card mt-3">
                        <div
                            class="card-header border-0 bg-transparent d-flex justify-content-between align-items-center"
                        >
                            <h5 class="card-title mb-0">
                                <span class="toggle-section cursor-pointer">
                                    <i
                                        class="fas fa-angle-down me-2 toggle-icon"
                                    ></i>
                                    Phiếu thu cấn trừ nợ
                                </span>
                            </h5>
                        </div>
                        <div class="card-body" style="display: none">
                            <div class="table-container">
                                <div class="table-responsive mt-2">
                                    <table
                                        class="table table-bordered table-hover align-middle"
                                    >
                                        <thead class="table-light text-center">
                                            <tr>
                                                <th>STT</th>
                                                <th>Mã số phiếu</th>
                                                <th>Invoice Number</th>
                                                <th>Đã trả gốc</th>
                                                <th>Ngày vay</th>
                                                <th>Ngày trả</th>
                                                <th>Lãi suất</th>
                                                <th>Tiền lãi</th>
                                                <th>Vụ đầu tư</th>
                                                <th>Vụ thanh toán</th>
                                                <th>Khách hàng cá nhân</th>
                                                <th>KH doanh nghiệp</th>
                                                <th>Số trờ trình</th>
                                                <th>Category Debt</th>
                                                <th>Description</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-if="phieuThuNo.length === 0">
                                                <td
                                                    colspan="15"
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
                                                ) in phieuThuNo"
                                                :key="index"
                                            >
                                                <td class="text-center">
                                                    {{ index + 1 }}
                                                </td>
                                                <td>{{ item.ma_so_phieu }}</td>
                                                <td>
                                                    {{ item.invoice_number }}
                                                </td>
                                                <td class="text-end">
                                                    {{
                                                        formatCurrency(
                                                            item.da_tra_goc
                                                        )
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        formatDate(
                                                            item.ngay_vay
                                                        )
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        formatDate(
                                                            item.ngay_tra
                                                        )
                                                    }}
                                                </td>
                                                <td class="text-center">
                                                    {{ item.lai_suat }}%
                                                </td>
                                                <td class="text-end">
                                                    {{
                                                        formatCurrency(
                                                            item.tien_lai
                                                        )
                                                    }}
                                                </td>
                                                <td>{{ item.vu_dau_tu }}</td>
                                                <td>
                                                    {{ item.vu_thanh_toan }}
                                                </td>
                                                <td>
                                                    {{
                                                        item.khach_hang_ca_nhan
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        item.khach_hang_doanh_nghiep
                                                    }}
                                                </td>
                                                <td>{{ item.so_tro_trinh }}</td>
                                                <td>
                                                    {{ item.category_debt }}
                                                </td>
                                                <td>{{ item.description }}</td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td
                                                    colspan="3"
                                                    class="text-end fw-bold"
                                                >
                                                    Tổng cộng:
                                                </td>
                                                <td class="text-end fw-bold">
                                                    {{
                                                        formatCurrency(
                                                            phieuThuNoTotals.total_deduction
                                                        )
                                                    }}
                                                </td>
                                                <td colspan="3"></td>
                                                <td class="text-end fw-bold">
                                                    {{
                                                        formatCurrency(
                                                            phieuThuNoTotals.total_interest
                                                        )
                                                    }}
                                                </td>
                                                <td colspan="7"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </PerfectScrollbar>
        </div>
    </div>
</template>
<script>
import { useStore } from "../../Store/Auth";
import axios from "axios";
import Swal from "sweetalert2";

export default {
    setup() {
        const store = useStore();
        return { store };
    },
    data() {
        return {
            document: {
                ma_giai_ngan: "",
                vu_dau_tu: "",
                loai_thanh_toan: "",
                trang_thai_thanh_toan: "",
                khach_hang_ca_nhan: "",
                ma_khach_hang_ca_nhan: "",
                khach_hang_doanh_nghiep: "",
                ma_khach_hang_doanh_nghiep: "",
                tong_tien: 0,
                tong_tien_tam_giu: 0,
                tong_tien_khau_tru: 0,
                tong_tien_lai_suat: 0,
                tong_tien_thanh_toan_con_lai: 0,
                ngay_thanh_toan: "",
                attachment_url: "", // Add this line
                so_to_trinh: "", // Add this line
                so_dot_thanh_toan: "", // Add this line
                comment: "",
            },
            bienbans: [], // Array to hold nghiệm thu dịch vụ data
            totals: {
                total_amount: 0,
                total_hold_amount: 0,
                total_payment_amount: 0,
            },
            chitietDichVu: [], // Array to hold chi tiết nghiệm thu dịch vụ data
            chitietTotals: {
                total_amount: 0,
                total_hold_amount: 0,
                total_payment_amount: 0,
            },
            phieuThuNo: [], // Array to hold phieu thu no dich vu data
            phieuThuNoTotals: {
                total_deduction: 0,
                total_interest: 0,
            },
            // Timeline related properties
            processingHistory: [], // Store timeline events
            showTimeline: false,
            users: {}, // Store user data by ID
            paymentRequestCode: null, // Store the parent payment request code
            isEditing: false,
            editableDocument: {
                ma_giai_ngan: "",
                ngay_thanh_toan: "",
                comment: "",
            },
            isLoading: false,
            showDetails: true,
        };
    },
    computed: {
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
                (item) => item.action === "rejected"
            );
        },

        daysBetweenProcessingAndSubmitted() {
            if (!this.processingAction || !this.submittedAction) return null;
            const processingDate = new Date(this.processingAction.created_at);
            const submittedDate = new Date(this.submittedAction.created_at);
            const diffTime = Math.abs(submittedDate - processingDate);
            return Math.ceil(diffTime / (1000 * 60 * 60 * 24)); // Convert ms to days
        },

        daysBetweenSubmittedAndPaid() {
            if (!this.submittedAction) return null;

            // If we don't have either payment_date or paidAction, return null
            if (!this.document.ngay_thanh_toan && !this.paidAction) return null;

            const submittedDate = new Date(this.submittedAction.created_at);

            // Use the actual payment date if available, otherwise use the status change date
            const paidDate = this.document.ngay_thanh_toan
                ? new Date(this.document.ngay_thanh_toan)
                : new Date(this.paidAction.created_at);

            const diffTime = Math.abs(paidDate - submittedDate);
            return Math.ceil(diffTime / (1000 * 60 * 60 * 24)); // Convert ms to days
        },
        documentStatusClass() {
            return {
                processing:
                    this.document.trang_thai_thanh_toan === "processing",
                submitted: this.document.trang_thai_thanh_toan === "submitted",
                paid: this.document.trang_thai_thanh_toan === "paid",
                rejected: this.document.trang_thai_thanh_toan === "rejected",
            };
        },

        statusClass() {
            const status = this.document.trang_thai_thanh_toan;
            if (!status) return "";

            const classes = {
                submitted: "badge bg-primary",
                processing: "badge bg-warning",
                paid: "badge bg-success",
                rejected: "badge bg-danger",
            };

            return classes[status] || "badge bg-secondary";
        },
        statusIcon() {
            const icons = {
                processing: "fa-spinner fa-spin",
                submitted: "fa-paper-plane",
                paid: "fa-check-circle",
                rejected: "fa-times-circle",
            };
            return (
                icons[this.document.trang_thai_thanh_toan] ||
                "fa-question-circle"
            );
        },
    },
    mounted() {
        this.fetchUserData();
        this.fetchDocument();
        this.fetchBienBanData();
        this.fetchChiTietDichVu();
        this.fetchPhieuThuNo();
        // Setup toggle functionality for sections
        this.setupToggleSections();
    },
    methods: {
        fetchPhieuThuNo() {
            const id = this.$route.params.id;
            if (!id) {
                this.showError("Không tìm thấy mã phiếu đề nghị thanh toán");
                return;
            }

            axios
                .get(`/api/payment-requests-dichvu/${id}/phieu-thu-no`, {
                    headers: {
                        Authorization: "Bearer " + this.store.getToken,
                    },
                })
                .then((response) => {
                    if (response.data.success) {
                        this.phieuThuNo = response.data.data || [];
                        this.phieuThuNoTotals = response.data.totals || {
                            total_deduction: 0,
                            total_interest: 0,
                        };
                    } else {
                        this.showError(
                            response.data.message ||
                                "Không thể tải dữ liệu phiếu thu nợ"
                        );
                    }
                })
                .catch((error) => {
                    console.error("Error fetching phieu thu no data:", error);
                    this.showError("Lỗi khi tải dữ liệu phiếu thu nợ");
                    if (error.response?.status === 401) {
                        this.handleAuthError();
                    }
                });
        },
        fetchChiTietDichVu() {
            const id = this.$route.params.id;
            if (!id) {
                this.showError("Không tìm thấy mã phiếu đề nghị thanh toán");
                return;
            }

            axios
                .get(`/api/payment-requests-dichvu/${id}/chitiet-dichvu`, {
                    headers: {
                        Authorization: "Bearer " + this.store.getToken,
                    },
                })
                .then((response) => {
                    if (response.data.success) {
                        this.chitietDichVu = response.data.data || [];
                        this.chitietTotals = response.data.totals || {
                            total_amount: 0,
                            total_hold_amount: 0,
                            total_payment_amount: 0,
                        };
                    } else {
                        this.showError(
                            response.data.message ||
                                "Không thể tải dữ liệu chi tiết nghiệm thu"
                        );
                    }
                })
                .catch((error) => {
                    console.error(
                        "Error fetching chi tiết nghiệm thu data:",
                        error
                    );
                    this.showError("Lỗi khi tải dữ liệu chi tiết nghiệm thu");
                    if (error.response?.status === 401) {
                        this.handleAuthError();
                    }
                });
        },
        fetchBienBanData() {
            const id = this.$route.params.id;
            if (!id) {
                this.showError("Không tìm thấy mã phiếu đề nghị thanh toán");
                return;
            }

            axios
                .get(`/api/payment-requests-dichvu/${id}/bienban-nghiemthu`, {
                    headers: {
                        Authorization: "Bearer " + this.store.getToken,
                    },
                })
                .then((response) => {
                    if (response.data.success) {
                        this.bienbans = response.data.data || [];
                        this.totals = response.data.totals || {
                            total_amount: 0,
                            total_hold_amount: 0,
                            total_payment_amount: 0,
                        };
                    } else {
                        this.showError(
                            response.data.message ||
                                "Không thể tải dữ liệu nghiệm thu"
                        );
                    }
                })
                .catch((error) => {
                    console.error("Error fetching nghiệm thu data:", error);
                    this.showError("Lỗi khi tải dữ liệu nghiệm thu");
                    if (error.response?.status === 401) {
                        this.handleAuthError();
                    }
                });
        },
        fetchUserData() {
            const user = localStorage.getItem("web_user");
            if (user) {
                this.user = JSON.parse(user);
            }
        },
        fetchDocument() {
            // Reset edit state
            this.isEditing = false;
            const id = this.$route.params.id;
            if (!id) {
                this.showError("Không tìm thấy mã phiếu đề nghị thanh toán");
                return;
            }

            this.isLoading = true;

            axios
                .get(`/api/payment-requests-dichvu/${id}`, {
                    headers: {
                        Authorization: "Bearer " + this.store.getToken,
                    },
                })
                .then((response) => {
                    if (response.data.success) {
                        this.document = {
                            ma_giai_ngan:
                                response.data.document.ma_giai_ngan || "",
                            vu_dau_tu: response.data.document.vu_dau_tu || "",
                            loai_thanh_toan:
                                response.data.document.loai_thanh_toan || "",
                            trang_thai_thanh_toan:
                                response.data.document.trang_thai_thanh_toan ||
                                "",
                            khach_hang_ca_nhan:
                                response.data.document.khach_hang_ca_nhan || "",
                            ma_khach_hang_ca_nhan:
                                response.data.document.ma_khach_hang_ca_nhan ||
                                "",
                            khach_hang_doanh_nghiep:
                                response.data.document
                                    .khach_hang_doanh_nghiep || "",
                            ma_khach_hang_doanh_nghiep:
                                response.data.document
                                    .ma_khach_hang_doanh_nghiep || "",
                            tong_tien: response.data.document.tong_tien || 0,
                            tong_tien_tam_giu:
                                response.data.document.tong_tien_tam_giu || 0,
                            tong_tien_khau_tru:
                                response.data.document.tong_tien_khau_tru || 0,
                            tong_tien_lai_suat:
                                response.data.document.tong_tien_lai_suat || 0,
                            tong_tien_thanh_toan_con_lai:
                                response.data.document
                                    .tong_tien_thanh_toan_con_lai || 0,
                            ngay_thanh_toan:
                                response.data.document.ngay_thanh_toan || "",
                            attachment_url:
                                response.data.document.attachment_url || "",
                            so_to_trinh:
                                response.data.document.so_to_trinh || "",
                            so_dot_thanh_toan:
                                response.data.document.so_dot_thanh_toan || "",
                            comment: response.data.document.comment || "",
                        };
                        // แปลงวันที่ให้อยู่ในรูปแบบที่ถูกต้องสำหรับ input type="date"
                        const formattedDate = this.formatDateForInput(
                            this.document.ngay_thanh_toan
                        );

                        // เตรียมข้อมูลสำหรับการแก้ไขทันที
                        this.editableDocument = {
                            ma_giai_ngan: this.document.ma_giai_ngan || "",
                            ngay_thanh_toan: this.formatDateForInput(
                                this.document.ngay_thanh_toan
                            ),
                            comment: this.document.comment || "",
                        };

                        // Update form fields with document data
                        this.$nextTick(() => {
                            this.updateFormFields();
                        });
                    } else {
                        this.showError(
                            response.data.message ||
                                "Không tìm thấy thông tin phiếu đề nghị"
                        );
                    }
                })
                .catch((error) => {
                    console.error("Error fetching document:", error);
                    this.showError("Lỗi khi tải thông tin phiếu đề nghị");
                    if (error.response?.status === 401) {
                        this.handleAuthError();
                    }
                })
                .finally(() => {
                    this.isLoading = false;
                });
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
            try {
                return new Date(date).toLocaleDateString("vi-VN");
            } catch (e) {
                console.error("Error formatting date:", e);
                return "N/A";
            }
        },

        formatStatus(status) {
            const statusMap = {
                submitted: "Đã nộp kế toán",
                processing: "Đang xử lý",
                paid: "Đã thanh toán",
                rejected: "Từ chối",
            };
            return statusMap[status] || status || "N/A";
        },

        toggleDetails() {
            this.showDetails = !this.showDetails;
        },

        // Toggle timeline view
        toggleTimelineView() {
            this.showTimeline = !this.showTimeline;

            // If closing timeline, ensure document data is available
            if (!this.showTimeline) {
                // Force re-render document data
                this.$nextTick(() => {
                    this.updateFormFields();
                });
            }
            // If showing timeline and no history data yet, fetch it
            else if (
                !this.processingHistory ||
                this.processingHistory.length === 0
            ) {
                this.fetchProcessingHistory();
            }
            // If showing timeline and users need to be loaded, do it
            else if (
                this.processingHistory &&
                this.processingHistory.length > 0 &&
                Object.keys(this.users).length === 0
            ) {
                this.loadUsers();
            }
        },

        // Fetch processing history
        async fetchProcessingHistory() {
            const id = this.$route.params.id;
            if (!id) {
                this.showError("Không tìm thấy mã phiếu đề nghị thanh toán");
                return;
            }

            try {
                const response = await axios.get(
                    `/api/payment-requests-dichvu/${id}/processing-history`,
                    {
                        headers: {
                            Authorization: "Bearer " + this.store.getToken,
                        },
                    }
                );

                if (response.data.success) {
                    this.processingHistory = response.data.history || [];

                    // Store the parent payment request code if needed
                    if (response.data.payment_code) {
                        this.paymentRequestCode = response.data.payment_code;
                    }

                    // Update document status if needed based on latest action
                    if (this.processingHistory.length > 0) {
                        // Sort by created_at descending to get the latest action
                        const sortedHistory = [...this.processingHistory].sort(
                            (a, b) =>
                                new Date(b.created_at) - new Date(a.created_at)
                        );

                        const latestAction = sortedHistory[0];

                        // Update the document status if it's different from current status
                        if (
                            latestAction &&
                            latestAction.action !==
                                this.document.trang_thai_thanh_toan
                        ) {
                            this.document.trang_thai_thanh_toan =
                                latestAction.action;
                            // console.log(
                            //     "Document status updated to:",
                            //     this.document.trang_thai_thanh_toan
                            // );
                            this.$forceUpdate();
                        }
                    }

                    // If timeline is being shown, load user data for display
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
        // Add this new method to update form fields
        updateFormFields() {
            if (!this.document) return;

            // Use optional chaining with getElementById and add null checks before setting values
            const maGiaiNgan = document.getElementById("maGiaiNgan");
            if (maGiaiNgan) maGiaiNgan.value = this.document.ma_giai_ngan || "";

            const vuDauTu = document.getElementById("vuDauTu");
            if (vuDauTu) vuDauTu.value = this.document.vu_dau_tu || "";

            const loaiThanhToan = document.getElementById("loaiThanhToan");
            if (loaiThanhToan)
                loaiThanhToan.value = this.document.loai_thanh_toan || "";

            // Since trangThai might be using a different approach for display, check if it's an input
            const trangThai = document.getElementById("trangThai");
            if (trangThai && trangThai.tagName === "INPUT") {
                trangThai.value = this.formatStatus(
                    this.document.trang_thai_thanh_toan
                );
            }

            const khachHangCaNhan = document.getElementById("khachHangCaNhan");
            if (khachHangCaNhan)
                khachHangCaNhan.value = this.document.khach_hang_ca_nhan || "";

            const maKhachHangCaNhan =
                document.getElementById("maKhachHangCaNhan");
            if (maKhachHangCaNhan)
                maKhachHangCaNhan.value =
                    this.document.ma_khach_hang_ca_nhan || "";

            const khachHangDoanhNghiep = document.getElementById(
                "khachHangDoanhNghiep"
            );
            if (khachHangDoanhNghiep)
                khachHangDoanhNghiep.value =
                    this.document.khach_hang_doanh_nghiep || "";

            const maKhachHangDoanhNghiep = document.getElementById(
                "maKhachHangDoanhNghiep"
            );
            if (maKhachHangDoanhNghiep)
                maKhachHangDoanhNghiep.value =
                    this.document.ma_khach_hang_doanh_nghiep || "";

            const soToTrinh = document.getElementById("soToTrinh");
            if (soToTrinh) soToTrinh.value = this.document.so_to_trinh || "";

            const soDotThanhToan = document.getElementById("soDotThanhToan");
            if (soDotThanhToan)
                soDotThanhToan.value = this.document.so_dot_thanh_toan || "";

            // Handle date field
            const ngayThanhToan = document.getElementById("ngayThanhToan");
            if (ngayThanhToan) {
                if (this.document.ngay_thanh_toan) {
                    const formattedDate = this.formatDateForInput(
                        this.document.ngay_thanh_toan
                    );
                    ngayThanhToan.value = formattedDate;
                    // Update editableDocument
                    this.editableDocument.ngay_thanh_toan = formattedDate;
                } else {
                    ngayThanhToan.value = "";
                    this.editableDocument.ngay_thanh_toan = "";
                }
            }

            // Financial fields
            const tongTien = document.getElementById("tongTien");
            if (tongTien)
                tongTien.value = this.formatCurrency(this.document.tong_tien);

            const tongTienTamGiu = document.getElementById("tongTienTamGiu");
            if (tongTienTamGiu)
                tongTienTamGiu.value = this.formatCurrency(
                    this.document.tong_tien_tam_giu
                );

            const tongTienKhauTru = document.getElementById("tongTienKhauTru");
            if (tongTienKhauTru)
                tongTienKhauTru.value = this.formatCurrency(
                    this.document.tong_tien_khau_tru
                );

            const tongTienLaiSuat = document.getElementById("tongTienLaiSuat");
            if (tongTienLaiSuat)
                tongTienLaiSuat.value = this.formatCurrency(
                    this.document.tong_tien_lai_suat
                );

            const tongTienConLai = document.getElementById("tongTienConLai");
            if (tongTienConLai)
                tongTienConLai.value = this.formatCurrency(
                    this.document.tong_tien_thanh_toan_con_lai
                );

            // Re-setup toggle functionality for sections
            this.$nextTick(() => {
                this.setupToggleSections();
            });
        },
        // Setup toggle sections functionality
        setupToggleSections() {
            document.querySelectorAll(".toggle-section").forEach((el) => {
                const icon = el.querySelector(".toggle-icon");
                const cardBody = el
                    .closest(".card")
                    .querySelector(".card-body");

                // Ensure icon is right-pointing (collapsed state)
                if (icon.classList.contains("fa-angle-down")) {
                    icon.classList.replace("fa-angle-down", "fa-angle-right");
                }

                // Hide the card body initially
                cardBody.style.display = "none";

                // Remove any existing event listener
                const newEl = el.cloneNode(true);
                el.parentNode.replaceChild(newEl, el);

                // Add click event listener for toggling
                newEl.addEventListener("click", () => {
                    const icon = newEl.querySelector(".toggle-icon");
                    const cardBody = newEl
                        .closest(".card")
                        .querySelector(".card-body");

                    if (icon.classList.contains("fa-angle-right")) {
                        icon.classList.replace(
                            "fa-angle-right",
                            "fa-angle-down"
                        );
                        cardBody.style.display = "block";
                    } else {
                        icon.classList.replace(
                            "fa-angle-down",
                            "fa-angle-right"
                        );
                        cardBody.style.display = "none";
                    }
                });
            });
        },
        // Load user data for timeline
        async loadUsers() {
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
                    newUsers.forEach((user) => {
                        this.users[user.id] = user;
                    });
                }
            } catch (error) {
                console.error("Error loading users:", error);
            }
        },

        // Add these new methods for editing functionality
        toggleEditing() {
            if (this.isEditing) {
                // Cancel editing - reset values
                this.cancelEdit();
            } else {
                // Enter edit mode - copy values
                this.startEdit();
            }
            this.isEditing = !this.isEditing;
        },

        startEdit() {
            // Copy values from document to editable document
            this.editableDocument = {
                ma_giai_ngan: this.document.ma_giai_ngan || "",
                ngay_thanh_toan: this.document.ngay_thanh_toan
                    ? this.formatDateForInput(this.document.ngay_thanh_toan)
                    : "",
                comment: this.document.comment || "", // แก้ไขจาก comment เป็น comment
            };
        },

        cancelEdit() {
            // Reset editable document
            this.editableDocument = {
                ma_giai_ngan: "",
                ngay_thanh_toan: "",
                comment: "",
            };
        },
        formatDateForInput(dateString) {
            if (!dateString) return "";

            try {
                // กรณีที่วันที่อยู่ในรูปแบบ yyyy-MM-dd แล้ว
                if (dateString.match(/^\d{4}-\d{2}-\d{2}$/)) {
                    return dateString;
                }

                // กรณีวันที่อยู่ในรูปแบบ dd/MM/yyyy
                if (dateString.includes("/")) {
                    const parts = dateString.split("/");
                    if (parts.length === 3) {
                        const day = parts[0].padStart(2, "0");
                        const month = parts[1].padStart(2, "0");
                        const year = parts[2];
                        return `${year}-${month}-${day}`;
                    }
                }

                // กรณีวันที่อยู่ในรูปแบบ Date object หรือ ISO string
                const date = new Date(dateString);
                if (!isNaN(date.getTime())) {
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, "0");
                    const day = String(date.getDate()).padStart(2, "0");
                    return `${year}-${month}-${day}`;
                }

                return "";
            } catch (e) {
                console.error(
                    "Error formatting date:",
                    e,
                    "for value:",
                    dateString
                );
                return "";
            }
        },
        // Add update method
        // แก้ไขใน method UpdateDocument
        UpdateDocument() {
            const id = this.$route.params.id;
            if (!id) {
                this.showError("Không tìm thấy mã phiếu đề nghị thanh toán");
                return;
            }

            // Prepare data for update
            const updateData = {
                comment: this.editableDocument.comment, // แก้ไขจาก comment เป็น comment
            };

            // เพิ่มวันที่เฉพาะเมื่อมีการกรอกข้อมูล
            if (this.editableDocument.ngay_thanh_toan) {
                updateData.ngay_thanh_toan =
                    this.editableDocument.ngay_thanh_toan;
            }

            // Show loading indicator
            this.isLoading = true;

            // Call API to update document
            axios
                .put(`/api/payment-requests-dichvu/${id}/update`, updateData, {
                    headers: {
                        Authorization: "Bearer " + this.store.getToken,
                    },
                })
                .then((response) => {
                    if (response.data.success) {
                        // Update local document
                        this.document.ma_giai_ngan = updateData.ma_giai_ngan;
                        if (updateData.ngay_thanh_toan) {
                            this.document.ngay_thanh_toan =
                                updateData.ngay_thanh_toan;
                        }
                        this.document.comment = updateData.comment; // แก้ไขจาก comment เป็น comment

                        // Exit edit mode
                        this.isEditing = false;

                        // Update form fields
                        this.updateFormFields();

                        // Show success message
                        this.showSuccess("Cập nhật thành công");

                        // Refresh document data
                        this.fetchDocument();
                    } else {
                        this.showError(
                            response.data.message ||
                                "Không thể cập nhật dữ liệu"
                        );
                    }
                })
                .catch((error) => {
                    console.error("Error updating document:", error);
                    this.showError(
                        "Lỗi khi cập nhật thông tin: " +
                            (error.response?.data?.message || error.message)
                    );

                    if (error.response?.status === 401) {
                        this.handleAuthError();
                    }
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },
        // Get user name by ID
        getUserName(userId) {
            if (!userId) return "N/A";
            return this.users[userId]?.full_name || "N/A";
        },

        handleAuthError() {
            // Handle authentication error
            this.showError("Phiên làm việc đã hết hạn, vui lòng đăng nhập lại");
            localStorage.removeItem("access_token");
            localStorage.removeItem("web_user");
            this.$router.push("/login");
        },
        openAttachment() {
            // This function will open the attachment URL
            const attachmentUrl = this.document.attachment_url || "#";

            if (attachmentUrl && attachmentUrl !== "#") {
                // Check if URL starts with http/https
                if (
                    attachmentUrl.startsWith("http://") ||
                    attachmentUrl.startsWith("https://")
                ) {
                    window.open(attachmentUrl, "_blank");
                } else {
                    // Assume it's a relative path
                    window.open(`/${attachmentUrl}`, "_blank");
                }
            } else {
                this.showError("Không tìm thấy file đính kèm");
            }
        },

        showSuccess(message) {
            Swal.fire({
                icon: "success",
                title: "Thành công",
                text: message,
                timer: 2000,
                timerProgressBar: true,
            });
        },

        showError(message) {
            Swal.fire({
                icon: "error",
                title: "Lỗi",
                text: message,
            });
        },

        goBack() {
            this.$router.go(-1);
        },
    },
};
</script>
<style scoped>
/* Action button clik link go to nghiem thu dv */
.receipt-link {
    color: #0d6efd;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s ease;
}

.receipt-link:hover {
    color: #0a58ca;
    text-decoration: underline;
}
/* Action buttons */
.action-button-group {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}
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

/* Container for buttons and progress */
.container-fluid {
    max-width: inherit;
    margin: 0 auto;
    background: white;
}

/* Progress container styles */
.progress-container {
    margin-bottom: 0.5rem;
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
    border-radius: 2px;
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

.progress-tracker.rejected::before {
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

.status-pending {
    color: #ffc107;
}

.status-approved {
    color: #1e88e5;
}

.status-paid {
    color: #198754;
}

.status-cancelled {
    color: #dc3545;
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

.track-step.active .status-cancelled {
    background-color: #dc3545;
    color: white;
}

.step-label {
    text-align: center;
    font-size: 0.9rem;
    max-width: 100px;
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

/* Responsive styles */
@media (max-width: 992px) {
    .sticky-wrapper {
        left: 0;
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

    .step-icon {
        width: 35px;
        height: 35px;
        font-size: 1rem;
    }

    .track-step.active .step-icon {
        width: 40px;
        height: 40px;
        font-size: 1.2rem;
    }

    .step-label {
        font-size: 0.8rem;
    }

    .d-flex.flex-md-row {
        flex-direction: column !important;
    }

    .col-md-6 {
        width: 100% !important;
    }
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

/* Card styling */
.card {
    border: none;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    border-radius: 5px;
    overflow: hidden;
}

/* Table Professional Enhancement */
.table {
    font-size: 0.8125rem; /* 13px */
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

/* Align numerical data right */
.table td.text-end,
.table th.text-end {
    text-align: right;
}

/* Highlight active row - add .active class to tr when needed */
.table tr.active {
    background-color: rgba(16, 185, 129, 0.1) !important;
}

/* Footer styling */
.table tfoot {
    font-weight: 600;
    background-color: #f8f9fa;
}

.table tfoot td {
    border-top: 2px solid #e9ecef;
}

/* Note styling */
.note-container {
    background-color: #f9f9f9;
    border: 1px solid #e6e6e6;
    border-radius: 4px;
    padding: 10px 15px;
    min-height: 60px;
    transition: all 0.2s ease;
}

.note-container:hover {
    border-color: #d1d1d1;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.note-content {
    white-space: pre-wrap;
    word-break: break-word;
    font-size: 0.9rem;
    line-height: 1.5;
}

.note-content .text-muted {
    font-size: 0.85rem;
}

/* Add these styles to your existing <style> section */

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
    font-size: 1rem;
    font-weight: 500;
    color: white;
    transition: all 0.3s ease;
}

.status-display i {
    margin-right: 0.5rem;
    font-size: 1.1rem;
}

.status-display.processing {
    background-color: #ffc107;
}

.status-display.submitted {
    background-color: #1e88e5;
}

.status-display.paid {
    background-color: #28a745;
}

.status-display.rejected {
    background-color: #dc3545;
}
</style>
