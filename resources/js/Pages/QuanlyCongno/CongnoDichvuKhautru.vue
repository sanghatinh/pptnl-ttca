<template>
    <!-- Loading overlay -->
    <div id="loading-wrapper" v-if="isLoading">
        <div class="spinner-border" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>

    <div class="container-fluid mx-auto p-2">
        <!-- Top toolbar with search and actions -->
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
                <!-- Actions dropdown menu -->
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
                            <li>
                                <a
                                    class="dropdown-item"
                                    href="#"
                                    @click.prevent="downloadImportTemplate"
                                >
                                    <i
                                        class="fas fa-file-download text-info me-2"
                                    ></i>
                                    Download Import Template
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
                    placeholder="Tìm kiếm công nợ..."
                    class="search-input px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500"
                />
            </div>
        </div>

        <!-- Add this right after the top toolbar with search and actions -->
        <div class="row mb-0" v-if="!isLoading && totals">
            <div class="col-12">
                <div class="totals-container">
                    <div class="total-card total-debt">
                        <div class="total-icon">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <div class="total-content">
                            <div class="total-label">Tổng nợ gốc</div>
                            <div class="total-value">
                                {{ formatCurrency(totals.total_debt) }}
                            </div>
                        </div>
                    </div>

                    <div class="total-card total-paid">
                        <div class="total-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="total-content">
                            <div class="total-label">Đã trả gốc</div>
                            <div class="total-value">
                                {{ formatCurrency(totals.total_paid) }}
                            </div>
                        </div>
                    </div>

                    <div class="total-card total-remaining">
                        <div class="total-icon">
                            <i class="fas fa-funnel-dollar"></i>
                        </div>
                        <div class="total-content">
                            <div class="total-label">Còn lại</div>
                            <div class="total-value">
                                {{ formatCurrency(totals.total_remaining) }}
                            </div>
                        </div>
                    </div>

                    <div class="total-card total-interest">
                        <div class="total-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="total-content">
                            <div class="total-label">Tổng lãi</div>
                            <div class="total-value">
                                {{ formatCurrency(totals.total_interest) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Controls -->
        <div
            class="mobile-controls p-3 bg-white rounded-lg shadow-sm mb-3"
            v-if="isMobile"
        >
            <div class="flex flex-col gap-3">
                <!-- Search bar with icon -->
                <div class="search-container relative">
                    <span
                        class="search-icon absolute left-3 top-1/2 transform -translate-y-1/2"
                    >
                        <i class="fas fa-search text-gray-400"></i>
                    </span>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Tìm kiếm công nợ..."
                        class="w-full pl-10 pr-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm"
                    />
                </div>

                <!-- Filters in a row with 50/50 split -->
                <div class="filters-row flex gap-3">
                    <!-- Loại đầu tư - 50% width -->
                    <div class="flex-1">
                        <label
                            class="form-label text-sm font-medium text-gray-700 mb-1"
                            >Loại đầu tư</label
                        >
                        <select
                            v-model="statusFilter"
                            class="form-select status-select w-full py-2.5 rounded-lg border border-gray-300 focus:border-green-500 focus:ring focus:ring-green-200"
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

                    <!-- Vụ đầu tư - 50% width -->
                    <div class="flex-1">
                        <label
                            class="form-label text-sm font-medium text-gray-700 mb-1"
                            >Vụ đầu tư</label
                        >
                        <select
                            v-model="selectedFilterValues.vu_dau_tu"
                            class="form-select w-full py-2.5 rounded-lg border border-gray-300 focus:border-green-500 focus:ring focus:ring-green-200"
                        >
                            <option value="">Tất cả</option>
                            <option
                                v-for="option in uniqueValues.vu_dau_tu"
                                :key="option"
                                :value="option"
                            >
                                {{ option }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Actions row -->
                <!-- Actions row -->
                <div class="actions-row mt-2 flex justify-between">
                    <!-- Total records count -->
                    <div
                        class="records-count px-3 py-2 bg-gray-100 rounded-md text-sm text-gray-700"
                    >
                        <i class="fas fa-list me-1"></i>
                        {{ paginatedItems.total || 0 }} kết quả
                    </div>

                    <!-- Actions button with reset filter button -->
                    <div class="flex gap-2">
                        <span
                            class="reset-all-filters-btn"
                            title="Reset all filters"
                            @click="resetAllFilters"
                        >
                            <i class="fas fa-redo-alt"></i>
                        </span>
                        <button
                            class="btn-actions px-3 py-2 bg-green-50 text-green-700 rounded-md flex items-center gap-2 hover:bg-green-100 transition-colors"
                            @click="showActionsMenu"
                        >
                            <i class="fas fa-ellipsis-v"></i>
                            <span>Tùy chọn</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main data table -->
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
                                        <!-- Trạm -->
                                        <th>
                                            Trạm
                                            <button
                                                @click="toggleFilter('tram')"
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            selectedFilterValues.tram &&
                                                            selectedFilterValues
                                                                .tram.length >
                                                                0,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="activeFilter === 'tram'"
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <!-- Debug info -->
                                                <div
                                                    class="text-xs text-gray-500 mb-2"
                                                >
                                                    {{
                                                        uniqueValues.tram
                                                            ? uniqueValues.tram
                                                                  .length
                                                            : 0
                                                    }}
                                                    options found
                                                </div>

                                                <div
                                                    class="max-h-40 overflow-y-auto"
                                                >
                                                    <template
                                                        v-if="
                                                            uniqueValues.tram &&
                                                            uniqueValues.tram
                                                                .length > 0
                                                        "
                                                    >
                                                        <div
                                                            v-for="(
                                                                option, index
                                                            ) in uniqueValues.tram"
                                                            :key="index"
                                                            class="flex items-center mb-2"
                                                        >
                                                            <input
                                                                type="checkbox"
                                                                :id="`tram-${index}`"
                                                                :value="option"
                                                                v-model="
                                                                    selectedFilterValues.tram
                                                                "
                                                                class="form-checkbox h-4 w-4 text-green-600"
                                                            />
                                                            <label
                                                                :for="`tram-${index}`"
                                                                class="ml-2 text-gray-700"
                                                            >
                                                                {{ option }}
                                                            </label>
                                                        </div>
                                                    </template>
                                                    <div
                                                        v-else
                                                        class="text-center py-2 text-gray-500"
                                                    >
                                                        No options available
                                                    </div>
                                                </div>

                                                <div
                                                    class="flex justify-between mt-2"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter('tram')
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter('tram')
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>
                                        <!-- Invoice Number -->
                                        <th>
                                            Invoice Number
                                            <button
                                                @click="
                                                    toggleFilter(
                                                        'invoicenumber'
                                                    )
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.invoicenumber,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'invoicenumber'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.invoicenumber
                                                    "
                                                    class="form-control mb-2"
                                                    placeholder="Lọc theo invoice..."
                                                />
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'invoicenumber'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'invoicenumber'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Vụ đầu tư -->
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
                                                    class="max-h-40 overflow-y-auto"
                                                >
                                                    <div
                                                        v-for="(
                                                            option, index
                                                        ) in uniqueValues.vu_dau_tu"
                                                        :key="index"
                                                        class="flex items-center mb-2"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            :id="`vu_dau_tu-${index}`"
                                                            :value="option"
                                                            v-model="
                                                                selectedFilterValues.vu_dau_tu
                                                            "
                                                            class="form-checkbox h-4 w-4 text-green-600"
                                                        />
                                                        <label
                                                            :for="`vu_dau_tu-${index}`"
                                                            class="ml-2 text-gray-700"
                                                        >
                                                            {{ option }}
                                                        </label>
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

                                        <!-- Category Debt -->
                                        <th>
                                            Category Debt
                                            <button
                                                @click="
                                                    toggleFilter(
                                                        'category_debt'
                                                    )
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            selectedFilterValues.category_debt &&
                                                            selectedFilterValues
                                                                .category_debt
                                                                .length > 0,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'category_debt'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <div
                                                    class="max-h-40 overflow-y-auto"
                                                >
                                                    <div
                                                        v-for="(
                                                            option, index
                                                        ) in uniqueValues.category_debt"
                                                        :key="index"
                                                        class="flex items-center mb-2"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            :id="`category_debt-${index}`"
                                                            :value="option"
                                                            v-model="
                                                                selectedFilterValues.category_debt
                                                            "
                                                            class="form-checkbox h-4 w-4 text-green-600"
                                                        />
                                                        <label
                                                            :for="`category_debt-${index}`"
                                                            class="ml-2 text-gray-700"
                                                        >
                                                            {{ option }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'category_debt'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'category_debt'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Description -->
                                        <th>
                                            Description
                                            <button
                                                @click="
                                                    toggleFilter('description')
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.description,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'description'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.description
                                                    "
                                                    class="form-control mb-2"
                                                    placeholder="Lọc theo mô tả..."
                                                />
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'description'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'description'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Ngày phát sinh -->
                                        <th>
                                            Ngày phát sinh
                                            <button
                                                @click="
                                                    toggleFilter(
                                                        'ngay_phat_sinh'
                                                    )
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.ngay_phat_sinh,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'ngay_phat_sinh'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="date"
                                                    v-model="
                                                        columnFilters.ngay_phat_sinh
                                                    "
                                                    class="form-control mb-2"
                                                />
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'ngay_phat_sinh'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'ngay_phat_sinh'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Loại tiền -->
                                        <th>
                                            Loại tiền
                                            <button
                                                @click="
                                                    toggleFilter('loai_tien')
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            selectedFilterValues.loai_tien &&
                                                            selectedFilterValues
                                                                .loai_tien
                                                                .length > 0,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter === 'loai_tien'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <div
                                                    class="max-h-40 overflow-y-auto"
                                                >
                                                    <div
                                                        v-for="(
                                                            option, index
                                                        ) in uniqueValues.loai_tien"
                                                        :key="index"
                                                        class="flex items-center mb-2"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            :id="`loai_tien-${index}`"
                                                            :value="option"
                                                            v-model="
                                                                selectedFilterValues.loai_tien
                                                            "
                                                            class="form-checkbox h-4 w-4 text-green-600"
                                                        />
                                                        <label
                                                            :for="`loai_tien-${index}`"
                                                            class="ml-2 text-gray-700"
                                                        >
                                                            {{ option }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'loai_tien'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'loai_tien'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Tỷ giá quy đổi -->
                                        <th>
                                            Tỷ giá quy đổi
                                            <button
                                                @click="
                                                    toggleFilter(
                                                        'ty_gia_quy_doi'
                                                    )
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.ty_gia_quy_doi,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'ty_gia_quy_doi'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.ty_gia_quy_doi
                                                    "
                                                    class="form-control mb-2"
                                                    placeholder="Lọc theo tỷ giá..."
                                                />
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'ty_gia_quy_doi'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'ty_gia_quy_doi'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Số tiền theo giá trị đầu tư -->
                                        <th>
                                            Số tiền theo giá trị đầu tư
                                            <button
                                                @click="
                                                    toggleFilter(
                                                        'so_tien_theo_gia_tri_dau_tu'
                                                    )
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.so_tien_theo_gia_tri_dau_tu,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'so_tien_theo_gia_tri_dau_tu'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.so_tien_theo_gia_tri_dau_tu
                                                    "
                                                    class="form-control mb-2"
                                                    placeholder="Lọc theo số tiền..."
                                                />
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'so_tien_theo_gia_tri_dau_tu'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'so_tien_theo_gia_tri_dau_tu'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Số tiền nợ gốc đã quy -->
                                        <th>
                                            Số tiền nợ gốc đã quy
                                            <button
                                                @click="
                                                    toggleFilter(
                                                        'so_tien_no_goc_da_quy'
                                                    )
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.so_tien_no_goc_da_quy,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'so_tien_no_goc_da_quy'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.so_tien_no_goc_da_quy
                                                    "
                                                    class="form-control mb-2"
                                                    placeholder="Lọc theo số tiền..."
                                                />
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'so_tien_no_goc_da_quy'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'so_tien_no_goc_da_quy'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Đã trả gốc -->
                                        <th>
                                            Đã trả gốc
                                            <button
                                                @click="
                                                    toggleFilter('da_tra_goc')
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.da_tra_goc,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'da_tra_goc'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.da_tra_goc
                                                    "
                                                    class="form-control mb-2"
                                                    placeholder="Lọc theo số tiền..."
                                                />
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'da_tra_goc'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'da_tra_goc'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Số tiền còn lại -->
                                        <th>
                                            Số tiền còn lại
                                            <button
                                                @click="
                                                    toggleFilter(
                                                        'so_tien_con_lai'
                                                    )
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.so_tien_con_lai,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'so_tien_con_lai'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.so_tien_con_lai
                                                    "
                                                    class="form-control mb-2"
                                                    placeholder="Lọc theo số tiền..."
                                                />
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'so_tien_con_lai'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'so_tien_con_lai'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Tiền lãi -->
                                        <th>
                                            Tiền lãi
                                            <button
                                                @click="
                                                    toggleFilter('tien_lai')
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.tien_lai,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter === 'tien_lai'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.tien_lai
                                                    "
                                                    class="form-control mb-2"
                                                    placeholder="Lọc theo tiền lãi..."
                                                />
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'tien_lai'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'tien_lai'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Mã khách hàng cá nhân -->
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
                                                    placeholder="Lọc theo mã KH..."
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

                                        <!-- Khách hàng cá nhân -->
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
                                                    placeholder="Lọc theo tên khách hàng..."
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

                                        <!-- Mã khách hàng doanh nghiệp -->
                                        <th>
                                            Mã KH DN
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
                                                    placeholder="Lọc theo mã DN..."
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

                                        <!-- Khách hàng doanh nghiệp -->
                                        <th>
                                            KH doanh nghiệp
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
                                                    placeholder="Lọc theo tên doanh nghiệp..."
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

                                        <!-- Lãi suất -->
                                        <th>
                                            Lãi suất
                                            <button
                                                @click="
                                                    toggleFilter('lai_suat')
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.lai_suat,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter === 'lai_suat'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.lai_suat
                                                    "
                                                    class="form-control mb-2"
                                                    placeholder="Lọc theo lãi suất..."
                                                />
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'lai_suat'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'lai_suat'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Loại lãi suất -->
                                        <th>
                                            Loại lãi suất
                                            <button
                                                @click="
                                                    toggleFilter(
                                                        'loai_lai_suat'
                                                    )
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            selectedFilterValues.loai_lai_suat &&
                                                            selectedFilterValues
                                                                .loai_lai_suat
                                                                .length > 0,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'loai_lai_suat'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <div
                                                    class="max-h-40 overflow-y-auto"
                                                >
                                                    <div
                                                        v-for="(
                                                            option, index
                                                        ) in uniqueValues.loai_lai_suat"
                                                        :key="index"
                                                        class="flex items-center mb-2"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            :id="`loai_lai_suat-${index}`"
                                                            :value="option"
                                                            v-model="
                                                                selectedFilterValues.loai_lai_suat
                                                            "
                                                            class="form-checkbox h-4 w-4 text-green-600"
                                                        />
                                                        <label
                                                            :for="`loai_lai_suat-${index}`"
                                                            class="ml-2 text-gray-700"
                                                        >
                                                            {{ option }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'loai_lai_suat'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'loai_lai_suat'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Vụ thanh toán -->
                                        <th>
                                            Vụ thanh toán
                                            <button
                                                @click="
                                                    toggleFilter(
                                                        'vu_thanh_toan'
                                                    )
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            selectedFilterValues.vu_thanh_toan &&
                                                            selectedFilterValues
                                                                .vu_thanh_toan
                                                                .length > 0,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'vu_thanh_toan'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <div
                                                    class="max-h-40 overflow-y-auto"
                                                >
                                                    <div
                                                        v-for="(
                                                            option, index
                                                        ) in uniqueValues.vu_thanh_toan"
                                                        :key="index"
                                                        class="flex items-center mb-2"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            :id="`vu_thanh_toan-${index}`"
                                                            :value="option"
                                                            v-model="
                                                                selectedFilterValues.vu_thanh_toan
                                                            "
                                                            class="form-checkbox h-4 w-4 text-green-600"
                                                        />
                                                        <label
                                                            :for="`vu_thanh_toan-${index}`"
                                                            class="ml-2 text-gray-700"
                                                        >
                                                            {{ option }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'vu_thanh_toan'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'vu_thanh_toan'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Loại đầu tư -->
                                        <th>
                                            Loại đầu tư
                                            <button
                                                @click="
                                                    toggleFilter('loai_dau_tu')
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            selectedFilterValues.loai_dau_tu &&
                                                            selectedFilterValues
                                                                .loai_dau_tu
                                                                .length > 0,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'loai_dau_tu'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <div
                                                    class="max-h-40 overflow-y-auto"
                                                >
                                                    <div
                                                        v-for="(
                                                            option, index
                                                        ) in uniqueValues.loai_dau_tu"
                                                        :key="index"
                                                        class="flex items-center mb-2"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            :id="`loai_dau_tu-${index}`"
                                                            :value="option"
                                                            v-model="
                                                                selectedFilterValues.loai_dau_tu
                                                            "
                                                            class="form-checkbox h-4 w-4 text-green-600"
                                                        />
                                                        <label
                                                            :for="`loai_dau_tu-${index}`"
                                                            class="ml-2 text-gray-700"
                                                        >
                                                            {{ option }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'loai_dau_tu'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'loai_dau_tu'
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
                                        class="desktop-row cursor-pointer"
                                    >
                                        <td>{{ item.tram }}</td>
                                        <td>{{ item.invoicenumber }}</td>
                                        <td>{{ item.vu_dau_tu }}</td>
                                        <td>
                                            <span
                                                :class="
                                                    getCategoryBadgeClass(
                                                        item.category_debt
                                                    )
                                                "
                                            >
                                                {{ item.category_debt }}
                                            </span>
                                        </td>
                                        <td>{{ item.description }}</td>
                                        <td>
                                            {{
                                                formatDate(item.ngay_phat_sinh)
                                            }}
                                        </td>
                                        <td>{{ item.loai_tien }}</td>
                                        <td>
                                            {{
                                                formatCurrency(
                                                    item.ty_gia_quy_doi
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                formatCurrency(
                                                    item.so_tien_theo_gia_tri_dau_tu,
                                                    false
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                formatCurrency(
                                                    item.so_tien_no_goc_da_quy
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                formatCurrency(item.da_tra_goc)
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                formatCurrency(
                                                    item.so_tien_con_lai
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{ formatCurrency(item.tien_lai) }}
                                        </td>
                                        <td>
                                            {{ item.ma_khach_hang_ca_nhan }}
                                        </td>
                                        <td>{{ item.khach_hang_ca_nhan }}</td>
                                        <td>
                                            {{
                                                item.ma_khach_hang_doanh_nghiep
                                            }}
                                        </td>
                                        <td>
                                            {{ item.khach_hang_doanh_nghiep }}
                                        </td>
                                        <td>{{ item.lai_suat }}%</td>
                                        <td>{{ item.loai_lai_suat }}</td>
                                        <td>{{ item.vu_thanh_toan }}</td>
                                        <td>{{ item.loai_dau_tu }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="flex justify-center mt-0 pagination-wrapper">
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
                class="card border-0 p-0 mb-4 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300"
                @click="viewDetails(item)"
            >
                <!-- Card header -->
                <div
                    class="card-header p-3 bg-green-50 flex justify-between items-center rounded-t-lg border-b border-gray-100"
                >
                    <div class="flex items-center">
                        <div class="badge-container mr-2">
                            <span
                                class="badge bg-green-500 text-green-800 text-xs px-2 py-1 rounded"
                            >
                                {{ item.category_debt || "N/A" }}
                            </span>
                        </div>
                        <h5 class="text-green-900 font-medium mb-0 text-sm">
                            {{ item.tram || "Không xác định" }}
                        </h5>
                    </div>
                    <span class="text-xs text-gray-500">{{
                        formatDate(item.ngay_phat_sinh)
                    }}</span>
                </div>

                <!-- Card body -->
                <div class="card-body p-3">
                    <!-- Invoice and description -->
                    <div class="mb-3">
                        <p class="font-medium mb-1">
                            {{ item.invoicenumber || "Không có mã" }}
                        </p>
                        <p class="text-sm text-gray-600 line-clamp-2 mb-0">
                            {{ item.description || "Không có mô tả" }}
                        </p>
                    </div>

                    <!-- Customer info -->
                    <div class="customer-info bg-gray-50 p-2 rounded-md mb-3">
                        <p class="text-sm mb-1">
                            <i class="fas fa-user-tie text-gray-500 mr-2"></i>
                            {{
                                item.khach_hang_ca_nhan ||
                                item.khach_hang_doanh_nghiep ||
                                "Không có thông tin"
                            }}
                        </p>
                        <p class="text-xs text-gray-500 mb-0">
                            <i class="fas fa-tag mr-1"></i>
                            Vụ: {{ item.vu_dau_tu || "N/A" }}
                            <span class="mx-1">•</span>
                            {{ item.loai_tien || "" }}
                        </p>
                    </div>

                    <!-- Financial details grid -->
                    <div class="grid grid-cols-2 gap-2 mb-1">
                        <div class="bg-blue-50 p-2 rounded">
                            <p class="text-xs text-blue-700 mb-0">Nợ gốc</p>
                            <p class="text-sm font-medium mb-0">
                                {{
                                    formatCurrency(
                                        item.so_tien_no_goc_da_quy,
                                        false
                                    )
                                }}
                            </p>
                        </div>
                        <div class="bg-green-50 p-2 rounded">
                            <p class="text-xs text-green-700 mb-0">
                                Đã trả gốc
                            </p>
                            <p class="text-sm font-medium mb-0">
                                {{ formatCurrency(item.da_tra_goc) }}
                            </p>
                        </div>
                        <div class="bg-red-50 p-2 rounded">
                            <p class="text-xs text-red-700 mb-0">Còn lại</p>
                            <p class="text-sm font-medium mb-0">
                                {{ formatCurrency(item.so_tien_con_lai) }}
                            </p>
                        </div>
                        <div class="bg-amber-50 p-2 rounded">
                            <p class="text-xs text-amber-700 mb-0">Tiền lãi</p>
                            <p class="text-sm font-medium mb-0">
                                {{ formatCurrency(item.tien_lai) }}
                            </p>
                        </div>
                    </div>

                    <!-- Interest rate info -->
                    <div
                        class="interest-info flex justify-between text-xs text-gray-500 mt-2"
                    >
                        <span>
                            <i class="fas fa-percentage mr-1"></i>
                            {{ item.lai_suat }}% ({{
                                item.loai_lai_suat || "N/A"
                            }})
                        </span>
                        <span class="card-btn">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Pagination for mobile -->
            <div class="flex justify-center mt-0">
                <div class="pagination-card">
                    <Bootstrap5Pagination
                        :data="paginatedItems"
                        @pagination-change-page="pageChanged"
                        :limit="3"
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
                        <button
                            class="btn btn-outline-secondary mb-3 w-100"
                            @click="downloadImportTemplate"
                        >
                            <i class="fas fa-download me-2"></i> Tải mẫu nhập
                            liệu
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
            congnoList: [],
            allCongnoList: [],
            dataInitialized: false,

            filterDebounce: null,
            paginatedItems: {
                data: [],
                current_page: 1,
                last_page: 1,
                per_page: 50,
                total: 0,
                from: 1,
                to: 0,
            },
            searchDebounce: null,
            search: "",
            statusFilter: "all",
            isMobile: window.innerWidth < 768,
            currentPage: 1,
            perPage: 50,
            sortField: "ngay_phat_sinh",
            sortOrder: "desc",
            ps: null,
            totals: {
                total_debt: 0,
                total_paid: 0,
                total_remaining: 0,
                total_interest: 0,
            },
            dateRange: {
                start: null,
                end: null,
            },
            statusOptions: [{ code: "all", name: "Tất cả" }],
            paginationClasses: {
                ul: "flex list-none pagination",
                li: "page-item mx-1",
                a: "page-link px-3 py-2 bg-white border border-gray-300 rounded hover:bg-gray-100",
                active: "bg-green-500 text-white active",
                disabled: "opacity-50 cursor-not-allowed disabled",
            },
            columnFilters: {
                tram: "",
                invoicenumber: "",
                vu_dau_tu: "",
                category_debt: "",
                description: "",
                ngay_phat_sinh: "",
                loai_tien: "",
                ty_gia_quy_doi: "",
                so_tien_theo_gia_tri_dau_tu: "",
                so_tien_no_goc_da_quy: "",
                da_tra_goc: "",
                so_tien_con_lai: "",
                tien_lai: "",
                ma_khach_hang_ca_nhan: "",
                khach_hang_ca_nhan: "",
                ma_khach_hang_doanh_nghiep: "",
                khach_hang_doanh_nghiep: "",
                lai_suat: "",
                loai_lai_suat: "",
                vu_thanh_toan: "",
                loai_dau_tu: "",
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
            exportModal: null,
            importModal: null,
            uniqueValues: {
                tram: [],
                vu_dau_tu: [],
                category_debt: [],
                loai_tien: [],
                loai_lai_suat: [],
                vu_thanh_toan: [],
                loai_dau_tu: [],
            },
            selectedFilterValues: {
                tram: [],
                vu_dau_tu: [],
                category_debt: [],
                loai_tien: [],
                loai_lai_suat: [],
                vu_thanh_toan: [],
                loai_dau_tu: [],
            },
        };
    },
    computed: {
        shouldShowFilter() {
            return (field) => {
                return (
                    this.dataInitialized &&
                    this.uniqueValues[field] &&
                    this.uniqueValues[field].length > 0
                );
            };
        },
        filteredItems() {
            return this.congnoList.filter((item) => {
                // Apply global search
                const matchesSearch =
                    (item.tram &&
                        item.tram
                            .toLowerCase()
                            .includes(this.search.toLowerCase())) ||
                    (item.invoicenumber &&
                        item.invoicenumber
                            .toLowerCase()
                            .includes(this.search.toLowerCase())) ||
                    (item.description &&
                        item.description
                            .toLowerCase()
                            .includes(this.search.toLowerCase())) ||
                    (item.khach_hang_ca_nhan &&
                        item.khach_hang_ca_nhan
                            .toLowerCase()
                            .includes(this.search.toLowerCase())) ||
                    (item.khach_hang_doanh_nghiep &&
                        item.khach_hang_doanh_nghiep
                            .toLowerCase()
                            .includes(this.search.toLowerCase()));

                // Apply status filter
                let matchesStatus = true;
                if (this.statusFilter !== "all") {
                    matchesStatus = item.status === this.statusFilter;
                }

                // Apply column filters
                const matchesColumnFilters =
                    // Trạm
                    (!this.columnFilters.tram ||
                        (item.tram &&
                            item.tram
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.tram.toLowerCase()
                                ))) &&
                    // Invoice Number
                    (!this.columnFilters.invoicenumber ||
                        (item.invoicenumber &&
                            item.invoicenumber
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.invoicenumber.toLowerCase()
                                ))) &&
                    // Description
                    (!this.columnFilters.description ||
                        (item.description &&
                            item.description
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.description.toLowerCase()
                                ))) &&
                    // Ngày phát sinh
                    (!this.columnFilters.ngay_phat_sinh ||
                        (item.ngay_phat_sinh &&
                            this.formatDateForComparison(
                                item.ngay_phat_sinh
                            ) ===
                                this.formatDateForComparison(
                                    this.columnFilters.ngay_phat_sinh
                                ))) &&
                    // Loại tiền
                    (!this.columnFilters.loai_tien ||
                        (item.loai_tien &&
                            item.loai_tien
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.loai_tien.toLowerCase()
                                ))) &&
                    // Tỷ giá quy đổi
                    (!this.columnFilters.ty_gia_quy_doi ||
                        (item.ty_gia_quy_doi !== undefined &&
                            item.ty_gia_quy_doi
                                .toString()
                                .includes(
                                    this.columnFilters.ty_gia_quy_doi
                                ))) &&
                    // Số tiền theo giá trị đầu tư
                    (!this.columnFilters.so_tien_theo_gia_tri_dau_tu ||
                        (item.so_tien_theo_gia_tri_dau_tu !== undefined &&
                            item.so_tien_theo_gia_tri_dau_tu
                                .toString()
                                .includes(
                                    this.columnFilters
                                        .so_tien_theo_gia_tri_dau_tu
                                ))) &&
                    // Số tiền nợ gốc đã quy
                    (!this.columnFilters.so_tien_no_goc_da_quy ||
                        (item.so_tien_no_goc_da_quy !== undefined &&
                            item.so_tien_no_goc_da_quy
                                .toString()
                                .includes(
                                    this.columnFilters.so_tien_no_goc_da_quy
                                ))) &&
                    // Đã trả gốc
                    (!this.columnFilters.da_tra_goc ||
                        (item.da_tra_goc !== undefined &&
                            item.da_tra_goc
                                .toString()
                                .includes(this.columnFilters.da_tra_goc))) &&
                    // Số tiền còn lại
                    (!this.columnFilters.so_tien_con_lai ||
                        (item.so_tien_con_lai !== undefined &&
                            item.so_tien_con_lai
                                .toString()
                                .includes(
                                    this.columnFilters.so_tien_con_lai
                                ))) &&
                    // Tiền lãi
                    (!this.columnFilters.tien_lai ||
                        (item.tien_lai !== undefined &&
                            item.tien_lai
                                .toString()
                                .includes(this.columnFilters.tien_lai))) &&
                    // Mã khách hàng cá nhân
                    (!this.columnFilters.ma_khach_hang_ca_nhan ||
                        (item.ma_khach_hang_ca_nhan &&
                            item.ma_khach_hang_ca_nhan
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.ma_khach_hang_ca_nhan.toLowerCase()
                                ))) &&
                    // Khách hàng cá nhân
                    (!this.columnFilters.khach_hang_ca_nhan ||
                        (item.khach_hang_ca_nhan &&
                            item.khach_hang_ca_nhan
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.khach_hang_ca_nhan.toLowerCase()
                                ))) &&
                    // Mã khách hàng doanh nghiệp
                    (!this.columnFilters.ma_khach_hang_doanh_nghiep ||
                        (item.ma_khach_hang_doanh_nghiep &&
                            item.ma_khach_hang_doanh_nghiep
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.ma_khach_hang_doanh_nghiep.toLowerCase()
                                ))) &&
                    // Khách hàng doanh nghiệp
                    (!this.columnFilters.khach_hang_doanh_nghiep ||
                        (item.khach_hang_doanh_nghiep &&
                            item.khach_hang_doanh_nghiep
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.khach_hang_doanh_nghiep.toLowerCase()
                                ))) &&
                    // Lãi suất
                    (!this.columnFilters.lai_suat ||
                        (item.lai_suat !== undefined &&
                            item.lai_suat
                                .toString()
                                .includes(this.columnFilters.lai_suat))) &&
                    // Dropdown filters with multiple selections
                    (this.selectedFilterValues.tram.length === 0 ||
                        (item.tram &&
                            this.selectedFilterValues.tram.includes(
                                item.tram
                            ))) &&
                    (this.selectedFilterValues.vu_dau_tu.length === 0 ||
                        (item.vu_dau_tu &&
                            this.selectedFilterValues.vu_dau_tu.includes(
                                item.vu_dau_tu
                            ))) &&
                    (this.selectedFilterValues.category_debt.length === 0 ||
                        (item.category_debt &&
                            this.selectedFilterValues.category_debt.includes(
                                item.category_debt
                            ))) &&
                    (this.selectedFilterValues.loai_tien.length === 0 ||
                        (item.loai_tien &&
                            this.selectedFilterValues.loai_tien.includes(
                                item.loai_tien
                            ))) &&
                    (this.selectedFilterValues.loai_lai_suat.length === 0 ||
                        (item.loai_lai_suat &&
                            this.selectedFilterValues.loai_lai_suat.includes(
                                item.loai_lai_suat
                            ))) &&
                    (this.selectedFilterValues.vu_thanh_toan.length === 0 ||
                        (item.vu_thanh_toan &&
                            this.selectedFilterValues.vu_thanh_toan.includes(
                                item.vu_thanh_toan
                            ))) &&
                    (this.selectedFilterValues.loai_dau_tu.length === 0 ||
                        (item.loai_dau_tu &&
                            this.selectedFilterValues.loai_dau_tu.includes(
                                item.loai_dau_tu
                            )));

                return matchesSearch && matchesStatus && matchesColumnFilters;
            });
        },
    },
    methods: {
        extractSpecificUniqueValues(field) {
            if (!this.allCongnoList || this.allCongnoList.length === 0) {
                console.warn(
                    `Cannot extract ${field} values: no data available`
                );
                return;
            }

            // สกัดค่า unique
            let values = [
                ...new Set(
                    this.allCongnoList
                        .map((item) => item[field])
                        .filter(Boolean) // กรองค่า null/undefined ออก
                ),
            ].sort();

            // ทำให้แน่ใจว่า Vue reactivity จะทำงาน โดยการใช้ Vue.set หรือการกำหนดค่าใหม่ทั้งหมด
            const newUniqueValues = { ...this.uniqueValues };
            newUniqueValues[field] = values;
            this.uniqueValues = newUniqueValues;
        },
        applyFilter(column) {
            try {
                this.activeFilter = null;
                this.currentPage = 1;
                this.applyFiltersAndPagination(1);
            } catch (error) {
                console.error(
                    `Error applying filter for column ${column}:`,
                    error
                );
                // Show user-friendly error message if needed
            }
        },
        // Add this method
        sortTable(field) {
            // If field is already active, toggle sort order
            if (this.sortField === field) {
                this.sortOrder = this.sortOrder === "asc" ? "desc" : "asc";
            } else {
                // Otherwise, set new field and default to ascending
                this.sortField = field;
                this.sortOrder = "asc";
            }

            // Apply client-side sorting
            this.applyFiltersAndPagination(this.currentPage);
        },
        getCategoryBadgeClass(category) {
            if (!category) return "badge bg-secondary text-white";

            // Map different categories to different colors
            const categoryColors = {
                "Ứng dầu": "bg-blue-100 text-blue-800",
                MMTB: "bg-red-100 text-red-800",
                "Sữa chữa": "bg-purple-100 text-purple-800",
                "Ứng tiền trồng": "bg-amber-100 text-amber-800",
                "Ứng tiền chăm sóc": "bg-green-100 text-green-700",
            };

            // Return the corresponding class or a default one
            return categoryColors[category]
                ? `badge ${categoryColors[category]}`
                : "badge bg-gray-100 text-gray-800";
        },
        formatDate(date) {
            if (!date) return "";
            const d = new Date(date);
            const day = d.getDate().toString().padStart(2, "0");
            const month = (d.getMonth() + 1).toString().padStart(2, "0");
            const year = d.getFullYear();
            return `${day}/${month}/${year}`;
        },
        formatDateForComparison(date) {
            if (!date) return "";
            const d = new Date(date);
            const year = d.getFullYear();
            const month = (d.getMonth() + 1).toString().padStart(2, "0");
            const day = d.getDate().toString().padStart(2, "0");
            return `${year}-${month}-${day}`;
        },
        formatCurrency(value, includeCurrency = true) {
            if (!value) return "0";
            return new Intl.NumberFormat("vi-VN", {
                style: includeCurrency ? "currency" : "decimal",
                currency: "KIP",
                maximumFractionDigits: 0,
            }).format(value);
        },
        pageChanged(page) {
            this.currentPage = page;
            this.applyFiltersAndPagination(page);
        },
        async fetchData(page = 1, initialFetch = false) {
            this.isLoading = true;
            try {
                // Only request all data on initial fetch
                const params = initialFetch
                    ? { all: true }
                    : {
                          page,
                          per_page: this.perPage,
                          ...this.getFilterParams(),
                      };

                const response = await axios.get("/api/congno-dichvu-khautru", {
                    params: params,
                    headers: {
                        Authorization: "Bearer " + this.store.getToken,
                    },
                });

                // Process successful response
                const responseData = response.data;

                if (responseData && responseData.status === "success") {
                    if (initialFetch) {
                        // ทำเครื่องหมายว่าข้อมูลถูกโหลดสมบูรณ์แล้ว
                        this.dataInitialized = true;

                        // Check if data is an array directly or nested
                        if (Array.isArray(responseData.data)) {
                            this.allCongnoList = responseData.data;
                        } else if (
                            responseData.data &&
                            Array.isArray(responseData.data.data)
                        ) {
                            this.allCongnoList = responseData.data.data;
                        } else {
                            console.error(
                                "Unexpected data format:",
                                responseData.data
                            );
                            this.allCongnoList = [];

                            Swal.fire({
                                title: "Error",
                                text: "Invalid data format received from server",
                                icon: "error",
                                confirmButtonText: "OK",
                            });
                            return;
                        }

                        // Sample data for verification
                        if (this.allCongnoList.length > 0) {
                        }

                        // Process unique values for filters
                        this.extractUniqueValues();

                        // Calculate totals from all data
                        this.calculateTotals();
                        // After setting uniqueValues, update statusOptions
                        this.updateStatusOptions();

                        // Apply client-side pagination for the first page
                        this.applyFiltersAndPagination(1);
                    } else {
                        // Using backend pagination (only for subsequent requests if needed)
                        this.paginatedItems = responseData.data;
                        this.currentPage = this.paginatedItems.current_page;
                    }
                } else {
                    console.warn(
                        "Unexpected API response format:",
                        responseData
                    );
                    Swal.fire({
                        title: "Warning",
                        text: "Received unexpected data format from server",
                        icon: "warning",
                        confirmButtonText: "OK",
                    });
                }
            } catch (error) {
                console.error("Error fetching data:", error);
                if (error.response?.status === 401) {
                    this.handleAuthError();
                } else {
                    Swal.fire({
                        title: "Lỗi",
                        text:
                            error.response?.data?.message ||
                            "Đã xảy ra lỗi khi tải dữ liệu",
                        icon: "error",
                        confirmButtonText: "OK",
                    });
                }
            } finally {
                this.isLoading = false;
            }
        },

        // Extract unique values from all data for filters
        extractUniqueValues() {
            if (!this.allCongnoList || this.allCongnoList.length === 0) {
                return;
            }

            // Initialize unique value containers
            const filterFields = [
                "tram",
                "vu_dau_tu",
                "category_debt",
                "loai_tien",
                "loai_lai_suat",
                "vu_thanh_toan",
                "loai_dau_tu",
            ];

            const newUniqueValues = {}; // สร้างออบเจ็กต์ใหม่

            filterFields.forEach((field) => {
                // ตรวจสอบว่า field มีอยู่จริงในข้อมูลหรือไม่
                const hasField = this.allCongnoList.some(
                    (item) => item[field] !== undefined
                );

                if (!hasField) {
                    console.warn(`Field ${field} not found in data`);
                    newUniqueValues[field] = [];
                    return;
                }

                newUniqueValues[field] = [
                    ...new Set(
                        this.allCongnoList
                            .map((item) => item[field])
                            .filter(Boolean) // Remove null/undefined values
                    ),
                ].sort(); // Sort values alphabetically
            });

            // ใช้การกำหนดค่าใหม่ทั้งออบเจ็กต์แทนการกำหนดทีละฟิลด์
            this.uniqueValues = newUniqueValues;
        },

        // Calculate totals from all filtered data
        calculateTotals() {
            const filteredData = this.applyFilters();

            this.totals = {
                total_debt: filteredData.reduce(
                    (sum, item) =>
                        sum + (parseFloat(item.so_tien_no_goc_da_quy) || 0),
                    0
                ),
                total_paid: filteredData.reduce(
                    (sum, item) => sum + (parseFloat(item.da_tra_goc) || 0),
                    0
                ),
                total_remaining: filteredData.reduce(
                    (sum, item) =>
                        sum + (parseFloat(item.so_tien_con_lai) || 0),
                    0
                ),
                total_interest: filteredData.reduce(
                    (sum, item) => sum + (parseFloat(item.tien_lai) || 0),
                    0
                ),
            };
        },

        // Apply all filters to the full dataset
        applyFilters() {
            if (!this.allCongnoList || this.allCongnoList.length === 0)
                return [];

            return this.allCongnoList.filter((item) => {
                // Apply global search
                const matchesSearch =
                    !this.search ||
                    (item.tram &&
                        item.tram
                            .toLowerCase()
                            .includes(this.search.toLowerCase())) ||
                    (item.invoicenumber &&
                        item.invoicenumber
                            .toLowerCase()
                            .includes(this.search.toLowerCase())) ||
                    (item.description &&
                        item.description
                            .toLowerCase()
                            .includes(this.search.toLowerCase())) ||
                    (item.khach_hang_ca_nhan &&
                        item.khach_hang_ca_nhan
                            .toLowerCase()
                            .includes(this.search.toLowerCase())) ||
                    (item.khach_hang_doanh_nghiep &&
                        item.khach_hang_doanh_nghiep
                            .toLowerCase()
                            .includes(this.search.toLowerCase()));

                // Apply status filter
                const matchesStatus =
                    this.statusFilter === "all" ||
                    item.status === this.statusFilter;

                // Apply column filters (text inputs)
                const matchesColumnFilters = Object.entries(
                    this.columnFilters
                ).every(([key, value]) => {
                    if (!value) return true;

                    // Date field special handling
                    if (key === "ngay_phat_sinh" && item[key]) {
                        return (
                            this.formatDateForComparison(item[key]) ===
                            this.formatDateForComparison(value)
                        );
                    }

                    // Number fields
                    if (
                        [
                            "ty_gia_quy_doi",
                            "so_tien_theo_gia_tri_dau_tu",
                            "so_tien_no_goc_da_quy",
                            "da_tra_goc",
                            "so_tien_con_lai",
                            "tien_lai",
                            "lai_suat",
                        ].includes(key)
                    ) {
                        return (
                            item[key] !== undefined &&
                            item[key].toString().includes(value)
                        );
                    }

                    // Default text field handling
                    return (
                        item[key] &&
                        item[key]
                            .toString()
                            .toLowerCase()
                            .includes(value.toLowerCase())
                    );
                });

                // Apply dropdown selection filters (checkboxes)
                const matchesDropdownFilters = Object.entries(
                    this.selectedFilterValues
                ).every(([key, values]) => {
                    return (
                        values.length === 0 ||
                        (item[key] && values.includes(item[key]))
                    );
                });

                return (
                    matchesSearch &&
                    matchesStatus &&
                    matchesColumnFilters &&
                    matchesDropdownFilters
                );
            });
        },

        // Handle sorting
        applySorting(data) {
            if (!data || data.length === 0) return [];

            return [...data].sort((a, b) => {
                let valueA = a[this.sortField];
                let valueB = b[this.sortField];

                // Handle numeric fields
                if (
                    [
                        "ty_gia_quy_doi",
                        "so_tien_theo_gia_tri_dau_tu",
                        "so_tien_no_goc_da_quy",
                        "da_tra_goc",
                        "so_tien_con_lai",
                        "tien_lai",
                        "lai_suat",
                    ].includes(this.sortField)
                ) {
                    valueA = parseFloat(valueA) || 0;
                    valueB = parseFloat(valueB) || 0;
                }
                // Handle date fields
                else if (this.sortField === "ngay_phat_sinh") {
                    valueA = new Date(valueA || 0).getTime();
                    valueB = new Date(valueB || 0).getTime();
                }
                // Default string comparison
                else {
                    valueA = valueA ? valueA.toString().toLowerCase() : "";
                    valueB = valueB ? valueB.toString().toLowerCase() : "";
                }

                if (this.sortOrder === "asc") {
                    return valueA > valueB ? 1 : valueA < valueB ? -1 : 0;
                } else {
                    return valueA < valueB ? 1 : valueA > valueB ? -1 : 0;
                }
            });
        },

        // Apply filters, sorting, and pagination
        applyFiltersAndPagination(page = 1) {
            // Apply filters
            const filteredData = this.applyFilters();

            // Apply sorting
            const sortedData = this.applySorting(filteredData);

            // Calculate pagination
            const totalItems = sortedData.length;
            const totalPages = Math.ceil(totalItems / this.perPage);
            const startIndex = (page - 1) * this.perPage;
            const endIndex = Math.min(startIndex + this.perPage, totalItems);

            // Get paginated items
            const items = sortedData.slice(startIndex, endIndex);

            // Update paginated items object
            this.paginatedItems = {
                data: items,
                current_page: page,
                last_page: totalPages,
                per_page: this.perPage,
                total: totalItems,
                from: totalItems ? startIndex + 1 : 0,
                to: endIndex,
            };

            // Update current page
            this.currentPage = page;

            // Update totals based on filtered data
            this.calculateTotals();
        },

        // Add to the methods section
        updateStatusOptions() {
            // Start with the "all" option
            const baseOptions = [{ code: "all", name: "Tất cả" }];

            // Add options from unique category_debt values
            if (
                this.uniqueValues.category_debt &&
                this.uniqueValues.category_debt.length > 0
            ) {
                const categoryOptions = this.uniqueValues.category_debt.map(
                    (category) => ({
                        code: category,
                        name: category,
                    })
                );

                // Update the statusOptions array with all options
                this.statusOptions = [...baseOptions, ...categoryOptions];
            }
        },

        // Helper method to prepare filter parameters
        getFilterParams() {
            const params = {};

            // Add column filters (text-based filters)
            for (const [key, value] of Object.entries(this.columnFilters)) {
                if (value && value.toString().trim() !== "") {
                    params[`filter_${key}`] = value;
                }
            }

            // Add dropdown selection filters (array-based filters)
            for (const [key, values] of Object.entries(
                this.selectedFilterValues
            )) {
                if (values && values.length > 0) {
                    params[`filter_${key}`] = values.join(",");
                }
            }

            // Status filter
            if (this.statusFilter !== "all") {
                params.status = this.statusFilter;
            }

            // Global search
            if (this.search && this.search.trim() !== "") {
                params.search = this.search;
            }

            // Add sorting
            if (this.sortField) {
                params.sort_by = this.sortField;
                params.sort_order = this.sortOrder;
            }

            return params;
        },

        // Format date for API calls
        formatDateForAPI(date) {
            if (!date) return null;
            const d = new Date(date);
            const year = d.getFullYear();
            const month = String(d.getMonth() + 1).padStart(2, "0");
            const day = String(d.getDate()).padStart(2, "0");
            return `${year}-${month}-${day}`;
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
            this.applyFiltersAndPagination(1);
        },
        async viewDetails(item) {
            try {
                // Show loading indicator
                this.isLoading = true;

                // Navigate directly to details page without permission check
                this.$router.push(
                    `/Details_CongnoDichVuKhauTru/${item.invoicenumber}`
                );
            } catch (error) {
                console.error("Error navigating to details:", error);

                // Handle any navigation errors
                Swal.fire({
                    title: "Lỗi",
                    text: "Không thể mở chi tiết công nợ",
                    icon: "error",
                    confirmButtonText: "OK",
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: "btn btn-success",
                    },
                });
            } finally {
                // Hide loading indicator
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
            // ถ้ากำลังเปิดฟิลเตอร์อยู่แล้ว ให้ปิด
            if (this.activeFilter === column) {
                this.activeFilter = null;
                return;
            }

            // ตรวจสอบข้อมูลก่อนเปิดฟิลเตอร์
            if (
                [
                    "tram",
                    "vu_dau_tu",
                    "category_debt",
                    "loai_tien",
                    "loai_lai_suat",
                    "vu_thanh_toan",
                    "loai_dau_tu",
                ].includes(column)
            ) {
                if (
                    !this.uniqueValues[column] ||
                    this.uniqueValues[column].length === 0
                ) {
                    console.warn(
                        `No unique values found for ${column}, re-extracting`
                    );
                    this.extractSpecificUniqueValues(column);
                } else {
                }
            }

            // เปิดฟิลเตอร์
            this.activeFilter = column;
        },
        updateUniqueValues(column) {
            if (
                [
                    "tram",
                    "vu_dau_tu",
                    "category_debt",
                    "loai_tien",
                    "loai_lai_suat",
                    "vu_thanh_toan",
                    "loai_dau_tu",
                ].includes(column)
            ) {
                this.uniqueValues[column] = [
                    ...new Set(
                        this.congnoList
                            .map((item) => item[column])
                            .filter(Boolean) // Remove null/undefined values
                    ),
                ];
            }
        },
        resetFilter(column) {
            if (
                [
                    "tram",
                    "vu_dau_tu",
                    "category_debt",
                    "loai_tien",
                    "loai_lai_suat",
                    "vu_thanh_toan",
                    "loai_dau_tu",
                ].includes(column)
            ) {
                this.selectedFilterValues[column] = [];
            } else {
                this.columnFilters[column] = "";
            }
            this.currentPage = 1;
            this.applyFiltersAndPagination(1);
        },

        applyFilters() {
            if (!this.allCongnoList || this.allCongnoList.length === 0)
                return [];

            return this.allCongnoList.filter((item) => {
                // Apply global search
                const matchesSearch =
                    !this.search ||
                    (item.tram &&
                        item.tram
                            .toLowerCase()
                            .includes(this.search.toLowerCase())) ||
                    (item.invoicenumber &&
                        item.invoicenumber
                            .toLowerCase()
                            .includes(this.search.toLowerCase())) ||
                    (item.description &&
                        item.description
                            .toLowerCase()
                            .includes(this.search.toLowerCase())) ||
                    (item.khach_hang_ca_nhan &&
                        item.khach_hang_ca_nhan
                            .toLowerCase()
                            .includes(this.search.toLowerCase())) ||
                    (item.khach_hang_doanh_nghiep &&
                        item.khach_hang_doanh_nghiep
                            .toLowerCase()
                            .includes(this.search.toLowerCase()));

                // Apply status filter
                const matchesStatus =
                    this.statusFilter === "all" ||
                    (item.category_debt &&
                        item.category_debt === this.statusFilter);

                // Apply column filters (text inputs)
                const matchesColumnFilters = Object.entries(
                    this.columnFilters
                ).every(([key, value]) => {
                    if (!value) return true;

                    // Date field special handling
                    if (key === "ngay_phat_sinh" && item[key]) {
                        return (
                            this.formatDateForComparison(item[key]) ===
                            this.formatDateForComparison(value)
                        );
                    }

                    // Number fields
                    if (
                        [
                            "ty_gia_quy_doi",
                            "so_tien_theo_gia_tri_dau_tu",
                            "so_tien_no_goc_da_quy",
                            "da_tra_goc",
                            "so_tien_con_lai",
                            "tien_lai",
                            "lai_suat",
                        ].includes(key)
                    ) {
                        return (
                            item[key] !== undefined &&
                            item[key].toString().includes(value)
                        );
                    }

                    // Default text field handling
                    return (
                        item[key] &&
                        item[key]
                            .toString()
                            .toLowerCase()
                            .includes(value.toLowerCase())
                    );
                });

                // Apply dropdown selection filters (checkboxes)
                const matchesDropdownFilters = Object.entries(
                    this.selectedFilterValues
                ).every(([key, values]) => {
                    return (
                        values.length === 0 ||
                        (item[key] && values.includes(item[key]))
                    );
                });

                return (
                    matchesSearch &&
                    matchesStatus &&
                    matchesColumnFilters &&
                    matchesDropdownFilters
                );
            });
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
                    "congno_dichvu_khautru_current_page"
                );
                this.closeExportModal();
            } else {
                alert("Không có dữ liệu để xuất");
            }
        },
        exportToExcelAllPages() {
            // First check if we have all the data loaded
            if (!this.allCongnoList || this.allCongnoList.length === 0) {
                Swal.fire({
                    title: "Lỗi",
                    text: "Không có dữ liệu để xuất hoặc dữ liệu chưa được tải xong",
                    icon: "error",
                    confirmButtonText: "OK",
                });
                return;
            }

            // Apply all current filters to get the filtered dataset
            const filteredData = this.applyFilters();

            if (filteredData && filteredData.length > 0) {
                this.generateExcel(
                    filteredData,
                    "congno_dichvu_khautru_all_data"
                );
                this.closeExportModal();
            } else {
                Swal.fire({
                    title: "Thông báo",
                    text: "Không có dữ liệu để xuất với bộ lọc hiện tại",
                    icon: "warning",
                    confirmButtonText: "OK",
                });
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
                            Trạm: item.tram || "",
                            "Invoice Number": item.invoicenumber || "",
                            "Vụ đầu tư": item.vu_dau_tu || "",
                            "Category Debt": item.category_debt || "",
                            Description: item.description || "",
                            "Ngày phát sinh": item.ngay_phat_sinh
                                ? new Date(
                                      item.ngay_phat_sinh
                                  ).toLocaleDateString("vi-VN")
                                : "",
                            "Loại tiền": item.loai_tien || "",
                            "Tỷ giá quy đổi": item.ty_gia_quy_doi || 0,
                            "Số tiền theo giá trị đầu tư":
                                item.so_tien_theo_gia_tri_dau_tu || 0,
                            "Số tiền nợ gốc đã quy":
                                item.so_tien_no_goc_da_quy || 0,
                            "Đã trả gốc": item.da_tra_goc || 0,
                            "Số tiền còn lại": item.so_tien_con_lai || 0,
                            "Tiền lãi": item.tien_lai || 0,
                            "Mã KH cá nhân": item.ma_khach_hang_ca_nhan || "",
                            "Khách hàng cá nhân": item.khach_hang_ca_nhan || "",
                            "Mã KH doanh nghiệp":
                                item.ma_khach_hang_doanh_nghiep || "",
                            "Khách hàng doanh nghiệp":
                                item.khach_hang_doanh_nghiep || "",
                            "Lãi suất": item.lai_suat || 0,
                            "Loại lãi suất": item.loai_lai_suat || "",
                            "Vụ thanh toán": item.vu_thanh_toan || "",
                            "Loại đầu tư": item.loai_dau_tu || "",
                        }));

                        // Create a worksheet
                        const worksheet = XLSX.utils.json_to_sheet(exportData);

                        // Set column widths
                        const columnWidths = [
                            { wch: 15 }, // Trạm
                            { wch: 15 }, // Invoice Number
                            { wch: 15 }, // Vụ đầu tư
                            { wch: 15 }, // Category Debt
                            { wch: 30 }, // Description
                            { wch: 15 }, // Ngày phát sinh
                            { wch: 10 }, // Loại tiền
                            { wch: 15 }, // Tỷ giá quy đổi
                            { wch: 20 }, // Số tiền theo giá trị đầu tư
                            { wch: 20 }, // Số tiền nợ gốc đã quy
                            { wch: 15 }, // Đã trả gốc
                            { wch: 15 }, // Số tiền còn lại
                            { wch: 15 }, // Tiền lãi
                            { wch: 15 }, // Mã KH cá nhân
                            { wch: 20 }, // Khách hàng cá nhân
                            { wch: 15 }, // Mã KH doanh nghiệp
                            { wch: 20 }, // Khách hàng doanh nghiệp
                            { wch: 10 }, // Lãi suất
                            { wch: 15 }, // Loại lãi suất
                            { wch: 15 }, // Vụ thanh toán
                            { wch: 15 }, // Loại đầu tư
                        ];
                        worksheet["!cols"] = columnWidths;

                        // Create a workbook
                        const workbook = XLSX.utils.book_new();
                        XLSX.utils.book_append_sheet(
                            workbook,
                            worksheet,
                            "Công nợ dịch vụ khấu trừ"
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
            try {
                const modalElement = document.getElementById("exportModal");
                if (modalElement) {
                    // Try to close using Bootstrap's API if available
                    import("bootstrap/dist/js/bootstrap.bundle.min.js")
                        .then((bootstrap) => {
                            if (bootstrap && bootstrap.Modal) {
                                const modal =
                                    bootstrap.Modal.getInstance(modalElement);
                                if (modal) {
                                    modal.hide();
                                } else {
                                    // Fallback: If no instance found, create one and hide it
                                    new bootstrap.Modal(modalElement).hide();
                                }
                            } else {
                                // Fallback for when bootstrap module doesn't load as expected
                                this.closeModalDirect(modalElement);
                            }
                        })
                        .catch(() => {
                            // Fallback if import fails
                            this.closeModalDirect(modalElement);
                        });
                }
            } catch (error) {
                console.error("Error closing modal:", error);
                // Try direct method as last resort
                const modalElement = document.getElementById("exportModal");
                if (modalElement) {
                    this.closeModalDirect(modalElement);
                }
            }
        },

        // Add this helper method for direct modal closing
        closeModalDirect(modalElement) {
            // Remove the modal classes that make it visible
            modalElement.classList.remove("show");
            modalElement.style.display = "none";
            document.body.classList.remove("modal-open");

            // Remove backdrop if it exists
            const backdrop = document.querySelector(".modal-backdrop");
            if (backdrop) {
                backdrop.remove();
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
                    "/api/import-congno-dichvu-khautru",
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
                            (Array.isArray(error.response.data.message)
                                ? error.response.data.message
                                : [
                                      error.response.data.message ||
                                          "Lỗi máy chủ",
                                  ]);
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
                    `/api/import-congno-dichvu-khautru-progress/${importId}`,
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
                            this.fetchData();
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
        /**
         * Open the import modal
         */
        openImportModal() {
            this.selectedFile = null;
            this.importErrors = [];
            this.uploadProgress = 0;
            this.isImporting = false;
            this.processingRecords = false;
            this.processingProgress = 0;

            // Initialize Bootstrap modal if not already initialized
            if (!this.importModal) {
                this.importModal = new bootstrap.Modal(
                    document.getElementById("importModal")
                );
            }

            this.importModal.show();
        },

        /**
         * Close the import modal
         */
        async closeImportModal() {
            try {
                if (this.importModal) {
                    this.importModal.hide();
                }

                // Reset all import-related states
                this.selectedFile = null;
                this.uploadProgress = 0;
                this.isImporting = false;
                this.processingRecords = false;
                this.processingProgress = 0;
                this.processedRecords = 0;
                this.totalRecords = 0;
                this.importErrors = [];
            } catch (error) {
                console.error("Error closing import modal:", error);
            }
        },

        /**
         * Handle file selection for import
         */
        handleFileSelected(event) {
            this.selectedFile = event.target.files[0] || null;
            this.importErrors = [];

            if (this.selectedFile) {
                // Validate file type
                const validTypes = [
                    "application/vnd.ms-excel",
                    "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                    "text/csv",
                ];

                if (!validTypes.includes(this.selectedFile.type)) {
                    this.importErrors = [
                        "Invalid file type. Please upload an Excel (.xlsx, .xls) or CSV file.",
                    ];
                    this.selectedFile = null;
                    event.target.value = null; // Reset file input
                }

                // Validate file size (max 10MB)
                if (this.selectedFile && this.selectedFile.size > 10485760) {
                    this.importErrors = [
                        "File size exceeds maximum limit (10MB).",
                    ];
                    this.selectedFile = null;
                    event.target.value = null; // Reset file input
                }
            }
        },

        /**
         * Start the import process
         */
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
                text: "Dữ liệu công nợ hiện có với cùng số hóa đơn sẽ bị cập nhật. Bạn có chắc chắn muốn tiếp tục?",
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Tải lên",
                cancelButtonText: "Hủy",
                buttonsStyling: false,
                customClass: {
                    confirmButton: "btn btn-success me-2",
                    cancelButton: "btn btn-secondary",
                },
            });

            if (!result.isConfirmed) {
                return;
            }

            this.isImporting = true;
            this.importErrors = [];

            // Create FormData object for file upload
            const formData = new FormData();
            formData.append("file", this.selectedFile);

            try {
                // Upload file to server
                const response = await axios.post(
                    "/api/import-congno-dichvu-khautru",
                    formData,
                    {
                        headers: {
                            "Content-Type": "multipart/form-data",
                            Authorization: "Bearer " + this.store.getToken,
                        },
                        onUploadProgress: (progressEvent) => {
                            // Calculate and update upload progress percentage
                            const percentCompleted = Math.round(
                                (progressEvent.loaded * 100) /
                                    progressEvent.total
                            );
                            this.uploadProgress = percentCompleted;
                        },
                    }
                );

                if (response.data.success) {
                    this.uploadProgress = 100;
                    this.processingRecords = true;

                    // Begin checking the import progress
                    await this.checkImportProgress(response.data.import_id);
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

        /**
         * Check the progress of the import process
         */
        async checkImportProgress(importId) {
            if (!importId) {
                this.importErrors = ["Invalid import ID. Please try again."];
                return;
            }

            try {
                const response = await axios.get(
                    `/api/import-congno-dichvu-khautru-progress/${importId}`,
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
                    this.processingRecords = false;
                    this.isImporting = false;

                    if (data.errors && data.errors.length > 0) {
                        this.importErrors = data.errors;

                        // Show error notification with SweetAlert2
                        Swal.fire({
                            title: "Cảnh báo",
                            text: `Tải lên hoàn tất với ${data.errors.length} lỗi. Vui lòng kiểm tra danh sách lỗi bên dưới.`,
                            icon: "warning",
                            confirmButtonText: "OK",
                            buttonsStyling: false,
                            customClass: {
                                confirmButton: "btn btn-success",
                            },
                        });
                    } else {
                        // Success notification
                        setTimeout(() => {
                            // Close the modal
                            this.closeImportModal();

                            // Show success toast
                            Swal.fire({
                                toast: true,
                                position: "top-end",
                                icon: "success",
                                title: "Tải lên thành công",
                                text: `Đã xử lý ${data.processed} bản ghi.`,
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

                            // Refresh data to get the latest changes
                            this.fetchData();
                        }, 1000);
                    }
                } else {
                    // Update progress
                    this.processedRecords = data.processed || 0;
                    this.totalRecords = data.total || 0;

                    if (this.totalRecords > 0) {
                        this.processingProgress = Math.round(
                            (this.processedRecords / this.totalRecords) * 100
                        );
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
        /**
         * Download template for import
         */
        downloadImportTemplate() {
            try {
                // Import xlsx dynamically
                import("xlsx").then((XLSX) => {
                    // Create a workbook with headers
                    const wb = XLSX.utils.book_new();
                    const headers = this.getDbColumns();

                    // Create example data row
                    const exampleRow = {};
                    headers.forEach((header) => {
                        // Add sample data for each column
                        switch (header) {
                            case "Tram":
                                exampleRow[header] = "Trạm Mẫu";
                                break;
                            case "Invoice_Number":
                                exampleRow[header] = "INV001";
                                break;
                            case "So_Tien_Theo_Gia_Tri_Dau_Tu":
                            case "So_Tien_No_Goc_Da_Quy":
                            case "Da_Tra_Goc":
                            case "So_Tien_Con_Lai":
                            case "Tien_Lai":
                                exampleRow[header] = "1000000";
                                break;
                            case "Ty_Gia_Quy_Doi":
                                exampleRow[header] = "1";
                                break;
                            case "Lai_Suat":
                                exampleRow[header] = "5.5";
                                break;
                            case "Ngay_Phat_Sinh":
                                exampleRow[header] = new Date()
                                    .toISOString()
                                    .split("T")[0];
                                break;
                            case "Vu_Dau_Tu":
                                exampleRow[header] = "Vụ 2023-2024";
                                break;
                            case "Loai_Tien":
                                exampleRow[header] = "KIP";
                                break;
                            case "Loai_Lai_Suat":
                                exampleRow[header] = "Cố định";
                                break;
                            case "Category_Debt":
                                exampleRow[header] = "Nợ thường";
                                break;
                            default:
                                exampleRow[header] = header + " (ví dụ)";
                        }
                    });

                    // Create worksheet with headers and example data
                    const ws = XLSX.utils.json_to_sheet([exampleRow], {
                        header: headers,
                    });

                    // Set column widths
                    const columnWidths = headers.map(() => ({ wch: 20 }));
                    ws["!cols"] = columnWidths;

                    // Add worksheet to workbook
                    XLSX.utils.book_append_sheet(wb, ws, "Template");

                    // Save file
                    XLSX.writeFile(
                        wb,
                        "congno_dichvu_khautru_import_template.xlsx"
                    );
                });
            } catch (error) {
                console.error("Error generating template:", error);
                Swal.fire({
                    title: "Lỗi",
                    text: "Không thể tạo tệp mẫu nhập liệu",
                    icon: "error",
                    confirmButtonText: "OK",
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: "btn btn-success",
                    },
                });
            }
        },
        /**
         * Get the list of database columns
         */
        getDbColumns() {
            return [
                "Tram",
                "Invoice_Number",
                "Vu_Dau_Tu",
                "Category_Debt",
                "Description",
                "Ngay_Phat_Sinh",
                "Loai_Tien",
                "Ty_Gia_Quy_Doi",
                "So_Tien_Theo_Gia_Tri_Dau_Tu",
                "So_Tien_No_Goc_Da_Quy",
                "Da_Tra_Goc",
                "So_Tien_Con_Lai",
                "Tien_Lai",
                "Ma_Khach_Hang_Ca_Nhan",
                "Khach_Hang_Ca_Nhan",
                "Ma_Khach_Hang_Doanh_Nghiep",
                "Khach_Hang_Doanh_Nghiep",
                "Lai_Suat",
                "Loai_Lai_Suat",
                "Vu_Thanh_Toan",
                "Loai_Dau_Tu",
            ];
        },
        showActionsMenu() {
            // For mobile view
            Swal.fire({
                title: "Tùy chọn",
                html: `
                <div class="list-group">
                    <button class="list-group-item list-group-item-action" onclick="window.exportToExcel()">
                        <i class="fas fa-file-excel text-success me-2"></i> Export to Excel
                    </button>
                    <button class="list-group-item list-group-item-action" onclick="window.importData()">
                        <i class="fas fa-upload text-primary me-2"></i> Import Data
                    </button>
                    <button class="list-group-item list-group-item-action" onclick="window.downloadTemplate()">
                        <i class="fas fa-file-download text-info me-2"></i> Download Template
                    </button>
                </div>
                `,
                showConfirmButton: false,
                showCloseButton: true,
                didOpen: () => {
                    // Define global functions for the buttons
                    window.exportToExcel = () => {
                        Swal.close();
                        this.showExportModal();
                    };
                    window.importData = () => {
                        Swal.close();
                        this.importData();
                    };
                    window.downloadTemplate = () => {
                        Swal.close();
                        this.downloadImportTemplate();
                    };
                },
                willClose: () => {
                    // Clean up global functions
                    delete window.exportToExcel;
                    delete window.importData;
                    delete window.downloadTemplate;
                },
            });
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
    },
    watch: {
        "uniqueValues.category_debt": {
            handler() {
                this.updateStatusOptions();
            },
            deep: true,
        },
        search(newVal, oldVal) {
            if (newVal !== oldVal) {
                this.currentPage = 1;
                // Use debounce to avoid too many calculations
                clearTimeout(this.searchDebounce);
                this.searchDebounce = setTimeout(() => {
                    this.applyFiltersAndPagination(1);
                }, 500);
            }
        },
        statusFilter(newVal, oldVal) {
            if (newVal !== oldVal) {
                this.currentPage = 1;
                this.applyFiltersAndPagination(1);
            }
        },
        // Add watcher for column filters
        columnFilters: {
            handler() {
                clearTimeout(this.filterDebounce);
                this.filterDebounce = setTimeout(() => {
                    this.currentPage = 1;
                    this.applyFiltersAndPagination(1);
                }, 500);
            },
            deep: true,
        },
        selectedFilterValues: {
            handler() {
                this.currentPage = 1;
                this.applyFiltersAndPagination(1);
            },
            deep: true,
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
        // Initialize uniqueValues with empty arrays
        this.uniqueValues = {
            tram: [],
            vu_dau_tu: [],
            category_debt: [],
            loai_tien: [],
            loai_lai_suat: [],
            vu_thanh_toan: [],
            loai_dau_tu: [],
        };
        // ทำให้แน่ใจว่า selectedFilterValues ถูกเริ่มต้นเป็น object ว่างเช่นกัน
        this.selectedFilterValues = {
            tram: [],
            vu_dau_tu: [],
            category_debt: [],
            loai_tien: [],
            loai_lai_suat: [],
            vu_thanh_toan: [],
            loai_dau_tu: [],
        };
        this.fetchData(1, true);

        // Watch for screen size changes to toggle mobile view
        window.addEventListener("resize", () => {
            this.isMobile = window.innerWidth < 768;
            this.updateScrollbar();
        });

        // Initialize PerfectScrollbar
        this.$nextTick(() => {
            this.initPerfectScrollbar();
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

        // Destroy PerfectScrollbar instance when component is unmounted
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

/* Desktop row styling */
.desktop-row {
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
}

.desktop-row:hover {
    background-color: #f0f9f0;
    box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);
}

.table-auto td {
    padding: 0.75rem;
    border-bottom: 1px solid #e5e7eb;
    vertical-align: middle;
    font-size: 0.7rem;
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

/* Filter button styling */
.filter-btn {
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 0.25rem;
    margin-left: 0.25rem;
    color: #6c757d;
    transition: all 0.2s ease;
}

.filter-btn:hover {
    color: #10b981;
}

.filter-btn:focus {
    outline: none;
}

/* Status color classes */
.text-yellow-600 {
    color: #d97706;
}

.text-blue-600 {
    color: #2563eb;
}

.text-green-500 {
    color: #10b981;
}

.text-red-500 {
    color: #ef4444;
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
    padding: 12px;
    border: 1px solid #e2e8f0;
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

    .table-auto thead th {
        font-size: 0.75rem;
        padding: 0.5rem;
    }

    .table-auto td {
        font-size: 0.75rem;
        padding: 0.5rem;
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

/* Additional styles for CongnoDichviKhautru.vue */
.btn-icon {
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.375rem;
    transition: all 0.2s;
}

.btn-icon:hover {
    background-color: #f3f4f6;
}

.actions-menu .dropdown-menu {
    min-width: 200px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1),
        0 2px 4px -1px rgba(0, 0, 0, 0.06);
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 0.5rem 0;
}

.actions-menu .dropdown-item {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    color: #374151;
    transition: all 0.2s;
}

.actions-menu .dropdown-item:hover {
    background-color: #f9fafb;
}

.search-input {
    min-width: 300px;
}

.search-input:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
    border-color: #10b981;
}

/* Mobile card display enhancements */
.card-container .card {
    cursor: pointer;
}

.card-container .card strong {
    font-weight: 600;
    color: #4b5563;
}

/* Export and Import Modal styling */
.modal-content {
    border-radius: 0.5rem;
    border: none;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.modal-header {
    border-bottom: 1px solid #e5e7eb;
    padding: 1rem 1.5rem;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    border-top: 1px solid #e5e7eb;
    padding: 1rem 1.5rem;
}

.btn-outline-success {
    color: #198754;
    border-color: #198754;
    background-color: transparent;
    transition: all 0.2s;
}

.btn-outline-success:hover {
    color: #fff;
    background-color: #198754;
    border-color: #198754;
}

.btn-outline-secondary {
    color: #6c757d;
    border-color: #6c757d;
    background-color: transparent;
    transition: all 0.2s;
}

.btn-outline-secondary:hover {
    color: #fff;
    background-color: #6c757d;
    border-color: #6c757d;
}

/* Progress bars */
.progress {
    display: flex;
    height: 1rem;
    overflow: hidden;
    font-size: 0.75rem;
    background-color: #e9ecef;
    border-radius: 0.25rem;
}

.progress-bar {
    display: flex;
    flex-direction: column;
    justify-content: center;
    overflow: hidden;
    color: #fff;
    text-align: center;
    white-space: nowrap;
    background-color: #0d6efd;
    transition: width 0.6s ease;
}

.progress-bar-animated {
    animation: progress-bar-stripes 1s linear infinite;
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

@keyframes progress-bar-stripes {
    0% {
        background-position-x: 1rem;
    }
}
/* Totals cards styling */
.totals-container {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1rem;
    justify-content: space-between;
}

.total-card {
    background-color: #ffffff;
    flex: 1;
    min-width: 200px;
    display: flex;
    align-items: center;
    padding: 1.25rem;
    border-radius: 0.75rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1),
        0 2px 4px -1px rgba(0, 0, 0, 0.06);
    transition: transform 0.2s, box-shadow 0.2s;
    overflow: hidden;
    position: relative;
}

.total-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1),
        0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.total-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    margin-right: 1rem;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.total-content {
    flex-grow: 1;
}

.total-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #6b7280;
    margin-bottom: 0.25rem;
    white-space: nowrap;
}

.total-value {
    font-size: 1rem;
    font-weight: 700;
    line-height: 1.2;
}

/* Card specific colors */
.total-debt {
    background-color: rgba(59, 130, 246, 0.1);
    border-left: 4px solid #3b82f6;
}

.total-debt .total-icon {
    color: #3b82f6;
    background-color: rgba(59, 130, 246, 0.2);
}

.total-debt .total-value {
    color: #1e40af;
}

.total-paid {
    background-color: rgba(16, 185, 129, 0.1);
    border-left: 4px solid #10b981;
}

.total-paid .total-icon {
    color: #10b981;
    background-color: rgba(16, 185, 129, 0.2);
}

.total-paid .total-value {
    color: #065f46;
}

.total-remaining {
    background-color: rgba(239, 68, 68, 0.1);
    border-left: 4px solid #ef4444;
}

.total-remaining .total-icon {
    color: #ef4444;
    background-color: rgba(239, 68, 68, 0.2);
}

.total-remaining .total-value {
    color: #b91c1c;
}

.total-interest {
    background-color: rgba(245, 158, 11, 0.1);
    border-left: 4px solid #f59e0b;
}

.total-interest .total-icon {
    color: #f59e0b;
    background-color: rgba(245, 158, 11, 0.2);
}

.total-interest .total-value {
    color: #b45309;
}

/* Mobile responsiveness for totals */
@media (max-width: 768px) {
    .totals-container {
        gap: 0.75rem;
    }

    .total-card {
        min-width: calc(50% - 0.5rem);
        flex: 0 0 calc(50% - 0.5rem);
        padding: 0.75rem;
    }

    .total-icon {
        width: 40px;
        height: 40px;
        font-size: 1.25rem;
        margin-right: 0.75rem;
    }

    .total-label {
        font-size: 0.75rem;
    }

    .total-value {
        font-size: 1rem;
    }
}

@media (max-width: 480px) {
    .totals-container {
        flex-direction: column;
    }

    .total-card {
        width: 100%;
        min-width: 100%;
    }
}

/* Enhanced mobile styles */
.mobile-controls {
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    background: linear-gradient(to bottom, #ffffff, #f9fafb);
}

.search-container {
    margin-bottom: 8px;
}

.search-icon {
    color: #9ca3af;
}

.form-label {
    display: block;
    margin-bottom: 4px;
    font-size: 0.8rem;
    font-weight: 500;
}

.actions-row {
    margin-top: 8px;
}

.btn-actions {
    transition: all 0.2s;
    font-weight: 500;
    font-size: 0.875rem;
}

.btn-actions:active {
    transform: translateY(1px);
}

.records-count {
    font-size: 0.875rem;
    color: #4b5563;
}

/* Card styling enhancements */
.card-container .card {
    overflow: hidden;
    border: 1px solid #f0f0f0;
    transition: all 0.3s ease;
}

.card-container .card:active {
    transform: scale(0.98);
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}

.card-btn {
    color: #10b981;
    font-size: 0.75rem;
}

.customer-info {
    background-color: rgba(243, 244, 246, 0.7);
}

/* Badge styling */
.badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    font-size: 1rem;
    font-weight: 500;
    line-height: 1;
    text-align: center;
    white-space: nowrap;
    vertical-align: baseline;
    border-radius: 0.25rem;
}

/* Limit pagination on mobile */
@media (max-width: 768px) {
    .pagination-card
        .page-item:nth-child(n + 5):not(:last-child):not(:nth-last-child(2)) {
        display: none;
    }
    .page-item:nth-child(n + 5):not(:last-child):not(:nth-last-child(2)) {
        display: none;
    }
    /* Fix reset filter button position for mobile */
    .mobile-controls .reset-all-filters-btn {
        position: relative;
        right: auto;
        top: auto;
        z-index: 5;
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
}
/* Category debt badges */
.badge {
    display: inline-block;
    padding: 0.3em 0.65em;
    font-size: 1em;
    font-weight: 600;
    line-height: 1;
    text-align: center;
    white-space: nowrap;
    vertical-align: baseline;
    border-radius: 0.375rem;
}

/* Blue */
.bg-blue-100 {
    background-color: #dbeafe;
}
.text-blue-800 {
    color: #1e40af;
}

/* Red */
.bg-red-100 {
    background-color: #fee2e2;
}
.text-red-800 {
    color: #b91c1c;
}

/* Purple */
.bg-purple-100 {
    background-color: #ede9fe;
}
.text-purple-800 {
    color: #5b21b6;
}

/* Amber */
.bg-amber-100 {
    background-color: #fef3c7;
}
.text-amber-800 {
    color: #92400e;
}

/* Green */
.bg-green-100 {
    background-color: #d1fae5;
}
.text-green-700 {
    color: #047857;
}

/* Indigo */
.bg-indigo-100 {
    background-color: #e0e7ff;
}
.text-indigo-800 {
    color: #3730a3;
}

/* Rose */
.bg-rose-100 {
    background-color: #ffe4e6;
}
.text-rose-800 {
    color: #9f1239;
}

/* Teal */
.bg-teal-100 {
    background-color: #ccfbf1;
}
.text-teal-800 {
    color: #115e59;
}

/* Cyan */
.bg-cyan-100 {
    background-color: #cffafe;
}
.text-cyan-800 {
    color: #155e75;
}

/* Gray (default) */
.bg-gray-100 {
    background-color: #f3f4f6;
}
.text-gray-800 {
    color: #1f2937;
}

/* Secondary */
.bg-secondary {
    background-color: #6c757d;
}
.text-white {
    color: #ffffff;
}
</style>
