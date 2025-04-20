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
                        <option
                            v-for="option in statusOptions"
                            :key="option.code"
                            :value="option.code"
                        >
                            {{ option.name }}
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
                            <li>
                                <a
                                    class="dropdown-item"
                                    href="#"
                                    @click.prevent="importData"
                                >
                                    <i
                                        class="fas fa-upload text-primary me-2"
                                    ></i>
                                    Import Data
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
                    <div class="overflow-x-auto">
                        <table class="table-auto w-full">
                            <thead>
                                <tr>
                                    <th>
                                        Mã số phiếu
                                        <button
                                            @click="toggleFilter('ma_so_phieu')"
                                            class="filter-btn"
                                        >
                                            <i
                                                class="fas fa-filter"
                                                :class="{
                                                    'text-green-500':
                                                        columnFilters.ma_so_phieu,
                                                }"
                                            ></i>
                                        </button>
                                        <div
                                            v-if="
                                                activeFilter === 'ma_so_phieu'
                                            "
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                        >
                                            <input
                                                type="text"
                                                v-model="
                                                    columnFilters.ma_so_phieu
                                                "
                                                class="form-control mb-2"
                                                placeholder="Lọc theo mã số..."
                                            />
                                            <div class="flex justify-between">
                                                <button
                                                    @click="
                                                        resetFilter(
                                                            'ma_so_phieu'
                                                        )
                                                    "
                                                    class="btn btn-sm btn-light"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="
                                                        applyFilter(
                                                            'ma_so_phieu'
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
                                        Cán bộ nông vụ
                                        <button
                                            @click="
                                                toggleFilter('can_bo_nong_vu')
                                            "
                                            class="filter-btn"
                                        >
                                            <i
                                                class="fas fa-filter"
                                                :class="{
                                                    'text-green-500':
                                                        columnFilters.can_bo_nong_vu,
                                                }"
                                            ></i>
                                        </button>
                                        <div
                                            v-if="
                                                activeFilter ===
                                                'can_bo_nong_vu'
                                            "
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                        >
                                            <input
                                                type="text"
                                                v-model="
                                                    columnFilters.can_bo_nong_vu
                                                "
                                                class="form-control mb-2"
                                                placeholder="Lọc theo cán bộ..."
                                            />
                                            <div class="flex justify-between">
                                                <button
                                                    @click="
                                                        resetFilter(
                                                            'can_bo_nong_vu'
                                                        )
                                                    "
                                                    class="btn btn-sm btn-light"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="
                                                        applyFilter(
                                                            'can_bo_nong_vu'
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
                                            @click="toggleFilter('vu_dau_tu')"
                                            class="filter-btn"
                                        >
                                            <i
                                                class="fas fa-filter"
                                                :class="{
                                                    'text-green-500':
                                                        selectedFilterValues.vu_dau_tu &&
                                                        selectedFilterValues
                                                            .vu_dau_tu.length >
                                                            0,
                                                }"
                                            ></i>
                                        </button>
                                        <div
                                            v-if="activeFilter === 'vu_dau_tu'"
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
                                            <div class="flex justify-between">
                                                <button
                                                    @click="
                                                        resetFilter('vu_dau_tu')
                                                    "
                                                    class="btn btn-sm btn-light"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="
                                                        applyFilter('vu_dau_tu')
                                                    "
                                                    class="btn btn-sm btn-success"
                                                >
                                                    Apply
                                                </button>
                                            </div>
                                        </div>
                                    </th>
                                    <th>
                                        Tên phiếu
                                        <button
                                            @click="toggleFilter('ten_phieu')"
                                            class="filter-btn"
                                        >
                                            <i
                                                class="fas fa-filter"
                                                :class="{
                                                    'text-green-500':
                                                        columnFilters.ten_phieu,
                                                }"
                                            ></i>
                                        </button>
                                        <div
                                            v-if="activeFilter === 'ten_phieu'"
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                        >
                                            <input
                                                type="text"
                                                v-model="
                                                    columnFilters.ten_phieu
                                                "
                                                class="form-control mb-2"
                                                placeholder="Lọc theo tên..."
                                            />
                                            <div class="flex justify-between">
                                                <button
                                                    @click="
                                                        resetFilter('ten_phieu')
                                                    "
                                                    class="btn btn-sm btn-light"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="
                                                        applyFilter('ten_phieu')
                                                    "
                                                    class="btn btn-sm btn-success"
                                                >
                                                    Apply
                                                </button>
                                            </div>
                                        </div>
                                    </th>
                                    <th>
                                        Hợp đồng đầu tư mía bên giao
                                        <button
                                            @click="
                                                toggleFilter(
                                                    'hop_dong_dau_tu_mia_ben_giao'
                                                )
                                            "
                                            class="filter-btn"
                                        >
                                            <i
                                                class="fas fa-filter"
                                                :class="{
                                                    'text-green-500':
                                                        columnFilters.hop_dong_dau_tu_mia_ben_giao,
                                                }"
                                            ></i>
                                        </button>
                                        <div
                                            v-if="
                                                activeFilter ===
                                                'hop_dong_dau_tu_mia_ben_giao'
                                            "
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                        >
                                            <input
                                                type="text"
                                                v-model="
                                                    columnFilters.hop_dong_dau_tu_mia_ben_giao
                                                "
                                                class="form-control mb-2"
                                                placeholder="Lọc theo hợp đồng..."
                                            />
                                            <div class="flex justify-between">
                                                <button
                                                    @click="
                                                        resetFilter(
                                                            'hop_dong_dau_tu_mia_ben_giao'
                                                        )
                                                    "
                                                    class="btn btn-sm btn-light"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="
                                                        applyFilter(
                                                            'hop_dong_dau_tu_mia_ben_giao'
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
                                        Hợp đồng đầu tư mía bên nhận
                                        <button
                                            @click="toggleFilter('ma_hop_dong')"
                                            class="filter-btn"
                                        >
                                            <i
                                                class="fas fa-filter"
                                                :class="{
                                                    'text-green-500':
                                                        columnFilters.ma_hop_dong,
                                                }"
                                            ></i>
                                        </button>
                                        <div
                                            v-if="
                                                activeFilter === 'ma_hop_dong'
                                            "
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                        >
                                            <input
                                                type="text"
                                                v-model="
                                                    columnFilters.ma_hop_dong
                                                "
                                                class="form-control mb-2"
                                                placeholder="Lọc theo hợp đồng..."
                                            />
                                            <div class="flex justify-between">
                                                <button
                                                    @click="
                                                        resetFilter(
                                                            'ma_hop_dong'
                                                        )
                                                    "
                                                    class="btn btn-sm btn-light"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="
                                                        applyFilter(
                                                            'ma_hop_dong'
                                                        )
                                                    "
                                                    class="btn btn-sm btn-success"
                                                >
                                                    Apply
                                                </button>
                                            </div>
                                        </div>
                                    </th>
                                    <th>Tổng thực nhận</th>
                                    <th>Tổng tiền</th>
                                    <th>
                                        Người giao hồ sơ
                                        <button
                                            @click="
                                                toggleFilter('nguoi_giao_ho_so')
                                            "
                                            class="filter-btn"
                                        >
                                            <i
                                                class="fas fa-filter"
                                                :class="{
                                                    'text-green-500':
                                                        columnFilters.nguoi_giao_ho_so,
                                                }"
                                            ></i>
                                        </button>
                                        <div
                                            v-if="
                                                activeFilter ===
                                                'nguoi_giao_ho_so'
                                            "
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                        >
                                            <input
                                                type="text"
                                                v-model="
                                                    columnFilters.nguoi_giao_ho_so
                                                "
                                                class="form-control mb-2"
                                                placeholder="Lọc theo người giao..."
                                            />
                                            <div class="flex justify-between">
                                                <button
                                                    @click="
                                                        resetFilter(
                                                            'nguoi_giao_ho_so'
                                                        )
                                                    "
                                                    class="btn btn-sm btn-light"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="
                                                        applyFilter(
                                                            'nguoi_giao_ho_so'
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
                                        Người nhận hồ sơ
                                        <button
                                            @click="
                                                toggleFilter('nguoi_nhan_ho_so')
                                            "
                                            class="filter-btn"
                                        >
                                            <i
                                                class="fas fa-filter"
                                                :class="{
                                                    'text-green-500':
                                                        columnFilters.nguoi_nhan_ho_so,
                                                }"
                                            ></i>
                                        </button>
                                        <div
                                            v-if="
                                                activeFilter ===
                                                'nguoi_nhan_ho_so'
                                            "
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                        >
                                            <input
                                                type="text"
                                                v-model="
                                                    columnFilters.nguoi_nhan_ho_so
                                                "
                                                class="form-control mb-2"
                                                placeholder="Lọc theo người nhận..."
                                            />
                                            <div class="flex justify-between">
                                                <button
                                                    @click="
                                                        resetFilter(
                                                            'nguoi_nhan_ho_so'
                                                        )
                                                    "
                                                    class="btn btn-sm btn-light"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="
                                                        applyFilter(
                                                            'nguoi_nhan_ho_so'
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
                                        Ngày nhận hồ sơ
                                        <button
                                            @click="
                                                toggleFilter('ngay_nhan_ho_so')
                                            "
                                            class="filter-btn"
                                        >
                                            <i
                                                class="fas fa-filter"
                                                :class="{
                                                    'text-green-500':
                                                        columnFilters.ngay_nhan_ho_so,
                                                }"
                                            ></i>
                                        </button>
                                        <div
                                            v-if="
                                                activeFilter ===
                                                'ngay_nhan_ho_so'
                                            "
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                        >
                                            <input
                                                type="date"
                                                v-model="
                                                    columnFilters.ngay_nhan_ho_so
                                                "
                                                class="form-control mb-2"
                                            />
                                            <div class="flex justify-between">
                                                <button
                                                    @click="
                                                        resetFilter(
                                                            'ngay_nhan_ho_so'
                                                        )
                                                    "
                                                    class="btn btn-sm btn-light"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="
                                                        applyFilter(
                                                            'ngay_nhan_ho_so'
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
                                                >Tình trạng giao nhận hồ
                                                sơ</span
                                            >
                                            <button
                                                @click="
                                                    toggleFilter(
                                                        'tinh_trang_giao_nhan_ho_so'
                                                    )
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            selectedFilterValues.tinh_trang_giao_nhan_ho_so &&
                                                            selectedFilterValues
                                                                .tinh_trang_giao_nhan_ho_so
                                                                .length > 0,
                                                    }"
                                                ></i>
                                            </button>
                                        </div>
                                        <div
                                            v-if="
                                                activeFilter ===
                                                'tinh_trang_giao_nhan_ho_so'
                                            "
                                            class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                        >
                                            <div
                                                class="max-h-40 overflow-y-auto mb-2"
                                            >
                                                <div
                                                    v-for="option in uniqueValues.tinh_trang_giao_nhan_ho_so"
                                                    :key="option"
                                                    class="flex items-center mb-2"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        :id="`tinh_trang_giao_nhan_ho_so-${option}`"
                                                        :value="option"
                                                        v-model="
                                                            selectedFilterValues.tinh_trang_giao_nhan_ho_so
                                                        "
                                                        class="mr-2 rounded text-green-500 focus:ring-green-500"
                                                    />
                                                    <label
                                                        :for="`tinh_trang_giao_nhan_ho_so-${option}`"
                                                        class="select-none"
                                                        >{{
                                                            formatStatus(option)
                                                        }}</label
                                                    >
                                                </div>
                                            </div>
                                            <div class="flex justify-between">
                                                <button
                                                    @click="
                                                        resetFilter(
                                                            'tinh_trang_giao_nhan_ho_so'
                                                        )
                                                    "
                                                    class="btn btn-sm btn-light"
                                                >
                                                    Reset
                                                </button>
                                                <button
                                                    @click="
                                                        applyFilter(
                                                            'tinh_trang_giao_nhan_ho_so'
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
                                >
                                    <td>{{ item.ma_so_phieu }}</td>
                                    <td>{{ item.can_bo_nong_vu }}</td>
                                    <td>{{ item.vu_dau_tu }}</td>
                                    <td>{{ item.ten_phieu }}</td>
                                    <td>
                                        {{
                                            item.hop_dong_dau_tu_mia_ben_giao_hom
                                        }}
                                    </td>
                                    <td>
                                        {{ item.ma_hop_dong }}
                                    </td>
                                    <td>
                                        {{
                                            item.tong_thuc_nhan
                                                ? new Intl.NumberFormat().format(
                                                      item.tong_thuc_nhan
                                                  )
                                                : "0"
                                        }}
                                    </td>
                                    <td>
                                        {{ formatCurrency(item.tong_tien) }}
                                    </td>
                                    <td>{{ item.nguoi_giao_ho_so }}</td>
                                    <td>{{ item.nguoi_nhan_ho_so }}</td>
                                    <td>
                                        {{ formatDate(item.ngay_nhan_ho_so) }}
                                    </td>
                                    <td>
                                        {{
                                            formatStatus(
                                                item.tinh_trang_giao_nhan_ho_so
                                            )
                                        }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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
                        <strong>Mã số phiếu:</strong>
                        {{ item.ma_so_phieu }}
                    </div>
                    <div class="mb-2">
                        <strong>Cán bộ nông vụ:</strong>
                        {{ item.can_bo_nong_vu }}
                    </div>
                    <div class="mb-2">
                        <strong>Vụ đầu tư:</strong>
                        {{ item.vu_dau_tu }}
                    </div>
                    <div class="mb-2">
                        <strong>Tên phiếu:</strong>
                        {{ item.ten_phieu }}
                    </div>
                    <div class="mb-2">
                        <strong>Hợp đồng đầu tư mía bên giao:</strong>
                        {{ item.hop_dong_dau_tu_mia_ben_giao_hom }}
                    </div>
                    <div class="mb-2">
                        <strong>Hợp đồng đầu tư mía bên nhận:</strong>
                        {{ item.ma_hop_dong }}
                    </div>
                    <div class="mb-2">
                        <strong>Tổng thực nhận:</strong>
                        {{ formatCurrency(item.tong_thuc_nhan) }}
                    </div>
                    <div class="mb-2">
                        <strong>Tổng tiền:</strong>
                        {{ formatCurrency(item.tong_tien) }}
                    </div>
                    <div class="mb-2">
                        <strong>Người giao hồ sơ:</strong>
                        {{ item.nguoi_giao_ho_so }}
                    </div>
                    <div class="mb-2">
                        <strong>Người nhận hồ sơ:</strong>
                        {{ item.nguoi_nhan_ho_so }}
                    </div>
                    <div class="mb-2">
                        <strong>Ngày nhận hồ sơ:</strong>
                        {{ formatDate(item.ngay_nhan_ho_so) }}
                    </div>
                    <div class="mb-2">
                        <strong>Tình trạng giao nhận hồ sơ:</strong>
                        {{ formatStatus(item.tinh_trang_giao_nhan_ho_so) }}
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

    <!-- Add Import Modal -->
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
                                <small>Other errors...</small>
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
</template>

<script>
import axios from "axios";
import { useStore } from "../../Store/Auth";
import { Bootstrap5Pagination } from "laravel-vue-pagination";
import "bootstrap/dist/js/bootstrap.bundle.min.js";
import "bootstrap/dist/css/bootstrap.min.css";
import Swal from "sweetalert2";
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
            perPage: 10,
            statusOptions: [
                { code: "all", name: "Tất cả" },
                { code: "approved", name: "Đã duyệt" },
                { code: "pending", name: "Chờ duyệt" },
                { code: "rejected", name: "Từ chối" },
            ],
            paginationClasses: {
                ul: "flex list-none pagination",
                li: "page-item mx-1",
                a: "page-link px-3 py-2 bg-white border border-gray-300 rounded hover:bg-gray-100",
                active: "bg-green-500 text-white active",
                disabled: "opacity-50 cursor-not-allowed disabled",
            },
            columnFilters: {
                ma_so_phieu: "",
                can_bo_nong_vu: "",
                vu_dau_tu: "",
                ten_phieu: "",
                hop_dong_dau_tu_mia_ben_giao: "",
                ma_hop_dong: "",
                nguoi_giao_ho_so: "",
                nguoi_nhan_ho_so: "",
                ngay_nhan_ho_so: "",
                tinh_trang_giao_nhan_ho_so: "",
            },
            activeFilter: null,
            selectedFile: null,
            uploadProgress: 0,
            importErrors: [],
            isImporting: false,
            // Add export/import related data
            exportModal: null,
            importModal: null,
            // Add unique values and selected filter values
            uniqueValues: {
                vu_dau_tu: [],
                tinh_trang_giao_nhan_ho_so: [],
            },
            selectedFilterValues: {
                vu_dau_tu: [],
                tinh_trang_giao_nhan_ho_so: [],
            },
        };
    },
    computed: {
        filteredItems() {
            return this.phieuList.filter((item) => {
                // Apply global search
                const matchesSearch =
                    item.ma_so_phieu
                        ?.toLowerCase()
                        .includes(this.search.toLowerCase()) ||
                    item.ten_phieu
                        ?.toLowerCase()
                        .includes(this.search.toLowerCase()) ||
                    item.can_bo_nong_vu
                        ?.toLowerCase()
                        .includes(this.search.toLowerCase());

                // Apply status filter
                let matchesStatus = true;
                if (this.statusFilter !== "all") {
                    if (this.statusFilter === "approved") {
                        matchesStatus = item.tinh_trang_giao_nhan_ho_so === 1;
                    } else if (this.statusFilter === "pending") {
                        matchesStatus = item.tinh_trang_giao_nhan_ho_so === 0;
                    } else if (this.statusFilter === "rejected") {
                        matchesStatus = item.tinh_trang_giao_nhan_ho_so === 2;
                    }
                }

                // Apply column filters
                const matchesColumnFilters =
                    // Mã số phiếu
                    (!this.columnFilters.ma_so_phieu ||
                        (item.ma_so_phieu &&
                            item.ma_so_phieu
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.ma_so_phieu.toLowerCase()
                                ))) &&
                    // Cán bộ nông vụ
                    (!this.columnFilters.can_bo_nong_vu ||
                        (item.can_bo_nong_vu &&
                            item.can_bo_nong_vu
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.can_bo_nong_vu.toLowerCase()
                                ))) &&
                    // Vụ đầu tư (Unique dropdown filter)
                    (this.selectedFilterValues.vu_dau_tu.length === 0 ||
                        (item.vu_dau_tu &&
                            this.selectedFilterValues.vu_dau_tu.includes(
                                item.vu_dau_tu
                            ))) &&
                    // Tên phiếu
                    (!this.columnFilters.ten_phieu ||
                        (item.ten_phieu &&
                            item.ten_phieu
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.ten_phieu.toLowerCase()
                                ))) &&
                    // Hợp đồng đầu tư mía bên giao
                    (!this.columnFilters.hop_dong_dau_tu_mia_ben_giao ||
                        (item.hop_dong_dau_tu_mia_ben_giao_hom &&
                            item.hop_dong_dau_tu_mia_ben_giao_hom
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.hop_dong_dau_tu_mia_ben_giao.toLowerCase()
                                ))) &&
                    // Hợp đồng đầu tư mía bên nhận
                    (!this.columnFilters.ma_hop_dong ||
                        (item.ma_hop_dong &&
                            item.ma_hop_dong
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.ma_hop_dong.toLowerCase()
                                ))) &&
                    // Người giao hồ sơ
                    (!this.columnFilters.nguoi_giao_ho_so ||
                        (item.nguoi_giao_ho_so &&
                            item.nguoi_giao_ho_so
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.nguoi_giao_ho_so.toLowerCase()
                                ))) &&
                    // Người nhận hồ sơ
                    (!this.columnFilters.nguoi_nhan_ho_so ||
                        (item.nguoi_nhan_ho_so &&
                            item.nguoi_nhan_ho_so
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.nguoi_nhan_ho_so.toLowerCase()
                                ))) &&
                    // Ngày nhận hồ sơ
                    (!this.columnFilters.ngay_nhan_ho_so ||
                        (item.ngay_nhan_ho_so &&
                            this.formatDate(item.ngay_nhan_ho_so).includes(
                                this.columnFilters.ngay_nhan_ho_so
                            ))) &&
                    // Tình trạng giao nhận hồ sơ
                    (this.selectedFilterValues.tinh_trang_giao_nhan_ho_so
                        .length === 0 ||
                        (item.tinh_trang_giao_nhan_ho_so &&
                            this.selectedFilterValues.tinh_trang_giao_nhan_ho_so.includes(
                                item.tinh_trang_giao_nhan_ho_so
                            )));

                return matchesSearch && matchesStatus && matchesColumnFilters;
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
                current_page: page,
                data: items,
                from: from,
                last_page: lastPage,
                per_page: perPage,
                to: to,
                total: total,
                prev_page_url: page > 1 ? `?page=${page - 1}` : null,
                next_page_url: page < lastPage ? `?page=${page + 1}` : null,
            };
        },
    },
    methods: {
        formatDate(date) {
            if (!date) return "";
            return new Date(date).toLocaleDateString("vi-VN");
        },
        formatStatus(status) {
            if (!status) return "";

            const statusMap = {
                0: "Chờ duyệt",
                1: "Đã duyệt",
                2: "Từ chối",
            };

            return statusMap[status] || status;
        },
        formatCurrency(value) {
            if (!value) return "0";
            return new Intl.NumberFormat("vi-VN", {
                style: "currency",
                currency: "KIP",
                maximumFractionDigits: 0,
            }).format(value);
        },
        pageChanged(page) {
            this.currentPage = page;
        },
        async fetchPhieuData(page = 1) {
            this.isLoading = true;
            try {
                const response = await axios.get(
                    "/api/phieu-giao-nhan-hom-giong",
                    {
                        params: {
                            page,
                        },
                        headers: {
                            Authorization: "Bearer " + this.store.getToken,
                        },
                    }
                );

                if (response.data.success) {
                    this.allPhieuList = response.data.data;
                    this.phieuList = this.allPhieuList;
                    this.currentPage = page;
                    // Extract unique values for filters
                    this.uniqueValues.vu_dau_tu = [
                        ...new Set(
                            this.allPhieuList.map((item) => item.vu_dau_tu)
                        ),
                    ];
                    this.uniqueValues.tinh_trang_giao_nhan_ho_so = [
                        ...new Set(
                            this.allPhieuList.map(
                                (item) => item.tinh_trang_giao_nhan_ho_so
                            )
                        ),
                    ];
                } else {
                    throw new Error(response.data.message);
                }
            } catch (error) {
                console.error("Error fetching phiếu:", error);
                if (error.response?.status === 401) {
                    this.handleAuthError();
                } else {
                    Swal.fire({
                        confirmButtonText: "OK",
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
        },
        viewDetails(item) {
            console.log("Viewing details for:", item);
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

            // If it's the vu_dau_tu column and we're opening the filter, ensure unique values are populated
            if (column === "vu_dau_tu" && this.activeFilter === "vu_dau_tu") {
                this.updateUniqueValues("vu_dau_tu");
            }
            if (
                column === "tinh_trang_giao_nhan_ho_so" &&
                this.activeFilter === "tinh_trang_giao_nhan_ho_so"
            ) {
                this.updateUniqueValues("tinh_trang_giao_nhan_ho_so");
            }
        },

        updateUniqueValues(column) {
            // Get unique values for dropdown columns
            if (column === "vu_dau_tu") {
                this.uniqueValues[column] = [
                    ...new Set(
                        this.phieuList
                            .map((item) => item[column])
                            .filter(Boolean) // Remove null/undefined values
                    ),
                ];
            }
            if (column === "tinh_trang_giao_nhan_ho_so") {
                this.uniqueValues[column] = [
                    ...new Set(
                        this.phieuList
                            .map((item) => item[column])
                            .filter(Boolean) // Remove null/undefined values
                    ),
                ];
            }
        },

        resetFilter(column) {
            if (column === "vu_dau_tu") {
                this.selectedFilterValues[column] = [];
            } else if (column === "tinh_trang_giao_nhan_ho_so") {
                this.selectedFilterValues[column] = [];
            } else {
                this.columnFilters[column] = "";
            }
            this.currentPage = 1;
        },

        applyFilter(column) {
            this.activeFilter = null;
            this.currentPage = 1;
        },

        // Export methods
        showExportModal() {
            try {
                // Ensure Bootstrap is properly imported and initialized
                import("bootstrap/dist/js/bootstrap.bundle.min.js")
                    .then((bootstrap) => {
                        const Modal = bootstrap.Modal;
                        const modalElement =
                            document.getElementById("exportModal");

                        if (modalElement) {
                            this.exportModal = new Modal(modalElement);
                            this.exportModal.show();
                        } else {
                            console.error("Modal element not found");
                        }
                    })
                    .catch((err) => {
                        console.error("Failed to load Bootstrap:", err);
                        // Fallback method using direct DOM manipulation
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
                console.error("Error showing modal:", error);
            }
        },

        exportToExcelCurrentPage() {
            if (
                this.paginatedItems.data &&
                this.paginatedItems.data.length > 0
            ) {
                this.generateExcel(
                    this.paginatedItems.data,
                    "phieu_giao_nhan_current_page"
                );
                this.closeExportModal();
            } else {
                alert("Không có dữ liệu để xuất");
            }
        },

        exportToExcelAllPages() {
            if (this.filteredItems && this.filteredItems.length > 0) {
                this.generateExcel(
                    this.filteredItems,
                    "phieu_giao_nhan_all_data"
                );
                this.closeExportModal();
            } else {
                alert("Không có dữ liệu để xuất");
            }
        },

        generateExcel(data, filename) {
            try {
                // Import xlsx and FileSaver dynamically
                import("xlsx").then((XLSX) => {
                    import("file-saver").then((module) => {
                        const FileSaver = module.default;

                        if (!data || data.length === 0) {
                            alert("Không có dữ liệu để xuất");
                            return;
                        }

                        // Format data for export
                        const exportData = data.map((item) => ({
                            "Mã số phiếu": item.ma_so_phieu || "",
                            "Cán bộ nông vụ": item.can_bo_nong_vu || "",
                            "Vụ đầu tư": item.vu_dau_tu || "",
                            "Tên phiếu": item.ten_phieu || "",
                            "Hợp đồng đầu tư mía bên giao":
                                item.hop_dong_dau_tu_mia_ben_giao_hom || "",
                            "Hợp đồng đầu tư mía bên nhận":
                                item.ma_hop_dong || "",
                            "Tổng thực nhận": item.tong_thuc_nhan || 0,
                            "Tổng tiền": item.tong_tien || 0,
                            "Người giao hồ sơ": item.nguoi_giao_ho_so || "",
                            "Người nhận hồ sơ": item.nguoi_nhan_ho_so || "",
                            "Ngày nhận hồ sơ": item.ngay_nhan_ho_so
                                ? new Date(
                                      item.ngay_nhan_ho_so
                                  ).toLocaleDateString("vi-VN")
                                : "",
                            "Tình trạng giao nhận hồ sơ":
                                this.formatStatus(
                                    item.tinh_trang_giao_nhan_ho_so
                                ) || "",
                        }));

                        // Create a worksheet
                        const worksheet = XLSX.utils.json_to_sheet(exportData);

                        // Set column widths
                        const columnWidths = [
                            { wch: 15 }, // Mã số phiếu
                            { wch: 20 }, // Cán bộ nông vụ
                            { wch: 15 }, // Vụ đầu tư
                            { wch: 30 }, // Tên phiếu
                            { wch: 25 }, // Hợp đồng đầu tư mía bên giao
                            { wch: 25 }, // Hợp đồng đầu tư mía bên nhận
                            { wch: 15 }, // Tổng thực nhận
                            { wch: 15 }, // Tổng tiền
                            { wch: 20 }, // Người giao hồ sơ
                            { wch: 20 }, // Người nhận hồ sơ
                            { wch: 15 }, // Ngày nhận hồ sơ
                            { wch: 20 }, // Tình trạng giao nhận hồ sơ
                        ];
                        worksheet["!cols"] = columnWidths;

                        // Create a workbook
                        const workbook = XLSX.utils.book_new();
                        XLSX.utils.book_append_sheet(
                            workbook,
                            worksheet,
                            "Phiếu giao nhận"
                        );

                        // Create an Excel file
                        const excelBuffer = XLSX.write(workbook, {
                            bookType: "xlsx",
                            type: "array",
                        });
                        const blob = new Blob([excelBuffer], {
                            type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                        });
                        FileSaver.saveAs(
                            blob,
                            `${filename}_${
                                new Date().toISOString().split("T")[0]
                            }.xlsx`
                        );

                        // Show success notification
                        Swal.fire({
                            title: "Thành công!",
                            text: "Xuất Excel thành công",
                            icon: "success",
                            confirmButtonText: "OK",
                            buttonsStyling: false,
                            customClass: {
                                confirmButton: "btn btn-success",
                            },
                        });
                    });
                });
            } catch (error) {
                console.error("Export error:", error);
                Swal.fire({
                    title: "Lỗi!",
                    text: "Đã xảy ra lỗi khi xuất dữ liệu. Vui lòng thử lại.",
                    icon: "error",
                    confirmButtonText: "OK",
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: "btn btn-success",
                    },
                });
            }
        },

        closeExportModal() {
            const modalElement = document.getElementById("exportModal");
            if (modalElement) {
                // First move focus to a safe element outside the modal
                document.body.focus();
                // Then hide the modal
                const bsModal = bootstrap.Modal.getInstance(modalElement);
                if (bsModal) {
                    bsModal.hide();
                }
            }
        },

        importData() {
            // Show the import modal
            try {
                import("bootstrap/dist/js/bootstrap.bundle.min.js")
                    .then((bootstrap) => {
                        const Modal = bootstrap.Modal;
                        const modalElement =
                            document.getElementById("importModal");

                        if (modalElement) {
                            const importModal = new Modal(modalElement);
                            importModal.show();
                        } else {
                            console.error("Import modal element not found");
                        }
                    })
                    .catch((err) => {
                        console.error("Failed to load Bootstrap:", err);

                        // Fallback method using direct DOM manipulation
                        const modalElement =
                            document.getElementById("importModal");
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
                console.error("Error showing import modal:", error);
            }
        },

        handleFileSelected(event) {
            const file = event.target.files[0];
            if (file) {
                // Validate file type
                const fileType = file.name.split(".").pop().toLowerCase();
                if (!["csv", "xlsx"].includes(fileType)) {
                    alert("Vui lòng chọn đúng tập tin. (CSV hoặc Excel)");
                    event.target.value = ""; // Clear the file input
                    this.selectedFile = null;
                    return;
                }

                this.selectedFile = file;
                this.importErrors = [];
                console.log("File selected:", file.name);
            } else {
                this.selectedFile = null;
            }
        },

        closeImportModal() {
            try {
                const modalElement = document.getElementById("importModal");
                if (modalElement) {
                    if (window.bootstrap) {
                        const modalInstance =
                            window.bootstrap.Modal.getInstance(modalElement);
                        if (modalInstance) {
                            modalInstance.hide();
                        }
                    } else {
                        // Fallback if bootstrap is not available
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

                // Reset import state
                this.selectedFile = null;
                this.uploadProgress = 0;
                this.processingRecords = false;
                this.processingProgress = 0;
                this.processedRecords = 0;
                this.totalRecords = 0;
                this.importErrors = [];
            } catch (error) {
                console.error("Error closing import modal:", error);
            }
        },

        async startImport() {
            if (!this.selectedFile) {
                Swal.fire({
                    title: "Cảnh báo",
                    text: "Vui lòng chọn tệp để tải lên",
                    icon: "warning",
                    confirmButtonText: "OK",
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: "btn btn-success",
                    },
                });
                return;
            }

            // Confirm before proceeding with SweetAlert2
            const result = await Swal.fire({
                title: "Xác nhận tải lên",
                text: "Cảnh báo: Việc nhập dữ liệu sẽ thay đổi dữ liệu theo mã tài liệu được nhập. Bạn có chắc chắn muốn tiếp tục không?",
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "OK",
                cancelButtonText: "Hủy",
                buttonsStyling: false,
                customClass: {
                    confirmButton: "btn btn-success me-2",
                    cancelButton: "btn btn-outline-success",
                },
            });

            if (!result.isConfirmed) {
                return;
            }

            this.isImporting = true;
            this.uploadProgress = 0;
            this.processingRecords = false;
            this.processingProgress = 0;
            this.processedRecords = 0;
            this.totalRecords = 0;
            this.importErrors = [];

            try {
                const formData = new FormData();
                formData.append("file", this.selectedFile);

                // Start file upload with progress tracking
                const response = await axios.post(
                    "/api/import-phieu-giao-nhan",
                    formData,
                    {
                        headers: {
                            "Content-Type": "multipart/form-data",
                            Authorization: "Bearer " + this.store.getToken,
                        },
                        onUploadProgress: (progressEvent) => {
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
                    this.processingRecords = true;
                    this.totalRecords = response.data.totalRecords || 0;

                    // Start tracking processing status
                    this.checkImportProgress(response.data.importId);
                } else {
                    this.isImporting = false;
                    this.importErrors = response.data.errors || [
                        "Đã xảy ra lỗi không xác định trong quá trình nhập.",
                    ];
                    console.error("Import failed:", response.data);

                    // Use SweetAlert2 to show failure
                    Swal.fire({
                        title: "Thất bại",
                        text: "Nhập dữ liệu không thành công. Vui lòng kiểm tra lỗi và thử lại.",
                        icon: "error",
                        confirmButtonText: "OK",
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: "btn btn-success",
                        },
                    });
                }
            } catch (error) {
                this.isImporting = false;
                console.error("Import error:", error);

                if (error.response) {
                    if (error.response.status === 401) {
                        this.handleAuthError();
                    } else {
                        this.importErrors =
                            error.response.data.errors ||
                            Array.isArray(error.response.data.message)
                                ? error.response.data.message
                                : [
                                      error.response.data.message ||
                                          "Lỗi máy chủ",
                                  ];
                    }
                } else if (error.request) {
                    // Request made but no response received
                    this.importErrors = [
                        "Không có phản hồi từ máy chủ. Vui lòng kiểm tra kết nối mạng và thử lại.",
                    ];
                } else {
                    // Error setting up the request
                    this.importErrors = [
                        "Lỗi mạng. Vui lòng kiểm tra kết nối của bạn và thử lại: " +
                            error.message,
                    ];
                }

                // Use SweetAlert2 to show error
                Swal.fire({
                    title: "Lỗi",
                    text: "Lỗi khi nhập dữ liệu. Vui lòng kiểm tra chi tiết lỗi hiển thị bên dưới.",
                    icon: "error",
                    confirmButtonText: "OK",
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: "btn btn-success",
                    },
                });
            }
        },

        async checkImportProgress(importId) {
            if (!importId) {
                this.importErrors = ["Invalid import ID. Please try again."];
                return;
            }

            try {
                const response = await axios.get(
                    `/api/import-homgiong-progress/${importId}`,
                    {
                        headers: {
                            Authorization: "Bearer " + this.store.getToken,
                        },
                    }
                );

                const data = response.data;

                if (data.finished) {
                    this.processedRecords = data.processed || 0;
                    this.totalRecords = data.total || 0;
                    this.processingProgress = 100;

                    if (data.success) {
                        // Import completed successfully
                        setTimeout(() => {
                            this.isImporting = false;
                            this.closeImportModal();

                            // Show success toast notification with SweetAlert2
                            Swal.fire({
                                title: "Thành công",
                                text:
                                    `Đã tải lên thành công ${this.processedRecords} bản ghi` +
                                    (this.importErrors.length > 0
                                        ? ` với ${this.importErrors.length} lỗi`
                                        : ""),
                                icon: "success",
                                toast: true,
                                position: "top-end",
                                showConfirmButton: false,
                                timer: 5000,
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

                            // Refresh data to get the latest changes
                            this.fetchBienBanData();
                        }, 1000);
                    } else {
                        // Import failed
                        this.isImporting = false;

                        // Make sure errors are properly presented
                        if (data.errors && data.errors.length > 0) {
                            this.importErrors = data.errors;
                        } else {
                            this.importErrors = [
                                "Đã xảy ra lỗi không xác định trong quá trình xử lý.",
                            ];
                        }

                        console.error("Import failed:", data);

                        // Show error notification with SweetAlert2
                        Swal.fire({
                            title: "Thất bại",
                            text: "Tải lên không thành công. Vui lòng kiểm tra thông báo lỗi bên dưới.",
                            icon: "error",
                            confirmButtonText: "OK",
                            buttonsStyling: false,
                            customClass: {
                                confirmButton: "btn btn-success",
                            },
                        });
                    }
                } else {
                    // Still processing, update progress
                    this.processedRecords = data.processed || 0;
                    this.totalRecords = data.total || this.totalRecords || 1;

                    // Avoid division by zero
                    if (this.totalRecords > 0) {
                        this.processingProgress = Math.min(
                            99, // Cap at 99% until finished
                            Math.round(
                                (this.processedRecords * 100) /
                                    this.totalRecords
                            )
                        );
                    } else {
                        this.processingProgress = 50; // Show 50% if total is unknown
                    }

                    // Update import errors if any are available during processing
                    if (data.errors && data.errors.length > 0) {
                        this.importErrors = data.errors;
                    }

                    // Check again after a delay
                    setTimeout(() => this.checkImportProgress(importId), 1000);
                }
            } catch (error) {
                console.error("Error checking import progress:", error);

                if (error.response && error.response.status === 401) {
                    this.handleAuthError();
                    return;
                }

                this.isImporting = false;
                this.importErrors = [
                    "Lỗi khi theo dõi tiến trình tải lên. Quá trình tải lên có thể vẫn đang được xử lý trong nền.",
                    "Vui lòng làm mới trang sau vài phút để xem dữ liệu đã được tải lên hay chưa.",
                ];

                if (error.response && error.response.data) {
                    if (typeof error.response.data === "string") {
                        this.importErrors.push(error.response.data);
                    } else if (error.response.data.message) {
                        this.importErrors.push(error.response.data.message);
                    }
                }
            }
        },
    },
    watch: {
        search() {
            this.currentPage = 1;
        },
        statusFilter() {
            this.currentPage = 1;
        },
    },
    mounted() {
        this.fetchPhieuData();
        // After fetching data, initialize the unique values
        this.updateUniqueValues("vu_dau_tu");
        this.updateUniqueValues("tinh_trang_giao_nhan_ho_so");

        window.addEventListener("resize", () => {
            this.isMobile = window.innerWidth < 768;
        });
    },
    beforeUnmount() {
        window.removeEventListener("resize", () => {
            this.isMobile = window.innerWidth < 768;
        });
    },
};
</script>

<style scoped>
/* Table & Pagination Styling */
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
}
.card:hover {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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
    text-align: left;
    white-space: nowrap;
    overflow: visible;
    text-overflow: clip;
    word-wrap: break-word;
    font-size: 0.875rem;
}
.table-auto th {
    background-color: #e7e7e7;
    border: 1px solid #e5e7eb;
    padding: 0.75rem;
    vertical-align: top;
}
.pagination-card {
    padding: 0.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border-radius: 0.5rem;
    background-color: #fff;
    max-height: 50px;
    display: flex;
    justify-content: center;
}

.desktop-row.selected {
    background-color: #e6f4ea;
}

.form-checkbox {
    cursor: pointer;
    width: 1rem;
    height: 1rem;
    border-radius: 0.25rem;
    border: 1px solid #d1d5db;
    transition: all 0.2s ease;
}

.form-checkbox:checked {
    background-color: #10b981;
    border-color: #10b981;
}

.form-checkbox:hover {
    border-color: #10b981;
}

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

.status-option {
    padding: 0.5rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.search-input {
    min-width: 250px;
}

/* Mobile responsive styles */
@media (max-width: 768px) {
    .status-filter {
        min-width: 100%;
        margin-bottom: 1rem;
    }

    .search-input {
        min-width: 100%;
    }
}

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
    z-index: 100;
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

/* Add new styles for filters */
th {
    position: relative;
}

.table-auto th {
    min-width: 150px;
    white-space: nowrap;
}

.desktop-row {
    cursor: pointer;
    transition: all 0.2s ease;
}

.desktop-row:hover {
    background-color: #f0fff4;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transform: translateY(-1px);
}

/* Improve table appearance */
.table-auto {
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
    border-radius: 0.5rem;
    overflow: hidden;
}

.table-auto th:first-child {
    border-top-left-radius: 0.5rem;
}

.table-auto th:last-child {
    border-top-right-radius: 0.5rem;
}

.table-auto tr:last-child td:first-child {
    border-bottom-left-radius: 0.5rem;
}

.table-auto tr:last-child td:last-child {
    border-bottom-right-radius: 0.5rem;
}

/* Filter icon styling */
.fa-filter {
    font-size: 0.75rem;
}

/* Filter dropdown positioning */
.relative {
    position: relative;
}

/* Checkbox styling */
input[type="checkbox"] {
    cursor: pointer;
}

/* Improve active filter visibility */
.text-green-500 {
    color: #10b981;
}

/* Improve dropdown shadow */
.shadow-lg {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1),
        0 4px 6px -2px rgba(0, 0, 0, 0.05);
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

/* Improved Pagination Styling */
.pagination {
    display: flex;
    padding-left: 0;
    list-style: none;
    border-radius: 0.25rem;
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

.table-auto td {
    padding: 0.75rem;
    border: 1px solid #e5e7eb;
    vertical-align: middle;
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

/* Table rows clickable indication */
.table-auto tbody tr {
    cursor: pointer;
}
</style>
