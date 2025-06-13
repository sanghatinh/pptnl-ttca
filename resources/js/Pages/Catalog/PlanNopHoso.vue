<template>
    <div class="container-fluid py-4">
        <!-- Modern Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div
                    class="card modern-header shadow-lg border-0 overflow-hidden"
                >
                    <div class="header-background"></div>
                    <div class="card-body position-relative">
                        <div class="row align-items-center">
                            <div class="col-lg-8">
                                <div class="header-content">
                                    <div class="icon-wrapper mb-3">
                                        <img
                                            src="/public/img/Logo/TTC LOGO FFF.png"
                                            alt="TTC Logo"
                                            class="logo-img"
                                        />
                                    </div>
                                    <h1 class="header-title mb-3">
                                        Kế hoạch nộp hồ sơ
                                        <span class="badge bg-primary ms-2"
                                            >v1.0</span
                                        >
                                    </h1>
                                    <p class="header-subtitle mb-4">
                                        <i class="bi bi-gear-fill me-2"></i>
                                        Quản lý lịch trình nộp và xử lý hồ sơ
                                        thanh toán một cách thông minh
                                    </p>
                                    <div class="header-stats">
                                        <div class="stat-item">
                                            <span class="stat-number">{{
                                                totalActiveDays
                                            }}</span>
                                            <span class="stat-label"
                                                >Ngày hoạt động</span
                                            >
                                        </div>
                                        <div class="stat-item">
                                            <span class="stat-number"
                                                >{{
                                                    currentWeekProgress
                                                }}%</span
                                            >
                                            <span class="stat-label"
                                                >Tiến độ tuần</span
                                            >
                                        </div>
                                        <div class="stat-item">
                                            <span class="stat-number">4</span>
                                            <span class="stat-label"
                                                >Quy trình</span
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="header-visual">
                                    <div class="floating-card">
                                        <div class="card-mini bg-success">
                                            <i
                                                class="bi bi-file-earmark-check"
                                            ></i>
                                            <span>Nộp hồ sơ</span>
                                        </div>
                                        <div class="card-mini bg-warning">
                                            <i class="bi bi-pen-fill"></i>
                                            <span>Ký duyệt</span>
                                        </div>
                                        <div class="card-mini bg-info">
                                            <i
                                                class="bi bi-cloud-upload-fill"
                                            ></i>
                                            <span>Hệ thống</span>
                                        </div>
                                        <div class="card-mini bg-primary">
                                            <i class="bi bi-bank2"></i>
                                            <span>Chuyển tiền</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Week Selector -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Chọn năm:</label>
                                <select
                                    class="form-select"
                                    v-model="selectedYear"
                                    @change="updateWeeks"
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
                            <div class="col-md-3">
                                <label class="form-label">Chọn tháng:</label>
                                <select
                                    class="form-select"
                                    v-model="selectedMonth"
                                    @change="updateWeeks"
                                >
                                    <option
                                        v-for="(month, index) in months"
                                        :key="index"
                                        :value="index"
                                    >
                                        {{ month }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Chọn tuần:</label>
                                <select
                                    class="form-select"
                                    v-model="selectedWeekIndex"
                                    @change="updateCurrentWeek"
                                >
                                    <option
                                        v-for="(week, index) in availableWeeks"
                                        :key="index"
                                        :value="index"
                                    >
                                        Tuần {{ index + 1 }}:
                                        {{ formatWeekRange(week.start) }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex gap-2">
                                    <button
                                        class="btn btn-outline-primary flex-fill"
                                        @click="goToCurrentWeek"
                                    >
                                        <i class="fas fa-calendar-day me-2"></i>
                                        Tuần hiện tại
                                    </button>
                                    <button
                                        class="btn btn-outline-secondary"
                                        @click="
                                            showControlButtons =
                                                !showControlButtons
                                        "
                                        :title="
                                            showControlButtons
                                                ? 'Ẩn panel điều khiển'
                                                : 'Hiện panel điều khiển'
                                        "
                                    >
                                        <i
                                            :class="
                                                showControlButtons
                                                    ? 'fas fa-eye-slash'
                                                    : 'fas fa-eye'
                                            "
                                        ></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Control Panel -->
        <div class="row mb-4" v-show="showControlButtons">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="control-buttons-container">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <button
                                        class="btn btn-success w-100"
                                        @click="openEditModal('normal')"
                                    >
                                        <i
                                            class="bi bi-file-earmark-text me-2"
                                        ></i>
                                        Cấu hình Nộp hồ sơ
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button
                                        class="btn btn-warning w-100"
                                        @click="openEditModal('sign')"
                                    >
                                        <i class="bi bi-pen me-2"></i>
                                        Cấu hình Ký trưởng phòng
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button
                                        class="btn btn-info w-100"
                                        @click="openEditModal('system')"
                                    >
                                        <i class="bi bi-cloud-upload me-2"></i>
                                        Cấu hình đưa Trình hệ thống EO
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button
                                        class="btn btn-primary w-100"
                                        @click="openEditModal('transfer')"
                                    >
                                        <i class="bi bi-bank me-2"></i>
                                        Cấu hình chuyển tiền
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Weekly Calendar Grid -->
        <div class="row">
            <!-- Week 1 -->
            <div class="col-12 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-gradient bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-calendar2-week me-2"></i>
                            Tuần 1: {{ formatWeekRange(currentWeek) }}
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center">Thứ 2</th>
                                        <th class="text-center">Thứ 3</th>
                                        <th class="text-center">Thứ 4</th>
                                        <th class="text-center">Thứ 5</th>
                                        <th class="text-center">Thứ 6</th>
                                        <th class="text-center">Thứ 7</th>
                                        <th class="text-center">Chủ nhật</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td
                                            v-for="(day, index) in week1Days"
                                            :key="index"
                                            :class="getDayClass(day, 1)"
                                            class="text-center p-3 position-relative"
                                        >
                                            <div class="fw-bold mb-2">
                                                {{ day.date }}
                                            </div>
                                            <div class="activity-tags">
                                                <span
                                                    v-for="activity in day.activities"
                                                    :key="activity.type"
                                                    :class="
                                                        getActivityClass(
                                                            activity.type
                                                        )
                                                    "
                                                    class="badge mb-1 d-block"
                                                >
                                                    {{ activity.label }}
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Week 2 -->
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div
                        class="card-header bg-gradient bg-secondary text-white"
                    >
                        <h5 class="mb-0">
                            <i class="bi bi-calendar2-week me-2"></i>
                            Tuần 2: {{ formatWeekRange(nextWeek) }}
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center">Thứ 2</th>
                                        <th class="text-center">Thứ 3</th>
                                        <th class="text-center">Thứ 4</th>
                                        <th class="text-center">Thứ 5</th>
                                        <th class="text-center">Thứ 6</th>
                                        <th class="text-center">Thứ 7</th>
                                        <th class="text-center">Chủ nhật</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td
                                            v-for="(day, index) in week2Days"
                                            :key="index"
                                            :class="getDayClass(day, 2)"
                                            class="text-center p-3 position-relative"
                                        >
                                            <div class="fw-bold mb-2">
                                                {{ day.date }}
                                            </div>
                                            <div class="activity-tags">
                                                <span
                                                    v-for="activity in day.activities"
                                                    :key="activity.type"
                                                    :class="
                                                        getActivityClass(
                                                            activity.type
                                                        )
                                                    "
                                                    class="badge mb-1 d-block"
                                                >
                                                    {{ activity.label }}
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Legend -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="card-title">Chú thích:</h6>
                        <div class="row g-2">
                            <div class="col-md-3">
                                <span class="badge bg-success me-2"
                                    >Nộp hồ sơ</span
                                >
                            </div>
                            <div class="col-md-3">
                                <span class="badge bg-warning me-2"
                                    >Ký trưởng phòng</span
                                >
                            </div>
                            <div class="col-md-3">
                                <span class="badge bg-info me-2"
                                    >Đưa Trình hệ thống EO</span
                                >
                            </div>
                            <div class="col-md-3">
                                <span class="badge bg-primary me-2"
                                    >Chuyển tiền</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div
            class="modal fade"
            id="editModal"
            tabindex="-1"
            aria-labelledby="editModalLabel"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">
                            {{ modalTitle }}
                        </h5>
                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"
                        ></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="saveConfiguration">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label"
                                        >Chọn các ngày trong tuần:</label
                                    >
                                </div>
                                <div
                                    class="col-md-6"
                                    v-for="(day, index) in weekDays"
                                    :key="index"
                                >
                                    <div class="form-check">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            :id="`day-${index}`"
                                            v-model="selectedDays[index]"
                                        />
                                        <label
                                            class="form-check-label"
                                            :for="`day-${index}`"
                                        >
                                            {{ day }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal"
                        >
                            Hủy
                        </button>
                        <button
                            type="button"
                            class="btn btn-primary"
                            @click="saveConfiguration"
                        >
                            Lưu thay đổi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed, onMounted, watch } from "vue";
import { Modal } from "bootstrap";

export default {
    name: "PlanNopHoso",
    setup() {
        const currentWeek = ref(new Date());
        const nextWeek = ref(new Date());
        const selectedDays = ref([
            false,
            false,
            false,
            false,
            false,
            false,
            false,
        ]);
        const currentEditType = ref("");
        const editModal = ref(null);
        const showControlButtons = ref(false); // เพิ่มบรรทัดนี้
        // Week selector variables
        const selectedYear = ref(new Date().getFullYear());
        const selectedMonth = ref(new Date().getMonth());
        const selectedWeekIndex = ref(0);
        const availableWeeks = ref([]);

        const weekDays = [
            "Thứ 2",
            "Thứ 3",
            "Thứ 4",
            "Thứ 5",
            "Thứ 6",
            "Thứ 7",
            "Chủ nhật",
        ];

        const months = [
            "Tháng 1",
            "Tháng 2",
            "Tháng 3",
            "Tháng 4",
            "Tháng 5",
            "Tháng 6",
            "Tháng 7",
            "Tháng 8",
            "Tháng 9",
            "Tháng 10",
            "Tháng 11",
            "Tháng 12",
        ];

        const availableYears = computed(() => {
            const currentYear = new Date().getFullYear();
            const years = [];
            for (let i = currentYear - 2; i <= currentYear + 2; i++) {
                years.push(i);
            }
            return years;
        });

        // Default configuration
        const configuration = ref({
            normal: [true, true, true, true, false, false, false], // Mon-Thu
            sign: [false, false, false, false, true, true, false], // Fri-Sat
            system: [true, false, false, false, false, false, false], // Monday next week
            transfer: [false, false, false, false, true, false, false], // Friday
        });

        const modalTitle = computed(() => {
            const titles = {
                normal: "Cấu hình ngày Nộp hồ sơ",
                sign: "Cấu hình ngày Ký trưởng phòng ",
                system: "Cấu hình ngày đưa Trình hệ thống EO",
                transfer: "Cấu hình ngày chuyển tiền",
            };
            return titles[currentEditType.value] || "";
        });

        const week1Days = computed(() => {
            return generateWeekDays(currentWeek.value, 1);
        });

        const week2Days = computed(() => {
            return generateWeekDays(nextWeek.value, 2);
        });

        function getWeeksInMonth(year, month) {
            const weeks = [];
            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);

            // Find first Monday of the month (or previous Monday if month doesn't start on Monday)
            let currentMonday = new Date(firstDay);
            currentMonday.setDate(firstDay.getDate() - firstDay.getDay() + 1);

            // If the Monday is in the previous month and it's more than 3 days before the 1st, skip it
            if (currentMonday.getMonth() !== month && firstDay.getDate() > 4) {
                currentMonday.setDate(currentMonday.getDate() + 7);
            }

            while (
                currentMonday <= lastDay ||
                currentMonday.getMonth() === month
            ) {
                const weekEnd = new Date(currentMonday);
                weekEnd.setDate(currentMonday.getDate() + 6);

                weeks.push({
                    start: new Date(currentMonday),
                    end: weekEnd,
                });

                currentMonday.setDate(currentMonday.getDate() + 7);

                // Stop if we've gone too far into the next month
                if (
                    currentMonday.getMonth() > month &&
                    currentMonday.getDate() > 7
                ) {
                    break;
                }
            }

            return weeks;
        }

        function updateWeeks() {
            availableWeeks.value = getWeeksInMonth(
                selectedYear.value,
                selectedMonth.value
            );
            selectedWeekIndex.value = 0;
            updateCurrentWeek();
        }

        function updateCurrentWeek() {
            if (availableWeeks.value.length > selectedWeekIndex.value) {
                currentWeek.value = new Date(
                    availableWeeks.value[selectedWeekIndex.value].start
                );
                nextWeek.value = new Date(currentWeek.value);
                nextWeek.value.setDate(currentWeek.value.getDate() + 7);
            }
        }

        function goToCurrentWeek() {
            const now = new Date();
            selectedYear.value = now.getFullYear();
            selectedMonth.value = now.getMonth();
            updateWeeks();

            // Find the week that contains today
            const today = new Date();
            for (let i = 0; i < availableWeeks.value.length; i++) {
                const week = availableWeeks.value[i];
                if (today >= week.start && today <= week.end) {
                    selectedWeekIndex.value = i;
                    break;
                }
            }
            updateCurrentWeek();
        }

        function generateWeekDays(startDate, weekNumber) {
            const days = [];
            const monday = new Date(startDate);
            monday.setDate(monday.getDate() - monday.getDay() + 1);

            for (let i = 0; i < 7; i++) {
                const day = new Date(monday);
                day.setDate(monday.getDate() + i);

                const activities = [];

                // For Week 1: Only normal and sign activities
                if (weekNumber === 1) {
                    if (configuration.value.normal[i]) {
                        activities.push({ type: "normal", label: "Nộp hồ sơ" });
                    }
                    if (configuration.value.sign[i]) {
                        activities.push({
                            type: "sign",
                            label: "Ký trưởng phòng",
                        });
                    }
                }
                // For Week 2: Only system and transfer activities
                else if (weekNumber === 2) {
                    if (configuration.value.system[i]) {
                        activities.push({
                            type: "system",
                            label: "Trình hệ thống EO",
                        });
                    }
                    if (configuration.value.transfer[i]) {
                        activities.push({
                            type: "transfer",
                            label: "Chuyển tiền",
                        });
                    }
                }

                days.push({
                    date: day.getDate(),
                    fullDate: day,
                    activities: activities,
                    isWeekend: i === 6,
                    dayOfWeek: i,
                });
            }
            return days;
        }

        function getDayClass(day, weekNumber) {
            const classes = ["calendar-day"];

            if (day.isWeekend) {
                classes.push("weekend-day");
            }

            if (day.activities.length === 0) {
                classes.push("rest-day");
            }

            return classes.join(" ");
        }

        function getActivityClass(type) {
            const classes = {
                normal: "bg-success",
                sign: "bg-warning",
                system: "bg-info",
                transfer: "bg-primary",
            };
            return classes[type] || "bg-secondary";
        }

        function formatWeekRange(date) {
            const monday = new Date(date);
            monday.setDate(monday.getDate() - monday.getDay() + 1);

            const sunday = new Date(monday);
            sunday.setDate(monday.getDate() + 6);

            return `${monday.getDate()}/${
                monday.getMonth() + 1
            } - ${sunday.getDate()}/${sunday.getMonth() + 1}`;
        }

        function openEditModal(type) {
            currentEditType.value = type;
            selectedDays.value = [...configuration.value[type]];
            editModal.value.show();
        }

        function saveConfiguration() {
            configuration.value[currentEditType.value] = [
                ...selectedDays.value,
            ];
            editModal.value.hide();
        }

        // New computed properties for header stats
        const totalActiveDays = computed(() => {
            const week1 = week1Days.value.filter(
                (day) => day.activities.length > 0
            ).length;
            const week2 = week2Days.value.filter(
                (day) => day.activities.length > 0
            ).length;
            return week1 + week2;
        });

        const currentWeekProgress = computed(() => {
            const today = new Date();
            const weekStart = new Date(currentWeek.value);
            weekStart.setDate(weekStart.getDate() - weekStart.getDay() + 1);
            const weekEnd = new Date(weekStart);
            weekEnd.setDate(weekStart.getDate() + 6);

            if (today < weekStart) return 0;
            if (today > weekEnd) return 100;

            const daysInWeek = 7;
            const daysPassed =
                Math.floor((today - weekStart) / (1000 * 60 * 60 * 24)) + 1;
            return Math.round((daysPassed / daysInWeek) * 100);
        });

        onMounted(() => {
            // Initialize modal
            editModal.value = new Modal(document.getElementById("editModal"));

            // Initialize with current week
            goToCurrentWeek();
        });

        return {
            currentWeek,
            nextWeek,
            week1Days,
            week2Days,
            weekDays,
            selectedDays,
            modalTitle,
            selectedYear,
            selectedMonth,
            selectedWeekIndex,
            availableWeeks,
            availableYears,
            months,
            getDayClass,
            getActivityClass,
            formatWeekRange,
            openEditModal,
            saveConfiguration,
            updateWeeks,
            updateCurrentWeek,
            goToCurrentWeek,
            totalActiveDays,
            currentWeekProgress,
            showControlButtons, // เพิ่มบรรทัดนี้
        };
    },
};
</script>

<style scoped>
/* Modern Header Styles */
.modern-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px !important;
    min-height: 180px;
    position: relative;
}

.header-background {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(
            circle at 20% 50%,
            rgba(255, 255, 255, 0.1) 0%,
            transparent 50%
        ),
        radial-gradient(
            circle at 80% 20%,
            rgba(255, 255, 255, 0.1) 0%,
            transparent 50%
        ),
        radial-gradient(
            circle at 40% 80%,
            rgba(255, 255, 255, 0.1) 0%,
            transparent 50%
        );
    border-radius: 20px;
}

.header-content {
    color: white;
    z-index: 2;
    position: relative;
}

.icon-wrapper {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 54px;
    height: 54px;
    border-radius: 12px;
    padding: 6px;
}

.logo-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    filter: brightness(1.2) contrast(1.1);
    transition: all 0.3s ease;
}

.icon-wrapper:hover .logo-img {
    transform: scale(1.1);
    filter: brightness(1.3) contrast(1.2);
}

.icon-wrapper i {
    font-size: 2rem;
    color: white;
}

.header-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

.header-title .badge {
    font-size: 0.5rem;
    padding: 0.3em 0.5em;
    border-radius: 50px;
    background: rgba(255, 255, 255, 0.2) !important;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.header-subtitle {
    font-size: 0.66rem;
    opacity: 0.9;
    font-weight: 400;
    line-height: 1.6;
}

.header-stats {
    display: flex;
    gap: 1.2rem;
    flex-wrap: wrap;
}

.stat-item {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}

.stat-number {
    font-size: 1.2rem;
    font-weight: 700;
    color: white;
    line-height: 1;
}

.stat-label {
    font-size: 0.54rem;
    opacity: 0.8;
    margin-top: 0.15rem;
}

.header-visual {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100%;
}

.floating-card {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    animation: float 6s ease-in-out infinite;
}

.card-mini {
    padding: 0.9rem 0.6rem;
    border-radius: 9px;
    color: white;
    text-align: center;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.3s ease;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.card-mini:hover {
    transform: translateY(-5px) scale(1.05);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
}

.card-mini i {
    font-size: 0.9rem;
    display: block;
    margin-bottom: 0.3rem;
}

.card-mini span {
    font-size: 0.48rem;
    font-weight: 600;
    display: block;
}

@keyframes float {
    0%,
    100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-10px);
    }
}

/* Responsive adjustments */
@media (max-width: 992px) {
    .header-title {
        font-size: 1.2rem;
    }

    .floating-card {
        grid-template-columns: 1fr;
        gap: 0.3rem;
    }

    .card-mini {
        padding: 0.6rem 0.48rem;
    }

    .header-stats {
        gap: 0.6rem;
    }

    .stat-number {
        font-size: 0.9rem;
    }
}

@media (max-width: 768px) {
    .modern-header {
        min-height: 150px;
    }

    .header-title {
        font-size: 1.08rem;
    }

    .icon-wrapper {
        width: 36px;
        height: 36px;
        padding: 5px;
    }

    .header-stats {
        flex-direction: row;
        justify-content: space-between;
    }
}

/* Existing styles... */
.calendar-day {
    min-height: 100px;
    transition: all 0.3s ease;
}

.calendar-day:hover {
    background-color: #f8f9fa !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.weekend-day {
    background-color: #fff3cd;
}

.rest-day {
    background-color: #f8d7da;
    color: #721c24;
}

.activity-tags {
    font-size: 0.75rem;
}

.badge {
    font-size: 0.7rem;
    padding: 0.25em 0.5em;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.8rem;
    }

    .calendar-day {
        min-height: 80px;
    }

    .activity-tags .badge {
        font-size: 0.6rem;
        padding: 0.2em 0.4em;
    }
}

.card {
    border-radius: 15px;
    overflow: hidden;
}

.bg-gradient {
    background: linear-gradient(
        135deg,
        var(--bs-primary),
        var(--bs-primary-dark)
    );
}

.table th {
    border-top: none;
    font-weight: 600;
}

.btn {
    border-radius: 10px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
/* Animation for control buttons */
.control-buttons-container {
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Button hover effects */
.btn-outline-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
}
/* Button hover effects */
.btn-outline-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
}

.btn-outline-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
}

/* Animation for control panel */
.control-buttons-container {
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
/* Button hover effects */
.btn-outline-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
}
</style>
