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
                        <i class="fas fa-chart-line me-3 text-primary"></i>
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

        <!-- Main Content -->
        <div class="main-content mb-2">
            <div class="row g-4">
                <!-- Chart Section (60%) -->
                <div class="col-xl-7 col-lg-6">
                    <div class="chart-section">
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
                                                max-height: 300px;
                                                width: 100%;
                                            "
                                        ></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Section (40%) -->
                <div class="col-xl-5 col-lg-6">
                    <div class="table-section">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-header bg-white border-0 pb-0">
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
                                            Danh sách các công việc và thống kê
                                            cho {{ getPeriodText() }}
                                        </p>
                                        <!-- Date Range Display for Table -->
                                        <p
                                            class="text-info small mb-0"
                                            v-if="dateRange"
                                        >
                                            <i class="fas fa-calendar me-1"></i>
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
                                                        <small class="fw-bold"
                                                            >Tổng tiền trong
                                                            {{
                                                                getPeriodText()
                                                            }}</small
                                                        >
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
                                                            >{{
                                                                job.unit ||
                                                                "Đơn vị"
                                                            }}</span
                                                        >
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
                                                                class="amount-value text-warning fw-medium"
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
                                                        class="text-end fw-bold text-warning"
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
            </div>
        </div>

        <!-- Keep existing calendar and donut chart sections -->
        <!-- Section Divider -->
        <div class="section-divider mb-4">
            <div class="divider-line"></div>
            <div class="divider-text">
                <i class="fas fa-calendar-alt me-2"></i>
                Lịch thanh toán & Phân bổ lũy kế
            </div>
            <div class="divider-line"></div>
        </div>

        <div class="secondary-content">
            <div class="row g-4">
                <!-- Calendar Section (60%) -->
                <div class="col-lg-7 col-md-12 mb-4">
                    <!-- Keep existing calendar code -->
                    <div class="card calendar-card">
                        <div class="card-header">
                            <div
                                class="d-flex justify-content-between align-items-center flex-wrap"
                            >
                                <div>
                                    <h5 class="card-title mb-1">
                                        <i class="fas fa-calendar-alt me-2"></i>
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
                                            class="btn btn-outline-light"
                                            @click="goToToday"
                                            title="Hôm nay"
                                        >
                                            <i class="fas fa-calendar-day"></i>
                                        </button>
                                        <button
                                            type="button"
                                            class="btn btn-outline-light"
                                            @click="previousMonth"
                                            title="Tháng trước"
                                        >
                                            <i class="fas fa-chevron-left"></i>
                                        </button>
                                        <button
                                            type="button"
                                            class="btn btn-outline-light"
                                            @click="nextMonth"
                                            title="Tháng sau"
                                        >
                                            <i class="fas fa-chevron-right"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="calendar-legend d-none d-md-flex">
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
                                                    selectedDate === day.date,
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
                                                    v-if="day.events.length > 3"
                                                    class="event-more"
                                                    :title="`+${
                                                        day.events.length - 3
                                                    } thanh toán khác`"
                                                >
                                                    +{{ day.events.length - 3 }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Donut Chart Section (40%) - Enhanced with Chart.js -->
                <div class="col-lg-5 col-md-12">
                    <div class="donut-section">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-header bg-white border-0">
                                <div
                                    class="d-flex justify-content-between align-items-center"
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

                                    <!-- Donut Legend -->
                                    <div class="donut-legend">
                                        <div
                                            v-for="(station, index) in stations"
                                            :key="station.name"
                                            class="donut-legend-item"
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
                                            <div
                                                class="legend-color-indicator"
                                                :style="{
                                                    backgroundColor:
                                                        getStationColor(index),
                                                }"
                                            ></div>
                                            <div class="legend-info">
                                                <div
                                                    class="legend-station-name"
                                                >
                                                    {{ station.name }}
                                                </div>
                                                <div class="legend-values">
                                                    <span
                                                        class="legend-amount"
                                                        >{{
                                                            formatCurrency(
                                                                station.weeklyAmount
                                                            )
                                                        }}</span
                                                    >
                                                    <span
                                                        class="legend-percentage"
                                                        >({{
                                                            getWeeklyPercentage(
                                                                station.weeklyAmount
                                                            )
                                                        }}%)</span
                                                    >
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

            // API Data
            stations: [],

            // Chart instances
            chartInstance: null,
            donutChartInstance: null,

            // Chart initialization flags
            chartsReady: false,

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

            jobCategories: [], // เปลี่ยนจาก static data เป็น dynamic
            tableLoading: false,
            tableError: null,
        };
    },
    computed: {
        // คำนวณ total สำหรับ table (เพิ่มใหม่)
        totalJobQuantity() {
            return this.jobCategories.reduce(
                (sum, job) => sum + job.quantity,
                0
            );
        },
        totalWeeklyAmountFromTable() {
            return this.jobCategories.reduce(
                (sum, job) => sum + job.weeklyAmount,
                0
            );
        },
        totalCumulativeAmountFromTable() {
            return this.jobCategories.reduce(
                (sum, job) => sum + job.cumulativeAmount,
                0
            );
        },
        totalStations() {
            return this.stations.length;
        },
        totalQuantity() {
            return this.stations.reduce(
                (sum, station) => sum + station.quantity,
                0
            );
        },
        totalWeeklyAmount() {
            return this.stations.reduce(
                (sum, station) => sum + station.weeklyAmount,
                0
            );
        },
        totalCumulativeAmount() {
            return this.stations.reduce(
                (sum, station) => sum + station.cumulativeAmount,
                0
            );
        },
        totalCumulativeFromStations() {
            return this.stations.reduce(
                (sum, station) => sum + station.cumulativeAmount,
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
        // เพิ่ม method fetchTableData
        async fetchTableData() {
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

                const response = await axios.get(
                    "/api/dashboard/table-section",
                    {
                        params: { period: this.selectedPeriod },
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

        // อัปเดต method refreshData
        async refreshData() {
            await Promise.all([this.fetchChartData(), this.fetchTableData()]);
        },
        // Chart lifecycle management
        destroyCharts() {
            try {
                if (this.chartInstance) {
                    console.log("Destroying bar chart...");
                    this.chartInstance.destroy();
                    this.chartInstance = null;
                }
            } catch (error) {
                console.warn("Error destroying bar chart:", error);
                this.chartInstance = null;
            }

            try {
                if (this.donutChartInstance) {
                    console.log("Destroying donut chart...");
                    this.donutChartInstance.destroy();
                    this.donutChartInstance = null;
                }
            } catch (error) {
                console.warn("Error destroying donut chart:", error);
                this.donutChartInstance = null;
            }

            this.chartsReady = false;
        },

        // API Methods
        async fetchChartData() {
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

                    console.log("Stations data:", this.stations);

                    // this.updateJobCategories();

                    // Wait for DOM updates
                    await this.$nextTick();

                    // Wait a bit more to ensure DOM is ready
                    setTimeout(() => {
                        this.initializeCharts();
                    }, 150);
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

        // Chart.js Methods
        initializeCharts() {
            console.log("Initializing charts with stations:", this.stations);

            // Check if charts are already ready
            if (this.chartsReady) {
                console.log("Charts already initialized, skipping...");
                return;
            }

            // Check if DOM elements are available
            const chartCanvas = this.$refs.chartCanvas;
            const donutCanvas = this.$refs.donutCanvas;

            if (!chartCanvas || !donutCanvas) {
                console.warn("Canvas elements not ready, retrying in 200ms...");
                setTimeout(() => {
                    this.initializeCharts();
                }, 200);
                return;
            }

            // Verify canvas elements are in DOM
            if (
                !document.contains(chartCanvas) ||
                !document.contains(donutCanvas)
            ) {
                console.warn("Canvas elements not in DOM, retrying...");
                setTimeout(() => {
                    this.initializeCharts();
                }, 200);
                return;
            }

            this.chartsReady = true;
            this.createBarChart();
            this.createDonutChart();
        },

        createBarChart() {
            // Double check canvas availability
            const chartCanvas = this.$refs.chartCanvas;
            if (!chartCanvas || !document.contains(chartCanvas)) {
                console.warn("Chart canvas not available for bar chart");
                return;
            }

            // Destroy existing chart safely
            if (this.chartInstance) {
                try {
                    this.chartInstance.destroy();
                } catch (error) {
                    console.warn("Error destroying existing bar chart:", error);
                }
                this.chartInstance = null;
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
                                label: `Tổng tiền trong ${this.getPeriodText()} (triệu ₫)`,
                                data: weeklyAmountData,
                                backgroundColor: "rgba(16, 185, 129, 0.8)",
                                borderColor: "rgba(16, 185, 129, 1)",
                                borderWidth: 2,
                                yAxisID: "y",
                            },
                            {
                                label: "Lũy kế tiền (triệu ₫)",
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
                                                ) + " ₫";
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
                                    text: "Số tiền (triệu ₫)",
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

        createDonutChart() {
            // Double check canvas availability
            const donutCanvas = this.$refs.donutCanvas;
            if (!donutCanvas || !document.contains(donutCanvas)) {
                console.warn("Donut canvas not available for donut chart");
                return;
            }

            // Destroy existing chart safely
            if (this.donutChartInstance) {
                try {
                    this.donutChartInstance.destroy();
                } catch (error) {
                    console.warn(
                        "Error destroying existing donut chart:",
                        error
                    );
                }
                this.donutChartInstance = null;
            }

            if (this.stations.length === 0) {
                console.log("No stations data available for donut chart");
                return;
            }

            // ใช้ weeklyAmount แทน cumulativeAmount
            const data = this.stations.map((station) => station.weeklyAmount);
            const labels = this.stations.map((station) => station.name);
            const colors = this.stations.map((_, index) =>
                this.getStationColor(index)
            );

            // Check if all data is zero
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
                                        ).format(value)} ₫ (${percentage}%)`;
                                    },
                                },
                            },
                        },
                        cutout: "60%",
                        onHover: (event, activeElements) => {
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
            if (!amount) return "0 ₫";
            return new Intl.NumberFormat("vi-VN").format(amount) + " ₫";
        },

        refreshData() {
            this.fetchChartData();
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
        selectDonutSegment(station, index) {
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
    },
    async mounted() {
        console.log("Dashboard mounted");
        this.selectedDate = new Date().toISOString().split("T")[0];

        // Wait for DOM to be ready
        await this.$nextTick();

        // Fetch data which will initialize charts and table
        await Promise.all([
            this.fetchChartData(),
            this.fetchTableData(), // เพิ่มการเรียก table data
        ]);
    },
    watch: {
        selectedPeriod() {
            console.log("Period changed to:", this.selectedPeriod);
            // อัปเดตทั้ง chart และ table เมื่อเปลี่ยน period
            this.fetchChartData();
            this.fetchTableData();
        },
    },
    beforeUnmount() {
        console.log("Dashboard before unmount - destroying charts");
        this.destroyCharts();
    },
    watch: {
        selectedPeriod: {
            handler(newPeriod, oldPeriod) {
                console.log(`Period changed from ${oldPeriod} to ${newPeriod}`);

                // เรียกทั้งสอง methods
                this.fetchChartData();
                this.fetchTableData();
            },
            immediate: false,
        },
    },
};
</script>

<!-- Keep the same template and styles -->

<style scoped>
/* Chart specific styles */
.chart-wrapper {
    position: relative;
    height: 300px;
    width: 100%;
}

.donut-chart-wrapper {
    position: relative;
    height: 250px;
    width: 100%;
    margin: 0 auto 1rem auto;
}

/* Keep all existing styles */
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

.no-data-message {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 200px;
    text-align: center;
}

/* Dashboard Container */
.dashboard-container {
    padding: 1.5rem;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: 100vh;
}

.dashboard-header {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    margin-bottom: 1.5rem;
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
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

.card {
    border-radius: 16px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1) !important;
}

.card-header {
    border-radius: 16px 16px 0 0 !important;
    padding: 1.5rem;
}

.card-title {
    color: #2c3e50;
    font-weight: 600;
}

.time-selector .btn {
    border-radius: 8px;
    padding: 0.5rem 1rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.time-selector .btn-check:checked + .btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-color: transparent;
    color: white;
}

/* Chart Section */
.chart-section {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.chart-section .card {
    min-height: 430px;
    display: flex;
    flex-direction: column;
}

.chart-section .card-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    padding: 1rem;
}

.chart-container {
    min-height: 320px;
    flex: 1;
    position: relative;
}

/* Table styles */
.table-section {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.table-section .card {
    min-height: 430px;
    display: flex;
    flex-direction: column;
}

.table-section .card-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    padding: 0;
}

.table-container {
    flex: 1;
}

.table-scroll-wrapper {
    height: 100%;
    overflow-y: auto;
    overflow-x: auto;
    border-radius: 0 0 16px 16px;
}

.table {
    font-size: 0.875rem;
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
    transform: scale(1.01);
}

.job-info {
    padding: 0.25rem 0;
}

.job-name {
    color: #2c3e50;
    font-size: 0.875rem;
    margin-bottom: 0.125rem;
}

.job-desc {
    font-size: 0.75rem;
    line-height: 1.2;
}

.unit-badge {
    background: #e9ecef;
    color: #495057;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 500;
}

.amount-value {
    font-size: 0.875rem;
    white-space: nowrap;
}

.table-actions .btn {
    border-radius: 8px;
    width: 36px;
    height: 36px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Calendar styles */
.calendar-card {
    height: 500px;
    display: flex;
    flex-direction: column;
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    overflow: hidden;
}

.calendar-card .card-header {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
    border: none;
    padding: 1.5rem;
    height: 120px;
    flex-shrink: 0;
}

.calendar-card .card-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    padding: 0;
}

.calendar-grid {
    flex: 1;
    display: flex;
    flex-direction: column;
    height: 380px;
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
    height: 330px;
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

.calendar-nav .btn {
    border-color: rgba(255, 255, 255, 0.3);
    color: white;
    margin: 0 2px;
}

.calendar-nav .btn:hover {
    background-color: rgba(255, 255, 255, 0.2);
    border-color: rgba(255, 255, 255, 0.5);
    color: white;
}

.calendar-legend {
    gap: 1rem;
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

/* Donut Chart styles */
.donut-section {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.donut-section .card {
    min-height: 500px;
    display: flex;
    flex-direction: column;
}

.donut-section .card-header {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
    height: 90px;
    flex-shrink: 0;
    padding: 1.5rem;
}

.donut-section .card-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    padding: 1rem;
}

.donut-container {
    height: 100%;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.donut-total {
    text-align: right;
}

.total-label {
    display: block;
    font-size: 0.8rem;
    opacity: 0.9;
}

.total-value {
    display: block;
    font-size: 1rem;
    font-weight: 700;
}

.donut-legend {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    overflow-y: auto;
    max-height: 200px;
    margin-top: 1rem;
}

.donut-legend-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    border: 2px solid transparent;
}

.donut-legend-item:hover {
    background-color: #f8fafc;
    transform: translateX(4px);
}

.donut-legend-item.active {
    background-color: #f0f9ff;
    border-color: #3b82f6;
}

.legend-color-indicator {
    width: 16px;
    height: 16px;
    border-radius: 4px;
    flex-shrink: 0;
}

.legend-info {
    flex: 1;
}

.legend-station-name {
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.25rem;
}

.legend-values {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.legend-amount {
    font-size: 0.875rem;
    color: #059669;
    font-weight: 600;
}

.legend-percentage {
    font-size: 0.8rem;
    color: #6b7280;
    background: #f3f4f6;
    padding: 0.125rem 0.5rem;
    border-radius: 4px;
}

/* Section Divider */
.section-divider {
    display: flex;
    align-items: center;
    margin: 1rem 0 1rem 0;
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

/* Scrollbar */
.table-scroll-wrapper::-webkit-scrollbar,
.donut-legend::-webkit-scrollbar {
    width: 3px;
}

.table-scroll-wrapper::-webkit-scrollbar-track,
.donut-legend::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.table-scroll-wrapper::-webkit-scrollbar-thumb,
.donut-legend::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
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

.secondary-content {
    animation: fadeInUp 0.8s ease forwards;
}

/* Responsive Adjustments */
@media (max-width: 1199px) {
    .chart-wrapper {
        height: 280px;
    }

    .donut-chart-wrapper {
        height: 220px;
    }
}

@media (max-width: 991px) {
    .chart-wrapper {
        height: 260px;
    }

    .donut-chart-wrapper {
        height: 200px;
    }

    .calendar-card,
    .chart-section .card,
    .table-section .card,
    .donut-section .card {
        min-height: 400px;
        margin-bottom: 1rem;
    }
}

@media (max-width: 768px) {
    .chart-wrapper {
        height: 240px;
    }

    .donut-chart-wrapper {
        height: 180px;
    }

    .calendar-legend {
        display: none !important;
    }

    .section-divider {
        margin: 2rem 0 1.5rem 0;
    }

    .divider-text {
        font-size: 0.9rem;
        padding: 0 1rem;
    }
}

@media (max-width: 576px) {
    .chart-wrapper {
        height: 220px;
    }

    .donut-chart-wrapper {
        height: 160px;
    }

    .dashboard-container {
        padding: 1rem;
    }

    .donut-total {
        text-align: center;
        margin-top: 0.5rem;
    }
}

/* Table Loading, Error, No Data States */
.table-loading,
.table-error,
.table-no-data {
    min-height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.table-loading .fa-spinner {
    color: #667eea;
}

.table-error .fa-exclamation-triangle {
    color: #f59e0b;
}

.table-no-data .fa-inbox {
    color: #9ca3af;
}

/* Enhanced Table Styles */
.table-container {
    flex: 1;
    position: relative;
}

.table-scroll-wrapper {
    height: 100%;
    overflow-y: auto;
    overflow-x: auto;
    border-radius: 0 0 16px 16px;
    max-height: 350px; /* จำกัดความสูงเพื่อให้มี scrollbar */
}

/* Loading animation for refresh button */
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
</style>
