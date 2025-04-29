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
                            <div class="row align-items-center mb-2"></div>
                        </div>
                        <!-- progress-tracker-container -->
                        <div class="progress-container mt-4">
                            <div class="col-12">
                                <div
                                    class="progress-tracker"
                                    :class="document.status || 'pending'"
                                >
                                    <!-- Pending Step -->
                                    <div
                                        class="track-step"
                                        :class="{
                                            active:
                                                document.status === 'pending' ||
                                                document.status ===
                                                    'approved' ||
                                                document.status === 'paid',
                                        }"
                                    >
                                        <div class="step-icon status-pending">
                                            <i class="fas fa-file-invoice"></i>
                                        </div>
                                        <span class="step-label">Đã trình</span>
                                    </div>

                                    <!-- Approved Step -->
                                    <div
                                        class="track-step"
                                        :class="{
                                            active:
                                                document.status ===
                                                    'approved' ||
                                                document.status === 'paid',
                                        }"
                                    >
                                        <div class="step-icon status-approved">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                        <span class="step-label">Đã duyệt</span>
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
                                <h5 class="card-title">
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
                                                :value="
                                                    document.investment_project
                                                "
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
                                                :value="document.payment_type"
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
                                    Thông tin tài chính và trạng thái
                                </h5>
                                <div class="row gutters">
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
                                                type="text"
                                                class="form-control"
                                                id="soDotTT"
                                                :value="
                                                    document.payment_installment
                                                "
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
                                                :value="
                                                    document.proposal_number
                                                "
                                                disabled
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
                                                type="text"
                                                class="form-control"
                                                id="ngayTao"
                                                :value="
                                                    formatDate(
                                                        document.created_at
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
                                                for="tongTien"
                                                class="form-label"
                                            >
                                                Tổng tiền thanh toán
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="tongTien"
                                                :value="
                                                    formatCurrency(
                                                        document.total_amount
                                                    )
                                                "
                                                disabled
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
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bảng chi tiết -->
                    <div class="card mt-3">
                        <div class="card-body">
                            <span
                                class="reset-all-filters-btn"
                                title="Reset all filters"
                                @click="resetAllFilters"
                            >
                                <i class="fas fa-redo-alt"></i>
                            </span>
                            <!-- Add edit button -->
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
                            <!-- Add delete button -->
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
                            <h5 class="card-title">
                                Chi tiết hồ sơ thanh toán
                            </h5>
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
                                                    filteredPaymentDetails.length ===
                                                    0
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
                                                ) in filteredPaymentDetails"
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
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lịch sử xử lý -->
                    <div class="card mt-3">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-history me-2"></i>Lịch sử xử lý
                            </h5>
                            <div class="timeline-wrapper">
                                <ul
                                    class="timeline"
                                    v-if="processingHistory.length > 0"
                                >
                                    <li
                                        v-for="(
                                            history, index
                                        ) in processingHistory"
                                        :key="index"
                                        class="timeline-item"
                                    >
                                        <div
                                            :class="[
                                                'timeline-badge',
                                                getActionClass(history.action),
                                            ]"
                                        >
                                            <i
                                                :class="
                                                    getActionIcon(
                                                        history.action
                                                    )
                                                "
                                            ></i>
                                        </div>
                                        <div class="timeline-panel">
                                            <div class="timeline-heading">
                                                <h6 class="timeline-title">
                                                    {{
                                                        formatActionText(
                                                            history.action
                                                        )
                                                    }}
                                                </h6>
                                                <small class="text-muted">
                                                    <i
                                                        class="fas fa-calendar-alt me-1"
                                                    ></i>
                                                    {{
                                                        formatDateTime(
                                                            history.created_at
                                                        )
                                                    }}
                                                </small>
                                            </div>
                                            <div class="timeline-body">
                                                <p class="mb-0">
                                                    {{
                                                        history.user_name ||
                                                        "Người dùng"
                                                    }}:
                                                    {{
                                                        history.note ||
                                                        "Xử lý phiếu trình thanh toán"
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                                <div
                                    v-else
                                    class="empty-state text-center py-4"
                                >
                                    <i class="fas fa-history empty-icon"></i>
                                    <p>Chưa có lịch sử xử lý</p>
                                </div>
                            </div>
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
                    <div class="alert alert-info mb-3">
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
            processingHistory: [],
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
                        // Map the payment request data
                        this.document = {
                            payment_code:
                                response.data.document.payment_code || "",
                            title: response.data.document.title || "",
                            investment_project:
                                response.data.document.investment_project || "",
                            payment_type:
                                response.data.document.payment_type || "",
                            status: response.data.document.status || "pending",
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

                        // Map the payment details with fields from tb_bien_ban_nghiemthu_dv
                        this.paymentDetails = Array.isArray(
                            response.data.paymentDetails
                        )
                            ? response.data.paymentDetails.map((item) => ({
                                  document_code: item.document_code || "",
                                  document_type: "Biên bản nghiệm thu DV",
                                  tram: item.tram || "", // Add tram field
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

                        // Process history/logs
                        this.processingHistory = Array.isArray(
                            response.data.processingHistory
                        )
                            ? response.data.processingHistory
                            : [];
                    } else {
                        this.showError(
                            response.data.message ||
                                "Không tìm thấy thông tin phiếu trình thanh toán"
                        );
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
        formatCurrency(value) {
            if (!value) return "0 VNĐ";
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
            if (status === "approved") return "Đã duyệt";
            if (status === "pending") return "Đã trình";
            if (status === "rejected") return "Từ chối";
            return status || "N/A";
        },
        formatActionText(action) {
            if (action === "created") return "Tạo phiếu trình";
            if (action === "approved") return "Đã duyệt phiếu";
            if (action === "paid") return "Đã thanh toán";
            if (action === "rejected") return "Từ chối duyệt";
            if (action === "note_added") return "Thêm ghi chú";
            return action || "Thao tác";
        },
        statusClass(status) {
            if (status === "paid") return "text-success";
            if (status === "approved") return "text-primary";
            if (status === "pending") return "text-warning";
            if (status === "rejected") return "text-danger";
            return "";
        },
        getActionClass(action) {
            if (action === "created") return "bg-primary";
            if (action === "approved") return "bg-success";
            if (action === "paid") return "bg-info";
            if (action === "rejected") return "bg-danger";
            if (action === "note_added") return "bg-secondary";
            return "bg-secondary";
        },
        getActionIcon(action) {
            if (action === "created") return "fas fa-file-invoice";
            if (action === "approved") return "fas fa-check-circle";
            if (action === "paid") return "fas fa-money-bill-wave";
            if (action === "rejected") return "fas fa-times-circle";
            if (action === "note_added") return "fas fa-sticky-note";
            return "fas fa-circle";
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
</style>
