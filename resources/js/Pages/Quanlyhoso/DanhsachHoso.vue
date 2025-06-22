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
                <router-link to="/taonewhoso">
                    <button type="button" class="button-30-save">
                        <i class="fa-solid fa-plus"></i>Tạo mới
                    </button>
                </router-link>
                <button
                    v-if="hasPermission('xóa danh sách hồ sơ')"
                    type="button"
                    class="button-30-text-red"
                    @click="deleteSelected"
                >
                    <i class="fa-solid fa-trash-can"></i>Xóa
                </button>
                <div class="status-filter">
                    <select
                        v-model="statusFilter"
                        class="form-select status-select"
                    >
                        <option
                            v-for="option in statusOptions"
                            :key="option.code"
                            :value="option.code"
                            class="status-option"
                        >
                            <span
                                v-if="option.code !== 'all'"
                                class="status-icon"
                            >
                                <i :class="statusIcon(option.code)"></i>
                            </span>
                            {{ option.name }} ({{
                                statusCounts[option.code] || 0
                            }})
                        </option>
                    </select>
                </div>
                <!-- <div class="investment-filter ms-3">
                    <select
                        v-model="investmentFilter"
                        class="form-select investment-select"
                    >
                        <option value="all">Tất cả vụ đầu tư</option>
                        <option
                            v-for="project in investmentProjects"
                            :key="project"
                            :value="project"
                        >
                            {{ project }}
                        </option>
                    </select>
                </div> -->
                <div
                    class="col d-flex justify-content-end gap-3 align-items-center"
                >
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search..."
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
            <!-- Search and Create Button Row -->
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
                        placeholder="Tìm kiếm hồ sơ..."
                        class="w-full pl-10 pr-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm"
                    />
                </div>
                <router-link to="/taonewhoso">
                    <button
                        type="button"
                        class="button-30-save flex items-center justify-center h-full px-3 py-2.5 whitespace-nowrap"
                    >
                        <i class="fa-solid fa-plus mr-1"></i
                        ><span>Tạo mới</span>
                    </button>
                </router-link>
            </div>

            <!-- Filters Section -->
            <div class="filter-section">
                <!-- Status Filter -->
                <div class="mb-2.5">
                    <label class="text-sm font-medium text-gray-700 mb-1 block"
                        >Trạng thái</label
                    >
                    <div class="relative">
                        <select
                            v-model="statusFilter"
                            class="w-full py-2.5 pl-3 pr-10 border rounded-lg appearance-none bg-white focus:outline-none focus:ring-2 focus:ring-green-500 text-sm"
                        >
                            <option
                                v-for="option in statusOptions"
                                :key="option.code"
                                :value="option.code"
                            >
                                <span v-if="option.code !== 'all'">
                                    <i :class="statusIcon(option.code)"></i>
                                </span>
                                {{ option.name }} ({{
                                    statusCounts[option.code] || 0
                                }})
                            </option>
                        </select>
                        <div
                            class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"
                        >
                            <i class="fas fa-chevron-down text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <!-- Investment Filter -->
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1 block"
                        >Vụ đầu tư</label
                    >
                    <div class="relative">
                        <select
                            v-model="investmentFilter"
                            class="w-full py-2.5 pl-3 pr-10 border rounded-lg appearance-none bg-white focus:outline-none focus:ring-2 focus:ring-green-500 text-sm"
                        >
                            <option value="all">Tất cả vụ đầu tư</option>
                            <option
                                v-for="project in investmentProjects"
                                :key="project"
                                :value="project"
                            >
                                {{ project }}
                            </option>
                        </select>
                        <div
                            class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"
                        >
                            <i class="fas fa-chevron-down text-gray-400"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delete Button (Only if items are selected) -->
            <div class="mt-3" v-if="selectedItems.length > 0">
                <button
                    type="button"
                    class="w-full py-2 px-4 bg-red-600 text-white rounded-lg flex items-center justify-center text-sm font-medium"
                    @click="deleteSelected"
                >
                    <i class="fa-solid fa-trash-can mr-2"></i>Xóa
                    {{ selectedItems.length }} mục đã chọn
                </button>
            </div>
        </div>

        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <!-- สำหรับ Desktop -->
            <div v-if="!isMobile" class="card">
                <div class="card-body">
                    <!-- Add this button before the table -->
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
                                        <!-- Checkbox column header -->
                                        <th class="px-4 py-2 clickable-cell">
                                            <input
                                                type="checkbox"
                                                @click="toggleSelectAll($event)"
                                                :checked="isAllSelected"
                                                class="form-checkbox h-4 w-4 text-green-600"
                                            />
                                        </th>

                                        <!-- Mã số phiếu with text search filter -->
                                        <th class="px-4 py-2 clickable-cell">
                                            Mã số phiếu
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
                                                    placeholder="Lọc theo mã số..."
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

                                        <!-- Trạm with unique dropdown filter -->
                                        <th class="px-4 py-2 clickable-cell">
                                            <div class="column-header">
                                                <div
                                                    class="header-content"
                                                    @click="
                                                        sortTable('station')
                                                    "
                                                >
                                                    Trạm
                                                    <i
                                                        :class="
                                                            getSortIcon(
                                                                'station'
                                                            )
                                                        "
                                                        class="sort-icon ml-1"
                                                    ></i>
                                                </div>
                                                <button
                                                    @click.stop="
                                                        toggleFilter('station')
                                                    "
                                                    class="filter-btn"
                                                >
                                                    <i
                                                        class="fas fa-filter"
                                                        :class="{
                                                            'text-green-500':
                                                                selectedFilterValues.station &&
                                                                selectedFilterValues
                                                                    .station
                                                                    .length > 0,
                                                        }"
                                                    >
                                                    </i>
                                                </button>
                                            </div>
                                            <div
                                                v-if="
                                                    activeFilter === 'station'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <div
                                                    class="max-h-40 overflow-y-auto mb-2"
                                                >
                                                    <div
                                                        v-for="option in uniqueValues.station"
                                                        :key="option"
                                                        class="flex items-center mb-2"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            :id="`station-${option}`"
                                                            :value="option"
                                                            v-model="
                                                                selectedFilterValues.station
                                                            "
                                                            class="mr-2 rounded text-green-500 focus:ring-green-500"
                                                        />
                                                        <label
                                                            :for="`station-${option}`"
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
                                                                'station'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'station'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Tiều đề with text search filter -->
                                        <th class="px-4 py-2 clickable-cell">
                                            Tiều đề
                                            <button
                                                @click="toggleFilter('title')"
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
                                                v-if="activeFilter === 'title'"
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
                                                            resetFilter('title')
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter('title')
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Vụ đầu tư with unique dropdown filter -->
                                        <th class="px-4 py-2 clickable-cell">
                                            <div class="column-header">
                                                <div
                                                    class="header-content"
                                                    @click="
                                                        sortTable(
                                                            'investment_project'
                                                        )
                                                    "
                                                >
                                                    Trạm
                                                    <i
                                                        :class="
                                                            getSortIcon(
                                                                'investment_project'
                                                            )
                                                        "
                                                        class="sort-icon"
                                                    ></i>
                                                </div>
                                                <button
                                                    @click.stop="
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
                                                                    .length > 0,
                                                        }"
                                                    ></i>
                                                </button>
                                            </div>
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
                                                            :value="option"
                                                            v-model="
                                                                selectedFilterValues.investment_project
                                                            "
                                                            class="mr-2 rounded text-green-500 focus:ring-green-500"
                                                        />
                                                        <label
                                                            :for="`investment_project-${option}`"
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

                                        <!-- Số lượng hồ sơ - no filter -->
                                        <th
                                            class="px-4 py-2 clickable-cell"
                                            @click="sortTable('file_count')"
                                        >
                                            <i
                                                :class="
                                                    getSortIcon('file_count')
                                                "
                                                class="ml-1"
                                            ></i>
                                            Số lượng hồ sơ
                                        </th>

                                        <!-- Loại hồ sơ with unique dropdown filter -->
                                        <th class="px-4 py-2 clickable-cell">
                                            Loại hồ sơ
                                            <button
                                                @click="
                                                    toggleFilter(
                                                        'document_type'
                                                    )
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            selectedFilterValues.document_type &&
                                                            selectedFilterValues
                                                                .document_type
                                                                .length > 0,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'document_type'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <div
                                                    class="max-h-40 overflow-y-auto mb-2"
                                                >
                                                    <div
                                                        v-for="option in uniqueValues.document_type"
                                                        :key="option"
                                                        class="flex items-center mb-2"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            :id="`document_type-${option}`"
                                                            :value="option"
                                                            v-model="
                                                                selectedFilterValues.document_type
                                                            "
                                                            class="mr-2 rounded text-green-500 focus:ring-green-500"
                                                        />
                                                        <label
                                                            :for="`document_type-${option}`"
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
                                                                'document_type'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'document_type'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Ngày lập with date filter -->
                                        <th class="px-4 py-2 clickable-cell">
                                            <div class="column-header">
                                                <div
                                                    class="header-content"
                                                    @click="
                                                        sortTable(
                                                            'created_date'
                                                        )
                                                    "
                                                >
                                                    Ngày tạo
                                                    <i
                                                        :class="
                                                            getSortIcon(
                                                                'created_date'
                                                            )
                                                        "
                                                        class="sort-icon"
                                                    ></i>
                                                </div>
                                                <button
                                                    @click.stop="
                                                        toggleFilter(
                                                            'created_date'
                                                        )
                                                    "
                                                    class="filter-btn"
                                                >
                                                    <i
                                                        class="fas fa-filter"
                                                        :class="{
                                                            'text-green-500':
                                                                selectedFilterValues.created_date &&
                                                                selectedFilterValues
                                                                    .created_date
                                                                    .length > 0,
                                                        }"
                                                    ></i>
                                                </button>
                                            </div>

                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'created_date'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="date"
                                                    v-model="
                                                        columnFilters.created_date
                                                    "
                                                    class="form-control mb-2"
                                                />
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'created_date'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'created_date'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Người tạo with text search filter -->
                                        <th class="px-4 py-2 clickable-cell">
                                            Người tạo
                                            <button
                                                @click="toggleFilter('creator')"
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.creator,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter === 'creator'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.creator
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
                                                                'creator'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'creator'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Người nhận with text search filter -->
                                        <th class="px-4 py-2 clickable-cell">
                                            Người nhận
                                            <button
                                                @click="
                                                    toggleFilter('receiver')
                                                "
                                                class="filter-btn"
                                            >
                                                <i
                                                    class="fas fa-filter"
                                                    :class="{
                                                        'text-green-500':
                                                            columnFilters.receiver,
                                                    }"
                                                ></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter === 'receiver'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.receiver
                                                    "
                                                    class="form-control mb-2"
                                                    placeholder="Lọc theo người nhận..."
                                                />
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'receiver'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'receiver'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Ngày nhận with date filter -->
                                        <th class="px-4 py-2 clickable-cell">
                                            <div class="column-header">
                                                <div
                                                    class="header-content"
                                                    @click="
                                                        sortTable(
                                                            'received_date'
                                                        )
                                                    "
                                                >
                                                    Ngày nhận
                                                    <i
                                                        :class="
                                                            getSortIcon(
                                                                'received_date'
                                                            )
                                                        "
                                                        class="sort-icon"
                                                    ></i>
                                                </div>
                                                <button
                                                    @click.stop="
                                                        toggleFilter(
                                                            'received_date'
                                                        )
                                                    "
                                                    class="filter-btn"
                                                >
                                                    <i
                                                        class="fas fa-filter"
                                                        :class="{
                                                            'text-green-500':
                                                                selectedFilterValues.received_date &&
                                                                selectedFilterValues
                                                                    .received_date
                                                                    .length > 0,
                                                        }"
                                                    ></i>
                                                </button>
                                            </div>
                                            <div
                                                v-if="
                                                    activeFilter ===
                                                    'received_date'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="date"
                                                    v-model="
                                                        columnFilters.received_date
                                                    "
                                                    class="form-control mb-2"
                                                />
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'received_date'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'received_date'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Trạng thái with unique dropdown filter -->
                                        <th class="px-4 py-2 clickable-cell">
                                            <div class="column-header">
                                                <div
                                                    class="header-content"
                                                    @click="sortTable('status')"
                                                >
                                                    Trạng thái giao nhận HS
                                                    <i
                                                        :class="
                                                            getSortIcon(
                                                                'status'
                                                            )
                                                        "
                                                        class="sort-icon"
                                                    ></i>
                                                </div>
                                                <button
                                                    @click.stop="
                                                        toggleFilter('status')
                                                    "
                                                    class="filter-btn"
                                                >
                                                    <i
                                                        class="fas fa-filter"
                                                        :class="{
                                                            'text-green-500':
                                                                selectedFilterValues.status &&
                                                                selectedFilterValues
                                                                    .status
                                                                    .length > 0,
                                                        }"
                                                    ></i>
                                                </button>
                                            </div>
                                            <div
                                                v-if="activeFilter === 'status'"
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <div
                                                    class="max-h-40 overflow-y-auto mb-2"
                                                >
                                                    <div
                                                        v-for="option in uniqueValues.status"
                                                        :key="option.code"
                                                        class="flex items-center mb-2"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            :id="`status-${option.code}`"
                                                            :value="option.code"
                                                            v-model="
                                                                selectedFilterValues.status
                                                            "
                                                            class="mr-2 rounded text-green-500 focus:ring-green-500"
                                                        />
                                                        <label
                                                            :for="`status-${option.code}`"
                                                            class="select-none"
                                                            >{{
                                                                option.name
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
                                                                'status'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-light"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'status'
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
                                        v-for="item in paginatedDeliveries.data"
                                        :key="item.id"
                                        class="desktop-row"
                                    >
                                        <!-- New checkbox column for each row -->
                                        <td class="px-4 py-2 clickable-cell">
                                            <input
                                                type="checkbox"
                                                v-model="selectedItems"
                                                :value="item.id"
                                                class="form-checkbox h-4 w-4 text-green-600"
                                            />
                                        </td>
                                        <td
                                            class="px-4 py-2 clickable-cell"
                                            @click="goToEditPage(item.id)"
                                        >
                                            <span>{{
                                                item.document_code
                                            }}</span>
                                        </td>
                                        <td
                                            class="px-4 py-2 clickable-cell"
                                            @click="goToEditPage(item.id)"
                                        >
                                            {{ item.station }}
                                        </td>

                                        <td
                                            class="px-4 py-2 clickable-cell"
                                            @click="goToEditPage(item.id)"
                                        >
                                            {{ item.title }}
                                        </td>
                                        <td
                                            class="px-4 py-2 clickable-cell"
                                            @click="goToEditPage(item.id)"
                                        >
                                            {{ item.investment_project }}
                                        </td>
                                        <td
                                            class="px-4 py-2 clickable-cell"
                                            @click="goToEditPage(item.id)"
                                        >
                                            {{ item.file_count }}
                                        </td>
                                        <td
                                            class="px-4 py-2 clickable-cell"
                                            @click="goToEditPage(item.id)"
                                        >
                                            {{ item.document_type }}
                                        </td>
                                        <td class="px-4 py-2 clickable-cell">
                                            {{ formatDate(item.created_date) }}
                                        </td>
                                        <td class="px-4 py-2 clickable-cell">
                                            <i
                                                class="fas fa-user text-blue-500"
                                            ></i>
                                            {{
                                                item.creator?.full_name || "N/A"
                                            }}
                                        </td>
                                        <td class="px-4 py-2 clickable-cell">
                                            <template v-if="item.receiver">
                                                <i
                                                    class="fas fa-user text-green-500"
                                                ></i>
                                                {{ item.receiver.full_name }}
                                            </template>
                                            <template v-else>
                                                <span class="text-gray-400"
                                                    >-</span
                                                >
                                            </template>
                                        </td>
                                        <td class="px-4 py-2 clickable-cell">
                                            {{
                                                item.received_date
                                                    ? formatDate(
                                                          item.received_date
                                                      )
                                                    : "-"
                                            }}
                                        </td>
                                        <td class="px-4 py-2 clickable-cell">
                                            <span
                                                :class="
                                                    statusClass(
                                                        item.status.code
                                                    )
                                                "
                                            >
                                                <i
                                                    :class="
                                                        statusIcon(
                                                            item.status.code
                                                        )
                                                    "
                                                    class="mr-1"
                                                ></i>
                                                {{ item.status.name }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- pagination -->
                    <div class="flex justify-center mt-4">
                        <pagination
                            :data="paginatedDeliveries"
                            @pagination-change-page="pageChanged"
                            :limit="5"
                            :classes="paginationClasses"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- สำหรับ Mobile: แสดงเป็นการ์ด -->
        <div v-if="isMobile" class="card-container">
            <div
                v-for="item in paginatedDeliveries.data"
                :key="item.id"
                class="card border p-4 mb-4 rounded shadow hover:shadow-green-500 transition-shadow duration-300"
                @click="goToEditPage(item.id)"
            >
                <div class="flex">
                    <!-- ห้องแรก: แสดงแค่สถานะ logo -->
                    <div
                        class="flex-shrink-0 flex items-center justify-center mr-4 me-4"
                    >
                        <i
                            :class="
                                statusIcon(item.status.code) +
                                ' text-3xl ' +
                                statusClass(item.status.code)
                            "
                        ></i>
                        <span :class="statusClass(item.status.code)">
                            {{ item.status.name }}
                        </span>
                    </div>
                    <!-- ห้องที่ 2: รายการอื่นๆ -->
                    <div class="flex-1 justify-items-start">
                        <div class="mb-2">
                            <strong>Mã số phiếu:</strong>
                            <span class="document-code">{{
                                item.document_code
                            }}</span>
                        </div>
                        <div class="mb-2">
                            <strong>Tiều đề:</strong> {{ item.title }}
                        </div>
                        <div class="mb-2">
                            <strong>Ngày lập:</strong>
                            {{ formatDate(item.created_date) }}
                        </div>
                        <div class="mb-2">
                            <strong>Người tạo:</strong>
                            <i class="fas fa-user text-blue-500"></i>
                            {{ item.creator?.full_name || "N/A" }}
                        </div>
                        <div class="mb-2">
                            <strong>Người nhận:</strong>
                            <template v-if="item.receiver">
                                <i class="fas fa-user text-green-500"></i>
                                {{ item.receiver.full_name }}
                            </template>
                            <template v-else>
                                <span class="text-gray-400">-</span>
                            </template>
                        </div>
                        <div class="mb-2" v-if="item.received_date">
                            <strong>Ngày nhận:</strong>
                            {{ formatDate(item.received_date) }}
                        </div>
                    </div>
                </div>
            </div>
            <!-- Mobile Pagination Card - แทนที่ pagination เดิม -->
            <div class="mobile-pagination-card" v-if="isMobile">
                <div class="pagination-info">
                    <span class="page-info"
                        >Trang {{ currentPage }} của
                        {{ paginatedDeliveries.last_page }}</span
                    >
                    <span class="record-info"
                        >{{ paginatedDeliveries.from }}-{{
                            paginatedDeliveries.to
                        }}
                        của {{ paginatedDeliveries.total }} bản ghi</span
                    >
                </div>

                <div class="pagination-controls">
                    <button
                        class="page-btn"
                        @click="pageChanged(1)"
                        :disabled="currentPage === 1"
                    >
                        <i class="fas fa-angle-double-left"></i>
                    </button>

                    <button
                        class="page-btn"
                        @click="pageChanged(currentPage - 1)"
                        :disabled="currentPage === 1"
                    >
                        <i class="fas fa-angle-left"></i>
                    </button>

                    <div class="current-page">{{ currentPage }}</div>

                    <button
                        class="page-btn"
                        @click="pageChanged(currentPage + 1)"
                        :disabled="
                            currentPage === paginatedDeliveries.last_page
                        "
                    >
                        <i class="fas fa-angle-right"></i>
                    </button>

                    <button
                        class="page-btn"
                        @click="pageChanged(paginatedDeliveries.last_page)"
                        :disabled="
                            currentPage === paginatedDeliveries.last_page
                        "
                    >
                        <i class="fas fa-angle-double-right"></i>
                    </button>
                </div>

                <!-- Quick jump สำหรับหน้าเยอะ -->
                <div
                    class="quick-jump"
                    v-if="paginatedDeliveries.last_page > 5"
                >
                    <span>Đi đến trang:</span>
                    <select
                        :value="currentPage"
                        @change="pageChanged(parseInt($event.target.value))"
                    >
                        <option
                            v-for="page in paginatedDeliveries.last_page"
                            :key="page"
                            :value="page"
                        >
                            {{ page }}
                        </option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import { Bootstrap5Pagination } from "laravel-vue-pagination";
import { useStore } from "../../Store/Auth";
import { usePermissions } from "../../Composables/usePermissions";
import Swal from "sweetalert2";
import PerfectScrollbar from "perfect-scrollbar";
import "perfect-scrollbar/css/perfect-scrollbar.css";

export default {
    setup() {
        const store = useStore();
        const { hasPermission } = usePermissions();
        return {
            store,
            hasPermission,
        };
    },
    name: "DanhsachHoso",
    components: {
        Pagination: Bootstrap5Pagination,
    },
    data() {
        return {
            investmentFilter: "all",
            investmentProjects: [],
            isLoading: false, // Add this line
            userRole: null,
            userStation: null,
            documentDeliveries: [],
            search: "",
            currentPage: 1,
            perPage: 15,
            isMobile: window.innerWidth < 768,
            selectedItems: [],
            paginationClasses: {
                ul: "flex list-none",
                li: "mx-1",
                a: "block px-3 py-2 bg-white border border-gray-300 rounded hover:bg-gray-100",
                active: "bg-green-500 text-white",
                disabled: "opacity-50 cursor-not-allowed",
            },
            ps: null, // Add this for PerfectScrollbar instance
            sortColumn: null,
            sortDirection: "asc", // 'asc' or 'desc'
            statusFilter: "all",
            statusOptions: [
                { code: "all", name: "Tất cả trạng thái" },
                { code: "creating", name: "Nháp" },
                { code: "sending", name: "Đã nộp" },
                { code: "received", name: "Đã nhận" },
                { code: "cancelled", name: "Hủy" },
            ],
            activeFilter: null,
            columnFilters: {
                document_code: "",
                title: "",
                creator: "",
                receiver: "",
                created_date: "",
                received_date: "",
            },
            uniqueValues: {
                station: [],
                investment_project: [],
                document_type: [],
                status: [],
            },
            selectedFilterValues: {
                station: [],
                investment_project: [],
                document_type: [],
                status: [],
            },
        };
    },
    computed: {
        isAllSelected() {
            return (
                this.paginatedDeliveries.data.length > 0 &&
                this.selectedItems.length ===
                    this.paginatedDeliveries.data.length
            );
        },
        filteredDeliveries() {
            let filtered = this.documentDeliveries.filter((item) => {
                // Global search (existing)
                const matchesSearch =
                    item.document_code
                        .toLowerCase()
                        .includes(this.search.toLowerCase()) ||
                    item.title
                        .toLowerCase()
                        .includes(this.search.toLowerCase()) ||
                    (item.creator?.full_name &&
                        item.creator.full_name
                            .toLowerCase()
                            .includes(this.search.toLowerCase())) ||
                    (item.receiver?.full_name &&
                        item.receiver.full_name
                            .toLowerCase()
                            .includes(this.search.toLowerCase()));

                // Status dropdown filter (existing)
                const matchesStatus =
                    this.statusFilter === "all" ||
                    item.status.code === this.statusFilter;

                // Investment filter (add this new condition)
                const matchesInvestment =
                    this.investmentFilter === "all" ||
                    item.investment_project === this.investmentFilter;

                // Column filters (new)
                const matchesColumnFilters =
                    // Mã số phiếu
                    (!this.columnFilters.document_code ||
                        (item.document_code &&
                            item.document_code
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.document_code.toLowerCase()
                                ))) &&
                    // Trạm (unique dropdown)
                    (this.selectedFilterValues.station.length === 0 ||
                        (item.station &&
                            this.selectedFilterValues.station.includes(
                                item.station
                            ))) &&
                    // Tiều đề
                    (!this.columnFilters.title ||
                        (item.title &&
                            item.title
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.title.toLowerCase()
                                ))) &&
                    // Vụ đầu tư (unique dropdown)
                    (this.selectedFilterValues.investment_project.length ===
                        0 ||
                        (item.investment_project &&
                            this.selectedFilterValues.investment_project.includes(
                                item.investment_project
                            ))) &&
                    // Loại hồ sơ (unique dropdown)
                    (this.selectedFilterValues.document_type.length === 0 ||
                        (item.document_type &&
                            this.selectedFilterValues.document_type.includes(
                                item.document_type
                            ))) &&
                    // Ngày lập
                    (!this.columnFilters.created_date ||
                        (item.created_date &&
                            this.formatDateForComparison(item.created_date) ===
                                this.formatDateForComparison(
                                    this.columnFilters.created_date
                                ))) &&
                    // Người tạo
                    (!this.columnFilters.creator ||
                        (item.creator?.full_name &&
                            item.creator.full_name
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.creator.toLowerCase()
                                ))) &&
                    // Người nhận
                    (!this.columnFilters.receiver ||
                        (item.receiver?.full_name &&
                            item.receiver.full_name
                                .toLowerCase()
                                .includes(
                                    this.columnFilters.receiver.toLowerCase()
                                ))) &&
                    // Ngày nhận
                    (!this.columnFilters.received_date ||
                        (item.received_date &&
                            this.formatDateForComparison(item.received_date) ===
                                this.formatDateForComparison(
                                    this.columnFilters.received_date
                                ))) &&
                    // Trạng thái (unique dropdown)
                    (this.selectedFilterValues.status.length === 0 ||
                        (item.status.code &&
                            this.selectedFilterValues.status.includes(
                                item.status.code
                            )));

                // Return true if it matches all filters
                return (
                    matchesSearch &&
                    matchesStatus &&
                    matchesInvestment &&
                    matchesColumnFilters
                );
            });
            // Apply sorting if a sort column is specified
            if (this.sortColumn) {
                filtered = [...filtered].sort((a, b) => {
                    // Get values to compare (handle nested properties like status.name)
                    let aValue = this.sortColumn.includes(".")
                        ? this.sortColumn
                              .split(".")
                              .reduce((obj, key) => obj?.[key], a)
                        : a[this.sortColumn];

                    let bValue = this.sortColumn.includes(".")
                        ? this.sortColumn
                              .split(".")
                              .reduce((obj, key) => obj?.[key], b)
                        : b[this.sortColumn];

                    // Handle nulls/undefined values
                    if (aValue === null || aValue === undefined) aValue = "";
                    if (bValue === null || bValue === undefined) bValue = "";

                    // Special handling for dates
                    if (
                        this.sortColumn === "created_date" ||
                        this.sortColumn === "received_date"
                    ) {
                        aValue = aValue ? new Date(aValue).getTime() : 0;
                        bValue = bValue ? new Date(bValue).getTime() : 0;
                    }

                    // Compare based on sort direction
                    if (this.sortDirection === "asc") {
                        return aValue > bValue ? 1 : aValue < bValue ? -1 : 0;
                    } else {
                        return aValue < bValue ? 1 : aValue > bValue ? -1 : 0;
                    }
                });
            }

            return filtered;
        },
        paginatedDeliveries() {
            const total = this.filteredDeliveries.length;
            const lastPage = Math.ceil(total / this.perPage) || 1;
            const start = (this.currentPage - 1) * this.perPage;
            const data = this.filteredDeliveries.slice(
                start,
                start + this.perPage
            );

            return {
                current_page: this.currentPage,
                data,
                first_page_url: "?page=1",
                from: total > 0 ? start + 1 : 0,
                last_page: lastPage,
                last_page_url: `?page=${lastPage}`,
                next_page_url:
                    this.currentPage < lastPage
                        ? `?page=${this.currentPage + 1}`
                        : null,
                path: "",
                per_page: this.perPage,
                prev_page_url:
                    this.currentPage > 1
                        ? `?page=${this.currentPage - 1}`
                        : null,
                to: start + data.length,
                total,
            };
        },
        //ນັບຈຳນວນສະຖານນ່ະການສົ່ງ,ມອບ,ໄດ້ຮັບ,ຍົກເລິກ
        statusCounts() {
            const counts = {
                all: this.documentDeliveries.length,
                creating: 0,
                sending: 0,
                received: 0,
                cancelled: 0,
            };

            this.documentDeliveries.forEach((item) => {
                if (item.status && item.status.code in counts) {
                    counts[item.status.code]++;
                }
            });

            return counts;
        },
    },
    methods: {
        // Add these methods to the methods section of your component
        saveFilterState() {
            // Create a deep copy of the filter state objects to avoid reference issues
            const filterState = {
                search: this.search,
                statusFilter: this.statusFilter,
                investmentFilter: this.investmentFilter,
                currentPage: this.currentPage,
                // Explicitly stringify each property of columnFilters
                columnFilters: {
                    document_code: this.columnFilters.document_code || "",
                    title: this.columnFilters.title || "",
                    creator: this.columnFilters.creator || "",
                    receiver: this.columnFilters.receiver || "",
                    created_date: this.columnFilters.created_date || "",
                    received_date: this.columnFilters.received_date || "",
                },
                // Explicitly copy each property of selectedFilterValues
                selectedFilterValues: {
                    station: [...(this.selectedFilterValues.station || [])],
                    investment_project: [
                        ...(this.selectedFilterValues.investment_project || []),
                    ],
                    document_type: [
                        ...(this.selectedFilterValues.document_type || []),
                    ],
                    status: [...(this.selectedFilterValues.status || [])],
                },
                sortColumn: this.sortColumn,
                sortDirection: this.sortDirection,
                // Add timestamp for potential expiration functionality
                timestamp: new Date().getTime(),
            };

            // console.log("Saving filter state:", JSON.stringify(filterState));
            localStorage.setItem(
                "danhsachHosoFilters",
                JSON.stringify(filterState)
            );
        },

        // Improved restoreFilterState method
        restoreFilterState() {
            const savedState = localStorage.getItem("danhsachHosoFilters");

            if (savedState) {
                try {
                    const filterState = JSON.parse(savedState);
                    // console.log("Restoring filter state:", filterState);

                    // Restore basic filters
                    this.search = filterState.search || "";
                    this.statusFilter = filterState.statusFilter || "all";
                    this.investmentFilter =
                        filterState.investmentFilter || "all";
                    this.currentPage = filterState.currentPage || 1;
                    this.sortColumn = filterState.sortColumn || null;
                    this.sortDirection = filterState.sortDirection || "asc";

                    // Restore column filters - check if exists first, then iterate through properties
                    if (filterState.columnFilters) {
                        // Initialize columnFilters with default empty values
                        this.columnFilters = {
                            document_code: "",
                            title: "",
                            creator: "",
                            receiver: "",
                            created_date: "",
                            received_date: "",
                        };

                        // Explicitly copy each property
                        Object.keys(filterState.columnFilters).forEach(
                            (key) => {
                                if (key in this.columnFilters) {
                                    this.columnFilters[key] =
                                        filterState.columnFilters[key];
                                }
                            }
                        );
                    }

                    // Restore selected filter values - check if exists first
                    if (filterState.selectedFilterValues) {
                        // Initialize selectedFilterValues with empty arrays
                        this.selectedFilterValues = {
                            station: [],
                            investment_project: [],
                            document_type: [],
                            status: [],
                        };

                        // Explicitly copy each array
                        Object.keys(filterState.selectedFilterValues).forEach(
                            (key) => {
                                if (
                                    key in this.selectedFilterValues &&
                                    Array.isArray(
                                        filterState.selectedFilterValues[key]
                                    )
                                ) {
                                    this.selectedFilterValues[key] = [
                                        ...filterState.selectedFilterValues[
                                            key
                                        ],
                                    ];
                                }
                            }
                        );
                    }
                } catch (error) {
                    console.error("Error restoring filter state:", error);
                    // If restoration fails, reset filters
                    this.resetAllFilters();
                }
            }
        },

        sortTable(column) {
            // If clicking the same column, toggle direction
            if (this.sortColumn === column) {
                this.sortDirection =
                    this.sortDirection === "asc" ? "desc" : "asc";
            } else {
                // If clicking a new column, set it as the sort column with 'asc' direction
                this.sortColumn = column;
                this.sortDirection = "asc";
            }

            // Reset to first page when sorting changes
            this.currentPage = 1;
            // ปิด active filter เมื่อมีการเรียงลำดับ
            this.activeFilter = null;
        },

        getSortIcon(column) {
            if (this.sortColumn !== column) return "fas fa-sort text-gray-300";
            return this.sortDirection === "asc"
                ? "fas fa-sort-up text-green-500"
                : "fas fa-sort-down text-green-500";
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
        fetchUserInfo() {
            axios
                .get("/api/user-info", {
                    headers: {
                        Authorization: "Bearer " + this.store.getToken,
                    },
                })
                .then((response) => {
                    const user = response.data;
                    // บันทึกข้อมูลสถานี
                    this.userStation = user.station;

                    // ดึงบทบาทตามตำแหน่ง
                    return axios.get(
                        `/api/get-role-by-position?position=${encodeURIComponent(
                            user.position
                        )}`,
                        {
                            headers: {
                                Authorization: "Bearer " + this.store.getToken,
                            },
                        }
                    );
                })
                .then((response) => {
                    this.userRole = response.data.role;
                })
                .catch((error) => {
                    console.error("เกิดข้อผิดพลาดในการดึงข้อมูลผู้ใช้:", error);
                });
        },

        formatDate(date) {
            return new Date(date).toLocaleDateString("vi-VN");
        },
        toggleSelectAll(event) {
            if (event.target.checked) {
                // Select all items on current page
                this.selectedItems = this.paginatedDeliveries.data.map(
                    (item) => item.id
                );
            } else {
                // Deselect all items
                this.selectedItems = [];
            }
        },
        // In the methods section

        async deleteSelected() {
            try {
                // Check if any items are selected
                if (this.selectedItems.length === 0) {
                    await Swal.fire({
                        title: "Thông báo",
                        text: "Vui lòng chọn ít nhất một hồ sơ để xóa",
                        icon: "warning",
                        confirmButtonText: "OK",
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: "btn btn-success px-4",
                        },
                    });
                    return;
                }

                // Confirm deletion
                const confirmResult = await Swal.fire({
                    title: "Xác nhận xóa",
                    text: `Bạn muốn xóa dữ liệu đã chọn. ${this.selectedItems.length} danh sách có hay không?`,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "OK",
                    cancelButtonText: "Cancel",
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: "btn btn-danger me-2",
                        cancelButton: "btn btn-outline-secondary",
                    },
                });

                if (!confirmResult.isConfirmed) {
                    return;
                }

                // Send delete request
                await axios.delete("/api/document-deliveries", {
                    data: { ids: this.selectedItems },
                    headers: {
                        Authorization: `Bearer ${this.store.getToken}`,
                    },
                });

                // Show success message
                await Swal.fire({
                    title: "Thành công",
                    text: "Đã được xóa thành công.",
                    icon: "success",
                    confirmButtonText: "OK",
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: "btn btn-success px-4",
                    },
                });

                // Reset selection and refresh data
                this.selectedItems = [];
                await this.fetchData();
            } catch (error) {
                console.error("Delete error:", error);

                if (error.response?.status === 401) {
                    // Handle unauthorized access
                    localStorage.removeItem("web_token");
                    localStorage.removeItem("web_user");
                    this.store.logout();
                    this.$router.push("/login");
                } else {
                    await Swal.fire({
                        title: "Lỗi",
                        text: "Không thể xóa tài liệu. Vui lòng thử lại.",

                        icon: "error",
                        confirmButtonText: "OK",
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: "btn btn-success px-4",
                        },
                    });
                }
            }
        },

        async fetchData() {
            try {
                // Log filter state when fetching data

                const response = await axios.get("/api/document-deliveries", {
                    params: {
                        search: this.search,
                        status:
                            this.statusFilter !== "all"
                                ? this.statusFilter
                                : undefined,
                        investment_project:
                            this.investmentFilter !== "all"
                                ? this.investmentFilter
                                : undefined,
                        per_page: 1000,
                        role: this.userRole,
                        station: this.userStation,
                        // You could add additional filter parameters here if your API supports it
                    },
                    headers: {
                        Authorization: "Bearer " + this.store.getToken,
                    },
                });

                this.documentDeliveries = response.data.data;
                this.extractInvestmentProjects();
            } catch (error) {
                console.error(error);
                if (error.response?.status === 401) {
                    localStorage.removeItem("web_token");
                    localStorage.removeItem("web_user");
                    this.store.logout();
                    this.$router.push("/login");
                }
            }
        },
        pageChanged(page) {
            this.currentPage = page;
        },
        handleResize() {
            this.isMobile = window.innerWidth < 768;
        },
        statusClass(status) {
            switch (status) {
                case "creating":
                    return "text-yellow-600";
                case "sending":
                    return "text-blue-600";
                case "received":
                    return "text-green-600";
                case "cancelled":
                    return "text-red-600";
                default:
                    return "";
            }
        },
        statusIcon(status) {
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
                    return "fas fa-info-circle";
            }
        },
        toggleSelectAll(event) {
            if (event.target.checked) {
                // Select all items displayed on the current page
                this.selectedItems = this.paginatedDeliveries.data.map(
                    (item) => item.id
                );
            } else {
                this.selectedItems = [];
            }
        },
        async goToEditPage(id) {
            try {
                // Save filter state before navigating
                this.saveFilterState();

                // Set the loading state to true
                this.isLoading = true;

                // Call API to check access rights
                const response = await axios.get(
                    `/api/document-deliveries/${id}/check-access`,
                    {
                        headers: {
                            Authorization: `Bearer ${this.store.getToken}`,
                        },
                    }
                );

                // Hide loading indicator
                this.isLoading = false;

                // If user has access
                if (response.data.hasAccess) {
                    this.$router.push(`/Danhsachhoso/${id}`);
                } else {
                    // If no access
                    this.$router.push("/unauthorized");
                }
            } catch (error) {
                // Error handling code (unchanged)
                console.error("Error checking document access:", error);
                this.isLoading = false;

                if (error.response?.status === 401) {
                    localStorage.removeItem("web_token");
                    localStorage.removeItem("web_user");
                    this.store.logout();
                    this.$router.push("/login");
                } else {
                    Swal.fire({
                        title: "Lỗi",
                        text: "Không thể kiểm tra quyền truy cập. Vui lòng thử lại sau.",
                        icon: "error",
                        confirmButtonText: "OK",
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: "btn btn-success px-4",
                        },
                    });
                    this.$router.push("/unauthorized");
                }
            }
        },
        extractInvestmentProjects() {
            // Get unique investment projects
            const projects = [
                ...new Set(
                    this.documentDeliveries
                        .map((item) => item.investment_project)
                        .filter((project) => project) // Remove null/empty values
                ),
            ];

            this.investmentProjects = projects;
        },
        // Existing methods...

        // Add new methods for column filtering
        toggleFilter(column) {
            this.activeFilter = this.activeFilter === column ? null : column;

            // If it's one of our dropdown columns and we're opening the filter
            if (
                [
                    "station",
                    "investment_project",
                    "document_type",
                    "status",
                ].includes(column) &&
                this.activeFilter === column
            ) {
                this.updateUniqueValues(column);
            }
        },

        updateUniqueValues(column) {
            if (column === "station") {
                this.uniqueValues.station = [
                    ...new Set(
                        this.documentDeliveries
                            .map((item) => item.station)
                            .filter(Boolean) // Remove null/undefined values
                    ),
                ];
            } else if (column === "investment_project") {
                this.uniqueValues.investment_project = [
                    ...new Set(
                        this.documentDeliveries
                            .map((item) => item.investment_project)
                            .filter(Boolean)
                    ),
                ];
            } else if (column === "document_type") {
                this.uniqueValues.document_type = [
                    ...new Set(
                        this.documentDeliveries
                            .map((item) => item.document_type)
                            .filter(Boolean)
                    ),
                ];
            } else if (column === "status") {
                // Status is a bit different because it's an object
                const uniqueStatusCodes = [
                    ...new Set(
                        this.documentDeliveries
                            .map((item) => item.status.code)
                            .filter(Boolean)
                    ),
                ];

                this.uniqueValues.status = uniqueStatusCodes.map((code) => {
                    const statusObj = this.documentDeliveries.find(
                        (item) => item.status.code === code
                    )?.status;
                    return statusObj
                        ? { code: statusObj.code, name: statusObj.name }
                        : { code, name: code };
                });
            }
        },

        resetFilter(column) {
            if (
                [
                    "station",
                    "investment_project",
                    "document_type",
                    "status",
                ].includes(column)
            ) {
                this.selectedFilterValues[column] = [];

                // If resetting investment_project, also reset the top filter
                if (column === "investment_project") {
                    this.investmentFilter = "all";
                }
            } else {
                this.columnFilters[column] = "";
            }
            this.currentPage = 1;
        },

        applyFilter(column) {
            // If applying investment_project filter, update the top filter
            if (column === "investment_project") {
                if (this.selectedFilterValues.investment_project.length === 1) {
                    this.investmentFilter =
                        this.selectedFilterValues.investment_project[0];
                } else {
                    this.investmentFilter = "all";
                }
            }

            this.activeFilter = null; // Close the dropdown
            this.currentPage = 1; // Reset to first page
        },

        resetAllFilters() {
            // Clear saved filter state from localStorage
            localStorage.removeItem("danhsachHosoFilters");
            // Reset global search
            this.search = "";

            // Reset status filter
            this.statusFilter = "all";

            // Reset all column filters
            Object.keys(this.columnFilters).forEach((key) => {
                this.columnFilters[key] = "";
            });

            // Reset all dropdown filters
            Object.keys(this.selectedFilterValues).forEach((key) => {
                this.selectedFilterValues[key] = [];
            });

            // Reset active filter
            this.activeFilter = null;

            // Reset to first page
            this.currentPage = 1;
            // Reset sort
            this.sortColumn = null;
            this.sortDirection = "asc";

            // Refresh data
            this.fetchData();
        },

        formatDateForComparison(date) {
            if (!date) return "";
            const d = new Date(date);
            const year = d.getFullYear();
            const month = (d.getMonth() + 1).toString().padStart(2, "0");
            const day = d.getDate().toString().padStart(2, "0");
            return `${year}-${month}-${day}`;
        },
    },
    // Add the navigation guards here, between methods and created()
    beforeRouteLeave(to, from, next) {
        // Save filter state before leaving the route
        this.saveFilterState();
        next();
    },

    beforeRouteEnter(to, from, next) {
        next((vm) => {
            // Component instance is now available as 'vm'
            // Always try to restore filter state when entering the route
            vm.restoreFilterState();

            // Apply filters after restoration with a slight delay
            setTimeout(() => {
                // Refresh data with restored filters
                vm.fetchData();
            }, 200);
        });
    },
    created() {
        // Initialize the filter objects first to avoid undefined errors
        this.columnFilters = {
            document_code: "",
            title: "",
            creator: "",
            receiver: "",
            created_date: "",
            received_date: "",
        };

        this.selectedFilterValues = {
            station: [],
            investment_project: [],
            document_type: [],
            status: [],
        };
        // This runs before the component is mounted
        this.restoreFilterState();
        // Log to verify restoration worked
    },
    mounted() {
        // First restore filter state
        this.restoreFilterState();

        // Then fetch user info and data with the restored filters
        this.fetchUserInfo();

        // Slight delay to ensure filters are fully restored before fetching data
        setTimeout(() => {
            this.fetchData();

            this.$nextTick(() => {
                // Initialize after the DOM is updated
                this.initPerfectScrollbar();

                // Initialize unique values for dropdown filters
                this.updateUniqueValues("station");
                this.updateUniqueValues("investment_project");
                this.updateUniqueValues("document_type");
                this.updateUniqueValues("status");
            });
        }, 100);

        window.addEventListener("resize", this.handleResize);
    },
    updated() {
        // Update scrollbar after component updates
        this.updateScrollbar();
    },
    beforeUnmount() {
        window.removeEventListener("resize", this.handleResize);
        if (this.ps) {
            this.ps.destroy();
            this.ps = null;
        }
    },
    watch: {
        sortColumn() {
            this.updateScrollbar();
        },
        sortDirection() {
            this.updateScrollbar();
        },
        // Add deep watcher for column filters
        columnFilters: {
            handler() {
                this.currentPage = 1;
                // Don't fetch data immediately as it can cause too many requests
                // Just save the state
                this.saveFilterState();
            },
            deep: true,
        },
        // Deep watcher for selected filter values
        selectedFilterValues: {
            handler() {
                this.currentPage = 1;
                this.saveFilterState();
            },
            deep: true,
        },
        statusFilter() {
            this.currentPage = 1; // Reset to first page when filter changes
        },
        investmentFilter() {
            this.currentPage = 1; // Reset to first page when filter changes
        },
        search() {
            this.currentPage = 1; // Reset to first page when search changes
        },
        documentDeliveries() {
            // This will recalculate statusCounts when the document data changes
            this.$forceUpdate();
        },
        // Add this new watch
        "selectedFilterValues.investment_project": {
            handler(newValue) {
                if (newValue.length === 1) {
                    // When exactly one value is selected in the column filter
                    this.investmentFilter = newValue[0];
                } else if (newValue.length === 0) {
                    // When the column filter is cleared
                    this.investmentFilter = "all";
                }
            },
        },

        // Add watchers for scrollbar updates
        search() {
            this.currentPage = 1;
            this.updateScrollbar();
        },
        statusFilter() {
            this.currentPage = 1;
            this.updateScrollbar();
        },
        paginatedDeliveries: {
            handler() {
                this.updateScrollbar();
            },
            deep: true,
        },
        filteredDeliveries: {
            handler() {
                this.updateScrollbar();
            },
            deep: true,
        },

        // Also watch the top filter to sync with column filter
        investmentFilter: {
            handler(newValue) {
                if (newValue === "all") {
                    // Clear the column filter when "all" is selected
                    if (
                        this.selectedFilterValues.investment_project.length > 0
                    ) {
                        this.selectedFilterValues.investment_project = [];
                    }
                } else if (
                    !this.selectedFilterValues.investment_project.includes(
                        newValue
                    )
                ) {
                    // Set column filter to match the top filter
                    this.selectedFilterValues.investment_project = [newValue];
                }
                this.currentPage = 1; // Reset to first page when filter changes
            },
        },
    },
};
</script>

<style scoped>
/* ...existing styles... */
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
    border-radius: 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: box-shadow 0.3s ease;
    overflow: hidden;
}

.card::before {
    border-radius: 1.5rem 1.5rem 0 0;
    content: "";
    position: absolute;

    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #10b981 0%, #059669 50%, #10b981 100%);
    z-index: 1;
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
.table-auto {
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
}
.table-auto th,
.table-auto td {
    text-align: left;
    white-space: nowrap;
    overflow: visible;
    text-overflow: clip;
    word-wrap: break-word;
    font-size: 0.875rem;
    border: none; /* Remove all borders */
    border-bottom: 1px solid #e5e7eb;
}
.table-auto th {
    background-color: #e7e7e7;
    border: none; /* Remove all borders */
    padding: 0.75rem;
    vertical-align: top;
    cursor: pointer;
    user-select: none;
    position: relative;
}
.table-auto th .d-flex {
    display: flex;
    align-items: center;
    gap: 4px;
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

/* Add to <style> section */

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

/* ... existing styles ... */

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

/* Add these new styles for filters */

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

/* Filter icon styling */
.filter-btn {
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
    margin-left: 4px;
    color: #6c757d;
    font-size: 0.75rem;
    vertical-align: middle;
}

.filter-btn:hover {
    color: #198754;
}

.fa-filter {
    font-size: 0.75rem;
}

.text-green-500 {
    color: #10b981;
}

/* Filter dropdown positioning */
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
    padding: 12px;
    background-color: white;
    border-radius: 8px;
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

/* Checkbox styling */
input[type="checkbox"] {
    cursor: pointer;
}

.table-auto th {
    position: relative;
    min-width: 150px;
    white-space: nowrap;
}

.table-auto th:first-child {
    min-width: auto;
}

/* Add more spacing to allow for filter icons */
.px-4.py-2 {
    position: relative;
}

/* Table container with fixed header */
.table-container-wrapper {
    position: relative;
    width: 100%;
    margin-bottom: 1.5rem;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1),
        0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.table-scroll-container {
    position: relative;
    min-height: 400px; /* Minimum height added */
    max-height: calc(100vh - 240px);
    overflow: auto;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    background-color: #ffffff;
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

/* Ensure proper spacing in pagination */
.pagination-wrapper {
    position: relative;
    margin-top: 1rem;
    width: 100%;
}

/* Mobile responsive styles */
@media (max-width: 768px) {
    .table-scroll-container {
        max-height: calc(100vh - 300px);
    }
    .justify-items-start {
        font-size: 12px;
    }
}

.filter-btn {
    position: relative;
    z-index: 100;
}

/* Add this to your existing <style> section */
.document-code {
    color: #3490dc; /* Blue color */
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s ease;
}

.document-code:hover {
    color: #2779bd; /* Darker blue on hover */
    text-decoration: underline;
}

/* Add hover styling for the entire cell */
.clickable-cell {
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.clickable-cell:hover {
    background-color: #ebf5ff; /* Light blue background on hover */
}

/* Add these styles to separate sort and filter functionality */
.column-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    position: relative;
}

.header-content {
    display: flex;
    align-items: center;
    cursor: pointer;
    flex-grow: 1;
}

.sort-icon {
    margin-left: 4px;
}

/* .filter-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-left: 8px;
    z-index: 100;
} */

/* Prevent the parent element from capturing filter button clicks */
.filter-btn:focus {
    outline: none;
}

/* Make the popup position correctly */
.filter-dropdown {
    position: absolute;
    right: 0;
    top: 100%;
    z-index: 1000;
}
/* Mobile Pagination - Simple & Clean */
.mobile-pagination-card {
    background: white;
    border-radius: 12px;
    padding: 16px;
    margin-top: 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border: 1px solid #e5e7eb;
}

.pagination-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    font-size: 14px;
    color: #6b7280;
}

.page-info {
    font-weight: 600;
    color: #374151;
}

.pagination-controls {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
}

.page-btn {
    width: 40px;
    height: 40px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: white;
    color: #6b7280;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    cursor: pointer;
}

.page-btn:hover:not(:disabled) {
    border-color: #10b981;
    color: #10b981;
    transform: translateY(-1px);
}

.page-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.current-page {
    background: #10b981;
    color: white;
    padding: 8px 16px;
    border-radius: 8px;
    font-weight: 600;
    margin: 0 8px;
    min-width: 40px;
    text-align: center;
}

.quick-jump {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding-top: 12px;
    border-top: 1px solid #e5e7eb;
    font-size: 14px;
    color: #6b7280;
}

.quick-jump select {
    border: 1px solid #d1d5db;
    border-radius: 6px;
    padding: 4px 8px;
    background: white;
    color: #374151;
    cursor: pointer;
}

.quick-jump select:focus {
    outline: none;
    border-color: #10b981;
}

@media (max-width: 480px) {
    .pagination-info {
        flex-direction: column;
        gap: 4px;
        text-align: center;
    }

    .page-btn {
        width: 36px;
        height: 36px;
    }

    .current-page {
        padding: 6px 12px;
        margin: 0 4px;
    }
}
</style>
