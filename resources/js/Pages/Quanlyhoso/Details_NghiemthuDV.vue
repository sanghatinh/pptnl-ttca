<template lang="">
    <breadcrumb-vue />
    <div class="timeline-view" v-if="showTimeline">
        <!-- Timeline Header -->
        <div class="timeline-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="timeline-title">
                    <i class="fas fa-history me-2 text-primary"></i>
                    Lịch sử xử lý
                </h5>
                <button class="btn-close-timeline" @click="onClose">
                    <i class="fas fa-times"></i>
                    <span class="ms-1">Đóng</span>
                </button>
            </div>
        </div>

        <!-- Timeline Content -->
        <div class="timeline-content-wrapper">
            <div class="timeline-scrollarea">
                <div class="timeline-container">
                    <!-- Show loading indicator while fetching history -->
                    <div
                        v-if="isLoadingHistory"
                        class="d-flex justify-content-center py-5"
                    >
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>

                    <!-- Empty State (shown when no timeline data) -->
                    <div
                        v-else-if="!processingHistory.length"
                        class="empty-timeline text-center py-5"
                    >
                        <i
                            class="fas fa-calendar-times fa-4x text-muted mb-3 opacity-50"
                        ></i>
                        <p class="lead text-muted">
                            Không tìm thấy dữ liệu lịch sử
                        </p>
                    </div>

                    <!-- Timeline Items - Dynamically render based on history data -->
                    <div v-else>
                        <div
                            v-for="(item, index) in processingHistory"
                            :key="index"
                            class="timeline-item appear"
                        >
                            <!-- Badge with different colors based on action type -->
                            <div
                                :class="[
                                    'timeline-badge',
                                    getActionClass(item.action),
                                ]"
                            >
                                <i :class="getActionIcon(item.action)"></i>
                            </div>

                            <!-- Content section for each history entry -->
                            <div
                                :class="[
                                    'timeline-content',
                                    `${item.action}-content`,
                                ]"
                            >
                                <h5 class="timeline-title">
                                    <span
                                        :class="[
                                            'badge',
                                            getActionClass(item.action),
                                        ]"
                                    >
                                        {{ formatActionText(item.action) }}
                                    </span>
                                </h5>

                                <!-- Date information -->
                                <p class="mb-1">
                                    <i class="far fa-calendar me-2"></i>
                                    <span class="fw-medium">{{
                                        formatDate(item.date)
                                    }}</span>
                                </p>

                                <!-- User information -->
                                <p class="mb-1">
                                    <i class="far fa-user me-2"></i>
                                    <span class="fw-medium">{{
                                        item.user
                                    }}</span>
                                </p>

                                <!-- Days since previous step - only shown if days > 0 -->
                                <p
                                    class="mb-1"
                                    v-if="item.days_since_previous > 0"
                                >
                                    <i class="far fa-clock me-2"></i>
                                    <span
                                        >{{ item.days_since_previous }} ngày sau
                                        khi xử lý</span
                                    >
                                </p>

                                <!-- Payment amount - only shown for paid status -->
                                <!-- <p
                                    class="mb-1"
                                    v-if="item.action === 'paid' && item.amount"
                                >
                                    <i class="fas fa-money-bill-wave me-2"></i>
                                    <span class="fw-medium">{{
                                        formatCurrency(item.amount)
                                    }}</span>
                                </p> -->

                                <!-- Note/Comment section -->
                                <div class="timeline-note">
                                    <i class="far fa-comment me-2"></i>
                                    <span>{{ item.note }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                <!-- button  -->
                <!-- Fixed top container -->
                <div class="sticky-wrapper">
                    <!-- Add container with padding -->
                    <div class="container-fluid px-4">
                        <div class="button-container">
                            <div class="row align-items-center mb-2"></div>
                        </div>
                        <!-- progress-tracker-container -->
                        <div class="progress-container mt-4">
                            <div class="col-12">
                                <div
                                    class="progress-tracker"
                                    :class="
                                        document.trang_thai_thanh_toan ||
                                        'received'
                                    "
                                    @click="toggleTimelineView"
                                >
                                    <!-- Received Step -->
                                    <div
                                        class="track-step"
                                        :class="{
                                            active: [
                                                'received',
                                                'processing',
                                                'submitted',
                                                'paid',
                                            ].includes(
                                                document.processing_status
                                            ),
                                        }"
                                    >
                                        <div class="step-icon status-received">
                                            <i
                                                :class="
                                                    getReceivedStepIcon(
                                                        document.trang_thai_nhan_hs
                                                    )
                                                "
                                            ></i>
                                        </div>
                                        <span class="step-label">
                                            {{
                                                getReceivedStepLabel(
                                                    document.trang_thai_nhan_hs
                                                )
                                            }}
                                        </span>
                                    </div>

                                    <!-- Processing Step -->
                                    <div
                                        class="track-step"
                                        :class="{
                                            active: [
                                                'processing',
                                                'submitted',
                                                'paid',
                                            ].includes(
                                                document.trang_thai_thanh_toan
                                            ),
                                        }"
                                    >
                                        <div
                                            class="step-icon status-processing"
                                        >
                                            <i class="fas fa-cog fa-spin"></i>
                                        </div>
                                        <span class="step-label"
                                            >Đang xử lý</span
                                        >
                                    </div>

                                    <!-- Submitted to Accounting Step -->
                                    <div
                                        class="track-step"
                                        :class="{
                                            active: [
                                                'submitted',
                                                'paid',
                                            ].includes(
                                                document.trang_thai_thanh_toan
                                            ),
                                        }"
                                    >
                                        <div class="step-icon status-submitted">
                                            <i class="fas fa-file-invoice"></i>
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
                        <!-- Thông tin nghiệm thu -->
                        <div class="card col-12 col-md-6">
                            <div class="card-body">
                                <h5 class="card-title">Thông tin nghiệm thu</h5>
                                <div class="row gutters">
                                    <!-- Mã nghiệm thu -->
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="maNghiemThu"
                                                class="form-label"
                                            >
                                                Mã nghiệm thu
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="maNghiemThu"
                                                :value="document.ma_nghiem_thu"
                                                disabled
                                            />
                                        </div>
                                    </div>
                                    <!-- Trạm -->
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="tram"
                                                class="form-label"
                                            >
                                                Trạm
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="tram"
                                                :value="document.tram"
                                                disabled
                                            />
                                        </div>
                                    </div>
                                    <!-- Cán bộ nông vụ -->
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="canBoNongVu"
                                                class="form-label"
                                            >
                                                Cán bộ nông vụ
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="canBoNongVu"
                                                :value="document.can_bo_nong_vu"
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
                                                :value="document.vu_dau_tu"
                                                disabled
                                            />
                                        </div>
                                    </div>
                                    <!-- Tiêu đề -->
                                    <div class="col-12">
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
                                                :value="document.tieu_de"
                                                disabled
                                            />
                                        </div>
                                    </div>
                                    <!-- Khách hàng cá nhân ĐT mía -->
                                    <div class="col-12">
                                        <div class="form-group mb-3">
                                            <label
                                                for="khachHangCaNhan"
                                                class="form-label"
                                            >
                                                Khách hàng cá nhân ĐT mía
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="khachHangCaNhan"
                                                :value="
                                                    document.khach_hang_ca_nhan_dt_mia
                                                "
                                                disabled
                                            />
                                        </div>
                                    </div>
                                    <!-- Khách hàng doanh nghiệp ĐT mía -->
                                    <div class="col-12">
                                        <div class="form-group mb-3">
                                            <label
                                                for="khachHangDN"
                                                class="form-label"
                                            >
                                                Khách hàng doanh nghiệp ĐT mía
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="khachHangDN"
                                                :value="
                                                    document.khach_hang_doanh_nghiep_dt_mia
                                                "
                                                disabled
                                            />
                                        </div>
                                    </div>
                                    <!-- Hợp đồng đầu tư mía -->
                                    <div class="col-12">
                                        <div class="form-group mb-3">
                                            <label
                                                for="hopDongDauTu"
                                                class="form-label"
                                            >
                                                Hợp đồng đầu tư mía
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="hopDongDauTu"
                                                :value="
                                                    document.hop_dong_dau_tu_mia
                                                "
                                                disabled
                                            />
                                        </div>
                                    </div>
                                    <!-- Hình thức thực hiện DV -->
                                    <div class="col-12">
                                        <div class="form-group mb-3">
                                            <label
                                                for="hinhThucDV"
                                                class="form-label"
                                            >
                                                Hình thức thực hiện DV
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="hinhThucDV"
                                                :value="
                                                    document.hinh_thuc_thuc_hien_dv
                                                "
                                                disabled
                                            />
                                        </div>
                                    </div>
                                    <!-- Hợp đồng cung ứng dịch vụ -->
                                    <div class="col-12">
                                        <div class="form-group mb-3">
                                            <label
                                                for="hopDongDichVu"
                                                class="form-label"
                                            >
                                                Hợp đồng cung ứng dịch vụ
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="hopDongDichVu"
                                                :value="
                                                    document.hop_dong_cung_ung_dich_vu
                                                "
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
                                <h5 class="card-title">
                                    Thông tin tài chính và giao nhận
                                </h5>
                                <div class="row gutters">
                                    <!-- Tổng tiền -->
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="tongTien"
                                                class="form-label"
                                            >
                                                Tổng tiền
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="tongTien"
                                                :value="
                                                    formatCurrency(
                                                        document.tong_tien_dich_vu
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
                                                for="tienTamGiu"
                                                class="form-label"
                                            >
                                                Tổng tiền tạm giữ
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="tienTamGiu"
                                                :value="
                                                    formatCurrency(
                                                        document.tong_tien_tam_giu
                                                    )
                                                "
                                                disabled
                                            />
                                        </div>
                                    </div>
                                    <!-- Tổng tiền thanh toán -->
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="tienThanhToan"
                                                class="form-label"
                                            >
                                                Tổng tiền thanh toán
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="tienThanhToan"
                                                :value="
                                                    formatCurrency(
                                                        document.tong_tien_thanh_toan
                                                    )
                                                "
                                                disabled
                                            />
                                        </div>
                                    </div>
                                    <!-- Mã giải ngân -->
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="maGiaiNgan"
                                                class="form-label"
                                            >
                                                Mã giải ngân
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="maGiaiNgan"
                                                :value="
                                                    document.ma_de_nghi_giai_ngan ||
                                                    'N/A'
                                                "
                                                disabled
                                            />
                                        </div>
                                    </div>
                                    <!-- Người giao hồ sơ -->
                                    <div class="col-12">
                                        <div class="form-group mb-3">
                                            <label
                                                for="nguoiGiao"
                                                class="form-label"
                                            >
                                                Người giao hồ sơ
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="nguoiGiao"
                                                :value="document.nguoi_giao"
                                                disabled
                                            />
                                        </div>
                                    </div>
                                    <!-- Người nhận hồ sơ -->
                                    <div class="col-12">
                                        <div class="form-group mb-3">
                                            <label
                                                for="nguoiNhan"
                                                class="form-label"
                                            >
                                                Người nhận hồ sơ
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="nguoiNhan"
                                                :value="document.nguoi_nhan"
                                                disabled
                                            />
                                        </div>
                                    </div>
                                    <!-- Ngày nhận hồ sơ -->
                                    <div class="col-12">
                                        <div class="form-group mb-3">
                                            <label
                                                for="ngayNhan"
                                                class="form-label"
                                            >
                                                Ngày nhận hồ sơ
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="ngayNhan"
                                                :value="
                                                    formatDate(
                                                        document.ngay_nhan
                                                    )
                                                "
                                                disabled
                                            />
                                        </div>
                                    </div>
                                    <!-- Tình trạng giao nhận hồ sơ -->
                                    <div class="col-12">
                                        <div class="form-group mb-3">
                                            <label
                                                for="tinhTrang"
                                                class="form-label"
                                            >
                                                Tình trạng giao nhận hồ sơ
                                            </label>
                                            <div
                                                class="input-group"
                                                style="cursor: pointer"
                                                @click="navigateToHosoList"
                                                title="Click để xem danh sách hồ sơ"
                                            >
                                                <span class="input-group-text">
                                                    <i
                                                        :class="[
                                                            getReceivedStepIcon(
                                                                document.trang_thai_nhan_hs
                                                            ),
                                                            statusClass(
                                                                document.trang_thai_nhan_hs
                                                            ),
                                                        ]"
                                                    ></i>
                                                </span>
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    id="tinhTrang"
                                                    :value="
                                                        formatStatus(
                                                            document.trang_thai_nhan_hs
                                                        )
                                                    "
                                                    :class="
                                                        statusClass(
                                                            document.trang_thai_nhan_hs
                                                        )
                                                    "
                                                    readonly
                                                    style="cursor: pointer"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Thêm Trạng thái thanh toán -->
                                    <div class="col-12">
                                        <div class="form-group mb-3">
                                            <label
                                                for="trangThaiThanhToan"
                                                class="form-label"
                                            >
                                                Trạng thái thanh toán
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i
                                                        :class="[
                                                            paymentStatusIcon(
                                                                document.trang_thai_thanh_toan ||
                                                                    document.processing_status
                                                            ),
                                                            paymentStatusClass(
                                                                document.trang_thai_thanh_toan ||
                                                                    document.processing_status
                                                            ),
                                                        ]"
                                                    ></i>
                                                </span>
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    id="trangThaiThanhToan"
                                                    :value="
                                                        formatPaymentStatus(
                                                            document.trang_thai_thanh_toan ||
                                                                document.processing_status
                                                        )
                                                    "
                                                    :class="
                                                        paymentStatusClass(
                                                            document.trang_thai_thanh_toan ||
                                                                document.processing_status
                                                        )
                                                    "
                                                    disabled
                                                />
                                            </div>
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
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Bảng chi tiết -->
                    <div class="card mt-3">
                        <div class="card-body">
                            <h5 class="card-title">Chi tiết dịch vụ</h5>
                            <div class="table-container">
                                <div class="table-responsive mt-2">
                                    <table
                                        class="table table-bordered table-hover align-middle resizable"
                                        v-resizable-columns="{
                                            minWidth: 80,
                                            saveState: true,
                                            id: 'nghiemthu-table',
                                            adjustTableWidth: false,
                                        }"
                                        style="table-layout: fixed"
                                    >
                                        <thead class="table-light text-center">
                                            <tr>
                                                <th style="width: 50px">STT</th>
                                                <th style="width: 150px">
                                                    Dịch vụ
                                                </th>
                                                <th style="width: 100px">
                                                    Mã số thửa
                                                </th>
                                                <th style="width: 80px">
                                                    Đơn vị tính
                                                </th>
                                                <th style="width: 100px">
                                                    Số lần thực hiện
                                                </th>
                                                <th style="width: 120px">
                                                    Khối lượng thực hiện
                                                </th>
                                                <th style="width: 100px">
                                                    Đơn giá
                                                </th>
                                                <th style="width: 120px">
                                                    Thành tiền
                                                </th>
                                                <th style="width: 120px">
                                                    Số tiền tạm giữ
                                                </th>
                                                <th style="width: 120px">
                                                    Tiền thanh toán
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-if="!serviceDetails.length">
                                                <td
                                                    colspan="10"
                                                    class="text-center py-3 text-muted"
                                                >
                                                    <i
                                                        class="fas fa-info-circle me-2"
                                                    ></i>
                                                    Không có dữ liệu chi tiết
                                                    dịch vụ cho nghiệm thu này
                                                </td>
                                            </tr>
                                            <tr
                                                v-for="(
                                                    item, index
                                                ) in serviceDetails"
                                                :key="index"
                                            >
                                                <td class="text-center">
                                                    {{ index + 1 }}
                                                </td>
                                                <td class="truncate">
                                                    {{ item.dich_vu }}
                                                </td>
                                                <td>{{ item.ma_so_thua }}</td>
                                                <td class="text-center">
                                                    {{ item.don_vi_tinh }}
                                                </td>
                                                <td class="text-end">
                                                    {{
                                                        formatNumber(
                                                            item.so_lan_thuc_hien
                                                        )
                                                    }}
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
                                                            item.tien_con_lai
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
                                            <tr
                                                v-if="serviceDetails.length"
                                                class="table-secondary"
                                            >
                                                <td
                                                    colspan="7"
                                                    class="text-end fw-bold"
                                                >
                                                    Tổng cộng:
                                                </td>
                                                <td class="text-end fw-bold">
                                                    {{
                                                        formatCurrency(
                                                            totalAmount
                                                        )
                                                    }}
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
                                                            totalRemainingAmount
                                                        )
                                                    }}
                                                </td>
                                            </tr>
                                        </tbody>
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
import Swal from "sweetalert2";
import axios from "axios";
import { useStore } from "../../Store/Auth";
import { ResizableColumns } from "../../Directives/ResizableColumns";

export default {
    directives: {
        "resizable-columns": ResizableColumns,
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
                ma_nghiem_thu: "",
                tram: "",
                can_bo_nong_vu: "",
                vu_dau_tu: "",
                tieu_de: "",
                khach_hang_ca_nhan_dt_mia: "",
                khach_hang_doanh_nghiep_dt_mia: "",
                hop_dong_dau_tu_mia: "",
                hinh_thuc_thuc_hien_dv: "",
                hop_dong_cung_ung_dich_vu: "",
                tong_tien_dich_vu: 0,
                tong_tien_tam_giu: 0,
                tong_tien_thanh_toan: 0,
                document_code: "", // Add this line
                document_delivery_id: null, // Add this line
                ma_giai_ngan: "",
                nguoi_giao: "",
                nguoi_nhan: "",
                ngay_nhan: null,
                trang_thai_nhan_hs: "",
                ghi_chu: "",
                processing_status: "received", // received, processing, submitted, paid
                attachment_url: null, // Add this line for the attachment URL
            },
            serviceDetails: [],
            processingHistory: [],
            isLoadingHistory: false,
            user: null,
            isEditingNote: false,
            noteText: "",
            isLoading: false,
            showTimeline: false,
        };
    },
    computed: {
        canEditNote() {
            return true; // Can be modified based on user permissions or document status
        },
        totalAmount() {
            return this.serviceDetails.reduce(
                (sum, item) => sum + (parseFloat(item.thanh_tien) || 0),
                0
            );
        },
        totalPaymentAmount() {
            return this.serviceDetails.reduce(
                (sum, item) => sum + (parseFloat(item.tien_con_lai) || 0),
                0
            );
        },
        totalRemainingAmount() {
            return this.serviceDetails.reduce(
                (sum, item) => sum + (parseFloat(item.tien_thanh_toan) || 0),
                0
            );
        },
    },
    mounted() {
        this.fetchUserData();
        this.fetchDocument();
    },
    methods: {
        fetchUserData() {
            const user = localStorage.getItem("web_user");
            if (user) {
                this.user = JSON.parse(user);
            }
        },
        fetchDocument() {
            const id = this.$route.params.id;
            if (!id) {
                this.showError("Không tìm thấy mã nghiệm thu");
                return;
            }
            // ตรวจสอบ token ก่อนเรียก API
            const token =
                localStorage.getItem("web_token") || this.store.getToken;
            if (!token) {
                // ถ้าไม่มี token ให้ไปหน้า login ทันที
                this.$router.replace("/login");
                return;
            }

            this.isLoading = true;
            axios
                .get(`/api/bienban-nghiemthu/${id}`, {
                    headers: {
                        Authorization: "Bearer " + this.store.getToken,
                    },
                })
                .then((response) => {
                    if (response.data.success) {
                        this.document = {
                            ...response.data.document,
                            document_code:
                                response.data.document.document_code || "",
                            document_delivery_id:
                                response.data.document.document_delivery_id ||
                                null,
                            processing_status:
                                response.data.document.processing_status ||
                                "received",
                            // Store attachment URL for use in openAttachment method
                            attachment_url:
                                response.data.document.attachment_url || null,
                        };
                        this.noteText = this.document.ghi_chu || "";

                        // Set service details
                        this.serviceDetails = response.data.serviceDetails.map(
                            (item) => ({
                                ...item,
                                so_lan_thuc_hien: item.so_lan_thuc_hien || 0,
                                so_luong: item.so_luong || 0,
                                don_gia: item.don_gia || 0,
                                thanh_tien: item.thanh_tien || 0,
                                tien_thanh_toan: item.tien_thanh_toan || 0,
                                tien_thanh_toan_con_lai:
                                    item.tien_thanh_toan_con_lai || 0,
                            })
                        );
                    } else {
                        this.showError(
                            response.data.message ||
                                "Không tìm thấy thông tin nghiệm thu"
                        );
                    }
                })
                .catch((error) => {
                    console.error("Error fetching document:", error);
                    this.showError("Lỗi khi tải thông tin nghiệm thu");
                    if (error.response?.status === 401) {
                        this.handleAuthError();
                    }
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },
        // Toggle timeline view
        toggleTimelineView() {
            this.showTimeline = !this.showTimeline;

            // If showing timeline and no history is loaded, fetch it
            if (this.showTimeline && this.processingHistory.length === 0) {
                this.fetchProcessingHistory();
            }
        },
        fetchProcessingHistory() {
            const id = this.$route.params.id;
            if (!id) return;

            this.isLoadingHistory = true;

            axios
                .get(`/api/bienban-nghiemthu/${id}/history`, {
                    headers: {
                        Authorization: "Bearer " + this.store.getToken,
                    },
                })
                .then((response) => {
                    if (response.data.success) {
                        this.processingHistory = response.data.history;
                    } else {
                        this.showError(
                            response.data.message ||
                                "Không thể tải lịch sử xử lý"
                        );
                    }
                })
                .catch((error) => {
                    console.error("Error fetching processing history:", error);
                    this.showError("Lỗi khi tải lịch sử xử lý");
                    if (error.response?.status === 401) {
                        this.handleAuthError();
                    }
                })
                .finally(() => {
                    this.isLoadingHistory = false;
                });
        },
        formatDayText(days) {
            if (!days) return "";
            return `${days} ngày sau khi ${this.getPreviousStepText(days)}`;
        },
        getPreviousStepText(days) {
            if (!days) return "";
            return "xử lý";
        },
        onClose() {
            this.showTimeline = false;
        },
        openAttachment() {
            if (!this.document.attachment_url) {
                this.showError("Không tìm thấy hồ sơ đính kèm");
                return;
            }

            // Handle URL opening based on format
            const attachmentUrl = this.document.attachment_url;

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
            if (status === "cancelled") return "Hủy";
            if (status === "received") return "Đã nhận";
            if (status === "sending") return "Đang nộp";
            if (status === "creating") return "Đang tạo";
            return status || "N/A";
        },
        formatActionText(action) {
            if (action === "creating") return "Đang tạo";
            if (action === "received") return "Đã nhận hồ sơ";
            if (action === "processing") return "Đang xử lý";
            if (action === "submitted") return "Đã nộp kế toán";
            if (action === "paid") return "Đã thanh toán";
            if (action === "returned") return "Đã trả lại";
            if (action === "note_added") return "Đã thêm ghi chú";
            return action || "N/A";
        },
        statusClass(status) {
            if (status === "received") return "text-success";
            if (status === "sending") return "text-primary";
            if (status === "cancelled") return "text-danger";
            if (status === "creating") return "text-primary";
            return "";
        },
        getActionClass(action) {
            if (action === "creating") return "bg-purple";
            if (action === "received") return "bg-teal";
            if (action === "processing") return "bg-primary";
            if (action === "submitted") return "bg-info";
            if (action === "paid") return "bg-success";
            if (action === "returned") return "bg-danger";
            if (action === "note_added") return "bg-secondary";
            return "bg-secondary";
        },
        getActionIcon(action) {
            if (action === "creating") return "fas fa-pencil-alt";
            if (action === "received") return "fas fas fa-hand-holding-medical";
            if (action === "processing") return "fas fa-cog";
            if (action === "submitted") return "fas fa-file-invoice";
            if (action === "paid") return "fas fa-money-bill-wave";
            if (action === "returned") return "fas fa-undo";
            if (action === "note_added") return "fas fa-sticky-note";
            return "fas fa-circle";
        },
        getReceivedStepIcon(status) {
            switch (status) {
                case "creating":
                    return "fas fa-spinner";
                case "sending":
                    return "fas fa-shipping-fast";
                case "received":
                    return "fas fa-hand-holding-medical";
                case "cancelled":
                    return "fas fa-times-circle";
                default:
                    return "fas fa-edit"; // Default icon
            }
        },
        getReceivedStepLabel(status) {
            switch (status) {
                case "creating":
                    return "Đang tạo";
                case "sending":
                    return "Đang nộp";
                case "received":
                    return "Đã nhận";
                case "cancelled":
                    return "Hủy";
                default:
                    return "Chưa nhận"; // Default label
            }
        },
        formatPaymentStatus(status) {
            switch (status) {
                case "paid":
                    return "Đã thanh toán";
                case "submitted":
                    return "Đã nộp kế toán";
                case "processing":
                    return "Đang xử lý";
                case "received":
                default:
                    return "Chưa thanh toán";
            }
        },
        paymentStatusClass(status) {
            switch (status) {
                case "paid":
                    return "text-success";
                case "submitted":
                    return "text-info";
                case "processing":
                    return "text-primary";
                case "received":
                default:
                    return "text-secondary";
            }
        },
        paymentStatusIcon(status) {
            if (!status) return "";

            const iconMap = {
                processing: "fas fa-cog",
                submitted: "fas fa-paper-plane",
                paid: "fas fa-check-circle",
                cancelled: "fas fa-times-circle",
                rejected: "fas fa-exclamation-circle",
            };

            return iconMap[status] || "fas fa-question-circle";
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
            localStorage.clear();

            this.store.logout();
            this.$router.push("/login");
        },
        //Go to page danh sách hồ sơ Details

        navigateToHosoList() {
            // ตรวจสอบว่ามี document_delivery_id หรือไม่
            const documentDeliveryId = this.document.document_delivery_id;

            if (!documentDeliveryId) {
                this.showError("Không tìm thấy ID hồ sơ giao nhận");
                return;
            }

            // ตรวจสอบสิทธิ์การเข้าถึงก่อนนำทาง
            this.isLoading = true;

            axios
                .get(
                    `/api/document-deliveries/${documentDeliveryId}/check-access`,
                    {
                        headers: {
                            Authorization: `Bearer ${this.store.getToken}`,
                        },
                    }
                )
                .then((response) => {
                    this.isLoading = false;

                    if (response.data.hasAccess) {
                        // นำทางไปยัง document-deliveries page โดยใช้ document_delivery_id
                        this.$router.push(
                            `/Danhsachhoso/${documentDeliveryId}`
                        );
                    } else {
                        // ไม่มีสิทธิ์เข้าถึง - แสดงข้อความและนำทางไปหน้า Unauthorized
                        this.showError(
                            "Bạn không có quyền truy cập tài liệu này"
                        );
                        this.$router.push("/unauthorized");
                    }
                })
                .catch((error) => {
                    this.isLoading = false;
                    console.error("Error checking access:", error);

                    if (error.response?.status === 401) {
                        this.handleAuthError();
                    } else if (error.response?.status === 404) {
                        this.showError(
                            "Không tìm thấy thông tin hồ sơ giao nhận"
                        );
                    } else {
                        this.showError(
                            "Không thể kiểm tra quyền truy cập. Vui lòng thử lại sau."
                        );
                    }
                });
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
    z-index: 99;
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
.progress-tracker::before {
    content: "";
    position: absolute;
    top: 20px;
    width: 98%;
    height: 3px;
    background-color: #e9ecef;
    z-index: 0;
    border-radius: 0;
    transition: background-color 0.3s ease;
}

.progress-tracker.received::before {
    background: linear-gradient(to right, #198754 25%, #e9ecef 25%);
}

.progress-tracker.processing::before {
    background: linear-gradient(
        to right,
        #198754 25%,
        #1e88e5 25%,
        #1e88e5 50%,
        #e9ecef 50%
    );
}

.progress-tracker.submitted::before {
    background: linear-gradient(
        to right,
        #198754 25%,
        #1e88e5 25%,
        #1e88e5 50%,
        #17a2b8 50%,
        #17a2b8 75%,
        #e9ecef 75%
    );
}

.progress-tracker.paid::before {
    background: linear-gradient(
        to right,
        #198754 25%,
        #1e88e5 25%,
        #1e88e5 50%,
        #17a2b8 50%,
        #17a2b8 75%,
        #ffc107 75%
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
.status-received {
    color: #198754;
}
.status-processing {
    color: #1e88e5;
}
.status-submitted {
    color: #17a2b8;
}
.status-paid {
    color: #ffc107;
}
.track-step.active .status-received {
    background-color: #198754;
    color: white;
}
.track-step.active .status-processing {
    background-color: #1e88e5;
    color: white;
}
.track-step.active .status-submitted {
    background-color: #17a2b8;
    color: white;
}
.track-step.active .status-paid {
    background-color: #ffc107;
    color: white;
}
.step-label {
    text-align: center;
    font-size: 0.9rem;
    max-width: 100px;
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

/* Responsive styles */
@media (max-width: 768px) {
    .sticky-wrapper {
        top: 0px;
        left: 0;
        padding: 0.5rem 0;
        z-index: 99;
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
    padding: 0.75rem;
    vertical-align: middle;
}

.table td {
    padding: 0.75rem;
    vertical-align: middle;
    white-space: nowrap;
    border: 1px solid #dee2e6;
}

.table tbody tr:nth-of-type(odd) {
    background-color: rgba(0, 0, 0, 0.02);
}

.table tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.05);
}

.table .text-center {
    text-align: center !important;
}

.table .fw-bold {
    font-weight: 600 !important;
}

.table-secondary {
    background-color: #f2f2f2 !important;
}

/* Handle long text in table cells */
.table td.truncate {
    max-width: 150px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Add subtle shadow to table */
.table-container {
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    border-radius: 5px;
    overflow: hidden;
}

/* Timeline View Container */
.timeline-view {
    display: flex;
    flex-direction: column;
    height: 100vh;
    max-height: 100vh;
    width: 100%;
    background-color: #f8f9fa;
    overflow: hidden;
}

/* Timeline Header */
.timeline-header {
    padding: 1.25rem 1.5rem;
    background: linear-gradient(to right, #f8f9fa, #ffffff);
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
    z-index: 10;
}

.timeline-title {
    margin-bottom: 0;
    font-weight: 600;
    color: #343a40;
}

.btn-close-timeline {
    background: none;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    transition: all 0.2s;
    display: flex;
    align-items: center;
}

.btn-close-timeline:hover {
    background-color: #f8f9fa;
    border-color: #ced4da;
}

/* Timeline Content Area */
.timeline-content-wrapper {
    flex: 1;
    overflow: hidden;
    position: relative;
}

.timeline-scrollarea {
    height: calc(100vh - 70px);
    overflow-y: auto;
    padding: 1.5rem;
}

/* Timeline Container with Line */
.timeline-container {
    position: relative;
    margin-left: 20px;
    padding-left: 25px;
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

/* Timeline Items */
.timeline-item {
    position: relative;
    margin-bottom: 2.5rem;
    padding-left: 25px;
    opacity: 0; /* Start with opacity 0 for animation */
}

.timeline-item:last-child {
    margin-bottom: 0;
}

/* Timeline Badge (Circle with Icon) */
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

/* Timeline Content Box */
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

/* Status-specific styling for left border */
.timeline-item:nth-child(1) .timeline-content {
    border-left: 5px solid #007bff; /* Primary - Processing */
}

.timeline-item:nth-child(2) .timeline-content {
    border-left: 5px solid #17a2b8; /* Info - Submitted */
}

.timeline-item:nth-child(3) .timeline-content {
    border-left: 5px solid #28a745; /* Success - Paid */
}

.timeline-item:nth-child(4) .timeline-content {
    border-left: 5px solid #17a2b8; /* Danger - Cancelled */
}

/* Arrow for timeline content */
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

/* Timeline Content Elements */
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
    transition: all 0.3s ease;
}

.timeline-note:hover {
    background: rgba(248, 249, 250, 0.9);
    border-left: 4px solid #007bff;
}

.fw-medium {
    font-weight: 500;
}

/* Empty Timeline State */
.empty-timeline {
    text-align: center;
    padding: 2rem;
    color: #6c757d;
    animation: pulse-glow 2s infinite;
}

.empty-timeline i {
    filter: drop-shadow(0 0 8px rgba(0, 0, 0, 0.1));
}

/* Animations */
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

.appear {
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

/* Enhanced Animation with Delay for Items */
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

/* Background Colors */
.bg-primary {
    background-color: #007bff !important;
}

.bg-success {
    background-color: #28a745 !important;
}

.bg-info {
    background-color: #17a2b8 !important;
}

.bg-danger {
    background-color: #dc3545 !important;
}

.bg-warning {
    background-color: #ffc107 !important;
}

.bg-secondary {
    background-color: #6c757d !important;
}

/* Responsive Design */
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

/* Desktop Enhanced Design */
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
        border-right: none;
        border-left: 8px solid #fff;
    }

    .timeline-item:nth-child(odd) .timeline-title {
        justify-content: flex-end;
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

    /* Enhanced animation for timeline */
    .timeline-item:nth-child(odd).appear {
        animation: slide-in-left 0.6s ease forwards;
    }

    .timeline-item:nth-child(even).appear {
        animation: slide-in-right 0.6s ease forwards;
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
}

/* Status Badge Styling */
.badge {
    display: inline-block;
    padding: 0.25em 0.6em;
    font-size: 0.75rem;
    font-weight: 700;
    line-height: 1;
    text-align: center;
    white-space: nowrap;
    vertical-align: baseline;
    border-radius: 0.375rem;
    transition: all 0.2s ease-in-out;
}

/* Utilities */
.mb-1 {
    margin-bottom: 0.25rem !important;
}

.mb-3 {
    margin-bottom: 1rem !important;
}

.me-2 {
    margin-right: 0.5rem !important;
}

.ms-1 {
    margin-left: 0.25rem !important;
}

.py-5 {
    padding-top: 3rem !important;
    padding-bottom: 3rem !important;
}

.d-flex {
    display: flex !important;
}

.justify-content-between {
    justify-content: space-between !important;
}

.align-items-center {
    align-items: center !important;
}

.text-center {
    text-align: center !important;
}

.text-muted {
    color: #6c757d !important;
}

.opacity-50 {
    opacity: 0.5 !important;
}

.lead {
    font-size: 1.25rem;
    font-weight: 300;
}

/* Additional Timeline Styling for Creating and Received States */
.creating-content {
    border-left: 5px solid #9c27b0 !important; /* Purple color for creating state */
}

.creating-content:hover {
    box-shadow: 0 8px 25px rgba(156, 39, 176, 0.2) !important;
}

.received-content {
    border-left: 5px solid #009688 !important; /* Teal color for received state */
}

.received-content:hover {
    box-shadow: 0 8px 25px rgba(0, 150, 136, 0.2) !important;
}

/* Status-specific badge enhancements */
.bg-purple {
    background-color: #9c27b0 !important;
}

.bg-teal {
    background-color: #009688 !important;
}

/* Desktop specific enhancements */
@media (min-width: 992px) {
    .timeline-item:nth-child(odd) .creating-content {
        border-left: none;
        border-right: 5px solid #9c27b0;
    }

    .timeline-item:nth-child(odd) .received-content {
        border-left: none;
        border-right: 5px solid #009688;
    }

    /* Add animation effects for creating and received states */
    .timeline-item:nth-child(1).appear .creating-content {
        animation: pulse-purple 1s ease;
    }

    .timeline-item:nth-child(2).appear .received-content {
        animation: pulse-teal 1s ease;
    }
}

/* Animation keyframes for timeline items */
@keyframes pulse-purple {
    0% {
        box-shadow: 0 0 0 0 rgba(156, 39, 176, 0.5);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(156, 39, 176, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(156, 39, 176, 0);
    }
}

@keyframes pulse-teal {
    0% {
        box-shadow: 0 0 0 0 rgba(0, 150, 136, 0.5);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(0, 150, 136, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(0, 150, 136, 0);
    }
}
/* ปรับ CSS สำหรับ resizable columns ให้ทำงานกับ Vue 3 */
/* สำหรับ Vue 3 จำเป็นต้องใช้ :deep ในการเข้าถึง DOM ภายใน component */
:deep(.column-resizer) {
    position: absolute;
    top: 0;
    right: 0;
    width: 8px;
    height: 100%;
    cursor: col-resize;
    z-index: 100;
    background-color: transparent;
    transition: background-color 0.2s;
}

:deep(.column-resizer:hover) {
    background-color: rgba(0, 123, 255, 0.2);
}

:deep(.column-resizer.resizing) {
    background-color: rgba(0, 123, 255, 0.4);
}

:deep(table.resizing) {
    cursor: col-resize;
    user-select: none;
}

:deep(.resizable) {
    table-layout: fixed;
    border-collapse: collapse;
    width: 100%;
}

:deep(.resizable th) {
    position: relative;
    overflow: visible;
}

:deep(.resizable th, .resizable td) {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>
