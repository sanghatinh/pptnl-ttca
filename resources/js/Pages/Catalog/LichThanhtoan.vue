<template>
    <div class="container-fluid payment-schedule-container">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="page-header">
                    <div
                        class="d-flex justify-content-between align-items-center flex-wrap"
                    >
                        <div>
                            <h2 class="page-title">
                                <i class="fas fa-calendar-alt me-2"></i>
                                Lịch thanh toán
                            </h2>
                            <p class="page-subtitle">
                                Quản lý và theo dõi lịch thanh toán dịch vụ và
                                hom giống
                            </p>
                        </div>
                        <div class="header-actions">
                            <button
                                class="btn btn-outline-light me-2"
                                @click="goToToday"
                            >
                                <i class="fas fa-calendar-day me-1"></i>
                                Hôm nay
                            </button>

                            <!-- Year Navigation -->
                            <div class="btn-group me-2" role="group">
                                <button
                                    type="button"
                                    class="btn btn-outline-light"
                                    @click="previousYear"
                                    title="Năm trước"
                                >
                                    <i class="fas fa-angle-double-left"></i>
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-outline-light"
                                    @click="nextYear"
                                    title="Năm sau"
                                >
                                    <i class="fas fa-angle-double-right"></i>
                                </button>
                            </div>

                            <!-- Month Navigation -->
                            <div class="btn-group" role="group">
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
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="row gx-4">
            <!-- Calendar Section (70%) -->
            <div class="col-lg-8 col-md-12 mb-4">
                <div class="card calendar-card">
                    <div class="card-header">
                        <div
                            class="d-flex justify-content-between align-items-center flex-wrap"
                        >
                            <h5 class="card-title mb-0">
                                {{ currentMonthYear }}
                            </h5>
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
                                            'other-month': !day.isCurrentMonth,
                                            today: day.isToday,
                                            'has-events': day.events.length > 0,
                                            selected: selectedDate === day.date,
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

            <!-- Tracking Section (30%) -->
            <div class="col-lg-4 col-md-12">
                <div class="tracking-section">
                    <!-- Service Payment Tracking (50%) -->
                    <div class="card tracking-card mb-3">
                        <div class="card-header bg-primary text-white">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-cogs me-2"></i>
                                Thanh toán tiền dịch vụ
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div
                                class="tracking-list"
                                style="max-height: 300px; overflow-y: auto"
                            >
                                <div
                                    v-for="payment in servicePayments"
                                    :key="payment.id"
                                    class="tracking-item"
                                    @click="viewPaymentDetails(payment)"
                                >
                                    <div class="tracking-info">
                                        <div class="tracking-title">
                                            {{ payment.title }}
                                        </div>
                                        <div class="tracking-meta">
                                            <span class="tracking-date">
                                                <i
                                                    class="fas fa-calendar me-1"
                                                ></i>
                                                {{ formatDate(payment.date) }}
                                            </span>
                                            <span class="tracking-amount">
                                                {{
                                                    formatCurrency(
                                                        payment.amount
                                                    )
                                                }}
                                            </span>
                                        </div>
                                        <div class="tracking-status">
                                            <span
                                                class="badge"
                                                :class="
                                                    getStatusClass(
                                                        payment.status
                                                    )
                                                "
                                            >
                                                {{ payment.status }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="tracking-action">
                                        <i class="fas fa-chevron-right"></i>
                                    </div>
                                </div>
                                <div
                                    v-if="servicePayments.length === 0"
                                    class="empty-state"
                                >
                                    <i
                                        class="fas fa-calendar-times text-muted"
                                    ></i>
                                    <p class="text-muted mb-0">
                                        Không có thanh toán dịch vụ
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Seed Payment Tracking (50%) -->
                    <div class="card tracking-card">
                        <div class="card-header bg-success text-white">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-seedling me-2"></i>
                                Thanh toán tiền hom giống
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div
                                class="tracking-list"
                                style="max-height: 300px; overflow-y: auto"
                            >
                                <div
                                    v-for="payment in seedPayments"
                                    :key="payment.id"
                                    class="tracking-item"
                                    @click="viewPaymentDetails(payment)"
                                >
                                    <div class="tracking-info">
                                        <div class="tracking-title">
                                            {{ payment.title }}
                                        </div>
                                        <div class="tracking-meta">
                                            <span class="tracking-date">
                                                <i
                                                    class="fas fa-calendar me-1"
                                                ></i>
                                                {{ formatDate(payment.date) }}
                                            </span>
                                            <span class="tracking-amount">
                                                {{
                                                    formatCurrency(
                                                        payment.amount
                                                    )
                                                }}
                                            </span>
                                        </div>
                                        <div class="tracking-status">
                                            <span
                                                class="badge"
                                                :class="
                                                    getStatusClass(
                                                        payment.status
                                                    )
                                                "
                                            >
                                                {{ payment.status }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="tracking-action">
                                        <i class="fas fa-chevron-right"></i>
                                    </div>
                                </div>
                                <div
                                    v-if="seedPayments.length === 0"
                                    class="empty-state"
                                >
                                    <i
                                        class="fas fa-calendar-times text-muted"
                                    ></i>
                                    <p class="text-muted mb-0">
                                        Không có thanh toán hom giống
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Details Modal -->
        <div
            class="modal fade"
            id="paymentDetailsModal"
            tabindex="-1"
            aria-labelledby="paymentDetailsModalLabel"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="paymentDetailsModalLabel">
                            Chi tiết thanh toán
                        </h5>
                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"
                        ></button>
                    </div>
                    <div class="modal-body" v-if="selectedPayment">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="detail-group">
                                    <label class="detail-label">Tiêu đề</label>
                                    <p class="detail-value">
                                        {{ selectedPayment.title }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-group">
                                    <label class="detail-label"
                                        >Loại thanh toán</label
                                    >
                                    <p class="detail-value">
                                        {{
                                            selectedPayment.type === "service"
                                                ? "Dịch vụ"
                                                : "Hom giống"
                                        }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-group">
                                    <label class="detail-label"
                                        >Ngày thanh toán</label
                                    >
                                    <p class="detail-value">
                                        {{ formatDate(selectedPayment.date) }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-group">
                                    <label class="detail-label">Số tiền</label>
                                    <p class="detail-value">
                                        {{
                                            formatCurrency(
                                                selectedPayment.amount
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="detail-group">
                                    <label class="detail-label"
                                        >Trạng thái</label
                                    >
                                    <p class="detail-value">
                                        <span
                                            class="badge"
                                            :class="
                                                getStatusClass(
                                                    selectedPayment.status
                                                )
                                            "
                                        >
                                            {{ selectedPayment.status }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div
                                class="col-md-12"
                                v-if="selectedPayment.description"
                            >
                                <div class="detail-group">
                                    <label class="detail-label">Mô tả</label>
                                    <p class="detail-value">
                                        {{ selectedPayment.description }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal"
                        >
                            Đóng
                        </button>
                        <button
                            type="button"
                            class="btn btn-success"
                            v-if="selectedPayment"
                            @click="navigateToDetailFromModal(selectedPayment)"
                        >
                            Xem chi tiết đầy đủ
                        </button>
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
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title" id="dayDetailsModalLabel">
                        <i class="fas fa-calendar-day me-2"></i>
                        Chi tiết thanh toán - {{ formatSelectedDate }}
                    </h5>
                    <button
                        type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>
                </div>
                <div
                    class="modal-body p-0"
                    v-if="selectedDateEvents.length > 0"
                >
                    <!-- Summary Cards -->
                    <div class="summary-section p-4 bg-light">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="summary-card total-card">
                                    <div class="summary-icon">
                                        <i class="fas fa-coins text-white"></i>
                                    </div>
                                    <div class="summary-content">
                                        <div class="summary-label">
                                            Tổng số khoản
                                        </div>
                                        <div class="summary-value">
                                            {{ selectedDateEvents.length }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="summary-card total-amount-card">
                                    <div class="summary-icon">
                                        <i
                                            class="fas fa-money-bill-wave text-success"
                                        ></i>
                                    </div>
                                    <div class="summary-content">
                                        <div class="summary-label">
                                            Tổng số tiền
                                        </div>
                                        <div class="summary-value">
                                            {{ formatCurrency(totalDayAmount) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="summary-card service-card">
                                    <div class="summary-icon">
                                        <i class="fas fa-cogs text-info"></i>
                                    </div>
                                    <div class="summary-content">
                                        <div class="summary-label">Dịch vụ</div>
                                        <div class="summary-value">
                                            {{
                                                formatCurrency(
                                                    totalServiceAmount
                                                )
                                            }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="summary-card seed-card">
                                    <div class="summary-icon">
                                        <i
                                            class="fas fa-seedling text-warning"
                                        ></i>
                                    </div>
                                    <div class="summary-content">
                                        <div class="summary-label">
                                            Hom giống
                                        </div>
                                        <div class="summary-value">
                                            {{
                                                formatCurrency(totalSeedAmount)
                                            }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payments List -->
                    <div class="payments-section">
                        <!-- Service Payments Section -->
                        <div
                            v-if="dayServicePayments.length > 0"
                            class="payment-category"
                        >
                            <div class="category-header">
                                <h6 class="category-title">
                                    <i
                                        class="fas fa-cogs me-2 text-primary"
                                    ></i>
                                    Thanh toán dịch vụ ({{
                                        dayServicePayments.length
                                    }})
                                </h6>
                            </div>
                            <div class="payment-list">
                                <div
                                    v-for="payment in dayServicePayments"
                                    :key="'service-' + payment.id"
                                    class="payment-item service-item"
                                    @click="navigateToServiceDetail(payment)"
                                >
                                    <div class="payment-info">
                                        <div class="payment-title">
                                            {{ payment.title }}
                                        </div>
                                        <div class="payment-meta">
                                            <span class="payment-id"
                                                >Tiêu đề:
                                                {{
                                                    payment.ma_trinh_thanh_toan ||
                                                    payment.payment_request_id ||
                                                    "N/A"
                                                }}</span
                                            >
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
                                        >
                                            {{ payment.status }}
                                        </span>
                                    </div>
                                    <div class="payment-action">
                                        <i class="fas fa-external-link-alt"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Seed Payments Section -->
                        <div
                            v-if="daySeedPayments.length > 0"
                            class="payment-category"
                        >
                            <div class="category-header">
                                <h6 class="category-title">
                                    <i
                                        class="fas fa-seedling me-2 text-success"
                                    ></i>
                                    Thanh toán hom giống ({{
                                        daySeedPayments.length
                                    }})
                                </h6>
                            </div>
                            <div class="payment-list">
                                <div
                                    v-for="payment in daySeedPayments"
                                    :key="'seed-' + payment.id"
                                    class="payment-item seed-item"
                                    @click="navigateToSeedDetail(payment)"
                                >
                                    <div class="payment-info">
                                        <div class="payment-title">
                                            {{ payment.title }}
                                        </div>
                                        <div class="payment-meta">
                                            <span class="payment-id"
                                                >Tiêu đề:
                                                {{
                                                    payment.ma_trinh_thanh_toan ||
                                                    payment.payment_request_id ||
                                                    "N/A"
                                                }}</span
                                            >
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
                                        >
                                            {{ payment.status }}
                                        </span>
                                    </div>
                                    <div class="payment-action">
                                        <i class="fas fa-external-link-alt"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-body p-4" v-else>
                    <div class="empty-day-state text-center">
                        <i
                            class="fas fa-calendar-times text-muted mb-3"
                            style="font-size: 3rem"
                        ></i>
                        <h5 class="text-muted">Không có thanh toán nào</h5>
                        <p class="text-muted">
                            Chưa có lịch thanh toán nào trong ngày
                            {{ formatSelectedDate }}
                        </p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        <i class="fas fa-times me-1"></i>
                        Đóng
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { Modal } from "bootstrap";
import axios from "axios";
import { useStore } from "../../Store/Auth";

export default {
    name: "LichThanhToan",
    setup() {
        const store = useStore();
        return {
            store,
        };
    },
    data() {
        return {
            currentDate: new Date(),
            selectedDate: null,
            weekDays: ["CN", "T2", "T3", "T4", "T5", "T6", "T7"],
            selectedPayment: null,
            paymentDetailsModal: null,
            dayDetailsModal: null, // เพิ่มสำหรับ Day Details Modal
            isLoading: false,
            // ข้อมูลจากAPI
            servicePayments: [],
            seedPayments: [],
            calendarEvents: [],
        };
    },
    computed: {
        currentMonthYear() {
            return this.currentDate.toLocaleDateString("vi-VN", {
                month: "long",
                year: "numeric",
            });
        },

        // เพิ่ม computed properties สำหรับ Day Details Modal
        formatSelectedDate() {
            if (!this.selectedDate) return "";
            try {
                return new Date(this.selectedDate).toLocaleDateString("vi-VN", {
                    weekday: "long",
                    year: "numeric",
                    month: "long",
                    day: "numeric",
                });
            } catch (error) {
                console.error("Error formatting selected date:", error);
                return this.selectedDate;
            }
        },

        selectedDateEvents() {
            if (!this.selectedDate) return [];
            return this.calendarEvents.filter(
                (event) => event.date === this.selectedDate
            );
        },

        dayServicePayments() {
            return this.selectedDateEvents.filter(
                (event) => event.type === "service"
            );
        },

        daySeedPayments() {
            return this.selectedDateEvents.filter(
                (event) => event.type === "seed"
            );
        },

        totalDayAmount() {
            return this.selectedDateEvents.reduce(
                (total, event) => total + (event.amount || 0),
                0
            );
        },

        totalServiceAmount() {
            return this.dayServicePayments.reduce(
                (total, event) => total + (event.amount || 0),
                0
            );
        },

        totalSeedAmount() {
            return this.daySeedPayments.reduce(
                (total, event) => total + (event.amount || 0),
                0
            );
        },

        calendarWeeks() {
            const year = this.currentDate.getFullYear();
            const month = this.currentDate.getMonth();
            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const startDate = new Date(firstDay);
            startDate.setDate(startDate.getDate() - firstDay.getDay());

            const weeks = [];
            let currentDate = new Date(startDate);

            // Generate 6 weeks to ensure full calendar grid
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

        allPayments() {
            return [...this.servicePayments, ...this.seedPayments];
        },
    },
    methods: {
        // Get authentication token using store
        getAuthToken() {
            // ใช้ store เพื่อดึง token
            return this.store.getToken;
        },

        // Create axios config with authentication
        getAxiosConfig(params = {}) {
            const config = {
                params,
                headers: this.store.getAuthHeaders(),
            };

            return config;
        },

        async loadCalendarData() {
            this.isLoading = true;
            try {
                const month = this.currentDate.getMonth() + 1;
                const year = this.currentDate.getFullYear();

                console.log(`Loading data for ${month}/${year}`);

                // Load all data simultaneously
                const [calendarResponse, serviceResponse, seedResponse] =
                    await Promise.all([
                        this.loadCalendarEvents(month, year),
                        this.loadServiceTracking(month, year),
                        this.loadSeedTracking(month, year),
                    ]);

                console.log("Data loaded successfully");
            } catch (error) {
                console.error("Error loading calendar data:", error);
                this.showError("Lỗi khi tải dữ liệu lịch thanh toán");

                // Handle authentication error
                if (error.response?.status === 401) {
                    this.handleAuthError();
                }
            } finally {
                this.isLoading = false;
            }
        },

        async loadCalendarEvents(month, year) {
            try {
                const config = this.getAxiosConfig({ month, year });
                const response = await axios.get(
                    "/api/payment-schedule/calendar-events",
                    config
                );

                console.log("Calendar events response:", response.data);

                if (response.data.success) {
                    this.calendarEvents = response.data.data || [];
                } else {
                    console.warn(
                        "Calendar events request not successful:",
                        response.data
                    );
                    this.calendarEvents = [];
                }
                return response;
            } catch (error) {
                console.error("Error loading calendar events:", error);
                // Set empty array on error to prevent UI issues
                this.calendarEvents = [];
                throw error;
            }
        },

        async loadServiceTracking(month, year) {
            try {
                const config = this.getAxiosConfig({ month, year });
                const response = await axios.get(
                    "/api/payment-schedule/service-tracking",
                    config
                );

                console.log("Service tracking response:", response.data);

                if (response.data.success) {
                    this.servicePayments = response.data.data || [];
                } else {
                    console.warn(
                        "Service tracking request not successful:",
                        response.data
                    );
                    this.servicePayments = [];
                }
                return response;
            } catch (error) {
                console.error("Error loading service tracking:", error);
                this.servicePayments = [];
                throw error;
            }
        },

        async loadSeedTracking(month, year) {
            try {
                const config = this.getAxiosConfig({ month, year });
                const response = await axios.get(
                    "/api/payment-schedule/seed-tracking",
                    config
                );

                console.log("Seed tracking response:", response.data);

                if (response.data.success) {
                    this.seedPayments = response.data.data || [];
                } else {
                    console.warn(
                        "Seed tracking request not successful:",
                        response.data
                    );
                    this.seedPayments = [];
                }
                return response;
            } catch (error) {
                console.error("Error loading seed tracking:", error);
                this.seedPayments = [];
                throw error;
            }
        },

        showError(message) {
            // Enhanced error display
            console.error(message);

            // If you have a notification system, use it here
            // Example: this.$toast.error(message);
            // For now, just alert
            if (typeof window !== "undefined") {
                alert(message);
            }
        },

        handleAuthError() {
            // Clear localStorage
            localStorage.removeItem("web_token");
            localStorage.removeItem("web_user");

            // Clear store
            this.store.logout();

            // Redirect to login
            this.$router.push("/login");
        },

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

                // ถ้ามี events ในวันนั้น ให้เปิด Day Details Modal
                if (day.events.length > 0) {
                    this.showDayDetailsModal();
                }
            }
        },

        // เพิ่ม methods สำหรับ Day Details Modal
        showDayDetailsModal() {
            if (this.dayDetailsModal) {
                this.dayDetailsModal.show();
            }
        },

        goToToday() {
            this.currentDate = new Date();
            this.selectedDate = new Date().toISOString().split("T")[0];
            this.loadCalendarData();
        },

        previousMonth() {
            this.currentDate = new Date(
                this.currentDate.getFullYear(),
                this.currentDate.getMonth() - 1,
                1
            );
            this.loadCalendarData();
        },

        nextMonth() {
            this.currentDate = new Date(
                this.currentDate.getFullYear(),
                this.currentDate.getMonth() + 1,
                1
            );
            this.loadCalendarData();
        },

        previousYear() {
            this.currentDate = new Date(
                this.currentDate.getFullYear() - 1,
                this.currentDate.getMonth(),
                1
            );
            this.loadCalendarData();
        },

        nextYear() {
            this.currentDate = new Date(
                this.currentDate.getFullYear() + 1,
                this.currentDate.getMonth(),
                1
            );
            this.loadCalendarData();
        },

        formatDate(dateStr) {
            try {
                return new Date(dateStr).toLocaleDateString("vi-VN");
            } catch (error) {
                console.error("Error formatting date:", dateStr, error);
                return dateStr;
            }
        },

        formatCurrency(amount) {
            try {
                return new Intl.NumberFormat("vi-VN", {
                    style: "currency",
                    currency: "KIP",
                }).format(amount || 0);
            } catch (error) {
                console.error("Error formatting currency:", amount, error);
                return amount ? amount.toString() : "0";
            }
        },

        getStatusClass(status) {
            switch (status) {
                case "Đã thanh toán":
                    return "bg-success";
                case "Đang xử lý":
                    return "bg-warning text-dark";
                case "Đã nộp kế toán":
                    return "bg-info text-dark";
                default:
                    return "bg-secondary";
            }
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

        viewPaymentDetails(payment) {
            this.selectedPayment = payment;
            if (this.paymentDetailsModal) {
                this.paymentDetailsModal.show();
            }
        },

        // เพิ่ม Navigation methods
        navigateToServiceDetail(payment) {
            try {
                // ปิด modal ก่อนนำทาง
                if (this.dayDetailsModal) {
                    this.dayDetailsModal.hide();
                }

                // บันทึกสถานะปัจจุบันก่อนนำทาง
                this.saveCurrentState();

                // นำทางไปยัง Details_Phieutrinhthanhtoan (แก้ไขชื่อ route)
                if (payment.ma_trinh_thanh_toan) {
                    this.$router.push(
                        `/Details_Phieutrinhthanhtoan/${payment.ma_trinh_thanh_toan}`
                    );
                } else if (payment.payment_request_id) {
                    // ถ้าไม่มี ma_trinh_thanh_toan ให้ใช้ payment_request_id
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

        navigateToSeedDetail(payment) {
            try {
                // ปิด modal ก่อนนำทาง
                if (this.dayDetailsModal) {
                    this.dayDetailsModal.hide();
                }

                // บันทึกสถานะปัจจุบันก่อนนำทาง
                this.saveCurrentState();

                // นำทางไปยัง Details_Phieutrinhthanhtoanhomgiong (แก้ไขชื่อ route)
                if (payment.ma_trinh_thanh_toan) {
                    this.$router.push(
                        `/Details_Phieutrinhthanhtoanhomgiong/${payment.ma_trinh_thanh_toan}`
                    );
                } else if (payment.payment_request_id) {
                    // ถ้าไม่มี ma_trinh_thanh_toan ให้ใช้ payment_request_id
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
        // Navigation method สำหรับ modal "Xem chi tiết đầy đủ" button
        navigateToDetailFromModal(payment) {
            try {
                // ปิด modal ก่อน
                if (this.paymentDetailsModal) {
                    this.paymentDetailsModal.hide();
                }

                this.saveCurrentState();

                // ตรวจสอบประเภทการชำระเงิน
                if (payment.type === "service") {
                    if (payment.ma_trinh_thanh_toan) {
                        this.$router.push(
                            `/Details_Phieutrinhthanhtoan/${payment.ma_trinh_thanh_toan}`
                        );
                    } else if (payment.payment_request_id) {
                        this.$router.push(
                            `/Details_Phieudenghithanhtoandichvu/${payment.payment_request_id}`
                        );
                    } else {
                        this.showError("Không tìm thấy thông tin thanh toán");
                    }
                } else if (payment.type === "seed") {
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
                } else {
                    this.showError("Không thể xác định loại thanh toán");
                }
            } catch (error) {
                console.error("Error navigating from modal:", error);
                this.showError("Đã xảy ra lỗi khi điều hướng");
            }
        },

        // เพิ่ม method สำหรับบันทึกสถานะปัจจุบัน
        saveCurrentState() {
            const state = {
                currentDate: this.currentDate.toISOString(),
                selectedDate: this.selectedDate,
                timestamp: new Date().getTime(),
            };
            localStorage.setItem("calendar_state", JSON.stringify(state));
        },

        // เพิ่ม method สำหรับโหลดสถานะที่บันทึกไว้
        loadSavedState() {
            try {
                const savedState = localStorage.getItem("calendar_state");
                if (savedState) {
                    const state = JSON.parse(savedState);
                    // ตรวจสอบว่าข้อมูลไม่เก่าเกิน 1 ชั่วโมง
                    const now = new Date().getTime();
                    if (now - state.timestamp < 3600000) {
                        // 1 hour
                        this.currentDate = new Date(state.currentDate);
                        this.selectedDate = state.selectedDate;
                        return true;
                    }
                }
            } catch (error) {
                console.error("Error loading saved state:", error);
            }
            return false;
        },

        // ...existing methods...
    },
    async mounted() {
        try {
            // Initialize Bootstrap modals
            this.paymentDetailsModal = new Modal(
                document.getElementById("paymentDetailsModal")
            );

            // เพิ่ม Day Details Modal
            this.dayDetailsModal = new Modal(
                document.getElementById("dayDetailsModal")
            );

            // Load saved state if available
            const stateLoaded = this.loadSavedState();

            // Set today as initially selected if no saved state
            if (!stateLoaded) {
                this.selectedDate = new Date().toISOString().split("T")[0];
            }

            // Check authentication using store
            const token = this.getAuthToken();
            if (!token) {
                console.warn("No authentication token found");
                this.showError("Vui lòng đăng nhập để sử dụng tính năng này");
                return;
            }

            // Load initial data
            await this.loadCalendarData();
        } catch (error) {
            console.error("Error in mounted hook:", error);
            this.showError("Lỗi khởi tạo trang lịch thanh toán");
        }
    },
};
</script>

<style scoped>
.payment-schedule-container {
    padding: 1.5rem;
    background-color: #f8f9fa;
    min-height: 100vh;
}

/* Header Styles */
.page-header {
    background: linear-gradient(90deg, #10b981 0%, #059669 50%, #10b981 100%);
    color: white;
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.page-title {
    font-size: 2rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.page-subtitle {
    opacity: 0.9;
    margin-bottom: 0;
}

.header-actions .btn {
    border-radius: 8px;
    font-weight: 500;
    border-color: rgba(255, 255, 255, 0.3);
    color: white;
}

.header-actions .btn:hover {
    background-color: rgba(255, 255, 255, 0.2);
    border-color: rgba(255, 255, 255, 0.5);
    color: white;
}

/* Calendar Styles */
.calendar-card {
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
}

.calendar-legend {
    gap: 1rem;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
}

.legend-color {
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

/* New legend colors for statuses */
.processing-status {
    background-color: #f59e0b; /* Amber for processing */
}

.submitted-status {
    background-color: #3b82f6; /* Blue for submitted to accounting */
}

.completed-status {
    background-color: #10b981; /* Green for completed */
}

.calendar-grid {
    display: flex;
    flex-direction: column;
}

.calendar-header {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    background-color: #f8f9fa;
    border-bottom: 2px solid #e9ecef;
}

.calendar-day-header {
    padding: 1rem;
    text-align: center;
    font-weight: 600;
    color: #495057;
    font-size: 0.875rem;
}

.calendar-body {
    display: flex;
    flex-direction: column;
}

.calendar-week {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    border-bottom: 1px solid #e9ecef;
}

.calendar-day {
    min-height: 120px;
    padding: 0.75rem;
    border-right: 1px solid #e9ecef;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
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
    margin-bottom: 0.5rem;
    color: #333;
}

.day-events {
    display: flex;
    flex-wrap: wrap;
    gap: 2px;
}

.event-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    cursor: pointer;
}

.event-more {
    font-size: 0.7rem;
    color: #666;
    font-weight: 500;
}

/* Tracking Section Styles */
.tracking-section {
    height: fit-content;
}

.tracking-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    overflow: hidden;
}

.tracking-card .card-header {
    border: none;
    font-weight: 600;
}

.tracking-list {
    background-color: white;
}

.tracking-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem;
    border-bottom: 1px solid #e9ecef;
    cursor: pointer;
    transition: all 0.2s ease;
}

.tracking-item:hover {
    background-color: #f8f9fa;
    transform: translateX(5px);
}

.tracking-item:last-child {
    border-bottom: none;
}

.tracking-info {
    flex: 1;
}

.tracking-title {
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #333;
    font-size: 0.9rem;
}

.tracking-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.tracking-date {
    font-size: 0.8rem;
    color: #666;
}

.tracking-amount {
    font-weight: 600;
    color: #10b981;
    font-size: 0.85rem;
}

.tracking-status .badge {
    font-size: 0.7rem;
}

.tracking-action {
    color: #ccc;
    margin-left: 1rem;
}

.empty-state {
    text-align: center;
    padding: 2rem;
}

.empty-state i {
    font-size: 2rem;
    margin-bottom: 1rem;
}

/* Modal Styles */
.modal-content {
    border: none;
    border-radius: 15px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.modal-header {
    background: linear-gradient(90deg, #10b981 0%, #059669 50%, #10b981 100%);
    color: white;
    border: none;
    border-radius: 15px 15px 0 0;
}

.detail-group {
    margin-bottom: 1.5rem;
}

.detail-label {
    font-weight: 600;
    color: #666;
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
}

.detail-value {
    color: #333;
    margin-bottom: 0;
    font-size: 1rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .payment-schedule-container {
        padding: 1rem;
    }

    .page-header {
        padding: 1.5rem;
    }

    .page-title {
        font-size: 1.5rem;
    }

    .header-actions {
        margin-top: 1rem;
        width: 100%;
        justify-content: center;
    }

    .calendar-day {
        min-height: 80px;
        padding: 0.5rem;
    }

    .calendar-day-header {
        padding: 0.75rem;
        font-size: 0.8rem;
    }

    .tracking-item {
        padding: 0.75rem;
    }

    .tracking-title {
        font-size: 0.85rem;
    }

    .calendar-legend {
        display: none !important;
    }

    /* Stack calendar and tracking on mobile */
    .row.gx-4 > .col-lg-8,
    .row.gx-4 > .col-lg-4 {
        flex: 0 0 100%;
        max-width: 100%;
    }
}

@media (max-width: 576px) {
    .calendar-day {
        min-height: 60px;
        padding: 0.25rem;
    }

    .day-number {
        font-size: 0.875rem;
    }

    .tracking-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }

    .header-actions .btn {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
    }
}

/* Animation for smooth transitions */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.calendar-card,
.tracking-card {
    animation: fadeIn 0.6s ease-out;
}

/* Hover effects */
.btn {
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

/* Focus states for accessibility */
.calendar-day:focus,
.tracking-item:focus {
    outline: 2px solid #10b981;
    outline-offset: 2px;
}

/* Loading states */
.loading {
    opacity: 0.6;
    pointer-events: none;
}

.loading::after {
    content: "";
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #10b981;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
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
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    border: 1px solid #e9ecef;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    height: 100%;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.summary-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
}

.summary-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.summary-content {
    flex: 1;
}

.summary-label {
    font-size: 0.875rem;
    color: #6b7280;
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.summary-value {
    font-size: 1rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

/* Summary Card Colors */
.total-card .summary-icon {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
}

.total-amount-card .summary-icon {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
}

.service-card .summary-icon {
    background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
    color: white;
}

.seed-card .summary-icon {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
}

/* Payments Section */
.payments-section {
    background: white;
}

.payment-category {
    border-bottom: 1px solid #e5e7eb;
}

.payment-category:last-child {
    border-bottom: none;
}

.category-header {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e2e8f0;
}

.category-title {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    color: #334155;
    display: flex;
    align-items: center;
}

.payment-list {
    background: white;
}

.payment-item {
    display: flex;
    align-items: center;
    padding: 1.25rem 1.5rem;
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
    background: linear-gradient(180deg, #10b981 0%, #059669 100%);
}

.payment-info {
    flex: 1;
    min-width: 0;
}

.payment-title {
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.5rem;
    font-size: 0.95rem;
    line-height: 1.4;
}

.payment-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.payment-id {
    font-size: 0.8rem;
    color: #6b7280;
    font-family: "Monaco", "Menlo", monospace;
    background: #f3f4f6;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-weight: 500;
}

.payment-amount {
    font-weight: 700;
    color: #059669;
    font-size: 0.9rem;
}

.payment-status {
    margin: 0 1rem;
}

.payment-status .badge {
    font-size: 0.75rem;
    padding: 0.4rem 0.8rem;
    border-radius: 6px;
    font-weight: 600;
}

.payment-action {
    color: #9ca3af;
    font-size: 0.875rem;
    transition: all 0.2s ease;
}

.payment-item:hover .payment-action {
    color: #059669;
    transform: translateX(2px);
}

/* Empty Day State */
.empty-day-state {
    padding: 3rem 2rem;
}

.empty-day-state i {
    opacity: 0.5;
}

.empty-day-state h5 {
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.empty-day-state p {
    margin: 0;
    font-size: 0.95rem;
}

/* Modal Enhancements */
.modal-xl .modal-content {
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
}

.modal-header {
    padding: 1.5rem;
    border-bottom: none;
}

.modal-title {
    font-size: 1.25rem;
    font-weight: 600;
}

.modal-footer {
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
    padding: 1rem 1.5rem;
}

/* Responsive Design for Day Details Modal */
@media (max-width: 768px) {
    .modal-xl {
        margin: 0.5rem;
    }

    .modal-xl .modal-dialog {
        max-width: calc(100% - 1rem);
        margin: 0.5rem auto;
    }

    .summary-section {
        padding: 1rem !important;
    }

    .summary-card {
        padding: 1rem;
        flex-direction: column;
        text-align: center;
        gap: 0.75rem;
    }

    .summary-icon {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }

    .summary-value {
        font-size: 1.1rem;
    }

    .category-header {
        padding: 0.75rem 1rem;
    }

    .category-title {
        font-size: 0.9rem;
    }

    .payment-item {
        padding: 1rem;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }

    .payment-meta {
        width: 100%;
        justify-content: space-between;
    }

    .payment-status {
        margin: 0;
        align-self: flex-end;
    }

    .payment-action {
        position: absolute;
        top: 1rem;
        right: 1rem;
    }

    .modal-header {
        padding: 1rem;
    }

    .modal-title {
        font-size: 1.1rem;
    }

    .empty-day-state {
        padding: 2rem 1rem;
    }
}

@media (max-width: 576px) {
    .summary-card {
        padding: 0.75rem;
    }

    .summary-icon {
        width: 35px;
        height: 35px;
        font-size: 0.9rem;
    }

    .summary-value {
        font-size: 1rem;
    }

    .payment-title {
        font-size: 0.9rem;
    }

    .payment-id {
        font-size: 0.75rem;
    }

    .payment-amount {
        font-size: 0.85rem;
    }
}

/* Loading Animation for Modal */
.modal.fade .modal-dialog {
    transition: transform 0.3s ease-out;
}

.modal.show .modal-dialog {
    transform: none;
}

/* Smooth Animations */
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

.summary-card:nth-child(1) {
    animation-delay: 0.1s;
}
.summary-card:nth-child(2) {
    animation-delay: 0.2s;
}
.summary-card:nth-child(3) {
    animation-delay: 0.3s;
}
.summary-card:nth-child(4) {
    animation-delay: 0.4s;
}

/* Default status for event dots */
.default-status {
    background-color: #6b7280; /* Gray for unknown status */
}

/* Better scrollbar for payments section */
.payment-list {
    max-height: 400px;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: #cbd5e0 #f7fafc;
}

.payment-list::-webkit-scrollbar {
    width: 6px;
}

.payment-list::-webkit-scrollbar-track {
    background: #f7fafc;
    border-radius: 3px;
}

.payment-list::-webkit-scrollbar-thumb {
    background-color: #cbd5e0;
    border-radius: 3px;
}

.payment-list::-webkit-scrollbar-thumb:hover {
    background-color: #a0aec0;
}
</style>
