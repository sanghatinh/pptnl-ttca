<template>
    <div class="dashboard-container">
        <!-- Loading Overlay -->
        <div v-if="isLoading" class="loading-overlay">
            <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin fa-2x text-success"></i>
                <p class="mt-2">Đang tải dữ liệu...</p>
            </div>
        </div>

        <!-- Header Section -->
        <div class="dashboard-header mb-4">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="dashboard-title mb-2">
                        <i class="fas fa-chart-line me-3 text-success"></i>
                        Dashboard Nông Dân
                    </h1>
                    <p class="dashboard-subtitle text-muted">
                        Theo dõi và phân tích dữ liệu tài chính của bạn
                    </p>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="summary-cards mb-4">
            <div class="row g-3">
                <!-- Processing Card -->
                <div class="col-lg-4 col-md-4 col-12">
                    <div class="summary-card processing-card">
                        <div class="card-content">
                            <div class="summary-icon">
                                <i class="fas fa-sync-alt"></i>
                            </div>
                            <div class="summary-content">
                                <div class="summary-label">
                                    Tổng tiền đang xử lý
                                </div>
                                <h4 class="summary-value">
                                    {{
                                        formatCurrency(
                                            financialSummary.processing
                                        )
                                    }}
                                </h4>
                            </div>
                        </div>
                        <div class="summary-footer">
                            <span class="trend-up">
                                <i class="fas fa-arrow-up"></i> 12.5%
                            </span>
                            <span class="trend-label">so với tháng trước</span>
                        </div>
                    </div>
                </div>

                <!-- Pending Card -->
                <div class="col-lg-4 col-md-4 col-12">
                    <div class="summary-card pending-card">
                        <div class="card-content">
                            <div class="summary-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="summary-content">
                                <div class="summary-label">
                                    Tổng tiền chờ thanh toán
                                </div>
                                <h4 class="summary-value">
                                    {{
                                        formatCurrency(financialSummary.pending)
                                    }}
                                </h4>
                            </div>
                        </div>
                        <div class="summary-footer">
                            <span class="trend-down">
                                <i class="fas fa-arrow-down"></i> 5.3%
                            </span>
                            <span class="trend-label">so với tháng trước</span>
                        </div>
                    </div>
                </div>

                <!-- Received Card -->
                <div class="col-lg-4 col-md-4 col-12">
                    <div class="summary-card received-card">
                        <div class="card-content">
                            <div class="summary-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="summary-content">
                                <div class="summary-label">
                                    Tổng tiền đã nhận
                                </div>
                                <h4 class="summary-value">
                                    {{
                                        formatCurrency(
                                            financialSummary.received
                                        )
                                    }}
                                </h4>
                            </div>
                        </div>
                        <div class="summary-footer">
                            <span class="trend-up">
                                <i class="fas fa-arrow-up"></i> 18.2%
                            </span>
                            <span class="trend-label">so với tháng trước</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-section mb-4">
            <div class="row g-4">
                <!-- Update the debt chart section in the template -->
                <div class="col-xl-7 col-lg-6">
                    <div class="chart-section h-100">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-header bg-white border-0 pb-0">
                                <div
                                    class="d-flex justify-content-between align-items-center"
                                >
                                    <div>
                                        <h5 class="card-title mb-1">
                                            <i
                                                class="fas fa-chart-bar me-2 text-primary"
                                            ></i>
                                            Đã trả gốc vs tiền lãi suất
                                        </h5>
                                        <p class="text-muted small mb-0">
                                            Phân tích thanh toán gốc và lãi suất
                                            theo thời gian
                                        </p>
                                    </div>
                                    <div class="year-selector">
                                        <select
                                            class="form-select form-select-sm"
                                            v-model="selectedYear"
                                            @change="handleYearChange"
                                            :disabled="
                                                chartLoading ||
                                                paymentTypeChartLoading
                                            "
                                        >
                                            <option
                                                v-for="year in availableYears"
                                                :key="year"
                                                :value="year"
                                            >
                                                {{ year }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div
                                    v-if="chartLoading"
                                    class="text-center py-5"
                                >
                                    <i
                                        class="fas fa-spinner fa-spin fa-2x text-primary"
                                    ></i>
                                    <p class="mt-2">Đang tải biểu đồ...</p>
                                </div>
                                <div
                                    v-else-if="chartError"
                                    class="text-center py-5"
                                >
                                    <i
                                        class="fas fa-exclamation-circle fa-2x text-danger"
                                    ></i>
                                    <p class="mt-2 text-danger">
                                        {{ chartError }}
                                    </p>
                                </div>
                                <div v-else class="chart-container">
                                    <canvas
                                        ref="debtInterestChart"
                                        height="300"
                                    ></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Update the Payment Status Chart section -->

                <!-- Payment Status Chart -->
                <div class="col-xl-5 col-lg-6">
                    <div class="chart-section h-100">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-header bg-white border-0 pb-0">
                                <div
                                    class="d-flex justify-content-between align-items-center"
                                >
                                    <div>
                                        <h5 class="card-title mb-1">
                                            <i
                                                class="fas fa-chart-pie me-2 text-warning"
                                            ></i>
                                            Loại trả gốc
                                        </h5>
                                        <p class="text-muted small mb-0">
                                            Phân bố các loại thanh toán gốc
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div
                                    v-if="paymentTypeChartLoading"
                                    class="text-center py-5"
                                >
                                    <i
                                        class="fas fa-spinner fa-spin fa-2x text-primary"
                                    ></i>
                                    <p class="mt-2">Đang tải biểu đồ...</p>
                                </div>
                                <div
                                    v-else-if="paymentTypeChartError"
                                    class="text-center py-5"
                                >
                                    <i
                                        class="fas fa-exclamation-circle fa-2x text-danger"
                                    ></i>
                                    <p class="mt-2 text-danger">
                                        {{ paymentTypeChartError }}
                                    </p>
                                </div>
                                <div v-else class="row">
                                    <div class="col-lg-7">
                                        <div class="pie-chart-container">
                                            <canvas
                                                ref="paymentTypeDistChartRef"
                                                height="260"
                                            ></canvas>
                                        </div>
                                    </div>
                                    <div class="col-lg-5">
                                        <div class="pie-legend mt-4 mt-lg-0">
                                            <div
                                                v-for="(
                                                    item, index
                                                ) in paymentTypeData"
                                                :key="`payment-type-${index}`"
                                                class="legend-item mb-3"
                                            >
                                                <div
                                                    class="d-flex align-items-center"
                                                >
                                                    <div
                                                        class="color-box"
                                                        :style="`background-color: ${item.color}`"
                                                    ></div>
                                                    <div
                                                        class="legend-text flex-grow-1"
                                                    >
                                                        {{ item.label }}
                                                    </div>
                                                    <div class="legend-value">
                                                        {{
                                                            formatCurrency(
                                                                item.value,
                                                                false
                                                            )
                                                        }}
                                                    </div>
                                                </div>
                                                <div
                                                    class="progress mt-1"
                                                    style="height: 5px"
                                                >
                                                    <div
                                                        class="progress-bar"
                                                        :style="`width: ${item.percentage}%; background-color: ${item.color}`"
                                                    ></div>
                                                </div>
                                                <div
                                                    class="text-end mt-1 small"
                                                >
                                                    <span class="text-muted"
                                                        >{{
                                                            item.percentage
                                                        }}%</span
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

        <!-- Tables Section -->
        <div class="tables-section">
            <div class="row g-4">
                <!-- Payment Details Table -->
                <div class="col-xl-7 col-lg-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div
                            class="card-header bg-white border-0 d-flex justify-content-between align-items-center"
                        >
                            <h5 class="card-title mb-0">
                                <i
                                    class="fas fa-file-invoice-dollar me-2 text-primary"
                                ></i>
                                Phiếu đề nghị thanh toán
                            </h5>
                            <div class="table-actions">
                                <div class="input-group input-group-sm">
                                    <input
                                        type="text"
                                        class="form-control"
                                        placeholder="Tìm kiếm..."
                                        v-model="paymentSearch"
                                    />
                                    <button
                                        class="btn btn-outline-secondary"
                                        type="button"
                                    >
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive payment-table">
                                <table
                                    class="table table-hover align-middle mb-0"
                                >
                                    <thead class="table-light">
                                        <tr>
                                            <th
                                                style="width: 50px"
                                                class="text-center"
                                            >
                                                STT
                                            </th>
                                            <th>Mã giải ngân</th>
                                            <th>Loại thanh toán</th>
                                            <th class="text-end">
                                                Tổng tiền thanh toán
                                            </th>
                                            <th style="width: 150px">
                                                Trạng thái thanh toán
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-if="filteredPayments.length === 0"
                                        >
                                            <td
                                                colspan="5"
                                                class="text-center py-4"
                                            >
                                                <div class="empty-state">
                                                    <i
                                                        class="fas fa-search empty-icon"
                                                    ></i>
                                                    <p>
                                                        Không tìm thấy dữ liệu
                                                        thanh toán
                                                    </p>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr
                                            v-for="(
                                                payment, index
                                            ) in paginatedPayments"
                                            :key="payment.id"
                                        >
                                            <td class="text-center">
                                                {{
                                                    (currentPaymentPage - 1) *
                                                        pageSize +
                                                    index +
                                                    1
                                                }}
                                            </td>
                                            <td>{{ payment.code }}</td>
                                            <td>
                                                <span
                                                    :class="
                                                        getPaymentTypeBadge(
                                                            payment.type
                                                        )
                                                    "
                                                >
                                                    {{ payment.type }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                {{
                                                    formatCurrency(
                                                        payment.amount
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                <span
                                                    :class="
                                                        getStatusBadge(
                                                            payment.status
                                                        )
                                                    "
                                                >
                                                    {{ payment.status }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div
                                class="pagination-container p-3"
                                v-if="filteredPayments.length > 0"
                            >
                                <div
                                    class="d-flex justify-content-between align-items-center"
                                >
                                    <span class="text-muted small">
                                        Hiển thị
                                        {{
                                            (currentPaymentPage - 1) *
                                                pageSize +
                                            1
                                        }}
                                        đến
                                        {{
                                            Math.min(
                                                currentPaymentPage * pageSize,
                                                filteredPayments.length
                                            )
                                        }}
                                        trong {{ filteredPayments.length }} bản
                                        ghi
                                    </span>
                                    <nav aria-label="Payment pagination">
                                        <ul
                                            class="pagination pagination-sm mb-0"
                                        >
                                            <li
                                                class="page-item"
                                                :class="{
                                                    disabled:
                                                        currentPaymentPage ===
                                                        1,
                                                }"
                                            >
                                                <a
                                                    class="page-link"
                                                    href="#"
                                                    @click.prevent="
                                                        currentPaymentPage = 1
                                                    "
                                                >
                                                    <i
                                                        class="fas fa-angle-double-left"
                                                    ></i>
                                                </a>
                                            </li>
                                            <li
                                                class="page-item"
                                                :class="{
                                                    disabled:
                                                        currentPaymentPage ===
                                                        1,
                                                }"
                                            >
                                                <a
                                                    class="page-link"
                                                    href="#"
                                                    @click.prevent="
                                                        currentPaymentPage--
                                                    "
                                                >
                                                    <i
                                                        class="fas fa-angle-left"
                                                    ></i>
                                                </a>
                                            </li>
                                            <li
                                                v-for="page in getPaymentPageNumbers()"
                                                :key="'payment-page-' + page"
                                                class="page-item"
                                                :class="{
                                                    active:
                                                        currentPaymentPage ===
                                                        page,
                                                }"
                                            >
                                                <a
                                                    class="page-link"
                                                    href="#"
                                                    @click.prevent="
                                                        currentPaymentPage =
                                                            page
                                                    "
                                                    >{{ page }}</a
                                                >
                                            </li>
                                            <li
                                                class="page-item"
                                                :class="{
                                                    disabled:
                                                        currentPaymentPage ===
                                                        totalPaymentPages,
                                                }"
                                            >
                                                <a
                                                    class="page-link"
                                                    href="#"
                                                    @click.prevent="
                                                        currentPaymentPage++
                                                    "
                                                >
                                                    <i
                                                        class="fas fa-angle-right"
                                                    ></i>
                                                </a>
                                            </li>
                                            <li
                                                class="page-item"
                                                :class="{
                                                    disabled:
                                                        currentPaymentPage ===
                                                        totalPaymentPages,
                                                }"
                                            >
                                                <a
                                                    class="page-link"
                                                    href="#"
                                                    @click.prevent="
                                                        currentPaymentPage =
                                                            totalPaymentPages
                                                    "
                                                >
                                                    <i
                                                        class="fas fa-angle-double-right"
                                                    ></i>
                                                </a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Work Items Table -->
                <div class="col-xl-5 col-lg-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div
                            class="card-header bg-white border-0 d-flex justify-content-between align-items-center"
                        >
                            <h5 class="card-title mb-0">
                                <i class="fas fa-tasks me-2 text-success"></i>
                                Hạng mục công việc
                            </h5>
                            <div class="table-actions">
                                <div class="input-group input-group-sm">
                                    <input
                                        type="text"
                                        class="form-control"
                                        placeholder="Tìm kiếm..."
                                        v-model="workSearch"
                                    />
                                    <button
                                        class="btn btn-outline-secondary"
                                        type="button"
                                    >
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive work-table">
                                <table
                                    class="table table-hover align-middle mb-0"
                                >
                                    <thead class="table-light">
                                        <tr>
                                            <th
                                                style="width: 50px"
                                                class="text-center"
                                            >
                                                STT
                                            </th>
                                            <th>Tên công việc</th>
                                            <th class="text-center">
                                                Số lượng
                                            </th>
                                            <th>Đơn vị tính</th>
                                            <th class="text-end">Tổng tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-if="
                                                filteredWorkItems.length === 0
                                            "
                                        >
                                            <td
                                                colspan="5"
                                                class="text-center py-4"
                                            >
                                                <div class="empty-state">
                                                    <i
                                                        class="fas fa-search empty-icon"
                                                    ></i>
                                                    <p>
                                                        Không tìm thấy dữ liệu
                                                        công việc
                                                    </p>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr
                                            v-for="(
                                                item, index
                                            ) in paginatedWorkItems"
                                            :key="item.id"
                                        >
                                            <td class="text-center">
                                                {{
                                                    (currentWorkPage - 1) *
                                                        pageSize +
                                                    index +
                                                    1
                                                }}
                                            </td>
                                            <td>{{ item.name }}</td>
                                            <td class="text-center">
                                                <span
                                                    class="badge bg-info text-white rounded-pill"
                                                >
                                                    {{ item.quantity }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="unit-badge">{{
                                                    item.unit
                                                }}</span>
                                            </td>
                                            <td class="text-end">
                                                <div class="amount-display">
                                                    <span
                                                        class="amount-value text-success fw-medium"
                                                    >
                                                        {{
                                                            formatCurrency(
                                                                item.amount
                                                            )
                                                        }}
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot
                                        class="table-light"
                                        v-if="filteredWorkItems.length > 0"
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
                                                        totalWorkAmount
                                                    )
                                                }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div
                                class="pagination-container p-3"
                                v-if="filteredWorkItems.length > 0"
                            >
                                <div
                                    class="d-flex justify-content-between align-items-center"
                                >
                                    <span class="text-muted small">
                                        Hiển thị
                                        {{
                                            (currentWorkPage - 1) * pageSize + 1
                                        }}
                                        đến
                                        {{
                                            Math.min(
                                                currentWorkPage * pageSize,
                                                filteredWorkItems.length
                                            )
                                        }}
                                        trong {{ filteredWorkItems.length }} bản
                                        ghi
                                    </span>
                                    <nav aria-label="Work pagination">
                                        <ul
                                            class="pagination pagination-sm mb-0"
                                        >
                                            <li
                                                class="page-item"
                                                :class="{
                                                    disabled:
                                                        currentWorkPage === 1,
                                                }"
                                            >
                                                <a
                                                    class="page-link"
                                                    href="#"
                                                    @click.prevent="
                                                        currentWorkPage = 1
                                                    "
                                                >
                                                    <i
                                                        class="fas fa-angle-double-left"
                                                    ></i>
                                                </a>
                                            </li>
                                            <li
                                                class="page-item"
                                                :class="{
                                                    disabled:
                                                        currentWorkPage === 1,
                                                }"
                                            >
                                                <a
                                                    class="page-link"
                                                    href="#"
                                                    @click.prevent="
                                                        currentWorkPage--
                                                    "
                                                >
                                                    <i
                                                        class="fas fa-angle-left"
                                                    ></i>
                                                </a>
                                            </li>
                                            <li
                                                v-for="page in getWorkPageNumbers()"
                                                :key="'work-page-' + page"
                                                class="page-item"
                                                :class="{
                                                    active:
                                                        currentWorkPage ===
                                                        page,
                                                }"
                                            >
                                                <a
                                                    class="page-link"
                                                    href="#"
                                                    @click.prevent="
                                                        currentWorkPage = page
                                                    "
                                                    >{{ page }}</a
                                                >
                                            </li>
                                            <li
                                                class="page-item"
                                                :class="{
                                                    disabled:
                                                        currentWorkPage ===
                                                        totalWorkPages,
                                                }"
                                            >
                                                <a
                                                    class="page-link"
                                                    href="#"
                                                    @click.prevent="
                                                        currentWorkPage++
                                                    "
                                                >
                                                    <i
                                                        class="fas fa-angle-right"
                                                    ></i>
                                                </a>
                                            </li>
                                            <li
                                                class="page-item"
                                                :class="{
                                                    disabled:
                                                        currentWorkPage ===
                                                        totalWorkPages,
                                                }"
                                            >
                                                <a
                                                    class="page-link"
                                                    href="#"
                                                    @click.prevent="
                                                        currentPaymentPage =
                                                            totalWorkPages
                                                    "
                                                >
                                                    <i
                                                        class="fas fa-angle-double-right"
                                                    ></i>
                                                </a>
                                            </li>
                                        </ul>
                                    </nav>
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
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from "vue";
import { useStore } from "../../Store/Auth";
import {
    Chart,
    CategoryScale,
    LinearScale,
    BarElement,
    LineElement,
    PointElement,
    BarController,
    LineController,
    ArcElement,
    DoughnutController,
    PieController, // Add this import
    Title,
    Tooltip,
    Legend,
} from "chart.js";

// Register Chart.js components
Chart.register(
    CategoryScale,
    LinearScale,
    BarElement,
    LineElement,
    PointElement,
    BarController,
    LineController,
    ArcElement,
    PieController, // Add this registration
    DoughnutController,
    Title,
    Tooltip,
    Legend
);

export default {
    name: "DashboardFarmer",
    setup() {
        const store = useStore();
        const isLoading = ref(false);
        const isComponentMounted = ref(false);
        const chartLoading = ref(false);
        const chartError = ref(null);

        // References to chart canvases
        const debtInterestChart = ref(null);
        const statusChart = ref(null);
        const debtChart = ref(null); // เพิ่มตัวนี้ที่หายไป

        // Add these inside the setup() function, in the data section
        const paymentTypeDistChartRef = ref(null);
        let paymentTypeDistChartInstance = null;
        const paymentTypeData = ref([]);
        const paymentTypeChartLoading = ref(false);
        const paymentTypeChartError = ref(null);

        // Chart instances
        let debtInterestChartInstance = null;
        let statusChartInstance = null;
        let debtChartInstance = null; // เพิ่มตัวนี้ที่หายไป

        // Time period selection
        const selectedPeriod = ref("month");
        const periods = [
            { label: "7 ngày", value: "week" },
            { label: "1 tháng", value: "month" },
            { label: "3 tháng", value: "quarter" },
            { label: "1 năm", value: "year" },
        ];

        // Year selection for debt vs interest chart
        const selectedYear = ref(new Date().getFullYear());
        const availableYears = ref([]);

        // Summary data
        const financialSummary = ref({
            processing: 0,
            pending: 0,
            received: 0,
        });

        // Debt vs paid data
        const debtData = ref({
            labels: [],
            totalDebt: [],
            paidAmount: [],
        });

        // Debt vs interest data
        const debtInterestData = ref({
            labels: [],
            principalPaid: [],
            interestPaid: [],
        });

        // Payment status data
        const paymentStatusData = ref([
            { label: "Đã thanh toán", value: 68750000 },
            { label: "Đang xử lý", value: 15800000 },
            { label: "Chờ thanh toán", value: 37500000 },
            { label: "Từ chối", value: 4200000 },
        ]);

        const statusColors = [
            "rgba(75, 192, 192, 0.8)",
            "rgba(54, 162, 235, 0.8)",
            "rgba(255, 159, 64, 0.8)",
            "rgba(255, 99, 132, 0.8)",
            "rgba(153, 102, 255, 0.8)",
        ];

        // Colors for the chart
        const chartColors = {
            principal: "rgba(75, 192, 192, 0.8)",
            interest: "rgba(255, 159, 64, 0.8)",
        };

        // Payment details
        const pageSize = 10;
        const paymentSearch = ref("");
        const currentPaymentPage = ref(1);
        const payments = ref([
            // ... existing payment data ...
        ]);

        // Work items
        const workSearch = ref("");
        const currentWorkPage = ref(1);
        const workItems = ref([
            // ... existing work items data ...
        ]);

        const handleYearChange = async () => {
            // Update both charts when year changes
            await fetchDebtVsInterestData();
            await fetchPaymentTypeDistribution();
        };

        const fetchPaymentRequests = async () => {
            try {
                const headers = store.getAuthHeaders();
                const response = await axios.get(
                    "/api/farmer/dashboard/payment-requests",
                    {
                        headers,
                    }
                );

                if (response.data.success) {
                    payments.value = response.data.data;
                } else {
                    console.error(
                        "Error fetching payment requests:",
                        response.data.message
                    );
                }
            } catch (error) {
                console.error("Error fetching payment requests:", error);
            }
        };

        // Add to the methods section
        const fetchPaymentTypeDistribution = async (month = null) => {
            paymentTypeChartLoading.value = true;
            paymentTypeChartError.value = null;

            try {
                const headers = store.getAuthHeaders();
                const params = {
                    year: selectedYear.value,
                };

                // Add month parameter if provided
                if (month) {
                    params.month = month;
                }

                const response = await axios.get(
                    `/api/farmer/dashboard/payment-type-distribution`,
                    {
                        headers,
                        params,
                    }
                );

                if (response.data.success) {
                    paymentTypeData.value = response.data.data.items || [];

                    // Update chart with the new data
                    await nextTick();
                    createPaymentTypeChart();
                } else {
                    paymentTypeChartError.value =
                        response.data.message ||
                        "Không thể tải dữ liệu biểu đồ";
                }
            } catch (error) {
                console.error(
                    "Error fetching payment type distribution data:",
                    error
                );
                paymentTypeChartError.value = "Đã xảy ra lỗi khi tải dữ liệu";
            } finally {
                paymentTypeChartLoading.value = false;
            }
        };

        const createPaymentTypeChart = () => {
            if (paymentTypeDistChartInstance) {
                paymentTypeDistChartInstance.destroy();
            }

            // Make sure chart element exists
            if (!paymentTypeDistChartRef.value) {
                console.log(
                    "Payment type chart creation skipped - ref is null"
                );
                // Try again after a short delay
                setTimeout(() => {
                    if (paymentTypeDistChartRef.value) {
                        createPaymentTypeChart();
                    }
                }, 200);
                return;
            }

            const ctx = paymentTypeDistChartRef.value.getContext("2d");

            // Prepare data for chart
            const labels = paymentTypeData.value.map((item) => item.label);
            const values = paymentTypeData.value.map((item) => item.value);
            const backgroundColors = paymentTypeData.value.map(
                (item) => item.color
            );

            paymentTypeDistChartInstance = new Chart(ctx, {
                type: "pie",
                data: {
                    labels: labels,
                    datasets: [
                        {
                            data: values,
                            backgroundColor: backgroundColors,
                            borderWidth: 1,
                            borderColor: backgroundColors.map((color) =>
                                color.replace("0.8", "1")
                            ),
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const item =
                                        paymentTypeData.value[
                                            context.dataIndex
                                        ];
                                    const formattedValue =
                                        new Intl.NumberFormat("lo-LA").format(
                                            item.value
                                        );
                                    return `${item.label}: ${formattedValue} ₭ (${item.percentage}%)`;
                                },
                            },
                        },
                    },
                    cutout: "65%",
                },
            });
        };

        const fetchFinancialSummary = async () => {
            try {
                const headers = store.getAuthHeaders();
                const response = await axios.get(
                    "/api/farmer/dashboard/financial-summary",
                    {
                        headers,
                    }
                );

                if (response.data.success) {
                    financialSummary.value = response.data.data;
                } else {
                    console.error(
                        "Error fetching financial summary:",
                        response.data.message
                    );
                }
            } catch (error) {
                console.error("Error fetching financial summary:", error);
            }
        };

        // Filtered and paginated data
        const filteredPayments = computed(() => {
            if (!paymentSearch.value) return payments.value;
            const search = paymentSearch.value.toLowerCase();
            return payments.value.filter(
                (p) =>
                    p.code.toLowerCase().includes(search) ||
                    p.type.toLowerCase().includes(search) ||
                    p.status.toLowerCase().includes(search)
            );
        });

        const totalPaymentPages = computed(() =>
            Math.ceil(filteredPayments.value.length / pageSize)
        );

        const paginatedPayments = computed(() => {
            const start = (currentPaymentPage.value - 1) * pageSize;
            const end = start + pageSize;
            return filteredPayments.value.slice(start, end);
        });

        const filteredWorkItems = computed(() => {
            if (!workSearch.value) return workItems.value;
            const search = workSearch.value.toLowerCase();
            return workItems.value.filter(
                (w) =>
                    w.name.toLowerCase().includes(search) ||
                    w.unit.toLowerCase().includes(search)
            );
        });

        const totalWorkPages = computed(() =>
            Math.ceil(filteredWorkItems.value.length / pageSize)
        );

        const paginatedWorkItems = computed(() => {
            const start = (currentWorkPage.value - 1) * pageSize;
            const end = start + pageSize;
            return filteredWorkItems.value.slice(start, end);
        });

        const totalWorkAmount = computed(() => {
            return filteredWorkItems.value.reduce(
                (sum, item) => sum + item.amount,
                0
            );
        });

        // Methods
        const formatCurrency = (amount) => {
            if (isNaN(amount) || amount === null || amount === undefined) {
                return "0 ₭";
            }
            return new Intl.NumberFormat("lo-LA").format(amount) + " ₭";
        };

        const getProgressColor = (progress) => {
            if (progress === 0) return "bg-danger";
            if (progress < 50) return "bg-warning";
            if (progress < 100) return "bg-info";
            return "bg-success";
        };

        const getPaymentTypeBadge = (type) => {
            switch (type) {
                case "Phiếu giao nhận hom giống":
                    return "badge bg-primary text-white rounded-pill";
                case "Nghiệm thu dịch vụ":
                    return "badge bg-info text-white rounded-pill";
                default:
                    return "badge bg-secondary text-white rounded-pill";
            }
        };

        const getStatusBadge = (status) => {
            switch (status) {
                case "Đã thanh toán":
                    return "badge bg-success text-white";
                case "Đang xử lý":
                    return "badge bg-info text-white";
                case "Chờ thanh toán":
                    return "badge bg-warning text-dark";
                case "Từ chối":
                    return "badge bg-danger text-white";
                default:
                    return "badge bg-secondary text-white";
            }
        };

        const getPaymentPageNumbers = () => {
            const totalPages = totalPaymentPages.value;
            const current = currentPaymentPage.value;

            if (totalPages <= 5) {
                return Array.from({ length: totalPages }, (_, i) => i + 1);
            }

            if (current <= 3) {
                return [1, 2, 3, 4, 5];
            }

            if (current >= totalPages - 2) {
                return [
                    totalPages - 4,
                    totalPages - 3,
                    totalPages - 2,
                    totalPages - 1,
                    totalPages,
                ];
            }

            return [
                current - 2,
                current - 1,
                current,
                current + 1,
                current + 2,
            ];
        };

        const getWorkPageNumbers = () => {
            const totalPages = totalWorkPages.value;
            const current = currentWorkPage.value;

            if (totalPages <= 5) {
                return Array.from({ length: totalPages }, (_, i) => i + 1);
            }

            if (current <= 3) {
                return [1, 2, 3, 4, 5];
            }

            if (current >= totalPages - 2) {
                return [
                    totalPages - 4,
                    totalPages - 3,
                    totalPages - 2,
                    totalPages - 1,
                    totalPages,
                ];
            }

            return [
                current - 2,
                current - 1,
                current,
                current + 1,
                current + 2,
            ];
        };

        const getPaymentPercentage = (value) => {
            const total = paymentStatusData.value.reduce(
                (sum, item) => sum + item.value,
                0
            );
            return total > 0 ? Math.round((value / total) * 100) : 0;
        };

        const fetchDebtData = async () => {
            chartLoading.value = true;
            chartError.value = null;

            try {
                // Simulating API call with timeout
                await new Promise((resolve) => setTimeout(resolve, 800));

                // Sample data based on selected period
                let labels, totalDebt, paidAmount;

                switch (selectedPeriod.value) {
                    case "week":
                        labels = [
                            "Thứ 2",
                            "Thứ 3",
                            "Thứ 4",
                            "Thứ 5",
                            "Thứ 6",
                            "Thứ 7",
                            "CN",
                        ];
                        totalDebt = [
                            125000000, 123000000, 122000000, 120500000,
                            118000000, 116000000, 115000000,
                        ];
                        paidAmount = [
                            0, 2000000, 3000000, 4500000, 7000000, 9000000,
                            10000000,
                        ];
                        break;
                    case "month":
                        labels = ["Tuần 1", "Tuần 2", "Tuần 3", "Tuần 4"];
                        totalDebt = [
                            130000000, 125000000, 120000000, 115000000,
                        ];
                        paidAmount = [0, 5000000, 10000000, 15000000];
                        break;
                    case "quarter":
                        labels = ["Tháng 1", "Tháng 2", "Tháng 3"];
                        totalDebt = [150000000, 135000000, 115000000];
                        paidAmount = [0, 15000000, 35000000];
                        break;
                    case "year":
                        labels = ["Q1", "Q2", "Q3", "Q4"];
                        totalDebt = [
                            180000000, 165000000, 140000000, 115000000,
                        ];
                        paidAmount = [0, 15000000, 40000000, 65000000];
                        break;
                    default:
                        labels = ["Tuần 1", "Tuần 2", "Tuần 3", "Tuần 4"];
                        totalDebt = [
                            130000000, 125000000, 120000000, 115000000,
                        ];
                        paidAmount = [0, 5000000, 10000000, 15000000];
                }

                debtData.value = {
                    labels,
                    totalDebt,
                    paidAmount,
                };

                await nextTick();
                createDebtChart();
            } catch (error) {
                console.error("Error fetching debt data:", error);
                chartError.value = "Không thể tải dữ liệu biểu đồ";
            } finally {
                chartLoading.value = false;
            }
        };

        const createDebtChart = () => {
            if (debtChartInstance) {
                debtChartInstance.destroy();
            }

            // ตรวจสอบว่า debtChart.value มีอยู่หรือไม่
            if (!debtChart.value) {
                console.error("debtChart ref is null");
                return;
            }

            const ctx = debtChart.value.getContext("2d");

            debtChartInstance = new Chart(ctx, {
                type: "bar",
                data: {
                    labels: debtData.value.labels,
                    datasets: [
                        {
                            label: "Tổng nợ",
                            data: debtData.value.totalDebt.map(
                                (val) => val / 1000000
                            ),
                            backgroundColor: "rgba(255, 99, 132, 0.8)",
                            borderColor: "rgba(255, 99, 132, 1)",
                            borderWidth: 1,
                        },
                        {
                            label: "Đã trả gốc",
                            data: debtData.value.paidAmount.map(
                                (val) => val / 1000000
                            ),
                            backgroundColor: "rgba(75, 192, 192, 0.8)",
                            borderColor: "rgba(75, 192, 192, 1)",
                            borderWidth: 1,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: "top",
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    let label = context.dataset.label || "";
                                    if (label) {
                                        label += ": ";
                                    }
                                    if (context.parsed.y !== null) {
                                        label +=
                                            new Intl.NumberFormat(
                                                "lo-LA"
                                            ).format(context.parsed.y) +
                                            " triệu ₭";
                                    }
                                    return label;
                                },
                            },
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: "Triệu ₭",
                            },
                            ticks: {
                                callback: function (value) {
                                    return value.toLocaleString() + "M";
                                },
                            },
                        },
                    },
                },
            });
        };

        const fetchDebtVsInterestData = async () => {
            chartLoading.value = true;
            chartError.value = null;

            try {
                const headers = store.getAuthHeaders();
                const response = await axios.get(
                    `/api/farmer/dashboard/debt-vs-interest`,
                    {
                        headers,
                        params: { year: selectedYear.value },
                    }
                );

                if (response.data.success) {
                    // Update data from API
                    debtInterestData.value = {
                        labels: response.data.data.labels || [],
                        principalPaid: response.data.data.principalPaid || [],
                        interestPaid: response.data.data.interestPaid || [],
                    };

                    // Update available years if provided
                    if (
                        response.data.data.availableYears &&
                        response.data.data.availableYears.length > 0
                    ) {
                        availableYears.value =
                            response.data.data.availableYears;
                    }

                    // If selected year is not in available years, select the first one
                    if (
                        availableYears.value.length > 0 &&
                        !availableYears.value.includes(selectedYear.value)
                    ) {
                        selectedYear.value = availableYears.value[0];
                    }

                    // รอให้ DOM update และสร้าง chart
                    await nextTick();
                    // รอสักครู่เพิ่มเติมเพื่อให้แน่ใจว่า DOM พร้อม
                    setTimeout(() => {
                        createDebtVsInterestChart();
                    }, 50);
                } else {
                    chartError.value =
                        response.data.message ||
                        "Không thể tải dữ liệu biểu đồ";
                }
            } catch (error) {
                console.error("Error fetching debt vs interest data:", error);
                chartError.value = "Đã xảy ra lỗi khi tải dữ liệu";
            } finally {
                chartLoading.value = false;
            }
        };

        const createDebtVsInterestChart = () => {
            if (debtInterestChartInstance) {
                debtInterestChartInstance.destroy();
            }

            // ตรวจสอบว่า debtInterestChart.value มีอยู่หรือไม่
            if (!debtInterestChart.value) {
                console.error("debtInterestChart ref is null");
                // รอสักครู่แล้วลองใหม่
                setTimeout(() => {
                    if (debtInterestChart.value) {
                        createDebtVsInterestChart();
                    }
                }, 100);
                return;
            }

            const ctx = debtInterestChart.value.getContext("2d");

            debtInterestChartInstance = new Chart(ctx, {
                type: "bar",
                data: {
                    labels: debtInterestData.value.labels,
                    datasets: [
                        {
                            label: "Đã trả gốc",
                            data: debtInterestData.value.principalPaid,
                            backgroundColor: chartColors.principal,
                            borderColor: chartColors.principal.replace(
                                "0.8",
                                "1"
                            ),
                            borderWidth: 1,
                        },
                        {
                            label: "Tiền lãi suất",
                            data: debtInterestData.value.interestPaid,
                            backgroundColor: chartColors.interest,
                            borderColor: chartColors.interest.replace(
                                "0.8",
                                "1"
                            ),
                            borderWidth: 1,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    onClick: (event, elements) => {
                        if (elements && elements.length > 0) {
                            const index = elements[0].index;
                            const month = index + 1; // Months are 1-indexed

                            // Update the payment type chart with the selected month
                            fetchPaymentTypeDistribution(month);
                        }
                    },
                    plugins: {
                        legend: {
                            position: "top",
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    let label = context.dataset.label || "";
                                    if (label) {
                                        label += ": ";
                                    }
                                    if (context.parsed.y !== null) {
                                        label +=
                                            new Intl.NumberFormat(
                                                "lo-LA"
                                            ).format(context.parsed.y) + " ₭";
                                    }
                                    return label;
                                },
                            },
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: "Số tiền (₭)",
                            },
                            ticks: {
                                callback: function (value) {
                                    if (value >= 1000000) {
                                        return (
                                            (value / 1000000).toLocaleString() +
                                            "M"
                                        );
                                    } else if (value >= 1000) {
                                        return (
                                            (value / 1000).toLocaleString() +
                                            "K"
                                        );
                                    }
                                    return value.toLocaleString();
                                },
                            },
                        },
                    },
                },
            });
        };

        const createStatusChart = () => {
            if (statusChartInstance) {
                statusChartInstance.destroy();
            }

            // Skip chart creation if the ref doesn't exist
            if (!statusChart.value) {
                console.log("Status chart creation skipped - ref is null");
                return;
            }

            const ctx = statusChart.value.getContext("2d");

            statusChartInstance = new Chart(ctx, {
                type: "doughnut",
                data: {
                    labels: paymentStatusData.value.map((item) => item.label),
                    datasets: [
                        {
                            data: paymentStatusData.value.map(
                                (item) => item.value
                            ),
                            backgroundColor: statusColors,
                            borderColor: statusColors.map((color) =>
                                color.replace("0.8", "1")
                            ),
                            borderWidth: 1,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const value = context.raw;
                                    const total = context.dataset.data.reduce(
                                        (a, b) => a + b,
                                        0
                                    );
                                    const percentage = (
                                        (value / total) *
                                        100
                                    ).toFixed(1);
                                    return `${
                                        context.label
                                    }: ${new Intl.NumberFormat("lo-LA").format(
                                        value
                                    )} ₭ (${percentage}%)`;
                                },
                            },
                        },
                    },
                    cutout: "65%",
                },
            });
        };

        const destroyCharts = () => {
            if (debtChartInstance) {
                debtChartInstance.destroy();
                debtChartInstance = null;
            }
            if (statusChartInstance) {
                statusChartInstance.destroy();
                statusChartInstance = null;
            }
            if (debtInterestChartInstance) {
                debtInterestChartInstance.destroy();
                debtInterestChartInstance = null;
            }
            if (paymentTypeDistChartInstance) {
                paymentTypeDistChartInstance.destroy();
                paymentTypeDistChartInstance = null;
            }
        };

        // Lifecycle hooks
        onMounted(async () => {
            isComponentMounted.value = true;
            isLoading.value = true;

            try {
                // Fetch data first
                await Promise.all([
                    fetchFinancialSummary(),
                    fetchPaymentRequests(),
                    new Promise((resolve) => setTimeout(resolve, 500)),
                ]);

                // Wait for DOM to be ready
                await nextTick();

                // Use a longer timeout to ensure DOM elements are available
                setTimeout(async () => {
                    // Only create status chart if the element exists
                    if (statusChart.value) {
                        createStatusChart();
                    } else {
                        console.log("Status chart element not found in DOM");
                    }

                    await fetchDebtVsInterestData();

                    // For payment type chart
                    if (paymentTypeDistChartRef.value) {
                        await fetchPaymentTypeDistribution();
                    } else {
                        console.log(
                            "Payment type chart element not found in DOM"
                        );
                        // Try again after a delay
                        setTimeout(async () => {
                            if (paymentTypeDistChartRef.value) {
                                await fetchPaymentTypeDistribution();
                            }
                        }, 300);
                    }
                }, 300);
            } catch (error) {
                console.error("Error initializing dashboard:", error);
            } finally {
                isLoading.value = false;
            }
        });

        onBeforeUnmount(() => {
            isComponentMounted.value = false;
            destroyCharts();
        });

        return {
            // State
            store,
            isLoading,
            chartLoading,
            chartError,
            debtInterestChart, // ตรวจสอบว่ามีใน return
            statusChart,
            debtChart, // ถ้าไม่ใช้จริงสามารถลบออกได้
            selectedPeriod,
            periods,
            financialSummary,
            paymentStatusData,
            statusColors,
            paymentSearch,
            workSearch,
            currentPaymentPage,
            currentWorkPage,
            pageSize,
            selectedYear,
            availableYears,

            // Computed
            filteredPayments,
            paginatedPayments,
            totalPaymentPages,
            filteredWorkItems,
            paginatedWorkItems,
            totalWorkPages,
            totalWorkAmount,

            // Methods
            formatCurrency,
            getProgressColor,
            getPaymentTypeBadge,
            getStatusBadge,
            getPaymentPageNumbers,
            getWorkPageNumbers,
            getPaymentPercentage,
            fetchDebtData,
            fetchDebtVsInterestData,
            fetchPaymentTypeDistribution,
            handleYearChange,

            // Add new properties for payment type chart
            paymentTypeDistChartRef,
            paymentTypeChartLoading,
            paymentTypeChartError,
            paymentTypeData,
        };
    },
};
</script>

<style scoped>
.dashboard-container {
    padding: 1.5rem;
}

/* Update the dashboard-header CSS */
.dashboard-header {
    margin-bottom: 1.5rem;
    background-color: white;
    border-radius: 0.75rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1),
        0 2px 4px -1px rgba(0, 0, 0, 0.06);
    padding: 1.5rem;
    border: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.dashboard-header:hover {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1),
        0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.dashboard-title {
    font-size: 1.75rem;
    font-weight: 600;
    color: #1a202c;
}

.dashboard-subtitle {
    font-size: 1rem;
    color: #718096;
}

/* Loading overlay */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    flex-direction: column;
}

.loading-spinner {
    text-align: center;
}

/* Summary cards - Improved */
.summary-cards {
    margin-bottom: 1.5rem;
}

.summary-card {
    display: flex;
    flex-direction: column;
    padding: 1.25rem;
    border-radius: 0.75rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    background-color: white;
    transition: all 0.3s ease;
    height: 100%;
    position: relative;
    overflow: hidden;
}

.summary-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.card-content {
    display: flex;
    align-items: center;
    margin-bottom: 1.25rem;
}

.summary-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 3.5rem;
    height: 3.5rem;
    border-radius: 1rem;
    margin-right: 1rem;
    font-size: 1.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.summary-content {
    flex: 1;
}

.summary-label {
    font-size: 0.875rem;
    color: #718096;
    margin-bottom: 0.25rem;
    font-weight: 500;
}

.summary-value {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
    letter-spacing: -0.5px;
}

.summary-footer {
    display: flex;
    align-items: center;
    font-size: 0.75rem;
    padding-top: 0.75rem;
    border-top: 1px solid rgba(0, 0, 0, 0.05);
}

.trend-up,
.trend-down {
    font-weight: 600;
    margin-right: 0.5rem;
    padding: 0.2rem 0.5rem;
    border-radius: 1rem;
    font-size: 0.7rem;
}

.trend-up {
    background-color: rgba(72, 187, 120, 0.1);
    color: #48bb78;
}

.trend-down {
    background-color: rgba(245, 101, 101, 0.1);
    color: #f56565;
}

.trend-label {
    color: #a0aec0;
}

.processing-card {
    background: linear-gradient(145deg, #ffffff, #f0f9ff);
}

.pending-card {
    background: linear-gradient(145deg, #ffffff, #fff8f0);
}

.received-card {
    background: linear-gradient(145deg, #ffffff, #f0fff4);
}

.processing-card .summary-icon {
    background-color: rgba(66, 153, 225, 0.2);
    color: #3182ce;
}

.pending-card .summary-icon {
    background-color: rgba(237, 137, 54, 0.2);
    color: #dd6b20;
}

.received-card .summary-icon {
    background-color: rgba(72, 187, 120, 0.2);
    color: #38a169;
}

.processing-card::before {
    content: "";
    position: absolute;
    top: -50px;
    right: -50px;
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: rgba(66, 153, 225, 0.05);
    z-index: 0;
}

.pending-card::before {
    content: "";
    position: absolute;
    top: -50px;
    right: -50px;
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: rgba(237, 137, 54, 0.05);
    z-index: 0;
}

.received-card::before {
    content: "";
    position: absolute;
    top: -50px;
    right: -50px;
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: rgba(72, 187, 120, 0.05);
    z-index: 0;
}

/* Table improvements */
.payment-table,
.work-table {
    overflow-y: auto;
    max-height: 400px;
    position: relative;
}

.table-responsive {
    margin-bottom: 0;
}

/* Badge styling */
.badge {
    padding: 0.35rem 0.65rem;
    font-weight: 500;
    font-size: 0.75rem;
}

.badge.rounded-pill {
    padding-left: 0.75rem;
    padding-right: 0.75rem;
}

/* Charts */
.chart-container {
    position: relative;
    height: 300px;
}

.pie-chart-container {
    height: 260px;
    position: relative;
}

.time-selector {
    display: flex;
    align-items: center;
}

.btn-group .btn {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

/* Pie chart legend */
.pie-legend {
    padding: 0.5rem 0;
}

.legend-item {
    margin-bottom: 0.75rem;
}

.color-box {
    width: 12px;
    height: 12px;
    border-radius: 3px;
    margin-right: 8px;
}

.legend-text {
    font-size: 0.75rem;
    color: #4a5568;
}

.legend-value {
    font-size: 0.75rem;
    font-weight: 600;
}

/* Tables */
.tables-section {
    margin-top: 1.5rem;
}

.table-actions {
    display: flex;
    align-items: center;
}

.table th {
    font-size: 0.75rem;
    font-weight: 600;
    position: sticky;
    top: 0;
    background-color: #f8fafc;
    z-index: 1;
    text-align: center;
}

.table td {
    font-size: 0.875rem;
    vertical-align: middle;
    white-space: nowrap;
}

.empty-state {
    text-align: center;
    padding: 2rem;
}

.empty-icon {
    font-size: 2rem;
    color: #cbd5e0;
    margin-bottom: 0.5rem;
}

.unit-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    background-color: #e2e8f0;
    color: #4a5568;
    font-size: 0.75rem;
}

.amount-display {
    text-align: right;
}

.amount-value {
    font-weight: 500;
}

.pagination-container {
    border-top: 1px solid #e2e8f0;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .dashboard-container {
        padding: 1rem;
    }

    .charts-section,
    .tables-section {
        margin-top: 1rem;
    }

    .summary-card {
        padding: 1rem;
    }

    .summary-icon {
        width: 2.75rem;
        height: 2.75rem;
        font-size: 1.25rem;
    }

    .summary-value {
        font-size: 1.25rem;
    }

    .summary-footer {
        flex-direction: column;
        align-items: flex-start;
    }

    .trend-up,
    .trend-down {
        margin-bottom: 0.25rem;
    }

    .card-body {
        padding: 0.75rem;
    }

    .payment-table,
    .work-table {
        max-height: 350px;
    }

    .pagination-container {
        padding: 0.75rem !important;
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .card {
        background-color: #2d3748;
        border-color: #4a5568;
    }

    .card-header {
        background-color: #2d3748 !important;
        border-color: #4a5568;
    }

    .table th {
        background-color: #2d3748;
    }

    .card-title,
    .summary-value {
        color: #f7fafc;
    }

    .dashboard-title {
        color: #f7fafc;
    }

    .processing-card,
    .pending-card,
    .received-card {
        background: #2d3748;
    }

    .table-light,
    .table-light td,
    .table-light th {
        background-color: #4a5568;
        color: #e2e8f0;
    }

    .table td {
        border-color: #4a5568;
    }

    .pagination .page-link {
        background-color: #2d3748;
        border-color: #4a5568;
        color: #e2e8f0;
    }

    .pagination .page-item.active .page-link {
        background-color: #4299e1;
        border-color: #4299e1;
        color: white;
    }

    .summary-card,
    .card {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
    }

    .trend-up {
        background-color: rgba(72, 187, 120, 0.2);
    }

    .trend-down {
        background-color: rgba(245, 101, 101, 0.2);
    }

    .trend-label {
        color: #cbd5e0;
    }
}

.year-selector {
    min-width: 100px;
}

.year-selector .form-select {
    padding-right: 2rem;
    background-position: right 0.5rem center;
    background-size: 16px 12px;
}
</style>
