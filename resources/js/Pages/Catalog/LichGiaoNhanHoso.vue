<template>
    <div class="container-fluid payment-schedule-container">
        <!-- Loading Overlay -->
        <div v-if="isLoading" class="loading-overlay">
            <div class="loading-spinner">
                <div class="spinner-border text-success" role="status">
                    <span class="visually-hidden">Đang tải...</span>
                </div>
                <p class="loading-text">Đang tải dữ liệu lịch giao nhận...</p>
            </div>
        </div>

        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="page-header-calander">
                    <div
                        class="d-flex justify-content-between align-items-center flex-wrap"
                    >
                        <div>
                            <h2 class="page-title">
                                <i class="fas fa-calendar-alt me-2"></i>
                                Lịch giao nhận hồ sơ
                            </h2>
                            <p class="page-subtitle">
                                Quản lý và theo dõi lịch giao nhận hồ sơ ({{
                                    calendarEvents.length
                                }}
                                sự kiện)
                            </p>
                        </div>
                        <div class="header-actions">
                            <button
                                class="btn btn-outline-light me-2"
                                @click="goToToday"
                                :disabled="isLoading"
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
                                    :disabled="isLoading"
                                >
                                    <i class="fas fa-angle-double-left"></i>
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-outline-light"
                                    @click="nextYear"
                                    title="Năm sau"
                                    :disabled="isLoading"
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
                                    :disabled="isLoading"
                                >
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-outline-light"
                                    @click="nextMonth"
                                    title="Tháng sau"
                                    :disabled="isLoading"
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
                <div class="card calendar-card" :class="{ loading: isLoading }">
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
                                        class="legend-color submitting-status"
                                    ></span>
                                    Đang nộp
                                </span>
                                <span class="legend-item">
                                    <span
                                        class="legend-color received-status"
                                    ></span>
                                    Đã nhận
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
                                                } giao dịch khác`"
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
                    <!-- Document Submission/Receipt Tracking -->
                    <div
                        class="card tracking-card"
                        :class="{ loading: isLoading }"
                    >
                        <div class="card-header bg-primary text-white">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-file-alt me-2"></i>
                                Chi tiết giao nhận hồ sơ ({{
                                    documentTransactions.length
                                }})
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div
                                class="tracking-list"
                                style="max-height: 600px; overflow-y: auto"
                            >
                                <!-- Loading skeleton for tracking items -->
                                <div
                                    v-if="isLoading"
                                    class="skeleton-container"
                                >
                                    <div
                                        v-for="n in 5"
                                        :key="`skeleton-${n}`"
                                        class="skeleton-item"
                                    >
                                        <div
                                            class="skeleton-line skeleton-title"
                                        ></div>
                                        <div
                                            class="skeleton-line skeleton-meta"
                                        ></div>
                                        <div
                                            class="skeleton-line skeleton-status"
                                        ></div>
                                    </div>
                                </div>

                                <!-- Actual tracking items -->
                                <div
                                    v-else
                                    v-for="document in documentTransactions"
                                    :key="document.id"
                                    class="tracking-item"
                                    @click="viewDocumentDetails(document)"
                                >
                                    <div class="tracking-info">
                                        <div class="tracking-title">
                                            {{ document.title }}
                                        </div>
                                        <div class="tracking-meta">
                                            <span class="tracking-date">
                                                <i
                                                    class="fas fa-calendar me-1"
                                                ></i>
                                                {{ formatDate(document.date) }}
                                            </span>
                                        </div>
                                        <div class="tracking-user">
                                            <span class="user-send">
                                                <i class="fas fa-user me-1"></i>
                                                {{ document.userSend }}
                                            </span>
                                        </div>
                                        <div class="tracking-status">
                                            <span
                                                class="badge"
                                                :class="
                                                    getStatusClass(
                                                        document.status
                                                    )
                                                "
                                            >
                                                {{ document.status }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="tracking-action">
                                        <i class="fas fa-chevron-right"></i>
                                    </div>
                                </div>

                                <!-- Empty state -->
                                <div
                                    v-if="
                                        !isLoading &&
                                        documentTransactions.length === 0
                                    "
                                    class="empty-state"
                                >
                                    <i
                                        class="fas fa-calendar-times text-muted"
                                    ></i>
                                    <p class="text-muted mb-0">
                                        Không có giao dịch hồ sơ trong tháng này
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Document Details Modal -->
        <div
            class="modal fade"
            id="documentDetailsModal"
            tabindex="-1"
            aria-labelledby="documentDetailsModalLabel"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="documentDetailsModalLabel">
                            Chi tiết giao nhận hồ sơ
                        </h5>
                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"
                        ></button>
                    </div>
                    <div class="modal-body" v-if="selectedDocument">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="detail-group">
                                    <label class="detail-label">Tiêu đề</label>
                                    <p class="detail-value">
                                        {{ selectedDocument.title }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-group">
                                    <label class="detail-label"
                                        >Ngày giao dịch</label
                                    >
                                    <p class="detail-value">
                                        {{ formatDate(selectedDocument.date) }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-group">
                                    <label class="detail-label"
                                        >Người gửi</label
                                    >
                                    <p class="detail-value">
                                        {{ selectedDocument.userSend }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-group">
                                    <label class="detail-label"
                                        >Trạng thái</label
                                    >
                                    <p class="detail-value">
                                        <span
                                            class="badge"
                                            :class="
                                                getStatusClass(
                                                    selectedDocument.status
                                                )
                                            "
                                        >
                                            {{ selectedDocument.status }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div
                                class="col-md-12"
                                v-if="selectedDocument.description"
                            >
                                <div class="detail-group">
                                    <label class="detail-label">Mô tả</label>
                                    <p class="detail-value">
                                        {{ selectedDocument.description }}
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
                            v-if="selectedDocument"
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
                        Chi tiết giao nhận hồ sơ - {{ formatSelectedDate }}
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
                            <div class="col-md-6">
                                <div class="summary-card total-card">
                                    <div class="summary-icon">
                                        <i
                                            class="fas fa-file-alt text-white"
                                        ></i>
                                    </div>
                                    <div class="summary-content">
                                        <div class="summary-label">
                                            Tổng số hồ sơ
                                        </div>
                                        <div class="summary-value">
                                            {{ selectedDateEvents.length }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="summary-card status-card">
                                    <div class="summary-icon">
                                        <i
                                            class="fas fa-chart-pie text-white"
                                        ></i>
                                    </div>
                                    <div class="summary-content">
                                        <div class="summary-label">
                                            Trạng thái
                                        </div>
                                        <div class="summary-value">
                                            {{ getStatusSummary }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Documents List -->
                    <div class="documents-section">
                        <div class="document-category">
                            <div class="category-header">
                                <h6 class="category-title">
                                    <i class="fas fa-folder-open me-2"></i>
                                    Danh sách hồ sơ giao nhận ({{
                                        selectedDateEvents.length
                                    }})
                                </h6>
                            </div>
                            <div class="document-list">
                                <div
                                    v-for="document in selectedDateEvents"
                                    :key="document.id"
                                    class="document-item"
                                    @click="navigateToDocument(document)"
                                >
                                    <div class="document-info">
                                        <div class="document-title">
                                            {{ document.title }}
                                        </div>
                                        <div class="document-meta">
                                            <span class="document-id">
                                                <i
                                                    class="fas fa-hashtag me-1"
                                                ></i>
                                                ID: {{ document.document_id }}
                                            </span>
                                            <span class="document-user">
                                                <i class="fas fa-user me-1"></i>
                                                {{ document.user_name }}
                                            </span>
                                        </div>
                                        <div class="document-status">
                                            <span
                                                class="badge"
                                                :class="
                                                    getStatusClass(
                                                        document.status
                                                    )
                                                "
                                            >
                                                {{ document.status }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="document-action">
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
                        <h5 class="text-muted">Không có hồ sơ nào</h5>
                        <p class="text-muted">
                            Chưa có giao dịch hồ sơ nào trong ngày
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
import { useStore } from "../../Store/Auth";
import axios from "axios";
import Swal from "sweetalert2";

export default {
    name: "LichGiaoNhanHoso",
    setup() {
        const store = useStore();
        return { store };
    },
    data() {
        return {
            currentDate: new Date(),
            selectedDate: null,
            weekDays: ["CN", "T2", "T3", "T4", "T5", "T6", "T7"],
            selectedDocument: null,
            documentDetailsModal: null,
            dayDetailsModal: null, // เพิ่มสำหรับ Day Details Modal
            isLoading: false,
            calendarEvents: [],
            documentTransactions: [],
            bootstrap: null,
        };
    },
    computed: {
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
        allDocuments() {
            return [...this.calendarEvents];
        },
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
        getStatusSummary() {
            const submitting = this.selectedDateEvents.filter(
                (event) => event.status === "Đang nộp"
            ).length;
            const received = this.selectedDateEvents.filter(
                (event) => event.status === "Đã nhận"
            ).length;

            return `${received} đã nhận, ${submitting} đang nộp`;
        },
    },
    methods: {
        // Get authentication token using store
        getAuthToken() {
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

                // Load calendar events và document transactions cùng lúc
                const [calendarResponse, transactionsResponse] =
                    await Promise.all([
                        axios.get(
                            "/api/document-schedule/calendar-events",
                            this.getAxiosConfig({ month, year })
                        ),
                        axios.get(
                            "/api/document-schedule/transactions",
                            this.getAxiosConfig({ month, year })
                        ),
                    ]);

                if (calendarResponse.data.success) {
                    this.calendarEvents = calendarResponse.data.data || [];
                } else {
                    this.showError("Không thể tải dữ liệu lịch");
                }

                if (transactionsResponse.data.success) {
                    this.documentTransactions =
                        transactionsResponse.data.data || [];
                } else {
                    this.showError("Không thể tải dữ liệu giao dịch hồ sơ");
                }
            } catch (error) {
                console.error("Error loading calendar data:", error);

                if (error.response?.status === 401) {
                    this.handleAuthError();
                } else {
                    this.showError("Đã xảy ra lỗi khi tải dữ liệu");
                }
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

        showError(message) {
            Swal.fire({
                icon: "error",
                title: "Lỗi",
                text: message,
                confirmButtonText: "OK",
                buttonsStyling: false,
                customClass: {
                    confirmButton: "btn btn-success",
                },
            });
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

        // Modified showDayDetailsModal method
        async showDayDetailsModal() {
            await this.showModal("dayDetailsModal");
        },

        goToToday() {
            this.currentDate = new Date();
            this.selectedDate = new Date().toISOString().split("T")[0];
            this.loadCalendarData();
        },

        async previousMonth() {
            this.currentDate = new Date(
                this.currentDate.getFullYear(),
                this.currentDate.getMonth() - 1,
                1
            );
            await this.loadCalendarData();
        },

        async nextMonth() {
            this.currentDate = new Date(
                this.currentDate.getFullYear(),
                this.currentDate.getMonth() + 1,
                1
            );
            await this.loadCalendarData();
        },

        async previousYear() {
            this.currentDate = new Date(
                this.currentDate.getFullYear() - 1,
                this.currentDate.getMonth(),
                1
            );
            await this.loadCalendarData();
        },

        async nextYear() {
            this.currentDate = new Date(
                this.currentDate.getFullYear() + 1,
                this.currentDate.getMonth(),
                1
            );
            await this.loadCalendarData();
        },

        formatDate(dateStr) {
            return new Date(dateStr).toLocaleDateString("vi-VN");
        },

        getStatusClass(status) {
            switch (status) {
                case "Đã nhận":
                    return "bg-success";
                case "Đang nộp":
                    return "bg-warning text-dark";
                default:
                    return "bg-secondary";
            }
        },

        getEventStatusClass(status) {
            switch (status) {
                case "Đã nhận":
                    return "received-status";
                case "Đang nộp":
                    return "submitting-status";
                default:
                    return "default-status";
            }
        },

        // Modified viewDocumentDetails method
        async viewDocumentDetails(document) {
            this.selectedDocument = document;
            await this.showModal("documentDetailsModal");
        },
        // New method to handle showing modals
        async showModal(modalId) {
            try {
                if (!this.bootstrap) {
                    // Dynamically import Bootstrap only when needed
                    this.bootstrap = await import(
                        "bootstrap/dist/js/bootstrap.bundle.min.js"
                    );
                }

                const modalElement = document.getElementById(modalId);
                if (!modalElement) return;

                // Check if there's an existing instance
                let modalInstance =
                    this.bootstrap.Modal.getInstance(modalElement);

                // If no instance exists, create a new one
                if (!modalInstance) {
                    modalInstance = new this.bootstrap.Modal(modalElement, {
                        backdrop: "static",
                        keyboard: false,
                    });
                }

                // Show the modal
                modalInstance.show();
            } catch (error) {
                console.error(`Error showing ${modalId}:`, error);
                // Fallback if Bootstrap fails
                this.showModalFallback(modalId);
            }
        },
        // New method to hide modals
        async hideModal(modalId) {
            try {
                if (!this.bootstrap) {
                    this.bootstrap = await import(
                        "bootstrap/dist/js/bootstrap.bundle.min.js"
                    );
                }

                const modalElement = document.getElementById(modalId);
                if (!modalElement) return;

                const modalInstance =
                    this.bootstrap.Modal.getInstance(modalElement);
                if (modalInstance) {
                    modalInstance.hide();
                }
            } catch (error) {
                console.error(`Error hiding ${modalId}:`, error);
                this.hideModalFallback(modalId);
            }
        },

        // Fallback methods if Bootstrap fails
        showModalFallback(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add("show");
                modal.style.display = "block";
                document.body.classList.add("modal-open");

                // Create backdrop if it doesn't exist
                let backdrop = document.querySelector(".modal-backdrop");
                if (!backdrop) {
                    backdrop = document.createElement("div");
                    backdrop.classList.add("modal-backdrop", "fade", "show");
                    document.body.appendChild(backdrop);
                }
            }
        },

        hideModalFallback(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove("show");
                modal.style.display = "none";
                document.body.classList.remove("modal-open");

                // Remove backdrop
                const backdrop = document.querySelector(".modal-backdrop");
                if (backdrop) {
                    backdrop.parentNode.removeChild(backdrop);
                }
            }
        },

        // Modified navigateToDocument method
        async navigateToDocument(document) {
            try {
                // Close modal before navigation
                await this.hideModal("dayDetailsModal");

                // Navigate to document details
                if (document.document_id) {
                    this.$router.push(`/Danhsachhoso/${document.document_id}`);
                } else {
                    this.showError("Không tìm thấy ID เอกสาร");
                }
            } catch (error) {
                console.error("Error navigating to document:", error);
                this.showError("Đã xảy ra lỗi khi điều hướng");
            }
        },
    },
    async mounted() {
        // Set today as initially selected
        this.selectedDate = new Date().toISOString().split("T")[0];

        // Load initial data
        await this.loadCalendarData();
    },
    beforeUnmount() {
        // Clean up modals before component destruction
        this.hideModal("documentDetailsModal");
        this.hideModal("dayDetailsModal");
    },
    watch: {
        // Watch for currentDate changes to reload data
        currentDate: {
            handler(newDate, oldDate) {
                if (
                    newDate &&
                    oldDate &&
                    (newDate.getMonth() !== oldDate.getMonth() ||
                        newDate.getFullYear() !== oldDate.getFullYear())
                ) {
                    this.loadCalendarData();
                }
            },
            deep: true,
        },
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
.page-header-calander {
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

/* Updated legend colors for document statuses */
.submitting-status {
    background-color: #f59e0b; /* Amber for submitting */
}

.received-status {
    background-color: #10b981; /* Green for received */
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

.tracking-user {
    margin-bottom: 0.5rem;
}

.user-send {
    font-size: 0.8rem;
    color: #666;
    font-weight: 500;
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

    .page-header-calander {
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

/* Loading Overlay */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255, 255, 255, 0.8);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.loading-spinner {
    text-align: center;
}

.loading-text {
    margin-top: 1rem;
    color: #666;
    font-weight: 500;
}

/* Loading states */
.loading {
    opacity: 0.7;
    pointer-events: none;
}

/* Skeleton loading for tracking items */
.skeleton-container {
    padding: 1rem;
}

.skeleton-item {
    padding: 1rem;
    border-bottom: 1px solid #e9ecef;
}

.skeleton-line {
    height: 12px;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
    border-radius: 4px;
    margin-bottom: 8px;
}

.skeleton-title {
    width: 80%;
    height: 16px;
}

.skeleton-meta {
    width: 60%;
}

.skeleton-status {
    width: 40%;
}

@keyframes loading {
    0% {
        background-position: -200% 0;
    }
    100% {
        background-position: 200% 0;
    }
}
/* เพิ่ม Enhanced Error Handling Styles */
.error-message {
    background-color: #fef2f2;
    border: 1px solid #fecaca;
    color: #dc2626;
    padding: 1rem;
    border-radius: 8px;
    margin: 1rem 0;
    text-align: center;
}

.error-icon {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.retry-button {
    background-color: #dc2626;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    cursor: pointer;
    margin-top: 0.5rem;
    transition: background-color 0.2s ease;
}

.retry-button:hover {
    background-color: #b91c1c;
}

/* Enhanced Mobile Responsive Design */
@media (max-width: 1200px) {
    .calendar-day {
        min-height: 100px;
    }

    .tracking-title {
        font-size: 0.9rem;
        line-height: 1.3;
    }
}

@media (max-width: 992px) {
    .row.gx-4 > .col-lg-8 {
        margin-bottom: 2rem;
    }

    .page-header-calander .d-flex {
        flex-direction: column;
        gap: 1rem;
    }

    .header-actions {
        justify-content: center;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
}

@media (max-width: 768px) {
    .calendar-grid {
        font-size: 0.875rem;
    }

    .calendar-day {
        min-height: 70px;
        padding: 0.5rem 0.25rem;
    }

    .day-number {
        font-size: 0.8rem;
        margin-bottom: 0.25rem;
    }

    .event-dot {
        width: 6px;
        height: 6px;
    }

    .tracking-item {
        padding: 0.75rem 0.5rem;
    }

    .tracking-title {
        font-size: 0.85rem;
    }

    .tracking-date,
    .user-send {
        font-size: 0.75rem;
    }

    .modal-dialog {
        margin: 0.5rem;
    }
}

@media (max-width: 576px) {
    .payment-schedule-container {
        padding: 0.5rem;
    }

    .page-header-calander {
        padding: 1rem;
    }

    .page-title {
        font-size: 1.25rem;
    }

    .calendar-day {
        min-height: 50px;
        padding: 0.25rem;
    }

    .calendar-day-header {
        padding: 0.5rem 0.25rem;
        font-size: 0.75rem;
    }

    .day-number {
        font-size: 0.75rem;
    }

    .tracking-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }

    .btn {
        font-size: 0.8rem;
        padding: 0.4rem 0.8rem;
    }

    .card-header h6 {
        font-size: 0.9rem;
    }
}

/* Accessibility Improvements */
@media (prefers-reduced-motion: reduce) {
    .calendar-card,
    .tracking-card {
        animation: none;
    }

    .btn,
    .calendar-day,
    .tracking-item {
        transition: none;
    }
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    .calendar-day {
        border: 2px solid #000;
    }

    .tracking-item {
        border-bottom: 2px solid #000;
    }

    .badge {
        border: 1px solid #000;
    }
}

/* Print styles */
@media print {
    .loading-overlay,
    .header-actions,
    .modal {
        display: none !important;
    }

    .page-header-calander {
        background: #f8f9fa !important;
        color: #000 !important;
    }

    .calendar-card,
    .tracking-card {
        box-shadow: none !important;
        border: 1px solid #000 !important;
    }
}

/* Enhanced Focus States */
.btn:focus,
.calendar-day:focus,
.tracking-item:focus {
    outline: 3px solid #10b981;
    outline-offset: 2px;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
}

/* Improved Loading States */
.loading-skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
}

/* Enhanced Event Status Colors */
.submitting-status {
    background-color: #f59e0b;
    box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2);
}

.received-status {
    background-color: #10b981;
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
}

.default-status {
    background-color: #6b7280;
    box-shadow: 0 0 0 2px rgba(107, 114, 128, 0.2);
}

/* Improved Calendar Grid for Touch Devices */
@media (pointer: coarse) {
    .calendar-day {
        min-height: 80px;
        cursor: pointer;
        -webkit-tap-highlight-color: rgba(16, 185, 129, 0.2);
    }

    .tracking-item {
        min-height: 60px;
        -webkit-tap-highlight-color: rgba(16, 185, 129, 0.2);
    }

    .btn {
        min-height: 44px;
        min-width: 44px;
    }
}

/* Dark mode support (if needed in future) */
@media (prefers-color-scheme: dark) {
    .payment-schedule-container {
        background-color: #1f2937;
        color: #f9fafb;
    }

    .calendar-card,
    .tracking-card {
        background-color: #374151;
        color: #f9fafb;
    }

    .calendar-day {
        border-color: #4b5563;
    }

    .tracking-item {
        border-color: #4b5563;
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

.total-card .summary-icon {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
}

.status-card .summary-icon {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

/* Documents Section */
.documents-section {
    background: white;
}

.document-category {
    border-bottom: 1px solid #e5e7eb;
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

.document-list {
    background: white;
}

.document-item {
    display: flex;
    align-items: center;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #f1f5f9;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
}

.document-item:hover {
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    transform: translateX(8px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.document-item:last-child {
    border-bottom: none;
}

.document-item::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: transparent;
    transition: background 0.2s ease;
}

.document-item:hover::before {
    background: linear-gradient(180deg, #10b981 0%, #059669 100%);
}

.document-info {
    flex: 1;
    min-width: 0;
}

.document-title {
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.5rem;
    font-size: 0.95rem;
    line-height: 1.4;
}

.document-meta {
    display: flex;
    gap: 1rem;
    margin-bottom: 0.5rem;
    flex-wrap: wrap;
}

.document-id,
.document-user {
    font-size: 0.8rem;
    color: #6b7280;
    display: flex;
    align-items: center;
}

.document-id {
    font-family: "Monaco", "Menlo", monospace;
    background: #f3f4f6;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-weight: 500;
}

.document-status .badge {
    font-size: 0.75rem;
    padding: 0.4rem 0.8rem;
    border-radius: 6px;
    font-weight: 600;
}

.document-action {
    color: #9ca3af;
    font-size: 0.875rem;
    transition: all 0.2s ease;
}

.document-item:hover .document-action {
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

/* Responsive Design for Day Details Modal */
@media (max-width: 768px) {
    .modal-xl {
        margin: 0.5rem;
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

    .document-item {
        padding: 1rem;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }

    .document-meta {
        width: 100%;
    }
}
</style>
