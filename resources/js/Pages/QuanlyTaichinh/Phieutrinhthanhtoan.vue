<template>
    <!-- Loading starts -->
    <div id="loading-wrapper" v-if="loading">
        <div class="spinner-border" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <div class="container-fluid mx-auto p-2">
        <div class="row align-items-center mb-2" v-if="!isMobile">
            <div class="col d-flex justify-content-start gap-3">
                <div class="status-filter">
                    <div class="position-relative">
                        <select
                            v-model="statusFilter"
                            class="form-select status-select"
                            @change="currentPage = 1"
                        >
                            <option value="all">
                                Tất cả trạng thái ({{ statusCounts.all }})
                            </option>
                            <option value="processing">
                                {{ formatStatus("processing") }} ({{
                                    statusCounts.processing
                                }})
                            </option>
                            <option value="submitted">
                                {{ formatStatus("submitted") }} ({{
                                    statusCounts.submitted
                                }})
                            </option>
                            <option value="paid">
                                {{ formatStatus("paid") }} ({{
                                    statusCounts.paid
                                }})
                            </option>
                            <option value="cancelled">
                                {{ formatStatus("cancelled") }} ({{
                                    statusCounts.cancelled
                                }})
                            </option>
                        </select>

                        <!-- Status indicator dot -->
                        <span
                            v-if="statusFilter !== 'all'"
                            class="status-indicator"
                            :class="{
                                'status-processing':
                                    statusFilter === 'processing',
                                'status-submitted':
                                    statusFilter === 'submitted',
                                'status-paid': statusFilter === 'paid',
                                'status-cancelled':
                                    statusFilter === 'cancelled',
                            }"
                        >
                        </span>
                    </div>
                </div>
                <!-- Add Export/Import buttons to the dropdown menu -->
                <div class="actions-menu">
                    <div class="dropdown">
                        <button
                            class="btn btn-light btn-icon"
                            type="button"
                            id="actionMenuButton"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                        >
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul
                            class="dropdown-menu shadow-sm"
                            aria-labelledby="actionMenuButton"
                        >
                            <li>
                                <a
                                    class="dropdown-item"
                                    href="#"
                                    @click.prevent="showExportModal"
                                >
                                    <i
                                        class="fas fa-file-excel text-success me-2"
                                    ></i>
                                    Export to Excel
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
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

        <!-- Mobile Controls -->
        <div
            class="mobile-controls p-3 bg-white rounded-lg shadow-sm mb-3"
            v-if="isMobile"
        >
            <div class="flex gap-2 mb-3">
                <div class="flex-1 relative">
                    <span
                        class="absolute inset-y-0 left-0 flex items-center pl-3"
                    >
                        <i class="fas fa-search text-gray-400"></i>
                    </span>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Tìm kiếm phiếu..."
                        class="w-full pl-10 pr-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm"
                    />
                </div>
            </div>
            <div class="status-filter">
                <div class="position-relative">
                    <select
                        v-model="statusFilter"
                        class="form-select status-select"
                        @change="currentPage = 1"
                    >
                        <option value="all">
                            Tất cả trạng thái ({{ statusCounts.all }})
                        </option>
                        <option value="processing">
                            {{ formatStatus("processing") }} ({{
                                statusCounts.processing
                            }})
                        </option>
                        <option value="submitted">
                            {{ formatStatus("submitted") }} ({{
                                statusCounts.submitted
                            }})
                        </option>
                        <option value="paid">
                            {{ formatStatus("paid") }} ({{ statusCounts.paid }})
                        </option>
                        <option value="cancelled">
                            {{ formatStatus("cancelled") }} ({{
                                statusCounts.cancelled
                            }})
                        </option>
                    </select>

                    <!-- Status indicator dot -->
                    <span
                        v-if="statusFilter !== 'all'"
                        class="status-indicator"
                        :class="{
                            'status-processing': statusFilter === 'processing',
                            'status-submitted': statusFilter === 'submitted',
                            'status-paid': statusFilter === 'paid',
                            'status-cancelled': statusFilter === 'cancelled',
                        }"
                    >
                    </span>
                </div>
            </div>
            <div class="investment-filter mt-3">
                <select
                    v-model="investmentFilter"
                    class="form-select investment-select"
                    @change="applyInvestmentFilter"
                >
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

        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <!-- Desktop view table -->
            <div v-if="!isMobile" class="card">
                <div class="card-body">
                    <span
                        class="reset-all-filters-btn"
                        title="Reset all filters"
                        @click="resetAllFilters"
                    >
                        <i class="fas fa-redo-alt"></i>
                    </span>
                    <div class="table-container-wrapper">
                        <div
                            class="table-scroll-container"
                            ref="tableScrollContainer"
                        >
                            <table class="table-auto w-full">
                                <thead>
                                    <tr>
                                        <th>
                                            Mã trình thanh toán
                                            <button
                                                @click="
                                                    toggleFilter(
                                                        'maTrinhThanhToan'
                                                    )
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
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'maTrinhThanhToan'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.maTrinhThanhToan
                                                    "
                                                    class="form-control mb-2"
                                                    placeholder="Lọc theo mã số..."
                                                />
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'maTrinhThanhToan'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'maTrinhThanhToan'
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
                                            <div
                                                v-if="activeFilter === 'tieuDe'"
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.tieuDe
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
                                                                'tieuDe'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'tieuDe'
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
                                                @click="toggleFilter('vuDauTu')"
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            selectedFilterValues.vuDauTu &&
                                                            selectedFilterValues
                                                                .vuDauTu
                                                                .length > 0,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter === 'vuDauTu'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <div
                                                    class="max-h-40 overflow-y-auto"
                                                >
                                                    <div
                                                        v-for="(
                                                            investment, idx
                                                        ) in uniqueInvestments"
                                                        :key="idx"
                                                        class="form-check mb-1"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            class="form-check-input"
                                                            :id="`investment-${idx}`"
                                                            :value="investment"
                                                            v-model="
                                                                selectedFilterValues.vuDauTu
                                                            "
                                                        />
                                                        <label
                                                            class="form-check-label"
                                                            :for="`investment-${idx}`"
                                                        >
                                                            {{ investment }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <div
                                                    class="flex justify-between mt-2"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'vuDauTu'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'vuDauTu'
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
                                                    toggleFilter(
                                                        'loaiThanhToan'
                                                    )
                                                "
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
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'loaiThanhToan'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.loaiThanhToan
                                                    "
                                                    class="form-control mb-2"
                                                    placeholder="Lọc theo loại thanh toán..."
                                                />
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'loaiThanhToan'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'loaiThanhToan'
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
                                            Trạng thái
                                            <button
                                                @click="
                                                    toggleFilter(
                                                        'trangThaiThanhToan'
                                                    )
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            selectedFilterValues.trangThaiThanhToan &&
                                                            selectedFilterValues
                                                                .trangThaiThanhToan
                                                                .length > 0,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'trangThaiThanhToan'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <div
                                                    class="max-h-40 overflow-y-auto"
                                                >
                                                    <div
                                                        v-for="status in uniqueStatuses"
                                                        :key="status"
                                                        class="form-check mb-1"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            class="form-check-input"
                                                            :id="`status-${status}`"
                                                            :value="status"
                                                            v-model="
                                                                selectedFilterValues.trangThaiThanhToan
                                                            "
                                                        />
                                                        <label
                                                            class="form-check-label"
                                                            :for="`status-${status}`"
                                                        >
                                                            {{
                                                                formatStatus(
                                                                    status
                                                                )
                                                            }}
                                                            ({{
                                                                statusCounts[
                                                                    status
                                                                ] || 0
                                                            }})
                                                        </label>
                                                    </div>
                                                </div>
                                                <div
                                                    class="flex justify-between mt-2"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'trangThaiThanhToan'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'trangThaiThanhToan'
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
                                            Số đợt thanh toán
                                            <button
                                                @click="
                                                    toggleFilter(
                                                        'soDotThanhToan'
                                                    )
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.soDotThanhToan,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'soDotThanhToan'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.soDotThanhToan
                                                    "
                                                    class="form-control mb-2"
                                                    placeholder="Lọc theo số đợt..."
                                                />
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'soDotThanhToan'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'soDotThanhToan'
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
                                                    toggleFilter('soToTrinh')
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.soToTrinh,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter === 'soToTrinh'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.soToTrinh
                                                    "
                                                    class="form-control mb-2"
                                                    placeholder="Lọc theo số tờ trình..."
                                                />
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'soToTrinh'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'soToTrinh'
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
                                            Ngày tạo
                                            <button
                                                @click="toggleFilter('ngayTao')"
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.ngayTao,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter === 'ngayTao'
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
                                                            columnFilters.ngayTaoFrom
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
                                                            columnFilters.ngayTaoTo
                                                        "
                                                        class="form-control"
                                                    />
                                                </div>
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'ngayTao'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'ngayTao'
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
                                            Ngày thanh toán
                                            <button
                                                @click="
                                                    toggleFilter(
                                                        'ngayThanhToan'
                                                    )
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.ngayThanhToan,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'ngayThanhToan'
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
                                                            columnFilters.ngayThanhToanFrom
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
                                                            columnFilters.ngayThanhToanTo
                                                        "
                                                        class="form-control"
                                                    />
                                                </div>
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'ngayThanhToan'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'ngayThanhToan'
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
                                            Tổng tiền thanh toán
                                            <button
                                                @click="
                                                    toggleFilter(
                                                        'tongTienThanhToan'
                                                    )
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.tongTienThanhToan,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'tongTienThanhToan'
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
                                                            columnFilters.tongTienThanhToanFrom
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
                                                            columnFilters.tongTienThanhToanTo
                                                        "
                                                        class="form-control"
                                                        placeholder="KIP"
                                                    />
                                                </div>
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'tongTienThanhToan'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'tongTienThanhToan'
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
                                            Người tạo
                                            <button
                                                @click="
                                                    toggleFilter('nguoiTao')
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.nguoiTao,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter === 'nguoiTao'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.nguoiTao
                                                    "
                                                    class="form-control mb-2"
                                                    placeholder="Lọc theo người tạo..."
                                                />
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'nguoiTao'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'nguoiTao'
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
                                        v-for="item in paginatedItems"
                                        :key="item.id"
                                        @dblclick="viewDetails(item)"
                                        class="desktop-row cursor-pointer"
                                    >
                                        <td>{{ item.maTrinhThanhToan }}</td>
                                        <td>{{ item.tieuDe }}</td>

                                        <td>
                                            {{
                                                getInvestmentProjectName(
                                                    item.vuDauTu
                                                )
                                            }}
                                        </td>

                                        <td>{{ item.loaiThanhToan }}</td>
                                        <td>
                                            <span
                                                :class="
                                                    statusClass(
                                                        item.trangThaiThanhToan
                                                    )
                                                "
                                                class="flex items-center"
                                            >
                                                <i
                                                    :class="
                                                        statusIcons(
                                                            item.trangThaiThanhToan
                                                        )
                                                    "
                                                    class="mr-1"
                                                ></i>
                                                {{
                                                    formatStatus(
                                                        item.trangThaiThanhToan
                                                    )
                                                }}
                                            </span>
                                        </td>
                                        <td>{{ item.soDotThanhToan }}</td>
                                        <td>{{ item.soToTrinh }}</td>
                                        <td>{{ formatDate(item.ngayTao) }}</td>
                                        <td>
                                            {{
                                                formatDate(
                                                    item.ngayThanhToan
                                                ) || "Chưa thanh toán"
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                formatCurrency(
                                                    item.tongTienThanhToan
                                                )
                                            }}
                                        </td>
                                        <td>
                                            <div class="flex items-center">
                                                <i
                                                    class="fas fa-user text-gray-400 mr-1"
                                                ></i>
                                                {{ item.nguoiTao }}
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="paginatedItems.length === 0">
                                        <td
                                            colspan="11"
                                            class="text-center py-4"
                                        >
                                            <div class="empty-state">
                                                <i
                                                    class="fas fa-search empty-icon"
                                                ></i>
                                                <p>
                                                    Không có dữ liệu phiếu trình
                                                    thanh toán
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="flex justify-center mt-4">
                        <div class="pagination-card">
                            <Bootstrap5Pagination
                                :data="paginationData"
                                @pagination-change-page="pageChanged"
                                :limit="5"
                                :classes="paginationClasses"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile view cards -->
            <div v-if="isMobile" class="card-container">
                <div
                    v-for="item in paginatedItems"
                    :key="item.id"
                    class="card border p-4 mb-4 rounded shadow hover:shadow-green-500 transition-shadow duration-300"
                    @click="viewDetails(item)"
                >
                    <div class="flex">
                        <!-- First section: Show status with icon -->
                        <div
                            class="flex-shrink-0 flex items-center justify-center mr-4 me-4"
                        >
                            <div
                                class="status-display flex flex-column items-center"
                            >
                                <i
                                    :class="[
                                        statusIcons(item.trangThaiThanhToan),
                                        'text-3xl mb-1',
                                        statusClass(item.trangThaiThanhToan),
                                    ]"
                                ></i>
                                <span
                                    class="status-badge text-xs"
                                    :class="
                                        statusClass(item.trangThaiThanhToan)
                                    "
                                >
                                    {{ formatStatus(item.trangThaiThanhToan) }}
                                </span>
                            </div>
                        </div>
                        <!-- Item details -->
                        <div
                            class="flex-1 justify-items-start"
                            style="font-size: 12px"
                        >
                            <div class="mb-2">
                                <strong>Mã trình thanh toán:</strong>
                                {{ item.maTrinhThanhToan }}
                            </div>
                            <div class="mb-2">
                                <strong>Tiêu đề:</strong>
                                {{ item.tieuDe }}
                            </div>
                            <div class="mb-2">
                                <strong>Vụ đầu tư:</strong>
                                {{ getInvestmentProjectName(item.vuDauTu) }}
                            </div>
                            <div class="mb-2">
                                <strong>Loại thanh toán:</strong>
                                {{ item.loaiThanhToan }}
                            </div>
                            <div class="mb-2">
                                <strong>Số đợt thanh toán:</strong>
                                {{ item.soDotThanhToan }}
                            </div>
                            <div class="mb-2">
                                <strong>Số tờ trình:</strong>
                                {{ item.soToTrinh }}
                            </div>
                            <div class="mb-2">
                                <strong>Ngày tạo:</strong>
                                {{ formatDate(item.ngayTao) }}
                            </div>
                            <div class="mb-2">
                                <strong>Tổng tiền thanh toán:</strong>
                                {{ formatCurrency(item.tongTienThanhToan) }}
                            </div>
                            <div class="mb-2">
                                <strong>
                                    <i
                                        class="fas fa-user text-gray-500 mr-1"
                                    ></i>
                                    Người tạo:
                                </strong>
                                {{ item.nguoiTao }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex justify-center mt-4">
                    <div class="pagination-card">
                        <Bootstrap5Pagination
                            :data="paginationData"
                            @pagination-change-page="pageChanged"
                            :limit="5"
                            :classes="paginationClasses"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Export Modal -->
    <div
        class="modal fade"
        id="exportModal"
        tabindex="-1"
        aria-labelledby="exportModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title text-success" id="exportModalLabel">
                        <i class="fas fa-file-excel text-success me-2"></i>
                        Export to Excel
                    </h5>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                        @click="closeExportModal"
                    ></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex flex-column">
                        <button
                            class="btn btn-outline-success mb-2"
                            @click="exportToExcelCurrentPage"
                        >
                            <i class="fas fa-file-export me-2"></i>
                            Export current page
                        </button>
                        <button
                            class="btn btn-outline-success"
                            @click="exportToExcelAllPages"
                        >
                            <i class="fas fa-file-export me-2"></i>
                            Export all data
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
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
                        <i class="fas fa-upload text-primary me-2"></i>
                        Import data
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
                    <div class="alert alert-warning mb-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <small
                            >Import data chỉ thay thế dữ liệu hiện có. Mã nhập
                            mới Không xóa dữ liệu tham chiếu gốc Vui lòng xác
                            minh thông tin là chính xác trước khi tiếp
                            tục.</small
                        >
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
                                class="progress-bar progress-bar-striped progress-bar-animated"
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

                    <div v-if="processingRecords" class="mb-3">
                        <label class="form-label">Processing data</label>
                        <div class="progress">
                            <div
                                class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                role="progressbar"
                                :style="`width: ${processingProgress}%`"
                                :aria-valuenow="processingProgress"
                                aria-valuemin="0"
                                aria-valuemax="100"
                            >
                                {{ processedRecords }} / {{ totalRecords }}
                            </div>
                        </div>
                    </div>

                    <div v-if="importErrors.length > 0" class="mt-3">
                        <div class="alert alert-danger">
                            <h6 class="alert-heading">Import error</h6>
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
                                <small>
                                    And {{ importErrors.length - 5 }} more
                                    errors...
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal"
                        :disabled="isImporting"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="btn btn-primary"
                        @click="importFile"
                        :disabled="!selectedFile || isImporting"
                    >
                        <span v-if="isImporting">
                            <i
                                class="fas fa-spinner fa-spin me-2"
                                aria-hidden="true"
                            ></i>
                            Processing...
                        </span>
                        <span v-else>Import</span>
                    </button>
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
import PerfectScrollbar from "perfect-scrollbar";
import "perfect-scrollbar/css/perfect-scrollbar.css";

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
                soDotThanhToan: "",
                soToTrinh: "",
                ngayTao: "",
                ngayTaoFrom: "",
                ngayTaoTo: "",
                ngayThanhToan: "",
                ngayThanhToanFrom: "",
                ngayThanhToanTo: "",
                tongTienThanhToan: "",
                tongTienThanhToanFrom: "",
                tongTienThanhToanTo: "",
                nguoiTao: "",
            },
            selectedFilterValues: {
                vuDauTu: [],
                trangThaiThanhToan: [],
            },
            investmentProjects: [],
            ps: null,
            // Add these new properties
            isMobile: window.innerWidth < 768,
            selectedFile: null,
            uploadProgress: 0,
            processingRecords: false,
            processingProgress: 0,
            processedRecords: 0,
            totalRecords: 0,
            importErrors: [],
            isImporting: false,
            paginationClasses: {
                ul: "pagination",
                li: "page-item",
                liActive: "active",
                liDisabled: "disabled",
                button: "page-link",
                buttonActive: "active",
            },
        };
    },
    computed: {
        // Add this computed property to your existing computed properties
        uniqueStatuses() {
            // Get all unique status values that actually exist in the data
            const statuses = [
                ...new Set(this.items.map((item) => item.trangThaiThanhToan)),
            ].filter(Boolean);
            return statuses;
        },
        // Add this computed property to your existing computed properties
        statusCounts() {
            const counts = {
                all: this.items.length,
                processing: 0,
                submitted: 0,
                paid: 0,
                cancelled: 0,
            };

            this.items.forEach((item) => {
                if (counts.hasOwnProperty(item.trangThaiThanhToan)) {
                    counts[item.trangThaiThanhToan]++;
                }
            });

            return counts;
        },
        uniqueInvestments() {
            // Get unique investment project IDs
            const uniqueIds = [
                ...new Set(this.items.map((item) => item.vuDauTu)),
            ].filter(Boolean);

            // Map IDs to names
            return uniqueIds.map((id) => {
                const name = this.getInvestmentProjectName(id);
                return name || id; // Fallback to ID if name not found
            });
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

                // Status filter checkboxes - if any are selected, item must match one of them
                const matchesStatusFilters =
                    this.selectedFilterValues.trangThaiThanhToan.length === 0 ||
                    this.selectedFilterValues.trangThaiThanhToan.includes(
                        item.trangThaiThanhToan
                    );

                // Investment filter
                let matchesInvestment = true;
                if (this.investmentFilter !== "all") {
                    const investmentName = this.getInvestmentProjectName(
                        item.vuDauTu
                    );
                    matchesInvestment =
                        investmentName === this.investmentFilter ||
                        item.vuDauTu === this.investmentFilter;
                }

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
                            this.columnFilters.trangThaiThanhToan) &&
                    (!this.columnFilters.soDotThanhToan ||
                        item.soDotThanhToan
                            .toLowerCase()
                            .includes(
                                this.columnFilters.soDotThanhToan.toLowerCase()
                            )) &&
                    (!this.columnFilters.soToTrinh ||
                        item.soToTrinh
                            .toLowerCase()
                            .includes(
                                this.columnFilters.soToTrinh.toLowerCase()
                            )) &&
                    (!this.columnFilters.ngayTaoFrom ||
                        new Date(item.ngayTao) >=
                            new Date(this.columnFilters.ngayTaoFrom)) &&
                    (!this.columnFilters.ngayTaoTo ||
                        new Date(item.ngayTao) <=
                            new Date(this.columnFilters.ngayTaoTo)) &&
                    (!this.columnFilters.ngayThanhToanFrom ||
                        new Date(item.ngayThanhToan) >=
                            new Date(this.columnFilters.ngayThanhToanFrom)) &&
                    (!this.columnFilters.ngayThanhToanTo ||
                        new Date(item.ngayThanhToan) <=
                            new Date(this.columnFilters.ngayThanhToanTo)) &&
                    (!this.columnFilters.tongTienThanhToanFrom ||
                        item.tongTienThanhToan >=
                            this.columnFilters.tongTienThanhToanFrom) &&
                    (!this.columnFilters.tongTienThanhToanTo ||
                        item.tongTienThanhToan <=
                            this.columnFilters.tongTienThanhToanTo) &&
                    (!this.columnFilters.nguoiTao ||
                        item.nguoiTao
                            .toLowerCase()
                            .includes(
                                this.columnFilters.nguoiTao.toLowerCase()
                            ));

                return (
                    matchesSearch &&
                    matchesStatus &&
                    matchesStatusFilters &&
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
        this.fetchInvestmentProjects();
    },
    methods: {
        // Add this method to fix the error

        isStatusSelected(status) {
            return this.selectedFilterValues.trangThaiThanhToan.includes(
                status
            );
        },

        toggleStatusFilter(status) {
            const index =
                this.selectedFilterValues.trangThaiThanhToan.indexOf(status);
            if (index === -1) {
                // Add to selected statuses
                this.selectedFilterValues.trangThaiThanhToan.push(status);
            } else {
                // Remove from selected statuses
                this.selectedFilterValues.trangThaiThanhToan.splice(index, 1);
            }
        },
        applyInvestmentFilter() {
            // Reset page to 1 when filter changes
            this.currentPage = 1;

            if (this.investmentFilter !== "all") {
                // Find the investment project with this name
                const project = this.investmentProjects.find(
                    (p) =>
                        p.Ten_Vudautu === this.investmentFilter ||
                        p.Ma_Vudautu === this.investmentFilter
                );

                // If found, use its ID for filtering
                if (project) {
                    this.columnFilters.vuDauTu =
                        project.Ma_Vudautu || project.id;
                } else {
                    // Otherwise use the name directly
                    this.columnFilters.vuDauTu = this.investmentFilter;
                }
            } else {
                // Clear the filter when "all" is selected
                this.columnFilters.vuDauTu = "";
            }
        },
        checkScreenSize() {
            this.isMobile = window.innerWidth < 768;
            console.log("Screen size checked, isMobile:", this.isMobile);
        },
        initPerfectScrollbar() {
            // Initialize PerfectScrollbar after the DOM is updated
            this.$nextTick(() => {
                if (this.$refs.tableScrollContainer) {
                    // Destroy existing instance if it exists
                    if (this.ps) {
                        this.ps.destroy();
                    }
                    // Create new PerfectScrollbar instance
                    this.ps = new PerfectScrollbar(
                        this.$refs.tableScrollContainer,
                        {
                            suppressScrollX: false,
                            wheelPropagation: false,
                        }
                    );
                }
            });
        },

        updateScrollbar() {
            // Update the scrollbar when data changes
            this.$nextTick(() => {
                if (this.ps) {
                    this.ps.update();
                }
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

        getInvestmentProjectName(maVuDauTu) {
            if (!maVuDauTu) return "";

            const project = this.investmentProjects.find(
                (p) => p.Ma_Vudautu === maVuDauTu || p.id === maVuDauTu
            );

            return project ? project.Ten_Vudautu : maVuDauTu;
        },
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
                        ngayThanhToan:
                            item.payment_date || item.ngay_thanh_toan, // Add this line
                        tongTienThanhToan:
                            item.total_amount ||
                            item.tong_tien_thanh_toan_con_lai,
                        nguoiTao: item.action_by_name || item.nguoi_tao, // Update this line
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
            if (column === "trangThaiThanhToan") {
                this.selectedFilterValues.trangThaiThanhToan = [];
            } else if (column === "vuDauTu") {
                this.selectedFilterValues.vuDauTu = [];
            } else if (column === "ngayTao") {
                this.columnFilters.ngayTaoFrom = "";
                this.columnFilters.ngayTaoTo = "";
            } else if (column === "ngayThanhToan") {
                this.columnFilters.ngayThanhToanFrom = "";
                this.columnFilters.ngayThanhToanTo = "";
            } else if (column === "tongTienThanhToan") {
                this.columnFilters.tongTienThanhToanFrom = "";
                this.columnFilters.tongTienThanhToanTo = "";
            } else {
                this.columnFilters[column] = "";
            }
            this.currentPage = 1;
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
            this.$router.push(
                `/Details_Phieutrinhthanhtoan/${item.maTrinhThanhToan}`
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

        statusClass(status) {
            switch (status) {
                case "processing":
                    return "text-yellow-600";
                case "submitted":
                    return "text-blue-600";
                case "paid":
                    return "text-green-600";
                case "cancelled":
                    return "text-red-600";
                default:
                    return "text-gray-600";
            }
        },

        statusIcons(status) {
            switch (status) {
                case "processing":
                    return "fas fa-spinner";
                case "submitted":
                    return "fas fa-paper-plane";
                case "paid":
                    return "fas fa-check-circle";
                case "cancelled":
                    return "fas fa-times-circle";
                default:
                    return "fas fa-question-circle";
            }
        },

        formatCurrency(amount) {
            if (!amount) return "0 ₫";
            return new Intl.NumberFormat("vi-VN", {
                style: "currency",
                currency: "KIP",
            }).format(amount);
        },

        resetAllFilters() {
            this.search = "";
            this.statusFilter = "all";
            this.investmentFilter = "all";
            this.activeFilter = null;
            this.columnFilters = {
                maTrinhThanhToan: "",
                tieuDe: "",
                vuDauTu: "",
                loaiThanhToan: "",
                trangThaiThanhToan: "",
                soDotThanhToan: "",
                soToTrinh: "",
                ngayTao: "",
                ngayTaoFrom: "",
                ngayTaoTo: "",
                ngayThanhToan: "",
                ngayThanhToanFrom: "",
                ngayThanhToanTo: "",
                tongTienThanhToan: "",
                tongTienThanhToanFrom: "",
                tongTienThanhToanTo: "",
                nguoiTao: "",
            };
            this.selectedFilterValues = {
                vuDauTu: [],
                trangThaiThanhToan: [],
            };
            this.currentPage = 1;
        },

        showExportModal() {
            const exportModal = new window.bootstrap.Modal(
                document.getElementById("exportModal")
            );
            exportModal.show();
        },

        // Add these methods to your existing methods object in Phieutrinhthanhtoan.vue
        exportToExcelCurrentPage() {
            this.loading = true;

            // Get the current page data from paginatedItems
            setTimeout(() => {
                if (this.paginatedItems.length > 0) {
                    try {
                        // Import xlsx and FileSaver dynamically
                        import("xlsx").then((XLSX) => {
                            import("file-saver").then((module) => {
                                const FileSaver = module.default;

                                // Format data for export
                                const exportData = this.paginatedItems.map(
                                    (item) => {
                                        return {
                                            "Mã trình thanh toán":
                                                item.maTrinhThanhToan || "",
                                            "Tiêu đề": item.tieuDe || "",
                                            "Vụ đầu tư":
                                                this.getInvestmentProjectName(
                                                    item.vuDauTu
                                                ) || "",
                                            "Loại thanh toán":
                                                item.loaiThanhToan || "",
                                            "Trạng thái":
                                                this.formatStatus(
                                                    item.trangThaiThanhToan
                                                ) || "",
                                            "Số đợt thanh toán":
                                                item.soDotThanhToan || "",
                                            "Số tờ trình": item.soToTrinh || "",
                                            "Ngày tạo":
                                                this.formatDate(item.ngayTao) ||
                                                "",
                                            "Ngày thanh toán":
                                                this.formatDate(
                                                    item.ngayThanhToan
                                                ) || "Chưa thanh toán",
                                            "Tổng tiền thanh toán":
                                                item.tongTienThanhToan
                                                    ? parseFloat(
                                                          item.tongTienThanhToan
                                                      )
                                                    : 0,
                                            "Người tạo": item.nguoiTao || "",
                                        };
                                    }
                                );

                                // Create a worksheet
                                const worksheet =
                                    XLSX.utils.json_to_sheet(exportData);

                                // Set column widths
                                const columnWidths = [
                                    { wpx: 120 }, // Mã trình thanh toán
                                    { wpx: 150 }, // Tiêu đề
                                    { wpx: 120 }, // Vụ đầu tư
                                    { wpx: 120 }, // Loại thanh toán
                                    { wpx: 120 }, // Trạng thái
                                    { wpx: 100 }, // Số đợt thanh toán
                                    { wpx: 100 }, // Số tờ trình
                                    { wpx: 100 }, // Ngày tạo
                                    { wpx: 100 }, // Ngày thanh toán
                                    { wpx: 120 }, // Tổng tiền thanh toán
                                    { wpx: 120 }, // Người tạo
                                ];
                                worksheet["!cols"] = columnWidths;

                                // Create a workbook
                                const workbook = XLSX.utils.book_new();
                                XLSX.utils.book_append_sheet(
                                    workbook,
                                    worksheet,
                                    "Phiếu trình thanh toán"
                                );

                                // Create an Excel file
                                const excelBuffer = XLSX.write(workbook, {
                                    bookType: "xlsx",
                                    type: "array",
                                });

                                // Save the file
                                const blob = new Blob([excelBuffer], {
                                    type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                                });
                                FileSaver.saveAs(
                                    blob,
                                    `phieu_trinh_thanh_toan_current_page_${
                                        new Date().toISOString().split("T")[0]
                                    }.xlsx`
                                );

                                // Show success notification using SweetAlert2 toast
                                Swal.fire({
                                    title: "Xuất thành công!",
                                    text: `Đã xuất ${exportData.length} bản ghi ra tệp Excel`,
                                    icon: "success",
                                    toast: true,
                                    position: "top-end",
                                    showConfirmButton: false,
                                    timer: 3000,
                                    timerProgressBar: true,
                                    didOpen: (toast) => {
                                        toast.addEventListener(
                                            "mouseenter",
                                            Swal.stopTimer
                                        );
                                        toast.addEventListener(
                                            "mouseleave",
                                            Swal.resumeTimer
                                        );
                                    },
                                });
                            });
                        });
                    } catch (error) {
                        console.error("Export error:", error);

                        // Show error toast
                        Swal.fire({
                            title: "Xuất không thành công!",
                            text: "Đã xảy ra lỗi khi xuất dữ liệu. Vui lòng thử lại.",
                            icon: "error",
                            toast: true,
                            position: "top-end",
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                        });
                    }
                } else {
                    // Show warning toast for no data
                    Swal.fire({
                        title: "Không có dữ liệu!",
                        text: "Không tìm thấy dữ liệu cần xuất.",
                        icon: "warning",
                        toast: true,
                        position: "top-end",
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                    });
                }
                this.loading = false;

                // Close modal after export
                this.closeExportModal();
            }, 100);
        },

        exportToExcelAllPages() {
            this.loading = true;

            // Use all filtered items instead of just the current page
            setTimeout(() => {
                if (this.filteredItems && this.filteredItems.length > 0) {
                    try {
                        // Import xlsx and FileSaver dynamically
                        import("xlsx").then((XLSX) => {
                            import("file-saver").then((module) => {
                                const FileSaver = module.default;

                                // Format data for export
                                const exportData = this.filteredItems.map(
                                    (item) => {
                                        return {
                                            "Mã trình thanh toán":
                                                item.maTrinhThanhToan || "",
                                            "Tiêu đề": item.tieuDe || "",
                                            "Vụ đầu tư":
                                                this.getInvestmentProjectName(
                                                    item.vuDauTu
                                                ) || "",
                                            "Loại thanh toán":
                                                item.loaiThanhToan || "",
                                            "Trạng thái":
                                                this.formatStatus(
                                                    item.trangThaiThanhToan
                                                ) || "",
                                            "Số đợt thanh toán":
                                                item.soDotThanhToan || "",
                                            "Số tờ trình": item.soToTrinh || "",
                                            "Ngày tạo":
                                                this.formatDate(item.ngayTao) ||
                                                "",
                                            "Ngày thanh toán":
                                                this.formatDate(
                                                    item.ngayThanhToan
                                                ) || "Chưa thanh toán",
                                            "Tổng tiền thanh toán":
                                                item.tongTienThanhToan
                                                    ? parseFloat(
                                                          item.tongTienThanhToan
                                                      )
                                                    : 0,
                                            "Người tạo": item.nguoiTao || "",
                                        };
                                    }
                                );

                                // Create a worksheet
                                const worksheet =
                                    XLSX.utils.json_to_sheet(exportData);

                                // Set column widths
                                const columnWidths = [
                                    { wpx: 120 }, // Mã trình thanh toán
                                    { wpx: 150 }, // Tiêu đề
                                    { wpx: 120 }, // Vụ đầu tư
                                    { wpx: 120 }, // Loại thanh toán
                                    { wpx: 120 }, // Trạng thái
                                    { wpx: 100 }, // Số đợt thanh toán
                                    { wpx: 100 }, // Số tờ trình
                                    { wpx: 100 }, // Ngày tạo
                                    { wpx: 100 }, // Ngày thanh toán
                                    { wpx: 120 }, // Tổng tiền thanh toán
                                    { wpx: 120 }, // Người tạo
                                ];
                                worksheet["!cols"] = columnWidths;

                                // Create a workbook
                                const workbook = XLSX.utils.book_new();
                                XLSX.utils.book_append_sheet(
                                    workbook,
                                    worksheet,
                                    "Phiếu trình thanh toán"
                                );

                                // Create an Excel file
                                const excelBuffer = XLSX.write(workbook, {
                                    bookType: "xlsx",
                                    type: "array",
                                });

                                // Save the file
                                const blob = new Blob([excelBuffer], {
                                    type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                                });
                                FileSaver.saveAs(
                                    blob,
                                    `phieu_trinh_thanh_toan_all_data_${
                                        new Date().toISOString().split("T")[0]
                                    }.xlsx`
                                );

                                // Show success notification using SweetAlert2 toast
                                Swal.fire({
                                    title: "Xuất thành công!",
                                    text: `Đã xuất ${exportData.length} bản ghi ra tệp Excel`,
                                    icon: "success",
                                    toast: true,
                                    position: "top-end",
                                    showConfirmButton: false,
                                    timer: 3000,
                                    timerProgressBar: true,
                                    didOpen: (toast) => {
                                        toast.addEventListener(
                                            "mouseenter",
                                            Swal.stopTimer
                                        );
                                        toast.addEventListener(
                                            "mouseleave",
                                            Swal.resumeTimer
                                        );
                                    },
                                });
                            });
                        });
                    } catch (error) {
                        console.error("Export error:", error);

                        // Show error toast
                        Swal.fire({
                            title: "Xuất không thành công!",
                            text: "Đã xảy ra lỗi khi xuất dữ liệu. Vui lòng thử lại.",
                            icon: "error",
                            toast: true,
                            position: "top-end",
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                        });
                    }
                } else {
                    // Show warning toast for no data
                    Swal.fire({
                        title: "Không có dữ liệu!",
                        text: "Không tìm thấy dữ liệu cần xuất.",
                        icon: "warning",
                        toast: true,
                        position: "top-end",
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                    });
                }
                this.loading = false;

                // Close modal after export
                this.closeExportModal();
            }, 100);
        },

        closeExportModal() {
            // Check if we have stored a modal reference and use it
            if (this.exportModal) {
                this.exportModal.hide();
            } else {
                // Try to close using direct DOM manipulation if Bootstrap isn't working properly
                const modalElement = document.getElementById("exportModal");
                if (modalElement) {
                    modalElement.classList.remove("show");
                    modalElement.style.display = "none";
                    document.body.classList.remove("modal-open");

                    // Remove backdrop
                    const backdrop = document.querySelector(".modal-backdrop");
                    if (backdrop) {
                        backdrop.remove();
                    }
                }
            }
        },

        handleFileSelected(event) {
            this.selectedFile = event.target.files[0];
            // Reset any previous import errors
            this.importErrors = [];
        },
    },

    watch: {
        search() {
            this.currentPage = 1;
            this.updateScrollbar();
        },
        // Add this to your existing watchers
        statusFilter() {
            this.currentPage = 1;

            if (this.statusFilter !== "all") {
                this.columnFilters.trangThaiThanhToan = this.statusFilter;
            } else {
                this.columnFilters.trangThaiThanhToan = "";
            }
        },
        paginatedItems: {
            handler() {
                this.updateScrollbar();
            },
            deep: true,
        },
        filteredItems: {
            handler() {
                this.updateScrollbar();
            },
            deep: true,
        },
        // Add this to existing watch properties
        investmentFilter() {
            this.applyInvestmentFilter();
        },
    },

    mounted() {
        // เพิ่ม event listener สำหรับการเปลี่ยนขนาดหน้าจอ
        window.addEventListener("resize", this.checkScreenSize);

        // Initialize PerfectScrollbar
        this.initPerfectScrollbar();
        this.fetchInvestmentProjects(); // Add this line
    },

    updated() {
        // Update scrollbar after component updates
        this.updateScrollbar();
    },

    beforeUnmount() {
        window.removeEventListener("resize", this.checkScreenSize);

        // Destroy PerfectScrollbar instance when component is unmounted
        if (this.ps) {
            this.ps.destroy();
            this.ps = null;
        }
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
/* .search-input {
    width: 100%;
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    line-height: 1.25rem;
    color: #374151;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid #e5e7eb;
    appearance: none;
    border-radius: 0.375rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    min-width: 250px;
} */

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

/* Table & Pagination Styling */
.text-success {
    color: #198754;
}
.text-gray-400 {
    color: #9ca3af;
}
.container-fluid {
    padding: 1rem;
}
.card-container {
    display: flex;
    flex-direction: column;
}
.card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: box-shadow 0.3s ease;
    position: relative;
}
.card:hover {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Table container with fixed header */
.table-container-wrapper {
    position: relative;
    width: 100%;
}

.table-scroll-container {
    position: relative;
    overflow-x: auto;
    overflow-y: auto;
    max-height: calc(100vh - 250px);
    width: 100%;
}

.desktop-row {
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
}
.desktop-row:hover {
    background-color: #f0f9f0;
    box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);
}
.table-auto th,
.table-auto td {
    padding: 0.75rem;
    border: 1px solid #e5e7eb;
    text-align: left;
}
.table-auto th {
    background-color: #e7e7e7;
    border: 1px solid #e5e7eb;
    padding: 0.75rem;
    vertical-align: top;
}
.table-auto td {
    text-align: left;
    white-space: nowrap;
    overflow: visible;
    text-overflow: clip;
    word-wrap: break-word;
    font-size: 0.875rem;
}
.table-auto {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
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

/* Status styling */
.text-yellow-600 {
    color: #d97706;
}
.text-blue-600 {
    color: #2563eb;
}
.text-green-600 {
    color: #059669;
}
.text-red-600 {
    color: #dc2626;
}
.text-purple-500 {
    color: #8b5cf6;
}

/* Pagination card styling */
.pagination-card {
    padding: 0.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border-radius: 0.5rem;
    background-color: #fff;
    max-height: 50px;
    display: flex;
    justify-content: center;
}

/* Filter dropdown positioning */
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

/* Status filters */
.status-filter {
    position: relative;
    min-width: 200px;
}

.status-select {
    appearance: none;
    background-color: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    padding: 0.5rem 2.5rem 0.5rem 1rem;
    font-size: 0.875rem;
    line-height: 1.25rem;
    color: #374151;
    cursor: pointer;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236B7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.5rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
    transition: all 0.2s ease;
}

.status-select:hover {
    border-color: #10b981;
}

.status-select:focus {
    outline: none;
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
}

/* Investment filter */
.investment-filter {
    position: relative;
    min-width: 200px;
}

.investment-select {
    appearance: none;
    background-color: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    padding: 0.5rem 2.5rem 0.5rem 1rem;
    font-size: 0.875rem;
    line-height: 1.25rem;
    color: #374151;
    cursor: pointer;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236B7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.5rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
    transition: all 0.2s ease;
}

.investment-select:hover {
    border-color: #10b981;
}

.investment-select:focus {
    outline: none;
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
}

/* Mobile responsive styles */
@media (max-width: 768px) {
    .investment-filter {
        min-width: 100%;
        margin-bottom: 1rem;
    }
}

/* จัดการการแสดงผลของไอคอนร่วมกับข้อความ */
.flex.items-center {
    display: flex;
    align-items: center;
}
.mr-1 {
    margin-right: 0.25rem;
}

/* Reset filters button styling */
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

/* Search styles */
/* .search-input {
    width: 100%;
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    line-height: 1.25rem;
    color: #374151;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid #e5e7eb;
    appearance: none;
    border-radius: 0.375rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    min-width: 250px;
} */

.search-input:focus {
    border-color: #10b981;
    outline: 0;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
}

/* Improved Pagination Styling */
.pagination {
    display: flex;
    padding-left: 0;
    list-style: none;
    border-radius: 0.25rem;
    margin: 0;
}

.page-item {
    margin: 0 2px;
}

.page-link {
    position: relative;
    display: block;
    padding: 0.5rem 0.75rem;
    margin-left: -1px;
    line-height: 1.25;
    color: #198754;
    background-color: #fff;
    border: 1px solid #dee2e6;
    transition: all 0.2s ease;
}

.page-link:hover {
    z-index: 2;
    color: #0d6efd;
    text-decoration: none;
    background-color: #e9ecef;
    border-color: #dee2e6;
}

.page-link:focus {
    z-index: 3;
    outline: 0;
    box-shadow: 0 0 0 0.25rem rgba(16, 185, 129, 0.25);
}

.page-item.active .page-link {
    z-index: 3;
    color: #fff;
    background-color: #198754;
    border-color: #198754;
}

.page-item.disabled .page-link {
    color: #6c757d;
    pointer-events: none;
    cursor: auto;
    background-color: #fff;
    border-color: #dee2e6;
}

/* Table row styling */
.table-auto tbody tr {
    border-bottom: 1px solid #e5e7eb;
    transition: all 0.2s ease;
}

.table-auto tbody tr:hover {
    background-color: rgba(16, 185, 129, 0.05);
}

/* Make sure the card has proper overflow handling */
.card-body {
    position: relative;
    overflow: visible;
}

/* Ensure table wrapper handles the overflow context properly */
.table-responsive-wrapper {
    position: relative;
    overflow: visible;
    width: 100%;
}

/* Make the filter dropdowns look more professional */
.absolute.mt-1.bg-white.p-2.rounded.shadow-lg.z-10 {
    padding: 12px;
    background-color: white;
    border-radius: 8px;
}

/* Improve filter input styling */
.absolute.mt-1.bg-white.p-2.rounded.shadow-lg.z-10 input[type="text"] {
    border: 1px solid #e2e8f0;
    transition: all 0.2s;
    font-size: 0.875rem;
}

.absolute.mt-1.bg-white.p-2.rounded.shadow-lg.z-10 input[type="text"]:focus {
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
}

/* Style filter buttons */
.flex.justify-between button {
    transition: all 0.2s;
    font-weight: 500;
}

.flex.justify-between button:hover {
    transform: translateY(-1px);
}

/* Add a subtle pointer indicator to make it clearer the dropdown is tied to a specific column */
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

/* Fix table header sticky positioning for better scrolling */
.table-auto thead th {
    white-space: nowrap;
    text-align: center;
    position: sticky;
    top: 0;
    background-color: #e7e7e7;
    z-index: 10;
}

/* Ensure filter icons look professional */
.fas.fa-filter {
    transition: color 0.2s;
}

button:hover .fas.fa-filter:not(.text-green-500) {
    color: #10b981;
}

/* Improve mobile view for filter dropdowns */
@media (max-width: 768px) {
    .absolute.mt-1.bg-white.p-2.rounded.shadow-lg.z-10 {
        width: 90vw;
        max-width: 90vw;
        left: 0;
        right: 0;
        margin-left: auto;
        margin-right: auto;
    }
}

/* Vertical ellipsis button styling */
.actions-menu {
    position: relative;
}

.btn-icon {
    width: 38px;
    height: 38px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.375rem;
    transition: all 0.2s;
    background-color: #f8f9fa;
    color: #6c757d;
    border: 1px solid #e5e7eb;
}

.btn-icon:hover {
    background-color: #e9ecef;
    color: #495057;
    border-color: #ddd;
}

.btn-icon:focus {
    box-shadow: 0 0 0 0.25rem rgba(16, 185, 129, 0.25);
    border-color: #10b981;
}

.dropdown-menu {
    min-width: 200px;
    padding: 0.5rem 0;
    margin: 0.125rem 0 0;
    border-radius: 0.375rem;
    border: 1px solid rgba(0, 0, 0, 0.1);
}

.dropdown-item {
    padding: 0.6rem 1rem;
    display: flex;
    align-items: center;
    color: #212529;
    transition: all 0.2s;
}

.dropdown-item:hover {
    background-color: #f0fff4;
    color: #10b981;
}

.dropdown-item i {
    font-size: 1rem;
    width: 20px;
    text-align: center;
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

/* Ensure the table container handles overlays properly */
.overflow-x-auto {
    position: relative;
    overflow: visible; /* Allow dropdowns to overflow outside the container */
    width: 100%;
}

/* Ensure filter dropdowns appear on top */
.z-10 {
    z-index: 10;
}

/* Fix for filter dropdowns positioning */
.table-auto th {
    position: relative;
}

/* Improved dropdown positioning to prevent overlap with table headers */
.absolute.mt-1.bg-white.p-2.rounded.shadow-lg.z-10 {
    position: absolute;
    top: calc(100% + 5px); /* Position below the header with some spacing */
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

/* Modal styling */
.modal-header {
    border-bottom: 1px solid #e5e7eb;
}

.modal-footer {
    border-top: 1px solid #e5e7eb;
}

.alert-warning {
    color: #856404;
    background-color: #fff3cd;
    border-color: #ffeeba;
}

.alert-danger {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}

.progress {
    height: 1rem;
    overflow: hidden;
    background-color: #e9ecef;
    border-radius: 0.25rem;
}

.progress-bar {
    display: flex;
    flex-direction: column;
    justify-content: center;
    color: #fff;
    text-align: center;
    white-space: nowrap;
    background-color: #198754;
    transition: width 0.6s ease;
}

.progress-bar-striped {
    background-image: linear-gradient(
        45deg,
        rgba(255, 255, 255, 0.15) 25%,
        transparent 25%,
        transparent 50%,
        rgba(255, 255, 255, 0.15) 50%,
        rgba(255, 255, 255, 0.15) 75%,
        transparent 75%,
        transparent
    );
    background-size: 1rem 1rem;
}

.progress-bar-animated {
    animation: progress-bar-stripes 1s linear infinite;
}

@keyframes progress-bar-stripes {
    from {
        background-position: 1rem 0;
    }
    to {
        background-position: 0 0;
    }
}

/* Empty state styling */
.empty-state {
    padding: 2rem;
    text-align: center;
    color: #6c757d;
}

.empty-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

/* Table container with fixed header */
.table-container-wrapper {
    position: relative;
    width: 100%;
}

.table-scroll-container {
    position: relative;
    max-height: calc(100vh - 240px);
    overflow: auto;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
}

.table-auto {
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
}

/* Fixed header styling */
.table-auto thead {
    position: sticky;
    top: 0;
    z-index: 20;
    background-color: #f9fafb;
    text-align: center;
    white-space: nowrap;
}

.table-auto thead th {
    font-size: 12px;
    position: sticky;
    top: 0;
    background-color: #f3f4f6;
    z-index: 20;
    padding: 0.75rem;
    border-bottom: 2px solid #e5e7eb;
    font-weight: 600;
    text-align: left;
    color: #374151;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

/* Custom scrollbar styling to match perfect-scrollbar */
.table-scroll-container::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

.table-scroll-container::-webkit-scrollbar-track {
    background: transparent;
}

.table-scroll-container::-webkit-scrollbar-thumb {
    background-color: rgba(0, 0, 0, 0.2);
    border-radius: 3px;
}

.table-scroll-container::-webkit-scrollbar-thumb:hover {
    background-color: rgba(0, 0, 0, 0.3);
}

/* Ensure proper spacing in pagination */
.pagination-wrapper {
    position: relative;
    margin-top: 1rem;
    width: 100%;
}

/* Perfect scrollbar customization */
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

.ps__rail-x {
    height: 9px;
    background-color: transparent !important;
}

.ps__thumb-x {
    height: 6px;
    background-color: rgba(0, 0, 0, 0.2);
    border-radius: 6px;
}

.ps__rail-x:hover > .ps__thumb-x,
.ps__rail-x:focus > .ps__thumb-x,
.ps__rail-x.ps--clicking .ps__thumb-x {
    height: 6px;
    background-color: rgba(0, 0, 0, 0.3);
}

/* Add this to your existing style section */
.flex.items-center {
    display: flex;
    align-items: center;
}

.fa-user {
    font-size: 0.9rem; /* Slightly smaller than text */
}

/* Add a subtle hover effect for user information */
td .flex.items-center:hover .fa-user {
    color: #10b981 !important; /* Change to green on hover */
    transition: color 0.3s ease;
}
</style>
