<template lang="">
    <breadcrumb-vue />
    <!-- Timeline View (เพิ่มส่วนนี้) -->
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

                                <!-- Note/Comment section -->
                                <div class="timeline-note" v-if="item.note">
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
                                        document.processing_status || 'received'
                                    "
                                    @click="toggleTimelineView"
                                >
                                    <!-- Received Step -->
                                    <div
                                        class="track-step"
                                        :class="{
                                            active:
                                                document.processing_status ===
                                                    'received' ||
                                                document.processing_status ===
                                                    'processing' ||
                                                document.processing_status ===
                                                    'submitted' ||
                                                document.processing_status ===
                                                    'paid',
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
                                        <span class="step-label">{{
                                            getReceivedStepLabel(
                                                document.trang_thai_nhan_hs
                                            )
                                        }}</span>
                                    </div>

                                    <!-- Processing Step -->
                                    <div
                                        class="track-step"
                                        :class="{
                                            active:
                                                document.processing_status ===
                                                    'processing' ||
                                                document.processing_status ===
                                                    'submitted' ||
                                                document.processing_status ===
                                                    'paid',
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
                                            active:
                                                document.processing_status ===
                                                    'submitted' ||
                                                document.processing_status ===
                                                    'paid',
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
                                                document.processing_status ===
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
                        <!-- Thông tin phiếu giao nhận hom giống -->
                        <div class="card col-12 col-md-6">
                            <div class="card-body">
                                <h5 class="card-title">
                                    Thông tin phiếu giao nhận
                                </h5>
                                <div class="row gutters">
                                    <!-- Mã số phiếu -->
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="maSoPhieu"
                                                class="form-label"
                                            >
                                                Mã số phiếu
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="maSoPhieu"
                                                :value="document.ma_so_phieu"
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
                                    <!-- Tên phiếu -->
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="tenPhieu"
                                                class="form-label"
                                            >
                                                Tên phiếu
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="tenPhieu"
                                                :value="document.ten_phieu"
                                                disabled
                                            />
                                        </div>
                                    </div>
                                    <!-- Hợp đồng đầu tư mía bên giao -->
                                    <div class="col-12">
                                        <div class="form-group mb-3">
                                            <label
                                                for="hopDongBenGiao"
                                                class="form-label"
                                            >
                                                Hợp đồng đầu tư mía bên giao
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="hopDongBenGiao"
                                                :value="
                                                    document.hop_dong_dau_tu_mia_ben_giao_hom
                                                "
                                                disabled
                                            />
                                        </div>
                                    </div>
                                    <!-- Hợp đồng đầu tư mía bên nhận -->
                                    <div class="col-12">
                                        <div class="form-group mb-3">
                                            <label
                                                for="hopDongBenNhan"
                                                class="form-label"
                                            >
                                                Hợp đồng đầu tư mía bên nhận
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="hopDongBenNhan"
                                                :value="
                                                    document.hop_dong_dau_tu_mia
                                                "
                                                disabled
                                            />
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
                                                        :class="
                                                            paymentStatusIcon(
                                                                document.processing_status
                                                            )
                                                        "
                                                    ></i>
                                                </span>
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    id="trangThaiThanhToan"
                                                    :value="
                                                        formatPaymentStatus(
                                                            document.processing_status
                                                        )
                                                    "
                                                    :class="
                                                        paymentStatusClass(
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

                        <!-- Thông tin tài chính và giao nhận -->
                        <div class="card col-12 col-md-6">
                            <div class="card-body">
                                <h5 class="card-title">
                                    Thông tin tài chính và giao nhận
                                </h5>
                                <div class="row gutters">
                                    <!-- Tổng thực nhận -->
                                    <div
                                        class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12"
                                    >
                                        <div class="form-group mb-3">
                                            <label
                                                for="tongThucNhan"
                                                class="form-label"
                                            >
                                                Tổng thực nhận
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="tongThucNhan"
                                                :value="
                                                    formatNumber(
                                                        document.tong_thuc_nhan
                                                    )
                                                "
                                                disabled
                                            />
                                        </div>
                                    </div>
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
                                                        document.tong_tien
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
                                            <div
                                                v-if="
                                                    document.ma_de_nghi_giai_ngan
                                                "
                                                class="input-group"
                                                style="cursor: pointer"
                                                @click="navigateToPaymentDetail"
                                                title="Click để xem chi tiết phiếu đề nghị thanh toán"
                                            >
                                                <span class="input-group-text">
                                                    <i
                                                        class="fas fa-external-link-alt text-primary"
                                                    ></i>
                                                </span>
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    id="maGiaiNgan"
                                                    :value="
                                                        document.ma_de_nghi_giai_ngan
                                                    "
                                                    disabled
                                                    style="cursor: pointer"
                                                />
                                            </div>
                                            <input
                                                v-else
                                                type="text"
                                                class="form-control"
                                                id="maGiaiNgan"
                                                value="N/A"
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
                                                        class="fas fa-list text-info"
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
                                                    disabled
                                                    style="cursor: pointer"
                                                />
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
                            <h5 class="card-title">Chi tiết hom giống</h5>
                            <div class="table-container">
                                <div class="table-responsive mt-2">
                                    <table
                                        class="table table-bordered table-hover align-middle"
                                    >
                                        <thead class="table-light text-center">
                                            <tr>
                                                <th>STT</th>
                                                <th>Loại giống</th>
                                                <th>Mã số thửa</th>
                                                <th>Đơn vị tính</th>
                                                <th>Số lượng</th>
                                                <th>Đơn giá</th>
                                                <th>Thành tiền</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-if="!serviceDetails.length">
                                                <td
                                                    colspan="7"
                                                    class="text-center py-3 text-muted"
                                                >
                                                    <i
                                                        class="fas fa-info-circle me-2"
                                                    ></i>
                                                    Không có dữ liệu chi tiết
                                                    hom giống cho phiếu này
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
                                                    {{ item.giong_mia }}
                                                </td>
                                                <td>{{ item.thua_dat }}</td>
                                                <td class="text-center">
                                                    {{ item.don_vi_tinh }}
                                                </td>
                                                <td class="text-end">
                                                    {{
                                                        formatNumber(
                                                            item.thuc_nhan
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
                                                            item.thanh_tien_hom_giong
                                                        )
                                                    }}
                                                </td>
                                            </tr>
                                            <tr
                                                v-if="serviceDetails.length"
                                                class="table-secondary"
                                            >
                                                <td
                                                    colspan="5"
                                                    class="text-end fw-bold"
                                                >
                                                    Tổng cộng:
                                                </td>
                                                <td
                                                    colspan="2"
                                                    class="text-end fw-bold"
                                                >
                                                    {{
                                                        formatCurrency(
                                                            totalAmount
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

export default {
    setup() {
        const store = useStore();
        return {
            store,
        };
    },
    data() {
        return {
            document: {
                ma_so_phieu: "",
                can_bo_nong_vu: "",
                vu_dau_tu: "",
                ten_phieu: "",
                hop_dong_dau_tu_mia_ben_giao: "",
                hop_dong_dau_tu_mia_ben_nhan: "",
                tong_thuc_nhan: 0,
                tong_tien: 0,
                nguoi_giao: "",
                nguoi_nhan: "",
                ngay_nhan: null,
                trang_thai_nhan_hs: "",
                ghi_chu: "",
                processing_status: "received", // received, processing, submitted, paid
            },
            serviceDetails: [],
            // เพิ่มใหม่สำหรับ timeline
            processingHistory: [],
            showTimeline: false,
            isLoadingHistory: false,
            user: null,
            isEditingNote: false,
            noteText: "",
            isLoading: false,
        };
    },
    computed: {
        showSubmitAccountButton() {
            return this.document.processing_status === "processing";
        },
        showProcessButton() {
            return this.document.processing_status === "received";
        },
        showReturnButton() {
            return ["processing", "submitted"].includes(
                this.document.processing_status
            );
        },
        showPaymentButton() {
            return this.document.processing_status === "submitted";
        },
        showBackButton() {
            return true; // Always show the back button
        },
        showEditNoteButtons() {
            return this.isEditingNote;
        },
        canEditNote() {
            return true; // Can be modified based on user permissions or document status
        },
        totalAmount() {
            return this.serviceDetails.reduce(
                (sum, item) =>
                    sum + (parseFloat(item.thanh_tien_hom_giong) || 0),
                0
            );
        },
    },
    mounted() {
        this.fetchUserData();
        this.fetchDocument();
    },
    methods: {
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

        // Navigate to payment detail page
        navigateToPaymentDetail() {
            const maGiaiNgan = this.document.ma_de_nghi_giai_ngan;

            if (!maGiaiNgan || maGiaiNgan === "N/A") {
                this.showError("Không tìm thấy mã giải ngân");
                return;
            }

            // Navigate to payment detail page with the payment code
            this.$router.push(
                `/Details_Phieudenghithanhtoanhomgiong/${maGiaiNgan}`
            );
        },
        // Timeline methods - เพิ่มใหม่
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
                .get(`/api/bienban-nghiemthu-homgiong/${id}/history`, {
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

        onClose() {
            this.showTimeline = false;
        },

        formatDayText(days) {
            if (!days) return "";
            return `${days} ngày sau khi ${this.getPreviousStepText(days)}`;
        },

        getPreviousStepText(days) {
            if (!days) return "";
            return "xử lý";
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
                this.showError("Không tìm thấy mã phiếu giao nhận");
                return;
            }

            this.isLoading = true;
            axios
                .get(`/api/bienban-nghiemthu-homgiong/${id}`, {
                    headers: {
                        Authorization: "Bearer " + this.store.getToken,
                    },
                })
                .then((response) => {
                    if (response.data.success) {
                        this.document = {
                            ...response.data.document,
                            processing_status:
                                response.data.document.processing_status ||
                                "received",
                        };
                        this.noteText = this.document.ghi_chu || "";

                        // Set service details
                        this.serviceDetails = response.data.serviceDetails.map(
                            (item) => ({
                                ...item,
                                so_luong: item.so_luong || 0,
                                don_gia: item.don_gia || 0,
                                thanh_tien: item.thanh_tien || 0,
                            })
                        );
                    } else {
                        this.showError(
                            response.data.message ||
                                "Không tìm thấy thông tin phiếu giao nhận"
                        );
                    }
                })
                .catch((error) => {
                    console.error("Error fetching document:", error);
                    this.showError("Lỗi khi tải thông tin phiếu giao nhận");
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
            return new Date(date).toLocaleDateString("vi-VN");
        },
        formatDateTime(date) {
            if (!date) return "N/A";
            return new Date(date).toLocaleString("vi-VN");
        },
        formatStatus(status) {
            if (status === "cancelled") return "Hủy";
            if (status === "received") return "Đã nộp";
            if (status === "sending") return "Đã nộp";
            if (status === "creating") return "Đang tạo";
            return status || "N/A";
        },
        formatActionText(action) {
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
            if (action === "received") return "bg-success";
            if (action === "processing") return "bg-primary";
            if (action === "submitted") return "bg-info";
            if (action === "paid") return "bg-success";
            if (action === "returned") return "bg-danger";
            if (action === "note_added") return "bg-secondary";
            return "bg-secondary";
        },
        getActionIcon(action) {
            if (action === "received") return "fas fa-check-circle";
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
                    return "fas fa-check-circle";
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
.progress-tracker.received::before {
    background: linear-gradient(to right, #198754 0%, #e9ecef 0%);
}
.progress-tracker.processing::before {
    background: linear-gradient(
        to right,
        #198754 33%,
        #1e88e5 33%,
        #1e88e5 66%,
        #e9ecef 66%
    );
}
.progress-tracker.submitted::before {
    background: linear-gradient(
        to right,
        #198754 33%,
        #1e88e5 33%,
        #1e88e5 66%,
        #17a2b8 66%,
        #17a2b8 100%
    );
}
.progress-tracker.paid::before {
    background: linear-gradient(
        to right,
        #198754 33%,
        #1e88e5 33%,
        #1e88e5 66%,
        #17a2b8 66%,
        #17a2b8 100%
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

/* Buttons */
.button-30 {
    align-items: center;
    appearance: none;
    background-color: #fcfcfd;
    border-radius: 4px;
    border-width: 0;
    box-shadow: rgba(45, 35, 66, 0.4) 0 2px 4px,
        rgba(45, 35, 66, 0.3) 0 7px 13px -3px, #d6d6e7 0 -3px 0 inset;
    box-sizing: border-box;
    color: #36395a;
    cursor: pointer;
    display: inline-flex;
    height: 40px;
    justify-content: center;
    line-height: 1;
    list-style: none;
    overflow: hidden;
    padding-left: 16px;
    padding-right: 16px;
    position: relative;
    text-align: left;
    text-decoration: none;
    transition: box-shadow 0.15s, transform 0.15s;
    user-select: none;
    -webkit-user-select: none;
    touch-action: manipulation;
    white-space: nowrap;
    will-change: box-shadow, transform;
    font-size: 14px;
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

.button-30-text-green {
    align-items: center;
    appearance: none;
    background-color: #e6fff2;
    border-radius: 4px;
    border-width: 0;
    box-shadow: rgba(45, 35, 66, 0.4) 0 2px 4px,
        rgba(45, 35, 66, 0.3) 0 7px 13px -3px, #92d9a7 0 -3px 0 inset;
    box-sizing: border-box;
    color: #03541c;
    cursor: pointer;
    display: inline-flex;
    font-family: "JetBrains Mono", monospace;
    height: 40px;
    justify-content: center;
    line-height: 1;
    list-style: none;
    overflow: hidden;
    padding-left: 16px;
    padding-right: 16px;
    position: relative;
    text-align: left;
    text-decoration: none;
    transition: box-shadow 0.15s, transform 0.15s;
    user-select: none;
    -webkit-user-select: none;
    touch-action: manipulation;
    white-space: nowrap;
    will-change: box-shadow, transform;
    font-size: 14px;
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

.button-30-text-green:active {
    box-shadow: #92d9a7 0 3px 7px inset;
    transform: translateY(2px);
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
    opacity: 0;
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

/* Background Colors */
.bg-purple {
    background-color: #9c27b0 !important;
}

.bg-teal {
    background-color: #009688 !important;
}
</style>
