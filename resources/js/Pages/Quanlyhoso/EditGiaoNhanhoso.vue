<template>
    <div class="row align-items-center mb-2">
        <div class="col d-flex gap-3">
            <button type="button" class="button-30" @click="saveDocument">
                <i class="bx bxs-save"></i>Save
            </button>
            <button class="button-30" role="button" @click="submitDocument">
                <i class="bx bx-calendar-check"></i>Nộp
            </button>
            <button type="button" class="button-30" @click="rejectDocument">
                <i class="bx bx-calendar-x"></i>Không duyệt
            </button>
            <button type="button" class="button-30" @click="receiveDocument">
                <i class="bx bx-check-square"></i>Duyệt
            </button>
            <button type="button" class="button-30" @click="cancelDocument">
                <i class="fa-solid fa-xmark"></i>Hủy
            </button>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <!-- progress-tracker-container -->
            <div class="col-12">
                <div class="progress-tracker">
                    <!-- Creating Step -->
                    <div
                        class="track-step"
                        :class="{ active: document.status === 'creating' }"
                    >
                        <div class="step-icon status-creating">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                        <span class="step-label">Nháp</span>
                    </div>

                    <!-- Sending Step -->
                    <div
                        class="track-step"
                        :class="{ active: document.status === 'sending' }"
                    >
                        <div class="step-icon status-sending">
                            <i class="fas fa-shipping-fast"></i>
                        </div>
                        <span class="step-label">Đang nộp</span>
                    </div>

                    <!-- Received Step -->
                    <div
                        class="track-step"
                        :class="{ active: document.status === 'received' }"
                    >
                        <div class="step-icon status-received">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <span class="step-label">Đã nhận</span>
                    </div>

                    <!-- Cancelled Step -->
                    <div
                        class="track-step"
                        :class="{ active: document.status === 'cancelled' }"
                    >
                        <div class="step-icon status-cancelled">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <span class="step-label">Hủy</span>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-column flex-md-row gap-1">
                <!-- ส่วนที่ 1 ของการสร้างหน้าจอ -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Thông tin phiếu</h5>
                        <div class="row gutters">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="">Mã số phiếu</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        v-model="document.document_code"
                                        disabled
                                    />
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="">Tiều đề</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        v-model="document.title"
                                        disabled
                                    />
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="">Loại hồ sơ</label>
                                    <select
                                        v-model="document.document_type"
                                        class="form-control"
                                    >
                                        <option
                                            v-for="type in documentTypes"
                                            :key="type.id"
                                            :value="type.name"
                                        >
                                            {{ type.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="">Số lượng hồ sơ</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        v-model="document.file_count"
                                        disabled
                                    />
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="">Tình trạng thanh toán</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        v-model="document.loan_status"
                                        disabled
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ส่วนที่ 2 ของการสร้างหน้าจอ -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Thông tin giao nhận</h5>
                        <div class="row gutters">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="">Người tạo</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        v-model="document.creator.full_name"
                                        disabled
                                    />
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="">Trạm</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        v-model="document.station"
                                        disabled
                                    />
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="">Ngày lập</label>
                                    <input
                                        type="date"
                                        class="form-control"
                                        v-model="document.created_date"
                                    />
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="">Người nhận</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        v-model="document.receiver.full_name"
                                        disabled
                                    />
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="">Ngày nhận</label>
                                    <input
                                        type="date"
                                        class="form-control"
                                        v-model="document.received_date"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ส่วนที่ 3 ของการสร้าง รายละเอียดตาลาง -->
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <h5 class="col card-title">
                            BIÊN BẢN NGHIỆM THU DỊCH VỤ
                        </h5>
                        <div class="col text-end">
                            <button
                                type="button"
                                class="btn btn-success btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#extraLargeModal"
                            >
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive mt-2">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Action</th>
                                    <th>Mã nghiệm thu</th>
                                    <th>Trạm</th>
                                    <th>Vụ đầu tư</th>
                                    <th>Tiêu đề</th>
                                    <th>Khách hàng cá nhân ĐT mía</th>
                                    <th>Khách hàng doanh nghiệp ĐT mía</th>
                                    <th>Hợp đồng đầu tư mía</th>
                                    <th>Hình thức thực hiện DV</th>
                                    <th>Hợp đồng cung ứng dịch vụ</th>
                                    <th>Tổng tiền</th>
                                    <th>Tổng tiền dịch vụ</th>
                                    <th>Tổng tiền tạm giữ</th>
                                    <th>Tổng tiền thanh toán</th>
                                    <th>Cán bộ nông vụ</th>
                                    <th>Tình trạng</th>
                                    <th>Tình trạng duyệt</th>
                                    <th>Ngày tạo</th>
                                    <th>Ngày cập nhật</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";

export default {
    data() {
        return {
            document: {
                document_code: "",
                title: "",
                document_type: "",
                file_count: 0,
                loan_status: "",
                creator: {
                    full_name: "",
                },
                station: "",
                created_date: "",
                receiver: {
                    full_name: "",
                },
                received_date: "",
                status: "",
            },
            documentTypes: [],
        };
    },
    methods: {
        fetchDocument(id) {
            axios.get(`/api/document-delivery/${id}`).then((response) => {
                this.document = response.data;
                console.log(this.id);
            });
        },
        fetchDocumentTypes() {
            axios.get("/api/document-types").then((response) => {
                this.documentTypes = response.data;
            });
        },
        saveDocument() {
            axios
                .put(
                    `/api/document-delivery/${this.document.id}`,
                    this.document
                )
                .then((response) => {
                    this.$router.push("/danhsachhoso");
                });
        },
        submitDocument() {
            this.document.status = "sending";
            this.saveDocument();
        },
        rejectDocument() {
            this.document.status = "creating";
            this.saveDocument();
        },
        receiveDocument() {
            this.document.status = "received";
            this.saveDocument();
        },
        cancelDocument() {
            this.document.status = "cancelled";
            this.saveDocument();
        },
    },
    mounted() {
        const id = this.$route.params.id;
        this.fetchDocument(id);
        this.fetchDocumentTypes();
    },
};
</script>

<style scoped>
.table th,
.table td {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 12px;
}

.progress-tracker {
    position: relative;
    display: flex;
    justify-content: space-between;
    margin: 0.5rem 0;
}
.progress-tracker::before {
    content: "";
    position: absolute;
    top: 20px;
    width: 100%;
    height: 3px;
    background-color: #e9ecef;
    z-index: 0;
    transition: background-color 0.3s;
}
.progress-tracker.creating::before {
    background-color: #fdd835;
}
.progress-tracker.sending::before {
    background-color: #1e88e5;
}
.progress-tracker.received::before {
    background-color: #198754;
}
.progress-tracker.cancelled::before {
    background-color: #dc3545;
}
.track-step {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    z-index: 1;
}
.step-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: white;
    font-size: 1.2rem;
    margin-bottom: 0.5rem;
    border: 2px solid #dee2e6;
    transition: all 0.3s;
}
.track-step.active .step-icon {
    width: 50px;
    height: 50px;
    font-size: 1.5rem;
    border: none;
}
.status-creating .step-icon {
    color: #fdd835;
}
.status-sending .step-icon {
    color: #1e88e5;
}
.status-received .step-icon {
    color: #198754;
}
.status-cancelled .step-icon {
    color: #dc3545;
}
.track-step.active .status-creating {
    background-color: #fdd835;
    color: white;
}
.track-step.active .status-sending {
    background-color: #1e88e5;
    color: white;
}
.track-step.active .status-received {
    background-color: #198754;
    color: white;
}
.track-step.active .status-cancelled {
    background-color: #dc3545;
    color: white;
}
.step-label {
    text-align: center;
    font-size: 0.9rem;
    max-width: 100px;
}
@media (max-width: 768px) {
    /* .progress-tracker {
        flex-wrap: wrap;
        justify-content: center;
        gap: 2rem;
    }
    .progress-tracker::before {
        display: none;
    } */
}
</style>
