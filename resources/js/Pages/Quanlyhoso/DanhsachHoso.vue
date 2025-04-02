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
                            {{ option.name }}
                        </option>
                    </select>
                </div>
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

        <div class="flex justify-center mb-2" v-if="isMobile">
            <div class="col-8 me-2">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search..."
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500"
                />
            </div>
            <div class="col-4">
                <router-link to="/taonewhoso">
                    <button
                        type="button"
                        class="button-30-save"
                        style="height: 38px"
                    >
                        <i class="fa-solid fa-plus"></i>Tạo mới
                    </button>
                </router-link>
            </div>
        </div>

        <!-- สำหรับ Mobile view: เพิ่ม scope indicator -->
        <div class="data-scope-indicator mb-2" v-if="isMobile"></div>

        <div class="status-filter" v-if="isMobile">
            <select v-model="statusFilter" class="form-select status-select">
                <option
                    v-for="option in statusOptions"
                    :key="option.code"
                    :value="option.code"
                    class="status-option"
                >
                    <span v-if="option.code !== 'all'" class="status-icon">
                        <i :class="statusIcon(option.code)"></i>
                    </span>
                    {{ option.name }}
                </option>
            </select>
        </div>

        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <!-- สำหรับ Desktop -->
            <div v-if="!isMobile" class="card">
                <div class="card-body">
                    <div class="overflow-x-auto">
                        <table class="table-auto w-full">
                            <thead>
                                <tr>
                                    <!-- New checkbox column header -->
                                    <th class="px-4 py-2">
                                        <input
                                            type="checkbox"
                                            @click="toggleSelectAll($event)"
                                            :checked="isAllSelected"
                                            class="form-checkbox h-4 w-4 text-green-600"
                                        />
                                    </th>
                                    <th class="px-4 py-2">Mã số phiếu</th>
                                    <th class="px-4 py-2">Trạm</th>

                                    <th class="px-4 py-2">Tiều đề</th>
                                    <th class="px-4 py-2">Vụ đầu tư</th>
                                    <th class="px-4 py-2">Số lượng hồ sơ</th>
                                    <th class="px-4 py-2">Loại hồ sơ</th>
                                    <th class="px-4 py-2">Ngày lập</th>
                                    <th class="px-4 py-2">Người tạo</th>
                                    <th class="px-4 py-2">Người nhận</th>
                                    <th class="px-4 py-2">Ngày nhận</th>
                                    <th class="px-4 py-2">Trạng thái</th>
                                    <th class="px-4 py-2">
                                        Trạng thái thanh toán
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
                                    <td class="border px-4 py-2">
                                        <input
                                            type="checkbox"
                                            v-model="selectedItems"
                                            :value="item.id"
                                            class="form-checkbox h-4 w-4 text-green-600"
                                        />
                                    </td>
                                    <td
                                        class="border px-4 py-2"
                                        @click="goToEditPage(item.id)"
                                    >
                                        {{ item.document_code }}
                                    </td>
                                    <td
                                        class="border px-4 py-2"
                                        @click="goToEditPage(item.id)"
                                    >
                                        {{ item.station }}
                                    </td>

                                    <td
                                        class="border px-4 py-2"
                                        @click="goToEditPage(item.id)"
                                    >
                                        {{ item.title }}
                                    </td>
                                    <td
                                        class="border px-4 py-2"
                                        @click="goToEditPage(item.id)"
                                    >
                                        {{ item.investment_project }}
                                    </td>
                                    <td
                                        class="border px-4 py-2"
                                        @click="goToEditPage(item.id)"
                                    >
                                        {{ item.file_count }}
                                    </td>
                                    <td
                                        class="border px-4 py-2"
                                        @click="goToEditPage(item.id)"
                                    >
                                        {{ item.document_type }}
                                    </td>
                                    <td class="border px-4 py-2">
                                        {{ formatDate(item.created_date) }}
                                    </td>
                                    <td class="border px-4 py-2">
                                        <i
                                            class="fas fa-user text-blue-500"
                                        ></i>
                                        {{ item.creator?.full_name || "N/A" }}
                                    </td>
                                    <td class="border px-4 py-2">
                                        <template v-if="item.receiver">
                                            <i
                                                class="fas fa-user text-green-500"
                                            ></i>
                                            {{ item.receiver.full_name }}
                                        </template>
                                        <template v-else>
                                            <span class="text-gray-400">-</span>
                                        </template>
                                    </td>
                                    <td class="border px-4 py-2">
                                        {{
                                            item.received_date
                                                ? formatDate(item.received_date)
                                                : "-"
                                        }}
                                    </td>
                                    <td class="border px-4 py-2">
                                        <span
                                            :class="
                                                statusClass(item.status.code)
                                            "
                                        >
                                            <i
                                                :class="
                                                    statusIcon(item.status.code)
                                                "
                                                class="mr-1"
                                            ></i>
                                            {{ item.status.name }}
                                        </span>
                                    </td>
                                    <td
                                        class="border px-4 py-2"
                                        @click="goToEditPage(item.id)"
                                    >
                                        {{ item.loan_status }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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
                            {{ item.document_code }}
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
            <!-- pagination -->
            <div class="flex justify-center mt-4">
                <div class="pagination-card">
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
</template>

<script>
import axios from "axios";
import { Bootstrap5Pagination } from "laravel-vue-pagination";
import { useStore } from "../../Store/Auth";
import Swal from "sweetalert2";

export default {
    setup() {
        const store = useStore();
        return {
            store,
        };
    },
    name: "DanhsachHoso",
    components: {
        Pagination: Bootstrap5Pagination,
    },
    data() {
        return {
            isLoading: false, // Add this line
            userRole: null,
            userStation: null,
            documentDeliveries: [],
            search: "",
            currentPage: 1,
            perPage: 10,
            isMobile: window.innerWidth < 768,
            selectedItems: [],
            paginationClasses: {
                ul: "flex list-none",
                li: "mx-1",
                a: "block px-3 py-2 bg-white border border-gray-300 rounded hover:bg-gray-100",
                active: "bg-green-500 text-white",
                disabled: "opacity-50 cursor-not-allowed",
            },

            statusFilter: "all",
            statusOptions: [
                { code: "all", name: "Tất cả trạng thái" },
                { code: "creating", name: "Nháp" },
                { code: "sending", name: "Đã nộp" },
                { code: "received", name: "Đã nhận" },
                { code: "cancelled", name: "Hủy" },
            ],
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
            return this.documentDeliveries.filter((item) => {
                return (
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
                            .includes(this.search.toLowerCase()))
                );
            });
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

        filteredDeliveries() {
            return this.documentDeliveries.filter((item) => {
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

                const matchesStatus =
                    this.statusFilter === "all" ||
                    item.status.code === this.statusFilter;

                return matchesSearch && matchesStatus;
            });
        },
    },
    methods: {
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
                const response = await axios.get("/api/document-deliveries", {
                    params: {
                        search: this.search,
                        status:
                            this.statusFilter !== "all"
                                ? this.statusFilter
                                : undefined,
                        per_page: 1000,
                        role: this.userRole,
                        station: this.userStation,
                    },
                    headers: {
                        Authorization: "Bearer " + this.store.getToken,
                    },
                });

                this.documentDeliveries = response.data.data;
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
                console.error("Error checking document access:", error);
                // Hide loading indicator
                this.isLoading = false;

                // Handle error cases
                if (error.response?.status === 401) {
                    // Session expired
                    localStorage.removeItem("web_token");
                    localStorage.removeItem("web_user");
                    this.store.logout();
                    this.$router.push("/login");
                } else {
                    // Other errors
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
                    // Navigate to Unauthorized page for safety
                    this.$router.push("/unauthorized");
                }
            }
        },
    },
    mounted() {
        this.fetchUserInfo(); // เพิ่มบรรทัดนี้
        this.fetchData();
        window.addEventListener("resize", this.handleResize);
    },
    beforeUnmount() {
        window.removeEventListener("resize", this.handleResize);
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
</style>
