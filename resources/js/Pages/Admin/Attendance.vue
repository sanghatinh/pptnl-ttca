<template>
    <!-- Loading starts -->
    <div id="loading-wrapper" v-if="isLoading">
        <div class="spinner-border" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>

    <!-- Desktop Controls -->
    <div class="desktop-controls-container mt-3" v-if="!isMobile">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-clock me-2"></i>
                    Quản lý chấm công
                    <span class="badge bg-primary ms-2"
                        >{{ totalRecords }} records</span
                    >
                </h5>
            </div>
            <div class="row align-items-center mt-2">
                <div class="row mb-3 align-items-center">
                    <div class="col-md-6">
                        <div class="d-flex gap-2 mx-3">
                            <button
                                type="button"
                                @click="openModal()"
                                class="button-30-save d-flex align-items-center"
                            >
                                <i class="bx bxs-time-five me-1"></i>
                                <span>Chấm công ngày hôm nay</span>
                            </button>

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
                    </div>

                    <div class="col-md-6 d-flex justify-content-end">
                        <div class="search-container">
                            <div class="input-group">
                                <span
                                    class="input-group-text bg-white border-end-0"
                                >
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Search attendance..."
                                    class="form-control border-start-0 search-input"
                                    aria-label="Search"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                    <!-- For Desktop: Table View -->
                    <div v-if="!isMobile" class="table-container">
                        <span
                            class="reset-all-filters-btn"
                            title="Reset all filters"
                            @click="resetAllFilters"
                        >
                            <i class="fas fa-redo-alt"></i>
                        </span>
                        <div
                            class="table-scroll-container"
                            ref="tableScrollContainer"
                        >
                            <table class="table table-hover table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>
                                            Ngày
                                            <button
                                                class="filter-btn"
                                                @click="toggleFilter('date')"
                                            >
                                                <i class="fas fa-filter"></i>
                                            </button>
                                            <div
                                                v-if="activeFilter === 'date'"
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                                style="min-width: 320px"
                                            >
                                                <div class="mb-2">
                                                    <label
                                                        class="form-label mb-1"
                                                        >Từ ngày</label
                                                    >
                                                    <input
                                                        type="date"
                                                        v-model="
                                                            columnFilters.start_date
                                                        "
                                                        class="form-control"
                                                    />
                                                </div>
                                                <div class="mb-2">
                                                    <label
                                                        class="form-label mb-1"
                                                        >Đến ngày</label
                                                    >
                                                    <input
                                                        type="date"
                                                        v-model="
                                                            columnFilters.end_date
                                                        "
                                                        class="form-control"
                                                    />
                                                </div>
                                                <div class="d-flex gap-2 mt-2">
                                                    <button
                                                        class="btn btn-sm btn-success"
                                                        @click="
                                                            applyDateRangeFilter
                                                        "
                                                    >
                                                        <i
                                                            class="fas fa-check"
                                                        ></i>
                                                        Áp dụng
                                                    </button>
                                                    <button
                                                        class="btn btn-sm btn-secondary"
                                                        @click="
                                                            resetDateRangeFilter
                                                        "
                                                    >
                                                        <i
                                                            class="fas fa-redo-alt"
                                                        ></i>
                                                        Reset
                                                    </button>
                                                </div>
                                            </div>
                                        </th>
                                        <th>
                                            Trạm
                                            <button
                                                @click="toggleFilter('station')"
                                                class="filter-btn"
                                            >
                                                <i class="fas fa-filter"></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter === 'station'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <div
                                                    class="max-h-40 overflow-y-auto"
                                                >
                                                    <div
                                                        v-for="station in uniqueValues.station"
                                                        :key="station"
                                                        class="form-check"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            :id="
                                                                'station-' +
                                                                station
                                                            "
                                                            :value="station"
                                                            v-model="
                                                                selectedFilterValues.station
                                                            "
                                                            class="form-check-input"
                                                        />
                                                        <label
                                                            :for="
                                                                'station-' +
                                                                station
                                                            "
                                                            class="form-check-label"
                                                        >
                                                            {{
                                                                getStationName(
                                                                    station
                                                                )
                                                            }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <div
                                                    class="d-flex justify-content-between mt-2"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'station'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-outline-secondary"
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
                                        <th>
                                            Full Name
                                            <button
                                                @click="
                                                    toggleFilter('full_name')
                                                "
                                                class="filter-btn"
                                            >
                                                <i class="fas fa-filter"></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter === 'full_name'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="text"
                                                    v-model="
                                                        columnFilters.full_name
                                                    "
                                                    class="form-control form-control-sm mb-2"
                                                    placeholder="Filter by name..."
                                                />
                                                <div
                                                    class="d-flex justify-content-between mt-2"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'full_name'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-outline-secondary"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'full_name'
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
                                            Chức vụ
                                            <button
                                                @click="
                                                    toggleFilter('position')
                                                "
                                                class="filter-btn"
                                            >
                                                <i class="fas fa-filter"></i>
                                            </button>
                                            <div
                                                v-if="
                                                    activeFilter === 'position'
                                                "
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <div
                                                    class="max-h-40 overflow-y-auto"
                                                >
                                                    <div
                                                        v-for="position in uniqueValues.position"
                                                        :key="position"
                                                        class="form-check"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            :id="
                                                                'position-' +
                                                                position
                                                            "
                                                            :value="position"
                                                            v-model="
                                                                selectedFilterValues.position
                                                            "
                                                            class="form-check-input"
                                                        />
                                                        <label
                                                            :for="
                                                                'position-' +
                                                                position
                                                            "
                                                            class="form-check-label"
                                                        >
                                                            {{
                                                                getPositionName(
                                                                    position
                                                                )
                                                            }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <div
                                                    class="d-flex justify-content-between mt-2"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'position'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-outline-secondary"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter(
                                                                'position'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                        </th>
                                        <th>CheckIn-Buổi Sáng</th>
                                        <th>Ghi chú Buổi Sáng</th>
                                        <th>CheckIn-Buổi Chiều</th>
                                        <th>Ghi chú Buổi Chiều</th>
                                        <th>
                                            Status
                                            <button
                                                @click="toggleFilter('status')"
                                                class="filter-btn"
                                            >
                                                <i class="fas fa-filter"></i>
                                            </button>
                                            <div
                                                v-if="activeFilter === 'status'"
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <div
                                                    class="max-h-40 overflow-y-auto"
                                                >
                                                    <div
                                                        v-for="status in uniqueValues.status"
                                                        :key="status"
                                                        class="form-check"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            :id="
                                                                'status-' +
                                                                status
                                                            "
                                                            :value="status"
                                                            v-model="
                                                                selectedFilterValues.status
                                                            "
                                                            class="form-check-input"
                                                        />
                                                        <label
                                                            :for="
                                                                'status-' +
                                                                status
                                                            "
                                                            class="form-check-label"
                                                        >
                                                            {{
                                                                statusText(
                                                                    status
                                                                )
                                                            }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <div
                                                    class="d-flex justify-content-between mt-2"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter(
                                                                'status'
                                                            )
                                                        "
                                                        class="btn btn-sm btn-outline-secondary"
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
                                        <th>Xem maps</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(log, index) in logs"
                                        :key="log.id"
                                        class="desktop-row clickable-row"
                                        @click="openModal(log)"
                                    >
                                        <td>
                                            {{
                                                (paginationData.from || 0) +
                                                index
                                            }}
                                        </td>
                                        <td>{{ formatDate(log.date) }}</td>
                                        <td>
                                            <span
                                                :class="
                                                    getStationBadgeClass(
                                                        log.user?.station
                                                    )
                                                "
                                            >
                                                <i
                                                    :class="
                                                        getStationIcon(
                                                            log.user?.station
                                                        )
                                                    "
                                                ></i>
                                                {{
                                                    getStationName(
                                                        log.user?.station
                                                    )
                                                }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ log.user?.full_name || "N/A" }}
                                        </td>
                                        <td>
                                            <span
                                                :class="
                                                    getPositionBadgeClass(
                                                        log.user?.position
                                                    )
                                                "
                                            >
                                                <i
                                                    :class="
                                                        getPositionIcon(
                                                            log.user?.position
                                                        )
                                                    "
                                                ></i>
                                                {{
                                                    getPositionName(
                                                        log.user?.position
                                                    )
                                                }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div
                                                v-if="log.photo_morning"
                                                class="checkin-info"
                                            >
                                                <img
                                                    :src="
                                                        getCloudinaryUrl(
                                                            log.photo_morning
                                                        )
                                                    "
                                                    class="checkin-photo"
                                                    alt="Check-in sáng"
                                                    @click.stop="
                                                        viewPhoto(
                                                            log.photo_morning
                                                        )
                                                    "
                                                />
                                                <div class="checkin-time">
                                                    {{
                                                        formatTime(
                                                            log.checkin_morning
                                                        )
                                                    }}
                                                </div>
                                            </div>
                                            <span v-else class="text-gray-400"
                                                >-</span
                                            >
                                        </td>
                                        <td>
                                            <div class="note-display">
                                                {{ log.note_morning || "-" }}
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div
                                                v-if="log.photo_evening"
                                                class="checkin-info"
                                            >
                                                <img
                                                    :src="
                                                        getCloudinaryUrl(
                                                            log.photo_evening
                                                        )
                                                    "
                                                    class="checkin-photo"
                                                    alt="Check-in chiều"
                                                    @click.stop="
                                                        viewPhoto(
                                                            log.photo_evening
                                                        )
                                                    "
                                                />
                                                <div class="checkin-time">
                                                    {{
                                                        formatTime(
                                                            log.checkin_evening
                                                        )
                                                    }}
                                                </div>
                                            </div>
                                            <span v-else class="text-gray-400"
                                                >-</span
                                            >
                                        </td>
                                        <td>
                                            <div class="note-display">
                                                {{ log.note_evening || "-" }}
                                            </div>
                                        </td>
                                        <td>
                                            <span
                                                :class="
                                                    getStatusBadgeClass(
                                                        log.status
                                                    )
                                                "
                                            >
                                                <i
                                                    :class="
                                                        getStatusIcon(
                                                            log.status
                                                        )
                                                    "
                                                ></i>
                                                {{ statusText(log.status) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button
                                                v-if="hasLocationData(log)"
                                                @click.stop="viewMap(log)"
                                                class="btn btn-sm btn-info"
                                                title="Xem vị trí trên bản đồ"
                                            >
                                                <i
                                                    class="fas fa-map-marker-alt"
                                                ></i>
                                            </button>
                                            <span v-else class="text-gray-400"
                                                >-</span
                                            >
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="pagination-card">
                            <pagination
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
    </div>

    <!-- Mobile Controls -->
    <div class="mobile-controls-container mt-3" v-if="isMobile">
        <div class="mobile-header-row">
            <div class="mobile-search-section">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search attendance..."
                        class="form-control border-start-0 search-input"
                        aria-label="Search"
                    />
                </div>
            </div>
            <div class="mobile-action-section">
                <button
                    type="button"
                    @click="openModal()"
                    class="btn-mobile-add"
                >
                    <i class="bx bxs-time-five"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Filter Section -->
        <div class="mobile-filter-row">
            <div class="mobile-filter-left">
                <div class="filter-dropdown-container">
                    <select
                        v-model="selectedMobileStatus"
                        class="mobile-role-select"
                        @change="applyMobileStatusFilter"
                    >
                        <option value="">All Status</option>
                        <option value="full_day">Đầy đủ</option>
                        <option value="morning_only">Chỉ sáng</option>
                        <option value="evening_only">Chỉ chiều</option>
                        <option value="no_checkin">Chưa chấm</option>
                    </select>
                </div>
            </div>
            <div class="mobile-filter-right">
                <button
                    @click="showMobileFilterModal = true"
                    class="mobile-filter-btn"
                >
                    <i class="fas fa-filter"></i>
                </button>
                <button @click="resetAllFilters" class="mobile-filter-btn">
                    <i class="fas fa-redo-alt me-1"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Filter Modal -->
    <div
        v-if="showMobileFilterModal"
        class="mobile-filter-modal-overlay"
        @click="showMobileFilterModal = false"
    >
        <div class="mobile-filter-modal" @click.stop>
            <div class="modal-header">
                <h5 class="modal-title">Filter Options</h5>
                <button
                    @click="showMobileFilterModal = false"
                    class="btn-close"
                >
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body">
                <!-- Date Filter -->
                <div class="filter-section">
                    <label class="filter-label">Từ ngày</label>
                    <input
                        type="date"
                        v-model="columnFilters.start_date"
                        class="form-control mb-2"
                    />
                    <label class="filter-label">Đến ngày</label>
                    <input
                        type="date"
                        v-model="columnFilters.end_date"
                        class="form-control"
                    />
                </div>

                <!-- Station Filter -->
                <div class="filter-section">
                    <label class="filter-label">Trạm</label>
                    <div class="checkbox-container">
                        <div
                            v-for="station in stations"
                            :key="station.ma_don_vi"
                            class="form-check"
                        >
                            <input
                                type="checkbox"
                                class="form-check-input"
                                :id="'mobile-station-' + station.ma_don_vi"
                                :value="station.ma_don_vi"
                                v-model="selectedFilterValues.station"
                            />
                            <label
                                class="form-check-label"
                                :for="'mobile-station-' + station.ma_don_vi"
                            >
                                {{ station.Name }}
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Position Filter -->
                <div class="filter-section">
                    <label class="filter-label">Chức vụ</label>
                    <div class="checkbox-container">
                        <div
                            v-for="position in positions"
                            :key="position.id_position"
                            class="form-check"
                        >
                            <input
                                type="checkbox"
                                class="form-check-input"
                                :id="'mobile-pos-' + position.id_position"
                                :value="position.id_position"
                                v-model="selectedFilterValues.position"
                            />
                            <label
                                class="form-check-label"
                                :for="'mobile-pos-' + position.id_position"
                            >
                                {{ position.position }}
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button @click="applyMobileFilters" class="btn btn-primary">
                    <i class="fas fa-check me-1"></i>
                    Apply Filters
                </button>
            </div>
        </div>
    </div>

    <!-- For Mobile: Card View -->
    <div v-if="isMobile" class="card-container">
        <div
            v-for="(log, index) in logs"
            :key="log.id"
            class="attendance-card mb-3 p-3 rounded border-0 clickable-card"
            @click="openModal(log)"
        >
            <div
                class="card-header d-flex justify-content-between align-items-center mb-2"
            >
                <div class="attendance-index fw-bold">
                    <span class="badge bg-label-primary rounded-pill"
                        >#{{ (paginationData.from || 0) + index }}</span
                    >
                </div>
                <div class="attendance-date">
                    <strong>{{ formatDate(log.date) }}</strong>
                </div>
            </div>

            <div class="attendance-info">
                <div class="info-section mb-3">
                    <div class="info-item">
                        <strong>Tên:</strong>
                        {{ log.user?.full_name || "N/A" }}
                    </div>
                    <div class="info-item">
                        <strong>Trạm:</strong>
                        <span :class="getStationBadgeClass(log.user?.station)">
                            <i :class="getStationIcon(log.user?.station)"></i>
                            {{ getStationName(log.user?.station) }}
                        </span>
                    </div>
                    <div class="info-item">
                        <strong>Chức vụ:</strong>
                        <span
                            :class="getPositionBadgeClass(log.user?.position)"
                        >
                            <i :class="getPositionIcon(log.user?.position)"></i>
                            {{ getPositionName(log.user?.position) }}
                        </span>
                    </div>
                </div>

                <div class="checkin-section mb-3">
                    <div class="row">
                        <div class="col-6">
                            <div class="checkin-morning">
                                <h6 class="text-warning">
                                    <i class="fas fa-sun"></i> Buổi sáng
                                </h6>
                                <div
                                    v-if="log.photo_morning"
                                    class="mobile-checkin-info"
                                >
                                    <img
                                        :src="
                                            getCloudinaryUrl(log.photo_morning)
                                        "
                                        class="mobile-checkin-photo"
                                        alt="Check-in sáng"
                                        @click.stop="
                                            viewPhoto(log.photo_morning)
                                        "
                                    />
                                    <div class="mobile-checkin-time">
                                        {{ formatTime(log.checkin_morning) }}
                                    </div>
                                    <div
                                        class="mobile-note"
                                        v-if="log.note_morning"
                                    >
                                        <small>{{ log.note_morning }}</small>
                                    </div>
                                </div>
                                <div v-else class="text-muted">
                                    <i class="fas fa-times-circle"></i> Chưa
                                    chấm
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="checkin-evening">
                                <h6 class="text-info">
                                    <i class="fas fa-moon"></i> Buổi chiều
                                </h6>
                                <div
                                    v-if="log.photo_evening"
                                    class="mobile-checkin-info"
                                >
                                    <img
                                        :src="
                                            getCloudinaryUrl(log.photo_evening)
                                        "
                                        class="mobile-checkin-photo"
                                        alt="Check-in chiều"
                                        @click.stop="
                                            viewPhoto(log.photo_evening)
                                        "
                                    />
                                    <div class="mobile-checkin-time">
                                        {{ formatTime(log.checkin_evening) }}
                                    </div>
                                    <div
                                        class="mobile-note"
                                        v-if="log.note_evening"
                                    >
                                        <small>{{ log.note_evening }}</small>
                                    </div>
                                </div>
                                <div v-else class="text-muted">
                                    <i class="fas fa-times-circle"></i> Chưa
                                    chấm
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="status-actions d-flex justify-content-between align-items-center"
                >
                    <span :class="getStatusBadgeClass(log.status)">
                        <i :class="getStatusIcon(log.status)"></i>
                        {{ statusText(log.status) }}
                    </span>
                    <button
                        v-if="hasLocationData(log)"
                        @click.stop="viewMap(log)"
                        class="btn btn-sm btn-info"
                    >
                        <i class="fas fa-map-marker-alt"></i> Maps
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Pagination Card -->
        <div class="mobile-pagination-card" v-if="isMobile">
            <div class="pagination-info">
                <span class="page-info"
                    >Trang {{ currentPage }} của
                    {{ paginationData.last_page || 1 }}</span
                >
                <span class="record-info"
                    >{{ paginationData.from || 0 }}-{{
                        paginationData.to || 0
                    }}
                    của {{ totalRecords }} bản ghi</span
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
                    :disabled="currentPage === (paginationData.last_page || 1)"
                >
                    <i class="fas fa-angle-right"></i>
                </button>

                <button
                    class="page-btn"
                    @click="pageChanged(paginationData.last_page || 1)"
                    :disabled="currentPage === (paginationData.last_page || 1)"
                >
                    <i class="fas fa-angle-double-right"></i>
                </button>
            </div>

            <div class="quick-jump" v-if="(paginationData.last_page || 1) > 5">
                <span>Đi đến trang:</span>
                <select
                    :value="currentPage"
                    @change="pageChanged(parseInt($event.target.value))"
                >
                    <option
                        v-for="page in paginationData.last_page || 1"
                        :key="page"
                        :value="page"
                    >
                        {{ page }}
                    </option>
                </select>
            </div>
        </div>
    </div>

    <!-- Export Modal -->
    <!-- Export Modal -->
    <div
        class="modal fade"
        id="exportModal"
        tabindex="-1"
        aria-labelledby="exportModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title" id="exportModalLabel">
                        <i class="fas fa-file-excel text-white me-2"></i>
                        Export dữ liệu chấm công
                    </h5>
                    <button
                        type="button"
                        class="btn-close"
                        @click="closeExportModal"
                    ></button>
                </div>
                <div class="modal-body">
                    <div class="d-grid gap-2">
                        <button
                            class="btn btn-outline-success"
                            @click="exportToExcelCurrentPage"
                        >
                            <i class="fas fa-file-excel"></i> Export trang hiện
                            tại
                        </button>
                        <button
                            class="btn btn-success"
                            @click="exportToExcelAllPages"
                        >
                            <i class="fas fa-file-excel"></i> Export tất cả bản
                            ghi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Photo View Modal -->
    <div
        v-if="showPhotoModal"
        class="photo-modal-overlay"
        @click="closePhotoModal"
    >
        <div class="photo-modal-content" @click.stop>
            <button @click="closePhotoModal" class="photo-modal-close">
                <i class="fas fa-times"></i>
            </button>
            <img
                :src="selectedPhoto"
                alt="Attendance Photo"
                class="photo-modal-image"
            />
        </div>
    </div>

    <!-- Map Modal -->
    <div v-if="showMapModal" class="map-modal-overlay" @click="closeMapModal">
        <div class="map-modal-content" @click.stop>
            <div class="map-modal-header">
                <h5>Vị trí chấm công</h5>
                <button @click="closeMapModal" class="btn-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div
                class="map-modal-body"
                style="overflow-y: auto; max-height: 60vh"
            >
                <div id="map" style="height: 400px; width: 100%">
                    <div class="location-info mt-3">
                        <div
                            v-if="
                                selectedLogForMap?.lat_morning &&
                                selectedLogForMap?.lng_morning
                            "
                        >
                            <strong>Vị trí buổi sáng:</strong>
                            {{ selectedLogForMap.lat_morning }},
                            {{ selectedLogForMap.lng_morning }}
                            <br />
                            <iframe
                                :src="`https://maps.google.com/maps?q=${selectedLogForMap.lat_morning},${selectedLogForMap.lng_morning}&z=16&output=embed`"
                                width="100%"
                                height="200"
                                style="
                                    border: 0;
                                    border-radius: 12px;
                                    margin-top: 8px;
                                "
                                allowfullscreen=""
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                            ></iframe>
                        </div>
                        <div
                            v-if="
                                selectedLogForMap?.lat_evening &&
                                selectedLogForMap?.lng_evening
                            "
                            style="margin-top: 16px"
                        >
                            <strong>Vị trí buổi chiều:</strong>
                            {{ selectedLogForMap.lat_evening }},
                            {{ selectedLogForMap.lng_evening }}
                            <br />
                            <iframe
                                :src="`https://maps.google.com/maps?q=${selectedLogForMap.lat_evening},${selectedLogForMap.lng_evening}&z=16&output=embed`"
                                width="100%"
                                height="200"
                                style="
                                    border: 0;
                                    border-radius: 12px;
                                    margin-top: 8px;
                                "
                                allowfullscreen=""
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                            ></iframe>
                        </div>
                        <div style="margin-top: 16px">
                            <a
                                href="https://www.google.com/maps/d/u/0/viewer?mid=1NRGygQgjZM_KifkMem99dg2VglWXmY0&ll=14.760469708692652,106.86124898895572&z=12"
                                target="_blank"
                                class="btn btn-sm btn-secondary mt-2"
                            >
                                <i class="fas fa-map"></i> Xem tất cả trên My
                                Maps
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Modal -->
    <ModalAttendance
        v-if="modalOpen"
        :log="selectedLog"
        @close="closeModal"
        @saved="handleAttendanceSaved"
    />
</template>

<script>
import { ref, onMounted, computed, watch, onUnmounted } from "vue";
import axios from "axios";
import ModalAttendance from "@/Components/ModalAttendance.vue";
import { useStore } from "../../Store/Auth";
import { Bootstrap5Pagination } from "laravel-vue-pagination";
import * as XLSX from "xlsx";
import Swal from "sweetalert2";
import "bootstrap/dist/js/bootstrap.bundle.min.js";
import "bootstrap/dist/css/bootstrap.min.css";
import PerfectScrollbar from "perfect-scrollbar";
import "perfect-scrollbar/css/perfect-scrollbar.css";

export default {
    components: {
        ModalAttendance,
        Pagination: Bootstrap5Pagination,
    },
    setup() {
        const store = useStore();

        const exportModalInstance = ref(null);

        // Reactive data - ปรับปรุงให้รับข้อมูลจาก API
        const logs = ref([]); // ข้อมูลจาก API response.data.data
        const paginationData = ref({}); // ข้อมูล pagination จาก API response.data.data
        const loading = ref(false);
        const modalOpen = ref(false);
        const selectedLog = ref(null);
        const isLoading = ref(false);
        const isMobile = ref(window.innerWidth < 768);

        // API Parameters - ปรับให้ส่งไป backend
        const apiParams = ref({
            page: 1,
            per_page: 20,
            search: "",
            date: "",
            full_name: "",
            station: [],
            position: [],
            status: [],
        });

        const pageChanged = (page) => {
            if (page < 1 || page > (paginationData.value.last_page || 1))
                return;
            apiParams.value.page = page;
            fetchLogs({ page });
        };

        // Search and filter UI state
        const search = ref("");
        const activeFilter = ref(null);
        const columnFilters = ref({
            date: "",
            start_date: "",
            end_date: "",
            full_name: "",
        });
        const selectedFilterValues = ref({
            station: [],
            position: [],
            status: [],
        });
        const selectedMobileStatus = ref("");
        const showMobileFilterModal = ref(false);

        // Master data
        const stations = ref([]);
        const positions = ref([]);

        // Photo and map modals
        const showPhotoModal = ref(false);
        const selectedPhoto = ref("");
        const showMapModal = ref(false);
        const selectedLogForMap = ref(null);

        // Unique values for filters
        const uniqueValues = ref({
            station: [],
            position: [],
            status: ["full_day", "morning_only", "evening_only", "no_checkin"],
        });

        // Perfect Scrollbar
        const ps = ref(null);
        const tableScrollContainer = ref(null);

        // Pagination classes
        const paginationClasses = ref({
            ul: "pagination rounded-pill shadow-sm justify-content-center align-items-center",
            li: "page-item mx-1",
            a: "page-link modern-page-link",
            active: "active modern-active",
            disabled: "disabled modern-disabled",
        });

        // Computed properties - ปรับให้ใช้ข้อมูลจาก API
        const currentPage = computed(
            () => paginationData.value.current_page || 1
        );
        const totalRecords = computed(() => paginationData.value.total || 0);

        const showExportModal = () => {
            const modal = document.getElementById("exportModal");
            if (modal) {
                exportModalInstance.value = new window.bootstrap.Modal(modal);
                exportModalInstance.value.show();
            }
        };

        const closeExportModal = () => {
            if (exportModalInstance.value) {
                exportModalInstance.value.hide();
            } else {
                // fallback: hide by manipulating DOM
                const modal = document.getElementById("exportModal");
                if (modal) {
                    modal.classList.remove("show");
                    modal.style.display = "none";
                }
            }
        };

        const exportToExcelCurrentPage = async () => {
            isLoading.value = true;
            try {
                const token =
                    localStorage.getItem("web_token") || store.getToken;

                // Clean up parameters - remove empty arrays and null values
                const cleanParams = {};
                Object.keys(apiParams.value).forEach((key) => {
                    const value = apiParams.value[key];
                    if (
                        value !== null &&
                        value !== "" &&
                        !(Array.isArray(value) && value.length === 0)
                    ) {
                        cleanParams[key] = value;
                    }
                });

                // สำหรับ export หน้าปัจจุบัน ต้องระบุ page และ per_page ชัดเจน
                cleanParams.all = false;
                cleanParams.page = apiParams.value.page;
                cleanParams.per_page = apiParams.value.per_page;

                console.log("Export current page params:", cleanParams);

                const queryString = new URLSearchParams();
                Object.keys(cleanParams).forEach((key) => {
                    if (Array.isArray(cleanParams[key])) {
                        cleanParams[key].forEach((item) => {
                            queryString.append(`${key}[]`, item);
                        });
                    } else {
                        queryString.append(key, cleanParams[key]);
                    }
                });

                const url = `/api/attendance-logs/export?${queryString.toString()}`;
                console.log("Export URL:", url);

                const response = await axios.get(url, {
                    responseType: "blob",
                    headers: { Authorization: `Bearer ${token}` },
                });

                // Check if response is an error JSON
                if (response.data.type === "application/json") {
                    const reader = new FileReader();
                    reader.onload = function () {
                        const result = JSON.parse(reader.result);
                        Swal.fire({
                            icon: "error",
                            text: result.message || "Export error",
                        });
                    };
                    reader.readAsText(response.data);
                    return;
                }

                // Create download link
                const blob = new Blob([response.data], {
                    type: response.headers["content-type"],
                });
                const link = document.createElement("a");
                link.href = window.URL.createObjectURL(blob);
                link.download = getExportFilename();
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                Swal.fire({
                    icon: "success",
                    title: "Export thành công",
                    text: "File Excel đã được tải xuống",
                    timer: 2000,
                });
            } catch (error) {
                console.error("Export error:", error);
                Swal.fire({
                    icon: "error",
                    text:
                        error.response?.data?.message ||
                        "Không thể export dữ liệu",
                });
            } finally {
                isLoading.value = false;
                closeExportModal();
            }
        };

        const exportToExcelAllPages = async () => {
            isLoading.value = true;
            try {
                const token =
                    localStorage.getItem("web_token") || store.getToken;

                // Clean up parameters
                const cleanParams = {};
                Object.keys(apiParams.value).forEach((key) => {
                    const value = apiParams.value[key];
                    if (
                        value !== null &&
                        value !== "" &&
                        !(Array.isArray(value) && value.length === 0)
                    ) {
                        cleanParams[key] = value;
                    }
                });

                cleanParams.all = true;
                delete cleanParams.page; // Remove page for all export
                delete cleanParams.per_page; // Remove per_page for all export

                const queryString = new URLSearchParams();
                Object.keys(cleanParams).forEach((key) => {
                    if (Array.isArray(cleanParams[key])) {
                        cleanParams[key].forEach((item) => {
                            queryString.append(`${key}[]`, item);
                        });
                    } else {
                        queryString.append(key, cleanParams[key]);
                    }
                });

                const url = `/api/attendance-logs/export?${queryString.toString()}`;

                const response = await axios.get(url, {
                    responseType: "blob",
                    headers: { Authorization: `Bearer ${token}` },
                });

                const blob = new Blob([response.data], {
                    type: response.headers["content-type"],
                });
                const link = document.createElement("a");
                link.href = window.URL.createObjectURL(blob);
                link.download = getExportFilename(true);
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                Swal.fire({
                    icon: "success",
                    title: "Export thành công",
                    text: "File Excel đã được tải xuống",
                    timer: 2000,
                });
            } catch (error) {
                console.error("Export error:", error);
                Swal.fire({
                    icon: "error",
                    title: "Export Error",
                    text:
                        error.response?.data?.message ||
                        "Không thể export dữ liệu",
                });
            } finally {
                isLoading.value = false;
                closeExportModal();
            }
        };
        function getExportFilename(all = false) {
            const date = new Date().toISOString().slice(0, 10);
            return all
                ? `attendance_all_${date}.xlsx`
                : `attendance_page_${apiParams.value.page}_${date}.xlsx`;
        }

        // Methods - ปรับปรุง fetchLogs ให้รับ parameters
        const fetchLogs = async (params = {}) => {
            isLoading.value = true;
            try {
                const token =
                    localStorage.getItem("web_token") || store.getToken;

                // รวม parameters และซิงค์กับ UI state
                const requestParams = {
                    ...apiParams.value,
                    ...params,
                };

                // ซิงค์ search term กับ UI
                if (requestParams.search !== search.value) {
                    search.value = requestParams.search || "";
                }

                // ลบ parameters ที่เป็น array ว่าง
                Object.keys(requestParams).forEach((key) => {
                    if (
                        Array.isArray(requestParams[key]) &&
                        requestParams[key].length === 0
                    ) {
                        delete requestParams[key];
                    }
                    if (
                        requestParams[key] === "" ||
                        requestParams[key] === null
                    ) {
                        delete requestParams[key];
                    }
                });

                const response = await axios.get("/api/attendance-logs", {
                    headers: { Authorization: `Bearer ${token}` },
                    params: requestParams,
                });

                // เก็บข้อมูลจาก API response
                const responseData = response.data.data;
                logs.value = responseData.data || [];
                paginationData.value = {
                    current_page: responseData.current_page,
                    last_page: responseData.last_page,
                    per_page: responseData.per_page,
                    total: responseData.total,
                    from: responseData.from,
                    to: responseData.to,
                    first_page_url: responseData.first_page_url,
                    last_page_url: responseData.last_page_url,
                    next_page_url: responseData.next_page_url,
                    prev_page_url: responseData.prev_page_url,
                    data: responseData.data,
                };

                // อัพเดต apiParams เฉพาะค่าที่เปลี่ยน
                Object.keys(params).forEach((key) => {
                    apiParams.value[key] = params[key];
                });

                updateUniqueValues();
            } catch (error) {
                console.error("Error fetching logs:", error);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Failed to fetch attendance logs",
                });
            } finally {
                isLoading.value = false;
            }
        };

        const fetchStations = async () => {
            try {
                const token =
                    localStorage.getItem("web_token") || store.getToken;
                const response = await axios.get("/api/stations", {
                    headers: { Authorization: `Bearer ${token}` },
                });
                stations.value = response.data;
            } catch (error) {
                console.error("Error fetching stations:", error);
            }
        };

        const fetchPositions = async () => {
            try {
                const token =
                    localStorage.getItem("web_token") || store.getToken;
                const response = await axios.get("/api/positions", {
                    headers: { Authorization: `Bearer ${token}` },
                });
                positions.value = response.data;
            } catch (error) {
                console.error("Error fetching positions:", error);
            }
        };

        const updateUniqueValues = () => {
            uniqueValues.value.station = [
                ...new Set(
                    logs.value.map((log) => log.user?.station).filter(Boolean)
                ),
            ];
            uniqueValues.value.position = [
                ...new Set(
                    logs.value.map((log) => log.user?.position).filter(Boolean)
                ),
            ];
        };

        // Utility functions
        const getCloudinaryUrl = (publicId) => {
            const id = publicId.startsWith("attendance_logs/")
                ? publicId
                : `attendance_logs/${publicId}`;
            return `https://res.cloudinary.com/dhtgcccax/image/upload/${id}.jpg`;
        };

        const formatTime = (datetime) => {
            if (!datetime) return "-";
            return new Date(datetime).toLocaleTimeString("th-TH", {
                hour: "2-digit",
                minute: "2-digit",
            });
        };

        const formatDate = (date) => {
            if (!date) return "-";
            const d = new Date(date);
            // Format DD/MM/YYYY (year as AD)
            const day = String(d.getDate()).padStart(2, "0");
            const month = String(d.getMonth() + 1).padStart(2, "0");
            const year = d.getFullYear(); // AD year
            return `${day}/${month}/${year}`;
        };

        const statusText = (status) => {
            switch (status) {
                case "full_day":
                    return "Đầy đủ";
                case "morning_only":
                    return "Chỉ sáng";
                case "evening_only":
                    return "Chỉ chiều";
                default:
                    return "Chưa chấm";
            }
        };

        const getStationName = (stationCode) => {
            if (!stationCode) return "N/A";
            const station = stations.value.find(
                (s) => s.ma_don_vi === stationCode
            );
            return station ? station.Name : stationCode;
        };

        const getPositionName = (positionId) => {
            if (!positionId) return "N/A";
            const position = positions.value.find(
                (p) => p.id_position === positionId
            );
            return position ? position.position : positionId;
        };

        // Badge and icon functions (same as User.vue)
        const getStationBadgeClass = (stationCode) => {
            if (!stationCode) return "badge bg-label-secondary station-badge";
            const station = stations.value.find(
                (s) => s.ma_don_vi === stationCode
            );
            if (!station) return "badge bg-label-secondary station-badge";

            const stationName = station.Name.toLowerCase();
            if (
                stationName.includes("trung tâm") ||
                stationName.includes("center")
            ) {
                return "badge bg-label-primary station-badge";
            } else if (
                stationName.includes("chi nhánh") ||
                stationName.includes("branch")
            ) {
                return "badge bg-label-success station-badge";
            } else if (
                stationName.includes("văn phòng") ||
                stationName.includes("office")
            ) {
                return "badge bg-label-info station-badge";
            } else if (
                stationName.includes("phòng") ||
                stationName.includes("department")
            ) {
                return "badge bg-label-warning station-badge";
            } else {
                return "badge bg-label-light station-badge";
            }
        };

        const getStationIcon = (stationCode) => {
            if (!stationCode) return "fas fa-building";
            const station = stations.value.find(
                (s) => s.ma_don_vi === stationCode
            );
            if (!station) return "fas fa-building";

            const stationName = station.Name.toLowerCase();
            if (
                stationName.includes("trung tâm") ||
                stationName.includes("center")
            ) {
                return "fas fa-building";
            } else if (
                stationName.includes("chi nhánh") ||
                stationName.includes("branch")
            ) {
                return "fas fa-sitemap";
            } else if (
                stationName.includes("văn phòng") ||
                stationName.includes("office")
            ) {
                return "fas fa-briefcase";
            } else if (
                stationName.includes("phòng") ||
                stationName.includes("department")
            ) {
                return "fas fa-door-open";
            } else {
                return "fas fa-map-marker-alt";
            }
        };

        const getPositionBadgeClass = (positionId) => {
            if (!positionId) return "badge bg-label-secondary position-badge";
            const position = positions.value.find(
                (p) => p.id_position === positionId
            );
            if (!position) return "badge bg-label-secondary position-badge";

            const positionName = position.position.toLowerCase();
            if (
                positionName.includes("giám đốc") ||
                positionName.includes("director")
            ) {
                return "badge bg-label-danger position-badge";
            } else if (
                positionName.includes("phó giám đốc") ||
                positionName.includes("deputy")
            ) {
                return "badge bg-label-warning position-badge";
            } else if (
                positionName.includes("trưởng") ||
                positionName.includes("manager")
            ) {
                return "badge bg-label-info position-badge";
            } else if (
                positionName.includes("chuyên viên") ||
                positionName.includes("specialist")
            ) {
                return "badge bg-label-success position-badge";
            } else {
                return "badge bg-label-primary position-badge";
            }
        };

        const getPositionIcon = (positionId) => {
            if (!positionId) return "fas fa-user";
            const position = positions.value.find(
                (p) => p.id_position === positionId
            );
            if (!position) return "fas fa-user";

            const positionName = position.position.toLowerCase();
            if (
                positionName.includes("giám đốc") ||
                positionName.includes("director")
            ) {
                return "fas fa-crown";
            } else if (
                positionName.includes("phó giám đốc") ||
                positionName.includes("deputy")
            ) {
                return "fas fa-star";
            } else if (
                positionName.includes("trưởng") ||
                positionName.includes("manager")
            ) {
                return "fas fa-user-tie";
            } else if (
                positionName.includes("chuyên viên") ||
                positionName.includes("specialist")
            ) {
                return "fas fa-graduation-cap";
            } else {
                return "fas fa-user";
            }
        };

        const getStatusBadgeClass = (status) => {
            switch (status) {
                case "full_day":
                    return "badge bg-label-success";
                case "morning_only":
                case "evening_only":
                    return "badge bg-label-warning";
                default:
                    return "badge bg-label-danger";
            }
        };

        const getStatusIcon = (status) => {
            switch (status) {
                case "full_day":
                    return "fas fa-check-circle";
                case "morning_only":
                case "evening_only":
                    return "fas fa-exclamation-circle";
                default:
                    return "fas fa-times-circle";
            }
        };

        const initPerfectScrollbar = () => {
            if (tableScrollContainer.value) {
                if (ps.value) {
                    ps.value.destroy();
                }
                ps.value = new PerfectScrollbar(tableScrollContainer.value, {
                    wheelSpeed: 1,
                    wheelPropagation: true,
                    minScrollbarLength: 20,
                });
            }
        };

        const updateScrollbar = () => {
            if (ps.value) {
                ps.value.update();
            }
        };

        const handleResize = () => {
            isMobile.value = window.innerWidth < 768;
            setTimeout(() => {
                updateScrollbar();
            }, 100);
        };

        // Modal functions
        const openModal = (log = null) => {
            selectedLog.value = log;
            modalOpen.value = true;
        };

        const closeModal = () => {
            modalOpen.value = false;
            selectedLog.value = null;
        };

        // Photo modal
        const viewPhoto = (photoId) => {
            selectedPhoto.value = getCloudinaryUrl(photoId);
            showPhotoModal.value = true;
        };

        const closePhotoModal = () => {
            showPhotoModal.value = false;
            selectedPhoto.value = "";
        };

        // Map modal
        const hasLocationData = (log) => {
            return (
                (log.lat_morning && log.lng_morning) ||
                (log.lat_evening && log.lng_evening)
            );
        };

        const viewMap = (log) => {
            selectedLogForMap.value = log;
            showMapModal.value = true;
            // Initialize map here if needed
        };

        const closeMapModal = () => {
            showMapModal.value = false;
            selectedLogForMap.value = null;
        };

        // Filter functions
        const toggleFilter = (column) => {
            activeFilter.value = activeFilter.value === column ? null : column;
        };

        // ปรับปรุง Filter functions ให้มีประสิทธิภาพมากขึ้น
        const resetFilter = (column) => {
            if (["station", "position", "status"].includes(column)) {
                selectedFilterValues.value[column] = [];
            } else {
                columnFilters.value[column] = "";
            }

            activeFilter.value = null;

            // เรียก API ใหม่พร้อมรีเซ็ต filter นั้นๆ
            const resetParams = { page: 1 };
            resetParams[column] = ["station", "position", "status"].includes(
                column
            )
                ? []
                : "";

            fetchLogs(resetParams);
        };

        const applyFilter = (column) => {
            // อัพเดต apiParams
            if (["station", "position", "status"].includes(column)) {
                apiParams.value[column] = [
                    ...selectedFilterValues.value[column],
                ];
            } else {
                apiParams.value[column] = columnFilters.value[column];
            }

            activeFilter.value = null;

            // เรียก API ใหม่
            fetchLogs({ page: 1 });
        };

        const applyDateRangeFilter = () => {
            fetchLogs({
                page: 1,
                start_date: columnFilters.value.start_date,
                end_date: columnFilters.value.end_date,
            });
            activeFilter.value = null;
        };

        const resetDateRangeFilter = () => {
            columnFilters.value.start_date = "";
            columnFilters.value.end_date = "";
            fetchLogs({ page: 1, start_date: "", end_date: "" });
            activeFilter.value = null;
        };

        const resetAllFilters = () => {
            // Reset UI state
            search.value = "";
            Object.keys(columnFilters.value).forEach((key) => {
                columnFilters.value[key] = "";
            });
            Object.keys(selectedFilterValues.value).forEach((key) => {
                selectedFilterValues.value[key] = [];
            });
            selectedMobileStatus.value = "";
            activeFilter.value = null;

            // Reset และเรียก API ใหม่
            fetchLogs({
                page: 1,
                search: "",
                date: "",
                start_date: "",
                end_date: "",
                full_name: "",
                station: [],
                position: [],
                status: [],
            });
        };

        const applyMobileStatusFilter = () => {
            if (selectedMobileStatus.value) {
                selectedFilterValues.value.status = [
                    selectedMobileStatus.value,
                ];
            } else {
                selectedFilterValues.value.status = [];
            }

            fetchLogs({
                page: 1,
                status: selectedFilterValues.value.status,
            });
        };

        const applyMobileFilters = () => {
            // อัพเดตและเรียก API ใหม่
            fetchLogs({
                page: 1,
                start_date: columnFilters.value.start_date || "",
                end_date: columnFilters.value.end_date || "",
                station: [...selectedFilterValues.value.station],
                position: [...selectedFilterValues.value.position],
            });

            showMobileFilterModal.value = false;
        };

        // ปรับปรุง Search ให้ใช้ debounce ที่ดีขึ้น
        let searchTimeout = null;
        const debouncedSearch = () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                fetchLogs({ page: 1, search: search.value });
            }, 500);
        };

        // เพิ่มฟังก์ชันสำหรับจัดการ saved modal
        const handleAttendanceSaved = () => {
            // รีเฟรชข้อมูลโดยใช้ parameters ปัจจุบัน
            fetchLogs({ page: apiParams.value.page });
        };

        // Lifecycle - ปรับปรุงการโหลดข้อมูลเริ่มต้น
        onMounted(async () => {
            // โหลดข้อมูลพื้นฐานก่อน
            await Promise.all([fetchStations(), fetchPositions()]);

            // จากนั้นโหลดข้อมูล logs
            await fetchLogs();

            // เซ็ตอัพ event listeners
            window.addEventListener("resize", handleResize);

            // เซ็ตอัพ perfect scrollbar หลังจากข้อมูลโหลดเสร็จ
            setTimeout(() => {
                initPerfectScrollbar();
            }, 100);
        });

        // ปรับปรุง Watchers - เปลี่ยนจาก watch search เป็น immediate search
        watch(search, (newValue, oldValue) => {
            // ถ้าค่าเปลี่ยนแปลงจริงๆ ถึงจะเรียก debounced search
            if (newValue !== oldValue) {
                debouncedSearch();
            }
        });

        // เพิ่ม watcher สำหรับ perfect scrollbar
        watch(
            () => logs.value.length,
            () => {
                setTimeout(() => {
                    updateScrollbar();
                }, 50);
            }
        );

        // Cleanup function
        onUnmounted(() => {
            clearTimeout(searchTimeout);
            window.removeEventListener("resize", handleResize);
            if (ps.value) {
                ps.value.destroy();
            }
        });

        return {
            // Data
            logs,
            paginationData,
            loading,
            modalOpen,
            selectedLog,
            isLoading,
            isMobile,
            apiParams,
            search,
            activeFilter,
            columnFilters,
            selectedFilterValues,
            selectedMobileStatus,
            showMobileFilterModal,
            stations,
            positions,
            showPhotoModal,
            selectedPhoto,
            showMapModal,
            selectedLogForMap,
            uniqueValues,
            ps,
            tableScrollContainer,
            paginationClasses,

            // Computed
            currentPage,
            totalRecords,

            // Methods
            fetchLogs,
            handleAttendanceSaved,
            getCloudinaryUrl,
            formatTime,
            formatDate,
            statusText,
            getStationName,
            getPositionName,
            getStationBadgeClass,
            getStationIcon,
            getPositionBadgeClass,
            getPositionIcon,
            getStatusBadgeClass,
            getStatusIcon,
            openModal,
            closeModal,
            viewPhoto,
            closePhotoModal,
            hasLocationData,
            viewMap,
            closeMapModal,
            toggleFilter,
            resetFilter,
            applyFilter,
            resetAllFilters,
            applyMobileStatusFilter,
            applyMobileFilters,
            pageChanged,
            debouncedSearch,
            showExportModal,
            closeExportModal,
            exportToExcelCurrentPage,
            exportToExcelAllPages,
            getExportFilename,
            initPerfectScrollbar,
            updateScrollbar,
            handleResize,
            applyDateRangeFilter,
            resetDateRangeFilter,
        };
    },
};
</script>

<style scoped>
/* Enhanced table styling for modern look - Optimized */
.table-container {
    position: relative;
    width: 100%;
    height: 100%;
    background-color: #fff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    padding: 1rem;
    margin-bottom: 1rem;
    overflow: hidden;
    min-height: calc(100vh - 450px);
    border-radius: 0.75rem;
}

.desktop-controls-container {
    min-height: calc(100vh - 450px);
    height: auto;
}

.desktop-controls-container .card {
    height: calc(100vh - 450px);
    height: auto;
}

.table-container::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: #10b981;
    z-index: 1;
}

.table-scroll-container {
    position: relative;
    max-height: calc(100vh - 350px);
    height: auto;
    overflow: auto;
    border-radius: 0.5rem;
    background: #ffffff;
}

/* Simplified table styling */
.table {
    margin-bottom: 0;
    width: 100%;
    min-width: 1400px;
    border-collapse: separate;
    border-spacing: 0;
    border: none;
    background-color: transparent;
}

/* Simplified header styling */
.table thead {
    position: sticky;
    top: 0;
    z-index: 20;
}

.table thead th {
    position: sticky;
    top: 0;
    background: #f8fafc;
    z-index: 20;
    padding: 1rem 0.75rem;
    vertical-align: middle;
    border: none;
    border-bottom: 1px solid #e2e8f0;
    font-weight: 600;
    font-size: 0.875rem;
    color: #059669;
    text-transform: none;
    white-space: nowrap;
    min-width: 120px;
    position: relative;
}

/* Specific column widths for attendance table */
.table thead th:nth-child(1) {
    min-width: 60px;
}
.table thead th:nth-child(2) {
    min-width: 120px;
}
.table thead th:nth-child(3) {
    min-width: 150px;
}
.table thead th:nth-child(4) {
    min-width: 180px;
}
.table thead th:nth-child(5) {
    min-width: 140px;
}
.table thead th:nth-child(6) {
    min-width: 180px;
}
.table thead th:nth-child(7) {
    min-width: 160px;
}
.table thead th:nth-child(8) {
    min-width: 180px;
}
.table thead th:nth-child(9) {
    min-width: 160px;
}
.table thead th:nth-child(10) {
    min-width: 120px;
}
.table thead th:nth-child(11) {
    min-width: 100px;
}

/* Simplified table body styling */
.table tbody {
    background-color: transparent;
}

.table td {
    padding: 0.875rem 0.75rem;
    vertical-align: middle;
    border: none;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 0.875rem;
    color: #374151;
    background-color: transparent;
}

/* Simplified row styling */
.table tbody tr {
    position: relative;
    transition: background-color 0.2s ease;
    border-radius: 0.25rem;
}

/* Simplified row separator */
.table tbody tr:not(:last-child):after {
    content: "";
    position: absolute;
    bottom: 0;
    left: 1rem;
    right: 1rem;
    height: 1px;
    background: #f1f5f9;
}

/* Simplified hover effect */
.table tbody tr:hover {
    background: #f8fafc;
}

.table tbody tr:hover:after {
    opacity: 0;
}

/* Simplified zebra striping */
.table tbody tr:nth-child(even) {
    background: #f9fafb;
}

.table tbody tr:nth-child(odd) {
    background: #ffffff;
}

.table tbody tr:hover {
    background: #f0fdf4 !important;
}

/* Check-in photo styling - Simplified */
.checkin-info {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.checkin-photo {
    width: 60px;
    height: 60px;
    border-radius: 0.5rem;
    object-fit: cover;
    border: 2px solid #e5e7eb;
    transition: border-color 0.2s ease;
    cursor: pointer;
}

.checkin-photo:hover {
    border-color: #10b981;
}

.checkin-time {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6b7280;
    background: #f3f4f6;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
}

/* Note display styling - Simplified */
.note-display {
    max-width: 150px;
    white-space: normal;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 0.8rem;
    color: #6b7280;
    line-height: 1.4;
    padding: 0.5rem;
    background: #f9fafb;
    border-radius: 0.25rem;
    border: 1px solid #e5e7eb;
}

/* Simplified badges */
.badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.375em 0.75em;
    font-size: 0.75rem;
    font-weight: 600;
    line-height: 1;
    text-align: center;
    white-space: nowrap;
    vertical-align: baseline;
    border-radius: 0.375rem;
    margin: auto;
}

/* Simplified badge colors */
.bg-label-primary {
    color: #1e40af;
    background: #dbeafe;
}

.bg-label-success {
    color: #065f46;
    background: #d1fae5;
}

.bg-label-warning {
    color: #92400e;
    background: #fef3c7;
}

.bg-label-danger {
    color: #991b1b;
    background: #fee2e2;
}

.bg-label-info {
    color: #0369a1;
    background: #e0f2fe;
}

.bg-label-secondary {
    color: #4b5563;
    background: #f3f4f6;
}

.bg-label-light {
    color: #6b7280;
    background: #f9fafb;
}

/* Position Badge Styling - Simplified */
.position-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.375rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 600;
    line-height: 1;
    border-radius: 0.375rem;
    min-width: 80px;
    justify-content: center;
}

/* Station Badge Styling - Simplified */
.station-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.375rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 600;
    line-height: 1;
    border-radius: 0.375rem;
    min-width: 90px;
    justify-content: center;
}

/* Simplified status styling */
.text-success {
    color: #059669;
    font-weight: 600;
}

.text-danger {
    color: #dc2626;
    font-weight: 600;
}

.text-gray-400 {
    color: #9ca3af;
    font-style: italic;
}

/* Simplified filter button */
.filter-btn {
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 0.375rem;
    margin-left: 0.375rem;
    color: #9ca3af;
    transition: color 0.2s ease;
    border-radius: 0.25rem;
}

.filter-btn:hover {
    color: #10b981;
}

/* Simplified reset filters button */
.reset-all-filters-btn {
    position: absolute;
    right: 15px;
    top: 30px;
    z-index: 99;
    font-size: 1rem;
    cursor: pointer;
    color: #fff;
    background: #10b981;
    border-radius: 50%;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.2s ease;
}

.reset-all-filters-btn:hover {
    background: #059669;
}

/* Simplified filter dropdown */
.absolute.mt-1.bg-white.p-2.rounded.shadow-lg.z-10 {
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    min-width: 280px;
    max-width: 320px;
    z-index: 1050;
    background: #ffffff;
    padding: 1rem;
    border-radius: 0.5rem;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    border: 1px solid #e2e8f0;
}

/* Simplified row number column */
.table th:first-child,
.table td:first-child {
    width: 60px;
    min-width: 60px;
    max-width: 60px;
    text-align: center;
    font-weight: 600;
    color: #6b7280;
}

/* Simplified scrollbar styling */
.ps__rail-y {
    width: 6px;
    background-color: transparent !important;
}

.ps__thumb-y {
    width: 6px;
    background: #cbd5e1;
    border-radius: 3px;
}

.ps__rail-y:hover > .ps__thumb-y {
    background: #10b981;
}

.ps__rail-x {
    height: 6px;
    background-color: transparent !important;
}

.ps__thumb-x {
    height: 6px;
    background: #cbd5e1;
    border-radius: 3px;
}

.ps__rail-x:hover > .ps__thumb-x {
    background: #10b981;
}

/* Simplified pagination styling */
.pagination-card {
    padding: 1rem;
    display: flex;
    justify-content: center;
}

.pagination {
    display: flex;
    list-style: none;
    padding-left: 0;
    margin: 0;
    border-radius: 0.5rem;
    gap: 0.25rem;
}

.pagination li {
    margin: 0;
}

.pagination li a {
    color: #6b7280;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.625rem 0.875rem;
    line-height: 1.25;
    border: 1px solid transparent;
    border-radius: 0.375rem;
    transition: all 0.2s ease;
    font-weight: 500;
    min-width: 40px;
    background: transparent;
}

.pagination li.active a {
    z-index: 1;
    color: #fff;
    background: #10b981;
    border-color: transparent;
}

.pagination li a:hover:not(.active) {
    color: #10b981;
    background: #f0fdf4;
    border-color: #bbf7d0;
}

/* Simplified search container */
.search-container {
    max-width: 350px;
    width: 100%;
}

.search-input {
    height: 42px;
    transition: border-color 0.2s ease;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    border: 1px solid #d1d5db;
    background: #ffffff;
}

.search-input:focus {
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
    border-color: #10b981;
    outline: none;
}

.input-group-text {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    background: #f3f4f6;
    border: 1px solid #d1d5db;
    border-right: none;
}

/* Simplified loading animation */
#loading-wrapper {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.spinner-border {
    width: 3rem;
    height: 3rem;
    color: #10b981;
}

/* Simplified clickable styles */
.clickable-row {
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.clickable-row:hover {
    background: #f0fdf4 !important;
}

.clickable-card {
    cursor: pointer;
    transition: all 0.2s ease;
}

.clickable-card:hover {
    background: #f0fdf4 !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(16, 185, 129, 0.1);
}

/* Simplified Photo Modal */
.photo-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.8);
    z-index: 2000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.photo-modal-content {
    position: relative;
    max-width: 90vw;
    max-height: 90vh;
    background: white;
    border-radius: 0.5rem;
    overflow: hidden;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
}

.photo-modal-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: block;
}

.photo-modal-close {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color 0.2s ease;
    z-index: 1;
}

.photo-modal-close:hover {
    background: rgba(239, 68, 68, 0.8);
}

/* Simplified Map Modal */
.map-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    z-index: 1500;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.map-modal-content {
    background: white;
    border-radius: 0.5rem;
    width: 100%;
    max-width: 600px;
    max-height: 80vh;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
}

.map-modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8fafc;
}

.map-modal-header h5 {
    margin: 0;
    color: #1f2937;
    font-weight: 600;
}

.map-modal-body {
    padding: 1.5rem;
}

.location-info {
    background: #f0fdf4;
    padding: 1rem;
    border-radius: 0.5rem;
    border: 1px solid #bbf7d0;
}

.location-info div {
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
    color: #374151;
}

.location-info strong {
    color: #059669;
}

/* Simplified Mobile Controls */
@media (max-width: 768px) {
    .mobile-controls-container {
        background: #ffffff;
        border-radius: 0.5rem;
        padding: 1.25rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid #e5e7eb;
        position: relative;
        overflow: hidden;
    }

    .mobile-controls-container::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: #10b981;
    }

    .mobile-header-row {
        display: flex;
        gap: 0.75rem;
        margin-bottom: 1rem;
        align-items: center;
    }

    .mobile-search-section {
        flex: 0 0 80%;
    }

    .mobile-action-section {
        flex: 0 0 20%;
        display: flex;
        justify-content: flex-end;
    }

    .btn-mobile-add {
        background: #10b981;
        border: none;
        border-radius: 0.5rem;
        color: white;
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        transition: background-color 0.2s ease;
        cursor: pointer;
    }

    .btn-mobile-add:hover {
        background: #059669;
    }

    .mobile-filter-row {
        display: flex;
        gap: 0.75rem;
        align-items: center;
    }

    .mobile-filter-left {
        flex: 0 0 65%;
    }

    .mobile-filter-right {
        flex: 0 0 35%;
        display: flex;
        justify-content: flex-start;
        gap: 0.5rem;
    }

    .mobile-role-select {
        width: 100%;
        height: 48px;
        border: 2px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 0 1rem;
        background: #ffffff;
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        transition: border-color 0.2s ease;
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.75rem center;
        background-repeat: no-repeat;
        background-size: 1.5em 1.5em;
        padding-right: 3rem;
    }

    .mobile-role-select:focus {
        outline: none;
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
    }

    .mobile-filter-btn {
        background: #f3f4f6;
        border: 2px solid #d1d5db;
        border-radius: 0.5rem;
        color: #6b7280;
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .mobile-filter-btn:hover {
        background: #10b981;
        border-color: #10b981;
        color: white;
    }

    /* Simplified Mobile Filter Modal */
    .mobile-filter-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1050;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .mobile-filter-modal {
        background: #ffffff;
        border-radius: 0.5rem;
        width: 100%;
        max-width: 400px;
        max-height: 80vh;
        overflow: hidden;
        box-shadow: 0 20px 25px rgba(0, 0, 0, 0.15);
        border: 1px solid #e2e8f0;
    }

    .modal-header {
        padding: 1.5rem 1.5rem 1rem;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
        flex: 1;
    }

    .btn-close {
        background: transparent;
        border: none;
        color: #6b7280;
        font-size: 1.25rem;
        padding: 0.5rem;
        border-radius: 0.25rem;
        transition: color 0.2s ease;
        cursor: pointer;
    }

    .btn-close:hover {
        color: #ef4444;
    }

    .modal-body {
        padding: 1rem 1.5rem;
        max-height: 50vh;
        overflow-y: auto;
    }

    .filter-section {
        margin-bottom: 1.5rem;
    }

    .filter-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
    }

    .checkbox-container {
        max-height: 150px;
        overflow-y: auto;
        border: 1px solid #e5e7eb;
        border-radius: 0.25rem;
        padding: 0.75rem;
        background: #f9fafb;
    }

    .form-check {
        margin-bottom: 0.5rem;
    }

    .form-check-input {
        margin-right: 0.5rem;
    }

    .form-check-label {
        font-size: 0.875rem;
        color: #374151;
        cursor: pointer;
    }

    .modal-footer {
        padding: 1rem 1.5rem 1.5rem;
        border-top: 1px solid #e2e8f0;
        display: flex;
        gap: 0.75rem;
        justify-content: flex-end;
        background: #f8fafc;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        font-weight: 600;
        transition: all 0.2s ease;
        border: 1px solid transparent;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        text-decoration: none;
    }

    .btn-primary {
        background: #10b981;
        color: white;
        border: none;
    }

    .btn-primary:hover {
        background: #059669;
    }
}

/* Simplified Mobile Card View */
.card-container {
    padding: 0.5rem;
}

.attendance-card {
    position: relative;
    background: #ffffff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    border: 1px solid #e2e8f0;
    transition: all 0.2s ease;
    overflow: hidden;
}

.attendance-card:before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: #10b981;
}

.card-header {
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 0.75rem;
}

.attendance-info {
    padding: 0.75rem 0;
}

.info-section {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.75rem;
}

.info-item {
    font-size: 0.875rem;
    line-height: 1.25rem;
}

.info-item strong {
    color: #4b5563;
    font-weight: 600;
    margin-right: 0.25rem;
}

.checkin-section {
    background: #f9fafb;
    border-radius: 0.5rem;
    padding: 1rem;
    border: 1px solid #e5e7eb;
}

.checkin-morning,
.checkin-evening {
    text-align: center;
}

.mobile-checkin-info {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.mobile-checkin-photo {
    width: 50px;
    height: 50px;
    border-radius: 0.375rem;
    object-fit: cover;
    border: 2px solid #e5e7eb;
    transition: border-color 0.2s ease;
    cursor: pointer;
}

.mobile-checkin-photo:hover {
    border-color: #10b981;
}

.mobile-checkin-time {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6b7280;
    background: #ffffff;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    border: 1px solid #d1d5db;
}

.mobile-note {
    font-size: 0.7rem;
    color: #9ca3af;
    text-align: center;
    font-style: italic;
}

.status-actions {
    padding-top: 0.75rem;
    border-top: 1px solid #e2e8f0;
}

/* Simplified Mobile Pagination */
.mobile-pagination-card {
    background: white;
    border-radius: 0.5rem;
    padding: 16px;
    margin-top: 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
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

.record-info {
    font-size: 12px;
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
    border-radius: 0.375rem;
    background: white;
    color: #6b7280;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    cursor: pointer;
}

.page-btn:hover:not(:disabled) {
    border-color: #10b981;
    color: #10b981;
}

.page-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.current-page {
    background: #10b981;
    color: white;
    padding: 8px 16px;
    border-radius: 0.375rem;
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
    border-radius: 0.25rem;
    padding: 4px 8px;
    background: white;
    color: #374151;
    cursor: pointer;
}

.quick-jump select:focus {
    outline: none;
    border-color: #10b981;
}

/* Simplified Export Modal */
.modal-content {
    border-radius: 0.5rem;
    border: none;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.modal-header {
    border-bottom: 1px solid #e5e7eb;
    border-radius: 0.5rem 0.5rem 0 0;
}

.modal-body {
    padding: 1.5rem;
}

.btn-outline-success {
    border-color: #10b981;
    color: #10b981;
    transition: all 0.2s ease;
}

.btn-outline-success:hover {
    background-color: #10b981;
    border-color: #10b981;
    color: white;
}

.btn-success {
    background-color: #10b981;
    border-color: #10b981;
    color: white;
    transition: all 0.2s ease;
}

.btn-success:hover {
    background-color: #059669;
    border-color: #059669;
}
</style>
