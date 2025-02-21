<template lang="">
    <div class="row align-items-center mb-2">
        <div class="col d-flex gap-3">
            <button type="button" class="button-30" @click="saveDocument">
                <i class="bx bxs-save"></i>Save
            </button>

            <button class="button-30" role="button" @click="sendDocument">
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
                <div class="progress-tracker" :class="document.status">
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
                                        placeholder="Mã số phiếu"
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
                                        placeholder="Ngày lập"
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
                                        placeholder="Tiều đề"
                                        disabled
                                    />
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="">Vụ đầu tư</label>
                                    <select
                                        class="form-control"
                                        v-model="document.investment_project"
                                    >
                                        <option
                                            v-for="project in investmentProjects"
                                            :key="project.id"
                                            :value="project.Ma_Vudautu"
                                        >
                                            {{ project.Ten_Vudautu }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="">Người tạo</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        :value="user ? user.full_name : ''"
                                        disabled
                                    />
                                </div>

                                <!-- <div class="form-group">
                                    <label for="">ID Người tạo</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        :value="document.creator_id"
                                        disabled
                                    />
                                </div> -->
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="">Trạm</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        :value="user ? user.station : ''"
                                        disabled
                                    />
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="">Loại hồ sơ</label>
                                    <select
                                        class="form-control"
                                        v-model="document.document_type"
                                    >
                                        <option
                                            v-for="type in documentTypes"
                                            :key="type.id"
                                            :value="type.Ten_LoaiHoso"
                                        >
                                            {{ type.Ten_LoaiHoso }}
                                        </option>
                                    </select>
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
                                    <label for="">Số lượng hồ sơ</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        v-model="document.file_count"
                                        placeholder="Số lượng hồ sơ"
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
                                        placeholder="Ngày nhận"
                                    />
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="">Người nhận</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        :value="user ? user.full_name : ''"
                                        disabled
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
            <!-- ส่วนที่ 4 ของการสร้าง รายละเอียดตาลาง -->
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <h5 class="col card-title">ADD BIÊN BẢN HOM GIỐNG</h5>
                        <div class="col text-end">
                            <button
                                type="button"
                                class="btn btn-success btn-sm"
                            >
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive mt-2">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Mã số phiếu</th>
                                    <th>Ngày lập</th>
                                    <th>Người tạo</th>
                                    <th>Trạm</th>
                                    <th>Loại hồ sơ</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div
        class="modal fade"
        id="extraLargeModal"
        tabindex="-1"
        aria-labelledby="extraLargeModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-lg modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="extraLargeModalLabel">
                        ADD BIÊN BẢN NGHIỆM THU DỊCH VỤ
                    </h6>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>
                </div>
                <div class="modal-body justify-content-center">
                    <div class="col d-flex gap-3">
                        <button type="button" class="button-30-save">
                            <i class="fa-solid fa-plus"></i>Add & Close
                        </button>
                    </div>
                    <!-- thong tin bien ban nghiem thu dich vu -->
                    <div class="col mt-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Thông tin chung</h5>
                                <div class="row gutters">
                                    <div class="col-12">
                                        <label for=""
                                            >Tìm kiếm Mã nghiệm thu</label
                                        >
                                        <div class="input-group">
                                            <div
                                                class="form-outline"
                                                data-mdb-input-init
                                            >
                                                <input
                                                    type="search"
                                                    id="form1"
                                                    class="form-control"
                                                />
                                            </div>
                                            <button
                                                type="button"
                                                class="btn btn btn-success"
                                                data-mdb-ripple-init
                                            >
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for=""
                                                >Hợp đồng đầu tư mía</label
                                            >
                                            <input
                                                type="text"
                                                class="form-control"
                                                placeholder="Tiều đề"
                                            />
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for=""
                                                >Khách hàng đầu tư mía</label
                                            >
                                            <input
                                                type="text"
                                                class="form-control"
                                                placeholder="Loại hồ sơ"
                                            />
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for=""
                                                >Hình thức thực hiện DV</label
                                            >
                                            <input
                                                type="text"
                                                class="form-control"
                                                placeholder="Số lượng hồ sơ"
                                            />
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for=""
                                                >Đối tác cung cấp dịch vụ</label
                                            >
                                            <input
                                                type="text"
                                                class="form-control"
                                                placeholder="Số lượng hồ sơ"
                                            />
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="">Tổng tiền</label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                placeholder="Số lượng hồ sơ"
                                            />
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
export default {
    data() {
        return {
            document: {
                document_code: "",
                created_date: "",
                title: "",
                investment_project: "",
                document_type: "",
                file_count: 0,
                received_date: "",
                status: "creating",
                creator_id: null,
                receiver_id: null,
                creator_name: "",
                station: "",
            },
            investmentProjects: [],
            documentTypes: [],
            user: null,
            receiver: null,
        };
    },
    mounted() {
        this.fetchUserData();
        this.fetchInvestmentProjects();
        this.fetchDocumentTypes();
    },
    methods: {
        fetchUserData() {
            const user = localStorage.getItem("web_user");
            if (user) {
                const parsedUser = JSON.parse(user);
                this.user = parsedUser;
                this.document.creator_id = parsedUser.id;
                this.document.creator_name = parsedUser.full_name;
                this.document.station = parsedUser.station;
                this.document.receiver_id = parsedUser.id;
                this.document.receiver_name = parsedUser.full_name;
            }
        },
        fetchInvestmentProjects() {
            axios
                .get("/api/investment-projects")
                .then((response) => {
                    this.investmentProjects = response.data;
                })
                .catch((error) => {
                    console.error(error);
                });
        },
        fetchDocumentTypes() {
            axios
                .get("/api/document-types")
                .then((response) => {
                    this.documentTypes = response.data;
                })
                .catch((error) => {
                    console.error(error);
                });
        },
        saveDocument() {
            axios
                .post("/api/document-deliveries", this.document)
                .then((response) => {
                    this.document = response.data;
                    alert("Document saved successfully");
                })
                .catch((error) => {
                    console.error(error);
                });
        },
        sendDocument() {
            this.updateDocumentStatus("sending");
        },
        rejectDocument() {
            this.updateDocumentStatus("creating");
        },
        receiveDocument() {
            this.updateDocumentStatus("received");
        },
        cancelDocument() {
            this.updateDocumentStatus("cancelled");
        },
        updateDocumentStatus(status) {
            axios
                .patch(`/api/document-deliveries/${this.document.id}/status`, {
                    status: status,
                    received_date: new Date().toISOString().split("T")[0],
                    receiver_id: this.user.id,
                })
                .then((response) => {
                    this.document = response.data;
                    alert(`Document status updated to ${status}`);
                })
                .catch((error) => {
                    console.error(error);
                });
        },
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
