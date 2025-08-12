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
                        >{{ filteredLogs.length }} records</span
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
                                                @click="toggleFilter('date')"
                                                class="filter-btn"
                                            >
                                                <i class="fas fa-filter"></i>
                                            </button>
                                            <div
                                                v-if="activeFilter === 'date'"
                                                class="absolute mt-1 bg-white p-2 rounded shadow-lg z-10"
                                            >
                                                <input
                                                    type="date"
                                                    v-model="columnFilters.date"
                                                    class="form-control form-control-sm mb-2"
                                                />
                                                <div
                                                    class="d-flex justify-content-between mt-2"
                                                >
                                                    <button
                                                        @click="
                                                            resetFilter('date')
                                                        "
                                                        class="btn btn-sm btn-outline-secondary"
                                                    >
                                                        Reset
                                                    </button>
                                                    <button
                                                        @click="
                                                            applyFilter('date')
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
                                        v-for="(
                                            log, index
                                        ) in paginatedLogs.data"
                                        :key="log.id"
                                        class="desktop-row clickable-row"
                                        @click="openModal(log)"
                                    >
                                        <td>
                                            {{
                                                (currentPage - 1) * perPage +
                                                index +
                                                1
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
                                :data="paginatedLogs"
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
                    <label class="filter-label">Ngày</label>
                    <input
                        type="date"
                        v-model="columnFilters.date"
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
                <button
                    @click="showMobileFilterModal = false"
                    class="btn btn-primary"
                >
                    <i class="fas fa-check me-1"></i>
                    Apply Filters
                </button>
            </div>
        </div>
    </div>

    <!-- For Mobile: Card View -->
    <div v-if="isMobile" class="card-container">
        <div
            v-for="(log, index) in paginatedLogs.data"
            :key="log.id"
            class="attendance-card mb-3 p-3 rounded border-0 clickable-card"
            @click="openModal(log)"
        >
            <div
                class="card-header d-flex justify-content-between align-items-center mb-2"
            >
                <div class="attendance-index fw-bold">
                    <span class="badge bg-label-primary rounded-pill"
                        >#{{ (currentPage - 1) * perPage + index + 1 }}</span
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
                    {{ paginatedLogs.last_page }}</span
                >
                <span class="record-info"
                    >{{ paginatedLogs.from }}-{{ paginatedLogs.to }} của
                    {{ paginatedLogs.total }} bản ghi</span
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
                    :disabled="currentPage === paginatedLogs.last_page"
                >
                    <i class="fas fa-angle-right"></i>
                </button>

                <button
                    class="page-btn"
                    @click="pageChanged(paginatedLogs.last_page)"
                    :disabled="currentPage === paginatedLogs.last_page"
                >
                    <i class="fas fa-angle-double-right"></i>
                </button>
            </div>

            <div class="quick-jump" v-if="paginatedLogs.last_page > 5">
                <span>Đi đến trang:</span>
                <select
                    :value="currentPage"
                    @change="pageChanged(parseInt($event.target.value))"
                >
                    <option
                        v-for="page in paginatedLogs.last_page"
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
                            Chỉ xuất trang hiện tại ({{
                                paginatedLogs.data.length
                            }}
                            records)
                        </button>
                        <button
                            @click="exportToExcelAllPages"
                            class="btn btn-success"
                        >
                            <i class="fas fa-table me-2"></i>
                            Xuất tất cả ({{ filteredLogs.length }} records)
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
        @saved="fetchLogs"
    />
</template>

<script>
import { ref, onMounted, computed, watch } from "vue";
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

        // Reactive data
        const logs = ref([]);
        const loading = ref(false);
        const modalOpen = ref(false);
        const selectedLog = ref(null);
        const isLoading = ref(false);
        const isMobile = ref(window.innerWidth < 768);

        // Pagination
        const currentPage = ref(1);
        const perPage = ref(10);

        // Search and filters
        const search = ref("");
        const activeFilter = ref(null);
        const columnFilters = ref({
            date: "",
            full_name: "",
        });

        // Filter values
        const selectedFilterValues = ref({
            station: [],
            position: [],
            status: [],
        });

        // Mobile filters
        const showMobileFilterModal = ref(false);
        const selectedMobileStatus = ref("");

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

        // Computed properties
        const filteredLogs = computed(() => {
            return logs.value.filter((log) => {
                // Global search
                const searchTerm = search.value.toLowerCase();
                const matchesGlobalSearch =
                    (log.user?.full_name || "")
                        .toLowerCase()
                        .includes(searchTerm) ||
                    (log.user?.username || "")
                        .toLowerCase()
                        .includes(searchTerm) ||
                    getStationName(log.user?.station)
                        .toLowerCase()
                        .includes(searchTerm) ||
                    getPositionName(log.user?.position)
                        .toLowerCase()
                        .includes(searchTerm) ||
                    log.date.includes(searchTerm) ||
                    statusText(log.status).toLowerCase().includes(searchTerm);

                // Column filters
                const matchesColumnFilters =
                    (!columnFilters.value.date ||
                        log.date === columnFilters.value.date) &&
                    (!columnFilters.value.full_name ||
                        (log.user?.full_name || "")
                            .toLowerCase()
                            .includes(
                                columnFilters.value.full_name.toLowerCase()
                            )) &&
                    (selectedFilterValues.value.station.length === 0 ||
                        selectedFilterValues.value.station.includes(
                            log.user?.station
                        )) &&
                    (selectedFilterValues.value.position.length === 0 ||
                        selectedFilterValues.value.position.includes(
                            log.user?.position
                        )) &&
                    (selectedFilterValues.value.status.length === 0 ||
                        selectedFilterValues.value.status.includes(
                            log.status
                        )) &&
                    (!selectedMobileStatus.value ||
                        log.status === selectedMobileStatus.value);

                return matchesGlobalSearch && matchesColumnFilters;
            });
        });

        const paginatedLogs = computed(() => {
            const total = filteredLogs.value.length;
            const lastPage = Math.ceil(total / perPage.value) || 1;
            const start = (currentPage.value - 1) * perPage.value;
            const data = filteredLogs.value.slice(start, start + perPage.value);

            return {
                current_page: currentPage.value,
                data,
                first_page_url: "?page=1",
                from: total > 0 ? start + 1 : 0,
                last_page: lastPage,
                last_page_url: `?page=${lastPage}`,
                next_page_url:
                    currentPage.value < lastPage
                        ? `?page=${currentPage.value + 1}`
                        : null,
                path: "",
                per_page: perPage.value,
                prev_page_url:
                    currentPage.value > 1
                        ? `?page=${currentPage.value - 1}`
                        : null,
                to: start + data.length,
                total,
            };
        });

        // Methods
        const fetchLogs = async () => {
            isLoading.value = true;
            try {
                const token =
                    localStorage.getItem("web_token") || store.getToken;
                const response = await axios.get("/api/attendance-logs", {
                    headers: { Authorization: `Bearer ${token}` },
                });

                logs.value = (response.data.data.data || []).map((log) => ({
                    ...log,
                    status:
                        log.checkin_morning && log.checkin_evening
                            ? "full_day"
                            : log.checkin_morning
                            ? "morning_only"
                            : log.checkin_evening
                            ? "evening_only"
                            : "no_checkin",
                }));

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
            return new Date(date).toLocaleDateString("th-TH", {
                year: "numeric",
                month: "2-digit",
                day: "2-digit",
            });
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

        const resetFilter = (column) => {
            if (["station", "position", "status"].includes(column)) {
                selectedFilterValues.value[column] = [];
            } else {
                columnFilters.value[column] = "";
            }
            currentPage.value = 1;
        };

        const applyFilter = (column) => {
            activeFilter.value = null;
            currentPage.value = 1;
        };

        const resetAllFilters = () => {
            search.value = "";
            Object.keys(columnFilters.value).forEach((key) => {
                columnFilters.value[key] = "";
            });
            Object.keys(selectedFilterValues.value).forEach((key) => {
                selectedFilterValues.value[key] = [];
            });
            selectedMobileStatus.value = "";
            activeFilter.value = null;
            currentPage.value = 1;
        };

        const applyMobileStatusFilter = () => {
            currentPage.value = 1;
        };

        // Pagination
        const pageChanged = (page) => {
            currentPage.value = page;
        };

        // Export functions
        const showExportModal = () => {
            try {
                import("bootstrap/dist/js/bootstrap.bundle.min.js").then(
                    (bootstrap) => {
                        const Modal = bootstrap.Modal;
                        const modalElement =
                            document.getElementById("exportModal");
                        if (modalElement) {
                            const modal = new Modal(modalElement);
                            modal.show();
                        }
                    }
                );
            } catch (error) {
                console.error("Error showing export modal:", error);
            }
        };

        const closeExportModal = () => {
            const modalElement = document.getElementById("exportModal");
            if (modalElement) {
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) modal.hide();
            }
        };

        const exportToExcelCurrentPage = () => {
            if (paginatedLogs.value.data.length > 0) {
                generateExcel(
                    paginatedLogs.value.data,
                    "attendance_current_page"
                );
                closeExportModal();
            }
        };

        const exportToExcelAllPages = () => {
            if (filteredLogs.value.length > 0) {
                generateExcel(filteredLogs.value, "attendance_all");
                closeExportModal();
            }
        };

        const generateExcel = (data, filename) => {
            try {
                const exportData = data.map((log) => ({
                    Ngày: formatDate(log.date),
                    Trạm: getStationName(log.user?.station),
                    "Tên đầy đủ": log.user?.full_name || "",
                    "Chức vụ": getPositionName(log.user?.position),
                    "Check-in sáng": formatTime(log.checkin_morning),
                    "Ghi chú sáng": log.note_morning || "",
                    "Check-in chiều": formatTime(log.checkin_evening),
                    "Ghi chú chiều": log.note_evening || "",
                    "Trạng thái": statusText(log.status),
                }));

                const ws = XLSX.utils.json_to_sheet(exportData);
                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, "Attendance");
                XLSX.writeFile(
                    wb,
                    `${filename}_${new Date().toISOString().split("T")[0]}.xlsx`
                );

                Swal.fire({
                    icon: "success",
                    title: "Export Successful",
                    text: "Excel file has been downloaded successfully!",
                    timer: 2000,
                    showConfirmButton: false,
                });
            } catch (error) {
                console.error("Error generating Excel:", error);
                Swal.fire({
                    icon: "error",
                    title: "Export Error",
                    text: "An error occurred while generating the Excel file",
                });
            }
        };

        // Perfect Scrollbar
        const initPerfectScrollbar = () => {
            if (tableScrollContainer.value) {
                if (ps.value) ps.value.destroy();
                ps.value = new PerfectScrollbar(tableScrollContainer.value, {
                    suppressScrollX: false,
                    wheelPropagation: false,
                });
            }
        };

        const updateScrollbar = () => {
            if (ps.value) ps.value.update();
        };

        // Resize handler
        const handleResize = () => {
            isMobile.value = window.innerWidth < 768;
        };

        // Lifecycle
        onMounted(() => {
            fetchLogs();
            fetchStations();
            fetchPositions();
            window.addEventListener("resize", handleResize);
            initPerfectScrollbar();
        });

        // Watchers
        watch(
            [search, filteredLogs],
            () => {
                currentPage.value = 1;
                updateScrollbar();
            },
            { deep: true }
        );

        return {
            // Data
            logs,
            loading,
            modalOpen,
            selectedLog,
            isLoading,
            isMobile,
            currentPage,
            perPage,
            search,
            activeFilter,
            columnFilters,
            selectedFilterValues,
            showMobileFilterModal,
            selectedMobileStatus,
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
            filteredLogs,
            paginatedLogs,

            // Methods
            fetchLogs,
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
            pageChanged,
            showExportModal,
            closeExportModal,
            exportToExcelCurrentPage,
            exportToExcelAllPages,
            generateExcel,
            initPerfectScrollbar,
            updateScrollbar,
            handleResize,
        };
    },
};
</script>
<style scoped>
/* Enhanced table styling for modern look */
.table-container {
    position: relative;
    width: 100%;
    height: 100%;
    background-color: #fff;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1),
        0 2px 4px -1px rgba(0, 0, 0, 0.06);
    padding: 1rem;
    margin-bottom: 1rem;
    overflow: hidden;
    min-height: calc(100vh - 450px);
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
    background: linear-gradient(90deg, #10b981 0%, #059669 50%, #10b981 100%);
    z-index: 1;
}

.table-scroll-container {
    position: relative;
    max-height: calc(100vh - 350px);
    height: auto;
    overflow: auto;
    border-radius: 0.75rem;
    background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
}

/* Modern table styling without borders */
.table {
    margin-bottom: 0;
    width: 100%;
    min-width: 1400px; /* Increased for attendance columns */
    border-collapse: separate;
    border-spacing: 0;
    border: none;
    background-color: transparent;
}

/* Enhanced header styling */
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
    padding: 1.25rem 1rem;
    vertical-align: middle;
    border: none;
    border-bottom: 2px solid #e2e8f0;
    font-weight: 600;
    font-size: 0.875rem;
    color: #059669;
    text-transform: none;
    letter-spacing: normal;
    white-space: nowrap;
    min-width: 120px;
    position: relative;
    transition: all 0.2s ease;
}

/* Specific column widths for attendance table */
.table thead th:nth-child(1) {
    min-width: 60px;
} /* # */
.table thead th:nth-child(2) {
    min-width: 120px;
} /* Ngày */
.table thead th:nth-child(3) {
    min-width: 150px;
} /* Trạm */
.table thead th:nth-child(4) {
    min-width: 180px;
} /* Full Name */
.table thead th:nth-child(5) {
    min-width: 140px;
} /* Chức vụ */
.table thead th:nth-child(6) {
    min-width: 180px;
} /* CheckIn-Buổi Sáng */
.table thead th:nth-child(7) {
    min-width: 160px;
} /* Ghi chú Buổi Sáng */
.table thead th:nth-child(8) {
    min-width: 180px;
} /* CheckIn-Buổi Chiều */
.table thead th:nth-child(9) {
    min-width: 160px;
} /* Ghi chú Buổi Chiều */
.table thead th:nth-child(10) {
    min-width: 120px;
} /* Status */
.table thead th:nth-child(11) {
    min-width: 100px;
} /* Xem maps */

/* Add subtle separator between headers */
.table thead th:not(:last-child):after {
    content: "";
    position: absolute;
    right: 0;
    top: 25%;
    height: 50%;
    width: 1px;
    background: linear-gradient(
        180deg,
        transparent 0%,
        #d1d5db 50%,
        transparent 100%
    );
}

/* Modern table body styling */
.table tbody {
    background-color: transparent;
}

.table td {
    padding: 1rem 0.75rem;
    vertical-align: middle;
    border: none;
    transition: all 0.3s ease;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 0.875rem;
    color: #374151;
    background-color: transparent;
}

/* Enhanced row styling with subtle separation */
.table tbody tr {
    position: relative;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 0.5rem;
    margin-bottom: 0.25rem;
}

/* Add subtle row separator */
.table tbody tr:not(:last-child):after {
    content: "";
    position: absolute;
    bottom: 0;
    left: 1rem;
    right: 1rem;
    height: 1px;
    background: linear-gradient(
        90deg,
        transparent 0%,
        rgba(229, 231, 235, 0.6) 10%,
        rgba(229, 231, 235, 0.6) 90%,
        transparent 100%
    );
}

/* Modern hover effect */
.table tbody tr:hover {
    background: linear-gradient(
        135deg,
        rgba(16, 185, 129, 0.05) 0%,
        rgba(16, 185, 129, 0.02) 100%
    );
    transform: translateY(-2px) scale(1.01);
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.15);
    border-radius: 0.75rem;
}

.table tbody tr:hover:after {
    opacity: 0;
}

.table tbody tr:hover td {
    color: #065f46;
}

/* Enhanced zebra striping */
.table tbody tr:nth-child(even) {
    background: linear-gradient(
        135deg,
        rgba(248, 250, 252, 0.7) 0%,
        rgba(241, 245, 249, 0.3) 100%
    );
}

.table tbody tr:nth-child(odd) {
    background: linear-gradient(
        135deg,
        rgba(255, 255, 255, 0.9) 0%,
        rgba(248, 250, 252, 0.5) 100%
    );
}

/* Check-in photo styling */
.checkin-info {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.checkin-photo {
    width: 60px;
    height: 60px;
    border-radius: 0.75rem;
    object-fit: cover;
    border: 3px solid #e5e7eb;
    transition: all 0.3s ease;
    cursor: pointer;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.checkin-photo:hover {
    transform: scale(1.1);
    border-color: #10b981;
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
}

.checkin-time {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6b7280;
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    border: 1px solid #d1d5db;
}

/* Note display styling */
.note-display {
    max-width: 150px;
    white-space: normal;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 0.8rem;
    color: #6b7280;
    line-height: 1.4;
    padding: 0.5rem;
    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
}

/* Enhanced badges and status styling */
.badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375em 0.75em;
    font-size: 0.75rem;
    font-weight: 600;
    line-height: 1;
    text-align: center;
    white-space: nowrap;
    vertical-align: baseline;
    border-radius: 0.5rem;
    margin: auto;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease;
}

.badge:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
}

/* Badge color schemes */
.bg-label-primary {
    color: #1e40af;
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    border: 1px solid rgba(30, 64, 175, 0.2);
}

.bg-label-success {
    color: #065f46;
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    border: 1px solid rgba(6, 95, 70, 0.2);
}

.bg-label-warning {
    color: #92400e;
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border: 1px solid rgba(146, 64, 14, 0.2);
}

.bg-label-danger {
    color: #991b1b;
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    border: 1px solid rgba(153, 27, 27, 0.2);
}

.bg-label-info {
    color: #0369a1;
    background: linear-gradient(135deg, #e0f2fe 0%, #b3e5fc 100%);
    border: 1px solid rgba(3, 105, 161, 0.2);
}

.bg-label-secondary {
    color: #4b5563;
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
    border: 1px solid rgba(75, 85, 99, 0.2);
}

.bg-label-light {
    color: #6b7280;
    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
    border: 1px solid rgba(107, 114, 128, 0.2);
}

/* Position Badge Styling */
.position-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 600;
    line-height: 1;
    border-radius: 0.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    border: 1px solid transparent;
    min-width: 80px;
    justify-content: center;
}

.position-badge:hover {
    transform: translateY(-1px) scale(1.02);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

/* Station Badge Styling */
.station-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 600;
    line-height: 1;
    border-radius: 0.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    border: 1px solid transparent;
    min-width: 90px;
    justify-content: center;
}

.station-badge:hover {
    transform: translateY(-1px) scale(1.02);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

/* Enhanced status styling */
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

/* Enhanced filter button */
.filter-btn {
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 0.375rem;
    margin-left: 0.375rem;
    color: #9ca3af;
    transition: all 0.2s ease;
    border-radius: 0.375rem;
}

.filter-btn:hover {
    color: #10b981;
    background: rgba(16, 185, 129, 0.1);
    transform: scale(1.1);
}

/* Enhanced reset filters button */
.reset-all-filters-btn {
    position: absolute;
    right: 15px;
    top: 30px;
    z-index: 99;
    font-size: 1rem;
    cursor: pointer;
    color: #fff;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border-radius: 50%;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 6px rgba(16, 185, 129, 0.25);
    transition: all 0.3s ease;
    border: 2px solid rgba(255, 255, 255, 0.2);
}

.reset-all-filters-btn:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    transform: rotate(180deg) scale(1.1);
    box-shadow: 0 6px 12px rgba(16, 185, 129, 0.4);
}

/* Enhanced filter dropdown */
.absolute.mt-1.bg-white.p-2.rounded.shadow-lg.z-10 {
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    min-width: 280px;
    max-width: 320px;
    z-index: 1050;
    background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
    padding: 1rem;
    border-radius: 0.75rem;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1),
        0 10px 10px -5px rgba(0, 0, 0, 0.04);
    border: 1px solid rgba(226, 232, 240, 0.8);
    backdrop-filter: blur(8px);
}

.absolute.mt-1.bg-white.p-2.rounded.shadow-lg.z-10:before {
    content: "";
    position: absolute;
    top: -8px;
    left: 15px;
    width: 16px;
    height: 16px;
    background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
    transform: rotate(45deg);
    border-left: 1px solid rgba(226, 232, 240, 0.8);
    border-top: 1px solid rgba(226, 232, 240, 0.8);
    z-index: -1;
}

/* Enhanced row number column */
.table th:first-child,
.table td:first-child {
    width: 60px;
    min-width: 60px;
    max-width: 60px;
    text-align: center;
    font-weight: 600;
    color: #6b7280;
}

/* Perfect scrollbar modern styling */
.ps__rail-y {
    width: 8px;
    background-color: transparent !important;
    border-radius: 4px;
}

.ps__thumb-y {
    width: 6px;
    background: linear-gradient(180deg, #cbd5e1 0%, #94a3b8 100%);
    border-radius: 4px;
    transition: all 0.2s ease;
}

.ps__rail-y:hover > .ps__thumb-y,
.ps__rail-y:focus > .ps__thumb-y {
    width: 8px;
    background: linear-gradient(180deg, #10b981 0%, #059669 100%);
}

.ps__rail-x {
    height: 8px;
    background-color: transparent !important;
    border-radius: 4px;
}

.ps__thumb-x {
    height: 6px;
    background: linear-gradient(90deg, #cbd5e1 0%, #94a3b8 100%);
    border-radius: 4px;
    transition: all 0.2s ease;
}

.ps__rail-x:hover > .ps__thumb-x,
.ps__rail-x:focus > .ps__thumb-x {
    height: 8px;
    background: linear-gradient(90deg, #10b981 0%, #059669 100%);
}

/* Enhanced pagination styling */
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
    border-radius: 0.5rem;
    transition: all 0.2s ease;
    font-weight: 500;
    min-width: 40px;
    background: transparent;
}

.pagination li.active a {
    z-index: 1;
    color: #fff;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border-color: transparent;
    box-shadow: 0 4px 6px rgba(16, 185, 129, 0.25);
    transform: translateY(-1px);
}

.pagination li a:hover:not(.active) {
    color: #10b981;
    background: linear-gradient(
        135deg,
        rgba(16, 185, 129, 0.1) 0%,
        rgba(16, 185, 129, 0.05) 100%
    );
    border-color: rgba(16, 185, 129, 0.2);
    transform: translateY(-1px);
}

/* Enhanced search container */
.search-container {
    max-width: 350px;
    width: 100%;
}

.search-input {
    height: 42px;
    transition: all 0.3s ease;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    border: 1px solid #d1d5db;
    background: linear-gradient(145deg, #ffffff 0%, #f9fafb 100%);
}

.search-input:focus {
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1), 0 1px 3px rgba(0, 0, 0, 0.1);
    border-color: #10b981;
    background: #ffffff;
}

.input-group-text {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    background: linear-gradient(145deg, #f3f4f6 0%, #e5e7eb 100%);
    border: 1px solid #d1d5db;
    border-right: none;
}

/* Loading animation enhancement */
#loading-wrapper {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(4px);
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

/* Clickable row styles */
.clickable-row {
    cursor: pointer;
    transition: all 0.3s ease;
}

.clickable-row:hover {
    background: linear-gradient(
        135deg,
        rgba(16, 185, 129, 0.1) 0%,
        rgba(16, 185, 129, 0.05) 100%
    ) !important;
    transform: translateY(-2px) scale(1.01);
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.15);
}

.clickable-card {
    cursor: pointer;
    transition: all 0.3s ease;
}

.clickable-card:hover {
    background: linear-gradient(
        135deg,
        rgba(16, 185, 129, 0.1) 0%,
        rgba(16, 185, 129, 0.05) 100%
    ) !important;
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(16, 185, 129, 0.2);
}

/* Photo Modal Styling */
.photo-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(4px);
    z-index: 2000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    animation: fadeIn 0.3s ease;
}

.photo-modal-content {
    position: relative;
    max-width: 90vw;
    max-height: 90vh;
    background: white;
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
    animation: slideIn 0.3s ease;
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
    transition: all 0.3s ease;
    z-index: 1;
}

.photo-modal-close:hover {
    background: rgba(239, 68, 68, 0.8);
    transform: scale(1.1);
}

/* Map Modal Styling */
.map-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    z-index: 1500;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    animation: fadeIn 0.3s ease;
}

.map-modal-content {
    background: white;
    border-radius: 1rem;
    width: 100%;
    max-width: 600px;
    max-height: 80vh;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    animation: slideIn 0.3s ease;
}

.map-modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
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
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    padding: 1rem;
    border-radius: 0.75rem;
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

/* Mobile Controls Styling */
@media (max-width: 768px) {
    .mobile-controls-container {
        background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 1rem;
        padding: 1.25rem;
        margin-bottom: 1rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1),
            0 2px 4px -1px rgba(0, 0, 0, 0.06),
            0 0 0 1px rgba(226, 232, 240, 0.7);
        border: 1px solid rgba(226, 232, 240, 0.7);
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
        background: linear-gradient(
            90deg,
            #10b981 0%,
            #059669 50%,
            #10b981 100%
        );
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
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border: none;
        border-radius: 0.75rem;
        color: white;
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        box-shadow: 0 4px 6px rgba(16, 185, 129, 0.25);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .btn-mobile-add::before {
        content: "";
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        transition: all 0.3s ease;
        transform: translate(-50%, -50%);
    }

    .btn-mobile-add:hover::before {
        width: 100px;
        height: 100px;
    }

    .btn-mobile-add:hover {
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 8px 12px rgba(16, 185, 129, 0.4);
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
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
        border-radius: 0.75rem;
        padding: 0 1rem;
        background: linear-gradient(145deg, #ffffff 0%, #f9fafb 100%);
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        transition: all 0.3s ease;
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
        background: #ffffff;
    }

    .mobile-filter-btn {
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        border: 2px solid #d1d5db;
        border-radius: 0.75rem;
        color: #6b7280;
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .mobile-filter-btn:hover {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-color: #10b981;
        color: white;
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 8px 12px rgba(16, 185, 129, 0.25);
    }

    /* Mobile Filter Modal */
    .mobile-filter-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
        z-index: 1050;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .mobile-filter-modal {
        background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 1rem;
        width: 100%;
        max-width: 400px;
        max-height: 80vh;
        overflow: hidden;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1),
            0 10px 10px -5px rgba(0, 0, 0, 0.04);
        border: 1px solid rgba(226, 232, 240, 0.8);
        animation: modalSlideIn 0.3s ease-out;
    }

    .modal-header {
        padding: 1.5rem 1.5rem 1rem;
        border-bottom: 1px solid rgba(226, 232, 240, 0.7);
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
        border-radius: 0.375rem;
        transition: all 0.2s ease;
    }

    .btn-close:hover {
        background: rgba(239, 68, 68, 0.1);
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
        border-radius: 0.5rem;
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
        border-top: 1px solid rgba(226, 232, 240, 0.7);
        display: flex;
        gap: 0.75rem;
        justify-content: flex-end;
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        transition: all 0.3s ease;
        border: 1px solid transparent;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        text-decoration: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border: none;
        box-shadow: 0 4px 6px rgba(16, 185, 129, 0.25);
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 12px rgba(16, 185, 129, 0.3);
    }
}

/* Mobile Card View Styling */
.card-container {
    padding: 0.5rem;
}

.attendance-card {
    position: relative;
    background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05),
        0 10px 15px -5px rgba(16, 185, 129, 0.07);
    border: 1px solid rgba(226, 232, 240, 0.7);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
}

.attendance-card:before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 6px;
    height: 100%;
    background: linear-gradient(180deg, #10b981 0%, #059669 100%);
    border-top-left-radius: 4px;
    border-bottom-left-radius: 4px;
}

.card-header {
    border-bottom: 1px solid rgba(226, 232, 240, 0.7);
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
    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
    border-radius: 0.75rem;
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
    border-radius: 0.5rem;
    object-fit: cover;
    border: 2px solid #e5e7eb;
    transition: all 0.3s ease;
    cursor: pointer;
}

.mobile-checkin-photo:hover {
    transform: scale(1.1);
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
    border-top: 1px solid rgba(226, 232, 240, 0.7);
}

/* Mobile Pagination Card */
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

/* Export Modal Styling */
.modal-content {
    border-radius: 1rem;
    border: none;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.modal-header {
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    border-radius: 1rem 1rem 0 0;
}

.modal-body {
    padding: 1.5rem;
}

.btn-outline-success {
    border-color: #10b981;
    color: #10b981;
}

.btn-outline-success:hover {
    background-color: #10b981;
    border-color: #10b981;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);
}

.btn-success {
    background-color: #10b981;
    border-color: #10b981;
    color: white;
}

.btn-success:hover {
    background-color: #059669;
    border-color: #059669;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(5, 150, 105, 0.4);
}

/* Animations */
@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: scale(0.9) translateY(20px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: scale(0.9) translateY(20px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

/* Responsive adjustments */
@media (max-width: 480px) {
    .table-scroll-container {
        max-height: calc(100vh - 300px);
        border-radius: 0.5rem;
    }

    .table thead th {
        font-size: 0.75rem;
        padding: 0.75rem 0.5rem;
    }

    .table td {
        font-size: 0.75rem;
        padding: 0.75rem 0.5rem;
    }

    .checkin-photo {
        width: 50px;
        height: 50px;
    }

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

    .badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
    }

    .position-badge,
    .station-badge {
        min-width: 70px;
    }
}

/* Additional utility classes */
.text-muted {
    color: #6b7280 !important;
}

.text-warning {
    color: #f59e0b !important;
}

.text-info {
    color: #0ea5e9 !important;
}

.fw-bold {
    font-weight: 600 !important;
}

/* Button styling */
.button-30-save {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border: none;
    border-radius: 0.75rem;
    color: white;
    padding: 0.75rem 1.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 6px rgba(16, 185, 129, 0.25);
    cursor: pointer;
}

.button-30-save:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 12px rgba(16, 185, 129, 0.3);
}

.btn-light {
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
    border: 1px solid #d1d5db;
    color: #6b7280;
    transition: all 0.3s ease;
}

.btn-light:hover {
    background: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%);
    color: #374151;
    transform: translateY(-1px);
}

.btn-icon {
    width: 40px;
    height: 40px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.5rem;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.8rem;
    border-radius: 0.375rem;
}

.btn-info {
    background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
    border: none;
    color: white;
    box-shadow: 0 2px 4px rgba(14, 165, 233, 0.25);
}

.btn-info:hover {
    background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(14, 165, 233, 0.3);
}

/* Dropdown menu styling */
.dropdown-menu {
    border: none;
    border-radius: 0.75rem;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    padding: 0.5rem;
    background: white;
}

.dropdown-item {
    border-radius: 0.5rem;
    padding: 0.75rem 1rem;
    transition: all 0.2s ease;
    font-size: 0.875rem;
}

.dropdown-item:hover {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    color: #059669;
    transform: translateX(4px);
}

/* Form controls enhancement */
.form-control-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    border-radius: 0.5rem;
    border: 1px solid #d1d5db;
    transition: all 0.3s ease;
}

.form-control-sm:focus {
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
    outline: none;
}

.form-control {
    border-radius: 0.5rem;
    border: 1px solid #d1d5db;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
    outline: none;
}

/* Badge hover animations */
.position-badge:hover,
.station-badge:hover {
    animation: badgeBounce 0.6s ease-in-out;
}

@keyframes badgeBounce {
    0%,
    20%,
    60%,
    100% {
        transform: translateY(0) scale(1);
    }
    40% {
        transform: translateY(-4px) scale(1.05);
    }
    80% {
        transform: translateY(-2px) scale(1.02);
    }
}
</style>
