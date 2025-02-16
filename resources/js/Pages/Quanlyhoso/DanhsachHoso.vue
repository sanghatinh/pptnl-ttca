<template>
    <div class="container mx-auto p-4">
        <div class="flex justify-end mb-4" v-if="!isMobile">
            <input
                v-model="search"
                type="text"
                placeholder="Search..."
                class="px-3 py-1 border rounded focus:outline-none focus:ring-2 focus:ring-green-500"
            />
        </div>
        <div class="flex justify-center mb-4" v-if="isMobile">
            <div class="col-12">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search..."
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500"
                />
            </div>
        </div>
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="card">
                <!-- สำหรับ Desktop -->
                <div v-if="!isMobile" class="overflow-x-auto">
                    <table class="table-auto w-full">
                        <thead>
                            <tr>
                                <th class="px-4 py-2">Mã số phiếu</th>
                                <th class="px-4 py-2">Trạm</th>
                                <th class="px-4 py-2">Ngày lập</th>
                                <th class="px-4 py-2">Tiều đề</th>
                                <th class="px-4 py-2">Vụ đầu tư</th>
                                <th class="px-4 py-2">Số lượng hồ sơ</th>
                                <th class="px-4 py-2">Loại hồ sơ</th>
                                <th class="px-4 py-2">Ngày lập</th>
                                <th class="px-4 py-2">Người tạo</th>
                                <th class="px-4 py-2">Người nhận</th>
                                <th class="px-4 py-2">Ngày nhận</th>
                                <th class="px-4 py-2">Trạng thái</th>
                                <th class="px-4 py-2">Trạng thái thanh toán</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="item in paginatedDeliveries.data"
                                :key="item.id"
                                class="desktop-row"
                            >
                                <td class="border px-4 py-2">
                                    {{ item.document_code }}
                                </td>
                                <td class="border px-4 py-2">
                                    {{ item.station }}
                                </td>
                                <td class="border px-4 py-2">
                                    {{ item.created_date }}
                                </td>
                                <td class="border px-4 py-2">
                                    {{ item.title }}
                                </td>
                                <td class="border px-4 py-2">
                                    {{ item.investment_project }}
                                </td>
                                <td class="border px-4 py-2">
                                    {{ item.file_count }}
                                </td>
                                <td class="border px-4 py-2">
                                    {{ item.document_type }}
                                </td>
                                <td class="border px-4 py-2">
                                    {{ item.created_date }}
                                </td>
                                <td class="border px-4 py-2">
                                    <i class="fas fa-user text-blue-500"></i>
                                    {{ item.creator?.full_name || "N/A" }}
                                </td>
                                <td class="border px-4 py-2">
                                    <i class="fas fa-user text-green-500"></i>
                                    {{ item.receiver?.full_name || "N/A" }}
                                </td>
                                <td class="border px-4 py-2">
                                    {{ item.received_date }}
                                </td>
                                <td class="border px-4 py-2">
                                    <span :class="statusClass(item.status)">
                                        <i
                                            :class="statusIcon(item.status)"
                                            class="mr-1"
                                        ></i>
                                        {{ item.status }}
                                    </span>
                                </td>
                                <td class="border px-4 py-2">
                                    {{ item.loan_status }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- สำหรับ Mobile: แสดงเป็นการ์ด -->
        <div v-if="isMobile" class="card-container">
            <div
                v-for="item in paginatedDeliveries.data"
                :key="item.id"
                class="card border p-4 mb-4 rounded shadow hover:shadow-green-500 transition-shadow duration-300"
            >
                <div class="mb-2">
                    <strong>Mã số phiếu:</strong> {{ item.document_code }}
                </div>
                <div class="mb-2">
                    <strong>Tiều đề:</strong> {{ item.title }}
                </div>
                <div class="mb-2">
                    <strong>Ngày lập:</strong> {{ item.created_date }}
                </div>
                <div class="mb-2">
                    <strong>Người tạo:</strong>
                    <i class="fas fa-user text-blue-500"></i>
                    {{ item.creator?.full_name || "N/A" }}
                </div>
                <div class="mb-2">
                    <strong>Người nhận:</strong>
                    <i class="fas fa-user text-green-500"></i>
                    {{ item.receiver?.full_name || "N/A" }}
                </div>
                <div>
                    <strong>Status:</strong>
                    <span :class="statusClass(item.status)">
                        <i :class="statusIcon(item.status)" class="mr-1"></i>
                        {{ item.status }}
                    </span>
                </div>
            </div>
        </div>

        <div class="flex justify-center mt-4">
            <pagination
                :data="paginatedDeliveries"
                @pagination-change-page="pageChanged"
                :limit="5"
                :classes="paginationClasses"
            />
        </div>
    </div>
</template>

<script>
import axios from "axios";
import { Bootstrap5Pagination } from "laravel-vue-pagination";

export default {
    name: "DanhsachHoso",
    components: {
        Pagination: Bootstrap5Pagination,
    },
    data() {
        return {
            documentDeliveries: [], // เก็บ array ข้อมูลทั้งหมด
            search: "",
            currentPage: 1,
            perPage: 10,
            isMobile: window.innerWidth < 768,
            paginationClasses: {
                ul: "flex list-none",
                li: "mx-1",
                a: "block px-3 py-2 bg-white border border-gray-300 rounded hover:bg-gray-100",
                active: "bg-green-500 text-white",
                disabled: "opacity-50 cursor-not-allowed",
            },
        };
    },
    computed: {
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

            // จำลองโครงสร้าง pagination ของ Laravel
            return {
                current_page: this.currentPage,
                data,
                first_page_url: "",
                from: total > 0 ? start + 1 : 0,
                last_page: lastPage,
                last_page_url: "",
                next_page_url: this.currentPage < lastPage ? "" : null,
                path: "",
                per_page: this.perPage,
                prev_page_url: this.currentPage > 1 ? "" : null,
                to: start + data.length,
                total,
            };
        },
    },
    methods: {
        fetchData() {
            axios
                .get("/api/document-deliveries", {
                    params: {
                        search: this.search,
                        per_page: 1000, // ดึงข้อมูลทั้งหมดเพื่อให้ client side filter ได้
                    },
                })
                .then((response) => {
                    // สมมติว่า response.data.data คือ array ของรายการ
                    this.documentDeliveries = response.data.data;
                })
                .catch((error) => {
                    console.error(error);
                });
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
    },
    mounted() {
        this.fetchData();
        window.addEventListener("resize", this.handleResize);
    },
    beforeUnmount() {
        window.removeEventListener("resize", this.handleResize);
    },
};
</script>

<style scoped>
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
    box-shadow: 0 4px 8px rgba(16, 185, 129, 0.5);
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
</style>
