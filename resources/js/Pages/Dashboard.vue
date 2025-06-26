<template>
    <div class="dashboard-container">
        <!-- Loading Overlay -->
        <div v-if="isLoading" class="loading-overlay">
            <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                <p class="mt-2"></p>
            </div>
        </div>

        <!-- Header Section -->
        <div class="dashboard-header mb-4">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="dashboard-title mb-2">
                        <i class="fas fa-chart-line me-3 text-success"></i>
                        Dashboard Analytics
                    </h1>
                    <p class="dashboard-subtitle text-muted">
                        Theo dõi và phân tích dữ liệu hoạt động của các trạm
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div
                        class="dashboard-stats d-flex justify-content-lg-end gap-3"
                    >
                        <div class="stat-card">
                            <span class="stat-number">{{ totalStations }}</span>
                            <span class="stat-label">Trạm</span>
                        </div>
                        <div class="stat-card">
                            <span class="stat-number">{{ totalQuantity }}</span>
                            <span class="stat-label">Tổng số lượng</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="alert alert-danger mb-4">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ error }}
            <button
                @click="refreshData"
                class="btn btn-sm btn-outline-danger ms-2"
            >
                <i class="fas fa-sync-alt"></i> Thử lại
            </button>
        </div>

        <!-- First Section: Chart และ Donut Chart -->
        <div class="charts-section mb-4">
            <div class="row g-4">
                <!-- Chart Section (60%) -->
                <div class="col-xl-7 col-lg-6">
                    <div class="chart-section h-100">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-header bg-white border-0 pb-0">
                                <div
                                    class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3"
                                >
                                    <div>
                                        <h5 class="card-title mb-1">
                                            <i
                                                class="fas fa-chart-bar me-2 text-primary"
                                            ></i>
                                            Thống kê theo trạm
                                        </h5>
                                        <p class="text-muted small mb-0">
                                            Phân tích dữ liệu hoạt động của
                                            {{ totalStations }} trạm
                                        </p>
                                        <!-- Date Range Display -->
                                        <p
                                            class="text-info small mb-0"
                                            v-if="dateRange"
                                        >
                                            <i class="fas fa-calendar me-1"></i>
                                            {{ formatDateRange() }}
                                        </p>
                                    </div>

                                    <!-- Time Period Selector -->
                                    <div class="time-selector">
                                        <div
                                            class="btn-group btn-group-sm"
                                            role="group"
                                        >
                                            <input
                                                type="radio"
                                                class="btn-check"
                                                name="timePeriod"
                                                id="week"
                                                value="week"
                                                v-model="selectedPeriod"
                                                @change="fetchChartData"
                                            />
                                            <label
                                                class="btn btn-outline-primary"
                                                for="week"
                                            >
                                                <i
                                                    class="fas fa-calendar-week me-1"
                                                ></i>
                                                Tuần
                                            </label>

                                            <input
                                                type="radio"
                                                class="btn-check"
                                                name="timePeriod"
                                                id="month"
                                                value="month"
                                                v-model="selectedPeriod"
                                                @change="fetchChartData"
                                            />
                                            <label
                                                class="btn btn-outline-primary"
                                                for="month"
                                            >
                                                <i
                                                    class="fas fa-calendar-alt me-1"
                                                ></i>
                                                Tháng
                                            </label>

                                            <input
                                                type="radio"
                                                class="btn-check"
                                                name="timePeriod"
                                                id="year"
                                                value="year"
                                                v-model="selectedPeriod"
                                                @change="fetchChartData"
                                            />
                                            <label
                                                class="btn btn-outline-primary"
                                                for="year"
                                            >
                                                <i
                                                    class="fas fa-calendar me-1"
                                                ></i>
                                                Năm
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <!-- Chart Container -->
                                <div class="chart-container">
                                    <!-- No Data Message -->
                                    <div
                                        v-if="
                                            !isLoading && stations.length === 0
                                        "
                                        class="no-data-message"
                                    >
                                        <i
                                            class="fas fa-chart-bar fa-3x text-muted mb-3"
                                        ></i>
                                        <h5 class="text-muted">
                                            Không có dữ liệu
                                        </h5>
                                        <p class="text-muted">
                                            Chưa có dữ liệu thống kê cho khoảng
                                            thời gian này
                                        </p>
                                    </div>

                                    <!-- Chart.js Canvas -->
                                    <div v-else class="chart-wrapper">
                                        <canvas
                                            ref="chartCanvas"
                                            id="stationsChart"
                                            style="
                                                max-height: 350px;
                                                width: 100%;
                                            "
                                        ></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Donut Chart Section (40%) -->
                <div class="col-xl-5 col-lg-6">
                    <div class="donut-section h-100">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-header bg-white border-0 pb-0">
                                <div
                                    class="d-flex justify-content-between align-items-start"
                                >
                                    <div>
                                        <h5 class="card-title mb-1">
                                            <i
                                                class="fas fa-chart-pie me-2 text-warning"
                                            ></i>
                                            Phân bổ {{ getPeriodText() }} theo
                                            trạm
                                        </h5>
                                        <p class="text-muted small mb-0">
                                            Tỷ lệ tiền trong
                                            {{ getPeriodText() }} của
                                            {{ totalStations }} trạm
                                        </p>
                                        <!-- Date Range Display for Donut Chart -->
                                        <p
                                            class="text-info small mb-0"
                                            v-if="dateRange"
                                        >
                                            <i class="fas fa-calendar me-1"></i>
                                            {{ formatDateRange() }}
                                        </p>
                                    </div>
                                    <div class="donut-total">
                                        <span class="total-label"
                                            >Tổng {{ getPeriodText() }}</span
                                        >
                                        <span class="total-value">{{
                                            formatCurrency(totalWeeklyAmount)
                                        }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="donut-container">
                                    <!-- Chart.js Donut Chart -->
                                    <div class="donut-chart-wrapper">
                                        <canvas
                                            ref="donutCanvas"
                                            id="donutChart"
                                            style="
                                                max-height: 250px;
                                                width: 100%;
                                            "
                                        ></canvas>
                                    </div>

                                    <!-- Enhanced Donut Legend with Grid Layout -->
                                    <div class="donut-legend-grid">
                                        <div
                                            v-for="(station, index) in stations"
                                            :key="station.name"
                                            class="legend-card"
                                            :class="{
                                                active:
                                                    selectedSegmentIndex ===
                                                    index,
                                            }"
                                            @click="
                                                selectDonutSegment(
                                                    station,
                                                    index
                                                )
                                            "
                                        >
                                            <div class="legend-card-content">
                                                <div class="legend-header">
                                                    <div
                                                        class="legend-color-dot"
                                                        :style="{
                                                            backgroundColor:
                                                                getStationColor(
                                                                    index
                                                                ),
                                                        }"
                                                    ></div>
                                                    <div
                                                        class="legend-percentage-badge"
                                                    >
                                                        {{
                                                            getWeeklyPercentage(
                                                                station.weeklyAmount
                                                            )
                                                        }}%
                                                    </div>
                                                </div>
                                                <div class="legend-body">
                                                    <div
                                                        class="legend-station-title"
                                                    >
                                                        {{ station.name }}
                                                    </div>
                                                    <div
                                                        class="legend-amount-display"
                                                    >
                                                        {{
                                                            formatCurrency(
                                                                station.weeklyAmount
                                                            )
                                                        }}
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Hover Effect Overlay -->
                                            <div
                                                class="legend-card-overlay"
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Divider -->
        <div class="section-divider mb-4">
            <div class="divider-line"></div>
            <div class="divider-text">
                <i class="fas fa-tasks me-2"></i>
                Hạng mục công việc & Lịch thanh toán
            </div>
            <div class="divider-line"></div>
        </div>

        <!-- Second Section: Table และ Calendar -->
        <div class="data-section">
            <div class="row g-4">
                <!-- Table Section (60%) -->
                <div class="col-xl-7 col-lg-6">
                    <div class="table-section h-100">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-header bg-white border-0 pb-0">
                                <div class="d-flex flex-column gap-3">
                                    <!-- Header Info -->
                                    <div
                                        class="d-flex justify-content-between align-items-center"
                                    >
                                        <div>
                                            <h5 class="card-title mb-1">
                                                <i
                                                    class="fas fa-tasks me-2 text-success"
                                                ></i>
                                                Hạng mục công việc
                                            </h5>
                                            <p class="text-muted small mb-0">
                                                Danh sách các công việc và thống
                                                kê cho {{ getPeriodText() }}
                                            </p>
                                            <!-- Date Range Display for Table -->
                                            <p
                                                class="text-info small mb-0"
                                                v-if="dateRange"
                                            >
                                                <i
                                                    class="fas fa-calendar me-1"
                                                ></i>
                                                {{ formatDateRange() }}
                                            </p>
                                        </div>
                                        <div class="table-actions">
                                            <button
                                                class="btn btn-sm btn-outline-primary"
                                                @click="refreshData"
                                                :disabled="tableLoading"
                                            >
                                                <i
                                                    class="fas fa-sync-alt"
                                                    :class="{
                                                        'fa-spin': tableLoading,
                                                    }"
                                                ></i>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Station Filter -->
                                    <div
                                        v-if="availableStations.length > 1"
                                        class="table-filters mb-2"
                                    >
                                        <div class="row align-items-center">
                                            <div class="col-auto">
                                                <label
                                                    class="form-label small fw-bold mb-0"
                                                >
                                                    <i
                                                        class="fas fa-filter me-1"
                                                    ></i>
                                                    Lọc theo trạm:
                                                </label>
                                            </div>
                                            <div class="col-auto">
                                                <select
                                                    v-model="selectedStation"
                                                    @change="onStationChange"
                                                    class="form-select form-select-sm station-filter"
                                                    :disabled="tableLoading"
                                                >
                                                    <option
                                                        v-for="station in availableStations"
                                                        :key="station.code"
                                                        :value="station.code"
                                                    >
                                                        {{ station.name }}
                                                    </option>
                                                </select>
                                            </div>
                                            <div
                                                class="col-auto"
                                                v-if="selectedStation"
                                            >
                                                <button
                                                    @click="clearStationFilter"
                                                    class="btn btn-sm btn-outline-secondary"
                                                    title="ล้างตัวกรอง"
                                                >
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Station Filter Info -->
                                    <div
                                        v-if="
                                            selectedStation &&
                                            getSelectedStationName()
                                        "
                                        class="station-filter-info"
                                    >
                                        <div
                                            class="alert alert-info py-2 px-3 mb-0"
                                        >
                                            <i
                                                class="fas fa-info-circle me-1"
                                            ></i>
                                            <small>
                                                Đang hiển thị dữ liệu của:
                                                <strong>{{
                                                    getSelectedStationName()
                                                }}</strong>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body p-0">
                                <!-- Table Loading State -->
                                <div
                                    v-if="
                                        tableLoading &&
                                        jobCategories.length === 0
                                    "
                                    class="table-loading"
                                >
                                    <div class="text-center py-4">
                                        <i
                                            class="fas fa-spinner fa-spin fa-2x text-primary mb-2"
                                        ></i>
                                        <p class="text-muted mb-0">
                                            Đang tải dữ liệu...
                                        </p>
                                    </div>
                                </div>

                                <!-- Table Error State -->
                                <div v-else-if="tableError" class="table-error">
                                    <div class="text-center py-4">
                                        <i
                                            class="fas fa-exclamation-triangle fa-2x text-warning mb-2"
                                        ></i>
                                        <p class="text-muted mb-2">
                                            {{ tableError }}
                                        </p>
                                        <button
                                            class="btn btn-sm btn-outline-primary"
                                            @click="fetchTableData"
                                        >
                                            <i class="fas fa-retry"></i> Thử lại
                                        </button>
                                    </div>
                                </div>

                                <!-- Table No Data State -->
                                <div
                                    v-else-if="
                                        !tableLoading &&
                                        jobCategories.length === 0
                                    "
                                    class="table-no-data"
                                >
                                    <div class="text-center py-4">
                                        <i
                                            class="fas fa-inbox fa-2x text-muted mb-2"
                                        ></i>
                                        <p class="text-muted mb-0">
                                            Không có dữ liệu cho
                                            {{ getPeriodText() }} này
                                        </p>
                                    </div>
                                </div>

                                <!-- Table Content -->
                                <div v-else class="table-container">
                                    <div class="table-scroll-wrapper">
                                        <table class="table table-hover mb-0">
                                            <thead
                                                class="table-light sticky-top"
                                            >
                                                <tr>
                                                    <th
                                                        scope="col"
                                                        class="text-center"
                                                        style="width: 60px"
                                                    >
                                                        <small class="fw-bold"
                                                            >STT</small
                                                        >
                                                    </th>
                                                    <th
                                                        scope="col"
                                                        style="min-width: 180px"
                                                    >
                                                        <small class="fw-bold"
                                                            >Tên công
                                                            việc</small
                                                        >
                                                    </th>
                                                    <th
                                                        scope="col"
                                                        class="text-center"
                                                        style="width: 80px"
                                                    >
                                                        <small class="fw-bold"
                                                            >Số lượng</small
                                                        >
                                                    </th>
                                                    <th
                                                        scope="col"
                                                        class="text-center"
                                                        style="width: 100px"
                                                    >
                                                        <small class="fw-bold"
                                                            >Đơn vị tính</small
                                                        >
                                                    </th>
                                                    <th
                                                        scope="col"
                                                        class="text-end"
                                                        style="width: 120px"
                                                    >
                                                        <small class="fw-bold">
                                                            Tổng tiền trong
                                                            {{
                                                                getPeriodText()
                                                            }}
                                                        </small>
                                                    </th>
                                                    <th
                                                        scope="col"
                                                        class="text-end"
                                                        style="width: 120px"
                                                    >
                                                        <small class="fw-bold"
                                                            >Lũy kế tiền</small
                                                        >
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr
                                                    v-for="(
                                                        job, index
                                                    ) in jobCategories"
                                                    :key="`job-${index}`"
                                                    class="table-row"
                                                >
                                                    <td class="text-center">
                                                        <span
                                                            class="badge bg-light text-dark rounded-pill"
                                                        >
                                                            {{ index + 1 }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="job-info">
                                                            <div
                                                                class="job-name fw-medium"
                                                            >
                                                                {{ job.name }}
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span
                                                            class="badge bg-info text-white rounded-pill"
                                                        >
                                                            {{
                                                                job.quantity ||
                                                                0
                                                            }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span
                                                            class="unit-badge"
                                                        >
                                                            {{ job.unit || "" }}
                                                        </span>
                                                    </td>
                                                    <td class="text-end">
                                                        <div
                                                            class="amount-display"
                                                        >
                                                            <span
                                                                class="amount-value text-success fw-medium"
                                                            >
                                                                {{
                                                                    formatCurrency(
                                                                        job.weeklyAmount ||
                                                                            0
                                                                    )
                                                                }}
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td class="text-end">
                                                        <div
                                                            class="amount-display"
                                                        >
                                                            <span
                                                                class="amount-value text-danger fw-medium"
                                                            >
                                                                {{
                                                                    formatCurrency(
                                                                        job.cumulativeAmount ||
                                                                            0
                                                                    )
                                                                }}
                                                            </span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                            <tfoot
                                                class="table-light"
                                                v-if="jobCategories.length > 0"
                                            >
                                                <tr>
                                                    <td
                                                        colspan="4"
                                                        class="text-end fw-bold"
                                                    >
                                                        <i
                                                            class="fas fa-calculator me-1"
                                                        ></i>
                                                        Tổng cộng:
                                                    </td>
                                                    <td
                                                        class="text-end fw-bold text-success"
                                                    >
                                                        {{
                                                            formatCurrency(
                                                                totalWeeklyAmountFromTable
                                                            )
                                                        }}
                                                    </td>
                                                    <td
                                                        class="text-end fw-bold text-danger"
                                                    >
                                                        {{
                                                            formatCurrency(
                                                                totalCumulativeAmountFromTable
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
                    </div>
                </div>

                <!-- Calendar Section (40%) -->
                <div class="col-xl-5 col-lg-6">
                    <div class="calendar-section h-100">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-header bg-white border-0 pb-0">
                                <div
                                    class="d-flex justify-content-between align-items-center flex-wrap"
                                >
                                    <div>
                                        <h5 class="card-title mb-1">
                                            <i
                                                class="fas fa-calendar-alt me-2 text-info"
                                            ></i>
                                            {{ currentMonthYear }}
                                        </h5>
                                        <p class="text-muted small mb-0">
                                            Lịch thanh toán dịch vụ và hom giống
                                        </p>
                                    </div>

                                    <!-- Calendar Navigation -->
                                    <div class="calendar-nav">
                                        <div
                                            class="btn-group btn-group-sm"
                                            role="group"
                                        >
                                            <button
                                                type="button"
                                                class="btn btn-outline-info"
                                                @click="goToToday"
                                                title="Hôm nay"
                                            >
                                                <i
                                                    class="fas fa-calendar-day"
                                                ></i>
                                            </button>
                                            <button
                                                type="button"
                                                class="btn btn-outline-info"
                                                @click="previousMonth"
                                                title="Tháng trước"
                                            >
                                                <i
                                                    class="fas fa-chevron-left"
                                                ></i>
                                            </button>
                                            <button
                                                type="button"
                                                class="btn btn-outline-info"
                                                @click="nextMonth"
                                                title="Tháng sau"
                                            >
                                                <i
                                                    class="fas fa-chevron-right"
                                                ></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Calendar Legend -->
                                <div
                                    class="calendar-legend d-none d-lg-flex mt-2"
                                >
                                    <span class="legend-item">
                                        <span
                                            class="legend-color processing-status"
                                        ></span>
                                        Đang xử lý
                                    </span>
                                    <span class="legend-item">
                                        <span
                                            class="legend-color submitted-status"
                                        ></span>
                                        Đã nộp kế toán
                                    </span>
                                    <span class="legend-item">
                                        <span
                                            class="legend-color completed-status"
                                        ></span>
                                        Đã thanh toán
                                    </span>
                                </div>
                            </div>

                            <div class="card-body p-0">
                                <!-- Calendar Grid -->
                                <div class="calendar-grid">
                                    <!-- Week Headers -->
                                    <div class="calendar-header">
                                        <div
                                            class="calendar-day-header"
                                            v-for="day in weekDays"
                                            :key="day"
                                        >
                                            {{ day }}
                                        </div>
                                    </div>
                                    <!-- Calendar Days -->
                                    <div class="calendar-body">
                                        <div
                                            v-for="week in calendarWeeks"
                                            :key="week.week"
                                            class="calendar-week"
                                        >
                                            <div
                                                v-for="day in week.days"
                                                :key="day.date"
                                                class="calendar-day"
                                                :class="{
                                                    'other-month':
                                                        !day.isCurrentMonth,
                                                    today: day.isToday,
                                                    'has-events':
                                                        day.events.length > 0,
                                                    selected:
                                                        selectedDate ===
                                                        day.date,
                                                }"
                                                @click="selectDate(day)"
                                            >
                                                <div class="day-number">
                                                    {{ day.dayNumber }}
                                                </div>
                                                <div
                                                    class="day-events"
                                                    v-if="day.events.length > 0"
                                                >
                                                    <div
                                                        v-for="event in day.events.slice(
                                                            0,
                                                            3
                                                        )"
                                                        :key="event.id"
                                                        class="event-dot"
                                                        :class="
                                                            getEventStatusClass(
                                                                event.status
                                                            )
                                                        "
                                                        :title="`${event.title} - ${event.status}`"
                                                    ></div>
                                                    <div
                                                        v-if="
                                                            day.events.length >
                                                            3
                                                        "
                                                        class="event-more"
                                                        :title="`+${
                                                            day.events.length -
                                                            3
                                                        } thanh toán khác`"
                                                    >
                                                        +{{
                                                            day.events.length -
                                                            3
                                                        }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Day Details Modal -->
    <div
        class="modal fade"
        id="dayDetailsModal"
        tabindex="-1"
        aria-labelledby="dayDetailsModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header bg-gradient-primary">
                    <h5
                        class="modal-title text-white"
                        id="dayDetailsModalLabel"
                    >
                        <i class="fas fa-calendar-day me-2"></i> Chi tiết ngày
                        {{ formatSelectedDate }}
                    </h5>
                    <button
                        type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-0">
                    <!-- Summary Section -->
                    <div class="summary-section p-4">
                        <div class="row gx-3 gy-3">
                            <!-- Total Payments Card -->
                            <div class="col-lg-3 col-md-6">
                                <div class="summary-card total-card">
                                    <div class="summary-icon">
                                        <i class="fas fa-clipboard-list"></i>
                                    </div>
                                    <div class="summary-content">
                                        <div class="summary-label">
                                            Tổng số thanh toán
                                        </div>
                                        <h4 class="summary-value">
                                            {{ selectedDateEvents.length }}
                                        </h4>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Amount Card -->
                            <div class="col-lg-3 col-md-6">
                                <div class="summary-card total-amount-card">
                                    <div class="summary-icon">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </div>
                                    <div class="summary-content">
                                        <div class="summary-label">
                                            Tổng số tiền
                                        </div>
                                        <h4 class="summary-value">
                                            {{ formatCurrency(totalDayAmount) }}
                                        </h4>
                                    </div>
                                </div>
                            </div>

                            <!-- Service Card -->
                            <div class="col-lg-3 col-md-6">
                                <div class="summary-card service-card">
                                    <div class="summary-icon">
                                        <i class="fas fa-tools"></i>
                                    </div>
                                    <div class="summary-content">
                                        <div class="summary-label">Dịch vụ</div>
                                        <h4 class="summary-value">
                                            {{
                                                formatCurrency(
                                                    totalServiceAmount
                                                )
                                            }}
                                        </h4>
                                    </div>
                                </div>
                            </div>

                            <!-- Seed Card -->
                            <div class="col-lg-3 col-md-6">
                                <div class="summary-card seed-card">
                                    <div class="summary-icon">
                                        <i class="fas fa-seedling"></i>
                                    </div>
                                    <div class="summary-content">
                                        <div class="summary-label">
                                            Hom giống
                                        </div>
                                        <h4 class="summary-value">
                                            {{
                                                formatCurrency(totalSeedAmount)
                                            }}
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payments Section -->
                    <div class="payments-section">
                        <!-- Empty State when no payments -->
                        <div
                            v-if="selectedDateEvents.length === 0"
                            class="empty-day-state text-center"
                        >
                            <i
                                class="far fa-calendar-times fa-4x text-muted mb-3"
                            ></i>
                            <h5 class="text-muted">Không có thanh toán</h5>
                            <p class="text-muted">
                                Không có thanh toán nào được lên lịch cho ngày
                                này
                            </p>
                        </div>

                        <!-- Service Payments -->
                        <div
                            v-else-if="dayServicePayments.length > 0"
                            class="payment-category"
                        >
                            <div class="category-header">
                                <h6 class="category-title">
                                    <i class="fas fa-tools me-2 text-info"></i>
                                    Thanh toán dịch vụ ({{
                                        dayServicePayments.length
                                    }})
                                </h6>
                            </div>
                            <div class="payment-list">
                                <div
                                    v-for="payment in dayServicePayments"
                                    :key="payment.id"
                                    class="payment-item service-item"
                                    @click="navigateToServiceDetail(payment)"
                                >
                                    <div class="payment-info">
                                        <div class="payment-title">
                                            {{ payment.title }}
                                        </div>
                                        <div class="payment-meta">
                                            <span class="payment-id">{{
                                                payment.id
                                            }}</span>
                                            <span class="payment-amount">{{
                                                formatCurrency(payment.amount)
                                            }}</span>
                                        </div>
                                    </div>
                                    <div class="payment-status">
                                        <span
                                            class="badge"
                                            :class="
                                                getStatusClass(payment.status)
                                            "
                                            >{{ payment.status }}</span
                                        >
                                    </div>
                                    <div class="payment-action">
                                        <i class="fas fa-chevron-right"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Seed Payments -->
                        <div
                            v-if="daySeedPayments.length > 0"
                            class="payment-category"
                        >
                            <div class="category-header">
                                <h6 class="category-title">
                                    <i
                                        class="fas fa-seedling me-2 text-warning"
                                    ></i>
                                    Thanh toán hom giống ({{
                                        daySeedPayments.length
                                    }})
                                </h6>
                            </div>
                            <div class="payment-list">
                                <div
                                    v-for="payment in daySeedPayments"
                                    :key="payment.id"
                                    class="payment-item seed-item"
                                    @click="navigateToSeedDetail(payment)"
                                >
                                    <div class="payment-info">
                                        <div class="payment-title">
                                            {{ payment.title }}
                                        </div>
                                        <div class="payment-meta">
                                            <span class="payment-id">{{
                                                payment.id
                                            }}</span>
                                            <span class="payment-amount">{{
                                                formatCurrency(payment.amount)
                                            }}</span>
                                        </div>
                                    </div>
                                    <div class="payment-status">
                                        <span
                                            class="badge"
                                            :class="
                                                getStatusClass(payment.status)
                                            "
                                            >{{ payment.status }}</span
                                        >
                                    </div>
                                    <div class="payment-action">
                                        <i class="fas fa-chevron-right"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Đóng
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    BarController,
    ArcElement,
    DoughnutController,
    Title,
    Tooltip,
    Legend,
} from "chart.js";

// Register Chart.js components
ChartJS.register(
    CategoryScale,
    LinearScale,
    BarElement,
    BarController,
    ArcElement,
    DoughnutController,
    Title,
    Tooltip,
    Legend
);

export default {
    name: "Dashboard",
    data() {
        return {
            isLoading: false,
            error: null,
            selectedPeriod: "week",
            dateRange: null,
            // Calendar Modal
            dayDetailsModal: null,

            // API Data for Calendar
            servicePayments: [],
            seedPayments: [],

            // For the selected date events
            selectedDate: new Date().toISOString().split("T")[0],

            // API Data
            stations: [],

            // Chart instances
            chartInstance: null,
            donutChartInstance: null,

            // Chart initialization flags
            chartsReady: false,
            isDestroying: false,
            isComponentMounted: false, // เพิ่มตัวแปรนี้

            // Calendar data
            currentDate: new Date(),
            selectedDate: null,
            weekDays: ["CN", "T2", "T3", "T4", "T5", "T6", "T7"],
            calendarEvents: [],

            // Donut chart
            selectedSegment: null,
            selectedSegmentIndex: null,
            stationColors: [
                "#3b82f6", // Blue
                "#ef4444", // Red
                "#10b981", // Green
                "#f59e0b", // Yellow
                "#8b5cf6", // Purple
                "#f97316", // Orange
            ],

            // Table Section
            jobCategories: [],
            tableLoading: false,
            tableError: null,
            availableStations: [],
            selectedStation: null,
        };
    },
    // ...existing computed properties...
    computed: {
        // Calendar day details computed properties
        selectedDateEvents() {
            if (!this.selectedDate) return [];
            return this.calendarEvents.filter(
                (event) => event.date === this.selectedDate
            );
        },

        dayServicePayments() {
            if (!this.selectedDate) return [];
            return this.servicePayments.filter(
                (payment) => payment.date === this.selectedDate
            );
        },

        daySeedPayments() {
            if (!this.selectedDate) return [];
            return this.seedPayments.filter(
                (payment) => payment.date === this.selectedDate
            );
        },

        totalDayAmount() {
            const serviceTotal = this.dayServicePayments.reduce(
                (sum, payment) => sum + Number(payment.amount || 0),
                0
            );
            const seedTotal = this.daySeedPayments.reduce(
                (sum, payment) => sum + Number(payment.amount || 0),
                0
            );
            return serviceTotal + seedTotal;
        },

        totalServiceAmount() {
            return this.dayServicePayments.reduce(
                (sum, payment) => sum + Number(payment.amount || 0),
                0
            );
        },

        totalSeedAmount() {
            return this.daySeedPayments.reduce(
                (sum, payment) => sum + Number(payment.amount || 0),
                0
            );
        },

        formatSelectedDate() {
            if (!this.selectedDate) return "";
            try {
                const date = new Date(this.selectedDate);
                return date.toLocaleDateString("vi-VN", {
                    year: "numeric",
                    month: "2-digit",
                    day: "2-digit",
                });
            } catch (error) {
                console.error("Error formatting date:", error);
                return this.selectedDate;
            }
        },
        totalJobQuantity() {
            return this.jobCategories.reduce(
                (sum, job) => sum + (Number(job.quantity) || 0),
                0
            );
        },
        totalWeeklyAmountFromTable() {
            return this.jobCategories.reduce(
                (sum, job) => sum + (Number(job.weeklyAmount) || 0),
                0
            );
        },
        totalCumulativeAmountFromTable() {
            return this.jobCategories.reduce(
                (sum, job) => sum + (Number(job.cumulativeAmount) || 0),
                0
            );
        },
        totalStations() {
            return this.stations.length;
        },
        totalQuantity() {
            return this.stations.reduce(
                (sum, station) => sum + (Number(station.quantity) || 0),
                0
            );
        },
        totalWeeklyAmount() {
            return this.stations.reduce(
                (sum, station) => sum + (Number(station.weeklyAmount) || 0),
                0
            );
        },
        totalCumulativeAmount() {
            return this.stations.reduce(
                (sum, station) => sum + (Number(station.cumulativeAmount) || 0),
                0
            );
        },
        totalCumulativeFromStations() {
            return this.stations.reduce(
                (sum, station) => sum + (Number(station.cumulativeAmount) || 0),
                0
            );
        },
        // Calendar computed properties
        currentMonthYear() {
            return this.currentDate.toLocaleDateString("vi-VN", {
                month: "long",
                year: "numeric",
            });
        },
        calendarWeeks() {
            const year = this.currentDate.getFullYear();
            const month = this.currentDate.getMonth();
            const firstDay = new Date(year, month, 1);
            const startDate = new Date(firstDay);
            startDate.setDate(startDate.getDate() - firstDay.getDay());

            const weeks = [];
            let currentDate = new Date(startDate);

            for (let week = 0; week < 6; week++) {
                const days = [];
                for (let day = 0; day < 7; day++) {
                    const dayObj = {
                        date: new Date(currentDate).toISOString().split("T")[0],
                        dayNumber: currentDate.getDate(),
                        isCurrentMonth: currentDate.getMonth() === month,
                        isToday: this.isToday(currentDate),
                        events: this.getEventsForDate(currentDate),
                    };
                    days.push(dayObj);
                    currentDate.setDate(currentDate.getDate() + 1);
                }
                weeks.push({ week, days });
            }
            return weeks;
        },
    },
    methods: {
        // Save current calendar state
        saveCurrentState() {
            const state = {
                currentDate: this.currentDate.toISOString(),
                selectedDate: this.selectedDate,
                timestamp: new Date().getTime(),
            };
            localStorage.setItem("calendar_state", JSON.stringify(state));
        },

        // Navigate to service payment details
        navigateToServiceDetail(payment) {
            try {
                // Close modal before navigating
                if (this.dayDetailsModal) {
                    this.dayDetailsModal.hide();
                }

                // Save current state before navigation
                this.saveCurrentState();

                // Navigate to Details_Phieutrinhthanhtoan
                if (payment.ma_trinh_thanh_toan) {
                    this.$router.push(
                        `/Details_Phieutrinhthanhtoan/${payment.ma_trinh_thanh_toan}`
                    );
                } else if (payment.payment_request_id) {
                    // If no ma_trinh_thanh_toan, use payment_request_id
                    this.$router.push(
                        `/Details_Phieudenghithanhtoandichvu/${payment.payment_request_id}`
                    );
                } else {
                    this.showError("Không tìm thấy thông tin thanh toán");
                }
            } catch (error) {
                console.error("Error navigating to service detail:", error);
                this.showError("Đã xảy ra lỗi khi điều hướng");
            }
        },

        // Navigate to seed payment details
        navigateToSeedDetail(payment) {
            try {
                // Close modal before navigating
                if (this.dayDetailsModal) {
                    this.dayDetailsModal.hide();
                }

                // Save current state before navigation
                this.saveCurrentState();

                // Navigate to Details_Phieutrinhthanhtoanhomgiong
                if (payment.ma_trinh_thanh_toan) {
                    this.$router.push(
                        `/Details_Phieutrinhthanhtoanhomgiong/${payment.ma_trinh_thanh_toan}`
                    );
                } else if (payment.payment_request_id) {
                    this.$router.push(
                        `/Details_Phieudenghithanhtoanhomgiong/${payment.payment_request_id}`
                    );
                } else {
                    this.showError("Không tìm thấy thông tin thanh toán");
                }
            } catch (error) {
                console.error("Error navigating to seed detail:", error);
                this.showError("Đã xảy ra lỗi khi điều hướng");
            }
        },
        // Get status class for payment badges
        getStatusClass(status) {
            switch (status) {
                case "Đã thanh toán":
                    return "badge-success";
                case "Đang xử lý":
                    return "badge-warning";
                case "Đã nộp kế toán":
                    return "badge-info";
                case "Từ chối":
                    return "badge-danger";
                default:
                    return "badge-secondary";
            }
        },

        // Format currency for display
        formatCurrency(amount) {
            if (!amount || isNaN(amount)) return "0 ₭";
            return new Intl.NumberFormat("lo-LA").format(amount) + " ₭";
        },
        // Get authentication token
        getAuthToken() {
            return (
                localStorage.getItem("web_token") ||
                this.$store?.getters?.getToken
            );
        },

        // Create axios config with authentication
        getAxiosConfig(params = {}) {
            const config = {
                headers: {
                    Authorization: `Bearer ${this.getAuthToken()}`,
                    Accept: "application/json",
                    "Content-Type": "application/json",
                },
                params: params,
            };
            return config;
        },

        // Load all calendar data
        async loadCalendarData() {
            try {
                this.isLoading = true;
                const year = this.currentDate.getFullYear();
                const month = this.currentDate.getMonth() + 1;

                // Load calendar events
                await this.loadCalendarEvents(month, year);

                // Load service and seed tracking data
                await Promise.all([
                    this.loadServiceTracking(month, year),
                    this.loadSeedTracking(month, year),
                ]);

                this.isLoading = false;
            } catch (error) {
                console.error("Error loading calendar data:", error);
                this.showError("Đã xảy ra lỗi khi tải dữ liệu lịch");
                this.isLoading = false;

                if (error.response?.status === 401) {
                    this.handleAuthError();
                }
            }
        },

        // Load calendar events
        async loadCalendarEvents(month, year) {
            try {
                const config = this.getAxiosConfig({ month, year });
                const response = await axios.get(
                    "/api/payment-schedule/calendar-events",
                    config
                );

                if (response.data.success) {
                    this.calendarEvents = response.data.data || [];
                } else {
                    this.calendarEvents = [];
                    console.warn(
                        "Calendar events request not successful:",
                        response.data
                    );
                }
                return response;
            } catch (error) {
                console.error("Error loading calendar events:", error);
                this.calendarEvents = [];
                throw error;
            }
        },

        // Load service tracking data
        async loadServiceTracking(month, year) {
            try {
                const config = this.getAxiosConfig({ month, year });
                const response = await axios.get(
                    "/api/payment-schedule/service-tracking",
                    config
                );

                if (response.data.success) {
                    this.servicePayments = response.data.data || [];
                } else {
                    this.servicePayments = [];
                    console.warn(
                        "Service tracking request not successful:",
                        response.data
                    );
                }
                return response;
            } catch (error) {
                console.error("Error loading service tracking:", error);
                this.servicePayments = [];
                throw error;
            }
        },

        // Load seed tracking data
        async loadSeedTracking(month, year) {
            try {
                const config = this.getAxiosConfig({ month, year });
                const response = await axios.get(
                    "/api/payment-schedule/seed-tracking",
                    config
                );

                if (response.data.success) {
                    this.seedPayments = response.data.data || [];
                } else {
                    this.seedPayments = [];
                    console.warn(
                        "Seed tracking request not successful:",
                        response.data
                    );
                }
                return response;
            } catch (error) {
                console.error("Error loading seed tracking:", error);
                this.seedPayments = [];
                throw error;
            }
        },

        // Format date for display
        formatDate(dateStr) {
            try {
                const date = new Date(dateStr);
                return date.toLocaleDateString("vi-VN", {
                    year: "numeric",
                    month: "2-digit",
                    day: "2-digit",
                });
            } catch (error) {
                console.error("Error formatting date:", error);
                return dateStr;
            }
        },

        // Show error message
        showError(message) {
            console.error(message);
            // If you have a notification system, use it here
            if (typeof window !== "undefined") {
                alert(message);
            }
        },

        // Handle authentication error
        handleAuthError() {
            localStorage.removeItem("web_token");
            localStorage.removeItem("web_user");
            if (this.$store) {
                this.$store.commit("logout");
            }
            this.$router.push("/login");
        },
        // แก้ไข destroyCharts method
        destroyCharts() {
            if (this.isDestroying) return;
            this.isDestroying = true;

            // Destroy Bar Chart
            if (this.chartInstance) {
                try {
                    if (this.chartInstance.canvas && this.chartInstance.ctx) {
                        this.chartInstance.stop && this.chartInstance.stop();
                        this.chartInstance.destroy &&
                            this.chartInstance.destroy();
                    }
                } catch (error) {
                    console.warn("Error destroying bar chart:", error);
                }
                this.chartInstance = null;
            }

            // Destroy Donut Chart
            if (this.donutChartInstance) {
                try {
                    if (
                        this.donutChartInstance.canvas &&
                        this.donutChartInstance.ctx
                    ) {
                        this.donutChartInstance.stop &&
                            this.donutChartInstance.stop();
                        this.donutChartInstance.destroy &&
                            this.donutChartInstance.destroy();
                    }
                } catch (error) {
                    console.warn("Error destroying donut chart:", error);
                }
                this.donutChartInstance = null;
            }

            this.chartsReady = false;
            this.isDestroying = false;
        },
        // แก้ไข fetchChartData method
        async fetchChartData() {
            // ป้องกันการเรียกใช้หลายครั้งพร้อมกัน
            if (this.isLoading || !this.isComponentMounted) return;

            this.isLoading = true;
            this.error = null;

            // Destroy existing charts first
            this.destroyCharts();

            try {
                const token =
                    localStorage.getItem("web_token") ||
                    this.$store?.getters?.getToken;

                if (!token) {
                    this.$router.replace("/login");
                    return;
                }

                const response = await axios.get(
                    "/api/dashboard/chart-section",
                    {
                        params: { period: this.selectedPeriod },
                        headers: {
                            Authorization: `Bearer ${token}`,
                            Accept: "application/json",
                            "Content-Type": "application/json",
                        },
                    }
                );

                console.log("API Response:", response.data);
                if (response.data.success) {
                    this.stations = response.data.data.stations || [];
                    this.dateRange = response.data.data.dateRange;

                    // Wait for DOM updates
                    await this.$nextTick();

                    // Destroy charts again to be extra safe
                    this.destroyCharts();

                    // Wait for DOM updates again
                    await this.$nextTick();

                    if (!this.isComponentMounted || this.isDestroying) return;

                    this.initializeCharts();
                } else {
                    this.error = response.data.error || "Không thể tải dữ liệu";
                }
            } catch (error) {
                console.error("Error fetching chart data:", error);

                if (error.response?.status === 401) {
                    this.$router.replace("/login");
                    return;
                }

                this.error =
                    error.response?.data?.message ||
                    error.message ||
                    "Có lỗi xảy ra khi tải dữ liệu";
            } finally {
                this.isLoading = false;
            }
        },

        // แก้ไข initializeCharts method
        initializeCharts() {
            // ตรวจสอบว่า component ยัง mounted และไม่กำลัง destroy
            if (!this.isComponentMounted || this.isDestroying) return;

            // ป้องกันการสร้าง chart ซ้ำ
            if (this.chartsReady) return;

            // ตรวจสอบว่า canvas element พร้อมหรือยัง
            const chartCanvas = this.$refs.chartCanvas;
            const donutCanvas = this.$refs.donutCanvas;

            // ถ้ายังไม่มี canvas ให้รอแล้วเรียกซ้ำ
            if (!chartCanvas || !donutCanvas) {
                setTimeout(() => {
                    if (this.isComponentMounted && !this.isDestroying) {
                        this.initializeCharts();
                    }
                }, 200);
                return;
            }

            // ตรวจสอบ context
            let chartCtx, donutCtx;
            try {
                chartCtx = chartCanvas.getContext("2d");
                donutCtx = donutCanvas.getContext("2d");
            } catch (e) {
                setTimeout(() => {
                    if (this.isComponentMounted && !this.isDestroying) {
                        this.initializeCharts();
                    }
                }, 200);
                return;
            }
            if (!chartCtx || !donutCtx) {
                setTimeout(() => {
                    if (this.isComponentMounted && !this.isDestroying) {
                        this.initializeCharts();
                    }
                }, 200);
                return;
            }

            this.chartsReady = true;
            this.createBarChart();
            this.createDonutChart();
        },

        // แก้ไข createBarChart method
        createBarChart() {
            // ตรวจสอบสถานะ component
            if (!this.isComponentMounted || this.isDestroying) {
                console.log(
                    "Component not mounted or being destroyed, skipping bar chart creation"
                );
                return;
            }

            const chartCanvas = this.$refs.chartCanvas;
            if (!chartCanvas || !document.contains(chartCanvas)) {
                console.warn("Chart canvas not available for bar chart");
                return;
            }

            // Destroy existing chart safely
            if (this.chartInstance) {
                try {
                    this.chartInstance.stop();
                    this.chartInstance.destroy();
                } catch (error) {
                    console.warn("Error destroying existing bar chart:", error);
                } finally {
                    this.chartInstance = null;
                }
            }

            if (this.stations.length === 0) {
                console.log("No stations data available for bar chart");
                return;
            }

            let ctx;
            try {
                ctx = chartCanvas.getContext("2d");
                if (!ctx) {
                    console.error("Failed to get 2D context from chart canvas");
                    return;
                }
            } catch (error) {
                console.error("Error getting canvas context:", error);
                return;
            }

            const labels = this.stations.map((station) => station.name);
            const quantityData = this.stations.map(
                (station) => station.quantity
            );
            const weeklyAmountData = this.stations.map(
                (station) => station.weeklyAmount / 1000000
            );
            const cumulativeAmountData = this.stations.map(
                (station) => station.cumulativeAmount / 1000000
            );

            console.log("Creating bar chart with data:", {
                labels,
                quantityData,
                weeklyAmountData,
                cumulativeAmountData,
            });

            try {
                this.chartInstance = new ChartJS(ctx, {
                    type: "bar",
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: "Số lượng",
                                data: quantityData,
                                backgroundColor: "rgba(59, 130, 246, 0.8)",
                                borderColor: "rgba(59, 130, 246, 1)",
                                borderWidth: 2,
                                yAxisID: "y1",
                            },
                            {
                                label: `Tổng tiền trong ${this.getPeriodText()} (triệu ₭)`,
                                data: weeklyAmountData,
                                backgroundColor: "rgba(16, 185, 129, 0.8)",
                                borderColor: "rgba(16, 185, 129, 1)",
                                borderWidth: 2,
                                yAxisID: "y",
                            },
                            {
                                label: "Lũy kế tiền (triệu ₭)",
                                data: cumulativeAmountData,
                                backgroundColor: "rgba(245, 158, 11, 0.8)",
                                borderColor: "rgba(245, 158, 11, 1)",
                                borderWidth: 2,
                                yAxisID: "y",
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: {
                            duration: 1000,
                            onComplete: () => {
                                // ตรวจสอบว่า component ยังคง mounted เมื่อ animation เสร็จ
                                if (!this.isComponentMounted) {
                                    console.log(
                                        "Component unmounted during animation"
                                    );
                                }
                            },
                        },
                        interaction: {
                            mode: "index",
                            intersect: false,
                        },
                        plugins: {
                            title: {
                                display: false,
                            },
                            legend: {
                                position: "bottom",
                                labels: {
                                    usePointStyle: true,
                                    padding: 20,
                                    font: {
                                        size: 12,
                                    },
                                },
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        let label = context.dataset.label || "";
                                        if (label) {
                                            label += ": ";
                                        }
                                        if (context.datasetIndex === 0) {
                                            label += context.parsed.y;
                                        } else {
                                            label +=
                                                new Intl.NumberFormat(
                                                    "vi-VN"
                                                ).format(
                                                    context.parsed.y * 1000000
                                                ) + " ₭";
                                        }
                                        return label;
                                    },
                                },
                            },
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false,
                                },
                                ticks: {
                                    font: {
                                        size: 11,
                                    },
                                },
                            },
                            y: {
                                type: "linear",
                                display: true,
                                position: "left",
                                title: {
                                    display: true,
                                    text: "Số tiền (triệu ₭)",
                                    font: {
                                        size: 12,
                                    },
                                },
                                ticks: {
                                    callback: function (value) {
                                        return value.toFixed(1) + "M";
                                    },
                                },
                            },
                            y1: {
                                type: "linear",
                                display: true,
                                position: "right",
                                title: {
                                    display: true,
                                    text: "Số lượng",
                                    font: {
                                        size: 12,
                                    },
                                },
                                grid: {
                                    drawOnChartArea: false,
                                },
                            },
                        },
                    },
                });

                console.log("Bar chart created successfully");
            } catch (error) {
                console.error("Error creating bar chart:", error);
                this.error = "Lỗi khi tạo biểu đồ cột: " + error.message;
                this.chartInstance = null;
            }
        },

        // แก้ไข createDonutChart method
        createDonutChart() {
            // ตรวจสอบสถานะ component
            if (!this.isComponentMounted || this.isDestroying) {
                console.log(
                    "Component not mounted or being destroyed, skipping donut chart creation"
                );
                return;
            }

            const donutCanvas = this.$refs.donutCanvas;
            if (!donutCanvas || !document.contains(donutCanvas)) {
                console.warn("Donut canvas not available for donut chart");
                return;
            }

            // Destroy existing chart safely
            if (this.donutChartInstance) {
                try {
                    this.donutChartInstance.stop();
                    this.donutChartInstance.destroy();
                } catch (error) {
                    console.warn(
                        "Error destroying existing donut chart:",
                        error
                    );
                } finally {
                    this.donutChartInstance = null;
                }
            }

            if (this.stations.length === 0) {
                console.log("No stations data available for donut chart");
                return;
            }

            const data = this.stations.map((station) => station.weeklyAmount);
            const labels = this.stations.map((station) => station.name);
            const colors = this.stations.map((_, index) =>
                this.getStationColor(index)
            );

            const totalAmount = data.reduce((sum, amount) => sum + amount, 0);
            if (totalAmount === 0) {
                console.log(
                    "All weekly amounts are zero, skipping donut chart"
                );
                return;
            }

            let ctx;
            try {
                ctx = donutCanvas.getContext("2d");
                if (!ctx) {
                    console.error("Failed to get 2D context from donut canvas");
                    return;
                }
            } catch (error) {
                console.error("Error getting donut canvas context:", error);
                return;
            }

            try {
                this.donutChartInstance = new ChartJS(ctx, {
                    type: "doughnut",
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                data: data,
                                backgroundColor: colors,
                                borderColor: colors.map((color) => color),
                                borderWidth: 2,
                                hoverBorderWidth: 4,
                                hoverOffset: 10,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: {
                            duration: 1000,
                            onComplete: () => {
                                // ตรวจสอบว่า component ยังคง mounted เมื่อ animation เสร็จ
                                if (!this.isComponentMounted) {
                                    console.log(
                                        "Component unmounted during donut animation"
                                    );
                                }
                            },
                        },
                        plugins: {
                            legend: {
                                display: false,
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        const label = context.label || "";
                                        const value = context.parsed;
                                        const total =
                                            context.dataset.data.reduce(
                                                (a, b) => a + b,
                                                0
                                            );
                                        const percentage =
                                            total > 0
                                                ? (
                                                      (value / total) *
                                                      100
                                                  ).toFixed(1)
                                                : "0.0";
                                        return `${label}: ${new Intl.NumberFormat(
                                            "vi-VN"
                                        ).format(value)} ₭ (${percentage}%)`;
                                    },
                                },
                            },
                        },
                        cutout: "60%",
                        onHover: (event, activeElements) => {
                            // ตรวจสอบว่า component ยังคง mounted
                            if (!this.isComponentMounted) return;

                            if (activeElements.length > 0) {
                                const index = activeElements[0].index;
                                this.selectedSegmentIndex = index;
                                this.selectedSegment = this.stations[index];
                            }
                        },
                    },
                });

                console.log("Donut chart created successfully");
            } catch (error) {
                console.error("Error creating donut chart:", error);
                this.error = "Lỗi khi tạo biểu đồ donut: " + error.message;
                this.donutChartInstance = null;
            }
        },

        // แก้ไข selectDonutSegment method
        selectDonutSegment(station, index) {
            if (!this.isComponentMounted) return;

            this.selectedSegment = station;
            this.selectedSegmentIndex = index;

            // Safely highlight segment in chart
            if (
                this.donutChartInstance &&
                this.donutChartInstance.canvas &&
                document.contains(this.donutChartInstance.canvas)
            ) {
                try {
                    this.donutChartInstance.setActiveElements([
                        {
                            datasetIndex: 0,
                            index: index,
                        },
                    ]);
                    this.donutChartInstance.update();
                } catch (error) {
                    console.warn(
                        "Error updating donut chart active elements:",
                        error
                    );
                }
            }
        },

        // ...existing methods...

        // Station Filter Methods
        async fetchAvailableStations() {
            try {
                const token =
                    localStorage.getItem("web_token") ||
                    this.$store?.getters?.getToken;

                if (!token) {
                    this.$router.replace("/login");
                    return;
                }

                const response = await axios.get(
                    "/api/dashboard/available-stations",
                    {
                        headers: {
                            Authorization: `Bearer ${token}`,
                            Accept: "application/json",
                            "Content-Type": "application/json",
                        },
                    }
                );

                if (response.data.success) {
                    this.availableStations = response.data.data || [];
                    if (this.availableStations.length > 0) {
                        this.selectedStation = this.availableStations[0].code;
                    }
                }
            } catch (error) {
                console.error("Error fetching available stations:", error);
            }
        },

        onStationChange() {
            console.log("Station changed to:", this.selectedStation);
            this.fetchTableData();
        },

        clearStationFilter() {
            this.selectedStation = null;
            this.fetchTableData();
        },

        getSelectedStationName() {
            if (!this.selectedStation) return null;
            const station = this.availableStations.find(
                (s) => s.code === this.selectedStation
            );
            return station ? station.name : null;
        },

        async fetchTableData() {
            if (this.tableLoading) return;

            this.tableLoading = true;
            this.tableError = null;

            try {
                const token =
                    localStorage.getItem("web_token") ||
                    this.$store?.getters?.getToken;

                if (!token) {
                    this.$router.replace("/login");
                    return;
                }

                const params = {
                    period: this.selectedPeriod,
                };

                if (this.selectedStation) {
                    params.station = this.selectedStation;
                }

                const response = await axios.get(
                    "/api/dashboard/table-section",
                    {
                        params: params,
                        headers: {
                            Authorization: `Bearer ${token}`,
                            Accept: "application/json",
                            "Content-Type": "application/json",
                        },
                    }
                );

                console.log("Table API Response:", response.data);

                if (response.data.success) {
                    this.jobCategories = response.data.data.jobCategories || [];

                    if (response.data.data.availableStations) {
                        this.availableStations =
                            response.data.data.availableStations;
                    }

                    console.log("Job categories updated:", this.jobCategories);
                } else {
                    this.tableError =
                        response.data.error || "Không thể tải dữ liệu bảng";
                }
            } catch (error) {
                console.error("Error fetching table data:", error);

                if (error.response?.status === 401) {
                    this.$router.replace("/login");
                    return;
                }

                this.tableError =
                    error.response?.data?.message ||
                    error.message ||
                    "Có lỗi xảy ra khi tải dữ liệu bảng";
            } finally {
                this.tableLoading = false;
            }
        },

        // แก้ไข refresh method
        async refreshData() {
            if (!this.isComponentMounted) return;
            await Promise.all([this.fetchChartData(), this.fetchTableData()]);
        },

        // Utility Methods
        formatDateRange() {
            if (!this.dateRange) return "";

            const formatDate = (dateStr) => {
                return new Date(dateStr).toLocaleDateString("vi-VN");
            };

            return `${formatDate(this.dateRange.start)} - ${formatDate(
                this.dateRange.end
            )}`;
        },

        getPeriodText() {
            const periods = {
                week: "tuần",
                month: "tháng",
                year: "năm",
            };
            return periods[this.selectedPeriod] || "tuần";
        },

        formatCurrency(amount) {
            // ตรวจสอบและแปลงค่าให้เป็นตัวเลข
            const numAmount = Number(amount);
            if (
                isNaN(numAmount) ||
                numAmount === null ||
                numAmount === undefined
            ) {
                return "0 ₭";
            }
            return new Intl.NumberFormat("lo-LA").format(numAmount) + " ₭";
        },

        // Calendar methods
        isToday(date) {
            const today = new Date();
            return date.toDateString() === today.toDateString();
        },
        getEventsForDate(date) {
            const dateStr = date.toISOString().split("T")[0];
            return this.calendarEvents.filter(
                (event) => event.date === dateStr
            );
        },
        selectDate(day) {
            if (day.isCurrentMonth) {
                this.selectedDate = day.date;
            }
        },
        goToToday() {
            this.currentDate = new Date();
            this.selectedDate = new Date().toISOString().split("T")[0];
        },
        previousMonth() {
            this.currentDate = new Date(
                this.currentDate.getFullYear(),
                this.currentDate.getMonth() - 1,
                1
            );
        },
        nextMonth() {
            this.currentDate = new Date(
                this.currentDate.getFullYear(),
                this.currentDate.getMonth() + 1,
                1
            );
        },
        getEventStatusClass(status) {
            switch (status) {
                case "Đã thanh toán":
                    return "completed-status";
                case "Đang xử lý":
                    return "processing-status";
                case "Đã nộp kế toán":
                    return "submitted-status";
                default:
                    return "default-status";
            }
        },

        // Donut chart methods
        getStationColor(index) {
            return this.stationColors[index % this.stationColors.length];
        },
        getPercentage(amount) {
            if (this.totalCumulativeFromStations === 0) return "0.0";
            return ((amount / this.totalCumulativeFromStations) * 100).toFixed(
                1
            );
        },
        getWeeklyPercentage(amount) {
            if (this.totalWeeklyAmount === 0) return "0.0";
            return ((amount / this.totalWeeklyAmount) * 100).toFixed(1);
        },
    },

    async mounted() {
        console.log("Dashboard mounted");
        this.isComponentMounted = true; // ตั้งค่าว่า component ถูก mount แล้ว
        this.selectedDate = new Date().toISOString().split("T")[0];

        // Wait for DOM to be ready
        await this.$nextTick();
        // Initialize Bootstrap modal with dynamic import
        try {
            const bootstrap = await import(
                "bootstrap/dist/js/bootstrap.bundle.min.js"
            );
            if (document.getElementById("dayDetailsModal")) {
                this.dayDetailsModal = new bootstrap.Modal(
                    document.getElementById("dayDetailsModal")
                );
            }
        } catch (error) {
            console.error("Error initializing modal:", error);
        }

        // Load calendar data
        await this.loadCalendarData();

        // Fetch available stations first, then fetch data
        await this.fetchAvailableStations();

        // Fetch data which will initialize charts and table
        await Promise.all([this.fetchChartData(), this.fetchTableData()]);
    },

    watch: {
        selectedPeriod: {
            handler(newPeriod, oldPeriod) {
                if (!this.isComponentMounted) return;
                console.log(`Period changed from ${oldPeriod} to ${newPeriod}`);
                this.fetchChartData();
                this.fetchTableData();
            },
            immediate: false,
        },
        // Watch for calendar month changes
        currentDate: {
            handler(newDate, oldDate) {
                // Only reload if the month or year has changed
                if (
                    newDate.getMonth() !== oldDate.getMonth() ||
                    newDate.getFullYear() !== oldDate.getFullYear()
                ) {
                    this.loadCalendarData();
                }
            },
            deep: true,
        },

        // Watch for selected date changes to show modal
        selectedDate(newDate) {
            if (newDate) {
                // Show day details modal when a date is selected
                if (this.dayDetailsModal) {
                    this.dayDetailsModal.show();
                }
            }
        },
    },

    beforeUnmount() {
        console.log("Dashboard before unmount - destroying charts");
        this.isComponentMounted = false; // ตั้งค่าว่า component กำลังจะถูก unmount
        this.destroyCharts();
    },
};
</script>

<!-- Keep the same template and styles -->

<style scoped>
/* Enhanced Dashboard Styles */
.dashboard-container {
    padding: 1.5rem;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: 100vh;
}

/* Loading Overlay */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.loading-spinner {
    text-align: center;
    padding: 2rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

/* Header Section */
.dashboard-header {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.dashboard-title {
    color: #2c3e50;
    font-weight: 700;
    font-size: 2rem;
}

.dashboard-subtitle {
    font-size: 1.1rem;
    margin-bottom: 0;
}

.stat-card {
    background: #01902d;
    color: white;
    padding: 1rem;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    min-width: 80px;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1;
}

.stat-label {
    font-size: 0.8rem;
    opacity: 0.9;
    margin-top: 0.25rem;
}

/* Enhanced Card Styles */
.card {
    border-radius: 16px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border: none;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12) !important;
}

.card-header {
    border-radius: 16px 16px 0 0 !important;
    padding: 1.5rem;
    background: white !important;
    border-bottom: 1px solid rgba(0, 0, 0, 0.06) !important;
}

.card-title {
    color: #2c3e50;
    font-weight: 600;
    font-size: 1.1rem;
}

/* Section Heights */
.charts-section,
.data-section {
    min-height: 500px;
}

.chart-section,
.donut-section,
.table-section,
.calendar-section {
    height: 100%;
}

.chart-section .card,
.donut-section .card,
.table-section .card,
.calendar-section .card {
    height: 100%;
    min-height: 500px;
    display: flex;
    flex-direction: column;
}

.card-body {
    flex: 1;
    display: flex;
    flex-direction: column;
}

/* Chart Container */
.chart-container {
    flex: 1;
    position: relative;
    min-height: 350px;
}

.chart-wrapper {
    position: relative;
    height: 100%;
    width: 100%;
}

.no-data-message {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    text-align: center;
}

/* Donut Chart */
.donut-container {
    height: 100%;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.donut-chart-wrapper {
    position: relative;
    height: 280px;
    width: 100%;
    flex-shrink: 0;
}

.donut-total {
    text-align: right;
}

.total-label {
    display: block;
    font-size: 0.8rem;
    color: #6b7280;
}

.total-value {
    display: block;
    font-size: 1rem;
    font-weight: 700;
    color: #059669;
}

/* Enhanced Donut Legend Grid Styles */
.donut-legend-grid {
    padding: 0.5rem 0.5rem;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    grid-template-rows: repeat(2, 1fr);
    gap: 0.75rem;
    margin-top: 1rem;
    flex: 1;
    min-height: 160px;
    max-height: 200px;
    overflow: hidden;
}

.legend-card {
    position: relative;
    background: #ffffff;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 0.875rem;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 70px;
}

.legend-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border-color: #6b7280;
}

.legend-card.active {
    border-color: #3b82f6;
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(59, 130, 246, 0.25);
}

.legend-card-content {
    position: relative;
    z-index: 2;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.legend-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.legend-color-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    flex-shrink: 0;
    box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.8);
}

.legend-percentage-badge {
    background: #f3f4f6;
    color: #374151;
    font-size: 0.7rem;
    font-weight: 600;
    padding: 0.125rem 0.375rem;
    border-radius: 6px;
    line-height: 1;
}

.legend-card.active .legend-percentage-badge {
    background: #3b82f6;
    color: white;
}

.legend-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.legend-station-title {
    font-weight: 600;
    color: #1f2937;
    font-size: 0.75rem;
    line-height: 1.2;
    margin-bottom: 0.25rem;
    word-break: break-word;
}

.legend-amount-display {
    font-size: 0.7rem;
    color: #059669;
    font-weight: 600;
    line-height: 1;
}

.legend-card.active .legend-station-title {
    color: #1e40af;
}

.legend-card.active .legend-amount-display {
    color: #047857;
}

/* Hover Effect Overlay */
.legend-card-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(
        135deg,
        rgba(59, 130, 246, 0.05) 0%,
        rgba(16, 185, 129, 0.05) 100%
    );
    opacity: 0;
    transition: opacity 0.3s ease;
    border-radius: 10px;
}

.legend-card:hover .legend-card-overlay {
    opacity: 1;
}

.legend-card.active .legend-card-overlay {
    opacity: 1;
    background: linear-gradient(
        135deg,
        rgba(59, 130, 246, 0.1) 0%,
        rgba(16, 185, 129, 0.1) 100%
    );
}

/* Time Selector */
.time-selector .btn {
    border-radius: 8px;
    padding: 0.5rem 1rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.time-selector .btn-check:checked + .btn {
    background: #01902d;
    border-color: transparent;
    color: white;
}

/* Table Styles */
.table-container {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.table-scroll-wrapper {
    flex: 1;
    overflow-y: auto;
    overflow-x: auto;
    border-radius: 0 0 16px 16px;
    max-height: 400px;
}

.table {
    font-size: 0.7rem;
    margin-bottom: 0;
}

.table thead th {
    border-bottom: 2px solid #dee2e6;
    background: #f8f9fa !important;
    color: #495057;
    font-weight: 600;
    position: sticky;
    top: 0;
    z-index: 10;
}

.table-row {
    transition: all 0.2s ease;
}

.table-row:hover {
    background-color: rgba(102, 126, 234, 0.05);
    transform: scale(1.005);
}

.job-info {
    padding: 0.25rem 0;
}

.job-name {
    color: #2c3e50;
    font-size: 0.7rem;
    margin-bottom: 0.125rem;
}

.unit-badge {
    background: #e9ecef;
    color: #495057;
    padding: 0.25rem 0.25rem;
    border-radius: 6px;
    /* font-size: 0.75rem; */
    font-weight: 500;
}

.amount-value {
    font-size: 0.7rem;
    white-space: nowrap;
}

/* Table Filter Styles */
.table-filters {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 0.75rem;
    border: 1px solid #e9ecef;
}

.station-filter {
    min-width: 150px;
    border-radius: 6px;
    border: 1px solid #ced4da;
    font-size: 0.875rem;
}

.station-filter:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.station-filter-info .alert {
    border-radius: 6px;
    border: 1px solid #b6d7ff;
    background-color: #f0f8ff;
    color: #0c5aa6;
}

/* Table Loading/Error States */
.table-loading,
.table-error,
.table-no-data {
    min-height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 1;
}

/* Calendar Styles */
.calendar-section .card {
    border: none;
    border-radius: 16px;
    overflow: hidden;
}

.calendar-grid {
    flex: 1;
    display: flex;
    flex-direction: column;
    height: 400px;
}

.calendar-header {
    height: 50px;
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    background-color: #f8f9fa;
    border-bottom: 2px solid #e9ecef;
    flex-shrink: 0;
}

.calendar-day-header {
    padding: 1rem;
    text-align: center;
    font-weight: 600;
    color: #495057;
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.calendar-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    height: 350px;
}

.calendar-week {
    flex: 1;
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    border-bottom: 1px solid #e9ecef;
    min-height: 0;
}

.calendar-day {
    padding: 0.5rem;
    border-right: 1px solid #e9ecef;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
    display: flex;
    flex-direction: column;
    min-height: 0;
    overflow: hidden;
}

.calendar-day:hover {
    background-color: #f8f9fa;
}

.calendar-day.today {
    background-color: #dcfce7;
    border: 2px solid #10b981;
}

.calendar-day.selected {
    background-color: #bbf7d0;
}

.calendar-day.other-month {
    opacity: 0.3;
    cursor: not-allowed;
}

.calendar-day.has-events {
    background-color: #fef3c7;
}

.day-number {
    font-weight: 600;
    margin-bottom: 0.25rem;
    color: #333;
    font-size: 0.875rem;
}

.day-events {
    display: flex;
    flex-wrap: wrap;
    gap: 2px;
}

.event-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    cursor: pointer;
}

.event-more {
    font-size: 0.6rem;
    color: #666;
    font-weight: 500;
}

/* Calendar Navigation */
.calendar-nav .btn {
    border-color: #17a2b8;
    color: #17a2b8;
    margin: 0 2px;
}

.calendar-nav .btn:hover {
    background-color: #17a2b8;
    border-color: #17a2b8;
    color: white;
}

/* Calendar Legend */
.calendar-legend {
    gap: 1rem;
    margin-top: 0.5rem;
}

.calendar-legend .legend-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
}

.calendar-legend .legend-color {
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.processing-status {
    background-color: #f59e0b;
}

.submitted-status {
    background-color: #3b82f6;
}

.completed-status {
    background-color: #10b981;
}

/* Section Divider */
.section-divider {
    display: flex;
    align-items: center;
    margin: 2rem 0 1.5rem 0;
}

.divider-line {
    flex: 1;
    height: 2px;
    background: linear-gradient(
        90deg,
        transparent 0%,
        #e2e8f0 50%,
        transparent 100%
    );
}

.divider-text {
    padding: 0 2rem;
    background: white;
    color: #64748b;
    font-weight: 600;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    border: 2px solid #e2e8f0;
    border-radius: 50px;
    white-space: nowrap;
}

/* Scrollbar Styles */
.table-scroll-wrapper::-webkit-scrollbar,
.donut-legend::-webkit-scrollbar {
    width: 4px;
}

.table-scroll-wrapper::-webkit-scrollbar-track,
.donut-legend::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.table-scroll-wrapper::-webkit-scrollbar-thumb,
.donut-legend::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.table-scroll-wrapper::-webkit-scrollbar-thumb:hover,
.donut-legend::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card {
    animation: fadeInUp 0.6s ease forwards;
}

.charts-section {
    animation: fadeInUp 0.6s ease forwards;
}

.data-section {
    animation: fadeInUp 0.8s ease forwards;
}

/* Responsive Design */
@media (max-width: 1399px) {
    .donut-chart-wrapper {
        height: 250px;
    }

    .donut-legend {
        max-height: 150px;
    }
}

@media (max-width: 1199px) {
    .chart-wrapper {
        height: 320px;
    }

    .donut-chart-wrapper {
        height: 220px;
    }

    .donut-legend {
        max-height: 120px;
    }

    .calendar-grid {
        height: 380px;
    }

    .calendar-body {
        height: 330px;
    }
}

@media (max-width: 991px) {
    .charts-section .card,
    .data-section .card {
        min-height: 450px;
        margin-bottom: 1.5rem;
    }

    .chart-wrapper {
        height: 280px;
    }

    .donut-chart-wrapper {
        height: 200px;
    }

    .donut-legend {
        max-height: 100px;
    }

    .calendar-grid {
        height: 360px;
    }

    .calendar-body {
        height: 310px;
    }

    .table-scroll-wrapper {
        max-height: 350px;
    }
}

@media (max-width: 768px) {
    .dashboard-container {
        padding: 1rem;
    }

    .dashboard-title {
        font-size: 1.5rem;
    }

    .dashboard-subtitle {
        font-size: 1rem;
    }

    .chart-wrapper {
        height: 250px;
    }

    .donut-chart-wrapper {
        height: 180px;
    }

    .donut-legend {
        max-height: 80px;
    }

    .calendar-grid {
        height: 320px;
    }

    .calendar-body {
        height: 270px;
    }

    .table-scroll-wrapper {
        max-height: 300px;
    }

    .section-divider {
        margin: 1.5rem 0 1rem 0;
    }

    .divider-text {
        font-size: 0.9rem;
        padding: 0 1rem;
    }

    .calendar-legend {
        display: none !important;
    }

    .donut-total {
        text-align: center;
        margin-top: 0.5rem;
    }

    .legend-values {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }
}

@media (max-width: 576px) {
    .charts-section .card,
    .data-section .card {
        min-height: 400px;
    }

    .chart-wrapper {
        height: 350px;
    }

    .donut-chart-wrapper {
        height: 160px;
    }

    .calendar-grid {
        height: 280px;
    }

    .calendar-body {
        height: 230px;
    }

    .table-scroll-wrapper {
        max-height: 250px;
    }

    .stat-card {
        min-width: 70px;
    }

    .stat-number {
        font-size: 1.25rem;
    }

    .dashboard-stats {
        justify-content: center !important;
    }
    .legend-amount-display {
        font-size: 8px;
        color: #059669;
        font-weight: 600;
        line-height: 1;
    }
}

/* Loading Animation */
.fa-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

/* Enhanced Button Styles */
.table-actions .btn {
    border-radius: 8px;
    width: 36px;
    height: 36px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-outline-secondary {
    border-color: #6c757d;
    color: #6c757d;
}

.btn-outline-secondary:hover {
    background-color: #6c757d;
    border-color: #6c757d;
    color: white;
}

.form-label.small {
    font-size: 0.875rem;
    color: #495057;
    margin-bottom: 0;
}

/* Day Details Modal Styles */
.bg-gradient-primary {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
}

.btn-close-white {
    filter: invert(1) grayscale(100%) brightness(200%);
}

.summary-section {
    background-color: #f8f9fa !important;
}

.summary-card {
    background: white;
    border-radius: 15px;
    padding: 1.25rem;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.3s ease;
}

.summary-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
}

.summary-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.summary-content {
    flex: 1;
}

.summary-label {
    color: #6b7280;
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
}

.summary-value {
    color: #1f2937;
    font-weight: 600;
    margin: 0;
    font-size: 1rem;
}

/* Summary Card Colors */
.total-card .summary-icon {
    background-color: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
}

.total-amount-card .summary-icon {
    background-color: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.service-card .summary-icon {
    background-color: rgba(14, 165, 233, 0.1);
    color: #0ea5e9;
}

.seed-card .summary-icon {
    background-color: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
}

/* Payments Section */
.payments-section {
    background: white;
}

.payment-category {
    padding: 1rem 0;
}

.payment-category:last-child {
    border-bottom: 0;
}

.category-header {
    padding: 0.5rem 1.5rem;
    border-bottom: 1px solid #f1f5f9;
    background: #f8fafc;
}

.category-title {
    color: #1f2937;
    margin: 0;
    font-weight: 600;
    font-size: 1rem;
}

.payment-list {
    max-height: 300px;
    overflow-y: auto;
}

.payment-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 1.5rem;
    border-bottom: 1px solid #f1f5f9;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
}

.payment-item:hover {
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    transform: translateX(8px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.payment-item:last-child {
    border-bottom: none;
}

.payment-item::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: transparent;
    transition: background 0.2s ease;
}

.service-item:hover::before {
    background: linear-gradient(180deg, #06b6d4 0%, #0891b2 100%);
}

.seed-item:hover::before {
    background: linear-gradient(180deg, #f59e0b 0%, #d97706 100%);
}

.payment-info {
    flex: 1;
}

.payment-title {
    font-weight: 500;
    color: #1f2937;
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.payment-meta {
    display: flex;
    align-items: center;
    gap: 1rem;
    font-size: 0.8rem;
}

.payment-id {
    color: #6b7280;
}

.payment-amount {
    font-weight: 600;
    color: #059669;
}

.payment-status {
    margin-left: 1rem;
}

.payment-status .badge {
    padding: 0.35em 0.65em;
    font-size: 0.75em;
    font-weight: 500;
}

.payment-action {
    margin-left: 0.75rem;
    color: #9ca3af;
    transition: transform 0.2s ease;
}

.payment-item:hover .payment-action {
    color: #3b82f6;
    transform: translateX(3px);
}

/* Status badge classes */
.badge-success {
    background-color: #10b981;
    color: white;
}

.badge-warning {
    background-color: #f59e0b;
    color: white;
}

.badge-info {
    background-color: #0ea5e9;
    color: white;
}

.badge-danger {
    background-color: #ef4444;
    color: white;
}

.badge-secondary {
    background-color: #6b7280;
    color: white;
}

/* Empty Day State */
.empty-day-state {
    padding: 3rem 1.5rem;
    background: white;
}

.empty-day-state i {
    color: #d1d5db;
}

.empty-day-state h5 {
    margin-top: 1rem;
    font-weight: 600;
}

.getStatusClass {
    background-color: #6b7280;
    color: white;
}

/* Animation for modal items */
@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translate3d(0, 30px, 0);
    }
    to {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }
}

.summary-card,
.payment-item {
    animation: slideInUp 0.4s ease-out;
}
</style>
