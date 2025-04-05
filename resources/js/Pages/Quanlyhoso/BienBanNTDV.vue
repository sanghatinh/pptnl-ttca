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
                <div class="investment-filter ms-3">
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
                </div>
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
                        placeholder="Tìm kiếm biên bản..."
                        class="w-full pl-10 pr-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm"
                    />
                </div>
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
        </div>

        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <!-- Desktop view table -->
            <div v-if="!isMobile" class="card">
                <div class="card-body">
                    <div class="overflow-x-auto">
                        <table class="table-auto w-full">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2">Mã nghiệm thu</th>
                                    <th class="px-4 py-2">Trạm</th>
                                    <th class="px-4 py-2">Vụ đầu tư</th>
                                    <th class="px-4 py-2">Tiêu đề</th>
                                    <th class="px-4 py-2">
                                        Khách hàng cá nhân
                                    </th>
                                    <th class="px-4 py-2">
                                        Khách hàng doanh nghiệp
                                    </th>
                                    <th class="px-4 py-2">
                                        Hợp đồng đầu tư mía
                                    </th>
                                    <th class="px-4 py-2">
                                        Hình thức thực hiện DV
                                    </th>
                                    <th class="px-4 py-2">
                                        Hợp đồng cung ứng dịch vụ
                                    </th>
                                    <th class="px-4 py-2">Tổng tiền dịch vụ</th>
                                    <th class="px-4 py-2">Tổng tiền tạm giữ</th>
                                    <th class="px-4 py-2">
                                        Tổng tiền thanh toán
                                    </th>
                                    <!-- Add new columns -->
                                    <th class="px-4 py-2">Người giao</th>
                                    <th class="px-4 py-2">Người nhận</th>
                                    <th class="px-4 py-2">Ngày nhận</th>
                                    <th class="px-4 py-2">
                                        Trạng thái nhận HS
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="item in paginatedItems.data"
                                    :key="item.id"
                                    class="desktop-row"
                                    @click="viewDetails(item)"
                                >
                                    <td class="border px-4 py-2">
                                        {{ item.ma_nghiem_thu }}
                                    </td>
                                    <td class="border px-4 py-2">
                                        {{ item.tram }}
                                    </td>
                                    <td class="border px-4 py-2">
                                        {{ item.vu_dau_tu }}
                                    </td>
                                    <td class="border px-4 py-2">
                                        {{ item.tieu_de }}
                                    </td>
                                    <td class="border px-4 py-2">
                                        {{ item.khach_hang_ca_nhan_dt_mia }}
                                    </td>
                                    <td class="border px-4 py-2">
                                        {{
                                            item.khach_hang_doanh_nghiep_dt_mia
                                        }}
                                    </td>
                                    <td class="border px-4 py-2">
                                        {{ item.hop_dong_dau_tu_mia }}
                                    </td>
                                    <td class="border px-4 py-2">
                                        {{ item.hinh_thuc_thuc_hien_dv }}
                                    </td>
                                    <td class="border px-4 py-2">
                                        {{ item.hop_dong_cung_ung_dich_vu }}
                                    </td>
                                    <td class="border px-4 py-2">
                                        {{
                                            formatCurrency(
                                                item.tong_tien_dich_vu
                                            )
                                        }}
                                    </td>
                                    <td class="border px-4 py-2">
                                        {{
                                            formatCurrency(
                                                item.tong_tien_tam_giu
                                            )
                                        }}
                                    </td>
                                    <td class="border px-4 py-2">
                                        {{
                                            formatCurrency(
                                                item.tong_tien_thanh_toan
                                            )
                                        }}
                                    </td>
                                    <!-- New columns -->
                                    <td class="border px-4 py-2">
                                        <template v-if="item.nguoi_giao">
                                            <i
                                                class="fas fa-user text-blue-500"
                                            ></i>
                                            {{ item.nguoi_giao }}
                                        </template>
                                    </td>
                                    <td class="border px-4 py-2">
                                        <template v-if="item.nguoi_nhan">
                                            <i
                                                class="fas fa-user text-green-500"
                                            ></i>
                                            {{ item.nguoi_nhan }}
                                        </template>
                                    </td>
                                    <td class="border px-4 py-2">
                                        {{ formatDate(item.ngay_nhan) }}
                                    </td>
                                    <td class="border px-4 py-2">
                                        <span
                                            v-if="item.trang_thai_nhan_hs"
                                            :class="
                                                statusClass(
                                                    item.trang_thai_nhan_hs
                                                )
                                            "
                                            class="flex items-center"
                                        >
                                            <i
                                                :class="
                                                    statusIcons(
                                                        item.trang_thai_nhan_hs
                                                    )
                                                "
                                                class="mr-1"
                                            ></i>
                                            {{
                                                formatStatus(
                                                    item.trang_thai_nhan_hs
                                                )
                                            }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- pagination -->
                    <div class="flex justify-center mt-4">
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

        <!-- Mobile view cards -->
        <div v-if="isMobile" class="card-container">
            <div
                v-for="item in paginatedItems.data"
                :key="item.id"
                class="card border p-4 mb-4 rounded shadow hover:shadow-green-500 transition-shadow duration-300"
                @click="viewDetails(item)"
            >
                <div class="flex-1 justify-items-start">
                    <div class="mb-2">
                        <strong>Mã nghiệm thu:</strong>
                        {{ item.ma_nghiem_thu }}
                    </div>
                    <div class="mb-2">
                        <strong>Trạm:</strong>
                        {{ item.tram }}
                    </div>
                    <div class="mb-2">
                        <strong>Vụ đầu tư:</strong>
                        {{ item.vu_dau_tu }}
                    </div>
                    <div class="mb-2">
                        <strong>Tiêu đề:</strong>
                        {{ item.tieu_de }}
                    </div>
                    <div class="mb-2">
                        <strong>Hợp đồng đầu tư mía:</strong>
                        {{ item.hop_dong_dau_tu_mia }}
                    </div>
                    <div class="mb-2">
                        <strong>Hình thức thực hiện DV:</strong>
                        {{ item.hinh_thuc_thuc_hien_dv }}
                    </div>
                    <div class="mb-2">
                        <strong>Hợp đồng cung ứng dịch vụ:</strong>
                        {{ item.hop_dong_cung_ung_dich_vu }}
                    </div>
                    <div class="mb-2">
                        <strong>Tổng tiền dịch vụ:</strong>
                        {{ formatCurrency(item.tong_tien_dich_vu) }}
                    </div>
                </div>
            </div>
            <!-- pagination -->
            <div class="flex justify-center mt-4">
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
</template>

<script>
import axios from "axios";
import { useStore } from "../../Store/Auth";
import { ref, computed, onMounted } from "vue";
import { Bootstrap5Pagination } from "laravel-vue-pagination"; // Add this import

export default {
    components: {
        Bootstrap5Pagination, // Register the component
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
            bienBanList: [],
            allBienBanList: [], // เพิ่มสำหรับเก็บข้อมูลทั้งหมด
            search: "",
            statusFilter: "all",
            investmentFilter: "all",
            investmentProjects: [],
            isMobile: window.innerWidth < 768,
            currentPage: 1, // เพิ่มสำหรับการจัดการหน้า
            perPage: 10, // จำนวนรายการต่อหน้า
            paginatedItems: {
                data: [],
            },
            statusOptions: [
                { code: "all", name: "Tất cả trạng thái" },
                { code: "approved", name: "Đã duyệt" },
                { code: "pending", name: "Chờ duyệt" },
                { code: "rejected", name: "Từ chối" },
            ],
            paginationClasses: {
                ul: "flex list-none pagination",
                li: "page-item mx-1",
                a: "page-link px-3 py-2 bg-white border border-gray-300 rounded hover:bg-gray-100",
                active: "bg-green-500 text-white active",
                disabled: "opacity-50 cursor-not-allowed disabled",
            },
        };
    },
    computed: {
        statusCounts() {
            const counts = {
                all: this.bienBanList.length,
                approved: 0,
                pending: 0,
                rejected: 0,
            };

            this.bienBanList.forEach((item) => {
                if (item.tinh_trang_duyet === 1) {
                    counts.approved++;
                } else if (item.tinh_trang_duyet === 0) {
                    counts.pending++;
                } else if (item.tinh_trang_duyet === 2) {
                    counts.rejected++;
                }
            });

            return counts;
        },
        filteredItems() {
            return this.bienBanList.filter((item) => {
                // Filter by search query
                const matchesSearch =
                    item.ma_nghiem_thu
                        ?.toLowerCase()
                        .includes(this.search.toLowerCase()) ||
                    item.tieu_de
                        ?.toLowerCase()
                        .includes(this.search.toLowerCase()) ||
                    item.hop_dong_dau_tu_mia
                        ?.toLowerCase()
                        .includes(this.search.toLowerCase()) ||
                    item.tram
                        ?.toLowerCase()
                        .includes(this.search.toLowerCase());

                // Filter by status
                let matchesStatus = true;
                if (this.statusFilter !== "all") {
                    if (this.statusFilter === "approved") {
                        matchesStatus = item.tinh_trang_duyet === 1;
                    } else if (this.statusFilter === "pending") {
                        matchesStatus = item.tinh_trang_duyet === 0;
                    } else if (this.statusFilter === "rejected") {
                        matchesStatus = item.tinh_trang_duyet === 2;
                    }
                }

                // Filter by investment project
                let matchesInvestment = true;
                if (this.investmentFilter !== "all") {
                    matchesInvestment =
                        item.vu_dau_tu === this.investmentFilter;
                }

                return matchesSearch && matchesStatus && matchesInvestment;
            });
        },
        paginatedItems() {
            // Implement client-side pagination
            const page = this.currentPage || 1;
            const perPage = this.perPage || 10;
            const total = this.filteredItems.length;
            const lastPage = Math.ceil(total / perPage);
            const from = (page - 1) * perPage + 1;
            const to = Math.min(from + perPage - 1, total);

            const items = this.filteredItems.slice(
                (page - 1) * perPage,
                page * perPage
            );

            return {
                current_page: page,
                data: items,
                from: from,
                last_page: lastPage,
                per_page: perPage,
                to: to,
                total: total,
                prev_page_url: page > 1 ? `?page=${page - 1}` : null,
                next_page_url: page < lastPage ? `?page=${page + 1}` : null,
            };
        },
    },
    methods: {
        formatDate(date) {
            if (!date) return "";
            return new Date(date).toLocaleDateString("vi-VN");
        },

        formatStatus(status) {
            if (!status) return "";

            const statusMap = {
                creating: "Đang tạo",
                sending: "Đang gửi",
                received: "Đã nhận",
                cancelled: "Đã hủy",
            };

            return statusMap[status] || status;
        },
        statusClass(status) {
            if (!status) return "";

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

        statusIcons(status) {
            if (!status) return "";

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
        statusIcon(status) {
            switch (status) {
                case "approved":
                    return "fas fa-check-circle text-success";
                case "pending":
                    return "fas fa-clock text-warning";
                case "rejected":
                    return "fas fa-times-circle text-danger";
                default:
                    return "";
            }
        },
        formatCurrency(value) {
            if (!value) return "0";
            return new Intl.NumberFormat("vi-VN", {
                style: "currency",
                currency: "VND",
                maximumFractionDigits: 0,
            }).format(value);
        },
        pageChanged(page) {
            this.currentPage = page;
        },
        checkScreenSize() {
            this.isMobile = window.innerWidth < 768;
        },
        async fetchBienBanData(page = 1) {
            this.isLoading = true;
            try {
                // โหลดข้อมูลทั้งหมดครั้งเดียว
                const response = await axios.get("/api/bien-ban-nghiem-thu", {
                    params: {
                        per_page: 1000, // โหลดทั้งหมดหรือจำนวนมากๆ
                    },
                    headers: {
                        Authorization: "Bearer " + this.store.getToken,
                    },
                });

                this.allBienBanList = response.data.data;
                this.bienBanList = this.allBienBanList;

                // Set current page
                this.currentPage = page;

                // ดึงรายการโครงการลงทุนที่มีอยู่ทั้งหมด
                this.extractInvestmentProjects();
            } catch (error) {
                console.error("Error fetching biên bản:", error);

                if (error.response?.status === 401) {
                    this.handleAuthError();
                } else {
                    alert(
                        "Có lỗi xảy ra khi tải dữ liệu. Vui lòng thử lại sau."
                    );
                }
            } finally {
                this.isLoading = false;
            }
        },
        extractInvestmentProjects() {
            // Get unique investment projects
            this.investmentProjects = [
                ...new Set(this.bienBanList.map((item) => item.vu_dau_tu)),
            ].filter(Boolean);
        },
        viewDetails(item) {
            // You can implement a detail view here
            // For example:
            // this.$router.push(`/bien-ban-nghiem-thu/${item.ma_nghiem_thu}`);

            // Or show a modal with details
            console.log("Viewing details for:", item);
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
    },
    watch: {
        search() {
            this.currentPage = 1; // รีเซ็ตกลับไปหน้าแรก
        },
        statusFilter() {
            this.currentPage = 1; // รีเซ็ตกลับไปหน้าแรก
        },
        investmentFilter() {
            this.currentPage = 1; // รีเซ็ตกลับไปหน้าแรก
        },
    },
    mounted() {
        this.fetchBienBanData();
        window.addEventListener("resize", this.checkScreenSize);
    },
    beforeUnmount() {
        window.removeEventListener("resize", this.checkScreenSize);
    },
};
</script>

<style scoped>
/* Styling */
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
.table-auto th,
.table-auto td {
    text-align: left;
    white-space: nowrap;
    overflow: visible;
    text-overflow: clip;
    word-wrap: break-word;
    font-size: 0.875rem;
}
.table-auto th {
    background-color: #e7e7e7;
    border: 1px solid #e5e7eb;
    padding: 0.75rem;
    vertical-align: top;
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
/* จัดการการแสดงผลของไอคอนร่วมกับข้อความ */
.flex.items-center {
    display: flex;
    align-items: center;
}
.mr-1 {
    margin-right: 0.25rem;
}
</style>
