<template>
    <!-- Loading starts -->
    <div id="loading-wrapper" v-if="isLoading">
        <div class="spinner-border" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <div class="container-fluid mx-auto p-2">
        <div class="row align-items-center mb-2" v-if="!isMobile">
            <div class="col d-flex justify-content-start gap-3">
                <div class="status-filter">
                    <select
                        v-model="statusFilter"
                        class="form-select status-select"
                    >
                        <option value="all">
                            Tất cả trạng thái ({{ statusCounts.total }})
                        </option>
                        <option value="submitted">
                            Đã nộp kế toán ({{ statusCounts.submitted }})
                        </option>
                        <option value="processing">
                            Đang xử lý ({{ statusCounts.processing }})
                        </option>
                        <option value="paid">
                            Đã thanh toán ({{ statusCounts.paid }})
                        </option>
                        <option value="rejected">
                            Từ chối ({{ statusCounts.rejected }})
                        </option>
                    </select>
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
                <div
                    class="col d-flex justify-content-end gap-3 align-items-center"
                >
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Tìm kiếm phiếu..."
                        class="search-input px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500"
                    />
                </div>
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
                                            Mã giải ngân
                                            <button
                                                @click="
                                                    toggleFilter('ma_giai_ngan')
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.ma_giai_ngan,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'ma_giai_ngan'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.ma_giai_ngan
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
                                                                'ma_giai_ngan'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'ma_giai_ngan'
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
                                                    toggleFilter('vu_dau_tu')
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            selectedFilterValues.vu_dau_tu &&
                                                            selectedFilterValues
                                                                .vu_dau_tu
                                                                .length > 0,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter === 'vu_dau_tu'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <div
                                                    class="max-h-40 overflow-y-auto mb-2"
                                                >
                                                    <div
                                                        v-for="option in uniqueValues.vu_dau_tu"
                                                        :key="option"
                                                        class="flex items-center mb-2"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            :id="`vu_dau_tu-${option}`"
                                                            :value="option"
                                                            v-model="
                                                                selectedFilterValues.vu_dau_tu
                                                            "
                                                            class="mr-2 rounded text-green-500 focus:ring-green-500"
                                                        />
                                                        <label
                                                            :for="`vu_dau_tu-${option}`"
                                                            class="select-none"
                                                            >{{ option }}</label
                                                        >
                                                    </div>
                                                </div>
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'vu_dau_tu'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'vu_dau_tu'
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
                                                        'loai_thanh_toan'
                                                    )
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            selectedFilterValues.loai_thanh_toan &&
                                                            selectedFilterValues
                                                                .loai_thanh_toan
                                                                .length > 0,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'loai_thanh_toan'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <div
                                                    class="max-h-40 overflow-y-auto mb-2"
                                                >
                                                    <div
                                                        v-for="option in uniqueValues.loai_thanh_toan"
                                                        :key="option"
                                                        class="flex items-center mb-2"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            :id="`loai_thanh_toan-${option}`"
                                                            :value="option"
                                                            v-model="
                                                                selectedFilterValues.loai_thanh_toan
                                                            "
                                                            class="mr-2 rounded text-green-500 focus:ring-green-500"
                                                        />
                                                        <label
                                                            :for="`loai_thanh_toan-${option}`"
                                                            class="select-none"
                                                            >{{ option }}</label
                                                        >
                                                    </div>
                                                </div>
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'loai_thanh_toan'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'loai_thanh_toan'
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
                                            <div
                                                class="flex items-center justify-between"
                                            >
                                                <span
                                                    >Trạng thái thanh toán</span
                                                >
                                                <button
                                                    @click="
                                                        toggleFilter(
                                                            'trang_thai_thanh_toan'
                                                        )
                                                    "
                                                    class="filter-btn"
                                                >
                                                    <i
                                                        class="fas fa-filter"
                                                        :class="{
                                                            'text-green-500':
                                                                selectedFilterValues.trang_thai_thanh_toan &&
                                                                selectedFilterValues
                                                                    .trang_thai_thanh_toan
                                                                    .length > 0,
                                                        }"
                                                    ></i>
                                                </button>
                                            </div>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'trang_thai_thanh_toan'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <div
                                                    class="max-h-40 overflow-y-auto mb-2"
                                                >
                                                    <div
                                                        v-for="option in uniqueValues.trang_thai_thanh_toan"
                                                        :key="option"
                                                        class="flex items-center mb-2"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            :id="`trang_thai_thanh_toan-${option}`"
                                                            :value="option"
                                                            v-model="
                                                                selectedFilterValues.trang_thai_thanh_toan
                                                            "
                                                            class="mr-2 rounded text-green-500 focus:ring-green-500"
                                                        />
                                                        <label
                                                            :for="`trang_thai_thanh_toan-${option}`"
                                                            class="select-none"
                                                            >{{
                                                                formatStatus(
                                                                    option
                                                                )
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
                                                                'trang_thai_thanh_toan'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'trang_thai_thanh_toan'
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
                                                        'ngay_thanh_toan'
                                                    )
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.ngay_thanh_toan,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'ngay_thanh_toan'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="date"
                                                    v-model="
                                                        columnFilters.ngay_thanh_toan
                                                    "
                                                    class="form-control mb-2"
                                                />
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'ngay_thanh_toan'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'ngay_thanh_toan'
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
                                                    toggleFilter(
                                                        'khach_hang_ca_nhan'
                                                    )
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.khach_hang_ca_nhan,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'khach_hang_ca_nhan'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.khach_hang_ca_nhan
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
                                                                'khach_hang_ca_nhan'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'khach_hang_ca_nhan'
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
                                                    toggleFilter(
                                                        'ma_khach_hang_ca_nhan'
                                                    )
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.ma_khach_hang_ca_nhan,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'ma_khach_hang_ca_nhan'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.ma_khach_hang_ca_nhan
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
                                                                'ma_khach_hang_ca_nhan'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'ma_khach_hang_ca_nhan'
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
                                                    toggleFilter(
                                                        'khach_hang_doanh_nghiep'
                                                    )
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.khach_hang_doanh_nghiep,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'khach_hang_doanh_nghiep'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.khach_hang_doanh_nghiep
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
                                                                'khach_hang_doanh_nghiep'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'khach_hang_doanh_nghiep'
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
                                                    toggleFilter(
                                                        'ma_khach_hang_doanh_nghiep'
                                                    )
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.ma_khach_hang_doanh_nghiep,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'ma_khach_hang_doanh_nghiep'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.ma_khach_hang_doanh_nghiep
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
                                                                'ma_khach_hang_doanh_nghiep'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'ma_khach_hang_doanh_nghiep'
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
                                        <th>Tổng tiền thanh toán còn lại</th>
                                        <th>
                                            Số tờ trình
                                            <button
                                                @click="
                                                    toggleFilter('so_to_trinh')
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.so_to_trinh,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'so_to_trinh'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.so_to_trinh
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
                                                                'so_to_trinh'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'so_to_trinh'
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
                                                    toggleFilter(
                                                        'dot_thanh_toan'
                                                    )
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.dot_thanh_toan,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'dot_thanh_toan'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.dot_thanh_toan
                                                    "
                                                    class="form-control mb-2"
                                                    placeholder="Lọc theo đợt thanh toán..."
                                                />
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'dot_thanh_toan'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'dot_thanh_toan'
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
                                        v-for="item in paginatedItems.data"
                                        :key="item.id"
                                        @click="viewDetails(item)"
                                        class="cursor-pointer"
                                    >
                                        <td>{{ item.ma_giai_ngan }}</td>
                                        <td>{{ item.vu_dau_tu }}</td>
                                        <td>{{ item.loai_thanh_toan }}</td>
                                        <td>
                                            <span
                                                v-if="
                                                    item.trang_thai_thanh_toan !==
                                                    undefined
                                                "
                                                :class="
                                                    statusClass(
                                                        item.trang_thai_thanh_toan
                                                    )
                                                "
                                                class="status-badge flex items-center"
                                            >
                                                <i
                                                    :class="
                                                        statusIcons(
                                                            item.trang_thai_thanh_toan
                                                        )
                                                    "
                                                    class="mr-1"
                                                ></i>
                                                {{
                                                    formatStatus(
                                                        item.trang_thai_thanh_toan
                                                    )
                                                }}
                                            </span>
                                        </td>
                                        <td>
                                            {{
                                                formatDate(item.ngay_thanh_toan)
                                            }}
                                        </td>
                                        <td>{{ item.khach_hang_ca_nhan }}</td>
                                        <td>
                                            {{ item.ma_khach_hang_ca_nhan }}
                                        </td>
                                        <td>
                                            {{ item.khach_hang_doanh_nghiep }}
                                        </td>
                                        <td>
                                            {{
                                                item.ma_khach_hang_doanh_nghiep
                                            }}
                                        </td>
                                        <td>
                                            {{ formatCurrency(item.tong_tien) }}
                                        </td>
                                        <td>
                                            {{
                                                formatCurrency(
                                                    item.tong_tien_tam_giu
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                formatCurrency(
                                                    item.tong_tien_khau_tru
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                formatCurrency(
                                                    item.tong_tien_lai_suat
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                formatCurrency(
                                                    item.tong_tien_thanh_toan_con_lai
                                                )
                                            }}
                                        </td>
                                        <td>{{ item.so_to_trinh }}</td>
                                        <td>{{ item.dot_thanh_toan }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="flex justify-center mt-4">
                        <div class="pagination-card">
                            <Bootstrap5Pagination
                                :data="paginatedItems"
                                @pagination-change-page="pageChanged"
                                :limit="5"
                                :classes="paginationClasses"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile view cards -->
        <div v-if="isMobile" class="card-container">
            <div
                v-for="item in paginatedItems.data"
                :key="item.id"
                class="card border p-4 mb-4 rounded shadow hover:shadow-green-500 transition-shadow duration-300"
                @click="viewDetails(item)"
            >
                <div class="flex-1 justify-items-start">
                    <div class="mb-2">
                        <strong>Mã giải ngân:</strong>
                        {{ item.ma_giai_ngan }}
                    </div>
                    <div class="mb-2">
                        <strong>Vụ đầu tư:</strong>
                        {{ item.vu_dau_tu }}
                    </div>
                    <div class="mb-2">
                        <strong>Loại thanh toán:</strong>
                        {{ item.loai_thanh_toan }}
                    </div>
                    <div class="mb-2">
                        <strong>Trạng thái thanh toán:</strong>
                        {{ formatStatus(item.trang_thai_thanh_toan) }}
                    </div>
                    <div class="mb-2">
                        <strong>Ngày thanh toán:</strong>
                        {{ formatDate(item.ngay_thanh_toan) }}
                    </div>
                    <div class="mb-2">
                        <strong>Khách hàng cá nhân:</strong>
                        {{ item.khach_hang_ca_nhan }}
                    </div>
                    <div class="mb-2">
                        <strong>Mã KH cá nhân:</strong>
                        {{ item.ma_khach_hang_ca_nhan }}
                    </div>
                    <div class="mb-2">
                        <strong>Khách hàng doanh nghiệp:</strong>
                        {{ item.khach_hang_doanh_nghiep }}
                    </div>
                    <div class="mb-2">
                        <strong>Mã KH doanh nghiệp:</strong>
                        {{ item.ma_khach_hang_doanh_nghiep }}
                    </div>
                    <div class="mb-2">
                        <strong>Tổng tiền:</strong>
                        {{ formatCurrency(item.tong_tien) }}
                    </div>
                    <div class="mb-2">
                        <strong>Tổng tiền tạm giữ:</strong>
                        {{ formatCurrency(item.tong_tien_tam_giu) }}
                    </div>
                    <div class="mb-2">
                        <strong>Tổng tiền khấu trừ:</strong>
                        {{ formatCurrency(item.tong_tien_khau_tru) }}
                    </div>
                    <div class="mb-2">
                        <strong>Tổng tiền lãi suất:</strong>
                        {{ formatCurrency(item.tong_tien_lai_suat) }}
                    </div>
                    <div class="mb-2">
                        <strong>Tổng tiền thanh toán còn lại:</strong>
                        {{ formatCurrency(item.tong_tien_thanh_toan_con_lai) }}
                    </div>
                    <div class="mb-2">
                        <strong>Số tờ trình:</strong>
                        {{ item.so_to_trinh }}
                    </div>
                    <div class="mb-2">
                        <strong>Đợt thanh toán:</strong>
                        {{ item.dot_thanh_toan }}
                    </div>
                </div>
            </div>
            <div class="flex justify-center mt-4">
                <div class="pagination-card">
                    <Bootstrap5Pagination
                        :data="paginatedItems"
                        @pagination-change-page="pageChanged"
                        :limit="5"
                        :classes="paginationClasses"
                    />
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
                    <p class="text-muted small mb-3">Chọn định dạng xuất:</p>
                    <div class="d-grid gap-2">
                        <button
                            @click="exportToExcelCurrentPage"
                            class="btn btn-outline-success"
                        >
                            <i class="fas fa-file-export me-2"></i>
                            Chỉ xuất trang hiện tại
                        </button>
                        <button
                            @click="exportToExcelAllPages"
                            class="btn btn-success"
                        >
                            <i class="fas fa-table me-2"></i>
                            Xuất tất cả
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import { useStore } from "../../Store/Auth";
import { Bootstrap5Pagination } from "laravel-vue-pagination";
import "bootstrap/dist/js/bootstrap.bundle.min.js";
import "bootstrap/dist/css/bootstrap.min.css";
import Swal from "sweetalert2";
import * as XLSX from "xlsx";
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
            isLoading: false,
            phieuList: [],
            allPhieuList: [],
            search: "",
            statusFilter: "all",
            isMobile: window.innerWidth < 768,
            currentPage: 1,
            perPage: 15,
            statusOptions: [
                { code: "all", name: "Tất cả trạng thái" },
                { code: "submitted", name: "Đã nộp kế toán" },
                { code: "processing", name: "Đang xử lý" },
                { code: "paid", name: "Đã thanh toán" },
                { code: "rejected", name: "Từ chối" },
            ],
            paginationClasses: {
                disabled: "opacity-50 cursor-not-allowed disabled",
            },
            columnFilters: {
                ma_giai_ngan: "",
                ngay_thanh_toan: "",
                khach_hang_ca_nhan: "",
                ma_khach_hang_ca_nhan: "",
                khach_hang_doanh_nghiep: "",
                ma_khach_hang_doanh_nghiep: "",
                so_to_trinh: "",
                dot_thanh_toan: "",
            },
            activeFilter: null,
            selectedFile: null,
            uploadProgress: 0,
            importErrors: [],
            isImporting: false,
            processingRecords: false,
            processingProgress: 0,
            processedRecords: 0,
            totalRecords: 0,
            ps: null, // Add this line for PerfectScrollbar instance
            // Export/import related data
            exportModal: null,
            importModal: null,
            // Unique values and selected filter values
            uniqueValues: {
                vu_dau_tu: [],
                loai_thanh_toan: [],
                trang_thai_thanh_toan: [],
            },
            selectedFilterValues: {
                vu_dau_tu: [],
                loai_thanh_toan: [],
                trang_thai_thanh_toan: [],
            },
        };
    },
    computed: {
        statusCounts() {
            // นับจำนวนรายการทั้งหมด
            const total = this.phieuList.length;

            // นับจำนวนรายการตามแต่ละสถานะ
            const counts = {
                submitted: 0,
                processing: 0,
                paid: 0,
                rejected: 0,
            };

            this.phieuList.forEach((item) => {
                if (
                    item.trang_thai_thanh_toan &&
                    counts.hasOwnProperty(item.trang_thai_thanh_toan)
                ) {
                    counts[item.trang_thai_thanh_toan]++;
                }
            });

            return {
                total,
                ...counts,
            };
        },
        filteredItems() {
            return this.phieuList.filter((item) => {
                // Global search
                const matchesSearch =
                    this.search === "" ||
                    Object.values(item).some(
                        (val) =>
                            val &&
                            val
                                .toString()
                                .toLowerCase()
                                .includes(this.search.toLowerCase())
                    );

                // Status filter
                const matchesStatus =
                    this.statusFilter === "all" ||
                    item.trang_thai_thanh_toan === this.statusFilter;

                // Column filters - text inputs
                const matchesColumnFilters = Object.keys(
                    this.columnFilters
                ).every((key) => {
                    if (!this.columnFilters[key]) return true;

                    if (key === "ngay_thanh_toan" && this.columnFilters[key]) {
                        // Date comparison
                        const filterDate = this.formatDateForComparison(
                            this.columnFilters[key]
                        );
                        const itemDate = item[key]
                            ? this.formatDateForComparison(item[key])
                            : null;
                        return itemDate && itemDate === filterDate;
                    }

                    return (
                        item[key] &&
                        item[key]
                            .toString()
                            .toLowerCase()
                            .includes(this.columnFilters[key].toLowerCase())
                    );
                });

                // Selected filters - checkboxes
                const matchesSelectedFilters = Object.keys(
                    this.selectedFilterValues
                ).every((key) => {
                    if (
                        !this.selectedFilterValues[key] ||
                        this.selectedFilterValues[key].length === 0
                    )
                        return true;
                    return (
                        item[key] &&
                        this.selectedFilterValues[key].includes(item[key])
                    );
                });

                return (
                    matchesSearch &&
                    matchesStatus &&
                    matchesColumnFilters &&
                    matchesSelectedFilters
                );
            });
        },
        paginatedItems() {
            const page = this.currentPage || 1;
            const perPage = this.perPage || 10;
            const total = this.filteredItems.length;
            const lastPage = Math.ceil(total / perPage);
            const from = (page - 1) * perPage + 1;
            const to = Math.min(from + perPage - 1, total);

            const items = this.filteredItems.slice(
                (page - 1) * perPage,
                page * perPage
            );

            return {
                data: items,
                current_page: page,
                from,
                last_page: lastPage,
                per_page: perPage,
                to,
                total,
                links: this.generatePaginationLinks(page, lastPage),
            };
        },
    },
    methods: {
        formatDate(date) {
            if (!date) return "";
            const d = new Date(date);
            const day = d.getDate().toString().padStart(2, "0");
            const month = (d.getMonth() + 1).toString().padStart(2, "0");
            const year = d.getFullYear();
            return `${day}/${month}/${year}`;
        },
        formatStatus(status) {
            if (!status) return "";

            const statusMap = {
                submitted: "Đã nộp kế toán",
                processing: "Đang xử lý",
                paid: "Đã thanh toán",
                rejected: "Từ chối",
            };

            return statusMap[status] || status;
        },
        formatCurrency(value) {
            if (!value) return "";
            return new Intl.NumberFormat("vi-VN", {
                maximumFractionDigits: 0,
            }).format(value);
        },
        pageChanged(page) {
            this.currentPage = page;
            // this.fetchPhieuData(page);
        },
        generatePaginationLinks(currentPage, lastPage) {
            // Generate pagination links for Bootstrap5Pagination
            const links = [];

            // Previous link
            links.push({
                url: currentPage > 1 ? "#" : null,
                label: "&laquo; Previous",
                active: false,
            });

            // Page links
            for (let i = 1; i <= lastPage; i++) {
                links.push({
                    url: "#",
                    label: i.toString(),
                    active: i === currentPage,
                });
            }

            // Next link
            links.push({
                url: currentPage < lastPage ? "#" : null,
                label: "Next &raquo;",
                active: false,
            });

            return links;
        },
        async fetchPhieuData(page = 1) {
            this.isLoading = true;
            try {
                const params = {
                    page: page,
                    per_page: this.perPage,
                    search: this.search || null,
                    status:
                        this.statusFilter !== "all" ? this.statusFilter : null,
                };

                // Add column filters to params
                Object.keys(this.columnFilters).forEach((key) => {
                    if (this.columnFilters[key]) {
                        params[`filter_${key}`] = this.columnFilters[key];
                    }
                });

                const response = await axios.get(
                    "/api/tai-chinh/phieu-de-nghi-thanh-toan-dv",
                    {
                        headers: {
                            Authorization: `Bearer ${localStorage.getItem(
                                "web_token"
                            )}`,
                        },
                        params: params,
                    }
                );

                if (response.data && response.data.success) {
                    this.phieuList = response.data.data;
                    this.currentPage = response.data.pagination.current_page;
                    this.totalPages = response.data.pagination.last_page;
                    this.totalRecords = response.data.pagination.total;

                    // Store the entire collection for operations that need all data
                    if (page === 1) {
                        this.allPhieuList = [...this.phieuList];
                    }

                    // Set unique values for dropdown filters if available
                    if (response.data.unique_filters) {
                        this.uniqueValues = {
                            ...this.uniqueValues,
                            ...response.data.unique_filters,
                        };
                    }
                } else {
                    console.error("Failed to fetch payment request data");
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Failed to fetch payment request data",
                    });
                }
            } catch (error) {
                console.error("Error fetching payment request data:", error);
                if (error.response?.status === 401) {
                    this.handleAuthError();
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "An error occurred while fetching data",
                    });
                }
            } finally {
                this.isLoading = false;
            }
        },
        resetAllFilters() {
            // Reset global search
            this.search = "";

            // Reset status filter
            this.statusFilter = "all";

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

            // Reset to first page
            this.currentPage = 1;
            this.fetchPhieuData(1);
        },
        async viewDetails(item) {
            try {
                this.isLoading = true;
                // Navigate to detail page
                this.$router.push({
                    name: "Details_Phieudenghithanhtoandichvu",
                    params: { id: item.ma_giai_ngan },
                });
            } catch (error) {
                console.error("Error navigating to details:", error);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Could not open details page",
                });
            } finally {
                this.isLoading = false;
            }
        },
        handleAuthError() {
            localStorage.removeItem("web_token");
            localStorage.removeItem("web_user");
            this.store.logout();
            this.$router.push("/login");
        },
        // Column filter methods
        toggleFilter(column) {
            this.activeFilter = this.activeFilter === column ? null : column;

            // If it's one of our dropdown columns and we're opening the filter, ensure unique values are populated
            if (
                (column === "vu_dau_tu" ||
                    column === "loai_thanh_toan" ||
                    column === "trang_thai_thanh_toan") &&
                this.activeFilter === column
            ) {
                this.updateUniqueValues(column);
            }
        },

        updateUniqueValues(column) {
            // Get unique values for dropdown columns
            if (
                column === "vu_dau_tu" ||
                column === "loai_thanh_toan" ||
                column === "trang_thai_thanh_toan"
            ) {
                // Extract unique values from all items
                const uniqueSet = new Set();
                this.allPhieuList.forEach((item) => {
                    if (item[column]) {
                        uniqueSet.add(item[column]);
                    }
                });
                this.uniqueValues[column] = Array.from(uniqueSet).sort();
            }
        },

        resetFilter(column) {
            if (
                column === "vu_dau_tu" ||
                column === "loai_thanh_toan" ||
                column === "trang_thai_thanh_toan"
            ) {
                this.selectedFilterValues[column] = [];
            } else {
                this.columnFilters[column] = "";
            }
            this.currentPage = 1;
        },

        applyFilter(column) {
            this.activeFilter = null;
            this.currentPage = 1;
            this.fetchPhieuData(1); // Fetch data with the new filter applied
        },

        // Export methods
        showExportModal() {
            try {
                // Initialize and show Bootstrap modal
                import("bootstrap/dist/js/bootstrap.bundle.min.js")
                    .then((bootstrap) => {
                        const Modal = bootstrap.Modal;
                        const modalElement =
                            document.getElementById("exportModal");

                        if (modalElement) {
                            this.exportModal = new Modal(modalElement);
                            this.exportModal.show();
                        } else {
                            console.error("Export modal element not found");
                        }
                    })
                    .catch((err) => {
                        console.error("Failed to load Bootstrap:", err);

                        // Fallback with direct DOM manipulation
                        const modalElement =
                            document.getElementById("exportModal");
                        if (modalElement) {
                            modalElement.classList.add("show");
                            modalElement.style.display = "block";
                            document.body.classList.add("modal-open");

                            // Add backdrop
                            const backdrop = document.createElement("div");
                            backdrop.classList.add(
                                "modal-backdrop",
                                "fade",
                                "show"
                            );
                            document.body.appendChild(backdrop);
                        }
                    });
            } catch (error) {
                console.error("Error showing export modal:", error);
            }
        },

        exportToExcelCurrentPage() {
            if (this.paginatedItems.data.length > 0) {
                this.generateExcel(
                    this.paginatedItems.data,
                    "payment_requests_current_page"
                );
                this.closeExportModal();
            } else {
                Swal.fire({
                    icon: "info",
                    title: "No Data",
                    text: "No data available to export",
                });
            }
        },

        exportToExcelAllPages() {
            if (this.filteredItems && this.filteredItems.length > 0) {
                this.generateExcel(this.filteredItems, "payment_requests_all");
                this.closeExportModal();
            } else {
                Swal.fire({
                    icon: "info",
                    title: "No Data",
                    text: "No data available to export",
                });
            }
        },

        generateExcel(data, filename) {
            try {
                // Process data for export - format dates and statuses
                const exportData = data.map((item) => {
                    return {
                        "Mã giải ngân": item.ma_giai_ngan || "",
                        "Vụ đầu tư": item.vu_dau_tu || "",
                        "Loại thanh toán": item.loai_thanh_toan || "",
                        "Trạng thái thanh toán":
                            this.formatStatus(item.trang_thai_thanh_toan) || "",
                        "Ngày thanh toán": item.ngay_thanh_toan
                            ? this.formatDate(item.ngay_thanh_toan)
                            : "",
                        "Khách hàng cá nhân": item.khach_hang_ca_nhan || "",
                        "Mã KH cá nhân": item.ma_khach_hang_ca_nhan || "",
                        "Khách hàng doanh nghiệp":
                            item.khach_hang_doanh_nghiep || "",
                        "Mã KH doanh nghiệp":
                            item.ma_khach_hang_doanh_nghiep || "",
                        "Tổng tiền": item.tong_tien || 0,
                        "Tổng tiền tạm giữ": item.tong_tien_tam_giu || 0,
                        "Tổng tiền khấu trừ": item.tong_tien_khau_tru || 0,
                        "Tổng tiền lãi suất": item.tong_tien_lai_suat || 0,
                        "Tổng tiền thanh toán còn lại":
                            item.tong_tien_thanh_toan_con_lai || 0,
                        "Số tờ trình": item.so_to_trinh || "",
                        "Đợt thanh toán": item.dot_thanh_toan || "",
                    };
                });

                // Create worksheet
                const ws = XLSX.utils.json_to_sheet(exportData);

                // Set column widths
                const wscols = [
                    { wch: 15 }, // Mã giải ngân
                    { wch: 15 }, // Vụ đầu tư
                    { wch: 15 }, // Loại thanh toán
                    { wch: 20 }, // Trạng thái thanh toán
                    { wch: 15 }, // Ngày thanh toán
                    { wch: 25 }, // Khách hàng cá nhân
                    { wch: 15 }, // Mã KH cá nhân
                    { wch: 25 }, // Khách hàng doanh nghiệp
                    { wch: 15 }, // Mã KH doanh nghiệp
                    { wch: 15 }, // Tổng tiền
                    { wch: 15 }, // Tổng tiền tạm giữ
                    { wch: 15 }, // Tổng tiền khấu trừ
                    { wch: 15 }, // Tổng tiền lãi suất
                    { wch: 15 }, // Tổng tiền thanh toán còn lại
                    { wch: 15 }, // Số tờ trình
                    { wch: 15 }, // Đợt thanh toán
                ];
                ws["!cols"] = wscols;

                // Create workbook and add worksheet
                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, "Payment Requests");

                // Generate Excel file
                XLSX.writeFile(
                    wb,
                    `${filename}_${new Date().toISOString().split("T")[0]}.xlsx`
                );
            } catch (error) {
                console.error("Error generating Excel:", error);
                Swal.fire({
                    icon: "error",
                    title: "Export Error",
                    text: "An error occurred while generating the Excel file",
                });
            }
        },

        closeExportModal() {
            try {
                if (this.exportModal) {
                    this.exportModal.hide();
                } else {
                    // Fallback if Bootstrap modal instance is not available
                    const modalElement = document.getElementById("exportModal");
                    if (modalElement) {
                        modalElement.classList.remove("show");
                        modalElement.style.display = "none";
                        document.body.classList.remove("modal-open");

                        // Remove backdrop
                        const backdrop =
                            document.querySelector(".modal-backdrop");
                        if (backdrop) {
                            backdrop.remove();
                        }
                    }
                }
            } catch (error) {
                console.error("Error closing export modal:", error);
            }
        },

        statusClass(status) {
            switch (status) {
                case "submitted":
                    return "text-warning";
                case "processing":
                    return "text-primary";
                case "paid":
                    return "text-success";
                case "rejected":
                    return "text-danger";
                default:
                    return "text-secondary";
            }
        },

        statusIcons(status) {
            switch (status) {
                case "submitted":
                    return "fas fa-clock text-warning";
                case "processing":
                    return "fas fa-spinner text-primary";
                case "paid":
                    return "fas fa-check-circle text-success";
                case "rejected":
                    return "fas fa-times-circle text-danger";
                default:
                    return "";
            }
        },
        formatDateForComparison(date) {
            if (!date) return null;

            const d = new Date(date);
            const year = d.getFullYear();
            const month = (d.getMonth() + 1).toString().padStart(2, "0");
            const day = d.getDate().toString().padStart(2, "0");

            return `${year}-${month}-${day}`;
        },
        // Add these new methods for PerfectScrollbar
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
    },
    watch: {
        search() {
            this.currentPage = 1;
            this.updateScrollbar();
        },
        statusFilter() {
            this.currentPage = 1;
            this.updateScrollbar();
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
    },
    mounted() {
        this.fetchPhieuData();

        // Add PerfectScrollbar initialization
        this.initPerfectScrollbar();

        // Initialize responsive behavior
        window.addEventListener("resize", () => {
            this.isMobile = window.innerWidth < 768;
        });
    },
    updated() {
        // Update scrollbar after component updates
        this.updateScrollbar();
    },
    beforeUnmount() {
        window.removeEventListener("resize", () => {
            this.isMobile = window.innerWidth < 768;
        });

        // Add PerfectScrollbar cleanup
        if (this.ps) {
            this.ps.destroy();
            this.ps = null;
        }
    },
};
</script>

<style scoped>
/* Table & Pagination Styling */
.text-success {
    color: #198754;
}
.text-gray-400 {
    color: #9ca3af;
}
.container {
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
    font-size: 14px;
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

/* Desktop row styling */
.desktop-row,
.cursor-pointer {
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
}

.desktop-row:hover,
.cursor-pointer:hover {
    background-color: #f0f9f0;
    box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);
}

.table-auto td {
    padding: 0.75rem;
    border-bottom: 1px solid #e5e7eb;
    vertical-align: middle;
    font-size: 14px;
    white-space: nowrap;
}

/* Status select styling */
.status-select {
    border: 1px solid #e5e7eb;
    padding: 0.5rem 2rem 0.5rem 1rem;
    border-radius: 0.375rem;
    background-color: #fff;
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

/* Reset filters button styling */
.reset-all-filters-btn {
    position: absolute;
    right: 15px;
    top: 35px;
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

/* Mobile responsive styles */
@media (max-width: 768px) {
    .table-scroll-container {
        max-height: calc(100vh - 300px);
    }

    .status-filter {
        min-width: 100%;
        margin-bottom: 1rem;
    }

    .search-input {
        min-width: 100%;
    }

    .absolute.mt-1.bg-white.p-2.rounded.shadow-lg.z-10 {
        width: 90vw;
        max-width: 90vw;
        left: 0;
        right: 0;
        margin-left: auto;
        margin-right: auto;
    }
}

/* Status badge styling */
.status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
    white-space: nowrap;
}

/* Center align status column Trạng thái thanh toán */
td:nth-child(4) {
    text-align: center;
}

/* Status colors with soft backgrounds */
.text-warning {
    background-color: rgba(255, 193, 7, 0.15);
    color: #8a6d3b;
}

.text-primary {
    background-color: rgba(13, 110, 253, 0.15);
    color: #0d6efd;
}

.text-success {
    background-color: rgba(25, 135, 84, 0.15);
    color: #198754;
}

.text-danger {
    background-color: rgba(220, 53, 69, 0.15);
    color: #dc3545;
}

.text-secondary {
    background-color: rgba(108, 117, 125, 0.15);
    color: #6c757d;
}
/* Keep all your existing styles below this line */
/* ... */
</style>
