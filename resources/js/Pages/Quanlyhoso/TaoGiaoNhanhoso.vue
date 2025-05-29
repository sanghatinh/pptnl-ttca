<template lang="">
    <!-- Breadcrumb navigation -->
    <breadcrumb-vue />
    <div class="document-delivery-container">
        <PerfectScrollbar
            :options="{
                wheelSpeed: 1,
                wheelPropagation: true,
                minScrollbarLength: 20,
            }"
            class="scroll-area"
        >
            <!-- Header Controls Section -->
            <div class="header-controls-wrapper">
                <div class="container-fluid">
                    <!-- Status Indicator and Buttons -->
                    <div class="header-content">
                        <div class="action-button-group">
                            <button
                                v-if="showSaveButton"
                                type="button"
                                class="button-30"
                                @click="saveOrUpdateDocument"
                            >
                                <i class="bx bxs-save"></i>
                                <span>Lưu</span>
                            </button>

                            <button
                                v-if="showCreateNewButton"
                                type="button"
                                class="button-30-save"
                                @click="createNew"
                            >
                                <i class="fa-solid fa-plus"></i>
                                <span>Tạo mới</span>
                            </button>

                            <button
                                v-if="showSubmitButton"
                                class="button-30-text-green"
                                @click="sendDocument"
                            >
                                <i class="bx bx-calendar-check"></i>
                                <span>Nộp</span>
                            </button>

                            <button
                                v-if="showRejectButton"
                                type="button"
                                class="button-30"
                                @click="rejectDocument"
                            >
                                <i class="bx bx-calendar-x"></i>
                                <span>Không duyệt</span>
                            </button>

                            <button
                                v-if="showApproveButton"
                                type="button"
                                class="button-30-text-green"
                                @click="receiveDocument"
                            >
                                <i class="bx bx-check-square"></i>
                                <span>Duyệt</span>
                            </button>

                            <button
                                v-if="showCancelButton"
                                type="button"
                                class="button-30"
                                @click="cancelDocument"
                            >
                                <i class="fa-solid fa-xmark"></i>
                                <span>Hủy</span>
                            </button>

                            <button
                                v-if="showDeleteButton"
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
                    </div>

                    <!-- Progress Tracker -->
                    <div class="progress-tracker-container">
                        <div class="progress-tracker" :class="document.status">
                            <!-- Creating Step -->
                            <div
                                class="track-step"
                                :class="{
                                    active: document.status === 'creating',
                                }"
                            >
                                <div class="step-icon status-creating">
                                    <i class="fas fa-spinner"></i>
                                </div>
                                <span class="step-label">Nháp</span>
                            </div>

                            <!-- Sending Step -->
                            <div
                                class="track-step"
                                :class="{
                                    active: document.status === 'sending',
                                }"
                            >
                                <div class="step-icon status-sending">
                                    <i class="fas fa-shipping-fast"></i>
                                </div>
                                <span class="step-label">Đang nộp</span>
                            </div>

                            <!-- Received Step -->
                            <div
                                class="track-step"
                                :class="{
                                    active: document.status === 'received',
                                }"
                            >
                                <div class="step-icon status-received">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <span class="step-label">Đã nhận</span>
                            </div>

                            <!-- Cancelled Step -->
                            <div
                                v-if="showCancelledStep"
                                class="track-step"
                                :class="{
                                    active: document.status === 'cancelled',
                                }"
                            >
                                <div class="step-icon status-cancelled">
                                    <i class="fas fa-times-circle"></i>
                                </div>
                                <span class="step-label">Hủy</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="main-content">
                <div class="container-fluid">
                    <!-- Document Information Cards -->
                    <div class="row">
                        <!-- Document Details Card -->
                        <div class="col-12 col-lg-6 mb-4">
                            <div class="info-card">
                                <div class="info-card-header">
                                    <h5>
                                        <i class="bx bx-file"></i> Thông tin
                                        phiếu
                                    </h5>
                                </div>
                                <div class="info-card-body">
                                    <div class="row">
                                        <div class="col-12 mb-3">
                                            <div class="form-group">
                                                <label class="form-label">
                                                    Mã số phiếu
                                                    <i
                                                        class="bx bx-lock-alt text-muted"
                                                    ></i>
                                                </label>
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    v-model="
                                                        document.document_code
                                                    "
                                                    placeholder="Tự động tạo khi lưu"
                                                    disabled
                                                />
                                            </div>
                                        </div>

                                        <div class="col-12 mb-3">
                                            <div class="form-group">
                                                <label class="form-label">
                                                    Ngày lập
                                                    <span
                                                        v-if="
                                                            formValidation
                                                                .createdDate
                                                                .required
                                                        "
                                                        class="required-indicator"
                                                        >*</span
                                                    >
                                                </label>
                                                <input
                                                    type="date"
                                                    class="form-control"
                                                    v-model="
                                                        documentDates.created_date
                                                    "
                                                    :class="{
                                                        'is-invalid':
                                                            formValidation
                                                                .createdDate
                                                                .required,
                                                    }"
                                                    :disabled="
                                                        !canModifyDates.created
                                                    "
                                                />
                                                <div
                                                    v-if="
                                                        formValidation
                                                            .createdDate
                                                            .required
                                                    "
                                                    class="invalid-feedback"
                                                >
                                                    {{
                                                        formValidation
                                                            .createdDate.message
                                                    }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 mb-3">
                                            <div class="form-group">
                                                <label class="form-label">
                                                    Tiêu đề
                                                    <i
                                                        class="bx bx-lock-alt text-muted"
                                                    ></i>
                                                </label>
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    v-model="document.title"
                                                    placeholder="Tiều đề"
                                                    disabled
                                                />
                                            </div>
                                        </div>

                                        <div class="col-12 mb-3">
                                            <div class="form-group">
                                                <label class="form-label">
                                                    Vụ đầu tư
                                                    <span
                                                        v-if="
                                                            formValidation
                                                                .investmentProject
                                                                .required
                                                        "
                                                        class="required-indicator"
                                                        >*</span
                                                    >
                                                </label>
                                                <select
                                                    class="form-select"
                                                    v-model="
                                                        document.investment_project
                                                    "
                                                    :class="{
                                                        'is-invalid':
                                                            formValidation
                                                                .investmentProject
                                                                .required,
                                                    }"
                                                >
                                                    <option value="" disabled>
                                                        Chọn vụ đầu tư
                                                    </option>
                                                    <option
                                                        v-for="project in investmentProjects"
                                                        :key="project.id"
                                                        :value="
                                                            project.Ten_Vudautu
                                                        "
                                                    >
                                                        {{
                                                            project.Ten_Vudautu
                                                        }}
                                                    </option>
                                                </select>
                                                <div
                                                    v-if="
                                                        formValidation
                                                            .investmentProject
                                                            .required
                                                    "
                                                    class="invalid-feedback"
                                                >
                                                    {{
                                                        formValidation
                                                            .investmentProject
                                                            .message
                                                    }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 mb-3">
                                            <div class="form-group">
                                                <label class="form-label">
                                                    Người tạo
                                                    <i
                                                        class="bx bx-lock-alt text-muted"
                                                    ></i>
                                                </label>
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    :value="creatorName"
                                                    disabled
                                                />
                                            </div>
                                        </div>

                                        <div class="col-12 mb-3">
                                            <div class="form-group">
                                                <label class="form-label">
                                                    Trạm
                                                    <i
                                                        class="bx bx-lock-alt text-muted"
                                                    ></i>
                                                </label>
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    :value="
                                                        user ? user.station : ''
                                                    "
                                                    disabled
                                                />
                                            </div>
                                        </div>

                                        <div class="col-12 mb-3">
                                            <div class="form-group">
                                                <label class="form-label">
                                                    Loại hồ sơ
                                                    <span
                                                        v-if="
                                                            formValidation
                                                                .documentType
                                                                .required
                                                        "
                                                        class="required-indicator"
                                                        >*</span
                                                    >
                                                </label>
                                                <select
                                                    class="form-select"
                                                    v-model="
                                                        document.document_type
                                                    "
                                                    :class="{
                                                        'is-invalid':
                                                            formValidation
                                                                .documentType
                                                                .required,
                                                    }"
                                                >
                                                    <option value="">
                                                        Chọn loại hồ sơ
                                                    </option>
                                                    <option
                                                        v-for="type in documentTypes"
                                                        :key="type.id"
                                                        :value="
                                                            type.Ten_LoaiHoso
                                                        "
                                                    >
                                                        {{ type.Ten_LoaiHoso }}
                                                    </option>
                                                </select>
                                                <div
                                                    v-if="
                                                        formValidation
                                                            .documentType
                                                            .required
                                                    "
                                                    class="invalid-feedback"
                                                >
                                                    {{
                                                        formValidation
                                                            .documentType
                                                            .message
                                                    }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Reception Information Card -->
                        <div class="col-12 col-lg-6 mb-4">
                            <div class="info-card">
                                <div class="info-card-header">
                                    <h5>
                                        <i class="bx bx-transfer"></i> Thông tin
                                        giao nhận
                                    </h5>
                                </div>
                                <div class="info-card-body">
                                    <div class="row">
                                        <div class="col-12 mb-4">
                                            <div class="document-stats">
                                                <div class="stat-item">
                                                    <div class="stat-icon">
                                                        <i
                                                            class="bx bx-file-blank"
                                                        ></i>
                                                    </div>
                                                    <div class="stat-content">
                                                        <span
                                                            class="stat-value"
                                                            >{{
                                                                documentCount
                                                            }}</span
                                                        >
                                                        <span class="stat-label"
                                                            >Số lượng hồ
                                                            sơ</span
                                                        >
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div
                                            class="col-12 mb-3"
                                            v-if="showreceivedate"
                                        >
                                            <div class="form-group">
                                                <label class="form-label">
                                                    Ngày nhận
                                                    <span
                                                        v-if="
                                                            formValidation
                                                                .receivedDate
                                                                .required
                                                        "
                                                        class="required-indicator"
                                                        >*</span
                                                    >
                                                </label>
                                                <input
                                                    type="date"
                                                    class="form-control"
                                                    v-model="
                                                        documentDates.received_date
                                                    "
                                                    :class="{
                                                        'is-invalid':
                                                            formValidation
                                                                .receivedDate
                                                                .required,
                                                    }"
                                                    :disabled="
                                                        !canModifyDates.received
                                                    "
                                                />
                                                <div
                                                    v-if="
                                                        formValidation
                                                            .receivedDate
                                                            .required
                                                    "
                                                    class="invalid-feedback"
                                                >
                                                    {{
                                                        formValidation
                                                            .receivedDate
                                                            .message
                                                    }}
                                                </div>
                                            </div>
                                        </div>

                                        <div
                                            class="col-12 mb-3"
                                            v-if="showReceiverField"
                                        >
                                            <div class="form-group">
                                                <label class="form-label">
                                                    Người nhận
                                                    <i
                                                        class="bx bx-lock-alt text-muted"
                                                    ></i>
                                                </label>
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    :value="receiverName"
                                                    disabled
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Nghiệm Thu Dịch Vụ Table -->
                    <div v-if="showNghiemThu" class="data-section mb-4">
                        <div class="section-card">
                            <div class="section-header">
                                <div class="section-title">
                                    <i class="bx bx-clipboard"></i>
                                    <h5>BIÊN BẢN NGHIỆM THU DỊCH VỤ</h5>
                                </div>
                                <button
                                    v-if="canModifyMappings"
                                    type="button"
                                    class="btn btn-add"
                                    data-bs-toggle="modal"
                                    data-bs-target="#extraLargeModal"
                                >
                                    <i class="fa-solid fa-plus"></i>
                                    <span>Thêm</span>
                                </button>
                            </div>
                            <div class="section-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th style="width: 80px">
                                                    Thao tác
                                                </th>
                                                <th>Mã nghiệm thu</th>
                                                <th>Trạm</th>
                                                <th>Cán bộ nông vụ</th>
                                                <th>Vụ đầu tư</th>
                                                <th>Tiêu đề</th>
                                                <th>Hợp đồng đầu tư mía</th>
                                                <th>Hình thức thực hiện DV</th>
                                                <th>
                                                    Hợp đồng cung ứng dịch vụ
                                                </th>
                                                <th>Tổng tiền</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-if="
                                                    mappedDocuments.length === 0
                                                "
                                            >
                                                <td
                                                    colspan="10"
                                                    class="text-center py-4"
                                                >
                                                    <div class="empty-state">
                                                        <i
                                                            class="bx bx-file-find empty-icon"
                                                        ></i>
                                                        <p>
                                                            Chưa có dữ liệu
                                                            nghiệm thu dịch vụ
                                                        </p>
                                                        <button
                                                            v-if="
                                                                canModifyMappings
                                                            "
                                                            class="btn btn-sm btn-success"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#extraLargeModal"
                                                        >
                                                            <i
                                                                class="fa-solid fa-plus"
                                                            ></i>
                                                            Thêm nghiệm thu
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr
                                                v-for="item in mappedDocuments"
                                                :key="item.mapping_id"
                                                class="data-row"
                                            >
                                                <td>
                                                    <button
                                                        v-if="canModifyMappings"
                                                        @click="
                                                            deleteMapping(
                                                                item.mapping_id
                                                            )
                                                        "
                                                        class="btn btn-sm btn-danger"
                                                        title="Xóa"
                                                    >
                                                        <i
                                                            class="fa-solid fa-trash"
                                                        ></i>
                                                    </button>
                                                </td>
                                                <td
                                                    class="text-primary fw-medium"
                                                >
                                                    {{ item.ma_nghiem_thu }}
                                                </td>
                                                <td>{{ item.tram }}</td>
                                                <td>
                                                    {{ item.can_bo_nong_vu }}
                                                </td>
                                                <td>{{ item.vu_dau_tu }}</td>
                                                <td>{{ item.tieu_de }}</td>
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
                                                <td class="text-end fw-medium">
                                                    {{
                                                        formatNumber(
                                                            item.tong_tien
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

                    <!-- Hom Giong Table -->
                    <div v-if="showHomGiong" class="data-section mb-4">
                        <div class="section-card">
                            <div class="section-header">
                                <div class="section-title">
                                    <i class="bx bx-leaf"></i>
                                    <h5>BIÊN BẢN HOM GIỐNG</h5>
                                </div>
                                <button
                                    v-if="canModifyMappings"
                                    type="button"
                                    class="btn btn-add"
                                    data-bs-toggle="modal"
                                    data-bs-target="#homGiongModal"
                                >
                                    <i class="fa-solid fa-plus"></i>
                                    <span>Thêm</span>
                                </button>
                            </div>
                            <div class="section-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th style="width: 80px">
                                                    Thao tác
                                                </th>
                                                <th>Mã số phiếu</th>
                                                <th>Cán bộ nông vụ</th>
                                                <th>Vụ đầu tư</th>
                                                <th>Tên phiếu</th>
                                                <th>
                                                    Hợp đồng đầu tư mía bên giao
                                                </th>
                                                <th>
                                                    Hợp đồng đầu tư mía bên nhận
                                                </th>
                                                <th>Tổng thực nhận</th>
                                                <th>Tổng tiền</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-if="
                                                    mappedHomGiongDocuments.length ===
                                                    0
                                                "
                                            >
                                                <td
                                                    colspan="9"
                                                    class="text-center py-4"
                                                >
                                                    <div class="empty-state">
                                                        <i
                                                            class="bx bx-file-find empty-icon"
                                                        ></i>
                                                        <p>
                                                            Chưa có dữ liệu hom
                                                            giống
                                                        </p>
                                                        <button
                                                            v-if="
                                                                canModifyMappings
                                                            "
                                                            class="btn btn-sm btn-success"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#homGiongModal"
                                                        >
                                                            <i
                                                                class="fa-solid fa-plus"
                                                            ></i>
                                                            Thêm hom giống
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr
                                                v-for="item in mappedHomGiongDocuments"
                                                :key="item.mapping_id"
                                                class="data-row"
                                            >
                                                <td>
                                                    <button
                                                        v-if="canModifyMappings"
                                                        @click="
                                                            deleteHomGiongMapping(
                                                                item.mapping_id
                                                            )
                                                        "
                                                        class="btn btn-sm btn-danger"
                                                        title="Xóa"
                                                    >
                                                        <i
                                                            class="fa-solid fa-trash"
                                                        ></i>
                                                    </button>
                                                </td>
                                                <td
                                                    class="text-primary fw-medium"
                                                >
                                                    {{ item.ma_so_phieu }}
                                                </td>
                                                <td>
                                                    {{ item.can_bo_nong_vu }}
                                                </td>
                                                <td>{{ item.vu_dau_tu }}</td>
                                                <td>{{ item.ten_phieu }}</td>
                                                <td>
                                                    {{
                                                        item.hop_dong_dau_tu_mia_ben_giao_hom
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        item.hop_dong_dau_tu_mia
                                                    }}
                                                </td>
                                                <td>
                                                    {{ item.tong_thuc_nhan }}
                                                </td>
                                                <td class="text-end fw-medium">
                                                    {{
                                                        formatNumber(
                                                            item.tong_tien
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
            </div>
        </PerfectScrollbar>
    </div>

    <!-- Modal nghiem thu dich vu - styled version -->
    <div
        class="modal fade"
        id="extraLargeModal"
        tabindex="-1"
        aria-labelledby="extraLargeModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="extraLargeModalLabel">
                        <i class="bx bx-clipboard me-2"></i>
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
                        <label for="bienBanSearch" class="form-label"
                            >Tìm kiếm mã nghiệm thu</label
                        >
                        <div class="search-input-wrapper">
                            <input
                                type="search"
                                class="form-control"
                                v-model="searchQuery"
                                @input="searchBienBan"
                                :placeholder="
                                    selectedBienBan
                                        ? selectedBienBan.ma_nghiem_thu
                                        : 'Nhập mã nghiệm thu...'
                                "
                            />
                            <i class="search-icon bx bx-search"></i>
                        </div>
                        <div class="search-results-container">
                            <ul
                                v-if="bienBanResults.length"
                                class="search-results-list"
                            >
                                <li
                                    v-for="item in bienBanResults"
                                    :key="item.ma_nghiem_thu"
                                    @click="selectBienBan(item)"
                                    class="search-result-item"
                                >
                                    {{ item.ma_nghiem_thu }}
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="selected-item-details" v-if="selectedBienBan">
                        <div class="details-header">
                            <h6>Thông tin nghiệm thu dịch vụ</h6>
                        </div>
                        <div class="details-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"
                                        >Hợp đồng đầu tư mía</label
                                    >
                                    <input
                                        type="text"
                                        class="form-control"
                                        :value="
                                            selectedBienBan
                                                ? selectedBienBan.hop_dong_dau_tu_mia
                                                : ''
                                        "
                                        disabled
                                    />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"
                                        >Hình thức thực hiện DV</label
                                    >
                                    <input
                                        type="text"
                                        class="form-control"
                                        :value="
                                            selectedBienBan
                                                ? selectedBienBan.hinh_thuc_thuc_hien_dv
                                                : ''
                                        "
                                        disabled
                                    />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"
                                        >Hợp đồng cung ứng dịch vụ</label
                                    >
                                    <input
                                        type="text"
                                        class="form-control"
                                        :value="
                                            selectedBienBan
                                                ? selectedBienBan.hop_dong_cung_ung_dich_vu
                                                : ''
                                        "
                                        disabled
                                    />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"
                                        >Tổng tiền dịch vụ</label
                                    >
                                    <input
                                        type="text"
                                        class="form-control"
                                        :value="
                                            selectedBienBan
                                                ? formatNumber(
                                                      selectedBienBan.tong_tien_dich_vu
                                                  )
                                                : ''
                                        "
                                        disabled
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Đóng
                    </button>
                    <button
                        type="button"
                        class="btn btn-success"
                        @click="addMapping(selectedBienBan)"
                        :disabled="!selectedBienBan"
                    >
                        <i class="fa-solid fa-plus me-1"></i>
                        Thêm nghiệm thu
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal hom giong - styled version -->
    <div
        class="modal fade"
        id="homGiongModal"
        tabindex="-1"
        aria-labelledby="homGiongModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="homGiongModalLabel">
                        <i class="bx bx-leaf me-2"></i>
                        Thêm biên bản hom giống
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
                        <label for="homGiongSearch" class="form-label"
                            >Tìm kiếm mã số phiếu</label
                        >
                        <div class="search-input-wrapper">
                            <input
                                type="search"
                                class="form-control"
                                v-model="searchHomGiongQuery"
                                @input="searchHomGiong"
                                :placeholder="
                                    selectedHomGiong
                                        ? selectedHomGiong.ma_so_phieu
                                        : 'Nhập mã số phiếu...'
                                "
                            />
                            <i class="search-icon bx bx-search"></i>
                        </div>
                        <div class="search-results-container">
                            <ul
                                v-if="homGiongResults.length"
                                class="search-results-list"
                            >
                                <li
                                    v-for="item in homGiongResults"
                                    :key="item.ma_so_phieu"
                                    @click="selectHomGiong(item)"
                                    class="search-result-item"
                                >
                                    {{ item.ma_so_phieu }}
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="selected-item-details" v-if="selectedHomGiong">
                        <div class="details-header">
                            <h6>Thông tin biên bản hom giống</h6>
                        </div>
                        <div class="details-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"
                                        >Hợp đồng đầu tư mía bên giao</label
                                    >
                                    <input
                                        type="text"
                                        class="form-control"
                                        :value="
                                            selectedHomGiong
                                                ? selectedHomGiong.hop_dong_dau_tu_mia_ben_giao_hom
                                                : ''
                                        "
                                        disabled
                                    />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"
                                        >Hợp đồng đầu tư mía bên nhận</label
                                    >
                                    <input
                                        type="text"
                                        class="form-control"
                                        :value="
                                            selectedHomGiong
                                                ? selectedHomGiong.hop_dong_dau_tu_mia
                                                : ''
                                        "
                                        disabled
                                    />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"
                                        >Tổng thực nhận</label
                                    >
                                    <input
                                        type="text"
                                        class="form-control"
                                        :value="
                                            selectedHomGiong
                                                ? selectedHomGiong.tong_thuc_nhan
                                                : ''
                                        "
                                        disabled
                                    />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tổng tiền</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        :value="
                                            selectedHomGiong
                                                ? formatNumber(
                                                      selectedHomGiong.tong_tien
                                                  )
                                                : ''
                                        "
                                        disabled
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Đóng
                    </button>
                    <button
                        type="button"
                        class="btn btn-success"
                        @click="addHomGiongMapping(selectedHomGiong)"
                        :disabled="!selectedHomGiong"
                    >
                        <i class="fa-solid fa-plus me-1"></i>
                        Thêm hom giống
                    </button>
                </div>
            </div>
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
            url: window.location.origin,
            document: {
                document_code: "",
                created_date: "",
                title: "",
                investment_project: "",
                document_type: "", // will hold one of: 'Nghiệm thu dịch vụ', 'Hom giống', 'Chọn cả 2'
                file_count: 0,
                received_date: "",
                status: "creating",
                creator_id: null,
                receiver_id: null,
                creator_name: "",
                station: "",
            },
            documentInfo: {
                creator: null,
                receiver: null,
                created_date: null,
                received_date: null,
            },
            documentDates: {
                created_date: null,
                received_date: null,
            },

            searchQuery: "",
            searchHomGiongQuery: "",
            investmentProjects: [],
            mappedDocuments: [],

            // Use a static array for demo purposes. You may later fetch these from #TABLE tb_loaihoso.
            documentTypes: [
                { id: 1, Ten_LoaiHoso: "Nghiệm thu dịch vụ" },
                { id: 2, Ten_LoaiHoso: "Hom giống" },
                { id: 3, Ten_LoaiHoso: "Chọn cả 2" },
            ],
            user: null,
            receiver: null,
            bienBanResults: [],
            selectedBienBan: null,
            homGiongResults: [],
            selectedHomGiong: null,
            mappedHomGiongDocuments: [],
        };
    },

    computed: {
        showNghiemThu() {
            return (
                this.document.document_type === "Nghiệm thu dịch vụ" ||
                this.document.document_type === "Chọn cả 2"
            );
        },
        showHomGiong() {
            return (
                this.document.document_type === "Hom giống" ||
                this.document.document_type === "Chọn cả 2"
            );
        },

        // Add these computed properties
        creatorName() {
            return this.documentInfo.creator?.full_name || "";
        },

        receiverName() {
            return this.documentInfo.receiver?.full_name || "";
        },

        canModifyDates() {
            return {
                created: this.document.status === "creating",
                received: this.document.status === "sending",
            };
        },
        showSaveButton() {
            return !this.document.id || this.document.status === "creating";
        },
        showCreateNewButton() {
            return this.document.id; // Show when document exists
        },
        showSubmitButton() {
            return this.document.status === "creating";
        },
        showApproveButton() {
            return this.document.status === "sending";
        },
        showRejectButton() {
            return this.document.status === "sending";
        },
        showCancelButton() {
            return this.document.status === "received";
        },
        showDeleteButton() {
            return this.document.id; // Show when document exists
        },
        showCancelledStep() {
            return this.document.status === "cancelled";
        },

        formValidation() {
            return {
                createdDate: {
                    required: !this.documentDates.created_date,
                    message: "Vui lòng nhập ngày lập",
                },
                receivedDate: {
                    required:
                        this.document.status === "sending" &&
                        !this.documentDates.received_date,
                    message: "Vui lòng nhập ngày nhận",
                },
                investmentProject: {
                    required: !this.document.investment_project,
                    message: "Vui lòng chọn vụ đầu tư",
                },
                documentType: {
                    required: !this.document.document_type,
                    message: "Vui lòng chọn loại hồ sơ",
                },
            };
        },
        showReceiverField() {
            return ["received", "cancelled"].includes(this.document.status);
        },
        showreceivedate() {
            return ["sending", "received", "cancelled"].includes(
                this.document.status
            );
        },
        canModifyMappings() {
            return !["sending", "received", "cancelled"].includes(
                this.document.status
            );
        },
        documentCount() {
            // Count both nghiem thu and hom giong documents
            return (
                this.mappedDocuments.length +
                this.mappedHomGiongDocuments.length
            );
        },
    },
    mounted() {
        this.fetchUserData();
        this.fetchInvestmentProjects();
        this.fetchDocumentInfo();
        // Add scroll event listener
        // window.addEventListener("scroll", this.handleScroll);
        // still fetchDocumentTypes if needed or use static one above
    },
    // beforeDestroy() {
    //     // Remove scroll event listener
    //     window.removeEventListener("scroll", this.handleScroll);
    // },
    methods: {
        // Add this new method
        // handleScroll() {
        //     const container = document.querySelector(".container-fluid");
        //     if (window.scrollY > 0) {
        //         container.classList.add("scrolled");
        //     } else {
        //         container.classList.remove("scrolled");
        //     }
        // },
        // formatNumber method
        formatNumber(value) {
            if (!value) return "";
            return new Intl.NumberFormat("en-US", {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0,
            }).format(value);
        },

        // Add these new methods
        saveOrUpdateDocument() {
            if (this.document.id) {
                // Update existing document
                const updateData = {
                    ...this.document,
                    file_count: this.documentCount,
                    action: "creating", // Add action for log
                    action_by: this.user.id, // Add action_by for log
                    action_date: this.documentDates.created_date, // Add action_date for log
                };
                axios
                    .put(
                        `/api/document-deliveries/${this.document.id}`,
                        updateData,
                        {
                            headers: {
                                Authorization: "Bearer " + this.store.getToken,
                            },
                        }
                    )
                    .then((response) => {
                        this.document = response.data;
                        Swal.fire({
                            toast: true,
                            position: "top-end",
                            title: "Updated successfully",
                            icon: "success",
                            showConfirmButton: false,
                            timer: 2000,
                        });
                    })
                    .catch((error) => {
                        console.error(error);
                        if (error.response && error.response.status === 401) {
                            this.handleAuthError();
                        }
                    });
            } else {
                // Create new document
                this.saveDocument();
            }
        },

        createNew() {
            // Reset form data
            this.document = {
                document_code: "",
                created_date: "",
                title: "",
                investment_project: "",
                document_type: "",
                file_count: 0,
                received_date: "",
                status: "creating",
                creator_id: this.user?.id,
                receiver_id: this.user?.id,
                creator_name: this.user?.full_name,
                station: this.user?.station,
                searchQuery: "",
                searchHomGiongQuery: "",
            };
            this.selectedBienBan = null;
            this.mappedDocuments = [];
            this.bienBanResults = [];
            this.selectedHomGiong = null;
            this.mappedHomGiongDocuments = [];
            this.homGiongResults = [];
        },

        async deleteDocument() {
            if (!this.document.id) return;

            const result = await Swal.fire({
                title: "Xác nhận xóa",
                text: "Bạn có chắc chắn muốn xóa hồ sơ này?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "OK",
                cancelButtonText: "Cancel",
                buttonsStyling: false,
                customClass: {
                    confirmButton: "btn btn-danger me-2",
                    cancelButton: "btn btn-outline-success",
                },
            });

            if (result.isConfirmed) {
                axios
                    .delete(`/api/document-deliveries/${this.document.id}`, {
                        headers: {
                            Authorization: "Bearer " + this.store.getToken,
                        },
                    })
                    .then(() => {
                        Swal.fire({
                            title: "Thành công!",
                            text: "Hồ sơ đã được xóa",
                            icon: "success",
                            confirmButtonText: "OK",
                            buttonsStyling: false,
                            customClass: {
                                confirmButton: "btn btn-success",
                            },
                        });
                        this.createNew(); // Reset form after delete
                    })
                    .catch((error) => {
                        console.error(error);
                        if (error.response && error.response.status === 401) {
                            this.handleAuthError();
                        } else {
                            Swal.fire({
                                title: "Lỗi!",
                                text: "Đã xảy ra lỗi khi xóa hồ sơ",
                                icon: "error",
                                confirmButtonText: "OK",
                                buttonsStyling: false,
                                customClass: {
                                    confirmButton: "btn btn-success",
                                },
                            });
                        }
                    });
            }
        },

        fetchUserData() {
            const user = localStorage.getItem("web_user");
            if (user) {
                const parsedUser = JSON.parse(user);
                this.user = parsedUser;
                this.document.creator_id = parsedUser.id;
                this.document.creator_name = parsedUser.full_name;
                this.document.station = parsedUser.station;
                // this.document.receiver_id = parsedUser.id;
            }
        },
        fetchInvestmentProjects() {
            axios
                .get("/api/investment-projects-original", {
                    headers: {
                        Authorization: "Bearer " + this.store.getToken,
                    },
                })
                .then((response) => {
                    this.investmentProjects = response.data;
                })
                .catch((err) => {
                    console.log(err);
                    if (err.response && err.response.status === 401) {
                        this.handleAuthError();
                    }
                });
        },
        // Update saveDocument method
        saveDocument() {
            // ตรวจสอบว่ามีการเลือกวันที่แล้ว
            if (!this.documentDates.created_date) {
                Swal.fire({
                    title: "Lỗi!",
                    text: "Vui lòng nhập ngày lập trước khi lưu",
                    icon: "error",
                });
                return;
            }

            if (this.formValidation.investmentProject.required) {
                Swal.fire({
                    title: "Lỗi!",
                    text: "Vui lòng chọn vụ đầu tư trước khi lưu",
                    icon: "error",
                });
                return;
            }

            if (this.formValidation.documentType.required) {
                Swal.fire({
                    title: "Lỗi!",
                    text: "Vui lòng chọn loại hồ sơ trước khi lưu",
                    icon: "error",
                });
                return;
            }

            // Update document data before saving
            this.document = {
                ...this.document,
                file_count: this.documentCount, // Set file_count from computed property
                created_date: this.documentDates.created_date,
                received_date: this.documentDates.received_date,
            };

            axios
                .post("/api/document-deliveries", this.document, {
                    headers: {
                        Authorization: "Bearer " + this.store.getToken,
                    },
                })
                .then((response) => {
                    this.document = response.data;
                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        title: "Saved successfully",
                        icon: "success",
                        showConfirmButton: false,
                        timer: 3000,
                    });
                })
                .catch((err) => {
                    console.error(err);
                    if (err.response?.status === 401) {
                        this.handleAuthError();
                    } else {
                        Swal.fire({
                            title: "Lỗi!",
                            text: "Có lỗi xảy ra khi lưu tài liệu",
                            icon: "error",
                        });
                    }
                });
        },
        // The existing status update methods...
        async sendDocument() {
            const result = await Swal.fire({
                title: "Xác nhận nộp",
                text: "Bạn có chắc chắn muốn nộp hồ sơ này?",
                icon: "question",
                showCancelButton: true,

                confirmButtonText: "OK",
                cancelButtonText: "Cancel",
                buttonsStyling: false,
                customClass: {
                    confirmButton: "btn btn-success me-2",
                    cancelButton: "btn btn-outline-success",
                },
            });

            if (result.isConfirmed) {
                this.updateDocumentStatus("sending");
            }
        },

        async receiveDocument() {
            const result = await Swal.fire({
                title: "Xác nhận duyệt",
                text: "Bạn có chắc chắn muốn duyệt hồ sơ này?",
                icon: "question",
                showCancelButton: true,

                confirmButtonText: "OK",
                cancelButtonText: "Cancel",
                buttonsStyling: false,
                customClass: {
                    confirmButton: "btn btn-success me-2",
                    cancelButton: "btn btn-outline-success",
                },
            });

            if (result.isConfirmed) {
                this.updateDocumentStatus("received");
            }
        },

        async cancelDocument() {
            const result = await Swal.fire({
                title: "Xác nhận hủy",
                text: "Bạn có chắc chắn muốn hủy hồ sơ này?",
                icon: "warning",
                showCancelButton: true,

                confirmButtonText: "OK",
                cancelButtonText: "Cancel",
                buttonsStyling: false,
                customClass: {
                    confirmButton: "btn btn-success me-2",
                    cancelButton: "btn btn-outline-success",
                },
            });

            if (result.isConfirmed) {
                this.updateDocumentStatus("cancelled");
            }
        },

        async rejectDocument() {
            const result = await Swal.fire({
                title: "Xác nhận không duyệt",
                text: "Bạn có chắc chắn không duyệt hồ sơ này?",
                icon: "warning",
                showCancelButton: true,

                confirmButtonText: "OK",
                cancelButtonText: "Cancel",
                buttonsStyling: false,
                customClass: {
                    confirmButton: "btn btn-success me-2",
                    cancelButton: "btn btn-outline-success",
                },
            });

            if (result.isConfirmed) {
                this.updateDocumentStatus("creating");
            }
        },

        // Update the updateDocumentStatus method to use SweetAlert2 for success/error messages
        updateDocumentStatus(status) {
            // Use selected received_date or current date as fallback
            const currentDate =
                this.documentDates.received_date ||
                new Date().toISOString().split("T")[0];
            const payload = {
                status: status,
                receiver_id: this.user.id,
                action_date: currentDate,
                file_count: this.documentCount,
            };

            // Set received_date when transitioning to received status
            if (status === "received") {
                payload.received_date = this.documentDates.received_date;
            }

            axios
                .patch(
                    `/api/document-deliveries/${this.document.id}/status`,
                    payload,
                    {
                        headers: {
                            Authorization: "Bearer " + this.store.getToken,
                        },
                    }
                )
                .then((response) => {
                    this.document = {
                        ...response.data,
                        file_count: this.documentCount, // Ensure file_count is updated in local state
                    };

                    // Fetch updated document info after status change
                    this.fetchDocumentInfo();

                    // Update documentInfo directly for immediate UI update
                    if (status === "received") {
                        this.documentInfo = {
                            ...this.documentInfo,
                            receiver: this.user,
                            received_date: this.documentDates.received_date, // Use selected date
                        };
                    }

                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        title: "Thành công!",
                        text: `Trạng thái hồ sơ đã được cập nhật thành ${status}`,
                        icon: "success",
                        showConfirmButton: false,
                        timer: 3000,
                    });
                })
                .catch((err) => {
                    console.error(err);
                    if (err.response?.status === 401) {
                        this.handleAuthError();
                    } else {
                        Swal.fire({
                            title: "Lỗi!",
                            text: "Đã xảy ra lỗi khi cập nhật trạng thái hồ sơ",
                            icon: "error",
                        });
                    }
                });
        },
        // NEW: Search for ma_nghiem_thu when the modal shows up.
        searchBienBan() {
            axios
                .get("/api/bienban-nghiemthu-search", {
                    params: {
                        search: this.searchQuery,
                        investment_project: this.document.investment_project,
                        station: this.user.station,
                    },
                    headers: {
                        Authorization: "Bearer " + this.store.getToken,
                    },
                })
                .then((response) => {
                    this.bienBanResults = response.data;
                })
                .catch((err) => {
                    console.log(err);
                    if (err.response && err.response.status === 401) {
                        this.handleAuthError();
                    }
                });
        },
        selectBienBan(item) {
            this.selectedBienBan = item;
            this.searchQuery = item.ma_nghiem_thu; // Set the search input value to selected ma_nghiem_thu
            this.showBienBanDetails();
            this.bienBanResults = []; // Clear the dropdown after selection
        },
        showBienBanDetails() {
            // This method will be triggered when a bien ban is selected
            // It will automatically bind the selectedBienBan details to the form fields
            if (this.selectedBienBan) {
                this.document.hop_dong_dau_tu_mia =
                    this.selectedBienBan.hop_dong_dau_tu_mia;
                this.document.hinh_thuc_thuc_hien_dv =
                    this.selectedBienBan.hinh_thuc_thuc_hien_dv;
                this.document.hop_dong_cung_ung_dich_vu =
                    this.selectedBienBan.hop_dong_cung_ung_dich_vu;
                this.document.tong_tien_dich_vu =
                    this.selectedBienBan.tong_tien_dich_vu;
            }
        },
        addMapping(selected) {
            axios
                .post(
                    "/api/document-mappings",
                    {
                        document_code: this.document.document_code,
                        ma_nghiem_thu: selected.ma_nghiem_thu,
                    },
                    {
                        headers: {
                            Authorization: "Bearer " + this.store.getToken,
                        },
                    }
                )
                .then((response) => {
                    if (response.status === 200) {
                        Swal.fire({
                            toast: true,
                            position: "top-end",
                            title: "Mapping added successfully",
                            icon: "success",
                            showConfirmButton: false,
                            timer: 2000,
                        });
                        this.fetchMappedDocuments();
                        // Close modal...
                    }
                })
                .catch((err) => {
                    console.log(err);
                    if (err.response && err.response.status === 401) {
                        this.handleAuthError();
                    } else if (err.response.status === 422) {
                        Swal.fire({
                            title: "Lỗi!",
                            text:
                                "Cannot add this mapping: " +
                                err.response.data.error,
                            icon: "error",
                        });
                    }
                });
        },
        fetchMappedDocuments() {
            if (!this.document.document_code) return;

            axios
                .get(`/api/document-mappings/${this.document.document_code}`, {
                    headers: {
                        Authorization: "Bearer " + this.store.getToken,
                    },
                })
                .then((response) => {
                    this.mappedDocuments = response.data;
                })
                .catch((err) => {
                    console.error(err);
                    if (err.response && err.response.status === 401) {
                        this.handleAuthError();
                    }
                });
        },
        deleteMapping(mappingId) {
            axios
                .delete(`/api/document-mappings/${mappingId}`, {
                    headers: {
                        Authorization: "Bearer " + this.store.getToken,
                    },
                })
                .then((response) => {
                    this.fetchMappedDocuments();
                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        title: "Mapping deleted successfully",
                        icon: "success",
                        showConfirmButton: false,
                        timer: 2000,
                    });
                })
                .catch((err) => {
                    console.error(err);
                    if (err.response && err.response.status === 401) {
                        this.handleAuthError();
                    } else {
                        Swal.fire({
                            title: "Error!",
                            text: "Failed to delete mapping",
                            icon: "error",
                        });
                    }
                });
        },
        //modal add hom giong
        searchHomGiong() {
            axios
                .get("/api/bienban-homgiong-search", {
                    params: {
                        search: this.searchHomGiongQuery,
                        investment_project: this.document.investment_project,
                        station: this.user.station,
                    },
                    headers: {
                        Authorization: "Bearer " + this.store.getToken,
                    },
                })
                .then((response) => {
                    this.homGiongResults = response.data;
                })
                .catch((err) => {
                    console.error(err);
                    if (err.response && err.response.status === 401) {
                        this.handleAuthError();
                    }
                });
        },

        selectHomGiong(item) {
            this.selectedHomGiong = item;
            this.searchHomGiongQuery = item.ma_so_phieu;
            this.homGiongResults = [];
        },

        addHomGiongMapping(selected) {
            if (!selected) return;

            axios
                .post(
                    "/api/document-mappings-homgiong",
                    {
                        document_code: this.document.document_code,
                        ma_so_phieu: selected.ma_so_phieu,
                    },
                    {
                        headers: {
                            Authorization: "Bearer " + this.store.getToken,
                        },
                    }
                )
                .then((response) => {
                    if (response.status === 200) {
                        Swal.fire({
                            toast: true,
                            position: "top-end",
                            title: "Hom Giong mapping added successfully",
                            icon: "success",
                            showConfirmButton: false,
                            timer: 2000,
                        });
                        console.log("Hom Giong mapping added successfully");
                        this.fetchMappedHomGiongDocuments();
                        // Close modal
                        const modal = document.getElementById("homGiongModal");
                        const modalInstance =
                            bootstrap.Modal.getInstance(modal);
                        modalInstance.hide();
                    }
                })
                .catch((error) => {
                    if (error.response) {
                        if (error.response.status === 401) {
                            this.handleAuthError();
                        } else if (error.response.status === 422) {
                            Swal.fire({
                                title: "Lỗi!",
                                text:
                                    "Cannot add this mapping: " +
                                    error.response.data.error,
                                icon: "error",
                                confirmButtonText: "OK",
                                buttonsStyling: false,
                                customClass: {
                                    confirmButton: "btn btn-success",
                                },
                            });
                        } else if (error.response.status === 500) {
                            Swal.fire({
                                title: "Lỗi!",
                                text: "Internal server error occurred",
                                icon: "error",
                                confirmButtonText: "OK",
                                buttonsStyling: false,
                                customClass: {
                                    confirmButton: "btn btn-success",
                                },
                            });
                        } else {
                            Swal.fire({
                                title: "Lỗi!",
                                text: "An error occurred while adding the mapping",
                                icon: "error",
                                confirmButtonText: "OK",
                                buttonsStyling: false,
                                customClass: {
                                    confirmButton: "btn btn-success",
                                },
                            });
                        }
                    }
                });
        },
        deleteHomGiongMapping(mappingId) {
            axios
                .delete(`/api/document-mappings-homgiong/${mappingId}`, {
                    headers: {
                        Authorization: "Bearer " + this.store.getToken,
                    },
                })
                .then((response) => {
                    this.fetchMappedHomGiongDocuments();
                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        title: "Mapping deleted successfully",
                        icon: "success",
                        showConfirmButton: false,
                        timer: 2000,
                    });
                })
                .catch((err) => {
                    console.error(err);
                    if (err.response && err.response.status === 401) {
                        this.handleAuthError();
                    } else {
                        Swal.fire({
                            title: "Error!",
                            text: "Failed to delete mapping",
                            icon: "error",
                        });
                    }
                });
        },

        fetchMappedHomGiongDocuments() {
            if (!this.document.document_code) return;

            axios
                .get(
                    `/api/document-mappings-homgiong/${this.document.document_code}`,
                    {
                        headers: {
                            Authorization: "Bearer " + this.store.getToken,
                        },
                    }
                )
                .then((response) => {
                    this.mappedHomGiongDocuments = response.data;
                })
                .catch((err) => {
                    console.error(err);
                    if (err.response && err.response.status === 401) {
                        this.handleAuthError();
                    }
                });
        },
        fetchDocumentInfo() {
            if (!this.document.id) {
                // Reset documentInfo if no document id
                this.documentInfo = {
                    creator: null,
                    receiver: null,
                    created_date: null,
                    received_date: null,
                };
                this.documentDates = {
                    created_date: null,
                    received_date: null,
                };
                return;
            }

            axios
                .get(`/api/document-deliveries/${this.document.id}/info`, {
                    headers: {
                        Authorization: "Bearer " + this.store.getToken,
                    },
                })
                .then((response) => {
                    this.documentInfo = response.data;
                    this.documentDates = {
                        created_date: response.data.created_date
                            ? new Date(response.data.created_date)
                                  .toISOString()
                                  .split("T")[0]
                            : null,
                        received_date: response.data.received_date
                            ? new Date(response.data.received_date)
                                  .toISOString()
                                  .split("T")[0]
                            : null,
                    };
                })
                .catch((err) => {
                    console.error("Error fetching document info:", err);
                    if (err.response?.status === 401) {
                        this.handleAuthError();
                    }
                });
        },
        // Add to methods in TaoGiaoNhanhoso.vue
        printDocument() {
            if (!this.document.document_code) {
                Swal.fire({
                    title: "Error",
                    text: "Vui lòng lưu tài liệu trước",
                    icon: "error",
                });
                return;
            }

            // Get token from store
            const token = this.store.getToken;

            // Check for valid auth token
            if (!token) {
                Swal.fire({
                    title: "Error",
                    text: "Unauthorized - Please login first",
                    icon: "error",
                });
                return;
            }

            // Create print URL with token
            const printUrl = `${window.location.origin}/api/print/giaonhan-hoso/${this.document.document_code}?token=${token}`;

            const printWindow = window.open(printUrl, "_blank");

            if (printWindow) {
                printWindow.onload = () => {
                    setTimeout(() => {
                        printWindow.print();
                        setTimeout(() => printWindow.close(), 3000);
                    }, 1000);
                };
            }
        },

        // Add a common method to handle authentication errors
        handleAuthError() {
            // Clear localStorage
            localStorage.removeItem("web_token");
            localStorage.removeItem("web_user");

            // Clear store
            this.store.logout();

            // Redirect to login
            this.$router.push("/login");
        },
        getStatusLabel(status) {
            const statusLabels = {
                creating: "Nháp",
                sending: "Đang nộp",
                received: "Đã nhận",
                cancelled: "Đã hủy",
            };
            return statusLabels[status] || status;
        },
    },

    // Add mounted or watch hook to load data when document_code changes:
    watch: {
        "document.document_code": {
            handler(newVal) {
                if (newVal) {
                    this.fetchMappedDocuments();
                    this.fetchMappedHomGiongDocuments();
                } else {
                    this.mappedDocuments = [];
                    this.mappedHomGiongDocuments = [];
                }
            },
            immediate: true,
        },

        documentDates: {
            handler(newVal) {
                if (this.canModifyDates.created) {
                    this.document.created_date = newVal.created_date;
                }
                if (this.canModifyDates.received) {
                    this.document.received_date = newVal.received_date;
                }
            },
            deep: true,
        },

        "document.id": {
            handler(newVal) {
                if (newVal) {
                    this.fetchDocumentInfo();
                }
            },
            immediate: true,
        },

        documentCount: {
            handler(newCount) {
                this.document.file_count = newCount;
            },
            immediate: true,
        },
    },
};
</script>

<style scoped>
/* ======== MAIN LAYOUT ======== */
.document-delivery-container {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 60px);
    background-color: #f8f9fa;
    position: relative;
}

.scroll-area {
    height: 100%;
    overflow-y: auto;
}

/* ======== HEADER SECTION ======== */
.header-controls-wrapper {
    position: sticky;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1;
    background-color: #ffffff;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    padding: 0.75rem 0;
    transition: all 0.3s ease;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    flex-wrap: wrap;
}

/* Document status indicator */
.document-status-indicator {
    display: flex;
    flex-direction: column;
}

.document-title {
    font-weight: 600;
    margin-bottom: 0.25rem;
    font-size: 1.2rem;
    max-width: 400px;
}

.document-id {
    font-size: 0.85rem;
    color: #6c757d;
}

.id-label {
    font-weight: 500;
}

/* Status badge */
.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 500;
    width: fit-content;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.status-badge.creating {
    background-color: #fff8e1;
    color: #f57c00;
    border: 1px solid #ffca28;
}

.status-badge.sending {
    background-color: #e3f2fd;
    color: #1976d2;
    border: 1px solid #90caf9;
}

.status-badge.received {
    background-color: #e8f5e9;
    color: #2e7d32;
    border: 1px solid #a5d6a7;
}

.status-badge.cancelled {
    background-color: #ffebee;
    color: #d32f2f;
    border: 1px solid #ef9a9a;
}

/* Action buttons */
.action-button-group {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.5rem 1rem;
    font-weight: 500;
    font-size: 0.875rem;
    border-radius: 0.375rem;
    border: none;
    transition: all 0.2s;
    box-shadow: none;
}

.btn:focus {
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.btn i {
    font-size: 1rem;
}

.btn-save {
    background-color: #198754;
    color: white;
}

.btn-save:hover {
    background-color: #1976d2;
}

.btn-new {
    background-color: #4caf50;
    color: white;
}

.btn-new:hover {
    background-color: #388e3c;
}

.btn-submit {
    background-color: #00897b;
    color: white;
}

.btn-submit:hover {
    background-color: #00695c;
}

.btn-reject {
    background-color: #f44336;
    color: white;
}

.btn-reject:hover {
    background-color: #d32f2f;
}

.btn-approve {
    background-color: #4caf50;
    color: white;
}

.btn-approve:hover {
    background-color: #388e3c;
}

.btn-cancel {
    background-color: #ff9800;
    color: white;
}

.btn-cancel:hover {
    background-color: #f57c00;
}

.btn-delete {
    background-color: #d32f2f;
    color: white;
}

.btn-delete:hover {
    background-color: #b71c1c;
}

.btn-print {
    background-color: #607d8b;
    color: white;
}

.btn-print:hover {
    background-color: #455a64;
}

.btn-add {
    background-color: #198754;
    color: white;
    padding: 0.375rem 0.75rem;
}

.btn-add:hover {
    background-color: #1976d2;
}

/* ======== PROGRESS TRACKER ======== */
.progress-tracker-container {
    margin-top: 0.5rem;
}

.progress-tracker {
    position: relative;
    display: flex;
    justify-content: space-between;
    margin: 0.5rem 0;
    padding: 0 1rem;
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

.progress-tracker.creating::before {
    background-color: #fdd835;
}

.progress-tracker.sending::before {
    background-color: #1e88e5;
}

.progress-tracker.received::before {
    background-color: #198754;
}

.progress-tracker.cancelled::before {
    background-color: #d32f2f;
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
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
    border: 2px solid #dee2e6;
    transition: all 0.3s;
}

.track-step.active .step-icon {
    width: 46px;
    height: 46px;
    font-size: 1.3rem;
    border: none;
}

.status-creating {
    color: #fdd835;
}

.status-sending {
    color: #1e88e5;
}

.status-received {
    color: #198754;
}

.status-cancelled {
    color: #d32f2f;
}

.track-step.active .status-creating {
    background-color: #fdd835;
    color: white;
}

.track-step.active .status-sending {
    background-color: #1e88e5;
    color: white;
}

.track-step.active .status-received {
    background-color: #198754;
    color: white;
}

.track-step.active .status-cancelled {
    background-color: #d32f2f;
    color: white;
}

.step-label {
    text-align: center;
    font-size: 0.85rem;
    font-weight: 500;
    max-width: 100px;
}

/* ======== MAIN CONTENT AREA ======== */
.main-content {
    padding: 1.5rem 0;
    background-color: #f8f9fa;
    flex-grow: 1;
}

/* Cards styling */
.info-card {
    background-color: #ffffff;
    border-radius: 0.5rem;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
    height: 100%;
    border: 1px solid #e9ecef;
    transition: transform 0.2s, box-shadow 0.2s;
}

.info-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.info-card-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e9ecef;
}

.info-card-header h5 {
    margin-bottom: 0;
    font-size: 1rem;
    font-weight: 600;
    color: #495057;
    display: flex;
    align-items: center;
}

.info-card-header h5 i {
    margin-right: 0.5rem;
    color: #198754;
    font-size: 1.1rem;
}

.info-card-body {
    padding: 1.25rem;
}

/* Form elements styling */
.form-label {
    font-weight: 500;
    font-size: 0.875rem;
    color: #495057;
    margin-bottom: 0.375rem;
    display: flex;
    align-items: center;
}

.form-control,
.form-select {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    transition: border-color 0.2s ease-in-out;
}

.form-control:focus,
.form-select:focus {
    border-color: #90caf9;
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(33, 150, 243, 0.25);
}

.form-control:disabled,
.form-select:disabled {
    background-color: #f8f9fa;
    opacity: 0.8;
}

.required-indicator {
    color: #d32f2f;
    margin-left: 0.25rem;
}

/* Document stats */
.document-stats {
    background-color: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1.5rem;
    display: flex;
    justify-content: center;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background-color: #e3f2fd;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-icon i {
    font-size: 1.5rem;
    color: #198754;
}

.stat-content {
    display: flex;
    flex-direction: column;
}

.stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #198754;
    line-height: 1;
}

.stat-label {
    color: #6c757d;
    font-size: 0.875rem;
}

/* ======== DATA SECTION STYLING ======== */
.data-section {
    background-color: #ffffff;
    border-radius: 0.5rem;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
    border: 1px solid #e9ecef;
}

.section-card {
    height: 100%;
}

.section-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.section-title i {
    font-size: 1.25rem;
    color: #198754;
}

.section-title h5 {
    margin-bottom: 0;
    font-size: 1rem;
    font-weight: 600;
    color: #343a40;
}

.section-body {
    padding: 0;
}

/* Table styling */
.table {
    margin-bottom: 0;
}

.table th {
    background-color: #f8f9fa;
    font-weight: 600;
    font-size: 0.8125rem;
    color: #495057;
    padding: 0.75rem;
    vertical-align: middle;
    border-color: #e9ecef;
    white-space: nowrap;
}

.table td {
    font-size: 0.875rem;
    padding: 0.625rem 0.75rem;
    vertical-align: middle;
    border-color: #e9ecef;
    white-space: nowrap;
}

.table .data-row {
    transition: background-color 0.15s;
}

.table .data-row:hover {
    background-color: #f8f9fa;
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem 0;
}

.empty-icon {
    font-size: 3rem;
    color: #adb5bd;
    margin-bottom: 1rem;
}

.empty-state p {
    color: #6c757d;
    margin-bottom: 1rem;
}

/* ======== MODAL STYLING ======== */
.modal-header {
    background-color: #198754;
    border-bottom: 1px solid #e9ecef;
    padding: 1rem 1.5rem;
}

.modal-title {
    font-weight: 600;
    font-size: 1.125rem;
    display: flex;
    align-items: center;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    border-top: 1px solid #198754;
    padding: 1rem 1.5rem;
}

/* Search styling */
.search-container {
    position: relative;
}

.search-input-wrapper {
    position: relative;
}

.search-icon {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
}

.search-results-container {
    position: relative;
}

.search-results-list {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    max-height: 200px;
    overflow-y: auto;
    list-style: none;
    padding: 0;
    margin: 0;
    background-color: #ffffff;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    z-index: 1050;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.search-result-item {
    padding: 0.5rem 0.75rem;
    cursor: pointer;
    transition: background-color 0.2s;
}

.search-result-item:hover {
    background-color: #e9ecef;
}

/* Selected item details */
.selected-item-details {
    background-color: #f8f9fa;
    border-radius: 0.375rem;
    margin-top: 1.5rem;
    border: 1px solid #e9ecef;
}

.details-header {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #198754;
}

.details-header h6 {
    margin-bottom: 0;
    font-weight: 600;
    font-size: 0.9375rem;
    color: #495057;
}

.details-body {
    padding: 1rem;
}

/* Responsive adjustments */
@media (max-width: 992px) {
    .header-content {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }

    .action-button-group {
        width: 100%;
    }

    .table th,
    .table td {
        font-size: 0.8125rem;
    }

    .form-label {
        font-size: 0.8125rem;
    }
}

@media (max-width: 767px) {
    .progress-tracker::before {
        width: 95%;
    }

    .step-icon {
        width: 36px;
        height: 36px;
        font-size: 1rem;
    }

    .track-step.active .step-icon {
        width: 42px;
        height: 42px;
        font-size: 1.2rem;
    }

    .step-label {
        font-size: 0.75rem;
    }
}

/* Perfect scrollbar custom styling */
.ps__rail-y {
    width: 9px;
    background-color: transparent !important;
}

.ps__thumb-y {
    width: 6px;
    background-color: rgba(0, 0, 0, 0.2);
    border-radius: 6px;
    transition: background-color 0.2s;
}

.ps__rail-y:hover > .ps__thumb-y,
.ps__rail-y:focus > .ps__thumb-y,
.ps__rail-y.ps--clicking .ps__thumb-y {
    width: 6px;
    background-color: rgba(0, 0, 0, 0.3);
}
</style>
